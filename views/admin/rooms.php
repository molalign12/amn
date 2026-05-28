<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Room.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /amnen/login.php');
    exit;
}

$roomObj = new Room($pdo);
$allRooms = $roomObj->getAllRooms();
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_room') {
        $result = $roomObj->addRoom([
            'room_number' => $_POST['room_number'],
            'room_type' => $_POST['room_type'],
            'price' => $_POST['price'],
            'capacity' => $_POST['capacity'],
            'floor' => $_POST['floor'],
            'description' => $_POST['description'],
            'amenities' => json_encode(explode(',', $_POST['amenities']))
        ]);
        $success = $result ? 'Room added successfully' : 'Error adding room';
        $allRooms = $roomObj->getAllRooms();
    } elseif ($_POST['action'] === 'update_status') {
        $result = $roomObj->updateRoomStatus($_POST['room_id'], $_POST['status']);
        $success = $result ? 'Room status updated' : 'Error updating status';
        $allRooms = $roomObj->getAllRooms();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Management - Admin - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        .form-section { background: #f9f9f9; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1a252f; color: white; }
        tr:hover { background: #f5f5f5; }
        .btn { padding: 8px 16px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; }
        nav { background: #1a252f; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Room Management</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Room Management</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="form-section">
            <h3>Add New Room</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_room">
                <div class="form-group">
                    <label>Room Number:</label>
                    <input type="text" name="room_number" required>
                </div>
                <div class="form-group">
                    <label>Room Type:</label>
                    <select name="room_type" required>
                        <option value="single">Single</option>
                        <option value="double">Double</option>
                        <option value="deluxe">Deluxe</option>
                        <option value="suite">Suite</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price (per night):</label>
                    <input type="number" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Capacity:</label>
                    <input type="number" name="capacity" required>
                </div>
                <div class="form-group">
                    <label>Floor:</label>
                    <input type="number" name="floor" required>
                </div>
                <div class="form-group">
                    <label>Description:</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Amenities (comma-separated):</label>
                    <input type="text" name="amenities" placeholder="WiFi,AC,TV,Bathroom">
                </div>
                <button type="submit" class="btn">Add Room</button>
            </form>
        </div>

        <h2>All Rooms</h2>
        <table>
            <thead>
                <tr>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Price</th>
                    <th>Capacity</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allRooms as $room): ?>
                    <tr>
                        <td><?php echo $room['room_number']; ?></td>
                        <td><?php echo ucfirst($room['room_type']); ?></td>
                        <td>Birr <?php echo number_format($room['price'], 2); ?></td>
                        <td><?php echo $room['capacity']; ?></td>
                        <td><?php echo ucfirst($room['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
