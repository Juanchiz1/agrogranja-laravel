@extends('layouts.app')
@section('title','Mortalidad')
@section('page_title','💀 Mortalidad Avícola')
@section('back_url', route('avicola.galpon'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/avicola.css') }}">
@endpush

@section('content')

{{-- RESUMEN POR LOTE --}}
@if($lotes->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📊 % Mortalidad acumulada por lote</div>
  @foreach($lotes as $l)
  @php $pct = $mortPorLote[$l->id] ?? 0; @endphp
  <div style="margin-bottom:10px;">
    <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:3px;">
      <span style="font-weight:600;">{{ $l->nombre_lote }}</span>
      <span style="color:{{ $pct >= 5 ? '#dc2626' : ($pct >= 2 ? '#b45309' : '#15803d') }};font-weight:700;">
        {{ $pct }}%
      </span>
    </div>
    <div style="background:#e2e8f0;border-radius:4px;height:6px;">
      <div style="width:{{ min(100,$pct*10) }}%;height:100%;border-radius:4px;
                  background:{{ $pct >= 5 ? '#dc2626' : ($pct >= 2 ? '#f59e0b' : '#16a34a') }};"></div>
    </div>
    <div style="font-size:.68rem;color:#94a3b8;margin-top:2px;">{{ $l->cantidad }} aves actuales</div>
  </div>
  @endforeach
</div>
@endif

{{-- RESUMEN POR CAUSA --}}
@if($porCausa->count())
<div class="section-card">
  <div class="section-header">
    <div class="section-title">💀 Causas — últimos 30 días</div>
    <button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
  </div>
  @foreach($porCausa as $causa => $cantidad)
  <div class="mort-row">
    <div>
      <div style="font-weight:600;font-size:.85rem;">{{ $causas[$causa] ?? $causa }}</div>
    </div>
    <span class="mort-badge">{{ $cantidad }} ave(s)</span>
  </div>
  @endforeach
</div>
@endif

{{-- HISTORIAL --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Historial — 30 días</div>
    @if($porCausa->isEmpty())
    <button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
    @endif
  </div>
  @forelse($registros as $r)
  <div class="mort-row">
    <div>
      <div style="font-weight:600;font-size:.85rem;">
        {{ $r->nombre_lote }}
        <span class="mort-badge" style="margin-left:6px;">{{ $r->cantidad }} ave(s)</span>
      </div>
      <div style="font-size:.75rem;color:#64748b;">
        {{ $causas[$r->causa] ?? $r->causa }}
        @if($r->descripcion) · {{ $r->descripcion }}@endif
      </div>
      @if($r->descartadas > 0)
      <div style="font-size:.72rem;color:#b45309;">+ {{ $r->descartadas }} descartadas</div>
      @endif
    </div>
    <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">
      {{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#94a3b8;font-size:.85rem;">
    Sin mortalidad registrada en los últimos 30 días.
    <br><button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary" style="margin-top:10px;">+ Registrar</button>
  </div>
  @endforelse
</div>

<div style="margin-bottom:80px;"></div>

{{-- MODAL --}}
<div id="modalMortalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💀 Registrar mortalidad</div>
    <form method="POST" action="{{ route('avicola.mortalidad.store') }}">
      @csrf
      <div class="form-group">
        <label>Lote *</label>
        <select name="animal_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          @foreach($lotes as $l)
          <option value="{{ $l->id }}">{{ $l->nombre_lote }} ({{ $l->cantidad }} aves)</option>
          @endforeach
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
        <div class="form-group">
          <label>Cantidad muertas *</label>
          <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
        </div>
      </div>
      <div class="form-group">
        <label>Causa *</label>
        <select name="causa" class="form-control" required>
          @foreach($causas as $key => $label)
          <option value="{{ $key }}">{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Descartadas (retiradas vivas por baja condición)</label>
        <input type="number" name="descartadas" class="form-control" min="0" value="0">
      </div>
      <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"
                  placeholder="Síntomas observados, medidas tomadas..."></textarea>
      </div>
      <div style="background:#fef2f2;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#991b1b;margin-bottom:10px;">
        ⚠️ El sistema actualizará automáticamente el conteo de aves del lote.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalMortalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });
</script>
@endpush