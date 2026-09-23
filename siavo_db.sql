-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Sep 2026 pada 08.14
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `siavo_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `artikel`
--

CREATE TABLE `artikel` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `konten` text NOT NULL,
  `kategori_info` enum('sop','panduan','beasiswa','faq') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `artikel`
--

INSERT INTO `artikel` (`id`, `judul`, `konten`, `kategori_info`, `created_at`) VALUES
(1, 'Alur Pengajuan Advokasi Mahasiswa', '<p>Ada dua hal penting yang sering diabaikan mahasiswa: cara menyampaikan laporan dan cara memantau perkembangannya.</p>\r\n\r\n<h4>1. Hubungi wali kelas</h4>\r\n<p>Pastikan masalahmu sudah jelas dan bisa didiskusikan lewat jalur internal prodi. Kumpulkan bukti yang mendukung, seperti tangkapan layar, foto, atau surat resmi.</p>\r\n\r\n<h4>2. Tahapan status laporan</h4>\r\n<ol>\r\n<li><strong>Pengajuan:</strong> Laporan diterima dan diberi nomor tiket unik.</li>\r\n<li><strong>Verifikasi:</strong> Admin memeriksa kelengkapan dan kebenaran data laporan.</li>\r\n<li><strong>Tindak lanjut:</strong> Laporan diteruskan ke pihak kampus yang berwenang.</li>\r\n<li><strong>Selesai:</strong> Admin menutup laporan dengan keterangan penyelesaian.</li>\r\n</ol>\r\n\r\n<div class=\"note\"><strong>Catatan:</strong> Jika pengaduan sudah mencapai 30 hari tanpa update, kamu bisa datang langsung ke sekretariat SEMA untuk meminta keterangan.</div>\r\n\r\n<h4>3. Dokumen yang perlu disiapkan</h4>\r\n<p>Siapkan identitas mahasiswa, kronologi kejadian, dan bukti pendukung. Semua dokumen diupload dalam format PDF, JPG, atau PNG dengan ukuran maksimal 2 MB per file.</p>', 'sop', '2026-09-14 09:35:17'),
(2, 'Standar Waktu Penanganan Laporan', '<p>Setiap laporan yang masuk ke SIAVO memiliki batas waktu penanganan yang jelas.</p>\r\n\r\n<h4>Batas waktu per tahap</h4>\r\n<ol>\r\n<li><strong>Verifikasi:</strong> Maksimal 7 hari kerja sejak laporan masuk.</li>\r\n<li><strong>Tindak lanjut:</strong> Maksimal 14 hari kerja sejak verifikasi selesai.</li>\r\n<li><strong>Penyelesaian:</strong> Tergantung kompleksitas, maksimal 60 hari.</li>\r\n</ol>\r\n\r\n<div class=\"note\">Jika laporan belum selesai setelah 60 hari, mahasiswa berhak meminta eskalasi ke pimpinan fakultas.</div>', 'sop', '2026-09-07 09:35:17'),
(3, 'Hak dan Kewajiban Pelapor', '<p>Mahasiswa yang melapor memiliki hak dan kewajiban yang harus dipahami.</p>\r\n\r\n<h4>Hak pelapor</h4>\r\n<ul>\r\n<li>Mendapat nomor tiket sebagai bukti laporan.</li>\r\n<li>Melacak status laporan kapan saja.</li>\r\n<li>Identitas dilindungi dan tidak dipublikasikan.</li>\r\n</ul>\r\n\r\n<h4>Kewajiban pelapor</h4>\r\n<ul>\r\n<li>Memberikan informasi yang benar.</li>\r\n<li>Melampirkan bukti yang valid.</li>\r\n<li>Tidak menyebarkan laporan ke pihak yang tidak berkepentingan.</li>\r\n</ul>', 'sop', '2026-08-30 09:35:17'),
(4, 'Cara Menulis Laporan yang Efektif', '<p>Laporan yang baik adalah laporan yang jelas, ringkas, dan didukung bukti.</p>\r\n\r\n<h4>1. Tulis judul yang spesifik</h4>\r\n<p>Hindari judul seperti \"Masalah Kampus\". Gunakan \"Kendala Akses WiFi di Gedung B Lantai 3\".</p>\r\n\r\n<h4>2. Jelaskan kronologi</h4>\r\n<p>Urutkan kejadian berdasarkan waktu. Sertakan tanggal, lokasi, dan pihak yang terlibat.</p>\r\n\r\n<h4>3. Lampirkan bukti</h4>\r\n<p>Foto, tangkapan layar, atau dokumen resmi akan sangat membantu proses verifikasi.</p>\r\n\r\n<div class=\"note\">Laporan yang lengkap mempercepat proses verifikasi hingga 2x lebih cepat.</div>', 'panduan', '2026-09-16 09:35:17'),
(5, 'Panduan Upload Bukti Pendukung', '<p>Bukti pendukung adalah kunci utama agar laporanmu tidak ditolak.</p>\r\n\r\n<h4>Format yang diterima</h4>\r\n<ul>\r\n<li>PDF (maks 2 MB)</li>\r\n<li>JPG / JPEG (maks 2 MB)</li>\r\n<li>PNG (maks 2 MB)</li>\r\n</ul>\r\n\r\n<h4>Tips kompres file</h4>\r\n<p>Kalau ukuran file melebihi 2 MB, gunakan tool kompres online atau screenshot ulang dengan resolusi lebih rendah.</p>', 'panduan', '2026-09-11 09:35:17'),
(8, 'Apakah Identitas Saya Terlindungi?', '<p>Ya. Identitas pelapor hanya diketahui oleh admin SIAVO yang menangani laporan tersebut.</p>\r\n\r\n<h4>Yang dilihat publik</h4>\r\n<p>Hanya statistik angka — bukan isi laporan atau identitas pelapor.</p>\r\n\r\n<h4>Yang dilihat admin</h4>\r\n<p>Admin bisa melihat identitas lengkap untuk keperluan verifikasi dan tindak lanjut.</p>\r\n\r\n<div class=\"note\">Kami tidak pernah membagikan identitas pelapor ke pihak ketiga tanpa izin.</div>', 'faq', '2026-09-15 09:35:17'),
(9, 'Berapa Lama Laporan Saya Diproses?', '<p>Rata-rata laporan diverifikasi dalam 7 hari kerja sejak diajukan.</p>\r\n\r\n<h4>Kalau lebih dari 7 hari</h4>\r\n<p>Cek status di menu <strong>Laporan Saya</strong>. Kalau status masih \"Pengajuan\" setelah 7 hari, hubungi admin via kontak yang tertera.</p>\r\n\r\n<h4>Kalau stuck di \"Tindak Lanjut\"</h4>\r\n<p>Biasanya karena menunggu respons dari unit kampus terkait. Admin akan update keterangan di timeline.</p>', 'faq', '2026-09-09 09:35:17'),
(10, 'Bagaimana Kalau Lupa Nomor Tiket?', '<p>Masuk ke akunmu, buka menu <strong>Laporan Saya</strong>. Semua laporan beserta nomor tiketnya ada di sana.</p>\r\n\r\n<h4>Kalau akun juga lupa</h4>\r\n<p>Hubungi admin SIAVO dengan menyebutkan NIM dan nama lengkap. Admin akan membantu pencarian.</p>', 'faq', '2026-09-01 09:35:17'),
(11, 'Hari Pendidikan Nasional', '<h4>hgfhgfhfdfdhfhfygf</h4><div><p><ol><li>yfdhgdhyfjfjghj</li><li>jguuyttuytuy</li><li>yfyfytyfytfty</li></ol><h4>,mndkjsahdkjsahdhaskd</h4><div><p><ol><li>lsadkjhasdkhasdkha</li><li>kasjdashdiuashd</li><li>jasgdasgsduygd</li></ol><div><div class=\"note\"><strong>Catatan:</strong>&nbsp;pukimay</div><p><br></p><p>dkasjdjkagdjasgdjasgdjasgdjasd</p><p>asdjkgasuydgasudguasgdsad</p><p>askdguaystdiusatydasd</p></div></p></div></p></div>', 'beasiswa', '2026-09-22 05:08:33');

-- --------------------------------------------------------

--
-- Struktur dari tabel `dokumen_pendukung`
--

CREATE TABLE `dokumen_pendukung` (
  `id` int(11) NOT NULL,
  `laporan_id` int(11) NOT NULL,
  `nama_file_asli` varchar(255) NOT NULL,
  `nama_file_tersimpan` varchar(255) NOT NULL,
  `path_file` varchar(255) NOT NULL,
  `tipe_file` varchar(100) NOT NULL,
  `ukuran_file` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `nama` varchar(200) NOT NULL,
  `deskripsi` text NOT NULL,
  `tipe` enum('sema','siavo') NOT NULL,
  `tanggal` date NOT NULL,
  `waktu` time DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nama`, `deskripsi`, `tipe`, `tanggal`, `waktu`, `gambar`, `created_at`) VALUES
