<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Room.php';

header('Content-Type: application/json');

try {
    $roomObj = new Room($pdo);
    
    $checkInDate = $_GET['check_in'] ?? date('Y-m-d');
    $checkOutDate = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
    $minPrice = $_GET['min_price'] ?? 0;
    $maxPrice = $_GET['max_price'] ?? 10000;
    $capacity = $_GET['capacity'] ?? 0;
    $roomType = $_GET['room_type'] ?? null;
    
    $availableRooms = $roomObj->getAvailableRooms($checkInDate, $checkOutDate, [
        'min_price' => $minPrice,
        'max_price' => $maxPrice,
        'capacity' => $capacity,
        'room_type' => $roomType
    ]);
    
    echo json_encode([
        'success' => true,
        'data' => $availableRooms,
        'count' => count($availableRooms)
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
