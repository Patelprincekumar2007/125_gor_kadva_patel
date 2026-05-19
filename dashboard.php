<?php
$pageTitle = 'Dashboard';
require_once 'includes/auth.php';
requireLogin();

// Handle accept/reject actions before HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $senderId = (int)($_POST['sender_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($senderId && in_array($action, ['accept', 'reject'])) {
        $userId = $_SESSION['user_id'];
        $status = $action === 'accept' ? 'accepted' : 'rejected';
        $pdo->prepare("UPDATE interests SET status = ? WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'")
            ->execute([$status, $senderId, $userId]);
        
        // Notify sender
        if ($action === 'accept') {
            $nameStmt = $pdo->prepare("SELECT full_name FROM users WHERE id = ?");
            $nameStmt->execute([$userId]);
            $userName = $nameStmt->fetchColumn();
            createNotification($pdo, $senderId, $userId, 'interest_accepted', 'Interest Accepted!', "$userName accepted your interest request.", "messages.php");
        }
        header('Location: dashboard.php');
        exit();
    }
}

require_once 'includes/header.php';
$user = getCurrentUser($pdo);
$completion = profileCompletion($user);

// Stats
$sentReq = $pdo->prepare("SELECT COUNT(*) FROM interests WHERE sender_id = ?"); $sentReq->execute([$user['id']]); $sent = $sentReq->fetchColumn();
$recReq = $pdo->prepare("SELECT COUNT(*) FROM interests WHERE receiver_id = ? AND status='pending'"); $recReq->execute([$user['id']]); $received = $recReq->fetchColumn();
$accReq = $pdo->prepare("SELECT COUNT(*) FROM interests WHERE (sender_id = ? OR receiver_id = ?) AND status='accepted'"); $accReq->execute([$user['id'], $user['id']]); $accepted = $accReq->fetchColumn();
$msgCount = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND seen_status = 0"); $msgCount->execute([$user['id']]); $unreadMsgs = $msgCount->fetchColumn();

// Pending interest requests received
$pendingInterests = $pdo->prepare("SELECT u.id, u.full_name, u.age, u.city, u.village, u.profile_photo, i.created_at FROM interests i JOIN users u ON i.sender_id = u.id WHERE i.receiver_id = ? AND i.status = 'pending' ORDER BY i.created_at DESC LIMIT 5");
$pendingInterests->execute([$user['id']]);
$pendingList = $pendingInterests->fetchAll();

// Notifications - only important ones (exclude profile_view type)
$notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND type NOT IN ('profile_view','profile_viewed') ORDER BY created_at DESC LIMIT 5");
$notifStmt->execute([$user['id']]);
$notifications = $notifStmt->fetchAll();
?>

