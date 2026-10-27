<?php

return [
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    // Pilar 1 — ML Prediction Service (Python FastAPI)
    'ml_service' => [
        'url'     => env('ML_SERVICE_URL', 'http://127.0.0.1:5001'),
        'enabled' => env('ML_SERVICE_ENABLED', true),
        'timeout' => env('ML_SERVICE_TIMEOUT', 5),
    ],

    // Pinecone Vector Database (Dual RAG)
    'pinecone' => [
        'api_key'      => env('PINECONE_API_KEY', ''),
        'index'        => env('PINECONE_INDEX', 'redsim-rag'),
        'environment'  => env('PINECONE_ENVIRONMENT', 'us-east-1'),
        'nvd_api_key'  => env('PINECONE_NVD_API_KEY', env('PINECONE_API_KEY', '')),
        'nvd_index'    => env('PINECONE_NVD_INDEX', 'redsim-nvd-cve'),
    ],

    // VirusTotal API
    'virustotal' => [
        'api_key' => env('VIRUSTOTAL_API_KEY', ''),
    ],

    // URLScan API
    'urlscan' => [
        'api_key' => env('URLSCAN_API_KEY', ''),
    ],

    // Google Gemini API
    'gemini' => [
        'api_key' => env('GOOGLE_GEMINI_API_KEY', env('GEMINI_API_KEY', '')),
        'model'   => env('GEMINI_MODEL', 'gemini-1.5-pro'),
    ],

    // HuggingFace Embeddings
    'huggingface' => [
        'api_token' => env('HUGGINGFACE_API_TOKEN', ''),
        'model'     => env('HUGGINGFACE_MODEL', 'intfloat/multilingual-e5-base'),
    ],

    // Fonnte WhatsApp Notification
    'fonnte' => [
        'token' => env('FONNTE_TOKEN', ''),
    ],
];

