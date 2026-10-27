<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$key = env('GEMINI_API_KEY');
$response = Illuminate\Support\Facades\Http::get("https://generativelanguage.googleapis.com/v1beta/models?key={$key}");
$data = $response->json();
$models = [];
foreach($data['models'] as $m) {
    if(str_contains($m['name'], 'gemini')) {
        $models[] = [
            'id' => $m['name'],
            'displayName' => $m['displayName'] ?? '',
            'supported' => $m['supportedGenerationMethods'] ?? []
        ];
    }
}
print_r($models);
