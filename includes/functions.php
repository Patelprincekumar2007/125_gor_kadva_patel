<?php
/**
 * Utility Functions
 * 125 Gor Kadva Patel Samaj Matrimony
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render CSRF hidden field
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}

/**
 * Sanitize input string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize email
 */
function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

/**
 * Calculate age from date of birth
 */
function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    return $today->diff($birthDate)->y;
}

/**
 * Format time ago
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

/**
 * Calculate profile completion percentage
 */
function profileCompletion($user) {
    $fields = ['full_name','gender','dob','village','city','mobile','email',
               'education','occupation','annual_income','marital_status','height',
               'weight','religion','sub_caste','family_type','father_name',
               'father_occupation','mother_name','mother_occupation','siblings',
               'hobbies','bio','partner_preferences','profile_photo'];
    $filled = 0;
    foreach ($fields as $field) {
        if (!empty($user[$field])) $filled++;
    }
    return round(($filled / count($fields)) * 100);
}

/**
 * Upload file with validation
 */
function uploadFile($file, $destination, $prefix = '') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error occurred.'];
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds 5MB limit.'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, JPEG, PNG, WEBP'];
    }
    // Verify it's actually an image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        return ['success' => false, 'error' => 'File is not a valid image.'];
    }
    // Generate unique filename
    $filename = $prefix . uniqid() . '_' . time() . '.' . $ext;
    $filepath = $destination . $filename;
    // Create directory if not exists
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Compress image
        compressImage($filepath, $filepath, 80);
        return ['success' => true, 'filename' => $filename];
    }
    return ['success' => false, 'error' => 'Failed to save file.'];
}

/**
 * Compress image
 */
function compressImage($source, $destination, $quality = 80) {
    if (!extension_loaded('gd')) {
        return false; // Gracefully bypass compression if GD library is not enabled
    }
    $info = getimagesize($source);
    if ($info === false) return false;
    
    switch ($info['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            imagejpeg($image, $destination, $quality);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            imagepng($image, $destination, round(9 * (100 - $quality) / 100));
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            imagewebp($image, $destination, $quality);
            break;
        default:
            return false;
    }
    if (isset($image)) imagedestroy($image);
    return true;
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

/**
 * Get recent notifications
 */
function getRecentNotifications($pdo, $userId, $limit = 10) {
    $stmt = $pdo->prepare("SELECT n.*, u.full_name as from_name, u.profile_photo as from_photo 
                           FROM notifications n 
                           LEFT JOIN users u ON n.from_user_id = u.id 
                           WHERE n.user_id = ? 
                           ORDER BY n.created_at DESC 
                           LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Create notification
 */
function createNotification($pdo, $userId, $fromUserId, $type, $title, $message = '', $link = '') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, from_user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$userId, $fromUserId, $type, $title, $message, $link]);
}

/**
 * Check if user is online (active in last 5 minutes)
 */
function isUserOnline($lastActive) {
    if (empty($lastActive)) return false;
    $last = new DateTime($lastActive);
    $now = new DateTime();
    $diff = $now->diff($last);
    return ($diff->days === 0 && $diff->h === 0 && $diff->i < 5);
}

/**
 * Get interest status between two users
 */
function getInterestStatus($pdo, $senderId, $receiverId) {
    $stmt = $pdo->prepare("SELECT * FROM interests WHERE 
                           (sender_id = ? AND receiver_id = ?) OR 
                           (sender_id = ? AND receiver_id = ?)");
    $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);
    return $stmt->fetch();
}

/**
 * Check if user is blocked
 */
function isBlocked($pdo, $blockerId, $blockedId) {
    $stmt = $pdo->prepare("SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->execute([$blockerId, $blockedId]);
    return $stmt->fetch() ? true : false;
}

/**
 * Format income display
 */
function formatIncome($income) {
    if (empty($income)) return 'Not specified';
    return $income;
}

/**
 * Get profile photo URL
 */
function getProfilePhoto($photo) {
    if (!empty($photo) && file_exists(PROFILE_UPLOAD_PATH . $photo)) {
        return SITE_URL . '/uploads/profiles/' . $photo;
    }
    return SITE_URL . '/assets/images/default-avatar.png';
}

/**
 * Flash message system
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Update user's last active timestamp
 */
function updateLastActive($pdo, $userId) {
    $stmt = $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
    $stmt->execute([$userId]);
}

/**
 * Send email using PHPMailer
 */
function sendEmail($to, $subject, $body) {
    require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your-email@gmail.com'; // Configure your email
        $mail->Password   = 'your-app-password';     // Configure app password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        $mail->setFrom('noreply@125gorsamaj.com', SITE_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Generate OTP
 */
function generateOTP() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Send OTP email
 */
function sendOTPEmail($to, $otp, $name) {
    $subject = "Your OTP - " . SITE_NAME;
    $body = "
    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;'>
        <div style='background: linear-gradient(135deg, #e75480, #ff69b4); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
            <h1 style='color: white; margin: 0;'>125 Gor Kadva Patel Samaj</h1>
            <p style='color: rgba(255,255,255,0.9); margin: 5px 0 0;'>Matrimony</p>
        </div>
        <div style='background: #fff; padding: 30px; border: 1px solid #eee; border-radius: 0 0 10px 10px;'>
            <p>Dear <strong>{$name}</strong>,</p>
            <p>Your One-Time Password (OTP) is:</p>
            <div style='background: #fff0f5; padding: 20px; text-align: center; border-radius: 10px; margin: 20px 0;'>
                <h2 style='color: #e75480; letter-spacing: 8px; font-size: 32px; margin: 0;'>{$otp}</h2>
            </div>
            <p>This OTP is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
            <p style='color: #999; font-size: 12px;'>If you did not request this, please ignore this email.</p>
        </div>
    </div>";
    return sendEmail($to, $subject, $body);
}
