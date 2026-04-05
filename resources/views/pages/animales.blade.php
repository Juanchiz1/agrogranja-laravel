@extends('layouts.app')
@section('title','Animales')
@section('page_title','🐄 Mis Animales')
@section('back_url', route('dashboard'))
@section('content')

<div class="card mb-3" style="background:var(--verde-bg);display:flex;align-items:center;gap:16px;">
  <span style="font-size:2.5rem;">🐄</span>
  <div><div style="font-size:2rem;font-weight:800;color:var(--verde-dark);">{{ $totalActivos }}</div><div class="text-xs text-gray">Animales activos en total</div></div>
</div>

@if($animales->isEmpty())
<div class="empty-state"><div class="emoji">🐄</div><p>No hay animales registrados.</p></div>
@else
@php $emojis=['Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔','Conejos'=>'🐰','Cabras'=>'🐐','Ovejas'=>'🐑','Caballos'=>'🐴','Peces'=>'🐟','Patos'=>'🦆','Pavos'=>'🦃']; @endphp
@foreach($animales as $a)
@php $em=$emojis[$a->especie]??'🐾'; $b=['activo'=>'badge-green','vendido'=>'badge-brown','muerte'=>'badge-red'][$a->estado]??'badge-green'; @endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg);font-size:1.3rem;">{{ $em }}</div>
  <div class="item-body">
    <div class="item-title">{{ $a->nombre_lote ?: $a->especie }}</div>
    <div class="item-sub">{{ $a->especie }} · {{ $a->cantidad }} animales{{ $a->peso_promedio ? ' · ~'.$a->peso_promedio.$a->unidad_peso : '' }}</div>
  </div>
  <div class="flex gap-2 items-center">
    <span class="badge {{ $b }}">{{ $a->estado }}</span>
    <button onclick="openModal('editAnimal{{ $a->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('animales.destroy',$a->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<div class="modal-overlay" id="editAnimal{{ $a->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar animal</h3>
    <form method="POST" action="{{ route('animales.update',$a->id) }}">@csrf
      <div class="form-group"><label>Especie *</label><select name="especie" class="form-control" required>@foreach($especies as $e)<option {{ $a->especie===$e?'selected':'' }}>{{ $e }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" value="{{ $a->nombre_lote }}"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="{{ $a->cantidad }}" min="1"></div><div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option {{ $a->estado==='activo'?'selected':'' }} value="activo">Activo</option><option {{ $a->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option><option {{ $a->estado==='muerte'?'selected':'' }} value="muerte">Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" value="{{ $a->peso_promedio }}"></div><div class="form-group"><label>Unidad</label><select name="unidad_peso" class="form-control"><option {{ $a->unidad_peso==='kg'?'selected':'' }} value="kg">kg</option><option {{ $a->unidad_peso==='lb'?'selected':'' }} value="lb">lb</option></select></div></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control">{{ $a->notas }}</textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editAnimal{{ $a->id }}')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
@endforeach
@endif

<div class="modal-overlay" id="modalNuevoAnimal" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🐄 Nuevo animal</h3>
    <form method="POST" action="{{ route('animales.store') }}">@csrf
      <div class="form-group"><label>Especie *</label><select name="especie" class="form-control" required><option value="">Seleccionar...</option>@foreach($especies as $e)<option>{{ $e }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" placeholder="Ej: Lote bovino 1"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" min="1"></div><div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option value="activo">Activo</option><option value="vendido">Vendido</option><option value="muerte">Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" placeholder="0"></div><div class="form-group"><label>Unidad</label><select name="unidad_peso" class="form-control"><option value="kg">kg</option><option value="lb">lb</option></select></div></div>
      <div class="form-group"><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso" class="form-control"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control"></textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoAnimal')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" onclick="openModal('modalNuevoAnimal')">+</button>
@endsection
