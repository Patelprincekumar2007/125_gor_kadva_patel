<?php
$pageTitle = 'Register';
require_once 'includes/header.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request.';
    } else {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $dob = $_POST['dob'] ?? '';
        $village = sanitize($_POST['village'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $mobile = sanitize($_POST['mobile'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $education = sanitize($_POST['education'] ?? '');
        $occupation = sanitize($_POST['occupation'] ?? '');
        $annual_income = sanitize($_POST['annual_income'] ?? '');
        $marital_status = sanitize($_POST['marital_status'] ?? '');
        $height = sanitize($_POST['height'] ?? '');
        $weight = sanitize($_POST['weight'] ?? '');
        $complexion = sanitize($_POST['complexion'] ?? '');
        $religion = sanitize($_POST['religion'] ?? 'Hindu');
        $sub_caste = sanitize($_POST['sub_caste'] ?? '');
        $gotra = sanitize($_POST['gotra'] ?? '');
        $manglik = sanitize($_POST['manglik'] ?? '');
        $blood_group = sanitize($_POST['blood_group'] ?? '');
        $family_type = sanitize($_POST['family_type'] ?? '');
        $father_name = sanitize($_POST['father_name'] ?? '');
        $father_occupation = sanitize($_POST['father_occupation'] ?? '');
        $mother_name = sanitize($_POST['mother_name'] ?? '');
        $mother_occupation = sanitize($_POST['mother_occupation'] ?? '');
        $siblings = sanitize($_POST['siblings'] ?? '');
        $hobbies = sanitize($_POST['hobbies'] ?? '');
        $bio = sanitize($_POST['bio'] ?? '');
        $partner_preferences = sanitize($_POST['partner_preferences'] ?? '');

        // Validations
        if (empty($full_name)) $errors[] = 'Full name is required.';
        if (empty($gender)) $errors[] = 'Gender is required.';
        if (empty($dob)) $errors[] = 'Date of birth is required.';
        if (empty($mobile) || !preg_match('/^[6-9]\d{9}$/', $mobile)) $errors[] = 'Valid 10-digit mobile number is required.';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm_password) $errors[] = 'Passwords do not match.';

        // Check duplicate
        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
            $stmt->execute([$email, $mobile]);
            if ($stmt->fetch()) $errors[] = 'Email or mobile already registered.';
        }

        // Upload profile photo
        $profile_photo = null;
        if (!empty($_FILES['profile_photo']['name'])) {
            $upload = uploadFile($_FILES['profile_photo'], PROFILE_UPLOAD_PATH, 'profile_');
            if ($upload['success']) $profile_photo = $upload['filename'];
            else $errors[] = $upload['error'];
        }

        if (empty($errors)) {
            try {
                $age = calculateAge($dob);
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (full_name,gender,dob,age,village,city,state,mobile,email,password,education,occupation,annual_income,marital_status,height,weight,complexion,blood_group,religion,sub_caste,gotra,manglik,family_type,father_name,father_occupation,mother_name,mother_occupation,siblings,hobbies,bio,partner_preferences,profile_photo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$full_name,$gender,$dob,$age,$village,$city,$state,$mobile,$email,$hashed,$education,$occupation,$annual_income,$marital_status,$height,$weight,$complexion,$blood_group,$religion,$sub_caste,$gotra,$manglik,$family_type,$father_name,$father_occupation,$mother_name,$mother_occupation,$siblings,$hobbies,$bio,$partner_preferences,$profile_photo]);
                
                $userId = $pdo->lastInsertId();

                // Upload gallery images
                if (!empty($_FILES['gallery']['name'][0])) {
                    foreach ($_FILES['gallery']['name'] as $i => $name) {
                        if ($_FILES['gallery']['error'][$i] === UPLOAD_ERR_OK) {
                            $gFile = ['name'=>$name,'tmp_name'=>$_FILES['gallery']['tmp_name'][$i],'error'=>$_FILES['gallery']['error'][$i],'size'=>$_FILES['gallery']['size'][$i]];
                            $gUpload = uploadFile($gFile, GALLERY_UPLOAD_PATH, 'gallery_');
                            if ($gUpload['success']) {
                                $pdo->prepare("INSERT INTO user_gallery (user_id,image_path) VALUES (?,?)")->execute([$userId,$gUpload['filename']]);
                            }
                        }
                    }
                }

                // Send welcome email (non-blocking - don't crash registration if email fails)
                try {
                    $welcomeBody = "<h2>Welcome to 125 Gor Kadva Patel Samaj Matrimony!</h2><p>Dear {$full_name},</p><p>Thank you for registering. Your profile is under review and will be approved shortly.</p>";
                    sendEmail($email, "Welcome to " . SITE_NAME, $welcomeBody);
                } catch (Exception $emailErr) {
                    error_log("Registration email failed for {$email}: " . $emailErr->getMessage());
                }

                $success = true;
                setFlashMessage('success', 'Registration successful! Please login.');
            } catch (PDOException $dbErr) {
                error_log("Registration DB error: " . $dbErr->getMessage());
                $errors[] = 'Registration failed. Please try again.';
            } catch (Exception $genErr) {
                error_log("Registration error: " . $genErr->getMessage());
                $errors[] = 'An unexpected error occurred. Please try again.';
            }
        }
    }
}
?>

