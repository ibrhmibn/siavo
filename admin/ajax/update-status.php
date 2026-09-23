<?php
// admin/ajax/update-status.php
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

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF token tidak valid']);
    exit;
}

$laporan_id = intval($_POST['laporan_id'] ?? 0);
$status = sanitize($_POST['status'] ?? '');
$keterangan = sanitize($_POST['keterangan'] ?? '');
$petugas_id = $_SESSION['user_id'];

$allowed_status = ['pengajuan', 'verifikasi', 'tindak_lanjut', 'selesai'];
if (!in_array($status, $allowed_status)) {
    echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
    exit;
}

if ($laporan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID laporan tidak valid']);
    exit;
}

// ============================================
// CEK FILE (kalau ada)
// ============================================
$has_file = isset($_FILES['feedback_file'])
            && $_FILES['feedback_file']['error'] !== UPLOAD_ERR_NO_FILE;

// Validasi file DULU sebelum update status
if ($has_file) {
    $file = $_FILES['feedback_file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Gagal upload file. Kode: ' . $file['error']]);
        exit;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        echo json_encode(['success' => false, 'message' => 'Hanya file PDF yang diperbolehkan.']);
        exit;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 2 MB.']);
        exit;
    }

    // Cek MIME type biar gak bisa diakalin rename
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($mime !== 'application/pdf') {
        echo json_encode(['success' => false, 'message' => 'File bukan PDF yang valid.']);
        exit;
    }
}

// ============================================
// UPDATE STATUS
// ============================================
if (!updateLaporanStatus($laporan_id, $status, $keterangan, $petugas_id)) {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status']);
    exit;
}

// ============================================
// UPLOAD FILE (kalau ada)
// ============================================
$warning = null;

if ($has_file) {
    $result = uploadFeedbackFile($_FILES['feedback_file'], $laporan_id);
    if (!$result['success']) {
        $warning = $result['message'];
    }
}

echo json_encode([
    'success' => true,
    'message' => 'Status berhasil diperbarui',
    'warning' => $warning
]);