<?php
require_once __DIR__ . '/config/db.php';
$db = getDB();

try {
    $db->exec("ALTER TABLE rooms ADD COLUMN image_url VARCHAR(500) DEFAULT NULL;");
} catch(Exception $e) {}

try {
    $db->exec("ALTER TABLE feedback ADD COLUMN room_id INT UNSIGNED DEFAULT NULL;");
} catch(Exception $e) {}

try {
    $db->exec("ALTER TABLE feedback ADD COLUMN service_type ENUM('room','staff','food','overall','cleanliness') NOT NULL DEFAULT 'overall';");
} catch(Exception $e) {}

try {
    $db->exec("ALTER TABLE feedback ADD COLUMN is_public TINYINT(1) NOT NULL DEFAULT 1;");
} catch(Exception $e) {}

try {
    $db->exec("ALTER TABLE feedback ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;");
} catch(Exception $e) {}

try {
    $db->exec("ALTER TABLE feedback ADD CONSTRAINT fk_fb_room FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE SET NULL;");
} catch(Exception $e) {
    // Constraint might already exist
}

echo "Database migrations complete!";
