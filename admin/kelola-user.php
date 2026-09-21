<?php
$page_title = 'Kelola User';
$page_active = 'user';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

$error = '';
$success = '';

// Proses update role atau hapus
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $action = $_POST['action'];
        $user_id = intval($_POST['user_id']);

        if ($action == 'update_role') {
            $role = sanitize($_POST['role']);
            if (!in_array($role, ['admin', 'mahasiswa'])) {
                $error = 'Role tidak valid.';
            } else {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->bind_param("si", $role, $user_id);
                if ($stmt->execute()) {
                    $success = 'Role user berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui role.';
                }
                $stmt->close();
            }
        } elseif ($action == 'delete') {
            if ($user_id == $_SESSION['user_id']) {
                $error = 'Anda tidak dapat menghapus akun sendiri.';
            } else {
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = 'User berhasil dihapus.';
                } else {
                    $error = 'Gagal menghapus user.';
                }
                $stmt->close();
            }
        } else {
            $error = 'Aksi tidak dikenali.';
        }
    }
}

// Ambil semua user
$users = [];
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <!-- Page Header -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Kelola User</h1>
            <p class="text-muted small mb-0">Kelola semua pengguna sistem SIAVO</p>
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

    <div class="table-modern-wrapper">
        <div class="table-header">
            <div class="title"><i class="fas fa-users me-2" style="color:#d00018;"></i>Daftar User</div>
        </div>
        <?php if (empty($users)): ?>
        <div class="empty-state-modern">
            <i class="fas fa-user"></i>
            <p>Belum ada user terdaftar</p>
        </div>
        <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIM</th>
                        <th>Role</th>
                        <th>Tanggal Daftar</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td class="text-muted"><?php echo $u['id']; ?></td>
                        <td><span class="cell-name"><?php echo htmlspecialchars($u['username']); ?></span></td>
                        <td><?php echo htmlspecialchars($u['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                        <td><?php echo htmlspecialchars($u['nim'] ?? '-'); ?></td>
                        <td>
                            <span class="badge-role-modern <?php echo $u['role'] == 'admin' ? 'admin' : 'mahasiswa'; ?>">
                                <?php echo ucfirst($u['role']); ?>
                            </span>
                        </td>
                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                        <td class="text-center">
                            <button class="btn-icon-modern edit" onclick="editUser(<?php echo $u['id']; ?>, '<?php echo $u['role']; ?>')">
                                <i class="fas fa-pencil"></i>
                            </button>
                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                            <button class="btn-icon-modern delete" onclick="confirmDelete(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>')">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Menampilkan <span class="fw-bold text-dark"><?php echo count($users); ?></span> data</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Edit Role -->
<div class="modal fade" id="editRoleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-3 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-cog me-2 text-primary"></i>Edit Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="role" class="form-select" id="edit_user_role">
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" style="background-color:#d00018; border-color:#d00018;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 rounded-3 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-danger"><i class="fas fa-trash me-2"></i>Hapus User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <p>Apakah Anda yakin ingin menghapus user <strong id="delete_username"></strong>?</p>
                    <p class="text-danger small">Tindakan ini tidak dapat dibatalkan!</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editUser(id, role) {
    document.getElementById('edit_user_id').value = id;
    document.getElementById('edit_user_role').value = role;
    new bootstrap.Modal(document.getElementById('editRoleModal')).show();
}

function confirmDelete(id, username) {
    document.getElementById('delete_user_id').value = id;
    document.getElementById('delete_username').textContent = username;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>