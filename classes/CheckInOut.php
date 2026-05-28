<?php
/**
 * CheckInOut Class - Digital check-in/check-out management
 */

class CheckInOut {
    
    /**
     * Generate digital key for check-in
     */
    public static function generateDigitalKey($reservationId) {
        $db = getDB();
        
        $reservation = Reservation::findById($reservationId);
        if (!$reservation) {
            throw new Exception("Reservation not found");
        }
        
        // Generate unique key ID and PIN
        $keyId = 'DK_' . date('Y') . '_' . str_pad($reservationId, 4, '0', STR_PAD_LEFT);
        $pin = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // QR code would be generated here in production
        $qrCode = 'https://amnen.local/checkin/' . $keyId;
        
        // Store digital key
        $stmt = $db->prepare("
            INSERT INTO digital_keys (
                key_id, reservation_id, room_id, pin, qr_code,
                valid_from, valid_until, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ON DUPLICATE KEY UPDATE pin = ?, valid_from = ?, valid_until = ?, status = 'active'
        ");
        
        $validFrom = $reservation['check_in_date'] . ' 14:00:00';
        $validUntil = $reservation['check_out_date'] . ' 11:00:00';
        
        $stmt->execute([
            $keyId, $reservationId, $reservation['room_id'], $pin, $qrCode,
            $validFrom, $validUntil,
            $pin, $validFrom, $validUntil
        ]);
        
        return [
            'key_id' => $keyId,
            'reservation_id' => $reservationId,
            'room_id' => $reservation['room_id'],
            'room_number' => $reservation['room_number'],
            'pin' => $pin,
            'qr_code' => $qrCode,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'access_type' => 'room_entry'
        ];
    }
    
    /**
     * Process check-in
     */
    public static function processCheckIn($reservationId, $method = 'manual') {
        $reservation = Reservation::findById($reservationId);
        
        if (!$reservation) {
            throw new Exception("Reservation not found");
        }
        
        if ($reservation['status'] !== 'confirmed') {
            throw new Exception("Reservation must be confirmed before check-in");
        }
        
        // Check if it's check-in day or later
        $today = date('Y-m-d');
        if ($reservation['check_in_date'] > $today) {
            throw new Exception("Check-in date has not arrived yet");
        }
        
        // Update reservation status
        Reservation::checkIn($reservationId);
        
        // Generate digital key
        $digitalKey = self::generateDigitalKey($reservationId);
        
        // Log check-in
        self::logActivity($reservationId, 'check_in', $method);
        
        return [
            'success' => true,
            'message' => 'Check-in successful',
            'digital_key' => $digitalKey,
            'room_info' => [
                'room_number' => $reservation['room_number'],
                'floor' => $reservation['floor'] ?? 1,
                'checkout_time' => '11:00'
            ]
        ];
    }
    
    /**
     * Process check-out
     */
    public static function processCheckOut($reservationId, $method = 'manual') {
        $reservation = Reservation::findById($reservationId);
        
        if (!$reservation) {
            throw new Exception("Reservation not found");
        }
        
        if ($reservation['status'] !== 'checked_in') {
            throw new Exception("Guest is not checked in");
        }
        
        // Update reservation status
        Reservation::checkOut($reservationId);
        
        // Deactivate digital key
        self::deactivateKey($reservationId);
        
        // Log check-out
        self::logActivity($reservationId, 'check_out', $method);
        
        return [
            'success' => true,
            'message' => 'Check-out successful',
            'can_leave_feedback' => true
        ];
    }
    
    /**
     * Deactivate digital key
     */
    public static function deactivateKey($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("UPDATE digital_keys SET status = 'expired' WHERE reservation_id = ?");
        return $stmt->execute([$reservationId]);
    }
    
    /**
     * Verify digital key PIN
     */
    public static function verifyPin($keyId, $pin) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT dk.*, r.status as reservation_status
            FROM digital_keys dk
            JOIN reservations r ON dk.reservation_id = r.reservation_id
            WHERE dk.key_id = ? AND dk.pin = ? AND dk.status = 'active'
        ");
        $stmt->execute([$keyId, $pin]);
        $key = $stmt->fetch();
        
        if (!$key) {
            return ['valid' => false, 'error' => 'Invalid key or PIN'];
        }
        
        $now = date('Y-m-d H:i:s');
        if ($now < $key['valid_from'] || $now > $key['valid_until']) {
            return ['valid' => false, 'error' => 'Key is not valid at this time'];
        }
        
        return ['valid' => true, 'room_id' => $key['room_id']];
    }
    
    /**
     * Get check-in list for today
     */
    public static function getTodayCheckIns() {
        return Reservation::getTodayCheckIns();
    }
    
    /**
     * Get check-out list for today
     */
    public static function getTodayCheckOuts() {
        return Reservation::getTodayCheckOuts();
    }
    
    /**
     * Log activity
     */
    private static function logActivity($reservationId, $action, $method) {
        $db = getDB();
        
        // Check if table exists, create if not
        $db->exec("
            CREATE TABLE IF NOT EXISTS checkin_log (
                log_id INT AUTO_INCREMENT PRIMARY KEY,
                reservation_id INT NOT NULL,
                action VARCHAR(50) NOT NULL,
                method VARCHAR(50) DEFAULT 'manual',
                performed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_reservation (reservation_id)
            )
        ");
        
        $stmt = $db->prepare("
            INSERT INTO checkin_log (reservation_id, action, method, performed_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$reservationId, $action, $method]);
    }
    
    /**
     * Clean up expired digital keys
     */
    public static function cleanupExpiredKeys() {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE digital_keys 
            SET status = 'expired' 
            WHERE status = 'active' AND valid_until < NOW()
        ");
        $stmt->execute();
        return $stmt->rowCount();
    }
}
