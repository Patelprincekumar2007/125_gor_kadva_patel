<?php
$pageTitle = 'Login';
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit(); }
require_once 'includes/header.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid request.'; }
    else {
        $emailOrMobile = trim($_POST['email_mobile'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $result = loginUser($pdo, $emailOrMobile, $password, $remember);
        if ($result['success']) { header('Location: dashboard.php'); exit(); }
        else $error = $result['error'];
    }
}
?>
<section class="auth-container">
<div class="container">
<div class="row justify-content-center">
<div class="col-lg-5 col-md-7">
    <div class="auth-card mx-auto">
        <div class="text-center mb-4">
            <i class="fas fa-heart" style="font-size:2.5rem;color:var(--pink)"></i>
            <h2 class="mt-2">Welcome Back</h2>
            <p class="text-muted">Login to 125 Gor Kadva Patel Samaj Matrimony</p>
        </div>
        <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="email_mobile" id="email_mobile" placeholder="Email or Mobile" required>
                <label for="email_mobile"><i class="fas fa-user me-2"></i>Email or Mobile Number</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
            </div>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>
                <a href="forgot-password.php" style="font-size:0.9rem">Forgot Password?</a>
            </div>
            <button type="submit" class="btn btn-pink btn-lg w-100"><i class="fas fa-sign-in-alt me-2"></i>Login</button>
            <p class="text-center mt-3 text-muted">Don't have an account? <a href="register.php">Register Free</a></p>
        </form>
    </div>
</div>
</div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
