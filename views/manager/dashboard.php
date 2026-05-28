<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/BookingCleanup.php';
require_once __DIR__ . '/../classes/RefundRequest.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: /amnen/login.php');
    exit;
}

$reservationObj = new Reservation($pdo);
$cleanupObj = new BookingCleanup($pdo);
$refundObj = new RefundRequest($pdo);

$totalRevenue = $reservationObj->getTotalRevenue();
$totalBookings = $reservationObj->getTotalBookings();
$pendingRefunds = $refundObj->getPendingRefunds();
$pendingCleanup = $cleanupObj->getPendingCleanup();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        .dashboard-container { display: flex; gap: 20px; margin: 20px; flex-wrap: wrap; }
        .dashboard-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1; min-width: 200px; }
        .stat-number { font-size: 32px; font-weight: bold; color: #2c3e50; }
        .stat-label { color: #7f8c8d; font-size: 12px; text-transform: uppercase; }
        .section { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        tr:hover { background: #f5f5f5; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
        .btn { padding: 8px 16px; background: #27ae60; color: white; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <nav>
        <strong>Manager Dashboard</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="revenue.php">Revenue</a>
            <a href="refunds.php">Refunds</a>
            <a href="cleanup.php">Cleanup Queue</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-number">Birr <?php echo number_format($totalRevenue, 2); ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Total Bookings</div>
            <div class="stat-number"><?php echo $totalBookings; ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Pending Refunds</div>
            <div class="stat-number"><?php echo count($pendingRefunds); ?></div>
        </div>
        <div class="dashboard-card">
            <div class="stat-label">Pending Cleanup</div>
            <div class="stat-number"><?php echo count($pendingCleanup); ?></div>
        </div>
    </div>

    <div class="section">
        <h2>Pending Refund Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Amount</th>
                    <th>Reason</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($pendingRefunds, 0, 5) as $refund): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($refund['guest_name']); ?></td>
                        <td>Birr <?php echo number_format($refund['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($refund['reason']); ?></td>
                        <td><a href="refunds.php" class="btn">Review</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2>Pending Cleanup Tasks</h2>
        <table>
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($pendingCleanup, 0, 5) as $task): ?>
                    <tr>
                        <td>Room <?php echo $task['room_number']; ?></td>
                        <td><?php echo ucfirst($task['priority']); ?></td>
                        <td><?php echo ucfirst($task['status']); ?></td>
                        <td><a href="cleanup.php" class="btn">Manage</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
