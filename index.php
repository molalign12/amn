<?php
require_once __DIR__ . '/bootstrap.php';

// If already logged in, redirect to their dashboard
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'] ?? 'customer';
    $map  = [
        'admin'        => '/amnen/views/admin/dashboard.php',
        'manager'      => '/amnen/views/manager/dashboard.php',
        'receptionist' => '/amnen/views/receptionist/dashboard.php',
        'customer'     => '/amnen/views/guest/home.php',
    ];
    header('Location: ' . ($map[$role] ?? '/amnen/views/guest/home.php'));
    exit;
}

$rooms = [];
$totalRoomCount = 0;
$avgRating = 0;
$happyGuests = 0;
$testimonials = [];

try {
    $rooms = Room::findAll('available');
    
    // Fetch real stats
    $db = getDB();
    $totalRoomCount = (int)$db->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
    
    $fbStats = Feedback::getStats();
    $avgRating = $fbStats['avg_rating'] > 0 ? round($fbStats['avg_rating'], 1) : 0;
    
    $happyGuests = Feedback::getUniqueCustomerCount();

    $testimonials = Feedback::getPublicTestimonials(3);
} catch (Exception $e) {
    // DB not set up yet — show page with empty rooms or fallbacks
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Amnen Guest House | Luxury Hotel in Hawassa, Ethiopia</title>
<meta name="description" content="Experience unparalleled luxury at Amnen Guest House in Hawassa, Ethiopia. World-class amenities, exceptional service, and unforgettable stays.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@400;500&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════
   GLOBAL RESETS & BASE
   ═══════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
    background: #0D0D0D;
    color: #F5F0E8;
    font-family: 'DM Sans', sans-serif;
    overflow-x: hidden;
    -webkit-font-smoothing: antialiased;
}
a { text-decoration: none; color: inherit; }
img { max-width: 100%; display: block; }

/* ═══════════════════════════════════════
   SCROLL ANIMATIONS
   ═══════════════════════════════════════ */
.fade-in-up {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 0.7s ease, transform 0.7s ease;
}
.fade-in-up.visible {
    opacity: 1;
    transform: translateY(0);
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(10px); }
}
.scroll-arrow { animation: bounce 1.8s ease-in-out infinite; }

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-20px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes pulse-glow {
    0%, 100% { box-shadow: 0 0 20px rgba(212,168,67,0.3); }
    50%      { box-shadow: 0 0 40px rgba(212,168,67,0.6); }
}

/* ═══════════════════════════════════════
   NAVBAR
   ═══════════════════════════════════════ */
#navbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 100;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 18px 48px;
    background: rgba(13,13,13,0.85);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid rgba(212,168,67,0.2);
    transition: all 0.35s ease;
}
#navbar.scrolled {
    background: rgba(13,13,13,0.98);
    border-bottom: 1px solid rgba(212,168,67,0.3);
    padding: 14px 48px;
}
.nav-brand {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #D4A843;
    letter-spacing: 4px;
}
.nav-actions {
    display: flex;
    gap: 14px;
    align-items: center;
}
.btn-signin {
    border: 1px solid #D4A843;
    color: #D4A843;
    background: transparent;
    padding: 8px 20px;
    border-radius: 99px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.25s ease;
}
.btn-signin:hover {
    background: rgba(212,168,67,0.12);
}
.btn-create {
    background: #D4A843;
    color: #1A1A1A;
    padding: 8px 20px;
    border-radius: 99px;
    border: none;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}
.btn-create:hover {
    background: #C49A35;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(212,168,67,0.35);
}

