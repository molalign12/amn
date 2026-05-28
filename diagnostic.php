<?php
/**
 * AMNEN Hotel - Diagnostic Tool
 * Run this to check if everything is set up correctly
 */

header('Content-Type: text/html; charset=utf-8');
echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>";
echo "<title>AMNEN Hotel - Diagnostics</title>";
echo "<style>";
echo "body{font-family:Monospace;background:#0D0D0D;color:#F5F0E8;margin:20px;line-height:1.8}";
echo ".pass{color:#4ade80;font-weight:bold}";
echo ".fail{color:#f87171;font-weight:bold}";
echo ".warn{color:#fbbf24;font-weight:bold}";
echo ".info{color:#60a5fa}";
echo "pre{background:#222;padding:15px;border-radius:4px;overflow-x:auto}";
echo "h1,h2{color:#F5F0E8;border-bottom:2px solid #666;padding-bottom:10px}";
echo ".box{border:1px solid #666;padding:15px;margin:15px 0;border-radius:4px}";
echo "</style></head><body>";

echo "<h1>🏨 AMNEN Hotel - System Diagnostics</h1>";

$passes = 0;
$fails = 0;

// Check 1: PHP Version
echo "<div class='box'><h2>PHP Configuration</h2>";
$phpVersion = phpversion();
echo "PHP Version: <span class='info'>$phpVersion</span>";
if (version_compare($phpVersion, '7.4.0', '>=')) {
    echo " <span class='pass'>✓ OK</span><br>";
    $passes++;
} else {
    echo " <span class='fail'>✗ Too old (needs 7.4+)</span><br>";
    $fails++;
}
echo "</div>";

// Check 2: Required Extensions
echo "<div class='box'><h2>Required PHP Extensions</h2>";
$extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span class='pass'>✓</span> $ext<br>";
        $passes++;
    } else {
        echo "<span class='fail'>✗</span> $ext (missing)<br>";
        $fails++;
    }
}
echo "</div>";

// Check 3: Files & Directories
echo "<div class='box'><h2>File Structure</h2>";
$requiredFiles = [
    'config/config.php',
    'config/db.php',
    'classes/User.php',
    'classes/Room.php',
    'classes/Reservation.php',
    'classes/Payment.php',
    'classes/Feedback.php',
    'sql/schema.sql',
    '.env'
];
foreach ($requiredFiles as $file) {
    $fullPath = __DIR__ . '/' . $file;
    if (file_exists($fullPath)) {
        echo "<span class='pass'>✓</span> $file<br>";
        $passes++;
    } else {
        echo "<span class='fail'>✗</span> $file (not found)<br>";
        $fails++;
    }
}
echo "</div>";

// Check 4: Database Connection
echo "<div class='box'><h2>Database Connection</h2>";
try {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/config/config.php';
    
    $db = getDB();
    
    echo "<span class='pass'>✓</span> Connected to MySQL<br>";
    $passes++;
    
    // Check tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<span class='pass'>✓</span> Database has " . count($tables) . " tables<br>";
        $passes++;
        
        // List tables
        echo "<br><strong>Tables:</strong><pre>";
        foreach ($tables as $table) {
            echo "• $table\n";
        }
        echo "</pre>";
    } else {
        echo "<span class='warn'>⚠</span> No tables found - <strong>Run setup.php first!</strong><br>";
        $fails++;
    }
    
    // Check for admin user
    try {
        $admin = $db->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
        if ($admin > 0) {
            echo "<span class='pass'>✓</span> Admin user exists<br>";
            $passes++;
        } else {
            echo "<span class='warn'>⚠</span> No admin user - create one in setup.php<br>";
            $fails++;
        }
    } catch (Exception $e) {
        echo "<span class='warn'>⚠</span> Can't check users table<br>";
    }
    
} catch (Exception $e) {
    echo "<span class='fail'>✗</span> Database Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    $fails++;
}
echo "</div>";

// Check 5: Classes
echo "<div class='box'><h2>PHP Classes</h2>";
$classes = ['User', 'Room', 'Reservation', 'Payment', 'Feedback'];
foreach ($classes as $class) {
    try {
        require_once __DIR__ . '/classes/' . $class . '.php';
        if (class_exists($class)) {
            echo "<span class='pass'>✓</span> $class class loaded<br>";
            $passes++;
        } else {
            echo "<span class='fail'>✗</span> $class class not found<br>";
            $fails++;
        }
    } catch (Exception $e) {
        echo "<span class='fail'>✗</span> $class error: " . htmlspecialchars($e->getMessage()) . "<br>";
        $fails++;
    }
}
echo "</div>";

// Check 6: Session Configuration
echo "<div class='box'><h2>Session Configuration</h2>";
echo "Session save path: <span class='info'>" . ini_get('session.save_path') . "</span><br>";
if (ini_get('session.auto_start')) {
    echo "<span class='warn'>⚠</span> auto_start is ON (should be OFF)<br>";
} else {
    echo "<span class='pass'>✓</span> Session auto_start is OFF<br>";
    $passes++;
}
echo "</div>";

// Summary
echo "<div class='box' style='background:#1a1a1a;border:2px solid'>";
echo "<h2 style='border:none'>📊 Summary</h2>";
$total = $passes + $fails;
$percentage = $total > 0 ? round(($passes / $total) * 100) : 0;

echo "<div style='font-size:20px;margin:10px 0'>";
echo "Passed: <span class='pass'>$passes</span> | ";
echo "Failed: <span class='fail'>$fails</span> | ";
echo "Score: <span class='info'>$percentage%</span>";
echo "</div>";

if ($fails === 0 && $passes > 10) {
    echo "<p style='color:#4ade80;font-size:18px'>✅ <strong>All systems GO!</strong> You can start using AMNEN Hotel.</p>";
    echo "<p><a href='/amnen/setup.php' style='color:#60a5fa;text-decoration:underline'>→ Run setup.php if you haven't already</a></p>";
} elseif ($fails === 0) {
    echo "<p style='color:#fbbf24;font-size:16px'>⚠️ <strong>Setup needed</strong> - Run setup.php to initialize the database</p>";
    echo "<p><a href='/amnen/setup.php' style='color:#60a5fa;text-decoration:underline'>→ Click here to run setup</a></p>";
} else {
    echo "<p style='color:#f87171;font-size:16px'>❌ <strong>Issues found</strong> - Check the errors above</p>";
}

echo "</div>";
echo "</body></html>";
