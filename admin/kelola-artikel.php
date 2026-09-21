<?php
$page_title = 'Kelola Artikel';
$page_active = 'artikel';

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth_check.php';

if (getUserRole() != 'admin') {
    redirect('../index.php');
}

$error = '';
$success = '';
$edit_data = null;

// ============================================
// HANDLE POST: SAVE + DELETE
// ============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $action = $_POST['action'] ?? 'save';

        if ($action === 'delete') {
            $id = intval($_POST['delete_id'] ?? 0);
            if ($id > 0) {
                $stmt = $conn->prepare("DELETE FROM artikel WHERE id = ?");
                $stmt->bind_param("i", $id);
                if ($stmt->execute() && $stmt->affected_rows > 0) {
                    $success = 'Artikel berhasil dihapus.';
                } else {
                    $error = 'Gagal menghapus artikel.';
                }
                $stmt->close();
            }
        } else {
            $judul         = sanitize($_POST['judul'] ?? '');
            $kategori_info = sanitize($_POST['kategori_info'] ?? '');
            $konten        = trim($_POST['konten'] ?? '');

            if ($judul === '' || $konten === '') {
                $error = 'Judul dan isi artikel wajib diisi.';
            } elseif (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
                $id = intval($_POST['edit_id']);
                $stmt = $conn->prepare("UPDATE artikel SET judul = ?, konten = ?, kategori_info = ? WHERE id = ?");
                $stmt->bind_param("sssi", $judul, $konten, $kategori_info, $id);
                if ($stmt->execute()) {
                    $success = 'Artikel berhasil diperbarui.';
                } else {
                    $error = 'Gagal memperbarui artikel.';
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("INSERT INTO artikel (judul, konten, kategori_info) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $judul, $konten, $kategori_info);
                if ($stmt->execute()) {
                    $success = 'Artikel berhasil ditambahkan.';
                } else {
                    $error = 'Gagal menambahkan artikel.';
                }
                $stmt->close();
            }
        }
    }
}

// ============================================
// LOAD EDIT DATA
// ============================================
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM artikel WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_data = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Ambil semua artikel
$artikel = [];
$sql = "SELECT * FROM artikel ORDER BY kategori_info, created_at DESC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $artikel[] = $row;
    }
}

include '../admin/includes/sidebar.php';
?>

