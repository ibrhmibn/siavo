<?php
$page_title = 'Laporan Saya';
$page_active = 'laporan-saya';
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'mahasiswa') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$laporan = [];

$stmt = $conn->prepare("
    SELECT l.*, k.nama_kategori 
    FROM laporan l 
    LEFT JOIN kategori k ON l.kategori_id = k.id 
    WHERE l.user_id = ? 
    ORDER BY l.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $laporan[] = $row;
$stmt->close();

include '../mahasiswa/includes/sidebar.php';
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-list-ul"></i> Laporan Saya</h1>
        <p>Daftar semua aspirasi yang pernah Anda kirimkan</p>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0">
        <?php if (empty($laporan)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <p>Anda belum memiliki laporan.</p>
            <a href="<?php echo APP_URL; ?>mahasiswa/aspirasi.php" class="btn btn-primary btn-sm"
                style="margin-top:16px">
                <i class="fas fa-pen-nib"></i> Kirim Aspirasi Sekarang
            </a>
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
                    <?php foreach ($laporan as $l): ?>
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