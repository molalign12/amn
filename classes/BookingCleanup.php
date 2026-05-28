<?php
/**
 * BookingCleanup Class - Automated cleanup of cancelled bookings
 */

class BookingCleanup {
    
    /**
     * Run cleanup process
     */
    public static function run($retentionDays = 7, $archiveMode = false) {
        $db = getDB();
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$retentionDays days"));
        $results = [
            'cancelled_bookings_deleted' => 0,
            'archived_bookings' => 0,
            'orphaned_keys_removed' => 0,
            'payments_cleaned' => 0
        ];
        
        try {
            $db->beginTransaction();
            
            if ($archiveMode) {
                // Archive cancelled bookings
                $results['archived_bookings'] = self::archiveCancelledBookings($cutoffDate);
            } else {
                // Delete cancelled bookings
                $results['cancelled_bookings_deleted'] = self::deleteCancelledBookings($cutoffDate);
            }
            
            // Clean up orphaned digital keys
            $results['orphaned_keys_removed'] = self::cleanupOrphanedKeys();
            
            // Clean up failed payment records for deleted reservations
            $results['payments_cleaned'] = self::cleanupOrphanedPayments();
            
            // Log the cleanup
            self::logCleanup($results, $retentionDays);
            
            $db->commit();
            
            return [
                'success' => true,
                'cleaned' => $results,
                'retention_days' => $retentionDays,
                'cutoff_date' => $cutoffDate
            ];
            
        } catch (Exception $e) {
            $db->rollBack();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete cancelled bookings older than cutoff
     */
    private static function deleteCancelledBookings($cutoffDate) {
        $db = getDB();
        
        // First delete related services
        $db->exec("
            DELETE rs FROM reservation_services rs
            INNER JOIN reservations r ON rs.reservation_id = r.reservation_id
            WHERE r.status = 'cancelled' AND r.created_at < '$cutoffDate'
        ");
        
        // Delete the reservations
        $stmt = $db->prepare("
            DELETE FROM reservations 
            WHERE status = 'cancelled' AND created_at < ?
        ");
        $stmt->execute([$cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Archive cancelled bookings (move to archive table)
     */
    private static function archiveCancelledBookings($cutoffDate) {
        $db = getDB();
        
        // Ensure archive table exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS reservations_archive LIKE reservations
        ");
        
        // Copy to archive
        $db->exec("
            INSERT INTO reservations_archive 
            SELECT * FROM reservations 
            WHERE status = 'cancelled' AND created_at < '$cutoffDate'
        ");
        
        // Delete from main table
        $stmt = $db->prepare("
            DELETE FROM reservations 
            WHERE status = 'cancelled' AND created_at < ?
        ");
        $stmt->execute([$cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Clean up orphaned digital keys
     */
    private static function cleanupOrphanedKeys() {
        $db = getDB();
        
        // Check if digital_keys table exists
        $tables = $db->query("SHOW TABLES LIKE 'digital_keys'")->fetchAll();
        if (empty($tables)) return 0;
        
        $stmt = $db->exec("
            DELETE dk FROM digital_keys dk
            LEFT JOIN reservations r ON dk.reservation_id = r.reservation_id
            WHERE r.reservation_id IS NULL OR dk.valid_until < NOW()
        ");
        
        return $stmt;
    }
    
    /**
     * Clean up orphaned payments
     */
    private static function cleanupOrphanedPayments() {
        $db = getDB();
        
        // Delete failed/pending payments for non-existent reservations
        $stmt = $db->exec("
            DELETE p FROM payments p
            LEFT JOIN reservations r ON p.reservation_id = r.reservation_id
            WHERE r.reservation_id IS NULL AND p.status IN ('pending', 'failed')
        ");
        
        return $stmt;
    }
    
    /**
     * Log cleanup activity
     */
    private static function logCleanup($results, $retentionDays) {
        $db = getDB();
        
        // Ensure log table exists
        $db->exec("
            CREATE TABLE IF NOT EXISTS cleanup_log (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                retention_days INT NOT NULL,
                cancelled_deleted INT DEFAULT 0,
                archived INT DEFAULT 0,
                keys_removed INT DEFAULT 0,
                payments_cleaned INT DEFAULT 0,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        $stmt = $db->prepare("
            INSERT INTO cleanup_log (
                retention_days, cancelled_deleted, archived, 
                keys_removed, payments_cleaned
            ) VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $retentionDays,
            $results['cancelled_bookings_deleted'],
            $results['archived_bookings'],
            $results['orphaned_keys_removed'],
            $results['payments_cleaned']
        ]);
    }
    
    /**
     * Get cleanup history
     */
    public static function getHistory($limit = 10) {
        $db = getDB();
        
        // Check if table exists
        $tables = $db->query("SHOW TABLES LIKE 'cleanup_log'")->fetchAll();
        if (empty($tables)) return [];
        
        $stmt = $db->prepare("
            SELECT * FROM cleanup_log 
            ORDER BY executed_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get cleanup statistics
     */
    public static function getStats() {
        $db = getDB();
        
        // Check if table exists
        $tables = $db->query("SHOW TABLES LIKE 'cleanup_log'")->fetchAll();
        if (empty($tables)) {
            return [
                'total_cleanups' => 0,
                'total_deleted' => 0,
                'total_archived' => 0,
                'last_cleanup' => null
            ];
        }
        
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total_cleanups,
                COALESCE(SUM(cancelled_deleted), 0) as total_deleted,
                COALESCE(SUM(archived), 0) as total_archived,
                MAX(executed_at) as last_cleanup
            FROM cleanup_log
        ");
        return $stmt->fetch();
    }
}
