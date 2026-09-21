<?php
// ========================================
// PUSAT INFORMASI - SIAVO GUEST
// Layout: Atlas 2026 (3-column docs)
// ========================================

$page_title  = 'Pusat Informasi';
$page_active = 'informasi';

require_once 'includes/config.php';
require_once 'includes/functions.php';

// ===== Kategori =====
$semua_kategori = ['sop', 'panduan', 'beasiswa', 'faq'];
$kategori_filter = $_GET['kategori'] ?? 'sop';
if (!in_array($kategori_filter, $semua_kategori)) $kategori_filter = 'sop';

$kategori_meta = [
    'sop'      => ['label' => 'SOP Advokasi',      'icon' => 'fa-clipboard-list',  'desc' => 'Prosedur resmi pengajuan, verifikasi, dan tindak lanjut aspirasi mahasiswa.'],
    'panduan'  => ['label' => 'Panduan Pengajuan', 'icon' => 'fa-map-signs',       'desc' => 'Langkah-langkah praktis menyampaikan aspirasi dengan benar.'],
    'beasiswa' => ['label' => 'Info Beasiswa',     'icon' => 'fa-graduation-cap',  'desc' => 'Informasi beasiswa, UKT, dan bantuan keuangan mahasiswa.'],
    'faq'      => ['label' => 'FAQ',               'icon' => 'fa-circle-question', 'desc' => 'Jawaban singkat untuk pertanyaan yang sering muncul.'],
];

// ===== Ambil artikel =====
$artikel = [];
$stmt = $conn->prepare("SELECT * FROM artikel WHERE kategori_info = ? ORDER BY created_at DESC");
$stmt->bind_param("s", $kategori_filter);
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $artikel[] = $row;
$stmt->close();

// ===== Count per kategori =====
$jumlah_per_kategori = [];
foreach ($semua_kategori as $k) {
    $ks = $conn->real_escape_string($k);
    $res = $conn->query("SELECT COUNT(*) c FROM artikel WHERE kategori_info='$ks'");
    $jumlah_per_kategori[$k] = $res ? (int)$res->fetch_assoc()['c'] : 0;
}

$conn->close();
include 'includes/header_guest.php';
?>
<link rel="stylesheet" href="<?php echo APP_URL; ?>assets/css/docs.css">
<?php

// ===== Pisah featured & sisanya =====
$featured = !empty($artikel) ? $artikel[0] : null;
$sisanya  = count($artikel) > 1 ? array_slice($artikel, 1) : [];

// Helper: estimasi waktu baca (200 kata/menit)
function estimasiBaca($teks) {
    return max(1, (int)ceil(str_word_count(strip_tags($teks)) / 200));
}
?>

<!-- ============ PAGE HEADER ============ -->
<header class="page-head">
    <div class="wrap">
        <div class="page-icon" aria-hidden="true">
            <i class="fa-solid <?php echo $kategori_meta[$kategori_filter]['icon']; ?>"></i>
        </div>
        <div>
            <h1>Pusat Informasi</h1>
            <nav class="crumbs" aria-label="Breadcrumb">
                <ol>
                    <li><a href="<?php echo APP_URL; ?>">Beranda</a></li>
                    <li><a href="<?php echo APP_URL; ?>informasi.php">Pusat Info</a></li>
                    <li><span aria-current="page"><?php echo strtoupper($kategori_filter); ?></span></li>
                </ol>
            </nav>
        </div>
    </div>
</header>

