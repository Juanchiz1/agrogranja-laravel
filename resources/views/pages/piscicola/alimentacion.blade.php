@extends('layouts.app')
@section('title','Alimentacion Piscicola')
@section('page_title','Alimentacion Diaria')
@section('back_url', route('piscicola.estanques'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- SELECTOR FECHA --}}
<div class="section-card" style="padding:12px 14px;">
  <form method="GET" action="{{ route('piscicola.alimentacion') }}"
        style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <label style="font-size:.82rem;font-weight:600;color:#64748b;">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="{{ $fecha }}"
           style="max-width:160px;" onchange="this.form.submit()">
    <div style="margin-left:auto;text-align:right;">
      <div style="font-size:1.1rem;font-weight:800;color:var(--pisc-naranja);">
        {{ round($totalAlimentoDia, 2) }} kg
      </div>
      <div style="font-size:.72rem;color:#64748b;">total del dia</div>
    </div>
    <button onclick="openModal('modalAlimentacion')" type="button" class="btn btn-sm btn-primary">
      + Registrar
    </button>
  </form>
</div>

{{-- REGISTROS DEL DÍA --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Alimentacion del dia</div>
  @forelse($siembras as $s)
  @php
    $regHoy  = $alimentosDelDia[$s->id] ?? null;
    $tienReg = !empty($regHoy);
    $bioRef  = $tienReg ? $regHoy->biomasa_referencia_kg : $s->biomasa_actual_kg;
    $tasaRec = 0;
    if ($bioRef && $bioRef > 0) {
        // Tasa recomendada segun peso
        $pesoGramos = (float)($s->peso_promedio_actual_g ?? 0);
        if ($pesoGramos < 50)        { $tasaRec = 6.0; }
        elseif ($pesoGramos < 150)   { $tasaRec = 4.0; }
        elseif ($pesoGramos < 300)   { $tasaRec = 2.8; }
        else                          { $tasaRec = 2.0; }
    }
    $aliRecomendado = ($bioRef && $tasaRec > 0) ? round($bioRef * $tasaRec / 100, 2) : null;
  @endphp
  <div class="ali-row">
    <div>
      <div style="font-weight:600;font-size:.87rem;">{{ $s->nombre_estanque }}</div>
      <div style="font-size:.72rem;color:#64748b;">
        {{ number_format($s->cantidad_actual ?? $s->cantidad_alevinos) }} peces ·
        Biomasa: {{ round((float)($s->biomasa_actual_kg ?? 0), 1) }} kg
        @if($aliRecomendado)
        · Recomendado: {{ $aliRecomendado }} kg/dia ({{ $tasaRec }}%)
        @endif
      </div>
    </div>
    <div style="text-align:right;">
      @if($tienReg)
      <div class="ali-kg">{{ $regHoy->alimento_kg }} kg</div>
      @if($regHoy->tasa_alimentacion_pct)
      <div class="ali-tasa">{{ $regHoy->tasa_alimentacion_pct }}% biomasa</div>
      @endif
      @else
      <button onclick="openAliRapido({{ $s->id }},{{ $s->estanque_id }},'{{ addslashes($s->nombre_estanque) }}',{{ $aliRecomendado ?? 0 }},{{ round((float)($s->biomasa_actual_kg ?? 0), 1) }})"
              style="background:none;border:1px dashed #e2e8f0;border-radius:6px;padding:3px 10px;
                     font-size:.78rem;color:#94a3b8;cursor:pointer;">
        Sin registro hoy
      </button>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    No hay siembras activas para registrar alimentacion.
  </div>
  @endforelse
</div>

{{-- CURVA 30 DÍAS --}}
@if(count($chartLabels) > 1)
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Alimento total — 30 dias</div>
  <div style="position:relative;height:150px;">
    <canvas id="chartAli"></canvas>
  </div>
</div>
@endif

<div style="margin-bottom:80px;"></div>

{{-- MODAL ALIMENTACION --}}
<div id="modalAlimentacion" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar alimentacion</div>
    <form method="POST" action="{{ route('piscicola.alimentacion.store') }}">
      @csrf
      <div class="form-group">
        <label>Siembra / Estanque *</label>
        <select name="siembra_id" class="form-control" required onchange="syncEstanque(this)">
          <option value="">Seleccionar...</option>
          @foreach($siembras as $s)
          <option value="{{ $s->id }}" data-estanque="{{ $s->estanque_id }}"
                  data-biomasa="{{ $s->biomasa_actual_kg ?? 0 }}">
            {{ $s->nombre_estanque }} ({{ $s->especie_cultivada }})
          </option>
          @endforeach
        </select>
        <input type="hidden" name="estanque_id" id="aliEstanqueId">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ $fecha }}" required>
        </div>
        <div class="form-group">
          <label>Alimento suministrado (kg) *</label>
          <input type="number" name="alimento_kg" id="aliKgInput" class="form-control"
                 step="0.01" min="0" required oninput="calcTasaAli()">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de alimento</label>
          <select name="tipo_alimento" class="form-control">
            <option value="">Sin especificar</option>
            <option value="iniciacion">Iniciacion (alevino)</option>
            <option value="levante">Levante</option>
            <option value="engorde">Engorde</option>
            <option value="finalizacion">Finalizacion</option>
          </select>
        </div>
        <div class="form-group">
          <label>N° de raciones/dia</label>
          <select name="num_raciones" class="form-control">
            <option value="2" selected>2 veces</option>
            <option value="3">3 veces</option>
            <option value="4">4 veces</option>
            <option value="1">1 vez</option>
          </select>
        </div>
      </div>
      <input type="hidden" name="biomasa_ref_kg" id="aliBiomasaRef">
      <div style="background:#f0f9ff;border-radius:8px;padding:8px 10px;margin-bottom:10px;font-size:.78rem;">
        Tasa calculada: <strong id="tasaCalc">—</strong> % de la biomasa
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control"
               placeholder="Apetito, comportamiento, sobrante...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalAlimentacion')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

