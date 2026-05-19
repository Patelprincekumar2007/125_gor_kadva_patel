<?php
require_once __DIR__ . '/../includes/auth.php';
if (isAdminLoggedIn()) { header('Location: dashboard.php'); exit(); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $result = loginAdmin($pdo, sanitize($_POST['email']??''), $_POST['password']??'');
    if ($result['success']) { header('Location: dashboard.php'); exit(); }
    else $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | 125 Gor Kadva Patel Samaj</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>
<div class="admin-login-page">
    <div class="admin-login-card">
        <div class="login-icon"><i class="fas fa-shield-halved"></i></div>
        <h2>Admin Panel</h2>
        <p class="subtitle">125 Gor Kadva Patel Samaj Matrimony</p>
        <?php if ($error): ?>
        <div class="alert alert-danger" style="border-radius:10px;font-size:0.88rem;border:none;background:#ffebee;color:#c62828;">
            <i class="fas fa-exclamation-circle me-1"></i><?php echo $error; ?>
        </div>
        <?php endif; ?>
        <form method="POST">
            <?php echo csrfField(); ?>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="email" id="adminEmail" placeholder="Email" required>
                <label for="adminEmail">Admin Email</label>
            </div>
            <div class="form-floating mb-4">
                <input type="password" class="form-control" name="password" id="adminPass" placeholder="Password" required>
                <label for="adminPass">Password</label>
            </div>
            <button class="btn btn-login"><i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard</button>
        </form>
        <div class="back-link"><a href="<?php echo SITE_URL; ?>/">← Back to Website</a></div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
