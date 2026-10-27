<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Scan;
use App\Models\Temuan;
use App\Models\SimulasiSerangan;
use App\Models\AssetSubdomain;
use App\Services\ScanBiasaService;
use App\Services\ScanIntensService;
use App\Services\AnalisisURLService;
use App\Services\MLPredictionService;
use App\Traits\NotifyScanComplete;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProsesAnalisisURL implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, NotifyScanComplete;

    public int $tries = 1;       // 1 try saja — retry menyebabkan status 'gagal' overwrite hasil sukses
    public int $timeout = 720;    // 12 menit — cukup untuk scan intens (port 23 port + HTTP methods + SSL + subdomain)

    public function __construct(
        protected Scan $scan,
        protected string $url,
        protected string $modeScan = 'biasa'
    ) {}

    public function handle(
        ScanBiasaService $scanBiasa,
        ScanIntensService $scanIntens,
        \App\Services\RAGService $ragService
    ): void {
        try {
            $this->scan->update(['progress_step' => 'Memulai Pemindaian...', 'progress_persen' => 5, 'pipeline_stage' => 1]);

            // === AUDIT LOG: Scan dimulai ===
            AuditLog::create([
                'user_id'     => $this->scan->user_id,
                'action'      => 'scan_started',
                'description' => "[Scan #{$this->scan->id}] Mode: {$this->modeScan} | Target: {$this->url}",
                'ip_address'  => null,
                'user_agent'  => 'Queue Worker',
            ]);

            $dataMentah = [];
            
            if ($this->modeScan === 'intens') {
                // Stage 1: Rekon & Aset
                $this->scan->update(['progress_step' => 'Rekon & Aset — WHOIS, DNS, IP Geolocation...', 'progress_persen' => 10, 'pipeline_stage' => 1]);
                $dataMentah = $scanIntens->scan($this->url, function (string $step, int $persen) {
                    // Map progress persen ke pipeline stages:
                    // 0-25 → stage 1 (Rekon), 25-40 → stage 2 (Fingerprint),
                    // 40-55 → stage 3 (Keamanan), 55-62 → stage 4 (SSL)
                    $stage = match(true) {
                        $persen < 25 => 1,
                        $persen < 40 => 2,
                        $persen < 55 => 3,
                        $persen < 62 => 4,
                        default      => 4,
                    };
                    $this->scan->update([
                        'progress_step'   => $step,
                        'progress_persen' => $persen,
                        'pipeline_stage'  => $stage,
                        'updated_at'      => now(),
                    ]);
                });
                $this->scan->update(['progress_step' => 'Pemindaian Selesai — Mengolah Data...', 'progress_persen' => 62, 'pipeline_stage' => 4]);
            } else {
                // Scan biasa: stage 1→3 saja (tidak ada intens)
                $this->scan->update(['progress_step' => 'Pemindaian Biasa Berjalan...', 'progress_persen' => 35, 'pipeline_stage' => 2]);
                $dataMentah = $scanBiasa->scan($this->url);
                $this->scan->update(['progress_persen' => 55, 'pipeline_stage' => 3]);
            }

            $useAi = $this->scan->mode_ai !== 'tanpa_ai';
            $useRag = $this->scan->mode_rag === 'dengan_rag';

            if (!$useAi) {
                // Stage 6: Finalisasi (tanpa AI)
                $this->scan->update(['progress_step' => 'Finalisasi Laporan (Tanpa AI)...', 'progress_persen' => 90, 'pipeline_stage' => 6]);
                $this->scan->update([
                    'status' => 'selesai',
                    'data_mentah' => $dataMentah,
                    'skor_keamanan' => $dataMentah['skor_reputasi'] ?? 50,
                    'teknologi_terdeteksi' => $dataMentah['teknologi'] ?? [],
                    'tech_stack' => $dataMentah['tech_stack_structured'] ?? null,
                    'screenshot_path' => $dataMentah['screenshot_url'] ?? null,
                    'progress_step' => 'Selesai!',
                    'progress_persen' => 100,
                    'pipeline_stage' => 6,
                    'selesai_at' => now(),
                ]);
                $this->persistSubdomainsAndTakeoverRisks($dataMentah);
                $this->notifyScanCompleteViaWa($this->scan);
                return;
            }

            // Stage 5: Threat Intelligence
            $this->scan->update(['progress_step' => 'Threat Intelligence — Korelasi CVE & ML Prediction...', 'progress_persen' => 68, 'pipeline_stage' => 5]);

            // Ekstraksi Fingerprint Layanan & Korelasi CVE NVD
            $correlator    = app(\App\Services\UrlVulnerabilityCorrelator::class);
            $nvdRagService = app(\App\Services\NVDRAGService::class);

            $fingerprint   = $correlator->extractFingerprint($dataMentah);
            $candidateCves = $correlator->getSpecificCandidateCveIds($fingerprint);
            $targetedQuery = $correlator->buildTargetedQuery($this->url, $dataMentah, $fingerprint);
            $threatGuide   = $correlator->formatThreatAnalysisGuide($fingerprint, $candidateCves);

            $customKonteks = null;
            $customChunks  = null;

            if ($useRag) {
                $this->scan->update(['progress_step' => 'Korelasi Layanan & Dataset NVD CVE...', 'progress_persen' => 78, 'pipeline_stage' => 5]);
                $correlatedResult = $correlator->retrieveCorrelatedContext(
                    $targetedQuery,
                    $candidateCves,
                    $ragService,
                    $nvdRagService
                );
                $customKonteks = $correlatedResult['konteks'];
                $customChunks  = $correlatedResult['chunks'];
            }
            
            $tugas = <<<TUGAS
Kamu adalah Senior Penetration Tester dan Red Team Specialist.
Analisis keamanan mendalam untuk URL target berikut berdasarkan hasil pemindaian dan korelasi intelijen kerentanan NVD CVE.

TARGET: {$this->url}

{$threatGuide}

DATA MENTAH HASIL PEMINDAIAN:
{$this->formatDataMentahRingkas($dataMentah)}

INSTRUKSI PENTING:
1. Analisis SEMUA kerentanan yang relevan berdasarkan data pemindaian dan fingerprint di atas
2. Evaluasi kerentanan spesifik yang diindikasikan oleh fingerprint:
   - Jika runtime PHP terdeteksi: evaluasi celah PHP2Shell (CVE-2026-109618 OOM longjmp RCE, CVE-2026-109619 UAF upload progress RCE) dan PHP CGI Injection (CVE-2024-4577).
   - Jika Node.js / encoding Brotli terdeteksi: evaluasi celah RCE decoder Brotli (CVE-2020-8927 buffer overflow, CVE-2024-21892 code injection, CVE-2024-22019 request smuggling).
   - Jika SharePoint / Microsoft-IIS terdeteksi: evaluasi eksploitasi Tool Shell SharePoint (CVE-2023-29357 pre-auth bypass, CVE-2023-24955 ToolShell RCE) dan deserialization (CVE-2024-38094).
   - Jika Apache HTTP Server terdeteksi: evaluasi Path Traversal & RCE (CVE-2021-41773, CVE-2021-42013).
   - Jika Next.js terdeteksi: evaluasi Server Actions SSRF/RCE (CVE-2024-34351).
   - Jika OS Linux/Ubuntu/Debian terdeteksi: periksa kemungkinan celah OpenSSH (CVE-2024-6387 regreSSHion).
3. Jika banner atau versi mengindikasikan komponen tersebut digunakan, cantumkan cve_id spesifik pada daftar temuan.
4. Buat skenario serangan yang REALISTIS dan PROPORSIONAL terhadap severity temuan nyata
5. Sertakan rantai serangan (kill chain) yang logis SESUAI temuan yang ada
6. Semua narasi WAJIB dalam BAHASA INDONESIA
7. Fokus pada risiko nyata yang terdeteksi dari data pemindaian

KALIBRASI SKOR (WAJIB DIIKUTI):
- skor_keamanan 85-100: hanya jika semua header lengkap, SSL valid, tidak ada temuan kritis/tinggi
- skor_keamanan 65-84: temuan rendah/informasi saja, header sebagian hilang
- skor_keamanan 45-64: ada temuan sedang atau beberapa header missing kritis
- skor_keamanan 25-44: ada temuan tinggi atau CORS/Cookie tidak aman
- skor_keamanan 0-24: ada temuan kritis atau terdeteksi malware/backdoor
- verdict "aman": skor >= 75, TIDAK ada temuan tinggi/kritis
- verdict "perhatian": skor 45-74, atau ada temuan sedang
- verdict "berbahaya": skor < 45, atau ada temuan tinggi/kritis

KALIBRASI CONFIDENCE (WAJIB DIIKUTI):
- tingkat_kepercayaan "tinggi": hanya jika kerentanan terdeteksi dari data scan langsung (header/cookie/banner)
- tingkat_kepercayaan "sedang": jika kerentanan logis berdasarkan teknologi terdeteksi tapi tidak dikonfirmasi
- tingkat_kepercayaan "rendah": jika hanya inferensi berdasarkan pola umum tanpa bukti konkret dari scan
- confidence_score keseluruhan: sesuaikan dengan persentase temuan yang didukung data nyata (bukan inferensi)

KALIBRASI SIMULASI SERANGAN:
- skor_kemungkinan dan skor_dampak HARUS proporsional dengan temuan nyata
- Jika semua temuan severity RENDAH/INFORMASI: skor_kemungkinan max 4, skor_dampak max 4
- Jika ada temuan SEDANG: skor_kemungkinan max 6, skor_dampak max 6
- Jika ada temuan TINGGI: skor_kemungkinan max 8, skor_dampak max 8
- Hanya jika ada temuan KRITIS: bisa mencapai 9-10

FORMAT OUTPUT JSON (tanpa markdown):
{
  "skor_keamanan": <0-100, dikalibrasi sesuai panduan di atas>,
  "verdict": "<aman|perhatian|berbahaya>",
  "ringkasan_eksekutif": "<2-3 kalimat ringkasan risiko bisnis dalam bahasa Indonesia>",
  "ringkasan_teknis": "<penjelasan teknis mendalam semua kerentanan yang ditemukan, bahasa Indonesia>",
  "confidence_score": <0.0-1.0, dikalibrasi berdasarkan proporsi temuan berbasis bukti nyata>,
  "temuan": [
    {
      "tipe": "<tipe kerentanan>",
      "tingkat_keparahan": "<kritis|tinggi|sedang|rendah|informasi>",
      "judul": "<judul kerentanan, bahasa Indonesia>",
      "deskripsi": "<deskripsi detail kerentanan, bahasa Indonesia>",
      "lokasi": "<URL/header/port/komponen spesifik yang rentan>",
      "nomor_baris": null,
      "kode_rentan": null,
      "kode_aman": null,
      "cve_id": "<CVE-xxxx-xxxxx atau null jika temuan umum>",
      "cwe_id": "<CWE-xxx>",
      "teknik_attck": "<MITRE ATT&CK ID dan nama>",
      "remediasi": "<langkah-langkah perbaikan konkret, bahasa Indonesia>",
      "estimasi_usaha": "<mudah|sedang|sulit>",
      "tingkat_kepercayaan": "<rendah|sedang|tinggi>"
    }
  ],
  "simulasi_serangan": [
    {
      "nama_skenario": "<nama skenario serangan, bahasa Indonesia>",
      "profil_penyerang": "<script_kiddie|penyerang_oportunistik|penyerang_tertarget|ancaman_internal>",
      "narasi_eksekutif": "<dampak bisnis POTENSIAL jika skenario terjadi, 2-3 kalimat, bahasa Indonesia>",
      "narasi_teknis": "<detail teknis serangan step-by-step SESUAI temuan nyata yang ditemukan, bahasa Indonesia>",
      "skor_kemungkinan": <1-10, dikalibrasi sesuai severity temuan>,
      "skor_dampak": <1-10, dikalibrasi sesuai severity temuan>,
      "rantai_serangan": [
        "Langkah 1: Pengintaian — deskripsi aktivitas rekon target",
        "Langkah 2: Pemindaian — identifikasi celah dari port/header/file yang terekspos",
        "Langkah 3: Eksploitasi — cara penyerang mengeksploitasi kerentanan yang ditemukan",
        "Langkah 4: Pasca-Eksploitasi — apa yang dilakukan setelah berhasil masuk",
        "Langkah 5: Dampak Akhir — konsekuensi serangan"
      ],
      "fase_attck": [
        "TA0001 - Initial Access: T1190 Exploit Public-Facing Application",
        "TA0007 - Discovery: T1046 Network Service Scanning"
      ]
    }
  ]
}
TUGAS;

            // Stage 6: Analisis AI
            $this->scan->update(['progress_step' => 'AI Sedang Mensintesis Laporan...', 'progress_persen' => 85, 'pipeline_stage' => 6]);

            $hasilAi = $ragService->analisis(
                $targetedQuery,
                $tugas,
                $useRag,
                $this->scan->model_ai,
                $this->scan->user_id,
                $customKonteks,
                $customChunks
            );

            $this->scan->update(['progress_step' => 'Finalisasi Laporan...', 'progress_persen' => 97, 'pipeline_stage' => 6]);

            $this->scan->update([
                'status' => 'selesai',
                'data_mentah' => $dataMentah,
                'skor_keamanan' => $hasilAi['skor_keamanan'] ?? ($dataMentah['skor_reputasi'] ?? 50),
                'verdict' => $hasilAi['verdict'] ?? null,
                'ringkasan_eksekutif' => $hasilAi['ringkasan_eksekutif'] ?? null,
                'ringkasan_teknis' => $hasilAi['ringkasan_teknis'] ?? null,
                'confidence_score' => $hasilAi['confidence_score'] ?? null,
                'rag_references' => $hasilAi['rag_references'] ?? null,
                'teknologi_terdeteksi' => $dataMentah['teknologi'] ?? [],
                'tech_stack' => $dataMentah['tech_stack_structured'] ?? null,
                'screenshot_path' => $dataMentah['screenshot_url'] ?? null,
                'progress_step' => 'Selesai!',
                'progress_persen' => 100,
                'pipeline_stage' => 6,
                'selesai_at' => now(),
            ]);

            $this->notifyScanCompleteViaWa($this->scan);

            // === AUDIT LOG: Scan selesai ===
            $jumlahTemuan = count($hasilAi['temuan'] ?? []);
            $skorAkhir    = $hasilAi['skor_keamanan'] ?? ($dataMentah['skor_reputasi'] ?? 50);
            AuditLog::create([
                'user_id'     => $this->scan->user_id,
                'action'      => 'scan_completed',
                'description' => "[Scan #{$this->scan->id}] Target: {$this->url} | Skor: {$skorAkhir}/100 | Temuan: {$jumlahTemuan} | Mode: {$this->modeScan}",
                'ip_address'  => null,
                'user_agent'  => 'Queue Worker',
            ]);

            foreach ($hasilAi['temuan'] ?? [] as $t) {
                // Pilar 1: Prediksi ML via Random Forest
                $mlService = app(MLPredictionService::class);
                $mlResult  = $mlService->predict($t);

                Temuan::create(array_merge(
                    ['scan_id' => $this->scan->id],
                    array_intersect_key($t, array_flip([
                        'tipe','tingkat_keparahan','judul','deskripsi','lokasi','nomor_baris',
                        'kode_rentan','kode_aman','cve_id','cwe_id','teknik_attck',
                        'remediasi','estimasi_usaha','tingkat_kepercayaan',
                    ])),
                    [
                        'ml_predicted_severity'   => $mlResult['ml_predicted_severity'] ?? null,
                        'ml_confidence'           => $mlResult['ml_confidence'] ?? null,
                        'konsensus_severity'      => $mlResult['konsensus'] ?? null,
                        'ml_class_probabilities'  => $mlResult['class_probabilities'] ?? null,
                    ]
                ));
            }

            foreach ($hasilAi['simulasi_serangan'] ?? [] as $s) {
                SimulasiSerangan::create(array_merge(['scan_id' => $this->scan->id], array_intersect_key($s, array_flip([
                    'nama_skenario','profil_penyerang','narasi_teknis','narasi_eksekutif',
                    'skor_kemungkinan','skor_dampak','rantai_serangan','fase_attck',
                ]))));
            }

            $this->persistSubdomainsAndTakeoverRisks($dataMentah);

        } catch (\Throwable $e) {
            Log::error("Analisis URL gagal: " . $e->getMessage());
            try {
                \DB::table('scans')->where('id', $this->scan->id)->update([
                    'status' => 'gagal',
                    'progress_step' => 'Gagal',
                    'error_message' => substr($e->getMessage(), 0, 1000),
                    'updated_at' => now(),
                ]);
                // === AUDIT LOG: Scan gagal ===
                AuditLog::create([
                    'user_id'     => $this->scan->user_id,
                    'action'      => 'scan_failed',
                    'description' => "[Scan #{$this->scan->id}] Target: {$this->url} | Error: " . substr($e->getMessage(), 0, 300),
                    'ip_address'  => null,
                    'user_agent'  => 'Queue Worker',
                ]);
            } catch (\Throwable $t) {
                Log::error("Gagal update status scan ke gagal: " . $t->getMessage());
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error("Job ProsesAnalisisURL failed(): " . ($exception ? $exception->getMessage() : 'No exception'));
        try {
            \DB::table('scans')->where('id', $this->scan->id)->update([
                'status' => 'gagal',
                'progress_step' => 'Gagal',
                'error_message' => $exception ? substr($exception->getMessage(), 0, 1000) : 'Job gagal dieksekusi oleh worker.',
                'updated_at' => now(),
            ]);
        } catch (\Throwable $t) {
            Log::error("Gagal update status scan di failed callback: " . $t->getMessage());
        }
    }

    /**
     * Format ringkasan data mentah scan untuk dikirim ke AI (menghindari token limit).
     */
    protected function formatDataMentahRingkas(array $data): string
    {
        $parts = [];

        // Reputasi & VirusTotal
        if (!empty($data['virustotal'])) {
            $vt = $data['virustotal'];
            $parts[] = "=== VIRUSTOTAL ===";
            $parts[] = "Malicious: " . ($vt['malicious_count'] ?? 0) . " engines";
            $parts[] = "Stats: " . json_encode($vt['stats'] ?? []);
        }

        // Security Headers
        if (!empty($data['security_headers'])) {
            $headers = $data['security_headers'];
            $parts[] = "\n=== SECURITY HEADERS ===";
            $parts[] = "Skor: " . ($headers['skor'] ?? 0) . "/100";
            if (!empty($headers['missing'])) {
                $parts[] = "Header Hilang (Kritis): " . implode(', ', array_slice($headers['missing'], 0, 10));
            }
        }

        // SSL
        if (!empty($data['ssl'])) {
            $ssl = $data['ssl'];
            $parts[] = "\n=== SSL/TLS ===";
            $parts[] = "Valid: " . ($ssl['valid'] ? 'Ya' : 'Tidak');
            $parts[] = "Status: " . ($ssl['status'] ?? 'unknown');
            if (!empty($ssl['weak_protocol'])) $parts[] = "⚠ Protokol Lemah Terdeteksi";
            if (!empty($ssl['weak_cipher'])) $parts[] = "⚠ Cipher Suite Lemah Terdeteksi";
            $parts[] = "Kadaluarsa: " . ($ssl['expires_at'] ?? 'N/A') . " (Sisa: " . ($ssl['days_until_expiry'] ?? '?') . " hari)";
        }

        // Port Scan
        if (!empty($data['port_scan'])) {
            $ports = $data['port_scan'];
            $parts[] = "\n=== PORT SCAN ===";
            if (!empty($ports['open_ports'])) {
                $parts[] = "Port Terbuka: " . implode(', ', array_slice($ports['open_ports'], 0, 20));
            }
            if (!empty($ports['critical_open'])) {
                $parts[] = "⚠ Port Kritis Terbuka: " . implode(', ', $ports['critical_open']);
            }
        }

        // Common Files
        $exposed = [];
        foreach ($data['common_files'] ?? [] as $f) {
            if ($f['terekspos'] ?? false) {
                $exposed[] = $f['path'] . ' [' . ($f['risiko'] ?? 'unknown') . ']';
            }
        }
        if (!empty($exposed)) {
            $parts[] = "\n=== FILE SENSITIF TEREKSPOS ===";
            $parts[] = implode(', ', array_slice($exposed, 0, 15));
        }

        // DNS & Subdomain
        if (!empty($data['dns_recon'])) {
            $dns = $data['dns_recon'];
            $parts[] = "\n=== DNS RECON ===";
            if (!empty($dns['ip_addresses'])) $parts[] = "IP: " . implode(', ', $dns['ip_addresses']);
            if (!empty($dns['cdn_waf'])) $parts[] = "CDN/WAF: " . implode(', ', $dns['cdn_waf']);
        }
        if (!empty($data['subdomains']['interesting'])) {
            $parts[] = "Subdomain Menarik: " . implode(', ', array_slice($data['subdomains']['interesting'], 0, 10));
        }

        // CORS
        if (!empty($data['cors_config'])) {
            $cors = $data['cors_config'];
            if ($cors['vulnerable'] ?? false) {
                $parts[] = "\n⚠ CORS MISCONFIGURATION TERDETEKSI";
                $parts[] = "Masalah: " . implode(', ', $cors['masalah'] ?? []);
            }
        }

        // Cookie
        if (!empty($data['cookie_security'])) {
            $cookie = $data['cookie_security'];
            $parts[] = "\n=== COOKIE SECURITY ===";
            $parts[] = "Skor: " . ($cookie['skor'] ?? 100) . "/100";
            if (!empty($cookie['masalah'])) {
                $parts[] = "Masalah: " . implode(', ', array_slice($cookie['masalah'], 0, 5));
            }
        }

        // HTTP Methods
        if (!empty($data['http_methods'])) {
            $methods = $data['http_methods'];
            if (!empty($methods['berbahaya'])) {
                $parts[] = "\n⚠ HTTP METHOD BERBAHAYA: " . implode(', ', $methods['berbahaya']);
            }
        }

        // Server Info
        if (!empty($data['server_info'])) {
            $server = $data['server_info'];
            $parts[] = "\n=== SERVER INFO ===";
            if (!empty($server['ip'])) $parts[] = "IP: " . $server['ip'];
            if (!empty($server['server'])) $parts[] = "Server: " . $server['server'];
            if (!empty($server['country'])) $parts[] = "Negara: " . $server['country'];
        }

        // Teknologi
        if (!empty($data['teknologi'])) {
            $parts[] = "\n=== TEKNOLOGI ===";
            $parts[] = implode(', ', array_slice($data['teknologi'], 0, 20));
        }

        // Skor Breakdown
        if (!empty($data['skor_breakdown'])) {
            $parts[] = "\n=== SKOR BREAKDOWN ===";
            foreach ($data['skor_breakdown'] as $key => $val) {
                $skor = $val['skor'] ?? ($val['bonus'] ?? ($val['penalty'] ?? '?'));
                $bobot = $val['bobot'] ?? '';
                $parts[] = "  {$key}: {$skor}" . ($bobot ? " (bobot {$bobot})" : '');
            }
        }

        return implode("\n", $parts);
    }

    /**
     * Persist discovered subdomains to database and create findings for any detected takeover risks.
     */
    protected function persistSubdomainsAndTakeoverRisks(array $dataMentah): void
    {
        try {
            $subData = $dataMentah['subdomains'] ?? [];
            $subList = $subData['subdomains'] ?? [];
            $baseDomain = $subData['base_domain'] ?? (parse_url($this->url, PHP_URL_HOST) ?: $this->url);

            if (is_array($subList) && !empty($subList)) {
                foreach ($subList as $sub) {
                    $subdomainName = $sub['subdomain'] ?? ($sub['name'] ?? null);
                    if (empty($subdomainName)) continue;

                    AssetSubdomain::updateOrCreate(
                        [
                            'scan_id'   => $this->scan->id,
                            'subdomain' => $subdomainName,
                        ],
                        [
                            'user_id'           => $this->scan->user_id,
                            'root_domain'       => $baseDomain,
                            'ip_address'        => $sub['ip_address'] ?? null,
                            'cname'             => $sub['cname'] ?? null,
                            'status_http'       => $sub['status_http'] ?? null,
                            'is_alive'          => (bool) ($sub['is_alive'] ?? false),
                            'takeover_risk'     => (bool) ($sub['takeover_risk'] ?? false),
                            'takeover_service'  => $sub['takeover_service'] ?? null,
                            'takeover_evidence' => $sub['takeover_evidence'] ?? null,
                            'is_interesting'    => (bool) ($sub['is_interesting'] ?? ($sub['menarik'] ?? false)),
                            'server_header'     => $sub['server_header'] ?? null,
                            'page_title'        => $sub['page_title'] ?? null,
                        ]
                    );
                }
            }

            // Ciptakan temuan kritis otomatis untuk setiap takeover risk yang terkonfirmasi
            $takeoverRisks = $subData['takeover_risks'] ?? [];
            if (is_array($takeoverRisks) && !empty($takeoverRisks)) {
                foreach ($takeoverRisks as $tr) {
                    $subName = $tr['subdomain'] ?? ($tr['name'] ?? '-');
                    $svc = $tr['takeover_service'] ?? 'Cloud Provider';
                    $cname = $tr['cname'] ?? '-';
                    $evidence = $tr['takeover_evidence'] ?? '';

                    Temuan::firstOrCreate(
                        [
                            'scan_id' => $this->scan->id,
                            'judul'   => "Kerentanan Subdomain Takeover ({$svc}) pada {$subName}",
                        ],
                        [
                            'tipe'                => 'subdomain_takeover',
                            'tingkat_keparahan'   => 'kritis',
                            'deskripsi'           => "Subdomain {$subName} memiliki record CNAME yang menunjuk ke {$cname} ({$svc}), namun resource di target service tidak terdaftar atau telah dihapus (dangling CNAME). Penyerang dapat mendaftarkan resource tersebut di {$svc} untuk mengontrol penuh konten subdomain ini. Bukti: {$evidence}",
                            'lokasi'              => "DNS CNAME: {$subName} -> {$cname}",
                            'cwe_id'              => 'CWE-284',
                            'teknik_attck'        => 'T1584.001 - Compromise Infrastructure: Domains',
                            'remediasi'           => "1. Segera hapus DNS CNAME record dari DNS Manager untuk subdomain {$subName} jika tidak lagi digunakan.\n2. Jika subdomain masih diperlukan, daftarkan kembali nama project/bucket/app tersebut di akun {$svc} organisasi Anda sebelum diklaim oleh pihak luar.",
                            'prioritas_perbaikan' => 1,
                            'estimasi_usaha'      => 'mudah',
                            'tingkat_kepercayaan' => 'tinggi',
                        ]
                    );
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Gagal menyimpan asset_subdomains: " . $e->getMessage());
        }
    }
}

