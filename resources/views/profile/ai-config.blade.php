@extends('layouts.app')
@section('title', 'Pengaturan Model AI — RedSim')
@section('page_feature', 'model-ai')

@section('content')
<style>
.ai-page { font-family: 'Inter', sans-serif; }

/* PAGE HEADING */
.ai-heading { margin-bottom: 28px; display: flex; align-items: flex-start; justify-content: space-between; }
.ai-heading h1 { font-weight: 600; color: #f5f3f4; font-size: 30px; line-height: 43.5px; }
.ai-heading .subtitle { color: #aaa2a8; font-size: 14px; line-height: 20.3px; margin-top: 6px; }
.ai-heading .breadcrumb { font-family: 'Roboto Mono', monospace; color: #91868d; font-size: 12px; line-height: 17.4px; white-space: nowrap; padding-top: 10px; }

/* ACTIVE MODEL CARD */
.active-model-card {
  position: relative; width: 100%; margin-bottom: 20px;
  background-color: #191316; border-radius: 14px;
  border: 1px solid #47252d; overflow: hidden; padding: 24px 28px;
}
.active-model-card .card-accent {
  position: absolute; top: 24px; left: 0;
  width: 3px; height: 96px; background-color: #f04452; border-radius: 1px;
}
.active-model-card .model-label {
  font-family: 'Roboto Mono', monospace; color: #c3a1ae; font-size: 10px;
  line-height: 14.5px; margin-bottom: 4px; letter-spacing: 0.08em;
}
.active-model-card .connection-badge {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 4px 12px; background-color: #332027; border-radius: 13px;
  font-size: 11px; font-weight: 500; color: #ffabb2;
}
.active-model-card .connection-dot {
  width: 5px; height: 5px; background-color: #f04452; border-radius: 50%;
}
.active-model-card .model-top-row {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.active-model-card .model-info-row {
  display: flex; align-items: center; gap: 24px; margin-bottom: 20px;
}
.active-model-card .gemini-icon {
  width: 48px; height: 48px; background-color: #342029;
  border-radius: 12px; border: 1px solid #59313f;
  display: flex; align-items: center; justify-content: center; shrink: 0;
}
.active-model-card .model-name { font-weight: 600; color: #f5f3f4; font-size: 20px; line-height: 29px; }
.active-model-card .model-id { font-family: 'Roboto Mono', monospace; color: #aaa2a8; font-size: 12px; line-height: 17.4px; }
.active-model-card .oauth-badge-sm {
  display: inline-block; padding: 3px 10px;
  background-color: #2d2229; border-radius: 6px;
  font-size: 10px; font-weight: 500; color: #dab5c4;
}
.active-model-card .usage-divider { width: 1px; height: 58px; background-color: #412b35; }
.active-model-card .usage-stat .stat-num { font-weight: 600; color: #f5f3f4; font-size: 23px; line-height: 33.4px; }
.active-model-card .usage-stat .stat-label { color: #aaa2a8; font-size: 11px; line-height: 16px; }
.active-model-card .usage-time .time-val { font-weight: 500; color: #f5f3f4; font-size: 14px; line-height: 20.3px; }
.active-model-card .usage-time .time-label { color: #aaa2a8; font-size: 11px; line-height: 16px; }
.active-model-card .delete-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  width: 160px; height: 36px; background-color: #261d23;
  border-radius: 7px; border: 1px solid #513440; cursor: pointer;
  font-weight: 600; color: #f0b8c4; font-size: 13px; transition: background 0.2s;
}
.active-model-card .delete-btn:hover { background-color: #3a2530; }
.active-model-card .model-divider { height: 1px; background-color: #3d2933; margin: 0 0 16px; }
.active-model-card .change-model-label { font-weight: 500; color: #aaa2a8; font-size: 12px; margin-bottom: 10px; }
.active-model-card .model-selector-row { display: flex; gap: 10px; align-items: center; }
.active-model-card .model-select {
  flex: 1; height: 44px; background-color: #100e11;
  border-radius: 8px; border: 1px solid #44353e;
  color: #f5f3f4; font-size: 14px; padding: 0 16px;
  font-family: 'Inter', sans-serif; outline: none; cursor: pointer;
}
.active-model-card .model-select:focus { border-color: #a24758; }
.active-model-card .save-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 144px; height: 44px; background-color: #e33445;
  border-radius: 7px; border: none; cursor: pointer;
  font-weight: 600; color: #fff; font-size: 13px; transition: background 0.2s;
  flex-shrink: 0;
}
.active-model-card .save-btn:hover { background-color: #c0202f; }
.active-model-card .refresh-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 44px; height: 44px; background-color: #261d23;
  border-radius: 7px; border: 1px solid #513440; cursor: pointer;
  color: #f0b8c4; transition: background 0.2s; flex-shrink: 0;
}
.active-model-card .refresh-btn:hover { background-color: #3a2530; }
.active-model-card .test-row {
  display: flex; align-items: center; justify-content: space-between; margin-top: 16px;
}
.active-model-card .test-label { font-weight: 500; color: #f5f3f4; font-size: 12px; }
.active-model-card .test-desc { color: #aaa2a8; font-size: 11px; line-height: 16px; margin-top: 2px; }
.active-model-card .check-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  width: 172px; height: 32px; background-color: #261d23;
  border-radius: 7px; border: 1px solid #513440; cursor: pointer;
  font-weight: 600; color: #f0b8c4; font-size: 13px; flex-shrink: 0;
  transition: background 0.2s;
}
.active-model-card .check-btn:hover { background-color: #3a2530; }

/* TWO COLUMN ROW */
.two-col-row { display: flex; gap: 20px; margin-bottom: 20px; }

/* API KEY CARD */
.api-key-card {
  flex: 0 0 808px; position: relative;
  background-color: #151316; border-radius: 14px;
  border: 1px solid #30292e; padding: 28px;
}
.card-icon-box {
  width: 34px; height: 34px; background-color: #2b1a20;
  border-radius: 8px; display: flex; align-items: center; justify-content: center;
}
.card-title-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.card-title { font-weight: 600; color: #f5f3f4; font-size: 18px; line-height: 26.1px; }
.card-desc { color: #aaa2a8; font-size: 13px; line-height: 18.9px; margin-bottom: 20px; }
.field-label-sm { font-weight: 500; color: #aaa2a8; font-size: 12px; line-height: 17.4px; margin-bottom: 8px; }
.api-input-row { display: flex; gap: 10px; align-items: center; margin-bottom: 16px; }
.api-key-input {
  flex: 1; height: 48px; background-color: #100e11;
  border-radius: 8px; border: 1px solid #44353e;
  color: #f5f3f4; font-family: 'Roboto Mono', monospace; font-size: 12px;
  padding: 0 16px; outline: none;
}
.api-key-input:focus { border-color: #a24758; }
.api-key-input::placeholder { color: #91868d; }
.detect-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 184px; height: 48px; background-color: #e33445;
  border-radius: 7px; border: none; cursor: pointer;
  font-weight: 600; color: #fff; font-size: 13px; flex-shrink: 0;
  transition: background 0.2s;
}
.detect-btn:hover { background-color: #c0202f; }
.provider-label { font-family: 'Roboto Mono', monospace; color: #91868d; font-size: 10px; margin-bottom: 8px; letter-spacing: 0.08em; }
.provider-badges { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
.provider-badge {
  display: inline-flex; align-items: center; padding: 4px 12px;
  background-color: #201b1f; border-radius: 6px; border: 1px solid #3a2f37;
  font-weight: 500; color: #aaa2a8; font-size: 11px; line-height: 16px;
}
.security-warn { color: #91868d; font-size: 10px; line-height: 14.5px; }

/* GOOGLE OAUTH CARD */
.oauth-card {
  flex: 1; position: relative;
  background-color: #151316; border-radius: 14px;
  border: 1px solid #30292e; padding: 28px;
}
.google-signin-btn {
  display: flex; align-items: center; gap: 12px;
  width: 100%; height: 56px; background-color: #221b20;
  border-radius: 8px; border: 1px solid #513440;
  cursor: pointer; text-decoration: none; margin-bottom: 16px;
  transition: border-color 0.2s; padding: 0 16px;
}
.google-signin-btn:hover { border-color: #a24758; }
.google-g { font-weight: 700; color: #f5f3f4; font-size: 19px; }
.google-signin-text { font-weight: 600; color: #f5f3f4; font-size: 14px; }
.oauth-models-text { color: #aaa2a8; font-size: 11px; line-height: 16px; margin-bottom: 2px; }
.oauth-divider { height: 1px; background-color: #30292e; margin: 12px 0; }
.oauth-default-text { font-weight: 500; color: #d0b5c0; font-size: 11px; line-height: 16px; }
.oauth-default-subtext { color: #91868d; font-size: 10px; line-height: 14.5px; }

/* INFO CARD */
.info-card {
  width: 100%; background-color: #151316;
  border-radius: 14px; border: 1px solid #30292e;
  padding: 24px 28px; margin-bottom: 20px;
}
.info-card .info-header { display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
.info-card .info-title { font-weight: 600; color: #f5f3f4; font-size: 15px; line-height: 21.8px; }
.info-grid { display: grid; grid-template-columns: 1fr 1px 1fr; gap: 0 28px; }
.info-col { display: flex; flex-direction: column; gap: 20px; }
.info-divider { background-color: #30292e; height: 100%; width: 1px; }
.info-item .info-item-title { font-weight: 500; color: #f5f3f4; font-size: 12px; line-height: 17.4px; margin-bottom: 4px; }
.info-item .info-item-title.red { color: #f3a4b3; }
.info-item .info-item-desc { color: #aaa2a8; font-size: 11px; line-height: 16px; }

/* FOOTER */
.ai-footer { margin-top: 20px; padding-top: 16px; border-top: 1px solid #30292e; display: flex; align-items: center; justify-content: space-between; }
.ai-footer .footer-left { display: flex; align-items: center; gap: 8px; }
.ai-footer .footer-brand { font-weight: 600; color: #aaa2a8; font-size: 10px; }
.ai-footer .footer-desc { color: #91868d; font-size: 10px; }
.ai-footer .footer-right { color: #91868d; font-size: 10px; }

/* Detection result */
.detection-result { margin-top: 12px; }
.detection-loading { display: flex; align-items: center; gap: 10px; padding: 12px; }
.spinner { width: 16px; height: 16px; border: 2px solid #ef4444; border-top-color: transparent; border-radius: 50%; animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.detection-success-box { background: rgba(74,222,128,0.05); border: 1px solid rgba(74,222,128,0.2); border-radius: 8px; padding: 12px; margin-bottom: 12px; }
.detection-error-box { background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.2); border-radius: 8px; padding: 12px; }
</style>

<div class="ai-page">

{{-- Flash Messages --}}
@if(session('success'))
<div style="margin-bottom:16px;padding:10px 14px;background:rgba(74,222,128,0.08);border:1px solid rgba(74,222,128,0.2);border-radius:8px;color:#4ade80;font-size:13px;display:flex;align-items:center;gap:8px;">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div style="margin-bottom:16px;padding:10px 14px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);border-radius:8px;color:#f87171;font-size:13px;">
    {{ session('error') }}
</div>
@endif

{{-- PAGE HEADING --}}
<div class="ai-heading">
    <div>
        <h1>Pengaturan model AI</h1>
        <p class="subtitle">Hubungkan model AI Anda sendiri untuk analisis keamanan yang lebih sesuai.</p>
    </div>
    <span class="breadcrumb">SETTINGS / AI MODEL</span>
</div>

{{-- ACTIVE MODEL CARD --}}
<div class="active-model-card">
    <div class="card-accent"></div>

    @if($config)
    {{-- User has config --}}
    <div class="model-top-row">
        <div>
            <div class="model-label">MODEL AI SAAT INI</div>
        </div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="connection-badge">
                <div class="connection-dot"></div>
                Terhubung
            </div>
            <form method="POST" action="{{ route('profile.ai.destroy') }}" onsubmit="return confirm('Yakin ingin menghapus konfigurasi AI Anda?')">
                @csrf @method('DELETE')
                <button type="submit" class="delete-btn">Hapus konfigurasi</button>
            </form>
        </div>
    </div>

    <div class="model-info-row">
        <div class="gemini-icon">
            <svg width="24" height="24" fill="none" stroke="#f04452" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <div class="model-name">{{ $config->provider_label }}</div>
            <div class="model-id">{{ $config->selected_model }}</div>
        </div>
        <div class="oauth-badge-sm">{{ $config->method_label }}</div>
        <div class="usage-divider"></div>
        <div class="usage-stat">
            <div class="stat-num">{{ $config->total_requests }}</div>
            <div class="stat-label">request</div>
        </div>
        @if($config->last_used_at)
        <div class="usage-time">
            <div class="time-val">{{ $config->last_used_at->diffForHumans() }}</div>
            <div class="time-label">Terakhir digunakan</div>
        </div>
        @endif
    </div>

    <div class="model-divider"></div>
    <div class="change-model-label">Ganti model</div>

    @if(!empty($config->available_models) && count($config->available_models) > 1)
    <div class="model-selector-row">
        <select id="modelSelector" class="model-select">
            @foreach($config->available_models as $m)
            <option value="{{ $m['id'] }}" {{ $config->selected_model === $m['id'] ? 'selected' : '' }}>{{ $m['name'] ?? $m['id'] }}</option>
            @endforeach
        </select>
        <button onclick="updateModel()" class="save-btn">Simpan</button>
        <button onclick="refreshModelList()" id="btnRefreshModels" class="refresh-btn" title="Refresh daftar model">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        </button>
    </div>
    @endif

    @if(!empty($config->available_models) && count($config->available_models) > 0)
    <div class="test-row">
        <div>
            <div class="test-label">Cek model aktif</div>
            <div class="test-desc">Tes koneksi untuk mengetahui model yang dapat digunakan saat ini.</div>
        </div>
        <button onclick="testAllModels()" id="btnTestModels" class="check-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Cek semua model
        </button>
    </div>

    <div id="testProgress" class="hidden" style="margin-top:12px;">
        <div class="detection-loading">
            <div class="spinner"></div>
            <span style="font-size:12px;color:#aaa2a8;" id="testProgressText">Menguji semua model...</span>
        </div>
    </div>
    <div id="testResults" class="hidden" style="margin-top:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <span style="font-size:12px;font-weight:600;color:#aaa2a8;" id="testSummary"></span>
            <button onclick="removeFailedModels()" id="btnRemoveFailed" class="hidden" style="padding:4px 10px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);border-radius:6px;color:#f87171;font-size:11px;cursor:pointer;">Hapus Model Error</button>
        </div>
        <div id="testResultsList" style="display:flex;flex-direction:column;gap:6px;max-height:200px;overflow-y:auto;"></div>
    </div>
    @endif

    @else
    {{-- No config - using admin default --}}
    <div class="model-top-row">
        <div class="model-label">MODEL AI SAAT INI</div>
    </div>
    <div class="model-info-row">
        <div class="gemini-icon">
            <svg width="24" height="24" fill="none" stroke="#555" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        </div>
        <div>
            <div class="model-name" style="color:#888;">Model Default Sistem</div>
            <div class="model-id">Disediakan oleh admin</div>
        </div>
    </div>
    <p style="color:#91868d;font-size:12px;margin-top:8px;">Hubungkan model AI pribadi Anda menggunakan API Key atau Google OAuth di bawah.</p>
    @endif
</div>

{{-- TWO COLUMN: API KEY + GOOGLE OAUTH --}}
<div class="two-col-row">

    {{-- API KEY CARD --}}
    <div class="api-key-card">
        <div class="card-title-row">
            <div class="card-icon-box">
                <svg width="18" height="18" fill="none" stroke="#f04452" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div class="card-title">Hubungkan via API Key</div>
        </div>
        <p class="card-desc">Masukkan API Key dari provider pilihan Anda. RedSim akan mendeteksi<br>provider dan menampilkan model yang tersedia secara otomatis.</p>

        <div class="field-label-sm">API Key</div>
        <div class="api-input-row">
            <input type="password" id="apiKeyInput" class="api-key-input" placeholder="sk-… / AIza… / gsk_… / nvapi-…">
            <button onclick="detectProvider()" id="btnDetect" class="detect-btn">Deteksi provider</button>
        </div>

        <div class="provider-label">PROVIDER YANG DIDUKUNG</div>
        <div class="provider-badges">
            <span class="provider-badge">Google AI Studio</span>
            <span class="provider-badge">OpenAI</span>
            <span class="provider-badge">Anthropic</span>
            <span class="provider-badge">NVIDIA NIM</span>
            <span class="provider-badge">Groq</span>
            <span class="provider-badge">OpenRouter</span>
        </div>
        <p class="security-warn">Jangan bagikan API Key Anda kepada siapa pun.</p>

        {{-- Detection Result --}}
        <div id="detectionResult" class="detection-result hidden">
            <div id="detectionLoading" class="detection-loading hidden">
                <div class="spinner"></div>
                <span style="font-size:12px;color:#aaa2a8;">Mendeteksi provider...</span>
            </div>
            <div id="detectionSuccess" class="hidden">
                <div class="detection-success-box">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                        <svg width="16" height="16" fill="none" stroke="#4ade80" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span style="font-size:13px;font-weight:600;color:#4ade80;" id="detectedProviderLabel"></span>
                    </div>
                    <p style="font-size:11px;color:#aaa2a8;" id="detectedModelCount"></p>
                </div>
                <form method="POST" action="{{ route('profile.ai.store') }}">
                    @csrf
                    <input type="hidden" name="api_key" id="formApiKey">
                    <input type="hidden" name="provider" id="formProvider">
                    <input type="hidden" name="available_models" id="formAvailableModels">
                    <div style="margin-bottom:10px;">
                        <div class="field-label-sm">Pilih Model</div>
                        <select name="selected_model" id="modelSelect" class="model-select" style="width:100%;"></select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <div class="field-label-sm">Label (opsional)</div>
                        <input type="text" name="label" placeholder="e.g. AI Pribadi" class="api-key-input" style="width:100%;font-family:'Inter',sans-serif;">
                    </div>
                    <button type="submit" class="detect-btn" style="width:100%;">Simpan Konfigurasi</button>
                </form>
            </div>
            <div id="detectionError" class="detection-error-box hidden">
                <div style="display:flex;align-items:center;gap:8px;">
                    <svg width="16" height="16" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span style="font-size:13px;color:#f87171;" id="detectionErrorMsg"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- GOOGLE OAUTH CARD --}}
    <div class="oauth-card">
        <div class="card-title-row">
            <div class="card-icon-box">
                <svg width="18" height="18" fill="none" stroke="#f04452" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            </div>
            <div class="card-title">Hubungkan via Google OAuth</div>
        </div>
        <p class="card-desc">Akses model Gemini dengan akun Google Anda.<br>Tersedia Free Tier tanpa biaya.</p>

        <a href="{{ route('profile.ai.oauth.google') }}" class="google-signin-btn">
            <span class="google-g">G</span>
            <span class="google-signin-text">Login dengan Google</span>
            <svg style="margin-left:auto;" width="18" height="18" fill="none" stroke="#91868d" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>

        <p class="oauth-models-text">Gemini 3.5 Flash Lite · Gemini 2.5 Flash</p>
        <p class="oauth-models-text">Gemini 2.0 Flash · dan lainnya</p>
        <div class="oauth-divider"></div>
        <p class="oauth-default-text">Model default: Gemini 3.5 Flash Lite.</p>
        <p class="oauth-default-subtext">Anda dapat menggantinya setelah terhubung.</p>
    </div>
</div>

{{-- INFO CARD --}}
<div class="info-card">
    <div class="info-header">
        <div class="card-icon-box">
            <svg width="18" height="18" fill="none" stroke="#f04452" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="info-title">Informasi konfigurasi</div>
    </div>
    <div class="info-grid">
        <div class="info-col">
            <div class="info-item">
                <div class="info-item-title">Model bawaan</div>
                <p class="info-item-desc">Tanpa model AI pribadi, sistem menggunakan model default dari admin.</p>
            </div>
            <div class="info-item">
                <div class="info-item-title red">Satu konfigurasi aktif</div>
                <p class="info-item-desc">Konfigurasi baru akan menggantikan konfigurasi yang lama.</p>
            </div>
        </div>
        <div class="info-divider"></div>
        <div class="info-col">
            <div class="info-item">
                <div class="info-item-title">Keamanan API Key</div>
                <p class="info-item-desc">API Key disimpan terenkripsi di database dan tidak ditampilkan kembali.</p>
            </div>
            <div class="info-item">
                <div class="info-item-title">Akses Google OAuth</div>
                <p class="info-item-desc">Pastikan akun Google memiliki akses ke Generative Language API.</p>
            </div>
        </div>
    </div>
</div>

{{-- FOOTER --}}
<div class="ai-footer">
    <div class="footer-left">
        <span class="footer-brand">REDSIM</span>
        <span class="footer-desc">Analisis keamanan. Simulasi serangan. Perbaikan.</span>
    </div>
    <span class="footer-right">Pengaturan model AI</span>
</div>

</div>
@endsection

@push('scripts')
<script>
function detectProvider() {
    const apiKey = document.getElementById('apiKeyInput').value.trim();
    if (!apiKey || apiKey.length < 10) { alert('Masukkan API key yang valid (minimal 10 karakter).'); return; }
    const resultDiv = document.getElementById('detectionResult');
    const loadingDiv = document.getElementById('detectionLoading');
    const successDiv = document.getElementById('detectionSuccess');
    const errorDiv = document.getElementById('detectionError');
    resultDiv.classList.remove('hidden');
    loadingDiv.classList.remove('hidden');
    successDiv.classList.add('hidden');
    errorDiv.classList.add('hidden');
    document.getElementById('btnDetect').disabled = true;
    fetch('{{ route("profile.ai.detect") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ api_key: apiKey })
    })
    .then(res => res.json())
    .then(data => {
        loadingDiv.classList.add('hidden');
        document.getElementById('btnDetect').disabled = false;
        if (data.detected) {
            successDiv.classList.remove('hidden');
            document.getElementById('detectedProviderLabel').textContent = 'Terdeteksi: ' + data.label;
            document.getElementById('detectedModelCount').textContent = data.models.length + ' model tersedia';
            document.getElementById('formApiKey').value = apiKey;
            document.getElementById('formProvider').value = data.provider;
            document.getElementById('formAvailableModels').value = JSON.stringify(data.models);
            const select = document.getElementById('modelSelect');
            select.innerHTML = '';
            data.models.forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id; opt.textContent = m.name || m.id;
                select.appendChild(opt);
            });
        } else {
            errorDiv.classList.remove('hidden');
            document.getElementById('detectionErrorMsg').textContent = data.error || 'API key tidak dapat dikenali.';
        }
    })
    .catch(err => {
        loadingDiv.classList.add('hidden');
        errorDiv.classList.remove('hidden');
        document.getElementById('detectionErrorMsg').textContent = 'Gagal menghubungi server: ' + err.message;
        document.getElementById('btnDetect').disabled = false;
    });
}

function updateModel() {
    const model = document.getElementById('modelSelector').value;
    fetch('{{ route("profile.ai.update-model") }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ selected_model: model })
    }).then(res => res.json()).then(data => {
        if (data.success) location.reload();
        else alert(data.error || 'Gagal memperbarui model.');
    });
}

function refreshModelList() {
    const btn = document.getElementById('btnRefreshModels');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner" style="border-color:#f0b8c4;border-top-color:transparent;"></div>';
    fetch('{{ route("profile.ai.refresh-models") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(res => res.json()).then(data => {
        if (data.success) { alert('Daftar model berhasil diperbarui! (' + data.count + ' model)'); location.reload(); }
        else { alert(data.error || 'Gagal refresh model.'); btn.disabled = false; btn.innerHTML = '<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'; }
    }).catch(err => { alert('Gagal: ' + err.message); btn.disabled = false; });
}

let failedModelIds = [];
function testAllModels() {
    const btn = document.getElementById('btnTestModels');
    const progress = document.getElementById('testProgress');
    const results = document.getElementById('testResults');
    const resultsList = document.getElementById('testResultsList');
    const summary = document.getElementById('testSummary');
    const btnRemove = document.getElementById('btnRemoveFailed');
    btn.disabled = true; btn.textContent = 'Menguji...';
    btnRemove.classList.add('hidden');
    progress.classList.remove('hidden'); results.classList.add('hidden');
    resultsList.innerHTML = ''; failedModelIds = [];
    fetch('{{ route("profile.ai.test-models") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(res => res.json()).then(data => {
        progress.classList.add('hidden');
        btn.disabled = false; btn.textContent = 'Cek semua model';
        if (data.error) { summary.textContent = '❌ ' + data.error; results.classList.remove('hidden'); return; }
        summary.textContent = `✅ ${data.working}/${data.total} model aktif — ${data.provider}`;
        results.classList.remove('hidden');
        (data.results || []).sort((a,b) => a.status==='ok'?-1:1).forEach(r => {
            if (r.status !== 'ok') failedModelIds.push(r.id);
            const row = document.createElement('div');
            row.style.cssText = `display:flex;align-items:center;justify-content:space-between;padding:6px 10px;border-radius:6px;background:${r.status==='ok'?'rgba(74,222,128,0.05)':'rgba(239,68,68,0.05)'};border:1px solid ${r.status==='ok'?'rgba(74,222,128,0.15)':'rgba(239,68,68,0.15)'};`;
            row.innerHTML = `<div style="display:flex;align-items:center;gap:8px;min-width:0;"><span style="color:${r.status==='ok'?'#4ade80':'#f87171'};font-size:13px;">${r.status==='ok'?'✓':'✗'}</span><span style="font-size:11px;color:#f5f3f4;font-family:'Roboto Mono',monospace;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${r.id}</span></div><span style="font-size:10px;color:${r.status==='ok'?'#4ade80':'#f87171'};flex-shrink:0;margin-left:8px;">${r.message}</span>`;
            resultsList.appendChild(row);
        });
        if (failedModelIds.length > 0) btnRemove.classList.remove('hidden');
    }).catch(err => { progress.classList.add('hidden'); btn.disabled=false; btn.textContent='Cek semua model'; summary.textContent='❌ Gagal: '+err.message; results.classList.remove('hidden'); });
}

function removeFailedModels() {
    if (!failedModelIds.length) return;
    if (!confirm(`Hapus ${failedModelIds.length} model error?`)) return;
    const btn = document.getElementById('btnRemoveFailed');
    btn.disabled = true; btn.textContent = 'Menghapus...';
    fetch('{{ route("profile.ai.remove-models") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
        body: JSON.stringify({ model_ids: failedModelIds })
    }).then(res => res.json()).then(data => {
        if (data.success) { alert('Berhasil!'); location.reload(); }
        else { alert(data.error||'Gagal.'); btn.disabled=false; btn.textContent='Hapus Model Error'; }
    }).catch(err => { alert('Gagal: '+err.message); btn.disabled=false; btn.textContent='Hapus Model Error'; });
}
</script>
@endpush
