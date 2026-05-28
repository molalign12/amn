<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Room.php';
require_once __DIR__ . '/../classes/Reservation.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /amnen/login.php');
    exit;
}

$userObj = new User($pdo);
$roomObj = new Room($pdo);
$reservationObj = new Reservation($pdo);

$totalUsers = $userObj->getTotalUsers();
$totalRooms = $roomObj->getTotalRooms();
$totalBookings = $reservationObj->getTotalBookings();
$totalRevenue = $reservationObj->getTotalRevenue();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        .dashboard-container { display: flex; gap: 20px; margin: 20px; flex-wrap: wrap; }
        .dashboard-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; min-width: 200px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
        .section { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        nav { background: #1a252f; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        nav strong { font-size: 16px; }
    </style>
</head>
<body>
    <nav>
        <strong>Admin Dashboard</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Users</a>
            <a href="rooms.php">Rooms</a>
            <a href="reports.php">Reports</a>
            <a href="settings.php">Settings</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-number"><?php echo $totalUsers; ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Total Rooms</div>
            <div class="stat-number"><?php echo $totalRooms; ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-number"><?php echo $totalBookings; ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-number">Birr <?php echo number_format($totalRevenue, 0); ?></div>
        </div>
    </div>

    <div class="section">
        <h2>Quick Access</h2>
        <ul>
            <li><a href="users.php">Manage Users & Roles</a></li>
            <li><a href="rooms.php">Manage Rooms & Amenities</a></li>
            <li><a href="reports.php">View Financial Reports</a></li>
            <li><a href="settings.php">System Settings</a></li>
        </ul>
    </div>
</body>
</html>
