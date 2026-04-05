@extends('layouts.app')
@section('title','Bienvenida')
@section('content')
<div id="slide-0" class="onboard-slide">
  <div class="onboard-emoji">🌾</div>
  <h2 class="onboard-title">¡Bienvenido a<br>Agrogranja!</h2>
  <p class="onboard-desc">Tu herramienta para gestionar cultivos, animales, gastos y actividades de tu finca.</p>
  <div class="dots"><div class="dot active"></div><div class="dot"></div><div class="dot"></div><div class="dot"></div></div>
  <button class="btn btn-primary" style="margin-top:28px;padding:13px 36px;" onclick="nextSlide()">Siguiente →</button>
</div>
<div id="slide-1" class="onboard-slide hidden">
  <div class="onboard-emoji">🌱</div>
  <h2 class="onboard-title">Registra tus cultivos y animales</h2>
  <p class="onboard-desc">Lleva el control de cada siembra, cosecha, lote de animales y su estado en tiempo real.</p>
  <div class="dots"><div class="dot"></div><div class="dot active"></div><div class="dot"></div><div class="dot"></div></div>
  <button class="btn btn-primary" style="margin-top:28px;padding:13px 36px;" onclick="nextSlide()">Siguiente →</button>
</div>
<div id="slide-2" class="onboard-slide hidden">
  <div class="onboard-emoji">💰</div>
  <h2 class="onboard-title">Controla ingresos y gastos</h2>
  <p class="onboard-desc">Registra semillas, fertilizantes, mano de obra y ventas. Analiza la rentabilidad de tu finca.</p>
  <div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot active"></div><div class="dot"></div></div>
  <button class="btn btn-primary" style="margin-top:28px;padding:13px 36px;" onclick="nextSlide()">Siguiente →</button>
</div>
<div id="slide-3" class="onboard-slide hidden">
  <div class="onboard-emoji">📅</div>
  <h2 class="onboard-title">Planifica tus actividades</h2>
  <p class="onboard-desc">Programa riegos, vacunaciones, cosechas y más. Nunca olvides una tarea importante.</p>
  <div class="dots"><div class="dot"></div><div class="dot"></div><div class="dot"></div><div class="dot active"></div></div>
  <form method="POST" action="{{ route('onboarding.complete') }}" style="margin-top:28px;">
    @csrf
    <button type="submit" class="btn btn-primary" style="padding:13px 36px;">¡Empezar! 🚀</button>
  </form>
</div>
@push('scripts')
<script>
let s = 0;
function nextSlide() {
  document.getElementById('slide-'+s).classList.add('hidden');
  s++;
  document.getElementById('slide-'+s).classList.remove('hidden');
}
</script>
@endpush
@endsection
