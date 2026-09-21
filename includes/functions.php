<?php
require_once 'config.php';

// ============================================
// FALLBACK KONSTANTA
// ============================================
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', $_SERVER['DOCUMENT_ROOT'] . '/siavo/uploads/');
}
if (!defined('UPLOAD_URL')) {
    define('UPLOAD_URL', '/siavo/uploads/');
}
if (!defined('FEEDBACK_DIR')) {
    define('FEEDBACK_DIR', $_SERVER['DOCUMENT_ROOT'] . '/siavo/uploads/feedback/');
}
if (!defined('FEEDBACK_URL')) {
    define('FEEDBACK_URL', '/siavo/uploads/feedback/');
}
if (!defined('MAX_FILE_SIZE')) {
    define('MAX_FILE_SIZE', 2 * 1024 * 1024);
}
if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png']);
}

// ============================================
// 1. FUNGSI DASAR
// ============================================

function sanitize($input) {
    return trim((string)$input);
}

function escape($input) {
    return htmlspecialchars((string)$input, ENT_QUOTES, 'UTF-8');
}

function validateNIM($nim) {
    return preg_match('/^[0-9]+$/', $nim);
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ============================================
// 2. FUNGSI TIKET & UPLOAD
// ============================================

function generateTicketNumber($jenis) {
    global $conn;
    $prefix = ($jenis == 'aspirasi') ? 'SIAVO' : 'ADV';

    $max_attempts = 10;
    $attempt = 0;

    do {
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $ticket = $prefix . '-' . $random;
        $attempt++;

        $stmt = $conn->prepare("SELECT id FROM laporan WHERE nomor_tiket = ?");
        $stmt->bind_param("s", $ticket);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        if ($exists && $attempt < $max_attempts) {
            continue;
        } elseif ($exists && $attempt >= $max_attempts) {
            $ticket = $prefix . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 4)) . date('s');
        }

        break;
    } while (true);

    return $ticket;
}

function uploadFiles($files, $laporan_id) {
    global $conn;
    $uploaded = [];
    $errors = [];

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    for ($i = 0; $i < count($files['name']); $i++) {
        if ($files['error'][$i] == UPLOAD_ERR_OK) {
            $filename = $files['name'][$i];
            $filesize = $files['size'][$i];
            $tmpname = $files['tmp_name'][$i];

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($ext, ALLOWED_EXTENSIONS)) {
                $errors[] = "File {$filename} memiliki ekstensi yang tidak diizinkan.";
                continue;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $tmpname);
            finfo_close($finfo);

            $allowed_mimes = [
                'pdf' => 'application/pdf',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png'
            ];

            if ($mime != $allowed_mimes[$ext]) {
                $errors[] = "File {$filename} memiliki tipe yang tidak sesuai.";
                continue;
            }

            if ($filesize > MAX_FILE_SIZE) {
                $errors[] = "File {$filename} melebihi batas ukuran 2MB.";
                continue;
            }

            $new_filename = uniqid() . '_' . time() . '.' . $ext;
            $path = UPLOAD_DIR . $new_filename;

            if (move_uploaded_file($tmpname, $path)) {
                $stmt = $conn->prepare("INSERT INTO dokumen_pendukung (laporan_id, nama_file_asli, nama_file_tersimpan, path_file, tipe_file, ukuran_file) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("issssi", $laporan_id, $filename, $new_filename, $path, $mime, $filesize);

                if ($stmt->execute()) {
                    $uploaded[] = $filename;
                } else {
                    $errors[] = "Gagal menyimpan data file {$filename} ke database.";
                }
                $stmt->close();
            } else {
                $errors[] = "Gagal mengupload file {$filename}.";
            }
        }
    }

    return ['uploaded' => $uploaded, 'errors' => $errors];
}

// ============================================
// 3. FUNGSI FEEDBACK (Bukti Formal)
// ============================================

function uploadFeedbackFile($file, $laporan_id) {
    global $conn;

    if ($file['error'] != UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal upload file. Kode error: ' . $file['error']];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return ['success' => false, 'message' => 'Hanya file PDF yang diperbolehkan.'];
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 2MB.'];
    }

    if (!is_dir(FEEDBACK_DIR)) {
        if (!mkdir(FEEDBACK_DIR, 0755, true)) {
            return ['success' => false, 'message' => 'Gagal membuat folder upload.'];
        }
    }

    $new_filename = 'feedback_' . $laporan_id . '_' . time() . '.pdf';
    $path = FEEDBACK_DIR . $new_filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
        $path_db = FEEDBACK_URL . $new_filename;
        $stmt = $conn->prepare("UPDATE laporan SET file_feedback = ? WHERE id = ?");
        $stmt->bind_param("si", $path_db, $laporan_id);
        if ($stmt->execute()) {
            $stmt->close();
            return ['success' => true, 'message' => 'File berhasil diupload.', 'path' => $path_db];
        }
        $stmt->close();
        if (file_exists($path)) {
            unlink($path);
        }
        return ['success' => false, 'message' => 'Gagal menyimpan path file ke database.'];
    }

    return ['success' => false, 'message' => 'Gagal memindahkan file ke folder tujuan.'];
}

function deleteFeedbackFile($laporan_id) {
    global $conn;

    $stmt = $conn->prepare("SELECT file_feedback FROM laporan WHERE id = ?");
    $stmt->bind_param("i", $laporan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || empty($row['file_feedback'])) {
        return ['success' => false, 'message' => 'Tidak ada file feedback.'];
    }

    $file_path = $_SERVER['DOCUMENT_ROOT'] . $row['file_feedback'];
    if (file_exists($file_path)) {
        unlink($file_path);
    }

    $stmt = $conn->prepare("UPDATE laporan SET file_feedback = NULL WHERE id = ?");
    $stmt->bind_param("i", $laporan_id);
    $stmt->execute();
    $stmt->close();

    return ['success' => true, 'message' => 'File berhasil dihapus.'];
}

