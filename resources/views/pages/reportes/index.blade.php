@extends('layouts.app')
@section('title','Reportes')
@section('page_title','Reportes y Rentabilidad')
@section('back_url', route('inicio'))

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- FILTROS --}}
<form method="GET" action="{{ route('reportes.index') }}"
      style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center;">
  <select name="anio" class="form-control" style="max-width:100px;" onchange="this.form.submit()">
    @foreach($aniosDisponibles as $a)
    <option value="{{ $a }}" {{ $anio == $a ? 'selected' : '' }}>{{ $a }}</option>
    @endforeach
  </select>
  <select name="mes" class="form-control" style="max-width:130px;" onchange="this.form.submit()">
    <option value="">Año completo</option>
    @foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $i => $nm)
    <option value="{{ $i+1 }}" {{ $mes == $i+1 ? 'selected' : '' }}>{{ $nm }}</option>
    @endforeach
  </select>
  <span style="font-size:.78rem;color:#64748b;">
    {{ \Carbon\Carbon::parse($inicio)->format('d/m/Y') }}
    — {{ \Carbon\Carbon::parse($fin)->format('d/m/Y') }}
  </span>
</form>

{{-- TOTALES GENERALES --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;">
  <div style="background:#f0fdf4;border-radius:14px;padding:14px;text-align:center;border-left:4px solid #16a34a;">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Ingresos</div>
    <div style="font-size:1.3rem;font-weight:900;color:#16a34a;">
      ${{ $totalIngresos >= 1000000 ? round($totalIngresos/1000000,1).'M' : number_format($totalIngresos/1000,0).'k' }}
    </div>
  </div>
  <div style="background:#fef2f2;border-radius:14px;padding:14px;text-align:center;border-left:4px solid #dc2626;">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Gastos</div>
    <div style="font-size:1.3rem;font-weight:900;color:#dc2626;">
      ${{ $totalGastos >= 1000000 ? round($totalGastos/1000000,1).'M' : number_format($totalGastos/1000,0).'k' }}
    </div>
  </div>
  <div style="background:{{ $balanceTotal >= 0 ? '#f0fdf4' : '#fef2f2' }};
              border-radius:14px;padding:14px;text-align:center;
              border-left:4px solid {{ $balanceTotal >= 0 ? '#16a34a' : '#dc2626' }};">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Balance</div>
    <div style="font-size:1.3rem;font-weight:900;color:{{ $balanceTotal >= 0 ? '#16a34a' : '#dc2626' }};">
      {{ $balanceTotal >= 0 ? '+' : '-' }}${{ abs($balanceTotal) >= 1000000 ? round(abs($balanceTotal)/1000000,1).'M' : number_format(abs($balanceTotal)/1000,0).'k' }}
    </div>
  </div>
</div>

{{-- RENTABILIDAD POR LÍNEA --}}
@if(count($rentabilidadLineas) > 0)
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">Rentabilidad por linea productiva</div>

  @php $maxAbsRent = max(1, max(array_map(fn($r) => abs($r['rentabilidad']), $rentabilidadLineas))); @endphp

  @foreach($rentabilidadLineas as $lineaKey => $r)
  @php
    $pctBarra = round((abs($r['rentabilidad']) / $maxAbsRent) * 100);
    $colorBarra = $r['es_rentable'] ? '#16a34a' : '#dc2626';
    $margenColor = $r['margen'] >= 20 ? '#15803d' : ($r['margen'] >= 0 ? '#b45309' : '#dc2626');
  @endphp
  <div style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
      <div>
        <span style="font-size:.95rem;font-weight:700;">
          {{ $r['emoji'] }} {{ $r['nombre'] }}
        </span>
        <span style="font-size:.72rem;color:#64748b;margin-left:8px;">
          Margen: <strong style="color:{{ $margenColor }};">{{ $r['margen'] }}%</strong>
        </span>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.95rem;font-weight:800;color:{{ $r['es_rentable'] ? '#16a34a' : '#dc2626' }};">
          {{ $r['es_rentable'] ? '+' : '' }}${{ number_format($r['rentabilidad']/1000,1) }}k
        </div>
        <div style="font-size:.68rem;color:#94a3b8;">rentabilidad neta</div>
      </div>
    </div>
    <div style="background:#e2e8f0;border-radius:6px;height:8px;margin-bottom:4px;overflow:hidden;">
      <div style="width:{{ $pctBarra }}%;height:100%;border-radius:6px;background:{{ $colorBarra }};"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#64748b;">
      <span style="color:#16a34a;">Ingresos: ${{ number_format($r['ingresos']/1000,1) }}k</span>
      <span style="color:#dc2626;">Gastos: ${{ number_format($r['gastos']/1000,1) }}k</span>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- KPIs ESPECÍFICOS POR LÍNEA --}}
@if(count($kpisEspecificos) > 0)
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">KPIs especificos por linea</div>
  @foreach($kpisEspecificos as $lk => $k)
  <div style="margin-bottom:14px;">
    <div style="font-weight:700;font-size:.88rem;margin-bottom:8px;">
      {{ $k['emoji'] }} {{ $k['titulo'] }}
    </div>
    @foreach($k['items'] as $item)
    <div style="display:flex;justify-content:space-between;align-items:flex-start;
                padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:.82rem;">
      <div>
        <span style="color:#475569;">{{ $item['kpi'] }}</span>
        @if(isset($item['meta']))
        <div style="font-size:.68rem;color:#94a3b8;margin-top:1px;">Meta: {{ $item['meta'] }}</div>
        @endif
      </div>
      <span style="font-weight:800;color:#1e293b;white-space:nowrap;margin-left:8px;">
        {{ $item['valor'] }}
      </span>
    </div>
    @endforeach
  </div>
  @endforeach
