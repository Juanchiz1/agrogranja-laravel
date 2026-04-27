@extends('layouts.app')
@section('title', $cultivo->nombre . ' — Rentabilidad')
@section('page_title', $cultivo->nombre)
@section('back_url', route('rentabilidad.index') . '?anio=' . $anio)

@push('head')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
@endpush

@section('content')

{{-- SELECTOR AÑO --}}
<div class="flex items-center gap-2 mb-3">
  <a href="?anio={{ $anio-1 }}" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;">{{ $cultivo->tipo }} · {{ $anio }}</span>
  <a href="?anio={{ $anio+1 }}" class="btn btn-sm btn-secondary btn-icon">›</a>
</div>

{{-- KPIs PRINCIPALES --}}
<div class="stats-grid" style="grid-template-columns:repeat(2,1fr);margin-bottom:14px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($ingresoReal,0,',','.') }}</div>
    <div class="stat-label">Ingresos totales</div>
  </div>
  <div class="stat-card" style="background:var(--marron-bg);">
    <div class="stat-value text-brown" style="font-size:1rem;">${{ number_format($costoTotal,0,',','.') }}</div>
    <div class="stat-label">Costo total</div>
    @if($totalManoObra > 0)
    <div style="font-size:10px;color:#9CA3AF;margin-top:2px;">
      Insumos ${{ number_format($totalGastos,0,',','.') }} +
      M.O. ${{ number_format($totalManoObra,0,',','.') }}
    </div>
    @endif
  </div>
</div>

{{-- BALANCE GRANDE --}}
<div class="card mb-3" style="background:{{ $rentabilidad >= 0 ? 'var(--verde-bg)' : '#fef2f2' }};text-align:center;">
  <p class="text-xs font-bold text-gray" style="text-transform:uppercase;margin-bottom:4px;">Balance del cultivo</p>
  <p style="font-size:2rem;font-weight:800;color:{{ $rentabilidad >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">
    {{ $rentabilidad >= 0 ? '+' : '' }}${{ number_format($rentabilidad,0,',','.') }}
  </p>
  <div style="display:flex;justify-content:center;gap:20px;margin-top:8px;flex-wrap:wrap;">
    <div>
      <span class="text-xs text-gray">ROI </span>
      <span class="font-bold" style="color:{{ $roi >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">{{ $roi }}%</span>
    </div>
    @if($cultivo->area)
    <div>
      <span class="text-xs text-gray">Balance/ha </span>
      <span class="font-bold">${{ number_format($cultivo->area > 0 ? $rentabilidad/$cultivo->area : 0,0,',','.') }}</span>
    </div>
    @endif
    <div>
      <span class="text-xs text-gray">Cosechas </span>
      <span class="font-bold text-green">${{ number_format($totalCosechas,0,',','.') }}</span>
    </div>
    @if($totalManoObra > 0)
    <div>
      <span class="text-xs text-gray">Mano de obra </span>
      <span class="font-bold text-brown">${{ number_format($totalManoObra,0,',','.') }}</span>
    </div>
    @endif
  </div>
</div>

{{-- GRÁFICA MENSUAL --}}
<div class="card mb-3">
  <p class="font-bold mb-3">Evolución mensual {{ $anio }}</p>
  <canvas id="chartMensual" height="180"></canvas>
</div>

{{-- TABS --}}
<div class="tabs">
  <button class="tab-btn active" onclick="showTab('gastos', this)">
    💰 Gastos ({{ count($gastos ?? []) }})
  </button>
  <button class="tab-btn" onclick="showTab('ingresos', this)">
    📈 Ingresos ({{ count($ingresos ?? []) }})
  </button>
  <button class="tab-btn" onclick="showTab('cosechas', this)">
    🌾 Cosechas ({{ count($cosechas ?? []) }})
  </button>
  @if($manoDeObra->count() > 0)
  <button class="tab-btn" onclick="showTab('mano-obra', this)">
    👥 Mano de obra ({{ $manoDeObra->count() }})
  </button>
  @endif
</div>

