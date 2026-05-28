<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/BookingCleanup.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: /amnen/login.php');
    exit;
}

$cleanupObj = new BookingCleanup($pdo);
$allCleanup = $cleanupObj->getAllCleanupTasks();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $result = $cleanupObj->updateTaskStatus($_POST['task_id'], $_POST['status']);
    $success = $result ? 'Task updated successfully' : 'Error updating task';
    $allCleanup = $cleanupObj->getAllCleanupTasks();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Queue - Manager - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        .task-item { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px; border-left: 4px solid; }
        .priority-high { border-left-color: #e74c3c; }
        .priority-medium { border-left-color: #f39c12; }
        .priority-low { border-left-color: #3498db; }
        select { padding: 8px; border: 1px solid #bdc3c7; border-radius: 4px; }
        .btn { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Cleanup Queue</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Cleaning Tasks</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php foreach ($allCleanup as $task): 
            $priorityClass = 'priority-' . $task['priority'];
        ?>
            <div class="task-item <?php echo $priorityClass; ?>">
                <h3>Room <?php echo $task['room_number']; ?></h3>
                <p>Priority: <?php echo ucfirst($task['priority']); ?></p>
                <p>Type: <?php echo ucfirst($task['task_type']); ?></p>
                <p>Status: <?php echo ucfirst($task['status']); ?></p>

                <form method="POST" style="display: inline;">
                    <input type="hidden" name="task_id" value="<?php echo $task['cleanup_id']; ?>">
                    <select name="status">
                        <option value="pending" <?php echo $task['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="in-progress" <?php echo $task['status'] === 'in-progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $task['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <button type="submit" class="btn">Update</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
