<?php
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../classes/RefundRequest.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'manager') {
    header('Location: /amnen/login.php');
    exit;
}

$refundObj = new RefundRequest($pdo);
$allRefunds = $refundObj->getAllRefunds();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $refundId = $_POST['refund_id'] ?? null;
    $action = $_POST['action'];
    $note = $_POST['manager_note'] ?? '';
    
    if ($action === 'approve') {
        $result = $refundObj->approveRefund($refundId, $note, $_SESSION['user']['user_id']);
        $success = $result ? 'Refund approved successfully' : 'Error approving refund';
    } elseif ($action === 'reject') {
        $result = $refundObj->rejectRefund($refundId, $note);
        $success = $result ? 'Refund rejected' : 'Error rejecting refund';
    }
}

$allRefunds = $refundObj->getAllRefunds();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refund Requests - Manager - Amnen Hotel</title>
    <link rel="stylesheet" href="/amnen/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #ecf0f1; }
        .container { margin: 20px; background: white; padding: 20px; border-radius: 8px; }
        .refund-item { background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 4px; }
        .form-group { margin: 10px 0; }
        input, textarea { width: 100%; padding: 8px; border: 1px solid #bdc3c7; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 8px 16px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-approve { background: #27ae60; color: white; }
        .btn-reject { background: #e74c3c; color: white; }
        .success { background: #d4edda; color: #155724; padding: 15px; margin: 10px 0; }
        .pending { color: #f39c12; }
        .approved { color: #27ae60; }
        .rejected { color: #e74c3c; }
        nav { background: #2c3e50; color: white; padding: 10px 20px; display: flex; justify-content: space-between; }
        nav a { color: white; margin: 0 15px; text-decoration: none; }
    </style>
</head>
<body>
    <nav>
        <strong>Refund Requests</strong>
        <div>
            <a href="index.php">Dashboard</a>
            <a href="/amnen/logout.php">Logout</a>
        </div>
    </nav>

    <div class="container">
        <h1>Refund Requests</h1>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php foreach ($allRefunds as $refund): ?>
            <div class="refund-item">
                <h3><?php echo htmlspecialchars($refund['guest_name']); ?></h3>
                <p>Amount: Birr <?php echo number_format($refund['amount'], 2); ?></p>
                <p>Reason: <?php echo htmlspecialchars($refund['reason']); ?></p>
                <p>Status: <strong class="<?php echo strtolower($refund['status']); ?>"><?php echo ucfirst($refund['status']); ?></strong></p>

                <?php if ($refund['status'] === 'pending'): ?>
                    <form method="POST">
                        <input type="hidden" name="refund_id" value="<?php echo $refund['refund_id']; ?>">
                        <div class="form-group">
                            <textarea name="manager_note" placeholder="Add a note..." rows="3"></textarea>
                        </div>
                        <button type="submit" name="action" value="approve" class="btn btn-approve">Approve</button>
                        <button type="submit" name="action" value="reject" class="btn btn-reject">Reject</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
