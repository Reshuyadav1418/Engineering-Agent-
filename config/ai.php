<?php

return [
    'provider' => env('AI_PROVIDER', 'ollama'),

    'ollama' => [
        'endpoint' => env('OLLAMA_ENDPOINT', 'http://localhost:11434'),
        'model' => env('OLLAMA_MODEL', 'llama3.2:1b'),
    ],
];
