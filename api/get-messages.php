<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode(['messages'=>[]]); exit; }
$userId = $_SESSION['user_id'];
$partnerId = (int)($_GET['partner_id'] ?? 0);
$lastId = (int)($_GET['last_id'] ?? 0);
if (!$partnerId) { echo json_encode(['messages'=>[]]); exit; }
// Mark as seen
$pdo->prepare("UPDATE messages SET seen_status=1 WHERE sender_id=? AND receiver_id=? AND seen_status=0")->execute([$partnerId,$userId]);
// Fetch messages
$stmt = $pdo->prepare("SELECT id, sender_id, receiver_id, message, seen_status, created_at FROM messages WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND id > ? ORDER BY created_at ASC");
$stmt->execute([$userId,$partnerId,$partnerId,$userId,$lastId]);
$messages = [];
foreach ($stmt->fetchAll() as $m) {
    $messages[] = ['id'=>(int)$m['id'],'sender_id'=>(int)$m['sender_id'],'message'=>sanitize($m['message']),'seen_status'=>(int)$m['seen_status'],'time'=>date('h:i A',strtotime($m['created_at']))];
}
echo json_encode(['messages'=>$messages]);
