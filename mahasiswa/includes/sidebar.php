<?php
$page_active = $page_active ?? '';

// Foto profil — pakai default SVG kalau user belum upload
$foto_nama = DEFAULT_PROFILE_PHOTO;
if (isset($_SESSION['user_id']) && isset($conn)) {
    $stmt = $conn->prepare("SELECT foto_profil FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $r = $stmt->get_result();
    if ($row = $r->fetch_assoc()) {
        if (!empty($row['foto_profil'])) $foto_nama = $row['foto_profil'];
    }
    $stmt->close();
}
$foto_path = APP_URL . 'assets/img/person/' . $foto_nama;
$foto_default_url = APP_URL . 'assets/img/person/' . DEFAULT_PROFILE_PHOTO;
$user_name = $_SESSION['user_name'] ?? 'Mahasiswa';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> · <?php echo $page_title ?? 'Dashboard'; ?></title>
    <link rel="icon" type="image/x-icon" href="<?php echo APP_URL; ?>assets/img/logo/logosiavo.webp">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Bootstrap 5.3 (grid only) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
        href="<?php echo APP_URL; ?>mahasiswa/assets/css/style.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/siavo/mahasiswa/assets/css/style.css'); ?>">
    <link rel="stylesheet"
        href="<?php echo APP_URL; ?>assets/css/file-upload.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/siavo/assets/css/file-upload.css'); ?>">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/theme-transition.css">

    <!-- Anti-flash theme -->
    <script>
    (function() {
        try {
            var t = localStorage.getItem('siavo-theme');
            if (t === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
            else if (t === 'light') document.documentElement.setAttribute('data-theme', 'light');
            else if (window.matchMedia('(prefers-color-scheme: dark)').matches)
                document.documentElement.setAttribute('data-theme', 'dark');
        } catch (e) {}
    })();
    </script>
</head>

<body>

    <div class="main-wrapper">
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="main-sidebar" id="mainSidebar">
            <div class="sidebar-brand">
                <a href="<?php echo APP_URL; ?>mahasiswa/dashboard.php">
                    <span class="logo-dot"></span>
                    <span class="brand-name">SIAVO</span>
                    <span class="brand-tag">Mahasiswa</span>
                </a>
            </div>

            <ul class="sidebar-menu">
                <li class="<?php echo $page_active==='dashboard'?'active':''; ?>">
                    <a href="<?php echo APP_URL; ?>mahasiswa/dashboard.php">
                        <i class="fas fa-tachometer-alt"></i><span>Dashboard</span>
                    </a>
                </li>
                <li class="<?php echo $page_active==='aspirasi'?'active':''; ?>">
                    <a href="<?php echo APP_URL; ?>mahasiswa/aspirasi.php">
                        <i class="fas fa-pen-nib"></i><span>Kirim Aspirasi</span>
                    </a>
                </li>
                <li class="<?php echo $page_active==='laporan-saya'?'active':''; ?>">
                    <a href="<?php echo APP_URL; ?>mahasiswa/laporan-saya.php">
                        <i class="fas fa-list-ul"></i><span>Laporan Saya</span>
                    </a>
                </li>
                <li class="<?php echo $page_active==='tracking'?'active':''; ?>">
                    <a href="<?php echo APP_URL; ?>mahasiswa/tracking.php">
                        <i class="fas fa-search"></i><span>Tracking</span>
                    </a>
                </li>
                <li class="<?php echo $page_active==='profile'?'active':''; ?>">
                    <a href="<?php echo APP_URL; ?>mahasiswa/profile.php">
                        <i class="fas fa-user-circle"></i><span>Profil</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo APP_URL; ?>logout.php" class="text-danger">
                        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>

        <div class="main-content">
            <header class="main-navbar">
                <div class="navbar-left">
                    <button class="top-toggle" id="sidebarToggle" aria-label="Menu">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="welcome-text">
                        <h5>Halo, <?php echo htmlspecialchars($user_name); ?></h5>
                        <small>Selamat datang kembali di SIAVO</small>
                    </div>
                </div>

                <ul class="navbar-actions">
                    <!-- Theme toggle -->
                    <li>
                        <button class="theme-btn" data-theme-toggle aria-label="Ganti tema">
                            <i class="fas fa-sun"></i>
                        </button>
                    </li>

                    <!-- User chip -->
                    <li>
                        <a href="<?php echo APP_URL; ?>mahasiswa/profile.php" class="user-chip" title="Profil Saya">
                            <img src="<?php echo $foto_path; ?>" alt="User"
                                onerror="this.onerror=null; this.src='<?php echo $foto_default_url; ?>'">
                            <div class="user-info">
                                <span><?php echo htmlspecialchars($user_name); ?></span>
                                <small>Mahasiswa</small>
                            </div>
                        </a>
                    </li>

                    <!-- Logout button -->
                    <li>
                        <a href="<?php echo APP_URL; ?>logout.php" class="logout-btn" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </header>

            <main class="admin-content">
                <div class="container-fluid" style="padding:0">