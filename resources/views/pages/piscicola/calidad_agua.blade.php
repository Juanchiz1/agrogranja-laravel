@extends('layouts.app')
@section('title','Calidad del Agua')
@section('page_title','Calidad del Agua')
@section('back_url', route('piscicola.estanques'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
@endpush

@section('content')

{{-- ALERTAS ACTIVAS --}}
@if($alertasActivas->count())
<div class="section-card" style="border-left:4px solid #dc2626;">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    Alertas recientes ({{ $alertasActivas->count() }})
  </div>
  @foreach($alertasActivas as $al)
  <div class="alerta-pisc critica">
    <span>&#9888;</span>
    <div>
      <strong>{{ $al->nombre_estanque }}</strong>
      — {{ \Carbon\Carbon::parse($al->fecha)->format('d/m/Y') }}
      <div style="font-size:.72rem;margin-top:2px;">
        @if($al->oxigeno_mgl !== null && ($al->oxigeno_mgl < 5 || $al->oxigeno_mgl > 8))
          O2: {{ $al->oxigeno_mgl }} mg/L (ideal 5-8) —
        @endif
        @if($al->ph !== null && ($al->ph < 6.5 || $al->ph > 8.5))
          pH: {{ $al->ph }} (ideal 6.5-8.5) —
        @endif
        @if($al->temperatura_c !== null && ($al->temperatura_c < 25 || $al->temperatura_c > 32))
          Temp: {{ $al->temperatura_c }}°C (ideal 25-32) —
        @endif
        @if($al->amoniaco_mg_l !== null && $al->amoniaco_mg_l > 0.05)
          NH3: {{ $al->amoniaco_mg_l }} mg/L (max 0.05)
        @endif
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- ÚLTIMO REGISTRO POR ESTANQUE --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">Estado actual por estanque</div>
    <button onclick="openModal('modalNuevaCalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
  </div>

  @forelse($estanques as $est)
  @php
    $reg = $ultimosRegistros[$est->id] ?? null;
    $tieneReg = !empty($reg);
    // Pre-calcular estados de cada parametro (sin directivas blade en el loop)
    $o2Ok   = true;  $phOk = true; $tempOk = true; $turbOk = true; $nh3Ok = true;
    $o2Str  = 'N/D'; $phStr = 'N/D'; $tempStr = 'N/D'; $turbStr = 'N/D'; $nh3Str = 'N/D';
    $diasUlt = null;

    if ($tieneReg) {
        $diasUlt = now()->diffInDays(\Carbon\Carbon::parse($reg->fecha));
        if ($reg->oxigeno_mgl !== null) {
            $o2Str = $reg->oxigeno_mgl;
            $o2Ok  = ($reg->oxigeno_mgl >= 5 && $reg->oxigeno_mgl <= 8);
        }
        if ($reg->ph !== null) {
            $phStr = $reg->ph;
            $phOk  = ($reg->ph >= 6.5 && $reg->ph <= 8.5);
        }
        if ($reg->temperatura_c !== null) {
            $tempStr = $reg->temperatura_c;
            $tempOk  = ($reg->temperatura_c >= 25 && $reg->temperatura_c <= 32);
        }
        if ($reg->turbidez_cm !== null) {
            $turbStr = $reg->turbidez_cm;
            $turbOk  = ($reg->turbidez_cm >= 25 && $reg->turbidez_cm <= 45);
        }
        if ($reg->amoniaco_mg_l !== null) {
            $nh3Str = $reg->amoniaco_mg_l;
            $nh3Ok  = ($reg->amoniaco_mg_l <= 0.05);
        }
    }
  @endphp
  <div style="margin-bottom:14px;">
    <div style="font-weight:700;font-size:.87rem;margin-bottom:4px;">
      {{ $est->nombre }}
      @if($tieneReg)
      <span style="font-size:.72rem;color:#94a3b8;font-weight:400;">
        — Ultimo: {{ \Carbon\Carbon::parse($reg->fecha)->format('d/m/Y') }}
        (hace {{ $diasUlt }} dias)
      </span>
      @endif
    </div>

    @if($tieneReg)
    <div class="calidad-grid">
      <div class="calidad-param {{ $o2Ok ? 'ok' : 'alerta' }}">
        <div class="calidad-val" style="color:{{ $o2Ok ? '#15803d' : '#dc2626' }};">{{ $o2Str }}</div>
        <div class="calidad-lbl">O2 mg/L</div>
        <div class="calidad-rango">ideal 5-8</div>
      </div>
      <div class="calidad-param {{ $phOk ? 'ok' : 'alerta' }}">
        <div class="calidad-val" style="color:{{ $phOk ? '#15803d' : '#dc2626' }};">{{ $phStr }}</div>
        <div class="calidad-lbl">pH</div>
        <div class="calidad-rango">ideal 6.5-8.5</div>
      </div>
      <div class="calidad-param {{ $tempOk ? 'ok' : 'alerta' }}">
        <div class="calidad-val" style="color:{{ $tempOk ? '#15803d' : '#dc2626' }};">{{ $tempStr }}{{ $tieneReg && $reg->temperatura_c !== null ? '°C' : '' }}</div>
        <div class="calidad-lbl">Temp</div>
        <div class="calidad-rango">ideal 25-32°C</div>
      </div>
      <div class="calidad-param {{ $turbOk ? 'ok' : 'alerta' }}">
        <div class="calidad-val" style="color:{{ $turbOk ? '#15803d' : '#dc2626' }};">{{ $turbStr }}{{ $tieneReg && $reg->turbidez_cm !== null ? ' cm' : '' }}</div>
        <div class="calidad-lbl">Secchi</div>
        <div class="calidad-rango">ideal 25-45 cm</div>
      </div>
      <div class="calidad-param {{ $nh3Ok ? 'ok' : 'alerta' }}">
        <div class="calidad-val" style="color:{{ $nh3Ok ? '#15803d' : '#dc2626' }};">{{ $nh3Str }}</div>
        <div class="calidad-lbl">NH3 mg/L</div>
        <div class="calidad-rango">max 0.05</div>
      </div>
      <div class="calidad-param sin-dato" style="cursor:pointer;" onclick="openCalidadEst({{ $est->id }},'{{ addslashes($est->nombre) }}')">
        <div class="calidad-val" style="font-size:1.3rem;">+</div>
        <div class="calidad-lbl">Nuevo</div>
        <div class="calidad-rango">registro</div>
      </div>
    </div>
    @else
    <div style="text-align:center;padding:10px;background:#f8fafc;border-radius:8px;font-size:.82rem;color:#94a3b8;">
      Sin registros de calidad del agua.
      <button onclick="openCalidadEst({{ $est->id }},'{{ addslashes($est->nombre) }}')"
              class="btn btn-sm btn-ghost" style="margin-left:8px;font-size:.75rem;">
        + Registrar
      </button>
    </div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    No hay estanques registrados.
  </div>
  @endforelse
</div>

{{-- HISTORIAL --}}
@if($historial->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">
    Historial 30 dias ({{ $historial->count() }} registros)
  </div>
  @foreach($historial->take(10) as $h)
  @php
    $hFecha = \Carbon\Carbon::parse($h->fecha)->format('d/m/Y');
    $hAlerta = !empty($h->alerta);
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.82rem;">
    <div>
      <span style="font-weight:600;">{{ $h->nombre_estanque }}</span>
      <span style="color:#94a3b8;font-size:.72rem;"> — {{ $hFecha }}</span>
    </div>
    <div style="display:flex;gap:8px;font-size:.72rem;color:#64748b;">
      @if($h->oxigeno_mgl !== null)<span>O2:{{ $h->oxigeno_mgl }}</span>@endif
      @if($h->ph !== null)<span>pH:{{ $h->ph }}</span>@endif
      @if($h->temperatura_c !== null)<span>{{ $h->temperatura_c }}°C</span>@endif
      @if($hAlerta)
      <span style="background:#fef2f2;color:#dc2626;padding:1px 6px;border-radius:8px;font-weight:700;">
        Alerta
      </span>
      @endif
    </div>
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>

{{-- MODAL NUEVA CALIDAD --}}
<div id="modalNuevaCalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Calidad del agua — <span id="nombreEstCalNew"></span></div>
    <form method="POST" action="{{ route('piscicola.calidad_agua.store') }}">
      @csrf
      <div class="form-group">
        <label>Estanque *</label>
        <select name="estanque_id" id="calEstanqueId" class="form-control" required
                onchange="document.getElementById('nombreEstCalNew').textContent = this.options[this.selectedIndex].text">
          <option value="">Seleccionar...</option>
          @foreach($estanques as $e)
          <option value="{{ $e->id }}">{{ $e->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Hora</label>
          <input type="time" name="hora" class="form-control" value="{{ now()->format('H:i') }}">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div class="form-group">
          <label>O2 (mg/L)</label>
          <input type="number" name="oxigeno_mg_l" class="form-control" step="0.1" placeholder="5-8">
        </div>
        <div class="form-group">
          <label>pH</label>
          <input type="number" name="ph" class="form-control" step="0.1" placeholder="6.5-8.5">
        </div>
        <div class="form-group">
          <label>Temp (°C)</label>
          <input type="number" name="temperatura_c" class="form-control" step="0.1" placeholder="25-32">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Secchi (cm)</label>
          <input type="number" name="turbidez_cm" class="form-control" step="1" placeholder="25-45">
        </div>
        <div class="form-group">
          <label>NH3 (mg/L)</label>
          <input type="number" name="amoniaco_mg_l" class="form-control" step="0.001" placeholder="max 0.05">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Color, olor, floraciones...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevaCalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
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

function openCalidadEst(estId, nombre) {
  document.getElementById('calEstanqueId').value = estId;
  document.getElementById('nombreEstCalNew').textContent = nombre;
  openModal('modalNuevaCalidad');
}
</script>
@endpush