</div>
@endif

{{-- GRÁFICA COMPARATIVA DE LÍNEAS --}}
@if(count($chartLineas) > 1)
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Comparativo entre lineas</div>
  <div style="position:relative;height:220px;">
    <canvas id="chartLineasComp"></canvas>
  </div>
</div>
@endif

{{-- GRÁFICA EVOLUCIÓN MENSUAL --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Evolucion mensual {{ $anio }}</div>
  <div style="position:relative;height:180px;">
    <canvas id="chartEvolucion"></canvas>
  </div>
</div>

{{-- DESGLOSE INGRESOS --}}
@if($detalleIngresos->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Ingresos por tipo</div>
  @foreach($detalleIngresos as $d)
  @php $pctI = $totalIngresos > 0 ? round(($d->total / $totalIngresos) * 100, 1) : 0; @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;
              padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div style="flex:1;">
      <span style="font-weight:600;">{{ ucfirst(str_replace('_',' ',$d->tipo ?? 'otro')) }}</span>
      <span style="font-size:.7rem;color:#94a3b8;margin-left:6px;">{{ $d->registros }} registros</span>
      <div style="background:#e2e8f0;border-radius:3px;height:4px;margin-top:3px;overflow:hidden;">
        <div style="width:{{ $pctI }}%;height:100%;background:#16a34a;border-radius:3px;"></div>
      </div>
    </div>
    <div style="text-align:right;margin-left:12px;">
      <span style="font-weight:700;color:#16a34a;">+${{ number_format($d->total/1000,1) }}k</span>
      <div style="font-size:.68rem;color:#94a3b8;">{{ $pctI }}%</div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- DESGLOSE GASTOS --}}
@if($detalleGastos->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Gastos por categoria</div>
  @foreach($detalleGastos as $d)
  @php $pctG = $totalGastos > 0 ? round(($d->total / $totalGastos) * 100, 1) : 0; @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;
              padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div style="flex:1;">
      <span style="font-weight:600;">{{ ucfirst($d->categoria ?? 'otros') }}</span>
      <span style="font-size:.7rem;color:#94a3b8;margin-left:6px;">{{ $d->registros }} registros</span>
      <div style="background:#e2e8f0;border-radius:3px;height:4px;margin-top:3px;overflow:hidden;">
        <div style="width:{{ $pctG }}%;height:100%;background:#dc2626;border-radius:3px;"></div>
      </div>
    </div>
    <div style="text-align:right;margin-left:12px;">
      <span style="font-weight:700;color:#dc2626;">-${{ number_format($d->total/1000,1) }}k</span>
      <div style="font-size:.68rem;color:#94a3b8;">{{ $pctG }}%</div>
    </div>
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>

@endsection

@push('scripts')
<script>
@if(count($chartLineas) > 1)
(function(){
  var ctx = document.getElementById('chartLineasComp');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: {!! json_encode(array_map(fn($l) => ucfirst(str_replace('_',' ',$l)), $chartLineas)) !!},
      datasets: [
        { label: 'Ingresos',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $chartIngresos)) !!},
          backgroundColor: 'rgba(22,163,74,.75)', borderColor: '#16a34a', borderWidth: 1 },
        { label: 'Gastos',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $chartGastos)) !!},
          backgroundColor: 'rgba(220,38,38,.6)', borderColor: '#dc2626', borderWidth: 1 },
        { label: 'Rentabilidad neta',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $chartRentab)) !!},
          backgroundColor: 'rgba(37,99,235,.6)', borderColor: '#2563eb', borderWidth: 1 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { ticks: { font: { size: 9 },
             callback: function(v){ return '$'+v+'k'; } } }
      }
    }
  });
})();
@endif

(function(){
  var ctx = document.getElementById('chartEvolucion');
  if (!ctx) return;
  @php
    $evMeses = array_column($evolucionMensual, 'mes');
    $evIng   = array_column($evolucionMensual, 'ingresos');
    $evGast  = array_column($evolucionMensual, 'gastos');
    $evRent  = array_column($evolucionMensual, 'rentabilidad');
  @endphp
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: {!! json_encode($evMeses) !!},
      datasets: [
        { label: 'Ingresos',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $evIng)) !!},
          borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.08)',
          borderWidth: 2, pointRadius: 3, fill: true },
        { label: 'Gastos',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $evGast)) !!},
          borderColor: '#dc2626', backgroundColor: 'transparent',
          borderWidth: 1.5, borderDash: [4,4], pointRadius: 2 },
        { label: 'Rentabilidad',
          data: {!! json_encode(array_map(fn($v) => round($v/1000,1), $evRent)) !!},
          borderColor: '#2563eb', backgroundColor: 'transparent',
          borderWidth: 2, pointRadius: 3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { ticks: { font: { size: 9 },
             callback: function(v){ return '$'+v+'k'; } } }
      }
    }
  });
})();
</script>
@endpush