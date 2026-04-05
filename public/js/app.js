// Agrogranja — app.js

(function () {
  'use strict';

  // ── Mode Toggle ──────────────────────────────────────────
  const STORAGE_KEY = 'agrogranja_layout';
  const body        = document.body;
  const toggle      = document.getElementById('modeToggle');

  function applyLayout(mode) {
    body.setAttribute('data-layout', mode);
    localStorage.setItem(STORAGE_KEY, mode);
  }

  function initLayout() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved === 'mobile' || saved === 'pc') {
      applyLayout(saved);
    }
    // If no saved preference, CSS media query handles it automatically (auto = PC on ≥900px)
  }

  if (toggle) {
    toggle.addEventListener('click', function () {
      const current = body.getAttribute('data-layout');
      // Determine effective mode (considering media query)
      const isWide = window.innerWidth >= 900;
      if (current === 'pc') {
        applyLayout('mobile');
      } else if (current === 'mobile') {
        applyLayout('pc');
      } else {
        // auto mode — toggle opposite of what CSS shows
        applyLayout(isWide ? 'mobile' : 'pc');
      }
    });
  }

  initLayout();

  // ── Auto-dismiss alerts ──────────────────────────────────
  document.querySelectorAll('.alert-flash').forEach(function (el) {
    setTimeout(function () {
      el.style.transition = 'opacity .4s, transform .4s';
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(-6px)';
      setTimeout(function () { el.remove(); }, 400);
    }, 4000);
  });

  // ── Modal helpers ────────────────────────────────────────
  window.openModal = function (id) {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'flex'; document.body.style.overflow = 'hidden'; }
  };
  window.closeModal = function (id) {
    const m = document.getElementById(id);
    if (m) { m.style.display = 'none'; document.body.style.overflow = ''; }
  };

  // Close modal on backdrop click
  document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay) {
        overlay.style.display = 'none';
        document.body.style.overflow = '';
        history.replaceState(null, '', location.pathname + location.search);
      }
    });
  });

  // Swipe down to close modal on touch
  document.querySelectorAll('.modal-sheet').forEach(function (sheet) {
    let startY = 0;
    sheet.addEventListener('touchstart', function (e) { startY = e.touches[0].clientY; }, { passive: true });
    sheet.addEventListener('touchend', function (e) {
      if (e.changedTouches[0].clientY - startY > 80) {
        const overlay = sheet.closest('.modal-overlay');
        if (overlay) { overlay.style.display = 'none'; document.body.style.overflow = ''; }
      }
    }, { passive: true });
  });

  // ── Toast ────────────────────────────────────────────────
  window.showToast = function (msg, type) {
    const t = document.createElement('div');
    t.className = 'alert alert-' + (type || 'success');
    t.style.cssText = 'position:fixed;top:16px;left:50%;transform:translateX(-50%);z-index:9999;white-space:nowrap;animation:slideDown .2s ease;min-width:200px;justify-content:center;';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function () { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(function () { t.remove(); }, 300); }, 2800);
  };

  // ── Price auto-calc (ingresos) ───────────────────────────
  function calcTotal() {
    const cant  = parseFloat(document.querySelector('[name="cantidad"]')?.value) || 0;
    const punit = parseFloat(document.querySelector('[name="precio_unitario"]')?.value) || 0;
    const totalEl = document.querySelector('[name="valor_total"]');
    if (cant && punit && totalEl) totalEl.value = Math.round(cant * punit);
  }
  document.querySelector('[name="precio_unitario"]')?.addEventListener('input', calcTotal);
  document.querySelector('[name="cantidad"]')?.addEventListener('input', calcTotal);

})();
