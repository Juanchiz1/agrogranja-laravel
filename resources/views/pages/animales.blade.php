@extends('layouts.app')
@section('title','Animales')
@section('page_title','🐾 Mis Animales')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/animales.css') }}">
@endpush

@section('content')

{{-- AYUDA --}}
<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaAnimales')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

{{-- RESUMEN POR ESPECIE --}}
<div class="stats-grid mb-3" style="grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:8px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1.3rem;">{{ $totalActivos }}</div>
    <div class="stat-label">Total activos</div>
  </div>
  @foreach($statsPorEspecie->take(4) as $st)
  @php $em = $emojis[$st->especie] ?? '🐾'; @endphp
  <div class="stat-card" style="background:var(--verde-bg);">
    <div style="font-size:1.2rem;">{{ $em }}</div>
    <div class="stat-value text-green" style="font-size:1.1rem;">{{ $st->total }}</div>
    <div class="stat-label" style="font-size:.65rem;">{{ Str::limit($st->especie,8) }}</div>
  </div>
  @endforeach
</div>

{{-- ALERTA ATENCIÓN --}}
@if($atencion > 0)
<div class="alert-atencion">
  <span style="font-weight:700;color:#92400e;">🚨 {{ $atencion }} animal(es) necesitan atención especial</span>
  <div style="font-size:.8rem;color:#78350f;margin-top:2px;">Búscalos por el ícono 🚨 en la lista.</div>
</div>
@endif

{{-- PRÓXIMAS DOSIS --}}
@if($proximasDosis->count())
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;box-shadow:var(--shadow-sm);">
  <p style="font-size:.8rem;font-weight:700;color:#4f46e5;margin-bottom:8px;">💊 Próximas dosis (7 días)</p>
  @foreach($proximasDosis as $pd)
  <div class="proxima-dosis-card">
    <span>{{ $emojis[$pd->especie]??'🐾' }} {{ $pd->nombre_lote??$pd->especie }} · {{ $pd->titulo }}</span>
    <span style="font-weight:700;color:#4f46e5;">{{ \Carbon\Carbon::parse($pd->proxima_dosis)->format('d/m') }}</span>
  </div>
  @endforeach
</div>
@endif

{{-- FILTROS --}}
<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:100px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar lote..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <select name="especie" class="form-control" style="width:130px;" onchange="this.form.submit()">
    <option value="">Especie</option>
    @foreach($especies as $e)<option {{ request('especie')===$e?'selected':'' }} value="{{ $e }}">{{ $emojis[$e]??'🐾' }} {{ $e }}</option>@endforeach
  </select>
  @if($ubicaciones->count())
  <select name="ubicacion" class="form-control" style="width:120px;" onchange="this.form.submit()">
    <option value="">Ubicación</option>
    @foreach($ubicaciones as $ub)<option {{ request('ubicacion')===$ub?'selected':'' }}>{{ $ub }}</option>@endforeach
  </select>
  @endif
  <select name="estado" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Estado</option>
    <option {{ request('estado')==='activo'?'selected':'' }} value="activo">Activos</option>
    <option {{ request('estado')==='vendido'?'selected':'' }} value="vendido">Vendidos</option>
    <option {{ request('estado')==='muerte'?'selected':'' }} value="muerte">Bajas</option>
  </select>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

