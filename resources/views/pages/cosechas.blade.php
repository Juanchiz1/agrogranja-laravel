@extends('layouts.app')
@section('title','Cosechas')
@section('page_title','🌾 Cosechas')
@section('back_url', route('dashboard'))

@push('head')
<style>
.destino-chip {
  display:inline-flex;align-items:center;gap:3px;
  font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600;
}
.dest-venta,.dest-intermediario,.dest-plaza_mercado,.dest-exportacion { background:#f0fdf4;color:#166534; }
.dest-autoconsumo { background:#eff6ff;color:#1d4ed8; }
.dest-almacenaje  { background:#fff7ed;color:#9a3412; }
.dest-semilla     { background:var(--verde-bg);color:var(--verde-dark); }
.dest-donacion    { background:#fdf4ff;color:#7e22ce; }
.merma-badge { background:#fef2f2;color:#dc2626;font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600; }
.almacen-badge { background:#fffbeb;color:#92400e;font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600; }
.ingreso-badge { background:#f0fdf4;color:#166534;font-size:.7rem;padding:2px 7px;border-radius:99px;font-weight:600; }
.cosecha-foto { width:44px;height:44px;border-radius:8px;object-fit:cover;cursor:pointer;border:1.5px solid var(--border);flex-shrink:0; }
</style>
@endpush

@section('content')

{{-- AYUDA --}}
<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaCosechas')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

{{-- STATS --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px;gap:8px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($totalMes/1000,1) }}k</div>
    <div class="stat-label">Valor mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-green">{{ $cantidadMes }}</div>
    <div class="stat-label">Este mes</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($totalAnio/1000,1) }}k</div>
    <div class="stat-label">Valor año</div>
  </div>
  <div class="stat-card" style="{{ $enAlmacen>0?'background:#fffbeb':'' }}">
    <div class="stat-value" style="{{ $enAlmacen>0?'color:#d97706':'' }}">{{ $enAlmacen }}</div>
    <div class="stat-label">En almacén</div>
  </div>
</div>

{{-- TOP PRODUCTOS --}}
@if($topProductos->count())
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
  <p style="font-size:.8rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">🏆 Top productos este año</p>
  @php $maxValor = $topProductos->max('total_valor') ?: 1; @endphp
  @foreach($topProductos as $tp)
  <div style="margin-bottom:10px;">
    <div class="flex items-center justify-between mb-1">
      <span style="font-size:.87rem;font-weight:700;">{{ $tp->producto }}</span>
      <span style="font-size:.82rem;color:var(--verde-dark);font-weight:700;">{{ number_format($tp->total_qty,1) }} · ${{ number_format($tp->total_valor,0,',','.') }}</span>
    </div>
    <div style="background:#e5e7eb;border-radius:99px;height:6px;overflow:hidden;">
      <div style="height:100%;border-radius:99px;background:var(--verde-mid);width:{{ round($tp->total_valor/$maxValor*100) }}%;"></div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- FILTROS --}}
<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:100px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar producto..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <input type="month" name="mes" class="form-control" style="width:130px;" value="{{ request('mes') }}" onchange="this.form.submit()">
  <select name="calidad" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Calidad</option>
    <option {{ request('calidad')==='excelente'?'selected':'' }} value="excelente">⭐ Excelente</option>
    <option {{ request('calidad')==='buena'?'selected':'' }} value="buena">👍 Buena</option>
    <option {{ request('calidad')==='regular'?'selected':'' }} value="regular">👌 Regular</option>
    <option {{ request('calidad')==='baja'?'selected':'' }} value="baja">👎 Baja</option>
  </select>
  <select name="destino" class="form-control" style="width:120px;" onchange="this.form.submit()">
    <option value="">Destino</option>
    @foreach($destinos as $k => $d)<option value="{{ $k }}" {{ request('destino')===$k?'selected':'' }}>{{ $d['label'] }}</option>@endforeach
  </select>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

{{-- LISTADO --}}
@if($cosechas->isEmpty())
<div class="empty-state"><div class="emoji">🌾</div><p><strong>Sin cosechas registradas.</strong></p><p>Toca + para registrar tu primera cosecha.</p></div>
@else
@php $calidadColor=['excelente'=>'badge-green','buena'=>'badge-green','regular'=>'badge-orange','baja'=>'badge-red']; @endphp
@foreach($cosechas as $cs)
<div class="list-item">
  {{-- Foto o ícono --}}
  <div class="item-icon" style="background:var(--verde-bg);font-size:1.3rem;overflow:hidden;padding:0;">
    @if(isset($cs->foto) && $cs->foto)
      <img src="{{ asset($cs->foto) }}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;" onclick="openLightboxC('{{ asset($cs->foto) }}')">
    @else
      <span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:1.4rem;">🌾</span>
    @endif
  </div>

  <div class="item-body">
    <div class="item-title">{{ $cs->producto }}</div>
    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
      <span style="font-size:.75rem;color:var(--text-secondary);">{{ number_format($cs->cantidad,1) }} {{ $cs->unidad }} · {{ \Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y') }}</span>
      @if($cs->cultivo_nombre)<span style="font-size:.72rem;color:var(--text-muted);">🌱 {{ $cs->cultivo_nombre }}</span>@endif
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
      @if($cs->destino)<span class="destino-chip dest-{{ $cs->destino }}">{{ $destinos[$cs->destino]['label'] ?? $cs->destino }}</span>@endif
      @if($cs->comprador)<span style="font-size:.72rem;color:var(--text-muted);">👤 {{ $cs->comprador }}</span>@endif
      @if(isset($cs->merma_porcentaje) && $cs->merma_porcentaje)<span class="merma-badge">-{{ $cs->merma_porcentaje }}% merma</span>@endif
      @if(isset($cs->almacen_ubicacion) && $cs->almacen_ubicacion)<span class="almacen-badge">📦 {{ $cs->almacen_ubicacion }}</span>@endif
      @if(isset($cs->ingreso_creado) && $cs->ingreso_creado)<span class="ingreso-badge">✅ Ingreso creado</span>@endif
    </div>
  </div>

  <div class="flex gap-1 items-center" style="flex-shrink:0;">
    <div style="text-align:right;">
      @if($cs->valor_estimado)
      <div class="font-bold text-green text-sm">${{ number_format($cs->valor_estimado,0,',','.') }}</div>
      @endif
      <span class="badge {{ $calidadColor[$cs->calidad]??'badge-green' }}">{{ $cs->calidad }}</span>
    </div>
    <button onclick="openModal('editCosecha{{ $cs->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="{{ route('cosechas.destroy',$cs->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
      <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="editCosecha{{ $cs->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar cosecha</h3>
    <form method="POST" action="{{ route('cosechas.update',$cs->id) }}" enctype="multipart/form-data">@csrf
      @include('pages._cosecha_form',['c'=>$cs,'cultivos'=>$cultivos,'destinos'=>$destinos,'clientes'=>$clientes,'isEdit'=>true])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCosecha{{ $cs->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endif

{{-- MODAL NUEVA COSECHA --}}
<div class="modal-overlay" id="modalNuevaCosecha" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🌾 Registrar cosecha</h3>
    <form method="POST" action="{{ route('cosechas.store') }}" enctype="multipart/form-data">@csrf
      @include('pages._cosecha_form',['c'=>null,'cultivos'=>$cultivos,'destinos'=>$destinos,'clientes'=>$clientes,'isEdit'=>false])
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaCosecha')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cosecha</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL AYUDA --}}
<div class="modal-overlay" id="modalAyudaCosechas" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">🌾 ¿Cómo funciona Cosechas?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí registras <strong style="color:var(--verde-dark)">todo lo que produces en tu finca</strong>: cultivos cosechados, huevos, leche, frutas, etc.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">🌱</span><div><strong>Cultivo de origen</strong><br>Vincula la cosecha a su cultivo. Puedes marcarlo como "cosechado" automáticamente.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">💵</span><div><strong>Ingreso automático</strong><br>Si el destino es venta, puedes crear el ingreso en el módulo financiero con un solo clic al registrar.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📦</span><div><strong>Almacenaje</strong><br>Si guardas la cosecha, registra dónde y hasta cuándo. Aparece el contador de cosechas en bodega.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📉</span><div><strong>Merma / pérdidas</strong><br>Registra el porcentaje de pérdida para calcular el valor neto real de la cosecha.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📷</span><div><strong>Foto de la cosecha</strong><br>Toma una foto del resultado para tener evidencia visual de la calidad.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">8️⃣</span><div><strong>8 tipos de destino</strong><br>Venta directa, intermediario, plaza de mercado, exportación, autoconsumo, almacenaje, semilla o donación.</div></div>
      </div>
      <p style="margin-top:14px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> También puedes registrar cosechas directamente desde la ficha de cada cultivo.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaCosechas')">¡Entendido!</button>
  </div>
</div>

{{-- Lightbox --}}
<div id="lightboxC" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxCImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>

<button class="fab" onclick="openModal('modalNuevaCosecha')">+</button>

@push('scripts')
<script>
function openLightboxC(src) {
  document.getElementById('lightboxCImg').src = src;
  document.getElementById('lightboxC').style.display = 'flex';
}
</script>
@endpush
@endsection