/* ═══════════════════════════════════════
   HERO SECTION
   ═══════════════════════════════════════ */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    z-index: 1;
}
.hero-overlay {
    position: absolute;
    inset: 0;
    background: rgba(13,13,13,0.55);
    z-index: 1;
    pointer-events: none;
}
.hero-content {
    position: relative;
    z-index: 2;
    max-width: 780px;
    padding: 0 24px;
    animation: fadeInUp 1s ease both 0.3s;
}
.hero-badge {
    display: inline-block;
    border: 1px solid #D4A843;
    color: #D4A843;
    font-size: 11px;
    letter-spacing: 3px;
    padding: 6px 14px;
    border-radius: 99px;
    margin-bottom: 28px;
    text-transform: uppercase;
    font-weight: 500;
}
.hero-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(3rem, 8vw, 6rem);
    line-height: 1.05;
    font-weight: 700;
    margin-bottom: 24px;
}
.hero-title .line1 { color: #F5F0E8; }
.hero-title .line2 { color: #D4A843; }
.hero-sub {
    font-size: 18px;
    color: rgba(245,240,232,0.7);
    max-width: 560px;
    margin: 0 auto 40px;
    line-height: 1.7;
}
.hero-btns {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}
.btn-explore {
    background: #D4A843;
    color: #1A1A1A;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.3s ease;
}
.btn-explore:hover {
    background: #C49A35;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(212,168,67,0.4);
}
.btn-hero-signin {
    border: 2px solid rgba(245,240,232,0.4);
    color: #F5F0E8;
    background: transparent;
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 4px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.3s ease;
}
.btn-hero-signin:hover {
    border-color: #F5F0E8;
    background: rgba(245,240,232,0.08);
}
.hero-arrow {
    position: absolute;
    bottom: 40px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 2;
    color: #D4A843;
    font-size: 28px;
    cursor: pointer;
}

/* ═══════════════════════════════════════
   STATS BAR
   ═══════════════════════════════════════ */
.stats-bar {
    background: rgba(212,168,67,0.08);
    border-top: 1px solid rgba(212,168,67,0.15);
    border-bottom: 1px solid rgba(212,168,67,0.15);
    padding: 48px 24px;
    position: relative;
    z-index: 2;
}
.stats-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 24px;
    text-align: center;
}
.stat-num {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: #D4A843;
    font-weight: 700;
    line-height: 1.2;
}
.stat-label {
    font-size: 0.85rem;
    color: rgba(245,240,232,0.6);
    text-transform: uppercase;
    letter-spacing: 0.12em;
    margin-top: 6px;
    font-weight: 500;
}

/* ═══════════════════════════════════════
   ROOMS SECTION
   ═══════════════════════════════════════ */
.rooms-section {
    background: #0F0F0F;
    padding: 80px 0;
    position: relative;
    z-index: 2;
}
.section-header {
    text-align: center;
    max-width: 600px;
    margin: 0 auto 40px;
}
.section-eyebrow {
    font-size: 11px;
    letter-spacing: 3px;
    color: #D4A843;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 12px;
}
.section-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    color: #F5F0E8;
    font-weight: 700;
    margin-bottom: 12px;
}
.section-sub {
    font-size: 15px;
    color: rgba(245,240,232,0.55);
    line-height: 1.6;
}

.locked-banner {
    max-width: 1200px;
    margin: 0 auto 40px;
    padding: 0 24px;
}
.locked-banner-inner {
    background: rgba(212,168,67,0.08);
    border: 1px solid rgba(212,168,67,0.2);
    border-radius: 8px;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    font-size: 14px;
    color: rgba(245,240,232,0.75);
}
.locked-banner-inner a {
    color: #D4A843;
    font-weight: 600;
    text-decoration: underline;
    text-underline-offset: 2px;
}
.locked-banner-inner a:hover {
    color: #F0D98A;
}

.rooms-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 28px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ── ROOM CARD ── */
.room-card {
    background: #1A1A1A;
    border: 1px solid rgba(212,168,67,0.15);
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
}
.room-card:hover {
    transform: translateY(-6px);
    border-color: rgba(212,168,67,0.5);
    box-shadow: 0 16px 48px rgba(212,168,67,0.12);
}

.rc-img-area {
    position: relative;
    height: 220px;
    overflow: hidden;
    background: linear-gradient(135deg, #1A1A1A, #2A2318);
}
.rc-img-area img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.room-card:hover .rc-img-area img {
    transform: scale(1.06);
}
.rc-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Playfair Display', serif;
    font-size: 72px;
    color: rgba(212,168,67,0.25);
    font-weight: 700;
    background: linear-gradient(135deg, #1A1A1A 0%, #2A2318 100%);
}

.rc-lock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.3s ease;
}
.room-card:hover .rc-lock-overlay {
    background: rgba(0,0,0,0.5);
}
.rc-lock-badge {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(212,168,67,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #1A1A1A;
    transition: transform 0.3s ease;
}
.room-card:hover .rc-lock-badge {
    transform: scale(1.05);
}

.rc-type-badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: rgba(212,168,67,0.9);
    color: #1A1A1A;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 2px;
    padding: 4px 10px;
    border-radius: 20px;
    z-index: 2;
}

