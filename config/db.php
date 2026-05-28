<?php
/**
 * Database Connection - PDO MySQL for XAMPP
 */

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'amnen_hotel';
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            // Try to create database if it doesn't exist
            try {
                $pdoTemp = new PDO("mysql:host=$host;charset=$charset", $username, $password, $options);
                $pdoTemp->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo = new PDO($dsn, $username, $password, $options);
                
                // Initialize schema
                initializeSchema($pdo);
            } catch (PDOException $e2) {
                die("Database connection failed: " . $e2->getMessage());
            }
        }
    }
    
    return $pdo;
}

function initializeSchema($pdo) {
    $schema = file_get_contents(__DIR__ . '/../sql/schema.sql');
    if ($schema) {
        $pdo->exec($schema);
    }
}

// Auto-initialize on first connection
try {
    $db = getDB();
    
    // Check if tables exist, if not create them
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($tables)) {
        $schemaFile = __DIR__ . '/../sql/schema.sql';
        if (file_exists($schemaFile)) {
            $schema = file_get_contents($schemaFile);
            $db->exec($schema);
        }
    }
} catch (Exception $e) {
    // Silently fail on first load if DB not set up
}
