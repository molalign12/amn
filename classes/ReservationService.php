<?php
/**
 * ReservationService Class - Additional services/add-ons management
 */

class ReservationService {
    
    // Available services
    private static $services = [
        1 => ['name' => 'Car Rental', 'category' => 'transportation', 'price' => 1500, 'icon' => 'car', 'description' => 'Daily car rental with driver'],
        2 => ['name' => 'Airport Transfer', 'category' => 'transportation', 'price' => 800, 'icon' => 'plane', 'description' => 'Pick up from airport'],
        3 => ['name' => 'Swimming Pool', 'category' => 'recreation', 'price' => 200, 'icon' => 'water', 'description' => 'Full day pool access'],
        4 => ['name' => 'Spa Treatment', 'category' => 'recreation', 'price' => 500, 'icon' => 'spa', 'description' => 'Relaxing spa session'],
        5 => ['name' => 'Room Upgrade', 'category' => 'accommodation', 'price' => 1000, 'icon' => 'star', 'description' => 'Upgrade to premium room'],
        6 => ['name' => 'Late Checkout', 'category' => 'accommodation', 'price' => 300, 'icon' => 'clock', 'description' => 'Checkout until 3 PM'],
        7 => ['name' => 'Breakfast', 'category' => 'dining', 'price' => 150, 'icon' => 'utensils', 'description' => 'Full breakfast buffet per person'],
        8 => ['name' => 'Dinner Package', 'category' => 'dining', 'price' => 350, 'icon' => 'utensils', 'description' => '3-course dinner per person'],
        9 => ['name' => 'Laundry Service', 'category' => 'other', 'price' => 100, 'icon' => 'shirt', 'description' => 'Per item laundry'],
        10 => ['name' => 'Tour Guide', 'category' => 'recreation', 'price' => 600, 'icon' => 'map', 'description' => 'Half-day guided tour']
    ];
    
    /**
     * Get all available services
     */
    public static function getAvailableServices() {
        $services = [];
        foreach (self::$services as $id => $service) {
            $services[] = array_merge(['service_id' => $id], $service, ['currency' => 'ETB']);
        }
        return $services;
    }
    
    /**
     * Get services by category
     */
    public static function getServicesByCategory($category) {
        $services = [];
        foreach (self::$services as $id => $service) {
            if ($service['category'] === $category) {
                $services[] = array_merge(['service_id' => $id], $service, ['currency' => 'ETB']);
            }
        }
        return $services;
    }
    
    /**
     * Add service to reservation
     */
    public static function addToReservation($reservationId, $serviceId, $quantity = 1) {
        $db = getDB();
        
        if (!isset(self::$services[$serviceId])) {
            throw new Exception("Service not found");
        }
        
        $service = self::$services[$serviceId];
        
        // Check if service already added
        $stmt = $db->prepare("
            SELECT * FROM reservation_services 
            WHERE reservation_id = ? AND service_id = ?
        ");
        $stmt->execute([$reservationId, $serviceId]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update quantity
            $stmt = $db->prepare("
                UPDATE reservation_services 
                SET quantity = quantity + ?, subtotal = (quantity + ?) * unit_price
                WHERE reservation_service_id = ?
            ");
            $stmt->execute([$quantity, $quantity, $existing['reservation_service_id']]);
        } else {
            // Add new
            $stmt = $db->prepare("
                INSERT INTO reservation_services (
                    reservation_id, service_id, service_name, unit_price, 
                    quantity, subtotal, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $subtotal = $service['price'] * $quantity;
            $stmt->execute([
                $reservationId, $serviceId, $service['name'], 
                $service['price'], $quantity, $subtotal
            ]);
        }
        
        return true;
    }
    
    /**
     * Remove service from reservation
     */
    public static function removeFromReservation($reservationId, $serviceId) {
        $db = getDB();
        $stmt = $db->prepare("
            DELETE FROM reservation_services 
            WHERE reservation_id = ? AND service_id = ?
        ");
        return $stmt->execute([$reservationId, $serviceId]);
    }
    
    /**
     * Update service quantity
     */
    public static function updateQuantity($reservationServiceId, $quantity) {
        $db = getDB();
        
        if ($quantity <= 0) {
            $stmt = $db->prepare("DELETE FROM reservation_services WHERE reservation_service_id = ?");
            return $stmt->execute([$reservationServiceId]);
        }
        
        $stmt = $db->prepare("
            UPDATE reservation_services 
            SET quantity = ?, subtotal = ? * unit_price
            WHERE reservation_service_id = ?
        ");
        return $stmt->execute([$quantity, $quantity, $reservationServiceId]);
    }
    
    /**
     * Get services for a reservation
     */
    public static function getForReservation($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT * FROM reservation_services 
            WHERE reservation_id = ?
            ORDER BY created_at
        ");
        $stmt->execute([$reservationId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Calculate total services cost for reservation
     */
    public static function getTotalForReservation($reservationId) {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(subtotal), 0) 
            FROM reservation_services 
            WHERE reservation_id = ?
        ");
        $stmt->execute([$reservationId]);
        return (float)$stmt->fetchColumn();
    }
    
    /**
     * Calculate complete reservation total
     */
    public static function calculateReservationTotal($reservationId) {
        $reservation = Reservation::findById($reservationId);
        if (!$reservation) {
            throw new Exception("Reservation not found");
        }
        
        $roomPrice = (float)$reservation['total_price'];
        $servicesTotal = self::getTotalForReservation($reservationId);
        
        return [
            'room_price' => $roomPrice,
            'services' => $servicesTotal,
            'total' => $roomPrice + $servicesTotal
        ];
    }
}