.rc-body {
    padding: 20px;
}
.rc-room-name {
    font-family: 'DM Sans', sans-serif;
    font-weight: 500;
    color: #F5F0E8;
    font-size: 18px;
    margin-bottom: 8px;
}
.rc-price {
    font-family: 'JetBrains Mono', monospace;
    color: #D4A843;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 10px;
}
.rc-price .per-night {
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
    color: rgba(245,240,232,0.5);
    font-weight: 400;
}
.rc-capacity {
    color: rgba(245,240,232,0.55);
    font-size: 13px;
    margin-bottom: 14px;
}
.rc-amenities {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 18px;
}
.rc-amenity-tag {
    border: 1px solid rgba(212,168,67,0.2);
    color: rgba(245,240,232,0.6);
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 20px;
}
.rc-book-btn {
    display: block;
    width: 100%;
    padding: 10px;
    background: transparent;
    border: 1px solid rgba(212,168,67,0.4);
    color: #D4A843;
    border-radius: 6px;
    font-family: 'DM Sans', sans-serif;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    text-align: center;
    transition: all 0.25s ease;
}
.rc-book-btn:hover {
    background: #D4A843;
    color: #1A1A1A;
}

/* ═══════════════════════════════════════
   FEATURES ROW
   ═══════════════════════════════════════ */
.features-section {
    background: #0D0D0D;
    padding: 70px 0;
    position: relative;
    z-index: 2;
}
.features-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    max-width: 960px;
    margin: 0 auto;
    padding: 0 24px;
}
.feature-card {
    background: rgba(212,168,67,0.04);
    border: 1px solid rgba(212,168,67,0.1);
    border-radius: 12px;
    padding: 36px 28px;
    text-align: center;
    transition: all 0.3s ease;
}
.feature-card:hover {
    transform: translateY(-4px);
    border-color: rgba(212,168,67,0.3);
    background: rgba(212,168,67,0.08);
}
.feature-icon {
    font-size: 32px;
    color: #D4A843;
    margin-bottom: 16px;
}
.feature-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.2rem;
    color: #F5F0E8;
    margin-bottom: 10px;
}
.feature-text {
    font-size: 14px;
    color: rgba(245,240,232,0.55);
    line-height: 1.7;
}

/* ═══════════════════════════════════════
   CTA BANNER
   ═══════════════════════════════════════ */
.cta-section {
    background: linear-gradient(135deg, #1A1208, #2A1D0A, #1A1208);
    border-top: 1px solid rgba(212,168,67,0.2);
    border-bottom: 1px solid rgba(212,168,67,0.2);
    padding: 80px 24px;
    text-align: center;
    position: relative;
    z-index: 2;
}
.cta-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.5rem;
    color: #F5F0E8;
    margin-bottom: 16px;
}
.cta-sub {
    font-size: 16px;
    color: rgba(245,240,232,0.65);
    max-width: 480px;
    margin: 0 auto 36px;
    line-height: 1.6;
}
.btn-cta-primary {
    display: inline-block;
    background: #D4A843;
    color: #1A1A1A;
    padding: 16px 40px;
    font-size: 17px;
    font-weight: 700;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-family: 'DM Sans', sans-serif;
    transition: all 0.3s ease;
    margin-bottom: 20px;
}
.btn-cta-primary:hover {
    background: #C49A35;
    transform: translateY(-2px);
    box-shadow: 0 8px 32px rgba(212,168,67,0.4);
}
.cta-link {
    display: block;
    color: #D4A843;
    text-decoration: underline;
    text-underline-offset: 3px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 15px;
    font-family: 'DM Sans', sans-serif;
}
.cta-link:hover {
    color: #F0D98A;
}

/* ═══════════════════════════════════════
   FOOTER
   ═══════════════════════════════════════ */
.site-footer {
    background: #080808;
    border-top: 1px solid rgba(212,168,67,0.1);
    padding: 32px 24px;
    position: relative;
    z-index: 2;
}
.footer-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}
.footer-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #D4A843;
    letter-spacing: 3px;
}
.footer-copy {
    font-size: 13px;
    color: rgba(245,240,232,0.35);
}
.footer-links {
    display: flex;
    gap: 16px;
}
.footer-links a {
    font-size: 13px;
    color: rgba(245,240,232,0.35);
    transition: color 0.2s ease;
}
.footer-links a:hover {
    color: #D4A843;
}

/* ═══════════════════════════════════════
   THREE.JS CANVAS
   ═══════════════════════════════════════ */
#three-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 0;
    pointer-events: none;
}

/* ═══════════════════════════════════════
   TESTIMONIALS
   ═══════════════════════════════════════ */
