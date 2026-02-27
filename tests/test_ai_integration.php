<?php
/**
 * Test script for AI Integration (GeminiEventService + GoogleMeetService)
 * 
 * Run: php tests/test_ai_integration.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load env
(new Dotenv())->bootEnv(__DIR__ . '/../.env');

echo "\n";
echo "╔══════════════════════════════════════════════════╗\n";
echo "║   EduLink AI Integration Test Suite              ║\n";
echo "╚══════════════════════════════════════════════════╝\n\n";

$passed = 0;
$failed = 0;
$total = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed, $total;
    $total++;
    try {
        $result = $fn();
        if ($result === true) {
            echo "  ✅ PASS: $name\n";
            $passed++;
        } else {
            echo "  ❌ FAIL: $name — $result\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "  ❌ FAIL: $name — Exception: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// ═══════════════════════════════════════════════
// TEST GROUP 1: GoogleMeetService
// ═══════════════════════════════════════════════
echo "── Google Meet Service ──────────────────────────\n\n";

$meetService = new \App\Service\GoogleMeetService();

test('Meet link has correct URL prefix', function () use ($meetService) {
    $link = $meetService->generateMeetLink('Test Event');
    return str_starts_with($link, 'https://meet.google.com/') ? true : "Got: $link";
});

test('Meet link follows xxx-xxxx-xxx format', function () use ($meetService) {
    $link = $meetService->generateMeetLink('PHP Hackathon 2026');
    $code = str_replace('https://meet.google.com/', '', $link);
    $pattern = '/^[a-z]{3}-[a-z]{4}-[a-z]{3}$/';
    return preg_match($pattern, $code) ? true : "Code '$code' doesn't match pattern xxx-xxxx-xxx";
});

test('Different calls produce different links', function () use ($meetService) {
    $link1 = $meetService->generateMeetLink('Event A');
    usleep(1000); // small delay to change microtime()
    $link2 = $meetService->generateMeetLink('Event B');
    return $link1 !== $link2 ? true : "Both links are identical: $link1";
});

test('Meet link works with special characters in title', function () use ($meetService) {
    $link = $meetService->generateMeetLink('Événement Spécial: C++ & Java "Edition"');
    $code = str_replace('https://meet.google.com/', '', $link);
    $pattern = '/^[a-z]{3}-[a-z]{4}-[a-z]{3}$/';
    return preg_match($pattern, $code) ? true : "Code '$code' doesn't match expected format";
});

test('Meet link works with empty string', function () use ($meetService) {
    $link = $meetService->generateMeetLink('');
    return str_starts_with($link, 'https://meet.google.com/') ? true : "Got: $link";
});

echo "\n";

// ═══════════════════════════════════════════════
// TEST GROUP 2: GeminiEventService (API Call)
// ═══════════════════════════════════════════════
echo "── Gemini Event Service (API) ───────────────────\n\n";

$apiKey = $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '';

// Create a real HTTP client using Symfony HttpClient
$httpClient = \Symfony\Component\HttpClient\HttpClient::create();

// Create a simple logger for tests
$logger = new class implements \Psr\Log\LoggerInterface {
    use \Psr\Log\LoggerTrait;
    public array $logs = [];
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->logs[] = ['level' => $level, 'message' => (string) $message];
    }
};

$geminiService = new \App\Service\GeminiEventService($httpClient, $apiKey, $logger);

test('API key is configured', function () use ($apiKey) {
    return !empty($apiKey) ? true : "GEMINI_API_KEY is empty in .env";
});

test('Generate description returns non-empty text', function () use ($geminiService) {
    $desc = $geminiService->generateDescription(
        'AI & Machine Learning Workshop',
        '2026-03-15 09:00',
        '2026-03-15 17:00',
        50
    );
    return strlen($desc) > 20 ? true : "Description too short: '$desc'";
});

test('Generated description is relevant to the event title', function () use ($geminiService) {
    $desc = $geminiService->generateDescription(
        'Web Development Hackathon',
        '2026-04-01 10:00',
        '2026-04-02 18:00',
        100
    );
    $descLower = strtolower($desc);
    $relevant = str_contains($descLower, 'web') || str_contains($descLower, 'develop')
        || str_contains($descLower, 'hackathon') || str_contains($descLower, 'code')
        || str_contains($descLower, 'build') || str_contains($descLower, 'learn')
        || str_contains($descLower, 'participant') || str_contains($descLower, 'event');
    return $relevant ? true : "Description doesn't seem relevant: '$desc'";
});

test('Description fallback works with invalid API key', function () use ($httpClient) {
    $badLogger = new class implements \Psr\Log\LoggerInterface {
        use \Psr\Log\LoggerTrait;
        public function log($level, string|\Stringable $message, array $context = []): void
        {
        }
    };
    $badService = new \App\Service\GeminiEventService($httpClient, 'INVALID_KEY_12345', $badLogger);
    $desc = $badService->generateDescription('Test', '2026-01-01', '2026-01-02', 10);
    return strlen($desc) > 10 ? true : "Fallback returned: '$desc'";
});

test('Fallback contains event title', function () use ($httpClient) {
    $badLogger = new class implements \Psr\Log\LoggerInterface {
        use \Psr\Log\LoggerTrait;
        public function log($level, string|\Stringable $message, array $context = []): void
        {
        }
    };
    $badService = new \App\Service\GeminiEventService($httpClient, '', $badLogger);
    $desc = $badService->generateDescription('Coding Marathon', '2026-01-01', '2026-01-02', 25);
    return str_contains($desc, 'Coding Marathon') ? true : "Fallback doesn't contain title: '$desc'";
});

echo "\n";

// ═══════════════════════════════════════════════
// SUMMARY
// ═══════════════════════════════════════════════
echo "══════════════════════════════════════════════════\n";
echo "  Results: $passed/$total passed";
if ($failed > 0) {
    echo " ($failed failed)";
}
echo "\n";
echo "══════════════════════════════════════════════════\n\n";

exit($failed > 0 ? 1 : 0);
