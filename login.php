<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_save_path('C:/xampp/tmp');
    session_start();
}
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role==='admin')        { header('Location:/amnen/views/admin/dashboard.php');        exit; }
    if ($role==='manager')      { header('Location:/amnen/views/manager/dashboard.php');      exit; }
    if ($role==='receptionist') { header('Location:/amnen/views/receptionist/dashboard.php'); exit; }
    header('Location:/amnen/views/guest/home.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    require_once __DIR__ . '/config/db.php';
    require_once __DIR__ . '/classes/User.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$username || !$password) {
        $error = 'Please enter your username and password.';
    } else {
        $user = User::login($username, $password);
        if ($user) {
            $_SESSION['user']    = $user;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role']    = $user['role'];
            $role = $user['role'];
            if ($role==='admin')        { header('Location:/amnen/views/admin/dashboard.php');        exit; }
            if ($role==='manager')      { header('Location:/amnen/views/manager/dashboard.php');      exit; }
            if ($role==='receptionist') { header('Location:/amnen/views/receptionist/dashboard.php'); exit; }
            header('Location:/amnen/views/guest/home.php'); exit;
        } else {
            $error = 'Invalid username or password. Please try again.';
        }
    }
}

$registered = isset($_GET['registered']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Sign In — Amnen Guest House</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;1,400&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ─── RESET ─────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

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
  -webkit-font-smoothing: antialiased;
}

/* ─── PAGE TRANSITION OVERLAY ────────────────────────────── */
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

/* ─── LAYOUT ─────────────────────────────────────────────── */
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
.auth-layout.loaded { opacity: 1; }

/* ─── DARK PANEL ─────────────────────────────────────────── */
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

/* ─── WHITE FORM PANEL ───────────────────────────────────── */
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
.panel-white::-webkit-scrollbar { width: 4px; }
.panel-white::-webkit-scrollbar-thumb {
  background: rgba(212,168,67,0.3);
  border-radius: 99px;
}

/* ─── PANEL ORDER: login = dark LEFT, white RIGHT ─────────── */
body.page-login .panel-dark  { order: 1; }
body.page-login .panel-white { order: 2; }

/* ─── SLIDE-IN ANIMATIONS ────────────────────────────────── */
body.page-login .panel-dark {
  animation: panelSlideFromLeft 800ms cubic-bezier(0.19,1,0.22,1) both;
  animation-delay: 80ms;
}
body.page-login .panel-white {
  animation: panelSlideFromRight 800ms cubic-bezier(0.19,1,0.22,1) both;
  animation-delay: 80ms;
}

@keyframes panelSlideFromLeft {
  from { opacity:0; transform:translateX(-60px); }
  to   { opacity:1; transform:translateX(0); }
}
@keyframes panelSlideFromRight {
  from { opacity:0; transform:translateX(60px); }
  to   { opacity:1; transform:translateX(0); }
}

/* ─── ORBS ───────────────────────────────────────────────── */
.orb { position: absolute; border-radius: 50%; z-index: 1; pointer-events: none; transition: transform 0.12s linear; }
.orb-1 { width:420px; height:420px; top:-120px; left:-80px;  background:rgba(212,168,67,0.14); filter:blur(80px); animation:float1 9s  ease-in-out infinite; }
.orb-2 { width:320px; height:320px; bottom:-80px; right:-60px; background:rgba(212,168,67,0.09); filter:blur(65px); animation:float2 11s ease-in-out infinite; }
.orb-3 { width:200px; height:200px; top:45%; left:38%;       background:rgba(255,255,255,0.04); filter:blur(45px); animation:float1 7s  ease-in-out infinite reverse; }

@keyframes float1 { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-22px) rotate(5deg)} }
@keyframes float2 { 0%,100%{transform:translateY(0)}               50%{transform:translateY(22px)  rotate(-4deg)} }

