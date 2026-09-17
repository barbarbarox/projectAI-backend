%% ============================================================
%% REDSIM ARCHITECTURE - MERMAID DIAGRAMS
%% Cara pakai: Import tiap diagram ke draw.io secara terpisah
%% Extras > Edit Diagram > Pilih "Mermaid" > Paste kode
%% ============================================================


%% ============================================================
%% DIAGRAM 1: HIGH-LEVEL ARCHITECTURE
%% ============================================================

flowchart LR
    U([User/Admin])
    subgraph BE["⚙️ Backend Laravel"]
        B1[Rate Limiter] --> B2[Controllers]
        B2 --> B3[AI Service]
    end
    subgraph EXT["🌐 Eksternal"]
        E1[urlscan.io]
        E2[VirusTotal]
    end
    subgraph RAG["🧠 RAG"]
        R1[(Pinecone\nVector DB)]
        R2[LLM\nGroq/OpenAI]
    end
    DB[(Supabase\nPostgreSQL)]

    U --> BE
    BE --> EXT
    BE --> RAG
    EXT --> RAG
    RAG --> DB
    BE --> DB


%% ============================================================
%% DIAGRAM 2: FLOW LOGIN & REGISTER
%% ============================================================

flowchart TD
    S([Mulai]) --> P{Punya Akun?}
    P -->|Belum| R{Metode Register}
    P -->|Ya| L{Metode Login}

    R -->|Email| RF[Isi Formulir] --> CAP[reCAPTCHA] --> SU[Simpan DB\nis_verified=false] --> WA[Tunggu Admin]
    R -->|Google| GO[Google OAuth] --> SG[Simpan google_id] --> WA

    WA --> AP{Admin Setuju?}
    AP -->|Ya| NK[Kirim Notifikasi] --> D
    AP -->|Tidak| RJ[❌ Ditolak]

    L -->|Email| CV[Cek Kredensial] --> VI{is_verified?}
    L -->|Google| GO
    VI -->|Tidak| BL[❌ Ditolak]
    VI -->|Ya| OT{OTP Aktif?}
    OT -->|Ya| WO[Kirim OTP WA] --> VO[Input OTP] --> D
    OT -->|Tidak| D([🏠 Dashboard])


%% ============================================================
%% DIAGRAM 3: FLOW SCANNING - RAG PIPELINE
%% ============================================================

flowchart TD
    S([User di Dashboard]) --> TS{Tipe Scan}
    TS -->|URL| UM{Mode?}
    TS -->|File ZIP/Code| RL
    TS -->|Log File| RL

    UM -->|Biasa| RL{Rate Limit OK?}
    UM -->|Intensif| DV[Verifikasi Domain\nDNS TXT / Meta Tag] --> DC{Verified?}
    DC -->|Belum| TG[Generate Token] --> DW[User Pasang Token] --> DC
    DC -->|Sudah| RL

    RL -->|Kuota Habis| ER[❌ Tolak]
    RL -->|OK| OSINT

    subgraph OSINT["🌐 Pengumpulan Data OSINT"]
        O1[urlscan.io\nHTTP, DOM, SSL]
        O2[VirusTotal\nMalware, Hash]
    end

    OSINT --> RAG

    subgraph RAG["🧠 RAG Pipeline"]
        RA[Query Embedding\ndari Temuan] --> RB[(Pinecone Search\nCVE/CWE/MITRE)] --> RC[Ambil Top-K\nKonteks Relevan]
    end

    RAG --> LLM

    subgraph LLM["🤖 AI LLM (Groq/OpenAI)"]
        LA[Prompt: Data Scan\n+ Konteks Pinecone] --> LB[Generate Analisis]
    end

    LLM --> OUT

    subgraph OUT["📊 Output Laporan"]
        direction LR
        X1[Skor 0-100] --- X2[Verdict] --- X3[Ringkasan AI]
        X4[Temuan CVE/CWE] --- X5[MITRE ATT&CK Chain]
    end

    OUT --> DB[(Simpan ke Supabase)] --> R([✅ Laporan Ditampilkan])


%% ============================================================
%% DIAGRAM 4: FLOW HUB EDUKASI & CTF
%% ============================================================

