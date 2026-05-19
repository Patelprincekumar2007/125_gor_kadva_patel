    </div><!-- /admin-content -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Security: Prevent back-button access after admin logout -->
    <script>
    (function(){
        document.addEventListener('visibilitychange', function(){
            if(document.visibilityState === 'visible'){
                fetch('<?php echo SITE_URL; ?>/api/check-online.php', {
                    method: 'GET', credentials: 'same-origin', cache: 'no-store'
                }).then(function(r){ return r.json(); })
                .then(function(data){
                    if(!data.logged_in){
                        window.location.replace('<?php echo SITE_URL; ?>/admin/index.php');
                    }
                }).catch(function(){});
            }
        });
        window.addEventListener('pageshow', function(e){
            if(e.persisted){
                window.location.replace('<?php echo SITE_URL; ?>/admin/index.php');
            }
        });
    })();
    </script>
</body>
</html>
