{{--
    _leaderboard_content.blade.php
    Props: $leaders (Collection), $isAuth (bool)
    Design: Desain referensi papan peringkat — dark theme, palet merah RedSim
--}}

@php
    $totalPengguna = $leaders->count();
    $poinTertinggi = $leaders->isNotEmpty() ? $leaders->first()->total_poin : 0;

    // Pisahkan top 3 dan finalis 4-5
    $top3   = $leaders->take(3);
    $rank1  = $leaders->get(0);
    $rank2  = $leaders->get(1);
    $rank3  = $leaders->get(2);
    $others = $leaders->slice(3, 2)->values(); // rank 4 & 5
@endphp

{{-- ══════════════════════════════════════════════════════════════
     BLOK 1 — HEADER STAT + HALL OF FAME + PODIUM
     ══════════════════════════════════════════════════════════════ --}}
<div class="lb-block lb-block--hof {{ $isAuth ? '' : 'lb-block--guest' }}">

    {{-- ── Judul Hall of Fame ── --}}
    <div class="lb-hof-header">
        <div class="lb-hof-header__left">
            <svg class="lb-icon-trophy" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
            </svg>
            <span class="lb-hof-title">Top 5 &mdash; Hall of Fame</span>
        </div>
        <span class="lb-label-teknis">[ LEADERBOARD / TOP 05 ]</span>
    </div>

    {{-- ── Sorotan Peringkat: Pesan Komunitas + Podium ── --}}
    <div class="lb-sorotan">

        {{-- Pesan Komunitas --}}
        <div class="lb-pesan-komunitas">
            <div class="lb-pesan__label">BELAJAR. TAKLUKKAN. UNGGUL.</div>
            <p class="lb-pesan__utama">Setiap tantangan, satu langkah lebih unggul.</p>
            <p class="lb-pesan__sub">Apresiasi untuk pengguna terbaik dalam tantangan keamanan RedSim.</p>
            <div class="lb-aksen-teknis">
                <span class="lb-penanda lb-penanda--1"></span>
                <span class="lb-penanda lb-penanda--2"></span>
                <span class="lb-penanda lb-penanda--3"></span>
            </div>
        </div>

        {{-- Podium Tiga Besar --}}
        @if($totalPengguna >= 1)
        <div class="lb-podium">

            {{-- POSISI 2 (kiri) --}}
            @if($rank2)
            <div class="lb-podium__posisi">
                <div class="lb-profil-juara">
                    <div class="lb-avatar lb-avatar--normal">
                        @if(isset($rank2->avatar) && $rank2->avatar)
                            <img src="{{ $rank2->avatar }}" alt="{{ $rank2->name }}" class="lb-avatar__img" referrerpolicy="no-referrer">
                        @else
                            <span class="lb-avatar__inisial">{{ strtoupper(substr($rank2->name ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="lb-profil__nama">{{ strtoupper($rank2->nama_lengkap ?? $rank2->name) }}</div>
                    <div class="lb-profil__poin">
                        <span class="lb-profil__poin-nilai">{{ $rank2->total_poin }}</span>
                        <span class="lb-profil__poin-label">poin</span>
                    </div>
                </div>
                <div class="lb-pijakan lb-pijakan--2">
                    <span class="lb-pijakan__nomor">02</span>
                </div>
            </div>
            @endif

            {{-- POSISI 1 (tengah, juara) --}}
            @if($rank1)
            <div class="lb-podium__posisi lb-podium__posisi--center">
                <div class="lb-crown">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                </div>
                <div class="lb-profil-juara">
                    <div class="lb-avatar lb-avatar--champion">
                        @if(isset($rank1->avatar) && $rank1->avatar)
                            <img src="{{ $rank1->avatar }}" alt="{{ $rank1->name }}" class="lb-avatar__img" referrerpolicy="no-referrer">
                        @else
                            <span class="lb-avatar__inisial lb-avatar__inisial--champion">{{ strtoupper(substr($rank1->name ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="lb-profil__nama">{{ strtoupper($rank1->nama_lengkap ?? $rank1->name) }}</div>
                    <div class="lb-profil__poin">
                        <span class="lb-profil__poin-nilai lb-profil__poin-nilai--champion">{{ $rank1->total_poin }}</span>
                        <span class="lb-profil__poin-label">poin</span>
                    </div>
                </div>
                <div class="lb-pijakan lb-pijakan--1">
                    <span class="lb-pijakan__nomor lb-pijakan__nomor--1">01</span>
                </div>
            </div>
            @endif

            {{-- POSISI 3 (kanan) --}}
            @if($rank3)
            <div class="lb-podium__posisi">
                <div class="lb-profil-juara">
                    <div class="lb-avatar lb-avatar--normal">
                        @if(isset($rank3->avatar) && $rank3->avatar)
                            <img src="{{ $rank3->avatar }}" alt="{{ $rank3->name }}" class="lb-avatar__img" referrerpolicy="no-referrer">
                        @else
                            <span class="lb-avatar__inisial">{{ strtoupper(substr($rank3->name ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="lb-profil__nama">{{ strtoupper($rank3->nama_lengkap ?? $rank3->name) }}</div>
                    <div class="lb-profil__poin">
                        <span class="lb-profil__poin-nilai">{{ $rank3->total_poin }}</span>
                        <span class="lb-profil__poin-label">poin</span>
                    </div>
                </div>
                <div class="lb-pijakan lb-pijakan--3">
                    <span class="lb-pijakan__nomor">03</span>
                </div>
            </div>
            @endif

        </div>
        @else
        {{-- Kosong --}}
        <div class="lb-podium lb-podium--empty">
            <p class="lb-empty-text">Belum ada peserta. Jadilah yang pertama!</p>
        </div>
        @endif

    </div>{{-- /lb-sorotan --}}

    {{-- ── Finalis 4 & 5 ── --}}
    @if($others->count() > 0)
    <div class="lb-finalis">
        @foreach($others as $i => $finalis)
        <div class="lb-finalis__item">
            <span class="lb-finalis__rank">0{{ $i + 4 }}</span>
            <div class="lb-finalis__avatar">
                @if(isset($finalis->avatar) && $finalis->avatar)
                    <img src="{{ $finalis->avatar }}" alt="{{ $finalis->name }}" class="lb-avatar__img" referrerpolicy="no-referrer">
                @else
                    <span class="lb-finalis__inisial">{{ strtoupper(substr($finalis->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <span class="lb-finalis__nama">{{ $finalis->nama_lengkap ?? $finalis->name }}</span>
            <span class="lb-finalis__poin">
                <span class="lb-finalis__poin-val">{{ $finalis->total_poin }}</span>
                <span class="lb-finalis__poin-lbl">poin</span>
            </span>
        </div>
        @endforeach
    </div>
    @endif

</div>{{-- /lb-block--hof --}}

{{-- ══════════════════════════════════════════════════════════════
     BLOK 2 — TABEL PERINGKAT LENGKAP
     ══════════════════════════════════════════════════════════════ --}}
<div class="lb-block lb-block--tabel {{ $isAuth ? '' : 'lb-block--guest' }}">

    {{-- ── Kontrol Header ── --}}
    <div class="lb-kontrol">
        <div class="lb-kontrol__kiri">
            <span class="lb-kontrol__judul">Peringkat Lengkap</span>
            <span class="lb-badge-count">{{ $totalPengguna }} pengguna</span>
        </div>
        <div class="lb-kontrol__kanan">
            <div class="lb-search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <span class="lb-search__placeholder">Cari pengguna...</span>
            </div>
            <div class="lb-sort-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <span>Poin tertinggi</span>
            </div>
        </div>
    </div>

    {{-- ── Kepala Tabel ── --}}
    <div class="lb-thead">
        <div class="lb-thead__col lb-thead__col--rank">PERINGKAT</div>
        <div class="lb-thead__col lb-thead__col--user">PENGGUNA</div>
        <div class="lb-thead__col lb-thead__col--role">PERAN</div>
        <div class="lb-thead__col lb-thead__col--poin">
            <span>TOTAL POIN</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>

    {{-- ── Baris Tabel ── --}}
    @forelse($leaders as $idx => $leader)
    @php
        $isCurrentUser = auth()->check() && $leader->id === auth()->id();
        $isFirst       = $idx === 0;
        $isAdmin       = $leader->role === 'admin';
    @endphp
    <div class="lb-baris {{ $isFirst ? 'lb-baris--first' : 'lb-baris--normal' }} {{ $isCurrentUser ? 'lb-baris--me' : '' }}">

        {{-- Kolom: Nomor Peringkat --}}
        <div class="lb-baris__rank">
            <span class="lb-rank-num {{ $isFirst ? 'lb-rank-num--first' : '' }}">{{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</span>
            @if($isFirst)
            <svg class="lb-rank-crown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/>
            </svg>
            @endif
        </div>

        {{-- Kolom: Identitas Pengguna --}}
        <div class="lb-baris__user">
            <div class="lb-baris__avatar {{ $isFirst ? 'lb-baris__avatar--first' : '' }}">
                @if(isset($leader->avatar) && $leader->avatar)
                    <img src="{{ $leader->avatar }}" alt="{{ $leader->name }}" class="lb-avatar__img" referrerpolicy="no-referrer">
                @else
                    <span class="lb-baris__inisial {{ $isFirst ? 'lb-baris__inisial--first' : '' }}">{{ strtoupper(substr($leader->name ?? 'U', 0, 1)) }}</span>
                @endif
            </div>
            <span class="lb-baris__nama {{ $isFirst ? 'lb-baris__nama--first' : '' }}">{{ $leader->nama_lengkap ?? $leader->name }}</span>
            @if($isFirst)
                <span class="lb-lencana-pemimpin">Peringkat teratas</span>
            @endif
            @if($isCurrentUser)
                <span class="lb-lencana-saya">Anda</span>
            @endif
        </div>

        {{-- Kolom: Peran --}}
        <div class="lb-baris__role">
            @if($isAdmin)
                <span class="lb-lencana-admin">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Admin
                </span>
            @else
                <span class="lb-tanpa-peran">&mdash;</span>
            @endif
        </div>

        {{-- Kolom: Total Poin --}}
        <div class="lb-baris__poin">
            <span class="lb-poin-val {{ $isFirst ? 'lb-poin-val--first' : '' }}">{{ $leader->total_poin }}</span>
            <span class="lb-poin-lbl">poin</span>
        </div>
    </div>
    @empty
    <div class="lb-empty-row">
        <span>Belum ada data peringkat.</span>
    </div>
    @endforelse

    {{-- ── Navigasi Tabel ── --}}
    <div class="lb-nav-tabel">
        <p class="lb-nav-tabel__info">
            <span class="lb-nav-tabel__muted">Menampilkan </span>
            <span class="lb-nav-tabel__accent">1&ndash;{{ $totalPengguna }}</span>
            <span class="lb-nav-tabel__muted"> dari </span>
            <span class="lb-nav-tabel__accent">{{ $totalPengguna }}</span>
            <span class="lb-nav-tabel__muted"> pengguna</span>
        </p>
        <div class="lb-nav-tabel__pages">
            <div class="lb-page-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </div>
            <div class="lb-page-aktif"><span class="lb-page-num">1</span></div>
            <div class="lb-page-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </div>
        </div>
    </div>

</div>{{-- /lb-block--tabel --}}

{{-- ══════════════════════════════════════════════════════════════
     CTA (hanya untuk auth layout)
     ══════════════════════════════════════════════════════════════ --}}
@if($isAuth)
<div class="lb-cta-auth">
    <a href="/edukasi/tantangan" class="lb-cta-auth__btn">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        Ikuti Tantangan Sekarang
    </a>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     STYLES — Desain Papan Peringkat (Red Theme)
     ══════════════════════════════════════════════════════════════ --}}
<style>
/* ── Variables ── */
:root {
    --lb-red:       #f04452;
    --lb-red-dark:  #dc2626;
    --lb-red-dim:   #6e2a36;
    --lb-red-bg:    #2b161c;
    --lb-red-soft:  #ffb9c0;
    --lb-bg:        #111216;
    --lb-bg2:       #191a20;
    --lb-bg3:       #0b0c0f;
    --lb-border:    #292a31;
    --lb-text:      #f6f6f8;
    --lb-muted:     #a0a1ac;
    --lb-dim:       #686a77;
    --lb-radius:    18px;
    --lb-font-mono: 'IBM Plex Mono', 'Courier New', monospace;
    --lb-font-sans: 'Inter', system-ui, sans-serif;
}

/* ── Block Container ── */
.lb-block {
    background: var(--lb-bg);
    border-radius: var(--lb-radius);
    border: 1px solid var(--lb-border);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    margin-bottom: 24px;
}
.lb-block--guest {
    /* Slight transparency for guest view */
    background: rgba(17,18,22,0.92);
}

/* ── Hall of Fame Header ── */
.lb-hof-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 28px 0;
}
.lb-hof-header__left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.lb-icon-trophy {
    width: 19px;
    height: 19px;
    color: var(--lb-red);
    flex-shrink: 0;
}
.lb-hof-title {
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 18px;
    color: var(--lb-text);
    line-height: 1;
}
.lb-label-teknis {
    font-family: var(--lb-font-mono);
    font-size: 10px;
    letter-spacing: 1.1px;
    color: var(--lb-dim);
    line-height: 1;
}

/* ── Sorotan (message + podium row) ── */
.lb-sorotan {
    display: flex;
    align-items: flex-end;
    gap: 32px;
    padding: 8px 28px 0;
}

/* ── Pesan Komunitas ── */
.lb-pesan-komunitas {
    display: flex;
    flex-direction: column;
    width: 220px;
    flex-shrink: 0;
    gap: 14px;
    padding-bottom: 28px;
}
.lb-pesan__label {
    font-family: var(--lb-font-mono);
    font-size: 10px;
    letter-spacing: 1.4px;
    color: var(--lb-red);
    line-height: 1;
}
.lb-pesan__utama {
    font-family: var(--lb-font-sans);
    font-size: 22px;
    font-weight: 400;
    color: var(--lb-text);
    letter-spacing: -0.6px;
    line-height: 1.2;
    margin: 0;
}
.lb-pesan__sub {
    font-family: var(--lb-font-sans);
    font-size: 12px;
    color: var(--lb-muted);
    line-height: 1.6;
    margin: 0;
}
.lb-aksen-teknis {
    display: flex;
    align-items: center;
    gap: 5px;
}
.lb-penanda {
    display: block;
    height: 3px;
    background: var(--lb-red);
    border-radius: 2px;
}
.lb-penanda--1 { width: 32px; }
.lb-penanda--2 { width: 12px; opacity: 0.38; }
.lb-penanda--3 { width: 6px;  opacity: 0.19; }

/* ── Podium ── */
.lb-podium {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    flex: 1;
    padding: 0 24px;
    min-height: 282px;
}
.lb-podium--empty {
    align-items: center;
    justify-content: center;
}
.lb-empty-text {
    font-family: var(--lb-font-sans);
    font-size: 13px;
    color: var(--lb-muted);
}
.lb-podium__posisi {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    flex: 1;
}
.lb-podium__posisi--center {
    /* champion is visually taller due to taller pijakan */
}

/* ── Avatar ── */
.lb-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 100px;
    overflow: hidden;
    flex-shrink: 0;
}
.lb-avatar--normal {
    width: 60px;
    height: 60px;
    padding: 5px;
    border: 1px solid var(--lb-border);
    background: transparent;
    border-radius: 100px;
    /* inner circle */
    box-sizing: border-box;
}
.lb-avatar--normal .lb-avatar__inisial {
    display: flex;
    width: 50px;
    height: 50px;
    align-items: center;
    justify-content: center;
    background: var(--lb-bg2);
    border: 1px solid var(--lb-border);
    border-radius: 100px;
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 18px;
    color: var(--lb-muted);
}
.lb-avatar--champion {
    width: 76px;
    height: 76px;
    padding: 5px;
    border: 1px solid var(--lb-red);
    border-radius: 100px;
    background: transparent;
    box-sizing: border-box;
}
.lb-avatar--champion .lb-avatar__inisial--champion {
    display: flex;
    width: 66px;
    height: 66px;
    align-items: center;
    justify-content: center;
    background: var(--lb-red-bg);
    border: 1px solid var(--lb-red-dim);
    border-radius: 100px;
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 22px;
    color: var(--lb-red-soft);
}
.lb-avatar__img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 100px;
}

/* ── Profil Juara ── */
.lb-profil-juara {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    width: 100%;
}
.lb-profil__nama {
    font-family: var(--lb-font-sans);
    font-weight: 400;
    font-size: 12px;
    color: var(--lb-text);
    text-align: center;
    line-height: 1.2;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.lb-profil__poin {
    display: flex;
    align-items: baseline;
    gap: 5px;
}
.lb-profil__poin-nilai {
    font-family: var(--lb-font-mono);
    font-weight: 500;
    font-size: 20px;
    color: var(--lb-text);
}
.lb-profil__poin-nilai--champion {
    font-size: 25px;
    color: var(--lb-red);
}
.lb-profil__poin-label {
    font-family: var(--lb-font-sans);
    font-size: 11px;
    color: var(--lb-muted);
}

/* ── Crown ── */
.lb-crown {
    width: 23px;
    height: 23px;
    color: var(--lb-red-soft);
    flex-shrink: 0;
}
.lb-crown svg {
    width: 100%;
    height: 100%;
}

/* ── Pijakan Podium ── */
.lb-pijakan {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    border-radius: 9px 9px 0 0;
    overflow: hidden;
    border-top: 2px solid;
    border-right: 1px solid;
    border-left: 1px solid;
}
.lb-pijakan--2 {
    height: 58px;
    border-color: var(--lb-border);
    background: linear-gradient(0deg, rgba(34,36,44,1) 0%, rgba(20,21,26,1) 100%);
}
.lb-pijakan--1 {
    height: 82px;
    border-color: var(--lb-red-dim);
    background: linear-gradient(0deg, rgba(93,32,44,1) 0%, rgba(35,20,25,1) 100%);
}
.lb-pijakan--3 {
    height: 42px;
    border-color: var(--lb-border);
    background: linear-gradient(0deg, rgba(34,36,44,1) 0%, rgba(20,21,26,1) 100%);
}
.lb-pijakan__nomor {
    font-family: var(--lb-font-mono);
    font-weight: 500;
    font-size: 28px;
    color: var(--lb-dim);
}
.lb-pijakan__nomor--1 {
    font-size: 40px;
    color: var(--lb-red-soft);
}

/* ── Finalis 4 & 5 ── */
.lb-finalis {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 18px 28px;
    border-top: 1px solid var(--lb-border);
}
.lb-finalis__item {
    display: flex;
    height: 42px;
    align-items: center;
    gap: 12px;
    flex: 1;
}
.lb-finalis__rank {
    font-family: var(--lb-font-mono);
    font-size: 13px;
    color: var(--lb-dim);
    flex-shrink: 0;
}
.lb-finalis__avatar {
    width: 32px;
    height: 32px;
    background: var(--lb-bg2);
    border: 1px solid var(--lb-border);
    border-radius: 100px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.lb-finalis__inisial {
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 11.5px;
    color: var(--lb-muted);
}
.lb-finalis__nama {
    flex: 1;
    font-family: var(--lb-font-sans);
    font-weight: 500;
    font-size: 13px;
    color: var(--lb-muted);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.lb-finalis__poin {
    display: flex;
    align-items: baseline;
    gap: 4px;
    flex-shrink: 0;
}
.lb-finalis__poin-val {
    font-family: var(--lb-font-mono);
    font-size: 13px;
    color: var(--lb-muted);
}
.lb-finalis__poin-lbl {
    font-family: var(--lb-font-sans);
    font-size: 11px;
    color: var(--lb-dim);
}

/* ── Kontrol Tabel ── */
.lb-kontrol {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 22px 28px;
}
.lb-kontrol__kiri {
    display: flex;
    align-items: center;
    gap: 12px;
}
.lb-kontrol__judul {
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 18px;
    color: var(--lb-text);
}
.lb-badge-count {
    display: inline-flex;
    align-items: center;
    padding: 4px 8px;
    background: var(--lb-bg2);
    border-radius: 6px;
    font-family: var(--lb-font-sans);
    font-size: 11px;
    color: var(--lb-muted);
}
.lb-kontrol__kanan {
    display: flex;
    align-items: center;
    gap: 10px;
}
.lb-search {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 220px;
    height: 38px;
    padding: 0 12px;
    background: var(--lb-bg3);
    border: 1px solid var(--lb-border);
    border-radius: 8px;
    overflow: hidden;
}
.lb-search svg {
    width: 16px;
    height: 16px;
    color: var(--lb-dim);
    flex-shrink: 0;
}
.lb-search__placeholder {
    font-family: var(--lb-font-sans);
    font-size: 12px;
    color: var(--lb-dim);
}
.lb-sort-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    height: 38px;
    padding: 0 14px;
    background: var(--lb-bg2);
    border: 1px solid var(--lb-border);
    border-radius: 8px;
    cursor: pointer;
}
.lb-sort-btn svg {
    width: 15px;
    height: 15px;
    color: var(--lb-muted);
    flex-shrink: 0;
}
.lb-sort-btn span {
    font-family: var(--lb-font-sans);
    font-weight: 500;
    font-size: 13px;
    color: var(--lb-text);
    white-space: nowrap;
}

/* ── Kepala Tabel ── */
.lb-thead {
    display: flex;
    height: 40px;
    align-items: center;
    gap: 24px;
    padding: 0 28px;
    background: var(--lb-bg2);
}
.lb-thead__col {
    font-family: var(--lb-font-sans);
    font-weight: 500;
    font-size: 11px;
    color: var(--lb-muted);
}
.lb-thead__col--rank { width: 112px; flex-shrink: 0; }
.lb-thead__col--user { flex: 1; }
.lb-thead__col--role { width: 164px; flex-shrink: 0; }
.lb-thead__col--poin {
    width: 160px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 6px;
}
.lb-thead__col--poin svg {
    width: 12px;
    height: 12px;
    color: var(--lb-dim);
}

/* ── Baris Tabel ── */
.lb-baris {
    display: flex;
    height: 64px;
    align-items: center;
    gap: 24px;
    padding: 0 28px;
    border-bottom: 1px solid var(--lb-border);
}
.lb-baris--first {
    background: #1d1318;
}
.lb-baris--normal {
    background: transparent;
}
.lb-baris--me {
    border-left: 2px solid var(--lb-red);
    padding-left: 26px;
    background: rgba(240,68,82,0.04);
}

/* Kolom rank */
.lb-baris__rank {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 112px;
    flex-shrink: 0;
}
.lb-rank-num {
    font-family: var(--lb-font-mono);
    font-size: 14px;
    color: var(--lb-muted);
}
.lb-rank-num--first {
    color: var(--lb-red);
}
.lb-rank-crown-icon {
    width: 15px;
    height: 15px;
    color: var(--lb-red);
    flex-shrink: 0;
}

/* Kolom user */
.lb-baris__user {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}
.lb-baris__avatar {
    width: 34px;
    height: 34px;
    border-radius: 100px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: var(--lb-bg2);
    border: 1px solid var(--lb-border);
}
.lb-baris__avatar--first {
    background: var(--lb-red-bg);
    border-color: var(--lb-red-dim);
}
.lb-baris__inisial {
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 12px;
    color: var(--lb-muted);
}
.lb-baris__inisial--first {
    color: var(--lb-red-soft);
}
.lb-baris__nama {
    font-family: var(--lb-font-sans);
    font-weight: 500;
    font-size: 13px;
    color: var(--lb-text);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.lb-baris__nama--first {
    font-weight: 600;
}

/* Lencana */
.lb-lencana-pemimpin {
    display: inline-flex;
    align-items: center;
    padding: 3px 7px;
    background: var(--lb-red-bg);
    border-radius: 4px;
    font-family: var(--lb-font-sans);
    font-weight: 500;
    font-size: 10px;
    color: var(--lb-red-soft);
    white-space: nowrap;
    flex-shrink: 0;
}
.lb-lencana-saya {
    display: inline-flex;
    align-items: center;
    padding: 3px 7px;
    background: rgba(240,68,82,0.12);
    border: 1px solid rgba(240,68,82,0.3);
    border-radius: 4px;
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 10px;
    color: var(--lb-red);
    white-space: nowrap;
    flex-shrink: 0;
}

/* Kolom role */
.lb-baris__role {
    display: flex;
    align-items: center;
    width: 164px;
    flex-shrink: 0;
}
.lb-tanpa-peran {
    font-family: var(--lb-font-sans);
    font-size: 13px;
    color: var(--lb-dim);
}
.lb-lencana-admin {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    background: var(--lb-bg2);
    border: 1px solid var(--lb-border);
    border-radius: 5px;
    font-family: var(--lb-font-sans);
    font-size: 11px;
    color: var(--lb-muted);
}
.lb-lencana-admin svg {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
    color: var(--lb-muted);
}

/* Kolom poin */
.lb-baris__poin {
    width: 160px;
    flex-shrink: 0;
    display: flex;
    align-items: baseline;
    justify-content: flex-end;
    gap: 4px;
}
.lb-poin-val {
    font-family: var(--lb-font-mono);
    font-weight: 500;
    font-size: 17px;
    color: var(--lb-text);
}
.lb-poin-val--first {
    color: var(--lb-red);
}
.lb-poin-lbl {
    font-family: var(--lb-font-sans);
    font-size: 11px;
    color: var(--lb-dim);
}

/* Empty row */
.lb-empty-row {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 28px;
    font-family: var(--lb-font-sans);
    font-size: 13px;
    color: var(--lb-dim);
}

/* ── Navigasi Tabel ── */
.lb-nav-tabel {
    display: flex;
    height: 62px;
    align-items: center;
    justify-content: space-between;
    padding: 0 28px;
}
.lb-nav-tabel__info {
    font-family: var(--lb-font-sans);
    font-size: 12px;
    margin: 0;
}
.lb-nav-tabel__muted { color: var(--lb-dim); }
.lb-nav-tabel__accent { color: var(--lb-muted); }
.lb-nav-tabel__pages {
    display: flex;
    align-items: center;
    gap: 8px;
}
.lb-page-btn {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.lb-page-btn svg {
    width: 15px;
    height: 15px;
    color: var(--lb-dim);
}
.lb-page-aktif {
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--lb-red-bg);
    border: 1px solid var(--lb-red-dim);
    border-radius: 6px;
}
.lb-page-num {
    font-family: var(--lb-font-mono);
    font-size: 12px;
    color: var(--lb-red);
}

/* ── CTA Auth ── */
.lb-cta-auth {
    text-align: center;
    margin-bottom: 8px;
}
.lb-cta-auth__btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0.75rem 1.75rem;
    border-radius: 8px;
    background: linear-gradient(135deg, var(--lb-red) 0%, var(--lb-red-dark) 100%);
    color: #fff;
    font-family: var(--lb-font-sans);
    font-weight: 600;
    font-size: 15px;
    text-decoration: none;
    transition: opacity 0.2s;
    box-shadow: 0 4px 16px rgba(240,68,82,0.3);
}
.lb-cta-auth__btn:hover { opacity: 0.88; }
.lb-cta-auth__btn svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* ── Responsive ── */
@media (max-width: 768px) {
    .lb-sorotan {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    .lb-pesan-komunitas {
        width: 100%;
        padding-bottom: 0;
    }
    .lb-podium {
        padding: 0;
        min-height: 200px;
        width: 100%;
    }
    .lb-hof-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 6px;
        padding: 18px 20px 0;
    }
    .lb-sorotan {
        padding: 8px 20px 0;
    }
    .lb-finalis {
        flex-direction: column;
        padding: 14px 20px;
        gap: 10px;
    }
    .lb-kontrol {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 20px;
    }
    .lb-kontrol__kanan {
        width: 100%;
        flex-wrap: wrap;
    }
    .lb-search { width: 100%; }
    .lb-thead, .lb-baris {
        padding: 0 16px;
        gap: 12px;
    }
    .lb-baris__role { display: none; }
    .lb-thead__col--role { display: none; }
    .lb-nav-tabel { padding: 0 16px; }
}
</style>
