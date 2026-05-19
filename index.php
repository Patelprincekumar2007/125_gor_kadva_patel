<?php
$pageTitle = 'Home';
require_once 'includes/header.php';

// Get featured profiles
$stmt = $pdo->prepare("SELECT * FROM users WHERE profile_status='active' AND verification_status='verified' AND is_featured=1 AND profile_photo IS NOT NULL LIMIT 8");
$stmt->execute();
$featured = $stmt->fetchAll();

// Get stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE profile_status='active'")->fetchColumn();
$totalMatches = $pdo->query("SELECT COUNT(*) FROM interests WHERE status='accepted'")->fetchColumn();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hearts-container"></div>
    <div class="container position-relative" style="z-index:2">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="mb-3">
                    <span class="badge-pink" style="padding:8px 20px;font-size:0.85rem;border-radius:50px;background:rgba(231,84,128,0.1);color:var(--pink)">
                        <i class="fas fa-heart me-1"></i> Trusted Community Matrimony
                    </span>
                </div>
                <h1 class="hero-title mb-3">
                    Find Your Perfect<br>Life Partner Within<br>
                    <span class="highlight" style="font-size:1.15em;letter-spacing:1px">125 Gor Kadva Patel Samaj</span>
                </h1>
                <p class="hero-subtitle mb-4">Where tradition meets trust. Join thousands of families from our beloved community who found their perfect match through our platform.</p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="register.php" class="btn btn-pink btn-lg px-4"><i class="fas fa-user-plus me-2"></i>Register Now</a>
                    <a href="search.php" class="btn btn-outline-pink btn-lg px-4"><i class="fas fa-search me-2"></i>Find Match</a>
                </div>
                <div class="d-flex gap-4 mt-4 pt-2">
                    <div><strong style="font-size:1.4rem;color:var(--pink)"><?php echo number_format($totalUsers); ?>+</strong><br><small class="text-muted">Active Members</small></div>
                    <div style="border-left:2px solid #eee"></div>
                    <div><strong style="font-size:1.4rem;color:var(--pink)"><?php echo number_format($totalMatches); ?>+</strong><br><small class="text-muted">Successful Matches</small></div>
                    <div style="border-left:2px solid #eee"></div>
                    <div><strong style="font-size:1.4rem;color:var(--gold)">100%</strong><br><small class="text-muted">Safe & Verified</small></div>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-block text-center">
                <div style="position:relative">
                    <div style="width:350px;height:350px;border-radius:50%;background:linear-gradient(135deg,rgba(231,84,128,0.12),rgba(255,105,180,0.08));margin:auto;display:flex;align-items:center;justify-content:center">
                        <i class="fas fa-heart" style="font-size:8rem;color:rgba(231,84,128,0.15)"></i>
                    </div>
                    <div style="position:absolute;top:20px;right:30px;background:var(--white);padding:12px 18px;border-radius:12px;box-shadow:var(--shadow);animation:float 4s ease-in-out infinite">
                        <i class="fas fa-shield-halved text-success me-2"></i><small class="fw-bold">Verified Profiles</small>
                    </div>
                    <div style="position:absolute;bottom:30px;left:10px;background:var(--white);padding:12px 18px;border-radius:12px;box-shadow:var(--shadow);animation:float 5s ease-in-out infinite reverse">
                        <i class="fas fa-lock text-warning me-2"></i><small class="fw-bold">100% Privacy</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats-section">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="stat-number counter" data-target="5000">0</div>
                <div class="stat-label">Registered Members</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number counter" data-target="1200">0</div>
                <div class="stat-label">Happy Marriages</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number counter" data-target="125">0</div>
                <div class="stat-label">Villages Connected</div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-number counter" data-target="15">0</div>
                <div class="stat-label">Years of Trust</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="py-5" style="background:var(--white)">
    <div class="container py-4 text-center">
        <h2 class="section-title reveal">How It Works</h2>
        <p class="section-subtitle reveal">Finding your soulmate is just 3 simple steps away</p>
        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="step-card glass-card h-100">
                    <span class="step-number">1</span>
                    <div class="step-icon"><i class="fas fa-user-plus"></i></div>
                    <h5 class="mt-3">Create Profile</h5>
                    <p class="text-muted small">Register and build your detailed matrimonial profile with photos and preferences.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="step-card glass-card h-100">
                    <span class="step-number">2</span>
                    <div class="step-icon"><i class="fas fa-search-plus"></i></div>
                    <h5 class="mt-3">Search & Connect</h5>
                    <p class="text-muted small">Browse verified profiles, use advanced filters, and send interest to your preferred match.</p>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="step-card glass-card h-100">
                    <span class="step-number">3</span>
                    <div class="step-icon"><i class="fas fa-heart"></i></div>
                    <h5 class="mt-3">Get Matched</h5>
                    <p class="text-muted small">Once both sides accept, start private conversations and plan your future together.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Profiles -->
