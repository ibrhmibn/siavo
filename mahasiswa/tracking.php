<?php
$page_title = 'Tracking Laporan';
$page_active = 'tracking';
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'mahasiswa') {
    redirect('../login.php');
}

$ticket_number = $_GET['ticket'] ?? '';
$laporan = null;
$riwayat = [];
$error = '';

if (!empty($ticket_number)) {
    $ticket_number = sanitize($ticket_number);

    $stmt = $conn->prepare("
        SELECT l.*, k.nama_kategori, u.email 
        FROM laporan l 
        LEFT JOIN kategori k ON l.kategori_id = k.id 
        LEFT JOIN users u ON l.user_id = u.id 
        WHERE l.nomor_tiket = ? AND l.user_id = ?
    ");
    $stmt->bind_param("si", $ticket_number, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $laporan = $result->fetch_assoc();

        $stmt_riwayat = $conn->prepare("
            SELECT r.*, u.nama_lengkap as petugas 
            FROM riwayat_status r 
            LEFT JOIN users u ON r.petugas_id = u.id 
            WHERE r.laporan_id = ? 
            ORDER BY r.created_at ASC
        ");
        $stmt_riwayat->bind_param("i", $laporan['id']);
        $stmt_riwayat->execute();
        $result_riwayat = $stmt_riwayat->get_result();
        while ($row = $result_riwayat->fetch_assoc()) $riwayat[] = $row;
        $stmt_riwayat->close();
    } else {
        $error = 'Nomor tiket tidak ditemukan atau bukan milik Anda.';
    }
    $stmt->close();
}

include '../mahasiswa/includes/sidebar.php';
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-search"></i> Tracking Laporan</h1>
        <p>Pantau progress laporan Anda secara real-time</p>
    </div>
</div>

<div class="card" style="margin-bottom:22px">
    <div class="card-body">
        <form method="GET">
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <input type="text" name="ticket" class="form-control"
                    placeholder="Masukkan nomor tiket (contoh: SIAVO-A7F3D2)"
                    value="<?php echo htmlspecialchars($ticket_number); ?>" required style="flex:1;min-width:220px">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Cari
                </button>
            </div>
        </form>

        <?php if ($error): ?>
        <div class="alert alert-danger" style="margin-top:16px;margin-bottom:0">
            <i class="fas fa-circle-exclamation"></i>
            <span><?php echo $error; ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($laporan): ?>
<div class="tracking-grid">

    <!-- KIRI: Detail -->
    <div class="tracking-detail">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-alt"></i> Detail Laporan
            </div>
            <div class="card-body">

                <!-- Identitas Pelapor -->
                <h4 class="detail-section-title">Informasi Pelapor</h4>
                <div class="detail-list">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-user"></i>Nama Lengkap</div>
                        <div class="detail-value"><?php echo htmlspecialchars($laporan['nama_pelapor'] ?? 'Anonim'); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-id-card"></i>NIM</div>
                        <div class="detail-value"><?php echo htmlspecialchars($laporan['nim']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-graduation-cap"></i>Program Studi</div>
                        <div class="detail-value"><?php echo htmlspecialchars($laporan['prodi']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-shield-alt"></i>Status Mahasiswa</div>
                        <span class="badge-status selesai">Aktif</span>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-envelope"></i>Email</div>
                        <div class="detail-value muted">
                            <?php echo !empty($laporan['email']) ? htmlspecialchars($laporan['email']) : '-'; ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-phone"></i>Kontak</div>
                        <div class="detail-value muted"><?php echo htmlspecialchars($laporan['kontak']); ?></div>
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">

                <!-- Info Laporan -->
                <h4 class="detail-section-title">Informasi Laporan</h4>
                <div class="detail-list">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-ticket"></i>Nomor Tiket</div>
                        <div class="detail-value"><?php echo htmlspecialchars($laporan['nomor_tiket']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-folder"></i>Kategori</div>
                        <div class="detail-value"><?php echo htmlspecialchars($laporan['nama_kategori']); ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-calendar-alt"></i>Tanggal Laporan</div>
                        <div class="detail-value">
                            <?php echo date('d/m/Y H:i', strtotime($laporan['created_at'])); ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-flag"></i>Status</div>
                        <span class="badge-status <?php echo $laporan['status']; ?>">
                            <?php echo getStatusLabel($laporan['status']); ?>
                        </span>
                    </div>
                </div>

                <hr style="border:0;border-top:1px solid var(--line);margin:22px 0">

                <!-- Isi Laporan -->
                <h4 class="detail-section-title">Isi Laporan</h4>
                <div class="content-block">
                    <?php echo nl2br(htmlspecialchars($laporan['isi'])); ?>
                </div>

                <!-- Bukti Formal -->
                <?php if (!empty($laporan['file_feedback'])): ?>
                <div style="margin-top:20px">
                    <div class="detail-label" style="margin-bottom:8px">
                        <i class="fas fa-file-pdf" style="color:var(--red)"></i>
                        Bukti Formal
                    </div>
                    <a href="<?php echo $laporan['file_feedback']; ?>" target="_blank"
                        class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <!-- KANAN: Riwayat -->
    <div class="tracking-history">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-clock-rotate-left"></i> Riwayat Progress
            </div>
            <div class="card-body">
                <?php if (empty($riwayat)): ?>
                <div class="empty-state" style="padding:32px 16px">
                    <i class="fas fa-clock" style="font-size:1.8rem"></i>
                    <p>Belum ada riwayat</p>
                </div>
                <?php else: ?>
                <div class="timeline">
                    <?php foreach ($riwayat as $r): ?>
                    <div class="timeline-item status-<?php echo $r['status']; ?>">
                        <div class="timeline-content">
                            <strong><?php echo getStatusLabel($r['status']); ?></strong>
                            <?php if (!empty($r['keterangan'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($r['keterangan'])); ?></p>
                            <?php endif; ?>
                            <div
                                style="display:flex;justify-content:space-between;gap:8px;margin-top:8px;flex-wrap:wrap">
                                <small>
                                    <?php if (!empty($r['petugas'])): ?>
                                    <i class="fas fa-user-shield"></i>
                                    Oleh: <?php echo htmlspecialchars($r['petugas']); ?>
                                    <?php endif; ?>
                                </small>
                                <small>
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>
<?php endif; ?>

<style>
.detail-section-title {
    font-family: var(--display);
    font-weight: 700;
    font-size: .9rem;
    color: var(--ink);
    margin-bottom: 14px;
    padding-bottom: 8px;
    border-bottom: 2px solid var(--red);
    display: inline-block;
}
</style>

<?php include '../mahasiswa/includes/footer.php'; ?>