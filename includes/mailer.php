<?php
// ============================================
// SIAVO — Mailer Wrapper (Composer Version)
// ============================================

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Kirim email HTML via SMTP
 */
function sendEmailNotification($to, $subject, $body_html, $to_name = '') {
    $config_file = __DIR__ . '/mail-config.php';
    if (!file_exists($config_file)) {
        error_log('SIAVO Mailer: mail-config.php tidak ditemukan');
        return false;
    }

    $cfg = require $config_file;
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = $cfg['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $cfg['smtp_user'];
        $mail->Password   = $cfg['smtp_pass'];
        $mail->SMTPSecure = $cfg['smtp_secure'];
        $mail->Port       = $cfg['smtp_port'];
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 15;

        // Debug (opsional)
        if (!empty($cfg['debug'])) {
            $mail->SMTPDebug   = 2;
            $mail->Debugoutput = function ($str, $level) {
                error_log("SMTP[$level]: $str");
            };
        }

        // Pengirim & penerima
        $mail->setFrom($cfg['from_email'], $cfg['from_name']);
        $mail->addAddress($to, $to_name);

        // Konten
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body_html));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('SIAVO Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Wrapper layout email SIAVO
 */
function emailLayout($title, $content_html, $footer_note = '') {
    $year = date('Y');
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: 'Segoe UI', Arial, sans-serif; background: #fdf7f7; margin: 0; padding: 20px; }
    .container { max-width: 600px; margin: 0 auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(208,0,24,.1); }
    .header { background: #4e0009; color: #fff; padding: 24px; text-align: center; }
    .header h1 { margin: 0; font-size: 20px; font-weight: 800; }
    .header p { margin: 6px 0 0; opacity: .85; font-size: 13px; }
    .body { padding: 24px 28px; color: #0a0a0a; line-height: 1.6; font-size: 14px; }
    .body p { margin: 0 0 12px; }
    .ticket-box { background: #fde8ea; border: 2px dashed #d00018; border-radius: 10px; padding: 16px; text-align: center; margin: 16px 0; }
    .ticket-box .label { font-size: 11px; text-transform: uppercase; color: #5a4a4a; letter-spacing: 1px; font-weight: 700; }
    .ticket-box .value { font-size: 22px; font-weight: 800; color: #0a0a0a; margin-top: 6px; letter-spacing: 2px; }
    .info-table { width: 100%; border-collapse: collapse; margin: 16px 0; }
    .info-table td { padding: 8px 0; border-bottom: 1px solid #f0dada; font-size: 13px; vertical-align: top; }
    .info-table td:first-child { color: #5a4a4a; width: 130px; font-weight: 500; }
    .info-table td:last-child { font-weight: 600; }
    .isi-box { background: #f9f0f0; border-left: 4px solid #d00018; padding: 14px 16px; border-radius: 0 8px 8px 0; margin: 16px 0; font-size: 13px; line-height: 1.7; }
    .btn { display: inline-block; background: #d00018; color: #fff !important; text-decoration: none; padding: 12px 28px; border-radius: 999px; font-weight: 700; margin-top: 16px; font-size: 13px; }
    .badge-status { display: inline-block; padding: 4px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
    .badge-pengajuan    { background: #e6eefe; color: #2563eb; }
    .badge-verifikasi   { background: #fdf0d9; color: #b45309; }
    .badge-tindak_lanjut { background: #dcf5f2; color: #0d9488; }
    .badge-selesai      { background: #dcf5e4; color: #15803d; }
    .footer { background: #f9f0f0; padding: 16px 24px; text-align: center; font-size: 12px; color: #5a4a4a; line-height: 1.5; }
    .footer small { display: block; margin-top: 4px; opacity: .7; }
</style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{$title}</h1>
            <p>SIAVO — Sistem Informasi Aspirasi dan Advokasi Online</p>
        </div>
        <div class="body">
            {$content_html}
        </div>
        <div class="footer">
            {$footer_note}
            Email otomatis dari SIAVO. Jangan balas email ini.<br>
            <small>© {$year} SIAVO — Suara Mahasiswa, Aksi Nyata</small>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Template email: Laporan baru masuk (ke admin)
 */
function emailTemplateLaporanBaru($laporan) {
    $ticket   = htmlspecialchars($laporan['nomor_tiket']);
    $judul    = htmlspecialchars($laporan['judul']);
    $nama     = htmlspecialchars($laporan['nama_pelapor']);
    $nim      = htmlspecialchars($laporan['nim']);
    $prodi    = htmlspecialchars($laporan['prodi']);
    $kategori = htmlspecialchars($laporan['nama_kategori'] ?? '-');
    $isi      = nl2br(htmlspecialchars(mb_strimwidth($laporan['isi'], 0, 400, '...')));
    $tanggal  = date('d M Y, H:i', strtotime($laporan['created_at']));
    $url      = APP_URL . 'admin/dashboard.php';

    $content = <<<HTML
<p>Halo Admin SEMA,</p>
<p>Ada <strong>laporan baru</strong> yang masuk ke sistem SIAVO. Berikut detailnya:</p>

<div class="ticket-box">
    <div class="label">Nomor Tiket</div>
    <div class="value">{$ticket}</div>
</div>

<table class="info-table">
    <tr><td>Judul</td><td>{$judul}</td></tr>
    <tr><td>Kategori</td><td>{$kategori}</td></tr>
    <tr><td>Pelapor</td><td>{$nama}</td></tr>
    <tr><td>NIM</td><td>{$nim}</td></tr>
    <tr><td>Prodi</td><td>{$prodi}</td></tr>
    <tr><td>Waktu</td><td>{$tanggal}</td></tr>
</table>

<p style="margin-top:18px"><strong>Isi Laporan:</strong></p>
<div class="isi-box">{$isi}</div>

<p style="text-align:center;margin-top:24px">
    <a href="{$url}" class="btn">Buka Dashboard Admin</a>
</p>
HTML;

    return emailLayout('🔔 Laporan Baru Masuk', $content);
}

/**
 * Template email: Status laporan berubah (ke mahasiswa)
 */
function emailTemplateStatusBerubah($laporan, $status_baru, $keterangan = '') {
    $ticket   = htmlspecialchars($laporan['nomor_tiket']);
    $judul    = htmlspecialchars($laporan['judul']);
    $nama     = htmlspecialchars($laporan['nama_pelapor']);
    $kategori = htmlspecialchars($laporan['nama_kategori'] ?? '-');
    $status   = htmlspecialchars($status_baru);
    $ket      = $keterangan ? nl2br(htmlspecialchars($keterangan)) : '';
    $tanggal  = date('d M Y, H:i');

    $status_labels = [
        'pengajuan'     => 'Pengajuan',
        'verifikasi'    => 'Verifikasi',
        'tindak_lanjut' => 'Tindak Lanjut',
        'selesai'       => 'Selesai',
    ];
    $status_label = $status_labels[$status_baru] ?? ucfirst($status_baru);
    $badge_class  = 'badge-' . $status;

    $ket_block = '';
    if ($ket) {
        $ket_block = <<<HTML
<p style="margin-top:18px"><strong>Keterangan dari Admin:</strong></p>
<div class="isi-box">{$ket}</div>
HTML;
    }

    $url = APP_URL . 'mahasiswa/tracking.php?ticket=' . urlencode($laporan['nomor_tiket']);

    $content = <<<HTML
<p>Halo <strong>{$nama}</strong>,</p>
<p>Laporan Anda dengan nomor tiket <strong>{$ticket}</strong> telah diperbarui statusnya.</p>

<table class="info-table">
    <tr><td>Judul</td><td>{$judul}</td></tr>
    <tr><td>Kategori</td><td>{$kategori}</td></tr>
    <tr><td>Status Baru</td><td><span class="badge-status {$badge_class}">{$status_label}</span></td></tr>
    <tr><td>Waktu Update</td><td>{$tanggal}</td></tr>
</table>

{$ket_block}

<p style="text-align:center;margin-top:24px">
    <a href="{$url}" class="btn">Lihat Detail Laporan</a>
</p>
HTML;

    return emailLayout('📢 Update Status Laporan', $content);
}