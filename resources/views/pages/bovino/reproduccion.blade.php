@extends('layouts.app')
@section('title','Reproducción')
@section('page_title','🐮 Reproducción Bovina')
@section('back_url', route('bovino.hato'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/bovino.css') }}">
@endpush

@section('content')

{{-- LISTA DE HEMBRAS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐄 Estado reproductivo del hato</div>
    <button onclick="openModal('modalServicio')" class="btn btn-sm btn-primary">＋ Servicio</button>
  </div>

  @if($hembras->isEmpty())
    <p style="text-align:center;color:#64748b;padding:20px;">No hay hembras bovinas activas registradas.</p>
  @else
  @foreach($hembras as $h)
  @php
    if ($h->fecha_parto_real)     { $estado='Parida';   $color='#3b82f6'; $bg='#eff6ff'; }
    elseif ($h->resultado_diagnostico === 'positivo') { $estado='Preñada'; $color='#22c55e'; $bg='#f0fdf4'; }
    elseif ($h->resultado_diagnostico === 'negativo') { $estado='Vacía';   $color='#ef4444'; $bg='#fef2f2'; }
    elseif ($h->fecha_servicio)   { $estado='Servida';  $color='#f59e0b'; $bg='#fffbeb'; }
    else                          { $estado='Sin servicio'; $color='#94a3b8'; $bg='#f8fafc'; }
    $diasLact = $h->en_produccion ? \Carbon\Carbon::parse(\App\Models\AnimalLactancia::where('animal_id',$h->id)->whereNull('fecha_secado')->value('fecha_inicio') ?? now())->diffInDays(now()) : null;
  @endphp
  <div class="repr-row">
    <div style="flex:1;">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <strong>{{ $h->nombre_lote }}</strong>
        @if($h->raza)<span style="font-size:.72rem;color:#94a3b8;">{{ $h->raza }}</span>@endif
        <span class="repr-badge" style="background:{{ $bg }};color:{{ $color }};">{{ $estado }}</span>
        @if($h->en_produccion)
          <span class="repr-badge" style="background:#f0fdf4;color:#15803d;">🥛 Producción</span>
        @endif
      </div>
      @if($h->fecha_servicio)
      <div style="font-size:.78rem;color:#64748b;margin-top:3px;">
        Último servicio: {{ \Carbon\Carbon::parse($h->fecha_servicio)->format('d/m/Y') }}
        ({{ $h->tipo_servicio === 'inseminacion_artificial' ? 'IA' : 'MN' }})
        @if($h->fecha_probable_parto && !$h->fecha_parto_real)
          · Parto probable: <strong>{{ \Carbon\Carbon::parse($h->fecha_probable_parto)->format('d/m/Y') }}</strong>
          @php $dp = \Carbon\Carbon::parse($h->fecha_probable_parto)->diffInDays(now(), false); @endphp
          @if($dp <= 15)
            <span style="color:#ef4444;">(¡en {{ abs($dp) }} días!)</span>
          @endif
        @endif
      </div>
      @endif
    </div>
    <div style="display:flex;gap:6px;flex-shrink:0;flex-wrap:wrap;">
      @if($h->repr_id && $h->resultado_diagnostico === 'pendiente')
      <button onclick="openDiagnostico({{ $h->repr_id }},'{{ $h->nombre_lote }}')"
              class="btn btn-sm btn-secondary">🔬 Diagnóstico</button>
      @endif
      @if($h->fecha_probable_parto && !$h->fecha_parto_real && $h->resultado_diagnostico === 'positivo')
      <button onclick="openParto({{ $h->repr_id }},'{{ addslashes($h->nombre_lote) }}','{{ addslashes($h->macho_descripcion ?? '') }}')" 
              class="btn btn-sm btn-secondary">🍼 Parto</button>
      @endif
      <button onclick="abrirServicio({{ $h->id }},'{{ $h->nombre_lote }}')"
              class="btn btn-sm btn-ghost">＋ Servicio</button>
    </div>
  </div>
  @endforeach
  @endif
</div>

{{-- PARTOS RECIENTES --}}
@if($partosRecientes->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🍼 Partos recientes (90 días)</div>
  @foreach($partosRecientes as $p)
  <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.85rem;">
    <div>
      <strong>{{ $p->vaca }}</strong> —
      {{ $p->num_crias_vivas ?? 0 }}/{{ $p->num_crias_nacidas ?? 0 }} crías vivas
      @if($p->sexo_cria)<span style="color:#64748b;">· {{ $p->sexo_cria }}</span>@endif
      @if($p->peso_cria_kg)<span style="color:#64748b;">· {{ $p->peso_cria_kg }} kg</span>@endif
    </div>
    <span style="color:#64748b;">{{ \Carbon\Carbon::parse($p->fecha_parto_real)->format('d/m/Y') }}</span>
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>

{{-- ══ MODALES ══════════════════════════════════════════════════════ --}}

{{-- Modal Nuevo Servicio --}}
<div id="modalServicio" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🐮 Registrar servicio</div>
    <form method="POST" action="{{ route('bovino.reproduccion.servicio') }}">
      @csrf
      <div class="form-group">
        <label>Vaca *</label>
        <select name="animal_id" id="selectVacaServicio" class="form-control" required>
          <option value="">Seleccionar...</option>
          @foreach($hembras as $h)
          <option value="{{ $h->id }}">{{ $h->nombre_lote }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de servicio *</label>
          <select name="tipo_servicio" class="form-control" required>
            <option value="monta_natural">🐂 Monta natural</option>
            <option value="inseminacion_artificial">💉 Inseminación IA</option>
            <option value="monta_controlada">🔒 Monta controlada</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha del servicio *</label>
          <input type="date" name="fecha_servicio" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
      </div>
      {{-- Campo dinámico según tipo_servicio --}}
      <div class="form-group" id="wrapToroSelect">
        <label>Toro (de tu hato)</label>
        <select name="macho_descripcion" id="selectToro" class="form-control"
                onchange="syncToroTexto(this)">
          <option value="">— Seleccionar toro registrado —</option>
          @foreach($toros as $t)
          <option value="{{ $t->nombre_lote }}{{ $t->raza ? ' ('.$t->raza.')' : '' }}">
            🐂 {{ $t->nombre_lote }}{{ $t->raza ? ' · '.$t->raza : '' }}
          </option>
          @endforeach
          @if($toros->isEmpty())
          <option value="" disabled>Sin toros registrados — categoriza uno en Animales</option>
          @endif
          <option value="__manual__">✏️ Ingresar manualmente...</option>
        </select>
      </div>
      <div class="form-group" id="wrapToroManual" style="display:none;">
        <label id="labelMacho">Nombre del toro / Código de semen</label>
        <input type="text" name="macho_descripcion_manual" id="inputToroManual"
               class="form-control" placeholder="Ej: Toro Simón / Código AX-123">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>
      <div style="font-size:.78rem;color:#64748b;background:#fffbeb;padding:8px 10px;border-radius:8px;margin-bottom:12px;">
        📅 Se genera automáticamente una tarea de <strong>diagnóstico de preñez a los 35 días</strong>.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalServicio')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Diagnóstico de Preñez --}}
<div id="modalDiagnostico" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🔬 Diagnóstico de preñez — <span id="dxNombreVaca"></span></div>
    <form method="POST" id="formDiagnostico" action="">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Resultado *</label>
          <select name="resultado_diagnostico" class="form-control" required>
            <option value="positivo">✅ Positivo (preñada)</option>
            <option value="negativo">❌ Negativo (vacía)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha diagnóstico *</label>
          <input type="date" name="fecha_diagnostico_prenez" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalDiagnostico')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Registrar Parto --}}
<div id="modalParto" class="modal-overlay" style="display:none;">
  <div class="modal-sheet" style="max-width:520px;">
    <div class="modal-handle"></div>
    <div class="modal-title">🍼 Registrar parto — <span id="partoNombreVaca"></span></div>
    <form method="POST" action="{{ route('bovino.reproduccion.parto') }}">
      @csrf
      <input type="hidden" name="repr_id" id="partoReprId">

      {{-- Datos del parto --}}
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha del parto *</label>
          <input type="date" name="fecha_parto_real" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Crías nacidas *</label>
          <input type="number" name="num_crias_nacidas" id="inputCriasNacidas"
                 class="form-control" min="0" value="1" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Crías vivas *</label>
          <input type="number" name="num_crias_vivas" id="inputCriasVivas"
                 class="form-control" min="0" value="1" required>
        </div>
        <div class="form-group">
          <label>Sexo</label>
          <select name="sexo_cria" id="selectSexoCria" class="form-control">
            <option value="">—</option>
            <option value="macho">🐂 Macho</option>
            <option value="hembra">🐄 Hembra</option>
            <option value="mixto">Mixto</option>
          </select>
        </div>
        <div class="form-group">
          <label>Peso cría (kg)</label>
          <input type="number" name="peso_cria_kg" class="form-control" step="0.1" min="0" placeholder="Ej: 35">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones del parto</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Parto normal, asistido, gemelar, distocia..."></textarea>
      </div>

      {{-- ── REGISTRO GENEALÓGICO DE LA CRÍA ──────────────────────── --}}
      <div style="border:1.5px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:12px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-weight:700;font-size:.88rem;margin-bottom:0;">
          <input type="checkbox" name="registrar_cria" value="1" id="chkRegistrarCria"
                 onchange="toggleRegistroCria(this.checked)" style="width:16px;height:16px;">
          🐄 Registrar cría(s) como nuevo(s) animal(es)
        </label>
        <div style="font-size:.74rem;color:#64748b;margin:4px 0 0 24px;">
          Se añadirá automáticamente al módulo de Animales con genealogía (madre y padre) vinculada.
        </div>

        <div id="wrapRegistroCria" style="display:none;margin-top:10px;">
          <div class="form-group">
            <label>Nombre de la(s) cría(s)</label>
            <input type="text" name="nombre_cria" class="form-control" id="inputNombreCria"
                   placeholder="Ej: Ternera Blanquita">
            <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
              Si hay más de una cría viva, se añadirá #1, #2... automáticamente.
            </div>
          </div>

          {{-- Resumen genealógico --}}
          <div style="background:#f8fafc;border-radius:8px;padding:10px;font-size:.78rem;color:#475569;">
            <div style="font-weight:700;margin-bottom:6px;">🧬 Genealogía que se asignará:</div>
            <div>🐄 <strong>Madre:</strong> <span id="genealogiaMadre">—</span></div>
            <div>🐂 <strong>Padre:</strong> <span id="genealogiaPadre">—</span></div>
            <div style="margin-top:4px;color:#94a3b8;font-style:italic;">
              La raza se hereda de la madre. Categoría asignada según el sexo.
            </div>
          </div>
        </div>
      </div>

      <div style="font-size:.78rem;color:#64748b;background:#f0fdf4;padding:8px 10px;border-radius:8px;margin-bottom:12px;">
        🥛 Se iniciará automáticamente una nueva <strong>lactancia</strong> para esta vaca.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalParto')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar parto</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id){ var m=document.getElementById(id); m.style.display='flex'; m.classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ var m=document.getElementById(id); m.style.display='none'; m.classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

function abrirServicio(animalId, nombre) {
  document.getElementById('selectVacaServicio').value = animalId;
  openModal('modalServicio');
}

// Cambiar el campo macho según tipo_servicio
document.addEventListener('DOMContentLoaded', function() {
  var selTipo = document.querySelector('[name="tipo_servicio"]');
  if (selTipo) selTipo.addEventListener('change', actualizarCampoMacho);
});
function actualizarCampoMacho() {
  var tipo   = document.querySelector('[name="tipo_servicio"]').value;
  var label  = document.getElementById('labelMacho');
  var wToro  = document.getElementById('wrapToroSelect');
  var wManual= document.getElementById('wrapToroManual');
  if (tipo === 'inseminacion_artificial') {
    if (label)  label.textContent = 'Código del semen / pajilla';
    if (wToro)  wToro.style.display  = 'none';
    if (wManual)wManual.style.display = 'block';
    var sel = document.getElementById('selectToro');
    if (sel) sel.value = '';
  } else {
    if (wToro)  wToro.style.display  = 'block';
    if (wManual){ wManual.style.display = 'none'; }
    if (label)  label.textContent = 'Nombre del toro / Código de semen';
  }
}
function syncToroTexto(sel) {
  var v = sel.value;
  var wManual = document.getElementById('wrapToroManual');
  if (v === '__manual__') {
    if (wManual) wManual.style.display = 'block';
    sel.value = '';
  } else {
    if (wManual) wManual.style.display = 'none';
  }
}
function openDiagnostico(reprId, nombre) {
  document.getElementById('dxNombreVaca').textContent = nombre;
  document.getElementById('formDiagnostico').action = '/bovino/reproduccion/'+reprId+'/prenez';
  openModal('modalDiagnostico');
}
function openParto(reprId, nombre, padre) {
  document.getElementById('partoNombreVaca').textContent = nombre;
  document.getElementById('partoReprId').value = reprId;
  // Pre-llenar genealogía
  document.getElementById('genealogiaMadre').textContent = nombre;
  document.getElementById('genealogiaPadre').textContent = padre || 'No registrado';
  document.getElementById('inputNombreCria').placeholder = 'Ej: Cría de ' + nombre;
  openModal('modalParto');
}

function toggleRegistroCria(checked) {
  document.getElementById('wrapRegistroCria').style.display = checked ? 'block' : 'none';
}
</script>
@endpush