<div class="wrap">
    <div class="docs">

        <!-- ============ KOLOM KIRI: SIDEBAR KATEGORI ============ -->
        <aside class="side" aria-label="Kategori informasi">
            <nav class="side-card" aria-labelledby="lbl-kategori">
                <span class="side-label" id="lbl-kategori">Kategori</span>
                <ul class="cat-list">
                    <?php foreach ($semua_kategori as $k): ?>
                    <li>
                        <a href="informasi.php?kategori=<?php echo $k; ?>"
                            <?php echo $k === $kategori_filter ? 'aria-current="page"' : ''; ?>>
                            <i class="fa-solid <?php echo $kategori_meta[$k]['icon']; ?>" aria-hidden="true"></i>
                            <?php echo $kategori_meta[$k]['label']; ?>
                            <span class="count"><?php echo $jumlah_per_kategori[$k]; ?></span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="cta-box">
                <div class="cta-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></div>
                <p>Punya aspirasi atau keluhan?</p>
                <a class="btn" href="<?php echo APP_URL; ?>login.php">Kirim Aspirasi</a>
            </div>
        </aside>

        <!-- ============ KOLOM TENGAH: MAIN ============ -->
        <main id="konten">
            <div class="main-head">
                <div>
                    <h2><?php echo $kategori_meta[$kategori_filter]['label']; ?></h2>
                    <p><?php echo $kategori_meta[$kategori_filter]['desc']; ?></p>
                </div>
                <span class="pill"><?php echo count($artikel); ?> artikel</span>
            </div>

            <?php if (!empty($artikel)): ?>

            <!-- ============================================== -->
            <!-- FEATURED ARTICLE — artikel terbaru             -->
            <!-- Konten dirender sebagai HTML (bukan plain text) -->
            <!-- ============================================== -->
            <?php if ($featured): ?>
            <article class="featured" id="artikel-1" aria-labelledby="judul-1">
                <span class="tag"><?php echo strtoupper($kategori_filter); ?></span>
                <h3 id="judul-1"><?php echo htmlspecialchars($featured['judul']); ?></h3>
                <p class="meta">
                    <span>
                        <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                        <time datetime="<?php echo date('Y-m-d', strtotime($featured['created_at'])); ?>">
                            <?php echo date('d M Y', strtotime($featured['created_at'])); ?>
                        </time>
                    </span>
                    <span>
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        <?php echo estimasiBaca($featured['konten']); ?> menit baca
                    </span>
                </p>
                <div class="prose">
                    <?php echo $featured['konten']; ?>
                </div>
            </article>
            <?php endif; ?>

            <!-- ============================================== -->
            <!-- LIST ARTIKEL LAINNYA                           -->
            <!-- ============================================== -->
            <?php if (!empty($sisanya)): ?>
            <div class="sep" role="separator"><span>Artikel lainnya</span></div>
            <div class="article-list">
                <?php foreach ($sisanya as $i => $a): $idx = $i + 2; ?>
                <article class="row-card" id="artikel-<?php echo $idx; ?>" aria-labelledby="judul-<?php echo $idx; ?>">
                    <div class="row-head">
                        <span class="tag tag--soft"><?php echo strtoupper($kategori_filter); ?></span>
                        <time datetime="<?php echo date('Y-m-d', strtotime($a['created_at'])); ?>">
                            <?php echo date('d M Y', strtotime($a['created_at'])); ?>
                        </time>
                    </div>
                    <h3 id="judul-<?php echo $idx; ?>"><?php echo htmlspecialchars($a['judul']); ?></h3>
                    <p class="body">
                        <?php echo htmlspecialchars(mb_strimwidth(strip_tags($a['konten']), 0, 200, '...')); ?>
                    </p>
                    <details>
                        <summary>Baca selengkapnya</summary>
                        <div class="prose"><?php echo $a['konten']; ?></div>
                    </details>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- CTA mobile -->
            <div class="cta-box cta-mobile">
                <div class="cta-icon" aria-hidden="true"><i class="fa-solid fa-bullhorn"></i></div>
                <p>Punya aspirasi atau keluhan?</p>
                <a class="btn" href="<?php echo APP_URL; ?>login.php">Kirim Aspirasi</a>
            </div>

            <?php else: ?>
            <div class="empty">
                <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                <h3>Belum ada artikel di kategori ini</h3>
                <p>Artikel akan muncul di sini setelah admin menerbitkannya. Sementara itu, lihat kategori lain.</p>
                <a class="btn btn-red" href="informasi.php?kategori=sop">Buka kategori SOP</a>
            </div>
            <?php endif; ?>
        </main>

        <!-- ============ KOLOM KANAN: TOC ============ -->
        <?php if (!empty($artikel)): ?>
        <aside class="rail" aria-label="Di halaman ini">
            <nav class="toc" data-toc aria-labelledby="lbl-toc">
                <span class="side-label" id="lbl-toc">Di halaman ini</span>
                <ul>
                    <?php if ($featured): ?>
                    <li>
                        <a href="#artikel-1" title="<?php echo htmlspecialchars($featured['judul']); ?>"
                            aria-current="true">
                            <?php echo htmlspecialchars(mb_strimwidth($featured['judul'], 0, 38, '…')); ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php foreach ($sisanya as $i => $a): $idx = $i + 2; ?>
                    <li>
                        <a href="#artikel-<?php echo $idx; ?>" title="<?php echo htmlspecialchars($a['judul']); ?>">
                            <?php echo htmlspecialchars(mb_strimwidth($a['judul'], 0, 38, '…')); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <a class="toc-top" href="#top">
                    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>Kembali ke atas
                </a>
            </nav>
        </aside>
        <?php endif; ?>

    </div>
</div>

<?php include 'includes/footer_guest.php'; ?>

<script src="<?php echo APP_URL; ?>assets/js/docs.js" defer></script>