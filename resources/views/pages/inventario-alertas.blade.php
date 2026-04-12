@extends('layouts.app')
@section('title','Alertas de inventario')
@section('page_title','Alertas de Inventario')
@section('back_url', route('inventario.index'))

@section('content')

@if($alertasStock->isEmpty() && $porVencer->isEmpty() && $vencidos->isEmpty())
<div class="empty-state">
  <div class="emoji">✅</div>
  <p><strong>Sin alertas activas.</strong></p>
  <p>Todo el inventario está en orden.</p>
</div>
@endif

{{-- STOCK BAJO MÍNIMO --}}
@if($alertasStock->count())
<p class="section-title" style="color:var(--rojo);">⚠️ Stock bajo mínimo ({{ $alertasStock->count() }})</p>
@foreach($alertasStock as $ins)
@php
  $pct = $ins->stock_minimo > 0 ? min(100, round($ins->cantidad_actual / $ins->stock_minimo * 100)) : 0;
@endphp
<div class="card mb-2" style="border-left:3px solid var(--rojo);">
  <div class="flex items-center justify-between">
    <div style="flex:1;">
      <div class="font-bold">{{ $ins->nombre }}</div>
      <div class="text-xs text-gray mt-2">{{ $ins->categoria }} @if($ins->proveedor) · {{ $ins->proveedor }} @endif</div>
      <div class="progress-bar mt-2" style="height:6px;">
        <div class="progress-fill" style="width:{{ $pct }}%;background:var(--rojo);"></div>
      </div>
      <div class="text-xs mt-2" style="color:var(--rojo);">
        Actual: <strong>{{ number_format($ins->cantidad_actual,1) }} {{ $ins->unidad }}</strong>
        — Mínimo: {{ number_format($ins->stock_minimo,1) }} {{ $ins->unidad }}
        ({{ $pct }}%)
      </div>
    </div>
    <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-primary" style="margin-left:14px;">
      Reponer
    </a>
  </div>
</div>
@endforeach
@endif

{{-- POR VENCER --}}
@if($porVencer->count())
<p class="section-title mt-3" style="color:var(--naranja);">⏰ Por vencer en 30 días ({{ $porVencer->count() }})</p>
@foreach($porVencer as $ins)
@php $diasRestantes = \Carbon\Carbon::parse($ins->fecha_vencimiento)->diffInDays(now()); @endphp
<div class="card mb-2" style="border-left:3px solid var(--naranja);">
  <div class="flex items-center justify-between">
    <div>
      <div class="font-bold">{{ $ins->nombre }}</div>
      <div class="text-xs text-gray mt-2">
        {{ number_format($ins->cantidad_actual,1) }} {{ $ins->unidad }} disponibles
      </div>
      <div class="text-xs mt-2" style="color:var(--naranja);">
        Vence el <strong>{{ \Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y') }}</strong>
        — faltan <strong>{{ $diasRestantes }} días</strong>
      </div>
    </div>
    <span class="badge badge-orange">{{ $diasRestantes }}d</span>
  </div>
</div>
@endforeach
@endif

{{-- VENCIDOS --}}
@if($vencidos->count())
<p class="section-title mt-3" style="color:var(--rojo);">❌ Vencidos ({{ $vencidos->count() }})</p>
@foreach($vencidos as $ins)
<div class="card mb-2" style="border-left:3px solid var(--rojo);opacity:.75;">
  <div class="flex items-center justify-between">
    <div>
      <div class="font-bold">{{ $ins->nombre }}</div>
      <div class="text-xs text-gray mt-2">{{ $ins->categoria }}</div>
      <div class="text-xs mt-2" style="color:var(--rojo);">
        Venció el {{ \Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y') }}
      </div>
    </div>
    <span class="badge badge-red">Vencido</span>
  </div>
</div>
@endforeach
@endif

@endsection