/* ─── DARK PANEL CONTENT ─────────────────────────────────── */
.panel-dark-inner {
  width:100%; height:100%;
  display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  padding:48px 44px;
  position:relative; z-index:2;
  text-align:center; color:white;
  animation: panelSlideFromLeft 800ms cubic-bezier(0.19,1,0.22,1) both;
  animation-delay: 100ms;
}
.logo-mark {
  width:64px; height:64px; background:#D4A843;
  border-radius:14px; margin:0 auto;
  display:flex; align-items:center; justify-content:center;
  font-family:'Playfair Display',serif;
  font-size:32px; font-weight:700; color:#1A1A1A;
  animation:pulse-gold 2.5s ease-in-out infinite;
}
@keyframes pulse-gold {
  0%,100%{box-shadow:0 0 0 0   rgba(212,168,67,0.45)}
  50%    {box-shadow:0 0 0 16px rgba(212,168,67,0)}
}
.brand-title    { font-family:'Playfair Display',serif; font-style:italic; font-size:2rem; margin-top:24px; }
.brand-subtitle { font-size:15px; color:rgba(255,255,255,0.5); margin-top:8px; }
.divider        { width:60px; height:1px; background:rgba(212,168,67,0.4); margin:32px auto; }
.feature-list   { display:flex; flex-direction:column; gap:16px; align-items:flex-start; max-width:280px; margin:0 auto; }
.feature-row    { display:flex; gap:12px; align-items:center; text-align:left; }
.feature-dot    { width:8px; height:8px; border-radius:50%; background:#D4A843; min-width:8px; }
.feature-text   { font-size:14px; color:rgba(255,255,255,0.65); }

/* ─── FORM INNER ─────────────────────────────────────────── */
.form-inner {
  padding: 52px 56px;
  display: flex;
  flex-direction: column;
  min-height: 100%;
  justify-content: center;
}

/* ─── BACK LINK ──────────────────────────────────────────── */
.back-link {
  display:inline-flex; align-items:center; gap:6px;
  font-size:13px; color:#6B6B6B;
  text-decoration:none; margin-bottom:40px;
  transition:color 200ms;
}
.back-link:hover { color:#D4A843; }

/* ─── FORM HEADER ────────────────────────────────────────── */
.form-header { margin-bottom:32px; }
.form-header h1 {
  font-family:'Playfair Display',serif;
  font-size:clamp(2rem,4vw,3rem);
  font-weight:700; color:#1A1A1A;
  line-height:1.1;
  animation: slideInRight 700ms ease both 100ms;
}
.form-header p {
  font-size:16px; color:#6B6B6B;
  margin-top:10px; line-height:1.6;
  animation: slideInRight 700ms ease both 200ms;
}

@keyframes slideInRight { from{opacity:0;transform:translateX(50px)} to{opacity:1;transform:translateX(0)} }

/* ─── ROLE SELECTOR ──────────────────────────────────────── */
.role-grid {
  display:grid; grid-template-columns:1fr 1fr;
  gap:10px; margin-bottom:24px;
  animation: fadeUp 600ms ease both 200ms;
}
.role-card {
  display:flex; align-items:center; gap:12px;
  padding:14px 16px;
  border:1.5px solid #E8E2D9; border-radius:10px;
  cursor:pointer; background:#FAFAF8;
  transition:all 200ms;
  font-size:14px; font-weight:500; color:#6B6B6B;
}
.role-card:hover { border-color:#D4A843; color:#1A1A1A; transform:scale(1.02); }
.role-card.sel   { border-color:#D4A843; background:rgba(212,168,67,0.08); color:#1A1A1A; }
.role-card i     { font-size:16px; color:#BBBBB0; transition:color 200ms; }
.role-card.sel i { color:#D4A843; }

/* ─── BANNERS ────────────────────────────────────────────── */
.err-banner {
  background:#FFEBEE; border-left:4px solid #C4623A;
  border-radius:10px; padding:13px 16px;
  font-size:13.5px; color:#C4623A; margin-bottom:22px;
  display:flex; align-items:center; gap:10px;
  animation:shake 400ms ease both;
}
.ok-banner {
  background:#E6F4F1; border-left:4px solid #2A7A6F;
  border-radius:10px; padding:13px 16px;
  font-size:13.5px; color:#2A7A6F; margin-bottom:22px;
  display:flex; align-items:center; gap:10px;
}
@keyframes shake {
  0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)}
  40%{transform:translateX(8px)}   60%{transform:translateX(-5px)}
  80%{transform:translateX(5px)}
}

/* ─── FORM FIELDS ────────────────────────────────────────── */
.form-fields  { display:flex; flex-direction:column; gap:18px; }
.fg           { display:flex; flex-direction:column; gap:6px; animation:fadeUp 600ms ease both; }
.fg label     { font-size:11px; font-weight:700; letter-spacing:0.12em; text-transform:uppercase; color:#9A9090; }
.input-wrapper { position:relative; }
.input-wrapper > .input-icon {
  position:absolute; left:15px; top:50%;
  transform:translateY(-50%);
  color:#BBBBB0; font-size:15px; pointer-events:none;
}
.input-field {
  width:100%; padding:13px 16px 13px 44px;
  border:1.5px solid #E8E2D9; border-radius:10px;
  font-family:'DM Sans',sans-serif; font-size:15px; color:#1A1A1A;
  background:#FAFAF8; outline:none;
  transition:border-color 250ms, box-shadow 250ms, transform 200ms, background 200ms;
}
.input-field:focus {
  border-color:#D4A843; background:#fff;
  box-shadow:0 0 0 4px rgba(212,168,67,0.12);
  transform:translateY(-1px);
}
.input-field.error {
  border-color:#C4623A;
  box-shadow:0 0 0 4px rgba(196,98,58,0.1);
}
.field-err { font-size:12px; color:#C4623A; margin-top:4px; }

/* ─── EYE TOGGLE ─────────────────────────────────────────── */
.password-toggle {
  position:absolute; right:14px; top:50%;
  transform:translateY(-50%);
  background:none; border:none; cursor:pointer;
  color:#BBBBB0; font-size:15px;
  transition:color 200ms; padding:4px;
}
.password-toggle:hover { color:#D4A843; }

/* ─── REMEMBER ROW ───────────────────────────────────────── */
.options-row {
  display:flex; align-items:center;
  justify-content:space-between;
  animation:fadeUp 600ms ease both 320ms;
}
.remember-me {
  display:flex; align-items:center; gap:9px;
  cursor:pointer; font-size:13.5px; color:#6B6B6B;
}
.remember-me input[type=checkbox] {
  width:17px; height:17px;
  accent-color:#D4A843; cursor:pointer;
}
.forgot-pass {
  font-size:13px; color:#D4A843; font-weight:500;
  text-decoration:none; transition:opacity 200ms;
}
.forgot-pass:hover { opacity:0.7; }

/* ─── SUBMIT BUTTON ──────────────────────────────────────── */
.btn-submit {
  width:100%; padding:15px; border-radius:12px;
  background:linear-gradient(135deg,#D4A843,#F0D98A,#D4A843);
  background-size:200% 100%;
  color:#1A1A1A; font-family:'DM Sans',sans-serif;
  font-size:16px; font-weight:700;
  border:none; cursor:pointer;
  display:flex; align-items:center;
  justify-content:center; gap:10px;
  transition:all 300ms;
  box-shadow:0 4px 18px rgba(212,168,67,0.3);
  animation:fadeUp 600ms ease both 400ms;
}
.btn-submit:hover {
  background-position:100% 0;
  transform:translateY(-3px);
  box-shadow:0 8px 32px rgba(212,168,67,0.45);
}
.btn-submit:active  { transform:translateY(-1px); }
.btn-submit:disabled{ opacity:0.6; cursor:not-allowed; }

/* ─── SWITCH LINK ────────────────────────────────────────── */
.signin-link {
  text-align:center; font-size:14px; color:#6B6B6B;
  margin-top:20px;
  animation:fadeUp 600ms ease both 500ms;
}
.signin-link a {
  color:#D4A843; font-weight:700;
  text-decoration:none; transition:opacity 200ms;
}
.signin-link a:hover { opacity:0.75; }

/* ─── KEYFRAMES ──────────────────────────────────────────── */
@keyframes fadeUp {
  from{opacity:0;transform:translateY(24px)}
  to  {opacity:1;transform:translateY(0)}
}
@keyframes spin { to { transform:rotate(360deg); } }

/* ─── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:768px){
  .auth-layout{flex-direction:column;height:auto;overflow-y:auto}
  html,body  {overflow-y:auto;height:auto}
  .panel-dark{width:100%;min-width:100%;max-width:100%;height:280px;min-height:280px}
  body.page-login .panel-dark,
  body.page-login .panel-white{order:unset}
  .panel-dark {order:-1 !important}
  .panel-white{width:100%;min-width:100%;max-width:100%;height:auto;overflow-y:visible}
  .form-inner {padding:36px 24px}
  .role-grid  {grid-template-columns:1fr 1fr}
}
@media(max-width:480px){
  .form-inner {padding:28px 20px}
}
</style>
</head>
<body class="page-login">

<div class="auth-layout" id="auth-layout">

  <!-- ═══ DARK PANEL (LEFT on login) ════════════════════════ -->
  <div class="panel-dark">
    <div class="orb orb-1" id="orb1"></div>
    <div class="orb orb-2" id="orb2"></div>
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

  <!-- ═══ WHITE FORM PANEL (RIGHT on login) ════════════════ -->
  <div class="panel-white">
    <div class="form-inner">

      <a href="/amnen/index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Home
      </a>

      <div class="form-header">
        <h1>Welcome Back</h1>
        <p>Sign in using your username, email, or phone number.</p>
      </div>


      <?php if($error): ?>
      <div class="err-banner">
        <i class="fas fa-circle-xmark"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <?php if($registered): ?>
      <div class="ok-banner">
        <i class="fas fa-circle-check"></i>
        Account created! Please sign in.
      </div>
      <?php endif; ?>

      <form class="form-fields" method="POST" id="loginForm" novalidate>

        <div class="fg" style="animation-delay:80ms">
          <label for="usernameInput">Username, Email or Phone</label>
          <div class="input-wrapper">
            <i class="input-icon fas fa-user"></i>
            <input type="text"
                   class="input-field"
                   name="username"
                   id="usernameInput"
                   placeholder="Username, email or phone"
                   autocomplete="username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   required>
          </div>
          <div class="field-err" id="username-err"></div>
        </div>

        <div class="fg" style="animation-delay:160ms">
          <label for="passwordInput">Password</label>
          <div class="input-wrapper">
            <i class="input-icon fas fa-lock"></i>
            <input type="password"
                   class="input-field"
                   name="password"
                   id="passwordInput"
                   placeholder="••••••••"
                   autocomplete="current-password"
                   required>
            <button type="button"
                    class="password-toggle"
                    id="eyeBtn"
                    aria-label="Toggle password visibility">
              <i class="fas fa-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div class="field-err" id="pass-err"></div>
        </div>

        <div class="options-row">
          <label class="remember-me">
            <input type="checkbox" name="remember">
            Remember me
          </label>
          <a href="/amnen/forgot-password.php" class="forgot-pass">Forgot password?</a>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
          <i class="fas fa-arrow-right-to-bracket"></i>
          Sign In
        </button>

      </form>

      <p class="signin-link">
        Don't have an account?
        <a href="/amnen/register.php" class="auth-nav-link">Register</a>
      </p>

    </div>
  </div>

</div><!-- /.auth-layout -->

<script>
// ── Page load reveal ────────────────────────────────────────
window.addEventListener('load', () => {
  document.getElementById('auth-layout').classList.add('loaded');
});


// ── Password toggle ──────────────────────────────────────────
document.getElementById('eyeBtn').addEventListener('click', function() {
  const inp = document.getElementById('passwordInput');
  const ico = document.getElementById('eyeIcon');
  const isPass = inp.type === 'password';
  inp.type = isPass ? 'text' : 'password';
  ico.className = isPass ? 'fas fa-eye-slash' : 'fas fa-eye';
});

// ── Client-side validation ───────────────────────────────────
document.getElementById('loginForm').addEventListener('submit', function(e) {
  let ok = true;
  const u    = document.getElementById('usernameInput');
  const p    = document.getElementById('passwordInput');
  const uErr = document.getElementById('username-err');
  const pErr = document.getElementById('pass-err');
  uErr.textContent = ''; pErr.textContent = '';
  u.classList.remove('error'); p.classList.remove('error');

  if (!u.value.trim()) {
    uErr.textContent = 'Username, email or phone is required.';
    u.classList.add('error'); ok = false;
  }
  if (!p.value) {
    pErr.textContent = 'Password is required.';
    p.classList.add('error'); ok = false;
  }
  if (!ok) { e.preventDefault(); return; }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
    stroke="currentColor" stroke-width="2"
    style="animation:spin 700ms linear infinite">
    <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
  </svg> Signing in…`;
});

// ── Orb parallax ─────────────────────────────────────────────
const darkPanel = document.querySelector('.panel-dark');
if (darkPanel) {
  document.addEventListener('mousemove', (e) => {
    const x = (e.clientX / window.innerWidth  - 0.5) * 30;
    const y = (e.clientY / window.innerHeight - 0.5) * 30;
    darkPanel.querySelectorAll('.orb').forEach((orb, i) => {
      const depth = (i + 1) * 0.4;
      orb.style.transform = `translate(${x * depth}px, ${y * depth}px)`;
    });
  });
}

// ── Morphing transition on navigation ────────────────────────
function navigateWithTransition(targetUrl) {
  const overlay = document.createElement('div');
  overlay.className = 'page-transition-overlay';
  const goingToRegister = targetUrl.includes('register');
  overlay.innerHTML = `
    <div class="overlay-dark"  style="transform:translateY(${goingToRegister ? '-100%' : '100%'})"></div>
    <div class="overlay-white" style="transform:translateY(${goingToRegister ? '100%'  : '-100%'})"></div>
  `;
  document.body.appendChild(overlay);
  requestAnimationFrame(() => {
    requestAnimationFrame(() => {
      overlay.querySelector('.overlay-dark').style.transform  = 'translateY(0%)';
      overlay.querySelector('.overlay-white').style.transform = 'translateY(0%)';
      overlay.querySelector('.overlay-dark').style.transition  = 'transform 550ms cubic-bezier(0.76,0,0.24,1)';
      overlay.querySelector('.overlay-white').style.transition = 'transform 550ms cubic-bezier(0.76,0,0.24,1)';
    });
  });
  setTimeout(() => { window.location.href = targetUrl; }, 560);
}

document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('a[href*="register"], a[href*="login"]').forEach(link => {
    link.addEventListener('click', function(e) {
      const href = this.getAttribute('href');
      if (href && (href.includes('register') || href.includes('login')) && !href.startsWith('http')) {
        e.preventDefault();
        navigateWithTransition(href);
      }
    });
  });
});
</script>
</body>
</html>