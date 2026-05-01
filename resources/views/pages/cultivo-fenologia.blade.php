@extends('layouts.app')
@section('title', 'Fenología · ' . $cultivo->nombre)
@section('page_title', ($cultivo->faseActual?->icono ?? '🌱') . ' Calendario Fenológico')
@section('back_url', route('cultivos.show', $cultivo->id))

@push('head')
<link rel="stylesheet" href="{{ asset('css/cultivos.css') }}">
<style>
/* ── Variables extra Fase 3 ─────────────────────────────────── */
:root {
  --fase-radius: 12px;
  --timeline-line: 3px;
}

/* ── Timeline fenológico ────────────────────────────────────── */
.fenologia-progress {
  display: flex;
  flex-direction: column;
  gap: 0;
  position: relative;
  padding: 8px 0;
}
.fenologia-progress::before {
  content: '';
  position: absolute;
  left: 22px;
  top: 0;
  bottom: 0;
  width: var(--timeline-line);
  background: var(--borde);
  z-index: 0;
}
.fase-item {
  display: flex;
  align-items: flex-start;
  gap: 14px;
  padding: 10px 0;
  position: relative;
  z-index: 1;
}
.fase-dot {
  width: 46px;
  height: 46px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  flex-shrink: 0;
  border: 3px solid white;
  box-shadow: 0 2px 6px rgba(0,0,0,.15);
  background: var(--borde);
  position: relative;
}
.fase-dot.activa {
  border-color: var(--verde);
  box-shadow: 0 0 0 4px rgba(76,175,80,.18), 0 2px 8px rgba(0,0,0,.15);
  animation: pulse-fase 2s infinite;
}
.fase-dot.completada { filter: brightness(.9); }
.fase-dot.pendiente  { opacity: .45; filter: grayscale(.6); }
@keyframes pulse-fase {
  0%,100% { box-shadow: 0 0 0 4px rgba(76,175,80,.18), 0 2px 8px rgba(0,0,0,.15); }
  50%      { box-shadow: 0 0 0 8px rgba(76,175,80,.08), 0 2px 8px rgba(0,0,0,.15); }
}
.fase-info { flex: 1; }
.fase-nombre { font-weight: 700; font-size: .95rem; }
.fase-duracion { font-size: .78rem; color: var(--text-secondary); margin-top: 2px; }
.fase-badge-activa {
  display: inline-block;
  background: var(--verde);
  color: white;
  font-size: .7rem;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 20px;
  margin-left: 6px;
  vertical-align: middle;
}

