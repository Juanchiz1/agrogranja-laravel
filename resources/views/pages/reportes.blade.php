@extends('layouts.app')
@section('title','Reportes')
@section('page_title','📊 Reportes')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/reportes.css') }}">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- AÑO SELECTOR --}}
<div class="flex items-center gap-2 mb-4">
  <a href="?tab={{ $tab }}&anio={{ $anio-1 }}" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;font-size:1.1rem;">Año {{ $anio }}</span>
  <a href="?tab={{ $tab }}&anio={{ $anio+1 }}" class="btn btn-sm btn-secondary btn-icon">›</a>
</div>

{{-- TABS --}}
<div class="tabs-rep">
  <a href="?tab=resumen&anio={{ $anio }}" class="tab-rep {{ $tab==='resumen'?'active':'' }}">📋 Resumen</a>
  <a href="?tab=finanzas&anio={{ $anio }}" class="tab-rep {{ $tab==='finanzas'?'active':'' }}">💰 Finanzas</a>
  <a href="?tab=cultivos&anio={{ $anio }}" class="tab-rep {{ $tab==='cultivos'?'active':'' }}">🌱 Cultivos</a>
  <a href="?tab=animales&anio={{ $anio }}" class="tab-rep {{ $tab==='animales'?'active':'' }}">🐄 Animales</a>
  <a href="?tab=rentabilidad&anio={{ $anio }}" class="tab-rep {{ $tab==='rentabilidad'?'active':'' }}">💹 Rentabilidad</a>
  <a href="?tab=exportar&anio={{ $anio }}" class="tab-rep {{ $tab==='exportar'?'active':'' }}">📤 Exportar</a>
</div>

@php
  $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  $colores10 = ['#3d8b3d','#7a4f2a','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#64748b','#059669'];
  $tiposLabels = ['venta'=>'Venta','cosecha_propia'=>'Cosecha','animal'=>'Animal','subproducto'=>'Subproducto','subsidio'=>'Subsidio','servicio'=>'Servicio','alquiler'=>'Alquiler','otro'=>'Otro'];
@endphp

{{-- ═══════════════════ TAB RESUMEN ═══════════════════ --}}
@if($tab === 'resumen')

{{-- KPI Principal --}}
<div class="stats-row">
  <div class="stat-mini" style="background:var(--verde-bg)">
    <div class="stat-mini-val text-green">${{ $totalIngresos>=1000?number_format($totalIngresos/1000,1).'k':number_format($totalIngresos,0,',','.') }}</div>
    <div class="stat-mini-label">Ingresos</div>
  </div>
  <div class="stat-mini" style="background:var(--marron-bg)">
    <div class="stat-mini-val" style="color:var(--marron)">${{ $totalGastos>=1000?number_format($totalGastos/1000,1).'k':number_format($totalGastos,0,',','.') }}</div>
    <div class="stat-mini-label">Gastos</div>
  </div>
  <div class="stat-mini" style="background:{{ $balance>=0?'var(--verde-bg)':'#fef2f2' }}">
    <div class="stat-mini-val" style="color:{{ $balance>=0?'var(--verde-dark)':'var(--rojo)' }}">${{ abs($balance)>=1000?number_format(abs($balance)/1000,1).'k':number_format(abs($balance),0,',','.') }}</div>
    <div class="stat-mini-label">Balance</div>
  </div>
  <div class="stat-mini" style="background:var(--verde-bg)">
    <div class="stat-mini-val text-green">{{ $totalCosechas }}</div>
    <div class="stat-mini-label">Cosechas</div>
  </div>
</div>

{{-- Balance neto por mes --}}
<div class="rep-card">
  <div class="rep-card-title">📈 Balance neto mensual — {{ $anio }}</div>
  <canvas id="chartBalance" height="160"></canvas>
</div>

{{-- Ingresos vs Gastos --}}
<div class="rep-card">
  <div class="rep-card-title">💹 Ingresos vs Gastos por mes</div>
  <canvas id="chartMensual" height="170"></canvas>
</div>