<section class="auth-container">
<div class="container py-5">
<?php if ($success): ?>
<div class="text-center py-5">
    <div class="glass-card" style="max-width:500px;margin:auto;padding:50px">
        <i class="fas fa-check-circle" style="font-size:4rem;color:#4caf50"></i>
        <h3 class="mt-3">Registration Successful!</h3>
        <p class="text-muted">Your profile has been submitted for review. You'll be notified once approved.</p>
        <a href="login.php" class="btn btn-pink mt-3">Login Now <i class="fas fa-arrow-right ms-2"></i></a>
    </div>
</div>
<?php else: ?>
<div style="max-width:800px;margin:auto">
    <div class="text-center mb-4">
        <h2 style="font-family:'Playfair Display',serif">Create Your Profile</h2>
        <p class="text-muted">Join the 125 Gor Kadva Patel Samaj Matrimony</p>
    </div>
    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo "<li>{$e}</li>"; ?></ul></div>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data" class="glass-card">
        <?php echo csrfField(); ?>
        
        <!-- Personal Details -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-user me-2"></i>Personal Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="full_name" id="full_name" placeholder="Full Name" required value="<?php echo $_POST['full_name'] ?? ''; ?>"><label for="full_name">Full Name *</label></div></div>
            <div class="col-md-6"><div class="form-floating"><select class="form-select" name="gender" id="gender" required><option value="">Select Gender</option><option value="Male">Male</option><option value="Female">Female</option></select><label for="gender">Gender *</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="date" class="form-control" name="dob" id="dob" placeholder="DOB" required onchange="calcAge(this, document.getElementById('ageDisplay'))"><label for="dob">Date of Birth *</label></div><small id="ageDisplay" class="text-muted ms-1"></small></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="marital_status" id="marital_status"><option value="Never Married">Never Married</option><option value="Divorced">Divorced</option><option value="Widowed">Widowed</option><option value="Awaiting Divorce">Awaiting Divorce</option></select><label for="marital_status">Marital Status</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="height" id="height" placeholder="Height" value="<?php echo $_POST['height'] ?? ''; ?>"><label for="height">Height (e.g. 5'8")</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="weight" id="weight" placeholder="Weight" value="<?php echo $_POST['weight'] ?? ''; ?>"><label for="weight">Weight (kg)</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="complexion" id="complexion"><option value="">Select</option><option value="Fair">Fair</option><option value="Wheatish">Wheatish</option><option value="Medium">Medium</option><option value="Dark">Dark</option></select><label for="complexion">Complexion</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="blood_group" id="blood_group"><option value="">Select</option><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>AB+</option><option>AB-</option><option>O+</option><option>O-</option></select><label for="blood_group">Blood Group</label></div></div>
        </div>

        <!-- Contact Details -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-phone me-2"></i>Contact Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="form-floating"><input type="tel" class="form-control" name="mobile" id="mobile" placeholder="Mobile" required pattern="[6-9]\d{9}" value="<?php echo $_POST['mobile'] ?? ''; ?>"><label for="mobile">Mobile Number *</label></div></div>
            <div class="col-md-6"><div class="form-floating"><input type="email" class="form-control" name="email" id="email" placeholder="Email" required value="<?php echo $_POST['email'] ?? ''; ?>"><label for="email">Email Address *</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="village" id="village" placeholder="Village" value="<?php echo $_POST['village'] ?? ''; ?>"><label for="village">Village</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="city" id="city" placeholder="City" value="<?php echo $_POST['city'] ?? ''; ?>"><label for="city">Current City</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="state" id="state" placeholder="State" value="<?php echo $_POST['state'] ?? 'Gujarat'; ?>"><label for="state">State</label></div></div>
        </div>

        <!-- Education & Career -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-graduation-cap me-2"></i>Education & Career</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="education" id="education" placeholder="Education" value="<?php echo $_POST['education'] ?? ''; ?>"><label for="education">Education</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="occupation" id="occupation" placeholder="Occupation" value="<?php echo $_POST['occupation'] ?? ''; ?>"><label for="occupation">Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="annual_income" id="annual_income"><option value="">Select</option><option>Below 2 Lakh</option><option>2-5 Lakh</option><option>5-10 Lakh</option><option>10-20 Lakh</option><option>20-50 Lakh</option><option>50 Lakh+</option></select><label for="annual_income">Annual Income</label></div></div>
        </div>

        <!-- Religious Details -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-om me-2"></i>Religious Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="religion" value="Hindu" readonly><label>Religion</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="sub_caste" id="sub_caste" placeholder="Sub Caste" value="<?php echo $_POST['sub_caste'] ?? ''; ?>"><label for="sub_caste">Sub Caste</label></div></div>
            <div class="col-md-3"><div class="form-floating"><input type="text" class="form-control" name="gotra" id="gotra" placeholder="Gotra" value="<?php echo $_POST['gotra'] ?? ''; ?>"><label for="gotra">Gotra</label></div></div>
            <div class="col-md-3"><div class="form-floating"><select class="form-select" name="manglik" id="manglik"><option value="Don't Know">Don't Know</option><option value="Yes">Yes</option><option value="No">No</option><option value="Partial">Partial</option></select><label for="manglik">Manglik</label></div></div>
        </div>

        <!-- Family Details -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-users me-2"></i>Family Details</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="form-floating"><select class="form-select" name="family_type" id="family_type"><option value="">Select</option><option value="Joint">Joint Family</option><option value="Nuclear">Nuclear Family</option></select><label for="family_type">Family Type</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="father_name" placeholder="Father's Name" value="<?php echo $_POST['father_name'] ?? ''; ?>"><label>Father's Name</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="father_occupation" placeholder="Father's Occupation" value="<?php echo $_POST['father_occupation'] ?? ''; ?>"><label>Father's Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="mother_name" placeholder="Mother's Name" value="<?php echo $_POST['mother_name'] ?? ''; ?>"><label>Mother's Name</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="mother_occupation" placeholder="Mother's Occupation" value="<?php echo $_POST['mother_occupation'] ?? ''; ?>"><label>Mother's Occupation</label></div></div>
            <div class="col-md-4"><div class="form-floating"><input type="text" class="form-control" name="siblings" placeholder="Siblings" value="<?php echo $_POST['siblings'] ?? ''; ?>"><label>Siblings (e.g. 1 Brother, 2 Sisters)</label></div></div>
        </div>

        <!-- About & Preferences -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-heart me-2"></i>About & Preferences</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="form-floating"><textarea class="form-control" name="bio" placeholder="Bio" style="height:100px"><?php echo $_POST['bio'] ?? ''; ?></textarea><label>About Me</label></div></div>
            <div class="col-md-6"><div class="form-floating"><textarea class="form-control" name="partner_preferences" placeholder="Partner Preferences" style="height:100px"><?php echo $_POST['partner_preferences'] ?? ''; ?></textarea><label>Partner Preferences</label></div></div>
            <div class="col-12"><div class="form-floating"><input type="text" class="form-control" name="hobbies" placeholder="Hobbies" value="<?php echo $_POST['hobbies'] ?? ''; ?>"><label>Hobbies (comma separated)</label></div></div>
        </div>

        <!-- Password -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-lock me-2"></i>Security</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6"><div class="form-floating"><input type="password" class="form-control" name="password" id="password" placeholder="Password" required minlength="8" oninput="updatePasswordMeter(this.value)"><label for="password">Password *</label></div><div class="password-strength" style="width:0;height:4px;border-radius:2px;margin-top:4px"></div></div>
            <div class="col-md-6"><div class="form-floating"><input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="Confirm" required><label for="confirm_password">Confirm Password *</label></div></div>
        </div>

        <!-- Photos -->
        <h5 class="mb-3" style="color:var(--pink)"><i class="fas fa-camera me-2"></i>Photos</h5>
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <label class="form-label fw-500">Profile Photo</label>
                <input type="file" class="form-control" name="profile_photo" accept="image/*" onchange="previewImage(this,'#profilePreview')">
                <img id="profilePreview" src="#" alt="" style="display:none;width:100px;height:100px;object-fit:cover;border-radius:50%;margin-top:10px;border:3px solid var(--pink-soft)">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-500">Gallery Images (up to 10)</label>
                <input type="file" class="form-control" name="gallery[]" accept="image/*" multiple>
            </div>
        </div>

        <button type="submit" class="btn btn-pink btn-lg w-100"><i class="fas fa-user-plus me-2"></i>Register Now</button>
        <p class="text-center mt-3 text-muted">Already have an account? <a href="login.php">Login here</a></p>
    </form>
</div>
<?php endif; ?>
</div>
</section>

<?php require_once 'includes/footer.php'; ?>
