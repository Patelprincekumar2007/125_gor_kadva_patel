<?php
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Edit Profile';
$errors = [];
$user = getCurrentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $fields = ['full_name','village','city','state','education','occupation','annual_income','marital_status','height','weight','complexion','religion','sub_caste','gotra','manglik','family_type','father_name','father_occupation','mother_name','mother_occupation','siblings','hobbies','bio','partner_preferences'];
    $data = [];
    foreach ($fields as $f) $data[$f] = sanitize($_POST[$f] ?? '');
    
    if (empty($data['full_name'])) $errors[] = 'Full name is required.';
    
    // Profile photo update
    if (!empty($_FILES['profile_photo']['name'])) {
        $upload = uploadFile($_FILES['profile_photo'], PROFILE_UPLOAD_PATH, 'profile_');
        if ($upload['success']) $data['profile_photo'] = $upload['filename'];
        else $errors[] = $upload['error'];
    }
    
    if (empty($errors)) {
        $setClauses = [];
        $params = [];
        foreach ($data as $k => $v) {
            $setClauses[] = "$k = ?";
            $params[] = $v;
        }
        $params[] = $user['id'];
        $sql = "UPDATE users SET " . implode(', ', $setClauses) . " WHERE id = ?";
        $pdo->prepare($sql)->execute($params);
        
        // Gallery uploads
        if (!empty($_FILES['gallery']['name'][0])) {
            foreach ($_FILES['gallery']['name'] as $i => $name) {
                if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                    $gFile = ['name'=>$name,'tmp_name'=>$_FILES['gallery']['tmp_name'][$i],'error'=>$_FILES['gallery']['error'][$i],'size'=>$_FILES['gallery']['size'][$i]];
                    $gUpload = uploadFile($gFile, GALLERY_UPLOAD_PATH, 'gallery_');
                    if ($gUpload['success']) $pdo->prepare("INSERT INTO user_gallery (user_id,image_path) VALUES (?,?)")->execute([$user['id'],$gUpload['filename']]);
                }
            }
        }
        setFlashMessage('success', 'Profile updated successfully!');
        header('Location: profile.php'); exit();
    }
}

// Get gallery
$gallery = $pdo->prepare("SELECT * FROM user_gallery WHERE user_id = ?"); $gallery->execute([$user['id']]); $photos = $gallery->fetchAll();

// Delete gallery image (POST with CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_photo']) && isset($_GET['token'])) {
    if (validateCSRFToken($_GET['token'])) {
        $photoId = (int)$_GET['delete_photo'];
        $pdo->prepare("DELETE FROM user_gallery WHERE id = ? AND user_id = ?")->execute([$photoId, $user['id']]);
    }
    header('Location: edit-profile.php'); exit();
}

require_once 'includes/header.php';
?>

