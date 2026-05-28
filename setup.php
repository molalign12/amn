<?php
/**
 * AMNEN Hotel - Setup & Initialization Helper
 * Run this once after putting files in htdocs/amnen/
 */

// Disable errors during setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>AMNEN Hotel Setup</title>";
echo "<style>body{font-family:Arial;background:#0D0D0D;color:#F5F0E8;margin:40px;line-height:1.6}";
echo ".success{color:#4ade80}.error{color:#f87171}.info{color:#60a5fa}h1{color:#F5F0E8}</style></head><body>";
echo "<h1>🏨 AMNEN Hotel Setup</h1>";

try {
    // Load environment file
    if (file_exists(__DIR__ . '/.env')) {
        $envContent = file_get_contents(__DIR__ . '/.env');
        preg_match_all('/^([A-Z_]+)=(.+)$/m', $envContent, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            putenv($match[1] . '=' . trim($match[2]));
        }
        echo "<p class='success'>✓ Loaded .env file</p>";
    }

    // Load config
    require_once __DIR__ . '/config/config.php';
    require_once __DIR__ . '/config/db.php';
    
    echo "<p class='success'>✓ Config loaded</p>";
    
    // Get database connection
    $db = getDB();
    echo "<p class='success'>✓ Database connection established</p>";
    
    // Check if tables exist
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "<p class='info'>→ Creating tables...</p>";
        
        $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
        if (!$schema) {
            throw new Exception("schema.sql not found!");
        }
        
        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));
        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $db->exec($statement);
            }
        }
        echo "<p class='success'>✓ Database tables created successfully</p>";
    } else {
        echo "<p class='info'>✓ Database tables already exist (" . count($tables) . " tables)</p>";
    }
    
    // Load classes
    require_once __DIR__ . '/classes/User.php';
    require_once __DIR__ . '/classes/Room.php';
    require_once __DIR__ . '/classes/Reservation.php';
    require_once __DIR__ . '/classes/Payment.php';
    require_once __DIR__ . '/classes/Feedback.php';
    
    echo "<p class='success'>✓ All classes loaded</p>";
    
    // Check for admin user
    $adminCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    if (!$adminCount) {
        // Create admin user
        User::create([
            'fname' => 'Admin',
            'lname' => 'User',
            'email' => 'admin@amnen.et',
            'phone' => '+251911111111',
            'username' => 'admin',
            'password' => 'Admin@123',
            'role' => 'admin'
        ]);
        echo "<p class='success'>✓ Admin user created (username: admin, password: Admin@123)</p>";
    } else {
        echo "<p class='info'>✓ Admin user already exists</p>";
    }
    
    echo "<hr>";
    echo "<h2>Setup Complete! 🎉</h2>";
    echo "<p class='success'><strong>Next Steps:</strong></p>";
    echo "<ul>";
    echo "<li>Navigate to <strong>/amnen/index.php</strong> to see the homepage</li>";
    echo "<li>Login with:<br>  Username: <strong>admin</strong><br>  Password: <strong>Admin@123</strong></li>";
    echo "<li>Change your password after first login</li>";
    echo "</ul>";
    
    echo "<p class='info' style='margin-top:20px'><strong>Database Info:</strong><br>";
    echo "Host: " . DB_HOST . "<br>";
    echo "Database: " . DB_NAME . "<br>";
    echo "Tables: " . count($db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN)) . "</p>";
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre style='background:#222;padding:10px;border-radius:4px'>";
    echo htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
}

echo "</body></html>";
