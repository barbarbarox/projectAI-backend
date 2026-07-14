<?php

namespace App\Services;

class AnalisisURLService
{
    protected RAGService $ragService;
    protected URLScanService $urlScanService;
    protected VirusTotalService $virusTotalService;

    public function __construct(RAGService $ragService, URLScanService $urlScanService, VirusTotalService $virusTotalService)
    {
        $this->ragService = $ragService;
        $this->urlScanService = $urlScanService;
        $this->virusTotalService = $virusTotalService;
    }

    public function analisis(string $url): array
    {
        $externalData = $this->gatherExternalData($url);
        $tugas = "Analisis keamanan URL: {$url}\n\nData dari scanner eksternal:\n" . json_encode($externalData, JSON_PRETTY_PRINT) . "\n\nBerikan penilaian keamanan lengkap termasuk risiko phishing, malware, dan miskonfigurasi.";
        return $this->ragService->analisis($url, $tugas);
    }

    protected function gatherExternalData(string $url): array
    {
        $data = ['url' => $url];
        $vtResult = $this->virusTotalService->scanUrl($url);
        if ($vtResult) $data['virustotal'] = $vtResult;
        $urlscanResult = $this->urlScanService->submit($url);
        if ($urlscanResult) $data['urlscan'] = $urlscanResult;
        return $data;
    }
}
