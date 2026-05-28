<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/CheckInOut.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$checkInOut = new CheckInOut($pdo);
$reservation = null;
$error = '';
$success = '';

if (isset($_GET['reservation_id'])) {
    $reservation = $checkInOut->getReservationDetails($_GET['reservation_id']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservation_id'])) {
    $verificationCode = $_POST['verification_code'] ?? '';
    $identificationNumber = $_POST['identification_number'] ?? '';
    
    $result = $checkInOut->processCheckIn($_POST['reservation_id'], $verificationCode, $identificationNumber);
    
    if ($result['success']) {
        $success = 'Check-in processed successfully!';
        $_GET['reservation_id'] = null;
        $reservation = null;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-In - Receptionist - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h1 { color: #2c3e50; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 4px; margin: 10px 0; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Check-In</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Guest Check-In</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($reservation): ?>
            <div class="info">
                <strong><?php echo htmlspecialchars($reservation['fname'] . ' ' . $reservation['lname']); ?></strong><br>
                Room: <?php echo $reservation['room_number']; ?><br>
                Check-in: <?php echo $reservation['check_in_date']; ?><br>
                Check-out: <?php echo $reservation['check_out_date']; ?>
            </div>

            <form method="POST">
                <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                
                <div class="form-group">
                    <label>Identification Number (National ID / Passport):</label>
                    <input type="text" name="identification_number" required>
                </div>

                <div class="form-group">
                    <label>Verification Code (sent to guest email):</label>
                    <input type="text" name="verification_code" placeholder="Enter the 6-digit code" required>
                </div>

                <button type="submit" class="btn">Complete Check-In</button>
            </form>
        <?php else: ?>
            <p>Select a reservation from the dashboard to process check-in.</p>
        <?php endif; ?>
    </div>
</body>
</html>