/* Barra de progreso general */
.progreso-bar-wrap {
  background: var(--bg-card);
  border: 1px solid var(--borde);
  border-radius: 50px;
  height: 18px;
  overflow: hidden;
  margin: 10px 0 4px;
}
.progreso-bar-fill {
  height: 100%;
  background: linear-gradient(90deg, #8BC34A, var(--verde));
  border-radius: 50px;
  transition: width .6s ease;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  padding-right: 8px;
  font-size: .7rem;
  font-weight: 700;
  color: white;
  min-width: 30px;
}

/* ── Eventos avanzados ──────────────────────────────────────── */
.evento-av-card {
  background: var(--bg-card);
  border: 1px solid var(--borde);
  border-radius: var(--fase-radius);
  padding: 14px;
  margin-bottom: 10px;
  position: relative;
}
.evento-av-card.alerta-carencia {
  border-left: 4px solid var(--rojo);
  background: #fff8f8;
}
.evento-av-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 8px;
  margin-bottom: 6px;
}
.evento-av-titulo { font-weight: 700; font-size: .9rem; flex: 1; }
.evento-av-fecha { font-size: .75rem; color: var(--text-secondary); white-space: nowrap; }
.evento-av-detalle { font-size: .82rem; color: var(--text-secondary); }
.badge-red    { background:#fde8e8; color:#c0392b; }
.badge-blue   { background:#e8f4fd; color:#1a6fa8; }
.badge-green  { background:var(--verde-bg); color:var(--verde-dark); }
.badge-orange { background:#fff3e0; color:#e65100; }
.badge-brown  { background:#efebe9; color:#5d4037; }
.badge-gray   { background:#f5f5f5; color:#616161; }
.alerta-carencia-tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  font-size: .75rem;
  background: #fde8e8;
  color: #c0392b;
  padding: 3px 8px;
  border-radius: 6px;
  margin-top: 4px;
  font-weight: 600;
}

/* ── Rendimiento ────────────────────────────────────────────── */
.rendimiento-card {
  background: var(--bg-card);
  border: 1px solid var(--borde);
  border-radius: var(--fase-radius);
  overflow: hidden;
}
.rendimiento-header {
  padding: 12px 16px;
  font-weight: 700;
  font-size: .9rem;
  background: var(--verde-bg);
  border-bottom: 1px solid var(--borde);
}
.rendimiento-body { padding: 14px 16px; }
.rendimiento-compare-bar {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 10px 0;
}
.rbar-label { font-size: .78rem; color: var(--text-secondary); width: 70px; flex-shrink: 0; }
.rbar-track {
  flex: 1;
  height: 12px;
  background: var(--borde);
  border-radius: 20px;
  overflow: visible;
  position: relative;
}
.rbar-real {
  height: 100%;
  border-radius: 20px;
  background: var(--verde);
  position: relative;
  transition: width .6s;
}
.rbar-regional-mark {
  position: absolute;
  top: -3px;
  height: 18px;
  width: 3px;
  background: #FF9800;
  border-radius: 2px;
}
.rbar-value { font-size: .78rem; font-weight: 700; width: 60px; text-align: right; }

/* ── Plan manejo ────────────────────────────────────────────── */
.plan-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 8px 0;
  border-bottom: 1px solid var(--borde);
}
.plan-item:last-child { border-bottom: none; }
.plan-icono {
  width: 32px; height: 32px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
  background: var(--verde-bg);
}
.plan-actividad { font-weight: 600; font-size: .875rem; }
.plan-producto  { font-size: .78rem; color: var(--text-secondary); margin-top: 2px; }

/* ── Modals ─────────────────────────────────────────────────── */
.modal-overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6) !important;
  z-index: 9999 !important;
  overflow-y: auto;
  padding: 20px;
  -webkit-backdrop-filter: none;
  backdrop-filter: none;
}
.modal-overlay.open {
  display: flex !important;
  align-items: flex-start;
  justify-content: center;
}
.modal-box {
  background: #ffffff !important;
  border-radius: 16px;
  padding: 20px;
  width: 100%;
  max-width: 500px;
  margin: auto;
  position: relative;
  z-index: 10000 !important;
  box-shadow: 0 8px 32px rgba(0,0,0,.25);
  opacity: 1 !important;
}
.modal-title { font-weight:800;font-size:1.05rem;margin-bottom:16px; }
.form-group { margin-bottom:14px; }
.form-group label { display:block;font-size:.82rem;font-weight:600;color:var(--text-secondary);margin-bottom:4px; }
.form-group input,.form-group select,.form-group textarea {
  width:100%;padding:9px 12px;border:1px solid #d1d5db;
  border-radius:8px;font-size:.9rem;background:#ffffff !important;color:#111827 !important;
  font-family:inherit;
  opacity: 1 !important;
}
.form-group textarea { resize:vertical;min-height:70px; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
.btn-full { width:100%; }
.tab-group { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:16px; }
.tab-btn { padding:6px 14px;border-radius:20px;border:1px solid var(--borde);
  background:var(--bg-page);font-size:.82rem;cursor:pointer;font-family:inherit; }
.tab-btn.active { background:var(--verde);color:white;border-color:var(--verde); }
.fields-group { display:none; }
.fields-group.active { display:block; }

/* ── Responsive ────────────────────────────────────────────── */
@media(min-width:600px){
  .fenologia-progress { flex-direction:row; flex-wrap:wrap; gap:8px; }
  .fenologia-progress::before { display:none; }
  .fase-item {
    flex-direction:column; align-items:center; text-align:center;
    width:calc(33% - 6px); padding:14px 8px;
    background:var(--bg-card); border:1px solid var(--borde);
    border-radius:var(--fase-radius);
  }
  .fase-item.activa-item { border-color:var(--verde); background:var(--verde-bg); }
}
</style>
@endpush

@section('content')
@php
  $em = ['Maíz'=>'🌽','Yuca'=>'🍠','Plátano'=>'🍌','Café'=>'☕','Hortalizas'=>'🥬'][$cultivo->tipo] ?? '🌿';
@endphp

{{-- ALERTAS DE CARENCIA ACTIVAS --}}
@if($alertasCarencia->count())
<div style="background:#fde8e8;border:1px solid #f5c6cb;border-radius:12px;padding:12px 14px;margin-bottom:14px;">
  <div style="font-weight:700;color:#c0392b;margin-bottom:6px;">⚠️ Alerta de período de carencia</div>
  @foreach($alertasCarencia as $alerta)
  <div style="font-size:.85rem;color:#721c24;margin-bottom:4px;">
    🧪 <strong>{{ $alerta->producto_nombre }}</strong> aplicado el {{ \Carbon\Carbon::parse($alerta->fecha)->format('d/m/Y') }}
    — No cosechar antes del <strong>{{ \Carbon\Carbon::parse($alerta->fecha_minima_cosecha)->format('d/m/Y') }}</strong>
    ({{ $alerta->periodo_carencia_dias }} días de carencia)
  </div>
  @endforeach
</div>
@endif

{{-- PROGRESO GENERAL --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">{{ $em }} {{ $cultivo->nombre }}</div>
    <span style="font-size:.82rem;color:var(--text-secondary);">{{ $porcentaje }}% completado</span>
  </div>
  <div class="progreso-bar-wrap">
    <div class="progreso-bar-fill" style="width:{{ $porcentaje }}%">{{ $porcentaje }}%</div>
  </div>
  @if($faseActual)
  <div style="font-size:.85rem;color:var(--text-secondary);margin-top:6px;">
    Fase actual: <strong>{{ $faseActual->icono }} {{ $faseActual->nombre }}</strong>
    @if($cultivo->fecha_cambio_fase)
      · desde {{ \Carbon\Carbon::parse($cultivo->fecha_cambio_fase)->format('d/m/Y') }}
      ({{ \Carbon\Carbon::parse($cultivo->fecha_cambio_fase)->diffInDays(now()) }} días)
    @endif
  </div>
  @else
  <div style="font-size:.85rem;color:var(--text-secondary);margin-top:6px;">Sin fase asignada aún.</div>
  @endif
  <button onclick="openModal('modalCambioFase')" class="btn btn-sm btn-primary" style="margin-top:12px;">
    🔄 Cambiar Fase
  </button>
</div>

{{-- TIMELINE DE FASES --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🗓 Ciclo Fenológico</div>
    <span style="font-size:.78rem;color:var(--text-secondary);">{{ $fases->count() }} fases</span>
  </div>
  <div class="fenologia-progress">
    @foreach($fases as $fase)
    @php
      $clase = $fase->es_actual ? 'activa' : ($fase->completada ? 'completada' : 'pendiente');
    @endphp
    <div class="fase-item {{ $fase->es_actual ? 'activa-item' : '' }}">
      <div class="fase-dot {{ $clase }}"
           style="background-color:{{ $fase->color_hex }}40; border-color:{{ $fase->completada||$fase->es_actual ? $fase->color_hex : 'var(--borde)' }}">
        {{ $fase->icono }}
        @if($fase->completada)
        <span style="position:absolute;bottom:-4px;right:-4px;background:var(--verde);color:white;
              border-radius:50%;width:16px;height:16px;font-size:.6rem;display:flex;
              align-items:center;justify-content:center;border:2px solid white;">✓</span>
        @endif
      </div>
      <div class="fase-info">
        <div class="fase-nombre">
          {{ $fase->nombre }}
          @if($fase->es_actual)
          <span class="fase-badge-activa">ACTUAL</span>
          @endif
        </div>
        @if($fase->duracion_dias_min)
        <div class="fase-duracion">
          {{ $fase->duracion_dias_min }}–{{ $fase->duracion_dias_max }} días
        </div>
        @endif
        @if($fase->descripcion)
        <div class="fase-duracion" style="margin-top:2px;">{{ $fase->descripcion }}</div>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  {{-- Historial de fechas --}}
  @if($historialFases->count())
  <div style="margin-top:16px;">
    <div style="font-size:.82rem;font-weight:700;color:var(--text-secondary);margin-bottom:8px;">📅 Historial</div>
    @foreach($historialFases as $hf)
    <div style="display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid var(--borde);">
      <span style="font-size:1rem;">{{ $hf->icono }}</span>
      <span style="font-size:.85rem;flex:1;">{{ $hf->fase_nombre }}</span>
      <span style="font-size:.78rem;color:var(--text-secondary);">
        {{ \Carbon\Carbon::parse($hf->fecha_inicio)->format('d/m/Y') }}
        @if($hf->fecha_fin)
          → {{ \Carbon\Carbon::parse($hf->fecha_fin)->format('d/m/Y') }}
          <span style="color:var(--text-secondary);">({{ \Carbon\Carbon::parse($hf->fecha_inicio)->diffInDays($hf->fecha_fin) }}d)</span>
        @else
          <span class="badge badge-green" style="font-size:.7rem;">activa</span>
        @endif
      </span>
    </div>
    @endforeach
  </div>
  @endif
</div>

{{-- PLAN DE MANEJO FASE ACTUAL --}}
@if($planFaseActual->count())
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Actividades Sugeridas — {{ $faseActual->nombre }}</div>
    <span style="font-size:.78rem;color:var(--text-secondary);">{{ $planFaseActual->count() }} actividades</span>
  </div>
  @php
    $iconosPlan = ['fertilizacion'=>'🌿','riego'=>'💧','control_fitosanitario'=>'🐛',
                   'aplicacion_agroquimico'=>'🧪','deshierbe'=>'🪚','poda'=>'✂️','monitoreo'=>'🔍','otro'=>'📝'];
  @endphp
  @foreach($planFaseActual as $plan)
  <div class="plan-item">
    <div class="plan-icono">{{ $iconosPlan[$plan->tipo_actividad] ?? '📝' }}</div>
    <div style="flex:1">
      <div class="plan-actividad">
        {{ $plan->actividad }}
        @if($plan->obligatoria)
        <span style="font-size:.7rem;background:#fde8e8;color:#c0392b;padding:1px 6px;border-radius:10px;margin-left:4px;">Obligatoria</span>
        @endif
      </div>
      @if($plan->producto_sugerido)
      <div class="plan-producto">📦 {{ $plan->producto_sugerido }} {{ $plan->dosis_sugerida ? '· '.$plan->dosis_sugerida : '' }}</div>
      @endif
      @if($plan->descripcion)
      <div class="plan-producto">{{ $plan->descripcion }}</div>
      @endif
    </div>
  </div>
  @endforeach
  <button onclick="openModal('modalEventoAvanzado')" class="btn btn-sm btn-primary" style="margin-top:10px;width:100%;">
    ➕ Registrar actividad
  </button>
</div>
@endif

{{-- RENDIMIENTO ESPERADO VS REAL --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📊 Rendimiento por Hectárea</div>
    <button onclick="openModal('modalRendimiento')" class="btn btn-sm btn-ghost">✏️ Editar</button>
  </div>
  <div class="rendimiento-body">
    @if($comparativa['tipo'] === 'sin_datos')
    <div style="text-align:center;padding:16px;color:var(--text-secondary);">
      <div style="font-size:2rem;">📊</div>
      <div style="font-size:.88rem;">Sin cosechas registradas en kg o toneladas aún.</div>
      <div style="font-size:.8rem;margin-top:4px;">Registra cosechas con unidad <strong>kg</strong> o <strong>toneladas</strong> para ver tu rendimiento.</div>
    </div>
    @else
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
      <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:.75rem;color:var(--text-secondary);">Real (tu cultivo)</div>
        <div style="font-weight:800;color:var(--verde-dark);font-size:1.1rem;">
          {{ number_format($comparativa['valor'],2) }}
        </div>
        <div style="font-size:.72rem;color:var(--text-secondary);">{{ $comparativa['unidad'] ?? 'ton/ha' }}</div>
      </div>
      <div style="background:#fff3e0;border-radius:10px;padding:12px;text-align:center;">
        <div style="font-size:.75rem;color:var(--text-secondary);">Regional promedio</div>
        <div style="font-weight:800;color:#e65100;font-size:1.1rem;">
          {{ $comparativa['regional_promedio'] ? number_format($comparativa['regional_promedio'],2) : 'N/D' }}
        </div>
        <div style="font-size:.72rem;color:var(--text-secondary);">{{ $comparativa['unidad'] ?? 'ton/ha' }}</div>
      </div>
    </div>

    @if($comparativa['porcentaje'] !== null)
    @php
      $tipoCmp = $comparativa['tipo'];
      $iconoCmp = $tipoCmp === 'superior' ? '🟢' : ($tipoCmp === 'inferior' ? '🔴' : '🟡');
      $textoCmp = $tipoCmp === 'superior'
        ? 'Tu rendimiento supera el promedio regional en ' . abs($comparativa['porcentaje']) . '%'
        : ($tipoCmp === 'inferior'
          ? 'Tu rendimiento está ' . abs($comparativa['porcentaje']) . '% por debajo del promedio regional'
          : 'Tu rendimiento es similar al promedio regional');
      $colorCmp = $tipoCmp === 'superior' ? 'var(--verde-dark)' : ($tipoCmp === 'inferior' ? 'var(--rojo)' : '#e65100');
      $maxVal   = max($comparativa['valor'], $comparativa['regional_max'] ?? $comparativa['regional_promedio'], 0.01);
      $pctReal  = min(100, round(($comparativa['valor'] / $maxVal) * 100));
      $pctRegMark = min(100, round(($comparativa['regional_promedio'] / $maxVal) * 100));
    @endphp
    <div class="rendimiento-compare-bar">
      <span class="rbar-label">Tu cultivo</span>
      <div class="rbar-track">
        <div class="rbar-real" style="width:{{ $pctReal }}%;background:{{ $colorCmp }};"></div>
        <div class="rbar-regional-mark" style="left:{{ $pctRegMark }}%;"></div>
      </div>
      <span class="rbar-value" style="color:{{ $colorCmp }};">{{ number_format($comparativa['valor'],2) }}</span>
    </div>

    <div style="font-size:.83rem;margin-top:8px;padding:10px;border-radius:8px;
         background:{{ $tipoCmp==='superior' ? 'var(--verde-bg)' : ($tipoCmp==='inferior' ? '#fde8e8' : '#fff8e1') }};
         color:{{ $colorCmp }};">
      {{ $iconoCmp }} {{ $textoCmp }}
    </div>

    @if($comparativa['regional_min'] && $comparativa['regional_max'])
    <div style="font-size:.77rem;color:var(--text-secondary);margin-top:6px;">
      Rango regional: {{ $comparativa['regional_min'] }} – {{ $comparativa['regional_max'] }} {{ $comparativa['unidad'] }}
      @if($comparativa['fuente']) · Fuente: {{ $comparativa['fuente'] }} ({{ $comparativa['anio'] }}) @endif
    </div>
    @endif
    @endif

    @if($cultivo->rendimiento_esperado_ha)
    <div style="font-size:.83rem;margin-top:10px;padding:8px 12px;background:var(--bg-page);border-radius:8px;">
      🎯 Meta esperada: <strong>{{ number_format($cultivo->rendimiento_esperado_ha,2) }} ton/ha</strong>
      @php
        $avanceMeta = $cultivo->rendimiento_real_ha && $cultivo->rendimiento_esperado_ha
          ? min(100, round(($cultivo->rendimiento_real_ha / $cultivo->rendimiento_esperado_ha)*100)) : 0;
      @endphp
      <div class="progreso-bar-wrap" style="height:10px;margin-top:6px;">
        <div class="progreso-bar-fill" style="width:{{ $avanceMeta }}%;font-size:.65rem;">{{ $avanceMeta }}%</div>
      </div>
    </div>
    @endif
    @endif

    {{-- Tabla por departamento --}}
    @if($rendRegional->count())
    <details style="margin-top:14px;">
      <summary style="cursor:pointer;font-size:.82rem;font-weight:700;color:var(--text-secondary);">Ver referencia por departamento</summary>
      <table style="width:100%;margin-top:8px;font-size:.8rem;border-collapse:collapse;">
        <thead>
          <tr style="background:var(--verde-bg);">
            <th style="padding:6px 8px;text-align:left;border-radius:6px 0 0 6px;">Departamento</th>
            <th style="padding:6px 8px;text-align:right;">Promedio</th>
            <th style="padding:6px 8px;text-align:right;border-radius:0 6px 6px 0;">Año</th>
          </tr>
        </thead>
        <tbody>
          @foreach($rendRegional as $depto => $registros)
          @php $reg = $registros->first(); @endphp
          <tr style="border-bottom:1px solid var(--borde);">
            <td style="padding:6px 8px;">{{ $depto }}</td>
            <td style="padding:6px 8px;text-align:right;">{{ number_format($reg->rendimiento_promedio_ha,2) }} {{ $reg->unidad }}</td>
            <td style="padding:6px 8px;text-align:right;color:var(--text-secondary);">{{ $reg->anio }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </details>
    @endif
  </div>
</div>

{{-- EVENTOS AVANZADOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🧪 Aplicaciones y Manejos</div>
    <button onclick="openModal('modalEventoAvanzado')" class="btn btn-sm btn-primary">➕ Agregar</button>
  </div>

  @php
    $resumen = [
      'riego'                  => ['label'=>'Riegos','icono'=>'💧','color'=>'badge-blue'],
      'fertilizacion'          => ['label'=>'Fertilizaciones','icono'=>'🌿','color'=>'badge-green'],
      'aplicacion_agroquimico' => ['label'=>'Agroquímicos','icono'=>'🧪','color'=>'badge-red'],
      'control_fitosanitario'  => ['label'=>'Control fitosanitario','icono'=>'🐛','color'=>'badge-orange'],
      'deshierbe'              => ['label'=>'Deshierbes','icono'=>'🪚','color'=>'badge-brown'],
    ];
  @endphp

  {{-- Chips resumen --}}
  @if($resumenEventos->count())
  <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:12px;">
    @foreach($resumen as $tipo => $meta)
    @if(isset($resumenEventos[$tipo]))
    <span class="badge {{ $meta['color'] }}">{{ $meta['icono'] }} {{ $meta['label'] }}: {{ $resumenEventos[$tipo] }}</span>
    @endif
    @endforeach
  </div>
  @endif

  @forelse($eventosAvanzados as $ev)
  <div class="evento-av-card {{ $ev->alerta_cosecha_activa ? 'alerta-carencia' : '' }}">
    <div class="evento-av-header">
      <div>
        <span class="badge {{ $ev->tipoBadgeClass() }}" style="font-size:.72rem;">{{ $ev->tipoLabel() }}</span>
        @if($ev->fase)
        <span style="font-size:.72rem;color:var(--text-secondary);margin-left:4px;">{{ $ev->fase->icono }} {{ $ev->fase->nombre }}</span>
        @endif
      </div>
      <span class="evento-av-fecha">{{ \Carbon\Carbon::parse($ev->fecha)->format('d/m/Y') }}</span>
    </div>
    <div class="evento-av-titulo">{{ $ev->titulo }}</div>

    {{-- Detalle por tipo --}}
    @if($ev->tipo === 'aplicacion_agroquimico' || ($ev->producto_nombre && in_array($ev->tipo,['fertilizacion','control_fitosanitario'])))
    <div class="evento-av-detalle" style="margin-top:4px;">
      @if($ev->producto_nombre) 📦 {{ $ev->producto_nombre }} @endif
      @if($ev->dosis) · {{ $ev->dosis }} {{ $ev->dosis_unidad }} @endif
      @if($ev->periodo_carencia_dias) · Carencia: {{ $ev->periodo_carencia_dias }}d @endif
    </div>
    @endif

    @if($ev->tipo === 'riego')
    <div class="evento-av-detalle" style="margin-top:4px;">
      @if($ev->volumen_agua_litros) 💧 {{ number_format($ev->volumen_agua_litros,0) }} L @endif
      @if($ev->metodo_riego) · {{ ucfirst($ev->metodo_riego) }} @endif
      @if($ev->duracion_minutos) · {{ $ev->duracion_minutos }} min @endif
    </div>
    @endif

    @if($ev->tipo === 'fertilizacion' && ($ev->nitrogeno_n || $ev->fosforo_p || $ev->potasio_k))
    <div class="evento-av-detalle" style="margin-top:4px;">
      NPK: N={{ $ev->nitrogeno_n ?? '–' }} / P={{ $ev->fosforo_p ?? '–' }} / K={{ $ev->potasio_k ?? '–' }}
      @if($ev->metodo_aplicacion_fertilizante) · {{ ucfirst($ev->metodo_aplicacion_fertilizante) }} @endif
    </div>
    @endif

    @if($ev->tipo === 'control_fitosanitario')
    <div class="evento-av-detalle" style="margin-top:4px;">
      @if($ev->plaga_enfermedad) 🐛 {{ $ev->plaga_enfermedad }} @endif
      @if($ev->nivel_severidad)
      <span class="badge {{ ['bajo'=>'badge-green','medio'=>'badge-orange','alto'=>'badge-red','critico'=>'badge-red'][$ev->nivel_severidad] }}" style="font-size:.7rem;">{{ $ev->nivel_severidad }}</span>
      @endif
    </div>
    @endif

    @if($ev->alerta_cosecha_activa && $ev->fecha_minima_cosecha)
    <div class="alerta-carencia-tag">
      ⚠️ No cosechar antes del {{ \Carbon\Carbon::parse($ev->fecha_minima_cosecha)->format('d/m/Y') }}
    </div>
    @endif

    @if($ev->descripcion)
    <div class="evento-av-detalle" style="margin-top:4px;">{{ $ev->descripcion }}</div>
    @endif

    @if($ev->foto_ruta)
    <img src="{{ asset($ev->foto_ruta) }}" alt="foto evento" style="width:100%;max-height:160px;object-fit:cover;border-radius:8px;margin-top:8px;cursor:pointer;" onclick="openLightbox(this.src)">
    @endif

    <div style="margin-top:8px;">
      <form method="POST" action="{{ route('cultivos.eventos-avanzados.delete', [$cultivo->id, $ev->id]) }}"
            onsubmit="return confirm('¿Eliminar este evento?');" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-sm btn-ghost" style="color:var(--rojo);font-size:.78rem;">🗑 Eliminar</button>
      </form>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:var(--text-secondary);">
    <div style="font-size:2rem;">🧪</div>
    <div style="font-size:.88rem;">Sin eventos registrados aún.</div>
  </div>
  @endforelse
</div>

{{-- ─────────────────── MODALS ─────────────────────────────── --}}

{{-- MODAL: Cambio de Fase --}}
<div id="modalCambioFase" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-title">🔄 Cambiar Fase Fenológica</div>
    <form method="POST" action="{{ route('cultivos.fase.cambiar', $cultivo->id) }}">
      @csrf
      <div class="form-group">
        <label>Fase</label>
        <select name="fase_id" required>
          @foreach($fases as $fase)
          <option value="{{ $fase->id }}" {{ $fase->es_actual ? 'selected' : '' }}>
            {{ $fase->icono }} {{ $fase->nombre }}
            @if($fase->duracion_dias_min) ({{ $fase->duracion_dias_min }}-{{ $fase->duracion_dias_max }} días) @endif
          </option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Fecha de inicio de fase</label>
        <input type="date" name="fecha_cambio" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="form-group">
        <label>Observaciones (opcional)</label>
        <textarea name="observaciones" placeholder="Notas sobre el cambio de fase…"></textarea>
      </div>
      <div style="font-size:.8rem;color:var(--text-secondary);margin-bottom:12px;background:var(--verde-bg);padding:8px 10px;border-radius:8px;">
        💡 Al cambiar la fase se generarán automáticamente las tareas sugeridas del plan de manejo en tu Agenda.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalCambioFase')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Confirmar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Evento Avanzado --}}
<div id="modalEventoAvanzado" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-title">➕ Registrar Aplicación / Manejo</div>
    <form method="POST" action="{{ route('cultivos.eventos-avanzados.store', $cultivo->id) }}" enctype="multipart/form-data">
      @csrf
      {{-- Selector de tipo --}}
      <div class="form-group">
        <label>Tipo</label>
        <div class="tab-group">
          <button type="button" class="tab-btn active" onclick="selectTipo('aplicacion_agroquimico',this)">🧪 Agroquímico</button>
          <button type="button" class="tab-btn" onclick="selectTipo('riego',this)">💧 Riego</button>
          <button type="button" class="tab-btn" onclick="selectTipo('fertilizacion',this)">🌿 Fertilización</button>
          <button type="button" class="tab-btn" onclick="selectTipo('control_fitosanitario',this)">🐛 Fitosanitario</button>
          <button type="button" class="tab-btn" onclick="selectTipo('deshierbe',this)">🪚 Deshierbe</button>
        </div>
        <input type="hidden" name="tipo" id="tipo_hidden" value="aplicacion_agroquimico">
      </div>

      {{-- Campos comunes --}}
      <div class="form-row">
        <div class="form-group">
          <label>Título *</label>
          <input type="text" name="titulo" required placeholder="Ej: Aplicación Mancozeb">
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" value="{{ now()->toDateString() }}" required>
        </div>
      </div>
      <div class="form-group">
        <label>Fase (opcional)</label>
        <select name="fase_id">
          <option value="">-- Fase actual del cultivo --</option>
          @foreach($fases as $fase)
          <option value="{{ $fase->id }}" {{ $fase->es_actual ? 'selected' : '' }}>{{ $fase->icono }} {{ $fase->nombre }}</option>
          @endforeach
        </select>
      </div>

      {{-- Campos Agroquímico --}}
      <div id="fields_aplicacion_agroquimico" class="fields-group active">
        <div class="form-row">
          <div class="form-group">
            <label>Producto *</label>
            <input type="text" name="producto_nombre" placeholder="Ej: Mancozeb 80%">
          </div>
          <div class="form-group">
            <label>Registro ICA</label>
            <input type="text" name="producto_registro_ica" placeholder="ICA-XXXXXX">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Dosis</label>
            <input type="number" name="dosis" step="0.0001" min="0" placeholder="0.00">
          </div>
          <div class="form-group">
            <label>Unidad dosis</label>
            <select name="dosis_unidad">
              <option value="cc/L">cc/L</option>
              <option value="g/L">g/L</option>
              <option value="L/ha">L/ha</option>
              <option value="kg/ha">kg/ha</option>
              <option value="g/ha">g/ha</option>
              <option value="mL/ha">mL/ha</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>⏱ Período de carencia (días)</label>
          <input type="number" name="periodo_carencia_dias" min="0" placeholder="Ej: 14">
          <div style="font-size:.75rem;color:var(--text-secondary);margin-top:3px;">Se calculará la fecha mínima de cosecha automáticamente.</div>
        </div>
      </div>

      {{-- Campos Riego --}}
      <div id="fields_riego" class="fields-group">
        <div class="form-row">
          <div class="form-group">
            <label>Volumen (litros)</label>
            <input type="number" name="volumen_agua_litros" step="0.01" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>Duración (min)</label>
            <input type="number" name="duracion_minutos" min="0" placeholder="0">
          </div>
        </div>
        <div class="form-group">
          <label>Método de riego</label>
          <select name="metodo_riego">
            <option value="goteo">Goteo</option>
            <option value="aspersion">Aspersión</option>
            <option value="surco">Surco</option>
            <option value="inundacion">Inundación</option>
            <option value="manual" selected>Manual</option>
            <option value="otro">Otro</option>
          </select>
        </div>
      </div>

      {{-- Campos Fertilización --}}
      <div id="fields_fertilizacion" class="fields-group">
        <div class="form-row">
          <div class="form-group">
            <label>Fuente / Producto</label>
            <input type="text" name="fuente_fertilizante" placeholder="Ej: Urea 46%">
          </div>
          <div class="form-group">
            <label>Método aplicación</label>
            <select name="metodo_aplicacion_fertilizante">
              <option value="edafico" selected>Edáfico (al suelo)</option>
              <option value="foliar">Foliar</option>
              <option value="fertiriego">Fertiriego</option>
              <option value="otro">Otro</option>
            </select>
          </div>
        </div>
        <div style="font-size:.8rem;font-weight:700;color:var(--text-secondary);margin-bottom:6px;">NPK (kg/ha)</div>
        <div class="form-row" style="grid-template-columns:1fr 1fr 1fr;">
          <div class="form-group">
            <label>N (Nitrógeno)</label>
            <input type="number" name="nitrogeno_n" step="0.01" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>P (Fósforo)</label>
            <input type="number" name="fosforo_p" step="0.01" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>K (Potasio)</label>
            <input type="number" name="potasio_k" step="0.01" min="0" placeholder="0">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Dosis (kg/ha)</label>
            <input type="number" name="dosis" step="0.01" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>Unidad</label>
            <select name="dosis_unidad"><option value="kg/ha">kg/ha</option><option value="g/planta">g/planta</option></select>
          </div>
        </div>
      </div>

      {{-- Campos Control fitosanitario --}}
      <div id="fields_control_fitosanitario" class="fields-group">
        <div class="form-row">
          <div class="form-group">
            <label>Plaga / Enfermedad</label>
            <input type="text" name="plaga_enfermedad" placeholder="Ej: Sigatoka negra">
          </div>
          <div class="form-group">
            <label>Severidad</label>
            <select name="nivel_severidad">
              <option value="bajo">Bajo</option>
              <option value="medio" selected>Medio</option>
              <option value="alto">Alto</option>
              <option value="critico">Crítico</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Tipo de control</label>
            <select name="tipo_control">
              <option value="quimico" selected>Químico</option>
              <option value="biologico">Biológico</option>
              <option value="cultural">Cultural</option>
              <option value="mecanico">Mecánico</option>
            </select>
          </div>
          <div class="form-group">
            <label>Producto</label>
            <input type="text" name="producto_nombre" placeholder="Nombre del producto">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Dosis</label>
            <input type="number" name="dosis" step="0.001" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label>Unidad</label>
            <select name="dosis_unidad">
              <option value="cc/L">cc/L</option>
              <option value="g/L">g/L</option>
              <option value="L/ha">L/ha</option>
            </select>
          </div>
        </div>
      </div>

      {{-- Campos Deshierbe --}}
      <div id="fields_deshierbe" class="fields-group">
        <div class="form-row">
          <div class="form-group">
            <label>Método</label>
            <select name="metodo_deshierbe">
              <option value="manual" selected>Manual</option>
              <option value="mecanico">Mecánico</option>
              <option value="quimico">Químico</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group">
            <label>Área (ha)</label>
            <input type="number" name="area_deshierbada_ha" step="0.0001" min="0" placeholder="0.00">
          </div>
        </div>
      </div>

      {{-- Campos comunes finales --}}
      <div class="form-group">
        <label>Descripción / Notas</label>
        <textarea name="descripcion" placeholder="Observaciones adicionales…"></textarea>
      </div>
      <div class="form-group">
        <label>Responsable</label>
        <select name="persona_id">
          <option value="">-- Sin asignar --</option>
          @foreach($personas as $p)
          <option value="{{ $p->id }}">{{ $p->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Foto (opcional)</label>
        <input type="file" name="foto" accept="image/*">
      </div>

      <div style="display:flex;gap:8px;margin-top:4px;">
        <button type="button" onclick="closeModal('modalEventoAvanzado')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL: Rendimiento esperado --}}
<div id="modalRendimiento" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-title">🎯 Meta de Rendimiento</div>
    <form method="POST" action="{{ route('cultivos.rendimiento.update', $cultivo->id) }}">
      @csrf
      <div class="form-group">
        <label>Rendimiento esperado (ton/ha)</label>
        <input type="number" name="rendimiento_esperado_ha" step="0.01" min="0"
               value="{{ $cultivo->rendimiento_esperado_ha }}"
               placeholder="Ej: 4.5">
        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:4px;">
          El rendimiento real se calcula automáticamente desde las cosechas registradas en kg o toneladas.
        </div>
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalRendimiento')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Lightbox --}}
<div id="lightbox" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:2000;
     align-items:center;justify-content:center;" onclick="this.style.display='none'">
  <img id="lightboxImg" src="" alt="" style="max-width:95%;max-height:90vh;border-radius:12px;object-fit:contain;">
</div>

@push('scripts')
<script>
function openModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  // Mover el modal al body para evitar que quede atrapado en stacking contexts
  document.body.appendChild(m);
  m.style.cssText = 'display:flex !important; position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:9999; overflow-y:auto; padding:20px; align-items:flex-start; justify-content:center;';
  m.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'none';
  m.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(m) {
  m.addEventListener('click', function(e) {
    if (e.target === this) {
      this.style.display = 'none';
      this.classList.remove('open');
      document.body.style.overflow = '';
    }
  });
});

// Selector de tipo en modal evento
function selectTipo(tipo, btn) {
  document.getElementById('tipo_hidden').value = tipo;
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  document.querySelectorAll('.fields-group').forEach(fg => fg.classList.remove('active'));
  const el = document.getElementById('fields_' + tipo);
  if (el) el.classList.add('active');
}

// Lightbox
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').style.display = 'flex';
}

// Auto-abrir modals con errores de validación
@if($errors->any())
openModal('modalEventoAvanzado');
@endif
</script>
@endpush
@endsection