.testimonials-section {
    padding: 120px 0;
    background: #0D0D0D;
    position: relative;
    z-index: 2;
}
.testimonials-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}
.testimonial-card {
    background: rgba(21, 21, 21, 0.6);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(212,168,67,0.1);
    padding: 35px 25px 30px;
    border-radius: 20px;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    width: 100%;
    max-width: 320px;
    display: flex;
    flex-direction: column;
}
.testimonial-card:hover {
    transform: translateY(-12px);
    border-color: rgba(212,168,67,0.4);
    background: rgba(26, 26, 26, 0.8);
    box-shadow: 0 20px 50px rgba(212,168,67,0.1);
}
.tm-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #D4A843, #8C6E2F);
    color: #1A1A1A;
    border-radius: 50%;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 700;
    font-family: 'Playfair Display', serif;
    box-shadow: 0 6px 20px rgba(212,168,67,0.3);
    flex-shrink: 0;
}
.tm-stars {
    color: #D4A843;
    font-size: 14px;
    margin-bottom: 20px;
    letter-spacing: 2px;
}
.tm-text {
    font-size: 15px;
    line-height: 1.7;
    color: rgba(245,240,232,0.8);
    margin-bottom: 24px;
    font-style: italic;
    position: relative;
    flex-grow: 1;
}
.tm-author {
    font-family: 'Playfair Display', serif;
    font-size: 19px;
    font-weight: 700;
    color: #D4A843;
    margin-bottom: 4px;
}
.tm-date {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: rgba(245,240,232,0.3);
}

/* ═══════════════════════════════════════
   CONTACT SECTION
   ═══════════════════════════════════════ */
.contact-section {
    padding: 100px 0 0;
    background: #0D0D0D;
    position: relative;
    z-index: 2;
}
.contact-container {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: 60px;
    max-width: 1200px;
    margin: 0 auto 100px;
    padding: 0 24px;
}
.info-card {
    background: rgba(21, 21, 21, 0.6);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    border: 1px solid rgba(212,168,67,0.1);
}
.info-item {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}
.info-icon {
    width: 44px;
    height: 44px;
    background: rgba(212,168,67,0.1);
    border: 1px solid #D4A843;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #D4A843;
    font-size: 16px;
}
.info-text h3 {
    margin: 0;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #D4A843;
}
.info-text p {
    margin: 5px 0 0;
    font-size: 14px;
    color: rgba(245,240,232,0.7);
}
.contact-form {
    background: rgba(21, 21, 21, 0.6);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 20px;
    border: 1px solid rgba(212,168,67,0.1);
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #D4A843;
    margin-bottom: 8px;
}
.form-group input, .form-group textarea {
    width: 100%;
    background: rgba(13, 13, 13, 0.8);
    border: 1px solid rgba(212,168,67,0.2);
    border-radius: 8px;
    padding: 12px;
    color: #F5F0E8;
    font-family: 'DM Sans', sans-serif;
    outline: none;
    transition: border-color 0.3s;
}
.form-group input:focus, .form-group textarea:focus {
    border-color: #D4A843;
}
.btn-submit {
    background: #D4A843;
    color: #1A1A1A;
    border: none;
    border-radius: 8px;
    padding: 14px 40px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s;
}
.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(212,168,67,0.3);
}
.map-section {
    position: relative;
    z-index: 2;
    height: 400px;
    width: 100%;
    border-top: 1px solid rgba(212,168,67,0.2);
}
.map-section iframe {
    width: 100%;
    height: 100%;
    border: none;
    filter: grayscale(1) invert(0.9) contrast(1.2);
}
@media (max-width: 900px) {
    .contact-container { grid-template-columns: 1fr; gap: 40px; }
}

