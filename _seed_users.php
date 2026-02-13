<?php
require_once __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('mysql:host=127.0.0.1;dbname=edulink;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Hash password using bcrypt (Symfony default)
$hashedPassword = password_hash('123456', PASSWORD_BCRYPT);

$users = [
    [
        'email'    => 'admin@edulink.com',
        'fullName' => 'Admin User',
        'roles'    => json_encode(['ROLE_ADMIN']),
    ],
    [
        'email'    => 'student1@edulink.com',
        'fullName' => 'Student One',
        'roles'    => json_encode(['ROLE_STUDENT']),
    ],
    [
        'email'    => 'student2@edulink.com',
        'fullName' => 'Student Two',
        'roles'    => json_encode(['ROLE_STUDENT']),
    ],
];

$stmt = $pdo->prepare('INSERT INTO user (email, full_name, password, roles, xp, wallet_balance, face_descriptor) VALUES (:email, :fullName, :password, :roles, 0, 0, NULL)');

foreach ($users as $user) {
    $stmt->execute([
        ':email'    => $user['email'],
        ':fullName' => $user['fullName'],
        ':password' => $hashedPassword,
        ':roles'    => $user['roles'],
    ]);
    echo "Created: {$user['email']} ({$user['roles']})\n";
}

echo "\nAll 3 users created with password: 123456\n";
