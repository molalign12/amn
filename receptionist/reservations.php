<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/Reservation.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$reservationObj = new Reservation($pdo);
$allReservations = $reservationObj->getAllReservations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations - Receptionist - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #2c3e50; color: white; }
        tr:hover { background: #f5f5f5; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Reservations</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>All Reservations</h1>
        
        <table>
            <thead>
                <tr>
                    <th>Guest Name</th>
                    <th>Room</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Status</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allReservations as $res): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($res['fname'] . ' ' . $res['lname']); ?></td>
                        <td><?php echo $res['room_number']; ?></td>
                        <td><?php echo $res['check_in_date']; ?></td>
                        <td><?php echo $res['check_out_date']; ?></td>
                        <td><?php echo ucfirst($res['status']); ?></td>
                        <td>Birr <?php echo number_format($res['total_amount'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
