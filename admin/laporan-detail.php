<?php
$page_title = 'Detail Laporan';
$page_active = 'verifikasi';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('verifikasi-laporan.php');
}

// ============================================
// AMBIL DATA LAPORAN
// ============================================
$stmt = $conn->prepare("
    SELECT l.*, k.nama_kategori, 
           u.nama_lengkap as pelapor_terbaru, u.nim as nim_terbaru,
           u.kontak as kontak_terbaru, u.prodi as prodi_terbaru
    FROM laporan l 
    LEFT JOIN kategori k ON l.kategori_id = k.id 
    LEFT JOIN users u ON l.user_id = u.id 
    WHERE l.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$laporan) {
    redirect('verifikasi-laporan.php');
}

// Override dengan data terbaru dari users
if ($laporan['user_id']) {
    $laporan['pelapor']        = $laporan['pelapor_terbaru']  ?? $laporan['nama_pelapor'];
    $laporan['nim_pelapor']    = $laporan['nim_terbaru']      ?? $laporan['nim'];
    $laporan['kontak_pelapor'] = $laporan['kontak_terbaru']   ?? $laporan['kontak'];
    $laporan['prodi_pelapor']  = $laporan['prodi_terbaru']    ?? $laporan['prodi'];
} else {
    $laporan['pelapor']        = $laporan['nama_pelapor'] ?? 'Anonim';
    $laporan['nim_pelapor']    = $laporan['nim'] ?? '';
    $laporan['kontak_pelapor'] = $laporan['kontak'] ?? '';
    $laporan['prodi_pelapor']  = $laporan['prodi'] ?? '';
}

