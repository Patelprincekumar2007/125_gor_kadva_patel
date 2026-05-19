<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn() || !validateCSRFToken($_POST['csrf_token']??'')) { echo json_encode(['success'=>false,'error'=>'Not authorized']); exit; }
$id = (int)($_POST['id'] ?? 0);
$status = $_POST['status'] ?? '';
if (!$id || !in_array($status, ['accepted','rejected'])) { echo json_encode(['success'=>false,'error'=>'Invalid request']); exit; }
$stmt = $pdo->prepare("UPDATE interests SET status=? WHERE id=? AND receiver_id=?");
$stmt->execute([$status, $id, $_SESSION['user_id']]);
if ($stmt->rowCount()) {
    // Get sender info for notification
    $interest = $pdo->prepare("SELECT sender_id FROM interests WHERE id=?"); $interest->execute([$id]); $int = $interest->fetch();
    if ($int) {
        $msg = $status === 'accepted' ? $_SESSION['user_name'].' accepted your interest!' : $_SESSION['user_name'].' declined your interest.';
        $type = $status === 'accepted' ? 'interest_accepted' : 'interest_rejected';
        createNotification($pdo, $int['sender_id'], $_SESSION['user_id'], $type, $msg, '', 'interests.php');
    }
    echo json_encode(['success'=>true,'message'=>'Request '.$status]);
} else echo json_encode(['success'=>false,'error'=>'Request not found']);
