<?php
$page_title = 'Profil Saya';
$page_active = 'profile';

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'mahasiswa') {
    redirect('../login.php');
}

$user_id = $_SESSION['user_id'];
$user_data = getUserData($user_id);

$error = '';
$success = '';

// URL fallback foto default
$foto_default_url = APP_URL . 'assets/img/person/' . DEFAULT_PROFILE_PHOTO;

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $nama_lengkap = sanitize($_POST['nama_lengkap']);
        $email        = sanitize($_POST['email']);
        $kontak       = sanitize($_POST['kontak']);
        $prodi        = sanitize($_POST['prodi']);

        $errors = [];
        if (empty($nama_lengkap)) $errors[] = 'Nama lengkap wajib diisi.';
        if (!validateEmail($email)) $errors[] = 'Email tidak valid.';
        if (empty($kontak)) $errors[] = 'Nomor kontak wajib diisi.';
        if (empty($prodi)) $errors[] = 'Program studi wajib diisi.';

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errors[] = 'Email sudah digunakan user lain.';
        $stmt->close();

        if (empty($errors)) {
            $foto_nama = $user_data['foto_profil'];

            if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                $ext = strtolower(pathinfo($_FILES['foto_profil']['name'], PATHINFO_EXTENSION));

                if (!in_array($ext, $allowed)) {
                    $error = 'Format foto hanya JPG, PNG, atau WEBP.';
                } elseif ($_FILES['foto_profil']['size'] > 2 * 1024 * 1024) {
                    $error = 'Ukuran foto maksimal 2 MB.';
                } else {
                    $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/siavo/assets/img/person/';
                    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

                    // Hapus foto lama (kalau bukan file default)
                    if (!empty($foto_nama) && $foto_nama !== DEFAULT_PROFILE_PHOTO) {
                        $old_file = $upload_dir . $foto_nama;
                        if (file_exists($old_file)) @unlink($old_file);
                    }

                    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $upload_dir . $new_filename)) {
                        $foto_nama = $new_filename;
                    } else {
                        $error = 'Gagal upload foto.';
                    }
                }
            }

            if (empty($error)) {
                $conn->begin_transaction();
                try {
                    $stmt = $conn->prepare("UPDATE users SET nama_lengkap=?, email=?, kontak=?, prodi=?, foto_profil=? WHERE id=?");
                    $stmt->bind_param("sssssi", $nama_lengkap, $email, $kontak, $prodi, $foto_nama, $user_id);
                    if (!$stmt->execute()) throw new Exception('Gagal update user: ' . $conn->error);
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE laporan SET nama_pelapor = ? WHERE user_id = ?");
                    $stmt->bind_param("si", $nama_lengkap, $user_id);
                    if (!$stmt->execute()) throw new Exception('Gagal update laporan: ' . $conn->error);
                    $stmt->close();

                    $conn->commit();
                    $success = 'Profil berhasil diperbarui!';
                    $user_data = getUserData($user_id);
                    $_SESSION['user_name'] = $user_data['nama_lengkap'];
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = $e->getMessage();
                }
            }
        } else {
            $error = implode('<br>', $errors);
        }
    }
}

include 'includes/sidebar.php';
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-user-circle"></i> Profil Saya</h1>
        <p>Kelola data pribadi dan foto profil Anda</p>
    </div>
</div>

<div class="row g-4">
    <!-- Foto -->
    <div class="col-md-4">
        <div class="card profile-card">
            <?php 
                $foto_file = !empty($user_data['foto_profil']) 
                    ? $user_data['foto_profil'] 
                    : DEFAULT_PROFILE_PHOTO;
                $foto_path = APP_URL . 'assets/img/person/' . $foto_file;
            ?>
            <img src="<?php echo $foto_path; ?>" class="profile-avatar" alt="Foto Profil"
                onerror="this.onerror=null; this.src='<?php echo $foto_default_url; ?>'">
            <div class="profile-name"><?php echo htmlspecialchars($user_data['nama_lengkap']); ?></div>
            <div class="profile-nim"><?php echo htmlspecialchars($user_data['nim']); ?></div>
            <span class="profile-badge">
                <i class="fas fa-check-circle"></i> Mahasiswa Aktif
            </span>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-edit"></i> Edit Profil
            </div>
            <div class="card-body">

                <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-circle-exclamation"></i>
                    <span><?php echo $error; ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $success; ?></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert">&times;</button>
                </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="update_profile" value="1">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                            <div class="form-text">Username tidak dapat diubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">NIM</label>
                            <input type="text" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['nim']); ?>" readonly>
                            <div class="form-text">NIM tidak dapat diubah.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['nama_lengkap']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">No. Kontak <span class="text-danger">*</span></label>
                            <input type="text" name="kontak" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['kontak']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Program Studi <span class="text-danger">*</span></label>
                            <input type="text" name="prodi" class="form-control"
                                value="<?php echo htmlspecialchars($user_data['prodi']); ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Foto Profil</label>

                            <label class="file-upload" for="inputFoto">
                                <div class="file-upload-info">
                                    <div class="file-upload-title" data-default="Belum ada foto dipilih">
                                        Belum ada foto dipilih
                                    </div>
                                    <div class="file-upload-hint" data-default="JPG, PNG, WEBP · Maks 2 MB">
                                        JPG, PNG, WEBP · Maks 2 MB
                                    </div>
                                </div>
                                <span class="file-upload-btn">Pilih Foto</span>
                                <input type="file" name="foto_profil" id="inputFoto"
                                    accept="image/jpeg,image/png,image/webp">
                            </label>

                            <div class="form-text" style="margin-top:8px">
                                <i class="fas fa-info-circle"></i>
                                Kosongkan jika tidak ingin mengganti foto.
                            </div>
                        </div>
                    </div>

                    <div
                        style="display:flex;justify-content:flex-end;border-top:1px solid var(--line);padding-top:20px;margin-top:22px">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>