flowchart TD
    S([Masuk Hub Edukasi]) --> L[Daftar Tantangan\nFilter Kategori & Level]
    L --> P[Pilih Soal CTF] --> T{Tipe Soal}
    T -->|Flag| FI[Input Flag/Teks]
    T -->|Pilihan Ganda| MI[Pilih Jawaban]
    T -->|Analisis Kode| CI[Submit Kode]
    FI & MI & CI --> V{Validasi Backend}
    V -->|Salah| WT[❌ Coba Lagi] --> FI
    V -->|Benar| PT[Hitung Poin\n+ Bonus Kecepatan] --> SP[(Simpan poin_user)] --> LB[🏆 Update Leaderboard] --> D([✅ Selesai])


%% ============================================================
%% DIAGRAM 5: FLOW ADMIN PANEL
%% ============================================================

flowchart LR
    A([Admin Login]) --> M{Menu Admin}

    M -->|Pengguna| U[Daftar Akun Baru] --> UA{Setuju?}
    UA -->|Ya| UV[is_verified=true\nNotifikasi]
    UA -->|Tidak| UR[❌ Tolak]

    M -->|Konfigurasi AI| AI[Daftar Provider] --> AC{Aksi}
    AC -->|Tambah| AM[Provider Baru\nAPI Key + Model]
    AC -->|Set Default| AD[is_default=true]
    AC -->|Nonaktifkan| AN[is_active=false]

    M -->|Soal CTF| CT[Daftar Tantangan] --> CC{Aksi}
    CC -->|Tambah| CM[Buat Soal\nJudul, Poin, Jawaban]
    CC -->|Toggle| CG[is_aktif on/off]

    M -->|Log Scan| LS[Monitor Scan\nStatus Real-time]


%% ============================================================
%% DIAGRAM 6: SEQUENCE DIAGRAM - RAG CHAT FLOW
%% (Berdasarkan Flow: User > Mobile App > Backend > Pinecone > LLM)
%% ============================================================

sequenceDiagram
    actor U as User
    participant M as Mobile App (Flutter)
    participant B as Backend API (Laravel)
    participant P as Vector DB (Pinecone)
    participant L as LLM (Gemini API)

    U->>M: Ketik pertanyaan
    note right of U: Misal: "Apa keutamaan sedekah?"

    M->>B: POST /api/chat { "query": "Apa keutamaan sedekah?" }

    rect rgb(255, 243, 200)
        note over B,P: Proses Retrieval (Pencarian Konteks)
        B->>P: POST /records/namespaces/default/search (Integrated Inference)
        P-->>B: Return top-K dokumen relevan (Teks Arab, Terjemahan, Tafsir)
    end

    rect rgb(220, 230, 255)
        note over B,L: Proses Generation (Pembuatan Jawaban)
        B->>L: POST /generateContent (Kirim Prompt + Konteks dari Pinecone)
        L-->>B: Return jawaban AI
    end

    B-->>M: Return { "answer": "...", "sources": ["..."] }
    M->>U: Tampilkan jawaban beserta rujukan sumber


%% ============================================================
%% DIAGRAM 7: SEQUENCE DIAGRAM - SCAN SECURITY FLOW
%% ============================================================

sequenceDiagram
    actor U as User
    participant B as Backend (Laravel)
    participant OS as OSINT APIs
    participant P as Pinecone
    participant L as LLM

    U->>B: POST /scan { url, mode, type }

    alt Mode Intensif & URL
        B->>U: Minta verifikasi kepemilikan domain
        U->>B: Konfirmasi token DNS/Meta Tag
        B->>B: Validasi token
    end

    B->>B: Cek rate limit (scan_count_today)

    rect rgb(230, 255, 230)
        note over B,OS: Pengumpulan Data OSINT
        B->>OS: Call urlscan.io (URL scan)
        OS-->>B: HTTP headers, DOM, SSL, cookies
        B->>OS: Call VirusTotal (hash/URL check)
        OS-->>B: Malware report, reputasi
    end

    rect rgb(255, 243, 200)
        note over B,P: RAG - Retrieval
        B->>P: Cari referensi CVE/CWE/MITRE dari temuan
        P-->>B: Top-K konteks OWASP, CISA, ATT&CK
    end

    rect rgb(220, 230, 255)
        note over B,L: AI - Generation
        B->>L: Prompt = Data Scan + Konteks Pinecone
        L-->>B: Skor, Verdict, Ringkasan, Temuan, Attack Chain
    end

    B->>B: Simpan ke Supabase (scans, temuan, simulasi_serangan)
    B-->>U: Return laporan lengkap
