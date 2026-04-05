@extends('layouts.app')
@section('title','Gastos')
@section('page_title','💰 Gastos e Insumos')
@section('back_url', route('dashboard'))
@section('content')

<div class="card mb-3" style="background:var(--marron-bg)">
  <div class="flex items-center justify-between">
    <div><p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Gasto mensual</p><p style="font-size:1.7rem;font-weight:800;color:var(--marron);">${{ number_format($totalMes,0,',','.') }}</p></div>
    <div style="text-align:right;"><p class="text-xs text-gray">Este año</p><p class="font-bold text-brown">${{ number_format($totalAnio,0,',','.') }}</p></div>
  </div>
</div>

@if($statsCat->count())
<p class="section-title">Top categorías este mes</p>
@foreach($statsCat as $sc)
<div class="mb-2">
  <div class="flex items-center justify-between mb-2"><span class="text-sm font-bold">{{ $sc->categoria }}</span><span class="text-sm font-bold text-brown">${{ number_format($sc->total,0,',','.') }}</span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:{{ $totalMes > 0 ? min(100,round($sc->total/$totalMes*100)) : 0 }}%;background:var(--marron-light);"></div></div>
</div>
@endforeach
@endif

<form method="GET" class="flex gap-2 mt-3 mb-2" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:100px;"><span class="search-icon">🔍</span><input type="text" name="q" class="form-control" placeholder="Buscar..." value="{{ request('q') }}" style="padding-left:34px;"></div>
  <input type="month" name="mes" class="form-control" style="width:130px;" value="{{ request('mes') }}" onchange="this.form.submit()">
  <select name="cat" class="form-control" style="width:120px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    @foreach($categorias as $cat)<option {{ request('cat')===$cat?'selected':'' }}>{{ $cat }}</option>@endforeach
  </select>
</form>

@if($gastos->isEmpty())
<div class="empty-state"><div class="emoji">💰</div><p>No hay gastos registrados.</p></div>
@else
@foreach($gastos as $g)
@php $icons=['Semillas'=>'🌰','Fertilizantes'=>'🌿','Plaguicidas'=>'🧴','Herramientas'=>'🔧','Combustible'=>'⛽','Mano de obra'=>'👷','Transporte'=>'🚛','Alimento animal'=>'🌾','Veterinario'=>'💉','Mantenimiento'=>'🏗️','Otros'=>'📦']; $ic=$icons[$g->categoria]??'📦'; @endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--marron-bg)">{{ $ic }}</div>
  <div class="item-body"><div class="item-title">{{ $g->descripcion }}</div><div class="item-sub">{{ $g->categoria }}{{ $g->cultivo_nombre ? ' · '.$g->cultivo_nombre : '' }}{{ $g->cantidad ? ' · '.$g->cantidad.' '.$g->unidad_cantidad : '' }}</div></div>
  <div class="flex gap-2 items-center">
    <span class="font-bold text-brown text-sm">${{ number_format($g->valor,0,',','.') }}</span>
    <button onclick="openModal('editGasto{{ $g->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('gastos.destroy',$g->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<div class="modal-overlay" id="editGasto{{ $g->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar gasto</h3>
    <form method="POST" action="{{ route('gastos.update',$g->id) }}">@csrf
      <div class="form-group"><label>Categoría *</label><select name="categoria" class="form-control" required>@foreach($categorias as $cat)<option {{ $g->categoria===$cat?'selected':'' }}>{{ $cat }}</option>@endforeach</select></div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="{{ $g->descripcion }}"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="{{ $g->cantidad }}"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" value="{{ $g->unidad_cantidad }}"></div></div>
      <div class="grid-2"><div class="form-group"><label>Valor *</label><input type="number" step="100" name="valor" class="form-control" required value="{{ $g->valor }}"></div><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ $g->fecha }}"></div></div>
      <div class="form-group"><label>Proveedor</label><input type="text" name="proveedor" class="form-control" value="{{ $g->proveedor }}"></div>
      @if($cultivos->count())<div class="form-group"><label>Cultivo asociado</label><select name="cultivo_id" class="form-control"><option value="">Sin asociar</option>@foreach($cultivos as $cv)<option value="{{ $cv->id }}" {{ $g->cultivo_id==$cv->id?'selected':'' }}>{{ $cv->nombre }}</option>@endforeach</select></div>@endif
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editGasto{{ $g->id }}')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
@endforeach
@endif

<div class="modal-overlay" id="modalNuevoGasto" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar gasto</h3>
    <form method="POST" action="{{ route('gastos.store') }}">@csrf
      <div class="form-group"><label>Categoría *</label><select name="categoria" class="form-control" required><option value="">Seleccionar...</option>@foreach($categorias as $cat)<option>{{ $cat }}</option>@endforeach</select></div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Bulto de semillas" required></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" placeholder="kg, bultos..."></div></div>
      <div class="grid-2"><div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}"></div></div>
      <div class="form-group"><label>Proveedor</label><input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor"></div>
      @if($cultivos->count())<div class="form-group"><label>Cultivo asociado</label><select name="cultivo_id" class="form-control"><option value="">Sin asociar</option>@foreach($cultivos as $cv)<option value="{{ $cv->id }}">{{ $cv->nombre }}</option>@endforeach</select></div>@endif
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoGasto')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" style="background:var(--marron);" onclick="openModal('modalNuevoGasto')">+</button>
@endsection
