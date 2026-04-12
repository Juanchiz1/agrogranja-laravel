@extends('layouts.app')
@section('title','Cultivos')
@section('page_title','🌱 Cultivos')
@section('back_url', route('dashboard'))

@section('content')

{{-- Botón de ayuda --}}
<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyuda')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

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
<div class="empty-state">
  <div class="emoji">🌱</div>
  <p><strong>Sin cultivos registrados.</strong></p>
  <p>Toca + para agregar el primero.</p>
  <button onclick="openModal('modalAyuda')" class="btn btn-ghost" style="margin-top:8px;">❓ ¿Cómo funciona?</button>
</div>
@else
@foreach($cultivos as $c)
@php
  $emojis = ['Maíz'=>'🌽','Yuca'=>'🍠','Plátano'=>'🍌','Arroz'=>'🌾','Frijol'=>'🫘','Tomate'=>'🍅','Cebolla'=>'🧅','Ají'=>'🌶️','Papa'=>'🥔','Aguacate'=>'🥑','Café'=>'☕','Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔'];
  $em = $emojis[$c->tipo] ?? '🌿';
  $b  = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado] ?? 'badge-green';
@endphp
<div class="list-item" style="cursor:pointer;" onclick="window.location='{{ route('cultivos.show',$c->id) }}'">
  <div class="item-icon" style="background:var(--verde-bg)">{{ $em }}</div>
  <div class="item-body">
    <div class="item-title">{{ $c->nombre }}</div>
    <div class="item-sub">{{ $c->tipo }}{{ $c->area ? ' · '.$c->area.' '.$c->unidad : '' }} · {{ \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') }}</div>
  </div>
  <div class="flex gap-2 items-center" onclick="event.stopPropagation()">
    <span class="badge {{ $b }}">{{ $c->estado }}</span>
    <a href="{{ route('cultivos.show',$c->id) }}" class="btn btn-sm btn-secondary btn-icon" title="Ver detalle">👁️</a>
    <button onclick="openModal('editCultivo{{ $c->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('cultivos.destroy',$c->id) }}" onsubmit="return confirm('¿Eliminar este cultivo?')">
      @csrf <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal-overlay" id="editCultivo{{ $c->id }}" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cultivo</h3>
    <form method="POST" action="{{ route('cultivos.update',$c->id) }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="back" value="list">
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option>@foreach($tiposCultivo as $t)<option {{ $c->tipo===$t?'selected':'' }}>{{ $t }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ $c->nombre }}"></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="{{ $c->fecha_siembra }}"></div>
        <div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option {{ $c->estado==='activo'?'selected':'' }} value="activo">Activo</option><option {{ $c->estado==='cosechado'?'selected':'' }} value="cosechado">Cosechado</option><option {{ $c->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option></select></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" value="{{ $c->area }}"></div>
        <div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option {{ $c->unidad==='hectareas'?'selected':'' }} value="hectareas">Hectáreas</option><option {{ $c->unidad==='metros2'?'selected':'' }} value="metros2">m²</option><option {{ $c->unidad==='fanegadas'?'selected':'' }} value="fanegadas">Fanegadas</option></select></div>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control">{{ $c->notas }}</textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCultivo{{ $c->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endif

{{-- Modal Nuevo --}}
<div class="modal-overlay" id="modalNuevo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌱 Nuevo cultivo</h3>
    <form method="POST" action="{{ route('cultivos.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option>@foreach($tiposCultivo as $t)<option>{{ $t }}</option>@endforeach</select></div>
      <div class="form-group"><label>Nombre / identificación *</label><input type="text" name="nombre" class="form-control" placeholder="Ej: Maíz Lote Norte" required></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="{{ date('Y-m-d') }}"></div>
        <div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option value="activo">Activo</option><option value="cosechado">Cosechado</option><option value="vendido">Vendido</option></select></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" placeholder="0.0"></div>
        <div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option value="hectareas">Hectáreas</option><option value="metros2">m²</option><option value="fanegadas">Fanegadas</option></select></div>
      </div>
      <div class="form-group"><label>Foto inicial (opcional)</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Ayuda --}}
<div class="modal-overlay" id="modalAyuda" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌱 ¿Cómo funciona Cultivos?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Este módulo es el <strong style="color:var(--verde-dark)">centro de tu finca</strong>. Aquí registras cada cultivo que tienes sembrado y puedes hacerle seguimiento completo.</p>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📷</span>
          <div><strong>Galería de fotos</strong><br>Sube fotos del cultivo en diferentes etapas para ver su evolución visual.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📅</span>
          <div><strong>Línea de tiempo</strong><br>Registra eventos como aplicaciones, riegos, podas y cambios de estado. Los gastos, tareas y cosechas aparecen automáticamente.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">💰</span>
          <div><strong>Gastos del cultivo</strong><br>Cuando registras un gasto y lo asocias a este cultivo, aparece aquí para ver cuánto estás invirtiendo.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">✅</span>
          <div><strong>Agenda y tareas</strong><br>Las tareas programadas para este cultivo (riego, fumigación, etc.) se muestran en su ficha.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🌾</span>
          <div><strong>Cosechas</strong><br>Cuando cosechas, el cultivo cambia a estado "cosechado" y queda vinculado al registro de cosecha.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📦</span>
          <div><strong>Inventario</strong><br>Los insumos usados en este cultivo (fertilizantes, semillas) aparecen en su historial.</div>
        </div>
      </div>

      <p style="margin-top:16px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Toca cualquier cultivo de la lista para abrir su ficha completa con toda la información.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyuda')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevo')">+</button>
@endsection