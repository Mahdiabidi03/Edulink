<?php
require_once __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$dsn = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
preg_match('/mysql:\/\/([^:]+):([^@]*)@([^:]+):?(\d+)?\/(.+?)(\?|$)/', $dsn, $m);
$pdo = new PDO("mysql:host={$m[3]};port=" . ($m[4] ?: 3306) . ";dbname={$m[5]}", $m[1], $m[2]);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check existing users
echo "=== Users ===\n";
$rows = $pdo->query("SELECT id, email, full_name FROM user")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r)
    echo "  ID {$r['id']}: {$r['email']} ({$r['full_name']})\n";

// Check existing matieres
echo "\n=== Matieres ===\n";
$rows = $pdo->query("SELECT id, name FROM matiere")->fetchAll(PDO::FETCH_ASSOC);
if (empty($rows))
    echo "  (none)\n";
foreach ($rows as $r)
    echo "  ID {$r['id']}: {$r['name']}\n";

echo "\nNeed: matiere IDs 38,39,41,46 and user IDs 3,5\n";
