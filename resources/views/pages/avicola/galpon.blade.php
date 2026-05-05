@extends('layouts.app')
@section('title','Galpón Avícola')
@section('page_title','🐔 Galpón Avícola')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/avicola.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- STATS --}}
<div class="galpon-stats">
  <div class="galpon-stat">
    <div class="galpon-stat-ico">🐔</div>
    <div class="galpon-stat-val">{{ number_format($totalAves) }}</div>
    <div class="galpon-stat-lbl">Aves activas</div>
  </div>
  <div class="galpon-stat verde">
    <div class="galpon-stat-ico">🥚</div>
    <div class="galpon-stat-val">{{ number_format($produccionHoy) }}</div>
    <div class="galpon-stat-lbl">Huevos hoy</div>
  </div>
  <div class="galpon-stat">
    <div class="galpon-stat-ico">📊</div>
    <div class="galpon-stat-val">{{ $posturaHoy ? round($posturaHoy,1).'%' : 'N/R' }}</div>
    <div class="galpon-stat-lbl">% postura</div>
  </div>
  <div class="galpon-stat rojo">
    <div class="galpon-stat-ico">💀</div>
    <div class="galpon-stat-val">{{ $mortSemana }}</div>
    <div class="galpon-stat-lbl">Muertes 7d</div>
  </div>
</div>

{{-- ALERTAS VACUNAS --}}
@if($alertasVacunas->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🔔 Alertas de vacunación</div>
  @foreach($alertasVacunas->take(4) as $v)
  @php $diasR = now()->diffInDays($v->fecha_programada, false); @endphp
  <div class="alerta-avi {{ $diasR < 0 ? 'vencida' : 'proxima' }}">
    <span>{{ $diasR < 0 ? '❌' : '⚠️' }}</span>
    <div>
      <strong>{{ $v->nombre_vacuna }}</strong>
      @if($v->nombre_lote)<span style="color:#64748b;"> · {{ $v->nombre_lote }}</span>@endif
      <br><span style="font-size:.74rem;">
        {{ \Carbon\Carbon::parse($v->fecha_programada)->format('d/m/Y') }}
        @if($diasR >= 0)(en {{ $diasR }} días)@endif
      </span>
    </div>
    <a href="{{ route('avicola.vacunacion') }}" style="margin-left:auto;font-size:.74rem;">Ver →</a>
  </div>
  @endforeach
</div>
@endif

{{-- MENÚ --}}
<div class="section-card">
  <div class="avicola-menu-grid">
    <a href="{{ route('avicola.postura') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">🥚</div>
      <div class="avicola-menu-lbl">Postura</div>
      <div class="avicola-menu-sub">Registro diario</div>
    </a>
    <a href="{{ route('avicola.engorde') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">🍗</div>
      <div class="avicola-menu-lbl">Engorde</div>
      <div class="avicola-menu-sub">Pesos semanales</div>
    </a>
    <a href="{{ route('avicola.mortalidad') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">💀</div>
      <div class="avicola-menu-lbl">Mortalidad</div>
      <div class="avicola-menu-sub">Causas y bajas</div>
    </a>
    <a href="{{ route('avicola.vacunacion') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">💉</div>
      <div class="avicola-menu-lbl">Vacunación</div>
      <div class="avicola-menu-sub">Calendario</div>
    </a>
    <a href="{{ route('avicola.conversion') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">🌾</div>
      <div class="avicola-menu-lbl">Conversión</div>
      <div class="avicola-menu-sub">CA alimenticia</div>
    </a>
    <a href="{{ route('avicola.reportes') }}" class="avicola-menu-card">
      <div class="avicola-menu-ico">📈</div>
      <div class="avicola-menu-lbl">Reportes</div>
      <div class="avicola-menu-sub">Análisis</div>
    </a>
  </div>
</div>

{{-- LOTES ACTIVOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐣 Lotes activos ({{ $totalLotes }})</div>
    <a href="{{ route('animales.index') }}" class="btn btn-sm btn-ghost">+ Nuevo lote</a>
  </div>
  @forelse($lotesConEtapa as $lote)
  @php
    $etapaClass = ['cria'=>'cria','levante'=>'levante','postura_produccion'=>'postura','desconocida'=>''][$lote->etapa] ?? '';
    $etapaLabel = ['cria'=>'Cría 0-6sem','levante'=>'Levante 7-18sem','postura_produccion'=>'Postura/Prod.','desconocida'=>'Sin fecha'][$lote->etapa] ?? '';
  @endphp
  <div class="lote-card {{ $etapaClass }}" style="margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="lote-nombre">🐔 {{ $lote->nombre_lote }}</div>
        <div class="lote-sub">
          {{ $lote->especie }}
          @if($lote->tipo_ave) · {{ str_replace('_',' ',$lote->tipo_ave) }}@endif
          @if($lote->linea_ave) · {{ $lote->linea_ave }}@endif
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:1.15rem;font-weight:800;color:#ea580c;">{{ number_format($lote->cantidad) }}</div>
        <div style="font-size:.68rem;color:#94a3b8;">aves</div>
      </div>
    </div>
    <div style="display:flex;gap:6px;margin-top:8px;align-items:center;flex-wrap:wrap;">
      @if($etapaLabel)
      <span class="etapa-badge etapa-{{ $etapaClass }}">{{ $etapaLabel }}</span>
      @endif
      @if($lote->semanas !== null)
      <span style="font-size:.72rem;color:#64748b;">Semana {{ $lote->semanas }}</span>
      @endif
      <a href="{{ route('avicola.postura') }}" class="btn btn-sm btn-ghost" style="margin-left:auto;font-size:.72rem;padding:3px 8px;">
        🥚 Postura
      </a>
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#64748b;">
    <div style="font-size:2rem;">🐔</div>
    <p style="margin-bottom:12px;">No hay lotes avícolas activos.</p>
    <a href="{{ route('animales.index') }}" class="btn btn-sm btn-primary">Registrar lote en Animales</a>
  </div>
  @endforelse
</div>

{{-- CURVA 30 DÍAS --}}
@if(count($chartLabels) > 1)
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📈 Producción últimos 30 días</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartProduccion"></canvas>
  </div>
</div>
@endif

<div style="margin-bottom:80px;"></div>

@endsection

@push('scripts')
<script>
var ctx = document.getElementById('chartProduccion');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: {!! json_encode($chartLabels) !!},
      datasets: [
        { label: 'Huevos/día', data: {!! json_encode($chartHuevos) !!},
          borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.12)',
          borderWidth:2, pointRadius:2, fill:true, tension:0.4, yAxisID:'y' },
        { label:'% Postura', data: {!! json_encode($chartPct) !!},
          borderColor:'#16a34a', backgroundColor:'transparent',
          borderWidth:1.5, pointRadius:1, borderDash:[4,4], yAxisID:'y2' }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{ font:{size:10} } } },
      scales: {
        x:  { ticks:{font:{size:9},maxTicksLimit:10}, grid:{display:false} },
        y:  { beginAtZero:true, position:'left',  ticks:{font:{size:9}} },
        y2: { beginAtZero:true, position:'right', max:100,
              ticks:{font:{size:9}}, grid:{display:false} }
      }
    }
  });
}
</script>
@endpush