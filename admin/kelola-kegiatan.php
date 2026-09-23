<?php
$page_title = 'Kelola Kegiatan';
$page_active = 'kegiatan';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

// AUTO-CLEANUP pas buka halaman ini (max 1x per 6 jam)
$auto_deleted = autoCleanupKegiatan();

$error = '';
$success = '';

// Folder upload gambar
$IMG_DIR = $_SERVER['DOCUMENT_ROOT'] . '/siavo/assets/img/kegiatan/';
$IMG_URL = APP_URL . 'assets/img/kegiatan/';

// Buat folder kalau belum ada
if (!is_dir($IMG_DIR)) {
    @mkdir($IMG_DIR, 0755, true);
}

// ============================================
// HANDLE POST
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $action = $_POST['action'] ?? 'save';

        // ---- CLEANUP MANUAL ----
        if ($action === 'cleanup') {
            $deleted = cleanupExpiredKegiatan();
            if ($deleted > 0) {
                $success = $deleted . ' kegiatan lama berhasil dihapus.';
            } else {
                $success = 'Tidak ada kegiatan lama yang perlu dihapus.';
            }
        }

        // ---- DELETE (single) ----
        elseif ($action === 'delete') {
            $id = intval($_POST['delete_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $row = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stmt = $conn->prepare("DELETE FROM kegiatan WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    if (!empty($row['gambar']) && file_exists($IMG_DIR . $row['gambar'])) {
                        @unlink($IMG_DIR . $row['gambar']);
                    }
                    $success = 'Kegiatan berhasil dihapus.';
                } else {
                    $error = 'Gagal menghapus kegiatan.';
                }
                $stmt->close();
            }
        }

        // ---- SAVE (insert/update) ----
        else {
            $nama       = sanitize($_POST['nama'] ?? '');
            $deskripsi  = trim($_POST['deskripsi'] ?? '');
            $tanggal    = $_POST['tanggal'] ?? '';
            $waktu      = $_POST['waktu'] ?? null;
            $tipe       = in_array($_POST['tipe'] ?? '', ['sema','siavo']) ? $_POST['tipe'] : 'sema';
            $edit_id    = intval($_POST['edit_id'] ?? 0);

            if ($nama === '' || $tanggal === '') {
                $error = 'Nama dan tanggal wajib diisi.';
            } else {
                $gambar_baru = null;
                $has_upload = isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK;

                if ($has_upload) {
                    $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                    $allowed = ['jpg','jpeg','png','webp'];

                    if (!in_array($ext, $allowed)) {
                        $error = 'Format gambar tidak valid. Gunakan JPG, PNG, atau WEBP.';
                    } elseif ($_FILES['gambar']['size'] > 3 * 1024 * 1024) {
                        $error = 'Ukuran gambar maksimal 3 MB.';
                    } else {
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        $mime = finfo_file($finfo, $_FILES['gambar']['tmp_name']);
                        finfo_close($finfo);

                        $allowed_mimes = ['image/jpeg', 'image/png', 'image/webp'];
                        if (!in_array($mime, $allowed_mimes)) {
                            $error = 'File bukan gambar yang valid.';
                        } else {
                            $gambar_baru = 'kegiatan_' . uniqid() . '_' . time() . '.' . $ext;
                            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $IMG_DIR . $gambar_baru)) {
                                $error = 'Gagal mengupload gambar.';
                                $gambar_baru = null;
                            }
                        }
                    }
                }

                if (empty($error)) {
                    if ($edit_id > 0) {
                        $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE id = ?");
                        $stmt->bind_param("i", $edit_id);
                        $stmt->execute();
                        $old = $stmt->get_result()->fetch_assoc();
                        $stmt->close();

                        if ($gambar_baru) {
                            $stmt = $conn->prepare("UPDATE kegiatan SET nama=?, deskripsi=?, tanggal=?, waktu=?, tipe=?, gambar=? WHERE id=?");
                            $stmt->bind_param("ssssssi", $nama, $deskripsi, $tanggal, $waktu, $tipe, $gambar_baru, $edit_id);
                        } else {
                            $stmt = $conn->prepare("UPDATE kegiatan SET nama=?, deskripsi=?, tanggal=?, waktu=?, tipe=? WHERE id=?");
                            $stmt->bind_param("sssssi", $nama, $deskripsi, $tanggal, $waktu, $tipe, $edit_id);
                        }

                        if ($stmt->execute()) {
                            if ($gambar_baru && !empty($old['gambar']) && file_exists($IMG_DIR . $old['gambar'])) {
                                @unlink($IMG_DIR . $old['gambar']);
                            }
                            $success = 'Kegiatan berhasil diperbarui.';
                        } else {
                            $error = 'Gagal memperbarui kegiatan.';
                            if ($gambar_baru && file_exists($IMG_DIR . $gambar_baru)) {
                                @unlink($IMG_DIR . $gambar_baru);
                            }
                        }
                        $stmt->close();
                    } else {
                        $stmt = $conn->prepare("INSERT INTO kegiatan (nama, deskripsi, tanggal, waktu, tipe, gambar) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->bind_param("ssssss", $nama, $deskripsi, $tanggal, $waktu, $tipe, $gambar_baru);
                        if ($stmt->execute()) {
                            $success = 'Kegiatan berhasil ditambahkan.';
                        } else {
                            $error = 'Gagal menambahkan kegiatan.';
                            if ($gambar_baru && file_exists($IMG_DIR . $gambar_baru)) {
                                @unlink($IMG_DIR . $gambar_baru);
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Ambil semua kegiatan
$kegiatan = [];
$sql = "SELECT * FROM kegiatan ORDER BY 
        (tanggal < CURDATE()) ASC, 
        IF(tanggal >= CURDATE(), tanggal, NULL) ASC, 
        tanggal DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kegiatan[] = $row;
    }
}

// Hitung kegiatan lewat (untuk badge cleanup)
$expired_count = countExpiredKegiatan();

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <!-- ============================================
         PAGE HEADER + TOMBOL CLEANUP
         ============================================ -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Kelola Kegiatan</h1>
            <p class="text-muted small mb-0">Kelola kegiatan SEMA & SIAVO yang tampil di halaman Kegiatan</p>
        </div>

        <?php if ($expired_count > 0): ?>
        <form method="POST"
            onsubmit="return confirm('Hapus <?php echo $expired_count; ?> kegiatan yang sudah lewat lebih dari 2 hari?\n\nFile gambar juga akan dihapus permanen.')"
            class="d-flex align-items-center gap-2 flex-wrap">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="cleanup">
            <span class="text-muted small d-flex align-items-center gap-1">
                <i class="fas fa-broom" style="color:var(--red)"></i>
                <strong><?php echo $expired_count; ?></strong> kegiatan lewat
            </span>
            <button type="submit" class="btn-bersihkan">
                Bersihkan Sekarang
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if ($auto_deleted > 0): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-broom me-1"></i>
        <strong><?php echo $auto_deleted; ?></strong> kegiatan lama (lewat > 2 hari) otomatis dihapus.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

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
        <!-- ============================================
             FORM TAMBAH/EDIT
             ============================================ -->
        <div class="col-lg-5">
            <div class="card-modern">
                <div class="card-modern-header">
                    <i class="fas fa-calendar-plus"></i>
                    <h5 id="formTitle">Tambah Kegiatan</h5>
                </div>
                <div class="card-modern-body">
                    <form method="POST" class="form-modern" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="save">
                        <input type="hidden" name="edit_id" id="edit_id" value="">

                        <div class="mb-3">
                            <label>Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" id="nama"
                                placeholder="Contoh: Forum Dengar Aspirasi" required>
                        </div>

                        <div class="mb-3">
                            <label>Deskripsi <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="4" id="deskripsi"
                                placeholder="Jelaskan detail kegiatan..." required></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-7">
                                <label>Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" id="tanggal" required>
                            </div>
                            <div class="col-5">
                                <label>Waktu</label>
                                <input type="time" name="waktu" class="form-control" id="waktu">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Tipe <span class="text-danger">*</span></label>
                            <select name="tipe" class="form-select" id="tipe" required>
                                <option value="sema">SEMA</option>
                                <option value="siavo">SIAVO</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Gambar <span class="text-muted small">(opsional)</span></label>

                            <label class="file-upload" for="gambar">
                                <div class="file-upload-info">
                                    <div class="file-upload-title" data-default="Belum ada gambar dipilih">
                                        Belum ada gambar dipilih
                                    </div>
                                    <div class="file-upload-hint" data-default="JPG, PNG, WEBP · Maks 3 MB">
                                        JPG, PNG, WEBP · Maks 3 MB
                                    </div>
                                </div>
                                <span class="file-upload-btn">Pilih Gambar</span>
                                <input type="file" name="gambar" id="gambar" accept="image/jpeg,image/png,image/webp">
                            </label>

                            <div id="gambarPreviewWrap" style="margin-top:10px;display:none">
                                <img id="gambarPreview" src="" alt="Preview"
                                    style="max-width:100%;border-radius:10px;max-height:180px">
                            </div>
                            <div id="gambarLama" style="margin-top:10px;display:none">
                                <small class="text-muted">Gambar saat ini:</small><br>
                                <img id="gambarLamaImg" src="" alt="Gambar saat ini"
                                    style="max-width:100%;border-radius:10px;max-height:140px;margin-top:6px">
                            </div>
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
        </div>

        <!-- ============================================
             DAFTAR KEGIATAN
             ============================================ -->
        <div class="col-lg-7">
            <div class="table-modern-wrapper">
                <div class="table-header">
                    <div class="title"><i class="fas fa-list"></i>Daftar Kegiatan</div>
                </div>
                <?php if (empty($kegiatan)): ?>
                <div class="empty-state-modern">
                    <i class="fas fa-calendar-xmark"></i>
                    <p>Belum ada kegiatan</p>
                </div>
                <?php else: ?>
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:70px">Gambar</th>
                                <th>Nama</th>
                                <th>Jadwal</th>
                                <th>Tipe</th>
                                <th class="text-center" style="width:90px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($kegiatan as $k):
                                $is_lewat = strtotime($k['tanggal']) < strtotime(date('Y-m-d'));
                                $gambar_src = !empty($k['gambar'])
                                    ? $IMG_URL . $k['gambar']
                                    : '';
                            ?>
                            <tr<?php echo $is_lewat ? ' style="opacity:.6"' : ''; ?>>
                                <td>
                                    <?php if ($gambar_src): ?>
                                    <img src="<?php echo $gambar_src; ?>" alt=""
                                        style="width:60px;height:60px;object-fit:cover;border-radius:8px">
                                    <?php else: ?>
                                    <div
                                        style="width:60px;height:60px;border-radius:8px;background:var(--surface-2);display:grid;place-items:center;color:var(--ink-2);font-size:.7rem">
                                        <i class="fas fa-image" style="font-size:1.1rem"></i>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="cell-name"><?php echo htmlspecialchars($k['nama']); ?></div>
                                    <?php if ($is_lewat): ?>
                                    <span
                                        style="font-size:.7rem;color:var(--ink-2);text-transform:uppercase;letter-spacing:.05em">
                                        <i class="fas fa-flag-checkered"></i> Sudah lewat
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small">
                                    <?php echo date('d M Y', strtotime($k['tanggal'])); ?>
                                    <?php if (!empty($k['waktu'])): ?>
                                    <br><i class="fas fa-clock"></i>
                                    <?php echo date('H.i', strtotime($k['waktu'])); ?> WIB
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span
                                        class="badge-role-modern <?php echo $k['tipe'] === 'sema' ? 'admin' : 'mahasiswa'; ?>">
                                        <?php echo strtoupper($k['tipe']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-icon-modern edit" onclick='editKegiatan(<?php echo json_encode([
                                            "id" => $k["id"],
                                            "nama" => $k["nama"],
                                            "deskripsi" => $k["deskripsi"],
                                            "tanggal" => $k["tanggal"],
                                            "waktu" => $k["waktu"],
                                            "tipe" => $k["tipe"],
                                            "gambar" => $k["gambar"]
                                        ]); ?>)' title="Edit">
                                        <i class="fas fa-pencil"></i>
                                    </button>
                                    <form method="POST" style="display:inline"
                                        onsubmit="return confirm('Yakin ingin menghapus kegiatan ini?')">
                                        <input type="hidden" name="csrf_token"
                                            value="<?php echo generateCSRFToken(); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="delete_id" value="<?php echo $k['id']; ?>">
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
                    <span>Menampilkan <span class="fw-bold text-dark"><?php echo count($kegiatan); ?></span> data</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
var IMG_URL = '<?php echo $IMG_URL; ?>';

function editKegiatan(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('nama').value = data.nama;
    document.getElementById('deskripsi').value = data.deskripsi;
    document.getElementById('tanggal').value = data.tanggal;
    document.getElementById('waktu').value = data.waktu || '';
    document.getElementById('tipe').value = data.tipe;

    document.getElementById('gambar').value = '';
    document.getElementById('gambarPreviewWrap').style.display = 'none';

    if (data.gambar) {
        document.getElementById('gambarLamaImg').src = IMG_URL + data.gambar;
        document.getElementById('gambarLama').style.display = 'block';
    } else {
        document.getElementById('gambarLama').style.display = 'none';
    }

    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-pencil"></i> Update';
    document.getElementById('formTitle').textContent = 'Edit Kegiatan';
    document.getElementById('cancelBtn').style.display = 'inline-flex';
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

document.getElementById('cancelBtn').addEventListener('click', function() {
    document.getElementById('edit_id').value = '';
    document.getElementById('nama').value = '';
    document.getElementById('deskripsi').value = '';
    document.getElementById('tanggal').value = '';
    document.getElementById('waktu').value = '';
    document.getElementById('tipe').value = 'sema';
    document.getElementById('gambar').value = '';
    document.getElementById('gambarPreviewWrap').style.display = 'none';
    document.getElementById('gambarLama').style.display = 'none';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Simpan';
    document.getElementById('formTitle').textContent = 'Tambah Kegiatan';
    this.style.display = 'none';
});

document.getElementById('gambar').addEventListener('change', function() {
    var f = this.files[0];
    if (!f) {
        document.getElementById('gambarPreviewWrap').style.display = 'none';
        return;
    }
    var reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('gambarPreview').src = e.target.result;
        document.getElementById('gambarPreviewWrap').style.display = 'block';
    };
    reader.readAsDataURL(f);
});
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>