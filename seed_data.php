<?php
require_once __DIR__ . '/vendor/autoload.php';
use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

$dsn = $_ENV['DATABASE_URL'] ?? $_SERVER['DATABASE_URL'] ?? null;
preg_match('/mysql:\/\/([^:]+):([^@]*)@([^:]+):?(\d+)?\/(.+?)(\?|$)/', $dsn, $m);
$pdo = new PDO("mysql:host={$m[3]};port=" . ($m[4] ?: 3306) . ";dbname={$m[5]}", $m[1], $m[2]);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Step 1: Seed missing matieres (IDs 38, 39, 41, 46)
echo "=== Seeding Matieres ===\n";
$matieres = [
    [38, 'Machine Learning', 'APPROVED', 3],
    [39, 'Mathematics', 'APPROVED', 3],
    [41, 'Programming', 'APPROVED', 3],
    [46, 'Data Science', 'APPROVED', 3],
];
$stmt = $pdo->prepare('INSERT INTO matiere (id, name, status, creator_id) VALUES (?, ?, ?, ?)');
foreach ($matieres as $mat) {
    try {
        $stmt->execute($mat);
        echo "  ✅ Matiere ID {$mat[0]}: {$mat[1]}\n";
    } catch (PDOException $e) {
        if ($e->getCode() == 23000)
            echo "  ⏭️  Already exists: {$mat[1]}\n";
        else
            echo "  ❌ Error: {$e->getMessage()}\n";
    }
}

// Step 2: Seed courses
echo "\n=== Seeding Courses ===\n";
$pdo->exec("INSERT INTO cours (id, title, description, level, price_points, xp, status, matiere_id, author_id, created_at) VALUES
    (22, 'ML Basics', 'Bayesian Linear Regression content inspired from Machine Learning and Pattern Recognition (Bishops)', 'Beginner', NULL, 5, 'APPROVED', 38, 3, '2026-02-02 02:20:58'),
    (23, 'AI Basics', 'In this course, you will learn what AI is and understand its applications and use cases and how it is transforming our lives.', 'Beginner', NULL, 5, 'APPROVED', 38, 3, '2026-02-02 02:23:10'),
    (24, 'Deep Learning', 'Beginner deep learning courses can help you learn neural networks, data preprocessing, model evaluation, and basic algorithms.', 'Beginner', NULL, 10, 'APPROVED', 38, 3, '2026-02-02 02:25:30'),
    (25, 'ML Techniques', 'Machine learning courses can help you learn data preprocessing, supervised and unsupervised learning, and model evaluation techniques.', 'Intermediate', NULL, 20, 'APPROVED', 38, 3, '2026-02-02 02:28:13'),
    (26, 'SPSS & IBM', 'This practical, hands-on course introduces IBM SPSS Statistics for data management, analysis, and visualization.', 'Intermediate', NULL, 30, 'APPROVED', 46, 3, '2026-02-02 02:37:15'),
    (27, 'Exploratory Data Analysis', 'This EDA course provides a foundational, hands-on approach to analyzing datasets to summarize their main characteristics.', 'Intermediate', NULL, 50, 'APPROVED', 46, 3, '2026-02-02 02:38:16'),
    (30, 'Python', 'Python courses can help you learn programming fundamentals, data analysis, web development, and automation techniques.', 'Intermediate', NULL, 50, 'APPROVED', 41, 3, '2026-02-12 00:06:06'),
    (31, 'C and C++', 'This course provides a fast-paced introduction to the C and C++ programming languages.', 'Intermediate', NULL, 50, 'APPROVED', 41, 3, '2026-02-12 00:06:31'),
    (32, 'Analyse Numérique', 'This course introduces mathematical methods designed to solve complex problems through numerical computation.', 'Intermediate', NULL, 70, 'APPROVED', 39, 5, '2026-02-12 09:47:31')
");
echo "  ✅ 9 courses inserted\n";

// Step 3: Seed enrollments
echo "\n=== Seeding Enrollments ===\n";
$pdo->exec("INSERT INTO enrollment (id, enrolled_at, progress, student_id, cours_id, completed_at, completed_resources) VALUES
    (10, '2026-02-11 23:50:15', 50, 5, 23, '2026-02-11 23:50:38', '[31]'),
    (11, '2026-02-12 00:17:50', 100, 5, 25, '2026-02-12 00:18:02', '[34,35]'),
    (12, '2026-02-12 00:43:53', 100, 5, 30, '2026-02-12 00:53:29', '[40,41]'),
    (13, '2026-02-12 10:36:45', 50, 5, 27, NULL, '[36]')
");
echo "  ✅ 4 enrollments inserted\n";

echo "\n🎉 All data seeded successfully!\n";
