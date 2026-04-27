@extends('layouts.app')
@section('title','Inventario')
@section('page_title','📦 Inventario')
@section('back_url', route('dashboard'))

@push('head')
<style>
.tabs-inv{display:flex;gap:5px;margin-bottom:16px;overflow-x:auto;padding-bottom:2px;}
.tab-inv{flex-shrink:0;padding:7px 13px;border-radius:99px;border:1.5px solid var(--border);background:var(--surface);font-size:.8rem;font-weight:600;color:var(--text-secondary);white-space:nowrap;text-decoration:none;display:inline-block;}
.tab-inv.active{background:var(--verde-dark);color:#fff;border-color:var(--verde-dark);}
.insumo-card{background:var(--surface);border-radius:var(--radius-lg);padding:14px;margin-bottom:10px;box-shadow:var(--shadow-sm);}
.insumo-foto{width:46px;height:46px;border-radius:var(--radius-md);object-fit:cover;flex-shrink:0;border:1.5px solid var(--border);}
.insumo-foto-ph{width:46px;height:46px;border-radius:var(--radius-md);background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.mov-timeline{position:relative;padding-left:28px;}
.mov-timeline::before{content:'';position:absolute;left:10px;top:0;bottom:0;width:2px;background:var(--verde-light);}
.mov-item{position:relative;margin-bottom:14px;}
.mov-dot{position:absolute;left:-24px;top:4px;width:14px;height:14px;border-radius:50%;border:2px solid var(--surface);}
.mov-dot.entrada{background:var(--verde-dark);box-shadow:0 0 0 2px var(--verde-light);}
.mov-dot.salida{background:var(--rojo);box-shadow:0 0 0 2px #fca5a5;}
.mov-dot.ajuste{background:#2563eb;box-shadow:0 0 0 2px #bfdbfe;}
.mov-body{background:var(--surface);border-radius:var(--radius-md);padding:10px 12px;box-shadow:var(--shadow-sm);}
.uso-badge{display:inline-flex;align-items:center;gap:3px;font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600;}
.uso-cultivo{background:var(--verde-bg);color:var(--verde-dark);}
.uso-animal{background:#fdf4ff;color:#7e22ce;}
.uso-general{background:#f1f5f9;color:#475569;}
</style>
@endpush

@section('content')
@php $tabActual = request('tab', 'insumos'); @endphp

{{-- AYUDA --}}
<div style="text-align:right;margin-bottom:10px;">
  <button onclick="openModal('modalAyudaInv')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

{{-- ALERTAS BANNER --}}
@if($alertasStock > 0 || $porVencer > 0)
<div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;cursor:pointer;" onclick="location.href='{{ route('inventario.alertas') }}'">
  <div style="display:flex;align-items:center;justify-content:space-between;">
    <span style="font-weight:700;color:#92400e;">
      ⚠️
      @if($alertasStock > 0) {{ $alertasStock }} con stock bajo @endif
      @if($alertasStock > 0 && $porVencer > 0) · @endif
      @if($porVencer > 0) {{ $porVencer }} por vencer @endif
    </span>
    <span style="font-size:.8rem;color:#d97706;font-weight:600;">Ver alertas →</span>
  </div>
</div>
@endif

{{-- STATS --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px;gap:8px;">
  <div class="stat-card">
    <div class="stat-value text-green">{{ $totalInsumos }}</div>
    <div class="stat-label">Total</div>
  </div>
  <div class="stat-card" style="{{ $alertasStock>0 ? 'background:#fff7ed' : '' }}">
    <div class="stat-value" style="color:{{ $alertasStock>0 ? '#d97706' : 'var(--verde-dark)' }}">{{ $alertasStock }}</div>
    <div class="stat-label">Bajo mínimo</div>
  </div>
  <div class="stat-card" style="{{ $porVencer>0 ? 'background:#fef2f2' : '' }}">
    <div class="stat-value" style="color:{{ $porVencer>0 ? 'var(--rojo)' : 'var(--verde-dark)' }}">{{ $porVencer }}</div>
    <div class="stat-label">Por vencer</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($valorInventario/1000,1) }}k</div>
    <div class="stat-label">Valor</div>
  </div>
</div>

{{-- TABS --}}
<div class="tabs-inv">
  <a href="?tab=insumos" class="tab-inv {{ $tabActual==='insumos' ? 'active' : '' }}">📦 Insumos</a>
  <a href="?tab=movimientos" class="tab-inv {{ $tabActual==='movimientos' ? 'active' : '' }}">📜 Línea de tiempo</a>
</div>

{{-- ===== TAB INSUMOS ===== --}}
@if($tabActual === 'insumos')

<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <input type="hidden" name="tab" value="insumos">
  <div class="search-box" style="flex:1;min-width:100px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar insumo..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <select name="cat" class="form-control" style="width:140px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    @foreach($categorias as $grupo => $items)
      <optgroup label="{{ $grupo }}">
        @foreach($items as $cat)
          <option {{ request('cat')===$cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </optgroup>
    @endforeach
  </select>
  <select name="uso" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Uso</option>
    <option value="cultivo" {{ request('uso')==='cultivo' ? 'selected' : '' }}>🌱 Cultivos</option>
    <option value="animal"  {{ request('uso')==='animal'  ? 'selected' : '' }}>🐄 Animales</option>
    <option value="general" {{ request('uso')==='general' ? 'selected' : '' }}>📦 General</option>
  </select>
  <label class="btn btn-secondary" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
    <input type="checkbox" name="alerta" value="1" {{ request('alerta') ? 'checked' : '' }} onchange="this.form.submit()">
    ⚠️ Alertas
  </label>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

@if($insumos->isEmpty())
  <div class="empty-state"><div class="emoji">📦</div><p><strong>Sin insumos registrados.</strong></p><p>Toca + para agregar tu primer insumo.</p></div>
@else
  @foreach($insumos as $ins)
  @php
    $pct      = $ins->stock_minimo > 0 ? min(100, round($ins->cantidad_actual / $ins->stock_minimo * 100)) : 100;
    $alerta   = $ins->cantidad_actual <= $ins->stock_minimo;
    $vencePronto = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->diffInDays(now()) <= 30 && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isFuture();
    $vencido  = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isPast();
    $barColor = $alerta ? 'var(--rojo)' : ($pct < 60 ? '#f59e0b' : 'var(--verde-dark)');
    $uso      = $ins->uso_principal ?? 'general';
    $iconos   = ['Semillas'=>'🌱','Fertilizantes'=>'🌿','Plaguicidas'=>'🧴','Herbicidas'=>'🌾','Herramientas'=>'🔧','Combustible'=>'⛽','Alimento animal'=>'🌾','Medicamentos veterinarios'=>'💊','Equipos'=>'⚙️'];
    $icono    = $iconos[$ins->categoria] ?? '📦';
    $usoBadges= ['cultivo'=>'uso-cultivo','animal'=>'uso-animal','general'=>'uso-general'];
    $usoLabels= ['cultivo'=>'🌱 Cultivos','animal'=>'🐄 Animales','general'=>'📦 General'];
    $bordeStyle= $alerta ? 'border-left:3px solid var(--rojo)' : ($vencePronto ? 'border-left:3px solid #f59e0b' : '');
  @endphp

  <div class="insumo-card" style="{{ $bordeStyle }}">
    <div class="flex gap-3 items-start">
      {{-- Foto o ícono --}}
      @if(isset($ins->foto) && $ins->foto)
        <img src="{{ asset($ins->foto) }}" class="insumo-foto" onclick="openLightboxI('{{ asset($ins->foto) }}')">
      @else
        <div class="insumo-foto-ph">{{ $icono }}</div>
      @endif

      <div style="flex:1;min-width:0;">
        {{-- Nombre y badges --}}
        <div class="flex items-center gap-2 flex-wrap mb-1">
          <span style="font-weight:700;font-size:.92rem;">{{ $ins->nombre }}</span>
          <span class="badge badge-green" style="font-size:.68rem;">{{ $ins->categoria }}</span>
          <span class="uso-badge {{ $usoBadges[$uso] ?? 'uso-general' }}">{{ $usoLabels[$uso] ?? '📦 General' }}</span>
          @if($alerta)
            <span class="badge badge-red" style="font-size:.68rem;">⚠️ Stock bajo</span>
          @endif
          @if($vencido)
            <span class="badge badge-red" style="font-size:.68rem;">Vencido</span>
          @endif
          @if($vencePronto && !$vencido)
            <span class="badge badge-orange" style="font-size:.68rem;">Por vencer</span>
          @endif
        </div>

        {{-- Barra stock --}}
        <div class="flex items-center gap-2 mb-2">
          <div style="flex:1;background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;">
            <div style="height:100%;border-radius:99px;background:{{ $barColor }};width:{{ $pct }}%;"></div>
          </div>
          <span style="font-size:.75rem;white-space:nowrap;color:{{ $alerta ? 'var(--rojo)' : 'var(--text-secondary)' }};font-weight:{{ $alerta ? '700' : '400' }};">
            {{ number_format($ins->cantidad_actual,1) }} / {{ number_format($ins->stock_minimo,1) }} {{ $ins->unidad }}
          </span>
        </div>

        {{-- Meta --}}
        <div style="display:flex;flex-wrap:wrap;gap:8px;font-size:.74rem;color:var(--text-muted);">
          @if($ins->proveedor) <span>🏪 {{ $ins->proveedor }}</span> @endif
          @if($ins->precio_unitario) <span>💰 ${{ number_format($ins->precio_unitario,0,',','.') }}/{{ $ins->unidad }}</span> @endif
          @if(isset($ins->ubicacion) && $ins->ubicacion) <span>📍 {{ $ins->ubicacion }}</span> @endif
          @if($ins->fecha_vencimiento)
            <span style="color:{{ $vencido ? 'var(--rojo)' : ($vencePronto ? '#d97706' : 'var(--text-muted)') }}">
              📅 Vence: {{ \Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y') }}
            </span>
          @endif
        </div>

        {{-- Acciones --}}
        <div class="flex gap-2 mt-2">
          <button onclick="openModal('mov{{ $ins->id }}')" class="btn btn-sm btn-primary">+/- Stock</button>
          <button onclick="openModal('edit{{ $ins->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
          <form method="POST" action="{{ route('inventario.destroy',$ins->id) }}" onsubmit="return confirm('¿Eliminar?')">
            @csrf
            <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- MODAL MOVIMIENTO --}}
  <div class="modal-overlay" id="mov{{ $ins->id }}" style="display:none;">
    <div class="modal-sheet">
      <div class="modal-handle"></div>
      <h3 class="modal-title">📦 Movimiento — {{ $ins->nombre }}</h3>
      <div style="background:var(--verde-bg);border-radius:10px;padding:10px 14px;margin-bottom:14px;">
        <span style="font-size:.85rem;">Stock actual: </span>
        <strong style="color:{{ $alerta ? 'var(--rojo)' : 'var(--verde-dark)' }}">{{ number_format($ins->cantidad_actual,2) }} {{ $ins->unidad }}</strong>
        <span style="font-size:.78rem;color:var(--text-muted);"> / Mín: {{ $ins->stock_minimo }}</span>
      </div>
      <form method="POST" action="{{ route('inventario.movimiento',$ins->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo" class="form-control" required id="tipoMov{{ $ins->id }}" onchange="toggleMovFields('{{ $ins->id }}')">
            <option value="salida">Salida (se consume/reduce stock)</option>
            <option value="entrada">Entrada (compra/recepcion)</option>
            <option value="en_uso">En uso (herramienta en uso, stock no cambia)</option>
            <option value="devolucion">Devolucion (retorno de herramienta en uso)</option>
            <option value="ajuste">Ajuste de stock</option>
          </select>
          <small style="font-size:.73rem;color:var(--text-muted);">
            "En uso" es para herramientas: la pala sigue en inventario pero alguien la tiene.
          </small>
        </div>
        <div id="cantidadWrap{{ $ins->id }}">
          <div class="grid-2">
            <div class="form-group">
              <label>Cantidad *</label>
              <input type="number" step="0.01" name="cantidad" class="form-control" placeholder="0" value="1">
            </div>
            <div class="form-group">
              <label>Precio unitario</label>
              <input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Motivo / descripcion</label>
          <input type="text" name="motivo" class="form-control" placeholder="Ej: Fumigacion lote norte, Compra nueva remesa...">
        </div>
        <div class="form-group">
          <label>Persona (opcional)</label>
          @if(isset($trabajadores) && $trabajadores->count())
          <select name="persona_id" class="form-control">
            <option value="">Sin asignar</option>
            @foreach($trabajadores as $wk)
              <option value="{{ $wk->id }}">{{ $wk->nombre }}{{ $wk->cargo ? ' ('.$wk->cargo.')' : '' }}</option>
            @endforeach
          </select>
          @else
          <input type="text" name="persona" class="form-control" placeholder="Nombre de quien retira o ingresa">
          @endif
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Fecha *</label>
            <input type="date" name="fecha" class="form-control" required value="{{ date('Y-m-d') }}">
          </div>
          <div class="form-group">
            <label>Proveedor</label>
            <input type="text" name="proveedor_mov" class="form-control" placeholder="{{ $ins->proveedor ?? 'Proveedor' }}">
          </div>
        </div>
        <div class="form-group">
          <label>Asociar a cultivo</label>
          <select name="cultivo_id" class="form-control">
            <option value="">Ninguno</option>
            @foreach($cultivos as $cv)
              <option value="{{ $cv->id }}">🌱 {{ $cv->nombre }}</option>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Asociar a animal</label>
          <select name="animal_id" class="form-control">
            <option value="">Ninguno</option>
            @foreach($animales as $an)
              <option value="{{ $an->id }}">🐄 {{ $an->especie }} - {{ $an->nombre_lote }}</option>
            @endforeach
          </select>
        </div>
        <div style="background:#f0fdf4;border-radius:8px;padding:10px;border:1px solid #86efac;margin-bottom:12px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="checkbox" name="crear_gasto" value="1" style="width:18px;height:18px;">
            <span style="font-size:.87rem;color:#166534;font-weight:600;">✅ Crear gasto automáticamente (solo en entradas con precio)</span>
          </label>
        </div>
        <div class="form-group">
          <label>📷 Foto soporte (opcional)</label>
          <input type="file" name="foto_soporte" class="form-control" accept="image/*">
        </div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('mov{{ $ins->id }}')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Registrar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- MODAL EDITAR --}}
  <div class="modal-overlay" id="edit{{ $ins->id }}" style="display:none;">
    <div class="modal-sheet">
      <div class="modal-handle"></div>
      <h3 class="modal-title">✏️ Editar insumo</h3>
      <form method="POST" action="{{ route('inventario.update',$ins->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
          <label>Nombre *</label>
          <input type="text" name="nombre" class="form-control" required value="{{ $ins->nombre }}">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Categoría *</label>
            <select name="categoria" class="form-control" required>
              @foreach($categorias as $grpE => $itemsE)
                <optgroup label="{{ $grpE }}">
                  @foreach($itemsE as $catE)
                    <option {{ $ins->categoria===$catE ? 'selected' : '' }}>{{ $catE }}</option>
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </div>
          <div class="form-group">
            <label>Unidad *</label>
            <select name="unidad" class="form-control" required>
              @foreach(['kg','litros','unidades','bultos','gramos','ml','toneladas','libras'] as $uE)
                <option {{ $ins->unidad===$uE ? 'selected' : '' }}>{{ $uE }}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Stock mínimo *</label>
            <input type="number" step="0.1" name="stock_minimo" class="form-control" required value="{{ $ins->stock_minimo }}">
          </div>
          <div class="form-group">
            <label>Precio unitario</label>
            <input type="number" step="100" name="precio_unitario" class="form-control" value="{{ $ins->precio_unitario }}">
          </div>
        </div>
        <div class="form-group">
          <label>Uso principal</label>
          <select name="uso_principal" class="form-control">
            <option value="cultivo" {{ ($ins->uso_principal??'general')==='cultivo' ? 'selected' : '' }}>🌱 Cultivos</option>
            <option value="animal"  {{ ($ins->uso_principal??'general')==='animal'  ? 'selected' : '' }}>🐄 Animales</option>
            <option value="general" {{ ($ins->uso_principal??'general')==='general' ? 'selected' : '' }}>📦 General</option>
          </select>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Proveedor</label>
            <input type="text" name="proveedor" class="form-control" value="{{ $ins->proveedor }}">
          </div>
          <div class="form-group">
            <label>Ubicación</label>
            <input type="text" name="ubicacion" class="form-control" value="{{ $ins->ubicacion ?? '' }}" placeholder="Bodega...">
          </div>
        </div>
        <div class="form-group">
          <label>Fecha vencimiento</label>
          <input type="date" name="fecha_vencimiento" class="form-control" value="{{ $ins->fecha_vencimiento ?? '' }}">
        </div>
        <div class="form-group">
          <label>📷 Foto</label>
          @if(isset($ins->foto) && $ins->foto)
            <img src="{{ asset($ins->foto) }}" style="width:70px;border-radius:8px;margin-bottom:6px;display:block;">
          @endif
          <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
        <div class="form-group">
          <label>Notas</label>
          <textarea name="notas" class="form-control" rows="2">{{ $ins->notas }}</textarea>
        </div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('edit{{ $ins->id }}')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>

  @endforeach
@endif

@endif
{{-- FIN TAB INSUMOS --}}

{{-- ===== TAB MOVIMIENTOS ===== --}}
@if($tabActual === 'movimientos')
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:16px;box-shadow:var(--shadow-sm);">
  <p style="font-weight:700;font-size:.9rem;margin-bottom:16px;">📜 Historial de movimientos</p>
  @if($movimientos->isEmpty())
    <div class="empty-state"><div class="emoji">📜</div><p>Sin movimientos registrados aún.</p></div>
  @else
    <div class="mov-timeline">
      @foreach($movimientos as $mov)
      @php
        $signo    = ['entrada'=>'+','salida'=>'-','ajuste'=>'=','en_uso'=>'↗','devolucion'=>'↙'][$mov->tipo]??'';
        $colorVal = ['entrada'=>'var(--verde-dark)','salida'=>'var(--rojo)','ajuste'=>'#2563eb','en_uso'=>'#7c3aed','devolucion'=>'#0891b2'][$mov->tipo]??'var(--text-secondary)';
        $tipoLabel= ['entrada'=>'Entrada','salida'=>'Salida','ajuste'=>'Ajuste','en_uso'=>'En uso','devolucion'=>'Devolucion'][$mov->tipo]??ucfirst($mov->tipo);
      @endphp
      <div class="mov-item">
        <div class="mov-dot {{ $mov->tipo }}"></div>
        <div class="mov-body">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
            <div>
              <div style="font-weight:700;font-size:.88rem;">{{ $mov->insumo_nombre }}</div>
              <div style="font-size:.78rem;color:var(--text-secondary);margin-top:2px;">
                {{ $mov->motivo ?? ucfirst($mov->tipo) }}
                @if($mov->cultivo_nombre) · 🌱 {{ $mov->cultivo_nombre }} @endif
                @if(isset($mov->animal_nombre) && $mov->animal_nombre) · 🐄 {{ $mov->animal_especie }} - {{ $mov->animal_nombre }} @endif
              </div>
              @if(isset($mov->persona_nombre) && $mov->persona_nombre)
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">Por: {{ $mov->persona_nombre }}</div>
              @elseif(isset($mov->persona) && $mov->persona)
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">Por: {{ $mov->persona }}</div>
              @endif
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;">📅 {{ \Carbon\Carbon::parse($mov->creado_en)->format('d/m/Y H:i') }}</div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-weight:800;color:{{ $colorVal }};font-size:.95rem;">{{ $signo }}{{ number_format($mov->cantidad,1) }}</div>
              <div style="font-size:.72rem;color:var(--text-muted);">{{ $mov->unidad }}</div>
              @if($mov->precio_unitario)
                <div style="font-size:.72rem;color:var(--text-muted);">${{ number_format($mov->precio_unitario,0,',','.') }}/u</div>
              @endif
            </div>
          </div>
          @if(isset($mov->foto_soporte) && $mov->foto_soporte)
            <img src="{{ asset($mov->foto_soporte) }}" style="width:100%;max-height:120px;object-fit:cover;border-radius:6px;margin-top:8px;cursor:pointer;" onclick="openLightboxI('{{ asset($mov->foto_soporte) }}')">
          @endif
        </div>
      </div>
      @endforeach
    </div>
  @endif
</div>
@endif
{{-- FIN TAB MOVIMIENTOS --}}

{{-- MODAL NUEVO INSUMO --}}
<div class="modal-overlay" id="modalNuevoInsumo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📦 Nuevo insumo</h3>
    <form method="POST" action="{{ route('inventario.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label>Nombre del insumo *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Glifosato 480SL, Urea 46%...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Categoría *</label>
          <select name="categoria" class="form-control" required>
            <option value="">Seleccionar...</option>
            @foreach($categorias as $grpN => $itemsN)
              <optgroup label="{{ $grpN }}">
                @foreach($itemsN as $catN)
                  <option>{{ $catN }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
        </div>
        <div class="form-group">
          <label>Unidad *</label>
          <select name="unidad" class="form-control" required>
            <option value="">Seleccionar</option>
            @foreach(['kg','litros','unidades','bultos','gramos','ml','toneladas','libras'] as $uN)
              <option>{{ $uN }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad actual *</label>
          <input type="number" step="0.01" name="cantidad_actual" class="form-control" required value="0">
        </div>
        <div class="form-group">
          <label>Stock mínimo *</label>
          <input type="number" step="0.1" name="stock_minimo" class="form-control" required value="0">
        </div>
      </div>
      <div class="form-group">
        <label>Uso principal</label>
        <select name="uso_principal" class="form-control">
          <option value="cultivo">🌱 Cultivos</option>
          <option value="animal">🐄 Animales</option>
          <option value="general">📦 General</option>
        </select>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Precio unitario (COP)</label>
          <input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0">
        </div>
        <div class="form-group">
          <label>Proveedor habitual</label>
          <input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor">
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Ubicación</label>
          <input type="text" name="ubicacion" class="form-control" placeholder="Bodega, estante, nevera...">
        </div>
        <div class="form-group">
          <label>Fecha vencimiento</label>
          <input type="date" name="fecha_vencimiento" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label>📷 Foto del insumo</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2" placeholder="Instrucciones de uso, dilución, almacenamiento..."></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoInsumo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar insumo</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL AYUDA --}}
<div class="modal-overlay" id="modalAyudaInv" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📦 ¿Cómo funciona Inventario?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Controla <strong style="color:var(--verde-dark)">todos los insumos y materiales</strong> de tu finca, tanto para cultivos como para animales.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">⚠️</span><div><strong>Alertas automáticas</strong><br>Cuando el stock baja del mínimo o el insumo está por vencer, aparece una alerta.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">📜</span><div><strong>Línea de tiempo</strong><br>Cada entrada, salida o ajuste queda registrado con fecha, motivo y quién lo retiró.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">👤</span><div><strong>Quién retira</strong><br>Al registrar una salida puedes anotar el nombre del jornalero o persona.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">💰</span><div><strong>Gasto automático</strong><br>Al registrar una compra con precio, puedes crear el gasto financiero automáticamente.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">🐄🌱</span><div><strong>Cultivos y animales</strong><br>Cada movimiento puede asociarse a un cultivo o animal específico.</div></div>
      </div>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaInv')">¡Entendido!</button>
  </div>
</div>

{{-- Lightbox --}}
<div id="lightboxInv" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxInvImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>

<button class="fab" onclick="openModal('modalNuevoInsumo')">+</button>

@push('scripts')
<script>
function openLightboxI(src) {
  document.getElementById('lightboxInvImg').src = src;
  document.getElementById('lightboxInv').style.display = 'flex';
}
function toggleMovFields(id) {
  var tipo = document.getElementById('tipoMov'+id).value;
  var cantWrap = document.getElementById('cantidadWrap'+id);
  // en_uso y devolucion no muestran precio, pero si muestran cantidad (para referencia)
  if (cantWrap) {
    cantWrap.style.opacity = (tipo === 'en_uso' || tipo === 'devolucion') ? '0.5' : '1';
  }
}
</script>
@endpush
@endsection