<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

// Always return login status
$loggedIn = isLoggedIn();

if (!$loggedIn) { 
    echo json_encode(['success' => false, 'logged_in' => false]); 
    exit; 
}

// Update last active if logged in
updateLastActive($pdo, $_SESSION['user_id']);

$userId = (int)($_GET['user_id'] ?? 0);
if (!$userId) { 
    echo json_encode(['logged_in' => true, 'online' => false]); 
    exit; 
}

$stmt = $pdo->prepare("SELECT last_active FROM users WHERE id=?"); 
$stmt->execute([$userId]); 
$u = $stmt->fetch();
echo json_encode([
    'logged_in' => true,
    'online' => $u ? isUserOnline($u['last_active']) : false
]);
