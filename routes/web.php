<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Analisis\KodeController;
use App\Http\Controllers\Analisis\UrlController;
use App\Http\Controllers\Analisis\CveIntelController;

use App\Http\Controllers\Analisis\LogController;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', fn() => view('landing'))->name('beranda');
Route::get('/privacy-policy', fn() => view('legal.privacy-policy'))->name('privacy-policy');
Route::get('/terms', fn() => view('legal.terms'))->name('terms');
Route::get('/ketentuan-layanan', fn() => view('legal.terms'))->name('legal.ketentuan');

// RFC 9116 — Security Contact (/.well-known/security.txt)
Route::get('/.well-known/security.txt', function () {
    $content = <<<EOT
Contact: mailto:security@redsim.my.id
Expires: 2027-01-01T00:00:00.000Z
Encryption: https://redsim.my.id/pgp-key.asc
Preferred-Languages: id, en
Canonical: https://redsim.my.id/.well-known/security.txt
Policy: https://redsim.my.id/terms
Acknowledgments: https://redsim.my.id/hall-of-fame
EOT;
    return response($content, 200)->header('Content-Type', 'text/plain; charset=utf-8');
})->name('security-txt');

// Fallback shortcut tanpa .well-known
Route::get('/security.txt', fn() => redirect('/.well-known/security.txt', 301));


// Auth
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'prosesMasuk'])->middleware('throttle.login');
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'prosesDaftar']);
    Route::get('/menunggu-verifikasi', [AuthController::class, 'menungguVerifikasi'])->name('menunggu-verifikasi');

    // OTP WhatsApp Login
    Route::get('/otp', [AuthController::class, 'formOtp'])->name('otp');
    Route::post('/otp/kirim', [AuthController::class, 'kirimOtp'])->name('otp.kirim');
    Route::get('/otp/verifikasi', [AuthController::class, 'formVerifikasiOtp'])->name('otp.verifikasi');
    Route::post('/otp/verifikasi', [AuthController::class, 'verifikasiOtp'])->name('otp.proses-verifikasi');

    // Lupa Password via WhatsApp
    Route::get('/lupa-password', [AuthController::class, 'formLupaPassword'])->name('lupa-password');
    Route::post('/lupa-password', [AuthController::class, 'prosesLupaPassword'])->name('lupa-password.proses');
    Route::get('/reset-password/{token}', [AuthController::class, 'formResetPassword'])->name('reset-password');
    Route::post('/reset-password/{token}', [AuthController::class, 'prosesResetPassword'])->name('reset-password.proses');
});

// Verifikasi Akun via Link WA (harus bisa diakses tanpa login)
Route::get('/verifikasi-akun/{token}', [AuthController::class, 'verifikasiAkun'])->name('verifikasi-akun');
Route::post('/verifikasi/kirim-ulang', [AuthController::class, 'kirimUlangVerifikasi'])->name('verifikasi.kirim-ulang');

// Public Edukasi
Route::get('/edukasi/leaderboard', [EdukasiController::class, 'leaderboard']);

