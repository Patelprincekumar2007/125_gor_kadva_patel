    </main>
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="mb-3 d-flex align-items-center gap-2">
                        <div style="width:45px;height:45px;background:rgba(255,255,255,0.1);border-radius:50%;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.2)">
                            <i class="fas fa-om" style="color:var(--gold);font-size:1.5rem"></i>
                        </div>
                        <h5 class="mb-0" style="font-size:1.3rem;font-weight:700;color:var(--pink-soft)">125 Gor Samaj</h5>
                    </div>
                    <p style="font-size:0.9rem;line-height:1.8;opacity:0.85">A trusted matrimonial platform for the 125 Gor Kadva Patel Samaj community. We help families find the perfect match with trust, tradition, and modern technology.</p>
                    <div class="d-flex gap-3 mt-3">
                        <a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center"><i class="fab fa-whatsapp"></i></a>
                        <a href="#" style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.08);display:flex;align-items:center;justify-content:center"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled" style="font-size:0.9rem">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Home</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/search.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Search</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/about.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>About Us</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/register.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Register</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/contact.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Legal</h5>
                    <ul class="list-unstyled" style="font-size:0.9rem">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/privacy.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Privacy Policy</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/terms.php"><i class="fas fa-chevron-right me-2" style="font-size:0.7rem"></i>Terms & Conditions</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Contact Info</h5>
                    <ul class="list-unstyled" style="font-size:0.9rem">
                        <li class="mb-2"><i class="fas fa-map-marker-alt me-2" style="color:var(--pink-soft)"></i>Gujarat, India</li>
                        <li class="mb-2"><i class="fas fa-phone me-2" style="color:var(--pink-soft)"></i>+91 98765 43210</li>
                        <li class="mb-2"><i class="fas fa-envelope me-2" style="color:var(--pink-soft)"></i>info@125gorsamaj.com</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="d-flex flex-column align-items-center text-center gap-2">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> 125 Gor Kadva Patel Samaj Matrimony. All Rights Reserved.</p>
                    <p class="mb-0" style="font-size:0.8rem;opacity:0.8">Powered by <strong style="color:var(--pink-soft)">125 Gor Kadva Patel Samaj Matrimony</strong> | Made with <i class="fas fa-heart mx-1" style="color:var(--pink)"></i> in Gujarat</p>
                    <a href="<?php echo SITE_URL; ?>/admin/" style="opacity:0.3;font-size:0.7rem;color:inherit;text-decoration:none;margin-top:10px" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='0.3'"><i class="fas fa-shield-halved me-1"></i>Admin Access</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
    
    <!-- Security: Prevent back-button access after logout -->
    <script>
    (function(){
        // Check session on page visibility change (back button, tab switch)
        document.addEventListener('visibilitychange', function(){
            if(document.visibilityState === 'visible'){
                fetch('<?php echo SITE_URL; ?>/api/check-online.php', {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store'
                }).then(function(r){ return r.json(); })
                .then(function(data){
                    if(!data.logged_in){
                        window.location.replace('<?php echo SITE_URL; ?>/login.php');
                    }
                }).catch(function(){});
            }
        });
        // Also check on pageshow (bfcache)
        window.addEventListener('pageshow', function(e){
            if(e.persisted){
                fetch('<?php echo SITE_URL; ?>/api/check-online.php', {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store'
                }).then(function(r){ return r.json(); })
                .then(function(data){
                    if(!data.logged_in){
                        window.location.replace('<?php echo SITE_URL; ?>/login.php');
                    }
                }).catch(function(){});
            }
        });
    })();
    </script>

    <?php if (isset($extraJS)) echo $extraJS; ?>
    <?php
    $flash = getFlashMessage();
    if ($flash): ?>
    <script>showToast('<?php echo addslashes($flash["message"]); ?>', '<?php echo $flash["type"]; ?>');</script>
    <?php endif; ?>
</body>
</html>
