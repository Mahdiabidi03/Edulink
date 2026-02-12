<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Connection Diagnostic</h1>";

// 1. Check Extensions
echo "<h2>1. Extension Check</h2>";
$extensions = get_loaded_extensions();
echo "Loaded Extensions: " . count($extensions) . "<br>";
if (in_array('pdo_mysql', $extensions)) {
    echo "<span style='color:green'>✅ pdo_mysql is LOADED.</span><br>";
} else {
    echo "<span style='color:red'>❌ pdo_mysql is NOT LOADED.</span><br>";
    echo "This is the generic cause of 'could not find driver'.<br>";
    echo "Check your php.ini file: <strong>" . php_ini_loaded_file() . "</strong><br>";
    echo "Make sure <code>extension=pdo_mysql</code> is uncommented.<br>";
}

// 2. Extensions Directory
echo "<h2>2. Environment</h2>";
echo "Configuration File (php.ini): " . php_ini_loaded_file() . "<br>";
echo "Extension Directory: " . ini_get('extension_dir') . "<br>";

// 3. Attempt Connection
echo "<h2>3. Connection Attempt (PDO)</h2>";
$dsn = 'mysql:host=127.0.0.1;dbname=edulink;port=3306';
$user = 'root';
$password = ''; // Default XAMPP password is empty

try {
    $pdo = new PDO($dsn, $user, $password);
    echo "<span style='color:green'>✅ Database Connection SUCCESSFUL!</span><br>";
    echo "Database is accessible via raw PHP.<br>";
} catch (PDOException $e) {
    echo "<span style='color:red'>❌ Connection FAILED</span><br>";
    echo "Error: " . $e->getMessage() . "<br>";
}
