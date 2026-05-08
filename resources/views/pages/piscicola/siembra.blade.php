@extends('layouts.app')
@section('title','Siembra Piscicola')
@section('page_title','Siembra y Muestreos')
@section('back_url', route('piscicola.estanques'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
@endpush

@section('content')

{{-- BOTON NUEVA SIEMBRA --}}
<div class="section-card" style="padding:12px 14px;">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <div style="font-size:.85rem;color:#64748b;">
      {{ $estanques->count() }} estanque(s) disponibles
    </div>
    @if($estanques->count())
    <button onclick="openModal('modalNuevaSiembra')" class="btn btn-sm btn-primary">
      + Nueva siembra
    </button>
    @endif
  </div>
</div>

{{-- SIEMBRAS ACTIVAS --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Siembras activas</div>

  {{-- ← CORREGIDO: filtrar por activo=1 en lugar de estado='activa' --}}
  @forelse($siembras->where('activo', 1) as $s)
  @php
    $cantActual    = (int)($s->cantidad_actual ?? $s->cantidad_alevinos);
    $biomasaActual = round((float)($s->biomasa_actual_kg ?? 0), 1);
    $pesoActualG   = round((float)($s->peso_promedio_actual_g ?? 0), 0);
    $tieneMuestreos = $s->muestreos->count() > 0;
    $ultimoMuest    = $tieneMuestreos ? $s->muestreos->last() : null;
    $tasaCrecActual = $ultimoMuest ? $ultimoMuest->ganancia_diaria_g : null;
    $fechaSiembraFmt = \Carbon\Carbon::parse($s->fecha_siembra)->format('d/m/Y');
    $mortTotal       = (int)($s->mortalidad_total ?? 0);
    $sobrev          = round((float)($s->sobrevivencia ?? $s->sobrevivencia_pct ?? 100), 1);
  @endphp
  <div class="estanque-card" style="margin-bottom:10px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="estanque-nombre">{{ $s->nombre_estanque }}</div>
        <div class="estanque-sub">
          {{ $s->especie }} · Sembrado {{ $fechaSiembraFmt }} · Dia {{ $s->dias_cultivo ?? 0 }}
        </div>
        @if(!empty($s->proveedor))
        <div style="font-size:.72rem;color:#94a3b8;">Proveedor: {{ $s->proveedor }}</div>
        @endif
      </div>
      <div style="text-align:right;">
        <div style="font-size:1.1rem;font-weight:800;color:var(--pisc-azul);">{{ $biomasaActual }} kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">biomasa est.</div>
      </div>
    </div>

    {{-- Stats de la siembra --}}
    <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.75rem;color:#64748b;margin:8px 0;">
      <span>Sembrados: <strong>{{ number_format($s->cantidad_alevinos) }}</strong></span>
      <span>Actuales: <strong>{{ number_format($cantActual) }}</strong></span>
      @if($mortTotal > 0)
      <span style="color:#dc2626;">Muertes: <strong>{{ $mortTotal }}</strong></span>
      @endif
      <span>Sobrev: <strong style="color:{{ $sobrev >= 90 ? '#16a34a' : ($sobrev >= 75 ? '#d97706' : '#dc2626') }};">
        {{ $sobrev }}%
      </strong></span>
      @if($pesoActualG > 0)
      <span>Peso prom: <strong>{{ $pesoActualG }} g</strong></span>
      @endif
      @if($tasaCrecActual !== null)
      <span class="muestreo-crec {{ $tasaCrecActual >= 1.5 ? 'crec-ok' : 'crec-lento' }}">
        Crec: {{ $tasaCrecActual }} g/dia
      </span>
      @endif
      @if(!empty($s->alimento_acumulado_kg) && $s->alimento_acumulado_kg > 0)
      <span>Alimento total: <strong>{{ $s->alimento_acumulado_kg }} kg</strong></span>
      @endif
    </div>

    {{-- Historial de muestreos --}}
    @if($tieneMuestreos)
    <div style="border-top:1px solid #e2e8f0;padding-top:8px;margin-top:4px;">
      <div style="font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:4px;">
        Muestreos ({{ $s->muestreos->count() }})
      </div>
      @foreach($s->muestreos->take(4) as $m)
      @php
        $mFecha = \Carbon\Carbon::parse($m->fecha)->format('d/m');
        $mCrecColor = ($m->ganancia_diaria_g !== null && $m->ganancia_diaria_g >= 1.5) ? '#16a34a' : '#d97706';
      @endphp
      <div class="muestreo-row">
        <span style="color:#64748b;min-width:45px;">{{ $mFecha }}</span>
        <span class="muestreo-peso">{{ $m->peso_promedio_g }} g</span>
        @if(!empty($m->biomasa_estimada_kg))
        <span style="font-size:.75rem;color:#64748b;">{{ round($m->biomasa_estimada_kg,1) }} kg biomasa</span>
        @endif
        @if($m->ganancia_diaria_g !== null)
        <span style="font-size:.72rem;color:{{ $mCrecColor }};">
          +{{ $m->ganancia_diaria_g }} g/dia
        </span>
        @endif
      </div>
      @endforeach
    </div>
    @endif

    {{-- Acciones --}}
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
      <button onclick="openNuevoMuestreo({{ $s->id }},{{ $s->estanque_id }},'{{ addslashes($s->nombre_estanque) }}')"
              class="btn btn-sm btn-primary" style="font-size:.75rem;">
        + Muestreo
      </button>
      <button onclick="openMortalidadModal({{ $s->id }},{{ $s->estanque_id }},'{{ addslashes($s->nombre_estanque) }}')"
              class="btn btn-sm btn-ghost" style="font-size:.75rem;color:#dc2626;">
        + Mortalidad
      </button>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#64748b;">
    <p>No hay siembras activas.</p>
    @if($estanques->count())
    <button onclick="openModal('modalNuevaSiembra')" class="btn btn-sm btn-primary" style="margin-top:8px;">
      + Registrar primera siembra
    </button>
    @else
    <p style="font-size:.82rem;margin-top:6px;">Primero registra un estanque en el Dashboard.</p>
    @endif
  </div>
  @endforelse
</div>

{{-- SIEMBRAS COSECHADAS --}}
{{-- ← CORREGIDO: filtrar por activo=0 en lugar de estado='cosechada' --}}
@if($siembras->where('activo', 0)->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">
    Siembras cosechadas ({{ $siembras->where('activo', 0)->count() }})
  </div>
  @foreach($siembras->where('activo', 0) as $s)
  @php
    $fechaSiembraFmt2 = \Carbon\Carbon::parse($s->fecha_siembra)->format('d/m/Y');
    $fechaCosechaFmt2 = !empty($s->fecha_cosecha) ? \Carbon\Carbon::parse($s->fecha_cosecha)->format('d/m/Y') : '';
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:700;">{{ $s->nombre_estanque }}</div>
      <div style="font-size:.72rem;color:#64748b;">
        {{ $s->especie }} · Sembrada {{ $fechaSiembraFmt2 }}
        @if($fechaCosechaFmt2) · Cosechada {{ $fechaCosechaFmt2 }}@endif
        · {{ $s->dias_cultivo ?? 0 }} dias
      </div>
    </div>
    <div style="text-align:right;">
      <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:700;">
        Cosechada
      </span>
    </div>
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>

{{-- MODAL NUEVA SIEMBRA --}}
<div id="modalNuevaSiembra" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Nueva siembra de alevinos</div>
    <form method="POST" action="{{ route('piscicola.siembra.store') }}">
      @csrf
      <div class="form-group">
        <label>Estanque *</label>
        <select name="estanque_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          @foreach($estanques as $e)
          <option value="{{ $e->id }}">{{ $e->nombre }} ({{ $e->especie_cultivada }})</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha de siembra *</label>
          <input type="date" name="fecha_siembra" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Especie *</label>
          <input type="text" name="especie" class="form-control" required placeholder="Cachama, Tilapia...">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Cantidad de alevinos *</label>
          <input type="number" name="cantidad_alevinos" class="form-control" min="1" required placeholder="Ej: 3000">
        </div>
        <div class="form-group">
          {{-- ← CORREGIDO: nombre cambiado de peso_inicial_g a peso_promedio_inicial_g --}}
          <label>Peso inicial promedio (g) *</label>
          <input type="number" name="peso_promedio_inicial_g" class="form-control"
                 step="0.001" min="0.001" required placeholder="Ej: 2.5">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Proveedor</label>
          <input type="text" name="proveedor" class="form-control" placeholder="Estacion piscicola, vivero...">
        </div>
        <div class="form-group">
          <label>Costo alevinos (COP)</label>
          <input type="number" name="costo_alevinos" class="form-control" step="100" min="0" placeholder="Total">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Procedencia, certificacion sanitaria..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevaSiembra')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar siembra</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL MUESTREO --}}
<div id="modalNuevoMuestreo" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Muestreo de biomasa — <span id="nomEstMuest2"></span></div>
    <form method="POST" action="{{ route('piscicola.muestreo.store') }}">
      @csrf
      <input type="hidden" name="siembra_id" id="siembraIdMuest2">
      <input type="hidden" name="estanque_id" id="estanqueIdMuest2">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Peces pesados *</label>
          <input type="number" name="peces_muestreados" class="form-control" min="1" value="30" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div class="form-group">
          <label>Peso promedio (g) *</label>
          <input type="number" name="peso_promedio_g" class="form-control" step="0.1" min="0" required>
        </div>
        <div class="form-group">
          <label>Peso minimo (g)</label>
          <input type="number" name="peso_minimo_g" class="form-control" step="0.1" min="0">
        </div>
        <div class="form-group">
          <label>Peso maximo (g)</label>
          <input type="number" name="peso_maximo_g" class="form-control" step="0.1" min="0">
        </div>
      </div>
      <div class="form-group">
        <label>Cantidad total estimada en el estanque</label>
        {{-- ← CORREGIDO: nombre alineado con el controlador (cantidad_estimada) --}}
        <input type="number" name="cantidad_estimada" class="form-control" min="0">
        <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
          Si no la ingresas, se usa la cantidad actual de la siembra.
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control"
               placeholder="Uniformidad, condicion corporal...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevoMuestreo')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar muestreo</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL MORTALIDAD --}}
<div id="modalMortalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar mortalidad — <span id="nomEstMort"></span></div>
    <form method="POST" action="{{ route('piscicola.mortalidad.store') }}">
      @csrf
      <input type="hidden" name="siembra_id" id="siembraIdMort">
      <input type="hidden" name="estanque_id" id="estanqueIdMort">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Cantidad de peces *</label>
          <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
        </div>
      </div>
      <div class="form-group">
        <label>Causa *</label>
        <select name="causa" class="form-control" required>
          <option value="calidad_agua">Calidad del agua</option>
          <option value="enfermedad">Enfermedad</option>
          <option value="estres">Estres (transporte, manipulacion)</option>
          <option value="depredador">Depredador</option>
          <option value="manipulacion">Manipulacion</option>
          <option value="causa_desconocida">Causa desconocida</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Descripcion</label>
        <input type="text" name="descripcion" class="form-control"
               placeholder="Sintomas, color, comportamiento observado...">
      </div>
      <div style="background:#fef2f2;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#991b1b;margin-bottom:10px;">
        La cantidad de peces activos en la siembra se actualiza automaticamente.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalMortalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
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

function openNuevoMuestreo(siembraId, estanqueId, nombre) {
  document.getElementById('siembraIdMuest2').value   = siembraId;
  document.getElementById('estanqueIdMuest2').value  = estanqueId;
  document.getElementById('nomEstMuest2').textContent = nombre;
  openModal('modalNuevoMuestreo');
}
function openMortalidadModal(siembraId, estanqueId, nombre) {
  document.getElementById('siembraIdMort').value     = siembraId;
  document.getElementById('estanqueIdMort').value    = estanqueId;
  document.getElementById('nomEstMort').textContent  = nombre;
  openModal('modalMortalidad');
}
</script>
@endpush