/* ═══════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════ */
@media (max-width: 900px) {
    #navbar { padding: 14px 20px; }
    .nav-brand { font-size: 22px; letter-spacing: 3px; }
    .stats-inner { grid-template-columns: repeat(2, 1fr); gap: 32px; }
    .features-grid { grid-template-columns: 1fr; max-width: 400px; }
    .section-title { font-size: 2rem; }
    .cta-title { font-size: 2rem; }
}
@media (max-width: 600px) {
    #navbar { padding: 12px 16px; gap: 8px; }
    .nav-brand { font-size: 20px; letter-spacing: 2px; }
    .btn-signin, .btn-create { padding: 6px 14px; font-size: 13px; }
    .hero-btns { flex-direction: column; align-items: center; }
    .btn-explore, .btn-hero-signin { width: 100%; max-width: 280px; text-align: center; }
    .rooms-grid { grid-template-columns: 1fr; }
    .stats-inner { grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .footer-inner { flex-direction: column; text-align: center; }
}
</style>
</head>
<body>

<!-- ═══════ THREE.JS CANVAS ═══════ -->
<canvas id="three-canvas"></canvas>

<!-- ═══════ NAVBAR ═══════ -->
<nav id="navbar">
    <div class="nav-brand">AMNEN</div>
    <div class="nav-actions">
        <a href="/amnen/login.php" class="btn-signin">Sign In</a>
        <a href="/amnen/register.php" class="btn-create">Create Account</a>
    </div>
</nav>

<!-- ═══════ HERO ═══════ -->
<section class="hero" id="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <div class="hero-badge">LUXURY HOTEL EXPERIENCE</div>
        <h1 class="hero-title">
            <span class="line1">Where Every Stay</span><br>
            <span class="line2">Becomes a Memory</span>
        </h1>
        <p class="hero-sub">
            Experience unparalleled luxury, world-class amenities,
            and exceptional service tailored just for you.
        </p>
        <div class="hero-btns">
            <a href="#rooms" class="btn-explore">Explore Rooms</a>
            <a href="/amnen/login.php" class="btn-hero-signin">Sign In to Book</a>
        </div>
    </div>
    <div class="hero-arrow scroll-arrow" onclick="document.getElementById('stats').scrollIntoView({behavior:'smooth'})">↓</div>
</section>

<!-- ═══════ STATS BAR ═══════ -->
<section class="stats-bar" id="stats">
    <div class="stats-inner">
        <div class="fade-in-up">
            <div class="stat-num"><?= $totalRoomCount ?: '50+' ?></div>
            <div class="stat-label">Luxury Rooms</div>
        </div>
        <div class="fade-in-up">
            <div class="stat-num"><?= $avgRating ?>★</div>
            <div class="stat-label">Guest Rating</div>
        </div>
        <div class="fade-in-up">
            <div class="stat-num">15+</div>
            <div class="stat-label">Years of Excellence</div>
        </div>
        <div class="fade-in-up">
            <div class="stat-num"><?= $happyGuests ?>+</div>
            <div class="stat-label">Happy Guests</div>
        </div>
    </div>
</section>

<!-- ═══════ ROOMS SECTION ═══════ -->
<section class="rooms-section" id="rooms">
    <div class="section-header fade-in-up">
        <div class="section-eyebrow">OUR ACCOMMODATIONS</div>
        <h2 class="section-title">Exquisite Rooms & Suites</h2>
        <p class="section-sub">Each room is a masterpiece of comfort and design</p>
    </div>


    <div class="rooms-grid">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $i => $room):
                $type      = htmlspecialchars(ucfirst($room['room_type'] ?? 'Standard'));
                $number    = htmlspecialchars($room['room_number'] ?? '');
                $price     = number_format($room['price'] ?? 0);
                $capacity  = (int)($room['capacity'] ?? 2);
                $imageUrl  = $room['image_url'] ?? '';
                $amenities = is_array($room['amenities']) ? $room['amenities'] : [];
                $roomId    = (int)($room['room_id'] ?? 0);
                $initial   = strtoupper(substr($room['room_type'] ?? 'R', 0, 1));
                $defaultAmenities = ['Wi-Fi', 'Air Conditioning', 'Room Service'];
                $displayAmenities = !empty($amenities) ? array_slice($amenities, 0, 3) : $defaultAmenities;
            ?>
            <div class="room-card fade-in-up"
                 style="transition-delay: <?= min($i * 80, 400) ?>ms"
                 onclick="window.location='/amnen/login.php?redirect=booking&room_id=<?= $roomId ?>'">
                <div class="rc-img-area">
                    <?php if (!empty($imageUrl)): ?>
                        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= $type ?> Room">
                    <?php else: ?>
                        <div class="rc-img-placeholder"><?= $initial ?></div>
                    <?php endif; ?>
                    <div class="rc-lock-overlay">
                        <div class="rc-lock-badge">🔒</div>
                    </div>
                    <div class="rc-type-badge"><?= $type ?></div>
                </div>
                <div class="rc-body">
                    <div class="rc-room-name">Room #<?= $number ?></div>
                    <div class="rc-price">ETB <?= $price ?> <span class="per-night">/night</span></div>
                    <div class="rc-capacity">👥 Up to <?= $capacity ?> guests</div>
                    <div class="rc-amenities">
                        <?php foreach ($displayAmenities as $am): ?>
                            <span class="rc-amenity-tag"><?= htmlspecialchars($am) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <div class="rc-book-btn">View Details</div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <?php
            $placeholders = [
                ['type' => 'Deluxe',   'letter' => 'D', 'price' => '3,500'],
                ['type' => 'Suite',    'letter' => 'S', 'price' => '5,200'],
                ['type' => 'Standard', 'letter' => 'T', 'price' => '1,800'],
            ];
            foreach ($placeholders as $i => $ph): ?>
            <div class="room-card fade-in-up" style="transition-delay: <?= $i * 100 ?>ms"
                 onclick="window.location='/amnen/login.php'">
                <div class="rc-img-area">
                    <div class="rc-img-placeholder"><?= $ph['letter'] ?></div>
                    <div class="rc-lock-overlay">
                        <div class="rc-lock-badge">🔒</div>
                    </div>
                    <div class="rc-type-badge"><?= $ph['type'] ?></div>
                </div>
                <div class="rc-body">
                    <div class="rc-room-name"><?= $ph['type'] ?> Room</div>
                    <div class="rc-price">ETB <?= $ph['price'] ?> <span class="per-night">/night</span></div>
                    <div class="rc-capacity">👥 Up to 3 guests</div>
                    <div class="rc-amenities">
                        <span class="rc-amenity-tag">Wi-Fi</span>
                        <span class="rc-amenity-tag">Air Conditioning</span>
                        <span class="rc-amenity-tag">Room Service</span>
                    </div>
                    <div class="rc-book-btn">View Details</div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ═══════ FEATURES ═══════ -->
