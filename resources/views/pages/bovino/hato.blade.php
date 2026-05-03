@extends('layouts.app')
@section('title','Hato Bovino')
@section('page_title','🐄 Mi Hato Bovino')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/bovino.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- STATS DEL HATO --}}
<div class="hato-stats">
  <div class="hato-stat">
    <div class="hato-stat-ico">🐄</div>
    <div class="hato-stat-val">{{ $totalBovinos }}</div>
    <div class="hato-stat-lbl">Total bovinos</div>
  </div>
  <div class="hato-stat" style="border-left-color:#22c55e;">
    <div class="hato-stat-ico">🥛</div>
    <div class="hato-stat-val" style="color:#15803d;">{{ $vacasProduccion }}</div>
    <div class="hato-stat-lbl">En producción</div>
  </div>
  <div class="hato-stat" style="border-left-color:#94a3b8;">
    <div class="hato-stat-ico">💤</div>
    <div class="hato-stat-val" style="color:#64748b;">{{ $vacasSecas }}</div>
    <div class="hato-stat-lbl">Secas</div>
  </div>
  <div class="hato-stat" style="border-left-color:#3b82f6;">
    <div class="hato-stat-ico">🤰</div>
    <div class="hato-stat-val" style="color:#1d4ed8;">{{ $enGestacion }}</div>
    <div class="hato-stat-lbl">En gestación</div>
  </div>
</div>

{{-- PRODUCCIÓN HOY --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🥛 Producción hoy</div>
    <a href="{{ route('bovino.ordenos') }}" class="btn btn-sm btn-primary">Registrar ordeño</a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;">
    <div class="prod-card">
      <div class="prod-val">{{ number_format($litrosHoy,1) }} L</div>
      <div class="prod-lbl">Litros hoy</div>
    </div>
    <div class="prod-card">
      <div class="prod-val">{{ number_format($litrosAyer,1) }} L</div>
      <div class="prod-lbl">Litros ayer</div>
    </div>
    <div class="prod-card">
      <div class="prod-val">{{ number_format($promedio7d,1) }} L</div>
      <div class="prod-lbl">Prom. 7 días</div>
    </div>
  </div>
  <div style="position:relative;height:120px;">
    <canvas id="chartProd"></canvas>
  </div>
</div>

{{-- ALERTAS --}}
@if($partosProximos->count() || $alertasSanidad->count() || $diasAbiertosAltos->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">⚠️ Alertas</div>

  @foreach($partosProximos as $p)
  <div class="alerta-row alerta-parto">
    <span>🐮</span>
    <div>
      <strong>{{ $p->nombre_lote }}</strong> — Parto esperado
      <span style="color:#1d4ed8;">{{ \Carbon\Carbon::parse($p->fecha_probable_parto)->format('d/m/Y') }}</span>
      ({{ \Carbon\Carbon::parse($p->fecha_probable_parto)->diffInDays(now()) }} días)
    </div>
  </div>
  @endforeach

  @foreach($alertasSanidad as $s)
  @php $vencida = $s->proxima_aplicacion < now()->toDateString(); @endphp
  <div class="alerta-row {{ $vencida ? 'alerta-vencida' : 'alerta-proxima' }}">
    <span>{{ $vencida ? '🔴' : '🟡' }}</span>
    <div>
      <strong>{{ $s->nombre_protocolo }}</strong> —
      @if($vencida)
        Vencida desde <span>{{ \Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y') }}</span>
      @else
        Próxima: <span>{{ \Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y') }}</span>
        ({{ \Carbon\Carbon::parse($s->proxima_aplicacion)->diffInDays(now()) }} días)
      @endif
    </div>
  </div>
  @endforeach

  @foreach($diasAbiertosAltos as $da)
  <div class="alerta-row alerta-vencida">
    <span>⏱️</span>
    <div>
      <strong>{{ $da->nombre_lote }}</strong> — Días abiertos críticos desde
      {{ \Carbon\Carbon::parse($da->fecha_parto_real)->format('d/m/Y') }}
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- MENÚ DEL MÓDULO BOVINO --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">📋 Módulos bovinos</div>
  <div class="bovino-menu-grid">
    <a href="{{ route('bovino.ordenos') }}" class="bovino-menu-card" style="border-top-color:#22c55e;">
      <div class="bovino-menu-ico">🥛</div>
      <div class="bovino-menu-lbl">Ordeños</div>
      <div class="bovino-menu-sub">Registro AM/PM y curva</div>
    </a>
    <a href="{{ route('bovino.reproduccion') }}" class="bovino-menu-card" style="border-top-color:#3b82f6;">
      <div class="bovino-menu-ico">🐮</div>
      <div class="bovino-menu-lbl">Reproducción</div>
      <div class="bovino-menu-sub">Servicios, preñez, partos</div>
    </a>
    <a href="{{ route('bovino.sanidad') }}" class="bovino-menu-card" style="border-top-color:#f59e0b;">
      <div class="bovino-menu-ico">💉</div>
      <div class="bovino-menu-lbl">Sanidad</div>
      <div class="bovino-menu-sub">Vacunas y desparasitación</div>
    </a>
    <a href="{{ route('bovino.pesaje') }}" class="bovino-menu-card" style="border-top-color:#8b5cf6;">
      <div class="bovino-menu-ico">⚖️</div>
      <div class="bovino-menu-lbl">Pesaje</div>
      <div class="bovino-menu-sub">Pesos y GPD</div>
    </a>
    <a href="{{ route('bovino.reportes') }}" class="bovino-menu-card" style="border-top-color:#ec4899;">
      <div class="bovino-menu-ico">📊</div>
      <div class="bovino-menu-lbl">Reportes</div>
      <div class="bovino-menu-sub">Análisis del hato</div>
    </a>
    <a href="{{ route('animales.index') }}" class="bovino-menu-card" style="border-top-color:#94a3b8;">
      <div class="bovino-menu-ico">🐄</div>
      <div class="bovino-menu-lbl">Mis Animales</div>
      <div class="bovino-menu-sub">Módulo general</div>
    </a>
  </div>
</div>

<div style="margin-bottom:80px;"></div>
@endsection

@push('scripts')
<script>
const ctx = document.getElementById('chartProd');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: {!! json_encode($labels) !!},
    datasets: [{
      data: {!! json_encode($valores) !!},
      borderColor: '#15803d',
      backgroundColor: 'rgba(21,128,61,.1)',
      borderWidth: 2,
      pointRadius: 3,
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { font: { size: 10 } }, grid: { display: false } },
      y: { ticks: { font: { size: 10 } }, beginAtZero: true,
           title: { display: true, text: 'Litros', font: { size: 10 } } }
    }
  }
});
</script>
@endpush