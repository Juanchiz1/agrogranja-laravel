{{-- resources/views/auth/welcome.blade.php --}}
@extends('layouts.app')
@section('title','Bienvenida')
@section('content')
<div class="welcome-bg">
  <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
    <div class="welcome-logo">🌾</div>
    <h1 class="welcome-title">Agrogranja</h1>
    <p class="welcome-sub">Tu finca en la palma de tu mano.<br>Gestiona cultivos, gastos y cosechas.</p>
    <div style="display:flex;flex-direction:column;gap:12px;width:100%;max-width:320px;">
      <a href="{{ route('register') }}" class="btn btn-full" style="background:#fff;color:var(--verde-dark);font-size:1rem;padding:15px;">✨ Crear cuenta gratis</a>
      <a href="{{ route('login') }}" class="btn btn-full btn-ghost" style="color:rgba(255,255,255,.9);border:2px solid rgba(255,255,255,.3);padding:14px;">Ya tengo cuenta</a>
    </div>
  </div>
  <div style="background:rgba(255,255,255,.1);border-radius:20px 20px 0 0;padding:24px 22px;width:100%;max-width:430px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;text-align:center;">
      @foreach([['🌱','Cultivos','Siembras y cosechas'],['💰','Gastos','Insumos y costos'],['📅','Agenda','Actividades'],['📊','Reportes','Análisis y gráficas']] as $f)
      <div><div style="font-size:1.7rem;">{{ $f[0] }}</div><div style="color:#fff;font-weight:700;font-size:.85rem;margin:4px 0 2px;">{{ $f[1] }}</div><div style="color:rgba(255,255,255,.7);font-size:.72rem;">{{ $f[2] }}</div></div>
      @endforeach
    </div>
    <p style="text-align:center;color:rgba(255,255,255,.55);font-size:.72rem;margin-top:16px;">Demo: demo@demo.com / demo123</p>
  </div>
</div>
@endsection
