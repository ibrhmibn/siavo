<?php
// admin/ajax/detail-laporan.php
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn() || getUserRole() != 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID tidak valid']);
    exit;
}

$stmt = $conn->prepare("
    SELECT l.*, k.nama_kategori, 
           u.nama_lengkap as pelapor_terbaru,
           u.nim as nim_terbaru,
           u.kontak as kontak_terbaru,
           u.prodi as prodi_terbaru
    FROM laporan l 
    LEFT JOIN kategori k ON l.kategori_id = k.id 
    LEFT JOIN users u ON l.user_id = u.id 
    WHERE l.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$laporan) {
    echo json_encode(['success' => false, 'message' => 'Laporan tidak ditemukan']);
    exit;
}

if ($laporan['user_id']) {
    $laporan['nama_pelapor'] = $laporan['pelapor_terbaru'] ?? $laporan['nama_pelapor'];
    $laporan['nim'] = $laporan['nim_terbaru'] ?? $laporan['nim'];
    $laporan['kontak'] = $laporan['kontak_terbaru'] ?? $laporan['kontak'];
    $laporan['prodi'] = $laporan['prodi_terbaru'] ?? $laporan['prodi'];
}

$laporan['created_at'] = date('d/m/Y H:i', strtotime($laporan['created_at']));

// ====== DOKUMEN PENDUKUNG — tambah url_file ======
$dokumen = [];
$stmt = $conn->prepare("SELECT * FROM dokumen_pendukung WHERE laporan_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    // ⚡ Convert path_file jadi URL yang bisa diakses browser
    $row['url_file'] = getFileUrl($row['path_file']);
    $dokumen[] = $row;
}
$stmt->close();
$laporan['dokumen'] = $dokumen;

// ====== FEEDBACK FILE — convert juga ======
if (!empty($laporan['file_feedback'])) {
    $laporan['url_feedback'] = getFileUrl($laporan['file_feedback']);
}

// ====== RIWAYAT ======
$riwayat = [];
$stmt = $conn->prepare("
    SELECT r.*, u.nama_lengkap as petugas 
    FROM riwayat_status r 
    LEFT JOIN users u ON r.petugas_id = u.id 
    WHERE r.laporan_id = ? 
    ORDER BY r.created_at ASC
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $row['created_at'] = date('d/m/Y H:i', strtotime($row['created_at']));
    $riwayat[] = $row;
}
$stmt->close();
$laporan['riwayat'] = $riwayat;

echo json_encode(['success' => true, 'data' => $laporan]);