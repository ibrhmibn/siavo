<?php
$page_title = 'Dashboard';
$page_active = 'dashboard';
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'mahasiswa') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$user_data = getUserData($user_id);

// Statistik
$stats = ['total'=>0,'pengajuan'=>0,'verifikasi'=>0,'tindak_lanjut'=>0,'selesai'=>0];
$q = [
    'total'          => "SELECT COUNT(*) c FROM laporan WHERE user_id = $user_id",
    'pengajuan'      => "SELECT COUNT(*) c FROM laporan WHERE user_id = $user_id AND status='pengajuan'",
    'verifikasi'     => "SELECT COUNT(*) c FROM laporan WHERE user_id = $user_id AND status='verifikasi'",
    'tindak_lanjut'  => "SELECT COUNT(*) c FROM laporan WHERE user_id = $user_id AND status='tindak_lanjut'",
    'selesai'        => "SELECT COUNT(*) c FROM laporan WHERE user_id = $user_id AND status='selesai'"
];
foreach ($q as $k => $sql) { $r = $conn->query($sql); if ($r) $stats[$k] = (int)$r->fetch_assoc()['c']; }

// Laporan terbaru
$laporan_terbaru = [];
$stmt = $conn->prepare("SELECT l.*, k.nama_kategori FROM laporan l LEFT JOIN kategori k ON l.kategori_id=k.id WHERE l.user_id=? ORDER BY l.created_at DESC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $laporan_terbaru[] = $row;
$stmt->close();

include 'includes/sidebar.php';
?>

<div class="section-header">
    <div>
        <h1>Dashboard</h1>
        <p>Ringkasan aktivitas aspirasi Anda</p>
    </div>
</div>

<!-- Info Mahasiswa -->
<div class="card" style="margin-bottom:22px">
    <div class="card-body">
        <div class="info-user-grid">

            <!-- Row 1: Nama, NIM, Prodi -->
            <div class="info-item">
                <div class="info-label"><i class="fas fa-user"></i>Nama Lengkap</div>
                <div class="info-value"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><i class="fas fa-id-card"></i>NIM</div>
                <div class="info-value"><?php echo htmlspecialchars($user_data['nim']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><i class="fas fa-graduation-cap"></i>Program Studi</div>
                <div class="info-value"><?php echo htmlspecialchars($user_data['prodi']); ?></div>
            </div>

            <!-- Row 2: Email, Kontak, Status -->
            <div class="info-item">
                <div class="info-label"><i class="fas fa-envelope"></i>Email</div>
                <div class="info-value muted"><?php echo htmlspecialchars($user_data['email']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><i class="fas fa-phone"></i>Kontak</div>
                <div class="info-value muted"><?php echo htmlspecialchars($user_data['kontak']); ?></div>
            </div>

            <div class="info-item">
                <div class="info-label"><i class="fas fa-shield-alt"></i>Status</div>
                <span class="badge-status selesai">Aktif</span>
            </div>

        </div>
    </div>
</div>

<!-- Statistik -->
<h3 style="font-family:var(--display);font-size:1.1rem;font-weight:700">
    <i class="fas fa-chart-bar" style="color:var(--red);margin-right:6px"></i>Statistik Laporan Saya
</h3>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-number"><?php echo $stats['total']; ?></div>
        <div class="stat-label">Total Aspirasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-primary"><i class="fas fa-paper-plane"></i></div>
        <div class="stat-number"><?php echo $stats['pengajuan']; ?></div>
        <div class="stat-label">Pengajuan</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-warning"><i class="fas fa-magnifying-glass"></i></div>
        <div class="stat-number"><?php echo $stats['verifikasi']; ?></div>
        <div class="stat-label">Verifikasi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-info"><i class="fas fa-screwdriver-wrench"></i></div>
        <div class="stat-number"><?php echo $stats['tindak_lanjut']; ?></div>
        <div class="stat-label">Tindak Lanjut</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon text-success"><i class="fas fa-circle-check"></i></div>
        <div class="stat-number"><?php echo $stats['selesai']; ?></div>
        <div class="stat-label">Selesai</div>
    </div>
</div>

<!-- Laporan Terbaru -->
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <span><i class="fas fa-list-ul"></i>Aspirasi Terbaru</span>
        <a href="<?php echo APP_URL; ?>mahasiswa/laporan-saya.php" class="btn btn-outline-primary btn-sm">
            Lihat Semua
        </a>
    </div>
    <div class="card-body" style="padding:0">
        <?php if (empty($laporan_terbaru)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Belum ada aspirasi yang Anda kirimkan.</p>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width:130px">No. Tiket</th>
                        <th style="width:180px">Kategori</th>
                        <th>Isi Singkat</th>
                        <th style="width:110px">Status</th>
                        <th style="width:110px">Tanggal</th>
                        <th style="width:130px;text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($laporan_terbaru as $l): ?>
                    <tr>
                        <td class="col-ticket"><?php echo htmlspecialchars($l['nomor_tiket']); ?></td>
                        <td><?php echo htmlspecialchars($l['nama_kategori']); ?></td>
                        <td class="col-isi">
                            <?php echo htmlspecialchars(mb_strimwidth($l['isi'], 0, 50, '...')); ?>
                        </td>
                        <td class="col-status">
                            <span class="badge-status <?php echo $l['status']; ?>">
                                <?php echo getStatusLabel($l['status']); ?>
                            </span>
                        </td>
                        <td class="col-tanggal">
                            <?php echo date('d/m/Y', strtotime($l['created_at'])); ?>
                        </td>
                        <td class="col-aksi">
                            <a href="<?php echo APP_URL; ?>mahasiswa/tracking.php?ticket=<?php echo $l['nomor_tiket']; ?>"
                                class="btn-icon-modern" title="Lacak Laporan" aria-label="Lacak Laporan">
                                <i class="fas fa-search"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>