<section class="py-5" style="background:var(--cream);min-height:80vh">
<div class="container">
    <!-- Welcome -->
    <div class="glass-card mb-4">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <img src="<?php echo getProfilePhoto($user['profile_photo']); ?>" alt="" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--pink-soft)">
            </div>
            <div class="col">
                <h3 class="mb-1" style="font-size:clamp(1.1rem,3vw,1.75rem)"><?php echo sanitize($user['full_name']); ?>! 
                    <?php if ($user['verification_status']==='verified'): ?><i class="fas fa-check-circle verified-badge"></i><?php endif; ?>
                </h3>
                <p class="text-muted mb-2">Profile Completion: <strong><?php echo $completion; ?>%</strong></p>
                <div class="completion-bar" style="max-width:300px"><div class="fill" style="width:<?php echo $completion; ?>%"></div></div>
            </div>
            <div class="col-12 col-sm-auto">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="edit-profile.php" class="btn btn-outline-pink btn-sm"><i class="fas fa-edit me-1"></i>Edit Profile</a>
                    <a href="profile.php" class="btn btn-pink btn-sm"><i class="fas fa-eye me-1"></i>View Profile</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="dash-stat"><div class="icon" style="background:#e8f5e9;color:#4caf50"><i class="fas fa-paper-plane"></i></div><h4><?php echo $sent; ?></h4><small class="text-muted">Sent Requests</small></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat"><div class="icon" style="background:#fff3e0;color:#ff9800"><i class="fas fa-inbox"></i></div><h4><?php echo $received; ?></h4><small class="text-muted">Received</small></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat"><div class="icon" style="background:#fce4ec;color:var(--rose)"><i class="fas fa-heart"></i></div><h4><?php echo $accepted; ?></h4><small class="text-muted">Accepted</small></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="dash-stat"><div class="icon" style="background:#e3f2fd;color:#2196f3"><i class="fas fa-comment"></i></div><h4><?php echo $unreadMsgs; ?></h4><small class="text-muted">New Messages</small></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Pending Interest Requests with Accept/Reject -->
        <div class="col-lg-6">
            <div class="glass-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="fas fa-inbox me-2" style="color:var(--pink)"></i>Pending Requests</h5>
                    <a href="interests.php" class="btn btn-outline-pink btn-sm">View All</a>
                </div>
                <?php if (empty($pendingList)): ?>
                <div class="empty-state py-4">
                    <i class="fas fa-inbox d-block mb-2" style="font-size:2rem;color:var(--pink-soft)"></i>
                    <small class="text-muted">No pending interest requests</small>
                </div>
                <?php else: foreach ($pendingList as $p): ?>
                <div class="d-flex align-items-center gap-3 p-3 rounded mb-2" style="background:var(--pink-bg);border-radius:14px !important">
                    <img src="<?php echo getProfilePhoto($p['profile_photo']); ?>" style="width:50px;height:50px;border-radius:50%;object-fit:cover;border:2px solid var(--pink-soft)">
                    <div class="flex-grow-1">
                        <strong style="font-size:0.9rem"><?php echo sanitize($p['full_name']); ?></strong>, <?php echo $p['age']; ?>
                        <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($p['city'] ?: $p['village'] ?: 'N/A'); ?></small>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="POST" class="d-inline">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="sender_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="action" value="accept">
                            <button class="btn btn-sm" style="background:#4caf50;color:#fff;border-radius:50px;padding:6px 14px;font-size:0.78rem;border:none" title="Accept"><i class="fas fa-check me-1"></i>Accept</button>
                        </form>
                        <form method="POST" class="d-inline">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="sender_id" value="<?php echo $p['id']; ?>">
                            <input type="hidden" name="action" value="reject">
                            <button class="btn btn-sm" style="background:#e74c3c;color:#fff;border-radius:50px;padding:6px 14px;font-size:0.78rem;border:none" title="Reject"><i class="fas fa-times me-1"></i>Reject</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>

        <!-- Notifications (only important - no profile views) -->
        <div class="col-lg-6">
            <div class="glass-card h-100">
                <h5 class="mb-3"><i class="fas fa-bell me-2" style="color:var(--pink)"></i>Notifications</h5>
                <?php if (empty($notifications)): ?>
                <div class="empty-state py-4">
                    <i class="fas fa-bell-slash d-block mb-2" style="font-size:2rem;color:var(--pink-soft)"></i>
                    <small class="text-muted">No notifications yet</small>
                </div>
                <?php else: foreach ($notifications as $n): ?>
                <div class="d-flex align-items-start gap-3 p-2 rounded mb-2" style="background:<?php echo $n['is_read']?'transparent':'var(--pink-bg)'; ?>;border-radius:10px !important">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--pink-bg);display:flex;align-items:center;justify-content:center;color:var(--pink);flex-shrink:0">
                        <i class="fas <?php
                            $icon = 'fa-bell';
                            if (strpos($n['type'],'interest')!==false) $icon = 'fa-heart';
                            elseif (strpos($n['type'],'approved')!==false) $icon = 'fa-check-circle';
                            elseif (strpos($n['type'],'rejected')!==false) $icon = 'fa-times-circle';
                            elseif (strpos($n['type'],'message')!==false) $icon = 'fa-comment';
                            echo $icon;
                        ?>"></i>
                    </div>
                    <div>
                        <strong style="font-size:0.88rem"><?php echo sanitize($n['title']); ?></strong>
                        <br><small class="text-muted"><?php echo sanitize($n['message']); ?></small>
                        <br><small class="text-muted" style="font-size:0.75rem"><?php echo timeAgo($n['created_at']); ?></small>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="glass-card mt-4">
        <h5 class="mb-3"><i class="fas fa-bolt me-2" style="color:var(--pink)"></i>Quick Actions</h5>
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="search.php" class="d-block text-center p-3 rounded" style="background:var(--pink-bg);border-radius:14px !important;text-decoration:none;color:var(--dark);transition:all 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <i class="fas fa-search d-block mb-2" style="font-size:1.5rem;color:var(--pink)"></i>
                    <strong style="font-size:0.85rem">Search Profiles</strong>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="edit-profile.php" class="d-block text-center p-3 rounded" style="background:#e8f5e9;border-radius:14px !important;text-decoration:none;color:var(--dark);transition:all 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <i class="fas fa-user-edit d-block mb-2" style="font-size:1.5rem;color:#4caf50"></i>
                    <strong style="font-size:0.85rem">Edit Profile</strong>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="interests.php" class="d-block text-center p-3 rounded" style="background:#fff3e0;border-radius:14px !important;text-decoration:none;color:var(--dark);transition:all 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <i class="fas fa-heart d-block mb-2" style="font-size:1.5rem;color:#ff9800"></i>
                    <strong style="font-size:0.85rem">My Interests</strong>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="messages.php" class="d-block text-center p-3 rounded" style="background:#e3f2fd;border-radius:14px !important;text-decoration:none;color:var(--dark);transition:all 0.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'">
                    <i class="fas fa-comments d-block mb-2" style="font-size:1.5rem;color:#2196f3"></i>
                    <strong style="font-size:0.85rem">Messages</strong>
                </a>
            </div>
        </div>
    </div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
