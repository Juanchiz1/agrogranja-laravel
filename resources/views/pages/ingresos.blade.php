@extends('layouts.app')
@section('title','Ingresos')
@section('page_title','📈 Ingresos y Ventas')
@section('back_url', route('dashboard'))
@section('content')

<div class="stats-grid" style="grid-template-columns:1fr 1fr;margin-bottom:16px;">
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1.1rem;">${{ number_format($totalMes,0,',','.') }}</div><div class="stat-label">Este mes</div></div>
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1.1rem;">${{ number_format($totalAnio,0,',','.') }}</div><div class="stat-label">Este año</div></div>
</div>

@if($ingresos->isEmpty())
<div class="empty-state"><div class="emoji">📈</div><p>No hay ingresos registrados.</p></div>
@else
@foreach($ingresos as $i)
<div class="list-item">
  <div class="item-icon" style="background:#eff6ff;font-size:1.3rem;">💵</div>
  <div class="item-body">
    <div class="item-title">{{ $i->descripcion }}</div>
    <div class="item-sub">{{ \Carbon\Carbon::parse($i->fecha)->format('d/m/Y') }}{{ $i->comprador ? ' · '.$i->comprador : '' }}{{ $i->cultivo_nombre ? ' · '.$i->cultivo_nombre : '' }}</div>
  </div>
  <div class="flex gap-2 items-center">
    <span class="font-bold text-green text-sm">${{ number_format($i->valor_total,0,',','.') }}</span>
    <button onclick="openModal('editIngreso{{ $i->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('ingresos.destroy',$i->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<div class="modal-overlay" id="editIngreso{{ $i->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar ingreso</h3>
    <form method="POST" action="{{ route('ingresos.update',$i->id) }}">@csrf
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="{{ $i->descripcion }}"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="{{ $i->cantidad }}"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" value="{{ $i->unidad }}"></div></div>
      <div class="grid-2"><div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" class="form-control" value="{{ $i->precio_unitario }}"></div><div class="form-group"><label>Total *</label><input type="number" step="100" name="valor_total" class="form-control" required value="{{ $i->valor_total }}"></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ $i->fecha }}"></div><div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" value="{{ $i->comprador }}"></div></div>
      @if($cultivos->count())<div class="form-group"><label>Cultivo origen</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option>@foreach($cultivos as $cv)<option value="{{ $cv->id }}" {{ $i->cultivo_id==$cv->id?'selected':'' }}>{{ $cv->nombre }}</option>@endforeach</select></div>@endif
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editIngreso{{ $i->id }}')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
@endforeach
@endif

<div class="modal-overlay" id="modalNuevoIngreso" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💵 Registrar ingreso</h3>
    <form method="POST" action="{{ route('ingresos.store') }}">@csrf
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Venta de maíz" required></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0" oninput="calcTotal()"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" placeholder="kg, bultos..."></div></div>
      <div class="grid-2"><div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0" oninput="calcTotal()"></div><div class="form-group"><label>Total (COP) *</label><input type="number" step="100" name="valor_total" class="form-control" required placeholder="0" id="valorTotal"></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}"></div><div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" placeholder="Nombre"></div></div>
      @if($cultivos->count())<div class="form-group"><label>Cultivo origen</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option>@foreach($cultivos as $cv)<option value="{{ $cv->id }}">{{ $cv->nombre }}</option>@endforeach</select></div>@endif
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoIngreso')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" style="background:var(--verde-dark);" onclick="openModal('modalNuevoIngreso')">+</button>
@endsection
