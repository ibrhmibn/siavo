<?php
// ========================================
// LANDING PAGE SIAVO - GUEST (Belum Login)
// ========================================

$page_title = 'Beranda';
$page_active = 'beranda';

require_once 'includes/config.php';
require_once 'includes/functions.php';

// ===== Ambil Data Statistik =====
$stats = [
    'total'    => 0,
    'selesai'  => 0,
    'diproses' => 0,
];

$queries = [
    'total'    => "SELECT COUNT(*) as count FROM laporan",
    'selesai'  => "SELECT COUNT(*) as count FROM laporan WHERE status = 'selesai'",
    'diproses' => "SELECT COUNT(*) as count FROM laporan WHERE status IN ('pengajuan', 'verifikasi', 'tindak_lanjut')",
];

foreach ($queries as $key => $sql) {
    $result = $conn->query($sql);
    if ($result) {
        $row = $result->fetch_assoc();
        $stats[$key] = $row['count'];
    }
}

// ===== Ambil Pengumuman Terbaru =====
$pengumuman = [];
$sql = "SELECT * FROM pengumuman ORDER BY created_at DESC LIMIT 4";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pengumuman[] = $row;
    }
}

// ===== Ambil Kegiatan Terbaru =====
$kegiatan = [];
$sql = "SELECT * FROM kegiatan ORDER BY tanggal DESC LIMIT 3";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $kegiatan[] = $row;
    }
}

$conn->close();
include 'includes/header_guest.php';
?>

<!-- HERO -->
<div class="hero">
    <div class="wrap">
        <div>
            <h1>Suara Mahasiswa, <span class="hl">Aksi Nyata.</span></h1>
            <p class="lead">Sampaikan aspirasi, keluhan, atau pengajuan advokasi ke SEMA. Setiap laporan
                mendapat nomor tiket, dan setiap perubahan statusnya bisa kamu lihat.</p>
            <div class="hero-cta">
                <a href="<?php echo APP_URL; ?>login.php" class="btn btn-red btn-lg">Kirim aspirasi</a>
                <a href="#cara-kerja" class="btn btn-ghost btn-lg">Lihat alurnya</a>
            </div>
            <p class="hero-note"><b>Belum punya akun?</b> Panduan dan informasi bisa dibaca tanpa mendaftar. Laporanmu
                hanya bisa dilihat olehmu.</p>
        </div>

        <!-- SIMULASI TRACKING -->
        <div class="ticket" aria-labelledby="trkTitle">
            <div class="ticket-head">
                <span id="trkTitle">Simulasi pelacakan</span>
                <span class="pulse">Contoh, bukan data asli</span>
            </div>
            <div class="tcode">SIAVO-<span>••••••</span></div>
            <div class="tabs" role="tablist" aria-label="Pilih tahap simulasi" style="padding-bottom:18px">
                <button type="button" role="tab" data-i="0" class="b-pengajuan">Pengajuan</button>
                <button type="button" role="tab" data-i="1" class="b-verifikasi">Verifikasi</button>
                <button type="button" role="tab" data-i="2" class="b-tindak">Tindak lanjut</button>
                <button type="button" role="tab" data-i="3" class="b-selesai">Selesai</button>
            </div>
            <div class="tresult" id="trkOut" aria-live="polite"></div>
            <div class="lock">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="4" y="11" width="16" height="10" rx="2" />
                    <path d="M8 11V7a4 4 0 0 1 8 0v4" />
                </svg>
                <span>Laporan aslimu bersifat rahasia dan hanya bisa dilacak oleh kamu setelah masuk.</span>
            </div>
            <a href="<?php echo APP_URL; ?>login.php" class="btn btn-red" style="width:100%;margin-top:14px">Masuk untuk
                melacak laporanmu</a>
        </div>
    </div>
</div>

<!-- STATS -->
<div class="stats" id="stats">
    <div class="wrap">
        <div class="stat"><strong data-n="<?php echo $stats['total']; ?>">0</strong><span>laporan masuk sejak
                dibuka</span></div>
        <div class="stat"><strong data-n="<?php echo $stats['selesai']; ?>">0</strong><span>laporan sudah selesai</span>
        </div>
        <div class="stat"><strong data-n="7" data-suffix=" hari">0</strong><span>rata-rata sampai verifikasi</span>
        </div>
        <div class="stat"><strong
                data-n="<?php echo $stats['total'] > 0 ? round($stats['selesai'] / $stats['total'] * 100) : 0; ?>"
                data-suffix="%">0</strong><span>tingkat penyelesaian</span></div>
    </div>
