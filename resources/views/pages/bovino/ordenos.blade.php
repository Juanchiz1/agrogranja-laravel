@extends('layouts.app')
@section('title','Ordeños')
@section('page_title','🥛 Ordeños')
@section('back_url', route('bovino.hato'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/bovino.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- SELECCIONAR FECHA --}}
<div class="section-card" style="padding:12px 14px;">
  <form method="GET" action="{{ route('bovino.ordenos') }}" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <label style="font-size:.82rem;font-weight:600;color:#64748b;">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}"
           style="max-width:160px;" onchange="this.form.submit()">
    <span style="font-size:.8rem;color:#64748b;">
      Total: <strong style="color:#15803d;">{{ number_format($totalDia,1) }} L</strong>
    </span>
    @if($totalDia > 0)
    <button type="button" onclick="openModal('modalVenta')"
            class="btn btn-sm btn-primary" style="margin-left:auto;">
      💰 Vender producción
    </button>
    @endif
  </form>
</div>

{{-- GRILLA DE ORDEÑOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐄 Registro por vaca</div>
    <button onclick="openModal('modalOrdeno')" class="btn btn-sm btn-primary">+ Individual</button>
  </div>

  @if($vacasProductoras->isEmpty())
    <div style="text-align:center;padding:24px;color:#64748b;">
      <div style="font-size:2rem;margin-bottom:8px;">🐄</div>
      <p style="margin-bottom:12px;">No hay vacas con lactancia activa.</p>
      <button onclick="openModal('modalLactancia')" class="btn btn-sm btn-primary">
        🐄 Iniciar lactancia
      </button>
    </div>
  @else
  <div class="ordeno-tabla">
    <div class="ordeno-header">
      <span>Vaca</span><span>AM 🌅</span><span>PM 🌆</span><span>Total</span>
    </div>
    @foreach($vacasProductoras as $vaca)
    @php
      $am = null; $pm = null;
      if (isset($ordenosDelDia[$vaca->id])) {
          foreach ($ordenosDelDia[$vaca->id] as $o) {
              if ($o->sesion === 'am') $am = $o;
              if ($o->sesion === 'pm') $pm = $o;
          }
      }
      $totalVaca = ($am->litros ?? 0) + ($pm->litros ?? 0);
    @endphp
    <div class="ordeno-row">
      <div class="ordeno-nombre">
        {{ $vaca->nombre_lote }}
        @if($vaca->raza)<div style="font-size:.7rem;color:#94a3b8;">{{ $vaca->raza }}</div>@endif
      </div>
      <div class="ordeno-celda">
        @if($am)
          <span class="ordeno-litros-ok">{{ $am->litros }} L</span>
          <form method="POST" action="{{ route('bovino.ordenos.delete',$am->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
            @csrf <button class="btn-icon-del" type="submit">✕</button>
          </form>
        @else
          <button onclick="openOrdenoRapido({{ $vaca->id }},'{{ addslashes($vaca->nombre_lote) }}','am','{{ $fecha }}')"
                  class="btn-ordeno-add" title="Registrar AM">＋</button>
        @endif
      </div>
      <div class="ordeno-celda">
        @if($pm)
          <span class="ordeno-litros-ok">{{ $pm->litros }} L</span>
          <form method="POST" action="{{ route('bovino.ordenos.delete',$pm->id) }}" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
            @csrf <button class="btn-icon-del" type="submit">✕</button>
          </form>
        @else
          <button onclick="openOrdenoRapido({{ $vaca->id }},'{{ addslashes($vaca->nombre_lote) }}','pm','{{ $fecha }}')"
                  class="btn-ordeno-add" title="Registrar PM">＋</button>
        @endif
      </div>
      <div class="ordeno-total">{{ $totalVaca > 0 ? number_format($totalVaca,1).' L' : '—' }}</div>
    </div>
    @endforeach
  </div>
  @endif
</div>

{{-- CURVA DE PRODUCCIÓN --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">📈 Producción últimos 30 días</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartOrdenos"></canvas>
  </div>
</div>

{{-- GESTIÓN DE LACTANCIAS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐄 Gestionar lactancias</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button onclick="openModal('modalLactancia')" class="btn btn-sm btn-secondary">
      ＋ Iniciar lactancia
    </button>
    @if($vacasProductoras->count())
    <button onclick="openModal('modalSecar')" class="btn btn-sm btn-ghost">
      💤 Secar vaca
    </button>
    @endif
  </div>
</div>

<div style="margin-bottom:80px;"></div>

{{-- ══ MODALES ════════════════════════════════════════════════════════ --}}

{{-- Modal Ordeño Individual --}}
<div id="modalOrdeno" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🥛 Registrar ordeño</div>
    <form method="POST" action="{{ route('bovino.ordenos.store') }}">
      @csrf
      <div class="form-group">
        <label>Vaca *</label>
        <select name="animal_id" id="selectVacaModal" class="form-control" required>
          <option value="">Seleccionar...</option>
          @foreach($vacasProductoras as $v)
          <option value="{{ $v->id }}">{{ $v->nombre_lote }}{{ $v->raza ? ' ('.$v->raza.')' : '' }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" id="inputFechaModal" class="form-control" value="{{ $fecha }}" required>
        </div>
        <div class="form-group">
          <label>Sesión *</label>
          <select name="sesion" id="selectSesionModal" class="form-control" required>
            <option value="am">🌅 AM (mañana)</option>
            <option value="pm">🌆 PM (tarde)</option>
            <option value="unica">🕐 Única</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Litros *</label>
          <input type="number" name="litros" class="form-control" step="0.1" min="0" placeholder="0.0" required>
        </div>
        <div class="form-group">
          <label>Temp. leche (°C)</label>
          <input type="number" name="temperatura_leche" class="form-control" step="0.1" placeholder="Ej: 38.5">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Calostro, mastitis, etc.">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalOrdeno')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Iniciar Lactancia --}}
<div id="modalLactancia" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🐄 Iniciar lactancia</div>
    <form method="POST" action="{{ route('bovino.ordenos.lactancia') }}">
      @csrf
      <div class="form-group">
        <label>Vaca *</label>
        <select name="animal_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          @forelse($todasBovinas as $b)
          <option value="{{ $b->id }}">
            {{ $b->nombre_lote }}{{ $b->raza ? ' ('.$b->raza.')' : '' }}
            @if($b->categoria_bovina) · {{ str_replace('_',' ',$b->categoria_bovina) }} @endif
          </option>
          @empty
          <option value="" disabled>Sin bovinos registrados — ve a Animales primero</option>
          @endforelse
        </select>
        @if($todasBovinas->isEmpty())
        <div style="font-size:.75rem;color:#ef4444;margin-top:4px;">
          ⚠️ Registra al menos una vaca en el módulo de Animales antes de iniciar una lactancia.
        </div>
        @endif
      </div>
      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" class="form-control" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Ej: Inicio después del parto #2..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalLactancia')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Iniciar lactancia</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Secar Vaca --}}
<div id="modalSecar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💤 Secar vaca</div>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:14px;">
      Cierra la lactancia activa de la vaca seleccionada y la retira del registro de ordeños.
    </p>
    <form id="formSecar" method="POST" action="">
      @csrf
      <div class="form-group">
        <label>Vaca *</label>
        <select id="selectVacaSecar" class="form-control" required onchange="setLactanciaSecar(this)">
          <option value="">Seleccionar...</option>
          @foreach($vacasProductoras as $v)
          <option value="{{ $v->lactancia_id }}" data-lid="{{ $v->lactancia_id }}">
            {{ $v->nombre_lote }}{{ $v->raza ? ' ('.$v->raza.')' : '' }}
          </option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Fecha de secado *</label>
        <input type="date" name="fecha_secado" class="form-control" value="{{ now()->toDateString() }}" required>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalSecar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Secar vaca</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Vender Producción de Leche --}}
<div id="modalVenta" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💰 Vender producción de leche</div>
    <form method="POST" action="{{ route('bovino.produccion.vender') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de venta *</label>
          <select name="tipo_venta" class="form-control" required>
            <option value="diaria">📅 Diaria</option>
            <option value="mensual">🗓️ Mensual (acumulado)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Litros *</label>
          <input type="number" name="litros" id="inputLitrosVenta"
                 class="form-control" step="0.1" min="0.01"
                 value="{{ $totalDia }}" required oninput="calcTotalVenta()">
        </div>
        <div class="form-group">
          <label>Precio / litro (COP) *</label>
          <input type="number" name="precio_litro" id="inputPrecioLitro"
                 class="form-control" step="50" min="0"
                 placeholder="Ej: 1200" required oninput="calcTotalVenta()">
        </div>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:10px;text-align:center;margin-bottom:12px;">
        <div style="font-size:.72rem;color:#64748b;">Total a cobrar</div>
        <div id="totalVentaDisplay" style="font-size:1.4rem;font-weight:800;color:#15803d;">$0</div>
      </div>
      @if(isset($personas) && $personas->count())
      <div class="form-group">
        <label>Comprador (de tu lista)</label>
        <select name="persona_id" class="form-control">
          <option value="">— Sin asignar —</option>
          @foreach($personas as $per)
          <option value="{{ $per->id }}">{{ $per->nombre }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="form-group">
        <label>Nombre del comprador (texto libre)</label>
        <input type="text" name="comprador" class="form-control"
               placeholder="Ej: Lácteos del Valle, Don Pedro...">
      </div>
      <div style="font-size:.78rem;color:#64748b;background:#eff6ff;
                  padding:8px 10px;border-radius:8px;margin-bottom:12px;">
        ✅ Se creará un <strong>Ingreso</strong> automáticamente en el módulo de Finanzas.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalVenta')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar venta</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'flex'; m.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'none'; m.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(m) {
  m.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});

function openOrdenoRapido(animalId, nombre, sesion, fecha) {
  var sel = document.getElementById('selectVacaModal');
  if (sel) sel.value = animalId;
  var ss  = document.getElementById('selectSesionModal');
  if (ss)  ss.value  = sesion;
  var fd  = document.getElementById('inputFechaModal');
  if (fd)  fd.value  = fecha;
  openModal('modalOrdeno');
}

function setLactanciaSecar(sel) {
  var lid = sel.options[sel.selectedIndex].getAttribute('data-lid');
  if (lid) document.getElementById('formSecar').action = '/bovino/ordenos/secar/' + lid;
}

function calcTotalVenta() {
  var litros = parseFloat(document.getElementById('inputLitrosVenta').value)  || 0;
  var precio = parseFloat(document.getElementById('inputPrecioLitro').value) || 0;
  var total  = Math.round(litros * precio);
  document.getElementById('totalVentaDisplay').textContent = '$' + total.toLocaleString('es-CO');
}

var ctx = document.getElementById('chartOrdenos');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: {!! json_encode($chartLabels) !!},
      datasets: [{
        label: 'Litros/día',
        data: {!! json_encode($chartData) !!},
        borderColor: '#15803d', backgroundColor: 'rgba(21,128,61,.12)',
        borderWidth: 2, pointRadius: 2, fill: true, tension: 0.4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font:{size:9}, maxTicksLimit:10 }, grid:{display:false} },
        y: { beginAtZero: true, ticks:{font:{size:10}} }
      }
    }
  });
}
</script>
@endpush