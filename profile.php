<?php
$pageTitle = 'Profile';
require_once 'includes/header.php';
requireLogin();
$user = getCurrentUser($pdo);
$profileId = isset($_GET['id']) ? (int)$_GET['id'] : $user['id'];
$isOwn = ($profileId === $user['id']);

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND profile_status != 'deleted'");
$stmt->execute([$profileId]); $profile = $stmt->fetch();
if (!$profile) { setFlashMessage('error','Profile not found.'); header('Location: search.php'); exit(); }

// Log view (no notification - privacy)
if (!$isOwn) {
    $pdo->prepare("INSERT INTO profile_views (viewer_id, viewed_id) VALUES (?,?)")->execute([$user['id'], $profileId]);
}

// Get gallery
$gallery = $pdo->prepare("SELECT * FROM user_gallery WHERE user_id = ?"); $gallery->execute([$profileId]); $photos = $gallery->fetchAll();

// Get interest status
$interest = getInterestStatus($pdo, $user['id'], $profileId);
$blocked = !$isOwn ? isBlocked($pdo, $user['id'], $profileId) : false;
?>

<section class="py-5" style="background:var(--cream)">
<div class="container">
<div class="row g-4">
    <!-- Left: Photo & Quick Info -->
    <div class="col-lg-4">
        <div class="glass-card text-center mb-4">
            <img src="<?php echo getProfilePhoto($profile['profile_photo']); ?>" alt="" style="width:200px;height:200px;border-radius:50%;object-fit:cover;border:4px solid var(--pink-soft);margin-bottom:15px">
            <h3 style="font-family:'Playfair Display',serif" class="mb-1"><?php echo sanitize($profile['full_name']); ?>, <?php echo calculateAge($profile['dob']); ?>
                <?php if ($profile['is_featured']): ?><span class="featured-badge ms-1">Featured</span><?php endif; ?>
            </h3>
            <?php if ($profile['verification_status']==='verified'): ?>
            <div class="mb-3 d-flex justify-content-center">
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1" style="font-size:0.75rem;font-weight:600;letter-spacing:0.5px">
                    <i class="fas fa-user-shield me-1"></i> Admin Approved & Verified
                </span>
            </div>
            <?php endif; ?>
            <p class="text-muted mb-1"><i class="fas fa-map-marker-alt me-1" style="color:var(--pink)"></i><?php echo sanitize($profile['city'] ?: $profile['village']); ?></p>
            <p class="text-muted mb-3"><i class="fas fa-graduation-cap me-1"></i><?php echo sanitize($profile['education'] ?: 'N/A'); ?></p>
            <?php if (isUserOnline($profile['last_active'])): ?>
            <span class="badge bg-success mb-3"><span class="online-dot me-1"></span>Online</span>
            <?php elseif ($profile['last_active']): ?>
            <small class="text-muted d-block mb-3">Last active: <?php echo timeAgo($profile['last_active']); ?></small>
            <?php endif; ?>
            
            <?php if (!$isOwn): ?>
            <div class="d-grid gap-2">
                <?php if (!$interest): ?>
                <button class="btn btn-pink" onclick="sendInterest(<?php echo $profileId; ?>)" id="interestBtn"><i class="fas fa-heart me-2"></i>Send Interest</button>
                <?php elseif ($interest['status']==='pending'): ?>
                <button class="btn btn-outline-pink" disabled><i class="fas fa-clock me-2"></i>Interest Pending</button>
                <?php elseif ($interest['status']==='accepted'): ?>
                <a href="messages.php?user=<?php echo $profileId; ?>" class="btn btn-pink"><i class="fas fa-comment me-2"></i>Chat Now</a>
                <?php endif; ?>
                <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal"><i class="fas fa-flag me-1"></i>Report</button>
            </div>
            <?php else: ?>
            <a href="edit-profile.php" class="btn btn-outline-pink w-100"><i class="fas fa-edit me-1"></i>Edit Profile</a>
            <?php endif; ?>
        </div>

        <!-- Gallery -->
        <?php if (!empty($photos)): ?>
        <div class="glass-card">
            <h5 class="mb-3"><i class="fas fa-images me-2" style="color:var(--pink)"></i>Gallery</h5>
            <div class="row g-2">
                <?php foreach ($photos as $ph): ?>
                <div class="col-4"><img src="<?php echo SITE_URL.'/uploads/gallery/'.$ph['image_path']; ?>" style="width:100%;height:80px;object-fit:cover;border-radius:8px;cursor:pointer" onclick="window.open(this.src)"></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Details -->
    <div class="col-lg-8">
        <?php if (!empty($profile['bio'])): ?>
        <div class="glass-card mb-4">
            <h5><i class="fas fa-quote-left me-2" style="color:var(--pink)"></i>About</h5>
            <p class="text-muted"><?php echo nl2br(sanitize($profile['bio'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="glass-card mb-4">
            <h5 class="mb-3"><i class="fas fa-user me-2" style="color:var(--pink)"></i>Personal Details</h5>
            <div class="row g-3">
                <?php $fields = [['Gender',$profile['gender'],'venus-mars'],['Age',calculateAge($profile['dob']).' years','calendar'],['DOB',date('d M Y',strtotime($profile['dob'])),'birthday-cake'],['Height',$profile['height'],'ruler-vertical'],['Weight',$profile['weight'].' kg','weight-hanging'],['Complexion',$profile['complexion'],'palette'],['Marital Status',$profile['marital_status'],'ring'],['Village',$profile['village'],'home'],['City',$profile['city'],'city'],['State',$profile['state'],'map']];
                foreach ($fields as $f): if(!empty($f[1]) && $f[1] !== ' kg'): ?>
                <div class="col-md-4 col-6"><small class="text-muted d-block"><i class="fas fa-<?php echo $f[2]; ?> me-1"></i><?php echo $f[0]; ?></small><strong><?php echo sanitize($f[1]); ?></strong></div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <div class="glass-card mb-4">
            <h5 class="mb-3"><i class="fas fa-graduation-cap me-2" style="color:var(--pink)"></i>Education & Career</h5>
            <div class="row g-3">
                <?php $fields2 = [['Education',$profile['education']],['Occupation',$profile['occupation']],['Income',$profile['annual_income']]];
                foreach ($fields2 as $f): if(!empty($f[1])): ?>
                <div class="col-md-4"><small class="text-muted d-block"><?php echo $f[0]; ?></small><strong><?php echo sanitize($f[1]); ?></strong></div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <div class="glass-card mb-4">
            <h5 class="mb-3"><i class="fas fa-om me-2" style="color:var(--gold)"></i>Religious Details</h5>
            <div class="row g-3">
                <?php $fields3 = [['Religion',$profile['religion']],['Sub Caste',$profile['sub_caste']],['Gotra',$profile['gotra']],['Manglik',$profile['manglik']]];
                foreach ($fields3 as $f): if(!empty($f[1])): ?>
                <div class="col-md-3"><small class="text-muted d-block"><?php echo $f[0]; ?></small><strong><?php echo sanitize($f[1]); ?></strong></div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <div class="glass-card mb-4">
            <h5 class="mb-3"><i class="fas fa-users me-2" style="color:var(--pink)"></i>Family Details</h5>
            <div class="row g-3">
                <?php $fields4 = [['Family Type',$profile['family_type']],['Father',$profile['father_name']],['Father Occupation',$profile['father_occupation']],['Mother',$profile['mother_name']],['Mother Occupation',$profile['mother_occupation']],['Siblings',$profile['siblings']]];
                foreach ($fields4 as $f): if(!empty($f[1])): ?>
                <div class="col-md-4"><small class="text-muted d-block"><?php echo $f[0]; ?></small><strong><?php echo sanitize($f[1]); ?></strong></div>
                <?php endif; endforeach; ?>
            </div>
        </div>

        <?php if (!empty($profile['partner_preferences'])): ?>
        <div class="glass-card mb-4">
            <h5><i class="fas fa-heart me-2" style="color:var(--pink)"></i>Partner Preferences</h5>
            <p class="text-muted"><?php echo nl2br(sanitize($profile['partner_preferences'])); ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
</section>

<!-- Report Modal -->
<div class="modal fade" id="reportModal"><div class="modal-dialog"><div class="modal-content" style="border-radius:var(--radius)">
<div class="modal-header border-0"><h5 class="modal-title">Report Profile</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form id="reportForm" onsubmit="submitReport(event)"><div class="modal-body">
<input type="hidden" name="reported_id" value="<?php echo $profileId; ?>">
<select class="form-select mb-3" name="reason" id="reportReason" required><option value="">Select Reason</option><option value="fake_profile">Fake Profile</option><option value="inappropriate_photo">Inappropriate Photo</option><option value="harassment">Harassment</option><option value="spam">Spam</option><option value="other">Other</option></select>
<textarea class="form-control" name="description" id="reportDesc" rows="3" placeholder="Describe the issue..."></textarea>
</div><div class="modal-footer border-0"><button type="submit" class="btn btn-pink">Submit Report</button></div></form>
</div></div></div>

<script>
function sendInterest(id){
    fetch('api/send-interest.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'receiver_id='+id+'&csrf_token=<?php echo generateCSRFToken(); ?>'})
    .then(r=>r.json()).then(d=>{
        if(d.success){document.getElementById('interestBtn').outerHTML='<button class="btn btn-outline-pink" disabled><i class="fas fa-clock me-2"></i>Interest Pending</button>';showToast('Interest sent successfully!');}
        else showToast(d.error,'error');
    });
}
function submitReport(e){
    e.preventDefault();
    var reason=document.getElementById('reportReason').value;
    var desc=document.getElementById('reportDesc').value;
    if(!reason){showToast('Please select a reason','error');return;}
    fetch('api/report-profile.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'reported_id=<?php echo $profileId; ?>&reason='+encodeURIComponent(reason)+'&description='+encodeURIComponent(desc)+'&csrf_token=<?php echo generateCSRFToken(); ?>'})
    .then(r=>r.json()).then(d=>{
        if(d.success){showToast('Report submitted successfully!');bootstrap.Modal.getInstance(document.getElementById('reportModal')).hide();}
        else showToast(d.error||'Failed to submit report','error');
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
