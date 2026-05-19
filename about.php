<?php $pageTitle = 'About Us'; require_once 'includes/header.php'; ?>
<section class="py-5" style="background:linear-gradient(135deg,var(--pink-bg),#ffe8ef)">
<div class="container text-center py-4">
    <h1 style="font-family:'Playfair Display',serif;font-size:2.8rem">About <span style="color:var(--pink)">125 Gor Kadva Patel Samaj</span></h1>
    <p class="text-muted mt-3" style="max-width:700px;margin:auto;line-height:1.9">Our community has a rich heritage rooted in values, culture, and strong family bonds. The 125 Gor Kadva Patel Samaj represents a proud and closely-knit network of families across Gujarat and beyond.</p>
</div>
</section>
<section class="py-5"><div class="container"><div class="row g-5 align-items-center">
<div class="col-lg-6 reveal"><div class="glass-card"><h3 style="color:var(--pink)"><i class="fas fa-om me-2"></i>Our Heritage</h3><p class="text-muted" style="line-height:1.9">The 125 Gor Kadva Patel Samaj has been a pillar of cultural preservation and community welfare for generations. Our community is known for its strong work ethic, family values, and commitment to education and progress.</p><p class="text-muted" style="line-height:1.9">With roots deeply embedded in agricultural traditions and entrepreneurial spirit, our community members have excelled across various fields while maintaining their cultural identity.</p></div></div>
<div class="col-lg-6 reveal"><div class="glass-card"><h3 style="color:var(--pink)"><i class="fas fa-heart me-2"></i>Our Mission</h3><p class="text-muted" style="line-height:1.9">This matrimonial platform is dedicated to helping families within the 125 Gor Kadva Patel Samaj find compatible life partners. We believe in:</p><ul class="text-muted" style="line-height:2"><li>Preserving our cultural values through meaningful matches</li><li>Providing a safe, verified, and trusted platform</li><li>Connecting families across villages, cities, and countries</li><li>Maintaining complete privacy and data security</li><li>Offering a free service for all community members</li></ul></div></div>
</div></div></section>
<section class="py-5" style="background:var(--pink-bg)"><div class="container text-center">
<h2 class="section-title reveal">Our Values</h2>
<div class="row g-4 mt-4">
<?php $vals = [['fa-praying-hands','Tradition','We honor our cultural traditions and help families find matches that align with our community values.'],['fa-shield-halved','Trust & Safety','Every profile is manually verified. We ensure a safe environment for all members.'],['fa-users','Family First','We believe marriage is a bond between families. Our platform facilitates family-approved connections.'],['fa-lock','Privacy','Your personal information is secure. We never share data with third parties.']];
foreach($vals as $v): ?>
<div class="col-md-3 col-sm-6 reveal"><div class="glass-card h-100 text-center"><i class="fas <?php echo $v[0]; ?>" style="font-size:2.5rem;color:var(--pink);margin-bottom:15px"></i><h5><?php echo $v[1]; ?></h5><p class="text-muted small"><?php echo $v[2]; ?></p></div></div>
<?php endforeach; ?>
</div></div></section>
<?php require_once 'includes/footer.php'; ?>