// ============================================
// DOKUMEN
// ============================================
$dokumen = [];
$stmt = $conn->prepare("SELECT * FROM dokumen_pendukung WHERE laporan_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $dokumen[] = $row;
$stmt->close();

// ============================================
// RIWAYAT
// ============================================
$riwayat = [];
$stmt = $conn->prepare("
    SELECT r.*, u.nama_lengkap as petugas 
    FROM riwayat_status r 
    LEFT JOIN users u ON r.petugas_id = u.id 
    WHERE r.laporan_id = ? 
    ORDER BY r.created_at ASC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) $riwayat[] = $row;
$stmt->close();

// ============================================
// PROSES UPDATE STATUS
// ============================================
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid';
    } else {
        $action     = $_POST['action'];
        $keterangan = sanitize($_POST['keterangan'] ?? '');
        $petugas_id = $_SESSION['user_id'] ?? 0;

        if ($petugas_id == 0) {
            $error = 'Anda harus login terlebih dahulu.';
        }

        if (empty($error)) {
            $status_map = [
                'pengajuan'     => 'pengajuan',
                'verifikasi'    => 'verifikasi',
                'tindak_lanjut' => 'tindak_lanjut',
                'selesai'       => 'selesai'
            ];

            if (!isset($status_map[$action])) {
                $error = 'Aksi tidak dikenali';
            } else {
                $status = $status_map[$action];
                $ket = $keterangan ?: 'Status diperbarui menjadi ' . getStatusLabel($status);
            }
        }

        if (empty($error)) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("UPDATE laporan SET status = ? WHERE id = ?");
                $stmt->bind_param("si", $status, $id);
                if (!$stmt->execute()) throw new Exception('Gagal update laporan: ' . $stmt->error);
                $stmt->close();

                $stmt = $conn->prepare("INSERT INTO riwayat_status (laporan_id, status, keterangan, petugas_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("issi", $id, $status, $ket, $petugas_id);
                if (!$stmt->execute()) throw new Exception('Gagal insert riwayat: ' . $stmt->error);
                $stmt->close();

                $conn->commit();
                $success = true;

                // Refresh data
                $stmt = $conn->prepare("
                    SELECT l.*, k.nama_kategori, 
                           u.nama_lengkap as pelapor_terbaru, u.nim as nim_terbaru,
                           u.kontak as kontak_terbaru, u.prodi as prodi_terbaru
                    FROM laporan l 
                    LEFT JOIN kategori k ON l.kategori_id = k.id 
                    LEFT JOIN users u ON l.user_id = u.id 
                    WHERE l.id = ?
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $laporan = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($laporan['user_id']) {
                    $laporan['pelapor']        = $laporan['pelapor_terbaru']  ?? $laporan['nama_pelapor'];
                    $laporan['nim_pelapor']    = $laporan['nim_terbaru']      ?? $laporan['nim'];
                    $laporan['kontak_pelapor'] = $laporan['kontak_terbaru']   ?? $laporan['kontak'];
                    $laporan['prodi_pelapor']  = $laporan['prodi_terbaru']    ?? $laporan['prodi'];
                } else {
                    $laporan['pelapor']        = $laporan['nama_pelapor'] ?? 'Anonim';
                    $laporan['nim_pelapor']    = $laporan['nim'] ?? '';
                    $laporan['kontak_pelapor'] = $laporan['kontak'] ?? '';
                    $laporan['prodi_pelapor']  = $laporan['prodi'] ?? '';
                }

                $riwayat = [];
                $stmt = $conn->prepare("
                    SELECT r.*, u.nama_lengkap as petugas 
                    FROM riwayat_status r 
                    LEFT JOIN users u ON r.petugas_id = u.id 
                    WHERE r.laporan_id = ? 
                    ORDER BY r.created_at ASC
                ");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) $riwayat[] = $row;
                $stmt->close();
            } catch (Exception $e) {
                $conn->rollback();
                $error = $e->getMessage();
            }
        }
    }
}

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <!-- ============================================
         PAGE HEADER
         ============================================ -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold" style="color: var(--siavo-red-heading) !important;">Detail Laporan</h1>
            <p class="text-muted small mb-0">Kelola status dan dokumen laporan aspirasi mahasiswa</p>
        </div>
        <a href="verifikasi-laporan.php" class="btn-back-modern">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-exclamation me-1"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> Status berhasil diperbarui!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- ============================================
             KIRI: DETAIL LAPORAN + DOKUMEN
             ============================================ -->
        <div class="col-lg-7">

            <!-- CARD: DETAIL LAPORAN -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-circle-info"></i>
                    <h5>Detail Laporan</h5>
                    <span class="ms-auto"><?php echo getStatusBadgeModern($laporan['status']); ?></span>
                </div>
                <div class="card-modern-body">

                    <!-- Nomor Tiket Highlight -->
                    <div class="ticket-highlight">
                        <div class="ticket-highlight-label">
                            <i class="fas fa-ticket"></i> Nomor Tiket
                        </div>
                        <div class="ticket-highlight-value">
                            <?php echo htmlspecialchars($laporan['nomor_tiket']); ?>
                        </div>
                    </div>

                    <!-- Grid Info -->
                    <div class="info-grid">
                        <div>
                            <span class="label">Pelapor</span>
                            <span class="value"><?php echo htmlspecialchars($laporan['pelapor'] ?? 'Anonim'); ?></span>
                        </div>
                        <?php if (!empty($laporan['nim_pelapor'])): ?>
                        <div>
                            <span class="label">NIM</span>
                            <span class="value"><?php echo htmlspecialchars($laporan['nim_pelapor']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <span class="label">Kategori</span>
                            <span class="value"><?php echo htmlspecialchars($laporan['nama_kategori'] ?? '-'); ?></span>
                        </div>
                        <div>
                            <span class="label">Prioritas</span>
                            <span class="value"><?php echo getPriorityBadgeModern($laporan['prioritas']); ?></span>
                        </div>
                        <?php if (!empty($laporan['prodi_pelapor'])): ?>
                        <div>
                            <span class="label">Program Studi</span>
                            <span class="value"><?php echo htmlspecialchars($laporan['prodi_pelapor']); ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($laporan['kontak_pelapor'])): ?>
                        <div>
                            <span class="label">Kontak</span>
                            <span class="value"><?php echo htmlspecialchars($laporan['kontak_pelapor']); ?></span>
                        </div>
                        <?php endif; ?>
                        <div>
                            <span class="label">Tanggal Laporan</span>
                            <span
                                class="value"><?php echo date('d/m/Y H:i', strtotime($laporan['created_at'])); ?></span>
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="section-label">Judul Laporan</div>
                    <div class="content-title-box"><?php echo htmlspecialchars($laporan['judul'] ?? '(Tanpa judul)'); ?>
                    </div>

                    <!-- Isi -->
                    <div class="section-label">Isi Laporan</div>
                    <div class="content-text"><?php echo nl2br(htmlspecialchars($laporan['isi'])); ?></div>

                    <!-- Feedback Admin (kalau selesai) -->
                    <?php if ($laporan['status'] == 'selesai' && !empty($laporan['feedback_admin'])): ?>
                    <div class="section-label">Feedback Admin</div>
                    <div class="content-text"
                        style="border-left-color:var(--green);background:var(--green-soft);color:var(--green)">
                        <?php echo nl2br(htmlspecialchars($laporan['feedback_admin'])); ?>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <!-- CARD: DOKUMEN PENDUKUNG -->
            <?php if (!empty($dokumen)): ?>
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-paperclip"></i>
                    <h5>Dokumen Pendukung</h5>
                    <span class="ms-auto" style="font-size:.78rem;color:var(--ink-2);font-weight:700">
                        <?php echo count($dokumen); ?> file
                    </span>
                </div>
                <div class="card-modern-body">
                    <div class="attachments">
                        <?php foreach ($dokumen as $d): ?>
                        <div class="attachment-item"
                            onclick="window.open('<?php echo APP_URL . $d['path_file']; ?>','_blank')">
                            <i class="fas fa-file-pdf"></i>
                            <div class="attachment-info">
                                <span
                                    class="attachment-name"><?php echo htmlspecialchars($d['nama_file_asli']); ?></span>
                                <span class="attachment-size"><?php echo round($d['ukuran_file'] / 1024); ?> KB</span>
                            </div>
                            <i class="fas fa-download attachment-action"></i>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- ============================================
             KANAN: UPDATE STATUS + BUKTI FORMAL + RIWAYAT
             ============================================ -->
        <div class="col-lg-5">

            <!-- CARD: UPDATE STATUS -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-pencil-square"></i>
                    <h5>Update Status</h5>
                </div>
                <div class="card-modern-body">
                    <form method="POST" id="formUpdateStatus" class="form-modern">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label>Status Saat Ini</label>
                            <div class="status-current-box">
                                <?php echo getStatusBadgeModern($laporan['status']); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Aksi</label>
                            <select name="action" class="form-select" id="actionSelect" required>
                                <option value="">Pilih Aksi</option>
                                <?php if ($laporan['status'] == 'pengajuan'): ?>
                                <option value="verifikasi">Verifikasi</option>
                                <option value="selesai">Selesai (Langsung)</option>
                                <?php endif; ?>
                                <?php if ($laporan['status'] == 'verifikasi'): ?>
                                <option value="tindak_lanjut">Tindak Lanjut</option>
                                <option value="selesai">Selesai (Langsung)</option>
                                <?php endif; ?>
                                <?php if ($laporan['status'] == 'tindak_lanjut'): ?>
                                <option value="selesai">Selesai</option>
                                <?php endif; ?>
                                <?php if ($laporan['status'] == 'selesai'): ?>
                                <option value="" disabled>Sudah Selesai</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="mb-3" id="keteranganDiv" style="display:none;">
                            <label>Keterangan <span class="text-danger">*</span></label>
                            <textarea name="keterangan" class="form-control" rows="3" id="keteranganInput"
                                placeholder="Tambahkan keterangan..."></textarea>
                            <small class="text-muted">Wajib diisi untuk aksi Selesai</small>
                        </div>

                        <button type="submit" class="btn btn-modern-primary w-100">
                            <i class="fas fa-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>

            <!-- CARD: BUKTI FORMAL -->
            <!-- CARD: BUKTI FORMAL -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-file-pdf"></i>
                    <h5>Bukti Formal</h5>
                    <?php if (!empty($laporan['file_feedback'])): ?>
                    <span class="ms-auto feedback-status-badge">
                        <span class="dot"></span>Tersedia
                    </span>
                    <?php endif; ?>
                </div>
                <div class="card-modern-body">

                    <?php if (!empty($laporan['file_feedback'])): ?>

                    <!-- ===== STATE: FILE SUDAH ADA ===== -->
                    <div class="feedback-card">
                        <div class="feedback-card-thumb">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <div class="feedback-card-meta">
                            <div class="feedback-card-name">
                                Feedback_<?php echo htmlspecialchars($laporan['nomor_tiket']); ?>.pdf
                            </div>
                            <div class="feedback-card-date">
                                <i class="far fa-calendar"></i>
                                <?php echo date('d M Y · H:i', strtotime($laporan['updated_at'])); ?> WIB
                            </div>
                        </div>
                    </div>

                    <div class="feedback-actions">
                        <a href="<?php echo $laporan['file_feedback']; ?>" target="_blank"
                            class="btn-feedback btn-feedback-view">
                            <i class="fas fa-eye"></i>
                            <span>Lihat Dokumen</span>
                        </a>
                        <button type="button" class="btn-feedback btn-feedback-delete"
                            onclick="deleteFeedback(<?php echo $laporan['id']; ?>)" title="Hapus bukti formal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <?php else: ?>

                    <!-- ===== STATE: FILE BELUM ADA ===== -->
                    <div class="feedback-empty">
                        <div class="feedback-empty-icon">
                            <i class="fas fa-file-pdf"></i>
                        </div>
                        <p class="feedback-empty-text">
                            Belum ada bukti formal. Upload dokumen PDF dari institusi sebagai tanggapan resmi.
                        </p>
                    </div>

                    <button type="button" class="btn-feedback btn-feedback-upload" data-bs-toggle="modal"
                        data-bs-target="#uploadFeedbackModal">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Bukti Formal (PDF)</span>
                    </button>

                    <div class="feedback-hint">
                        <i class="fas fa-info-circle"></i>
                        Format PDF · Maks 2 MB
                    </div>

                    <?php endif; ?>

                </div>
            </div>

            <!-- CARD: RIWAYAT STATUS -->
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-clock-rotate-left"></i>
                    <h5>Riwayat Status</h5>
                </div>
                <div class="card-modern-body">
                    <?php if (empty($riwayat)): ?>
                    <div class="empty-state-modern" style="padding:30px 20px">
                        <i class="fas fa-clock" style="font-size:1.8rem;margin-bottom:10px"></i>
                        <p>Belum ada riwayat</p>
                    </div>
                    <?php else: ?>
                    <div class="timeline">
                        <?php foreach ($riwayat as $r): 
                            $dotColors = [
                                'pengajuan'     => 'blue',
                                'verifikasi'    => 'yellow',
                                'tindak_lanjut' => 'cyan',
                                'selesai'       => 'green'
                            ];
                            $c = $dotColors[$r['status']] ?? 'blue';
                        ?>
                        <div class="tl-item">
                            <div class="tl-dot <?php echo $c; ?>"></div>
                            <div class="tl-title"><?php echo getStatusLabel($r['status']); ?></div>
                            <div class="tl-date">
                                <?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?>
                                <?php if (!empty($r['petugas'])): ?>
                                · oleh <?php echo htmlspecialchars($r['petugas']); ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($r['keterangan'])): ?>
                            <div class="tl-desc"><?php echo nl2br(htmlspecialchars($r['keterangan'])); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<!-- ============================================
     MODAL UPLOAD FEEDBACK
     ============================================ -->
