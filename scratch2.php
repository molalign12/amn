<?php
$files = [
  'manager/rooms.php',
  'manager/reports.php',
  'receptionist/dashboard.php',
  'receptionist/reservations.php',
  'receptionist/feedback.php'
];

foreach ($files as $rel) {
    $p = 'c:/xampp/htdocs/amnen/views/' . $rel;
    if (!file_exists($p)) continue;
    $c = file_get_contents($p);
    
    // Attempt to strip out the old toggleSidebar definitions and restored states
    $c = preg_replace('/function toggleSidebar\(\) \{.*?\n\}/s', '', $c);
    $c = preg_replace('/\(function restoreSidebar\(\).*?\)\(\);/s', '', $c);
    
    file_put_contents($p, $c);
}
