<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Cek login
if (!isLoggedIn() || getUserRole() != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Cek CSRF
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token tidak valid']);
    exit;
}

// Cek ID laporan
$laporan_id = intval($_POST['laporan_id'] ?? 0);
if ($laporan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID laporan tidak valid']);
    exit;
}

// Cek file
if (!isset($_FILES['feedback_file']) || $_FILES['feedback_file']['error'] == UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'Silakan pilih file terlebih dahulu.']);
    exit;
}

// Cek error upload
if ($_FILES['feedback_file']['error'] !== UPLOAD_ERR_OK) {
    $error_messages = [
        UPLOAD_ERR_INI_SIZE => 'File melebihi ukuran maksimum yang diizinkan oleh server.',
        UPLOAD_ERR_FORM_SIZE => 'File melebihi ukuran maksimum yang diizinkan oleh form.',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian.',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload.',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP.'
    ];
    $msg = $error_messages[$_FILES['feedback_file']['error']] ?? 'Unknown upload error.';
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}

// Proses upload
$result = uploadFeedbackFile($_FILES['feedback_file'], $laporan_id);

// Kirim response
echo json_encode($result);
?>