
(function() {
  'use strict';

  // ─── CONVERSION VERBAL ESPAÑOL COLOMBIANO ───────────────────────────────────

  const UNIDADES = [
    '', 'un', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve',
    'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis',
    'diecisiete', 'dieciocho', 'diecinueve'
  ];
  const DECENAS = [
    '', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta',
    'sesenta', 'setenta', 'ochenta', 'noventa'
  ];
  const CENTENAS = [
    '', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos',
    'seiscientos', 'setecientos', 'ochocientos', 'novecientos'
  ];

  function menorDeMil(n) {
    if (n === 0) return '';
    if (n === 100) return 'cien';
    if (n < 20) return UNIDADES[n];
    if (n < 100) {
      const dec = Math.floor(n / 10);
      const uni = n % 10;
      if (dec === 2 && uni > 0) return 'veinti' + UNIDADES[uni];
      return uni === 0 ? DECENAS[dec] : DECENAS[dec] + ' y ' + UNIDADES[uni];
    }
    const cent = Math.floor(n / 100);
    const resto = n % 100;
    const centStr = CENTENAS[cent];
    return resto === 0 ? centStr : centStr + ' ' + menorDeMil(resto);
  }

  function copVerbal(n) {
    if (!n || isNaN(n)) return '';
    n = Math.abs(Math.round(Number(n)));
    if (n === 0) return 'cero pesos';

    // Menos de mil
    if (n < 1000) {
      return menorDeMil(n) + ' pesos';
    }

    // Miles (1.000 – 999.999)
    if (n < 1_000_000) {
      const miles = Math.floor(n / 1000);
      const resto = n % 1000;
      let txt = miles === 1 ? 'mil' : menorDeMil(miles) + ' mil';
      if (resto > 0) txt += ' ' + menorDeMil(resto);
      return txt + ' pesos';
    }

    // Millones (1.000.000 – 999.999.999)
    if (n < 1_000_000_000) {
      const millones = Math.floor(n / 1_000_000);
      const resto = n % 1_000_000;
      let txt = millones === 1 ? 'un millón' : menorDeMil(millones) + ' millones';
      if (resto >= 1000) {
        const milesResto = Math.floor(resto / 1000);
        const unidadesResto = resto % 1000;
        txt += ' ' + (milesResto === 1 ? 'mil' : menorDeMil(milesResto) + ' mil');
        if (unidadesResto > 0) txt += ' ' + menorDeMil(unidadesResto);
      } else if (resto > 0) {
        txt += ' ' + menorDeMil(resto);
      }
      return txt + ' de pesos';
    }

    // Miles de millones
    const milesMill = Math.floor(n / 1_000_000_000);
    const restoMill = n % 1_000_000_000;
    let txt = milesMill === 1 ? 'mil millones' : menorDeMil(milesMill) + ' mil millones';
    if (restoMill >= 1_000_000) {
      const millResto = Math.floor(restoMill / 1_000_000);
      txt += ' ' + menorDeMil(millResto) + ' millones';
    }
    return txt + ' de pesos';
  }

  // Capitaliza primera letra
  function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
  }

  // Formatea número con puntos como separador de miles (estilo COP)
  function copFormatNum(n) {
    if (!n && n !== 0) return '';
    return Math.round(Number(n)).toLocaleString('es-CO');
  }

  // ─── INICIALIZAR INPUTS ──────────────────────────────────────────────────────
  // Añadir atributo data-cop-input a cualquier <input type="number"> de dinero

  function crearHintDiv(input) {
    const hint = document.createElement('div');
    hint.className = 'cop-hint';
    // Si el input tiene data-cop-type="gasto", aplicar estilos marrón
    if (input.dataset.copType === 'gasto') {
      hint.classList.add('cop-hint-gasto');
    }
    hint.setAttribute('aria-live', 'polite');
    input.parentNode.insertBefore(hint, input.nextSibling);
    return hint;
  }

  function actualizarHint(input, hint) {
    const raw = parseFloat(input.value) || 0;
    if (raw <= 0) {
      hint.innerHTML = '';
      hint.classList.remove('visible');
      return;
    }
    const verbal = ucfirst(copVerbal(raw));
    const formatted = copFormatNum(raw);
    hint.innerHTML = `<span class="cop-hint-num">$ ${formatted}</span><span class="cop-hint-sep">·</span><span class="cop-hint-verbal">${verbal}</span>`;
    hint.classList.add('visible');
  }

  function initInputs() {
    document.querySelectorAll('[data-cop-input]').forEach(function(input) {
      // Evitar doble inicialización
      if (input.dataset.copInit) return;
      input.dataset.copInit = '1';

      const hint = crearHintDiv(input);

      // Actualizar al escribir
      input.addEventListener('input', function() { actualizarHint(input, hint); });
      input.addEventListener('change', function() { actualizarHint(input, hint); });

      // Actualizar al abrir modal (observer en el padre overlay)
      actualizarHint(input, hint);
    });
  }

  // ─── INICIALIZAR DISPLAYS ────────────────────────────────────────────────────
  // Añadir atributo data-cop="valor_numerico" a spans/divs que muestran montos

  function initDisplays() {
    document.querySelectorAll('[data-cop]').forEach(function(el) {
      if (el.dataset.copInit) return;
      el.dataset.copInit = '1';

      const val = parseFloat(el.dataset.cop) || 0;
      if (val <= 0) return;

      const verbal = ucfirst(copVerbal(val));
      const hint = document.createElement('div');
      hint.className = 'cop-display-hint';
      hint.textContent = verbal;
      el.appendChild(hint);
    });
  }

  // ─── OBSERVER PARA MODALES ───────────────────────────────────────────────────
  // Cuando se abre un modal, inicializar los inputs que contiene

  function watchModals() {
    const observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(m) {
        m.addedNodes.forEach(function(node) {
          if (node.nodeType !== 1) return;
          // También buscar inputs dentro del nodo que cambió estilo (modal visible)
        });
        // Detectar cambio de display en modal-overlay
        if (m.type === 'attributes' && m.attributeName === 'style') {
          const el = m.target;
          if (el.classList && el.classList.contains('modal-overlay')) {
            const isVisible = el.style.display !== 'none' && el.style.display !== '';
            if (isVisible) {
              setTimeout(function() {
                el.querySelectorAll('[data-cop-input]').forEach(function(input) {
                  // Re-inicializar si no tiene hint aún
                  if (!el.querySelector('.cop-hint')) {
                    initInputs();
                  }
                  const hint = input.nextElementSibling;
                  if (hint && hint.classList.contains('cop-hint')) {
                    actualizarHint(input, hint);
                  }
                });
              }, 50);
            }
          }
        }
      });
    });

    document.querySelectorAll('.modal-overlay').forEach(function(modal) {
      observer.observe(modal, { attributes: true, attributeFilter: ['style'] });
    });
  }

  // ─── EXPONER GLOBALMENTE ─────────────────────────────────────────────────────

  window.COP = {
    verbal: copVerbal,
    format: copFormatNum,
    init: function() {
      initInputs();
      initDisplays();
      watchModals();
    }
  };

  // ─── CSS EMBEBIDO ────────────────────────────────────────────────────────────

  function injectCSS() {
    if (document.getElementById('cop-format-css')) return;
    const style = document.createElement('style');
    style.id = 'cop-format-css';
    style.textContent = `
      /* ── COP Hint en inputs ───────────────────── */
      .cop-hint {
        display: none;
        margin-top: 5px;
        padding: 5px 10px;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #86efac;
        border-radius: 8px;
        font-size: .78rem;
        color: #166534;
        line-height: 1.3;
        transition: all .2s ease;
        animation: copHintIn .2s ease;
      }
      .cop-hint.visible { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
      @keyframes copHintIn {
        from { opacity: 0; transform: translateY(-4px); }
        to   { opacity: 1; transform: translateY(0); }
      }
      .cop-hint-num {
        font-weight: 700;
        font-size: .8rem;
        color: #15803d;
      }
      .cop-hint-sep {
        color: #86efac;
        font-size: .75rem;
      }
      .cop-hint-verbal {
        font-style: italic;
        color: #166534;
        font-size: .76rem;
      }

      /* ── Hint en gastos (rojo/marrón) ─────────── */
      .cop-hint-gasto {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%) !important;
        border-color: #fb923c !important;
        color: #9a3412 !important;
      }
      .cop-hint-gasto .cop-hint-num  { color: #c2410c !important; }
      .cop-hint-gasto .cop-hint-sep  { color: #fdba74 !important; }
      .cop-hint-gasto .cop-hint-verbal { color: #9a3412 !important; }

      /* ── Display hint bajo montos mostrados ───── */
      .cop-display-hint {
        font-size: .67rem;
        color: var(--text-muted, #9ca3af);
        font-style: italic;
        display: block;
        margin-top: 1px;
        line-height: 1.2;
      }
    `;
    document.head.appendChild(style);
  }

  // ─── AUTO-INIT ───────────────────────────────────────────────────────────────

  injectCSS();
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() { COP.init(); });
  } else {
    COP.init();
  }

})();