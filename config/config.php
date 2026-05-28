<?php
/**
 * AMNEN Guest House - Clean Architecture Configuration
 * Central configuration file for database, Fayda ID, Chapa payment, and app settings
 */

define('APP_NAME', 'AMNEN Guest House');
define('APP_VERSION', '2.0.0');
define('APP_ENV', getenv('APP_ENV') ?? 'development');

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?? 'localhost');
define('DB_USER', getenv('DB_USER') ?? 'root');
define('DB_PASS', getenv('DB_PASS') ?? '');
define('DB_NAME', getenv('DB_NAME') ?? 'amnen_hotel');
define('DB_PORT', getenv('DB_PORT') ?? 3306);

// Fayda ID Configuration
define('FAYDA_ID_ENABLED', getenv('FAYDA_ID_ENABLED') ?? true);
define('FAYDA_ID_API_KEY', getenv('FAYDA_ID_API_KEY') ?? '');
define('FAYDA_ID_API_URL', getenv('FAYDA_ID_API_URL') ?? 'https://api.fayda.id/v1');
define('FAYDA_ID_WEBHOOK_SECRET', getenv('FAYDA_ID_WEBHOOK_SECRET') ?? '');

// Chapa Payment Configuration
define('CHAPA_ENABLED', getenv('CHAPA_ENABLED') ?? true);
define('CHAPA_PUBLIC_KEY', getenv('CHAPA_PUBLIC_KEY') ?? '');
define('CHAPA_SECRET_KEY', getenv('CHAPA_SECRET_KEY') ?? '');
define('CHAPA_API_URL', getenv('CHAPA_API_URL') ?? 'https://api.chapa.co/v1');
define('CHAPA_CALLBACK_URL', getenv('APP_URL') ?? 'http://localhost/amnen' . '/api/webhooks/chapa-callback.php');

// Session Configuration
define('SESSION_LIFETIME', 3600 * 24); // 24 hours
define('SESSION_NAME', 'amnen_session_' . md5(APP_NAME));

// Set session save path (portable for all OS)
$sessionPath = sys_get_temp_dir() . '/amnen_sessions';
if (!is_dir($sessionPath) && is_writable(sys_get_temp_dir())) {
    mkdir($sessionPath, 0755, true);
}
if (is_dir($sessionPath) && is_writable($sessionPath)) {
    session_save_path($sessionPath);
}

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB

// Automated Check-in/Check-out Configuration
define('AUTO_CHECKIN_ENABLED', true);
define('AUTO_CHECKOUT_ENABLED', true);
define('CHECKIN_HOUR', 14); // 2 PM
define('CHECKOUT_HOUR', 11); // 11 AM

// Booking Cancellation Cleanup (days)
define('CANCELLED_BOOKING_RETENTION_DAYS', 7);
define('CLEANUP_CRON_ENABLED', true);

// Accessibility Features
define('ACCESSIBILITY_ENABLED', true);
define('SHOW_FLOOR_NUMBERS', true);
define('SHOW_ELEVATOR_ACCESS', true);

// Application Paths
define('BASE_PATH', __DIR__ . '/../');
define('APP_PATH', BASE_PATH . 'app/');
define('CONFIG_PATH', __DIR__);
define('VIEWS_PATH', BASE_PATH . 'views/');
define('PUBLIC_PATH', BASE_PATH . 'public/');
define('API_PATH', BASE_PATH . 'api/');
define('STORAGE_PATH', BASE_PATH . 'storage/');

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', STORAGE_PATH . 'logs/error.log');
}

// Timezone
date_default_timezone_set('Africa/Addis_Ababa');

// JWT Secret for API Authentication
define('JWT_SECRET', getenv('JWT_SECRET') ?? 'your-secret-key-change-in-production');
define('JWT_ALGORITHM', 'HS256');
