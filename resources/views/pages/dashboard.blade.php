@extends('layouts.app')
@section('title','Inicio')
@section('page_title', '🌾 ' . (now()->hour < 12 ? 'Buenos días' : (now()->hour < 18 ? 'Buenas tardes' : 'Buenas noches')) . ', ' . explode(' ', $user->nombre)[0])

@push('head')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<link rel="stylesheet" href="{{ asset('css/dashboard-adaptativo.css') }}">
@endpush

@section('content')
@php $balance = $ingresosMes - $gastosMes; @endphp

{{-- BANNER ENCUESTA (ahora dentro del content, bien posicionado) --}}
@if(!session('encuesta_respondida_en') || \Carbon\Carbon::parse(session('encuesta_respondida_en'))->diffInDays(now()) > 90)
<div class="encuesta-banner">
    <div class="encuesta-banner-text">
        <p class="encuesta-banner-titulo">📋 ¿Cómo te ha ido con Agrogranja?</p>
        <p class="encuesta-banner-sub">Responde una encuesta rápida · solo 5 minutos</p>
    </div>
    <div class="encuesta-banner-actions">
        <a href="{{ route('encuesta.show') }}" class="encuesta-banner-btn">
            Responder encuesta
        </a>
        <form method="POST" action="{{ route('encuesta.ignorar') }}" style="margin:0;">
            @csrf
            <button type="submit" class="encuesta-banner-close" title="Cerrar">×</button>
        </form>
    </div>
</div>
@endif

{{-- ALERTAS ACTIVAS --}}
@if($tareasVencidas > 0)
<div class="dash-alert rojo" onclick="location.href='{{ route('calendario.index') }}?tab=vencidas'">
  <span>⚠️ {{ $tareasVencidas }} tarea(s) vencida(s) sin completar</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
@endif

@if($alertasInventario > 0)
<div class="dash-alert" onclick="location.href='{{ route('inventario.alertas') }}'">
  <span>📦 {{ $alertasInventario }} insumo(s) con stock bajo mínimo</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
@endif

@if(\App\Models\LineaProductiva::tieneAnimales() && $proximasDosis->count())
<div class="dash-alert azul" onclick="location.href='{{ route('animales.index') }}'">
  <span>💊 {{ $proximasDosis->count() }} dosis de medicamento próxima(s)</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     STATS GRID — KPIs principales (siempre visibles)
     ══════════════════════════════════════════════════════════════ --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px;">
  @if(in_array('cultivos', $lineasActivas))
    <div class="quick-stat"><div class="quick-stat-val">{{ $cultivosActivos }}</div><div class="quick-stat-lbl">Cultivos activos</div></div>
  @elseif(\App\Models\LineaProductiva::tieneAnimales())
    <div class="quick-stat"><div class="quick-stat-val">{{ $animalesActivos }}</div><div class="quick-stat-lbl">Animales activos</div></div>
  @else
    <div class="quick-stat"><div class="quick-stat-val">{{ $cultivosActivos }}</div><div class="quick-stat-lbl">Cultivos activos</div></div>
  @endif
  <div class="quick-stat"><div class="quick-stat-val" style="color:var(--marron);">${{ number_format($gastosMes/1000,0) }}k</div><div class="quick-stat-lbl">Gastos mes</div></div>
  <div class="quick-stat"><div class="quick-stat-val text-green">${{ number_format($ingresosMes/1000,0) }}k</div><div class="quick-stat-lbl">Ingresos mes</div></div>
  <div class="quick-stat" style="{{ $tareasPend>0?'background:#fef2f2':'' }}"><div class="quick-stat-val" style="{{ $tareasPend>0?'color:var(--rojo)':'' }}">{{ $tareasPend }}</div><div class="quick-stat-lbl">Tareas pend.</div></div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     KPIs POR LÍNEA PRODUCTIVA — solo aparece si tiene líneas con datos
     ══════════════════════════════════════════════════════════════ --}}