<?php if (!empty($featured)): ?>
<section class="py-5" style="background:var(--pink-bg)">
    <div class="container py-4 text-center">
        <h2 class="section-title reveal">Featured Profiles</h2>
        <p class="section-subtitle reveal">Handpicked profiles from our community</p>
        <div class="row g-4">
            <?php foreach ($featured as $p): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 reveal">
                <div class="profile-card">
                    <div style="overflow:hidden;position:relative">
                        <img src="<?php echo getProfilePhoto($p['profile_photo']); ?>" alt="<?php echo sanitize($p['full_name']); ?>" loading="lazy">
                        <?php if ($p['verification_status']==='verified'): ?><span class="position-absolute text-white" style="top:10px;left:10px;background:var(--success, #28a745);padding:4px 10px;border-radius:50px;font-size:0.65rem;font-weight:700;letter-spacing:0.5px;box-shadow:0 2px 10px rgba(40,167,69,0.25)"><i class="fas fa-user-shield me-1"></i>Admin Approved</span><?php endif; ?>
                    </div>
                    <div class="card-body">
                        <h5><?php echo sanitize($p['full_name']); ?>, <?php echo calculateAge($p['dob']); ?></h5>
                        <p class="text-muted small mb-2">
                            <i class="fas fa-graduation-cap me-1"></i><?php echo sanitize($p['education'] ?: 'N/A'); ?><br>
                            <i class="fas fa-map-marker-alt me-1"></i><?php echo sanitize($p['city'] ?: $p['village'] ?: 'N/A'); ?>
                        </p>
                        <a href="profile.php?id=<?php echo $p['id']; ?>" class="btn btn-pink btn-sm w-100">View Profile</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Testimonials -->
<section class="py-5" style="background:var(--white)">
    <div class="container py-4 text-center">
        <h2 class="section-title reveal">Success Stories</h2>
        <p class="section-subtitle reveal">Real stories of love from our community</p>
        <div class="row g-4">
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <p class="text-muted mt-4" style="font-size:0.92rem;line-height:1.8">"We found each other through this platform. Being from Kadi and Visnagar respectively, finding a match that aligns with our family traditions and Gor values was our top priority. Jai Umiya Maa! The process was incredibly smooth, secure, and our families are overjoyed. Thank you so much!"</p>
                    <div class="mt-3 pt-2 border-top">
                        <strong>Sneha & Kinjal Patel</strong><br>
                        <small class="text-muted">Visnagar - Kadi, Married 2025</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <p class="text-muted mt-4" style="font-size:0.92rem;line-height:1.8">"Being part of the 125 Gor Kadva Patel community, finding a life partner who understands our traditional roots while embracing modern prospects was important. This platform was a blessing. Real, verified profiles and safe family-approved interaction. Strongly recommended!"</p>
                    <div class="mt-3 pt-2 border-top">
                        <strong>Hardik & Vimal Patel</strong><br>
                        <small class="text-muted">Bhandu - Unjha, Married 2024</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4 reveal">
                <div class="testimonial-card h-100">
                    <p class="text-muted mt-4" style="font-size:0.92rem;line-height:1.8">"We are highly impressed with the verification standard. Every profile is meticulously reviewed by the admin panel, giving us ultimate peace of mind. We connected with the perfect family from Mehsana. Jai Shree Krishna to the organizers!"</p>
                    <div class="mt-3 pt-2 border-top">
                        <strong>Patel Family (Parents of Snehal)</strong><br>
                        <small class="text-muted">Mehsana, Matched 2025</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Section -->
