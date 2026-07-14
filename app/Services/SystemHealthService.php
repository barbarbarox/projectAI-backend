<?php

namespace App\Services;

use App\Models\AiConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SystemHealthService
{
    protected PineconeService $pineconeService;

    public function __construct(PineconeService $pineconeService)
    {
        $this->pineconeService = $pineconeService;
    }

    /**
     * Mengecek seluruh status komponen sistem
     */
    public function checkAll(): array
    {
        return [
            'virustotal' => $this->checkVirusTotal(),
            'urlscan' => $this->checkUrlScan(),
            'embedding_service' => $this->checkEmbeddingService(),
            'vector_database' => $this->checkPinecone(),
            'ai_provider' => $this->checkAiProvider(),
        ];
    }

    protected function checkVirusTotal(): array
    {
        $apiKey = env('VIRUSTOTAL_API_KEY');
        if (empty($apiKey)) {
            return ['status' => 'error', 'message' => 'API Key belum dikonfigurasi di .env'];
        }

        try {
            // Kita tes dengan melihat domain Google sebagai mock test ringan
            $response = Http::withHeaders(['x-apikey' => $apiKey])
                ->timeout(5)
                ->get('https://www.virustotal.com/api/v3/domains/google.com');

            if ($response->successful()) {
                return ['status' => 'ok', 'message' => 'Terhubung (Terespon dalam ' . $response->transferStats->getTransferTime() * 1000 . 'ms)'];
            }
            if ($response->status() === 401) {
                return ['status' => 'error', 'message' => 'API Key tidak valid (401 Unauthorized)'];
            }
            if ($response->status() === 429) {
                return ['status' => 'warning', 'message' => 'Limit kuota tercapai (429 Too Many Requests)'];
            }

            return ['status' => 'error', 'message' => 'HTTP ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal terhubung: Timeout atau jaringan bermasalah'];
        }
    }

    protected function checkUrlScan(): array
    {
        $apiKey = env('URLSCAN_API_KEY');
        if (empty($apiKey)) {
            return ['status' => 'error', 'message' => 'API Key belum dikonfigurasi di .env'];
        }

        try {
            $response = Http::withHeaders(['API-Key' => $apiKey])
                ->timeout(5)
                ->get('https://urlscan.io/user/quotas/'); // Menggunakan endpoint user quotas

            if ($response->successful()) {
                return ['status' => 'ok', 'message' => 'Terhubung (Terespon dalam ' . $response->transferStats->getTransferTime() * 1000 . 'ms)'];
            }
            if ($response->status() === 400 || $response->status() === 401) {
                return ['status' => 'error', 'message' => 'API Key tidak valid'];
            }
            if ($response->status() === 429) {
                return ['status' => 'warning', 'message' => 'Limit kuota tercapai'];
            }

            return ['status' => 'error', 'message' => 'HTTP ' . $response->status()];
        } catch (\Exception $e) {
            return ['status' => 'error', 'message' => 'Gagal terhubung: Timeout atau jaringan bermasalah'];
        }
    }

    /**
     * Cek layanan Embedding via Pinecone Integrated Inference.
     * (Tidak lagi menggunakan HuggingFace API — embedding dilakukan langsung oleh Pinecone)
     */
    protected function checkEmbeddingService(): array
    {
        try {
            // Tes koneksi Pinecone Integrated Embedding lewat search sederhana
            $matches = $this->pineconeService->queryText('connection test', 1);

            // Jika tidak ada error (meskipun hasil kosong), berarti koneksi berhasil
            return [
                'status' => 'ok',
                'message' => 'Pinecone Integrated Embedding Aktif | Model: multilingual-e5-large | Hasil: ' . count($matches) . ' match(es)',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Gagal terhubung ke Pinecone Embedding: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Cek koneksi ke Pinecone vector database.
     */
    protected function checkPinecone(): array
    {
        return $this->pineconeService->healthCheck();
    }

    protected function checkAiProvider(): array
    {
        $config = AiConfiguration::where('is_default', true)->where('is_active', true)->first();
        if (!$config) {
            return ['status' => 'error', 'message' => 'Tidak ada konfigurasi AI aktif yang diset sebagai default'];
        }

        return [
            'status' => 'ok',
            'message' => 'Konfigurasi aktif: ' . $config->label . ' (' . $config->selected_model . ')',
            'provider' => $config->provider
        ];
    }
}