{{-- Insights automáticos --}}
@if(count($insights))
<div class="rep-card">
  <div class="rep-card-title">💡 Análisis automático</div>
  @foreach($insights as $ins)
  <div class="insight-card insight-{{ $ins['tipo'] }}">
    <span>{{ ['positivo'=>'✅','negativo'=>'⚠️','info'=>'📊','alerta'=>'🚨'][$ins['tipo']] }}</span>
    <span>{{ $ins['texto'] }}</span>
  </div>
  @endforeach
</div>
@endif

{{-- Distribución origen gastos --}}
<div class="rep-card">
  <div class="rep-card-title">🎯 ¿Dónde se van los gastos?</div>
  <canvas id="chartGastosOrigen" height="200"></canvas>
</div>

{{-- Cumplimiento tareas --}}
@if($tareasStats && $tareasStats->total > 0)
<div class="rep-card">
  <div class="flex items-center justify-between mb-2">
    <div class="rep-card-title" style="margin:0;">✅ Cumplimiento de tareas</div>
    <span style="font-weight:800;color:var(--verde-dark);">{{ round($tareasStats->completadas/$tareasStats->total*100) }}%</span>
  </div>
  <div style="background:#e5e7eb;border-radius:99px;height:10px;overflow:hidden;margin-bottom:8px;">
    <div style="height:100%;border-radius:99px;background:var(--verde-mid);width:{{ round($tareasStats->completadas/$tareasStats->total*100) }}%;transition:width .5s;"></div>
  </div>
  <div style="font-size:.8rem;color:var(--text-secondary);">{{ intval($tareasStats->completadas) }} de {{ $tareasStats->total }} tareas completadas</div>
</div>
@endif

{{-- ═══════════════════ TAB FINANZAS ═══════════════════ --}}
@elseif($tab === 'finanzas')

{{-- Ingresos por tipo (donut) --}}
@if($ingresosTipo->count())
<div class="rep-card">
  <div class="rep-card-title">💰 Ingresos por tipo — {{ $anio }}</div>
  <canvas id="chartIngresosTipo" height="200"></canvas>
  <div style="margin-top:12px;">
    @foreach($ingresosTipo as $it)
    @php $pct = $totalIngresos>0?round($it->total/$totalIngresos*100):0; @endphp
    <div class="rank-row">
      <div style="flex:1;font-size:.85rem;font-weight:600;">{{ $tiposLabels[$it->tipo]??ucfirst($it->tipo) }}</div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:{{ $pct }}%;background:var(--verde-mid);"></div></div>
      <div style="font-size:.82rem;font-weight:700;color:var(--verde-dark);min-width:60px;text-align:right;">${{ number_format($it->total,0,',','.') }}</div>
      <div style="font-size:.72rem;color:var(--text-muted);min-width:30px;text-align:right;">{{ $pct }}%</div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- Gastos por categoría --}}
<div class="rep-card">
  <div class="rep-card-title">💸 Gastos por categoría</div>
  <canvas id="chartCategorias" height="220"></canvas>
  <div style="margin-top:12px;">
    @foreach($gastosCat->take(8) as $gc)
    @php $pct = $totalGastos>0?min(100,round($gc->total/$totalGastos*100)):0; @endphp
    <div class="rank-row">
      <div style="flex:1;font-size:.85rem;font-weight:600;">{{ $gc->categoria }}</div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:{{ $pct }}%;background:var(--marron-light);"></div></div>
      <div style="font-size:.82rem;font-weight:700;color:var(--marron);min-width:60px;text-align:right;">${{ number_format($gc->total,0,',','.') }}</div>
    </div>
    @endforeach
  </div>
</div>

{{-- Top compradores --}}
@if($topCompradores->count())
<div class="rep-card">
  <div class="rep-card-title">🏆 Top compradores del año</div>
  @foreach($topCompradores as $idx => $tc)
  <div class="rank-row">
    <div class="rank-num">{{ $idx+1 }}</div>
    <div style="flex:1;"><div style="font-weight:600;font-size:.88rem;">{{ $tc->comprador }}</div><div style="font-size:.73rem;color:var(--text-secondary);">{{ $tc->cnt }} venta(s)</div></div>
    <div style="font-weight:700;color:var(--verde-dark);">${{ number_format($tc->total,0,',','.') }}</div>
  </div>
  @endforeach
</div>
@endif