</div>

<!-- HOW -->
<section class="how" id="cara-kerja">
    <div class="wrap">
        <div class="sec-head">
            <h2>Empat tahap dari laporan sampai selesai.</h2>
            <p>Setiap laporan melewati alur yang sama. Tidak ada yang dilewati dan tidak ada yang hilang.</p>
        </div>
        <div class="flow">
            <div>
                <div class="n">1</div>
                <h3>Pengajuan</h3>
                <p>Kamu memilih kategori, menulis laporan, dan melampirkan bukti. Sistem langsung membuat nomor tiket.
                </p><span class="who">Mahasiswa</span>
            </div>
            <div>
                <div class="n">2</div>
                <h3>Verifikasi</h3>
                <p>Admin memeriksa kelengkapan dan kebenaran data laporanmu.</p><span class="who">Admin SEMA</span>
            </div>
            <div>
                <div class="n">3</div>
                <h3>Tindak lanjut</h3>
                <p>Laporan dikoordinasikan ke pihak kampus yang berwenang, lengkap dengan catatan tiap langkah.</p><span
                    class="who">Admin SEMA</span>
            </div>
            <div>
                <div class="n">4</div>
                <h3>Selesai</h3>
                <p>Admin menulis keterangan penyelesaian dan mengunggah dokumen resmi dari institusi.</p><span
                    class="who">Admin SEMA</span>
            </div>
        </div>
    </div>
</section>

<!-- BENTO -->
<section id="fitur">
    <div class="wrap">
        <div class="sec-head">
            <h2>Dibuat supaya kamu tahu laporanmu sedang di mana.</h2>
            <p>Bukan kotak saran yang tidak pernah dibuka. Setiap fitur ada untuk membuat prosesnya terlihat.</p>
        </div>
        <div class="bento">
            <div class="cell c1">
                <div>
                    <div class="code">SIAVO<em>-</em>A7F3D2</div>
                    <h3>Satu laporan, satu nomor tiket</h3>
                </div>
                <p>Nomornya acak sehingga tidak bisa ditebak. Simpan nomor itu, lalu masuk ke akunmu untuk melacak kapan
                    saja tanpa perlu bertanya ke siapa pun.</p>
            </div>
            <div class="cell c2">
                <h3>Riwayat status transparan</h3>
                <p>Tiap perubahan status tercatat lengkap dengan waktu dan keterangan admin. Hanya kamu dan admin
                    penanganmu yang bisa membacanya.</p>
                <div class="pills">
                    <span class="badge b-pengajuan">Pengajuan</span>
                    <span class="badge b-verifikasi">Verifikasi</span>
                    <span class="badge b-tindak">Tindak lanjut</span>
                    <span class="badge b-selesai">Selesai</span>
                </div>
            </div>
            <div class="cell c3">
                <h3>Setiap aksi admin tercatat</h3>
                <p>Siapa mengubah status apa dan kapan, semuanya tersimpan. Admin tidak bisa menutup laporan tanpa
                    keterangan penyelesaian.</p>
            </div>
            <div class="cell c4">
                <h3>Bukti resmi berupa PDF</h3>
                <p>Dokumen tanggapan dari institusi diunggah langsung ke laporanmu.</p>
            </div>
            <div class="cell c5">
                <h3>Rekap untuk lembaga</h3>
                <p>Admin melihat tren 6 bulan dan kategori yang paling sering dilaporkan.</p>
                <div class="minibars" aria-hidden="true">
                    <i style="height:34%"></i><i style="height:52%"></i><i style="height:41%"></i>
                    <i style="height:70%"></i><i style="height:58%"></i><i style="height:92%"></i>
                </div>
            </div>
            <div class="cell c6">
                <h3>Delapan kategori</h3>
                <p>Akademik, fasilitas, beasiswa, kemahasiswaan, dan lainnya, jadi laporanmu sampai ke orang yang tepat.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- INFO / FAQ -->