<section class="py-5" style="background:var(--cream)">
<div class="container" style="max-width:800px">
    <h2 class="text-center mb-4" style="font-family:'Playfair Display',serif"><i class="fas fa-edit me-2" style="color:var(--pink)"></i>Edit Profile</h2>
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div><?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data" class="glass-card">
        <?php echo csrfField(); ?>
        
        <!-- Current Photo -->
        <div class="text-center mb-4">
            <img src="<?php echo getProfilePhoto($user['profile_photo']); ?>" id="profilePreview" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--pink-soft)">
            <div class="mt-2"><input type="file" class="form-control form-control-sm" name="profile_photo" accept="image/*" style="max-width:250px;margin:auto" onchange="previewImage(this,'#profilePreview')"></div>
        </div>

        <h5 class="mb-3" style="color:var(--pink)">Personal Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="full_name" value="<?php echo sanitize($user['full_name']); ?>" required><label>Full Name *</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="height" value="<?php echo sanitize($user['height']); ?>"><label>Height</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="weight" value="<?php echo sanitize($user['weight']); ?>"><label>Weight</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="complexion"><option value="">Select</option><?php foreach(['Fair','Wheatish','Medium','Dark'] as $c): ?><option <?php echo $user['complexion']===$c?'selected':''; ?>><?php echo $c; ?></option><?php endforeach; ?></select><label>Complexion</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="marital_status"><?php foreach(['Never Married','Divorced','Widowed','Awaiting Divorce'] as $m): ?><option <?php echo $user['marital_status']===$m?'selected':''; ?>><?php echo $m; ?></option><?php endforeach; ?></select><label>Marital Status</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="village" value="<?php echo sanitize($user['village']); ?>"><label>Village</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="city" value="<?php echo sanitize($user['city']); ?>"><label>City</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="state" value="<?php echo sanitize($user['state']); ?>"><label>State</label></div></div>
        </div>

        <h5 class="mb-3" style="color:var(--pink)">Education & Career</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="education" value="<?php echo sanitize($user['education']); ?>"><label>Education</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="occupation" value="<?php echo sanitize($user['occupation']); ?>"><label>Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="annual_income"><option value="">Select</option><?php foreach(['Below 2 Lakh','2-5 Lakh','5-10 Lakh','10-20 Lakh','20-50 Lakh','50 Lakh+'] as $i): ?><option <?php echo $user['annual_income']===$i?'selected':''; ?>><?php echo $i; ?></option><?php endforeach; ?></select><label>Annual Income</label></div></div>
        </div>

        <h5 class="mb-3" style="color:var(--pink)">Religious & Family</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="religion" value="<?php echo sanitize($user['religion']); ?>"><label>Religion</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="sub_caste" value="<?php echo sanitize($user['sub_caste']); ?>"><label>Sub Caste</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="gotra" value="<?php echo sanitize($user['gotra']); ?>"><label>Gotra</label></div></div>
            <div class="col-md-3"><div class="form-floating"><select class="form-select" name="manglik"><?php foreach(["Don't Know",'Yes','No','Partial'] as $mk): ?><option <?php echo $user['manglik']===$mk?'selected':''; ?>><?php echo $mk; ?></option><?php endforeach; ?></select><label>Manglik</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="family_type"><option value="">Select</option><option <?php echo $user['family_type']==='Joint'?'selected':''; ?>>Joint</option><option <?php echo $user['family_type']==='Nuclear'?'selected':''; ?>>Nuclear</option></select><label>Family Type</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="father_name" value="<?php echo sanitize($user['father_name']); ?>"><label>Father's Name</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="father_occupation" value="<?php echo sanitize($user['father_occupation']); ?>"><label>Father's Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="mother_name" value="<?php echo sanitize($user['mother_name']); ?>"><label>Mother's Name</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="mother_occupation" value="<?php echo sanitize($user['mother_occupation']); ?>"><label>Mother's Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="siblings" value="<?php echo sanitize($user['siblings']); ?>"><label>Siblings</label></div></div>
        </div>

        <h5 class="mb-3" style="color:var(--pink)">About & Preferences</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-12"><div class="form-floating"><input type="text" class="form-control" name="hobbies" value="<?php echo sanitize($user['hobbies']); ?>"><label>Hobbies</label></div></div>
            <div class="col-md-6"><div class="form-floating"><textarea class="form-control" name="bio" style="height:100px"><?php echo sanitize($user['bio']); ?></textarea><label>About Me</label></div></div>
            <div class="col-md-6"><div class="form-floating"><textarea class="form-control" name="partner_preferences" style="height:100px"><?php echo sanitize($user['partner_preferences']); ?></textarea><label>Partner Preferences</label></div></div>
        </div>

        <!-- Gallery Management -->
        <h5 class="mb-3" style="color:var(--pink)">Gallery</h5>
        <?php if (!empty($photos)): ?>
        <div class="row g-2 mb-3">
            <?php foreach ($photos as $ph): ?>
            <div class="col-3 col-md-2 position-relative">
                <img src="<?php echo SITE_URL.'/uploads/gallery/'.$ph['image_path']; ?>" style="width:100%;height:80px;object-fit:cover;border-radius:8px">
                <a href="?delete_photo=<?php echo $ph['id']; ?>&token=<?php echo generateCSRFToken(); ?>" class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:22px;height:22px;font-size:0.7rem" onclick="return confirm('Delete this photo?')"><i class="fas fa-times"></i></a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <input type="file" class="form-control mb-4" name="gallery[]" accept="image/*" multiple>

        <button type="submit" class="btn btn-pink btn-lg w-100"><i class="fas fa-save me-2"></i>Save Changes</button>
    </form>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
