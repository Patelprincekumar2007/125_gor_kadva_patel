<?php

require_once __DIR__ . '/../../includes/auth.php';
// Prevent browser from caching admin pages (back button after logout)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');

$admin = getCurrentAdmin($pdo);
if (!$admin) { header('Location: index.php'); exit(); }
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - 125 Gor Kadva Patel Samaj</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/admin.css" rel="stylesheet">
</head>
<body>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-heart"></i></div>
        <h6>Admin Panel</h6>
        <small>125 Gor Kadva Patel Samaj</small>
    </div>
    <nav class="sidebar-nav">
        <a class="nav-link <?php echo $currentAdminPage==='dashboard'?'active':''; ?>" href="dashboard.php">
            <i class="fas fa-th-large"></i>Dashboard
        </a>
        <a class="nav-link <?php echo $currentAdminPage==='users'?'active':''; ?>" href="users.php">
            <i class="fas fa-users"></i>Users
        </a>
        <a class="nav-link <?php echo $currentAdminPage==='approvals'?'active':''; ?>" href="approvals.php">
            <i class="fas fa-user-check"></i>Approvals
        </a>
        <a class="nav-link <?php echo $currentAdminPage==='reports'?'active':''; ?>" href="reports.php">
            <i class="fas fa-flag"></i>Reports
        </a>
        <div class="nav-divider"></div>
        <a class="nav-link" href="<?php echo SITE_URL; ?>/" target="_blank">
            <i class="fas fa-external-link-alt"></i>View Site
        </a>
        <a class="nav-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i>Logout
        </a>
    </nav>
</aside>

<!-- Main Content -->
<div class="admin-content">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="mobile-toggle" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <h1 class="page-title mb-0"><?php
                $pageTitles = ['dashboard'=>'Dashboard','users'=>'User Management','approvals'=>'Profile Approvals','reports'=>'Reports'];
                echo $pageTitles[$currentAdminPage] ?? ucfirst($currentAdminPage);
            ?></h1>
        </div>
        <div class="admin-user">
            <div class="user-avatar"><i class="fas fa-user-shield"></i></div>
            <span><?php echo sanitize($admin['name']); ?></span>
        </div>
    </div>

<script>
function toggleSidebar() {
    document.getElementById('adminSidebar').classList.toggle('show');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}
</script>