@if(!empty($kpisLineas))
<div class="kpis-lineas-grid">
  @foreach($kpisLineas as $codigo => $kpi)
  <div class="kpi-linea-card kpi-color-{{ $kpi['color'] }}">
    <div class="kpi-linea-header">
      <span class="kpi-linea-emoji">{{ $kpi['emoji'] }}</span>
      <span class="kpi-linea-titulo">{{ $kpi['titulo'] }}</span>
    </div>
    <div class="kpi-linea-metricas">
      @foreach($kpi['metricas'] as $m)
      <div class="kpi-linea-metrica">
        <div class="kpi-linea-valor">{{ $m['valor'] }}</div>
        <div class="kpi-linea-label">{{ $m['label'] }}</div>
        @if(!empty($m['sub']))
          <div class="kpi-linea-sub">{{ $m['sub'] }}</div>
        @endif
      </div>
      @endforeach
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- BALANCE MES --}}
<div class="card mb-3" style="background:{{ $balance >= 0 ? 'var(--verde-bg)' : '#fef2f2' }}">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gris);">Balance {{ now()->locale('es')->monthName }}</p>
      <p style="font-size:1.7rem;font-weight:800;color:{{ $balance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};margin-top:4px;">
        ${{ number_format($balance, 0, ',', '.') }}
      </p>
    </div>
    <div style="text-align:right;">
      <p class="text-xs text-gray">Ingresos: <strong class="text-green">${{ number_format($ingresosMes,0,',','.') }}</strong></p>
      <p class="text-xs text-gray mt-1">Gastos: <strong style="color:var(--rojo)">${{ number_format($gastosMes,0,',','.') }}</strong></p>
      @if($pagadoMesPersonas > 0)
      <p class="text-xs text-gray mt-1">Mano de obra: <strong style="color:#9a3412">${{ number_format($pagadoMesPersonas,0,',','.') }}</strong></p>
      @endif
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     MENÚ PRINCIPAL — Solo muestra módulos relevantes a las líneas
     productivas activas. Todos los siempre-visibles (gastos,
     ingresos, agenda, personas, inventario, reportes) aparecen
     siempre.
     ══════════════════════════════════════════════════════════════ --}}
<div class="menu-grid">
  @if(in_array('cultivos', $lineasActivas))
  <a href="{{ route('cultivos.index') }}"    class="menu-card"><div class="menu-icon" style="background:#edf7ed;">🌱</div><span class="menu-label">Cultivos</span></a>
  @endif

  <a href="{{ route('gastos.index') }}"      class="menu-card"><div class="menu-icon" style="background:#fdf3ea;">💰</div><span class="menu-label">Gastos</span></a>
  <a href="{{ route('ingresos.index') }}"    class="menu-card"><div class="menu-icon" style="background:#eff6ff;">📈</div><span class="menu-label">Ingresos</span></a>
  <a href="{{ route('calendario.index') }}"  class="menu-card"><div class="menu-icon" style="background:#fdf2f8;">📅</div><span class="menu-label">Agenda</span></a>

  @if(\App\Models\LineaProductiva::tieneAnimales())
  <a href="{{ route('animales.index') }}"    class="menu-card"><div class="menu-icon" style="background:#f5f3ff;">🐄</div><span class="menu-label">Animales</span></a>
  @endif

  <a href="{{ route('personas.index') }}"    class="menu-card"><div class="menu-icon" style="background:#fdf4ff;">👥</div><span class="menu-label">Personas</span></a>

  @if(in_array('cultivos', $lineasActivas))
  <a href="{{ route('cosechas.index') }}"    class="menu-card"><div class="menu-icon" style="background:#f0fdf4;">🌾</div><span class="menu-label">Cosechas</span></a>
  @endif

  <a href="{{ route('inventario.index') }}"  class="menu-card"><div class="menu-icon" style="background:#fff7ed;">📦</div><span class="menu-label">Inventario</span></a>
  <a href="{{ route('reportes.index') }}"    class="menu-card"><div class="menu-icon" style="background:#eef2ff;">📊</div><span class="menu-label">Reportes</span></a>
</div>