<section class="features-section">
    <div class="features-grid">
        <div class="feature-card fade-in-up">
            <div class="feature-icon">✦</div>
            <div class="feature-title">Curated Luxury</div>
            <div class="feature-text">Handpicked amenities for discerning travelers who expect nothing but the finest</div>
        </div>
        <div class="feature-card fade-in-up" style="transition-delay: 100ms">
            <div class="feature-icon">◈</div>
            <div class="feature-title">Prime Location</div>
            <div class="feature-text">In the heart of the city, close to everything you need during your stay</div>
        </div>
        <div class="feature-card fade-in-up" style="transition-delay: 200ms">
            <div class="feature-icon">❋</div>
            <div class="feature-title">24/7 Support</div>
            <div class="feature-text">Our dedicated team is always here when you need us, day or night</div>
        </div>
    </div>
</section>

<!-- ═══════ TESTIMONIALS ═══════ -->
<?php if (!empty($testimonials)): ?>
<section class="testimonials-section">
    <div class="section-header fade-in-up">
        <div class="section-eyebrow">GUEST VOICES</div>
        <h2 class="section-title">Enduring Impressions</h2>
        <p class="section-sub">Authentic stories from our beloved guests</p>
    </div>
    
    <div class="testimonials-grid">
        <?php foreach ($testimonials as $i => $tm): 
            $authorInitial = strtoupper($tm['fname'][0] ?? 'G');
            $rating = (int)$tm['rating'];
        ?>
        <div class="testimonial-card fade-in-up" style="transition-delay: <?= $i * 150 ?>ms">
            <div class="tm-avatar"><?= $authorInitial ?></div>
            <div class="tm-stars">
                <?php 
                    for($s=1; $s<=5; $s++) echo ($s <= $rating) ? '★' : '☆';
                ?>
            </div>
            <p class="tm-text">"<?= htmlspecialchars($tm['message']) ?>"</p>
            <div class="tm-author"><?= htmlspecialchars($tm['fname'] . ' ' . ($tm['lname'] ? substr($tm['lname'], 0, 1) . '.' : '')) ?></div>
            <div class="tm-date"><?= date('M d, Y', strtotime($tm['created_at'])) ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ═══════ CONTACT SECTION ═══════ -->