<section id="informasi" style="background:var(--surface-2)">
    <div class="wrap split">
        <div>
            <div class="sec-head" style="margin-bottom:0">
                <h2>Bingung mau lapor ke mana? Mulai dari sini.</h2>
                <p>Pusat Informasi berisi panduan yang bisa dibaca siapa saja, tanpa login.</p>
            </div>
            <div class="cats">
                <a href="<?php echo APP_URL; ?>informasi.php?kategori=sop" class="cat">SOP pengaduan<small>Alur dan
                        batas waktu</small></a>
                <a href="<?php echo APP_URL; ?>informasi.php?kategori=panduan" class="cat">Panduan<small>Cara mengisi
                        laporan</small></a>
                <a href="<?php echo APP_URL; ?>informasi.php?kategori=beasiswa" class="cat">Beasiswa<small>Info dan
                        syarat terbaru</small></a>
                <a href="<?php echo APP_URL; ?>informasi.php?kategori=faq" class="cat">FAQ<small>Pertanyaan yang sering
                        muncul</small></a>
            </div>
        </div>
        <div>
            <details open>
                <summary>Apakah identitas saya terlihat oleh mahasiswa lain?</summary>
                <p>Tidak. Laporanmu hanya bisa dilihat oleh kamu dan admin yang menanganinya. Statistik yang tampil di
                    halaman publik hanya berupa angka.</p>
            </details>
            <details>
                <summary>Berapa lama laporan saya diproses?</summary>
                <p>Verifikasi rata-rata selesai dalam 7 hari. Tindak lanjut bergantung pada pihak kampus yang terlibat,
                    tapi setiap perkembangannya akan muncul di timeline.</p>
            </details>
            <details>
                <summary>Bukti apa yang boleh dilampirkan?</summary>
                <p>Berkas PDF, JPG, atau PNG dengan ukuran maksimal 2 MB per file, misalnya tangkapan layar, surat, atau
                    foto kondisi fasilitas.</p>
            </details>
            <details>
                <summary>Saya lupa nomor tiket. Bagaimana cara melacaknya?</summary>
                <p>Masuk ke akunmu, lalu buka menu Laporan Saya. Semua laporan beserta nomor tiketnya ada di sana.</p>
            </details>
        </div>
    </div>
</section>

<!-- NEWS -->
<section id="kabar">
    <div class="wrap">
        <div class="sec-head">
            <h2>Kabar terbaru dari SEMA.</h2>
        </div>
        <div class="news">
            <div>
                <?php if (empty($pengumuman)): ?>
                <div class="ann">
                    <p>Belum ada pengumuman.</p>
                </div>
                <?php else: foreach ($pengumuman as $i => $p): ?>
                <div class="ann <?php echo $i === 0 ? 'pin' : ''; ?>">
                    <time><?php echo $i === 0 ? 'Disematkan · ' : ''; ?><?php echo date('d F Y', strtotime($p['created_at'])); ?></time>
                    <h3 style="margin-top:6px"><?php echo htmlspecialchars($p['judul']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars(substr($p['isi'], 0, 160))); ?><?php echo strlen($p['isi']) > 160 ? '...' : ''; ?>
                    </p>
                </div>
                <?php endforeach; endif; ?>
            </div>
            <div class="ann" style="padding-bottom:10px">
                <h3 style="margin-bottom:20px">Kegiatan mendatang</h3>
                <?php if (empty($kegiatan)): ?>
                <p style="color:var(--ink-2);font-size:.9rem">Belum ada kegiatan.</p>
                <?php else: foreach ($kegiatan as $k): ?>
                <div class="ev">
                    <div class="date">
                        <?php echo date('d', strtotime($k['tanggal'])); ?>
                        <small><?php echo strtoupper(date('M', strtotime($k['tanggal']))); ?></small>
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($k['nama']); ?></h3>
                        <p><?php echo ucfirst($k['tipe']); ?> · Sisa
                            <?php echo $k['sisa']; ?>/<?php echo $k['kuota']; ?></p>
                    </div>
                </div>
                <?php endforeach; endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<div class="cta">
    <div class="wrap">
        <div class="cta-box">
            <h2>Ada yang mengganjal? Laporkan sekarang.</h2>
            <p>Daftar dengan email kampusmu dan kirim laporan pertamamu dalam beberapa menit.</p>
            <a href="<?php echo APP_URL; ?>register.php" class="btn btn-red btn-lg">Buat akun</a>
        </div>
    </div>
