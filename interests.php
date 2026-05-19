<?php
$pageTitle = 'My Interests';
require_once 'includes/auth.php';
requireLogin();

// Handle accept/reject actions before HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $interestId = (int)($_POST['interest_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'];
    if ($interestId && in_array($action, ['accept', 'reject'])) {
        $status = $action === 'accept' ? 'accepted' : 'rejected';
        $pdo->prepare("UPDATE interests SET status = ?, updated_at = NOW() WHERE id = ? AND receiver_id = ?")
            ->execute([$status, $interestId, $userId]);
        if ($action === 'accept') {
            $interest = $pdo->prepare("SELECT sender_id FROM interests WHERE id = ?");
            $interest->execute([$interestId]);
            $senderId = $interest->fetchColumn();
            $userName = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $userName->execute([$userId]);
            createNotification($pdo, $senderId, $userId, 'interest_accepted', 'Interest Accepted!', $userName->fetchColumn() . ' accepted your interest request.', 'messages.php');
        }
        header('Location: interests.php');
        exit();
    }
}

require_once 'includes/header.php';
$user = getCurrentUser($pdo);

// Tab
$tab = $_GET['tab'] ?? 'received';

// Received interests
$recvStmt = $pdo->prepare("SELECT i.*, u.full_name, u.age, u.city, u.village, u.profile_photo, u.education, u.occupation FROM interests i JOIN users u ON i.sender_id = u.id WHERE i.receiver_id = ? ORDER BY i.created_at DESC");
$recvStmt->execute([$user['id']]);
$receivedList = $recvStmt->fetchAll();

// Sent interests
$sentStmt = $pdo->prepare("SELECT i.*, u.full_name, u.age, u.city, u.village, u.profile_photo, u.education, u.occupation FROM interests i JOIN users u ON i.receiver_id = u.id WHERE i.sender_id = ? ORDER BY i.created_at DESC");
$sentStmt->execute([$user['id']]);
$sentList = $sentStmt->fetchAll();

