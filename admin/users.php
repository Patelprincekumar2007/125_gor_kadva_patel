<?php
require_once 'includes/admin-header.php';

// Handle actions FIRST before query
if ($_SERVER['REQUEST_METHOD']==='POST' && validateCSRFToken($_POST['csrf_token']??'')) {
    $uid = (int)($_POST['user_id']??0); $action = $_POST['action']??'';
    if ($uid && $action) {
        if ($action==='verify') $pdo->prepare("UPDATE users SET verification_status='verified' WHERE id=?")->execute([$uid]);
        elseif ($action==='block') $pdo->prepare("UPDATE users SET profile_status='blocked' WHERE id=?")->execute([$uid]);
        elseif ($action==='unblock') $pdo->prepare("UPDATE users SET profile_status='active' WHERE id=?")->execute([$uid]);
        elseif ($action==='delete') $pdo->prepare("UPDATE users SET profile_status='deleted' WHERE id=?")->execute([$uid]);
        elseif ($action==='feature') $pdo->prepare("UPDATE users SET is_featured=1 WHERE id=?")->execute([$uid]);
        elseif ($action==='unfeature') $pdo->prepare("UPDATE users SET is_featured=0 WHERE id=?")->execute([$uid]);
        header('Location: users.php?'.http_build_query($_GET)); exit();
    }
}

$page = max(1,(int)($_GET['page']??1)); $perPage = 20; $offset = ($page-1)*$perPage;
$search = sanitize($_GET['search']??''); $status = sanitize($_GET['status']??'');

$where = []; $params = [];
if ($search) { $where[] = "(full_name LIKE ? OR email LIKE ? OR mobile LIKE ?)"; $s = "%$search%"; $params = [$s,$s,$s]; }
if ($status) { $where[] = "verification_status = ?"; $params[] = $status; }
$whereSQL = $where ? 'WHERE '.implode(' AND ',$where) : '';

$total = $pdo->prepare("SELECT COUNT(*) FROM users $whereSQL"); $total->execute($params); $totalCount = $total->fetchColumn();
$totalPages = ceil($totalCount/$perPage);
$allParams = array_merge($params, [$perPage, $offset]);
$users = $pdo->prepare("SELECT * FROM users $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?"); $users->execute($allParams);
$userList = $users->fetchAll();
?>

<!-- Filter Bar -->
<div class="filter-bar">
    <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" class="admin-input" name="search" placeholder="🔍  Search name, email, mobile..." value="<?php echo $search; ?>">
        </div>
        <div class="col-md-3">
            <select class="admin-input admin-select" name="status">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status==='pending'?'selected':''; ?>>Pending</option>
                <option value="verified" <?php echo $status==='verified'?'selected':''; ?>>Verified</option>
                <option value="rejected" <?php echo $status==='rejected'?'selected':''; ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-2"><button class="btn-admin btn-admin-primary w-100"><i class="fas fa-search"></i> Search</button></div>
        <div class="col-md-2"><a href="users.php" class="btn-admin btn-admin-outline w-100 text-center">Clear</a></div>
    </form>
</div>