<div class="container-fluid">

    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4">
        <div>
            <h1 class="h3 fw-bold text-dark" style="color: var(--siavo-red-heading) !important;">Kelola Artikel</h1>
            <p class="text-muted small mb-0">Tulis langsung seperti di Word — hasil otomatis rapi di Pusat Informasi</p>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-circle-exclamation me-1"></i> <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-1"></i> <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ============================================
         FORM EDITOR
         ============================================ -->
    <div class="card-modern" style="margin-bottom:22px">
        <div class="card-modern-header">
            <i class="fas fa-pen-fancy"></i>
            <h5><?php echo $edit_data ? 'Edit Artikel' : 'Tulis Artikel Baru'; ?></h5>
        </div>
        <div class="card-modern-body">
            <form method="POST" class="form-modern" id="artikelForm">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="edit_id" value="<?php echo $edit_data ? $edit_data['id'] : ''; ?>">

                <!-- Row 1: Kategori + Judul -->
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label>Kategori <span class="text-danger">*</span></label>
                        <select name="kategori_info" class="form-select" required>
                            <option value="sop"
                                <?php echo ($edit_data['kategori_info'] ?? '') === 'sop' ? 'selected' : ''; ?>>SOP
                                Advokasi</option>
                            <option value="panduan"
                                <?php echo ($edit_data['kategori_info'] ?? '') === 'panduan' ? 'selected' : ''; ?>>
                                Panduan Pengajuan</option>
                            <option value="beasiswa"
                                <?php echo ($edit_data['kategori_info'] ?? '') === 'beasiswa' ? 'selected' : ''; ?>>Info
                                Beasiswa</option>
                            <option value="faq"
                                <?php echo ($edit_data['kategori_info'] ?? '') === 'faq' ? 'selected' : ''; ?>>FAQ
                            </option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <label>Judul Artikel <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control"
                            value="<?php echo htmlspecialchars($edit_data['judul'] ?? ''); ?>"
                            placeholder="Contoh: Alur Pengajuan Advokasi Mahasiswa" required>
                    </div>
                </div>

                <!-- Row 2: Editor -->
                <label>Isi Artikel <span class="text-danger">*</span></label>
                <p class="text-muted small mb-2">
                    <i class="fas fa-info-circle"></i>
                    Klik tombol di toolbar untuk menambah bagian. Hasil ketikan langsung terlihat seperti di halaman
                    Pusat Informasi.
                </p>

                <div class="editor-toolbar">
                    <button type="button" data-block="p" title="Paragraf biasa"><i class="fas fa-paragraph"></i>
                        Paragraf</button>
                    <button type="button" data-block="h4" title="Sub-judul bagian"><i class="fas fa-heading"></i>
                        Heading</button>
                    <button type="button" data-block="ol" title="List bernomor"><i class="fas fa-list-ol"></i> List
                        1-2-3</button>
                    <button type="button" data-block="ul" title="List bullet"><i class="fas fa-list-ul"></i> List
                        Bullet</button>
                    <button type="button" data-block="note" title="Kotak catatan merah"><i
                            class="fas fa-circle-exclamation"></i> Catatan</button>
                    <span class="editor-toolbar-sep"></span>
                    <button type="button" data-cmd="bold" title="Bold"><i class="fas fa-bold"></i></button>
                    <button type="button" data-cmd="italic" title="Italic"><i class="fas fa-italic"></i></button>
                    <button type="button" data-cmd="removeFormat" title="Hapus format"><i
                            class="fas fa-eraser"></i></button>
                </div>

                <div id="editorArea" class="editor-area prose" contenteditable="true"
                    data-placeholder="Mulai tulis artikel di sini..."><?php echo $edit_data['konten'] ?? ''; ?></div>

                <textarea name="konten" id="kontenHidden" hidden></textarea>

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-modern-primary">
                        <i class="fas fa-save"></i> <?php echo $edit_data ? 'Update Artikel' : 'Simpan Artikel'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                    <a href="kelola-artikel.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Batal Edit
                    </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================
         LIST ARTIKEL
         ============================================ -->
    <div class="table-modern-wrapper">
        <div class="table-header">
            <div class="title"><i class="fas fa-list"></i>Daftar Artikel</div>
        </div>
        <?php if (empty($artikel)): ?>
        <div class="empty-state-modern">
            <i class="fas fa-file-lines"></i>
            <p>Belum ada artikel</p>
        </div>
        <?php else: ?>
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Kategori</th>
                        <th>Judul</th>
                        <th>Tanggal</th>
                        <th class="text-center" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($artikel as $a): ?>
                    <tr>
                        <td><span class="badge-category-modern"><?php echo strtoupper($a['kategori_info']); ?></span>
                        </td>
                        <td><span class="cell-title"><?php echo htmlspecialchars($a['judul']); ?></span></td>
                        <td class="text-muted small"><?php echo date('d/m/Y', strtotime($a['created_at'])); ?></td>
                        <td class="text-center">
                            <a href="?edit=<?php echo $a['id']; ?>" class="btn-icon-modern edit" title="Edit">
                                <i class="fas fa-pencil"></i>
                            </a>
                            <form method="POST" style="display:inline"
                                onsubmit="return confirm('Yakin ingin menghapus artikel ini?')">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="delete_id" value="<?php echo $a['id']; ?>">
                                <button type="submit" class="btn-icon-modern delete" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="table-footer">
            <span>Menampilkan <span class="fw-bold text-dark"><?php echo count($artikel); ?></span> data</span>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var editor = document.getElementById('editorArea');
    var hidden = document.getElementById('kontenHidden');
    var form = document.getElementById('artikelForm');

    /* ============================================
       TOOLBAR
       ============================================ */
    document.querySelectorAll('.editor-toolbar button').forEach(function(btn) {
        btn.addEventListener('mousedown', function(e) {
            e.preventDefault(); // biar cursor gak hilang dari editor
        });

        btn.addEventListener('click', function() {
            var block = this.dataset.block;
            var cmd = this.dataset.cmd;

            editor.focus();

            if (cmd) {
                document.execCommand(cmd, false, null);
            } else if (block === 'p') {
                document.execCommand('formatBlock', false, 'p');
            } else if (block === 'h4') {
                document.execCommand('formatBlock', false, 'h4');
            } else if (block === 'ol') {
                document.execCommand('insertOrderedList');
            } else if (block === 'ul') {
                document.execCommand('insertUnorderedList');
            } else if (block === 'note') {
                insertNote();
            }

            sync();
        });
    });

    /* ============================================
       INSERT NOTE (kotak merah)
       ============================================ */
    function insertNote() {
        var note = document.createElement('div');
        note.className = 'note';
        // Bold "Catatan:" biar match sama contoh di informasi.php
        note.innerHTML = '<strong>Catatan:</strong> Tulis catatan penting di sini...';

        var sel = window.getSelection();
        if (sel.rangeCount && editor.contains(sel.anchorNode)) {
            var range = sel.getRangeAt(0);
            range.deleteContents();
            range.insertNode(note);
            range.setStartAfter(note);
            range.collapse(true);
            sel.removeAllRanges();
            sel.addRange(range);
        } else {
            editor.appendChild(note);
        }

        // Kasih paragraf kosong di bawah biar user bisa lanjut nulis
        var after = document.createElement('p');
        after.innerHTML = '<br>';
        note.parentNode.insertBefore(after, note.nextSibling);
        var r = document.createRange();
        r.setStart(after, 0);
        r.collapse(true);
        sel.removeAllRanges();
        sel.addRange(r);
    }

    /* ============================================
       PASTE AS PLAIN TEXT
       ============================================ */
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        var text = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
    });

    /* ============================================
       SYNC KE HIDDEN TEXTAREA
       ============================================ */
    function sync() {
        hidden.value = editor.innerHTML;
    }

    editor.addEventListener('input', sync);
    editor.addEventListener('keyup', sync);
    editor.addEventListener('blur', sync);

    form.addEventListener('submit', function() {
        sync();
        if (!editor.textContent.trim()) {
            alert('Isi artikel tidak boleh kosong.');
            editor.focus();
            return false;
        }
    });

    /* ============================================
       ENTER KEY → paragraf baru (bukan div)
       ============================================ */
    editor.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            // Biar browser bikin <p> bukan <div>
            var block = document.queryCommandValue('formatBlock');
            if (!block || block === 'div') {
                document.execCommand('formatBlock', false, 'p');
            }
        }
    });

    /* ============================================
       INIT — kalau kosong, kasih 1 paragraf
       ============================================ */
    if (!editor.innerHTML.trim()) {
        editor.innerHTML = '<p><br></p>';
        var range = document.createRange();
        range.setStart(editor.firstChild, 0);
        range.collapse(true);
        var sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(range);
    }

    sync();
})();
</script>

<?php include '../admin/includes/sidebar-footer.php'; ?>