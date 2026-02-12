<?php

use Symfony\Component\HttpClient\HttpClient;

require_once __DIR__ . '/../vendor/autoload.php';

// Manually load .env to get the key (simple regex parse for this test script)
$envFile = __DIR__ . '/../.env';
$envContent = file_get_contents($envFile);
preg_match('/GEMINI_API_KEY=(.*)/', $envContent, $matches);
$apiKey = trim($matches[1] ?? '');

if (empty($apiKey)) {
    die("Error: GEMINI_API_KEY not found in .env\n");
}

echo "Testing Gemini API with key: " . substr($apiKey, 0, 5) . "...\n";

$client = HttpClient::create();
$models = ['gemini-1.5-flash', 'gemini-pro', 'gemini-1.0-pro'];

foreach ($models as $model) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/$model:generateContent?key=$apiKey";
    echo "Trying model: $model\n";
    echo "URL: $url\n";

    try {
        $response = $client->request('POST', $url, [
            'json' => [
                'contents' => [
                    ['parts' => [['text' => 'Say hello']]]
                ]
            ]
        ]);

        $statusCode = $response->getStatusCode();
        echo "Status: $statusCode\n";
        
        if ($statusCode === 200) {
            echo "Success! Response: " . substr($response->getContent(), 0, 100) . "...\n";
            exit(0); // Found a working one
        } else {
            echo "Error content: " . $response->getContent(false) . "\n";
        }
    } catch (\Throwable $e) {
        echo "Exception: " . $e->getMessage() . "\n";
    }
    echo "--------------------------------\n";
}
