<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RAGService;
use App\Services\GeminiService;

class ChatController extends Controller
{
    public function index(GeminiService $geminiService)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $availableModels = $geminiService->getAvailableModels($userId);
        
        $userConfig = \App\Models\UserAiConfig::getActiveForUser($userId);
        $adminConfig = \App\Models\AiConfiguration::getActiveWithModel();
        
        $defaultModelId = $userConfig ? $userConfig->selected_model : ($adminConfig->selected_model ?? '');
        $defaultModelName = 'Gunakan Model Default';
        
        foreach ($availableModels as $m) {
            if ($m['id'] === $defaultModelId) {
                $defaultModelName = 'Default: ' . $m['name'];
                break;
            }
        }
        
        return view('admin.chat-test', compact('availableModels', 'defaultModelName'));
    }

    public function indexPublic(GeminiService $geminiService)
    {
        $userId = \Illuminate\Support\Facades\Auth::id();
        $availableModels = $geminiService->getAvailableModels($userId);
        
        $userConfig = \App\Models\UserAiConfig::getActiveForUser($userId);
        $adminConfig = \App\Models\AiConfiguration::getActiveWithModel();
        
        $defaultModelId = $userConfig ? $userConfig->selected_model : ($adminConfig->selected_model ?? '');
        $defaultModelName = 'Gunakan Model Default';
        
        foreach ($availableModels as $m) {
            if ($m['id'] === $defaultModelId) {
                $defaultModelName = 'Default: ' . $m['name'];
                break;
            }
        }
        
        return view('chat-ai', compact('availableModels', 'defaultModelName'));
    }

    public function chat(Request $request, RAGService $ragService, GeminiService $geminiService)
    {
        $request->validate([
            'message' => 'required|string',
            'model_ai' => 'nullable|string'
        ]);
        
        $query = $request->message;
        $modelOverride = $request->model_ai;
        $ragData = $ragService->getKonteks($query, 5, includeNvd: true);
        $konteks = $ragData['konteks'];
        
        $prompt = <<<PROMPT
SYSTEM:
Anda adalah AI Assistant RedSim.
PENTING: Seluruh jawaban WAJIB menggunakan BAHASA INDONESIA yang baku dan profesional, tidak boleh menggunakan bahasa Inggris.
Berikan jawaban yang panjang, komprehensif, dan sedetail mungkin sesuai dengan informasi yang ada.
Susun jawaban Anda dengan rapi menggunakan Markdown. 
Gunakan penomoran (1, 2, 3), cetak tebal (**teks**), atau garis miring (*teks*) untuk memperjelas informasi.
Jika Anda menyertakan kode, letakkan di dalam blok kode Markdown (```).

Gunakan hanya informasi dari context yang diberikan.
Jika informasi tidak ditemukan dalam context, katakan bahwa data tidak tersedia.

CONTEXT:
{$konteks}

QUESTION:
{$query}
PROMPT;
        
        try {
            $response = $geminiService->generate($prompt, $modelOverride, false, \Illuminate\Support\Facades\Auth::id());
            
            $generalCount = count($ragData['general_chunks'] ?? []);
            $nvdCount = count($ragData['nvd_chunks'] ?? []);
            $nvdList = array_map(fn($c) => $c['title'] ?? ($c['source_id'] ?? 'CVE'), $ragData['nvd_chunks'] ?? []);
            $generalList = array_map(fn($c) => $c['title'] ?? ($c['source_id'] ?? 'Doc'), $ragData['general_chunks'] ?? []);

            return response()->json([
                'status' => 'success',
                'message' => $response,
                'rag_used' => !empty($konteks),
                'rag_details' => [
                    'general_count' => $generalCount,
                    'nvd_count' => $nvdCount,
                    'general_sources' => array_values(array_unique(array_filter($generalList))),
                    'nvd_cves' => array_values(array_unique(array_filter($nvdList))),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'AI Provider error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function ragDebug(Request $request, RAGService $ragService)
    {
        $request->validate(['query' => 'required|string']);
        $query = $request->input('query');
        
        $ragData = $ragService->getKonteks($query, 5);
        
        return response()->json([
            'query' => $query,
            'estimated_tokens' => $ragData['estimasi_token'],
            'total_chunks_retrieved' => count($ragData['chunks']),
            'chunks' => array_map(function($chunk) {
                return [
                    'id' => $chunk['id'] ?? null,
                    'title' => $chunk['title'] ?? 'N/A',
                    'similarity_score' => $chunk['sem_score'] ?? 0,
                    'final_score' => $chunk['final_score'] ?? 0,
                    'content_length' => isset($chunk['content']) ? strlen($chunk['content']) : 0,
                ];
            }, $ragData['chunks']),
            'warning' => $ragData['peringatan']
        ], 200, [], JSON_PRETTY_PRINT);
    }
}
