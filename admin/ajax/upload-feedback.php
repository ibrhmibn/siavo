<?php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token tidak valid']);
    exit;
}

$laporan_id = intval($_POST['laporan_id'] ?? 0);
if ($laporan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID laporan tidak valid']);
    exit;
}

if (!isset($_FILES['feedback_file']) || $_FILES['feedback_file']['error'] == UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'Silakan pilih file terlebih dahulu.']);
    exit;
}

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

// Cek folder
if (!is_dir(FEEDBACK_DIR)) {
    if (!mkdir(FEEDBACK_DIR, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Gagal membuat folder feedback: ' . FEEDBACK_DIR]);
        exit;
    }
}

try {
    $result = uploadFeedbackFile($_FILES['feedback_file'], $laporan_id);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}