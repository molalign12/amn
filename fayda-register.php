<?php
/**
 * FAYDA ID REGISTRATION PAGE
 * Feature #1: User registration with Fayda ID verification
 * 
 * This page handles:
 * 1. Fayda ID mock verification
 * 2. User registration with verified KYC data
 * 3. Automatic account creation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_save_path('C:/xampp/tmp');
    session_start();
}

// Redirect if already authenticated
if (isset($_SESSION['user'])) {
    header('Location: /amnen/views/guest/home.php');
    exit;
}

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/classes/FaydaAuth.php';
require_once __DIR__ . '/classes/User.php';

$error = '';
$success = '';
$step = 'fayda'; // fayda, confirm, success

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['step'])) {
        if ($_POST['step'] === 'confirm' && isset($_SESSION['fayda_verified'])) {
            try {
                $kycData = $_SESSION['fayda_verified'];
                $userId = FaydaAuth::createOrUpdateUserFromFayda($kycData);
                
                // Auto-login after registration
                $user = User::findById($userId);
                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = $user;
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['role'] = $user['role'];
                    
                    header('Location: /amnen/views/guest/home.php');
                    exit;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
                $step = 'fayda';
            }
        }
    }
}

$fayda_verified = $_SESSION['fayda_verified'] ?? null;
if ($fayda_verified) {
    $step = 'confirm';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register with Fayda ID — Amnen</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2417 50%, #3D2E0F 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #1A1A1A 0%, #2C2417 100%);
            padding: 40px 20px;
            text-align: center;
            color: #F0D98A;
        }

        .header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            margin-bottom: 8px;
        }

        .header p {
            color: #AAA;
            font-size: 14px;
        }

        .content {
            padding: 40px;
        }

        .error {
            background: #FFE8E0;
            color: #C4623A;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #C4623A;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .fayda-container {
            background: #F5F0E8;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .confirm-section {
            background: #F5F0E8;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .kyc-data {
            background: white;
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 2px solid #E8E2D9;
        }

        .kyc-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .kyc-field {
            padding: 8px;
            background: #FAFAF8;
            border-radius: 4px;
        }

        .kyc-field label {
            display: block;
            font-size: 11px;
            color: #6B6B6B;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kyc-field value {
            display: block;
            font-size: 14px;
            color: #1A1A1A;
            font-weight: 600;
        }

        .button-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 24px;
        }

        .button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .button-primary {
            background: linear-gradient(135deg, #1A1A1A, #2C2417);
            color: #F0D98A;
            grid-column: 1 / -1;
        }

        .button-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .button-secondary {
            background: #E8E2D9;
            color: #1A1A1A;
        }

        .button-secondary:hover {
            background: #D9CEBC;
        }

        .success-message {
            text-align: center;
            padding: 40px 20px;
        }

        .success-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .login-link {
            margin-top: 20px;
            text-align: center;
        }

        .login-link a {
            color: #D4A843;
            text-decoration: none;
            font-weight: 600;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Amnen Guest House</h1>
            <p>Register with Fayda ID</p>
        </div>

        <div class="content">
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($step === 'fayda'): ?>
                <!-- Fayda Verification Component -->
                <div class="fayda-container">
                    <div id="fayda-verification"></div>
                </div>
                <p style="text-align: center; color: #6B6B6B; font-size: 13px; margin-top: 16px;">
                    After verification, you'll complete your registration.
                </p>

            <?php elseif ($step === 'confirm' && $fayda_verified): ?>
                <!-- Confirm Registration -->
                <h2 style="font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 20px;">Verify Your Information</h2>
                
                <div class="confirm-section">
                    <p style="color: #6B6B6B; font-size: 13px; margin-bottom: 16px;">Your Fayda ID has been verified. Please confirm the following information:</p>
                    
                    <div class="kyc-data">
                        <div class="kyc-row">
                            <div class="kyc-field">
                                <label>Full Name</label>
                                <value><?php echo htmlspecialchars($fayda_verified['fullNameEn']); ?></value>
                            </div>
                            <div class="kyc-field">
                                <label>FIN</label>
                                <value><?php echo htmlspecialchars($fayda_verified['fin']); ?></value>
                            </div>
                        </div>
                        <div class="kyc-row">
                            <div class="kyc-field">
                                <label>Date of Birth</label>
                                <value><?php echo htmlspecialchars($fayda_verified['dob']); ?></value>
                            </div>
                            <div class="kyc-field">
                                <label>Gender</label>
                                <value><?php echo htmlspecialchars($fayda_verified['gender']); ?></value>
                            </div>
                        </div>
                        <div class="kyc-row">
                            <div class="kyc-field">
                                <label>Phone Number</label>
                                <value><?php echo htmlspecialchars($fayda_verified['phoneNumber']); ?></value>
                            </div>
                            <div class="kyc-field">
                                <label>Region</label>
                                <value><?php echo htmlspecialchars($fayda_verified['address']['region'] ?? ''); ?></value>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST">
                    <input type="hidden" name="step" value="confirm">
                    <div class="button-group">
                        <button type="button" class="button button-secondary" onclick="location.href=location.href;">
                            ← Change Information
                        </button>
                        <button type="submit" class="button button-primary">
                            Complete Registration
                        </button>
                    </div>
                </form>

            <?php endif; ?>
        </div>
    </div>

    <!-- Include Fayda Mock Component -->
    <script src="/amnen/assets/js/fayda-mock.js"></script>
    <script>
        // Only initialize Fayda if on verification step
        <?php if ($step === 'fayda'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const fayda = new FaydaMockVerification({
                containerId: 'fayda-verification',
                onSuccess: function(userData) {
                    console.log('Fayda verification successful:', userData);
                    // Refresh page to show confirmation step
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            });
            fayda.mount();
        });
        <?php endif; ?>
    </script>
</body>
</html>
