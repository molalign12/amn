<?php
/**
 * Payment Class - Handles payment records and Chapa integration
 */

class Payment {
    
    /**
     * Create payment record
     */
    public static function create($data) {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO payments (
                reservation_id, amount, tx_ref, 
                payment_method, status, created_at
            ) VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['reservation_id'],
            $data['amount'],
            $data['tx_ref'],
            $data['payment_method'] ?? 'chapa',
            $data['status'] ?? 'pending'
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Find payment by ID
     */
    public static function findById($paymentId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        return $stmt->fetch();
    }
    
    /**
     * Find payment by transaction reference
     */
    public static function findByTxRef($txRef) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE tx_ref = ?");
        $stmt->execute([$txRef]);
        return $stmt->fetch();
    }
    
    /**
     * Find payments by reservation
     */
    public static function findByReservation($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM payments WHERE reservation_id = ? ORDER BY created_at DESC");
        $stmt->execute([$reservationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Update payment status
     */
    public static function updateStatus($paymentId, $status, $chapaRef = null) {
        $db = getDB();
        
        $sql = "UPDATE payments SET status = ?";
        $params = [$status];
        
        if ($chapaRef) {
            $sql .= ", chapa_reference = ?";
            $params[] = $chapaRef;
        }
        
        if ($status === 'completed') {
            $sql .= ", paid_at = NOW()";
        }
        
        $sql .= " WHERE payment_id = ?";
        $params[] = $paymentId;
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Update payment status by tx_ref
     */
    public static function updateStatusByTxRef($txRef, $status, $chapaRef = null) {
        $payment = self::findByTxRef($txRef);
        if ($payment) {
            return self::updateStatus($payment['payment_id'], $status, $chapaRef);
        }
        return false;
    }
    
    /**
     * Get total revenue
     */
    public static function getTotalRevenue($startDate = null, $endDate = null) {
        $db = getDB();
        
        $sql = "SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed'";
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
     * Get all payments
     */
    public static function findAll($filters = []) {
        $db = getDB();
        
        $sql = "
            SELECT p.*, r.check_in_date, r.check_out_date,
                   u.fname, u.lname, rm.room_number
            FROM payments p
            JOIN reservations r ON p.reservation_id = r.reservation_id
            JOIN users u ON r.user_id = u.user_id
            JOIN rooms rm ON r.room_id = rm.room_id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
