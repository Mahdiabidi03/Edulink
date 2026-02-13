<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class GeminiService
{
    private string $apiKey;
    private HttpClientInterface $httpClient;
    private LoggerInterface $logger;
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-001:generateContent';

    public function __construct(string $geminiApiKey, HttpClientInterface $httpClient, LoggerInterface $logger)
    {
        $this->apiKey = $geminiApiKey;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
    }

    public function generateContent(string $prompt): string
    {
        if (empty($this->apiKey)) {
            // Ideally should assume it's set in env, or handle proactively.
            // For now, let's log error or return a message.
            return "Error: GEMINI_API_KEY is not configured.";
        }

        try {
            $response = $this->httpClient->request('POST', self::API_URL . '?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $content = $response->getContent(false);
                $this->logger->error("Gemini API Error ($statusCode): " . $content);
                return "Error: AI Service Failed ($statusCode) - " . substr($content, 0, 200);
            }

            $data = $response->toArray();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'No content generated.';

        } catch (\Exception $e) {
            $this->logger->error('Gemini Exception: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }

    // Specialized methods
    public function generateQuiz(string $text): string
    {
        $prompt = "Generate a multiple-choice quiz (5 questions) based on the following text. " .
                  "Format the output as JSON with keys: question, options (array), answer (index). " .
                  "Just return the JSON array, no markdown formatting. Text: \n\n" . substr($text, 0, 30000); // Limit context slightly
        
        return $this->generateContent($prompt);
    }

    public function generateSummary(string $text): string
    {
        $prompt = "Summarize the following text in 3-5 bullet points. Text: \n\n" . substr($text, 0, 30000);
        return $this->generateContent($prompt);
    }

    public function isToxic(string $text): bool
    {
        $bad = [
            'hate', 'racist', 'homophobic', 'sexist',
            'kill', 'bomb', 'terror', 'harass',
            'slur', 'offensive', 'insult',
        ];
        $lower = mb_strtolower($text);
        foreach ($bad as $w) {
            if (str_contains($lower, $w)) {
                return true;
            }
        }
        return false;
    }
}
