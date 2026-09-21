/* ============================================
   SIAVO — Reusable File Upload Handler
   ============================================ */

(function() {
    'use strict';

    window.updateFileUpload = function(input) {
        var wrapper = input.closest('.file-upload');
        if (!wrapper) return;

        var title = wrapper.querySelector('.file-upload-title');
        var hint  = wrapper.querySelector('.file-upload-hint');
        if (!title) return;

        var files = input.files;
        var defaultTitle = title.dataset.default || 'Belum ada file dipilih';
        var defaultHint  = hint ? (hint.dataset.default || hint.textContent) : '';

        /* Kosong */
        if (!files || files.length === 0) {
            title.textContent = defaultTitle;
            if (hint) hint.textContent = defaultHint;
            wrapper.classList.remove('has-file');
            return;
        }

        /* 1 file */
        if (files.length === 1) {
            var f = files[0];
            var sizeText = f.size > 1024 * 1024
                ? (f.size / 1024 / 1024).toFixed(1) + ' MB'
                : (f.size / 1024).toFixed(1) + ' KB';
            title.textContent = f.name;
            if (hint) hint.textContent = sizeText;
        }
        /* Multiple */
        else {
            var total = 0;
            for (var i = 0; i < files.length; i++) total += files[i].size;
            var totalText = total > 1024 * 1024
                ? (total / 1024 / 1024).toFixed(1) + ' MB'
                : (total / 1024).toFixed(0) + ' KB';
            title.textContent = files.length + ' file dipilih';
            if (hint) hint.textContent = 'Total ' + totalText;
        }

        wrapper.classList.add('has-file');
    };

    function bindAuto() {
        document.querySelectorAll('.file-upload input[type="file"]').forEach(function(input) {
            if (input.dataset.fileBound) return;
            input.dataset.fileBound = '1';
            input.addEventListener('change', function() {
                window.updateFileUpload(this);
            });
            window.updateFileUpload(input);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindAuto);
    } else {
        bindAuto();
    }

    window.rebindFileUpload = bindAuto;
})();