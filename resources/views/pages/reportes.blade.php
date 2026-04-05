@extends('layouts.app')
@section('title','Reportes')
@section('page_title','📊 Reportes')
@section('back_url', route('dashboard'))
@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
@endpush
@section('content')

<div class="flex items-center gap-2 mb-3">
  <a href="?tab={{ $tab }}&anio={{ $anio-1 }}" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;">Año {{ $anio }}</span>
  <a href="?tab={{ $tab }}&anio={{ $anio+1 }}" class="btn btn-sm btn-secondary btn-icon">›</a>
</div>

<div class="tabs">
  <button class="tab-btn {{ $tab==='resumen'?'active':'' }}"  onclick="location.href='?tab=resumen&anio={{ $anio }}'">📋 Resumen</button>
  <button class="tab-btn {{ $tab==='gastos'?'active':'' }}"   onclick="location.href='?tab=gastos&anio={{ $anio }}'">💰 Gastos</button>
  <button class="tab-btn {{ $tab==='cultivos'?'active':'' }}" onclick="location.href='?tab=cultivos&anio={{ $anio }}'">🌱 Cultivos</button>
</div>

@php $balance = $totalIngresos - $totalGastos; @endphp

@if($tab === 'resumen')
<div class="grid-2 mb-3">
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1rem;">${{ number_format($totalIngresos,0,',','.') }}</div><div class="stat-label">Total ingresos</div></div>
  <div class="stat-card" style="background:var(--marron-bg);"><div class="stat-value text-brown" style="font-size:1rem;">${{ number_format($totalGastos,0,',','.') }}</div><div class="stat-label">Total gastos</div></div>
</div>
<div class="card mb-3" style="background:{{ $balance >= 0 ? 'var(--verde-bg)' : '#fef2f2' }};text-align:center;">
  <p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Balance del año</p>
  <p style="font-size:2rem;font-weight:800;color:{{ $balance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">${{ number_format($balance,0,',','.') }}</p>
  <p class="text-xs text-gray">{{ $balance >= 0 ? '✅ Operando con ganancia' : '⚠️ Gastos superan ingresos' }}</p>
</div>
<div class="card mb-3">
  <p class="font-bold mb-3">Ingresos vs Gastos por mes</p>
  <canvas id="chartMensual" height="180"></canvas>
</div>
@if($tareasStats)
<div class="card">
  <div class="flex items-center justify-between mb-2"><p class="font-bold">Cumplimiento de tareas</p><span class="font-bold text-green">{{ $tareasStats->total > 0 ? round($tareasStats->completadas/$tareasStats->total*100) : 0 }}%</span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:{{ $tareasStats->total > 0 ? round($tareasStats->completadas/$tareasStats->total*100) : 0 }}%"></div></div>
  <p class="text-xs text-gray mt-2">{{ $tareasStats->completadas ?? 0 }} de {{ $tareasStats->total }} tareas completadas</p>
</div>
@endif

@elseif($tab === 'gastos')
<div class="card mb-3"><p class="font-bold mb-3">Gastos mensuales {{ $anio }}</p><canvas id="chartGastosMes" height="180"></canvas></div>
<p class="section-title">Por categoría</p>
@foreach($gastosCat as $gc)
<div class="mb-3">
  <div class="flex items-center justify-between mb-2"><span class="text-sm font-bold">{{ $gc->categoria }}</span><span class="font-bold text-brown text-sm">${{ number_format($gc->total,0,',','.') }}</span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:{{ $totalGastos > 0 ? min(100,round($gc->total/$totalGastos*100)) : 0 }}%;background:var(--marron-light);"></div></div>
</div>
@endforeach
@if($gastosCat->count())
<div class="card mt-3"><p class="font-bold mb-3">Distribución por categoría</p><canvas id="chartCategorias" height="200"></canvas></div>
@endif

@elseif($tab === 'cultivos')
<div class="grid-3 mb-3">
  <div class="stat-card"><div class="stat-value text-green">{{ $cultivosEst['activo'] ?? 0 }}</div><div class="stat-label">Activos</div></div>
  <div class="stat-card"><div class="stat-value text-orange">{{ $cultivosEst['cosechado'] ?? 0 }}</div><div class="stat-label">Cosechados</div></div>
  <div class="stat-card"><div class="stat-value text-brown">{{ $cultivosEst['vendido'] ?? 0 }}</div><div class="stat-label">Vendidos</div></div>
</div>
<div class="card"><p class="font-bold mb-3">Estado de cultivos</p><canvas id="chartCultivos" height="200"></canvas></div>
@endif

@push('scripts')
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';
const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const gastosArr = @json($gastosArr);
const ingresosArr = @json($ingresosArr);

@if($tab === 'resumen')
new Chart(document.getElementById('chartMensual'), {
  type: 'bar', data: { labels: meses, datasets: [
    { label:'Ingresos', data:ingresosArr, backgroundColor:'rgba(61,139,61,.75)', borderRadius:5 },
    { label:'Gastos',   data:gastosArr,   backgroundColor:'rgba(122,79,42,.65)', borderRadius:5 }
  ]}, options: { responsive:true, plugins:{legend:{position:'bottom'}}, scales:{ y:{ ticks:{ callback: v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@elseif($tab === 'gastos')
new Chart(document.getElementById('chartGastosMes'), {
  type:'line', data:{ labels:meses, datasets:[{ label:'Gastos', data:gastosArr, borderColor:'#7a4f2a', backgroundColor:'rgba(122,79,42,.1)', fill:true, tension:.4, pointRadius:4 }]},
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@php $catLabels = $gastosCat->pluck('categoria'); $catValues = $gastosCat->pluck('total'); $colors = ['#3d8b3d','#7a4f2a','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#64748b','#059669']; @endphp
@if($gastosCat->count())
new Chart(document.getElementById('chartCategorias'), {
  type:'doughnut', data:{ labels:@json($catLabels), datasets:[{ data:@json($catValues), backgroundColor:@json(array_slice($colors,0,$gastosCat->count())), borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom', labels:{boxWidth:12,font:{size:11}}}} }
});
@endif
@elseif($tab === 'cultivos')
new Chart(document.getElementById('chartCultivos'), {
  type:'doughnut', data:{ labels:['Activos','Cosechados','Vendidos'], datasets:[{ data:[{{ $cultivosEst['activo']??0 }},{{ $cultivosEst['cosechado']??0 }},{{ $cultivosEst['vendido']??0 }}], backgroundColor:['#3d8b3d','#ea580c','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}} }
});
@endif
</script>
@endpush
@endsection
