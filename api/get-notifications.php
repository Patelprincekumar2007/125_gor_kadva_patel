<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['count'=>0,'notifications'=>[]]); exit; }
$notifs = getRecentNotifications($pdo, $_SESSION['user_id'], 15);
$count = getUnreadNotificationCount($pdo, $_SESSION['user_id']);
echo json_encode(['count'=>$count,'notifications'=>$notifs]);
