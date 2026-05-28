<?php
/**
 * Automated Booking Cleanup - Cron Job Script
 * 
 * Usage: php cleanup-cron.php [retention_days] [archive]
 * 
 * Example cron entry (runs daily at 3 AM):
 *   0 3 * * * cd /var/www/amnen && php cleanup-cron.php 7
 * 
 * With archiving instead of deletion:
 *   0 3 * * * cd /var/www/amnen && php cleanup-cron.php 7 archive
 */

// Prevent execution from web
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from command line');
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/BookingCleanup.php';

// Parse arguments
$retentionDays = (int) ($argv[1] ?? BookingCleanup::DEFAULT_RETENTION_DAYS);
$archiveInstead = isset($argv[2]) && strtolower($argv[2]) === 'archive';

// Validate retention days
if ($retentionDays < 1) {
    echo "[ERROR] Retention days must be at least 1\n";
    exit(1);
}

// Start execution
echo "[" . date('Y-m-d H:i:s') . "] Starting automated booking cleanup...\n";
echo "  Retention period: $retentionDays days\n";
echo "  Archive mode: " . ($archiveInstead ? "YES" : "NO") . "\n\n";

try {
    // Run full maintenance
    $result = BookingCleanup::fullMaintenance($retentionDays, $archiveInstead);

    if (!$result['success']) {
        throw new Exception('Maintenance failed');
    }

    // Report results
    echo "[" . date('Y-m-d H:i:s') . "] Cleanup completed successfully\n";
    echo "\nResults:\n";

    if (isset($result['results']['cancelled_bookings'])) {
        $cb = $result['results']['cancelled_bookings'];
        $action = $archiveInstead ? 'archived' : 'deleted';
        echo "  - Cancelled bookings $action: " . $cb['records_' . ($archiveInstead ? 'archived' : 'deleted')] . "\n";
    }

    if (isset($result['results']['orphaned_keys'])) {
        echo "  - Orphaned digital keys deleted: " . $result['results']['orphaned_keys'] . "\n";
    }

    if (isset($result['results']['orphaned_feedback'])) {
        echo "  - Orphaned feedback deleted: " . $result['results']['orphaned_feedback'] . "\n";
    }

    echo "\nCleanup job finished at " . $result['completed_at'] . "\n";
    exit(0);

} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage() . "\n";
    error_log("[Cleanup Cron] Error: " . $e->getMessage());
    exit(1);
}