<!-- Users Table -->
<div class="data-panel">
    <div class="panel-header">
        <h6><i class="fas fa-users me-2" style="color:var(--admin-primary)"></i>All Users</h6>
        <span class="badge-status badge-pending"><?php echo $totalCount; ?> total</span>
    </div>
    <div class="table-responsive">
        <table class="admin-table">
            <thead><tr><th>ID</th><th>Photo</th><th>Name</th><th>Gender</th><th>Contact</th><th>City</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
            <tbody>
            <?php if(empty($userList)): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted">No users found</td></tr>
            <?php else: foreach ($userList as $u): ?>
            <tr>
                <td><strong>#<?php echo $u['id']; ?></strong></td>
                <td><img src="<?php echo getProfilePhoto($u['profile_photo']); ?>" style="width:38px;height:38px;border-radius:10px;object-fit:cover;border:2px solid #f0f0f0"></td>
                <td>
                    <strong><?php echo sanitize($u['full_name']); ?></strong>
                    <?php if($u['is_featured']): ?><span style="background:linear-gradient(135deg,#f39c12,#f1c40f);color:#fff;padding:2px 8px;border-radius:50px;font-size:0.65rem;font-weight:700;margin-left:4px;">★ FEATURED</span><?php endif; ?>
                </td>
                <td><span class="badge-status <?php echo $u['gender']==='Male'?'badge-male':'badge-female'; ?>"><?php echo $u['gender']; ?></span></td>
                <td><small><?php echo sanitize($u['email']); ?><br><strong><?php echo sanitize($u['mobile']); ?></strong></small></td>
                <td><small><?php echo sanitize($u['city']?:$u['village']??'—'); ?></small></td>
                <td>
                    <?php if($u['verification_status']==='verified'): ?><span class="badge-status badge-verified"><i class="fas fa-check-circle"></i> Verified</span>
                    <?php elseif($u['verification_status']==='pending'): ?><span class="badge-status badge-pending"><i class="fas fa-clock"></i> Pending</span>
                    <?php else: ?><span class="badge-status badge-rejected"><i class="fas fa-times-circle"></i> Rejected</span><?php endif; ?>
                    <?php if($u['profile_status']==='blocked'): ?><br><span class="badge-status badge-blocked"><i class="fas fa-ban"></i> Blocked</span><?php endif; ?>
                </td>
                <td><small class="text-muted"><?php echo date('d M Y',strtotime($u['created_at'])); ?></small></td>
                <td>
                    <div class="admin-dropdown dropdown">
                        <button class="dropdown-toggle" data-bs-toggle="dropdown">Actions</button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="openProfileModal(<?php echo $u['id']; ?>);return false;"><i class="fas fa-eye me-2 text-primary"></i>View Profile</a></li>
                            <?php if($u['verification_status']!=='verified'): ?>
                            <li><form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="verify"><button class="dropdown-item"><i class="fas fa-check me-2 text-success"></i>Verify</button></form></li>
                            <?php endif; ?>
                            <?php if($u['profile_status']!=='blocked'): ?>
                            <li><form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="block"><button class="dropdown-item"><i class="fas fa-ban me-2 text-warning"></i>Block</button></form></li>
                            <?php else: ?>
                            <li><form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="unblock"><button class="dropdown-item"><i class="fas fa-unlock me-2 text-info"></i>Unblock</button></form></li>
                            <?php endif; ?>
                            <?php if(!$u['is_featured']): ?>
                            <li><form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="feature"><button class="dropdown-item"><i class="fas fa-star me-2 text-warning"></i>Feature</button></form></li>
                            <?php else: ?>
                            <li><form method="POST" class="d-inline"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="unfeature"><button class="dropdown-item"><i class="far fa-star me-2"></i>Unfeature</button></form></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                <?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="delete">
                                <button class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i>Delete</button>
                            </form></li>
                        </ul>
                    </div>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
<nav><ul class="pagination admin-pagination justify-content-center">
<?php for ($i=1;$i<=$totalPages;$i++): ?>
<li class="page-item <?php echo $i===$page?'active':''; ?>"><a class="page-link" href="?<?php echo http_build_query(array_merge($_GET,['page'=>$i])); ?>"><?php echo $i; ?></a></li>
<?php endfor; ?>
</ul></nav>
<?php endif; ?>
<!-- Profile Detail Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border:none;border-radius:18px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--pink),var(--pink-light));border:none;padding:20px 28px;">
                <h5 class="modal-title text-white" style="font-family:'Playfair Display',serif;"><i class="fas fa-user me-2"></i>Profile Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:28px;" id="profileModalBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:var(--pink)"></i></div>
            </div>
        </div>
    </div>
</div>