<section class="contact-section" id="contact">
    <div class="section-header fade-in-up">
        <div class="section-eyebrow">GET IN TOUCH</div>
        <h2 class="section-title">Visit Amnen Today</h2>
        <p class="section-sub">Experience luxury in the heart of Hawassa</p>
    </div>

    <div class="contact-container fade-in-up">
        <div class="info-card">
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-location-dot"></i></div>
                <div class="info-text">
                    <h3>Address</h3>
                    <p>Subcity, Hawassa, Ethiopia<br>Near Lake Hawassa</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-phone"></i></div>
                <div class="info-text">
                    <h3>Phone</h3>
                    <p>0911957824</p>
                </div>
            </div>
            <div class="info-item">
                <div class="info-icon"><i class="fas fa-envelope"></i></div>
                <div class="info-text">
                    <h3>Email</h3>
                    <p>info@amnen.et</p>
                </div>
            </div>
        </div>
        
        <form class="contact-form" id="contactForm" method="POST">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter your name" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label>Your Message</label>
                <textarea rows="4" name="message" placeholder="How can we help you?" required></textarea>
            </div>
            <button type="submit" class="btn-submit">Send Message</button>
        </form>
    </div>

    <div class="map-section fade-in-up">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d899.756927344638!2d38.48008182015879!3d7.065396252876877!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x17b1455944877653%3A0xaaca2b379750127a!2s3F8J%2B76V%2C%20Town!5e0!3m2!1sen!2set!4v1779582139514!5m2!1sen!2set" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</section>

<!-- ═══════ FOOTER ═══════ -->
<?php require_once __DIR__ . '/views/partials/footer.php'; ?>

