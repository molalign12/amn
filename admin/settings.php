<?php
require_once __DIR__ . '/../bootstrap.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /amnen/login.php');
    exit;
}

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = 'Settings updated successfully';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Admin - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; max-width: 600px; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; }
        nav { background: #1a252f; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>System Settings</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>System Settings</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Hotel Name:</label>
                <input type="text" name="hotel_name" value="Amnen Guest House">
            </div>

            <div class="form-group">
                <label>Email Address:</label>
                <input type="email" name="email" value="info@amnen.hotel">
            </div>

            <div class="form-group">
                <label>Phone Number:</label>
                <input type="tel" name="phone" value="+251 11 123 4567">
            </div>

            <div class="form-group">
                <label>Currency:</label>
                <input type="text" name="currency" value="ETB (Birr)">
            </div>

            <div class="form-group">
                <label>Check-In Time:</label>
                <input type="time" name="checkin_time" value="14:00">
            </div>

            <div class="form-group">
                <label>Check-Out Time:</label>
                <input type="time" name="checkout_time" value="11:00">
            </div>

            <div class="form-group">
                <label>Support Email:</label>
                <input type="email" name="support_email" value="support@amnen.hotel">
            </div>

            <button type="submit" class="btn">Save Settings</button>
        </form>
    </div>
</body>
</html>
