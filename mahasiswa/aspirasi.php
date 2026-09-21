<?php
$page_title = 'Kirim Aspirasi';
$page_active = 'aspirasi';

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'mahasiswa') {
    redirect('../login.php');
}

$error = '';
$success = false;
$ticket_number = '';

$user_id = $_SESSION['user_id'];
$user_data = getUserData($user_id);

// Ambil kategori
$kategori = [];
$stmt_kat = $conn->prepare("SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");
$stmt_kat->execute();
$res_kat = $stmt_kat->get_result();
while ($row = $res_kat->fetch_assoc()) $kategori[] = $row;
$stmt_kat->close();

// Proses form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Sesi tidak valid atau kadaluarsa. Silakan refresh halaman.";
    } else {
        $kategori_id = sanitize($_POST['kategori']);
        $judul = trim($_POST['judul'] ?? '');
        $isi = trim($_POST['isi']);

        if (empty($kategori_id)) {
            $error = "Kategori wajib dipilih.";
        } elseif (empty($judul)) {
            $error = "Judul laporan wajib diisi.";
        } elseif (empty($isi)) {
            $error = "Isi aspirasi wajib diisi.";
        } else {
            $judul_safe = sanitize($judul);
            $isi_safe = sanitize($isi);
            $ticket_number = generateTicketNumber('aspirasi');

            $query = "INSERT INTO laporan (nomor_tiket, user_id, nama_pelapor, nim, prodi, kontak, kategori_id, judul, isi, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pengajuan')";

            $stmt = $conn->prepare($query);
            $stmt->bind_param(
                "sisssssis",
                $ticket_number,
                $user_id,
                $user_data['nama_lengkap'],
                $user_data['nim'],
                $user_data['prodi'],
                $user_data['kontak'],
                $kategori_id,
                $judul_safe,
                $isi_safe
            );

            if ($stmt->execute()) {
                $laporan_id = $stmt->insert_id;
                addStatusHistory($laporan_id, 'pengajuan', 'Aspirasi berhasil dikirim dan masuk ke tahap pengajuan.', null);

                if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'][0] != 4) {
                    uploadFiles($_FILES['dokumen'], $laporan_id);
                }

                $success = true;
            } else {
                $error = "Terjadi kesalahan sistem. Gagal mengirim aspirasi.";
            }
            $stmt->close();
        }
    }
}

include '../mahasiswa/includes/sidebar.php';
?>

<div class="section-header">
    <div>
        <h2><i class="fas fa-pen-nib"></i> Tulis Aspirasi</h2>
        <p>Sampaikan gagasan, kritik, atau saran Anda dengan jelas dan detail.</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9 col-xl-8">
        <div class="card" style="margin-bottom:22px">
            <div class="card-body" style="padding:32px 36px">

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo $error; ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success alert-permanent"
                    style="display:block;text-align:center;padding:32px 24px">
                    <div style="margin-bottom:14px">
                        <i class="fas fa-check-circle" style="font-size:3rem;color:var(--green)"></i>
                    </div>
                    <h4 style="font-family:var(--display);font-weight:800;color:var(--ink);margin-bottom:8px">
                        Aspirasi Terkirim!
                    </h4>
                    <p style="color:var(--ink-2);margin-bottom:20px">
                        Terima kasih, aspirasi Anda telah masuk ke dalam sistem kami.
                    </p>

                    <div class="ticket-result">
                        <div class="ticket-label">Nomor Tiket Anda</div>
                        <div class="ticket-value"><?php echo $ticket_number; ?></div>
                    </div>

                    <div style="margin-top:8px">
                        <a href="tracking.php?ticket=<?php echo $ticket_number; ?>" class="btn btn-primary">
                            Lacak Progress <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                    <!-- Identitas Pelapor -->
                    <div class="identity-box">
                        <div class="id-avatar">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <div class="id-label">Mengirim sebagai</div>
                            <div class="id-name">
                                <?php echo htmlspecialchars($user_data['nama_lengkap']); ?>
                                <span class="id-nim">(<?php echo htmlspecialchars($user_data['nim']); ?>)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div style="margin-bottom:20px">
                        <label class="form-label">Kategori Aspirasi <span class="text-danger">*</span></label>
                        <select name="kategori" class="form-select" required>
                            <option value="" selected disabled>-- Pilih kategori yang paling sesuai --</option>
                            <?php foreach ($kategori as $k): ?>
                            <option value="<?php echo $k['id']; ?>">
                                <?php echo htmlspecialchars($k['nama_kategori']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Judul -->
                    <div style="margin-bottom:20px">
                        <label class="form-label">Judul Laporan <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control"
                            placeholder="Misal: Fasilitas parkir gedung A" required>
                    </div>

                    <!-- Isi -->
                    <div style="margin-bottom:20px">
                        <label class="form-label">Detail Aspirasi <span class="text-danger">*</span></label>
                        <textarea name="isi" id="detailAspirasi" class="form-control" rows="3"
                            placeholder="Jelaskan aspirasi atau masalah yang Anda alami secara rinci..."
                            style="resize:none" required></textarea>
                    </div>

                    <!-- Upload Dokumen -->
                    <div style="margin-bottom:26px">
                        <label class="form-label">
                            Unggah Lampiran
                            <span style="font-weight:400;color:var(--ink-2);text-transform:none;letter-spacing:0">
                                (Opsional)
                            </span>
                        </label>

                        <label class="file-upload" for="inputDokumen">
                            <div class="file-upload-info">
                                <div class="file-upload-title" data-default="Belum ada file dipilih">
                                    Belum ada file dipilih
                                </div>
                                <div class="file-upload-hint" data-default="Format PDF, JPG, PNG · Maks 2 MB per file">
                                    Format PDF, JPG, PNG · Maks 2 MB per file
                                </div>
                            </div>
                            <span class="file-upload-btn">Pilih File</span>
                            <input type="file" name="dokumen[]" id="inputDokumen" multiple
                                accept=".pdf,.jpg,.jpeg,.png">
                        </label>
                    </div>

                    <!-- Submit -->
                    <div
                        style="display:flex;justify-content:flex-end;border-top:1px solid var(--line);padding-top:20px">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Kirim Aspirasi <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('detailAspirasi');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }
});
</script>

<?php include '../mahasiswa/includes/footer.php'; ?>