<!-- ═══════ THREE.JS ═══════ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
(function() {
    // ── SCENE SETUP ──
    const canvas = document.getElementById('three-canvas');
    const scene  = new THREE.Scene();
    scene.background = new THREE.Color(0x0D0D0D);
    scene.fog = new THREE.FogExp2(0x0D0D0D, 0.06);

    const camera = new THREE.PerspectiveCamera(60, window.innerWidth / window.innerHeight, 0.1, 100);
    camera.position.set(0, 1.5, 5);

    const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;

    // ── LIGHTING ──
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.3);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight(0xD4A843, 1.2);
    dirLight.position.set(5, 8, 3);
    dirLight.castShadow = true;
    scene.add(dirLight);

    const pointLight = new THREE.PointLight(0xffffff, 0.8);
    pointLight.position.set(-3, 3, 3);
    scene.add(pointLight);

    const spotLight = new THREE.SpotLight(0xD4A843, 0.8);
    spotLight.position.set(0, 6, 4);
    spotLight.angle = Math.PI / 5;
    spotLight.penumbra = 0.4;
    scene.add(spotLight);

    // ── HOTEL BUILDING GROUP ──
    const hotel = new THREE.Group();

    // Gold material
    const goldMat = new THREE.MeshPhongMaterial({
        color: 0xD4A843,
        specular: 0xFFD700,
        shininess: 60
    });
    const darkGoldMat = new THREE.MeshPhongMaterial({
        color: 0xB8912F,
        specular: 0xD4A843,
        shininess: 40
    });

    // Main body
    const mainBody = new THREE.Mesh(new THREE.BoxGeometry(2, 3, 1), goldMat);
    mainBody.position.y = 1.5;
    mainBody.castShadow = true;
    hotel.add(mainBody);

    // Left wing
    const leftWing = new THREE.Mesh(new THREE.BoxGeometry(0.8, 2, 0.8), darkGoldMat);
    leftWing.position.set(-1.4, 1, 0);
    leftWing.castShadow = true;
    hotel.add(leftWing);

    // Right wing
    const rightWing = new THREE.Mesh(new THREE.BoxGeometry(0.8, 2, 0.8), darkGoldMat);
    rightWing.position.set(1.4, 1, 0);
    rightWing.castShadow = true;
    hotel.add(rightWing);

    // Roof accent
    const roofGeo = new THREE.BoxGeometry(2.2, 0.15, 1.1);
    const roofMat = new THREE.MeshPhongMaterial({ color: 0xC49A35, specular: 0xFFD700, shininess: 80 });
    const roof = new THREE.Mesh(roofGeo, roofMat);
    roof.position.y = 3.08;
    hotel.add(roof);

    // Windows — 4 cols x 6 rows on front face
    const windowMat = new THREE.MeshPhongMaterial({
        color: 0xFFF8DC,
        emissive: 0xFFF8DC,
        emissiveIntensity: 0.7,
        transparent: true,
        opacity: 0.85
    });
    for (let row = 0; row < 6; row++) {
        for (let col = 0; col < 4; col++) {
            const win = new THREE.Mesh(
                new THREE.PlaneGeometry(0.2, 0.25),
                windowMat
            );
            win.position.set(
                -0.6 + col * 0.4,
                0.5 + row * 0.44,
                0.51
            );
            hotel.add(win);
        }
    }

    // Wing windows — 2 cols x 4 rows each
    for (let side = -1; side <= 1; side += 2) {
        for (let row = 0; row < 4; row++) {
            for (let col = 0; col < 2; col++) {
                const wWin = new THREE.Mesh(
                    new THREE.PlaneGeometry(0.15, 0.2),
                    windowMat
                );
                wWin.position.set(
                    side * 1.4 + (col - 0.5) * 0.3,
                    0.4 + row * 0.4,
                    0.41
                );
                hotel.add(wWin);
            }
        }
    }

    // Entrance
    const doorMat = new THREE.MeshPhongMaterial({
        color: 0xF0D98A,
        emissive: 0xF0D98A,
        emissiveIntensity: 0.5
    });
    const door = new THREE.Mesh(new THREE.PlaneGeometry(0.35, 0.5), doorMat);
    door.position.set(0, 0.25, 0.51);
    hotel.add(door);

    // Entrance canopy
    const canopy = new THREE.Mesh(
        new THREE.BoxGeometry(0.6, 0.05, 0.3),
        roofMat
    );
    canopy.position.set(0, 0.52, 0.65);
    hotel.add(canopy);

    hotel.position.y = -0.5;
    scene.add(hotel);

    // Aim spotlight at hotel
    spotLight.target = mainBody;
    scene.add(spotLight.target);

    // ── GROUND PLANE ──
    const groundGeo = new THREE.PlaneGeometry(20, 20);
    const groundMat = new THREE.MeshPhongMaterial({ color: 0x1A1A1A });
    const ground = new THREE.Mesh(groundGeo, groundMat);
    ground.rotation.x = -Math.PI / 2;
    ground.position.y = -0.5;
    ground.receiveShadow = true;
    scene.add(ground);

    // ── FLOATING GOLD PARTICLES ──
    const particles = [];
    const particleMat = new THREE.MeshPhongMaterial({
        color: 0xD4A843,
        emissive: 0xD4A843,
        emissiveIntensity: 0.4,
        transparent: true,
        opacity: 0.6
    });
    for (let i = 0; i < 200; i++) {
        const size = 0.015 + Math.random() * 0.015;
        const p = new THREE.Mesh(
            new THREE.SphereGeometry(size, 6, 6),
            particleMat
        );
        p.position.set(
            (Math.random() - 0.5) * 16,
            Math.random() * 8 - 1,
            (Math.random() - 0.5) * 16
        );
        p.userData.speed = 0.003 + Math.random() * 0.004;
        p.userData.drift = (Math.random() - 0.5) * 0.002;
        particles.push(p);
        scene.add(p);
    }

    // ── ANIMATION LOOP ──
    const clock = new THREE.Clock();

    function animate() {
        requestAnimationFrame(animate);
        const time = clock.getElapsedTime();

        // Rotate hotel
        hotel.rotation.y += 0.002;

        // Float particles
        for (const p of particles) {
            p.position.y += p.userData.speed;
            p.position.x += p.userData.drift;
            if (p.position.y > 8) {
                p.position.y = -1;
                p.position.x = (Math.random() - 0.5) * 16;
                p.position.z = (Math.random() - 0.5) * 16;
            }
        }

        // Gentle camera bob
        camera.position.y = Math.sin(time * 0.3) * 0.2 + 1.5;
        camera.lookAt(0, 1, 0);

        renderer.render(scene, camera);
    }
    animate();

    // ── RESIZE HANDLER ──
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();

// ═══════ NAVBAR SCROLL EFFECT ═══════
window.addEventListener('scroll', () => {
    const nav = document.getElementById('navbar');
    if (window.scrollY > 80) {
        nav.classList.add('scrolled');
    } else {
        nav.classList.remove('scrolled');
    }
});

// ═══════ SCROLL REVEAL OBSERVER ═══════
const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            e.target.classList.add('visible');
            observer.unobserve(e.target);
        }
    });
}, { threshold: 0.12 });
document.querySelectorAll('.fade-in-up').forEach(el => observer.observe(el));

// ═══════ SMOOTH SCROLL FOR ANCHORS ═══════
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href === '#') return;
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

// ═══════ CONTACT FORM SUBMIT (DELIVER TO ADMIN) ═══════
const contactForm = document.getElementById('contactForm');
if (contactForm) {
  contactForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(contactForm);
    try {
      const res = await fetch('/amnen/api/contact-message.php', { method: 'POST', body: fd });
      const data = await res.json().catch(() => ({}));
      if (res.ok && data.ok) {
        alert('Thank you for your message! Our team will contact you shortly.');
        contactForm.reset();
      } else {
        alert('Could not send your message. Please try again.');
      }
    } catch (err) {
      alert('Could not send your message. Please try again.');
    }
  });
}
</script>

</body>
</html>
