<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Analisis\KodeController;
use App\Http\Controllers\Analisis\UrlController;
use App\Http\Controllers\Analisis\ZipController;
use App\Http\Controllers\Analisis\LogController;
use App\Http\Controllers\EdukasiController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Halaman publik
Route::get('/', fn() => view('landing'))->name('beranda');

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

    // Profile
    Route::get('/profil', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profil', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Analisis dengan rate limit
    Route::middleware('cek.ratelimit')->group(function () {
        Route::get('/analisis/kode', [KodeController::class, 'form'])->name('analisis.kode');
        Route::post('/analisis/kode', [KodeController::class, 'proses']);

        Route::get('/analisis/url', [UrlController::class, 'form'])->name('analisis.url');
        Route::post('/analisis/url', [UrlController::class, 'proses']);
        Route::get('/analisis/url/generate-verifikasi', [UrlController::class, 'generateVerifikasi'])->name('analisis.url.generate-verifikasi');

        Route::get('/analisis/zip', [ZipController::class, 'form'])->name('analisis.zip');
        Route::post('/analisis/zip', [ZipController::class, 'proses']);

        Route::get('/analisis/log', [LogController::class, 'form'])->name('analisis.log');
        Route::post('/analisis/log', [LogController::class, 'proses']);
    });

    // Domain verification routes (no rate limit needed)
    Route::get('/analisis/url/verifikasi/{domainVerification}', [UrlController::class, 'formVerifikasi'])->name('analisis.url.verifikasi');
    Route::post('/analisis/url/verifikasi/{domainVerification}', [UrlController::class, 'prosesVerifikasi'])->name('analisis.url.proses-verifikasi');
    Route::get('/analisis/url/download-verifikasi/{domainVerification}', [UrlController::class, 'downloadVerifikasi'])->name('analisis.url.download-verifikasi');

    // Route yang memerlukan verifikasi kepemilikan scan (UUID)
    Route::middleware('scan.owner')->group(function () {
        Route::get('/analisis/status/{scan}', [LaporanController::class, 'status'])->name('analisis.status');
        Route::get('/analisis/hasil/{scan}', [LaporanController::class, 'hasil'])->name('analisis.hasil');
        Route::get('/laporan/{scan}', [LaporanController::class, 'detail'])->name('laporan.detail');
        Route::get('/laporan/{scan}/pdf', [LaporanController::class, 'exportPDF'])->name('laporan.pdf');
        Route::post('/analisis/url/ai-analisis/{scan}', [UrlController::class, 'aiAnalisis'])->name('analisis.url.ai-analisis');
    });

    // Laporan index (tidak perlu scan.owner karena sudah filter by user)
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');

    // Chat AI (semua user)
    Route::get('/chat-ai', [\App\Http\Controllers\ChatController::class, 'indexPublic'])->name('chat-ai');
    Route::post('/chat-ai', [\App\Http\Controllers\ChatController::class, 'chat'])->name('chat-ai.send');

    // Edukasi
    Route::get('/edukasi', [EdukasiController::class, 'index'])->name('edukasi.index');
    Route::get('/edukasi/ensiklopedia', [EdukasiController::class, 'ensiklopedia']);
    Route::get('/edukasi/tantangan', [EdukasiController::class, 'tantangan']);
    Route::post('/edukasi/tantangan/{tantangan}/jawab', [EdukasiController::class, 'jawab']);

    // Admin
    Route::middleware('cek.admin')->prefix('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/kesehatan-sistem', [AdminController::class, 'kesehatanSistem'])->name('admin.kesehatan-sistem');
        Route::get('/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit-log');
        
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

        // Manajemen Pengguna (Approval)
        Route::get('/pengguna', [\App\Http\Controllers\AdminUserController::class, 'index'])->name('admin.users.index');
        Route::patch('/pengguna/{user}/setujui', [\App\Http\Controllers\AdminUserController::class, 'verify'])->name('admin.users.verify');
        Route::patch('/pengguna/{user}/batalkan', [\App\Http\Controllers\AdminUserController::class, 'unverify'])->name('admin.users.unverify');
        Route::post('/pengguna/{user}/kirim-notifikasi', [\App\Http\Controllers\AdminUserController::class, 'resendNotification'])->name('admin.users.resendNotification');
        Route::delete('/pengguna/{user}', [\App\Http\Controllers\AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/chat-test', [\App\Http\Controllers\ChatController::class, 'index'])->name('admin.chat-test');
        Route::post('/chat-test', [\App\Http\Controllers\ChatController::class, 'chat']);
        Route::get('/rag-debug', [\App\Http\Controllers\ChatController::class, 'ragDebug'])->name('admin.rag-debug');
    });

    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
});
