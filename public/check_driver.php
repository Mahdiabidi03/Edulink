<?php
echo "<h3>PHP Configuration Check</h3>";
echo "PHP Version: " . phpversion() . "<br/>";
echo "PDO Loaded: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br/>";
echo "PDO MySQL Loaded: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br/>";
echo "<hr>";
echo "<h3>PDO Drivers:</h3>";
if (extension_loaded('pdo')) {
    $drivers = PDO::getAvailableDrivers();
    if (empty($drivers)) {
        echo "No drivers found.<br/>";
    } else {
        echo "<ul>";
        foreach ($drivers as $driver) {
            echo "<li>" . $driver . "</li>";
        }
        echo "</ul>";
    }
}
echo "<hr>";
echo "Loaded Configuration File: " . php_ini_loaded_file();
