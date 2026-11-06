<?php
$page_title = 'Masuk';
$page_active = 'login';

require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect(getUserRole() === 'mahasiswa' ? 'mahasiswa/dashboard.php' : 'admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap, role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $r = $stmt->get_result();

        if ($r && $r->num_rows > 0) {
            $u = $r->fetch_assoc();
            if (password_verify($password, $u['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = $u['id'];
                $_SESSION['user_name'] = $u['nama_lengkap'];
                $_SESSION['user_role'] = $u['role'];
                
                $stmt->close();
                $conn->close();
                
                redirect($u['role'] === 'mahasiswa' ? 'mahasiswa/dashboard.php' : 'admin/dashboard.php');
            } else {
                $error = 'Username atau password salah.';
            }
        } else {
            $error = 'Username atau password salah.';
        }
        $stmt->close();
    }
}

include 'includes/header_guest.php';
?>

<div class="wrap">
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-head">
                <div class="ico"><i class="fas fa-arrow-right-to-bracket"></i></div>
                <h1>Masuk</h1>
                <p>Masuk ke akun SIAVO Anda</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-err">
                <i class="fas fa-circle-exclamation"></i>
                <span><?php echo escape($error); ?></span>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div class="field">
                    <label for="username">Username</label>
                    <div class="field-input">
                        <span class="ico"><i class="fas fa-user"></i></span>
                        <input type="text" id="username" name="username" placeholder="Masukkan username" required
                            autofocus autocomplete="username">
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="field-input">
                        <span class="ico"><i class="fas fa-lock"></i></span>
                        <input type="password" id="password" name="password" placeholder="Masukkan password" required
                            autocomplete="current-password">
                        <button type="button" class="toggle" id="togglePassword" aria-label="Lihat password">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-red btn-full">Masuk</button>
            </form>
            <p style="text-align:center;margin-top:20px;font-size:.9rem;color:var(--ink-2)">
                Belum punya akun?
                <a href="<?php echo APP_URL; ?>register.php" style="color:var(--red);font-weight:600">Daftar disini</a>
            </p>
        </div>
    </div>
</div>

<script>
document.getElementById('togglePassword').addEventListener('click', function() {
    var p = document.getElementById('password');
    var eye = document.getElementById('eyeIcon');
    var isPwd = p.type === 'password';
    p.type = isPwd ? 'text' : 'password';
    eye.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
});
</script>

<?php include 'includes/footer_guest.php'; 
if(isset($conn)) { $conn->close(); }
?>