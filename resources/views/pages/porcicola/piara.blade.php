@extends('layouts.app')
@section('title','Piara Porcícola')
@section('page_title','🐷 Piara Porcícola')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/porcicola.css') }}">
@endpush

@section('content')
@php
  $iconosCat = [
    'lechon'           => ['🐷','Lechones','cat-lechon'],
    'levante'          => ['🐖','Levante','cat-levante'],
    'ceba'             => ['🏋️','Ceba','cat-ceba'],
    'hembra_cria'      => ['🐷','Hembras cría','cat-hembra'],
    'verraco'          => ['🐗','Verracos','cat-verraco'],
    'vientre_descarte' => ['🔴','Descarte','cat-lechon'],
    'otro'             => ['🐾','Otros',''],
  ];
@endphp

{{-- STATS ──────────────────────────────────────────────────────── --}}
<div class="piara-stats">
  <div class="piara-stat">
    <div class="piara-stat-ico">🐷</div>
    <div class="piara-stat-val">{{ number_format($totalCerdos) }}</div>
    <div class="piara-stat-lbl">Total piara</div>
  </div>
  <div class="piara-stat naranja">
    <div class="piara-stat-ico">🤰</div>
    <div class="piara-stat-val">{{ $hembrasPreniadas }}</div>
    <div class="piara-stat-lbl">Preñadas</div>
  </div>
  <div class="piara-stat azul">
    <div class="piara-stat-ico">🍼</div>
    <div class="piara-stat-val">{{ $enLactancia }}</div>
    <div class="piara-stat-lbl">En lactancia</div>
  </div>
  <div class="piara-stat verde">
    <div class="piara-stat-ico">🏃</div>
    <div class="piara-stat-val">{{ $desteteProximos->count() }}</div>
    <div class="piara-stat-lbl">Destetar pronto</div>
  </div>
</div>