{{-- TAREAS DE HOY --}}
@if($tareasHoy->count())
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">📌 Tareas de hoy</p>
  <a href="{{ route('calendario.index') }}?tab=hoy" class="text-sm text-green font-bold">Ver todas</a>
</div>
@php $tiposIcono=['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝','medicamento'=>'💊','traslado_animal'=>'🏃','parto'=>'🐣','pago_proveedor'=>'🏪']; @endphp
@foreach($tareasHoy as $t)
@php $ic=$tiposIcono[$t->tipo]??'📝'; $pc=['alta'=>'var(--rojo)','media'=>'#d97706','baja'=>'var(--verde-dark)'][$t->prioridad]??'var(--text-secondary)'; @endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)">{{ $ic }}</div>
  <div class="item-body">
    <div class="item-title">{{ $t->titulo }}</div>
    <div class="item-sub" style="color:{{ $pc }}">{{ ucfirst($t->prioridad) }}</div>
  </div>
  <form method="POST" action="{{ route('tareas.completar',$t->id) }}">@csrf
    <button class="btn btn-sm btn-secondary">✓</button>
  </form>
</div>
@endforeach
@endif

{{-- ÚLTIMOS CULTIVOS (solo si tiene línea de cultivos) --}}
@if(in_array('cultivos', $lineasActivas) && $recentCultivos->count())
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌱 Cultivos recientes</p>
  <a href="{{ route('cultivos.index') }}" class="text-sm text-green font-bold">Ver todos</a>
</div>
@foreach($recentCultivos as $c)
@php $b=['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado]??'badge-green'; @endphp
<div class="list-item" style="cursor:pointer;" onclick="window.location='{{ route('cultivos.show',$c->id) }}'">
  <div class="item-icon" style="background:var(--verde-bg)">🌿</div>
  <div class="item-body">
    <div class="item-title">{{ $c->nombre }}</div>
    <div class="item-sub">{{ $c->tipo }} · {{ \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') }}</div>
  </div>
  <span class="badge {{ $b }}">{{ $c->estado }}</span>
</div>
@endforeach
@endif

{{-- COSECHAS RECIENTES (solo si tiene línea de cultivos) --}}
@if(in_array('cultivos', $lineasActivas) && $cosechasRecientes->count())
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌾 Últimas cosechas</p>
  <a href="{{ route('cosechas.index') }}" class="text-sm text-green font-bold">Ver todas</a>
</div>
@foreach($cosechasRecientes as $co)
<div class="list-item">
  <div class="item-icon" style="background:#f0fdf4">🌾</div>
  <div class="item-body">
    <div class="item-title">{{ $co->producto }}</div>
    <div class="item-sub">{{ number_format($co->cantidad,1) }} {{ $co->unidad }} · {{ \Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y') }}</div>
  </div>
  @if($co->valor_estimado)<span style="font-weight:700;color:var(--verde-dark);font-size:.87rem;">${{ number_format($co->valor_estimado,0,',','.') }}</span>@endif
</div>
@endforeach
@endif

{{-- TOP CULTIVO (solo si tiene línea de cultivos) --}}
@if(in_array('cultivos', $lineasActivas) && $topCultivo && $topCultivo->rentabilidad > 0)
<div style="background:var(--verde-bg);border-radius:var(--radius-lg);padding:12px 14px;margin-top:16px;border-left:3px solid var(--verde-dark);">
  <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:4px;">⭐ Cultivo más rentable {{ now()->year }}</p>
  <div class="flex justify-between items-center">
    <div>
      <div style="font-weight:700;">{{ $topCultivo->nombre }}</div>
      <div style="font-size:.78rem;color:var(--text-secondary);">{{ $topCultivo->tipo }}</div>
    </div>
    <div style="text-align:right;">
      <div style="font-weight:800;color:var(--verde-dark);font-size:1rem;">${{ number_format($topCultivo->rentabilidad,0,',','.') }}</div>
      <a href="{{ route('rentabilidad.detalle',$topCultivo->id) }}" style="font-size:.75rem;color:var(--verde-dark);">Ver detalle →</a>
    </div>
  </div>
</div>
@endif

@endsection