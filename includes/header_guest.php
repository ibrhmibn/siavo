<?php
$page_active = $page_active ?? '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escape(APP_NAME); ?> · <?php echo escape($page_title ?? 'Beranda'); ?></title>
    <meta name="description"
        content="Sistem Informasi Aspirasi dan Advokasi Online — jembatan digital antara mahasiswa dan institusi.">

    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>assets/img/logo/logosiavo.webp">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/landing.css?v=3">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/theme-transition.css">

    <!-- Anti-flash: terapkan tema sebelum CSS lain render (Paksa Light Mode) -->
    <script>
    (function() {
        try {
            var t = localStorage.getItem('siavo-theme');
            if (t === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                // Paksa selalu light mode kalau user belum pernah milih
                document.documentElement.setAttribute('data-theme', 'light');
            }
        } catch (e) {}
    })();
    </script>

    <style>
    /* ============================================
       CSS KHUSUS HEADER (LOGO & LAYOUT)
       ============================================ */
    .brand-logos {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-right: 15px;
        padding-right: 15px;
        border-right: 2px solid var(--line-strong);
    }

    .logo-box {
        background: var(--yellow);
        /* Kuning Emas UIN */
        padding: 6px 14px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 12px rgba(251, 191, 36, 0.3);
        /* Shadow kuning */
        transition: transform 0.2s ease;
    }

    .logo-box:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
    }

    .logo-box img {
        height: 32px;
        width: auto;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .brand-logos {
            gap: 8px;
            padding-right: 8px;
            border-right: 0;
        }

        .logo-box {
            padding: 4px 8px;
        }

        .logo-box img {
            height: 24px;
        }

        .logo-text {
            display: none !important;
        }
    }
    </style>
</head>

<body>

    <header class="nav">
        <div class="wrap">
            <a href="<?php echo APP_URL; ?>" class="logo" aria-label="SIAVO beranda">
                <div class="brand-logos">
                    <div class="logo-box">
                        <img src="<?php echo APP_URL; ?>assets/img/logo/logo-uin.png" alt="UIN Ponorogo">
                        <img src="<?php echo APP_URL; ?>assets/img/logo/logo-sema.webp" alt="SEMA FUAD">
                    </div>
                </div>
                <!-- Teks SIAVO -->
                <span class="logo-text" style="display: flex; align-items: center; gap: 8px;">
                    <span class="logo-dot"></span>SIAVO
                </span>
            </a>

            <nav class="links" aria-label="Navigasi utama">
                <a href="<?php echo APP_URL; ?>"
                    class="<?php echo $page_active==='beranda'?'is-active':''; ?>">Beranda</a>
                <a href="<?php echo APP_URL; ?>informasi.php"
                    class="<?php echo $page_active==='informasi'?'is-active':''; ?>">Pusat Informasi</a>
                <a href="<?php echo APP_URL; ?>kegiatan.php"
                    class="<?php echo $page_active==='kegiatan'?'is-active':''; ?>">Kegiatan</a>
            </nav>

            <div class="nav-actions">
                <button class="icon-btn" data-theme-toggle aria-label="Ganti tema terang atau gelap">
                    <i class="fas fa-sun"></i>
                </button>
                <a href="<?php echo APP_URL; ?>login.php" class="btn btn-ghost">Masuk</a>
                <a href="<?php echo APP_URL; ?>register.php" class="btn btn-red">Daftar</a>
                <button class="mobile-toggle" id="mobileMenuToggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <div id="mobileMenu" class="mobile-menu">
        <a href="<?php echo APP_URL; ?>" class="<?php echo $page_active==='beranda'?'is-active':''; ?>">Beranda</a>
        <a href="<?php echo APP_URL; ?>informasi.php"
            class="<?php echo $page_active==='informasi'?'is-active':''; ?>">Pusat Informasi</a>
        <a href="<?php echo APP_URL; ?>kegiatan.php"
            class="<?php echo $page_active==='kegiatan'?'is-active':''; ?>">Kegiatan</a>
        <hr>
        <a href="<?php echo APP_URL; ?>login.php">Masuk</a>
        <a href="<?php echo APP_URL; ?>register.php" class="btn btn-red"
            style="margin-top:6px;display:block;text-align:center">Daftar</a>
    </div>

    <main id="top">