(4, 'Launching SIAVO', 'Peluncuran resmi Sistem Informasi Aspirasi dan Advokasi Online (SIAVO). Platform ini hadir sebagai jembatan digital antara mahasiswa dan institusi kampus untuk menyampaikan aspirasi, keluhan, dan pengajuan advokasi secara transparan dan terdokumentasi.', 'siavo', '2026-09-24', '13:00:00', 'kegiatan_6ab36d7968e50_1790143865.jpeg', '2026-09-23 06:00:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan`
--

CREATE TABLE `laporan` (
  `id` int(11) NOT NULL,
  `nomor_tiket` varchar(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `nama_pelapor` varchar(100) DEFAULT NULL,
  `nim` varchar(20) NOT NULL,
  `prodi` varchar(100) NOT NULL,
  `kontak` varchar(20) NOT NULL,
  `kategori_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `kronologi` text DEFAULT NULL,
  `status` enum('pengajuan','verifikasi','tindak_lanjut','selesai') DEFAULT 'pengajuan',
  `prioritas` enum('rendah','sedang','tinggi','urgent') DEFAULT 'sedang',
  `feedback_admin` text DEFAULT NULL,
  `file_feedback` varchar(255) DEFAULT NULL,
  `file_tindak_lanjut` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `created_by`, `created_at`) VALUES
(1, 'SIAVO Resmi Diluncurkan!', 'Sistem Informasi Aspirasi dan Advokasi Online (SIAVO) resmi diluncurkan untuk memfasilitasi mahasiswa dalam menyampaikan aspirasi dan pengajuan advokasi.', 1, '2026-06-17 21:16:20'),
(2, 'asdfghjkl', 'ececrvtvtvntvrvf', 1, '2026-09-22 05:05:40'),
(3, 'asdfghjkl', 'ececrvtvtvntvrvf', 1, '2026-09-22 05:06:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `riwayat_status`
--

CREATE TABLE `riwayat_status` (
  `id` int(11) NOT NULL,
  `laporan_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `keterangan` text DEFAULT NULL,
  `petugas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nim` varchar(20) DEFAULT NULL,
  `prodi` varchar(100) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `role` enum('admin','mahasiswa') DEFAULT 'mahasiswa',
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `nim`, `prodi`, `kontak`, `role`, `foto_profil`, `created_at`) VALUES
(1, 'admin', '$2y$10$T0vpB3kpYFsoBjaP2w94oufYB.gMlw.BmO4vDSYr6OXWibcLG3zIu', 'Administrator', 'ibrahimtyo39@gmail.com', NULL, NULL, NULL, 'admin', NULL, '2026-06-18 04:16:20');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `artikel`
--
ALTER TABLE `artikel`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `dokumen_pendukung`
--
ALTER TABLE `dokumen_pendukung`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_id` (`laporan_id`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_tiket` (`nomor_tiket`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indeks untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `laporan_id` (`laporan_id`),
  ADD KEY `petugas_id` (`petugas_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `artikel`
--
ALTER TABLE `artikel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `dokumen_pendukung`
--
ALTER TABLE `dokumen_pendukung`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `dokumen_pendukung`
--
ALTER TABLE `dokumen_pendukung`
  ADD CONSTRAINT `dokumen_pendukung_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`);

--
-- Ketidakleluasaan untuk tabel `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Ketidakleluasaan untuk tabel `riwayat_status`
--
ALTER TABLE `riwayat_status`
  ADD CONSTRAINT `riwayat_status_ibfk_1` FOREIGN KEY (`laporan_id`) REFERENCES `laporan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `riwayat_status_ibfk_2` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
