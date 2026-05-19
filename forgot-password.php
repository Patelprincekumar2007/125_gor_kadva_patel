<?php
$pageTitle = 'Forgot Password';
require_once 'includes/header.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }

// Determine current step from session (secure server-side flow)
$step = $_SESSION['reset_step'] ?? 'email';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $postStep = $_POST['step'] ?? '';
    
    if ($postStep === 'email') {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE email = ? AND profile_status != 'deleted'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user) {
            $otp = generateOTP();
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $pdo->prepare("UPDATE users SET otp_code=?, otp_expiry=? WHERE id=?")->execute([$otp, $expiry, $user['id']]);
            sendOTPEmail($email, $otp, $user['full_name']);
            // Store in session (NOT hidden fields)
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_step'] = 'otp';
            $step = 'otp';
            $success = 'OTP sent to your email.';
        } else {
            $error = 'No account found with this email.';
        }
    } elseif ($postStep === 'otp' && $step === 'otp') {
        // Only allow OTP step if session confirms we're at OTP step
        $email = $_SESSION['reset_email'] ?? '';
        $otp = sanitize($_POST['otp'] ?? '');
        if (empty($email)) { $error = 'Session expired. Please start over.'; $step = 'email'; $_SESSION['reset_step'] = 'email'; }
        else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email=? AND otp_code=? AND otp_expiry > NOW()");
            $stmt->execute([$email, $otp]);
            if ($stmt->fetch()) {
                // Invalidate OTP after successful verification
                $pdo->prepare("UPDATE users SET otp_code=NULL, otp_expiry=NULL WHERE email=?")->execute([$email]);
                $_SESSION['reset_step'] = 'reset';
                $_SESSION['reset_verified'] = true;
                $step = 'reset';
                $success = 'OTP verified. Set new password.';
            } else {
                $error = 'Invalid or expired OTP.';
                $step = 'otp';
            }
        }
    } elseif ($postStep === 'reset' && $step === 'reset' && !empty($_SESSION['reset_verified'])) {
        // Only allow reset if OTP was verified in session
        $email = $_SESSION['reset_email'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (empty($email)) { $error = 'Session expired. Please start over.'; $step = 'email'; }
        elseif (strlen($password) < 8) $error = 'Password must be at least 8 characters.';
        elseif ($password !== $confirm) $error = 'Passwords do not match.';
        else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=?, otp_code=NULL, otp_expiry=NULL WHERE email=?")->execute([$hashed, $email]);
            // Clear all reset session data
            unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified']);
            setFlashMessage('success', 'Password reset successful. Please login.');
            header('Location: login.php'); exit();
        }
    } else {
        // Invalid step transition attempt - potential tampering
        $error = 'Invalid request. Please start over.';
        $step = 'email';
        unset($_SESSION['reset_email'], $_SESSION['reset_step'], $_SESSION['reset_verified']);
    }
}
?>
<section class="auth-container">
<div class="container"><div class="row justify-content-center"><div class="col-lg-5 col-md-7">
<div class="auth-card mx-auto">
    <div class="text-center mb-4">
        <i class="fas fa-key" style="font-size:2.5rem;color:var(--pink)"></i>
        <h2 class="mt-2">Reset Password</h2>
        <p class="text-muted">We'll send an OTP to your email</p>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?php echo sanitize($error); ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?php echo sanitize($success); ?></div><?php endif; ?>
    
    <?php if ($step === 'email'): ?>
    <form method="POST"><?php echo csrfField(); ?><input type="hidden" name="step" value="email">
        <div class="form-floating mb-3"><input type="email" class="form-control" name="email" placeholder="Email" required><label>Email Address</label></div>
        <button class="btn btn-pink btn-lg w-100">Send OTP</button>
    </form>
    <?php elseif ($step === 'otp'): ?>
    <form method="POST"><?php echo csrfField(); ?><input type="hidden" name="step" value="otp">
        <p class="text-muted small text-center mb-3">OTP sent to <strong><?php echo sanitize($_SESSION['reset_email'] ?? ''); ?></strong></p>
        <div class="form-floating mb-3"><input type="text" class="form-control" name="otp" placeholder="OTP" required maxlength="6" pattern="\d{6}" autocomplete="one-time-code"><label>Enter 6-digit OTP</label></div>
        <button class="btn btn-pink btn-lg w-100">Verify OTP</button>
    </form>
    <?php elseif ($step === 'reset'): ?>
    <form method="POST"><?php echo csrfField(); ?><input type="hidden" name="step" value="reset">
        <div class="form-floating mb-3"><input type="password" class="form-control" name="password" placeholder="New Password" required minlength="8"><label>New Password</label></div>
        <div class="form-floating mb-3"><input type="password" class="form-control" name="confirm_password" placeholder="Confirm" required><label>Confirm Password</label></div>
        <button class="btn btn-pink btn-lg w-100">Reset Password</button>
    </form>
    <?php endif; ?>
    <p class="text-center mt-3"><a href="login.php"><i class="fas fa-arrow-left me-1"></i>Back to Login</a></p>
</div>
</div></div></div>
</section>
<?php require_once 'includes/footer.php'; ?>