function syncEstanque(sel) {
  var opt = sel.options[sel.selectedIndex];
  document.getElementById('aliEstanqueId').value  = opt.getAttribute('data-estanque') || '';
  document.getElementById('aliBiomasaRef').value  = opt.getAttribute('data-biomasa')  || '';
  calcTasaAli();
}
function calcTasaAli() {
  var ali  = parseFloat(document.getElementById('aliKgInput').value) || 0;
  var bio  = parseFloat(document.getElementById('aliBiomasaRef').value) || 0;
  var el   = document.getElementById('tasaCalc');
  el.textContent = (ali > 0 && bio > 0) ? Math.round(ali / bio * 1000) / 10 + '%' : '—';
}
function openAliRapido(siembraId, estanqueId, nombre, aliRec, biomasa) {
  var sel = document.querySelector('[name=siembra_id]');
  for (var i = 0; i < sel.options.length; i++) {
    if (sel.options[i].value == siembraId) { sel.selectedIndex = i; break; }
  }
  document.getElementById('aliEstanqueId').value = estanqueId;
  document.getElementById('aliBiomasaRef').value = biomasa;
  if (aliRec > 0) document.getElementById('aliKgInput').value = aliRec;
  calcTasaAli();
  openModal('modalAlimentacion');
}

var ctxAli = document.getElementById('chartAli');
if (ctxAli) {
  new Chart(ctxAli, {
    type: 'bar',
    data: {
      labels: {!! json_encode($chartLabels) !!},
      datasets: [{
        label: 'Alimento total (kg)',
        data: {!! json_encode($chartAlimento) !!},
        backgroundColor: 'rgba(2,132,199,.6)',
        borderColor: '#0284c7', borderWidth: 1
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font: { size: 9 }, maxTicksLimit: 12 }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { font: { size: 9 } } }
      }
    }
  });
}
</script>
@endpush