<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Room.php';

header('Content-Type: application/json');

try {
    $roomObj = new Room($pdo);
    
    $roomId = $_GET['room_id'] ?? null;
    $checkInDate = $_GET['check_in'] ?? date('Y-m-d');
    $checkOutDate = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
    
    if (!$roomId) {
        throw new Exception('Room ID is required');
    }
    
    $isAvailable = $roomObj->isRoomAvailable($roomId, $checkInDate, $checkOutDate);
    $room = $roomObj->getRoomById($roomId);
    
    echo json_encode([
        'success' => true,
        'room_id' => $roomId,
        'available' => $isAvailable,
        'check_in' => $checkInDate,
        'check_out' => $checkOutDate,
        'room_details' => $room
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
