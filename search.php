<?php
$pageTitle = 'Search Matches';
require_once 'includes/auth.php';
requireLogin();

// Handle POST search before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['search_filters'] = [
        'name' => trim($_POST['name'] ?? ''),
        'gender' => trim($_POST['gender'] ?? ''),
        'village' => trim($_POST['village'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
    ];
    header('Location: search.php?r=1');
    exit();
}

// Now include header (sends HTML)
require_once 'includes/header.php';
$user = getCurrentUser($pdo);

// Get filters only when coming from POST redirect
$filters = ['name'=>'','gender'=>'','village'=>'','city'=>''];
if (isset($_GET['r']) && isset($_SESSION['search_filters'])) {
    $filters = $_SESSION['search_filters'];
    unset($_SESSION['search_filters']);
}

$nameFilter = $filters['name'];
$genderFilter = $filters['gender'];
$villageFilter = $filters['village'];
$cityFilter = $filters['city'];
$hasFilter = !empty($nameFilter) || !empty($genderFilter) || !empty($villageFilter) || !empty($cityFilter);

$total = 0; $profiles = [];

if ($hasFilter) {
    $where = ["u.profile_status = 'active'"];
    $params = [];

    if (!empty($user['id'])) { $where[] = "u.id != ?"; $params[] = $user['id']; }
    if ($genderFilter === 'Male' || $genderFilter === 'Female') { $where[] = "u.gender = ?"; $params[] = $genderFilter; }
    if (!empty($nameFilter)) { $where[] = "u.full_name LIKE ?"; $params[] = '%'.$nameFilter.'%'; }
    if (!empty($villageFilter)) { $where[] = "u.village LIKE ?"; $params[] = '%'.$villageFilter.'%'; }
    if (!empty($cityFilter)) { $where[] = "u.city LIKE ?"; $params[] = '%'.$cityFilter.'%'; }

    $whereSQL = implode(' AND ', $where);
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u WHERE $whereSQL");
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    $params[] = 12; $params[] = 0;
    $stmt = $pdo->prepare("SELECT u.* FROM users u WHERE $whereSQL ORDER BY u.is_featured DESC, u.created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute($params);
    $profiles = $stmt->fetchAll();
}
?>

<section class="py-5" style="background:var(--cream);min-height:80vh">
<div class="container">
    <h2 class="text-center mb-4" style="font-family:'Playfair Display',serif">
        <i class="fas fa-search me-2" style="color:var(--pink)"></i>Search Matches
    </h2>

    <div class="glass-card mb-4" style="padding:24px 28px">
        <form method="POST" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted mb-1"><i class="fas fa-user me-1"></i>Name</label>
                <input type="text" class="form-control" name="name" placeholder="Search by name..." value="<?php echo sanitize($nameFilter); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-muted mb-1"><i class="fas fa-venus-mars me-1"></i>Gender</label>
                <select class="form-select" name="gender">
                    <option value="">All</option>
                    <option value="Male" <?php echo $genderFilter==='Male'?'selected':''; ?>>Male</option>
                    <option value="Female" <?php echo $genderFilter==='Female'?'selected':''; ?>>Female</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-muted mb-1"><i class="fas fa-home me-1"></i>Village</label>
                <input type="text" class="form-control" name="village" placeholder="Village name..." value="<?php echo sanitize($villageFilter); ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small text-muted mb-1"><i class="fas fa-city me-1"></i>City</label>
                <input type="text" class="form-control" name="city" placeholder="City name..." value="<?php echo sanitize($cityFilter); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-pink w-100" style="height:42px"><i class="fas fa-search me-1"></i>Search</button>
            </div>
            <div class="col-md-1">
                <a href="search.php" class="btn btn-outline-secondary w-100" style="height:42px;display:flex;align-items:center;justify-content:center">Clear</a>
            </div>
        </form>
    </div>

    <?php if ($hasFilter): ?>
    <p class="text-muted mb-3"><strong style="color:var(--pink)"><?php echo $total; ?></strong> profiles found</p>
    <?php endif; ?>

    <div class="row g-4">
        <?php if (!$hasFilter): ?>
        <div class="col-12">
            <div class="empty-state py-5">
                <i class="fas fa-users d-block mb-3" style="font-size:3rem;color:var(--pink-soft)"></i>
                <h5>Search for Profiles</h5>
                <p class="text-muted">Please enter name, gender, village, or city to search profiles</p>
            </div>
        </div>
        <?php elseif (empty($profiles)): ?>
        <div class="col-12">
            <div class="empty-state py-5">
                <i class="fas fa-search d-block mb-3" style="font-size:3rem;color:var(--pink-soft)"></i>
                <h5>No profiles found</h5>
                <p class="text-muted">Try adjusting your search filters</p>
            </div>
        </div>
        <?php else: foreach ($profiles as $p): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <div class="profile-card">
                <div style="overflow:hidden;position:relative">
                    <img src="<?php echo getProfilePhoto($p['profile_photo']); ?>" alt="<?php echo sanitize($p['full_name']); ?>" loading="lazy">
                    <?php if ($p['is_featured']): ?><span class="featured-badge position-absolute" style="top:10px;right:10px">Featured</span><?php endif; ?>
                    <?php if ($p['verification_status']==='verified'): ?><span class="position-absolute text-white" style="top:10px;left:10px;background:var(--success, #28a745);padding:4px 10px;border-radius:50px;font-size:0.65rem;font-weight:700;letter-spacing:0.5px;box-shadow:0 2px 10px rgba(40,167,69,0.25)"><i class="fas fa-user-shield me-1"></i>Admin Approved</span><?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="mb-1"><?php echo sanitize($p['full_name']); ?>, <?php echo calculateAge($p['dob']); ?></h5>
                    <p class="text-muted small mb-2">
                        <i class="fas fa-graduation-cap me-1"></i><?php echo sanitize($p['education'] ?: 'N/A'); ?><br>
                        <i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($p['city'] ?: $p['village'] ?: 'N/A'); ?>
                    </p>
                    <div class="d-flex gap-2">
                        <a href="profile.php?id=<?php echo $p['id']; ?>" class="btn btn-pink btn-sm flex-fill">View Profile</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; endif; ?>
    </div>
</div>
</section>
<?php require_once 'includes/footer.php'; ?>