{{-- TAB GASTOS --}}
<div id="tab-gastos">
  @if($gastosCat->count())
  <p class="section-title">Por categoría</p>
  @php $maxCat = $gastosCat->max('total') ?: 1; @endphp
  @foreach($gastosCat as $cat)
  <div class="mb-2">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-bold">{{ $cat->categoria }}</span>
      <span class="text-sm font-bold text-brown">${{ number_format($cat->total,0,',','.') }}</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" style="width:{{ round($cat->total/$maxCat*100) }}%;background:var(--marron-light);"></div>
    </div>
  </div>
  @endforeach

  {{-- Mano de obra como categoría extra en el resumen --}}
  @if($totalManoObra > 0)
  <div class="mb-2">
    <div class="flex items-center justify-between mb-2">
      <span class="text-sm font-bold">👥 Mano de obra</span>
      <span class="text-sm font-bold text-brown">${{ number_format($totalManoObra,0,',','.') }}</span>
    </div>
    <div class="progress-bar">
      <div class="progress-fill" style="width:{{ round($totalManoObra/$maxCat*100) }}%;background:#7C3AED55;"></div>
    </div>
  </div>
  @endif
  @endif

  @if(!empty($gastos) && count($gastos))
  <p class="section-title mt-3">Detalle de insumos</p>
  @foreach($gastos as $g)
  <div class="list-item">
    <div class="item-icon" style="background:var(--marron-bg);">💰</div>
    <div class="item-body">
      <div class="item-title">{{ $g->descripcion }}</div>
      <div class="item-sub">{{ $g->categoria }} · {{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</div>
    </div>
    <span class="font-bold text-brown text-sm">${{ number_format($g->valor,0,',','.') }}</span>
  </div>
  @endforeach
  @else
  <div class="empty-state" style="padding:28px 16px;"><p>Sin gastos de insumos registrados.</p></div>
  @endif
</div>

{{-- TAB INGRESOS --}}
<div id="tab-ingresos" style="display:none;">
  @if(!empty($ingresos) && count($ingresos))
  @foreach($ingresos as $i)
  <div class="list-item">
    <div class="item-icon" style="background:var(--verde-bg);">📈</div>
    <div class="item-body">
      <div class="item-title">{{ $i->descripcion }}</div>
      <div class="item-sub">
        {{ \Carbon\Carbon::parse($i->fecha)->format('d/m/Y') }}
        @if($i->comprador) · {{ $i->comprador }} @endif
      </div>
    </div>
    <span class="font-bold text-green text-sm">${{ number_format($i->valor_total,0,',','.') }}</span>
  </div>
  @endforeach
  @else
  <div class="empty-state" style="padding:28px 16px;"><p>Sin ingresos registrados para este cultivo.</p></div>
  @endif
</div>

{{-- TAB COSECHAS --}}
<div id="tab-cosechas" style="display:none;">
  @if(!empty($cosechas) && count($cosechas))
  @foreach($cosechas as $cs)
  <div class="list-item">
    <div class="item-icon" style="background:var(--verde-bg);">🌾</div>
    <div class="item-body">
      <div class="item-title">{{ $cs->producto ?? $cultivo->tipo }}</div>
      <div class="item-sub">
        {{ number_format($cs->cantidad,1) }} {{ $cs->unidad }}
        · {{ \Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y') }}
        · Calidad: {{ ucfirst($cs->calidad ?? '-') }}
      </div>
    </div>
    <span class="font-bold text-green text-sm">${{ number_format($cs->valor_estimado ?? 0,0,',','.') }}</span>
  </div>
  @endforeach
  @else
  <div class="empty-state" style="padding:28px 16px;"><p>Sin cosechas registradas para este cultivo.</p></div>
  @endif
</div>

{{-- TAB MANO DE OBRA --}}
@if($manoDeObra->count() > 0)
<div id="tab-mano-obra" style="display:none;">

  {{-- Resumen mano de obra --}}
  <div class="card mb-3" style="background:var(--marron-bg);">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-xs text-gray font-bold" style="text-transform:uppercase;margin-bottom:4px;">Total mano de obra</p>
        <p class="font-bold" style="font-size:1.4rem;color:var(--marron-dark);">
          ${{ number_format($totalManoObra,0,',','.') }}
        </p>
      </div>
      <div style="text-align:right;">
        <p class="text-xs text-gray">{{ $manoDeObra->count() }} pago(s)</p>
        <p class="text-xs text-gray">
          {{ $manoDeObra->filter(fn($p) => $p->dias)->sum('dias') }} días trabajados
        </p>
      </div>
    </div>
  </div>

  {{-- Lista de pagos --}}
  @foreach($manoDeObra as $pago)
  <div class="list-item">
    <div class="item-icon" style="background:#EDE9FE;">👤</div>
    <div class="item-body">
      <div class="item-title">
        {{ $pago->trabajador }}
        @if($pago->cargo)
          <span class="text-xs text-gray"> — {{ $pago->cargo }}</span>
        @endif
      </div>
      <div class="item-sub">
        {{ $pago->concepto ?? ucfirst($pago->tipo_pago) }}
        · {{ \Carbon\Carbon::parse($pago->fecha)->format('d/m/Y') }}
        @if($pago->dias) · {{ $pago->dias }} días @endif
      </div>
    </div>
    <span class="font-bold text-sm" style="color:#7C3AED;">
      ${{ number_format($pago->valor,0,',','.') }}
    </span>
  </div>
  @endforeach

</div>
@endif

@push('scripts')
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';

const meses    = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const gastos   = @json($gastosArr);
const ingresos = @json($ingresosArr);
const balance  = @json($balanceArr);

new Chart(document.getElementById('chartMensual'), {
  type: 'bar',
  data: {
    labels: meses,
    datasets: [
      { label: 'Ingresos', data: ingresos, backgroundColor: 'rgba(61,139,61,.75)', borderRadius: 4, order: 2 },
      { label: 'Costos',   data: gastos,   backgroundColor: 'rgba(122,79,42,.65)', borderRadius: 4, order: 2 },
      {
        label: 'Balance',
        data: balance,
        type: 'line',
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37,99,235,.1)',
        borderWidth: 2,
        pointRadius: 4,
        fill: false,
        tension: 0.4,
        order: 1,
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: { callbacks: { label: ctx => ' $' + ctx.raw.toLocaleString('es-CO') } }
    },
    scales: {
      y: { ticks: { callback: v => '$' + (v/1000).toFixed(0) + 'k' } }
    }
  }
});

function showTab(tab, btn) {
  ['gastos','ingresos','cosechas','mano-obra'].forEach(t => {
    const el = document.getElementById('tab-'+t);
    if (el) el.style.display = 'none';
  });
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  const target = document.getElementById('tab-'+tab);
  if (target) target.style.display = 'block';
  btn.classList.add('active');
}
</script>
@endpush
@endsection