<?php
$pageTitle = 'Interest Requests';
require_once 'includes/header.php';
requireLogin();
$user = getCurrentUser($pdo);
$tab = $_GET['tab'] ?? 'received';

// Received requests
$recvStmt = $pdo->prepare("SELECT i.*, u.full_name, u.profile_photo, u.age, u.city, u.education FROM interests i JOIN users u ON i.sender_id = u.id WHERE i.receiver_id = ? ORDER BY i.created_at DESC");
$recvStmt->execute([$user['id']]); $receivedReqs = $recvStmt->fetchAll();

// Sent requests
$sentStmt = $pdo->prepare("SELECT i.*, u.full_name, u.profile_photo, u.age, u.city, u.education FROM interests i JOIN users u ON i.receiver_id = u.id WHERE i.sender_id = ? ORDER BY i.created_at DESC");
$sentStmt->execute([$user['id']]); $sentReqs = $sentStmt->fetchAll();

// Accepted
$accStmt = $pdo->prepare("SELECT i.*, u.id as uid, u.full_name, u.profile_photo, u.age, u.city, u.education FROM interests i JOIN users u ON (CASE WHEN i.sender_id = ? THEN i.receiver_id ELSE i.sender_id END) = u.id WHERE (i.sender_id = ? OR i.receiver_id = ?) AND i.status = 'accepted' ORDER BY i.updated_at DESC");
$accStmt->execute([$user['id'], $user['id'], $user['id']]); $acceptedReqs = $accStmt->fetchAll();
?>

<section class="py-5" style="background:var(--cream);min-height:80vh">
<div class="container" style="max-width:900px">
    <h2 class="text-center mb-4" style="font-family:'Playfair Display',serif"><i class="fas fa-heart me-2" style="color:var(--pink)"></i>Interest Requests</h2>
    
    <ul class="nav nav-pills justify-content-center mb-4 gap-2">
        <li><a class="btn <?php echo $tab==='received'?'btn-pink':'btn-outline-pink'; ?>" href="?tab=received">Received <span class="badge bg-white text-dark ms-1"><?php echo count($receivedReqs); ?></span></a></li>
        <li><a class="btn <?php echo $tab==='sent'?'btn-pink':'btn-outline-pink'; ?>" href="?tab=sent">Sent <span class="badge bg-white text-dark ms-1"><?php echo count($sentReqs); ?></span></a></li>
        <li><a class="btn <?php echo $tab==='accepted'?'btn-pink':'btn-outline-pink'; ?>" href="?tab=accepted">Accepted <span class="badge bg-white text-dark ms-1"><?php echo count($acceptedReqs); ?></span></a></li>
    </ul>

    <?php
    $items = $tab==='sent' ? $sentReqs : ($tab==='accepted' ? $acceptedReqs : $receivedReqs);
    if (empty($items)): ?>
    <div class="empty-state py-5"><i class="fas fa-heart-broken d-block mb-3" style="font-size:3rem;color:var(--pink-soft)"></i><h5>No requests yet</h5><p class="text-muted">Start exploring profiles and send interests!</p><a href="search.php" class="btn btn-pink mt-2">Browse Profiles</a></div>
    <?php else: foreach ($items as $r): ?>
    <div class="glass-card mb-3">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <img src="<?php echo getProfilePhoto($r['profile_photo']); ?>" style="width:60px;height:60px;border-radius:50%;object-fit:cover;border:2px solid var(--pink-soft)">
            <div class="flex-grow-1">
                <h6 class="mb-0"><a href="profile.php?id=<?php echo $tab==='sent'?$r['receiver_id']:($tab==='accepted'?$r['uid']:$r['sender_id']); ?>" style="color:var(--dark)"><?php echo sanitize($r['full_name']); ?></a>, <?php echo $r['age']; ?></h6>
                <small class="text-muted"><i class="fas fa-graduation-cap me-1"></i><?php echo sanitize($r['education']?:'N/A'); ?> | <i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($r['city']?:'N/A'); ?></small>
            </div>
            <div>
                <?php if ($tab === 'received' && $r['status'] === 'pending'): ?>
                <button class="btn btn-pink btn-sm" onclick="respondInterest(<?php echo $r['id']; ?>,'accepted')"><i class="fas fa-check me-1"></i>Accept</button>
                <button class="btn btn-outline-secondary btn-sm" onclick="respondInterest(<?php echo $r['id']; ?>,'rejected')"><i class="fas fa-times me-1"></i>Reject</button>
                <?php elseif ($r['status'] === 'accepted'): ?>
                <span class="badge bg-success">Accepted</span>
                <a href="messages.php?user=<?php echo $tab==='accepted'?$r['uid']:$r['sender_id']; ?>" class="btn btn-pink btn-sm ms-1"><i class="fas fa-comment me-1"></i>Chat</a>
                <?php elseif ($r['status'] === 'pending'): ?>
                <span class="badge" style="background:var(--pink-bg);color:var(--pink)">Pending</span>
                <?php elseif ($r['status'] === 'rejected'): ?>
                <span class="badge bg-secondary">Rejected</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; endif; ?>
</div>
</section>

<script>
function respondInterest(id, status) {
    fetch('api/respond-interest.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'id='+id+'&status='+status+'&csrf_token=<?php echo generateCSRFToken(); ?>'})
    .then(r=>r.json()).then(d=>{ if(d.success){location.reload();showToast(d.message)} else showToast(d.error,'error'); });
}
</script>
<?php require_once 'includes/footer.php'; ?>
