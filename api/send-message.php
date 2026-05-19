<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn() || !validateCSRFToken($_POST['csrf_token']??'')) { echo json_encode(['success'=>false,'error'=>'Not authorized']); exit; }
$userId = $_SESSION['user_id'];
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$message = trim($_POST['message'] ?? '');
if (!$receiverId || empty($message) || mb_strlen($message) > 2000) { echo json_encode(['success'=>false,'error'=>'Invalid request']); exit; }
// Check if accepted interest exists
$check = $pdo->prepare("SELECT id FROM interests WHERE ((sender_id=? AND receiver_id=?) OR (sender_id=? AND receiver_id=?)) AND status='accepted'");
$check->execute([$userId,$receiverId,$receiverId,$userId]);
if (!$check->fetch()) { echo json_encode(['success'=>false,'error'=>'You can only chat with accepted matches']); exit; }
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?,?,?)");
$stmt->execute([$userId, $receiverId, $message]);
createNotification($pdo, $receiverId, $userId, 'new_message', $_SESSION['user_name'].' sent you a message', $message, 'messages.php?user='.$userId);
echo json_encode(['success'=>true,'id'=>$pdo->lastInsertId()]);
