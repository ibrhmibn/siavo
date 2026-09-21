/**
 * SIAVO - Custom JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {

    // ========================================
    // HEADER: HIDE ON SCROLL DOWN, SHOW ON SCROLL UP
    // ========================================
    const header = document.getElementById('header');

    if (header) {
        const SCROLL_THRESHOLD = 80; // jarak scroll (px) sebelum header mulai bereaksi
        let lastScrollY = window.scrollY;
        let isHeaderHidden = false;
        let ticking = false;

        function handleHeaderScroll() {
            const currentScrollY = window.scrollY;

            // Background lebih solid + shadow begitu halaman sudah discroll
            header.classList.toggle('scrolled', currentScrollY > SCROLL_THRESHOLD);

            if (currentScrollY < SCROLL_THRESHOLD) {
                // Selalu tampil saat masih dekat bagian atas halaman
                header.classList.remove('header-hidden');
                header.classList.add('header-visible');
                isHeaderHidden = false;
            } else if (currentScrollY > lastScrollY && !isHeaderHidden) {
                // Scroll ke bawah melewati threshold -> sembunyikan
                header.classList.add('header-hidden');
                header.classList.remove('header-visible');
                isHeaderHidden = true;
            } else if (currentScrollY < lastScrollY && isHeaderHidden) {
                // Scroll ke atas -> tampilkan lagi
                header.classList.remove('header-hidden');
                header.classList.add('header-visible');
                isHeaderHidden = false;
            }

            lastScrollY = currentScrollY;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(handleHeaderScroll);
                ticking = true;
            }
        });

        // Set posisi awal yang benar (misal saat reload di tengah halaman)
        handleHeaderScroll();
    }

    // ========================================
    // AUTO-HIDE ALERTS
    // ========================================
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });

    // ========================================
    // FILE UPLOAD PREVIEW
    // ========================================
    const fileInputs = document.querySelectorAll('input[type="file"][multiple]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            let container = this.parentElement.querySelector('.file-preview');
            if (!container) {
                container = document.createElement('div');
                container.className = 'file-preview mt-2';
                this.parentElement.appendChild(container);
            }
            container.innerHTML = '';
            const files = this.files;
            for (let i = 0; i < files.length; i++) {
                const div = document.createElement('div');
                div.className = 'file-preview-item';
                div.innerHTML = `
                    <span class="file-name">${files[i].name}</span>
                    <span class="file-size text-muted ms-2">(${(files[i].size / 1024).toFixed(1)} KB)</span>
                `;
                container.appendChild(div);
            }
        });
    });

    // ========================================
    // CONFIRM ACTION
    // ========================================
    window.confirmAction = function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    };

    // ========================================
    // TOAST NOTIFICATION
    // ========================================
    window.showToast = function(message, type = 'success') {
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            toastContainer.className = 'position-fixed bottom-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.role = 'alert';
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        toastContainer.appendChild(toastEl);
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        toastEl.addEventListener('hidden.bs.toast', function() {
            toastEl.remove();
        });
    };

    // ========================================
    // EXPORT TO CSV
    // ========================================
    window.exportToCSV = function(tableId, filename = 'data.csv') {
        const table = document.getElementById(tableId);
        if (!table) return;
        let csv = [];
        const rows = table.querySelectorAll('tr');
        rows.forEach(function(row) {
            const cols = row.querySelectorAll('td, th');
            const rowData = [];
            cols.forEach(function(col) {
                rowData.push('"' + col.textContent.trim() + '"');
            });
            csv.push(rowData.join(','));
        });
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        window.URL.revokeObjectURL(url);
    };

});