<?php
$page_title = 'Dashboard';
$page_active = 'dashboard';

require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || getUserRole() != 'admin') {
    redirect('../login.php');
}

// AUTO-CLEANUP kegiatan lewat > 2 hari (max 1x per 6 jam)
$auto_deleted_kegiatan = autoCleanupKegiatan();

// Statistik
$stats = ['total'=>0,'pengajuan'=>0,'verifikasi'=>0,'tindak_lanjut'=>0,'selesai'=>0];
$q = [
    'total'          => "SELECT COUNT(*) c FROM laporan",
    'pengajuan'      => "SELECT COUNT(*) c FROM laporan WHERE status='pengajuan'",
    'verifikasi'     => "SELECT COUNT(*) c FROM laporan WHERE status='verifikasi'",
    'tindak_lanjut'  => "SELECT COUNT(*) c FROM laporan WHERE status='tindak_lanjut'",
    'selesai'        => "SELECT COUNT(*) c FROM laporan WHERE status='selesai'"
];
foreach ($q as $k => $sql) { $r = $conn->query($sql); if ($r) $stats[$k] = (int)$r->fetch_assoc()['c']; }

// Recent reports
$reports = [];
$stmt = $conn->prepare("SELECT l.*, k.nama_kategori FROM laporan l LEFT JOIN kategori k ON l.kategori_id=k.id ORDER BY l.created_at DESC LIMIT 20");
$stmt->execute();
$r = $stmt->get_result();
while ($row = $r->fetch_assoc()) $reports[] = $row;
$stmt->close();

include 'includes/sidebar.php';
?>

<div class="page-head">
    <div>
        <h1>Dashboard Verifikasi</h1>
        <p>Pantau dan kelola semua laporan aspirasi mahasiswa</p>
    </div>
    <div class="page-meta">
        <i class="fas fa-clock"></i>
        <span>Update: <?php echo date('d/m/Y H:i'); ?> WIB</span>
    </div>
</div>

<?php if ($auto_deleted_kegiatan > 0): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-broom me-1"></i>
    <strong><?php echo $auto_deleted_kegiatan; ?></strong> kegiatan lama otomatis dihapus (lewat > 2 hari).
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card-modern border-blue">
        <div>
            <div class="stat-label">Pengajuan Baru</div>
            <div class="stat-number"><?php echo $stats['pengajuan']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-message"></i></div>
    </div>
    <div class="stat-card-modern border-yellow">
        <div>
            <div class="stat-label">Dalam Verifikasi</div>
            <div class="stat-number"><?php echo $stats['verifikasi']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-triangle-exclamation"></i></div>
    </div>
    <div class="stat-card-modern border-cyan">
        <div>
            <div class="stat-label">Tindak Lanjut</div>
            <div class="stat-number"><?php echo $stats['tindak_lanjut']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
    </div>
    <div class="stat-card-modern border-green">
        <div>
            <div class="stat-label">Selesai</div>
            <div class="stat-number"><?php echo $stats['selesai']; ?></div>
        </div>
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
    </div>
</div>