<div class="modal fade" id="uploadFeedbackModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-file-pdf text-danger me-2"></i>Upload Bukti Formal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="uploadFeedbackForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="laporan_id" value="<?php echo $laporan['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Pilih File PDF</label>
                        <input type="file" name="feedback_file" id="modalFeedbackInput" class="form-control"
                            accept=".pdf" required>
                        <div class="form-text">Maksimal 2MB, hanya file PDF</div>
                    </div>
                    <div id="uploadFeedbackStatus" class="alert d-none"></div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-modern-primary" id="btnUploadFeedback">
                        <i class="fas fa-upload me-1"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('actionSelect');
    var keteranganDiv = document.getElementById('keteranganDiv');
    var keteranganInput = document.getElementById('keteranganInput');
    var form = document.getElementById('formUpdateStatus');

    select.addEventListener('change', function() {
        var value = this.value;
        if (value === 'selesai') {
            keteranganDiv.style.display = 'block';
            keteranganInput.setAttribute('required', 'required');
        } else {
            keteranganDiv.style.display = 'none';
            keteranganInput.removeAttribute('required');
        }
    });

    form.addEventListener('submit', function(e) {
        var action = select.value;
        if (!action) {
            e.preventDefault();
            alert('Silakan pilih aksi terlebih dahulu!');
            return false;
        }
        if (action === 'selesai') {
            var ket = keteranganInput.value.trim();
            if (!ket) {
                e.preventDefault();
                alert('Keterangan wajib diisi untuk menyelesaikan laporan!');
                keteranganInput.focus();
                return false;
            }
        }
        var labels = {
            'verifikasi': 'memverifikasi',
            'tindak_lanjut': 'melanjutkan ke tindak lanjut',
            'selesai': 'menyelesaikan'
        };
        if (!confirm('Yakin ingin ' + (labels[action] || 'mengupdate') + ' laporan ini?')) {
            e.preventDefault();
            return false;
        }
    });

    /* ===== UPLOAD FEEDBACK ===== */
    var uploadForm = document.getElementById('uploadFeedbackForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function(e) {
            e.preventDefault();

            var formData = new FormData(this);
            var statusDiv = document.getElementById('uploadFeedbackStatus');
            var btn = document.getElementById('btnUploadFeedback');
            var originalText = btn.innerHTML;

            statusDiv.classList.add('d-none');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';
            btn.disabled = true;

            fetch('<?php echo APP_URL; ?>admin/ajax/upload-feedback.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    statusDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
                    if (data.success) {
                        statusDiv.classList.add('alert-success');
                        statusDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + data
                            .message;
                        setTimeout(() => {
                            location.reload();
                        }, 1200);
                    } else {
                        statusDiv.classList.add('alert-danger');
                        statusDiv.innerHTML = '<i class="fas fa-circle-exclamation me-1"></i> ' +
                            data.message;
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(() => {
                    statusDiv.classList.remove('d-none', 'alert-success');
                    statusDiv.classList.add('alert-danger');
                    statusDiv.innerHTML =
                        '<i class="fas fa-circle-exclamation me-1"></i> Kesalahan jaringan.';
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });
    }
});

function deleteFeedback(id) {
    if (!confirm('Yakin ingin menghapus file feedback ini?')) return;

    const formData = new FormData();
    formData.append('csrf_token', '<?php echo generateCSRFToken(); ?>');
    formData.append('laporan_id', id);

    fetch('<?php echo APP_URL; ?>admin/ajax/delete-feedback.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus file.');
            }
        })
        .catch(() => {
            alert('Terjadi kesalahan jaringan.');
        });
}
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>