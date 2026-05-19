<?php
require_once 'includes/admin-header.php';
if ($_SERVER['REQUEST_METHOD']==='POST' && validateCSRFToken($_POST['csrf_token']??'')) {
    $rid = (int)($_POST['report_id']??0); $action = $_POST['action']??'';
    if ($rid) {
        if ($action==='dismiss') $pdo->prepare("UPDATE reports SET status='dismissed' WHERE id=?")->execute([$rid]);
        elseif ($action==='resolve') $pdo->prepare("UPDATE reports SET status='resolved' WHERE id=?")->execute([$rid]);
        elseif ($action==='block_user') {
            $report = $pdo->prepare("SELECT reported_user_id FROM reports WHERE id=?"); $report->execute([$rid]); $r=$report->fetch();
            if($r) $pdo->prepare("UPDATE users SET profile_status='blocked' WHERE id=?")->execute([$r['reported_user_id']]);
            $pdo->prepare("UPDATE reports SET status='resolved' WHERE id=?")->execute([$rid]);
        }
        header('Location: reports.php'); exit();
    }
}
$reports = $pdo->prepare("SELECT r.*, u1.full_name as reporter_name, u2.full_name as reported_name, u2.profile_photo as reported_photo FROM reports r JOIN users u1 ON r.reporter_id=u1.id JOIN users u2 ON r.reported_user_id=u2.id ORDER BY r.created_at DESC");
$reports->execute(); $reportList = $reports->fetchAll();
?>

<?php if (empty($reportList)): ?>
<div class="data-panel">
    <div class="panel-body">
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-shield-check"></i></div>
            <h5>No Reports</h5>
            <p>Everything looks good — no user reports to review.</p>
        </div>
    </div>
</div>
<?php else: foreach ($reportList as $r): ?>
<div class="report-card">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <img src="<?php echo getProfilePhoto($r['reported_photo']); ?>" style="width:50px;height:50px;border-radius:12px;object-fit:cover;border:2px solid #f0f0f0">
        <div class="flex-grow-1">
            <h6 class="mb-1" style="font-size:0.95rem"><strong><?php echo sanitize($r['reported_name']); ?></strong> <span class="text-muted fw-normal">reported by</span> <?php echo sanitize($r['reporter_name']); ?></h6>
            <span class="badge-status badge-pending mb-1"><?php echo ucfirst(str_replace('_',' ',$r['reason'])); ?></span>
            <?php if($r['description']): ?><p class="text-muted small mb-0 mt-1"><?php echo sanitize($r['description']); ?></p><?php endif; ?>
            <small class="text-muted"><?php echo timeAgo($r['created_at']); ?></small>
        </div>
        <div class="d-flex gap-2">
            <?php if ($r['status']==='pending'): ?>
            <form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="report_id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="block_user">
                <button class="btn-admin btn-admin-danger"><i class="fas fa-ban"></i> Block</button></form>
            <form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="report_id" value="<?php echo $r['id']; ?>"><input type="hidden" name="action" value="dismiss">
                <button class="btn-admin btn-admin-outline"><i class="fas fa-times"></i> Dismiss</button></form>
            <?php else: ?>
            <span class="badge-status <?php echo $r['status']==='resolved'?'badge-verified':'badge-blocked'; ?>"><?php echo ucfirst($r['status']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; endif; ?>

<?php require_once 'includes/admin-footer.php'; ?>
