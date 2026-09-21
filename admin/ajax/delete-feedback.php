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

if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'CSRF token tidak valid']);
    exit;
}

$laporan_id = intval($_POST['laporan_id'] ?? 0);
if ($laporan_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID laporan tidak valid']);
    exit;
}

$result = deleteFeedbackFile($laporan_id);
echo json_encode($result);
?>