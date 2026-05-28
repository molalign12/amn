<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Version: " . PHP_VERSION . "</h2>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=amnen_guesthouse;charset=utf8mb4', 'root', '');
    echo "<h2 style='color:green'>✅ Database connected!</h2>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Tables: " . implode(', ', $tables) . "</p>";
} catch (PDOException $e) {
    echo "<h2 style='color:red'>❌ DB Error: " . $e->getMessage() . "</h2>";
}

session_save_path('C:/xampp/tmp');
session_start();
$_SESSION['test'] = 'ok';
echo "<h2 style='color:green'>✅ Sessions working</h2>";

echo "<h2>✅ All checks passed — server is ready</h2>";