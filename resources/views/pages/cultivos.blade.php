@extends('layouts.app')
@section('title','Cultivos')
@section('page_title','🌱 Cultivos y Animales')
@section('back_url', route('dashboard'))

@section('content')

<div class="stats-grid mb-3">
  <div class="stat-card"><div class="stat-value text-green">{{ $stats['activo'] ?? 0 }}</div><div class="stat-label">Activos</div></div>
  <div class="stat-card"><div class="stat-value text-orange">{{ $stats['cosechado'] ?? 0 }}</div><div class="stat-label">Cosechados</div></div>
  <div class="stat-card"><div class="stat-value text-brown">{{ $stats['vendido'] ?? 0 }}</div><div class="stat-label">Vendidos</div></div>
</div>

<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <select name="estado" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Todos</option>
    <option {{ request('estado')==='activo'?'selected':'' }} value="activo">Activos</option>
    <option {{ request('estado')==='cosechado'?'selected':'' }} value="cosechado">Cosechados</option>
    <option {{ request('estado')==='vendido'?'selected':'' }} value="vendido">Vendidos</option>
  </select>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

@if($cultivos->isEmpty())
<div class="empty-state"><div class="emoji">🌱</div><p><strong>Sin cultivos registrados.</strong></p><p>Toca + para agregar el primero.</p></div>
@else
@foreach($cultivos as $c)
@php
  $emojis = ['Maíz'=>'🌽','Yuca'=>'🍠','Plátano'=>'🍌','Arroz'=>'🌾','Frijol'=>'🫘','Tomate'=>'🍅','Cebolla'=>'🧅','Ají'=>'🌶️','Papa'=>'🥔','Aguacate'=>'🥑','Café'=>'☕','Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔'];
  $em = $emojis[$c->tipo] ?? '🌿';
  $b  = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado] ?? 'badge-green';
@endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)">{{ $em }}</div>
  <div class="item-body">
    <div class="item-title">{{ $c->nombre }}</div>
    <div class="item-sub">{{ $c->tipo }}{{ $c->area ? ' · '.$c->area.' '.$c->unidad : '' }} · {{ \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') }}</div>
  </div>
  <div class="flex gap-2 items-center">
    <span class="badge {{ $b }}">{{ $c->estado }}</span>
    <button onclick="openModal('editCultivo{{ $c->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('cultivos.destroy',$c->id) }}" onsubmit="return confirm('¿Eliminar?')">
      @csrf <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editCultivo{{ $c->id }}" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cultivo</h3>
    <form method="POST" action="{{ route('cultivos.update',$c->id) }}">
      @csrf
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option>@foreach($tiposCultivo as $t)<option {{ $c->tipo===$t?'selected':'' }}>{{ $t }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ $c->nombre }}"></div>
      <div class="grid-2"><div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="{{ $c->fecha_siembra }}"></div><div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option {{ $c->estado==='activo'?'selected':'' }} value="activo">Activo</option><option {{ $c->estado==='cosechado'?'selected':'' }} value="cosechado">Cosechado</option><option {{ $c->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" value="{{ $c->area }}"></div><div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option {{ $c->unidad==='hectareas'?'selected':'' }} value="hectareas">Hectáreas</option><option {{ $c->unidad==='metros2'?'selected':'' }} value="metros2">m²</option><option {{ $c->unidad==='fanegadas'?'selected':'' }} value="fanegadas">Fanegadas</option></select></div></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control">{{ $c->notas }}</textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCultivo{{ $c->id }}')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
@endforeach
@endif

{{-- New Modal --}}
<div class="modal-overlay" id="modalNuevo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌱 Nuevo cultivo</h3>
    <form method="POST" action="{{ route('cultivos.store') }}">
      @csrf
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option>@foreach($tiposCultivo as $t)<option>{{ $t }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre / identificación *</label><input type="text" name="nombre" class="form-control" placeholder="Ej: Maíz Lote Norte" required></div>
      <div class="grid-2"><div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="{{ date('Y-m-d') }}"></div><div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option value="activo">Activo</option><option value="cosechado">Cosechado</option><option value="vendido">Vendido</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" placeholder="0.0"></div><div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option value="hectareas">Hectáreas</option><option value="metros2">m²</option><option value="fanegadas">Fanegadas</option></select></div></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevo')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevo')">+</button>
@endsection
