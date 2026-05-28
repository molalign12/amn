<?php
/**
 * Room Class - Handles all room operations
 */

class Room {
    
    /**
     * Find all rooms, optionally filtered by status
     */
    public static function findAll($status = null) {
        $db = getDB();
        
        if ($status) {
            $stmt = $db->prepare("SELECT * FROM rooms WHERE status = ? ORDER BY room_number");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query("SELECT * FROM rooms ORDER BY room_number");
        }
        
        $rooms = $stmt->fetchAll();
        
        // Parse amenities JSON for each room
        foreach ($rooms as &$room) {
            $room['amenities'] = json_decode($room['amenities'] ?? '[]', true) ?: [];
        }
        
        return $rooms;
    }
    
    /**
     * Find room by ID
     */
    public static function findById($roomId) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM rooms WHERE room_id = ?");
        $stmt->execute([$roomId]);
        $room = $stmt->fetch();
        
        if ($room) {
            $room['amenities'] = json_decode($room['amenities'] ?? '[]', true) ?: [];
        }
        
        return $room;
    }
    
    /**
     * Check if room is available for date range
     */
    public static function isAvailable($roomId, $checkIn, $checkOut) {
        $db = getDB();
        
        // Check room exists and is not in maintenance/cleaning
        $room = self::findById($roomId);
        if (!$room || in_array($room['status'], ['maintenance', 'cleaning'])) {
            return false;
        }
        
        // Check for overlapping reservations
        $stmt = $db->prepare("
            SELECT COUNT(*) FROM reservations 
            WHERE room_id = ? 
            AND status NOT IN ('cancelled', 'checked_out')
            AND (
                (check_in_date < ? AND check_out_date > ?)
                OR (check_in_date >= ? AND check_in_date < ?)
                OR (check_out_date > ? AND check_out_date <= ?)
            )
        ");
        $stmt->execute([$roomId, $checkOut, $checkIn, $checkIn, $checkOut, $checkIn, $checkOut]);
        
        return (int)$stmt->fetchColumn() === 0;
    }
    
    /**
     * Get rooms available for date range with optional filters
     */
    public static function findAvailable($checkIn, $checkOut, $filters = []) {
        $db = getDB();
        
        $sql = "
            SELECT r.* FROM rooms r
            WHERE r.status NOT IN ('maintenance', 'cleaning')
            AND r.room_id NOT IN (
                SELECT room_id FROM reservations 
                WHERE status NOT IN ('cancelled', 'checked_out')
                AND (
                    (check_in_date < ? AND check_out_date > ?)
                    OR (check_in_date >= ? AND check_in_date < ?)
                    OR (check_out_date > ? AND check_out_date <= ?)
                )
            )
        ";
        
        $params = [$checkOut, $checkIn, $checkIn, $checkOut, $checkIn, $checkOut];
        
        // Apply filters
        if (!empty($filters['room_type'])) {
            $sql .= " AND r.room_type = ?";
            $params[] = $filters['room_type'];
        }
        
        if (!empty($filters['min_capacity'])) {
            $sql .= " AND r.capacity >= ?";
            $params[] = $filters['min_capacity'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND r.price >= ?";
            $params[] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND r.price <= ?";
            $params[] = $filters['max_price'];
        }
        
        if (!empty($filters['floor'])) {
            $sql .= " AND r.floor = ?";
            $params[] = $filters['floor'];
        }
        
        // Sort
        $sortOptions = [
            'price_asc' => 'r.price ASC',
            'price_desc' => 'r.price DESC',
            'capacity_asc' => 'r.capacity ASC',
            'capacity_desc' => 'r.capacity DESC',
            'floor_asc' => 'r.floor ASC',
            'floor_desc' => 'r.floor DESC',
            'room_type' => 'r.room_type ASC'
        ];
        
        $sortBy = $filters['sort_by'] ?? 'room_type';
        $orderBy = $sortOptions[$sortBy] ?? $sortOptions['room_type'];
        $sql .= " ORDER BY $orderBy";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rooms = $stmt->fetchAll();
        
        // Parse amenities and apply amenity filter
        $amenityFilter = $filters['amenities'] ?? [];
        $filteredRooms = [];
        
        foreach ($rooms as &$room) {
            $room['amenities'] = json_decode($room['amenities'] ?? '[]', true) ?: [];
            
            // Check amenity filter
            if (!empty($amenityFilter)) {
                $hasAll = true;
                foreach ($amenityFilter as $amenity) {
                    if (!in_array($amenity, $room['amenities'])) {
                        $hasAll = false;
                        break;
                    }
                }
                if (!$hasAll) continue;
            }
            
            $filteredRooms[] = $room;
        }
        
        return $filteredRooms;
    }
    
    /**
     * Get availability calendar for a room
     */
    public static function getAvailabilityCalendar($roomId, $startDate, $endDate) {
        $db = getDB();
        
        // Get all reservations for this room in date range
        $stmt = $db->prepare("
            SELECT check_in_date, check_out_date, status 
            FROM reservations 
            WHERE room_id = ? 
            AND status NOT IN ('cancelled', 'checked_out')
            AND check_in_date <= ? 
            AND check_out_date >= ?
        ");
        $stmt->execute([$roomId, $endDate, $startDate]);
        $reservations = $stmt->fetchAll();
        
        $calendar = [];
        $current = new DateTime($startDate);
        $end = new DateTime($endDate);
        
        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            $available = true;
            $reservedBy = null;
            
            foreach ($reservations as $res) {
                if ($dateStr >= $res['check_in_date'] && $dateStr < $res['check_out_date']) {
                    $available = false;
                    $reservedBy = $res['check_in_date'] . ' to ' . $res['check_out_date'];
                    break;
                }
            }
            
            $calendar[$dateStr] = [
                'available' => $available,
                'reserved_by' => $reservedBy
            ];
            
            $current->modify('+1 day');
        }
        
        return $calendar;
    }
    
    /**
     * Get available date ranges for a room
     */
    public static function getAvailableDateRanges($roomId, $startDate, $endDate) {
        $calendar = self::getAvailabilityCalendar($roomId, $startDate, $endDate);
        $ranges = [];
        $currentRange = null;
        
        foreach ($calendar as $date => $info) {
            if ($info['available']) {
                if ($currentRange === null) {
                    $currentRange = ['start' => $date, 'end' => $date];
                } else {
                    $currentRange['end'] = $date;
                }
            } else {
                if ($currentRange !== null) {
                    $start = new DateTime($currentRange['start']);
                    $end = new DateTime($currentRange['end']);
                    $currentRange['nights'] = $start->diff($end)->days + 1;
                    $ranges[] = $currentRange;
                    $currentRange = null;
                }
            }
        }
        
        // Don't forget the last range
        if ($currentRange !== null) {
            $start = new DateTime($currentRange['start']);
            $end = new DateTime($currentRange['end']);
            $currentRange['nights'] = $start->diff($end)->days + 1;
            $ranges[] = $currentRange;
        }
        
        return $ranges;
    }
    
    /**
     * Create a new room
     */
    public static function create($data) {
        $db = getDB();
        
        $stmt = $db->prepare("
            INSERT INTO rooms (
                room_number, room_type, price, capacity, floor, 
                description, amenities, image_url, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $amenities = is_array($data['amenities'] ?? []) ? 
            json_encode($data['amenities']) : ($data['amenities'] ?? '[]');
        
        $stmt->execute([
            $data['room_number'],
            $data['room_type'] ?? 'single',
            $data['price'] ?? 0,
            $data['capacity'] ?? 1,
            $data['floor'] ?? 1,
            $data['description'] ?? '',
            $amenities,
            $data['image_url'] ?? null,
            $data['status'] ?? 'available'
        ]);
        
        return $db->lastInsertId();
    }
    
    /**
     * Update room
     */
    public static function update($roomId, $data) {
        $db = getDB();
        
        $fields = [];
        $values = [];
        
        $allowedFields = ['room_number', 'room_type', 'price', 'capacity', 'floor', 'description', 'image_url', 'status'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        if (isset($data['amenities'])) {
            $fields[] = "amenities = ?";
            $values[] = is_array($data['amenities']) ? json_encode($data['amenities']) : $data['amenities'];
        }
        
        if (empty($fields)) return false;
        
        $values[] = $roomId;
        $sql = "UPDATE rooms SET " . implode(', ', $fields) . " WHERE room_id = ?";
        $stmt = $db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Update room status
     */
    public static function updateStatus($roomId, $status) {
        return self::update($roomId, ['status' => $status]);
    }
    
    /**
     * Count rooms by status
     */
    public static function countByStatus($status = null) {
        $db = getDB();
        if ($status) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM rooms WHERE status = ?");
            $stmt->execute([$status]);
        } else {
            $stmt = $db->query("SELECT COUNT(*) FROM rooms");
        }
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Delete room
     */
    public static function delete($roomId) {
        $db = getDB();
        $stmt = $db->prepare("DELETE FROM rooms WHERE room_id = ?");
        return $stmt->execute([$roomId]);
    }
    
    /**
     * Get distinct room types
     */
    public static function getRoomTypes() {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT room_type FROM rooms ORDER BY room_type");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Get distinct floors
     */
    public static function getFloors() {
        $db = getDB();
        $stmt = $db->query("SELECT DISTINCT floor FROM rooms ORDER BY floor");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
