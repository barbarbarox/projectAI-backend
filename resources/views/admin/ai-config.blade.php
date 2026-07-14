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
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-[#00d4ff] to-[#7c3aed] text-white text-sm font-semibold hover:shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap">
                        <span x-show="!detecting">🔍 Deteksi Provider</span>
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

                        <button type="submit" class="px-6 py-3 rounded-xl bg-green-500 text-white text-sm font-semibold hover:bg-green-600 transition-colors">
                            💾 Simpan Konfigurasi
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
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $config->detected_provider === 'google' ? 'bg-blue-500/10' : ($config->detected_provider === 'openai' ? 'bg-green-500/10' : ($config->detected_provider === 'anthropic' ? 'bg-orange-500/10' : ($config->detected_provider === 'groq' ? 'bg-red-500/10' : 'bg-[#7c3aed]/10'))) }}">
                            <span class="text-lg">{{ $config->detected_provider === 'google' ? '🔷' : ($config->detected_provider === 'openai' ? '🟢' : ($config->detected_provider === 'anthropic' ? '🟠' : ($config->detected_provider === 'groq' ? '⚡' : '🔮'))) }}</span>
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
                        <button @click="editing = !editing" class="px-3 py-1.5 rounded-lg bg-[#1e2d4a] text-xs text-[#94a3b8] hover:bg-[#2a3a5c] transition-colors">✏️ Edit</button>
                        <form method="POST" action="/admin/ai-config/{{ $config->id }}/hapus" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Hapus konfigurasi ini?')" class="px-3 py-1.5 rounded-lg bg-red-500/10 text-xs text-red-400 hover:bg-red-500/20 transition-colors">🗑️</button>
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
    <div class="rounded-xl bg-yellow-500/5 border border-yellow-500/20 p-4">
        <p class="text-xs text-yellow-400/80">💡 Jika tidak ada konfigurasi aktif, sistem akan menggunakan API key default dari .env ({{ env('GEMINI_MODEL', 'gemini-1.5-pro') }}).</p>
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
