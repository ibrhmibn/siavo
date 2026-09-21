<?php
$page_title = 'Daftar';
$page_active = 'register';
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (isLoggedIn()) redirect('index.php');

$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $username = sanitize($_POST['username']);
        $password = $_POST['password'];
        $confirm  = $_POST['confirm_password'];
        $nama     = sanitize($_POST['nama_lengkap']);
        $email    = sanitize($_POST['email']);
        $nim      = sanitize($_POST['nim']);
        $prodi    = sanitize($_POST['prodi']);
        $kontak   = sanitize($_POST['kontak']);

        $errs = [];
        if (strlen($username) < 3) $errs[] = 'Username minimal 3 karakter';
        if (strlen($password) < 6) $errs[] = 'Password minimal 6 karakter';
        if ($password !== $confirm) $errs[] = 'Password tidak sama';
        if (!validateEmail($email)) $errs[] = 'Email tidak valid';
        if (!validateNIM($nim)) $errs[] = 'NIM harus berupa angka';
        if (empty($prodi)) $errs[] = 'Program studi harus diisi';
        if (empty($kontak)) $errs[] = 'Nomor kontak harus diisi';

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) $errs[] = 'Username atau email sudah digunakan';
        $stmt->close();

        if (empty($errs)) {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, nama_lengkap, email, nim, prodi, kontak, role) VALUES (?,?,?,?,?,?,?,'mahasiswa')");
            $stmt->bind_param("sssssss", $username, $hash, $nama, $email, $nim, $prodi, $kontak);
            if ($stmt->execute()) $success = 'Registrasi berhasil! Silakan login.';
            else $error = 'Gagal registrasi: ' . $conn->error;
            $stmt->close();
        } else $error = implode('<br>', $errs);
    }
}
$conn->close();
include 'includes/header_guest.php';
?>

<div class="wrap">
    <div class="auth-wrap">
        <div class="auth-card wide">
            <div class="auth-head">
                <div class="ico"><i class="fas fa-user-plus"></i></div>
                <h1>Daftar Akun</h1>
                <p>Bergabunglah sebagai mahasiswa SIAVO</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-err"><i class="fas fa-circle-exclamation"></i><span><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            <?php if ($success): ?>
            <div class="alert alert-ok"><i class="fas fa-circle-check"></i><span><?php echo $success; ?> <a
                        href="<?php echo APP_URL; ?>login.php"
                        style="color:inherit;text-decoration:underline;font-weight:700">Login disini</a></span></div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="field-row">
                    <div class="field">
                        <label for="username">Username</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-user"></i></span>
                            <input type="text" id="username" name="username" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="nama_lengkap">Nama Lengkap</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-id-card"></i></span>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <div class="field-input"><span class="ico"><i class="fas fa-envelope"></i></span>
                        <input type="email" id="email" name="email" placeholder="email@kampus.ac.id" required>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="password">Password</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-lock"></i></span>
                            <input type="password" id="password" name="password" placeholder="Min. 6 karakter" required>
                            <button type="button" class="toggle" id="tp1"><i class="fas fa-eye" id="ei1"></i></button>
                        </div>
                    </div>
                    <div class="field">
                        <label for="confirm_password">Konfirmasi</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-lock"></i></span>
                            <input type="password" id="confirm_password" name="confirm_password"
                                placeholder="Ulangi password" required>
                            <button type="button" class="toggle" id="tp2"><i class="fas fa-eye" id="ei2"></i></button>
                        </div>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label for="nim">NIM</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-id-badge"></i></span>
                            <input type="text" id="nim" name="nim" required>
                        </div>
                    </div>
                    <div class="field">
                        <label for="prodi">Program Studi</label>
                        <div class="field-input"><span class="ico"><i class="fas fa-graduation-cap"></i></span>
                            <input type="text" id="prodi" name="prodi" required>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label for="kontak">Nomor Kontak (WA)</label>
                    <div class="field-input"><span class="ico"><i class="fab fa-whatsapp"></i></span>
                        <input type="text" id="kontak" name="kontak" placeholder="0812-3456-7890" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-red btn-full">Daftar Sekarang</button>
            </form>

            <p style="text-align:center;margin-top:20px;font-size:.9rem;color:var(--ink-2)">
                Sudah punya akun? <a href="<?php echo APP_URL; ?>login.php"
                    style="color:var(--red);font-weight:600">Login disini</a>
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function bindToggle(btnId, inputId, iconId) {
    var b = document.getElementById(btnId);
    if (!b) return;
    b.addEventListener('click', function() {
        var p = document.getElementById(inputId),
            e = document.getElementById(iconId);
        var isPwd = p.type === 'password';
        p.type = isPwd ? 'text' : 'password';
        e.className = isPwd ? 'fas fa-eye-slash' : 'fas fa-eye';
    });
}
bindToggle('tp1', 'password', 'ei1');
bindToggle('tp2', 'confirm_password', 'ei2');
</script>

<?php include 'includes/footer_guest.php'; ?>