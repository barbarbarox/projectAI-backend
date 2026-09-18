<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (str_starts_with(env('APP_URL', ''), 'https://')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $userId = \Illuminate\Support\Facades\Auth::id();
            $geminiService = app(\App\Services\GeminiService::class);
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
            
            $view->with(compact('availableModels', 'defaultModelName'));
        });
    }
}
