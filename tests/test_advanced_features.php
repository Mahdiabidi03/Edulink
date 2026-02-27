<?php
/**
 * Advanced Assistance Features — Integration Test Script
 *
 * Tests: Video Call, Smart Matching, File Sharing, Stats Dashboard
 * Run: php tests/test_advanced_features.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

echo "═══════════════════════════════════════════════════\n";
echo "  ADVANCED ASSISTANCE FEATURES — TEST SUITE\n";
echo "═══════════════════════════════════════════════════\n\n";

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $result = $fn();
        if ($result === true) {
            echo "  ✅ PASS: $name\n";
            $passed++;
        } else {
            echo "  ❌ FAIL: $name — returned: " . var_export($result, true) . "\n";
            $failed++;
        }
    } catch (\Throwable $e) {
        echo "  ❌ FAIL: $name — " . $e->getMessage() . "\n";
        $failed++;
    }
}

// ══════════════════════════════════════════════════
// FEATURE 1: Jitsi Video Call
// ══════════════════════════════════════════════════
echo "── Feature 1: Jitsi Video Call ──\n";

test('Session entity has jitsiRoomId property', function () {
    $refClass = new ReflectionClass(\App\Entity\Session::class);
    return $refClass->hasProperty('jitsiRoomId');
});

test('Session entity has getJitsiRoomId method', function () {
    $refClass = new ReflectionClass(\App\Entity\Session::class);
    return $refClass->hasMethod('getJitsiRoomId');
});

test('Session entity has setJitsiRoomId method', function () {
    $refClass = new ReflectionClass(\App\Entity\Session::class);
    return $refClass->hasMethod('setJitsiRoomId');
});

test('JitsiRoomId getter/setter work correctly', function () {
    $session = new \App\Entity\Session();
    $session->setJitsiRoomId('test-room-123');
    return $session->getJitsiRoomId() === 'test-room-123';
});

test('Chat template has Jitsi video call button', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/chat.html.twig');
    return str_contains($template, 'video-call-btn') && str_contains($template, 'jitsiOverlay');
});

test('Chat template has Jitsi iframe modal', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/chat.html.twig');
    return str_contains($template, 'meet.jit.si') && str_contains($template, 'display-capture');
});

// ══════════════════════════════════════════════════
// FEATURE 2: AI Smart Matching
// ══════════════════════════════════════════════════
echo "\n── Feature 2: AI Smart Matching ──\n";

test('SmartMatchingService class exists', function () {
    return class_exists(\App\Service\SmartMatchingService::class);
});

test('SmartMatchingService has findBestTutors method', function () {
    $refClass = new ReflectionClass(\App\Service\SmartMatchingService::class);
    return $refClass->hasMethod('findBestTutors');
});

test('SmartMatchingService has calculateScore method', function () {
    $refClass = new ReflectionClass(\App\Service\SmartMatchingService::class);
    return $refClass->hasMethod('calculateScore');
});

test('Controller uses SmartMatchingService in new()', function () {
    $code = file_get_contents(__DIR__ . '/../src/Controller/HelpRequestController.php');
    return str_contains($code, 'SmartMatchingService') && str_contains($code, 'findBestTutors');
});

test('Show template has recommended tutors section', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/show.html.twig');
    return str_contains($template, 'suggested_tutors') && str_contains($template, 'AI Recommended Tutors');
});

test('Show template shows match score, rating, availability', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/show.html.twig');
    return str_contains($template, '% Match')
        && str_contains($template, 'avgRating')
        && str_contains($template, 'Available now')
        && str_contains($template, 'completedSessions');
});

// ══════════════════════════════════════════════════
// FEATURE 3: File/Image Sharing in Chat
// ══════════════════════════════════════════════════
echo "\n── Feature 3: File/Image Sharing ──\n";

test('Message entity has VichUploadable annotation', function () {
    $code = file_get_contents(__DIR__ . '/../src/Entity/Message.php');
    return str_contains($code, 'Vich\\Uploadable') && str_contains($code, 'attachmentFile');
});

test('MessageType form has attachmentFile field', function () {
    $code = file_get_contents(__DIR__ . '/../src/Form/MessageType.php');
    return str_contains($code, "add('attachmentFile'") && str_contains($code, 'VichFileType');
});

test('Chat template has file upload button', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/chat.html.twig');
    return str_contains($template, 'file-upload-btn')
        && str_contains($template, 'ri-attachment-2')
        && str_contains($template, 'multipart/form-data');
});

test('Chat template shows image attachments', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/chat.html.twig');
    return str_contains($template, 'msg-attachment')
        && str_contains($template, '/uploads/chat/')
        && str_contains($template, "['jpg', 'jpeg', 'png', 'gif', 'webp']");
});

test('Chat template shows file download links', function () {
    $template = file_get_contents(__DIR__ . '/../templates/help_request/chat.html.twig');
    return str_contains($template, 'msg-attachment-file') && str_contains($template, 'ri-file-download-line');
});

test('VichUploader config has chat_attachments mapping', function () {
    $config = file_get_contents(__DIR__ . '/../config/packages/vich_uploader.yaml');
    return str_contains($config, 'chat_attachments') && str_contains($config, '/uploads/chat');
});

// ══════════════════════════════════════════════════
// FEATURE 4: Statistics Dashboard
// ══════════════════════════════════════════════════
echo "\n── Feature 4: Statistics Dashboard ──\n";

test('AssistanceController has statsDashboard route', function () {
    $code = file_get_contents(__DIR__ . '/../src/Controller/Admin/AssistanceController.php');
    return str_contains($code, 'stats-dashboard') && str_contains($code, 'statsDashboard');
});

test('HelpRequestRepository has getMonthlyRequestCounts', function () {
    $refClass = new ReflectionClass(\App\Repository\HelpRequestRepository::class);
    return $refClass->hasMethod('getMonthlyRequestCounts');
});

test('HelpRequestRepository has getCategoryDistribution', function () {
    $refClass = new ReflectionClass(\App\Repository\HelpRequestRepository::class);
    return $refClass->hasMethod('getCategoryDistribution');
});

test('HelpRequestRepository has getResolutionBreakdown', function () {
    $refClass = new ReflectionClass(\App\Repository\HelpRequestRepository::class);
    return $refClass->hasMethod('getResolutionBreakdown');
});

test('HelpRequestRepository has getTopTutors', function () {
    $refClass = new ReflectionClass(\App\Repository\HelpRequestRepository::class);
    return $refClass->hasMethod('getTopTutors');
});

test('Stats template exists with Chart.js', function () {
    $template = file_get_contents(__DIR__ . '/../templates/admin/assistance/stats.html.twig');
    return str_contains($template, 'chart.js')
        && str_contains($template, 'monthlyChart')
        && str_contains($template, 'categoryChart')
        && str_contains($template, 'resolutionChart');
});

test('Stats template has tutor leaderboard', function () {
    $template = file_get_contents(__DIR__ . '/../templates/admin/assistance/stats.html.twig');
    return str_contains($template, 'leaderboard') && str_contains($template, 'Top Tutors') && str_contains($template, 'leaderboard-rank');
});

test('Admin index has link to stats dashboard', function () {
    $template = file_get_contents(__DIR__ . '/../templates/admin/assistance/index.html.twig');
    return str_contains($template, 'app_admin_assistance_stats') && str_contains($template, 'Stats Dashboard');
});

// ══════════════════════════════════════════════════
// SUMMARY
// ══════════════════════════════════════════════════
echo "\n═══════════════════════════════════════════════════\n";
echo "  RESULTS: $passed passed, $failed failed\n";
echo "═══════════════════════════════════════════════════\n";

exit($failed > 0 ? 1 : 0);
