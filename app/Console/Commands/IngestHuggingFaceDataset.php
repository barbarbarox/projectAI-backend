<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Services\PineconeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IngestHuggingFaceDataset extends Command
{
    protected $signature = 'ingest:hf {dataset} {--limit=100} {--batch=10}';
    protected $description = 'Ingest dataset dari Hugging Face ke Pinecone (Integrated Embedding) & Supabase';

    public function handle(PineconeService $pineconeService)
    {
        $datasetName = $this->argument('dataset');
        $limit = (int) $this->option('limit');
        $batchSize = (int) $this->option('batch');

        $this->info("🚀 Memulai pengambilan dataset: {$datasetName}");
        $this->info("📦 Target: {$limit} baris | Batch: {$batchSize}");

        // =====================================================
        // STEP 1: Ambil data dari Hugging Face Datasets Server
        // =====================================================
        $this->info("\n📡 Mengambil data dari Hugging Face Datasets Server...");

        $url = "https://datasets-server.huggingface.co/rows";
        $response = Http::timeout(30)->get($url, [
            'dataset' => $datasetName,
            'config'  => 'default',
            'split'   => 'train',
            'offset'  => 0,
            'length'  => $limit,
        ]);

        if (!$response->successful()) {
            $this->error("❌ Gagal mengambil dataset. HTTP {$response->status()}: {$response->body()}");
            return 1;
        }

        $rows = $response->json('rows') ?? [];
        if (empty($rows)) {
            $this->warn("⚠️ Dataset kosong atau tidak ditemukan.");
            return 1;
        }

        $this->info("✅ Berhasil mengambil " . count($rows) . " baris dari Hugging Face.");

        // =====================================================
        // STEP 2: Proses dan kirim ke Pinecone + Supabase
        // =====================================================
        $this->info("\n🔄 Memulai proses ingestion ke Pinecone & Supabase...");

        $bar = $this->output->createProgressBar(count($rows));
        $bar->start();

        $successCount = 0;
        $failCount = 0;
        $batch = [];

        foreach ($rows as $index => $row) {
            $data = $row['row'];

            // Gabungkan kolom teks yang relevan
            $userText = $data['user'] ?? ($data['instruction'] ?? ($data['question'] ?? ''));
            $assistantText = $data['assistant'] ?? ($data['output'] ?? ($data['answer'] ?? ''));
            $systemText = $data['system'] ?? ($data['input'] ?? '');

            if (empty($userText) && empty($assistantText)) {
                $bar->advance();
                $failCount++;
                continue;
            }

            // Format konten yang akan disimpan
            $content = "";
            if (!empty($systemText)) {
                $content .= "Context: {$systemText}\n";
            }
            $content .= "Question: {$userText}\nAnswer: {$assistantText}";

            $chunkId = (string) Str::uuid();

            // Tambah ke batch Pinecone
            $batch[] = [
                'id'       => $chunkId,
                'text'     => $content,
                'metadata' => [
                    'source'    => "hf:{$datasetName}",
                    'source_id' => $datasetName,
                    'row_index' => (string) ($row['row_idx'] ?? $index),
                ],
            ];

            // Simpan metadata ke Supabase (PostgreSQL)
            try {
                DB::table('knowledge_chunks')->insert([
                    'content'    => $content,
                    'source'     => 'huggingface',
                    'source_id'  => $datasetName,
                    'metadata'   => json_encode([
                        'system'    => $systemText,
                        'row_index' => $row['row_idx'] ?? $index,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Exception $e) {
                $this->newLine();
                $this->warn("⚠️ DB insert gagal baris {$index}: " . $e->getMessage());
            }

            // Kirim batch ke Pinecone jika sudah cukup
            if (count($batch) >= $batchSize) {
                $this->flushBatch($pineconeService, $batch, $successCount, $failCount);
                $batch = [];
            }

            $bar->advance();
        }

        // Kirim sisa batch terakhir
        if (!empty($batch)) {
            $this->flushBatch($pineconeService, $batch, $successCount, $failCount);
        }

        $bar->finish();

        $this->newLine(2);
        $this->info("🎉 Ingestion selesai!");
        $this->table(
            ['Metrik', 'Nilai'],
            [
                ['Dataset', $datasetName],
                ['Total Diproses', count($rows)],
                ['Berhasil ke Pinecone', $successCount],
                ['Gagal', $failCount],
            ]
        );

        return 0;
    }

    /**
     * Kirim batch records ke Pinecone via Integrated Embedding.
     */
    private function flushBatch(PineconeService $pineconeService, array &$batch, int &$success, int &$fail): void
    {
        $result = $pineconeService->upsertText($batch);

        if ($result) {
            $success += count($batch);
        } else {
            $fail += count($batch);
            $this->newLine();
            $this->error("❌ Batch gagal dikirim ke Pinecone (" . count($batch) . " records)");
        }

        // Jeda antar batch agar tidak rate-limited
        usleep(300000); // 300ms
    }
}
