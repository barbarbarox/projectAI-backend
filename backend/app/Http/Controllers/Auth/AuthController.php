<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PasswordResetWa;
use App\Models\User;
use App\Services\WhatsappService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    // ========== reCAPTCHA v3 Helper ==========

    protected function verifyRecaptcha(?string $token): bool
    {
        if (empty($token)) return false;
        $secret = env('RECAPTCHA_SECRET_KEY');
        if (empty($secret)) return true; // Skip jika belum dikonfigurasi

        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $token,
            ]);

            $data = $response->json();
            return ($data['success'] ?? false) && ($data['score'] ?? 0) >= 0.3;
        } catch (\Exception $e) {
            return false;
        }
    }

    // ========== LOGIN FORM ==========

    public function formMasuk()
    {
        return view('auth.masuk');
    }

    public function prosesMasuk(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'g-recaptcha-response.required' => 'Verifikasi reCAPTCHA gagal. Muat ulang halaman.',
        ]);

        // Verifikasi reCAPTCHA v3
        if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return back()->withErrors(['email' => 'Verifikasi reCAPTCHA gagal. Anda terdeteksi sebagai bot.'])->onlyInput('email');
        }

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $user = Auth::user();

            // Admin hanya bisa login via Google OAuth
            if ($user->tier === 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun admin hanya bisa login menggunakan Google. Gunakan tombol "Masuk dengan Google".'])->onlyInput('email');
            }

            // Cek verifikasi — user yang belum diverifikasi TIDAK BOLEH login
            if (!$user->is_verified && $user->tier !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda belum diverifikasi. Silakan cek WhatsApp Anda untuk link verifikasi.'])->onlyInput('email');
            }

            // Cek apakah user punya nomor WA — jika belum, tidak boleh login
            if (empty($user->phone) && $user->tier !== 'admin') {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun Anda belum memiliki nomor WhatsApp. Silakan daftar ulang.'])->onlyInput('email');
            }

            $request->session()->regenerate();
            $user->update(['last_login' => now()]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'login_sukses',
                'description' => 'Pengguna berhasil login menggunakan form email/password.',
            ]);

            // Kirim notifikasi login via WhatsApp (async, tidak block response)
            $this->kirimNotifikasiLogin($user, 'Email & Password');

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
    }

    // ========== REGISTER FORM ==========

    public function formDaftar()
    {
        return view('auth.daftar');
    }

    public function prosesDaftar(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'nama_lengkap' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()     // Harus ada huruf besar dan kecil
                    ->numbers()       // Harus ada angka
                    ->symbols(),      // Harus ada simbol/karakter spesial
            ],
            'g-recaptcha-response' => 'required',
        ], [
            'name.required' => 'Nama pengguna wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        // Verifikasi reCAPTCHA v3
        if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return back()->withErrors(['email' => 'Verifikasi reCAPTCHA gagal.'])->withInput();
        }

        // Normalisasi nomor telepon: 08xxx → 628xxx
        $phone = $this->normalizePhone($request->phone);

        // Cek duplikasi nomor telepon
        if (User::where('phone', $phone)->exists()) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp sudah terdaftar.'])->withInput();
        }

        // Generate verification token
        $verificationToken = Str::random(64);

        $user = User::create([
            'name' => $request->name,
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'phone' => $phone,
            'password' => Hash::make($request->password),
            'last_login' => now(),
            'is_verified' => false,
            'verification_token' => $verificationToken,
            'verification_expires_at' => now()->addMinutes(30),
        ]);

        // Kirim link verifikasi via WhatsApp
        $verificationUrl = route('verifikasi-akun', ['token' => $verificationToken]);
        $wa = app(WhatsappService::class);
        $wa->sendVerificationLink($phone, $user->name, $verificationUrl);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'daftar_akun',
            'description' => 'Pengguna mendaftar dan link verifikasi dikirim ke WA.',
        ]);

        return redirect()->route('menunggu-verifikasi')->with('phone', $phone);
    }

    public function menungguVerifikasi()
    {
        return view('auth.menunggu-verifikasi');
    }

    // ========== VERIFIKASI AKUN VIA LINK WA ==========

    public function verifikasiAkun(string $token)
    {
        $user = User::where('verification_token', $token)->first();

        if (!$user) {
            return view('auth.verifikasi-akun-gagal', [
                'pesan' => 'Link verifikasi tidak valid atau sudah digunakan.',
            ]);
        }

        if ($user->is_verified) {
            return redirect()->route('masuk')->with('success', 'Akun Anda sudah terverifikasi. Silakan login.');
        }

        if (now()->gt($user->verification_expires_at)) {
            return view('auth.verifikasi-akun-gagal', [
                'pesan' => 'Link verifikasi sudah kedaluwarsa. Silakan minta kirim ulang.',
                'dapat_kirim_ulang' => true,
                'user_id' => $user->id,
            ]);
        }

        // Verifikasi berhasil!
        $user->update([
            'is_verified' => true,
            'verification_token' => null,
            'verification_expires_at' => null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'verifikasi_akun',
            'description' => 'Pengguna berhasil memverifikasi akun via link WhatsApp.',
        ]);

        return redirect()->route('masuk')->with('success', 'Akun berhasil diverifikasi! Silakan login.');
    }

    /**
     * Kirim ulang link verifikasi.
     */
    public function kirimUlangVerifikasi(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak terdaftar.'])->withInput();
        }

        if ($user->is_verified) {
            return redirect()->route('masuk')->with('success', 'Akun Anda sudah terverifikasi. Silakan login.');
        }

        // Generate token baru
        $verificationToken = Str::random(64);
        $user->update([
            'verification_token' => $verificationToken,
            'verification_expires_at' => now()->addMinutes(30),
        ]);

        // Kirim link baru via WhatsApp
        $verificationUrl = route('verifikasi-akun', ['token' => $verificationToken]);
        $wa = app(WhatsappService::class);
        $sent = $wa->sendVerificationLink($phone, $user->name, $verificationUrl);

        if (!$sent) {
            return back()->withErrors(['phone' => 'Gagal mengirim link verifikasi. Pastikan nomor WhatsApp aktif.'])->withInput();
        }

        return back()->with('success', 'Link verifikasi baru telah dikirim ke WhatsApp Anda. Berlaku 30 menit.');
    }

    // ========== OTP LOGIN VIA WHATSAPP ==========

    public function formOtp()
    {
        return view('auth.otp');
    }

    public function kirimOtp(Request $request, WhatsappService $wa)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'g-recaptcha-response' => 'required',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            'g-recaptcha-response.required' => 'Verifikasi reCAPTCHA gagal.',
        ]);

        if (!$this->verifyRecaptcha($request->input('g-recaptcha-response'))) {
            return back()->withErrors(['phone' => 'Verifikasi reCAPTCHA gagal.'])->withInput();
        }

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak terdaftar.'])->withInput();
        }

        // Cek verifikasi — WAJIB diverifikasi sebelum bisa login
        if (!$user->is_verified && $user->tier !== 'admin') {
            return back()->withErrors(['phone' => 'Akun Anda belum diverifikasi. Silakan cek WhatsApp Anda untuk link verifikasi.'])->withInput();
        }

        // User sudah diverifikasi — langsung login
        $user->update([
            'last_login' => now(),
        ]);

        Auth::login($user, true);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login_whatsapp_direct',
            'description' => 'Pengguna login langsung via WhatsApp tanpa OTP.',
        ]);

        // Kirim notifikasi login
        $this->kirimNotifikasiLogin($user, 'WhatsApp (Tanpa OTP)');

        return redirect('/dashboard')->with('success', 'Berhasil masuk.');
    }

    public function formVerifikasiOtp(Request $request)
    {
        $phone = $request->query('phone');
        if (empty($phone)) return redirect()->route('otp');

        return view('auth.verifikasi-otp', compact('phone'));
    }

    public function verifikasiOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.size' => 'Kode OTP harus 6 digit.',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Nomor WhatsApp tidak ditemukan.']);
        }

        // Cek verifikasi akun
        if (!$user->is_verified && $user->tier !== 'admin') {
            return back()->withErrors(['otp' => 'Akun Anda belum diverifikasi. Silakan cek WhatsApp Anda untuk link verifikasi.']);
        }

        if (!$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP sudah kedaluwarsa. Silakan minta kode baru.']);
        }

        if (!Hash::check($request->otp, $user->otp_code)) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // OTP valid — login user
        $user->update([
            'otp_code' => null,
            'otp_expires_at' => null,
            'last_login' => now(),
        ]);

        Auth::login($user, true);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'login_otp_whatsapp',
            'description' => 'Pengguna login via OTP WhatsApp.',
        ]);

        // Kirim notifikasi login via WhatsApp
        $this->kirimNotifikasiLogin($user, 'OTP WhatsApp');

        return redirect('/dashboard');
    }

    // ========== GOOGLE OAUTH ==========

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect('/masuk')->with('error', 'Gagal masuk dengan Google. Silakan coba lagi.');
        }

        $adminEmail = 'maulanaakbar1387@gmail.com';
        $isAdminEmail = strtolower($googleUser->getEmail()) === strtolower($adminEmail);

        // Cari user berdasarkan google_id atau email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        // ══════════════════════════════════════════════
        // ADMIN: Login langsung tanpa verifikasi WA
        // ══════════════════════════════════════════════
        if ($isAdminEmail) {
            if ($user) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'tier' => 'admin',
                    'is_verified' => true,
                    'last_login' => now(),
                ]);
            } else {
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'Admin',
                    'nama_lengkap' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(32)),
                    'tier' => 'admin',
                    'is_verified' => true,
                    'last_login' => now(),
                ]);
            }

            Auth::login($user, true);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'login_admin_google',
                'description' => 'Admin login menggunakan akun Google OAuth.',
            ]);

            return redirect('/admin')->with('success', 'Selamat datang, Admin!');
        }

        // ══════════════════════════════════════════════
        // USER BIASA: Alur normal (verifikasi WA)
        // ══════════════════════════════════════════════
        if ($user) {
            // Pastikan user biasa tidak bisa jadi admin
            if ($user->tier === 'admin') {
                $user->update(['tier' => 'user']);
            }

            // Update google_id jika belum ada
            if (!$user->google_id) {
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Jika user sudah punya WA dan sudah diverifikasi → langsung login
            if ($user->is_verified && !empty($user->phone)) {
                $user->update(['last_login' => now()]);
                Auth::login($user, true);

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'login_google',
                    'description' => 'Pengguna login menggunakan akun Google.',
                ]);

                $this->kirimNotifikasiLogin($user, 'Google OAuth');
                return redirect('/dashboard');
            }

            // Jika user sudah punya WA tapi belum diverifikasi
            if (!empty($user->phone) && !$user->is_verified) {
                return redirect('/masuk')->withErrors(['email' => 'Akun Anda belum diverifikasi. Silakan cek WhatsApp Anda untuk link verifikasi.']);
            }

            // Jika belum punya WA → redirect ke form isi WA
            session(['google_auth_user_id' => $user->id]);
            return redirect()->route('oauth.google.whatsapp');
        }

        // User baru — buat akun baru tanpa WA, redirect ke form WA
        $user = User::create([
            'name' => $googleUser->getName() ?? $googleUser->getNickname() ?? 'User',
            'nama_lengkap' => $googleUser->getName(),
            'email' => $googleUser->getEmail(),
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => Hash::make(Str::random(24)),
            'is_verified' => false,
            'last_login' => now(),
        ]);

        session(['google_auth_user_id' => $user->id]);
        return redirect()->route('oauth.google.whatsapp');
    }

    public function formGoogleWhatsapp()
    {
        if (!session()->has('google_auth_user_id')) {
            return redirect('/masuk');
        }
        return view('auth.google-whatsapp');
    }

    public function prosesGoogleWhatsapp(Request $request)
    {
        $userId = session('google_auth_user_id');
        if (!$userId) {
            return redirect('/masuk');
        }

        $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $phone = $this->normalizePhone($request->phone);

        if (User::where('phone', $phone)->where('id', '!=', $userId)->exists()) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp sudah terdaftar oleh akun lain.'])->withInput();
        }

        $user = User::find($userId);
        if (!$user) {
            session()->forget('google_auth_user_id');
            return redirect('/masuk')->with('error', 'Akun tidak ditemukan.');
        }

        // Generate verification token
        $verificationToken = Str::random(64);

        $user->update([
            'phone' => $phone,
            'is_verified' => false,
            'verification_token' => $verificationToken,
            'verification_expires_at' => now()->addMinutes(30),
        ]);

        // Kirim link verifikasi via WhatsApp
        $verificationUrl = route('verifikasi-akun', ['token' => $verificationToken]);
        $wa = app(WhatsappService::class);
        $wa->sendVerificationLink($phone, $user->name, $verificationUrl);

        session()->forget('google_auth_user_id');

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'google_wa_verifikasi',
            'description' => 'Pengguna Google OAuth melengkapi WA, link verifikasi dikirim.',
        ]);

        return redirect()->route('menunggu-verifikasi')->with([
            'success' => 'Link verifikasi telah dikirim ke WhatsApp Anda. Berlaku 30 menit.',
            'phone' => $phone,
        ]);
    }

    // ========== LUPA PASSWORD VIA WHATSAPP ==========

    public function formLupaPassword()
    {
        return view('auth.lupa-password');
    }

    public function prosesLupaPassword(Request $request, WhatsappService $wa)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ], [
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
        ]);

        $phone = $this->normalizePhone($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return back()->withErrors(['phone' => 'Nomor WhatsApp tidak terdaftar.'])->withInput();
        }

        // Hapus token sebelumnya yang belum digunakan
        PasswordResetWa::where('phone', $phone)->where('used', false)->delete();

        // Generate token unik
        $token = Str::random(64);

        PasswordResetWa::create([
            'phone' => $phone,
            'token' => $token,
            'expires_at' => now()->addMinutes(5),
        ]);

        // Buat link reset password
        $resetUrl = route('reset-password', ['token' => $token]);

        // Kirim link via WhatsApp
        $sent = $wa->sendPasswordResetLink($phone, $resetUrl);

        if (!$sent) {
            return back()->withErrors(['phone' => 'Gagal mengirim link reset. Pastikan nomor WhatsApp aktif.'])->withInput();
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'lupa_password',
            'description' => 'Pengguna meminta reset password via WhatsApp.',
        ]);

        return back()->with('success', 'Link reset password telah dikirim ke WhatsApp Anda. Link berlaku selama 5 menit.');
    }

    public function formResetPassword(string $token)
    {
        $reset = PasswordResetWa::where('token', $token)->first();

        if (!$reset || !$reset->isValid()) {
            return redirect()->route('lupa-password')
                ->withErrors(['token' => 'Link reset password sudah kedaluwarsa atau tidak valid. Silakan minta link baru.']);
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function prosesResetPassword(Request $request, string $token)
    {
        $reset = PasswordResetWa::where('token', $token)->first();

        if (!$reset || !$reset->isValid()) {
            return redirect()->route('lupa-password')
                ->withErrors(['token' => 'Link reset password sudah kedaluwarsa atau tidak valid.']);
        }

        $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::where('phone', $reset->phone)->first();

        if (!$user) {
            return redirect()->route('lupa-password')
                ->withErrors(['token' => 'Akun tidak ditemukan.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Tandai token sebagai sudah digunakan
        $reset->update(['used' => true]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'reset_password',
            'description' => 'Pengguna berhasil mereset password via WhatsApp.',
        ]);

        return redirect()->route('masuk')
            ->with('success', 'Kata sandi berhasil direset. Silakan masuk dengan kata sandi baru Anda.');
    }

    // ========== LOGOUT ==========

    public function keluar(Request $request)
    {
        $userId = Auth::id();

        if ($userId) {
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'logout',
                'description' => 'Pengguna logout dari sistem.',
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // ========== HELPER ==========

    /**
     * Normalisasi nomor telepon ke format 628xxx.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }
        return $phone;
    }

    /**
     * Kirim notifikasi login via WhatsApp (non-blocking).
     */
    protected function kirimNotifikasiLogin(User $user, string $method): void
    {
        if (empty($user->phone)) return;

        try {
            $wa = app(WhatsappService::class);
            $wa->sendLoginNotification($user->phone, $user->name, $method);
        } catch (\Exception $e) {
            Log::warning("Gagal kirim notifikasi login WA: " . $e->getMessage());
        }
    }
}
