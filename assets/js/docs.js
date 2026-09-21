/* ============================================
   SIAVO — docs.js
   Scroll spy + smooth scroll untuk TOC (informasi.php & kegiatan.php)
   Vanilla JS, tanpa library. Scroll di-throttle pakai requestAnimationFrame.
   ============================================ */
(function () {
  'use strict';

  var toc = document.querySelector('[data-toc]');
  if (!toc) return;

  var list  = toc.querySelector('ul');
  var links = [].slice.call(toc.querySelectorAll('ul a[href^="#"]'));
  var targets = links.map(function (a) {
    return document.getElementById(a.getAttribute('href').slice(1));
  });
  // Kalau ada link TOC yang target-nya tidak ketemu, batalkan (jangan setengah jalan)
  if (!links.length || targets.indexOf(null) !== -1) return;

  var OFFSET  = 90;   // tinggi navbar sticky + sedikit ruang napas
  var reduce  = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var current = -1;
  var ticking = false;

  // Live region untuk pembaca layar (aria-live), dibuat lewat JS supaya PHP tetap bersih
  var live = document.createElement('p');
  live.setAttribute('aria-live', 'polite');
  live.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap';
  toc.appendChild(live);

  function setActive(i) {
    if (i === current) return;
    current = i;

    links.forEach(function (a, k) {
      if (k === i) a.setAttribute('aria-current', 'true');
      else a.removeAttribute('aria-current');
    });

    // Jaga link aktif tetap kelihatan di dalam list TOC yang punya scroll sendiri
    // (tanpa ikut menggeser halaman)
    var a = links[i];
    if (list && (a.offsetTop < list.scrollTop ||
                 a.offsetTop + a.offsetHeight > list.scrollTop + list.clientHeight)) {
      list.scrollTop = a.offsetTop - list.clientHeight / 2;
    }

    live.textContent = 'Bagian aktif: ' + (a.getAttribute('title') || a.textContent.trim());
  }

  function update() {
    ticking = false;
    var idx = 0;
    for (var i = 0; i < targets.length; i++) {
      // target yang bagian atasnya sudah melewati garis offset = kandidat aktif
      if (targets[i].getBoundingClientRect().top <= OFFSET + 12) idx = i;
    }
    // Mentok di bawah halaman: pastikan item terakhir bisa aktif
    if (window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4) {
      idx = targets.length - 1;
    }
    setActive(idx);
  }

  function onScroll() {
    if (!ticking) {
      ticking = true;
      window.requestAnimationFrame(update);
    }
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  window.addEventListener('resize', onScroll);
  update();

  // Klik TOC -> smooth scroll dengan offset -90px
  links.forEach(function (a, i) {
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var t = targets[i];
      var y = t.getBoundingClientRect().top + window.scrollY - OFFSET;
      window.scrollTo({ top: y, behavior: reduce ? 'auto' : 'smooth' });
      try { history.replaceState(null, '', '#' + t.id); } catch (err) {}
      t.setAttribute('tabindex', '-1');       // biar fokus keyboard ikut pindah
      t.focus({ preventScroll: true });
    });
  });

  // "Kembali ke atas"
  var topLink = toc.querySelector('.toc-top');
  if (topLink) {
    topLink.addEventListener('click', function (e) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
      try { history.replaceState(null, '', location.pathname + location.search); } catch (err) {}
    });
  }
})();