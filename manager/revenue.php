<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Reservation.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: /amnen/login.php');
    exit;
}

$reservationObj = new Reservation($pdo);
$monthlyRevenue = $reservationObj->getMonthlyRevenue();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report - Manager - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #34495e; color: white; }
        tr:hover { background: #f5f5f5; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Revenue Report</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Monthly Revenue Report</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Revenue</th>
                    <th>Bookings</th>
                    <th>Avg. per Booking</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($monthlyRevenue as $month): ?>
                    <tr>
                        <td><?php echo $month['month']; ?></td>
                        <td>Birr <?php echo number_format($month['total_revenue'], 2); ?></td>
                        <td><?php echo $month['booking_count']; ?></td>
                        <td>Birr <?php echo number_format($month['avg_per_booking'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
