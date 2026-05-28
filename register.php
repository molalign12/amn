<?php
require_once __DIR__ . '/bootstrap.php';

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['password'] !== $_POST['confirm_password']) {
        $error = "Passwords do not match!";
    } else {
        $sexMap = ['M' => 'male', 'F' => 'female', 'O' => 'other'];
        $sex    = $sexMap[$_POST['sex'] ?? ''] ?? 'other';
        $phone  = trim($_POST['phone'] ?? '');
        if ($phone !== '' && $phone[0] !== '+') {
            $phone = '+251' . preg_replace('/\D/', '', $phone);
        }
        try {
            User::create([
                'fname' => trim($_POST['fname']),
                'lname' => trim($_POST['lname']),
                'email' => trim($_POST['email']),
                'phone' => $phone,
                'address' => trim($_POST['address']),
                'age' => (int)$_POST['age'],
                'sex' => $sex,
                'username' => trim($_POST['username']),
                'password' => $_POST['password'],
                'role' => 'customer',
                'security_question' => trim($_POST['security_question']),
                'security_answer' => trim($_POST['security_answer'])
            ]);
            header("Location: login.php?registered=1");
            exit;
        } catch (PDOException $e) {
            $sqlCode = $e->errorInfo[1] ?? 0;
            if ($sqlCode === 1062 || $e->getCode() === '23000') {
                $error = 'That username or email is already registered. Try signing in or use different details.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Amnen Guest House</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
        }

        html {
          width: 100%;
          height: 100%;
          overflow: hidden;
        }

        body {
          width: 100vw;
          height: 100vh;
          overflow: hidden;
          margin: 0;
          padding: 0;
          font-family: 'DM Sans', sans-serif;
        }

        /* Allow white panel to scroll on register page */
        body.page-register .panel-white {
          overflow-y: auto;
          height: 100vh;
        }
        body.page-register .panel-white::-webkit-scrollbar { width: 4px; }
        body.page-register .panel-white::-webkit-scrollbar-thumb {
          background: rgba(212,168,67,0.3);
          border-radius: 99px;
        }

        /* Page transition overlay */
        .page-transition-overlay {
          position: fixed;
          inset: 0;
          z-index: 99999;
          pointer-events: none;
          display: flex;
        }
        .overlay-dark {
          width: 50%;
          height: 100%;
          background: linear-gradient(160deg, #1A1A1A 0%, #2C2417 55%, #3D2E0F 100%);
          transform: translateY(-100%);
          transition: transform 600ms cubic-bezier(0.76, 0, 0.24, 1);
        }
        .overlay-white {
          width: 50%;
          height: 100%;
          background: #FFFFFF;
          transform: translateY(100%);
          transition: transform 600ms cubic-bezier(0.76, 0, 0.24, 1);
        }
        .page-transition-overlay.active .overlay-dark,
        .page-transition-overlay.active .overlay-white {
          transform: translateY(0%);
        }
        
        /* Layout replaced by auth-layout */

        /* Left Panel Decor */
        .orb { position: absolute; border-radius: 50%; z-index: 1; }
        .orb-1 { width: 400px; height: 400px; top: -100px; left: -100px; background: rgba(212,168,67,0.15); filter: blur(80px); animation: float1 8s ease-in-out infinite; z-index: 1; }
        .orb-2 { width: 300px; height: 300px; bottom: 100px; right: -80px; background: rgba(212,168,67,0.10); filter: blur(60px); animation: float2 10s ease-in-out infinite; z-index: 1; }
        .orb-3 { width: 200px; height: 200px; top: 50%; left: 40%; background: rgba(255,255,255,0.04); filter: blur(40px); animation: float1 6s ease-in-out infinite reverse; z-index: 1; }

        .panel-dark-inner {
          width: 100%;
          height: 100%;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          padding: 48px 44px;
          position: relative;
          z-index: 2;
          text-align: center;
          color: white;
          animation: slideInLeft 800ms cubic-bezier(0.19,1,0.22,1) both;
        }
        .logo-mark { width: 64px; height: 64px; background: #D4A843; border-radius: 14px; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1A1A1A; animation: pulse-gold 2s ease-in-out infinite; }
        .brand-title { font-family: 'Playfair Display', serif; font-style: italic; font-size: 2rem; margin-top: 24px; }
        .brand-subtitle { font-size: 15px; color: rgba(255,255,255,0.5); margin-top: 8px; }
        .divider { width: 60px; height: 1px; background: rgba(212,168,67,0.4); margin: 32px auto; }
        .feature-list { display: flex; flex-direction: column; gap: 16px; align-items: flex-start; max-width: 280px; margin: 0 auto; }
        .feature-row { display: flex; gap: 12px; align-items: center; text-align: left; }
        .feature-dot { width: 8px; height: 8px; border-radius: 50%; background: #D4A843; min-width: 8px; }
        .feature-text { font-size: 14px; color: rgba(255,255,255,0.65); }

        /* Right Panel Content */
        .back-link { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; color: #6B6B6B; text-decoration: none; margin-bottom: 40px; transition: color 200ms; }
        .back-link:hover { color: #D4A843; }
        .form-header h1 { font-family: 'Playfair Display', serif; font-size: 3rem; font-weight: 700; color: #1A1A1A; animation: slideInRight 700ms ease both 100ms; }
        .form-header p { font-size: 16px; color: #6B6B6B; margin-top: 10px; line-height: 1.6; animation: slideInRight 700ms ease both 200ms; margin-bottom: 32px; }

        .form-row { display: flex; gap: 16px; margin-bottom: 20px; }
        .form-col { flex: 1; }
        
        .input-group { display: flex; flex-direction: column; position: relative; animation: fadeUp 600ms ease both; }
        .delay-1 { animation-delay: 200ms; } .delay-2 { animation-delay: 250ms; } .delay-3 { animation-delay: 300ms; } .delay-4 { animation-delay: 350ms; } .delay-5 { animation-delay: 400ms; }

        .input-label { font-size: 11px; font-weight: 600; letter-spacing: 0.12em; text-transform: uppercase; color: #9A9090; margin-bottom: 8px; }
        .input-wrapper { position: relative; }
        .input-field { width: 100%; padding: 14px 16px; border: 1.5px solid #E8E2D9; border-radius: 10px; font-family: 'DM Sans', sans-serif; font-size: 15px; color: #1A1A1A; background: #FAFAF8; outline: none; transition: border-color 250ms, box-shadow 250ms, transform 200ms, background 250ms; }
        .has-icon { padding-left: 44px; }
        .input-field:focus { border-color: #D4A843; box-shadow: 0 0 0 4px rgba(212,168,67,0.12); background: #FFFFFF; transform: translateY(-1px); }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #BBBBB0; font-size: 16px; pointer-events: none; }
        .input-field.error { border-color: #C4623A; box-shadow: 0 0 0 4px rgba(196,98,58,0.12); }
        .error-msg { font-size: 12px; color: #C4623A; margin-top: 4px; display: none; }
        .input-field.error + .error-msg, .input-field.error ~ .error-msg { display: block; }

        .password-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #BBBBB0; cursor: pointer; transition: color 200ms; }
        .password-toggle:hover { color: #1A1A1A; }
        
        .prefix { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #1A1A1A; font-weight: 500; font-size: 15px; pointer-events: none; display: flex; gap: 6px; align-items: center; }

        /* Password Strength */
        .strength-meter { margin-top: 8px; display: flex; align-items: center; gap: 8px; }
        .strength-bars { display: flex; gap: 4px; flex: 1; height: 4px; border-radius: 2px; overflow: hidden; background: #E8E2D9; }
        .strength-bar { height: 100%; width: 0; transition: width 300ms, background 300ms; }
        .strength-text { font-size: 11px; font-weight: 600; color: #9A9090; text-transform: uppercase; width: 50px; text-align: right; }

        /* Checkbox */
        .checkbox-container { display: flex; align-items: flex-start; gap: 10px; margin-top: 24px; animation: fadeUp 600ms ease both 450ms; cursor: pointer; }
        .checkbox-container input { display: none; }
        .checkmark { width: 20px; height: 20px; border: 1.5px solid #E8E2D9; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: #FAFAF8; transition: all 200ms; }
        .checkbox-container input:checked + .checkmark { background: #D4A843; border-color: #D4A843; }
        .checkmark::after { content: '\f00c'; font-family: 'Font Awesome 6 Free'; font-weight: 900; color: #1A1A1A; font-size: 12px; display: none; }
        .checkbox-container input:checked + .checkmark::after { display: block; }
        .terms-text { font-size: 13px; color: #6B6B6B; line-height: 1.5; }
        .terms-text a { color: #D4A843; text-decoration: none; font-weight: 600; }

        /* Submit Button */
        .btn-submit { width: 100%; padding: 16px; border-radius: 12px; background: linear-gradient(135deg, #D4A843, #F0D98A, #D4A843); background-size: 200% 100%; color: #1A1A1A; font-weight: 600; font-size: 16px; font-family: 'DM Sans', sans-serif; border: none; cursor: pointer; transition: all 300ms; margin-top: 32px; position: relative; animation: fadeUp 600ms ease both 500ms; display: flex; justify-content: center; align-items: center; gap: 12px; }
        .btn-submit:hover { background-position: 100% 0; transform: translateY(-3px); box-shadow: 0 8px 32px rgba(212,168,67,0.40); }
        .btn-submit:active { transform: translateY(-1px); }
        .btn-submit:disabled { opacity: 0.8; pointer-events: none; }
        
        .signin-link { font-size: 14px; color: #6B6B6B; text-align: center; margin-top: 20px; animation: fadeUp 600ms ease both 550ms; }
        .signin-link a { color: #D4A843; font-weight: 700; text-decoration: none; }

        .banner-error { background: rgba(196,98,58,0.1); color: #C4623A; padding: 16px; border-radius: 10px; margin-bottom: 24px; font-size: 14px; display: flex; align-items: center; gap: 12px; animation: shake 400ms; border: 1px solid rgba(196,98,58,0.2); }

        /* Overlays */
        .success-overlay { position: absolute; inset: 0; background: #FFFFFF; z-index: 100; display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; visibility: hidden; transition: all 500ms; }
        .success-overlay.active { opacity: 1; visibility: visible; }
        .check-circle { stroke-dasharray: 1000; stroke-dashoffset: 1000; animation: drawCheck 1.5s ease forwards; }
        
        .loader-svg { animation: spin 1s linear infinite; }

        /* Keyframes */
        @keyframes slideInLeft  { from { opacity:0; transform:translateX(-50px); } to { opacity:1; transform:translateX(0); } }
        @keyframes slideInRight { from { opacity:0; transform:translateX(50px);  } to { opacity:1; transform:translateX(0); } }
        @keyframes fadeUp       { from { opacity:0; transform:translateY(24px);  } to { opacity:1; transform:translateY(0); } }
        @keyframes float1       { 0%,100% { transform:translateY(0) rotate(0deg);   } 50% { transform:translateY(-20px) rotate(5deg);  } }
        @keyframes float2       { 0%,100% { transform:translateY(0) rotate(0deg);   } 50% { transform:translateY(20px)  rotate(-5deg); } }
        @keyframes pulse-gold   { 0%,100% { box-shadow:0 0 0 0   rgba(212,168,67,0.5); } 50% { box-shadow:0 0 0 16px rgba(212,168,67,0); } }
        @keyframes shake        { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)} 40%{transform:translateX(8px)} 60%{transform:translateX(-6px)} 80%{transform:translateX(6px)} }
        @keyframes drawCheck    { to { stroke-dashoffset: 0; } }
        @keyframes spin         { to { transform:rotate(360deg); } }
        
        /* Page Loader */
        #page-loader { position:fixed; inset:0; z-index:99999; background: #1A1A1A; display:flex; align-items:center; justify-content:center; transition: opacity 500ms, visibility 500ms; }
        .loader-bar { height:100%; background:#D4A843; border-radius:99px; animation: loadBar 1s ease-in-out infinite; width:40%; }
        @keyframes loadBar { 0% { transform: translateX(-100%); } 100% { transform: translateX(350%); } }
/* Main layout panels animate on page load */
.auth-layout {
  display: flex;
  flex-direction: row;
  width: 100vw;
  height: 100vh;
  overflow: hidden;
  position: relative;
  opacity: 0;
  transition: opacity 400ms ease;
}
.auth-layout.loaded {
  opacity: 1;
}

/* Dark panel base */
.panel-dark {
  width: 42%;
  min-width: 42%;
  max-width: 42%;
  height: 100vh;
  overflow: hidden;
  position: relative;
  flex-shrink: 0;
  background: linear-gradient(160deg, #1A1A1A 0%, #2C2417 55%, #3D2E0F 100%);
}

/* White form panel base */
.panel-white {
  width: 58%;
  min-width: 58%;
  max-width: 58%;
  height: 100vh;
  overflow-y: auto;
  overflow-x: hidden;
  flex-shrink: 0;
  background: #FFFFFF;
}

.form-inner {
  padding: 40px 56px;
  display: flex;
  flex-direction: column;
}

/* LOGIN PAGE: dark left, white right — slide in from left */
body.page-login .panel-dark {
  animation: panelSlideFromLeft 800ms cubic-bezier(0.19, 1, 0.22, 1) both;
  animation-delay: 100ms;
}
body.page-login .panel-white {
  animation: panelSlideFromRight 800ms cubic-bezier(0.19, 1, 0.22, 1) both;
  animation-delay: 100ms;
}

/* REGISTER PAGE: white left, dark right — REVERSED */
body.page-register .panel-dark {
  animation: panelSlideFromRight 800ms cubic-bezier(0.19, 1, 0.22, 1) both;
  animation-delay: 100ms;
}
body.page-register .panel-white {
  animation: panelSlideFromLeft 800ms cubic-bezier(0.19, 1, 0.22, 1) both;
  animation-delay: 100ms;
}

@keyframes panelSlideFromLeft {
  from { opacity: 0; transform: translateX(-60px); }
  to   { opacity: 1; transform: translateX(0); }
}
@keyframes panelSlideFromRight {
  from { opacity: 0; transform: translateX(60px); }
  to   { opacity: 1; transform: translateX(0); }
}

/* Content inside panels also animate */
.panel-content {
  animation: fadeUp 700ms cubic-bezier(0.19, 1, 0.22, 1) both;
  animation-delay: 350ms;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(28px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* Parallax effect on scroll inside the form panel */
.panel-dark .orb {
  transition: transform 0.1s linear;
  will-change: transform;
}

@media (max-width: 768px) {
  .auth-layout {
    flex-direction: column;
    height: auto;
    min-height: 100vh;
    overflow-y: auto;
  }
  .panel-dark {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    height: 280px;
    min-height: 280px;
  }
  .panel-white {
    width: 100%;
    min-width: 100%;
    max-width: 100%;
    height: auto;
    overflow-y: visible;
  }
  .form-inner {
    padding: 36px 24px;
  }
  body, html {
    overflow-y: auto;
    height: auto;
  }
}
    </style>
</head>
<body class="page-register">
    <div id="page-loader">
        <div style="text-align:center;">
            <div class="logo-mark" style="margin-bottom: 20px;">A</div>
            <div style="width:40px; height:3px; background:#333; border-radius:99px; overflow:hidden; margin:0 auto;">
                <div class="loader-bar"></div>
            </div>
        </div>
    </div>

    <div class="auth-layout">
        <div class="panel-white">
            <div class="form-inner panel-content">
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Home</a>
            
            <div class="form-header">
                <h1>Join Amnen</h1>
                <p>Create your account to book your stay in Hawassa.</p>
            </div>

            <?php if($error): ?>
                <div class="banner-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="registerForm" method="POST" autocomplete="off" novalidate onsubmit="return validateForm()">
                <div class="form-row">
                    <div class="form-col input-group delay-1">
                        <label class="input-label">First Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="fname" class="input-field" id="fname" required>
                            <div class="error-msg">Required field</div>
                        </div>
                    </div>
                    <div class="form-col input-group delay-1">
                        <label class="input-label">Last Name</label>
                        <div class="input-wrapper">
                            <input type="text" name="lname" class="input-field" id="lname" required>
                            <div class="error-msg">Required field</div>
                        </div>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-col input-group delay-2">
                        <label class="input-label">Email Address</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-envelope"></i>
                            <input type="email" name="email" class="input-field has-icon" id="email" required>
                            <div class="error-msg">Valid email required</div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col input-group delay-3">
                        <label class="input-label">Phone Number</label>
                        <div class="input-wrapper">
                            <div class="prefix">🇪🇹 +251</div>
                            <input type="tel" name="phone" class="input-field" style="padding-left:90px;" id="phone" required placeholder="911 234 567">
                            <div class="error-msg">Number must be valid</div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col input-group delay-4">
                        <label class="input-label">Username</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-user"></i>
                            <input type="text" name="username" class="input-field has-icon" id="username" required>
                            <div class="error-msg">Min 3 chars, no spaces</div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col input-group delay-4">
                        <label class="input-label">Password</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-lock"></i>
                            <input type="password" name="password" class="input-field has-icon" id="password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePwd('password', this)"></i>
                            <div class="error-msg">Minimum 8 characters</div>
                        </div>
                        <div class="strength-meter">
                            <div class="strength-bars">
                                <div class="strength-bar" id="str-bar"></div>
                            </div>
                            <div class="strength-text" id="str-text">Weak</div>
                        </div>
                    </div>
                    <div class="form-col input-group delay-4">
                        <label class="input-label">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="input-icon fas fa-shield-check"></i>
                            <input type="password" name="confirm_password" class="input-field has-icon" id="confirm_password" required>
                            <i class="fas fa-eye password-toggle" onclick="togglePwd('confirm_password', this)"></i>
                            <div class="error-msg">Passwords must match</div>
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col input-group delay-5">
                        <label class="input-label">Address</label>
                        <div class="input-wrapper">
                            <input type="text" name="address" class="input-field" id="address" required>
                            <div class="error-msg">Required field</div>
                        </div>
                    </div>
                    <div class="form-col input-group delay-5" style="max-width: 100px;">
                        <label class="input-label">Age</label>
                        <div class="input-wrapper">
                            <input type="number" name="age" class="input-field" id="age" min="1" max="120" required>
                            <div class="error-msg">Invalid</div>
                        </div>
                    </div>
                    <div class="form-col input-group delay-5" style="max-width: 150px;">
                        <label class="input-label">Sex</label>
                        <div class="input-wrapper">
                            <select name="sex" class="input-field" style="padding-right: 16px;">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                                <option value="O">Other</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Recovery Information -->
                <div class="form-row">
                    <div class="form-col input-group delay-5">
                        <label class="input-label">Security Question (For Recovery)</label>
                        <div class="input-wrapper">
                            <select name="security_question" class="input-field" id="security_question" required>
                                <option value="" disabled selected>Select a question</option>
                                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                                <option value="In what city were you born?">In what city were you born?</option>
                                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                                <option value="What was the name of your first school?">What was the name of your first school?</option>
                                <option value="What is your favorite book?">What is your favorite book?</option>
                            </select>
                             <div class="error-msg">Please select a question</div>
                        </div>
                    </div>
                    <div class="form-col input-group delay-5">
                        <label class="input-label">Security Answer</label>
                        <div class="input-wrapper">
                            <input type="text" name="security_answer" class="input-field" id="security_answer" placeholder="Your answer" required>
                            <div class="error-msg">Answer is required</div>
                        </div>
                    </div>
                </div>

                <label class="checkbox-container">
                    <input type="checkbox" id="terms" required>
                    <div class="checkmark"></div>
                    <div class="terms-text">I agree to the <a href="terms.php" target="_blank">Terms of Service</a> and <a href="privacy.php" target="_blank">Privacy Policy</a></div>
                </label>

                <button type="submit" class="btn-submit" id="submitBtn">
                    <span id="btn-text">Create Account</span>
                </button>
                
                <div class="signin-link">
                    Already have an account? <a href="/amnen/login.php" class="auth-nav-link">Sign In</a>
                </div>
            </form>
            
            <div class="success-overlay" id="successLayer">
                <svg width="120" height="120" viewBox="0 0 120 120" fill="none" style="margin-bottom: 24px;">
                    <circle cx="60" cy="60" r="54" stroke="#D4A843" stroke-width="4"/>
                    <path class="check-circle" d="M35 60L52 77L85 44" stroke="#D4A843" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <h2 style="font-family:'Playfair Display',serif; font-size:32px; color:#1A1A1A; margin-bottom:8px;">Account Created!</h2>
                <p style="color:#6B6B6B; font-size:16px;">Redirecting you to login...</p>
                <svg class="loader-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#D4A843" stroke-width="3" stroke-linecap="round" style="margin-top:24px;">
                    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
            </div>
            </div>
        </div>

        <div class="panel-dark">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            
            <div class="panel-dark-inner">
                <div class="logo-mark">A</div>
                <div class="brand-title">Amnen Guest House</div>
                <div class="brand-subtitle">Your sanctuary in Hawassa, Ethiopia</div>
                <div class="divider"></div>
                
                <div class="feature-list">
                    <div class="feature-row">
                        <div class="feature-dot"></div>
                        <div class="feature-text">Book rooms 24/7 from anywhere</div>
                    </div>
                    <div class="feature-row">
                        <div class="feature-dot"></div>
                        <div class="feature-text">Secure mobile banking payments</div>
                    </div>
                    <div class="feature-row">
                        <div class="feature-dot"></div>
                        <div class="feature-text">Real-time availability updates</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const loader = document.getElementById('page-loader');
            loader.style.opacity = '0'; loader.style.visibility = 'hidden';
            setTimeout(() => loader.remove(), 500);
        });

        function togglePwd(id, icon) {
            const pwd = document.getElementById(id);
            if(pwd.type === 'password') { pwd.type = 'text'; icon.className = 'fas fa-eye-slash password-toggle'; }
            else { pwd.type = 'password'; icon.className = 'fas fa-eye password-toggle'; }
        }

        // Password Strength
        const pwdInput = document.getElementById('password');
        const strBar = document.getElementById('str-bar');
        const strText = document.getElementById('str-text');
        
        pwdInput.addEventListener('input', function() {
            const val = this.value;
            let str = 0;
            if(val.length > 5) str++;
            if(val.length >= 8) str++;
            if(/[A-Z]/.test(val) && /[0-9]/.test(val)) str++;
            if(/[^A-Za-z0-9]/.test(val)) str++;
            
            if(val.length === 0) { strBar.style.width = '0'; strText.innerText = 'WEAK'; return; }
            
            if(str <= 1) { strBar.style.width = '25%'; strBar.style.background = '#C4623A'; strText.innerText = 'WEAK'; }
            else if(str === 2) { strBar.style.width = '50%'; strBar.style.background = '#F5A623'; strText.innerText = 'FAIR'; }
            else if(str === 3) { strBar.style.width = '75%'; strBar.style.background = '#D4A843'; strText.innerText = 'GOOD'; }
            else { strBar.style.width = '100%'; strBar.style.background = '#2ECC71'; strText.innerText = 'STRONG'; }
        });

        // Validation Rules
        const rules = {
            fname: val => val.trim().length > 0,
            lname: val => val.trim().length > 0,
            email: val => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
            phone: val => val.replace(/[^0-9]/g, '').length >= 9,
            username: val => val.length >= 3 && !/\s/.test(val),
            password: val => val.length >= 8,
            confirm_password: val => val === document.getElementById('password').value,
            address: val => val.trim().length > 0,
            age: val => parseInt(val) >= 1 && parseInt(val) <= 120,
            security_question: val => val !== null && val !== '',
            security_answer: val => val.trim().length > 0
        };

        const form = document.getElementById('registerForm');
        
        function validateField(id) {
            const input = document.getElementById(id);
            if(!input) return true;
            const valid = rules[id](input.value);
            if(!valid) input.classList.add('error');
            else input.classList.remove('error');
            return valid;
        }

        ['fname', 'lname', 'email', 'phone', 'username', 'password', 'confirm_password', 'address', 'age', 'security_question', 'security_answer'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('blur', () => validateField(id));
            el.addEventListener('input', () => el.classList.remove('error'));
        });

        function validateForm() {
            let isValid = true;
            Object.keys(rules).forEach(id => { if(!validateField(id)) isValid = false; });
            const terms = document.getElementById('terms');
            if(!terms.checked) isValid = false;
            
            if(!isValid) {
                form.style.animation = 'none';
                form.offsetHeight; /* trigger reflow */
                form.style.animation = 'shake 400ms';
                return false;
            }

            // Show success logic via loading btn
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = `<svg class="loader-svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg> Creating account...`;
            
            // Allow form to submit natively (so PHP handles it)
            return true;
        }
    </script>
    <script>
// ── Page load reveal ──────────────────────────────────────
window.addEventListener('load', () => {
  document.querySelector('.auth-layout').classList.add('loaded');
});

// ── Intercepted navigation with morphing transition ───────
function navigateWithTransition(targetUrl) {
  // Create overlay
  const overlay = document.createElement('div');
  overlay.className = 'page-transition-overlay';
  
  // Determine which direction panels should slide based on destination
  const goingToRegister = targetUrl.includes('register');
  
  overlay.innerHTML = `
    <div class="overlay-dark"  style="transform: translateY(${goingToRegister ? '-100%' : '100%'})"></div>
    <div class="overlay-white" style="transform: translateY(${goingToRegister ? '100%' : '-100%'})"></div>
  `;
  document.body.appendChild(overlay);

  // Trigger transition
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      overlay.querySelector('.overlay-dark').style.transform  = 'translateY(0%)';
      overlay.querySelector('.overlay-white').style.transform = 'translateY(0%)';
      overlay.querySelector('.overlay-dark').style.transition  = 'transform 550ms cubic-bezier(0.76, 0, 0.24, 1)';
      overlay.querySelector('.overlay-white').style.transition = 'transform 550ms cubic-bezier(0.76, 0, 0.24, 1)';
    });
  });

  // Navigate after panels cover screen
  setTimeout(() => {
    window.location.href = targetUrl;
  }, 560);
}

// ── Intercept all auth navigation links ───────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Intercept "Register" / "Sign In" / "Back to Home" links
  document.querySelectorAll('a[href*="register"], a[href*="login"]').forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      // Only intercept local auth page links
      if (href && (href.includes('register') || href.includes('login')) && !href.startsWith('http')) {
        e.preventDefault();
        navigateWithTransition(href);
      }
    });
  });
});

// ── Orb parallax on mouse move ────────────────────────────
const darkPanel = document.querySelector('.panel-dark');
if (darkPanel) {
  document.addEventListener('mousemove', (e) => {
    const orbs = darkPanel.querySelectorAll('.orb');
    const x = (e.clientX / window.innerWidth  - 0.5) * 30;
    const y = (e.clientY / window.innerHeight - 0.5) * 30;
    orbs.forEach((orb, i) => {
      const depth = (i + 1) * 0.4;
      orb.style.transform = `translate(${x * depth}px, ${y * depth}px)`;
    });
  });
}
    </script>
</body>
</html>
