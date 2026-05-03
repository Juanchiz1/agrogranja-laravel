@extends('layouts.app')
@section('title','Reportes Bovinos')
@section('page_title','📊 Reportes Bovinos')
@section('back_url', route('bovino.hato'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/bovino.css') }}">
@endpush

@section('content')

{{-- KPIs RESUMEN --}}
<div class="hato-stats" style="margin-bottom:14px;">
  <div class="hato-stat">
    <div class="hato-stat-ico">🥛</div>
    <div class="hato-stat-val">{{ $vacasConLactancia->count() }}</div>
    <div class="hato-stat-lbl">En producción</div>
  </div>
  <div class="hato-stat" style="border-left-color:#94a3b8;">
    <div class="hato-stat-ico">💤</div>
    <div class="hato-stat-val" style="color:#64748b;">{{ $vacasSecas->count() }}</div>
    <div class="hato-stat-lbl">Secas</div>
  </div>
  <div class="hato-stat" style="border-left-color:#3b82f6;">
    <div class="hato-stat-ico">📈</div>
    <div class="hato-stat-val" style="color:#1d4ed8;">{{ $promedioHato }} L</div>
    <div class="hato-stat-lbl">Prom. L/vaca/día</div>
  </div>
  <div class="hato-stat" style="border-left-color:#f59e0b;">
    <div class="hato-stat-ico">📅</div>
    <div class="hato-stat-val" style="color:#b45309;">{{ $ipPromedio ?? '—' }}</div>
    <div class="hato-stat-lbl">IEP promedio (días)</div>
  </div>
</div>

{{-- PRODUCCIÓN POR VACA --}}
@if(!empty($produccionPorVaca))
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🥛 Producción por vaca (últimos 30 días)</div>
  <div class="tabla-reporte">
    <div class="tabla-header"><span>Vaca</span><span>Raza</span><span>Promedio L/día</span><span>Total 30d</span><span>Días ordeñada</span></div>
    @foreach($produccionPorVaca as $pv)
    @php $pct = $promedioHato > 0 ? min(100, round(($pv['promedio']/$promedioHato)*100)) : 0; @endphp
    <div class="tabla-row">
      <span style="font-weight:600;">{{ $pv['nombre'] }}</span>
      <span style="color:#94a3b8;font-size:.8rem;">{{ $pv['raza'] ?? '—' }}</span>
      <span>
        <span style="font-weight:700;color:{{ $pv['promedio'] >= $promedioHato ? '#15803d' : '#dc2626' }}">{{ $pv['promedio'] }}</span>
        <div style="background:#e2e8f0;border-radius:4px;height:5px;width:100%;margin-top:2px;overflow:hidden;">
          <div style="height:100%;background:#15803d;width:{{ $pct }}%;"></div>
        </div>
      </span>
      <span>{{ $pv['total30d'] }} L</span>
      <span>{{ $pv['dias'] }}/30</span>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- DÍAS ABIERTOS --}}
@if(!empty($diasAbiertos))
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Días abiertos</div>
    <span style="font-size:.78rem;color:#64748b;">Prom: <strong>{{ $daPromedio ?? '—' }} días</strong></span>
  </div>
  <div style="font-size:.75rem;color:#64748b;margin-bottom:10px;">
    Meta: &lt;85 días · Alerta: 85-120 · Crítico: &gt;120
  </div>
  @foreach($diasAbiertos as $da)
  @php
    $color = $da['estado']==='ok' ? '#22c55e' : ($da['estado']==='alerta' ? '#f59e0b' : '#ef4444');
    $bg    = $da['estado']==='ok' ? '#f0fdf4' : ($da['estado']==='alerta' ? '#fffbeb' : '#fef2f2');
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:8px;background:{{ $bg }};margin-bottom:6px;">
    <div>
      <strong>{{ $da['nombre'] }}</strong>
      <div style="font-size:.75rem;color:#64748b;">Último parto: {{ \Carbon\Carbon::parse($da['ultimo_parto'])->format('d/m/Y') }}</div>
    </div>
    <div style="font-weight:800;font-size:1.1rem;color:{{ $color }};">
      {{ $da['dias'] }} días
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- GPD POR ANIMAL --}}
@if(!empty($gpdPorAnimal))
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">⚖️ Ganancia diaria de peso (GPD)</div>
  <div class="tabla-reporte">
    <div class="tabla-header"><span>Animal</span><span>GPD (kg/día)</span><span>Peso actual</span><span>Meta</span><span>Estado</span></div>
    @foreach($gpdPorAnimal as $g)
    @php
      $gc = $g['estado']==='bueno' ? '#15803d' : ($g['estado']==='regular' ? '#b45309' : '#dc2626');
      $gt = $g['estado']==='bueno' ? 'Excelente' : ($g['estado']==='regular' ? 'Normal' : 'Bajo');
    @endphp
    <div class="tabla-row">
      <span style="font-weight:600;">{{ $g['nombre'] }}</span>
      <span style="font-weight:800;color:{{ $gc }};">{{ $g['gpd'] >= 0 ? '+' : '' }}{{ $g['gpd'] }}</span>
      <span>{{ $g['peso_actual'] ? $g['peso_actual'].' kg' : '—' }}</span>
      <span>{{ $g['meta_kg'] ? $g['meta_kg'].' kg' : '—' }}</span>
      <span style="font-size:.75rem;color:{{ $gc }};">{{ $gt }}</span>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- VACAS SECAS --}}
@if($vacasSecas->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">💤 Vacas secas</div>
  @foreach($vacasSecas as $vs)
  <div style="padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.85rem;">
    {{ $vs->nombre_lote }} @if($vs->raza)<span style="color:#94a3b8;">· {{ $vs->raza }}</span>@endif
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>
@endsection