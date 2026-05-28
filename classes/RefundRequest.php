<?php
/**
 * RefundRequest Class - Handle refund requests
 */

class RefundRequest {
    
    /**
     * Create refund request
     */
    public static function create($data) {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO refund_requests (
                reservation_id, user_id, amount, reason, 
                status, created_at
            ) VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        
        $stmt->execute([
            $data['reservation_id'],
            $data['user_id'],
            $data['amount'],
            $data['reason'] ?? ''
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Find by ID
     */
    public static function findById($id) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT rr.*, r.room_id, r.check_in_date, r.check_out_date, r.total_price,
                   u.fname, u.lname, u.email, u.phone,
                   rm.room_number
            FROM refund_requests rr
            JOIN reservations r ON rr.reservation_id = r.reservation_id
            JOIN users u ON rr.user_id = u.user_id
            JOIN rooms rm ON r.room_id = rm.room_id
            WHERE rr.refund_id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Find by user
     */
    public static function findByUser($userId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT rr.*, r.check_in_date, r.check_out_date, rm.room_number
            FROM refund_requests rr
            JOIN reservations r ON rr.reservation_id = r.reservation_id
            JOIN rooms rm ON r.room_id = rm.room_id
            WHERE rr.user_id = ?
            ORDER BY rr.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Find all refund requests
     */
    public static function findAll($status = null) {
        $db = getDB();
        
        $sql = "
            SELECT rr.*, r.check_in_date, r.check_out_date, r.total_price,
                   u.fname, u.lname, u.email, rm.room_number
            FROM refund_requests rr
            JOIN reservations r ON rr.reservation_id = r.reservation_id
            JOIN users u ON rr.user_id = u.user_id
            JOIN rooms rm ON r.room_id = rm.room_id
        ";
        
        if ($status) {
            $sql .= " WHERE rr.status = ?";
            $stmt = $db->prepare($sql . " ORDER BY rr.created_at DESC");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query($sql . " ORDER BY rr.created_at DESC");
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Approve refund
     */
    public static function approve($refundId, $managerId, $note = '') {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE refund_requests 
            SET status = 'approved', manager_note = ?, approved_by = ?, processed_at = NOW()
            WHERE refund_id = ?
        ");
        $result = $stmt->execute([$note, $managerId, $refundId]);
        
        if ($result) {
            // Update payment status to refunded
            $refund = self::findById($refundId);
            if ($refund) {
                $db->prepare("
                    UPDATE payments SET status = 'refunded' 
                    WHERE reservation_id = ?
                ")->execute([$refund['reservation_id']]);
            }
        }
        
        return $result;
    }
    
    /**
     * Reject refund
     */
    public static function reject($refundId, $managerId, $note = '') {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE refund_requests 
            SET status = 'rejected', manager_note = ?, approved_by = ?, processed_at = NOW()
            WHERE refund_id = ?
        ");
        return $stmt->execute([$note, $managerId, $refundId]);
    }
    
    /**
     * Count pending refunds
     */
    public static function countPending() {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) FROM refund_requests WHERE status = 'pending'");
        return (int)$stmt->fetchColumn();
    }
}
