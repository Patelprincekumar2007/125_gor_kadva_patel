<?php
require_once 'includes/admin-header.php';
// Handle actions
if ($_SERVER['REQUEST_METHOD']==='POST' && validateCSRFToken($_POST['csrf_token']??'')) {
    $uid = (int)($_POST['user_id']??0); $action = $_POST['action']??'';
    if ($uid) {
        if ($action==='approve') { $pdo->prepare("UPDATE users SET verification_status='verified' WHERE id=?")->execute([$uid]); createNotification($pdo,$uid,null,'profile_approved','Your profile has been approved!','Your profile is now visible to other members.','dashboard.php'); }
        elseif ($action==='reject') { $pdo->prepare("UPDATE users SET verification_status='rejected' WHERE id=?")->execute([$uid]); createNotification($pdo,$uid,null,'profile_rejected','Your profile needs updates','Please update your profile information and resubmit.','edit-profile.php'); }
        header('Location: approvals.php'); exit();
    }
}
$pending = $pdo->query("SELECT * FROM users WHERE verification_status='pending' ORDER BY created_at DESC")->fetchAll();
?>

<?php if (empty($pending)): ?>
<div class="data-panel">
    <div class="panel-body">
        <div class="empty-state">
            <div class="empty-icon"><i class="fas fa-check-double"></i></div>
            <h5>All Clear!</h5>
            <p>No pending profile approvals at the moment.</p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <small class="text-muted"><?php echo count($pending); ?> profile(s) awaiting review</small>
</div>
<?php foreach ($pending as $u): ?>
<div class="approval-card">
    <div class="row align-items-center">
        <div class="col-auto">
            <img src="<?php echo getProfilePhoto($u['profile_photo']); ?>" class="user-photo" alt="">
        </div>
        <div class="col user-info">
            <h5>
                <?php echo sanitize($u['full_name']); ?>, <?php echo $u['age']; ?>
                <span class="badge-status <?php echo $u['gender']==='Male'?'badge-male':'badge-female'; ?> ms-1"><?php echo $u['gender']; ?></span>
            </h5>
            <p class="user-meta mb-1">
                <i class="fas fa-envelope me-1"></i><?php echo sanitize($u['email']); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-phone me-1"></i><?php echo sanitize($u['mobile']); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($u['city']?:$u['village']??'N/A'); ?>
            </p>
            <p class="user-meta mb-0">
                <i class="fas fa-graduation-cap me-1"></i><?php echo sanitize($u['education']??'N/A'); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-briefcase me-1"></i><?php echo sanitize($u['occupation']??'N/A'); ?>
                <span class="mx-2">|</span>
                <i class="fas fa-calendar me-1"></i>Registered <?php echo date('d M Y', strtotime($u['created_at'])); ?>
            </p>
        </div>
        <div class="col-auto d-flex gap-2 flex-wrap">
            <form method="POST"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="approve">
                <button class="btn-admin btn-admin-success"><i class="fas fa-check"></i> Approve</button>
            </form>
            <form method="POST"><?php echo csrfField(); ?><input type="hidden" name="user_id" value="<?php echo $u['id']; ?>"><input type="hidden" name="action" value="reject">
                <button class="btn-admin btn-admin-danger"><i class="fas fa-times"></i> Reject</button>
            </form>
            <button class="btn-admin btn-admin-outline" onclick="openProfileModal(<?php echo $u['id']; ?>)"><i class="fas fa-eye"></i> View</button>
        </div>
    </div>
</div>
<?php endforeach; endif; ?>

<!-- Profile Detail Modal -->
<div class="modal fade" id="profileModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border:none;border-radius:18px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,var(--pink),var(--pink-light));border:none;padding:20px 28px;">
                <h5 class="modal-title text-white" style="font-family:'Playfair Display',serif;"><i class="fas fa-user me-2"></i>Profile Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:28px;" id="profileModalBody">
                <div class="text-center py-5"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:var(--pink)"></i><p class="mt-2 text-muted">Loading...</p></div>
            </div>
        </div>
    </div>
</div>

<?php
// Build profile data for JS
$profilesJSON = [];
foreach ($pending as $u) {
    $gallery = $pdo->prepare("SELECT image_path FROM user_gallery WHERE user_id = ?");
    $gallery->execute([$u['id']]);
    $galleryImages = $gallery->fetchAll(PDO::FETCH_COLUMN);
    
    $profilesJSON[$u['id']] = [
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
        'hobbies' => sanitize($u['hobbies'] ?? 'N/A'),
        'bio' => sanitize($u['bio'] ?? 'N/A'),
        'partner_preferences' => sanitize($u['partner_preferences'] ?? 'N/A'),
        'registered' => date('d M Y, h:i A', strtotime($u['created_at'])),
        'gallery' => array_map(function($img) { return SITE_URL . '/uploads/gallery/' . $img; }, $galleryImages),
    ];
}
?>

