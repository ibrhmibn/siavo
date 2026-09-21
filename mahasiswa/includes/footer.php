                </div>
                </main>
                </div>
                </div>

                <!-- Theme toggle (circle wipe) -->
                <script src="<?php echo APP_URL; ?>assets/js/theme-toggle.js"></script>

                <!-- File upload component -->
                <script src="<?php echo APP_URL; ?>assets/js/file-upload.js"></script>

                <!-- Mahasiswa custom JS -->
                <script src="<?php echo APP_URL; ?>mahasiswa/assets/js/custom.js"></script>

                <script>
/* Sidebar mobile toggle */
(function() {
    var t = document.getElementById('sidebarToggle');
    var s = document.getElementById('mainSidebar');
    var o = document.getElementById('sidebarOverlay');
    if (!t || !s || !o) return;
    t.addEventListener('click', function(e) {
        e.preventDefault();
        s.classList.toggle('active');
        o.classList.toggle('is-open');
    });
    o.addEventListener('click', function() {
        s.classList.remove('active');
        o.classList.remove('is-open');
    });
})();

/* Auto-hide alerts */
document.querySelectorAll('.alert:not(.alert-permanent)').forEach(function(el) {
    setTimeout(function() {
        el.style.transition = 'opacity .3s';
        el.style.opacity = '0';
        setTimeout(function() {
            el.remove();
        }, 300);
    }, 5000);
});

/* Toggle icon theme: moon <-> sun */
(function() {
    var themeBtn = document.querySelector('[data-theme-toggle]');
    if (!themeBtn) return;
    var icon = themeBtn.querySelector('i');
    if (!icon) return;

    function updateIcon() {
        var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        if (isDark) {
            icon.className = 'fas fa-moon';
            themeBtn.setAttribute('aria-label', 'Mode gelap aktif');
        } else {
            icon.className = 'fas fa-sun';
            themeBtn.setAttribute('aria-label', 'Mode terang aktif');
        }
    }

    updateIcon();
    themeBtn.addEventListener('click', function() {
        setTimeout(updateIcon, 50);
    });

    var observer = new MutationObserver(updateIcon);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });
})();
                </script>

                </body>

                </html>

                <?php if (isset($conn)) { $conn->close(); } ?>