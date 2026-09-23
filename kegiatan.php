<?php
// ========================================
// KEGIATAN - SIAVO GUEST
// Layout: Atlas 2026 (3-column docs)
// ========================================

$page_title  = 'Kegiatan';
$page_active = 'kegiatan';

require_once 'includes/config.php';
require_once 'includes/functions.php';

// ===== Filter =====
$tipe_filter = $_GET['tipe'] ?? 'semua';
if (!in_array($tipe_filter, ['semua','sema','siavo'])) $tipe_filter = 'semua';

$waktu_filter = $_GET['waktu'] ?? 'semua';
if (!in_array($waktu_filter, ['semua','akan_datang','sudah_lewat'])) $waktu_filter = 'semua';

// ===== Query =====
$where = [];
if ($tipe_filter !== 'semua') $where[] = "tipe = '" . $conn->real_escape_string($tipe_filter) . "'";
if ($waktu_filter === 'akan_datang') $where[] = "tanggal >= CURDATE()";
elseif ($waktu_filter === 'sudah_lewat') $where[] = "tanggal < CURDATE()";
$where_sql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$kegiatan = [];
$order_sql = "ORDER BY (tanggal < CURDATE()) ASC, IF(tanggal >= CURDATE(), tanggal, NULL) ASC, tanggal DESC";
$r = $conn->query("SELECT * FROM kegiatan $where_sql $order_sql");
if ($r) { while ($row = $r->fetch_assoc()) $kegiatan[] = $row; }

// ===== Statistik =====
$total_kegiatan = 0;
$r = $conn->query("SELECT COUNT(*) c FROM kegiatan");
if ($r) $total_kegiatan = (int)$r->fetch_assoc()['c'];

$total_bulan_ini = 0;
$r = $conn->query("SELECT COUNT(*) c FROM kegiatan WHERE MONTH(tanggal)=MONTH(CURDATE()) AND YEAR(tanggal)=YEAR(CURDATE())");
if ($r) $total_bulan_ini = (int)$r->fetch_assoc()['c'];

$conn->close();
include 'includes/header_guest.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/docs.css">
<?php

// ===== Featured: kegiatan akan datang terdekat =====
$featured = null;
foreach ($kegiatan as $k) {
    if (strtotime($k['tanggal']) >= strtotime(date('Y-m-d'))) { $featured = $k; break; }
}
if (!$featured && !empty($kegiatan)) $featured = $kegiatan[0];

$sisanya = array_values(array_filter($kegiatan, function ($k) use ($featured) {
    return !$featured || $k['id'] !== $featured['id'];
}));

// Helper
function getBulanSingkat($tanggal) {
    $bulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return $bulan[(int)date('n', strtotime($tanggal)) - 1];
}

// Placeholder image generator (SVG data URI)
function placeholderKegiatan($warna, $w = 800, $h = 400) {
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 {$w} {$h}' preserveAspectRatio='xMidYMid slice'>"
         . "<rect width='{$w}' height='{$h}' fill='{$warna}'/>"
         . "<rect x='80' y='60' width='70' height='70' rx='8' fill='#ffffff' opacity='0.35' transform='rotate(45 115 95)'/>"
         . "<rect x='300' y='210' width='120' height='120' rx='15' fill='#ffffff' opacity='0.22' transform='rotate(45 360 270)'/>"
         . "<rect x='560' y='40' width='90' height='90' rx='11' fill='#ffffff' opacity='0.3' transform='rotate(45 605 85)'/>"
         . "<rect x='660' y='240' width='60' height='60' rx='7' fill='#ffffff' opacity='0.4' transform='rotate(45 690 270)'/>"
         . "</svg>";
    return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
}
?>

<!-- ============ PAGE HEADER ============ -->
<header class="page-head">
    <div class="wrap">
        <div class="page-icon" aria-hidden="true"><i class="fa-solid fa-calendar-days"></i></div>
        <div>
            <h1>Kegiatan</h1>
            <nav class="crumbs" aria-label="Breadcrumb">
                <ol>
                    <li><a href="<?php echo APP_URL; ?>">Beranda</a></li>
                    <li><span aria-current="page">Kegiatan</span></li>
                </ol>
            </nav>
        </div>
    </div>
</header>

