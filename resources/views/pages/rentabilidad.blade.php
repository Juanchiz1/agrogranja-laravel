@extends('layouts.app')
@section('title','Rentabilidad')
@section('page_title','Rentabilidad por Cultivo')
@section('back_url', route('dashboard'))

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- SELECTOR AÑO Y ORDEN --}}
<div class="flex items-center gap-2 mb-3" style="flex-wrap:wrap;">
  <a href="?anio={{ $anio-1 }}&orden={{ $orden }}" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;">{{ $anio }}</span>
  <a href="?anio={{ $anio+1 }}&orden={{ $orden }}" class="btn btn-sm btn-secondary btn-icon">›</a>
  <select onchange="location.href='?anio={{ $anio }}&orden='+this.value" class="form-control" style="width:140px;">
    @foreach(['rentabilidad'=>'Mejor balance','ingresos'=>'Mayor ingreso','gastos'=>'Mayor gasto','nombre'=>'Nombre'] as $val => $lbl)
    <option value="{{ $val }}" {{ $orden === $val ? 'selected' : '' }}>{{ $lbl }}</option>
    @endforeach
  </select>
</div>

{{-- RESUMEN GLOBAL --}}
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($totalIngresos/1000,0) }}k</div>
    <div class="stat-label">Total ingresos</div>
  </div>
  <div class="stat-card" style="background:var(--marron-bg);">
    <div class="stat-value text-brown" style="font-size:1rem;">${{ number_format($totalGastos/1000,0) }}k</div>
    <div class="stat-label">Total gastos</div>
  </div>
  <div class="stat-card" style="background:{{ $totalBalance >= 0 ? 'var(--verde-bg)' : '#fef2f2' }};">
    <div class="stat-value" style="font-size:1rem;color:{{ $totalBalance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">
      ${{ number_format(abs($totalBalance)/1000,0) }}k
    </div>
    <div class="stat-label">Balance {{ $totalBalance >= 0 ? '▲' : '▼' }}</div>
  </div>
</div>

{{-- MEJORES / PEORES --}}
@if($mejorCultivo || $peorCultivo)
<div class="grid-2 mb-3">
  @if($mejorCultivo && $mejorCultivo->rentabilidad > 0)
  <div class="card" style="background:var(--verde-bg);border-left:3px solid var(--verde-dark);">
    <p class="text-xs font-bold text-gray" style="text-transform:uppercase;margin-bottom:6px;">⭐ Mejor cultivo</p>
    <p class="font-bold">{{ $mejorCultivo->nombre }}</p>
    <p class="text-green font-bold" style="font-size:1.1rem;">${{ number_format($mejorCultivo->rentabilidad,0,',','.') }}</p>
    <p class="text-xs text-gray">ROI: {{ $mejorCultivo->roi }}%</p>
  </div>
  @endif
  @if($peorCultivo && $peorCultivo->rentabilidad < 0)
  <div class="card" style="background:#fef2f2;border-left:3px solid var(--rojo);">
    <p class="text-xs font-bold text-gray" style="text-transform:uppercase;margin-bottom:6px;">⚠️ Atención</p>
    <p class="font-bold">{{ $peorCultivo->nombre }}</p>
    <p style="color:var(--rojo);font-weight:700;font-size:1.1rem;">${{ number_format($peorCultivo->rentabilidad,0,',','.') }}</p>
    <p class="text-xs text-gray">ROI: {{ $peorCultivo->roi }}%</p>
  </div>
  @endif
</div>
@endif

{{-- GRÁFICA COMPARATIVA --}}
<div class="card mb-3">
  <p class="font-bold mb-3">Ingresos vs Gastos por cultivo</p>
  <canvas id="chartComparativo" height="220"></canvas>
</div>

{{-- TABLA DE CULTIVOS --}}
@if($datos->isEmpty())
<div class="empty-state">
  <div class="emoji">🌱</div>
  <p>No hay cultivos con datos para {{ $anio }}.</p>
</div>
@else
@foreach($datos as $d)
@php
  $positivo = $d->rentabilidad >= 0;
  $maxVal   = max($d->gastos, $d->ingresos, 1);
  $pctG     = round($d->gastos / $maxVal * 100);
  $pctI     = round($d->ingresos / $maxVal * 100);
  $estadoBadge = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$d->estado] ?? 'badge-green';