<div class="table-modern-wrapper">
    <div class="table-header">
        <div class="title"><i class="fas fa-list"></i>Daftar Laporan</div>
        <div class="filter-tabs">
            <button class="tab-btn active" data-filter="all">Semua</button>
            <button class="tab-btn" data-filter="pengajuan">Pengajuan</button>
            <button class="tab-btn" data-filter="verifikasi">Verifikasi</button>
            <button class="tab-btn" data-filter="tindak_lanjut">Tindak Lanjut</button>
            <button class="tab-btn" data-filter="selesai">Selesai</button>
        </div>
    </div>
    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th style="width:50px">No</th>
                    <th>Nomor Tiket</th>
                    <th>Pelapor</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th>Prioritas</th>
                    <th>Tanggal</th>
                    <th style="width:90px" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="reportTableBody">
                <?php if (empty($reports)): ?>
                <tr>
                    <td colspan="8" class="text-center" style="padding:60px 24px;color:var(--ink-2)">
                        <i class="fas fa-inbox"
                            style="font-size:2rem;display:block;margin-bottom:12px;color:var(--line)"></i>
                        Belum ada data laporan
                    </td>
                </tr>
                <?php else: $no=1; foreach ($reports as $r): ?>
                <tr data-status="<?php echo $r['status']; ?>">
                    <td style="color:var(--ink-2)"><?php echo $no++; ?></td>
                    <td><span
                            style="font-weight:700;color:var(--ink)"><?php echo htmlspecialchars($r['nomor_tiket']); ?></span>
                    </td>
                    <td>
                        <div class="cell-name"><?php echo htmlspecialchars($r['nama_pelapor'] ?? 'Anonim'); ?></div>
                        <div class="cell-nim"><?php echo htmlspecialchars($r['nim']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($r['nama_kategori'] ?? '-'); ?></td>
                    <td><?php echo getStatusBadgeModern($r['status']); ?></td>
                    <td><?php echo getPriorityBadgeModern($r['prioritas']); ?></td>
                    <td style="color:var(--ink-2);font-size:.8rem">
                        <?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                    <td class="text-center">
                        <button class="btn-icon-modern btn-detail" data-id="<?php echo $r['id']; ?>" title="Detail">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <span>Menampilkan <strong style="color:var(--ink)" id="rowCount"><?php echo count($reports); ?></strong>
            data</span>
        <div class="pagination">
            <button disabled><i class="fas fa-chevron-left"></i></button>
            <button class="active">1</button>
            <button><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<!-- Detail Panel -->
<div class="detail-overlay" id="detailOverlay"></div>
<div class="detail-panel" id="detailPanel">
    <div class="panel-header">
        <div>
            <div class="ticket-label">Tiket</div>
            <div class="ticket-number" id="panelTicket">-</div>
        </div>
        <button class="btn-close-panel" id="closePanel"><i class="fas fa-xmark"></i></button>
    </div>
    <div class="panel-body" id="panelBody">
        <div style="text-align:center;padding:60px 20px;color:var(--ink-2)">
            <i class="fas fa-spinner fa-spin" style="font-size:1.5rem"></i>
            <p style="margin-top:10px;font-size:.9rem">Memuat data...</p>
        </div>
    </div>
    <div class="panel-footer">
        <div class="footer-label">Update Status</div>
        <form id="updateStatusForm" enctype="multipart/form-data">
            <input type="hidden" id="laporanId" name="laporan_id">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-group">
                <select name="status" id="statusSelect">
                    <option value="pengajuan">Pengajuan</option>
                    <option value="verifikasi">Verifikasi</option>
                    <option value="tindak_lanjut">Tindak Lanjut</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            <div class="form-group">
                <textarea name="keterangan" id="keteranganInput" placeholder="Keterangan update status..."
                    rows="2"></textarea>
            </div>

            <div class="form-group file-upload-group" id="feedbackFileGroup" style="display:none">
                <input type="file" name="feedback_file" id="feedbackFileInput" accept="application/pdf,.pdf" hidden>

                <label for="feedbackFileInput" class="btn-upload-pdf">
                    <i class="fas fa-upload"></i>
                    <span>Upload Bukti Formal (PDF)</span>
                </label>

                <div class="file-preview" id="feedbackFilePreview"></div>
            </div>

            <button type="submit" class="btn-update">
                <i class="fas fa-save" style="margin-right:6px"></i> Simpan Perubahan
            </button>
        </form>
    </div>
</div>

<input type="hidden" id="baseUrl" value="<?php echo APP_URL; ?>">

<?php include 'includes/sidebar-footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {

    /* FILTER TABS */
    var tabs = document.querySelectorAll('.filter-tabs .tab-btn');
    var rows = document.querySelectorAll('#reportTableBody tr[data-status]');
    var rowCount = document.getElementById('rowCount');

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) {
                t.classList.remove('active');
            });
            this.classList.add('active');
            var f = this.dataset.filter,
                n = 0;
            rows.forEach(function(row) {
                if (f === 'all' || row.dataset.status === f) {
                    row.style.display = '';
                    n++;
                } else {
                    row.style.display = 'none';
                }
            });
            if (rowCount) rowCount.textContent = n;
        });
    });

    /* REFS */
    var overlay = document.getElementById('detailOverlay');
    var panel = document.getElementById('detailPanel');
    var panelBody = document.getElementById('panelBody');
    var panelTicket = document.getElementById('panelTicket');
    var laporanIdInput = document.getElementById('laporanId');
    var statusSelect = document.getElementById('statusSelect');
    var keteranganInput = document.getElementById('keteranganInput');
    var baseUrl = document.getElementById('baseUrl').value;

    var feedbackFileGroup = document.getElementById('feedbackFileGroup');
    var feedbackFileInput = document.getElementById('feedbackFileInput');
    var feedbackFilePreview = document.getElementById('feedbackFilePreview');

    /* HELPERS */
    function nl2br(s) {
        return (s || '').replace(/\n/g, '<br>');
    }

    function esc(s) {
        return String(s || '').replace(/[&<>"]/g, function(c) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;'
            } [c];
        });
    }

    /* TOGGLE UPLOAD FILE */
    function toggleFeedbackFile() {
        var isSelesai = statusSelect.value === 'selesai';
        feedbackFileGroup.style.display = isSelesai ? 'block' : 'none';
        if (!isSelesai) {
            feedbackFileInput.value = '';
            feedbackFilePreview.innerHTML = '';
            feedbackFileGroup.querySelector('.btn-upload-pdf').classList.remove('has-file');
            feedbackFileGroup.querySelector('.btn-upload-pdf span').textContent =
                'Upload Bukti Formal (PDF)';
        }
    }
    statusSelect.addEventListener('change', toggleFeedbackFile);

    /* PREVIEW FILE */
    feedbackFileInput.addEventListener('change', function() {
        feedbackFilePreview.innerHTML = '';
        var btn = feedbackFileGroup.querySelector('.btn-upload-pdf');
        var btnLabel = btn.querySelector('span');
        var f = this.files[0];

        if (!f) {
            btnLabel.textContent = 'Upload Bukti Formal (PDF)';
            btn.classList.remove('has-file');
            return;
        }

        var isPdf = f.type === 'application/pdf' || f.name.toLowerCase().endsWith('.pdf');
        if (!isPdf) {
            feedbackFilePreview.innerHTML =
                '<span class="file-error"><i class="fas fa-circle-exclamation"></i> Hanya file PDF yang diperbolehkan</span>';
            this.value = '';
            btnLabel.textContent = 'Upload Bukti Formal (PDF)';
            btn.classList.remove('has-file');
            return;
        }

        if (f.size > 2 * 1024 * 1024) {
            feedbackFilePreview.innerHTML =
                '<span class="file-error"><i class="fas fa-circle-exclamation"></i> Ukuran maksimal 2 MB</span>';
            this.value = '';
            btnLabel.textContent = 'Upload Bukti Formal (PDF)';
            btn.classList.remove('has-file');
            return;
        }

        var kb = (f.size / 1024).toFixed(1);
        btnLabel.textContent = 'Ganti Bukti Formal (PDF)';
        btn.classList.add('has-file');

        feedbackFilePreview.innerHTML =
            '<span class="file-ok"><i class="fas fa-file-pdf"></i> ' +
            esc(f.name) + ' <em>(' + kb + ' KB)</em></span>';
    });

    /* OPEN PANEL */
    function openPanel(id) {
        overlay.classList.add('active');
        panel.classList.add('active');
        document.body.style.overflow = 'hidden';
        panelBody.innerHTML =
            '<div style="text-align:center;padding:60px 20px;color:var(--ink-2)"><i class="fas fa-spinner fa-spin" style="font-size:1.5rem"></i><p style="margin-top:10px;font-size:.9rem">Memuat...</p></div>';

        fetch(baseUrl + 'admin/ajax/detail-laporan.php?id=' + id)
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                if (j.success) renderPanel(j.data);
                else panelBody.innerHTML =
                    '<div style="text-align:center;padding:60px 20px;color:var(--red)"><i class="fas fa-circle-exclamation" style="font-size:1.5rem"></i><p style="margin-top:10px">' +
                    esc(j.message || 'Gagal') + '</p></div>';
            })
            .catch(function() {
                panelBody.innerHTML =
                    '<div style="text-align:center;padding:60px 20px;color:var(--red)"><i class="fas fa-circle-exclamation" style="font-size:1.5rem"></i><p style="margin-top:10px">Kesalahan jaringan</p></div>';
            });
    }

    /* RENDER PANEL */
    function renderPanel(data) {
        panelTicket.textContent = data.nomor_tiket;
        laporanIdInput.value = data.id;
        statusSelect.value = data.status;
        keteranganInput.value = '';

        feedbackFileInput.value = '';
        feedbackFilePreview.innerHTML = '';
        toggleFeedbackFile();

        var statusLabels = {
            pengajuan: 'Pengajuan',
            verifikasi: 'Verifikasi',
            tindak_lanjut: 'Tindak Lanjut',
            selesai: 'Selesai'
        };
        var priorityLabels = {
            rendah: 'Rendah',
            sedang: 'Sedang',
            tinggi: 'Tinggi',
            urgent: 'Urgent'
        };

        var timeline = '';
        if (data.riwayat && data.riwayat.length > 0) {
            data.riwayat.forEach(function(r) {
                var dotColors = {
                    pengajuan: 'blue',
                    verifikasi: 'yellow',
                    tindak_lanjut: 'cyan',
                    selesai: 'green'
                };
                var c = dotColors[r.status] || 'blue';
                timeline += '<div class="tl-item"><div class="tl-dot ' + c +
                    '"></div><div class="tl-title">' + (statusLabels[r.status] || r.status) +
                    '</div><div class="tl-date">' + esc(r.created_at) + '</div>' +
                    (r.keterangan ? '<div class="tl-desc">' + esc(r.keterangan) + '</div>' : '') +
                    '</div>';
            });
        } else {
            timeline = '<p style="color:var(--ink-2);font-size:.85rem">Belum ada riwayat</p>';
        }

        var lampiranHtml = '';
        if (data.dokumen && data.dokumen.length > 0) {
            lampiranHtml = '<div class="section-label">Lampiran</div><div class="attachments">' +
                data.dokumen.map(function(d) {
                    return '<div class="attachment-item" onclick="window.open(\'' + baseUrl + d.path_file +
                        '\',\'_blank\')"><i class="fas fa-file-pdf" style="color:var(--red)"></i> ' +
                        esc(d.nama_file_asli) + '</div>';
                }).join('') + '</div>';
        }

        var feedbackHtml = '';
        if (data.file_feedback) {
            feedbackHtml =
                '<div class="section-label">Bukti Formal</div>' +
                '<div class="attachment-item" onclick="window.open(\'' + esc(data.file_feedback) +
                '\',\'_blank\')">' +
                '<i class="fas fa-file-pdf" style="color:var(--red)"></i> Lihat dokumen PDF' +
                '</div>';
        }

        panelBody.innerHTML =
            '<div class="badge-group">' +
            '<span class="badge-status-modern ' + data.status + '">' +
            (statusLabels[data.status] || data.status) + '</span>' +
            '<span class="badge-priority ' + (data.prioritas || 'sedang') + '">' +
            (priorityLabels[data.prioritas] || data.prioritas || '-') + '</span>' +
            '</div>' +
            '<h3 class="detail-title">' + esc(data.judul || '(Tanpa judul)') + '</h3>' +
            '<div class="info-grid">' +
            '<div><span class="label">Kategori</span><span class="value">' +
            esc(data.nama_kategori || '-') + '</span></div>' +
            '<div><span class="label">Tanggal</span><span class="value">' +
            esc(data.created_at) + '</span></div>' +
            '<div><span class="label">Pelapor</span><span class="value">' +
            esc(data.nama_pelapor || 'Anonim') + '</span></div>' +
            '<div><span class="label">NIM</span><span class="value">' +
            esc(data.nim || '-') + '</span></div>' +
            '</div>' +
            '<div class="section-label">Deskripsi</div>' +
            '<div class="content-text">' + nl2br(esc(data.isi)) + '</div>' +
            lampiranHtml + feedbackHtml +
            '<div class="section-label">Riwayat Status</div>' +
            '<div class="timeline">' + timeline + '</div>';
    }

    /* CLOSE PANEL */
    function closePanel() {
        overlay.classList.remove('active');
        panel.classList.remove('active');
        document.body.style.overflow = '';
    }

    document.querySelectorAll('.btn-detail').forEach(function(b) {
        b.addEventListener('click', function() {
            openPanel(this.dataset.id);
        });
    });
    overlay.addEventListener('click', closePanel);
    document.getElementById('closePanel').addEventListener('click', closePanel);

    /* UPDATE STATUS */
    document.getElementById('updateStatusForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var status = statusSelect.value;
        var isSelesai = status === 'selesai';

        if (!isSelesai && feedbackFileInput.files.length > 0) {
            alert('Upload bukti formal hanya saat status = Selesai.');
            return;
        }

        var fd = new FormData(this);
        fd.append('action', 'update_status');

        var btn = this.querySelector('.btn-update');
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
        btn.disabled = true;

        fetch(baseUrl + 'admin/ajax/update-status.php', {
                method: 'POST',
                body: fd
            })
            .then(function(r) {
                return r.json();
            })
            .then(function(j) {
                if (j.success) {
                    var id = fd.get('laporan_id');
                    var newStatus = fd.get('status');
                    var rowBtn = document.querySelector(
                        '#reportTableBody button.btn-detail[data-id="' + id + '"]');
                    if (rowBtn) {
                        var tr = rowBtn.closest('tr');
                        var cell = tr.querySelector('td:nth-child(5)');
                        var labels = {
                            pengajuan: 'Pengajuan',
                            verifikasi: 'Verifikasi',
                            tindak_lanjut: 'Tindak Lanjut',
                            selesai: 'Selesai'
                        };
                        cell.innerHTML = '<span class="badge-status-modern ' + newStatus + '">' +
                            (labels[newStatus] || newStatus) + '</span>';
                        tr.dataset.status = newStatus;
                    }
                    if (j.warning) alert('Status diperbarui, tetapi: ' + j.warning);
                    openPanel(id);
                } else {
                    alert(j.message || 'Gagal memperbarui status');
                }
            })
            .catch(function() {
                alert('Kesalahan jaringan');
            })
            .finally(function() {
                btn.innerHTML = orig;
                btn.disabled = false;
            });
    });

});
</script>