<section class="py-5" style="background:linear-gradient(135deg,var(--pink-bg),#ffe8ef)">
    <div class="container py-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 reveal">
                <span class="badge-pink mb-2" style="padding:6px 16px;font-size:0.8rem;border-radius:50px;background:rgba(216,93,130,0.1);color:var(--pink);display:inline-block">
                    <i class="fas fa-om me-1"></i> Jai Umiya Maa
                </span>
                <h2 class="section-title" style="text-align:left">About Our Community</h2>
                <p class="text-muted" style="line-height:1.9;font-size:0.95rem">The <strong>125 Gor Kadva Patel Samaj</strong> is a respected and closely-knit community with deep roots in Gujarat, India. Our community values family bonds, cultural traditions, and mutual respect.</p>
                <p class="text-muted" style="line-height:1.9;font-size:0.95rem">This matrimonial platform is built exclusively for our community members to find compatible life partners while preserving our rich heritage and values.</p>
                <a href="about.php" class="btn btn-pink mt-2">Learn More <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
            <div class="col-lg-6 reveal text-center">
                <div style="background:var(--white);border-radius:20px;padding:40px;box-shadow:var(--shadow)">
                    <i class="fas fa-om" style="font-size:3rem;color:var(--gold);margin-bottom:15px"></i>
                    <h4 style="font-family:'Playfair Display',serif">Our Core Strengths</h4>
                    <div class="row g-3 mt-3 text-start">
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>Family First</small></div></div>
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>Cultural Pride</small></div></div>
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>Trust & Safety</small></div></div>
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>100% Privacy</small></div></div>
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>Verified Profiles</small></div></div>
                        <div class="col-6"><div class="d-flex gap-2 align-items-center"><i class="fas fa-check-circle text-success"></i><small>Free Service</small></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Community Heritage & Gallery -->
