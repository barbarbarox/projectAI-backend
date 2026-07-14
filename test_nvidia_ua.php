<?php
$apiKey = "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";
$payload = json_encode([
    'model' => 'deepseek-ai/deepseek-v4-pro',
    'messages' => [['role' => 'user', 'content' => 'test, apa kabar?']],
    'max_tokens' => 10,
    'stream' => false
]);
$ch = curl_init('https://integrate.api.nvidia.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo 'Error: ' . curl_error($ch);
} else {
    echo "\nResponse:\n" . $response;
}
curl_close($ch);