{{-- Top proveedores --}}
@if($topProveedores->count())
<div class="rep-card">
  <div class="rep-card-title">🏪 Top proveedores del año</div>
  @foreach($topProveedores as $idx => $tp)
  <div class="rank-row">
    <div class="rank-num">{{ $idx+1 }}</div>
    <div style="flex:1;"><div style="font-weight:600;font-size:.88rem;">{{ $tp->proveedor }}</div><div style="font-size:.73rem;color:var(--text-secondary);">{{ $tp->cnt }} compra(s)</div></div>
    <div style="font-weight:700;color:var(--marron);">${{ number_format($tp->total,0,',','.') }}</div>
  </div>
  @endforeach
</div>
@endif

{{-- ═══════════════════ TAB CULTIVOS ═══════════════════ --}}
@elseif($tab === 'cultivos')

<div class="stats-row">
  <div class="stat-mini"><div class="stat-mini-val text-green">{{ $cultivosEst['activo']??0 }}</div><div class="stat-mini-label">Activos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:#ea580c;">{{ $cultivosEst['cosechado']??0 }}</div><div class="stat-mini-label">Cosechados</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--marron);">{{ $cultivosEst['vendido']??0 }}</div><div class="stat-mini-label">Vendidos</div></div>
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green">{{ $totalCosechas }}</div><div class="stat-mini-label">Cosechas</div></div>
</div>

{{-- Valor de cosechas por mes --}}
@if($valorCosechas > 0)
<div class="rep-card">
  <div class="rep-card-title">🌾 Valor de cosechas por mes — {{ $anio }}</div>
  <canvas id="chartCosechas" height="160"></canvas>
</div>
@endif

{{-- Cultivos por especie (donut) --}}
@if($cultivosTipo->count())
<div class="rep-card">
  <div class="rep-card-title">🌱 Tipos de cultivo activos</div>
  <canvas id="chartCultivosDonut" height="200"></canvas>
</div>
@endif

{{-- Rentabilidad por cultivo --}}
@if($rentCultivos->count())
<div class="rep-card">
  <div class="rep-card-title">💹 Rentabilidad por cultivo</div>
  <a href="{{ route('rentabilidad.index') }}" class="btn btn-sm btn-ghost mb-3" style="font-size:.8rem;">Ver detalle completo →</a>
  @foreach($rentCultivos as $rc)
  @php $color = $rc->balance>=0?'var(--verde-dark)':'var(--rojo)'; @endphp
  <div class="rank-row">
    <div style="flex:1;"><div style="font-weight:600;font-size:.87rem;">{{ $rc->nombre }}</div><div style="font-size:.72rem;color:var(--text-secondary);">{{ $rc->tipo }}</div></div>
    <div style="text-align:right;">
      <div style="font-size:.75rem;color:var(--text-muted);">G ${{ number_format($rc->gastos,0,',','.') }}</div>
      <div style="font-weight:700;color:{{ $color }};">{{ $rc->balance>=0?'+':'' }}${{ number_format($rc->balance,0,',','.') }}</div>
    </div>
  </div>
  @endforeach
  <canvas id="chartRentCultivos" height="200" style="margin-top:16px;"></canvas>
</div>
@endif

{{-- ═══════════════════ TAB ANIMALES ═══════════════════ --}}
@elseif($tab === 'animales')

<div class="stats-row">
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green">{{ $animalesEst['activo']??0 }}</div><div class="stat-mini-label">Activos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--marron)">{{ $animalesEst['vendido']??0 }}</div><div class="stat-mini-label">Vendidos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--rojo)">{{ $animalesEst['muerte']??0 }}</div><div class="stat-mini-label">Bajas</div></div>
  @if($valorHato > 0)
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green" style="font-size:.9rem;">${{ number_format($valorHato/1000,0).'k' }}</div><div class="stat-mini-label">Valor hato</div></div>
  @endif
</div>

{{-- Animales por especie --}}
@if($animalesPorEspecie->count())
<div class="rep-card">
  <div class="rep-card-title">🐾 Inventario por especie</div>
  <canvas id="chartAnimalesEspecie" height="200"></canvas>
  <div style="margin-top:14px;">
    @php $totalAnim = $animalesPorEspecie->sum('total') ?: 1; @endphp
    @foreach($animalesPorEspecie as $ae)
    <div class="rank-row">
      <div style="flex:1;font-weight:600;font-size:.87rem;">{{ $ae->especie }}</div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:{{ round($ae->total/$totalAnim*100) }}%;"></div></div>
      <div style="font-weight:700;color:var(--verde-dark);min-width:40px;text-align:right;">{{ $ae->total }}</div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- Valor estimado del hato --}}