<script>
const profileData = <?php echo json_encode($profilesJSON); ?>;

function openProfileModal(id) {
    const p = profileData[id];
    if (!p) return;
    
    let galleryHTML = '';
    if (p.gallery && p.gallery.length > 0) {
        galleryHTML = '<div class="mt-3"><h6 style="color:var(--pink);font-size:0.9rem;"><i class="fas fa-images me-1"></i>Gallery</h6><div class="d-flex gap-2 flex-wrap">';
        p.gallery.forEach(img => {
            galleryHTML += '<img src="'+img+'" style="width:80px;height:80px;object-fit:cover;border-radius:10px;border:2px solid #f0f0f0;" onclick="window.open(this.src)">';
        });
        galleryHTML += '</div></div>';
    }

    document.getElementById('profileModalBody').innerHTML = `
        <div class="row">
            <div class="col-md-4 text-center mb-3">
                <img src="${p.photo}" style="width:160px;height:160px;border-radius:50%;object-fit:cover;border:4px solid var(--pink-soft);margin-bottom:12px;">
                <h5 style="font-family:'Playfair Display',serif;margin-bottom:2px;">${p.name}</h5>
                <span class="badge-status ${p.gender==='Male'?'badge-male':'badge-female'}">${p.gender}</span>
                <span class="badge-status badge-pending ms-1">${p.age} yrs</span>
                <p class="text-muted mt-2" style="font-size:0.82rem;"><i class="fas fa-calendar me-1"></i>${p.registered}</p>
            </div>
            <div class="col-md-8">
                <div class="row g-2" style="font-size:0.88rem;">
                    <div class="col-12"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-user me-1"></i>Personal Details</h6></div>
                    <div class="col-6"><strong>DOB:</strong> ${p.dob}</div>
                    <div class="col-6"><strong>Marital Status:</strong> ${p.marital_status}</div>
                    <div class="col-6"><strong>Height:</strong> ${p.height}</div>
                    <div class="col-6"><strong>Weight:</strong> ${p.weight}</div>
                    <div class="col-6"><strong>Complexion:</strong> ${p.complexion}</div>
                    
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-phone me-1"></i>Contact</h6></div>
                    <div class="col-6"><strong>Email:</strong> ${p.email}</div>
                    <div class="col-6"><strong>Mobile:</strong> ${p.mobile}</div>
                    <div class="col-6"><strong>Village:</strong> ${p.village}</div>
                    <div class="col-6"><strong>City:</strong> ${p.city}</div>
                    <div class="col-6"><strong>State:</strong> ${p.state}</div>
                    
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-graduation-cap me-1"></i>Education & Career</h6></div>
                    <div class="col-6"><strong>Education:</strong> ${p.education}</div>
                    <div class="col-6"><strong>Occupation:</strong> ${p.occupation}</div>
                    <div class="col-6"><strong>Income:</strong> ${p.income}</div>
                    
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-om me-1"></i>Religious Details</h6></div>
                    <div class="col-6"><strong>Religion:</strong> ${p.religion}</div>
                    <div class="col-6"><strong>Sub Caste:</strong> ${p.sub_caste}</div>
                    <div class="col-6"><strong>Gotra:</strong> ${p.gotra}</div>
                    <div class="col-6"><strong>Manglik:</strong> ${p.manglik}</div>
                    
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-users me-1"></i>Family</h6></div>
                    <div class="col-6"><strong>Family Type:</strong> ${p.family_type}</div>
                    <div class="col-6"><strong>Siblings:</strong> ${p.siblings}</div>
                    <div class="col-6"><strong>Father:</strong> ${p.father_name}</div>
                    <div class="col-6"><strong>Father's Occupation:</strong> ${p.father_occupation}</div>
                    <div class="col-6"><strong>Mother:</strong> ${p.mother_name}</div>
                    <div class="col-6"><strong>Mother's Occupation:</strong> ${p.mother_occupation}</div>
                    
                    <div class="col-12 mt-2"><h6 style="color:var(--pink);font-size:0.9rem;border-bottom:1px solid var(--border,#f3e4ea);padding-bottom:6px;"><i class="fas fa-heart me-1"></i>About</h6></div>
                    <div class="col-12"><strong>Bio:</strong> ${p.bio}</div>
                    <div class="col-12"><strong>Hobbies:</strong> ${p.hobbies}</div>
                    <div class="col-12"><strong>Partner Preferences:</strong> ${p.partner_preferences}</div>
                </div>
                ${galleryHTML}
            </div>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('profileModal')).show();
}
</script>

<?php require_once 'includes/admin-footer.php'; ?>
