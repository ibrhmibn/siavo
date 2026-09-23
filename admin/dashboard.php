<?php
$page_title = 'Dashboard';
$page_active = 'dashboard';

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'admin') {
    redirect('../login.php');
}

// AUTO-CLEANUP kegiatan lewat > 2 hari
$auto_deleted_kegiatan = autoCleanupKegiatan();

// Statistik
$stats = ['total'=>0,'pengajuan'=>0,'verifikasi'=>0,'tindak_lanjut'=>0,'selesai'=>0];
$q = [
    'total'          => "SELECT COUNT(*) c FROM laporan",
    'pengajuan'      => "SELECT COUNT(*) c FROM laporan WHERE status='pengajuan'",
    'verifikasi'     => "SELECT COUNT(*) c FROM laporan WHERE status='verifikasi'",
    'tindak_lanjut'  => "SELECT COUNT(*) c FROM laporan WHERE status='tindak_lanjut'",
    'selesai'        => "SELECT COUNT(*) c FROM laporan WHERE status='selesai'"
];
foreach ($q as $k => $sql) { $r = $conn->query($sql); if ($r) $stats[$k] = (int)$r->fetch_assoc()['c']; }

// Recent reports
$reports = [];
$stmt = $conn->prepare("SELECT l.*, k.nama_kategori FROM laporan l LEFT JOIN kategori k ON l.kategori_id=k.id ORDER BY l.created_at DESC LIMIT 20");
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $reports[] = $row;
$stmt->close();

include 'includes/sidebar.php';
?>

<div class="page-head">
    <div>
        <h1>Dashboard Verifikasi</h1>
        <p>Pantau dan kelola semua laporan aspirasi mahasiswa</p>
    </div>
    <div class="page-meta">
        <i class="fas fa-clock"></i>
        <span>Update: <?php echo date('d/m/Y H:i'); ?> WIB</span>
    </div>
</div>

<?php if ($auto_deleted_kegiatan > 0): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-broom me-1"></i>
    <strong><?php echo $auto_deleted_kegiatan; ?></strong> kegiatan lama otomatis dihapus (lewat > 2 hari).
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card-modern border-blue">
        <div>
            <div class="stat-label">Pengajuan Baru</div>
            <div class="stat-number"><?php echo $stats['pengajuan']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-message"></i></div>
    </div>
    <div class="stat-card-modern border-yellow">
        <div>
            <div class="stat-label">Dalam Verifikasi</div>
            <div class="stat-number"><?php echo $stats['verifikasi']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
    </div>
    <div class="stat-card-modern border-cyan">
        <div>
            <div class="stat-label">Tindak Lanjut</div>
            <div class="stat-number"><?php echo $stats['tindak_lanjut']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card-modern border-green">
        <div>
            <div class="stat-label">Selesai</div>
            <div class="stat-number"><?php echo $stats['selesai']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
    </div>
</div>

<div class="table-modern-wrapper">
    <div class="table-header">
        <div class="title"><i class="fas fa-list"></i>Daftar Laporan</div>
        <div class="filter-tabs">
            <button class="tab-btn active" data-filter="all">Semua</button>
            <button class="tab-btn" data-filter="pengajuan">Pengajuan</button>
            <button class="tab-btn" data-filter="verifikasi">Verifikasi</button>
            <button class="tab-btn" data-filter="tindak_lanjut">Tindak Lanjut</button>
            <button class="tab-btn" data-filter="selesai">Selesai</button>
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
            <tbody id="reportTableBody">
                <?php if (empty($reports)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding:60px 24px;color:var(--ink-2)">
                        <i class="fas fa-inbox"
                            style="font-size:2rem;display:block;margin-bottom:12px;color:var(--line)"></i>
                        Belum ada data laporan
                    </td>
                </tr>
                <?php else: $no=1; foreach ($reports as $r): ?>
                <tr data-status="<?php echo $r['status']; ?>">
                    <td style="color:var(--ink-2)"><?php echo $no++; ?></td>
                    <td><span
                            style="font-weight:700;color:var(--ink)"><?php echo htmlspecialchars($r['nomor_tiket']); ?></span>
                    </td>
                    <td>
                        <div class="cell-name"><?php echo htmlspecialchars($r['nama_pelapor'] ?? 'Anonim'); ?></div>
                        <div class="cell-nim"><?php echo htmlspecialchars($r['nim']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($r['nama_kategori'] ?? '-'); ?></td>
                    <td><?php echo getStatusBadgeModern($r['status']); ?></td>
                    <td><?php echo getPriorityBadgeModern($r['prioritas']); ?></td>
                    <td style="color:var(--ink-2);font-size:.8rem">
                        <?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                    <td class="text-center">
                        <a href="laporan-detail.php?id=<?php echo $r['id']; ?>" class="btn-icon-modern"
                            title="Detail Laporan">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <span>Menampilkan <strong style="color:var(--ink)" id="rowCount"><?php echo count($reports); ?></strong>
            data</span>
        <div class="pagination">
            <button disabled><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    /* FILTER TABS */
    var tabs = document.querySelectorAll('.filter-tabs .tab-btn');
    var rows = document.querySelectorAll('#reportTableBody tr[data-status]');
    var rowCount = document.getElementById('rowCount');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) {
                t.classList.remove('active');
            });
            this.classList.add('active');
            var f = this.dataset.filter,
                n = 0;
            rows.forEach(function(row) {
                if (f === 'all' || row.dataset.status === f) {
                    row.style.display = '';
                    n++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (rowCount) rowCount.textContent = n;
        });
    });
});
</script>

<?php include 'includes/sidebar-footer.php'; ?>