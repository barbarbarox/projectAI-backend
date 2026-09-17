<?php
$apiKey = "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";
$payload = json_encode([
    'model' => 'deepseek-ai/deepseek-v4-pro',
    'messages' => [['role' => 'user', 'content' => 'test, apa kabar?']],
    'temperature' => 1,
    'top_p' => 0.95,
    'max_tokens' => 1024,
    'stream' => false
]);
$ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo $response;
}
curl_close($ch);
