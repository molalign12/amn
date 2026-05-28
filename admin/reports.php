<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Reservation.php';
require_once __DIR__ . '/../classes/Feedback.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /amnen/login.php');
    exit;
}

$reservationObj = new Reservation($pdo);
$feedbackObj = new Feedback($pdo);

$monthlyRevenue = $reservationObj->getMonthlyRevenue();
$allFeedback = $feedbackObj->getAllFeedback();
$avgRating = $feedbackObj->getAverageRating();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #1a252f; color: white; }
        tr:hover { background: #f5f5f5; }
        .stat { display: inline-block; margin: 10px 20px 10px 0; padding: 15px; background: #f9f9f9; border-radius: 4px; }
        nav { background: #1a252f; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Financial Reports</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>System Reports</h1>
        
        <div>
            <div class="stat">Average Rating: <strong><?php echo number_format($avgRating, 2); ?>/5</strong></div>
            <div class="stat">Total Feedback: <strong><?php echo count($allFeedback); ?></strong></div>
        </div>

        <h2>Monthly Revenue</h2>
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

        <h2>Recent Feedback</h2>
        <table>
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (array_slice($allFeedback, 0, 10) as $fb): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['guest_name']); ?></td>
                        <td><?php echo str_repeat('★', $fb['rating']) . str_repeat('☆', 5 - $fb['rating']); ?></td>
                        <td><?php echo htmlspecialchars(substr($fb['comment'], 0, 50)); ?>...</td>
                        <td><?php echo $fb['created_at']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