</div>

<?php include 'includes/footer_guest.php'; ?>

<script>
/* ===== SIMULASI TRACKING ===== */
var SIM = {
    judul: 'Contoh: kendala akses jaringan di perpustakaan',
    kat: 'Fasilitas',
    log: [{
            s: 'pengajuan',
            t: 'Hari 1',
            n: 'Laporan diterima dan nomor tiket dibuat otomatis.'
        },
        {
            s: 'verifikasi',
            t: 'Hari 3',
            n: 'Admin memeriksa kelengkapan data dan bukti.'
        },
        {
            s: 'tindak',
            t: 'Hari 6',
            n: 'Diteruskan ke unit terkait beserta catatan koordinasi.'
        },
        {
            s: 'selesai',
            t: 'Hari 12',
            n: 'Masalah ditangani. Dokumen tanggapan resmi dilampirkan (PDF).'
        }
    ]
};
var LBL = {
    pengajuan: ['Pengajuan', 'b-pengajuan', 'var(--blue)'],
    verifikasi: ['Verifikasi', 'b-verifikasi', 'var(--amber)'],
    tindak: ['Tindak lanjut', 'b-tindak', 'var(--teal)'],
    selesai: ['Selesai', 'b-selesai', 'var(--green)']
};

function esc(x) {
    return String(x).replace(/[&<>"]/g, function(c) {
        return {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;'
        } [c];
    });
}

function showStage(i) {
    var out = document.getElementById('trkOut');
    if (!out) return;
    var cur = SIM.log[i].s;
    var L = LBL[cur];

    document.querySelectorAll('.tabs button').forEach(function(b) {
        var on = +b.dataset.i === i;
        b.classList.toggle('active', on);
        b.setAttribute('aria-selected', on);
    });

    var steps = SIM.log.map(function(_, k) {
        return '<div class="step ' + (k <= i ? 'on s' + (k + 1) : '') + '"></div>';
    }).join('');

    var items = SIM.log.slice(0, i + 1).reverse().map(function(l) {
        return '<li style="--c:' + LBL[l.s][2] + '"><b>' + LBL[l.s][0] + '</b>' + esc(l.n) + '<br><span>' + esc(
            l.t) + '</span></li>';
    }).join('');

    out.innerHTML =
        '<div class="fadein">' +
        '<div class="tmeta">' +
        '<div><h3>' + esc(SIM.judul) + '</h3><small>' + esc(SIM.kat) + '</small></div>' +
        '<span class="badge ' + L[1] + '">' + L[0] + '</span>' +
        '</div>' +
        '<div class="stepper" aria-hidden="true">' + steps + '</div>' +
        '<ul class="tl">' + items + '</ul>' +
        '</div>';
}

document.querySelectorAll('.tabs button').forEach(function(b) {
    b.addEventListener('click', function() {
        showStage(+b.dataset.i);
    });
});
showStage(2);

/* ===== COUNTERS ===== */
(function() {
    var els = document.querySelectorAll('[data-n]');
    var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;

    function fmt(v) {
        return v.toLocaleString('id-ID');
    }

    function fin(el) {
        el.textContent = fmt(+el.dataset.n) + (el.dataset.suffix || '');
    }
    if (reduce || !('IntersectionObserver' in window)) {
        els.forEach(fin);
        return;
    }
    var io = new IntersectionObserver(function(es) {
        es.forEach(function(e) {
            if (!e.isIntersecting) return;
            io.unobserve(e.target);
            var el = e.target,
                end = +el.dataset.n,
                t0 = null;

            function tick(t) {
                if (!t0) t0 = t;
                var p = Math.min((t - t0) / 1200, 1);
                var v = Math.round(end * (1 - Math.pow(1 - p, 3)));
                el.textContent = fmt(v) + (el.dataset.suffix || '');
                if (p < 1) requestAnimationFrame(tick);
            }
            requestAnimationFrame(tick);
        });
    }, {
        threshold: .4
    });
    els.forEach(function(el) {
        io.observe(el);
    });
})();
</script>

</body>

</html>