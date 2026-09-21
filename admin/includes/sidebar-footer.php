</div> <!-- /.container-fluid -->
</main>

<footer class="admin-foot">
    <span>&copy; <?php echo date('Y'); ?> Lenathyodev · Admin Panel</span>
    <span>Dibuat untuk mahasiswa.</span>
</footer>
</div> <!-- /.admin-main -->
</div> <!-- /.admin-layout -->

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Theme toggle (circle wipe) -->
<script src="<?php echo APP_URL; ?>assets/js/theme-toggle.js"></script>

<!-- File upload component -->
<script src="<?php echo APP_URL; ?>assets/js/file-upload.js"></script>s"></script>

<script>
/* Sidebar toggle mobile */
(function() {
    var t = document.getElementById('sideToggle');
    var s = document.getElementById('adminSide');
    var o = document.getElementById('sideOverlay');
    if (!t || !s || !o) return;
    t.addEventListener('click', function() {
        s.classList.toggle('is-open');
        o.classList.toggle('is-open');
    });
    o.addEventListener('click', function() {
        s.classList.remove('is-open');
        o.classList.remove('is-open');
    });
})();

/* Laporan dropdown */
(function() {
    var t = document.getElementById('laporanToggle');
    var s = document.getElementById('laporanSub');
    if (!t || !s) return;
    t.addEventListener('click', function() {
        s.classList.toggle('is-open');
        t.classList.toggle('is-open');
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
</script>

</body>

</html>