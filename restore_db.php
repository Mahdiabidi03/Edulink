<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=edulink', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents('restore.sql');
    
    // Split SQL into individual statements
    // This is a naive split, but might work for simple dumps with INSERTs
    // PDO->exec can verify multiple statements in one go depending on driver, but sometimes better to be explicit.
    // However, usually importing a dump is best done by exec()ing the whole thing if the driver supports multiple statements.
    // MySQL driver usually supports multiple statements if configured, but let's try raw exec first.
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec($sql);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Restore completed successfully.\n";
} catch (PDOException $e) {
    echo "Restore failed: " . $e->getMessage() . "\n";
    exit(1);
}
