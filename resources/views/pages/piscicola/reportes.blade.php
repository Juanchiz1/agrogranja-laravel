@extends('layouts.app')
@section('title','Reportes Piscicola')
@section('page_title','Reportes Piscicola')
@section('back_url', route('piscicola.estanques'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/piscicola.css') }}">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- TOTALES HISTÓRICOS --}}
@if($totales && $totales->cosechas > 0)
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">Totales historicos</div>
  <div class="pisc-stats">
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#9937;</div>
      <div class="pisc-stat-val">{{ $totales->cosechas }}</div>
      <div class="pisc-stat-lbl">Cosechas</div>
    </div>
    <div class="pisc-stat verde">
      <div class="pisc-stat-ico">&#128031;</div>
      <div class="pisc-stat-val">{{ round($totales->kg_total ?? 0, 1) }}</div>
      <div class="pisc-stat-lbl">kg producidos</div>
    </div>
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#128200;</div>
      <div class="pisc-stat-val">{{ round($totales->ca_promedio ?? 0, 2) }}</div>
      <div class="pisc-stat-lbl">CA promedio</div>
    </div>
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#128077;</div>
      <div class="pisc-stat-val">{{ round($totales->sobrev_promedio ?? 0, 1) }}%</div>
      <div class="pisc-stat-lbl">Sobrevivencia</div>
    </div>
  </div>
  @if($totales->ingresos_total)
  <div style="background:var(--pisc-bg);border-radius:10px;padding:10px;text-align:center;margin-top:8px;">
    <div style="font-size:.75rem;color:var(--pisc-gris);">Ingresos totales</div>
    <div style="font-size:1.4rem;font-weight:800;color:var(--pisc-verde);">
      ${{ number_format($totales->ingresos_total, 0, ',', '.') }}
    </div>
    @if($totales->rendimiento_kg_m2)
    <div style="font-size:.75rem;color:#64748b;">
      Rendimiento promedio: {{ round($totales->rendimiento_kg_m2, 2) }} kg/m²
    </div>
    @endif
  </div>
  @endif
</div>
@endif

{{-- SIEMBRAS ACTIVAS --}}
@if($siembrasActivas->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Siembras activas</div>
  @foreach($siembrasActivas as $s)
  @php
    $biomasaS  = round((float)($s->biomasa_actual_kg ?? 0), 1);
    $pesoPromS = round((float)($s->peso_promedio_actual_g ?? 0), 0);
    $sobrevS   = round((float)($s->sobrevivencia), 1);
    $tasaCrecS = $s->ultimo_muestreo ? $s->ultimo_muestreo->ganancia_diaria_g : null;
    $aliAcumS  = round((float)($s->alimento_acumulado_kg ?? 0), 1);
    $rendS     = ($s->area_m2 && $s->area_m2 > 0 && $biomasaS > 0)
                 ? round($biomasaS / $s->area_m2, 2) : null;
  @endphp
  <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.9rem;">{{ $s->nombre_estanque }}</div>
        <div style="font-size:.73rem;color:#64748b;">
          {{ $s->especie_cultivada }} · Dia {{ $s->dias_cultivo }} · Sembrado {{ $s->cantidad_alevinos }} alevinos
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:800;color:var(--pisc-azul);">{{ $biomasaS }} kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">biomasa est.</div>
      </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:.74rem;color:#64748b;margin-top:6px;">
      @if($pesoPromS > 0)<span>Peso: <strong>{{ $pesoPromS }} g</strong></span>@endif
      <span>Sobreviv: <strong style="color:{{ $sobrevS >= 90 ? '#15803d' : '#d97706' }};">{{ $sobrevS }}%</strong></span>
      @if($tasaCrecS !== null)<span>Crec: <strong>{{ $tasaCrecS }} g/dia</strong></span>@endif
      <span>Alimento acum: <strong>{{ $aliAcumS }} kg</strong></span>
      @if($rendS !== null)<span>Rend: <strong>{{ $rendS }} kg/m²</strong></span>@endif
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- MEJORES COSECHAS --}}
@if($mejoresCosechas->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Mejores cosechas (por CA)</div>
  @foreach($mejoresCosechas as $c)
  @php
    $cCA2    = $c->conversion_alimenticia;
    $caColor2 = $cCA2 <= 1.5 ? '#15803d' : ($cCA2 <= 2.0 ? '#d97706' : '#1e293b');
    $cFecha2 = \Carbon\Carbon::parse($c->fecha)->format('d/m/Y');
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:600;">{{ $c->nombre_estanque }}</div>
      <div style="font-size:.72rem;color:#64748b;">
        {{ $cFecha2 }} · {{ $c->biomasa_cosechada_kg }} kg
        @if($c->sobrevivencia) · Sobreviv: {{ $c->sobrevivencia }}%@endif
      </div>
    </div>
    <div style="font-size:1.1rem;font-weight:800;color:{{ $caColor2 }};">
      CA {{ $cCA2 }}
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- ALERTAS CALIDAD --}}
@if($alertasCalidad->count())
<div class="section-card">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    Alertas calidad del agua (15 dias)
  </div>
  @foreach($alertasCalidad as $al)
  @php
    $alFecha = \Carbon\Carbon::parse($al->fecha)->format('d/m/Y');
  @endphp
  <div class="alerta-pisc critica">
    <span>&#9888;</span>
    <div>
      <strong>{{ $al->nombre_estanque }}</strong> — {{ $alFecha }}
      <div style="font-size:.72rem;margin-top:2px;">
        @if($al->oxigeno_mgl !== null) O2: {{ $al->oxigeno_mgl }} mg/L @endif
        @if($al->ph !== null) pH: {{ $al->ph }} @endif
        @if($al->temperatura_c !== null) Temp: {{ $al->temperatura_c }}°C @endif
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- CURVA PRODUCCIÓN MENSUAL --}}
@if(count($chartMeses) > 1)
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Produccion mensual (kg)</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartProdMes"></canvas>
  </div>
</div>
@endif

<div style="margin-bottom:80px;"></div>

@endsection

@push('scripts')
<script>
var ctxProd = document.getElementById('chartProdMes');
if (ctxProd) {
  new Chart(ctxProd, {
    type: 'bar',
    data: {
      labels: {!! json_encode($chartMeses) !!},
      datasets: [{
        label: 'kg cosechados',
        data: {!! json_encode($chartKg) !!},
        backgroundColor: 'rgba(2,132,199,.7)',
        borderColor: '#0284c7', borderWidth: 1,
        borderRadius: 4
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { font: { size: 9 } } }
      }
    }
  });
}
</script>
@endpush