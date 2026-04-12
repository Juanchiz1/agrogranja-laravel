@extends('layouts.app')
@section('title','Inventario')
@section('page_title','Inventario de Insumos')
@section('back_url', route('dashboard'))

@section('content')

{{-- ALERTAS BANNER --}}
@if($alertasStock > 0 || $porVencer > 0)
<div class="alert alert-warning" style="cursor:pointer;" onclick="location.href='{{ route('inventario.alertas') }}'">
  ⚠️
  @if($alertasStock > 0) {{ $alertasStock }} insumo(s) con stock bajo mínimo @endif
  @if($alertasStock > 0 && $porVencer > 0) · @endif
  @if($porVencer > 0) {{ $porVencer }} por vencer en 30 días @endif
  — <strong>Ver alertas →</strong>
</div>
@endif

{{-- MÉTRICAS --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px;">
  <div class="stat-card">
    <div class="stat-value text-green">{{ $totalInsumos }}</div>
    <div class="stat-label">Total insumos</div>
  </div>
  <div class="stat-card" style="{{ $alertasStock > 0 ? 'background:#fff7ed' : '' }}">
    <div class="stat-value" style="color:{{ $alertasStock > 0 ? 'var(--naranja)' : 'var(--verde-dark)' }}">
      {{ $alertasStock }}
    </div>
    <div class="stat-label">Bajo mínimo</div>
  </div>
  <div class="stat-card" style="{{ $porVencer > 0 ? 'background:#fef2f2' : '' }}">
    <div class="stat-value" style="color:{{ $porVencer > 0 ? 'var(--rojo)' : 'var(--verde-dark)' }}">
      {{ $porVencer }}
    </div>
    <div class="stat-label">Por vencer</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">
      ${{ number_format($valorInventario/1000,0) }}k
    </div>
    <div class="stat-label">Valor total</div>
  </div>
</div>

{{-- FILTROS --}}
<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar insumo..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <select name="cat" class="form-control" style="width:140px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    @foreach($categorias as $cat)
    <option {{ request('cat') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
    @endforeach
  </select>
  <label class="btn btn-secondary" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
    <input type="checkbox" name="alerta" value="1" {{ request('alerta') ? 'checked' : '' }} onchange="this.form.submit()">
    Solo alertas
  </label>
  <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>

{{-- LISTADO DE INSUMOS --}}
@if($insumos->isEmpty())
<div class="empty-state">
  <div class="emoji">📦</div>
  <p><strong>Sin insumos registrados.</strong></p>
  <p>Toca + para agregar tu primer insumo.</p>
</div>
@else
@foreach($insumos as $ins)
@php
  $pct       = $ins->stock_minimo > 0 ? min(100, round($ins->cantidad_actual / $ins->stock_minimo * 100)) : 100;
  $alerta    = $ins->cantidad_actual <= $ins->stock_minimo;
  $vencePronto = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->diffInDays(now()) <= 30 && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isFuture();
  $vencido     = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isPast();
  $barColor  = $alerta ? 'var(--rojo)' : ($pct < 60 ? 'var(--naranja)' : 'var(--verde-dark)');
@endphp

<div class="card mb-2" style="{{ $alerta ? 'border-left:3px solid var(--rojo);' : ($vencePronto ? 'border-left:3px solid var(--naranja);' : '') }}">
  <div class="flex items-center gap-3">

    {{-- Ícono --}}
    <div style="width:44px;height:44px;border-radius:11px;background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
      📦
    </div>

    {{-- Info principal --}}
    <div style="flex:1;min-width:0;">
      <div class="flex items-center gap-2" style="flex-wrap:wrap;">
        <span class="font-bold text-sm">{{ $ins->nombre }}</span>
        <span class="badge badge-green" style="font-size:.68rem;">{{ $ins->categoria }}</span>
        @if($alerta)<span class="badge badge-red" style="font-size:.68rem;">⚠️ Stock bajo</span>@endif
        @if($vencido)<span class="badge badge-red" style="font-size:.68rem;">Vencido</span>@endif
        @if($vencePronto && !$vencido)<span class="badge badge-orange" style="font-size:.68rem;">Por vencer</span>@endif
      </div>

      {{-- Barra de stock --}}
      <div class="flex items-center gap-2 mt-2">
        <div class="progress-bar" style="flex:1;">
          <div class="progress-fill" style="width:{{ $pct }}%;background:{{ $barColor }};"></div>
        </div>
        <span class="text-xs" style="white-space:nowrap;color:{{ $alerta ? 'var(--rojo)' : 'var(--gris)' }};">
          {{ number_format($ins->cantidad_actual,1) }} / {{ number_format($ins->stock_minimo,1) }} {{ $ins->unidad }}
        </span>
      </div>

      {{-- Meta info --}}
      <div class="text-xs text-gray mt-2" style="display:flex;gap:10px;flex-wrap:wrap;">
        @if($ins->proveedor)<span>🏪 {{ $ins->proveedor }}</span>@endif
        @if($ins->precio_unitario)<span>💰 ${{ number_format($ins->precio_unitario,0,',','.') }}/{{ $ins->unidad }}</span>@endif
        @if($ins->fecha_vencimiento)<span style="color:{{ $vencido ? 'var(--rojo)' : ($vencePronto ? 'var(--naranja)' : 'var(--gris)') }}">📅 Vence: {{ \Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y') }}</span>@endif
      </div>
    </div>

    {{-- Acciones --}}
    <div class="flex gap-2" style="flex-shrink:0;align-items:center;">
      <button onclick="openModal('movimiento{{ $ins->id }}')"
        class="btn btn-sm btn-primary" title="Registrar movimiento">
        +/-
      </button>
      <button onclick="openModal('editInsumo{{ $ins->id }}')"
        class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="{{ route('inventario.destroy',$ins->id) }}" onsubmit="return confirm('Eliminar este insumo?')">
        @csrf<button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>
</div>

{{-- Modal: movimiento --}}
<div class="modal-overlay" id="movimiento{{ $ins->id }}" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar movimiento — {{ $ins->nombre }}</h3>
    <div style="background:var(--verde-bg);border-radius:10px;padding:10px 14px;margin-bottom:14px;">
      <span class="text-sm">Stock actual:</span>
      <strong style="color:{{ $alerta ? 'var(--rojo)' : 'var(--verde-dark)' }}">
        {{ number_format($ins->cantidad_actual,2) }} {{ $ins->unidad }}
      </strong>
      <span class="text-xs text-gray"> / Mínimo: {{ $ins->stock_minimo }} {{ $ins->unidad }}</span>
    </div>
    <form method="POST" action="{{ route('inventario.movimiento',$ins->id) }}">
      @csrf
      <div class="form-group">
        <label>Tipo de movimiento *</label>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
          @foreach(['entrada'=>['🟢','Entrada','verde-bg','verde-dark'],'salida'=>['🔴','Salida','#fef2f2','var(--rojo)'],'ajuste'=>['🔵','Ajuste','#eff6ff','#2563eb']] as $tipo => [$icn,$lbl,$bg,$color])
          <label style="cursor:pointer;">
            <input type="radio" name="tipo" value="{{ $tipo }}" {{ $tipo==='entrada'?'checked':'' }} style="display:none;" class="tipo-radio">
            <div class="tipo-btn" data-tipo="{{ $tipo }}"
              style="background:var(--gris-light);border:2px solid var(--gris-border);border-radius:10px;padding:10px;text-align:center;transition:all .15s;">
              <div style="font-size:1.3rem;">{{ $icn }}</div>
              <div style="font-size:.8rem;font-weight:700;">{{ $lbl }}</div>
            </div>
          </label>
          @endforeach
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad *</label>
          <input type="number" step="0.1" name="cantidad" class="form-control" required
            placeholder="0" oninput="calcNuevoStock({{ $ins->id }}, {{ $ins->cantidad_actual }})">
        </div>
        <div class="form-group">
          <label>Precio unitario</label>
          <input type="number" step="100" name="precio_unitario" class="form-control"
            placeholder="${{ $ins->precio_unitario ?? '0' }}">
        </div>
      </div>
      <div class="form-group">
        <label>Nuevo stock estimado</label>
        <div id="nuevoStock{{ $ins->id }}" style="font-size:1.1rem;font-weight:700;color:var(--verde-dark);padding:8px;background:var(--verde-bg);border-radius:8px;">
          {{ number_format($ins->cantidad_actual,2) }} {{ $ins->unidad }}
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" required value="{{ date('Y-m-d') }}">
        </div>
        <div class="form-group">
          <label>Motivo</label>
          <input type="text" name="motivo" class="form-control" placeholder="Compra, uso, ajuste...">
        </div>
      </div>
      @if($cultivos->count())
      <div class="form-group">
        <label>Cultivo donde se usó</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          @foreach($cultivos as $cv)
          <option value="{{ $cv->id }}">{{ $cv->nombre }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('movimiento{{ $ins->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal: editar --}}
<div class="modal-overlay" id="editInsumo{{ $ins->id }}" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Editar insumo</h3>
    <form method="POST" action="{{ route('inventario.update',$ins->id) }}">
      @csrf
      @include('pages._inventario_form', ['ins' => $ins, 'categorias' => $categorias, 'nuevo' => false])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editInsumo{{ $ins->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>

@endforeach
@endif

{{-- Últimos movimientos --}}
@if($movimientos->count())
<p class="section-title mt-3">Últimos movimientos</p>
@foreach($movimientos as $mov)
@php
  $ic    = ['entrada'=>'🟢','salida'=>'🔴','ajuste'=>'🔵'][$mov->tipo] ?? '⚪';
  $color = ['entrada'=>'var(--verde-dark)','salida'=>'var(--rojo)','ajuste'=>'#2563eb'][$mov->tipo] ?? 'var(--gris)';
  $signo = $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'salida' ? '-' : '=');
@endphp
<div class="list-item">
  <div class="item-icon" style="background:var(--gris-light);font-size:1rem;">{{ $ic }}</div>
  <div class="item-body">
    <div class="item-title">{{ $mov->insumo_nombre }}</div>
    <div class="item-sub">
      {{ $mov->motivo ?? ucfirst($mov->tipo) }}
      @if($mov->cultivo_nombre) · {{ $mov->cultivo_nombre }} @endif
      · {{ \Carbon\Carbon::parse($mov->creado_en)->format('d/m/Y H:i') }}
    </div>
  </div>
  <span style="font-weight:700;color:{{ $color }};font-size:.95rem;">
    {{ $signo }}{{ number_format($mov->cantidad,1) }} {{ $mov->unidad }}
  </span>
</div>
@endforeach
@endif

{{-- Modal nuevo insumo --}}
<div class="modal-overlay" id="modalNuevoInsumo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Nuevo insumo</h3>
    <form method="POST" action="{{ route('inventario.store') }}">
      @csrf
      @include('pages._inventario_form', ['ins' => null, 'categorias' => $categorias, 'nuevo' => true])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoInsumo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar insumo</button>
      </div>
    </form>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevoInsumo')">+</button>

@push('scripts')
<script>
// Selector visual de tipo de movimiento
document.querySelectorAll('.tipo-radio').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.tipo-btn').forEach(btn => {
      btn.style.background = 'var(--gris-light)';
      btn.style.borderColor = 'var(--gris-border)';
    });
    const btn = document.querySelector(`.tipo-btn[data-tipo="${this.value}"]`);
    if (btn) {
      const colors = { entrada: ['var(--verde-bg)','var(--verde-dark)'], salida: ['#fef2f2','var(--rojo)'], ajuste: ['#eff6ff','#2563eb'] };
      const [bg, border] = colors[this.value] || ['var(--gris-light)','var(--gris-border)'];
      btn.style.background = bg;
      btn.style.borderColor = border;
    }
  });
});

// Calcular nuevo stock dinámicamente
function calcNuevoStock(id, stockActual) {
  const modal    = document.getElementById('movimiento' + id);
  const tipo     = modal.querySelector('input[name="tipo"]:checked')?.value || 'entrada';
  const cantidad = parseFloat(modal.querySelector('input[name="cantidad"]')?.value) || 0;
  const el       = document.getElementById('nuevoStock' + id);
  if (!el) return;

  let nuevo = stockActual;
  if (tipo === 'entrada') nuevo = stockActual + cantidad;
  else if (tipo === 'salida')  nuevo = Math.max(0, stockActual - cantidad);
  else if (tipo === 'ajuste')  nuevo = cantidad;

  el.textContent = nuevo.toFixed(2) + ' ' + el.dataset.unidad;
  el.style.color = nuevo <= parseFloat(el.dataset.minimo || 0) ? 'var(--rojo)' : 'var(--verde-dark)';
}

// Activar primer tipo al cargar
document.querySelectorAll('.tipo-btn[data-tipo="entrada"]').forEach(btn => {
  btn.style.background = 'var(--verde-bg)';
  btn.style.borderColor = 'var(--verde-dark)';
});
</script>
@endpush
@endsection