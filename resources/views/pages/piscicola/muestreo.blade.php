@extends('layouts.app')
@section('title','Muestreos')
@section('page_title','Muestreos de Biomasa')
@section('back_url', route('piscicola.estanques'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

<div class="alerta-pisc info" style="margin-bottom:10px;">
  <span>i</span>
  <div style="font-size:.8rem;">
    <strong>Muestreo:</strong> Pesa una muestra de 30-50 peces y calcula el peso promedio.
    El sistema actualiza la biomasa total del estanque: <em>cantidad x peso promedio</em>.
    Hacerlo cada 15-21 dias para ajustar la tasa de alimentacion.
  </div>
</div>

<div class="section-card" style="padding:12px 14px;">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <span style="font-size:.85rem;color:#64748b;">{{ $siembrasActivas->count() }} siembra(s) activas</span>
    @if($siembrasActivas->count())
    <button onclick="openModal('modalMuestreo')" class="btn btn-sm btn-primary">+ Nuevo muestreo</button>
    @endif
  </div>
</div>

{{-- HISTORIAL --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Historial de muestreos</div>

  @forelse($muestreos as $m)
  @php
    $gpd         = (float)($m->ganancia_diaria_g ?? 0);
    $pesoG       = (float)$m->peso_promedio_g;
    $biomasaKg   = (float)($m->biomasa_estimada_kg ?? 0);
    $semana      = (int)($m->semana_cultivo ?? 0);
    $cantEst     = (int)($m->cantidad_estimada ?? 0);
    $fechaFmt    = \Carbon\Carbon::parse($m->fecha)->format('d/m/Y');
    $gpdColor    = $gpd >= 3.0 ? '#15803d' : ($gpd >= 1.5 ? '#b45309' : '#dc2626');
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;
              padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:700;">{{ $m->nombre_estanque }}</div>
      <div style="font-size:.74rem;color:#64748b;">
        {{ $fechaFmt }}
        @if($semana > 0)&middot; Semana {{ $semana }}@endif
        &middot; {{ $m->peces_muestreados }} peces pesados
        @if($m->especie ?? false) &middot; {{ $m->especie }}@endif
      </div>
      @if($m->observaciones)
      <div style="font-size:.72rem;color:#94a3b8;">{{ $m->observaciones }}</div>
      @endif
    </div>
    <div style="text-align:right;">
      <div style="font-size:1rem;font-weight:800;color:#0ea5e9;">{{ $pesoG }}g</div>
      <div style="font-size:.7rem;color:#64748b;">{{ $biomasaKg }} kg biomasa</div>
      @if($gpd > 0)
      <div style="font-size:.7rem;font-weight:700;color:{{ $gpdColor }};">
        +{{ $gpd }}g/dia
      </div>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    Sin muestreos registrados. Realiza el primer muestreo 15-21 dias despues de la siembra.
  </div>
  @endforelse
</div>

<div style="margin-bottom:80px;"></div>

{{-- MODAL MUESTREO --}}
<div id="modalMuestreo" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar muestreo de biomasa</div>
    <form method="POST" action="{{ route('piscicola.muestreo.store') }}">
      @csrf
      <div class="form-group">
        <label>Siembra / Estanque *</label>
        <select name="siembra_id" class="form-control" required
                onchange="actualizarBiomasa(this)">
          <option value="">Seleccionar...</option>
          @foreach($siembrasActivas as $s)
          <option value="{{ $s->id }}"
                  data-biomasa="{{ $s->biomasa_actual_kg ?? $s->biomasa_inicial_kg }}"
                  data-cantidad="{{ $s->cantidad_actual ?? $s->cantidad_alevinos }}"
                  data-peso="{{ $s->peso_promedio_actual_g ?? $s->peso_promedio_inicial_g }}">
            {{ $s->nombre_estanque }} — {{ $s->especie_cultivada }} (dia {{ now()->diffInDays(\Carbon\Carbon::parse($s->fecha_siembra)) }})
          </option>
          @endforeach
        </select>
      </div>

      {{-- Info biomasa actual --}}
      <div id="infoBiomasaActual" style="display:none;background:#f0f9ff;border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:.78rem;color:#0369a1;">
        Biomasa actual: <strong id="biomasaActDisplay">—</strong> kg
        &middot; Cantidad: <strong id="cantidadActDisplay">—</strong> peces
        &middot; Peso actual: <strong id="pesoActDisplay">—</strong> g
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Peces pesados (muestra) *</label>
          <input type="number" name="peces_muestreados" class="form-control"
                 min="5" value="30" required placeholder="Ej: 30">
          <div style="font-size:.68rem;color:#64748b;margin-top:2px;">Minimo 30 peces para buena muestra</div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Peso promedio (g) *</label>
          <input type="number" name="peso_promedio_g" class="form-control"
                 step="0.1" min="1" required placeholder="Ej: 250.5"
                 oninput="calcBiomasa()">
        </div>
        <div class="form-group">
          <label>Cantidad estimada total</label>
          <input type="number" name="cantidad_estimada" id="cantEstInput" class="form-control"
                 min="0" placeholder="Dejar vacio = usa cantidad actual"
                 oninput="calcBiomasa()">
          <div style="font-size:.68rem;color:#64748b;margin-top:2px;">Si no ingresa, usa la cantidad registrada</div>
        </div>
      </div>

      {{-- Biomasa estimada calculada --}}
      <div style="background:#f0fdf4;border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:.78rem;">
        Biomasa nueva estimada: <strong id="biomasaNueva" style="color:#15803d;">—</strong> kg
      </div>

      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control"
               placeholder="Condicion general, color, comportamiento...">
      </div>

      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalMuestreo')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar muestreo</button>
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

var cantidadActual = 0;

function actualizarBiomasa(sel) {
  var opt = sel.options[sel.selectedIndex];
  var biomasa  = opt.getAttribute('data-biomasa') || '—';
  var cantidad = opt.getAttribute('data-cantidad') || '—';
  var peso     = opt.getAttribute('data-peso')     || '—';
  cantidadActual = parseInt(opt.getAttribute('data-cantidad')) || 0;

  var wrap = document.getElementById('infoBiomasaActual');
  if (opt.value) {
    wrap.style.display = 'block';
    document.getElementById('biomasaActDisplay').textContent  = biomasa + ' kg';
    document.getElementById('cantidadActDisplay').textContent = cantidad + ' peces';
    document.getElementById('pesoActDisplay').textContent     = peso + ' g';
  } else {
    wrap.style.display = 'none';
  }
  calcBiomasa();
}

function calcBiomesa() { calcBiomasa(); }
function calcBiomasa() {
  var peso  = parseFloat(document.querySelector('[name=peso_promedio_g]').value) || 0;
  var cant  = parseInt(document.getElementById('cantEstInput').value) || cantidadActual;
  var el    = document.getElementById('biomasaNueva');
  if (peso > 0 && cant > 0) {
    var bm = Math.round((cant * peso / 1000) * 100) / 100;
    el.textContent = bm + ' kg (' + cant + ' peces x ' + peso + 'g)';
  } else {
    el.textContent = '—';
  }
}
</script>
@endpush