<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/ReservationService.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Unauthorized');
    }
    
    $serviceObj = new ReservationService($pdo);
    $action = $_GET['action'] ?? null;
    
    if ($action === 'list') {
        $reservationId = $_GET['reservation_id'] ?? null;
        
        if (!$reservationId) {
            throw new Exception('Reservation ID is required');
        }
        
        $services = $serviceObj->getReservationServices($reservationId);
        
        echo json_encode([
            'success' => true,
            'services' => $services,
            'total' => array_sum(array_column($services, 'subtotal'))
        ]);
    } elseif ($action === 'add') {
        $reservationId = $_POST['reservation_id'] ?? null;
        $serviceName = $_POST['service_name'] ?? null;
        $unitPrice = $_POST['unit_price'] ?? null;
        $quantity = $_POST['quantity'] ?? 1;
        
        $result = $serviceObj->addService($reservationId, $serviceName, $unitPrice, $quantity);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Service added successfully'
            ]);
        } else {
            throw new Exception('Error adding service');
        }
    } elseif ($action === 'remove') {
        $serviceId = $_POST['service_id'] ?? null;
        
        $result = $serviceObj->removeService($serviceId);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Service removed successfully'
            ]);
        } else {
            throw new Exception('Error removing service');
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
