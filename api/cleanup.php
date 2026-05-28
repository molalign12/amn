<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/BookingCleanup.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
        throw new Exception('Unauthorized');
    }
    
    $cleanupObj = new BookingCleanup($pdo);
    $action = $_GET['action'] ?? null;
    
    if ($action === 'list') {
        $status = $_GET['status'] ?? null;
        
        $tasks = $cleanupObj->getCleanupTasks($status);
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks,
            'total' => count($tasks)
        ]);
    } elseif ($action === 'update') {
        $taskId = $_POST['task_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        $result = $cleanupObj->updateTaskStatus($taskId, $status);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Task status updated'
            ]);
        } else {
            throw new Exception('Error updating task');
        }
    } elseif ($action === 'create') {
        $reservationId = $_POST['reservation_id'] ?? null;
        $roomId = $_POST['room_id'] ?? null;
        $taskType = $_POST['task_type'] ?? 'general';
        $priority = $_POST['priority'] ?? 'medium';
        
        $result = $cleanupObj->createCleanupTask($reservationId, $roomId, $taskType, $priority);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cleanup task created',
                'task_id' => $result
            ]);
        } else {
            throw new Exception('Error creating task');
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
