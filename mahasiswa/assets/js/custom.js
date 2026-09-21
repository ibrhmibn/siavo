/**
 * SIAVO - Mahasiswa Dashboard Custom JS
 */
document.addEventListener('DOMContentLoaded', function() {

    /* ========================================
       BOOTSTRAP TOOLTIPS
       ======================================== */
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
            new bootstrap.Tooltip(el);
        });
    }

    /* ========================================
       STAT CARDS — entrance + count-up
       ======================================== */
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const statCards = document.querySelectorAll('.stat-card');

    statCards.forEach(function(card, i) {
        const numEl = card.querySelector('.stat-number');
        if (!numEl) return;

        const target = parseInt(numEl.textContent.trim(), 10);
        if (isNaN(target)) return;

        if (reduceMotion) {
            numEl.textContent = target;
            return;
        }

        // Fade-in
        card.style.opacity = '0';
        card.style.transform = 'translateY(8px)';
        setTimeout(function() {
            card.style.transition = 'opacity .4s ease, transform .4s ease';
            card.style.opacity = '1';
            card.style.transform = 'none';
        }, 70 * i);

        // Count-up
        const duration = 500;
        const start = performance.now();
        numEl.textContent = '0';
        requestAnimationFrame(function step(now) {
            const progress = Math.min((now - start) / duration, 1);
            numEl.textContent = Math.round(progress * target);
            if (progress < 1) requestAnimationFrame(step);
        });
    });

    /* ========================================
       FILE UPLOAD PREVIEW
       ======================================== */
    document.querySelectorAll('input[type="file"][multiple]').forEach(function(input) {
        input.addEventListener('change', function() {
            let container = this.parentElement.querySelector('.file-preview');
            if (!container) {
                container = document.createElement('div');
                container.className = 'file-preview';
                this.parentElement.appendChild(container);
            }

            const files = this.files;
            if (files.length === 0) {
                container.remove();
                return;
            }

            let html = '<h6><i class="fas fa-paperclip"></i> File terpilih:</h6>';
            for (let i = 0; i < files.length; i++) {
                const sizeKB = (files[i].size / 1024).toFixed(1);
                const name = files[i].name.replace(/[&<>"]/g, function(c) {
                    return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c];
                });
                html += '<div class="file-preview-item">' +
                        '<span class="file-name"><i class="fas fa-file-alt"></i>' + name + '</span>' +
                        '<span class="file-size">' + sizeKB + ' KB</span>' +
                        '</div>';
            }
            container.innerHTML = html;
        });
    });

    /* ========================================
       AVOID DOUBLE SUBMIT
       ======================================== */
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function() {
            const btn = form.querySelector('button[type="submit"]');
            if (btn && !btn.disabled) {
                setTimeout(function() {
                    btn.disabled = true;
                    const orig = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mengirim...';
                }, 10);
            }
        });
    });

});