// Accepted (mutual)
$accStmt = $pdo->prepare("SELECT i.*, 
    CASE WHEN i.sender_id = ? THEN u2.full_name ELSE u1.full_name END AS full_name,
    CASE WHEN i.sender_id = ? THEN u2.age ELSE u1.age END AS age,
    CASE WHEN i.sender_id = ? THEN u2.city ELSE u1.city END AS city,
    CASE WHEN i.sender_id = ? THEN u2.village ELSE u1.village END AS village,
    CASE WHEN i.sender_id = ? THEN u2.profile_photo ELSE u1.profile_photo END AS profile_photo,
    CASE WHEN i.sender_id = ? THEN u2.id ELSE u1.id END AS other_id
    FROM interests i 
    JOIN users u1 ON i.sender_id = u1.id 
    JOIN users u2 ON i.receiver_id = u2.id 
    WHERE (i.sender_id = ? OR i.receiver_id = ?) AND i.status = 'accepted' ORDER BY i.updated_at DESC");
$uid = $user['id'];
$accStmt->execute([$uid,$uid,$uid,$uid,$uid,$uid,$uid,$uid]);
$acceptedList = $accStmt->fetchAll();
?>

<section class="py-5" style="background:var(--cream);min-height:80vh">
<div class="container">
    <h2 class="text-center mb-4" style="font-family:'Playfair Display',serif">
        <i class="fas fa-heart me-2" style="color:var(--pink)"></i>My Interests
    </h2>

    <!-- Tabs -->
    <div class="d-flex justify-content-center gap-2 mb-4">
        <a href="?tab=received" class="btn <?php echo $tab==='received'?'btn-pink':'btn-outline-pink'; ?> btn-sm px-4">
            <i class="fas fa-inbox me-1"></i>Received <?php if(count(array_filter($receivedList, fn($r) => $r['status']==='pending'))): ?><span class="badge bg-danger ms-1"><?php echo count(array_filter($receivedList, fn($r) => $r['status']==='pending')); ?></span><?php endif; ?>
        </a>
        <a href="?tab=sent" class="btn <?php echo $tab==='sent'?'btn-pink':'btn-outline-pink'; ?> btn-sm px-4">
            <i class="fas fa-paper-plane me-1"></i>Sent
        </a>
        <a href="?tab=accepted" class="btn <?php echo $tab==='accepted'?'btn-pink':'btn-outline-pink'; ?> btn-sm px-4">
            <i class="fas fa-heart me-1"></i>Accepted
        </a>
    </div>

    <!-- Received Tab -->
    <?php if ($tab === 'received'): ?>
    <div class="row g-3">
        <?php if (empty($receivedList)): ?>
        <div class="col-12"><div class="glass-card text-center py-5"><i class="fas fa-inbox d-block mb-2" style="font-size:2.5rem;color:var(--pink-soft)"></i><h5>No received requests</h5><p class="text-muted">When someone sends you an interest, it will appear here</p></div></div>
        <?php else: foreach ($receivedList as $r): ?>
        <div class="col-md-6">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?php echo getProfilePhoto($r['profile_photo']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--pink-soft)">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo sanitize($r['full_name']); ?>, <?php echo $r['age']; ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($r['city'] ?: $r['village'] ?: 'N/A'); ?></small>
                        <br><small class="text-muted"><i class="fas fa-briefcase me-1"></i><?php echo sanitize($r['occupation'] ?? $r['education'] ?? 'N/A'); ?></small>
                        <br><small class="text-muted" style="font-size:0.75rem"><?php echo timeAgo($r['created_at']); ?></small>
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <?php if ($r['status'] === 'pending'): ?>
                        <form method="POST"><input type="hidden" name="interest_id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="accept"><?php echo csrfField(); ?>
                            <button class="btn btn-sm w-100" style="background:#4caf50;color:#fff;border-radius:50px;border:none;padding:6px 16px;font-size:0.8rem"><i class="fas fa-check me-1"></i>Accept</button></form>
                        <form method="POST"><input type="hidden" name="interest_id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="reject"><?php echo csrfField(); ?>
                            <button class="btn btn-sm w-100" style="background:#e74c3c;color:#fff;border-radius:50px;border:none;padding:6px 16px;font-size:0.8rem"><i class="fas fa-times me-1"></i>Reject</button></form>
                        <?php elseif ($r['status'] === 'accepted'): ?>
                        <span class="badge" style="background:#e8f5e9;color:#4caf50;padding:6px 14px;border-radius:50px;font-size:0.78rem"><i class="fas fa-check-circle me-1"></i>Accepted</span>
                        <?php else: ?>
                        <span class="badge" style="background:#ffebee;color:#e74c3c;padding:6px 14px;border-radius:50px;font-size:0.78rem"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <!-- Sent Tab -->
    <?php if ($tab === 'sent'): ?>
    <div class="row g-3">
        <?php if (empty($sentList)): ?>
        <div class="col-12"><div class="glass-card text-center py-5"><i class="fas fa-paper-plane d-block mb-2" style="font-size:2.5rem;color:var(--pink-soft)"></i><h5>No sent requests</h5><p class="text-muted">Send interest requests from profile pages</p></div></div>
        <?php else: foreach ($sentList as $s): ?>
        <div class="col-md-6">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?php echo getProfilePhoto($s['profile_photo']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--pink-soft)">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo sanitize($s['full_name']); ?>, <?php echo $s['age']; ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($s['city'] ?: $s['village'] ?: 'N/A'); ?></small>
                        <br><small class="text-muted" style="font-size:0.75rem"><?php echo timeAgo($s['created_at']); ?></small>
                    </div>
                    <?php if ($s['status'] === 'pending'): ?>
                    <span class="badge" style="background:#fff3e0;color:#ff9800;padding:6px 14px;border-radius:50px;font-size:0.78rem"><i class="fas fa-clock me-1"></i>Pending</span>
                    <?php elseif ($s['status'] === 'accepted'): ?>
                    <span class="badge" style="background:#e8f5e9;color:#4caf50;padding:6px 14px;border-radius:50px;font-size:0.78rem"><i class="fas fa-check-circle me-1"></i>Accepted</span>
                    <?php else: ?>
                    <span class="badge" style="background:#ffebee;color:#e74c3c;padding:6px 14px;border-radius:50px;font-size:0.78rem"><i class="fas fa-times-circle me-1"></i>Rejected</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

    <!-- Accepted Tab -->
    <?php if ($tab === 'accepted'): ?>
    <div class="row g-3">
        <?php if (empty($acceptedList)): ?>
        <div class="col-12"><div class="glass-card text-center py-5"><i class="fas fa-heart d-block mb-2" style="font-size:2.5rem;color:var(--pink-soft)"></i><h5>No accepted interests yet</h5><p class="text-muted">When someone accepts your interest, you can start chatting!</p></div></div>
        <?php else: foreach ($acceptedList as $a): ?>
        <div class="col-md-6">
            <div class="glass-card">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?php echo getProfilePhoto($a['profile_photo']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:3px solid var(--pink-soft)">
                    <div class="flex-grow-1">
                        <h6 class="mb-0"><?php echo sanitize($a['full_name']); ?>, <?php echo $a['age']; ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($a['city'] ?: $a['village'] ?: 'N/A'); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="profile.php?id=<?php echo $a['other_id']; ?>" class="btn btn-outline-pink btn-sm"><i class="fas fa-eye"></i></a>
                        <a href="messages.php?user=<?php echo $a['other_id']; ?>" class="btn btn-pink btn-sm"><i class="fas fa-comment me-1"></i>Chat</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
    <?php endif; ?>

</div>
</section>
<?php require_once 'includes/footer.php'; ?>
