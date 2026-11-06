</main>

<footer>
    <div class="wrap">
        <div class="fgrid">
            <div>
                <a href="<?php echo APP_URL; ?>" class="logo" style="color:#fff">
                    <span class="logo-dot" style="background:var(--yellow)"></span>SIAVO
                </a>
                <p style="margin-top:12px;max-width:32ch">
                    Sistem Informasi Aspirasi dan Advokasi Online. Jembatan digital antara mahasiswa dan institusi
                    kampus.
                </p>
                <div style="display:flex;gap:10px;margin-top:18px">
                    <a href="#" class="icon-btn" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="icon-btn" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="icon-btn" aria-label="Email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>

            <div>
                <h4>Platform</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>">Beranda</a></li>
                    <li><a href="<?php echo APP_URL; ?>informasi.php">Pusat Informasi</a></li>
                    <li><a href="<?php echo APP_URL; ?>kegiatan.php">Kegiatan</a></li>
                </ul>
            </div>

            <div>
                <h4>Bantuan</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>informasi.php?kategori=panduan">Panduan</a></li>
                    <li><a href="<?php echo APP_URL; ?>informasi.php?kategori=faq">FAQ</a></li>
                    <li><a href="<?php echo APP_URL; ?>informasi.php?kategori=sop">SOP Advokasi</a></li>
                    <li><a href="<?php echo APP_URL; ?>informasi.php?kategori=beasiswa">Info Beasiswa</a></li>
                </ul>
            </div>

            <div>
                <h4>Akun</h4>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>login.php">Masuk</a></li>
                    <li><a href="<?php echo APP_URL; ?>register.php">Daftar</a></li>
                </ul>
                <h4 style="margin-top:24px">Kontak Sekretariat</h4>
                <ul>
                    <li style="font-size:.85rem;color:var(--side-muted,#c8b0b2); line-height: 1.5; margin-bottom: 8px;">
                        <i class="fas fa-map-marker-alt" style="margin-right:6px;color:var(--yellow)"></i>
                        Kampus II Gd. FUAD Lt.1 Jl. Puspita Jaya, Pintu, Jenangan, Ponorogo 63492
                    </li>
                    <li style="font-size:.85rem;color:var(--side-muted,#c8b0b2); margin-bottom: 8px;">
                        <i class="fas fa-envelope" style="margin-right:6px;color:var(--yellow)"></i>
                        semafuaduinponorogo@gmail.com
                    </li>
                    <li style="font-size:.85rem;color:var(--side-muted,#c8b0b2);">
                        <i class="fas fa-phone" style="margin-right:6px;color:var(--yellow)"></i>
                        0859176889431
                    </li>
                </ul>
            </div>
        </div>

        <div class="copy">
            <span>© <?php echo date('Y'); ?> SEMA FUAD UIN Ponorogo · Lembaga Kemahasiswaan</span>
            <span>Dibuat untuk mahasiswa.</span>
        </div>
    </div>
</footer>

<!-- Theme toggle (circle wipe) -->
<script src="<?php echo APP_URL; ?>assets/js/theme-toggle.js"></script>

<script>
/* MOBILE MENU */
(function() {
    var t = document.getElementById('mobileMenuToggle');
    var m = document.getElementById('mobileMenu');
    if (!t || !m) return;
    t.addEventListener('click', function(e) {
        e.preventDefault();
        var open = m.classList.toggle('is-open');
        var i = t.querySelector('i');
        if (i) i.className = open ? 'fas fa-times' : 'fas fa-bars';
    });
})();
</script>

</body>

</html>