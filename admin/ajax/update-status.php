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

// Update status
if (updateLaporanStatus($laporan_id, $status, $keterangan, $petugas_id)) {
    echo json_encode(['success' => true, 'message' => 'Status berhasil diperbarui']);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status']);
}
?>