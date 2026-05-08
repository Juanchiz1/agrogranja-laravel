@extends('layouts.app')
@section('title','Piscicola')
@section('page_title','Piscicola')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
@endpush

@section('content')

{{-- STATS --}}
<div class="pisc-stats">
  <div class="pisc-stat">
    <div class="pisc-stat-ico">&#128031;</div>
    <div class="pisc-stat-val">{{ $totalEstanques }}</div>
    <div class="pisc-stat-lbl">Estanques</div>
  </div>
  <div class="pisc-stat verde">
    <div class="pisc-stat-ico">&#9989;</div>
    <div class="pisc-stat-val">{{ $totalActivos }}</div>
    <div class="pisc-stat-lbl">En produccion</div>
  </div>
  <div class="pisc-stat">
    <div class="pisc-stat-ico">&#9618;</div>
    <div class="pisc-stat-val">{{ number_format($totalAreaM2, 0) }} m2</div>
    <div class="pisc-stat-lbl">Area activa</div>
  </div>
  <div class="pisc-stat naranja">
    <div class="pisc-stat-ico">&#9878;</div>
    <div class="pisc-stat-val">{{ number_format($totalBiomasa, 1) }} kg</div>
    <div class="pisc-stat-lbl">Biomasa total</div>
  </div>
</div>

