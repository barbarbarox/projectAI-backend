<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user        = Auth::user();
        $batasHarian = $user->tier === 'gratis' ? (int) env('MAX_SCANS_PER_DAY_FREE', 10) : 999;
        $scanHariIni = min($user->scan_count_today ?? 0, $batasHarian);

        return view('profile.edit', [
            'user'        => $user,
            'scanHariIni' => $scanHariIni,
            'batasHarian' => $batasHarian,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Normalisasi nomor telepon sebelum validasi
        $phone = $request->phone;
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        if (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        // Merge back to request so validation uses the normalized value
        $request->merge(['phone' => $phone]);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nama_lengkap' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
        ], [
            'phone.unique' => 'Nomor WhatsApp sudah digunakan oleh akun lain.',
        ]);

        $user->update([
            'name' => $request->name,
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'phone' => $phone,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required', 
                'confirmed', 
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
            ],
        ], [
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
        ], [
            'avatar.max'   => 'Ukuran foto maksimal 2MB.',
            'avatar.mimes' => 'Format foto harus jpeg, png, jpg, gif, atau webp.',
        ]);

        $user = Auth::user();

        // Hapus avatar lama jika bukan URL eksternal
        if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
            \Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');

        $user->update(['avatar' => \Storage::disk('public')->url($path)]);

        return back()->with('success', 'Foto profil berhasil diperbarui.');
    }
}