// ============================================
// 4. FUNGSI STATUS & RIWAYAT
// ============================================

function addStatusHistory($laporan_id, $status, $keterangan = null, $petugas_id = null) {
    global $conn;
    $stmt = $conn->prepare("INSERT INTO riwayat_status (laporan_id, status, keterangan, petugas_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("issi", $laporan_id, $status, $keterangan, $petugas_id);
    return $stmt->execute();
}

function updateLaporanStatus($laporan_id, $status, $keterangan = null, $petugas_id = null) {
    global $conn;
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE laporan SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $laporan_id);
        $stmt->execute();
        $stmt->close();
        addStatusHistory($laporan_id, $status, $keterangan, $petugas_id);
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// ============================================
// 5. BADGE & LABEL
// ============================================

function getStatusBadge($status) {
    $status = strtolower(trim($status ?? ''));
    if (empty($status)) {
        return '<span class="badge-status-modern unknown">Tidak Diketahui</span>';
    }
    $labels = [
        'pengajuan'     => 'Pengajuan',
        'verifikasi'    => 'Verifikasi',
        'tindak_lanjut' => 'Tindak Lanjut',
        'selesai'       => 'Selesai'
    ];
    $label = $labels[$status] ?? ucfirst($status);
    return '<span class="badge-status-modern ' . $status . '">' . $label . '</span>';
}

function getStatusLabel($status) {
    if (empty($status)) return 'Tidak Diketahui';
    $labels = [
        'pengajuan'     => 'Pengajuan',
        'verifikasi'    => 'Verifikasi',
        'tindak_lanjut' => 'Tindak Lanjut',
        'selesai'       => 'Selesai'
    ];
    return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
}

function getPrioritasBadge($prioritas) {
    if (empty($prioritas)) {
        return '<span class="badge-priority unknown">Tidak Diketahui</span>';
    }
    $labels = [
        'rendah' => 'Rendah',
        'sedang' => 'Sedang',
        'tinggi' => 'Tinggi',
        'urgent' => 'Urgent'
    ];
    $label = $labels[$prioritas] ?? ucfirst($prioritas);
    return '<span class="badge-priority ' . $prioritas . '">' . $label . '</span>';
}

// ============================================
// 6. BADGE MODERN
// ============================================

function getStatusBadgeModern($status) {
    return getStatusBadge($status);
}

function getPriorityBadgeModern($prioritas) {
    return getPrioritasBadge($prioritas);
}

// ============================================
// 7. STATISTIK
// ============================================

function getLaporanStats() {
    global $conn;
    $stats = [
        'total' => 0,
        'pengajuan' => 0,
        'verifikasi' => 0,
        'tindak_lanjut' => 0,
        'selesai' => 0
    ];
    $sql = "SELECT status, COUNT(*) as count FROM laporan GROUP BY status";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $status = $row['status'];
            if (isset($stats[$status])) {
                $stats[$status] = $row['count'];
            }
            $stats['total'] += $row['count'];
        }
    }
    return $stats;
}

// ============================================
// 8. CSRF & SESSION
// ============================================

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function requireRole($role) {
    if (!isLoggedIn()) {
        redirect('login.php');
    }
    if (getUserRole() !== $role) {
        redirect('index.php');
    }
}

// ============================================
// 9. USER DATA
// ============================================

function getUserData($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// ============================================
// 10. CLEANUP KEGIATAN LEWAT (> 2 hari)
// Kegiatan hari ini, kemarin, dan 2 hari lewat
// masih ditampilkan biar user tau event baru aja lewat.
// ============================================

function cleanupExpiredKegiatan() {
    global $conn;

    $img_dir = $_SERVER['DOCUMENT_ROOT'] . '/siavo/assets/img/kegiatan/';

    // 1. Ambil daftar gambar yang akan dihapus
    $files = [];
    $stmt = $conn->prepare("SELECT gambar FROM kegiatan WHERE tanggal < DATE_SUB(CURDATE(), INTERVAL 2 DAY)");
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['gambar'])) $files[] = $row['gambar'];
        }
        $stmt->close();
    }

    // 2. Hapus row dari database
    $deleted = 0;
    $stmt = $conn->prepare("DELETE FROM kegiatan WHERE tanggal < DATE_SUB(CURDATE(), INTERVAL 2 DAY)");
    if ($stmt) {
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
    }

    // 3. Hapus file gambar fisik
    foreach ($files as $f) {
        $path = $img_dir . $f;
        if (file_exists($path)) @unlink($path);
    }

    return $deleted;
}

function autoCleanupKegiatan() {
    $lock_file = sys_get_temp_dir() . '/siavo_kegiatan_cleanup.lock';
    $interval  = 6 * 3600; // 6 jam

    if (file_exists($lock_file) && (time() - filemtime($lock_file)) < $interval) {
        return 0;
    }

    touch($lock_file);
    return cleanupExpiredKegiatan();
}

function countExpiredKegiatan() {
    global $conn;
    $r = $conn->query("SELECT COUNT(*) c FROM kegiatan WHERE tanggal < DATE_SUB(CURDATE(), INTERVAL 2 DAY)");
    return $r ? (int)$r->fetch_assoc()['c'] : 0;
}