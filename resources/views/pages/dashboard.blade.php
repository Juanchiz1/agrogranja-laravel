@extends('layouts.app')
@section('title','Inicio')
@section('page_title', '🌾 ' . (now()->hour < 12 ? 'Buenos días' : (now()->hour < 18 ? 'Buenas tardes' : 'Buenas noches')) . ', ' . explode(' ', $user->nombre)[0])

@section('content')

{{-- STATS --}}
<div class="stats-grid">
  <div class="stat-card"><div class="stat-value text-green">{{ $cultivosActivos }}</div><div class="stat-label">Cultivos activos</div></div>
  <div class="stat-card"><div class="stat-value text-brown">${{ number_format($gastosMes/1000,0) }}k</div><div class="stat-label">Gastos mes</div></div>
  <div class="stat-card"><div class="stat-value text-orange">{{ $tareasPend }}</div><div class="stat-label">Tareas pend.</div></div>
</div>

{{-- BALANCE --}}
@php $balance = $ingresosMes - $gastosMes; @endphp
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
      <p class="text-xs text-gray mt-2">Gastos: <strong class="text-red">${{ number_format($gastosMes,0,',','.') }}</strong></p>
    </div>
  </div>
</div>

{{-- MENU --}}
<div class="menu-grid">
  <a href="{{ route('cultivos.index') }}" class="menu-card"><div class="menu-icon" style="background:#edf7ed;">🌱</div><span class="menu-label">Cultivos y Animales</span></a>
  <a href="{{ route('gastos.index') }}"   class="menu-card"><div class="menu-icon" style="background:#fdf3ea;">💰</div><span class="menu-label">Gastos e Insumos</span></a>
  <a href="{{ route('ingresos.index') }}" class="menu-card"><div class="menu-icon" style="background:#eff6ff;">📈</div><span class="menu-label">Ingresos y Ventas</span></a>
  <a href="{{ route('calendario.index') }}"class="menu-card"><div class="menu-icon" style="background:#fdf2f8;">📅</div><span class="menu-label">Agenda y Tareas</span></a>
  <a href="{{ route('animales.index') }}" class="menu-card"><div class="menu-icon" style="background:#f5f3ff;">🐄</div><span class="menu-label">Mis Animales</span></a>
  <a href="{{ route('reportes.index') }}" class="menu-card"><div class="menu-icon" style="background:#eef2ff;">📊</div><span class="menu-label">Reportes</span></a>
  <a href="{{ route('cosechas.index') }}" class="menu-card"><div class="menu-icon" style="background:#f0fdf4;">🌾</div><span class="menu-label">Cosechas y<br>Producción</span></a>
</div>

{{-- TAREAS HOY --}}
@if($tareasHoy->count())
<p class="section-title">📌 Tareas de hoy</p>
@foreach($tareasHoy as $t)
@php $ic = ['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝'][$t->tipo] ?? '📝'; $pc = ['alta'=>'var(--rojo)','media'=>'var(--naranja)','baja'=>'var(--verde-dark)'][$t->prioridad] ?? 'var(--gris)'; @endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)">{{ $ic }}</div>
  <div class="item-body"><div class="item-title">{{ $t->titulo }}</div><div class="item-sub" style="color:{{ $pc }}">Prioridad {{ $t->prioridad }}</div></div>
  <form method="POST" action="{{ route('tareas.completar', $t->id) }}">@csrf<button class="btn btn-sm btn-secondary">✓ Listo</button></form>
</div>
@endforeach
@endif

{{-- ÚLTIMOS CULTIVOS --}}
@if($recentCultivos->count())
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌱 Últimos cultivos</p>
  <a href="{{ route('cultivos.index') }}" class="text-sm text-green font-bold">Ver todos</a>
</div>
@foreach($recentCultivos as $c)
@php $b = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado] ?? 'badge-green'; @endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)">🌿</div>
  <div class="item-body"><div class="item-title">{{ $c->nombre }}</div><div class="item-sub">{{ $c->tipo }} · {{ \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') }}</div></div>
  <span class="badge {{ $b }}">{{ $c->estado }}</span>
</div>
@endforeach
@endif

@endsection
