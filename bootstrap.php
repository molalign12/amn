<?php
/**
 * AMNEN Hotel - Bootstrap Loader
 * Loads all dependencies in correct order
 */

// Prevent output before headers
if (ob_get_level() == 0) ob_start();

// Security headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
}

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load database connection
require_once __DIR__ . '/config/db.php';

// Load all classes
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/Room.php';
require_once __DIR__ . '/classes/Reservation.php';
require_once __DIR__ . '/classes/Payment.php';
require_once __DIR__ . '/classes/Feedback.php';

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error handling
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $isDev = APP_ENV === 'development';
    
    if ($isDev) {
        echo "Error [$errno]: $errstr in $errfile:$errline";
    } else {
        error_log("[$errno] $errstr in $errfile:$errline");
    }
    
    return true;
});

// All systems ready
define('AMNEN_BOOTSTRAPPED', true);
