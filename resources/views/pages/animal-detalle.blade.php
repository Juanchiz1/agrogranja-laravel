@extends('layouts.app')
@section('title', $animal->nombre_lote ?: $animal->especie)
@section('page_title', ($emojis[$animal->especie]??'🐾').' '.($animal->nombre_lote ?: $animal->especie))
@section('back_url', route('animales.index'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/animales.css') }}">
@endpush

@section('content')
@php
  $em = $emojis[$animal->especie] ?? '🐾';
  $badgeClass = ['activo'=>'badge-green','vendido'=>'badge-brown','muerte'=>'badge-red'][$animal->estado] ?? 'badge-green';
  $balance = $totalIngresos - $totalGastos;
  $tieneProduccion = $animal->produccion && !in_array(strtolower(trim($animal->produccion)), ['carne','','null']);
@endphp

{{-- HERO --}}
<div class="animal-hero">
  @if($animal->foto)
    <img src="{{ asset($animal->foto) }}" alt="{{ $animal->nombre_lote }}" onclick="openLightbox(this.src)">
  @elseif($fotos->count())
    <img src="{{ asset($fotos->first()->ruta) }}" onclick="openLightbox(this.src)">
  @else
    <div class="animal-hero-placeholder">{{ $em }}</div>
  @endif
  <div style="position:absolute;top:12px;right:12px;display:flex;gap:6px;">
    <span class="badge {{ $badgeClass }}">{{ $animal->estado }}</span>
    @if($animal->favorito) <span style="background:#fef9c3;border-radius:99px;padding:2px 8px;font-size:.8rem;">⭐ Favorito</span>@endif
    @if($animal->atencion_especial) <span style="background:#fef2f2;border-radius:99px;padding:2px 8px;font-size:.8rem;color:#dc2626;">🚨 Atención</span>@endif
  </div>
</div>

{{-- MINI STATS --}}
<div class="stats-grid mb-3" style="grid-template-columns:repeat(3,1fr);gap:10px;">
  <div class="stat-card"><div class="stat-value text-green">{{ $animal->cantidad }}</div><div class="stat-label">animales</div></div>
  <div class="stat-card"><div class="stat-value" style="{{ $balance<0?'color:var(--rojo)':'' }}">${{ abs($balance)>=1000?round(abs($balance)/1000,1).'k':number_format(abs($balance),0,',','.') }}</div><div class="stat-label">balance</div></div>
  <div class="stat-card"><div class="stat-value">{{ $animal->peso_promedio ? $animal->peso_promedio.$animal->unidad_peso : '—' }}</div><div class="stat-label">peso prom.</div></div>
</div>

{{-- CALCULADORA DE VENTA --}}
@if($animal->estado === 'activo' && ($animal->precio_kilo || $animal->precio_unidad))
<div class="venta-calc">
  <p style="font-weight:700;color:#166534;margin-bottom:8px;">💰 Valor estimado de venta</p>
  @if($animal->vende_por_kilo && $animal->precio_kilo && $animal->peso_promedio)
    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">
      ${{ number_format($animal->precio_kilo * $animal->peso_promedio * $animal->cantidad, 0, ',', '.') }}
    </div>
    <div style="font-size:.78rem;color:#166534;">{{ $animal->cantidad }} animal(es) × {{ $animal->peso_promedio }}{{ $animal->unidad_peso }} × ${{ number_format($animal->precio_kilo,0,',','.') }}/kg</div>
  @elseif($animal->precio_unidad)
    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">
      ${{ number_format($animal->precio_unidad * $animal->cantidad, 0, ',', '.') }}
    </div>
    <div style="font-size:.78rem;color:#166534;">{{ $animal->cantidad }} animal(es) × ${{ number_format($animal->precio_unidad,0,',','.') }}/cabeza</div>
  @endif
  @if($animal->estado === 'activo')
  <button onclick="openModal('modalSalida')" class="btn btn-sm btn-primary mt-2" style="font-size:.82rem;">Registrar venta / sacrificio →</button>
  @endif
</div>
@endif

{{-- INFORMACIÓN --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Información</div>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">✏️ Editar</button>
  </div>
  <div class="info-row"><span class="info-label">Especie</span><span class="info-value">{{ $em }} {{ $animal->especie }}</span></div>
  <div class="info-row"><span class="info-label">Etapa</span><span class="info-value">{{ ['cria'=>'🐣 Cría','juvenil'=>'🐥 Juvenil','adulto'=>'✅ Adulto'][$animal->etapa_vida??'adulto'] }}</span></div>
  @if($animal->ubicacion)<div class="info-row"><span class="info-label">Ubicación</span><span class="info-value">📍 {{ $animal->ubicacion }}</span></div>@endif
  @if($animal->propietario)<div class="info-row"><span class="info-label">Propietario</span><span class="info-value">👤 {{ $animal->propietario }}</span></div>@endif
  @if($animal->produccion)<div class="info-row"><span class="info-label">Produce</span><span class="info-value">🥛 {{ $animal->produccion }}</span></div>@endif
  @if($animal->fecha_nacimiento)<div class="info-row"><span class="info-label">Nacimiento</span><span class="info-value">🎂 {{ \Carbon\Carbon::parse($animal->fecha_nacimiento)->format('d/m/Y') }} ({{ \Carbon\Carbon::parse($animal->fecha_nacimiento)->diffForHumans(null,true) }})</span></div>@endif
  @if($animal->fecha_ingreso)<div class="info-row"><span class="info-label">Ingreso a finca</span><span class="info-value">{{ \Carbon\Carbon::parse($animal->fecha_ingreso)->format('d/m/Y') }}</span></div>@endif
  @if($animal->atencion_motivo)<div style="margin-top:10px;padding:8px 10px;background:#fef2f2;border-radius:8px;font-size:.83rem;color:#dc2626;">🚨 {{ $animal->atencion_motivo }}</div>@endif
  @if($animal->notas)<div style="margin-top:10px;padding:8px 10px;background:var(--verde-bg);border-radius:8px;font-size:.83rem;color:var(--verde-dark);">📝 {{ $animal->notas }}</div>@endif

  {{-- ── ACCIONES RÁPIDAS ── --}}
  <div class="flex gap-2 mt-3" style="flex-wrap:wrap;">
    <form method="POST" action="{{ route('animales.favorito',$animal->id) }}">@csrf
      <button class="btn btn-sm btn-ghost">{{ $animal->favorito?'⭐ Quitar favorito':'⭐ Marcar favorito' }}</button>
    </form>
    <form method="POST" action="{{ route('animales.atencion',$animal->id) }}">@csrf
      <button class="btn btn-sm btn-ghost" style="{{ $animal->atencion_especial?'color:var(--rojo)':'' }}">
        {{ $animal->atencion_especial?'✅ Sin atención':'🚨 Necesita atención' }}
      </button>
    </form>
    @if($animal->estado==='activo')
    <button onclick="openModal('modalSalida')" class="btn btn-sm btn-ghost" style="color:var(--marron);">
      💰 Venta/Sacrificio
    </button>
    @endif

    {{-- Botón producción: mismo estilo que los demás --}}
    @if($tieneProduccion)
    <button onclick="openModal('modalProduccion')" class="btn btn-sm btn-ghost" style="color:var(--verde-dark);">
      🥛 Registrar producción
    </button>
    <a href="{{ route('produccion-animal.index') }}?animal_id={{ $animal->id }}"
       class="btn btn-sm btn-ghost">
      📊 Ver historial
    </a>
    @endif
  </div>
</div>

{{-- RENTABILIDAD --}}
<div class="section-card">
  <div class="section-title mb-3">💹 Rentabilidad</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:10px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Ingresos</div>
      <div style="font-weight:800;color:var(--verde-dark);">${{ number_format($totalIngresos,0,',','.') }}</div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:10px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Gastos</div>
      <div style="font-weight:800;color:var(--rojo);">${{ number_format($totalGastos,0,',','.') }}</div>
    </div>
  </div>
  <div class="flex justify-between" style="font-size:.85rem;">
    <span>Balance</span>
    <strong style="{{ $balance>=0?'color:var(--verde-dark)':'color:var(--rojo)' }}">{{ $balance>=0?'+':'-' }}${{ number_format(abs($balance),0,',','.') }}</strong>
  </div>
</div>

{{-- RENTABILIDAD ANIMAL VENDIDO --}}
@if($animal->estado === 'vendido' && $animal->valor_venta)
@php
    $fechaInicio   = $animal->fecha_ingreso ?? $animal->creado_en;
    $fechaFin      = $animal->fecha_venta   ?? now()->toDateString();
    $gastosPeriodo = DB::table('gastos')
        ->where('usuario_id', session('usuario_id'))
        ->where('animal_id', $animal->id)
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->sum('valor');
    $rentabilidad = $animal->valor_venta - $gastosPeriodo;
@endphp
<div class="section-card">
    <div class="section-title mb-3">📊 Rentabilidad estimada</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
        <div style="background:#f0faf5;border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Valor venta</p>
            <p style="font-size:1.1rem;font-weight:700;color:#1D9E75;">${{ number_format($animal->valor_venta,0,',','.') }}</p>
        </div>
        <div style="background:#fff8f0;border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Gastos</p>
            <p style="font-size:1.1rem;font-weight:700;color:#B45309;">${{ number_format($gastosPeriodo,0,',','.') }}</p>
        </div>
        <div style="background:{{ $rentabilidad>=0?'#f0faf5':'#fef2f2' }};border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Ganancia neta</p>
            <p style="font-size:1.1rem;font-weight:700;color:{{ $rentabilidad>=0?'#1D9E75':'#DC2626' }};">
                {{ $rentabilidad>=0?'+':'' }}${{ number_format($rentabilidad,0,',','.') }}
            </p>
        </div>
    </div>
</div>
@endif

{{-- GALERÍA --}}
<div class="section-card">
  <div class="section-header"><div class="section-title">📷 Galería ({{ $fotos->count() }})</div></div>
  <div class="foto-grid">
    @foreach($fotos as $f)
    <div class="foto-thumb">
      <img src="{{ asset($f->ruta) }}" onclick="openLightbox('{{ asset($f->ruta) }}')">
      <form method="POST" action="{{ route('animales.fotos.delete',[$animal->id,$f->id]) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
        <button class="foto-thumb-del">✕</button>
      </form>
    </div>
    @endforeach
    <label class="foto-upload-btn" for="fotoAnimalInput">
      <span style="font-size:1.5rem;">📷</span><span>Agregar foto</span>
    </label>
  </div>
  <form method="POST" action="{{ route('animales.fotos.upload',$animal->id) }}" enctype="multipart/form-data" id="fotoAnimalForm" style="display:none;margin-top:12px;">@csrf
    <input type="file" id="fotoAnimalInput" name="foto" accept="image/*" style="display:none;" onchange="previewAnimalFoto(this)">
    <div id="fotoAnimalPreviewWrap" style="display:none;">
      <img id="fotoAnimalPreview" style="width:100%;border-radius:10px;margin-bottom:10px;max-height:200px;object-fit:cover;">
      <div class="form-group"><label>Título (opcional)</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Julio 2026"></div>
      <div class="flex gap-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="cancelFotoA()">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">📷 Subir</button>
      </div>
    </div>
  </form>
</div>

{{-- HISTORIAL DE PESO --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">⚖️ Historial de peso ({{ $pesos->count() }})</div>
    <button onclick="openModal('modalPeso')" class="btn btn-sm btn-primary">+ Pesaje</button>
  </div>
  @if($pesos->isEmpty())
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin pesajes registrados.</p>
  @else
    @foreach($pesos->take(6) as $p)
    <div class="peso-row">
      <div><div style="font-weight:600;">{{ $p->peso }} {{ $p->unidad }}</div>@if($p->notas)<div style="font-size:.75rem;color:var(--text-secondary);">{{ $p->notas }}</div>@endif</div>
      <div style="font-size:.78rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') }}</div>
    </div>
    @endforeach
    @if($pesos->count() > 1)
    @php $primero=$pesos->last(); $ultimo=$pesos->first(); $ganancia=$ultimo->peso-$primero->peso; @endphp
    <div style="margin-top:10px;padding:8px;background:var(--verde-bg);border-radius:8px;font-size:.82rem;color:var(--verde-dark);">
      📈 Ganancia de peso: {{ $ganancia>0?'+':'' }}{{ $ganancia }} {{ $ultimo->unidad }} desde {{ \Carbon\Carbon::parse($primero->fecha)->format('d/m/Y') }}
    </div>
    @endif
  @endif
</div>

{{-- PROPIETARIOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">👥 Propietarios</div>
    <button onclick="openModal('modalPropietario')" class="btn btn-sm btn-ghost">+ Agregar</button>
  </div>
  @if($propietarios->isEmpty())
    <div style="font-size:.85rem;color:var(--text-secondary);padding:8px 0;">{{ $animal->propietario ?? 'Sin propietario asignado.' }}</div>
  @else
    @foreach($propietarios as $pr)
    <div class="propietario-row">
      <div><div style="font-weight:600;">{{ $pr->nombre }}</div>@if($pr->telefono)<div style="font-size:.75rem;"><a href="tel:{{ $pr->telefono }}" style="color:var(--verde-dark);">📞 {{ $pr->telefono }}</a></div>@endif</div>
      <div class="flex gap-2 items-center">
        <span class="badge badge-green">{{ $pr->porcentaje }}%</span>
        <form method="POST" action="{{ route('animales.propietario.delete',[$animal->id,$pr->id]) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
          <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
        </form>
      </div>
    </div>
    @endforeach
  @endif
</div>

{{-- GASTOS --}}
@if($gastos->count())
<div class="section-card">
  <div class="section-header"><div class="section-title">💰 Gastos ({{ $gastos->count() }})</div><a href="{{ route('gastos.index') }}" class="btn btn-sm btn-ghost">Ver todos →</a></div>
  @foreach($gastos->take(4) as $g)
  <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:.86rem;">
    <div><div style="font-weight:600;">{{ $g->descripcion }}</div><div style="font-size:.74rem;color:var(--text-secondary);">{{ $g->categoria }} · {{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</div></div>
    <div style="font-weight:700;color:var(--rojo);">-${{ number_format($g->valor,0,',','.') }}</div>
  </div>
  @endforeach
</div>
@endif

{{-- LÍNEA DE TIEMPO --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📜 Línea de tiempo</div>
    <button onclick="openModal('modalEvento')" class="btn btn-sm btn-primary">+ Evento</button>
  </div>
  @if($timeline->isEmpty())
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">La línea de tiempo se irá llenando con eventos, pesajes y gastos.</p>
  @else
  <div class="timeline">
    @foreach($timeline as $item)
    <div class="tl-item">
      <div class="tl-dot {{ $item['tipo'] }}"></div>
      <div class="tl-body">
        <div class="tl-title">{{ $item['titulo'] }}</div>
        @if($item['descripcion'])<div class="tl-desc">{{ $item['descripcion'] }}</div>@endif
        @if($item['dosis']??null)<div class="tl-desc" style="color:#4f46e5;">💊 Dosis: {{ $item['dosis'] }}</div>@endif
        @if($item['proxima_dosis']??null)<div class="tl-desc" style="color:#f59e0b;">📅 Próxima: {{ \Carbon\Carbon::parse($item['proxima_dosis'])->format('d/m/Y') }}</div>@endif
        <div class="tl-date">📅 {{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</div>
        @if($item['foto']??null)<img class="tl-img" src="{{ asset($item['foto']) }}" onclick="openLightbox('{{ asset($item['foto']) }}')">@endif
        @if($item['origen']==='evento')
        <form method="POST" action="{{ route('animales.eventos.delete',[$animal->id,$item['id']]) }}" onsubmit="return confirm('¿Eliminar?')" style="margin-top:6px;">@csrf
          <button class="btn btn-sm btn-ghost" style="font-size:.72rem;padding:2px 8px;color:var(--text-muted);">✕ Eliminar</button>
        </form>
        @endif
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>

<div style="margin-bottom:80px;"></div>

{{-- ════════════ MODALES ════════════ --}}

{{-- ── MODAL PRODUCCIÓN (nuevo, inline) ── --}}
@if($tieneProduccion)
<div class="modal-overlay" id="modalProduccion" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🥛 Registrar producción</h3>
    <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:14px;">
      {{ $em }} {{ $animal->nombre_lote ?? $animal->especie }}
      @if($animal->produccion) · <strong>{{ $animal->produccion }}</strong>@endif
    </p>

    <form method="POST" action="{{ route('produccion-animal.store') }}">
      @csrf
      {{-- Animal fijo --}}
      <input type="hidden" name="animal_id" value="{{ $animal->id }}">

      {{-- Período --}}
      <div class="form-group">
        <label style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">¿Para qué período?</label>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;" id="chips-m">
          <button type="button" class="chip-p active" onclick="setPm('dia',this)">Hoy</button>
          <button type="button" class="chip-p" onclick="setPm('semana',this)">Esta semana</button>
          <button type="button" class="chip-p" onclick="setPm('mes',this)">Este mes</button>
        </div>
        <input type="hidden" name="periodo" id="m-periodo" value="dia">
      </div>

      {{-- Fecha y tipo --}}
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha</label>
          <input type="date" name="fecha" id="m-fecha" class="form-control"
                 value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Tipo</label>
          <select name="tipo_produccion" id="m-tipo" class="form-control" required onchange="mActUnidad(this)">
            @php
              $prodLower = strtolower($animal->produccion ?? '');
              $tipoDefault = str_contains($prodLower,'leche') ? 'leche'
                : (str_contains($prodLower,'huevo') ? 'huevos'
                : (str_contains($prodLower,'lana') ? 'lana'
                : (str_contains($prodLower,'miel') ? 'miel' : 'otro')));
            @endphp
            <option value="leche"  {{ $tipoDefault==='leche'  ? 'selected':'' }}>🥛 Leche</option>
            <option value="huevos" {{ $tipoDefault==='huevos' ? 'selected':'' }}>🥚 Huevos</option>
            <option value="lana"   {{ $tipoDefault==='lana'   ? 'selected':'' }}>🐑 Lana</option>
            <option value="miel"   {{ $tipoDefault==='miel'   ? 'selected':'' }}>🍯 Miel</option>
            <option value="otro"   {{ $tipoDefault==='otro'   ? 'selected':'' }}>📦 Otro</option>
          </select>
        </div>
      </div>

      {{-- Cantidad y unidad --}}
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad *</label>
          <input type="number" name="cantidad" class="form-control"
                 placeholder="0" step="0.1" min="0" required>
        </div>
        <div class="form-group">
          <label>Unidad</label>
          <select name="unidad" id="m-unidad" class="form-control">
            @if($tipoDefault==='leche')
              <option>litros</option><option>ml</option>
            @elseif($tipoDefault==='huevos')
              <option>unidades</option><option>docenas</option>
            @elseif($tipoDefault==='lana')
              <option>kg</option><option>lb</option>
            @else
              <option>unidades</option><option>kg</option><option>litros</option>
            @endif
          </select>
        </div>
      </div>

      {{-- Precio y comprador --}}
      <div class="grid-2">
        <div class="form-group">
          <label>Precio unitario</label>
          <input type="number" name="precio_unitario" class="form-control"
                 placeholder="Opcional" step="100">
        </div>
        <div class="form-group">
          <label>Comprador</label>
          <input type="text" name="comprador" class="form-control" placeholder="Opcional">
        </div>
      </div>

      {{-- Vendido --}}
      <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;cursor:pointer;margin-bottom:10px;">
        <input type="checkbox" name="vendido" value="1">
        Marcar como vendido (crea ingreso automáticamente)
      </label>

      <div class="form-group">
        <label>Notas</label>
        <input type="text" name="notas" id="m-notas" class="form-control" placeholder="Opcional">
      </div>

      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalProduccion')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar</button>
      </div>
    </form>
  </div>
</div>
@endif

{{-- Modal Editar --}}
<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar animal</h3>
    <form method="POST" action="{{ route('animales.update',$animal->id) }}" enctype="multipart/form-data">@csrf
      <input type="hidden" name="back" value="detalle">
      <div class="form-group"><label>Especie *</label>
        <select name="especie" class="form-control" required>
          @foreach($especies as $e)<option {{ $animal->especie===$e?'selected':'' }}>{{ $e }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" value="{{ $animal->nombre_lote }}"></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="{{ $animal->cantidad }}"></div>
        <div class="form-group"><label>Estado</label>
          <select name="estado" class="form-control">
            <option {{ $animal->estado==='activo'?'selected':'' }} value="activo">Activo</option>
            <option {{ $animal->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option>
            <option {{ $animal->estado==='muerte'?'selected':'' }} value="muerte">Baja</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" value="{{ $animal->peso_promedio }}"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad_peso" class="form-control">
            <option {{ ($animal->unidad_peso??'kg')==='kg'?'selected':'' }} value="kg">kg</option>
            <option {{ ($animal->unidad_peso??'kg')==='lb'?'selected':'' }} value="lb">lb</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" value="{{ $animal->ubicacion }}" placeholder="Corral, potrero..."></div>
        <div class="form-group"><label>Etapa de vida</label>
          <select name="etapa_vida" class="form-control">
            <option {{ ($animal->etapa_vida??'adulto')==='cria'?'selected':'' }} value="cria">🐣 Cría</option>
            <option {{ ($animal->etapa_vida??'adulto')==='juvenil'?'selected':'' }} value="juvenil">🐥 Juvenil</option>
            <option {{ ($animal->etapa_vida??'adulto')==='adulto'?'selected':'' }} value="adulto">✅ Adulto</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" value="{{ $animal->propietario }}"></div>
      <div class="form-group"><label>Produce</label><input type="text" name="produccion" class="form-control" value="{{ $animal->produccion }}" placeholder="Leche, Huevos, Lana, Cría..."></div>
      <div class="grid-2">
        <div class="form-group"><label>Precio/kg (COP)</label><input type="number" step="100" name="precio_kilo" class="form-control" value="{{ $animal->precio_kilo }}"></div>
        <div class="form-group"><label>Precio/cabeza (COP)</label><input type="number" step="100" name="precio_unidad" class="form-control" value="{{ $animal->precio_unidad }}"></div>
      </div>
      <div class="form-group"><label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="vende_por_kilo" {{ ($animal->vende_por_kilo??1)?'checked':'' }}> Se vende por kg</label></div>
      <div class="form-group"><label>Motivo atención especial</label><input type="text" name="atencion_motivo" class="form-control" value="{{ $animal->atencion_motivo }}" placeholder="Ej: Lesión en pata derecha"></div>
      <div class="form-group"><label>Foto principal</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2">{{ $animal->notas }}</textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Pesaje --}}
<div class="modal-overlay" id="modalPeso" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">⚖️ Registrar pesaje</h3>
    <form method="POST" action="{{ route('animales.pesos.store',$animal->id) }}">@csrf
      <div class="grid-2">
        <div class="form-group"><label>Peso *</label><input type="number" step="0.1" name="peso" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad" class="form-control">
            <option value="kg" {{ ($animal->unidad_peso??'kg')==='kg'?'selected':'' }}>kg</option>
            <option value="lb" {{ ($animal->unidad_peso??'kg')==='lb'?'selected':'' }}>lb</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required></div>
      <div class="form-group"><label>Notas</label><input type="text" name="notas" class="form-control" placeholder="Observaciones..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPeso')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar pesaje</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Evento --}}
<div class="modal-overlay" id="modalEvento" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">📝 Registrar evento</h3>
    <form method="POST" action="{{ route('animales.eventos.store',$animal->id) }}" enctype="multipart/form-data">@csrf
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required id="tipoEvento" onchange="toggleDosis()">
          <option value="nota">📝 Nota</option>
          <option value="medicamento">💊 Medicamento</option>
          <option value="vacuna">💉 Vacuna</option>
          <option value="traslado">🏃 Traslado de lote</option>
          <option value="marcado">🏷️ Marcado / herrado</option>
          <option value="nacimiento">🐣 Nacimiento / parto</option>
          <option value="destete">🍼 Destete</option>
          <option value="castracion">✂️ Castración</option>
          <option value="enfermedad">🤒 Enfermedad</option>
          <option value="recuperacion">💪 Recuperación</option>
          <option value="otro">🔧 Otro</option>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="titulo" class="form-control" required placeholder="Ej: Ivermectina 1% · Dosis: 5ml"></div>
      <div id="dosisWrap" style="display:none;">
        <div class="grid-2">
          <div class="form-group"><label>Dosis / cantidad</label><input type="text" name="dosis" class="form-control" placeholder="Ej: 5ml, 1 pastilla"></div>
          <div class="form-group"><label>Próxima dosis</label><input type="date" name="proxima_dosis" class="form-control"></div>
        </div>
      </div>
      <div class="form-group"><label>Detalle</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones adicionales..."></textarea></div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required></div>
      @if(isset($personas) && $personas->count())
      <div class="form-group"><label>Realizado por (opcional)</label>
        <select name="persona_id" class="form-control">
          <option value="">Sin asignar</option>
          @foreach($personas as $per)
            <option value="{{ $per->id }}">{{ $per->nombre }}{{ $per->cargo ? ' - '.$per->cargo : '' }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="form-group"><label>Foto del evento</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEvento')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar evento</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Propietario --}}
<div class="modal-overlay" id="modalPropietario" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">👥 Agregar propietario</h3>
    <form method="POST" action="{{ route('animales.propietario.store',$animal->id) }}">@csrf
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required placeholder="Nombre del dueño"></div>
      <div class="grid-2">
        <div class="form-group"><label>% participación</label><input type="number" step="0.1" name="porcentaje" class="form-control" value="50" min="1" max="100"></div>
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
      </div>
      <div class="form-group"><label>Notas</label><input type="text" name="notas" class="form-control" placeholder="Condiciones, acuerdos..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPropietario')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Agregar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Venta/Sacrificio --}}
<div class="modal-overlay" id="modalSalida" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar salida</h3>
    <form method="POST" action="{{ route('animales.salida',$animal->id) }}">@csrf
      <div class="form-group"><label>Tipo de salida *</label>
        <select name="tipo_salida" class="form-control" required id="tipoSalida" onchange="toggleVentaFields()">
          <option value="venta">💰 Venta</option>
          <option value="sacrificio">🔪 Sacrificio</option>
        </select>
      </div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required></div>
      <div id="ventaFields">
        <div class="form-group"><label>Valor de venta (COP)</label>
          <input type="number" step="100" name="valor_venta" class="form-control"
            placeholder="{{ $valorVentaEst ? 'Estimado: $'.number_format($valorVentaEst,0,',','.') : 'Se calculará automáticamente' }}"
            value="{{ $valorVentaEst }}">
        </div>
        <div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" placeholder="Nombre del comprador"></div>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas_salida" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div style="background:#eff6ff;border-radius:8px;padding:10px;font-size:.82rem;color:#1d4ed8;margin-bottom:12px;">
        💡 Si es una <strong>venta</strong>, se creará automáticamente un ingreso en el módulo de Ingresos.
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalSalida')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Confirmar salida</button>
      </div>
    </form>
  </div>
</div>

{{-- Lightbox --}}
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
</div>

@push('scripts')
<script>
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}
function previewAnimalFoto(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => {
      document.getElementById('fotoAnimalPreview').src = e.target.result;
      document.getElementById('fotoAnimalPreviewWrap').style.display = 'block';
      document.getElementById('fotoAnimalForm').style.display = 'block';
    };
    r.readAsDataURL(input.files[0]);
  }
}
function cancelFotoA() {
  document.getElementById('fotoAnimalInput').value = '';
  document.getElementById('fotoAnimalPreviewWrap').style.display = 'none';
  document.getElementById('fotoAnimalForm').style.display = 'none';
}
function toggleDosis() {
  const tipo = document.getElementById('tipoEvento').value;
  document.getElementById('dosisWrap').style.display = ['medicamento','vacuna'].includes(tipo) ? 'block' : 'none';
}
function toggleVentaFields() {
  const tipo = document.getElementById('tipoSalida').value;
  document.getElementById('ventaFields').style.display = tipo === 'venta' ? 'block' : 'none';
}

// ── Funciones para modal de producción ─────────────────────────────
const mesesEs = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

function setPm(periodo, btn) {
  document.querySelectorAll('.chip-p').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('m-periodo').value = periodo;
  const hoy  = new Date();
  const yyyy = hoy.getFullYear();
  const mm   = String(hoy.getMonth()+1).padStart(2,'0');
  const dd   = String(hoy.getDate()).padStart(2,'0');
  const notas = document.getElementById('m-notas');

  if (periodo === 'dia') {
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    if (notas.value.startsWith('Producción semana') || notas.value.startsWith('Producción mes')) notas.value = '';
  } else if (periodo === 'semana') {
    const lunes = new Date(hoy);
    lunes.setDate(hoy.getDate() - (hoy.getDay() === 0 ? 6 : hoy.getDay()-1));
    const dl = String(lunes.getDate()).padStart(2,'0');
    const ml = String(lunes.getMonth()+1).padStart(2,'0');
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    notas.value = `Producción semana del ${dl}/${ml} al ${dd}/${mm}`;
  } else if (periodo === 'mes') {
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    notas.value = `Producción mes de ${mesesEs[hoy.getMonth()]} ${yyyy}`;
  }
}

function mActUnidad(sel) {
  const u = document.getElementById('m-unidad');
  const mapa = { leche:['litros','ml'], huevos:['unidades','docenas'], lana:['kg','lb'], miel:['kg','litros'], otro:['unidades','kg','litros'] };
  const ops = mapa[sel.value] || ['unidades'];
  u.innerHTML = ops.map(o => `<option>${o}</option>`).join('');
}
</script>
@endpush
@endsection