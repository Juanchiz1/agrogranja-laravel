@extends('layouts.app')
@section('title','Gastos')
@section('page_title','💰 Gastos')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/gastos.css') }}">
@endpush

@section('content')

{{-- Botón de ayuda --}}
<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaGastos')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

{{-- RESUMEN FINANCIERO --}}
<div class="card mb-3" style="background:var(--marron-bg)">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Gasto mensual</p>
      <p style="font-size:1.7rem;font-weight:800;color:var(--marron);">${{ number_format($totalMes,0,',','.') }}</p>
    </div>
    <div style="text-align:right;">
      <p class="text-xs text-gray">Este año</p>
      <p class="font-bold text-brown">${{ number_format($totalAnio,0,',','.') }}</p>
    </div>
  </div>
</div>

@if($statsCat->count())
<p class="section-title">Top categorías este mes</p>
@foreach($statsCat as $sc)
<div class="mb-2">
  <div class="flex items-center justify-between mb-1"><span class="text-sm font-bold">{{ $sc->categoria }}</span><span class="text-sm font-bold text-brown">${{ number_format($sc->total,0,',','.') }}</span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:{{ $totalMes>0?min(100,round($sc->total/$totalMes*100)):0 }}%;background:var(--marron-light);"></div></div>
</div>
@endforeach
@endif

{{-- ALERTAS RECURRENTES PRÓXIMAS --}}
@php
  $proximos = $recurrentes->filter(fn($r) => $r->proximo_vencimiento && \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false) >= -3)->take(3);
@endphp
@if($proximos->count())
<div style="background:#fffbeb;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;border-left:4px solid #f59e0b;">
  <p style="font-size:.82rem;font-weight:700;color:#92400e;margin-bottom:8px;">⏰ Gastos recurrentes próximos</p>
  @foreach($proximos as $r)
  @php $dias = \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false); @endphp
  <div class="flex items-center justify-between" style="margin-bottom:6px;">
    <span style="font-size:.83rem;">{{ $r->descripcion }}</span>
    <div class="flex gap-2 items-center">
      <span style="font-weight:700;font-size:.83rem;color:var(--marron);">${{ number_format($r->valor,0,',','.') }}</span>
      <form method="POST" action="{{ route('gastos.recurrente.generar',$r->id) }}">@csrf
        <button class="btn btn-sm btn-primary" style="font-size:.72rem;padding:3px 10px;">Registrar</button>
      </form>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- TABS --}}
<div class="tabs-gastos">
  <button class="tab-btn active" onclick="switchTab('gastos')">📋 Gastos</button>
  <button class="tab-btn" onclick="switchTab('recurrentes')">🔄 Recurrentes @if($recurrentes->count())<span style="background:var(--marron);color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:4px;">{{ $recurrentes->count() }}</span>@endif</button>
  <button class="tab-btn" onclick="switchTab('proveedores')">🏪 Proveedores @if($proveedores->count())<span style="background:var(--verde);color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:4px;">{{ $proveedores->count() }}</span>@endif</button>
</div>

