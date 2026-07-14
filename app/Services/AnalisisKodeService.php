<?php

namespace App\Services;

class AnalisisKodeService
{
    protected RAGService $ragService;

    public function __construct(RAGService $ragService)
    {
        $this->ragService = $ragService;
    }

    public function analisis(string $kode, string $bahasa = 'php', bool $useRag = true, ?string $modelOverride = null): array
    {
        $tugas = "Analisis kode {$bahasa} berikut untuk kerentanan keamanan:\n\n```{$bahasa}\n{$kode}\n```\n\nIdentifikasi semua kerentanan, berikan skor keamanan, dan simulasi serangan yang mungkin.";
        return $this->ragService->analisis($kode, $tugas, $useRag, $modelOverride);
    }
}
