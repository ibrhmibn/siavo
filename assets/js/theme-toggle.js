/* ============================================
   SIAVO — Theme Toggle with Circle Wipe
   + Auto-update Icon (moon <-> sun)
   ============================================ */

(function () {
    'use strict';

    var STORAGE_KEY = 'siavo-theme';
    var DARK_BG = '#14080a';
    var LIGHT_BG = '#fdf7f7';

    /* ============================================
       1. Terapkan tema ke <html> + simpan
       ============================================ */
    function applyTheme(isDark) {
        var root = document.documentElement;
        if (isDark) {
            root.setAttribute('data-theme', 'dark');
        } else {
            root.setAttribute('data-theme', 'light');
        }
        try {
            localStorage.setItem(STORAGE_KEY, isDark ? 'dark' : 'light');
        } catch (e) {}
        updateIcons();
    }

    /* ============================================
       2. Baca tema saat ini
       ============================================ */
    function isDarkActive() {
        var root = document.documentElement;
        var explicit = root.getAttribute('data-theme');
        if (explicit === 'dark') return true;
        if (explicit === 'light') return false;
        return window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    /* ============================================
       3. Update SEMUA icon toggle di halaman
       - Dark aktif  → icon BULAN (fa-moon)
       - Light aktif → icon MATAHARI (fa-sun)
       ============================================ */
    function updateIcons() {
        var isDark = isDarkActive();

        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            // Hanya update kalo ada elemen <i> (FontAwesome)
            var icon = btn.querySelector('i');
            if (!icon) return;

            if (isDark) {
                icon.className = 'fas fa-moon';
                btn.setAttribute('aria-label', 'Mode gelap aktif');
                btn.setAttribute('title', 'Mode gelap aktif');
            } else {
                icon.className = 'fas fa-sun';
                btn.setAttribute('aria-label', 'Mode terang aktif');
                btn.setAttribute('title', 'Mode terang aktif');
            }
        });
    }

    /* ============================================
       4. Hitung posisi tombol + radius
       ============================================ */
    function getOrigin(event) {
        var btn = event && event.currentTarget ? event.currentTarget : null;
        if (!btn) {
            return { x: window.innerWidth / 2, y: window.innerHeight / 2 };
        }
        var rect = btn.getBoundingClientRect();
        return {
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2
        };
    }

    function getMaxRadius(x, y) {
        return Math.hypot(
            Math.max(x, window.innerWidth - x),
            Math.max(y, window.innerHeight - y)
        );
    }

    /* ============================================
       5. Fallback: overlay fullscreen
       ============================================ */
    function fallbackAnimate(x, y, maxRadius, nextDark) {
        var overlay = document.createElement('div');
        overlay.className = 'siavo-theme-overlay';
        overlay.style.background = nextDark ? DARK_BG : LIGHT_BG;
        overlay.style.clipPath = 'circle(0px at ' + x + 'px ' + y + 'px)';
        overlay.style.transition = 'clip-path 500ms cubic-bezier(0.4, 0, 0.2, 1)';
        document.body.appendChild(overlay);

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                overlay.style.clipPath = 'circle(' + maxRadius + 'px at ' + x + 'px ' + y + 'px)';
            });
        });

        setTimeout(function () {
            applyTheme(nextDark);
            setTimeout(function () {
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
            }, 60);
        }, 500);
    }

    /* ============================================
       6. Fungsi utama toggle
       ============================================ */
    function toggleTheme(event) {
        var next = !isDarkActive();
        var origin = getOrigin(event);
        var maxRadius = getMaxRadius(origin.x, origin.y);

        // --- View Transitions API ---
        if (typeof document.startViewTransition === 'function') {
            var root = document.documentElement;

            root.style.setProperty('--vt-x', origin.x + 'px');
            root.style.setProperty('--vt-y', origin.y + 'px');
            root.style.setProperty('--vt-r', maxRadius + 'px');

            root.classList.add('vt-disable-anim');
            root.classList.add('vt-wipe-active');

            var transition = document.startViewTransition(function () {
                applyTheme(next);
            });

            transition.finished.finally(function () {
                root.classList.remove('vt-disable-anim');
                root.classList.remove('vt-wipe-active');
                root.style.removeProperty('--vt-x');
                root.style.removeProperty('--vt-y');
                root.style.removeProperty('--vt-r');
            });

            return;
        }

        // --- Fallback ---
        fallbackAnimate(origin.x, origin.y, maxRadius, next);
    }

    /* ============================================
       7. Auto-bind semua [data-theme-toggle]
       ============================================ */
    function bind() {
        var btns = document.querySelectorAll('[data-theme-toggle]');
        btns.forEach(function (btn) {
            if (btn.dataset.themeBound) return;
            btn.dataset.themeBound = '1';
            btn.addEventListener('click', toggleTheme);
        });
    }

    /* ============================================
       8. Expose API
       ============================================ */
    window.SiavoTheme = {
        toggle: toggleTheme,
        apply: applyTheme,
        isDark: isDarkActive,
        updateIcons: updateIcons
    };

    /* ============================================
       9. Init saat DOM siap
       ============================================ */
    function init() {
        bind();
        updateIcons();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    /* ============================================
       10. Observe perubahan attribute data-theme
       ============================================ */
    var observer = new MutationObserver(function () {
        updateIcons();
    });
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
    });

})();