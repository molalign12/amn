<?php
// Simple password recovery using existing User security question flow
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/User.php';
if (session_status() === PHP_SESSION_NONE) { session_save_path('C:/xampp/tmp'); session_start(); }

$step = 1;
$error = '';
$info = '';
$question = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    try {
        $db = getDB();
    } catch (Throwable $e) {
        $db = null;
    }

    if ($action === 'request_reset') {
        $identifier = trim($_POST['identifier'] ?? '');
        if (!$identifier) {
            $error = 'Please enter your username, email, or phone.';
        } else {
            // Resolve to username (allow username, email, phone)
            $stmt = $db->prepare('SELECT username FROM users WHERE (username = ? OR email = ? OR phone = ?) AND is_active = 1 LIMIT 1');
            $stmt->execute([$identifier, $identifier, $identifier]);
            $row = $stmt->fetch();
            if (!$row) {
                $error = 'No active account found matching that identifier.';
            } else {
                $username = $row['username'];
                $infoArr = User::getRecoveryInfo($username);
                if (!$infoArr || empty($infoArr['security_question'])) {
                    $error = 'We do not have a recovery question set for this account. Please contact an administrator.';
                } else {
                    $question = $infoArr['security_question'];
                    $step = 2;
                }
            }
        }
    } elseif ($action === 'verify_answer') {
        $username = trim($_POST['username'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        if (!$username || !$answer) {
            $error = 'Please provide your answer.';
            $step = 2;
        } else {
            if (User::verifyRecovery($username, $answer)) {
                // proceed to password reset
                $step = 3;
            } else {
                $error = 'Verification failed. Please check your answer and try again.';
                $step = 2;
            }
        }
    } elseif ($action === 'reset_password') {
        $username = trim($_POST['username'] ?? '');
        $pass = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';
        if (!$username || !$pass || !$pass2) {
            $error = 'Please enter and confirm your new password.';
            $step = 3;
        } elseif ($pass !== $pass2) {
            $error = 'Passwords do not match.';
            $step = 3;
        } else {
            if (User::resetPassword($username, $pass)) {
                $_SESSION['success'] = 'Password updated successfully. You may now sign in.';
                header('Location: /amnen/login.php');
                exit;
            } else {
                $error = 'Failed to update password. Please try again later.';
                $step = 3;
            }
        }
    }
}

function esc($s) { return htmlspecialchars($s ?? ''); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Forgot Password | AMNEN</title>
<link rel="stylesheet" href="/amnen/assets/css/main.css">
<style>
.auth-layout{max-width:420px;margin:48px auto;padding:28px;background:var(--bg1);border-radius:12px;box-shadow:var(--shadow)}
.hdr{font-size:1.4rem;margin-bottom:12px}
.form-row{margin-bottom:12px}
.input-field{width:100%;padding:10px;border:1px solid #d5dbe8;border-radius:6px}
.btn{display:inline-block;padding:10px 14px;background:var(--accent);color:#fff;border-radius:8px;border:none}
.msg{padding:10px;border-radius:8px;margin-bottom:10px}
.msg.err{background:#ffecec;color:#c0392b}
.msg.ok{background:#e9f7ef;color:#1d6b3a}
</style>
</head>
<body>
<div class="auth-layout">
    <div class="hdr">Password Recovery</div>
    <?php if ($error): ?>
        <div class="msg err"><?php echo esc($error); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="msg ok"><?php echo esc($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <p>Enter your username, email, or phone to begin account recovery.</p>
        <form method="post">
            <div class="form-row">
                <input type="text" name="identifier" class="input-field" placeholder="Username or email or phone" required>
            </div>
            <input type="hidden" name="action" value="request_reset">
            <div style="text-align:right"><button class="btn" type="submit">Continue</button></div>
        </form>
    <?php elseif ($step === 2): ?>
        <p>Please answer your recovery question for <strong><?php echo esc($username); ?></strong>.</p>
        <form method="post">
            <div class="form-row">
                <label><?php echo esc($question); ?></label>
                <input type="text" name="answer" class="input-field" placeholder="Your answer" required>
            </div>
            <input type="hidden" name="action" value="verify_answer">
            <input type="hidden" name="username" value="<?php echo esc($username); ?>">
            <div style="text-align:right"><button class="btn" type="submit">Verify</button></div>
        </form>
    <?php elseif ($step === 3): ?>
        <p>Enter a new password for <strong><?php echo esc($username); ?></strong>.</p>
        <form method="post">
            <div class="form-row">
                <input type="password" name="password" class="input-field" placeholder="New password" required>
            </div>
            <div class="form-row">
                <input type="password" name="password2" class="input-field" placeholder="Confirm new password" required>
            </div>
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="username" value="<?php echo esc($username); ?>">
            <div style="text-align:right"><button class="btn" type="submit">Set Password</button></div>
        </form>
    <?php endif; ?>

    <p style="margin-top:14px"><a href="/amnen/login.php">Back to Sign In</a></p>
</div>
</body>
</html>