@extends('layouts.app')
@section('title','Reportes Porcícola')
@section('page_title','📈 Reportes Porcícola')
@section('back_url', route('porcicola.piara'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/porcicola.css') }}">
@endpush

@section('content')

{{-- MÉTRICAS REPRODUCTIVAS --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">🐷 Métricas reproductivas</div>
  <div class="piara-stats">
    <div class="piara-stat">
      <div class="piara-stat-ico">🍼</div>
      <div class="piara-stat-val">{{ $totalCamadas }}</div>
      <div class="piara-stat-lbl">Camadas totales</div>
    </div>
    <div class="piara-stat verde">
      <div class="piara-stat-ico">🐷</div>
      <div class="piara-stat-val">{{ $promedioNacidos }}</div>
      <div class="piara-stat-lbl">Lechones/camada</div>
    </div>
    <div class="piara-stat azul">
      <div class="piara-stat-ico">✅</div>
      <div class="piara-stat-val">{{ $promDestetados }}</div>
      <div class="piara-stat-lbl">Destetados/camada</div>
    </div>
    <div class="piara-stat {{ $pctMortPreD > 10 ? '' : 'naranja' }}">
      <div class="piara-stat-ico">💀</div>
      <div class="piara-stat-val" style="color:{{ $pctMortPreD > 10 ? '#dc2626' : '#1e293b' }};">{{ $pctMortPreD }}%</div>
      <div class="piara-stat-lbl">Mort. pre-destete</div>
    </div>
  </div>
  @if($promedioNacidos > 0)
  <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-top:8px;font-size:.78rem;">
    @if($promedioNacidos >= 11) 🏆 Excelente prolificidad (> 11 lechones)
    @elseif($promedioNacidos >= 9) ✅ Buena prolificidad (9-10 lechones)
    @else ⚠️ Prolificidad baja (< 9 lechones — revisar nutrición y manejo reproductivo)
    @endif
    · Eficiencia destete: {{ $totalCamadas > 0 ? round($promDestetados / $promedioNacidos * 100, 1) : 0 }}%
    · Mortalidad pre-destete: {{ $pctMortPreD <= 8 ? '✅ Normal' : '❌ Alta (meta < 8%)' }}
  </div>
  @endif
</div>

{{-- MEJOR CA --}}
@if($mejorCA)
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🏆 Mejor conversión del mes</div>
  <div style="text-align:center;padding:10px;">
    <div style="font-size:.85rem;color:#64748b;">{{ $mejorCA->nombre_lote }} · Semana {{ $mejorCA->semana }}</div>
    <div class="{{ $mejorCA->conversion_alimenticia <= 2.8 ? 'ca-val-ok' : 'ca-val-med' }}" style="font-size:2rem;">
      {{ $mejorCA->conversion_alimenticia }}
    </div>
    <div style="font-size:.75rem;color:#94a3b8;">kg alimento / kg ganancia</div>
  </div>
</div>
@endif

{{-- PARIDAD HEMBRAS --}}
@if($hembrasParidad->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐷 Distribución de partos (hembras activas)</div>
  @php $totalHembras = $hembrasParidad->sum('cantidad'); @endphp
  @foreach($hembrasParidad as $hp)
  <div style="margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:2px;">
      <span>
        {{ $hp->num_partos == 0 ? 'Primerizas (0 partos)' : 'Parto #'.$hp->num_partos }}
        @if($hp->num_partos >= 8)<span style="font-size:.7rem;color:#dc2626;"> · Considerar descarte</span>@endif
      </span>
      <strong>{{ $hp->cantidad }}</strong>
    </div>
    <div style="background:#e2e8f0;border-radius:4px;height:6px;overflow:hidden;">
      <div style="width:{{ $totalHembras > 0 ? round(($hp->cantidad/$totalHembras)*100) : 0 }}%;
                  height:100%;border-radius:4px;
                  background:{{ $hp->num_partos <= 6 ? '#ec4899' : '#94a3b8' }};"></div>
    </div>
  </div>
  @endforeach
  <div style="font-size:.75rem;color:#64748b;margin-top:6px;">
    💡 Vida productiva ideal de la cerda: partos 2-7. Después del parto 8, evaluar descarte.
  </div>
</div>
@endif

{{-- INVENTARIO ACTUAL --}}
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐖 Estado actual de la piara</div>
  @forelse($lotes as $l)
  @php
    // Sin emojis en PHP (Windows Blade no los soporta)
    $catMap = [
      'lechon'           => 'Lechon',
      'levante'          => 'Levante',
      'ceba'             => 'Ceba',
      'hembra_cria'      => 'Hembra cria',
      'verraco'          => 'Verraco',
      'vientre_descarte' => 'Descarte',
      'otro'             => 'Otro',
    ];
    $catLabel = $catMap[$l->categoria_porcina ?? 'otro'] ?? 'Otro';
    $esHembra = ($l->categoria_porcina === 'hembra_cria');
    $catReemplazada = str_replace('_', ' ', $l->categoria_porcina ?? 'sin categoria');
  @endphp
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:700;">{{ $l->nombre_lote }}</div>
      <div style="font-size:.72rem;color:#64748b;">
        {{ $catReemplazada }}
        @if($l->raza_porcina)
        · {{ $l->raza_porcina }}
        @endif
        @if($esHembra)
        @if(($l->num_partos ?? 0) > 0)
        · {{ $l->num_partos }} partos
        @endif
        @endif
      </div>
    </div>
    <div style="text-align:right;">
      <div style="font-weight:700;color:#f97316;">{{ number_format($l->cantidad) }} animales</div>
      @if($l->peso_promedio)
      <div style="font-size:.7rem;color:#94a3b8;">{{ $l->peso_promedio }} kg prom.</div>
      @endif
    </div>
  </div>
  @empty
  <div style="text-align:center;padding:20px;color:#94a3b8;">Sin lotes porcinos activos.</div>
  @endforelse
</div>

<div style="margin-bottom:80px;"></div>
@endsection