<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn() || !validateCSRFToken($_POST['csrf_token']??'')) { echo json_encode(['success'=>false,'error'=>'Not authorized']); exit; }
$userId = $_SESSION['user_id'];
$receiverId = (int)($_POST['receiver_id'] ?? 0);
if (!$receiverId) { echo json_encode(['success'=>false,'error'=>'Invalid request']); exit; }
// Check duplicate
$check = $pdo->prepare("SELECT id FROM interests WHERE sender_id=? AND receiver_id=?");
$check->execute([$userId, $receiverId]);
if ($check->fetch()) { echo json_encode(['success'=>false,'error'=>'Interest already sent']); exit; }
// Check blocked
if (isBlocked($pdo, $receiverId, $userId)) { echo json_encode(['success'=>false,'error'=>'Cannot send interest']); exit; }
$stmt = $pdo->prepare("INSERT INTO interests (sender_id, receiver_id) VALUES (?,?)");
$stmt->execute([$userId, $receiverId]);
$name = $_SESSION['user_name'];
createNotification($pdo, $receiverId, $userId, 'interest_received', "$name sent you an interest request", '', "interests.php");
echo json_encode(['success'=>true,'message'=>'Interest sent!']);