<?php
$usersJSON = [];
foreach ($userList as $u) {
    $usersJSON[$u['id']] = [
        'photo' => getProfilePhoto($u['profile_photo']),
        'name' => sanitize($u['full_name']),
        'age' => $u['age'],
        'gender' => $u['gender'],
        'dob' => $u['dob'] ? date('d M Y', strtotime($u['dob'])) : 'N/A',
        'email' => sanitize($u['email']),
        'mobile' => sanitize($u['mobile']),
        'village' => sanitize($u['village'] ?? 'N/A'),
        'city' => sanitize($u['city'] ?? 'N/A'),
        'state' => sanitize($u['state'] ?? 'N/A'),
        'education' => sanitize($u['education'] ?? 'N/A'),
        'occupation' => sanitize($u['occupation'] ?? 'N/A'),
        'income' => sanitize($u['annual_income'] ?? 'N/A'),
        'marital_status' => sanitize($u['marital_status'] ?? 'N/A'),
        'height' => sanitize($u['height'] ?? 'N/A'),
        'weight' => sanitize($u['weight'] ?? 'N/A'),
        'complexion' => sanitize($u['complexion'] ?? 'N/A'),
        'religion' => sanitize($u['religion'] ?? 'Hindu'),
        'sub_caste' => sanitize($u['sub_caste'] ?? 'N/A'),
        'gotra' => sanitize($u['gotra'] ?? 'N/A'),
        'manglik' => sanitize($u['manglik'] ?? 'N/A'),
        'family_type' => sanitize($u['family_type'] ?? 'N/A'),
        'father_name' => sanitize($u['father_name'] ?? 'N/A'),
        'father_occupation' => sanitize($u['father_occupation'] ?? 'N/A'),
        'mother_name' => sanitize($u['mother_name'] ?? 'N/A'),
        'mother_occupation' => sanitize($u['mother_occupation'] ?? 'N/A'),
        'siblings' => sanitize($u['siblings'] ?? 'N/A'),
        'bio' => sanitize($u['bio'] ?? 'N/A'),
        'hobbies' => sanitize($u['hobbies'] ?? 'N/A'),
        'partner_preferences' => sanitize($u['partner_preferences'] ?? 'N/A'),
        'registered' => date('d M Y, h:i A', strtotime($u['created_at'])),
        'verification' => $u['verification_status'],
        'status' => $u['profile_status'],
    ];
}
?>
<script>
const profileData = <?php echo json_encode($usersJSON); ?>;
function openProfileModal(id) {
    const p = profileData[id];
    if (!p) return;
    const vBadge = p.verification==='verified' ? '<span class="badge-status badge-verified">✓ Verified</span>' : p.verification==='pending' ? '<span class="badge-status badge-pending">⏳ Pending</span>' : '<span class="badge-status badge-rejected">✗ Rejected</span>';
    document.getElementById('profileModalBody').innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center mb-3">
                <img src="${p.photo}" style="width:150px;height:150px;border-radius:50%;object-fit:cover;border:4px solid var(--pink-soft);margin-bottom:12px;">
                <h5 style="font-family:'Playfair Display',serif;margin-bottom:4px;">${p.name}</h5>
                <span class="badge-status ${p.gender==='Male'?'badge-male':'badge-female'}">${p.gender}</span>
                <span class="badge-status badge-pending ms-1">${p.age} yrs</span>
                <div class="mt-2">${vBadge}</div>
                <p class="text-muted mt-2" style="font-size:0.8rem;"><i class="fas fa-calendar me-1"></i>${p.registered}</p>
            </div>
            <div class="col-md-8">
                <div class="row g-2" style="font-size:0.87rem;">
                    <div class="col-12"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-user me-1"></i>Personal</h6></div>
                    <div class="col-6"><strong>DOB:</strong> ${p.dob}</div><div class="col-6"><strong>Marital:</strong> ${p.marital_status}</div>
                    <div class="col-6"><strong>Height:</strong> ${p.height}</div><div class="col-6"><strong>Weight:</strong> ${p.weight}</div>
                    <div class="col-6"><strong>Complexion:</strong> ${p.complexion}</div>
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-phone me-1"></i>Contact</h6></div>
                    <div class="col-6"><strong>Email:</strong> ${p.email}</div><div class="col-6"><strong>Mobile:</strong> ${p.mobile}</div>
                    <div class="col-6"><strong>Village:</strong> ${p.village}</div><div class="col-6"><strong>City:</strong> ${p.city}</div>
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-briefcase me-1"></i>Career</h6></div>
                    <div class="col-6"><strong>Education:</strong> ${p.education}</div><div class="col-6"><strong>Occupation:</strong> ${p.occupation}</div>
                    <div class="col-6"><strong>Income:</strong> ${p.income}</div>
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-om me-1"></i>Religious</h6></div>
                    <div class="col-6"><strong>Religion:</strong> ${p.religion}</div><div class="col-6"><strong>Sub Caste:</strong> ${p.sub_caste}</div>
                    <div class="col-6"><strong>Gotra:</strong> ${p.gotra}</div><div class="col-6"><strong>Manglik:</strong> ${p.manglik}</div>
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-users me-1"></i>Family</h6></div>
                    <div class="col-6"><strong>Type:</strong> ${p.family_type}</div><div class="col-6"><strong>Siblings:</strong> ${p.siblings}</div>
                    <div class="col-6"><strong>Father:</strong> ${p.father_name}</div><div class="col-6"><strong>Father Job:</strong> ${p.father_occupation}</div>
                    <div class="col-6"><strong>Mother:</strong> ${p.mother_name}</div><div class="col-6"><strong>Mother Job:</strong> ${p.mother_occupation}</div>
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.88rem;border-bottom:1px solid #f3e4ea;padding-bottom:6px;"><i class="fas fa-heart me-1"></i>About</h6></div>
                    <div class="col-12"><strong>Bio:</strong> ${p.bio}</div>
                    <div class="col-12"><strong>Hobbies:</strong> ${p.hobbies}</div>
                    <div class="col-12"><strong>Partner Pref:</strong> ${p.partner_preferences}</div>
                </div>
            </div>
        </div>`;
    new bootstrap.Modal(document.getElementById('profileModal')).show();
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>