@if($valorHato > 0)
<div class="rep-card">
  <div class="rep-card-title">💰 Valor estimado del hato</div>
  <div style="text-align:center;padding:16px 0;">
    <div style="font-size:2rem;font-weight:800;color:var(--verde-dark);">${{ number_format($valorHato,0,',','.') }}</div>
    <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;">Calculado según precio/kg o precio/cabeza asignado a cada lote</div>
  </div>
  @if($ventasAnimales > 0)
  <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px;display:flex;justify-content:space-between;font-size:.87rem;">
    <span>Ventas de animales este año</span>
    <strong style="color:var(--verde-dark)">${{ number_format($ventasAnimales,0,',','.') }}</strong>
  </div>
  @endif
</div>
@endif

{{-- Gastos vs ingresos animales --}}
@php
  $gastosAnimTotal   = DB::table('gastos')->where('usuario_id',session('usuario_id'))->whereYear('fecha',$anio)->whereNotNull('animal_id')->sum('valor');
  $ingresosAnimTotal = DB::table('ingresos')->where('usuario_id',session('usuario_id'))->whereYear('fecha',$anio)->whereNotNull('animal_id')->sum('valor_total');
  $balAnim = $ingresosAnimTotal - $gastosAnimTotal;
@endphp
@if($gastosAnimTotal > 0 || $ingresosAnimTotal > 0)
<div class="rep-card">
  <div class="rep-card-title">📊 Finanzas de animales</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Ingresos por animales</div>
      <div style="font-weight:800;color:var(--verde-dark);">${{ number_format($ingresosAnimTotal,0,',','.') }}</div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Gastos en animales</div>
      <div style="font-weight:800;color:var(--rojo);">${{ number_format($gastosAnimTotal,0,',','.') }}</div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.88rem;">
    <span>Balance ganadería</span>
    <strong style="color:{{ $balAnim>=0?'var(--verde-dark)':'var(--rojo)' }}">{{ $balAnim>=0?'+':'' }}${{ number_format($balAnim,0,',','.') }}</strong>
  </div>
</div>
@endif


{{-- ═══════════════════ TAB RENTABILIDAD ═══════════════════ --}}
@elseif($tab === 'rentabilidad')

<div class="flex gap-2 mb-3 items-center">
  <span style="font-size:.82rem;color:var(--text-secondary);">Ordenar por:</span>
  <select onchange="location.href='?tab=rentabilidad&anio={{ $anio }}&orden='+this.value" class="form-control" style="width:160px;font-size:.82rem;">
    @foreach(['rentabilidad'=>'Mejor balance','ingresos'=>'Mayor ingreso','gastos'=>'Mayor gasto','nombre'=>'Nombre'] as $val => $lbl)
      <option value="{{ $val }}" {{ $orden===$val ? 'selected' : '' }}>{{ $lbl }}</option>
    @endforeach
  </select>
</div>

@if($rentMejor || $rentPeor)
<div class="grid-2 mb-3">
  @if($rentMejor)
  <div style="background:var(--verde-bg);border-radius:var(--radius-lg);padding:14px;border-left:3px solid var(--verde-dark);">
    <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">&#11088; Mejor cultivo</p>
    <p style="font-weight:700;">{{ $rentMejor->nombre }}</p>
    <p style="color:var(--verde-dark);font-weight:800;font-size:1.1rem;">${{ number_format($rentMejor->rentabilidad,0,',','.') }}</p>
    <p style="font-size:.78rem;color:var(--text-secondary);">ROI: {{ $rentMejor->roi }}%</p>
  </div>
  @endif
  @if($rentPeor && $rentPeor->rentabilidad < 0)
  <div style="background:#fef2f2;border-radius:var(--radius-lg);padding:14px;border-left:3px solid var(--rojo);">
    <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">&#9888; Atencion</p>
    <p style="font-weight:700;">{{ $rentPeor->nombre }}</p>
    <p style="color:var(--rojo);font-weight:800;font-size:1.1rem;">${{ number_format($rentPeor->rentabilidad,0,',','.') }}</p>
    <p style="font-size:.78rem;color:var(--text-secondary);">ROI: {{ $rentPeor->roi }}%</p>
  </div>
  @endif
