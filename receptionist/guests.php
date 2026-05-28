<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/User.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'receptionist') {
    header('Location: /amnen/login.php');
    exit;
}

$userObj = new User($pdo);
$currentGuests = $userObj->getCurrentGuests();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guests - Receptionist - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        .guest-card { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px; border-left: 4px solid #3498db; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Current Guests</strong>
        <div style="float: right;">
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Current Guests</h1>
        
        <?php foreach ($currentGuests as $guest): ?>
            <div class="guest-card">
                <strong><?php echo htmlspecialchars($guest['fname'] . ' ' . $guest['lname']); ?></strong><br>
                Email: <?php echo htmlspecialchars($guest['email']); ?><br>
                Phone: <?php echo htmlspecialchars($guest['phone']); ?><br>
                Room: <?php echo $guest['room_number']; ?><br>
                Check-Out: <?php echo $guest['check_out_date']; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
