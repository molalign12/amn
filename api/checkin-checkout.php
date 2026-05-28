<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/CheckInOut.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized');
    }
    
    $checkInOut = new CheckInOut($pdo);
    $action = $_POST['action'] ?? $_GET['action'] ?? null;
    
    if ($action === 'checkin') {
        $reservationId = $_POST['reservation_id'] ?? null;
        $verificationCode = $_POST['verification_code'] ?? null;
        $identificationNumber = $_POST['identification_number'] ?? null;
        
        $result = $checkInOut->processCheckIn($reservationId, $verificationCode, $identificationNumber);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Check-in processed successfully',
                'data' => $result
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $result['message']
            ]);
        }
    } elseif ($action === 'checkout') {
        $reservationId = $_POST['reservation_id'] ?? null;
        
        $result = $checkInOut->processCheckOut($reservationId);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Check-out processed successfully',
                'amount_due' => $result['total_amount']
            ]);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $result['message']
            ]);
        }
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
