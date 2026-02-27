<?php
// Quick user seeder — run: php seed_users.php
require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$dsn = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
if (!$dsn) {
    die("DATABASE_URL not found\n");
}

// Parse DSN
preg_match('/mysql:\/\/([^:]+):([^@]*)@([^:]+):?(\d+)?\/(.+?)(\?|$)/', $dsn, $m);
$pdo = new PDO("mysql:host={$m[3]};port=" . ($m[4] ?: 3306) . ";dbname={$m[5]}", $m[1], $m[2]);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash('123456', PASSWORD_BCRYPT);

$users = [
    ['admin@edulink.com', 'Admin User', $hash, '["ROLE_ADMIN"]', 100, 100],
    ['student@edulink.com', 'Dali Student', $hash, '["ROLE_STUDENT"]', 50, 50],
];

$stmt = $pdo->prepare('INSERT INTO user (email, full_name, password, roles, xp, wallet_balance) VALUES (?, ?, ?, ?, ?, ?)');

foreach ($users as $u) {
    try {
        $stmt->execute($u);
        echo "✅ Created: {$u[0]}\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "⏭️  Already exists: {$u[0]}\n";
        } else {
            echo "❌ Error for {$u[0]}: {$e->getMessage()}\n";
        }
    }
}

echo "\n🔑 Password for both: 123456\n";
