<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/CheckInOut.php';

// Check if user is receptionist
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$checkInOut = new CheckInOut($pdo);
$reservationObj = new Reservation($pdo);

// Get today's check-ins and check-outs
$todayCheckIns = $checkInOut->getTodayCheckIns();
$todayCheckOuts = $checkInOut->getTodayCheckOuts();
$pendingReservations = $reservationObj->getPendingReservations(5);
$occupancyRate = $checkInOut->getOccupancyRate();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receptionist Dashboard - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        .dashboard-container { display: flex; gap: 20px; margin: 20px; }
        .dashboard-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; }
        .stat-number { font-size: 28px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
        .today-section { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        .checkin-list, .checkout-list { display: grid; gap: 10px; }
        .reservation-item { padding: 15px; background: #f8f9fa; border-left: 4px solid #3498db; border-radius: 4px; }
        .reservation-item.checkout { border-left-color: #e74c3c; }
        .action-buttons { display: flex; gap: 10px; margin-top: 10px; }
        .btn { padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-primary { background: #3498db; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Amnen Hotel - Receptionist</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="check-in.php">Check-In</a>
            <a href="check-out.php">Check-Out</a>
            <a href="reservations.php">Reservations</a>
            <a href="rooms-status.php">Room Status</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="stat-label">Today's Check-Ins</div>
            <div class="stat-number"><?php echo count($todayCheckIns); ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Today's Check-Outs</div>
            <div class="stat-number"><?php echo count($todayCheckOuts); ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Occupancy Rate</div>
            <div class="stat-number"><?php echo $occupancyRate; ?>%</div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Pending Reservations</div>
            <div class="stat-number"><?php echo count($pendingReservations); ?></div>
        </div>
    </div>

    <div class="today-section">
        <h2>Today's Check-Ins</h2>
        <div class="checkin-list">
            <?php foreach ($todayCheckIns as $reservation): ?>
                <div class="reservation-item">
                    <strong><?php echo htmlspecialchars($reservation['guest_name']); ?></strong>
                    <p>Room: <?php echo $reservation['room_number']; ?> | Phone: <?php echo htmlspecialchars($reservation['phone']); ?></p>
                    <div class="action-buttons">
                        <a href="check-in.php?reservation_id=<?php echo $reservation['reservation_id']; ?>" class="btn btn-primary">Process Check-In</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="today-section">
        <h2>Today's Check-Outs</h2>
        <div class="checkout-list">
            <?php foreach ($todayCheckOuts as $reservation): ?>
                <div class="reservation-item checkout">
                    <strong><?php echo htmlspecialchars($reservation['guest_name']); ?></strong>
                    <p>Room: <?php echo $reservation['room_number']; ?> | Phone: <?php echo htmlspecialchars($reservation['phone']); ?></p>
                    <div class="action-buttons">
                        <a href="check-out.php?reservation_id=<?php echo $reservation['reservation_id']; ?>" class="btn btn-danger">Process Check-Out</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