</div>
@endif

@if(count($rentChartLabels) > 0)
<div class="rep-card">
  <div class="rep-card-title">Ingresos vs Gastos por cultivo</div>
  <canvas id="chartRentComp" height="220"></canvas>
</div>
@endif

@if($rentDatos->isEmpty())
<div class="empty-state"><div class="emoji">&#127807;</div><p>No hay cultivos con datos para {{ $anio }}.</p></div>
@else
@foreach($rentDatos as $d)
@php
  $dPos  = $d->rentabilidad >= 0;
  $dMaxV = max($d->gastos, $d->ingresos, 1);
  $dPG   = round($d->gastos / $dMaxV * 100);
  $dPI   = round($d->ingresos / $dMaxV * 100);
  $dBadge= ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$d->estado] ?? 'badge-green';
@endphp
<div class="rep-card" style="border-left:3px solid {{ $dPos ? 'var(--verde-dark)' : 'var(--rojo)' }};">
  <div class="flex items-center justify-between mb-3">
    <div><div style="font-weight:700;">{{ $d->nombre }}</div><div style="font-size:.75rem;color:var(--text-secondary);">{{ $d->tipo }}</div></div>
    <div class="flex items-center gap-2">
      <span class="badge {{ $dBadge }}">{{ $d->estado }}</span>
      <a href="{{ route('rentabilidad.detalle',$d->id) }}?anio={{ $anio }}" class="btn btn-sm btn-secondary" style="font-size:.78rem;">Detalle</a>
    </div>
  </div>
  <div style="margin-bottom:10px;">
    <div class="flex items-center justify-between mb-1"><span style="font-size:.78rem;color:var(--text-secondary);">Gastos</span><span style="font-size:.78rem;font-weight:700;color:var(--marron);">${{ number_format($d->gastos,0,',','.') }}</span></div>
    <div style="background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;"><div style="height:100%;border-radius:99px;background:var(--marron-light);width:{{ $dPG }}%;"></div></div>
    <div class="flex items-center justify-between mt-2 mb-1"><span style="font-size:.78rem;color:var(--text-secondary);">Ingresos</span><span style="font-size:.78rem;font-weight:700;color:var(--verde-dark);">${{ number_format($d->ingresos,0,',','.') }}</span></div>
    <div style="background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;"><div style="height:100%;border-radius:99px;background:var(--verde-dark);width:{{ $dPI }}%;"></div></div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px;background:#f8fafc;border-radius:10px;">
    <div style="text-align:center;"><div style="font-weight:800;color:{{ $dPos ? 'var(--verde-dark)' : 'var(--rojo)' }};">${{ number_format($d->rentabilidad,0,',','.') }}</div><div style="font-size:.7rem;color:var(--text-secondary);">Balance</div></div>
    <div style="text-align:center;"><div style="font-weight:800;color:{{ $d->roi >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">{{ $d->roi }}%</div><div style="font-size:.7rem;color:var(--text-secondary);">ROI</div></div>
    <div style="text-align:center;"><div style="font-weight:800;color:var(--text-secondary);">{{ $d->margen }}%</div><div style="font-size:.7rem;color:var(--text-secondary);">Margen</div></div>
  </div>
</div>
@endforeach
@endif

{{-- ═══════════════════ TAB EXPORTAR ═══════════════════ --}}
@elseif($tab === 'exportar')

<p style="font-size:.87rem;color:var(--text-secondary);margin-bottom:16px;">Genera reportes en PDF o Excel con todos los datos del año <strong>{{ $anio }}</strong>.</p>

{{-- Reporte general --}}
<div class="export-group">
  <div class="export-group-title">📋 Reporte general</div>
  <div class="export-btns">
    <a href="{{ route('exportar.reporte.pdf', ['anio'=>$anio]) }}" class="exp-btn exp-pdf">📄 PDF General</a>
  </div>
</div>

