<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/CheckInOut.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$checkInOut = new CheckInOut($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $result = $checkInOut->processCheckOut($_POST['reservation_id']);
    
    if ($result['success']) {
        $success = 'Check-out processed! Amount due: ' . $result['total_amount'];
    } else {
        $error = $result['message'];
    }
}

$todayCheckOuts = $checkInOut->getTodayCheckOuts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-Out - Receptionist - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .checkout-list { display: grid; gap: 10px; }
        .reservation-item { padding: 15px; background: #f8f9fa; border-left: 4px solid #e74c3c; border-radius: 4px; }
        .btn { padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #c0392b; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Check-Out</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Guest Check-Out</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <h2>Today's Check-Outs</h2>
        <div class="checkout-list">
            <?php if (empty($todayCheckOuts)): ?>
                <p>No check-outs scheduled for today.</p>
            <?php else: ?>
                <?php foreach ($todayCheckOuts as $reservation): ?>
                    <div class="reservation-item">
                        <strong><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname']); ?></strong>
                        <p>Room: <?php echo $reservation['room_number']; ?> | Email: <?php echo htmlspecialchars($reservation['email']); ?></p>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                            <button type="submit" class="btn">Process Check-Out</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
