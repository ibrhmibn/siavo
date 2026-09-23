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

        if (!empty($laporan['file_feedback'])) {
            $laporan['url_feedback'] = getFileUrl($laporan['file_feedback']);
        }

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

// ============================================
// FALLBACK JUDUL
// ============================================
$judul_tampil = '';
if ($laporan) {
    $judul_tampil = $laporan['judul'] ?? '';
    if (empty($judul_tampil) || $judul_tampil === '0') {
        $judul_tampil = mb_strimwidth(strip_tags($laporan['isi'] ?? ''), 0, 60, '...');
    }
    if (empty($judul_tampil)) {
        $judul_tampil = '(Tanpa judul)';
    }
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

                <!-- Judul Laporan -->
                <h3 class="detail-title" style="margin-bottom:20px">
                    <?php echo htmlspecialchars($judul_tampil); ?>
                </h3>

                <!-- Identitas Pelapor -->
                <h4 class="detail-section-title">Informasi Pelapor</h4>
                <div class="detail-list">
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-user"></i>Nama Lengkap</div>
                        <div class="detail-value">
                            <?php echo htmlspecialchars($laporan['nama_pelapor'] ?? 'Anonim'); ?></div>
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

                <!-- ============================================ -->
                <!-- BUKTI FORMAL DARI ADMIN                       -->
                <!-- ============================================ -->
                <?php if (!empty($laporan['file_feedback'])): ?>
                <div style="margin-top:24px">
                    <h4 class="detail-section-title" style="color:var(--green)">
                        <i class="fas fa-file-pdf"></i> Bukti Formal dari Admin
                    </h4>

                    <div class="feedback-user-box">
                        <div class="feedback-user-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="feedback-user-info">
                            <div class="feedback-user-title">
                                Bukti Formal — <?php echo htmlspecialchars($laporan['nomor_tiket']); ?>.pdf
                            </div>
                            <div class="feedback-user-meta">
                                <i class="far fa-calendar"></i>
                                Diunggah: <?php echo date('d M Y · H:i', strtotime($laporan['updated_at'])); ?> WIB
                            </div>
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr auto;gap:8px;margin-top:14px">
                        <a href="<?php echo htmlspecialchars($laporan['url_feedback']); ?>" target="_blank"
                            class="btn btn-primary" style="padding:12px 20px;font-weight:700">
                            <i class="fas fa-eye"></i> Lihat Bukti Formal
                        </a>
                        <a href="<?php echo htmlspecialchars($laporan['url_feedback']); ?>" download
                            class="btn-icon-modern" title="Download PDF">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>

                    <div class="feedback-user-hint">
                        <i class="fas fa-info-circle"></i>
                        Dokumen ini merupakan tanggapan resmi dari pihak kampus.
                    </div>
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

/* Bukti formal box di sisi mahasiswa */
.feedback-user-box {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px;
    background: var(--green-soft);
    border: 1px solid var(--green);
    border-radius: 14px;
}

.feedback-user-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--green);
    color: #fff;
    display: grid;
    place-items: center;
    font-size: 1.25rem;
    flex: none;
}

.feedback-user-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.feedback-user-title {
    font-family: var(--display);
    font-weight: 700;
    font-size: .88rem;
    color: var(--green);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.feedback-user-meta {
    font-size: .74rem;
    color: var(--ink-2);
    display: flex;
    align-items: center;
    gap: 5px;
}

.feedback-user-meta i {
    color: var(--red)
}

.feedback-user-hint {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    margin-top: 10px;
    font-size: .72rem;
    color: var(--ink-2);
    opacity: .75;
}

.feedback-user-hint i {
    color: var(--red)
}
</style>

<?php include '../mahasiswa/includes/footer.php'; ?>