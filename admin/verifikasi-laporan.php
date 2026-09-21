<?php
$page_title = 'Verifikasi Laporan';

$status_filter = $_GET['status'] ?? 'semua';
$allowed_status = ['semua', 'pengajuan', 'verifikasi', 'tindak_lanjut', 'selesai'];
if (!in_array($status_filter, $allowed_status)) {
    $status_filter = 'semua';
}

if ($status_filter == 'semua') {
    $page_active = 'verifikasi';
} elseif ($status_filter == 'pengajuan') {
    $page_active = 'pengajuan';
} elseif ($status_filter == 'verifikasi') {
    $page_active = 'verifikasi';
} elseif ($status_filter == 'tindak_lanjut') {
    $page_active = 'tindak_lanjut';
} elseif ($status_filter == 'selesai') {
    $page_active = 'selesai';
} else {
    $page_active = 'verifikasi';
}

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

if ($status_filter == 'semua') {
    $sql = "SELECT l.*, k.nama_kategori, u.nama_lengkap as pelapor, u.nim as nim_pelapor 
            FROM laporan l 
            LEFT JOIN kategori k ON l.kategori_id = k.id 
            LEFT JOIN users u ON l.user_id = u.id 
            ORDER BY l.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
} else {
    $sql = "SELECT l.*, k.nama_kategori, u.nama_lengkap as pelapor, u.nim as nim_pelapor 
            FROM laporan l 
            LEFT JOIN kategori k ON l.kategori_id = k.id 
            LEFT JOIN users u ON l.user_id = u.id 
            WHERE l.status = ? 
            ORDER BY l.created_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
}

$result = $stmt->get_result();
$laporan = [];
while ($row = $result->fetch_assoc()) {
    $laporan[] = $row;
}
$stmt->close();

$stats = [
    'total' => 0,
    'pengajuan' => 0,
    'verifikasi' => 0,
    'tindak_lanjut' => 0,
    'selesai' => 0
];

$queries = [
    'total' => "SELECT COUNT(*) as count FROM laporan",
    'pengajuan' => "SELECT COUNT(*) as count FROM laporan WHERE status = 'pengajuan'",
    'verifikasi' => "SELECT COUNT(*) as count FROM laporan WHERE status = 'verifikasi'",
    'tindak_lanjut' => "SELECT COUNT(*) as count FROM laporan WHERE status = 'tindak_lanjut'",
    'selesai' => "SELECT COUNT(*) as count FROM laporan WHERE status = 'selesai'"
];
foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $stats[$key] = $row['count'];
    }
}

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Verifikasi Laporan</h1>
            <p class="text-muted small mb-0">Kelola dan verifikasi semua laporan aspirasi mahasiswa</p>
        </div>
        <div class="text-muted small d-flex align-items-center gap-2 mt-2 mt-md-0">
            <i class="fas fa-filter"></i>
            <span>Filter: <strong><?php echo ucfirst(str_replace('_', ' ', $status_filter)); ?></strong></span>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card-modern border-blue">
            <div class="stat-left">
                <div class="stat-label">Total Laporan</div>
                <div class="stat-number"><?php echo $stats['total']; ?></div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-file-lines"></i></div>
        </div>
        <div class="stat-card-modern border-blue">
            <div class="stat-left">
                <div class="stat-label">Pengajuan</div>
                <div class="stat-number"><?php echo $stats['pengajuan']; ?></div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-message"></i></div>
        </div>
        <div class="stat-card-modern border-yellow">
            <div class="stat-left">
                <div class="stat-label">Verifikasi</div>
                <div class="stat-number"><?php echo $stats['verifikasi']; ?></div>
            </div>
            <div class="stat-icon yellow"><i class="fas fa-triangle-exclamation"></i></div>
        </div>
        <div class="stat-card-modern border-cyan">
            <div class="stat-left">
                <div class="stat-label">Tindak Lanjut</div>
                <div class="stat-number"><?php echo $stats['tindak_lanjut']; ?></div>
            </div>
            <div class="stat-icon cyan"><i class="fas fa-clock"></i></div>
        </div>
        <div class="stat-card-modern border-green">
            <div class="stat-left">
                <div class="stat-label">Selesai</div>
                <div class="stat-number"><?php echo $stats['selesai']; ?></div>
            </div>
            <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>

    <div class="table-modern-wrapper">
        <div class="table-header">
            <div class="title"><i class="fas fa-list-ul me-2 text-primary"></i>Daftar Laporan</div>
            <div class="filter-tabs">
                <a href="?status=semua"
                    class="tab-btn <?php echo $status_filter == 'semua' ? 'active' : ''; ?>">Semua</a>
                <a href="?status=pengajuan"
                    class="tab-btn <?php echo $status_filter == 'pengajuan' ? 'active' : ''; ?>">Pengajuan</a>
                <a href="?status=verifikasi"
                    class="tab-btn <?php echo $status_filter == 'verifikasi' ? 'active' : ''; ?>">Verifikasi</a>
                <a href="?status=tindak_lanjut"
                    class="tab-btn <?php echo $status_filter == 'tindak_lanjut' ? 'active' : ''; ?>">Tindak Lanjut</a>
                <a href="?status=selesai"
                    class="tab-btn <?php echo $status_filter == 'selesai' ? 'active' : ''; ?>">Selesai</a>
            </div>
        </div>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th style="width:50px">No</th>
                        <th>Nomor Tiket</th>
                        <th>Pelapor</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Prioritas</th>
                        <th>Tanggal</th>
                        <th style="width:90px" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($laporan)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-file-lines fa-2x d-block mb-2 text-secondary" style="font-size: 2rem;"></i>
                            Tidak ada laporan dengan status ini
                        </td>
                    </tr>
                    <?php else: 
                        $no = 1;
                        foreach ($laporan as $l): 
                    ?>
                    <tr>
                        <td class="text-muted" style="text-align:center"><?php echo $no++; ?></td>
                        <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($l['nomor_tiket']); ?></span>
                        </td>
                        <td>
                            <div class="cell-name"><?php echo htmlspecialchars($l['pelapor'] ?? 'Anonim'); ?></div>
                            <div class="cell-nim"><?php echo htmlspecialchars($l['nim']); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($l['nama_kategori'] ?? '-'); ?></td>
                        <td><?php echo getStatusBadgeModern($l['status']); ?></td>
                        <td><?php echo getPriorityBadgeModern($l['prioritas']); ?></td>
                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($l['created_at'])); ?></td>
                        <td class="text-center">
                            <a href="laporan-detail.php?id=<?php echo $l['id']; ?>" class="btn-icon-modern"
                                title="Detail Laporan">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Menampilkan <span class="fw-bold text-dark"><?php echo count($laporan); ?></span> data</span>
            <div class="pagination">
                <button disabled><i class="fas fa-chevron-left"></i></button>
                <button class="active">1</button>
                <button><i class="fas fa-chevron-right"></i></button>
            </div>
        </div>
    </div>
</div>

<?php include '../admin/includes/sidebar-footer.php'; ?>