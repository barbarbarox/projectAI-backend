<?php

namespace App\Http\Controllers;

use App\Models\Temuan;
use App\Services\PayloadGeneratorService;
use Illuminate\Http\Request;

class PayloadController extends Controller
{
    protected PayloadGeneratorService $payloadService;

    public function __construct(PayloadGeneratorService $payloadService)
    {
        $this->payloadService = $payloadService;
    }

    public function generate(Request $request, Temuan $temuan)
    {
        // Pastikan scan milik user yang login
        if ($temuan->scan->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $payloadData = $this->payloadService->generateForTemuan($temuan, auth()->id());
            return response()->json($payloadData);
        } catch (\Exception $e) {
            \Log::error('Payload Generation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal meng-generate payload: ' . $e->getMessage()], 500);
        }
    }
}
