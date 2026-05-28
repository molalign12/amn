<?php
/**
 * Reservation Class - Handles all booking operations
 */

class Reservation {
    
    /**
     * Create a new reservation
     */
    public static function create($data) {
        $db = getDB();
        
        // Generate unique transaction reference
        $txRef = 'AMN-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        
        $stmt = $db->prepare("
            INSERT INTO reservations (
                user_id, room_id, check_in_date, check_out_date, 
                guests, total_price, special_requests, status, tx_ref, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['room_id'],
            $data['check_in_date'],
            $data['check_out_date'],
            $data['guests'] ?? 1,
            $data['total_price'],
            $data['special_requests'] ?? '',
            $data['status'] ?? 'pending',
            $txRef
        ]);
        
        $reservationId = $db->lastInsertId();
        
        // Mark room as reserved
        Room::updateStatus($data['room_id'], 'reserved');
        
        return $reservationId;
    }
    
    /**
     * Find reservation by ID
     */
    public static function findById($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT r.*, rm.room_number, rm.room_type, rm.price as room_price,
                   u.fname, u.lname, u.email, u.phone
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.room_id
            JOIN users u ON r.user_id = u.user_id
            WHERE r.reservation_id = ?
        ");
        $stmt->execute([$reservationId]);
        return $stmt->fetch();
    }
    
    /**
     * Find reservations by user
     */
    public static function findByUser($userId, $status = null) {
        $db = getDB();
        
        $sql = "
            SELECT r.*, rm.room_number, rm.room_type, rm.image_url
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.room_id
            WHERE r.user_id = ?
        ";
        $params = [$userId];
        
        if ($status) {
            if ($status === 'completed') {
                $sql .= " AND r.status = 'checked_out'";
            } elseif ($status === 'active') {
                $sql .= " AND r.status IN ('confirmed', 'checked_in')";
            } else {
                $sql .= " AND r.status = ?";
                $params[] = $status;
            }
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find all reservations with filters
     */
    public static function findAll($filters = []) {
        $db = getDB();
        
        $sql = "
            SELECT r.*, rm.room_number, rm.room_type,
                   u.fname, u.lname, u.email, u.phone
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.room_id
            JOIN users u ON r.user_id = u.user_id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND r.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['date'])) {
            $sql .= " AND (r.check_in_date = ? OR r.check_out_date = ?)";
            $params[] = $filters['date'];
            $params[] = $filters['date'];
        }
        
        if (!empty($filters['check_in_date'])) {
            $sql .= " AND r.check_in_date = ?";
            $params[] = $filters['check_in_date'];
        }
        
        if (!empty($filters['check_out_date'])) {
            $sql .= " AND r.check_out_date = ?";
            $params[] = $filters['check_out_date'];
        }
        
        $sql .= " ORDER BY r.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get today's check-ins
     */
    public static function getTodayCheckIns() {
        $today = date('Y-m-d');
        return self::findAll(['check_in_date' => $today, 'status' => 'confirmed']);
    }
    
    /**
     * Get today's check-outs
     */
    public static function getTodayCheckOuts() {
        $today = date('Y-m-d');
        $db = getDB();
        $stmt = $db->prepare("
            SELECT r.*, rm.room_number, rm.room_type,
                   u.fname, u.lname, u.email, u.phone
            FROM reservations r
            JOIN rooms rm ON r.room_id = rm.room_id
            JOIN users u ON r.user_id = u.user_id
            WHERE r.check_out_date = ? AND r.status = 'checked_in'
            ORDER BY r.created_at DESC
        ");
        $stmt->execute([$today]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update reservation status
     */
    public static function updateStatus($reservationId, $newStatus) {
        $db = getDB();
        
        // Get current reservation
        $reservation = self::findById($reservationId);
        if (!$reservation) return false;
        
        // Update reservation status
        $stmt = $db->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
        $stmt->execute([$newStatus, $reservationId]);
        
        // Update room status based on reservation status
        $roomStatus = match($newStatus) {
            'confirmed' => 'reserved',
            'checked_in' => 'occupied',
            'checked_out', 'cancelled' => 'available',
            default => null
        };
        
        if ($roomStatus) {
            Room::updateStatus($reservation['room_id'], $roomStatus);
        }
        
        return true;
    }
    
    /**
     * Cancel reservation
     */
    public static function cancel($reservationId) {
        return self::updateStatus($reservationId, 'cancelled');
    }
    
    /**
     * Check in
     */
    public static function checkIn($reservationId) {
        return self::updateStatus($reservationId, 'checked_in');
    }
    
    /**
     * Check out
     */
    public static function checkOut($reservationId) {
        return self::updateStatus($reservationId, 'checked_out');
    }
    
    /**
     * Confirm reservation (after payment)
     */
    public static function confirm($reservationId) {
        return self::updateStatus($reservationId, 'confirmed');
    }
    
    /**
     * Find by transaction reference
     */
    public static function findByTxRef($txRef) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM reservations WHERE tx_ref = ?");
        $stmt->execute([$txRef]);
        return $stmt->fetch();
    }
    
    /**
     * Count reservations by status
     */
    public static function countByStatus($status = null) {
        $db = getDB();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query("SELECT COUNT(*) FROM reservations");
        }
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Count today's check-ins
     */
    public static function countTodayCheckIns() {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE check_in_date = ? AND status = 'confirmed'");
        $stmt->execute([date('Y-m-d')]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Count today's check-outs
     */
    public static function countTodayCheckOuts() {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM reservations WHERE check_out_date = ? AND status = 'checked_in'");
        $stmt->execute([date('Y-m-d')]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Count guests in house (checked in)
     */
    public static function countGuestsInHouse() {
        $db = getDB();
        $stmt = $db->query("SELECT COALESCE(SUM(guests), 0) FROM reservations WHERE status = 'checked_in'");
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Get revenue for period
     */
    public static function getRevenue($startDate = null, $endDate = null) {
        $db = getDB();
        
        $sql = "SELECT COALESCE(SUM(total_price), 0) FROM reservations WHERE status IN ('confirmed', 'checked_in', 'checked_out')";
        $params = [];
        
        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }
        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return (float)$stmt->fetchColumn();
    }
    
    /**
     * Get monthly revenue for chart
     */
    public static function getMonthlyRevenue($year = null) {
        $db = getDB();
        $year = $year ?? date('Y');
        
        $stmt = $db->prepare("
            SELECT MONTH(created_at) as month, COALESCE(SUM(total_price), 0) as revenue
            FROM reservations 
            WHERE YEAR(created_at) = ? AND status IN ('confirmed', 'checked_in', 'checked_out')
            GROUP BY MONTH(created_at)
            ORDER BY month
        ");
        $stmt->execute([$year]);
        
        $results = $stmt->fetchAll();
        $monthly = array_fill(1, 12, 0);
        
        foreach ($results as $row) {
            $monthly[(int)$row['month']] = (float)$row['revenue'];
        }
        
        return $monthly;
    }
    
    /**
     * Clean up old cancelled reservations
     */
    public static function cleanupCancelled($retentionDays = 7) {
        $db = getDB();
        $cutoffDate = date('Y-m-d', strtotime("-$retentionDays days"));
        
        $stmt = $db->prepare("
            DELETE FROM reservations 
            WHERE status = 'cancelled' 
            AND created_at < ?
        ");
        $stmt->execute([$cutoffDate]);
        
        return $stmt->rowCount();
    }
}
