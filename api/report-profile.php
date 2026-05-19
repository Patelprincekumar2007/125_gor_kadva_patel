<?php
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!isLoggedIn() || !validateCSRFToken($_POST['csrf_token']??'')) { echo json_encode(['success'=>false]); exit; }
$reportedId = (int)($_POST['reported_id'] ?? 0);
$reason = sanitize($_POST['reason'] ?? '');
$desc = sanitize($_POST['description'] ?? '');
if (!$reportedId || empty($reason)) { echo json_encode(['success'=>false,'error'=>'Missing fields']); exit; }
$stmt = $pdo->prepare("INSERT INTO reports (reporter_id, reported_user_id, reason, description) VALUES (?,?,?,?)");
$stmt->execute([$_SESSION['user_id'], $reportedId, $reason, $desc]);
echo json_encode(['success'=>true,'message'=>'Report submitted']);
