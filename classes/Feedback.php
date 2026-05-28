<?php
/**
 * Feedback Class - Handles guest reviews and ratings
 */

class Feedback {
    
    /**
     * Create feedback for a reservation
     */
    public static function create($data) {
        $db = getDB();
        
        // Check if feedback already exists for this reservation
        if (self::existsForReservation($data['reservation_id'])) {
            throw new Exception("Feedback already submitted for this reservation");
        }
        
        $stmt = $db->prepare("
            INSERT INTO feedback (
                user_id, reservation_id, rating, message, 
                service_type, is_public, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $data['user_id'],
            $data['reservation_id'],
            $data['rating'],
            $data['message'],
            $data['service_type'] ?? 'overall',
            $data['is_public'] ?? 0
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Check if feedback exists for reservation
     */
    public static function existsForReservation($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM feedback WHERE reservation_id = ?");
        $stmt->execute([$reservationId]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Find feedback by ID
     */
    public static function findById($feedbackId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT f.*, u.fname, u.lname, r.room_id,
                   rm.room_number, rm.room_type
            FROM feedback f
            JOIN users u ON f.user_id = u.user_id
            LEFT JOIN reservations r ON f.reservation_id = r.reservation_id
            LEFT JOIN rooms rm ON r.room_id = rm.room_id
            WHERE f.feedback_id = ?
        ");
        $stmt->execute([$feedbackId]);
        return $stmt->fetch();
    }
    
    /**
     * Find all feedback
     */
    public static function findAll($filters = []) {
        $db = getDB();
        
        $sql = "
            SELECT f.*, u.fname, u.lname,
                   rm.room_number, rm.room_type
            FROM feedback f
            JOIN users u ON f.user_id = u.user_id
            LEFT JOIN reservations r ON f.reservation_id = r.reservation_id
            LEFT JOIN rooms rm ON r.room_id = rm.room_id
            WHERE 1=1
        ";
        $params = [];
        
        if (!empty($filters['is_public'])) {
            $sql .= " AND f.is_public = 1";
        }
        
        if (!empty($filters['pending_reply'])) {
            $sql .= " AND (f.reply IS NULL OR f.reply = '')";
        }
        
        if (!empty($filters['service_type'])) {
            $sql .= " AND f.service_type = ?";
            $params[] = $filters['service_type'];
        }
        
        $sql .= " ORDER BY f.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Find feedback by user
     */
    public static function findByUser($userId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT f.*, rm.room_number, rm.room_type
            FROM feedback f
            LEFT JOIN reservations r ON f.reservation_id = r.reservation_id
            LEFT JOIN rooms rm ON r.room_id = rm.room_id
            WHERE f.user_id = ?
            ORDER BY f.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get public testimonials for homepage
     */
    public static function getPublicTestimonials($limit = 3) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT f.*, u.fname, u.lname
            FROM feedback f
            JOIN users u ON f.user_id = u.user_id
            WHERE f.is_public = 1 AND f.rating >= 4
            ORDER BY f.rating DESC, f.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Add staff reply to feedback
     */
    public static function addReply($feedbackId, $reply, $repliedBy) {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE feedback 
            SET reply = ?, replied_by = ?, replied_at = NOW() 
            WHERE feedback_id = ?
        ");
        return $stmt->execute([$reply, $repliedBy, $feedbackId]);
    }
    
    /**
     * Get feedback statistics
     */
    public static function getStats() {
        $db = getDB();
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total_reviews,
                AVG(rating) as avg_rating,
                COUNT(CASE WHEN rating = 5 THEN 1 END) as five_star,
                COUNT(CASE WHEN rating = 4 THEN 1 END) as four_star,
                COUNT(CASE WHEN rating = 3 THEN 1 END) as three_star,
                COUNT(CASE WHEN rating = 2 THEN 1 END) as two_star,
                COUNT(CASE WHEN rating = 1 THEN 1 END) as one_star
            FROM feedback
        ");
        return $stmt->fetch();
    }
    
    /**
     * Get unique customer count who left feedback
     */
    public static function getUniqueCustomerCount() {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(DISTINCT user_id) FROM feedback WHERE rating >= 4");
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Count pending replies
     */
    public static function countPendingReplies() {
        $db = getDB();
        $stmt = $db->query("SELECT COUNT(*) FROM feedback WHERE reply IS NULL OR reply = ''");
        return (int)$stmt->fetchColumn();
    }
}