{{-- Cultivos --}}
<div class="export-group">
  <div class="export-group-title">🌱 Cultivos</div>
  <div class="export-btns">
    <a href="{{ route('exportar.cultivos.pdf') }}" class="exp-btn exp-pdf">📄 PDF</a>
    <a href="{{ route('exportar.cultivos.excel') }}" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>

{{-- Gastos --}}
<div class="export-group">
  <div class="export-group-title">💰 Gastos</div>
  <div class="export-btns">
    <a href="{{ route('exportar.gastos.pdf', ['anio'=>$anio]) }}" class="exp-btn exp-brown">📄 PDF</a>
    <a href="{{ route('exportar.gastos.excel', ['anio'=>$anio]) }}" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>

{{-- Cosechas --}}
<div class="export-group">
  <div class="export-group-title">🌾 Cosechas</div>
  <div class="export-btns">
    <a href="{{ route('exportar.cosechas.pdf', ['anio'=>$anio]) }}" class="exp-btn exp-orange">📄 PDF</a>
    <a href="{{ route('exportar.cosechas.excel', ['anio'=>$anio]) }}" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>

{{-- Animales (NUEVO) --}}
<div class="export-group">
  <div class="export-group-title">🐄 Animales</div>
  <div class="export-btns">
    <a href="{{ route('exportar.animales.pdf') }}" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>

{{-- Inventario (NUEVO) --}}
<div class="export-group">
  <div class="export-group-title">📦 Inventario</div>
  <div class="export-btns">
    <a href="{{ route('exportar.inventario.pdf') }}" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>

{{-- Nómina (NUEVO) --}}
<div class="export-group">
  <div class="export-group-title">👷 Nómina</div>
  <div class="export-btns">
    <a href="{{ route('exportar.nomina.pdf') }}?mes={{ now()->format('Y-m') }}" class="exp-btn exp-purple">📄 PDF</a>
  </div>
</div>

{{-- Producción (NUEVO) --}}
<div class="export-group">
  <div class="export-group-title">🥛 Producción</div>
  <div class="export-btns">
    <a href="{{ route('exportar.produccion.pdf') }}?mes={{ now()->format('Y-m') }}" class="exp-btn exp-purple">📄 PDF</a>
  </div>
</div>

{{-- Rentabilidad --}}
<div class="export-group">
  <div class="export-group-title">💹 Rentabilidad</div>
  <div class="export-btns">
    <a href="{{ route('rentabilidad.index') }}" class="exp-btn exp-purple">💹 Ver detalle por cultivo</a>
    <a href="{{ route('exportar.rentabilidad.pdf') }}?anio={{ $anio }}" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>

{{-- Consejo --}}
<div style="background:#eff6ff;border-radius:var(--radius-lg);padding:14px;margin-top:8px;">
  <p style="font-size:.82rem;color:#1d4ed8;font-weight:600;margin-bottom:6px;">💡 Consejo para reportes</p>
  <p style="font-size:.8rem;color:#1e40af;">Los PDFs incluyen tablas detalladas con toda la información del año seleccionado. Puedes cambiar el año con las flechas ‹ › arriba de la página.</p>
</div>

@endif

{{-- ─────── SCRIPTS DE GRÁFICAS ─────── --}}
@push('scripts')
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';
const meses = @json($meses);
const gastosArr   = @json($gastosArr);
const ingresosArr = @json($ingresosArr);
const balanceArr  = @json($balanceArr);