// Google OAuth (bisa diakses guest maupun auth)
Route::get('/oauth/google', [AuthController::class, 'redirectToGoogle'])->name('oauth.google');
Route::get('/oauth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::get('/oauth/google/whatsapp', [AuthController::class, 'formGoogleWhatsapp'])->name('oauth.google.whatsapp');
Route::post('/oauth/google/whatsapp', [AuthController::class, 'prosesGoogleWhatsapp'])->name('oauth.google.whatsapp.proses');

// Protected
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/ai-health', [\App\Http\Controllers\AiHealthController::class, 'check'])->name('api.ai-health');
    Route::get('/api/nvd-health', [\App\Http\Controllers\AiHealthController::class, 'nvdHealth'])->name('api.nvd-health');
    Route::get('/api/ml-health', [\App\Http\Controllers\AiHealthController::class, 'mlHealth'])->name('api.ml-health');
    Route::get('/api/system-health', [\App\Http\Controllers\AiHealthController::class, 'systemHealth'])->name('api.system-health');

    // Profile
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // User AI Configuration
    Route::get('/profil/ai', [\App\Http\Controllers\UserAiConfigController::class, 'index'])->name('profile.ai');
    Route::post('/profil/ai/detect', [\App\Http\Controllers\UserAiConfigController::class, 'detectKey'])->name('profile.ai.detect');
    Route::post('/profil/ai', [\App\Http\Controllers\UserAiConfigController::class, 'store'])->name('profile.ai.store');
    Route::patch('/profil/ai/model', [\App\Http\Controllers\UserAiConfigController::class, 'updateModel'])->name('profile.ai.update-model');
    Route::delete('/profil/ai', [\App\Http\Controllers\UserAiConfigController::class, 'destroy'])->name('profile.ai.destroy');
    Route::get('/profil/ai/oauth/google', [\App\Http\Controllers\UserAiConfigController::class, 'redirectGoogleAI'])->name('profile.ai.oauth.google');
    Route::get('/profil/ai/oauth/google/callback', [\App\Http\Controllers\UserAiConfigController::class, 'handleGoogleAICallback']);
    Route::get('/profil/ai/oauth/openai', [\App\Http\Controllers\UserAiConfigController::class, 'redirectOpenAI'])->name('profile.ai.oauth.openai');
    Route::get('/profil/ai/oauth/openai/callback', [\App\Http\Controllers\UserAiConfigController::class, 'handleOpenAICallback']);
    Route::post('/profil/ai/test-models', [\App\Http\Controllers\UserAiConfigController::class, 'testModels'])->name('profile.ai.test-models');
    Route::post('/profil/ai/remove-models', [\App\Http\Controllers\UserAiConfigController::class, 'removeModels'])->name('profile.ai.remove-models');
    Route::post('/profil/ai/refresh-models', [\App\Http\Controllers\UserAiConfigController::class, 'refreshModels'])->name('profile.ai.refresh-models');

    // Analisis dengan rate limit
    Route::middleware('cek.ratelimit')->group(function () {
        Route::get('/analisis/kode', [KodeController::class, 'form'])->name('analisis.kode');
        Route::post('/analisis/kode', [KodeController::class, 'proses']);

        Route::get('/analisis/url', [UrlController::class, 'form'])->name('analisis.url');
        Route::post('/analisis/url', [UrlController::class, 'proses']);
        // Halaman pilih metode verifikasi domain
        Route::get('/analisis/url/pilih-metode-verifikasi', [UrlController::class, 'pilihMetodeVerifikasi'])->name('analisis.url.pilih-metode-verifikasi');
        // Generate token verifikasi (menerima metode yang dipilih)
        Route::post('/analisis/url/generate-verifikasi', [UrlController::class, 'generateVerifikasi'])->name('analisis.url.generate-verifikasi');

        Route::get('/analisis/log', [LogController::class, 'form'])->name('analisis.log');
        Route::post('/analisis/log', [LogController::class, 'proses']);

        // Fase 2: CVE Intelligence Dashboard
        Route::get('/analisis/cve-intel', [CveIntelController::class, 'form'])->name('analisis.cve-intel');
        Route::post('/analisis/cve-intel', [CveIntelController::class, 'analisis'])->name('analisis.cve-intel.proses');

        // Fase D: Threat Hunt by CVE
        Route::get('/threat-hunt', [\App\Http\Controllers\Analisis\ThreatHuntController::class, 'index'])->name('threat-hunt.index');
        Route::post('/threat-hunt/execute', [\App\Http\Controllers\Analisis\ThreatHuntController::class, 'execute'])->name('threat-hunt.execute');

        // Fase B.2: Asset Groups & Multi-Target ASM
        Route::get('/asset-groups', [\App\Http\Controllers\AssetGroupController::class, 'index'])->name('asset-groups.index');
        Route::get('/asset-groups/tambah', [\App\Http\Controllers\AssetGroupController::class, 'create'])->name('asset-groups.create');
        Route::post('/asset-groups', [\App\Http\Controllers\AssetGroupController::class, 'store'])->name('asset-groups.store');
        Route::get('/asset-groups/{assetGroup}', [\App\Http\Controllers\AssetGroupController::class, 'show'])->name('asset-groups.show');
        Route::put('/asset-groups/{assetGroup}', [\App\Http\Controllers\AssetGroupController::class, 'update'])->name('asset-groups.update');
        Route::delete('/asset-groups/{assetGroup}', [\App\Http\Controllers\AssetGroupController::class, 'destroy'])->name('asset-groups.destroy');
        Route::post('/asset-groups/{assetGroup}/targets', [\App\Http\Controllers\AssetGroupController::class, 'addTarget'])->name('asset-groups.add-target');
        Route::delete('/asset-groups/targets/{target}', [\App\Http\Controllers\AssetGroupController::class, 'removeTarget'])->name('asset-groups.remove-target');
        Route::post('/asset-groups/{assetGroup}/batch-scan', [\App\Http\Controllers\AssetGroupController::class, 'batchScan'])->name('asset-groups.batch-scan');
    });

    // Domain verification routes (no rate limit needed)
    Route::get('/analisis/url/verifikasi/{domainVerification}', [UrlController::class, 'formVerifikasi'])->name('analisis.url.verifikasi');
    Route::post('/analisis/url/verifikasi/{domainVerification}', [UrlController::class, 'prosesVerifikasi'])->name('analisis.url.proses-verifikasi');
    Route::post('/analisis/url/verifikasi/{domainVerification}/kirim-email', [UrlController::class, 'kirimEmailVerifikasi'])->name('analisis.url.kirim-email-verifikasi');
    Route::get('/analisis/url/download-verifikasi/{domainVerification}', [UrlController::class, 'downloadVerifikasi'])->name('analisis.url.download-verifikasi');

    // Route yang memerlukan verifikasi kepemilikan scan (UUID)
    Route::middleware('scan.owner')->group(function () {
        Route::get('/analisis/status/{scan}', [LaporanController::class, 'status'])->name('analisis.status');
        Route::get('/analisis/hasil/{scan}', [LaporanController::class, 'hasil'])->name('analisis.hasil');
        Route::get('/laporan/{scan}', [LaporanController::class, 'detail'])->name('laporan.detail');
        Route::get('/laporan/{scan}/pdf', [LaporanController::class, 'exportPDF'])->name('laporan.pdf');
        Route::get('/laporan/{scan}/bandingkan', [LaporanController::class, 'bandingkan'])->name('laporan.bandingkan');
        Route::post('/analisis/url/ai-analisis/{scan}', [UrlController::class, 'aiAnalisis'])->name('analisis.url.ai-analisis');
        
        // Red Team Simulation
        Route::get('/simulasi/{scan}', [\App\Http\Controllers\SimulasiController::class, 'index'])->name('simulasi.index');
        Route::get('/simulasi/{scan}/{simulasi}', [\App\Http\Controllers\SimulasiController::class, 'show'])->name('simulasi.show');
        Route::post('/simulasi/{scan}/{simulasi}/run-ai', [\App\Http\Controllers\SimulasiController::class, 'runAI'])->name('simulasi.run-ai');
        Route::post('/simulasi/{scan}/{simulasi}/pdf', [\App\Http\Controllers\SimulasiController::class, 'exportPDF'])->name('simulasi.pdf');
        
        // Payload Generator
        Route::post('/payload/generate/{temuan}', [\App\Http\Controllers\PayloadController::class, 'generate'])->name('payload.generate');
    });

    // Laporan index (tidak perlu scan.owner karena sudah filter by user)
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Incident Management & Remediasi (SOC / DevSecOps)
    Route::get('/insiden', [\App\Http\Controllers\InsidenController::class, 'index'])->name('insiden.index');
    Route::get('/insiden/tambah', [\App\Http\Controllers\InsidenController::class, 'create'])->name('insiden.create');
    Route::post('/insiden', [\App\Http\Controllers\InsidenController::class, 'store'])->name('insiden.store');
    Route::post('/insiden/temuan/{temuan}', [\App\Http\Controllers\InsidenController::class, 'storeFromTemuan'])->name('insiden.store-from-temuan');
    Route::post('/insiden/takeover', [\App\Http\Controllers\InsidenController::class, 'storeFromTakeover'])->name('insiden.store-from-takeover');
    Route::get('/insiden/{insiden}', [\App\Http\Controllers\InsidenController::class, 'show'])->name('insiden.show');
    Route::put('/insiden/{insiden}', [\App\Http\Controllers\InsidenController::class, 'update'])->name('insiden.update');
    Route::delete('/insiden/{insiden}', [\App\Http\Controllers\InsidenController::class, 'destroy'])->name('insiden.destroy');

    // Hub Seluruh Temuan Keamanan (All Findings Hub) & Resolusi
    Route::get('/temuan', [\App\Http\Controllers\TemuanController::class, 'index'])->name('temuan.index');
    Route::patch('/temuan/{temuan}/resolve', [\App\Http\Controllers\TemuanController::class, 'resolve'])->name('temuan.resolve');
    Route::patch('/temuan/{temuan}/reopen', [\App\Http\Controllers\TemuanController::class, 'reopen'])->name('temuan.reopen');

    // Chat AI (semua user)
    Route::get('/chat-ai', [\App\Http\Controllers\ChatController::class, 'indexPublic'])->name('chat-ai');
    Route::post('/chat-ai', [\App\Http\Controllers\ChatController::class, 'chat'])->name('chat-ai.send');

    // Pilar 3 — Red Team Lab (CVSSv3 Calculator + Attack Path + MITRE)
    Route::get('/red-team-lab', [\App\Http\Controllers\CvssController::class, 'lab'])->name('red-team-lab');
    Route::post('/api/cvss/calculate', [\App\Http\Controllers\CvssController::class, 'calculate'])->name('cvss.calculate');

    // Edukasi
    Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi.index');
    Route::get('/edukasi/ensiklopedia', [EdukasiController::class, 'ensiklopedia']);
    Route::get('/edukasi/tantangan', [EdukasiController::class, 'tantangan'])->name('edukasi.tantangan');
    Route::get('/edukasi/tantangan/{tantangan}', [EdukasiController::class, 'detailTantangan'])->name('edukasi.tantangan.detail');
    Route::post('/edukasi/tantangan/{tantangan}/jawab', [EdukasiController::class, 'jawab'])->name('edukasi.tantangan.jawab');

    // Admin
    Route::middleware('cek.admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/kesehatan-sistem', [AdminController::class, 'kesehatanSistem'])->name('admin.kesehatan-sistem');
        Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit-log');

        // Pilar 2 — Benchmark LLM vs ML
        Route::get('/benchmark', [\App\Http\Controllers\BenchmarkController::class, 'index'])->name('admin.benchmark');
        Route::get('/benchmark/export-csv', [\App\Http\Controllers\BenchmarkController::class, 'exportCsv'])->name('admin.benchmark.csv');
        
        Route::get('/ai-config', [AdminController::class, 'aiConfig'])->name('admin.ai-config');
        Route::post('/ai-config/detect', [AdminController::class, 'detectApiKey']);
        Route::post('/ai-config', [AdminController::class, 'storeAiConfig']);
        Route::patch('/ai-config/{config}', [AdminController::class, 'updateAiConfig']);
        Route::delete('/ai-config/{config}/hapus', [AdminController::class, 'deleteAiConfig']);

        // Manajemen Edukasi (Soal Pilihan Ganda)
        Route::get('/tantangan', [\App\Http\Controllers\AdminTantanganController::class, 'index'])->name('admin.tantangan.index');
        Route::get('/tantangan/tambah', [\App\Http\Controllers\AdminTantanganController::class, 'create'])->name('admin.tantangan.create');
        Route::post('/tantangan', [\App\Http\Controllers\AdminTantanganController::class, 'store'])->name('admin.tantangan.store');
        Route::get('/tantangan/{tantangan}/edit', [\App\Http\Controllers\AdminTantanganController::class, 'edit'])->name('admin.tantangan.edit');
        Route::put('/tantangan/{tantangan}', [\App\Http\Controllers\AdminTantanganController::class, 'update'])->name('admin.tantangan.update');
        Route::delete('/tantangan/{tantangan}', [\App\Http\Controllers\AdminTantanganController::class, 'destroy'])->name('admin.tantangan.destroy');
        Route::post('/tantangan/generate-ai', [\App\Http\Controllers\AdminTantanganController::class, 'generateAi'])->name('admin.tantangan.generate-ai');
        Route::get('/tantangan/random-topik', [\App\Http\Controllers\AdminTantanganController::class, 'randomTopik'])->name('admin.tantangan.random-topik');

        // Manajemen Pengguna (Approval)
        Route::get('/pengguna', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('admin.users.index');
        Route::patch('/pengguna/{user}/setujui', [\App\Http\Controllers\AdminUserController::class, 'verify'])->name('admin.users.verify');
        Route::patch('/pengguna/{user}/batalkan', [\App\Http\Controllers\AdminUserController::class, 'unverify'])->name('admin.users.unverify');
        Route::post('/pengguna/{user}/kirim-notifikasi', [\App\Http\Controllers\AdminUserController::class, 'resendNotification'])->name('admin.users.resendNotification');
        Route::delete('/pengguna/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        // Konfigurasi WhatsApp (OpenWA)
        Route::get('/wa-settings', [\App\Http\Controllers\AdminUserController::class, 'waSettings'])->name('admin.wa-settings');
        Route::post('/wa-settings', [\App\Http\Controllers\AdminUserController::class, 'updateWaSettings'])->name('admin.wa-settings.update');
        Route::post('/wa-settings/test', [\App\Http\Controllers\AdminUserController::class, 'testWaMessage'])->name('admin.wa-settings.test');
        Route::get('/wa-settings/status', [\App\Http\Controllers\AdminUserController::class, 'checkWaStatus'])->name('admin.wa-settings.status');

    });

    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
});
