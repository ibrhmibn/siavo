<?php
$page_title = 'Kelola Pengumuman';
$page_active = 'pengumuman';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $id = intval($_POST['delete_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare("DELETE FROM pengumuman WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = 'Pengumuman berhasil dihapus.';
                } else {
                    $error = 'Gagal menghapus pengumuman.';
                }
                $stmt->close();
            }
        } else {
            $judul = sanitize($_POST['judul'] ?? '');
            $isi   = trim($_POST['isi'] ?? '');

            if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
                $id = intval($_POST['edit_id']);
                $stmt = $conn->prepare("UPDATE pengumuman SET judul = ?, isi = ? WHERE id = ?");
                $stmt->bind_param("ssi", $judul, $isi, $id);
                if ($stmt->execute()) {
                    $success = 'Pengumuman berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui pengumuman.';
                }
                $stmt->close();
            } else {
                $user_id = $_SESSION['user_id'];
                $stmt = $conn->prepare("INSERT INTO pengumuman (judul, isi, created_by) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $judul, $isi, $user_id);
                if ($stmt->execute()) {
                    $success = 'Pengumuman berhasil ditambahkan.';
                } else {
                    $error = 'Gagal menambahkan pengumuman.';
                }
                $stmt->close();
            }
        }
    }
}

$pengumuman = [];
$sql = "SELECT p.*, u.nama_lengkap AS creator FROM pengumuman p 
        LEFT JOIN users u ON p.created_by = u.id 
        ORDER BY p.created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pengumuman[] = $row;
    }
}

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Kelola Pengumuman</h1>
            <p class="text-muted small mb-0">Pengumuman akan otomatis tampil di beranda setelah disimpan</p>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-exclamation me-1"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Form -->
        <div class="col-lg-5">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-bullhorn"></i>
                    <h5 id="formTitle">Tambah Pengumuman</h5>
                </div>
                <div class="card-modern-body">
                    <form method="POST" class="form-modern">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="edit_id" id="edit_id" value="">

                        <div class="mb-3">
                            <label>Judul <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" id="judul"
                                placeholder="Contoh: Jadwal Libur Semester Ganjil" required>
                        </div>
                        <div class="mb-3">
                            <label>Isi Pengumuman <span class="text-danger">*</span></label>
                            <textarea name="isi" class="form-control" rows="6" id="isi"
                                placeholder="Tulis isi pengumuman di sini..." required></textarea>
                            <small class="text-muted d-block mt-1">
                                Teks biasa. Di beranda hanya 160 karakter pertama yang ditampilkan.
                            </small>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-modern-primary" id="submitBtn">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                            <button type="button" class="btn btn-secondary" id="cancelBtn" style="display:none">
                                <i class="fas fa-times"></i> Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info card -->
            <div class="card-modern" style="border-top-color:var(--blue)">
                <div class="card-modern-body" style="display:flex;gap:12px;align-items:flex-start">
                    <i class="fas fa-lightbulb" style="color:var(--amber);font-size:1.2rem;margin-top:2px"></i>
                    <div>
                        <div style="font-weight:700;font-size:.9rem;margin-bottom:4px">Tampil otomatis di beranda</div>
                        <p style="color:var(--ink-2);font-size:.83rem;line-height:1.6;margin:0">
                            4 pengumuman terbaru akan muncul di halaman utama. Yang paling baru otomatis
                            disematkan di paling atas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar -->
        <div class="col-lg-7">
            <div class="table-modern-wrapper">
                <div class="table-header">
                    <div class="title"><i class="fas fa-list"></i>Daftar Pengumuman</div>
                </div>
                <?php if (empty($pengumuman)): ?>
                <div class="empty-state-modern">
                    <i class="fas fa-bullhorn"></i>
                    <p>Belum ada pengumuman</p>
                </div>
                <?php else: ?>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul</th>
                                <th>Dibuat Oleh</th>
                                <th>Tanggal</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pengumuman as $p): ?>
                            <tr>
                                <td><span class="cell-name"><?php echo htmlspecialchars($p['judul']); ?></span></td>
                                <td><?php echo htmlspecialchars($p['creator'] ?? '-'); ?></td>
                                <td class="text-muted small">
                                    <?php echo date('d/m/Y H:i', strtotime($p['created_at'])); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn-icon-modern edit"
                                        onclick="editPengumuman(<?php echo $p['id']; ?>, '<?php echo addslashes(htmlspecialchars($p['judul'], ENT_QUOTES)); ?>', '<?php echo addslashes(htmlspecialchars($p['isi'], ENT_QUOTES)); ?>')"
                                        title="Edit">
                                        <i class="fas fa-pencil"></i>
                                    </button>
                                    <form method="POST" style="display:inline"
                                        onsubmit="return confirm('Yakin ingin menghapus pengumuman ini?')">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?php echo $p['id']; ?>">
                                        <button type="submit" class="btn-icon-modern delete" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span>Menampilkan <span class="fw-bold text-dark"><?php echo count($pengumuman); ?></span>
                        data</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function editPengumuman(id, judul, isi) {
    document.getElementById('edit_id').value = id;
    document.getElementById('judul').value = judul;
    document.getElementById('isi').value = isi;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-pencil"></i> Update';
    document.getElementById('formTitle').textContent = 'Edit Pengumuman';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('edit_id').value = '';
    document.getElementById('judul').value = '';
    document.getElementById('isi').value = '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';
    document.getElementById('formTitle').textContent = 'Tambah Pengumuman';
    this.style.display = 'none';
});
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>