<section class="py-5" style="background:var(--cream)">
    <div class="container py-4 text-center">
        <h2 class="section-title reveal">Samaj Heritage & Events</h2>
        <p class="section-subtitle reveal">Glance at our vibrant community gatherings, events, and cultural preservation</p>
        <div class="row g-4 justify-content-center">
            <div class="col-lg-4 col-md-6 reveal">
                <div class="glass-card p-0 overflow-hidden h-100" style="border-radius:16px;box-shadow:var(--shadow);background:var(--white)">
                    <div style="height:220px;position:relative;overflow:hidden">
                        <img src="<?php echo SITE_URL; ?>/assets/images/unjha-temple.png" alt="Unjha Umiya Dham" style="width:100%;height:100%;object-fit:cover;transition:var(--transition)">
                        <div style="position:absolute;top:0;left:0;width:100%;height:100%;background:linear-gradient(to bottom, transparent, rgba(0,0,0,0.4))"></div>
                        <span class="position-absolute text-white" style="bottom:15px;right:15px;background:rgba(0,0,0,0.6);padding:4px 12px;border-radius:50px;font-size:0.75rem"><i class="fas fa-camera me-1"></i>Unjha Umiya Dham</span>
                    </div>
                    <div class="p-4 text-start">
                        <h5 class="fw-bold mb-2">Umiya Mataji Temple</h5>
                        <p class="text-muted small mb-0" style="line-height:1.7">The spiritual and cultural center of the Kadva Patel community. Our platform operates with blessings from Umiya Mataji, preserving family sanctity and traditional values.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="glass-card p-0 overflow-hidden h-100" style="border-radius:16px;box-shadow:var(--shadow);background:var(--white)">
                    <div style="height:220px;background:linear-gradient(135deg,rgba(216,93,130,0.15),rgba(212,168,67,0.08));display:flex;align-items:center;justify-content:center;position:relative">
                        <i class="fas fa-users-viewfinder" style="font-size:4.5rem;color:var(--pink)"></i>
                        <span class="position-absolute text-white" style="bottom:15px;right:15px;background:rgba(0,0,0,0.6);padding:4px 12px;border-radius:50px;font-size:0.75rem"><i class="fas fa-camera me-1"></i>Samaj Sneha Milan</span>
                    </div>
                    <div class="p-4 text-start">
                        <h5 class="fw-bold mb-2">Annual Community Meetups</h5>
                        <p class="text-muted small mb-0" style="line-height:1.7">Bridging the gap between 125 villages. Our physical and digital meets bring together elders and youth to cherish cultural connections and mutual respect.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 reveal">
                <div class="glass-card p-0 overflow-hidden h-100" style="border-radius:16px;box-shadow:var(--shadow);background:var(--white)">
                    <div style="height:220px;background:linear-gradient(135deg,rgba(216,93,130,0.08),rgba(212,168,67,0.15));display:flex;align-items:center;justify-content:center;position:relative">
                        <i class="fas fa-gift" style="font-size:4.5rem;color:var(--gold)"></i>
                        <span class="position-absolute text-white" style="bottom:15px;right:15px;background:rgba(0,0,0,0.6);padding:4px 12px;border-radius:50px;font-size:0.75rem"><i class="fas fa-camera me-1"></i>Sanskari Shadi</span>
                    </div>
                    <div class="p-4 text-start">
                        <h5 class="fw-bold mb-2">Traditional Sanskar Weddings</h5>
                        <p class="text-muted small mb-0" style="line-height:1.7">We honor our heritage with complete transparency, verified gotras, and village reference checks, guiding you beautifully toward a sacred lifetime union.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-5" style="background:var(--white)">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title reveal">Frequently Asked Questions</h2>
            <p class="section-subtitle reveal">Everything you need to know</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8 reveal">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius);box-shadow:var(--shadow)">
                        <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" style="border-radius:var(--radius);font-weight:500">Is registration free?</button></h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Yes! Registration is completely free for all members of the 125 Gor Kadva Patel Samaj community.</div></div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius);box-shadow:var(--shadow)">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" style="border-radius:var(--radius);font-weight:500">How are profiles verified?</button></h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Our admin team manually reviews each profile. We verify identity through Aadhaar and community references.</div></div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius);box-shadow:var(--shadow)">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" style="border-radius:var(--radius);font-weight:500">Is my personal information safe?</button></h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Absolutely. We use encrypted connections and never share your data with third parties. Your privacy is our top priority.</div></div>
                    </div>
                    <div class="accordion-item border-0 mb-3" style="border-radius:var(--radius);box-shadow:var(--shadow)">
                        <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" style="border-radius:var(--radius);font-weight:500">Can I chat with matches?</button></h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted">Yes, once both parties accept the interest request, a private chat feature becomes available for secure communication.</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-5" style="background:linear-gradient(135deg,var(--pink),var(--rose))">
    <div class="container py-4 text-center text-white">
        <h2 style="font-family:'Playfair Display',serif;font-size:2.2rem" class="reveal">Ready to Find Your Life Partner?</h2>
        <p class="mt-3 mb-4 reveal" style="opacity:0.9;max-width:500px;margin:auto">Join thousands of families from our community. Your perfect match is just a click away.</p>
        <a href="register.php" class="btn btn-lg px-5 py-3 reveal" style="background:var(--white);color:var(--pink);border-radius:50px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.15)">
            <i class="fas fa-heart me-2"></i>Register Free Today
        </a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
