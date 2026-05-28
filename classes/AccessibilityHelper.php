<?php
/**
 * AccessibilityHelper Class - WCAG-compliant accessibility features
 */

class AccessibilityHelper {
    
    /**
     * Get floor label
     */
    public static function getFloorLabel($floor) {
        return match((int)$floor) {
            0 => 'Ground Floor',
            1 => 'First Floor',
            2 => 'Second Floor',
            3 => 'Third Floor',
            4 => 'Fourth Floor',
            5 => 'Fifth Floor',
            default => "Floor $floor"
        };
    }
    
    /**
     * Get accessibility info for a room
     */
    public static function getRoomAccessibility($room) {
        $floor = (int)($room['floor'] ?? 1);
        $isGroundFloor = $floor <= 1;
        
        $features = [];
        $description = '';
        
        if ($isGroundFloor) {
            $features[] = 'Ground floor access';
            $features[] = 'No stairs required';
            $description = 'Ground floor room with easy access, no elevator or stairs needed.';
        } else {
            $features[] = 'Elevator available';
            $features[] = 'Stairs also available';
            $description = self::getFloorLabel($floor) . ' room. Elevator access available. Stairs are also an option.';
        }
        
        // Check for accessibility features in amenities
        $amenities = is_array($room['amenities']) ? $room['amenities'] : 
            json_decode($room['amenities'] ?? '[]', true) ?: [];
        
        if (in_array('Wheelchair Accessible', $amenities)) {
            $features[] = 'Wheelchair accessible';
        }
        if (in_array('Accessible Bathroom', $amenities)) {
            $features[] = 'Accessible bathroom';
        }
        if (in_array('Visual Alerts', $amenities)) {
            $features[] = 'Visual alerts for hearing impaired';
        }
        
        return [
            'room_id' => $room['room_id'] ?? null,
            'room_number' => $room['room_number'] ?? '',
            'floor' => $floor,
            'floor_label' => self::getFloorLabel($floor),
            'is_ground_floor' => $isGroundFloor,
            'elevator_access' => !$isGroundFloor,
            'accessibility_features' => $features,
            'description' => $description,
            'aria_label' => self::generateAriaLabel($room),
            'audio_description' => self::generateAudioDescription($room)
        ];
    }
    
    /**
     * Generate ARIA label for screen readers
     */
    public static function generateAriaLabel($room) {
        $roomNumber = $room['room_number'] ?? '';
        $roomType = $room['room_type'] ?? 'Standard';
        $floor = self::getFloorLabel($room['floor'] ?? 1);
        $price = number_format($room['price'] ?? 0);
        $capacity = $room['capacity'] ?? 1;
        
        $guestText = $capacity === 1 ? '1 guest' : "$capacity guests";
        
        return "Room $roomNumber, $roomType room on $floor, $price birr per night, accommodates $guestText";
    }
    
    /**
     * Generate audio description for text-to-speech
     */
    public static function generateAudioDescription($room) {
        $roomNumber = $room['room_number'] ?? '';
        $roomType = ucfirst($room['room_type'] ?? 'standard');
        $floor = (int)($room['floor'] ?? 1);
        $floorLabel = self::getFloorLabel($floor);
        $price = number_format($room['price'] ?? 0);
        $capacity = $room['capacity'] ?? 1;
        
        $description = "Room $roomNumber is a $roomType room located on the $floorLabel.";
        $description .= " It can accommodate up to $capacity " . ($capacity === 1 ? 'guest' : 'guests') . ".";
        $description .= " The nightly rate is $price Ethiopian Birr.";
        
        if ($floor <= 1) {
            $description .= " This is a ground floor room with easy access.";
        } else {
            $description .= " Elevator access is available to this floor.";
        }
        
        $amenities = is_array($room['amenities']) ? $room['amenities'] : 
            json_decode($room['amenities'] ?? '[]', true) ?: [];
        
        if (!empty($amenities)) {
            $description .= " Amenities include: " . implode(', ', array_slice($amenities, 0, 5)) . ".";
        }
        
        return $description;
    }
    
    /**
     * Get rooms by accessibility features
     */
    public static function findAccessibleRooms($requirements = []) {
        $rooms = Room::findAll('available');
        $accessible = [];
        
        foreach ($rooms as $room) {
            $info = self::getRoomAccessibility($room);
            $matches = true;
            
            // Check ground floor requirement
            if (in_array('ground_floor', $requirements) && !$info['is_ground_floor']) {
                $matches = false;
            }
            
            // Check wheelchair accessible
            if (in_array('wheelchair', $requirements)) {
                $amenities = is_array($room['amenities']) ? $room['amenities'] : 
                    json_decode($room['amenities'] ?? '[]', true) ?: [];
                if (!in_array('Wheelchair Accessible', $amenities)) {
                    $matches = false;
                }
            }
            
            if ($matches) {
                $room['accessibility'] = $info;
                $accessible[] = $room;
            }
        }
        
        return $accessible;
    }
    
    /**
     * Generate skip links for keyboard navigation
     */
    public static function getSkipLinks() {
        return [
            ['id' => 'main-content', 'label' => 'Skip to main content'],
            ['id' => 'navigation', 'label' => 'Skip to navigation'],
            ['id' => 'search', 'label' => 'Skip to search'],
            ['id' => 'footer', 'label' => 'Skip to footer']
        ];
    }
    
    /**
     * Get high contrast colors
     */
    public static function getHighContrastTheme() {
        return [
            'background' => '#FFFFFF',
            'text' => '#000000',
            'primary' => '#0000FF',
            'accent' => '#D4A843',
            'error' => '#CC0000',
            'success' => '#006600',
            'border' => '#333333'
        ];
    }
}
