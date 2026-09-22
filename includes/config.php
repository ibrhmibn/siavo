<?php
// Konfigurasi database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'siavo_db');

// Konfigurasi aplikasi
define('APP_NAME', 'SIAVO');
define('APP_URL', 'http://localhost/siavo/');
define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/siavo/uploads/');
define('UPLOAD_URL', '/siavo/uploads/');
define('FEEDBACK_DIR', $_SERVER['DOCUMENT_ROOT'] . '/siavo/uploads/feedback/');
define('FEEDBACK_URL', '/siavo/uploads/feedback/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);

// Foto profil default — SVG avatar icon (bukan gambar upload user)
define('DEFAULT_PROFILE_PHOTO', 'default-avatar.svg');

// Mulai session
session_start();

// Koneksi database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Fungsi untuk debug (opsional)
function debug($data) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}
?>