<div class="wrap">
    <div class="docs">

        <!-- ============ KOLOM KIRI: FILTER ============ -->
        <aside class="side" aria-label="Filter kegiatan">

            <nav class="side-card" aria-labelledby="lbl-tipe">
                <span class="side-label" id="lbl-tipe">Tipe</span>
                <ul class="cat-list">
                    <?php
                    $tipe_link = function($t) use ($waktu_filter) {
                        return 'kegiatan.php?tipe=' . $t . '&waktu=' . $waktu_filter;
                    };
                    ?>
                    <li><a href="<?php echo $tipe_link('semua'); ?>"
                            <?php echo $tipe_filter==='semua'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-globe"></i>Semua</a></li>
                    <li><a href="<?php echo $tipe_link('sema'); ?>"
                            <?php echo $tipe_filter==='sema'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-users"></i>SEMA</a></li>
                    <li><a href="<?php echo $tipe_link('siavo'); ?>"
                            <?php echo $tipe_filter==='siavo'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-graduation-cap"></i>SIAVO</a></li>
                </ul>
            </nav>

            <nav class="side-card" aria-labelledby="lbl-waktu">
                <span class="side-label" id="lbl-waktu">Waktu</span>
                <ul class="cat-list">
                    <?php
                    $waktu_link = function($w) use ($tipe_filter) {
                        return 'kegiatan.php?tipe=' . $tipe_filter . '&waktu=' . $w;
                    };
                    ?>
                    <li><a href="<?php echo $waktu_link('semua'); ?>"
                            <?php echo $waktu_filter==='semua'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-infinity"></i>Semua</a></li>
                    <li><a href="<?php echo $waktu_link('akan_datang'); ?>"
                            <?php echo $waktu_filter==='akan_datang'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-arrow-right"></i>Akan Datang</a></li>
                    <li><a href="<?php echo $waktu_link('sudah_lewat'); ?>"
                            <?php echo $waktu_filter==='sudah_lewat'?'aria-current="page"':''; ?>><i
                                class="fa-solid fa-clock-rotate-left"></i>Sudah Lewat</a></li>
                </ul>
            </nav>

            <div class="cta-box">
                <div class="cta-icon" aria-hidden="true"><i class="fa-solid fa-lightbulb"></i></div>
                <p>Ada kegiatan yang mau diusulkan?</p>
                <a class="btn" href="<?php echo APP_URL; ?>login.php">Usulkan Kegiatan</a>
            </div>
        </aside>

        <!-- ============ KOLOM TENGAH: MAIN ============ -->
        <main id="konten">
            <div class="main-head">
                <div>
                    <h2>Daftar Kegiatan</h2>
                    <p>
                        <?php if ($tipe_filter !== 'semua' || $waktu_filter !== 'semua'): ?>
                        Menampilkan <?php echo count($kegiatan); ?> kegiatan
                        <?php if ($tipe_filter !== 'semua'): ?> dengan tipe
                        <strong><?php echo strtoupper($tipe_filter); ?></strong><?php endif; ?>
                        <?php if ($waktu_filter !== 'semua'): ?>
                        (<?php echo str_replace('_', ' ', $waktu_filter); ?>)<?php endif; ?>
                        <?php else: ?>
                        Semua kegiatan SEMA dan SIAVO, dari yang terdekat hingga yang sudah selesai.
                        <?php endif; ?>
                    </p>
                </div>
                <span class="pill"><?php echo count($kegiatan); ?> kegiatan</span>
            </div>

            <?php if (!empty($kegiatan)): ?>

            <!-- ============================================== -->
            <!-- FEATURED — kegiatan akan datang terdekat       -->
            <!-- ============================================== -->
            <?php if ($featured):
                    $is_upcoming = strtotime($featured['tanggal']) >= strtotime(date('Y-m-d'));
                    $img = !empty($featured['gambar'])
                        ? APP_URL . 'assets/img/kegiatan/' . $featured['gambar']
                        : placeholderKegiatan($featured['tipe'] === 'sema' ? '#4e0009' : '#1d4ed8', 800, 280);
                ?>
            <article class="k-featured" id="kegiatan-1" aria-labelledby="k-judul-1">
                <div class="media">
                    <img src="<?php echo $img; ?>" alt="" loading="lazy" width="800" height="280">
                    <?php if ($is_upcoming): ?>
                    <span class="badge badge--soon badge-soon">Segera</span>
                    <?php endif; ?>
                    <span
                        class="badge badge--<?php echo $featured['tipe']; ?> badge-type"><?php echo strtoupper($featured['tipe']); ?></span>
                </div>
                <div class="k-body">
                    <div class="k-top">
                        <div class="datebox" aria-hidden="true">
                            <b><?php echo date('d', strtotime($featured['tanggal'])); ?></b>
                            <span><?php echo getBulanSingkat($featured['tanggal']); ?></span>
                        </div>
                        <div>
                            <h3 id="k-judul-1"><?php echo htmlspecialchars($featured['nama']); ?></h3>
                            <p class="meta">
                                <span>
                                    <i class="fa-regular fa-calendar"></i>
                                    <time
                                        datetime="<?php echo date('Y-m-d', strtotime($featured['tanggal'])); ?>"><?php echo date('d M Y', strtotime($featured['tanggal'])); ?></time>
                                </span>
                                <?php if (!empty($featured['waktu'])): ?>
                                <span>
                                    <i class="fa-regular fa-clock"></i>
                                    <?php echo date('H.i', strtotime($featured['waktu'])); ?> WIB
                                </span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    <p class="k-desc"><?php echo nl2br(htmlspecialchars($featured['deskripsi'])); ?></p>
                </div>
            </article>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- LIST KEGIATAN LAINNYA                          -->
            <!-- ============================================== -->
            <?php if (!empty($sisanya)): ?>
            <div class="sep" role="separator"><span>Kegiatan lainnya</span></div>
            <div class="k-grid">
                <?php foreach ($sisanya as $i => $k):
                            $idx = $i + 2;
                            $sudah_lewat = strtotime($k['tanggal']) < strtotime(date('Y-m-d'));
                            $img = !empty($k['gambar'])
                                ? APP_URL . 'assets/img/kegiatan/' . $k['gambar']
                                : placeholderKegiatan($k['tipe'] === 'sema' ? '#4e0009' : '#1d4ed8', 400, 160);
                        ?>
                <article class="k-card <?php echo $sudah_lewat ? 'is-done' : ''; ?>" id="kegiatan-<?php echo $idx; ?>"
                    aria-labelledby="k-judul-<?php echo $idx; ?>">
                    <div class="media">
                        <img src="<?php echo $img; ?>" alt="" loading="lazy" width="400" height="160">
                        <span
                            class="badge badge--<?php echo $k['tipe']; ?> badge-type"><?php echo strtoupper($k['tipe']); ?></span>
                    </div>
                    <div class="k-info">
                        <p class="k-date">
                            <time datetime="<?php echo date('Y-m-d', strtotime($k['tanggal'])); ?>">
                                <i
                                    class="fa-regular fa-calendar"></i><?php echo date('d M Y', strtotime($k['tanggal'])); ?>
                            </time>
                            <?php if (!empty($k['waktu'])): ?>
                            <time datetime="<?php echo date('H:i', strtotime($k['waktu'])); ?>">
                                <i class="fa-regular fa-clock"></i><?php echo date('H.i', strtotime($k['waktu'])); ?>
                                WIB
                            </time>
                            <?php endif; ?>
                        </p>
                        <h3 id="k-judul-<?php echo $idx; ?>"><a
                                href="<?php echo APP_URL; ?>kegiatan_detail.php?id=<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama']); ?></a>
                        </h3>
                        <p><?php echo htmlspecialchars(mb_strimwidth($k['deskripsi'], 0, 140, '...')); ?></p>
                    </div>
                    <div class="k-foot">
                        <?php if ($sudah_lewat): ?>
                        <span><i class="fa-solid fa-flag-checkered"></i>Kegiatan selesai</span>
                        <span class="badge badge--done">Selesai</span>
                        <?php else: ?>
                        <span><i class="fa-solid fa-calendar-check"></i>Kegiatan akan datang</span>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- CTA mobile -->
            <div class="cta-box cta-mobile">
                <div class="cta-icon" aria-hidden="true"><i class="fa-solid fa-lightbulb"></i></div>
                <p>Ada kegiatan yang mau diusulkan?</p>
                <a class="btn" href="<?php echo APP_URL; ?>login.php">Usulkan Kegiatan</a>
            </div>

            <?php else: ?>
            <div class="empty">
                <i class="fa-regular fa-calendar-xmark" aria-hidden="true"></i>
                <h3>Belum ada kegiatan yang cocok</h3>
                <p>Coba ubah filter tipe atau waktu, atau lihat semua kegiatan.</p>
                <a class="btn btn-red" href="kegiatan.php">Tampilkan semua kegiatan</a>
            </div>
            <?php endif; ?>
        </main>

        <!-- ============ KOLOM KANAN: INFO PANEL ============ -->
        <?php if (!empty($kegiatan)): ?>
        <aside class="rail" aria-label="Ringkasan dan daftar kegiatan">
            <section class="side-card" aria-labelledby="lbl-stat">
                <span class="side-label" id="lbl-stat">Ringkasan</span>
                <div class="stat-list">
                    <div class="stat"><b><?php echo $total_kegiatan; ?></b><span>Total kegiatan</span></div>
                    <div class="stat"><b><?php echo $total_bulan_ini; ?></b><span>Bulan ini</span></div>
                </div>
            </section>

            <nav class="toc" data-toc aria-labelledby="lbl-toc">
                <span class="side-label" id="lbl-toc">Di halaman ini</span>
                <ul>
                    <?php if ($featured): ?>
                    <li><a href="#kegiatan-1" title="<?php echo htmlspecialchars($featured['nama']); ?>"
                            aria-current="true"><?php echo htmlspecialchars(mb_strimwidth($featured['nama'], 0, 34, '…')); ?></a>
                    </li>
                    <?php endif; ?>
                    <?php foreach ($sisanya as $i => $k): $idx = $i + 2; ?>
                    <li><a href="#kegiatan-<?php echo $idx; ?>"
                            title="<?php echo htmlspecialchars($k['nama']); ?>"><?php echo htmlspecialchars(mb_strimwidth($k['nama'], 0, 34, '…')); ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a class="toc-top" href="#top"><i class="fa-solid fa-arrow-up"></i>Kembali ke atas</a>
            </nav>
        </aside>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer_guest.php'; ?>

<script src="<?php echo APP_URL; ?>assets/js/docs.js" defer></script>