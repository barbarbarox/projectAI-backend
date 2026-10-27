@extends('layouts.app')
@section('title', 'Konfigurasi AI — Admin RedSim')
@section('header', 'Konfigurasi AI')
@section('subheader', 'Kelola API key dan pilih model AI yang digunakan')

@section('content')
<div class="space-y-6" x-data="aiConfigManager()">

    {{-- Add New Config --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] p-6">
        <h3 class="font-semibold mb-4">Tambah Konfigurasi AI Baru</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm text-[#94a3b8] mb-2">API Key</label>
                <div class="flex gap-3">
                    <input type="password" x-model="apiKey" placeholder="Masukkan API key..."
                           class="flex-1 px-4 py-3 rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] text-white placeholder-[#475569] focus:border-[#00d4ff] focus:outline-none text-sm">
                    <button @click="detectKey()" :disabled="detecting || !apiKey"
                            class="px-5 py-3 rounded-xl text-white text-sm font-semibold transition-all disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap flex items-center gap-2"
                            style="background:linear-gradient(135deg,#06b6d4,#7c3aed);">
                        <template x-if="!detecting">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                Deteksi Provider
                            </span>
                        </template>
                        <span x-show="detecting" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Mendeteksi...
                        </span>
                    </button>
                </div>
            </div>

            {{-- Detection Result --}}
            <template x-if="detected">
                <div class="rounded-xl bg-[#0a0e1a] border border-[#1e2d4a] p-5 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full bg-green-400 animate-pulse"></div>
                        <p class="text-sm font-medium text-green-400">Provider Terdeteksi: <span class="text-white" x-text="result.label"></span></p>
                    </div>

                    <form method="POST" action="/admin/ai-config" class="space-y-4">
                        @csrf
                        <input type="hidden" name="api_key" :value="apiKey">
                        <input type="hidden" name="provider" :value="result.provider">
                        <input type="hidden" name="detected_provider" :value="result.provider">
                        <input type="hidden" name="available_models" :value="JSON.stringify(result.models)">

                        <div>
                            <label class="block text-sm text-[#94a3b8] mb-2">Label / Nama Konfigurasi</label>
                            <input type="text" name="label" :value="result.label" required
                                   class="w-full px-4 py-3 rounded-xl bg-[#0f1629] border border-[#1e2d4a] text-white text-sm focus:border-[#00d4ff] focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-sm text-[#94a3b8] mb-2">Model AI (Ketik Manual atau Pilih)</label>
                            <input type="text" name="selected_model" required placeholder="Contoh: cohere/north-mini-code:free"
                                   class="w-full px-4 py-3 rounded-xl bg-[#0f1629] border border-[#1e2d4a] text-white text-sm focus:border-[#00d4ff] focus:outline-none" list="detected-models">
                            <datalist id="detected-models">
                                <template x-for="m in result.models" :key="m.id">
                                    <option :value="m.id" x-text="m.name + (m.description ? ' — ' + m.description.substring(0, 60) : '')"></option>
                                </template>
                            </datalist>
                        </div>

                        <div class="flex items-center gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" class="w-4 h-4 rounded bg-[#0a0e1a] border-[#1e2d4a] text-[#00d4ff]">
                                <span class="text-sm text-[#94a3b8]">Jadikan default</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white text-sm font-semibold transition-all"
                                style="background:linear-gradient(135deg,#06b6d4,#7c3aed);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Simpan Konfigurasi
                        </button>
                    </form>
                </div>
            </template>

            {{-- Error --}}
            <template x-if="error">
                <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4">
                    <p class="text-sm text-red-400" x-text="error"></p>
                </div>
            </template>
        </div>
    </div>

    {{-- Existing Configs --}}
    <div class="rounded-2xl bg-[#0f1629] border border-[#1e2d4a] overflow-hidden">
        <div class="px-6 py-4 border-b border-[#1e2d4a]">
            <h3 class="font-semibold">Konfigurasi Tersimpan</h3>
        </div>
        <div class="divide-y divide-[#1e2d4a]">
            @forelse($configs as $config)
            <div class="px-6 py-5" x-data="{ editing: false }">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center
                            {{ $config->detected_provider === 'google' ? 'bg-blue-500/10'
                                : ($config->detected_provider === 'openai' ? 'bg-green-500/10'
                                : ($config->detected_provider === 'anthropic' ? 'bg-orange-500/10'
                                : ($config->detected_provider === 'groq' ? 'bg-red-500/10' : 'bg-[#7c3aed]/10'))) }}">
                            @if($config->detected_provider === 'google')
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            @elseif($config->detected_provider === 'openai')
                                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @elseif($config->detected_provider === 'anthropic')
                                <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            @elseif($config->detected_provider === 'groq')
                                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            @else
                                <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-semibold">{{ $config->label }}</p>
                            <p class="text-xs text-[#64748b]">
                                {{ ucfirst($config->detected_provider) }} · {{ $config->selected_model }}
                                @if($config->is_default) · <span class="text-[#00d4ff]">Default</span> @endif
                                @if(!$config->is_active) · <span class="text-red-400">Nonaktif</span> @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="editing = !editing"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs text-[#94a3b8] transition-colors"
                                style="background:#1e2d4a;border:1px solid #2a3a5c;"
                                x-bind:class="editing ? 'text-cyan-400' : 'text-[#94a3b8]'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="/admin/ai-config/{{ $config->id }}/hapus" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    onclick="return confirm('Hapus konfigurasi ini?')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition-colors"
                                    style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);"
                                    title="Hapus">
                                <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
                <div x-show="editing" x-collapse class="mt-4 pt-4 border-t border-[#1e2d4a]">
                    <form method="POST" action="/admin/ai-config/{{ $config->id }}" class="flex flex-wrap gap-3 items-end">
                        @csrf @method('PATCH')
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs text-[#64748b] mb-1">Model (Bisa diketik manual)</label>
                            <input type="text" name="selected_model" value="{{ $config->selected_model }}" class="w-full px-3 py-2 rounded-lg bg-[#0a0e1a] border border-[#1e2d4a] text-sm text-white focus:outline-none" list="edit-models-{{ $config->id }}" placeholder="Contoh: cohere/north-mini-code:free">
                            <datalist id="edit-models-{{ $config->id }}">
                                @foreach($config->available_models ?? [] as $m)
                                <option value="{{ $m['id'] }}">{{ $m['name'] ?? $m['id'] }}</option>
                                @endforeach
                            </datalist>
                        </div>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ $config->is_active ? 'checked' : '' }} class="w-4 h-4 rounded"><span class="text-xs text-[#94a3b8]">Aktif</span></label>
                        <label class="flex items-center gap-2"><input type="checkbox" name="is_default" value="1" {{ $config->is_default ? 'checked' : '' }} class="w-4 h-4 rounded"><span class="text-xs text-[#94a3b8]">Default</span></label>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#00d4ff] text-xs text-white font-semibold hover:bg-[#00bfe6] transition-colors">Simpan</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center">
                <p class="text-[#64748b] text-sm">Belum ada konfigurasi. Masukkan API key di atas untuk memulai.</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Model Info dari .env --}}
    <div class="rounded-xl p-4 flex items-start gap-3" style="background:rgba(234,179,8,0.05);border:1px solid rgba(234,179,8,0.2);">
        <svg class="w-4 h-4 text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-xs text-yellow-400/80">Jika tidak ada konfigurasi aktif, sistem akan menggunakan API key default dari .env ({{ env('GEMINI_MODEL', 'gemini-1.5-pro') }}).</p>
    </div>
</div>

@push('scripts')
<script>
function aiConfigManager() {
    return {
        apiKey: '',
        detecting: false,
        detected: false,
        error: null,
        result: null,
        async detectKey() {
            this.detecting = true;
            this.detected = false;
            this.error = null;
            try {
                const res = await fetch('/admin/ai-config/detect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ api_key: this.apiKey })
                });
                const data = await res.json();
                if (data.detected) {
                    this.result = data;
                    this.detected = true;
                } else {
                    this.error = data.error || 'API key tidak dapat dikenali.';
                }
            } catch (e) {
                this.error = 'Gagal menghubungi server: ' + e.message;
            }
            this.detecting = false;
        }
    }
}
</script>
@endpush
@endsection
