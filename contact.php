<?php $pageTitle = 'Contact Us'; require_once 'includes/header.php';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateCSRFToken($_POST['csrf_token'] ?? '')) {
    $name = sanitize($_POST['name']??''); $email = sanitizeEmail($_POST['email']??''); $phone = sanitize($_POST['phone']??''); $subject = sanitize($_POST['subject']??''); $message = sanitize($_POST['message']??'');
    if ($name && $email && $subject && $message) {
        $pdo->prepare("INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")->execute([$name,$email,$phone,$subject,$message]);
        $success = true;
    }
}
?>
<section class="py-5" style="background:linear-gradient(135deg,var(--pink-bg),#ffe8ef)">
<div class="container text-center py-3"><h1 style="font-family:'Playfair Display',serif">Contact Us</h1><p class="text-muted">We'd love to hear from you</p></div>
</section>
<section class="py-5"><div class="container"><div class="row g-5">
<div class="col-lg-7">
    <?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Message sent successfully!</div><?php endif; ?>
    <div class="glass-card"><h4 class="mb-4" style="color:var(--pink)">Send us a Message</h4>
    <form method="POST"><?php echo csrfField(); ?>
    <div class="row g-3">
        <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="name" placeholder="Name" required><label>Your Name *</label></div></div>
        <div class="col-md-6"><div class="form-floating"><input type="email" class="form-control" name="email" placeholder="Email" required><label>Email *</label></div></div>
        <div class="col-md-6"><div class="form-floating"><input type="tel" class="form-control" name="phone" placeholder="Phone"><label>Phone</label></div></div>
        <div class="col-md-6"><div class="form-floating"><input type="text" class="form-control" name="subject" placeholder="Subject" required><label>Subject *</label></div></div>
        <div class="col-12"><div class="form-floating"><textarea class="form-control" name="message" placeholder="Message" style="height:120px" required></textarea><label>Message *</label></div></div>
        <div class="col-12"><button class="btn btn-pink btn-lg"><i class="fas fa-paper-plane me-2"></i>Send Message</button></div>
    </div></form></div>
</div>
<div class="col-lg-5">
    <div class="glass-card mb-4"><h5 style="color:var(--pink)"><i class="fas fa-map-marker-alt me-2"></i>Address</h5><p class="text-muted">Gujarat, India</p></div>
    <div class="glass-card mb-4"><h5 style="color:var(--pink)"><i class="fas fa-phone me-2"></i>Phone</h5><p class="text-muted">+91 98765 43210</p></div>
    <div class="glass-card mb-4"><h5 style="color:var(--pink)"><i class="fas fa-envelope me-2"></i>Email</h5><p class="text-muted">info@125gorsamaj.com</p></div>
    <div class="glass-card"><h5 style="color:var(--pink)"><i class="fas fa-clock me-2"></i>Hours</h5><p class="text-muted">Mon - Sat: 9 AM - 6 PM</p></div>
</div>
</div></div></section>
<?php require_once 'includes/footer.php'; ?>
