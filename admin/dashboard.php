<?php
require_once 'includes/admin-header.php';
// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$activeUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE profile_status='active'")->fetchColumn();
$males = $pdo->query("SELECT COUNT(*) FROM users WHERE gender='Male'")->fetchColumn();
$females = $pdo->query("SELECT COUNT(*) FROM users WHERE gender='Female'")->fetchColumn();
$pending = $pdo->query("SELECT COUNT(*) FROM users WHERE verification_status='pending'")->fetchColumn();
$matches = $pdo->query("SELECT COUNT(*) FROM interests WHERE status='accepted'")->fetchColumn();
$totalChats = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();
$reports = $pdo->query("SELECT COUNT(*) FROM reports WHERE status='pending'")->fetchColumn();
$newToday = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()")->fetchColumn();
$contacts = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status='unread'")->fetchColumn();

// Recent registrations
$recent = $pdo->query("SELECT id,full_name,gender,email,mobile,created_at,verification_status FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll();
?>

<!-- Stat Cards -->
<div class="stat-grid">
    <?php
    $stats = [
        ['Total Users', $totalUsers, 'fa-users', 'purple'],
        ['Active', $activeUsers, 'fa-user-check', 'green'],
        ['Males', $males, 'fa-mars', 'blue'],
        ['Females', $females, 'fa-venus', 'pink'],
        ['New Today', $newToday, 'fa-user-plus', 'orange'],
        ['Pending', $pending, 'fa-clock', 'yellow'],
        ['Matches', $matches, 'fa-heart', 'pink'],
        ['Messages', $totalChats, 'fa-comment-dots', 'teal'],
        ['Reports', $reports, 'fa-flag', 'red'],
        ['Contacts', $contacts, 'fa-envelope', 'green'],
    ];
    foreach ($stats as $s): ?>
    <div class="stat-card <?php echo $s[3]; ?>">
        <div class="stat-icon"><i class="fas <?php echo $s[2]; ?>"></i></div>
        <div class="stat-value"><?php echo $s[1]; ?></div>
        <div class="stat-label"><?php echo $s[0]; ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Gender Ratio + Recent -->
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="data-panel">
            <div class="panel-header"><h6>Gender Ratio</h6></div>
            <div class="panel-body">
                <div class="d-flex gap-4 justify-content-center">
                    <div class="text-center">
                        <div class="ratio-circle ratio-male"><?php echo $totalUsers ? round($males/$totalUsers*100) : 0; ?>%</div>
                        <small class="text-muted fw-semibold">Male</small>
                    </div>
                    <div class="text-center">
                        <div class="ratio-circle ratio-female"><?php echo $totalUsers ? round($females/$totalUsers*100) : 0; ?>%</div>
                        <small class="text-muted fw-semibold">Female</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="data-panel">
            <div class="panel-header">
                <h6>Recent Registrations</h6>
                <a href="users.php" class="btn-admin btn-admin-outline" style="font-size:0.8rem;padding:5px 14px;">View All</a>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead><tr><th>Name</th><th>Gender</th><th>Email</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                    <tr>
                        <td><strong><?php echo sanitize($r['full_name']); ?></strong></td>
                        <td><span class="badge-status <?php echo $r['gender']==='Male'?'badge-male':'badge-female'; ?>"><?php echo $r['gender']; ?></span></td>
                        <td><small><?php echo sanitize($r['email']); ?></small></td>
                        <td>
                            <?php if($r['verification_status']==='verified'): ?><span class="badge-status badge-verified"><i class="fas fa-check-circle"></i> Verified</span>
                            <?php elseif($r['verification_status']==='pending'): ?><span class="badge-status badge-pending"><i class="fas fa-clock"></i> Pending</span>
                            <?php else: ?><span class="badge-status badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span><?php endif; ?>
                        </td>
                        <td><small class="text-muted"><?php echo date('d M', strtotime($r['created_at'])); ?></small></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>