{{-- LISTADO AGRUPADO POR ESPECIE --}}
@if($animales->isEmpty())
<div class="empty-state"><div class="emoji">🐾</div><p>No hay animales registrados.</p></div>
@else
@php $agrupados = $animales->groupBy('especie'); @endphp
@foreach($agrupados as $especie => $grupo)
<div class="especie-group">
  <div class="especie-header">
    <span>{{ $emojis[$especie] ?? '🐾' }}</span>
    <span>{{ $especie }}</span>
    <span class="especie-count">{{ $grupo->count() }} lote(s) · {{ $grupo->sum('cantidad') }} animales</span>
  </div>

  @foreach($grupo as $a)
  @php
    $b = ['activo'=>'badge-green','vendido'=>'badge-brown','muerte'=>'badge-red'][$a->estado] ?? 'badge-green';
    $etapaEmoji = ['cria'=>'🐣','juvenil'=>'🐥','adulto'=>'✅'][$a->etapa_vida??'adulto'] ?? '✅';
  @endphp
  <div class="list-item" style="cursor:pointer;border-left:{{ $a->atencion_especial?'3px solid #f59e0b':($a->favorito?'3px solid #fbbf24':'none') }};" onclick="window.location='{{ route('animales.show',$a->id) }}'">
    {{-- Foto o emoji --}}
    <div class="item-icon" style="background:var(--verde-bg);position:relative;">
      @if($a->foto)
        <img src="{{ asset($a->foto) }}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
      @else
        <span style="font-size:1.4rem;">{{ $emojis[$a->especie]??'🐾' }}</span>
      @endif
    </div>

    <div class="item-body">
      <div class="item-title" style="display:flex;align-items:center;gap:5px;">
        {{ $a->nombre_lote ?: $a->especie }}
        @if($a->favorito) <span title="Favorito">⭐</span>@endif
        @if($a->atencion_especial) <span title="{{ $a->atencion_motivo }}">🚨</span>@endif
        <span title="{{ ucfirst($a->etapa_vida??'adulto') }}">{{ $etapaEmoji }}</span>
      </div>
      <div class="item-sub">
        {{ $a->cantidad }} animal(es)
        @if($a->peso_promedio) · ~{{ $a->peso_promedio }}{{ $a->unidad_peso }}@endif
        @if($a->ubicacion) · 📍{{ $a->ubicacion }}@endif
        @if($a->propietario) · 👤{{ Str::limit($a->propietario,12) }}@endif
        @if($a->produccion) · 🥛{{ $a->produccion }}@endif
      </div>
    </div>

    <div class="flex gap-2 items-center" onclick="event.stopPropagation()">
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
        <span class="badge {{ $b }}">{{ $a->estado }}</span>
        @if($a->fecha_nacimiento)
          <span style="font-size:.68rem;color:var(--text-muted);">🎂 {{ \Carbon\Carbon::parse($a->fecha_nacimiento)->diffForHumans(null,true) }}</span>
        @endif
      </div>
      <a href="{{ route('animales.show',$a->id) }}" class="btn btn-sm btn-secondary btn-icon" title="Ver detalle">👁️</a>
      <button onclick="openModal('editAnimal{{ $a->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="{{ route('animales.destroy',$a->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>

  {{-- MODAL EDITAR --}}
  <div class="modal-overlay" id="editAnimal{{ $a->id }}" style="display:none;">
    <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar animal</h3>
      <form method="POST" action="{{ route('animales.update',$a->id) }}" enctype="multipart/form-data">@csrf
        <input type="hidden" name="back" value="list">
        <div class="form-group"><label>Especie *</label>
          <select name="especie" class="form-control" required>
            @foreach($especies as $e)<option {{ $a->especie===$e?'selected':'' }}>{{ $e }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" value="{{ $a->nombre_lote }}"></div>
        <div class="grid-2">
          <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="{{ $a->cantidad }}" min="1"></div>
          <div class="form-group"><label>Estado</label>
            <select name="estado" class="form-control">
              <option {{ $a->estado==='activo'?'selected':'' }} value="activo">Activo</option>
              <option {{ $a->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option>
              <option {{ $a->estado==='muerte'?'selected':'' }} value="muerte">Baja</option>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" value="{{ $a->peso_promedio }}"></div>
          <div class="form-group"><label>Unidad</label>
            <select name="unidad_peso" class="form-control">
              <option {{ ($a->unidad_peso??'kg')==='kg'?'selected':'' }} value="kg">kg</option>
              <option {{ ($a->unidad_peso??'kg')==='lb'?'selected':'' }} value="lb">lb</option>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" value="{{ $a->ubicacion }}" placeholder="Corral, potrero..."></div>
          <div class="form-group"><label>Etapa de vida</label>
            <select name="etapa_vida" class="form-control">
              <option {{ ($a->etapa_vida??'adulto')==='cria'?'selected':'' }} value="cria">🐣 Cría</option>
              <option {{ ($a->etapa_vida??'adulto')==='juvenil'?'selected':'' }} value="juvenil">🐥 Juvenil</option>
              <option {{ ($a->etapa_vida??'adulto')==='adulto'?'selected':'' }} value="adulto">✅ Adulto</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" value="{{ $a->propietario }}" placeholder="Dueño del animal"></div>
        <div class="form-group"><label>Produce</label><input type="text" name="produccion" class="form-control" value="{{ $a->produccion }}" placeholder="Leche, Huevos, Cría..."></div>
        <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2">{{ $a->notas }}</textarea></div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editAnimal{{ $a->id }}')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  @endforeach
</div>
@endforeach
@endif

{{-- MODAL NUEVO ANIMAL --}}
<div class="modal-overlay" id="modalNuevoAnimal" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🐾 Nuevo animal</h3>
    <form method="POST" action="{{ route('animales.store') }}" enctype="multipart/form-data">@csrf
      <div class="form-group"><label>Especie *</label>
        <select name="especie" class="form-control" required id="especieNuevo" onchange="updateVentaMode()">
          <option value="">Seleccionar...</option>
          @foreach($especies as $e)<option>{{ $e }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Nombre del lote / identificación</label><input type="text" name="nombre_lote" class="form-control" placeholder="Ej: Lote bovino norte, Flor, Gallinero 1"></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" min="1"></div>
        <div class="form-group"><label>Etapa de vida</label>
          <select name="etapa_vida" class="form-control">
            <option value="cria">🐣 Cría</option>
            <option value="juvenil">🐥 Juvenil</option>
            <option value="adulto" selected>✅ Adulto</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" placeholder="0"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad_peso" class="form-control">
            <option value="kg">kg</option><option value="lb">lb</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha ingreso</label><input type="date" name="fecha_ingreso" class="form-control" value="{{ date('Y-m-d') }}"></div>
        <div class="form-group"><label>Fecha nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" placeholder="Corral, potrero..."></div>
        <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" placeholder="Nombre del dueño"></div>
      </div>
      <div class="form-group"><label>¿Qué produce?</label><input type="text" name="produccion" class="form-control" placeholder="Leche, Huevos, Lana, Cría..."></div>
      <div id="precioKiloWrap" class="form-group" style="display:none;">
        <label>Precio de venta por kg (COP)</label>
        <input type="number" step="100" name="precio_kilo" class="form-control" placeholder="Ej: 8000">
        <input type="hidden" name="vende_por_kilo" value="1">
      </div>
      <div id="precioUniWrap" class="form-group" style="display:none;">
        <label>Precio de venta por cabeza (COP)</label>
        <input type="number" step="100" name="precio_unidad" class="form-control" placeholder="Ej: 25000">
      </div>
      <div class="form-group"><label>📷 Foto del animal (opcional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoAnimal')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL AYUDA --}}
<div class="modal-overlay" id="modalAyudaAnimales" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">🐾 ¿Cómo funciona Animales?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí llevas el control completo de <strong style="color:var(--verde-dark)">todos los animales de tu finca</strong>, organizados por especie.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📋</span><div><strong>Por especie y ubicación</strong><br>Los animales se agrupan por especie. Puedes asignarles un corral, potrero o estanque.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">⭐🚨</span><div><strong>Favoritos y atención</strong><br>Marca animales favoritos con ⭐ o señala los que necesitan cuidado especial con 🚨.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">⚖️</span><div><strong>Historial de peso</strong><br>Registra pesajes periódicos para ver la evolución de crecimiento.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">💊</span><div><strong>Medicamentos y eventos</strong><br>Registra vacunas, medicamentos, traslados, partos, marcados. El sistema te avisa cuando se acerca una próxima dosis.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">👥</span><div><strong>Propietarios múltiples</strong><br>Un animal puede tener varios dueños con porcentaje de participación.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">💰</span><div><strong>Venta automática</strong><br>Al registrar la salida, el sistema calcula el valor según precio/kg o precio por cabeza y crea el ingreso automáticamente.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">🥛</span><div><strong>Producción</strong><br>Indica qué produce el animal: leche, huevos, lana, crías, etc.</div></div>
      </div>
      <p style="margin-top:14px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Toca cualquier animal para abrir su ficha completa con galería, historial y línea de tiempo.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaAnimales')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevoAnimal')">+</button>

@push('scripts')
<script>
const porKilo = ['Ganado bovino','Terneros','Cerdos','Cerdas de cría','Cabras','Ovejas','Caballos'];
function updateVentaMode() {
  const esp = document.getElementById('especieNuevo').value;
  document.getElementById('precioKiloWrap').style.display = porKilo.includes(esp) ? 'block' : 'none';
  document.getElementById('precioUniWrap').style.display  = (!porKilo.includes(esp) && esp) ? 'block' : 'none';
}
</script>
@endpush
@endsection