{{-- ALERTAS CALIDAD AGUA --}}
@if($alertasAgua->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;color:#dc2626;">
    Alertas de calidad del agua ({{ $alertasAgua->count() }})
  </div>
  @foreach($alertasAgua->take(3) as $a)
  <div class="alerta-pisc critica">
    <span>!</span>
    <div>
      <strong>{{ $a->nombre_estanque ?? 'Estanque' }}</strong>
      — {{ \Carbon\Carbon::parse($a->fecha)->format('d/m/Y') }}<br>
      <span style="font-size:.74rem;">
        @if(!empty($a->oxigeno_mgl))Oxigeno: {{ $a->oxigeno_mgl }} mg/L @endif
        @if(!empty($a->ph)) — pH: {{ $a->ph }} @endif
        @if(!empty($a->temperatura_c)) — Temp: {{ $a->temperatura_c }} C @endif
      </span>
    </div>
    <a href="{{ route('piscicola.calidad_agua') }}"
       style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Ver →</a>
  </div>
  @endforeach
</div>
@endif

{{-- MENU --}}
<div class="section-card">
  <div class="pisc-menu-grid">
    <a href="{{ route('piscicola.siembra') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#127793;</div>
      <div class="pisc-menu-lbl">Siembras</div>
      <div class="pisc-menu-sub">Alevinos</div>
    </a>
    <a href="{{ route('piscicola.alimentacion') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#127860;</div>
      <div class="pisc-menu-lbl">Alimentacion</div>
      <div class="pisc-menu-sub">Diaria</div>
    </a>
    <a href="{{ route('piscicola.muestreo') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#9878;</div>
      <div class="pisc-menu-lbl">Muestreos</div>
      <div class="pisc-menu-sub">Biomasa</div>
    </a>
    <a href="{{ route('piscicola.calidad_agua') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#128167;</div>
      <div class="pisc-menu-lbl">Calidad Agua</div>
      <div class="pisc-menu-sub">O2, pH, Temp</div>
    </a>
    <a href="{{ route('piscicola.cosecha') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#128175;</div>
      <div class="pisc-menu-lbl">Cosecha</div>
      <div class="pisc-menu-sub">Cierre ciclo</div>
    </a>
    <a href="{{ route('piscicola.reportes') }}" class="pisc-menu-card">
      <div class="pisc-menu-ico">&#128200;</div>
      <div class="pisc-menu-lbl">Reportes</div>
      <div class="pisc-menu-sub">Analisis</div>
    </a>
  </div>
</div>

{{-- ESTANQUES --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">Estanques ({{ $totalEstanques }})</div>
    <button onclick="openModal('modalNuevoEstanque')" class="btn btn-sm btn-primary">+ Nuevo</button>
  </div>

  @forelse($estanques as $est)
  @php
    $estadoClass   = $est->estado ?? 'vacio';
    $siembra       = $est->siembra_activa ?? null;
    $tieneSiembra  = !is_null($siembra);
    $pctBiomasa    = 0;
    $biomasaActKg  = 0;
    $biomasaInicKg = 0;

    if ($tieneSiembra) {
        $biomasaActKg  = (float)($siembra->biomasa_actual_kg ?? $siembra->biomasa_inicial_kg ?? 0);
        $biomasaInicKg = (float)($siembra->biomasa_inicial_kg ?? 0);
        if ($biomasaInicKg > 0) {
            $pctBiomasa = min(300, round(($biomasaActKg / $biomasaInicKg) * 100));
        }
    }

    $tieneAlerta   = !empty($est->ultima_agua) && !empty($est->ultima_agua->alerta);
    $diasCultivo   = $est->dias_cultivo ?? 0;
    $sobrevivencia = $est->sobrevivencia ?? null;   // ← CORREGIDO: ?? null en lugar de asignación directa
    $mortalidadAcum = (int)($est->mortalidad_acum ?? 0);
  @endphp

  <div class="estanque-card {{ $estadoClass }}" style="margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="estanque-nombre">{{ $est->nombre }}</div>
        <div class="estanque-sub">
          {{ $est->especie_cultivada ?? '—' }} &middot;
          {{ $est->area_m2 }} m2
          @if(!empty($est->profundidad_m))
          &middot; {{ $est->profundidad_m }}m prof.
          @endif
          @if(!empty($est->ubicacion))
          &middot; {{ $est->ubicacion }}
          @endif
        </div>
      </div>
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
        <span class="estado-pisc {{ $estadoClass }}">{{ $est->estado }}</span>
        @if($tieneAlerta)
        <span style="font-size:.7rem;color:#dc2626;font-weight:700;">Alerta agua</span>
        @endif
      </div>
    </div>

    @if($tieneSiembra)
    <div style="margin-top:8px;border-top:1px solid #e2e8f0;padding-top:8px;">
      <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:3px;">
        <span style="color:#64748b;">Dia {{ $diasCultivo }} &mdash; {{ $siembra->especie ?? '—' }}</span>
        <span style="font-weight:700;color:#0ea5e9;">{{ $biomasaActKg }} kg biomasa</span>
      </div>
      <div class="biomasa-barra-wrap">
        <div class="biomasa-barra-fill" style="width:{{ min(100,$pctBiomasa) }}%;"></div>
      </div>
      <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-top:2px;">
        <span>{{ number_format($siembra->cantidad_actual ?? $siembra->cantidad_alevinos ?? 0) }} peces</span>
        @if($sobrevivencia !== null)
        <span>Sobrevivencia: <strong style="color:{{ $sobrevivencia >= 90 ? '#15803d' : ($sobrevivencia >= 75 ? '#b45309' : '#dc2626') }};">{{ $sobrevivencia }}%</strong></span>
        @endif
        @if(!empty($siembra->peso_promedio_actual_g))
        <span>{{ $siembra->peso_promedio_actual_g }}g/pez</span>
        @endif
      </div>
    </div>
    @else
    <div style="margin-top:6px;">
      <button onclick="openSiembra({{ $est->id }},'{{ addslashes($est->nombre) }}','{{ addslashes($est->especie_cultivada ?? '') }}')"
              class="btn btn-sm btn-secondary" style="font-size:.74rem;">
        + Sembrar alevinos
      </button>
    </div>
    @endif

    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
      <button onclick="openEditEstanque({{ $est->id }},'{{ addslashes($est->nombre) }}',{{ $est->area_m2 }},'{{ addslashes($est->especie_cultivada ?? '') }}','{{ $est->estado }}')"
              class="btn btn-sm btn-ghost" style="font-size:.72rem;">Editar</button>
      @if($tieneSiembra)
      <a href="{{ route('piscicola.alimentacion') }}" class="btn btn-sm btn-ghost" style="font-size:.72rem;">Alimentar</a>
      <a href="{{ route('piscicola.calidad_agua') }}" class="btn btn-sm btn-ghost" style="font-size:.72rem;">Agua</a>
      <a href="{{ route('piscicola.cosecha') }}" class="btn btn-sm btn-ghost" style="font-size:.72rem;">Cosechar</a>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">&#128031;</div>
    <p style="margin-bottom:12px;">No hay estanques registrados.</p>
    <button onclick="openModal('modalNuevoEstanque')" class="btn btn-sm btn-primary">
      + Registrar primer estanque
    </button>
  </div>
  @endforelse
</div>

<div style="margin-bottom:80px;"></div>

{{-- MODAL NUEVO ESTANQUE --}}
<div id="modalNuevoEstanque" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Nuevo estanque</div>
    <form method="POST" action="{{ route('piscicola.estanques.store') }}">
      @csrf
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Estanque 1, Piscina Norte">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo</label>
          <select name="tipo" class="form-control">
            <option value="tierra">Tierra</option>
            <option value="plastico">Plastico</option>
            <option value="concreto">Concreto</option>
            <option value="geomembrana">Geomembrana</option>
            <option value="jaula_flotante">Jaula flotante</option>
          </select>
        </div>
        <div class="form-group">
          <label>Especie a cultivar *</label>
          <select name="especie_cultivada" class="form-control" required>
            <option value="Cachama">Cachama</option>
            <option value="Tilapia">Tilapia</option>
            <option value="Bocachico">Bocachico</option>
            <option value="Trucha">Trucha</option>
            <option value="Carpa">Carpa</option>
            <option value="Mojarra">Mojarra</option>
            <option value="Yamú">Yamu</option>
            <option value="Bagre">Bagre</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Area (m2) *</label>
          <input type="number" name="area_m2" class="form-control" step="0.1" min="1" required
                 placeholder="Ej: 200" oninput="calcVolumen()">
        </div>
        <div class="form-group">
          <label>Profundidad (m)</label>
          <input type="number" name="profundidad_m" class="form-control" id="profInput"
                 step="0.1" min="0.1" placeholder="Ej: 1.2" oninput="calcVolumen()">
        </div>
      </div>
      <div style="background:#f0f9ff;border-radius:8px;padding:8px 12px;font-size:.78rem;color:#0369a1;margin-bottom:8px;">
        Volumen estimado: <strong id="volEstimado">—</strong> m3
      </div>
      <div class="form-group">
        <label>Ubicacion</label>
        <input type="text" name="ubicacion" class="form-control" placeholder="Potrero norte, lote 3...">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2"></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevoEstanque')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL EDITAR ESTANQUE --}}
<div id="modalEditEstanque" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Editar estanque — <span id="nombreEstEdit"></span></div>
    <form id="formEditEstanque" method="POST" action="">
      @csrf
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" id="editNombre" class="form-control" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Area (m2) *</label>
          <input type="number" name="area_m2" id="editArea" class="form-control" step="0.1" min="1" required>
        </div>
        <div class="form-group">
          <label>Profundidad (m)</label>
          <input type="number" name="profundidad_m" id="editProf" class="form-control" step="0.1" min="0.1">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Especie *</label>
          <select name="especie_cultivada" id="editEspecie" class="form-control">
            <option value="Cachama">Cachama</option>
            <option value="Tilapia">Tilapia</option>
            <option value="Bocachico">Bocachico</option>
            <option value="Trucha">Trucha</option>
            <option value="Carpa">Carpa</option>
            <option value="Mojarra">Mojarra</option>
          </select>
        </div>
        <div class="form-group">
          <label>Estado</label>
          <select name="estado" id="editEstado" class="form-control">
            <option value="activo">Activo</option>
            <option value="vacio">Vacio</option>
            <option value="mantenimiento">Mantenimiento</option>
            <option value="cosechado">Cosechado</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalEditEstanque')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL SEMBRAR (acceso rapido desde la card) --}}
<div id="modalSiembraRapida" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Sembrar en <span id="nombreEstSiembra"></span></div>
    <form method="POST" action="{{ route('piscicola.siembra.store') }}">
      @csrf
      <input type="hidden" name="estanque_id" id="estanqueIdSiembra">
      <div class="form-group">
        <label>Especie</label>
        <input type="text" name="especie" id="especieSiembra" class="form-control" required>
      </div>
      <div class="form-group">
        <label>Fecha de siembra *</label>
        <input type="date" name="fecha_siembra" class="form-control" value="{{ now()->toDateString() }}" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Cantidad alevinos *</label>
          <input type="number" name="cantidad_alevinos" class="form-control" min="1" required placeholder="Ej: 1000">
        </div>
        <div class="form-group">
          <label>Peso promedio inicial (g) *</label>
          <input type="number" name="peso_promedio_inicial_g" class="form-control" step="0.001" min="0.1" required placeholder="Ej: 3.5">
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalSiembraRapida')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Sembrar</button>
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

function calcVolumen() {
  var area = parseFloat(document.querySelector('[name=area_m2]').value) || 0;
  var prof = parseFloat(document.getElementById('profInput').value) || 0;
  var el   = document.getElementById('volEstimado');
  el.textContent = (area > 0 && prof > 0) ? (Math.round(area * prof * 100)/100) + ' m3' : 'Ingrese area y profundidad';
}

function openEditEstanque(id, nombre, area, especie, estado) {
  document.getElementById('nombreEstEdit').textContent  = nombre;
  document.getElementById('formEditEstanque').action    = '/piscicola/estanques/' + id + '/update';
  document.getElementById('editNombre').value           = nombre;
  document.getElementById('editArea').value             = area;
  document.getElementById('editEspecie').value          = especie;
  document.getElementById('editEstado').value           = estado;
  openModal('modalEditEstanque');
}

function openSiembra(estId, nombre, especie) {
  document.getElementById('nombreEstSiembra').textContent = nombre;
  document.getElementById('estanqueIdSiembra').value      = estId;
  document.getElementById('especieSiembra').value         = especie;
  openModal('modalSiembraRapida');
}
</script>
@endpush