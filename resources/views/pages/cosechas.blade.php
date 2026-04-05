@extends('layouts.app')
@section('title','Cosechas')
@section('page_title','🌾 Registro de Cosechas')
@section('back_url', route('dashboard'))

@section('content')

{{-- ESTADÍSTICAS --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green">${{ number_format($totalMes/1000,0) }}k</div>
    <div class="stat-label">Valor mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-green">{{ $cantidadMes }}</div>
    <div class="stat-label">Registros mes</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green">${{ number_format($totalAnio/1000,0) }}k</div>
    <div class="stat-label">Valor año</div>
  </div>
</div>

{{-- TOP PRODUCTOS --}}
@if($topProductos->count())
<p class="section-title">Top productos este año</p>
@foreach($topProductos as $tp)
<div class="flex items-center justify-between mb-2">
  <span class="text-sm font-bold">{{ $tp->producto }}</span>
  <span class="text-sm" style="color:var(--verde-dark);">
    {{ number_format($tp->total_qty,1) }} · ${{ number_format($tp->total_valor,0,',','.') }}
  </span>
</div>
<div class="progress-bar mb-2">
  <div class="progress-fill" style="width:{{ $topProductos->max('total_valor') > 0 ? round($tp->total_valor/$topProductos->max('total_valor')*100) : 0 }}%"></div>
</div>
@endforeach
@endif

{{-- FILTROS --}}
<form method="GET" class="flex gap-2 mt-3 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar producto..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <input type="month" name="mes" class="form-control" style="width:130px;" value="{{ request('mes') }}" onchange="this.form.submit()">
  <select name="calidad" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Calidad</option>
    <option {{ request('calidad')==='excelente'?'selected':'' }} value="excelente">Excelente</option>
    <option {{ request('calidad')==='buena'?'selected':'' }}     value="buena">Buena</option>
    <option {{ request('calidad')==='regular'?'selected':'' }}   value="regular">Regular</option>
    <option {{ request('calidad')==='baja'?'selected':'' }}      value="baja">Baja</option>
  </select>
  <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>

{{-- LISTADO --}}
@if($cosechas->isEmpty())
<div class="empty-state">
  <div class="emoji">🌾</div>
  <p><strong>Sin cosechas registradas.</strong></p>
  <p>Toca + para registrar tu primera cosecha.</p>
</div>
@else
@php
  $calidadColor = ['excelente'=>'badge-green','buena'=>'badge-green','regular'=>'badge-orange','baja'=>'badge-red'];
  $destinoIcon  = ['venta'=>'💵','autoconsumo'=>'🏠','almacenaje'=>'📦'];
@endphp
@foreach($cosechas as $cs)
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg);font-size:1.3rem;">🌾</div>
  <div class="item-body">
    <div class="item-title">{{ $cs->producto }}</div>
    <div class="item-sub">
      {{ number_format($cs->cantidad,1) }} {{ $cs->unidad }}
      · {{ \Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y') }}
      @if($cs->cultivo_nombre) · {{ $cs->cultivo_nombre }}@endif
      @if($cs->destino) · {{ $destinoIcon[$cs->destino] ?? '' }} {{ $cs->destino }}@endif
    </div>
  </div>
  <div class="flex gap-2 items-center" style="flex-shrink:0;">
    <div style="text-align:right;">
      @if($cs->valor_estimado)
      <div class="font-bold text-green text-sm">${{ number_format($cs->valor_estimado,0,',','.') }}</div>
      @endif
      <span class="badge {{ $calidadColor[$cs->calidad] ?? 'badge-green' }}">{{ $cs->calidad }}</span>
    </div>
    <button onclick="openModal('editCosecha{{ $cs->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('cosechas.destroy',$cs->id) }}" onsubmit="return confirm('¿Eliminar?')">
      @csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="editCosecha{{ $cs->id }}" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cosecha</h3>
    <form method="POST" action="{{ route('cosechas.update',$cs->id) }}">
      @csrf
      @include('pages._cosecha_form', ['c' => $cs, 'cultivos' => $cultivos])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCosecha{{ $cs->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endif

{{-- MODAL NUEVA COSECHA --}}
<div class="modal-overlay" id="modalNuevaCosecha" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌾 Registrar cosecha</h3>
    <form method="POST" action="{{ route('cosechas.store') }}">
      @csrf
      @include('pages._cosecha_form', ['c' => null, 'cultivos' => $cultivos])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaCosecha')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cosecha</button>
      </div>
    </form>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevaCosecha')">+</button>
@endsection