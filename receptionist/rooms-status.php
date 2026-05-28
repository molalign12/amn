<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Room.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$roomObj = new Room($pdo);
$rooms = $roomObj->getAllRooms();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Status - Receptionist - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; }
        .room-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px; }
        .room-card { background: white; padding: 15px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .room-available { border-top: 4px solid #27ae60; }
        .room-occupied { border-top: 4px solid #e74c3c; }
        .room-maintenance { border-top: 4px solid #f39c12; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Room Status</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Room Status</h1>
        
        <div class="room-grid">
            <?php foreach ($rooms as $room): 
                $roomClass = 'room-' . $room['status'];
            ?>
                <div class="room-card <?php echo $roomClass; ?>">
                    <strong>Room <?php echo $room['room_number']; ?></strong><br>
                    Type: <?php echo ucfirst($room['room_type']); ?><br>
                    Price: Birr <?php echo number_format($room['price'], 2); ?><br>
                    Capacity: <?php echo $room['capacity']; ?> guests<br>
                    Status: <strong><?php echo ucfirst($room['status']); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