{{-- ═══════════════ TAB: GASTOS ═══════════════ --}}
<div class="tab-panel active" id="tab-gastos">
  <form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
    <div class="search-box" style="flex:1;min-width:100px;"><span class="search-icon">🔍</span>
      <input type="text" name="q" class="form-control" placeholder="Buscar..." value="{{ request('q') }}" style="padding-left:34px;">
    </div>
    <input type="month" name="mes" class="form-control" style="width:130px;" value="{{ request('mes') }}" onchange="this.form.submit()">
    <select name="cat" class="form-control" style="width:130px;" onchange="this.form.submit()">
      <option value="">Categoría</option>
      @foreach($categorias as $grupo => $items)
        <optgroup label="{{ $grupo }}">
          @foreach($items as $cat)<option {{ request('cat')===$cat?'selected':'' }} value="{{ $cat }}">{{ $cat }}</option>@endforeach
        </optgroup>
      @endforeach
    </select>
    <select name="asociado" class="form-control" style="width:120px;" onchange="this.form.submit()">
      <option value="">Todos</option>
      <option {{ request('asociado')==='cultivo'?'selected':'' }} value="cultivo">🌱 Cultivos</option>
      <option {{ request('asociado')==='animal'?'selected':'' }} value="animal">🐄 Animales</option>
      <option {{ request('asociado')==='cosecha'?'selected':'' }} value="cosecha">🌾 Cosechas</option>
      <option {{ request('asociado')==='ninguno'?'selected':'' }} value="ninguno">Sin asociar</option>
    </select>
    <button type="submit" class="btn btn-secondary">🔍</button>
  </form>

  @if($gastos->isEmpty())
  <div class="empty-state"><div class="emoji">💰</div><p>No hay gastos registrados.</p></div>
  @else
  @foreach($gastos as $g)
  @php
    $icons=['Semillas'=>'🌰','Fertilizantes'=>'🌿','Abonos orgánicos'=>'♻️','Plaguicidas'=>'🧴','Herbicidas'=>'🌾','Fungicidas'=>'🍄','Insecticidas'=>'🐛','Riego'=>'💧','Herramientas'=>'🔧','Combustible'=>'⛽','Mano de obra'=>'👷','Jornales'=>'👷','Transporte'=>'🚛','Fletes'=>'🚛','Alimento animal'=>'🌾','Veterinario'=>'💉','Medicamentos animales'=>'💊','Vacunas'=>'💉','Mantenimiento'=>'🏗️','Maquinaria'=>'🚜','Arriendo de tierra'=>'🏡','Servicios públicos'=>'💡','Seguros'=>'🛡️','Impuestos'=>'🏛️','Otros'=>'📦'];
    $ic = $icons[$g->categoria] ?? '📦';
  @endphp
  <div class="list-item">
    <div class="item-icon" style="background:var(--marron-bg)">{{ $ic }}</div>
    <div class="item-body">
      <div class="item-title">{{ $g->descripcion }}</div>
      <div class="item-sub" style="display:flex;flex-wrap:wrap;gap:4px;margin-top:3px;">
        <span>{{ $g->categoria }}</span>
        @if($g->cultivo_nombre) <span class="asociado-chip">🌱 {{ $g->cultivo_nombre }}</span>@endif
        @if($g->animal_nombre)  <span class="asociado-chip animal">🐄 {{ $g->animal_especie }} · {{ $g->animal_nombre }}</span>@endif
        @if($g->cosecha_nombre) <span class="asociado-chip cosecha">🌾 {{ $g->cosecha_nombre }}</span>@endif
        @if($g->proveedor_nombre_bd ?? $g->proveedor) <span style="font-size:.72rem;color:var(--text-muted);">🏪 {{ $g->proveedor_nombre_bd ?? $g->proveedor }}</span>@endif
        @if($g->es_recurrente ?? false) <span style="font-size:.7rem;background:#fff7ed;color:#9a3412;padding:1px 6px;border-radius:99px;">🔄 recurrente</span>@endif
      </div>
    </div>
    <div class="flex gap-1 items-center">
      @if(isset($g->foto_factura) && $g->foto_factura)
        <img src="{{ asset($g->foto_factura) }}" class="foto-factura-thumb" onclick="openLightbox('{{ asset($g->foto_factura) }}')" title="Ver factura">
      @endif
      <div style="text-align:right;">
        <div class="font-bold text-brown text-sm">${{ number_format($g->valor,0,',','.') }}</div>
        <div style="font-size:.7rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</div>
      </div>
      <button onclick="openModal('editGasto{{ $g->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="{{ route('gastos.destroy',$g->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>

  {{-- MODAL EDITAR --}}
  <div class="modal-overlay" id="editGasto{{ $g->id }}" style="display:none;">
    <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar gasto</h3>
      <form method="POST" action="{{ route('gastos.update',$g->id) }}" enctype="multipart/form-data">@csrf
        <div class="form-group"><label>Categoría *</label>
          <select name="categoria" class="form-control" required>
            @foreach($categorias as $grupo => $items)
              <optgroup label="{{ $grupo }}">
                @foreach($items as $cat)<option {{ $g->categoria===$cat?'selected':'' }} value="{{ $cat }}">{{ $cat }}</option>@endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
        <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="{{ $g->descripcion }}"></div>
        <div class="grid-2">
          <div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="{{ $g->cantidad }}"></div>
          <div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" value="{{ $g->unidad_cantidad }}" placeholder="kg, bultos..."></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required value="{{ $g->valor }}"></div>
          <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ $g->fecha }}"></div>
        </div>
        <div class="form-group"><label>N° Factura</label><input type="text" name="factura_numero" class="form-control" value="{{ $g->factura_numero }}" placeholder="Opcional"></div>

        {{-- Proveedor --}}
        <div class="form-group"><label>Proveedor</label>
          <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_e{{ $g->id }}')">
            <option value="">-- Escribir manualmente --</option>
            @foreach($proveedores as $pv)<option value="{{ $pv->id }}" {{ $g->persona_id==$pv->id?'selected':'' }}>{{ $pv->nombre }}</option>@endforeach
          </select>
          <input type="text" name="proveedor" id="prov_text_e{{ $g->id }}" class="form-control mt-1" placeholder="O escribe el nombre" value="{{ $g->proveedor }}">
        </div>

        {{-- Asociar --}}
        <div class="form-group"><label>Asociar a cultivo</label>
          <select name="cultivo_id" class="form-control">
            <option value="">Sin asociar</option>
            @foreach($cultivos as $cv)<option value="{{ $cv->id }}" {{ $g->cultivo_id==$cv->id?'selected':'' }}>{{ $cv->nombre }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Asociar a animal</label>
          <select name="animal_id" class="form-control">
            <option value="">Sin asociar</option>
            @foreach($animales as $an)<option value="{{ $an->id }}" {{ ($g->animal_id ?? null)==$an->id?'selected':'' }}>{{ $an->especie }} - {{ $an->nombre_lote }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Asociar a cosecha</label>
          <select name="cosecha_id" class="form-control">
            <option value="">Sin asociar</option>
            @foreach($cosechas as $co)<option value="{{ $co->id }}" {{ ($g->cosecha_id ?? null)==$co->id?'selected':'' }}>{{ $co->producto }} · {{ \Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y') }}</option>@endforeach
          </select>
        </div>

        <div class="form-group"><label>Foto/Imagen de factura</label>
          @if($g->foto_factura)<img src="{{ asset($g->foto_factura) }}" style="width:80px;border-radius:8px;margin-bottom:6px;display:block;">@endif
          <input type="file" name="foto_factura" class="form-control" accept="image/*">
        </div>
        <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2">{{ $g->notas ?? '' }}</textarea></div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editGasto{{ $g->id }}')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  @endforeach
  @endif
</div>

{{-- ═══════════════ TAB: RECURRENTES ═══════════════ --}}
<div class="tab-panel" id="tab-recurrentes">
  @if($recurrentes->isEmpty())
  <div class="empty-state"><div class="emoji">🔄</div><p>No tienes gastos recurrentes.</p><p style="font-size:.85rem;">Son gastos que se repiten automáticamente cada semana, mes, etc.</p></div>
  @else
  @foreach($recurrentes as $r)
  @php
    $diasR = $r->proximo_vencimiento ? \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false) : null;
    $vcClass = 'vence-ok';
    if ($diasR !== null) {
      if ($diasR >= 0) $vcClass = 'vence-hoy';
      elseif ($diasR >= -3) $vcClass = 'vence-pronto';
    }
  @endphp
  <div class="recurrente-card">
    <div style="flex:1;">
      <div style="font-weight:700;font-size:.9rem;">{{ $r->descripcion }}</div>
      <div style="font-size:.78rem;color:var(--text-secondary);">{{ $r->categoria }} · {{ ucfirst($r->frecuencia) }}</div>
      @if($r->proveedor)<div style="font-size:.75rem;color:var(--text-muted);">🏪 {{ $r->proveedor }}</div>@endif
    </div>
    <div style="text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
      <span style="font-weight:800;color:var(--marron);">${{ number_format($r->valor,0,',','.') }}</span>
      @if($r->proximo_vencimiento)
        <span class="vencimiento-badge {{ $vcClass }}">
          @if($diasR >= 0) Vence hoy
          @elseif($diasR >= -3) Vence en {{ abs($diasR) }} días
          @else Próx. {{ \Carbon\Carbon::parse($r->proximo_vencimiento)->format('d/m') }}
          @endif
        </span>
      @endif
      <div class="flex gap-1">
        <form method="POST" action="{{ route('gastos.recurrente.generar',$r->id) }}">@csrf
          <button class="btn btn-sm btn-primary" style="font-size:.75rem;">✅ Registrar</button>
        </form>
        <form method="POST" action="{{ route('gastos.recurrente.destroy',$r->id) }}" onsubmit="return confirm('¿Desactivar?')">@csrf
          <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
        </form>
      </div>
    </div>
  </div>
  @endforeach
  @endif

  <button class="btn btn-primary btn-full mt-2" onclick="openModal('modalNuevoRecurrente')">+ Nuevo gasto recurrente</button>
</div>

{{-- ═══════════════ TAB: PROVEEDORES ═══════════════ --}}
<div class="tab-panel" id="tab-proveedores">
  @if($proveedores->isEmpty())
  <div class="empty-state"><div class="emoji">🏪</div><p>Sin proveedores guardados.</p><p style="font-size:.85rem;">Guarda tus proveedores frecuentes para seleccionarlos rápido al registrar gastos.</p></div>
  @else
  @foreach($proveedores as $pv)
  <div class="proveedor-card">
    <div style="flex:1;">
      <div style="font-weight:700;">{{ $pv->nombre }}</div>
      <div style="font-size:.78rem;color:var(--text-secondary);">{{ $pv->categoria ?? 'Sin categoría' }}</div>
      @if($pv->telefono)<div style="font-size:.75rem;"><a href="tel:{{ $pv->telefono }}" style="color:var(--verde-dark);">📞 {{ $pv->telefono }}</a></div>@endif
      @if($pv->email)<div style="font-size:.75rem;color:var(--text-muted);">📧 {{ $pv->email }}</div>@endif
    </div>
    <form method="POST" action="{{ route('gastos.proveedor.destroy',$pv->id) }}" onsubmit="return confirm('¿Eliminar proveedor?')">@csrf
      <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
  @endforeach
  @endif

  <button class="btn btn-primary btn-full mt-2" onclick="openModal('modalNuevoProveedor')">+ Nuevo proveedor</button>
</div>

{{-- ═══════════════ MODALES ═══════════════ --}}

{{-- Modal Nuevo Gasto --}}
<div class="modal-overlay" id="modalNuevoGasto" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar gasto</h3>
    <form method="POST" action="{{ route('gastos.store') }}" enctype="multipart/form-data">@csrf
      <div class="form-group"><label>Categoría *</label>
        <select name="categoria" class="form-control" required>
          <option value="">Seleccionar...</option>
          @foreach($categorias as $grupo => $items)
            <optgroup label="{{ $grupo }}">
              @foreach($items as $cat)<option>{{ $cat }}</option>@endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Bulto de urea 46%" required></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0"></div>
        <div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" placeholder="kg, bultos..."></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}"></div>
      </div>
      <div class="form-group"><label>N° Factura</label><input type="text" name="factura_numero" class="form-control" placeholder="Opcional"></div>

      {{-- Proveedor --}}
      <div class="form-group"><label>Proveedor</label>
        <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_nuevo')">
          <option value="">-- Seleccionar guardado --</option>
          @foreach($proveedores as $pv)<option value="{{ $pv->id }}">{{ $pv->nombre }}</option>@endforeach
        </select>
        <input type="text" name="proveedor" id="prov_text_nuevo" class="form-control mt-1" placeholder="O escribe el nombre">
      </div>

      {{-- Asociaciones --}}
      <div class="form-group"><label>Asociar a cultivo</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Sin asociar</option>
          @foreach($cultivos as $cv)<option value="{{ $cv->id }}">🌱 {{ $cv->nombre }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Asociar a animal</label>
        <select name="animal_id" class="form-control">
          <option value="">Sin asociar</option>
          @foreach($animales as $an)<option value="{{ $an->id }}">🐄 {{ $an->especie }} - {{ $an->nombre_lote }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Asociar a cosecha</label>
        <select name="cosecha_id" class="form-control">
          <option value="">Sin asociar</option>
          @foreach($cosechas as $co)<option value="{{ $co->id }}">🌾 {{ $co->producto }} · {{ \Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y') }}</option>@endforeach
        </select>
      </div>

      <div class="form-group"><label>📷 Foto de factura (opcional)</label><input type="file" name="foto_factura" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>

      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoGasto')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Nuevo Proveedor --}}
<div class="modal-overlay" id="modalNuevoProveedor" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🏪 Nuevo proveedor</h3>
    <form method="POST" action="{{ route('gastos.proveedor.store') }}">@csrf
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required placeholder="Ej: Agroquímicos del Valle"></div>
      <div class="form-group"><label>Qué vende / servicio</label><input type="text" name="categoria" class="form-control" placeholder="Ej: Fertilizantes y semillas"></div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Opcional"></div>
      </div>
      <div class="form-group"><label>Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Opcional"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Condiciones de pago, observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoProveedor')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar proveedor</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Nuevo Recurrente --}}
<div class="modal-overlay" id="modalNuevoRecurrente" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🔄 Gasto recurrente</h3>
    <p style="font-size:.83rem;color:var(--text-secondary);margin-bottom:14px;">Un gasto recurrente es una plantilla que te recuerda registrar pagos que se repiten (arriendo, servicios, contratos, etc.).</p>
    <form method="POST" action="{{ route('gastos.recurrente.store') }}">@csrf
      <div class="form-group"><label>Categoría *</label>
        <select name="categoria" class="form-control" required>
          @foreach($categorias as $grupo => $items)
            <optgroup label="{{ $grupo }}">
              @foreach($items as $cat)<option>{{ $cat }}</option>@endforeach
            </optgroup>
          @endforeach
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required placeholder="Ej: Arriendo del lote norte"></div>
      <div class="grid-2">
        <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Frecuencia *</label>
          <select name="frecuencia" class="form-control" required>
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
            <option value="mensual" selected>Mensual</option>
            <option value="bimestral">Bimestral</option>
            <option value="trimestral">Trimestral</option>
            <option value="anual">Anual</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Día del mes en que vence</label><input type="number" name="dia_del_mes" class="form-control" min="1" max="28" value="1" placeholder="Ej: 5 = cada día 5 del mes"></div>
      <div class="form-group"><label>Proveedor</label>
        <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_rec')">
          <option value="">-- Seleccionar --</option>
          @foreach($proveedores as $pv)<option value="{{ $pv->id }}">{{ $pv->nombre }}</option>@endforeach
        </select>
        <input type="text" name="proveedor" id="prov_text_rec" class="form-control mt-1" placeholder="O escribe el nombre">
      </div>
      <div class="form-group"><label>Asociar a cultivo (opcional)</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Sin asociar</option>
          @foreach($cultivos as $cv)<option value="{{ $cv->id }}">{{ $cv->nombre }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoRecurrente')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Crear recurrente</button>
      </div>
    </form>
  </div>
</div>

{{-- Lightbox --}}
<div id="lightboxG" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxGImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>

{{-- Modal Ayuda --}}
<div class="modal-overlay" id="modalAyudaGastos" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">💰 ¿Cómo funciona Gastos?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí registras <strong style="color:var(--marron)">todo lo que gastas en tu finca</strong>. Puedes asociar cada gasto a un cultivo, animal o cosecha para saber exactamente cuánto te cuesta cada uno.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📋</span>
          <div><strong>Gastos</strong><br>Registra cualquier egreso: insumos, jornales, combustible, veterinario, arriendo y más. Puedes adjuntar la foto de la factura.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🔄</span>
          <div><strong>Recurrentes</strong><br>Crea plantillas para gastos que se repiten (arriendo, servicios, contratos). Te avisa cuando se acerca el vencimiento y los registra en un clic.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🏪</span>
          <div><strong>Proveedores</strong><br>Guarda tus proveedores frecuentes con teléfono y email para seleccionarlos rápidamente al registrar un gasto.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🌱🐄🌾</span>
          <div><strong>Asociaciones</strong><br>Cada gasto puede vincularse a un cultivo, un animal o una cosecha. Así sabes el costo real de cada uno en su ficha de rentabilidad.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📷</span>
          <div><strong>Foto de factura</strong><br>Toma una foto de la factura física y adjúntala al gasto para tener todo documentado.</div>
        </div>
      </div>
      <p style="margin-top:16px;padding:10px;background:var(--marron-bg);border-radius:8px;color:var(--marron);font-size:.85rem;">
        💡 <strong>Tip:</strong> Usa los tabs de arriba para navegar entre gastos, recurrentes y proveedores.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaGastos')">¡Entendido!</button>
  </div>
</div>

<button class="fab" style="background:var(--marron);" onclick="openModal('modalNuevoGasto')">+</button>

@push('scripts')
<script>
function switchTab(name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  event.currentTarget.classList.add('active');
}
function syncProvText(sel, textId) {
  const opt = sel.options[sel.selectedIndex];
  if (sel.value) document.getElementById(textId).value = opt.text;
  else document.getElementById(textId).value = '';
}
function openLightbox(src) {
  const lb = document.getElementById('lightboxG');
  document.getElementById('lightboxGImg').src = src;
  lb.style.display = 'flex';
}
</script>
@endpush
@endsection