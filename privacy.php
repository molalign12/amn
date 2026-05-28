<?php require_once __DIR__ . '/config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Privacy Policy | Amnen Guest House</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
/* ═══════════════════════════════════════
   GLOBAL & REUSED STYLES
   ═══════════════════════════════════════ */
body {
    background: #0D0D0D;
    color: #F5F0E8;
    font-family: 'DM Sans', sans-serif;
    margin: 0;
    overflow-x: hidden;
}
#three-canvas { position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 0; pointer-events: none; }
#navbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 48px; background: rgba(13,13,13,0.85);
    backdrop-filter: blur(12px); border-bottom: 1px solid rgba(212,168,67,0.2);
}
.nav-brand { font-family: 'Playfair Display', serif; font-size: 28px; font-weight: 700; color: #D4A843; letter-spacing: 4px; text-decoration: none; }
.content-section { position: relative; z-index: 2; max-width: 900px; margin: 120px auto 80px; padding: 0 24px; }
h1 { font-family: 'Playfair Display', serif; font-size: 3.5rem; color: #D4A843; margin-bottom: 40px; }
h2 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #D4A843; margin-top: 40px; margin-bottom: 20px; }
p { line-height: 1.8; color: rgba(245,240,232,0.8); margin-bottom: 20px; }
.site-footer {
    background: #080808; border-top: 1px solid rgba(212,168,67,0.1);
    padding: 32px 24px; position: relative; z-index: 2;
}
.footer-inner { max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
.footer-logo { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: #D4A843; letter-spacing: 3px; }
.footer-copy { font-size: 13px; color: rgba(245,240,232,0.35); }
.footer-links a { font-size: 13px; color: rgba(245,240,232,0.35); text-decoration: none; margin-left: 16px; transition: color 0.2s; }
.footer-links a:hover { color: #D4A843; }
.btn-back-home {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: rgba(212,168,67,0.1);
    border: 1px solid #D4A843;
    color: #D4A843;
    padding: 12px 24px;
    border-radius: 30px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 30px;
    transition: all 0.3s ease;
}
.btn-back-home:hover {
    background: #D4A843;
    color: #1A1A1A;
    transform: translateX(-5px);
}
</style>
</head>
<body>

<canvas id="three-canvas"></canvas>

<nav id="navbar">
    <a href="/amnen/index.php" class="nav-brand">AMNEN</a>
</nav>

<main class="content-section">
    <a href="/amnen/index.php" class="btn-back-home">
        <i class="fas fa-arrow-left"></i> Back to Homepage
    </a>
    <h1>Privacy Policy</h1>
    <p>Last Updated: April 17, 2026</p>
    
    <p>At Amnen Guest House, we value the trust you place in us when you share your personal information. This Privacy Policy describes how we collect, use, and protect your data across our reservation system and services.</p>
    
    <h2>1. Information We Collect</h2>
    <p>We collect information necessary to provide you with exceptional hospitality services. This includes:</p>
    <ul>
        <li><strong>Personal Identification:</strong> Name, email address, phone number, and physical address.</li>
        <li><strong>Booking Details:</strong> Arrival/departure dates, room preferences, and special requests.</li>
        <li><strong>Payment Information:</strong> Transaction details through our secure payment partners (e.g., Chapa). We do not store full credit card numbers on our servers.</li>
    </ul>

    <h2>2. How We Use Your Information</h2>
    <p>Your data is used solely to facilitate your stay and improve our guest experience:</p>
    <ul>
        <li>Processing reservations and payments.</li>
        <li>Sending booking confirmations and service updates.</li>
        <li>Responding to your inquiries and feedback.</li>
        <li>Complying with legal and safety requirements.</li>
    </ul>

    <h2>3. Data Security</h2>
    <p>We implement robust technical and organizational measures to safeguard your information. Our database uses modern encryption, and access is restricted to authorized personnel only.</p>

    <h2>4. Sharing with Third Parties</h2>
    <p>We do not sell your personal data. We only share information with trusted partners necessary for operations, such as payment processors or as required by Ethiopian law.</p>

    <h2>5. Your Rights</h2>
    <p>You have the right to access, correct, or request the deletion of your personal information. Please contact our support team for any data-related requests.</p>
</main>

<?php require_once __DIR__ . '/views/partials/footer.php'; ?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
// Re-using the landing page background script for branding consistency
(function(){
    const canvas = document.getElementById('three-canvas');
    const scene = new THREE.Scene();
    scene.background = new THREE.Color(0x0D0D0D);
    const camera = new THREE.PerspectiveCamera(60, window.innerWidth/window.innerHeight, 0.1, 100);
    camera.position.set(0, 1.5, 5);
    const renderer = new THREE.WebGLRenderer({canvas, antialias: true, alpha: true});
    renderer.setSize(window.innerWidth, window.innerHeight);
    const goldMat = new THREE.MeshPhongMaterial({color: 0xD4A843, specular: 0xFFD700, shininess: 40});
    const hotel = new THREE.Mesh(new THREE.BoxGeometry(2, 3, 1), goldMat);
    hotel.position.y = -0.5;
    scene.add(hotel);
    const light = new THREE.PointLight(0xffffff, 1);
    light.position.set(5, 5, 5);
    scene.add(light);
    scene.add(new THREE.AmbientLight(0xffffff, 0.4));
    function animate() {
        requestAnimationFrame(animate);
        hotel.rotation.y += 0.005;
        renderer.render(scene, camera);
    }
    animate();
    window.addEventListener('resize', () => {
        camera.aspect = window.innerWidth/window.innerHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(window.innerWidth, window.innerHeight);
    });
})();
</script>
</body>
</html>