@if($tab === 'resumen')
// Balance neto por mes
new Chart(document.getElementById('chartBalance'), {
  type:'bar',
  data:{ labels:meses, datasets:[{
    label:'Balance neto',
    data:balanceArr,
    backgroundColor: balanceArr.map(v => v>=0 ? 'rgba(61,139,61,.75)' : 'rgba(220,38,38,.65)'),
    borderRadius:5
  }]},
  options:{ responsive:true, plugins:{legend:{display:false}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' }, grid:{ color:'#f0f0f0' } } } }
});

// Ingresos vs Gastos
new Chart(document.getElementById('chartMensual'), {
  type:'bar',
  data:{ labels:meses, datasets:[
    { label:'Ingresos', data:ingresosArr, backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:gastosArr,   backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' }, grid:{ color:'#f0f0f0' } } } }
});

// Origen gastos
const origenLabels = ['Cultivos','Animales','Finca general'];
const origenData   = [{{ round($gastosCultivo,0) }}, {{ round($gastosAnimal,0) }}, {{ round($gastosGeneral,0) }}];
if(origenData.some(v=>v>0)) {
  new Chart(document.getElementById('chartGastosOrigen'), {
    type:'doughnut',
    data:{ labels:origenLabels, datasets:[{ data:origenData, backgroundColor:['#3d8b3d','#7c3aed','#ea580c'], borderWidth:2, borderColor:'#fff' }]},
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12 } } } }
  });
}

@elseif($tab === 'finanzas')
// Ingresos por tipo
@php $itLabels=$ingresosTipo->map(fn($t)=>$tiposLabels[$t->tipo]??ucfirst($t->tipo)); $itValues=$ingresosTipo->pluck('total'); @endphp
@if($ingresosTipo->count())
new Chart(document.getElementById('chartIngresosTipo'), {
  type:'doughnut',
  data:{ labels:@json($itLabels), datasets:[{ data:@json($itValues), backgroundColor:['#3d8b3d','#ea580c','#7c3aed','#2563eb','#d97706','#0891b2','#dc2626','#64748b'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:11}}}} }
});
@endif

// Gastos por categoría (horizontal bar)
@php $catL=$gastosCat->pluck('categoria'); $catV=$gastosCat->pluck('total'); @endphp
@if($gastosCat->count())
new Chart(document.getElementById('chartCategorias'), {
  type:'bar',
  data:{ labels:@json($catL), datasets:[{ label:'Gastos', data:@json($catV), backgroundColor:'rgba(122,79,42,.7)', borderRadius:4 }]},
  options:{ indexAxis:'y', responsive:true, plugins:{legend:{display:false}},
    scales:{ x:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@endif

@elseif($tab === 'cultivos')
// Cosechas por mes
@if($valorCosechas > 0)
new Chart(document.getElementById('chartCosechas'), {
  type:'bar',
  data:{ labels:meses, datasets:[{ label:'Valor cosechado', data:@json($cosechasArr), backgroundColor:'rgba(234,88,12,.7)', borderRadius:4 }]},
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@endif

// Cultivos por tipo (donut)
@php $ctL=$cultivosTipo->pluck('tipo'); $ctV=$cultivosTipo->pluck('c'); @endphp
@if($cultivosTipo->count())
new Chart(document.getElementById('chartCultivosDonut'), {
  type:'doughnut',
  data:{ labels:@json($ctL), datasets:[{ data:@json($ctV), backgroundColor:['#3d8b3d','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#064748','#059669','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12}}} }
});
@endif

// Rentabilidad por cultivo (barras horizontales)
@if($rentCultivos->count())
@php $rcL=$rentCultivos->pluck('nombre'); $rcG=$rentCultivos->pluck('gastos'); $rcI=$rentCultivos->pluck('ingresos'); @endphp
new Chart(document.getElementById('chartRentCultivos'), {
  type:'bar',
  data:{ labels:@json($rcL), datasets:[
    { label:'Ingresos', data:@json($rcI), backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:@json($rcG), backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ indexAxis:'y', responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ x:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@endif

@elseif($tab === 'animales')
// Animales por especie
@php $aeL=$animalesPorEspecie->pluck('especie'); $aeV=$animalesPorEspecie->pluck('total'); @endphp
@if($animalesPorEspecie->count())
new Chart(document.getElementById('chartAnimalesEspecie'), {
  type:'doughnut',
  data:{ labels:@json($aeL), datasets:[{ data:@json($aeV), backgroundColor:['#3d8b3d','#ea580c','#7c3aed','#2563eb','#d97706','#0891b2','#dc2626','#64748b','#059669','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12}}} }
});
@endif

@elseif($tab === 'rentabilidad')
@if(count($rentChartLabels) > 0)
new Chart(document.getElementById('chartRentComp'), {
  type:'bar',
  data:{ labels:@json($rentChartLabels), datasets:[
    { label:'Ingresos', data:@json($rentChartIngresos), backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:@json($rentChartGastos),   backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
@endif
@endif
</script>
@endpush
@endsection