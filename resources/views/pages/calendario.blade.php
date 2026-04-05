@extends('layouts.app')
@section('title','Agenda')
@section('page_title','📅 Agenda y Tareas')
@section('back_url', route('dashboard'))
@section('content')

<div class="tabs">
  <button class="tab-btn {{ $tab==='proximas'?'active':'' }}" onclick="location.href='?tab=proximas'">⏳ Próximas</button>
  <button class="tab-btn {{ $tab==='tareas'?'active':'' }}"   onclick="location.href='?tab=tareas'">📋 Todas</button>
  <button class="tab-btn {{ $tab==='completadas'?'active':'' }}" onclick="location.href='?tab=completadas'">✅ Hechas</button>
</div>

@if($tab === 'tareas')
{{-- Mini Calendar --}}
@php
  $mesDate = \Carbon\Carbon::parse($mes . '-01');
  $diasEnMes = $mesDate->daysInMonth;
  $diaSemanaInicio = $mesDate->dayOfWeekIso; // 1=Mon
  $hoy = now()->toDateString();
  $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
@endphp
<div class="card mb-3" style="padding:14px;">
  <div class="flex items-center justify-between mb-3">
    <a href="?tab=tareas&mes={{ \Carbon\Carbon::parse($mes.'-01')->subMonth()->format('Y-m') }}" class="btn btn-sm btn-secondary btn-icon">‹</a>
    <span class="font-bold">{{ $meses[$mesDate->month] }} {{ $mesDate->year }}</span>
    <a href="?tab=tareas&mes={{ \Carbon\Carbon::parse($mes.'-01')->addMonth()->format('Y-m') }}" class="btn btn-sm btn-secondary btn-icon">›</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;font-size:.68rem;color:var(--gris);margin-bottom:4px;">
    @foreach(['L','M','X','J','V','S','D'] as $d)<div>{{ $d }}</div>@endforeach
  </div>
  <div class="calendar-grid">
    @for($b = 1; $b < $diaSemanaInicio; $b++)<div></div>@endfor
    @for($d = 1; $d <= $diasEnMes; $d++)
      @php $ds = $mes.'-'.str_pad($d,2,'0',STR_PAD_LEFT); $cls='cal-day'; if($ds===$hoy) $cls.=' today'; if(in_array($ds,$diasConTareas)) $cls.=' has-task'; @endphp
      <div class="{{ $cls }}">{{ $d }}</div>
    @endfor
  </div>
</div>
@endif

@php $tiposIcon = ['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝']; @endphp

@if($tareas->isEmpty())
<div class="empty-state"><div class="emoji">📅</div><p>No hay tareas aquí.</p></div>
@else
@foreach($tareas as $t)
@php $ic=$tiposIcon[$t->tipo]??'📝'; $pc=['alta'=>'var(--rojo)','media'=>'var(--naranja)','baja'=>'var(--verde-dark)'][$t->prioridad]??'var(--gris)'; @endphp
<div class="list-item" style="{{ $t->completada ? 'opacity:.55' : '' }}">
  <div class="item-icon" style="background:var(--verde-bg)">{{ $ic }}</div>
  <div class="item-body">
    <div class="item-title" style="{{ $t->completada ? 'text-decoration:line-through' : '' }}">{{ $t->titulo }}</div>
    <div class="item-sub"><span style="color:{{ $pc }};font-weight:700;text-transform:capitalize;">{{ $t->prioridad }}</span>{{ $t->hora ? ' · '.substr($t->hora,0,5) : '' }}{{ $t->cultivo_nombre ? ' · '.$t->cultivo_nombre : '' }} · {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</div>
  </div>
  <div class="flex gap-2 items-center" style="flex-shrink:0;">
    @if(!$t->completada)
    <form method="POST" action="{{ route('tareas.completar',$t->id) }}">@csrf<button class="btn btn-sm btn-secondary btn-icon">✓</button></form>
    <button onclick="openModal('editTarea{{ $t->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    @endif
    <form method="POST" action="{{ route('tareas.destroy',$t->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
@if(!$t->completada)
<div class="modal-overlay" id="editTarea{{ $t->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar tarea</h3>
    <form method="POST" action="{{ route('tareas.update',$t->id) }}">@csrf
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" required value="{{ $t->titulo }}"></div>
      <div class="grid-2"><div class="form-group"><label>Tipo</label><select name="tipo" class="form-control">@foreach($tiposIcon as $v=>$ic2)<option value="{{ $v }}" {{ $t->tipo===$v?'selected':'' }}>{{ $ic2 }} {{ ucfirst($v) }}</option>@endforeach</select></div><div class="form-group"><label>Prioridad</label><select name="prioridad" class="form-control"><option {{ $t->prioridad==='alta'?'selected':'' }} value="alta">🔴 Alta</option><option {{ $t->prioridad==='media'?'selected':'' }} value="media">🟡 Media</option><option {{ $t->prioridad==='baja'?'selected':'' }} value="baja">🟢 Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ $t->fecha }}"></div><div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control" value="{{ $t->hora }}"></div></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control">{{ $t->notas }}</textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editTarea{{ $t->id }}')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
@endif
@endforeach
@endif

<div class="modal-overlay" id="modalNuevaTarea" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">📅 Nueva tarea</h3>
    <form method="POST" action="{{ route('tareas.store') }}">@csrf
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Riego lote de maíz" required></div>
      <div class="grid-2"><div class="form-group"><label>Tipo</label><select name="tipo" class="form-control">@foreach($tiposIcon as $v=>$ic)<option value="{{ $v }}">{{ $ic }} {{ ucfirst($v) }}</option>@endforeach</select></div><div class="form-group"><label>Prioridad</label><select name="prioridad" class="form-control"><option value="alta">🔴 Alta</option><option value="media" selected>🟡 Media</option><option value="baja">🟢 Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}"></div><div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control"></div></div>
      @if($cultivos->count())<div class="form-group"><label>Cultivo relacionado</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option>@foreach($cultivos as $cv)<option value="{{ $cv->id }}">{{ $cv->nombre }}</option>@endforeach</select></div>@endif
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" placeholder="Detalles..."></textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaTarea')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" onclick="openModal('modalNuevaTarea')">+</button>
@endsection
