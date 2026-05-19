<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Security & cache headers
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: Sat, 01 Jan 2000 00:00:00 GMT');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

$currentUser = getCurrentUser($pdo);
$notifCount = $currentUser ? getUnreadNotificationCount($pdo, $currentUser['id']) : 0;
$notifications = $currentUser ? getRecentNotifications($pdo, $currentUser['id']) : [];
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="125 Gor Kadva Patel Samaj Matrimony - Find your perfect life partner within our trusted community.">
    <meta property="og:title" content="125 Gor Kadva Patel Samaj Matrimony">
    <meta property="og:description" content="Premium matrimonial platform for the 125 Gor Kadva Patel Samaj community.">
    <meta property="og:type" content="website">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?>125 Gor Kadva Patel Samaj Matrimony</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <?php if (isset($extraCSS)) echo $extraCSS; ?>
</head>
<body>
    <div class="toast-container"></div>
    
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/">
                <div style="width:38px;height:38px;background:rgba(216,93,130,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(216,93,130,0.15)">
                    <i class="fas fa-om" style="color:var(--gold);font-size:1.2rem"></i>
                </div>
                <div>
                    125 Gor Kadva Patel
                    <span>Matrimony</span>
                </div>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="fas fa-bars" style="color:var(--pink)"></i>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'search' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/search.php">Search Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'contact' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/contact.php">Contact</a>
                    </li>
                    <?php if ($currentUser): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'messages' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/messages.php">Chat</a>
                    </li>
                    <li class="nav-item ms-2">
                        <div class="notif-bell">
                            <i class="fas fa-bell" style="font-size:1.1rem;color:var(--dark)"></i>
                            <?php if ($notifCount > 0): ?>
                            <span class="notif-badge"><?php echo $notifCount; ?></span>
                            <?php endif; ?>
                            <div class="notif-dropdown">
                                <div style="padding:14px 16px;border-bottom:1px solid #eee;font-weight:600">Notifications</div>
                                <?php if (empty($notifications)): ?>
                                <div style="padding:20px;text-align:center;color:var(--gray)">No notifications yet</div>
                                <?php else: foreach ($notifications as $n): ?>
                                <a href="<?php echo $n['link'] ?: '#'; ?>" class="notif-item d-flex gap-3 <?php echo !$n['is_read'] ? 'unread' : ''; ?>" style="text-decoration:none;color:inherit">
                                    <div>
                                        <div style="font-size:0.85rem;font-weight:500"><?php echo sanitize($n['title']); ?></div>
                                        <div style="font-size:0.75rem;color:var(--gray)"><?php echo timeAgo($n['created_at']); ?></div>
                                    </div>
                                </a>
                                <?php endforeach; endif; ?>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                            <img src="<?php echo getProfilePhoto($currentUser['profile_photo']); ?>" alt="" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid var(--pink-soft)">
                            <span class="d-none d-lg-inline"><?php echo sanitize($currentUser['full_name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/edit-profile.php"><i class="fas fa-edit me-2"></i>Edit Profile</a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/interests.php"><i class="fas fa-heart me-2"></i>My Interests</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                   <?php else: ?>
                       <?php if (isAdminLoggedIn()): ?>
                           <li class="nav-item ms-1">
                               <a class="btn btn-outline-gold btn-sm" href="<?php echo SITE_URL; ?>/admin/dashboard.php">
                                   <i class="fas fa-shield-halved me-1"></i>
                                   Admin Dashboard
                               </a>
                           </li>
                           <li class="nav-item ms-1">
                               <a class="btn btn-danger btn-sm" style="border-radius:50px;padding:9px 28px;font-weight:600" href="<?php echo SITE_URL; ?>/admin/logout.php">
                                   <i class="fas fa-sign-out-alt me-1"></i>
                                   Logout
                               </a>
                           </li>
                       <?php else: ?>
                           <li class="nav-item ms-1">
                               <a class="btn btn-outline-pink btn-sm" href="<?php echo SITE_URL; ?>/login.php">
                                   <i class="fas fa-sign-in-alt me-1"></i>
                                   Login
                               </a>
                           </li>
                           <li class="nav-item ms-1">
                               <a class="btn btn-pink btn-sm" href="<?php echo SITE_URL; ?>/register.php">
                                   <i class="fas fa-user-plus me-1"></i>
                                   Register Free
                               </a>
                           </li>
                           <li class="nav-item ms-1">
                               <a class="btn btn-outline-gold btn-sm" href="<?php echo SITE_URL; ?>/admin/">
                                   <i class="fas fa-shield-halved me-1"></i>
                                   Admin
                               </a>
                           </li>
                       <?php endif; ?>
                   <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main>
