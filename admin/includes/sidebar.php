<?php
$page_active = $page_active ?? '';
$is_laporan_open = in_array($page_active, ['pengajuan','verifikasi','tindak_lanjut','selesai']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> · Admin · <?php echo $page_title ?? 'Dashboard'; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>assets/img/logo/logosiavo.webp">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="<?php echo APP_URL; ?>admin/style/css/admin-styles.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/file-upload.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/theme-transition.css">

    <!-- Anti-flash -->
    <script>
    (function() {
        try {
            var t = localStorage.getItem('siavo-theme');
            if (t === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else if (t === 'light') {
                document.documentElement.setAttribute('data-theme', 'light');
            } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) {}
    })();
    </script>
</head>

<body>

    <div class="admin-layout">
        <div class="side-overlay" id="sideOverlay"></div>

        <aside class="admin-side" id="adminSide">
            <div class="side-brand">
                <span class="logo-dot"></span>
                <span class="brand-name">SIAVO</span>
                <span class="brand-tag">Admin</span>
            </div>

            <nav class="side-nav">
                <div class="side-label">Utama</div>
                <a href="<?php echo APP_URL; ?>admin/dashboard.php"
                    class="side-link <?php echo $page_active==='dashboard'?'is-active':''; ?>">
                    <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                </a>

                <div class="side-label">Laporan</div>
                <button class="side-link side-link-toggle <?php echo $is_laporan_open?'is-open':''; ?>"
                    id="laporanToggle">
                    <i class="fas fa-file-alt"></i><span>Verifikasi Laporan</span>
                    <i class="fas fa-chevron-down side-arrow"></i>
                </button>
                <div class="side-sub <?php echo $is_laporan_open?'is-open':''; ?>" id="laporanSub">
                    <a href="<?php echo APP_URL; ?>admin/verifikasi-laporan.php?status=pengajuan"
                        class="<?php echo $page_active==='pengajuan'?'is-active':''; ?>">Pengajuan</a>
                    <a href="<?php echo APP_URL; ?>admin/verifikasi-laporan.php?status=verifikasi"
                        class="<?php echo $page_active==='verifikasi'?'is-active':''; ?>">Verifikasi</a>
                    <a href="<?php echo APP_URL; ?>admin/verifikasi-laporan.php?status=tindak_lanjut"
                        class="<?php echo $page_active==='tindak_lanjut'?'is-active':''; ?>">Tindak Lanjut</a>
                    <a href="<?php echo APP_URL; ?>admin/verifikasi-laporan.php?status=selesai"
                        class="<?php echo $page_active==='selesai'?'is-active':''; ?>">Selesai</a>
                </div>

                <a href="<?php echo APP_URL; ?>admin/rekap-laporan.php"
                    class="side-link <?php echo $page_active==='rekap'?'is-active':''; ?>">
                    <i class="fas fa-chart-pie"></i><span>Rekap Laporan</span>
                </a>

                <div class="side-label">Manajemen</div>
                <a href="<?php echo APP_URL; ?>admin/kelola-pengumuman.php"
                    class="side-link <?php echo $page_active==='pengumuman'?'is-active':''; ?>">
                    <i class="fas fa-bullhorn"></i><span>Pengumuman</span>
                </a>
                <a href="<?php echo APP_URL; ?>admin/kelola-artikel.php"
                    class="side-link <?php echo $page_active==='artikel'?'is-active':''; ?>">
                    <i class="fas fa-file-lines"></i><span>Artikel</span>
                </a>

                <a href="<?php echo APP_URL; ?>admin/kelola-kegiatan.php"
                    class="side-link <?php echo $page_active==='kegiatan'?'is-active':''; ?>">
                    <i class="fas fa-calendar-days"></i><span>Kegiatan</span>
                </a>

                <a href="<?php echo APP_URL; ?>admin/kelola-user.php"
                    class="side-link <?php echo $page_active==='user'?'is-active':''; ?>">
                    <i class="fas fa-users"></i><span>User</span>
                </a>

                <div class="side-label">Sistem</div>
                <a href="<?php echo APP_URL; ?>" target="_blank" class="side-link">
                    <i class="fas fa-globe"></i><span>Lihat Website</span>
                </a>
                <a href="<?php echo APP_URL; ?>logout.php" class="side-link side-link-danger">
                    <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                </a>
            </nav>

            <div class="side-foot">
                <div class="foot-avatar">AD</div>
                <div class="foot-info">
                    <div class="foot-name">Administrator</div>
                    <div class="foot-mail">admin@siavo.ac.id</div>
                </div>
            </div>
        </aside>

        <div class="admin-main">
            <header class="admin-top">
                <button class="top-toggle" id="sideToggle" aria-label="Menu"><i class="fas fa-bars"></i></button>
                <div class="top-title"><?php echo $page_title ?? 'Dashboard'; ?></div>
                <div class="top-actions">
                    <button class="icon-btn" data-theme-toggle aria-label="Ganti tema">
                        <i class="fas fa-sun"></i>
                    </button>
                    <button class="icon-btn" aria-label="Notifikasi">
                        <i class="fas fa-bell"></i>
                        <span class="dot-notif"></span>
                    </button>
                    <div class="top-divider"></div>
                    <div class="top-user">
                        <div class="user-avatar">AD</div>
                        <div class="user-text">
                            <div class="user-name">Admin</div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                <div class="container-fluid">