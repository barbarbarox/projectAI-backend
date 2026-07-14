<?php

namespace App\Http\Controllers;

use App\Models\AiConfiguration;
use App\Models\User;
use App\Models\Scan;
use App\Models\AuditLog;
use App\Services\AIProviderService;
use App\Services\SystemHealthService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_scans' => Scan::count(),
            'scans_hari_ini' => Scan::whereDate('created_at', today())->count(),
        ];
        $aiConfigs = AiConfiguration::orderByDesc('is_default')->get();

        return view('admin.index', compact('stats', 'aiConfigs'));
    }

    public function aiConfig()
    {
        $configs = AiConfiguration::orderByDesc('is_default')->get();
        return view('admin.ai-config', compact('configs'));
    }

    public function detectApiKey(Request $request, AIProviderService $providerService)
    {
        $request->validate(['api_key' => 'required|string|min:10'], [
            'api_key.required' => 'API key wajib diisi.',
            'api_key.min' => 'API key terlalu pendek.',
        ]);

        $result = $providerService->detectAndListModels($request->api_key);

        return response()->json($result);
    }

    public function storeAiConfig(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:10',
            'label' => 'required|string|max:100',
            'provider' => 'required|string',
            'detected_provider' => 'required|string',
            'available_models' => 'required|string',
            'selected_model' => 'required|string',
        ]);

        // Jika set sebagai default, hapus default lama
        if ($request->boolean('is_default')) {
            AiConfiguration::where('is_default', true)->update(['is_default' => false]);
        }

        AiConfiguration::create([
            'provider' => $request->provider,
            'label' => $request->label,
            'api_key' => $request->api_key,
            'detected_provider' => $request->detected_provider,
            'available_models' => json_decode($request->available_models, true),
            'selected_model' => $request->selected_model,
            'is_active' => true,
            'is_default' => $request->boolean('is_default'),
            'last_verified_at' => now(),
        ]);

        return back()->with('success', 'Konfigurasi AI berhasil disimpan.');
    }

    public function updateAiConfig(Request $request, AiConfiguration $config)
    {
        $request->validate(['selected_model' => 'required|string']);

        if ($request->boolean('is_default')) {
            AiConfiguration::where('is_default', true)->where('id', '!=', $config->id)->update(['is_default' => false]);
        }

        $config->update([
            'selected_model' => $request->selected_model,
            'is_active' => $request->boolean('is_active', true),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Konfigurasi diperbarui.');
    }

    public function deleteAiConfig(AiConfiguration $config)
    {
        $config->delete();
        return back()->with('success', 'Konfigurasi dihapus.');
    }

    public function kesehatanSistem(SystemHealthService $healthService)
    {
        // Hitung status pengguna
        // Dianggap online jika last_seen_at dalam 15 menit terakhir
        $onlineThreshold = now()->subMinutes(15);
        
        $usersOnline = User::where('last_seen_at', '>=', $onlineThreshold)->count();
        $usersOffline = User::where(function($query) use ($onlineThreshold) {
            $query->where('last_seen_at', '<', $onlineThreshold)
                  ->orWhereNull('last_seen_at');
        })->count();

        // Cek status API
        $healthStatus = $healthService->checkAll();

        return view('admin.kesehatan', compact('healthStatus', 'usersOnline', 'usersOffline'));
    }

    public function auditLog(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter berdasar tindakan
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter berdasar user (berdasarkan email atau nama)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate(15)->withQueryString();
        
        // Ambil unique actions untuk dropdown filter
        $actions = AuditLog::select('action')->distinct()->pluck('action');

        return view('admin.audit-log', compact('logs', 'actions'));
    }
}
