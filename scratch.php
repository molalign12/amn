<?php
$files = [
  'admin/dashboard.php' => ['type'=>'admin'],
  'manager/dashboard.php' => ['type'=>'manager'],
  'manager/rooms.php' => ['type'=>'manager'],
  'manager/reports.php' => ['type'=>'manager'],
  'receptionist/dashboard.php' => ['type'=>'receptionist'],
  'receptionist/reservations.php' => ['type'=>'receptionist'],
  'receptionist/feedback.php' => ['type'=>'receptionist']
];

foreach ($files as $relPath => $info) {
    $path = 'c:/xampp/htdocs/amnen/views/' . $relPath;
    if (!file_exists($path)) {
       echo "Not found: $path\n";
       continue;
    }
    $content = file_get_contents($path);

    // PART 1: Fix Hotel Names
    $content = str_replace('HotelOS', 'AMNEN Guest House', $content);
    $content = str_replace('Hotel Management System', 'AMNEN Guest House', $content);
    $content = preg_replace('/(?<!\w)Amnen(?!\w)(?!=)/', 'AMNEN Guest House', $content); // case-sensitive to avoid amnen folder
    
    // Fix Brand texts
    $content = preg_replace('/<span class="brand-text">.*?<\/span>/', '<span class="logo-text">AMNEN</span>', $content);
    $content = preg_replace('/<div class="logo-h">H<\/div>/', '<div class="logo-icon">A</div>', $content);

    // PART 5/6: Change A
    $content = str_replace('<html lang="en">', '<html lang="en" data-theme="dark">', $content);

    // PART 5/6: Change B - Replace Sidebar
    if ($info['type'] !== 'admin') {
        $partial = $info['type'] . '-sidebar.php';
        $content = preg_replace('/<aside[^>]*>.*?<\/aside>/s', '<?php require_once __DIR__ . \'/../partials/' . $partial . '\'; ?>', $content);
    } else {
        // Admin: Make sure sidebar has id="sidebar"
        // Existing is already id="sidebar", just making sure it stays intact.
    }

    // PART 5/6/7: Change C - Topbar toggle
    $toggleBtn = '<button class="tb-toggle" id="sidebar-toggle" onclick="toggleSidebar()" title="Collapse/Expand Sidebar">
  <i class="fa fa-bars" id="toggle-icon"></i>
</button>';
    $content = preg_replace('/<button[^>]+(?:id="sidebar-toggle"|class="menu-btn")[^>]*>.*?<\/button>/s', $toggleBtn, $content);

    // PART 5/6/7: Add CSS/JS
    // Admin needs its CSS/JS updated as per Part 7, but Part 7 says:
    // CHANGE E - Add sidebar collapse CSS from part 4 to <style> block
    // CHANGE F - Replace or add toggleSidebar() function
    // For admin, we could just include the sidebar-assets.php in <head> too?
    // Wait, prompt says: "Add sidebar collapse CSS from part 4 to the existing <style> block" for Admin.
    if ($info['type'] === 'admin') {
        if (strpos($content, 'sidebar-assets.php') === false) {
             // For simplicity, let's just use the shared file for admin too, or physically replace it.
             // Actually, the simplest way is to put the PHP require. The require file ONLY outputs a style map and a script map.
        }
    }
    
    // So for all, let's inject sidebar-assets.php right before </head> if not there.
    if (strpos($content, 'sidebar-assets.php') === false) {
        $content = str_replace('</head>', "  <?php require_once __DIR__ . '/../partials/sidebar-assets.php'; ?>\n</head>", $content);
    }

    // Titles
    if ($info['type'] === 'receptionist' && basename($path) === 'dashboard.php') {
        $content = preg_replace('/<h1 class="page-title">.*?<\/h1>/s', '<h1 class="page-title">Receptionist Dashboard</h1>', $content);
    }
    if ($info['type'] === 'receptionist' && basename($path) === 'reservations.php') {
        $content = preg_replace('/<h1 class="page-title">.*?<\/h1>/s', '<h1 class="page-title">Reservations</h1>', $content);
    }
    if ($info['type'] === 'receptionist' && basename($path) === 'feedback.php') {
        $content = preg_replace('/<h1 class="page-title">.*?<\/h1>/s', '<h1 class="page-title">Guest Feedback</h1>', $content);
    }

    file_put_contents($path, $content);
    echo "Modified $relPath\n";
}