@endphp

<div class="card mb-2" style="border-left:3px solid {{ $positivo ? 'var(--verde-dark)' : 'var(--rojo)' }};">

  {{-- Cabecera del cultivo --}}
  <div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-2">
      <span style="font-size:1.2rem;">🌿</span>
      <div>
        <div class="font-bold">{{ $d->nombre }}</div>
        <div class="text-xs text-gray">{{ $d->tipo }}{{ $d->area ? ' · '.$d->area.' '.$d->unidad : '' }}</div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="badge {{ $estadoBadge }}">{{ $d->estado }}</span>
      <a href="{{ route('rentabilidad.detalle', $d->id) }}?anio={{ $anio }}"
         class="btn btn-sm btn-secondary">Ver detalle →</a>
    </div>
  </div>

  {{-- Barras de gastos vs ingresos --}}
  <div style="margin-bottom:10px;">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs text-gray">💰 Gastos</span>
      <span class="text-xs font-bold text-brown">${{ number_format($d->gastos,0,',','.') }}</span>
    </div>
    <div class="progress-bar" style="height:8px;">
      <div class="progress-fill" style="width:{{ $pctG }}%;background:var(--marron-light);"></div>
    </div>

    <div class="flex items-center justify-between mt-2 mb-2">
      <span class="text-xs text-gray">📈 Ingresos</span>
      <span class="text-xs font-bold text-green">${{ number_format($d->ingresos,0,',','.') }}</span>
    </div>
    <div class="progress-bar" style="height:8px;">
      <div class="progress-fill" style="width:{{ $pctI }}%;background:var(--verde-dark);"></div>
    </div>
  </div>

  {{-- Indicadores --}}
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px;background:var(--gris-light);border-radius:10px;">
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:{{ $positivo ? 'var(--verde-dark)' : 'var(--rojo)' }};">
        {{ $positivo ? '+' : '' }}${{ number_format($d->rentabilidad,0,',','.') }}
      </div>
      <div class="text-xs text-gray">Balance</div>
    </div>
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:{{ $d->roi >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">
        {{ $d->roi }}%
      </div>
      <div class="text-xs text-gray">ROI</div>
    </div>
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:var(--gris);">
        {{ $d->margen }}%
      </div>
      <div class="text-xs text-gray">Margen</div>
    </div>
  </div>

  {{-- Desglose de gastos por categoría --}}
  @if($d->gastosPorCategoria->count())
  <div style="margin-top:10px;">
    <p class="text-xs text-gray font-bold" style="margin-bottom:6px;">Principales gastos:</p>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      @foreach($d->gastosPorCategoria->take(4) as $cat)
      <span style="background:#fff;border:1px solid var(--gris-border);border-radius:20px;padding:2px 9px;font-size:.72rem;">
        {{ $cat->categoria }}: ${{ number_format($cat->total/1000,0) }}k
      </span>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endforeach
@endif

@push('scripts')
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';

const labels   = @json($chartLabels);
const gastos   = @json($chartGastos);
const ingresos = @json($chartIngresos);

// Acortar etiquetas largas
const labelsCortos = labels.map(l => l.length > 12 ? l.substring(0,12)+'…' : l);

new Chart(document.getElementById('chartComparativo'), {
  type: 'bar',
  data: {
    labels: labelsCortos,
    datasets: [
      {
        label: 'Ingresos',
        data: ingresos,
        backgroundColor: 'rgba(61,139,61,.75)',
        borderRadius: 5,
        borderSkipped: false,
      },
      {
        label: 'Gastos',
        data: gastos,
        backgroundColor: 'rgba(122,79,42,.65)',
        borderRadius: 5,
        borderSkipped: false,
      },
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: {
        callbacks: {
          label: ctx => ' $' + ctx.raw.toLocaleString('es-CO')
        }
      }
    },
    scales: {
      y: {
        ticks: { callback: v => '$' + (v/1000).toFixed(0) + 'k' }
      }
    }
  }
});
</script>
@endpush
@endsection