{{-- ALERTAS URGENTES --}}
@if($partosProximos->count() || $desteteProximos->count() || $alertasSanidad->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🔔 Alertas</div>

  @foreach($partosProximos as $c)
  @php $dias = now()->diffInDays($c->fecha_probable_parto, false); @endphp
  <div class="alerta-porc {{ $dias <= 3 ? 'urgente' : 'aviso' }}">
    <span>{{ $dias <= 3 ? '🚨' : '⚠️' }}</span>
    <div>
      <strong>Parto próximo — {{ $c->nombre_lote }}</strong>
      · Camada #{{ $c->numero_camada }}<br>
      <span style="font-size:.74rem;">
        {{ \Carbon\Carbon::parse($c->fecha_probable_parto)->format('d/m/Y') }}
        @if($dias >= 0)(en {{ $dias }} días)@else(¡hoy o pasado!)@endif
      </span>
    </div>
    <a href="{{ route('porcicola.reproductivo') }}" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Ver →</a>
  </div>
  @endforeach

  @foreach($desteteProximos as $c)
  @php $diasLact = now()->diffInDays(\Carbon\Carbon::parse($c->fecha_parto_real)); @endphp
  <div class="alerta-porc aviso">
    <span>🍼</span>
    <div>
      <strong>Destete — {{ $c->nombre_lote }}</strong>
      · {{ $c->lechones_nacidos_vivos }} lechones<br>
      <span style="font-size:.74rem;">{{ $diasLact }} días de lactancia</span>
    </div>
    <a href="{{ route('porcicola.reproductivo') }}" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Destetar →</a>
  </div>
  @endforeach

  @foreach($alertasSanidad->take(3) as $s)
  @php $diasR = now()->diffInDays($s->fecha_programada, false); @endphp
  <div class="alerta-porc {{ $diasR < 0 ? 'urgente' : 'aviso' }}">
    <span>{{ $diasR < 0 ? '❌' : '💉' }}</span>
    <div>
      <strong>{{ $s->nombre_protocolo }}</strong><br>
      <span style="font-size:.74rem;">
        {{ \Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y') }}
        @if($diasR >= 0)(en {{ $diasR }} días)@else(vencida)@endif
      </span>
    </div>
    <a href="{{ route('porcicola.sanidad') }}" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Aplicar →</a>
  </div>
  @endforeach
</div>
@endif

{{-- MENÚ --}}
<div class="section-card">
  <div class="porcicola-menu-grid">
    <a href="{{ route('porcicola.reproductivo') }}" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">🤰</div>
      <div class="porcicola-menu-lbl">Reproductivo</div>
      <div class="porcicola-menu-sub">Camadas · Partos</div>
    </a>
    <a href="{{ route('porcicola.ceba') }}" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">🏋️</div>
      <div class="porcicola-menu-lbl">Ceba</div>
      <div class="porcicola-menu-sub">Pesos · CA</div>
    </a>
    <a href="{{ route('porcicola.sanidad') }}" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">💉</div>
      <div class="porcicola-menu-lbl">Sanidad</div>
      <div class="porcicola-menu-sub">PPC · Parvo · Lepto</div>
    </a>
    <a href="{{ route('porcicola.reportes') }}" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">📈</div>
      <div class="porcicola-menu-lbl">Reportes</div>
      <div class="porcicola-menu-sub">Análisis</div>
    </a>
    <a href="{{ route('animales.index') }}" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">📋</div>
      <div class="porcicola-menu-lbl">Inventario</div>
      <div class="porcicola-menu-sub">Registrar cerdos</div>
    </a>
  </div>
</div>

{{-- INVENTARIO POR CATEGORÍA --}}
@if($inventario->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐷 Inventario por categoría</div>
  <div class="inventario-grid">
    @foreach($inventario as $cat)
    @php $cfg = $iconosCat[$cat->categoria_porcina ?? 'otro'] ?? ['🐷','Otro','']; @endphp
    <div class="inv-cat-card">
      <div class="inv-cat-ico">{{ $cfg[0] }}</div>
      <div class="inv-cat-val">{{ number_format($cat->total) }}</div>
      <div class="inv-cat-lbl">{{ $cfg[1] }}</div>
    </div>
    @endforeach
  </div>
</div>
@endif

{{-- LOTES --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐖 Lotes activos</div>
    <a href="{{ route('animales.index') }}" class="btn btn-sm btn-ghost">+ Nuevo</a>
  </div>
  @forelse($lotes as $l)
  @php
    $cfg = $iconosCat[$l->categoria_porcina ?? 'otro'] ?? ['🐷','Otro',''];
    $esHembra = $l->categoria_porcina === 'hembra_cria';
  @endphp
  <div class="cerda-card {{ $l->camada_activa ? 'prenada' : ($l->en_lactancia ? 'parida' : 'disponible') }}">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="cerda-nombre">{{ $cfg[0] }} {{ $l->nombre_lote }}</div>
        <div class="cerda-sub">
          {{ $l->especie }} · {{ number_format($l->cantidad) }} animales
          @if($l->raza_porcina) · {{ $l->raza_porcina }}@endif
          @if($l->ubicacion) · 📍{{ $l->ubicacion }}@endif
        </div>
        <span class="cat-badge {{ $cfg[2] }}">{{ $cfg[1] }}</span>
      </div>
      <div style="text-align:right;">
        @if($l->peso_promedio)
        <div style="font-size:1.1rem;font-weight:800;color:#ea580c;">{{ $l->peso_promedio }}kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">peso prom.</div>
        @endif
      </div>
    </div>
    @if($esHembra)
    <div style="margin-top:8px;font-size:.75rem;color:#64748b;">
      @if($l->camada_activa)
        🤰 Preñada · parto probable {{ \Carbon\Carbon::parse($l->camada_activa->fecha_probable_parto)->format('d/m/Y') }}
      @elseif($l->en_lactancia)
        🍼 En lactancia · {{ now()->diffInDays(\Carbon\Carbon::parse($l->en_lactancia->fecha_parto_real)) }} días
      @else
        ✅ Disponible para servicio
      @endif
      · {{ $l->num_partos ?? 0 }} partos
    </div>
    @endif
  </div>
  @empty
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">🐷</div>
    <p>No hay cerdos activos.</p>
    <a href="{{ route('animales.index') }}" class="btn btn-sm btn-primary">Registrar en Animales</a>
  </div>
  @endforelse
</div>

<div style="margin-bottom:80px;"></div>
@endsection