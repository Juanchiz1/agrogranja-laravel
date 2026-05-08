<!DOCTYPE html>
<html lang="es" data-mode="auto">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#4CAF50">
  <title>@yield('title', 'Agrogranja') · Agrogranja</title>
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌾</text></svg>">
  @stack('head')
<style>
.sidebar-grupo { display:flex; flex-direction:column; }
#subAnimales { overflow:hidden; transition:max-height .3s ease; }
#btnToggleAnimales:hover { background:rgba(255,255,255,.12) !important; color:#fff !important; }
</style>
</head>
<body>

{{-- MODE TOGGLE --}}
@if(session('usuario_id'))
<button id="modeToggle" class="mode-toggle" title="Cambiar vista">
  <span class="mode-icon-mobile">📱</span>
  <span class="mode-icon-pc">🖥️</span>
  <span class="mode-label-mobile">Vista PC</span>
  <span class="mode-label-pc">Vista Móvil</span>
</button>
@endif

<div class="app-shell" id="appShell">

  {{-- SIDEBAR (modo PC) --}}
  @if(session('usuario_id'))
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="sidebar-logo">
        <img src="{{ asset('img/logo-seedling-transparente.svg') }}" alt="" style="height:60px;width:60px;margin-right:8px;">
      </span>
      <div>
        <div class="sidebar-name">Agrogranja</div>
        <div class="sidebar-finca">{{ session('usuario_nombre', 'Mi Finca') }}</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      {{-- Inicio (siempre visible) --}}
      <a href="{{ route('dashboard') }}"        class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="sidebar-icon">🏠</span><span>Inicio</span>
      </a>

      {{-- Cultivos: solo si tiene línea de cultivos --}}
      @if(in_array('cultivos', $lineasActivas))
      <a href="{{ route('cultivos.index') }}"   class="sidebar-item {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">🌱</span><span>Cultivos</span>
      </a>
      @endif

      {{-- Gastos e Ingresos: siempre (transversales) --}}
      <a href="{{ route('gastos.index') }}"     class="sidebar-item {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">💰</span><span>Gastos</span>
      </a>
      <a href="{{ route('ingresos.index') }}"   class="sidebar-item {{ request()->routeIs('ingresos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📈</span><span>Ingresos</span>
      </a>

      {{-- Animales: si tiene cualquier línea animal — con subitems desplegables --}}
      @if(\App\Models\LineaProductiva::tieneAnimales())
      @php
        $tieneSubLineas = \App\Models\LineaProductiva::activa('bovino')
            || \App\Models\LineaProductiva::activa('avicola')
            || \App\Models\LineaProductiva::activa('porcino')
            || \App\Models\LineaProductiva::activa('piscicola');
        $animalesActivo = request()->routeIs('animales.*')
            || request()->routeIs('bovino.*')
            || request()->routeIs('avicola.*')
            || request()->routeIs('porcicola.*')
            || request()->routeIs('piscicola.*')
            || request()->routeIs('produccion-animal.*');
      @endphp
      <div class="sidebar-grupo" id="grupoAnimales">
        {{-- Botón principal Animales (siempre navegable) --}}
        <div style="display:flex;align-items:center;">
          <a href="{{ route('animales.index') }}"
             class="sidebar-item {{ $animalesActivo ? 'active' : '' }}"
             style="flex:1;">
            <span class="sidebar-icon">🐄</span><span>Animales</span>
          </a>
          @if($tieneSubLineas)
          <button type="button"
                  onclick="toggleGrupoAnimales()"
                  id="btnToggleAnimales"
                  style="background:none;border:none;cursor:pointer;color:rgba(255,255,255,.6);
                         padding:6px 8px;border-radius:6px;font-size:.8rem;transition:all .2s;"
                  title="Desplegar módulos">
            <span id="arrowAnimales">&#9660;</span>
          </button>
          @endif
        </div>

        {{-- Sub-ítems desplegables --}}
        @if($tieneSubLineas)
        <div id="subAnimales"
             style="overflow:hidden;transition:max-height .3s ease;max-height:{{ $animalesActivo ? '300px' : '0px' }};">
          <div style="padding-left:12px;display:flex;flex-direction:column;gap:2px;padding-top:2px;">

            {{-- Producción del día --}}
            <a href="{{ route('produccion-animal.index') }}"
               class="sidebar-item {{ request()->routeIs('produccion-animal.*') ? 'active' : '' }}"
               style="font-size:.82rem;padding:7px 10px;">
              <span class="sidebar-icon" style="font-size:.95rem;">&#127860;</span>
              <span>Produccion</span>
            </a>

            {{-- Hato Bovino --}}
            @if(\App\Models\LineaProductiva::activa('bovino'))
            <a href="{{ route('bovino.hato') }}"
               class="sidebar-item {{ request()->routeIs('bovino.*') ? 'active' : '' }}"
               style="font-size:.82rem;padding:7px 10px;">
              <span class="sidebar-icon" style="font-size:.95rem;">🐮</span>
              <span>Hato Bovino</span>
            </a>
            @endif

            {{-- Avícola --}}
            @if(\App\Models\LineaProductiva::activa('avicola'))
            <a href="{{ route('avicola.galpon') }}"
               class="sidebar-item {{ request()->routeIs('avicola.*') ? 'active' : '' }}"
               style="font-size:.82rem;padding:7px 10px;">
              <span class="sidebar-icon" style="font-size:.95rem;">🐔</span>
              <span>Avicola</span>
            </a>
            @endif

            {{-- Porcícola --}}
            @if(\App\Models\LineaProductiva::activa('porcino'))
            <a href="{{ route('porcicola.piara') }}"
               class="sidebar-item {{ request()->routeIs('porcicola.*') ? 'active' : '' }}"
               style="font-size:.82rem;padding:7px 10px;">
              <span class="sidebar-icon" style="font-size:.95rem;">🐷</span>
              <span>Porcicola</span>
            </a>
            @endif

            {{-- Piscícola --}}
            @if(\App\Models\LineaProductiva::activa('piscicola'))
            <a href="{{ route('piscicola.estanques') }}"
               class="sidebar-item {{ request()->routeIs('piscicola.*') ? 'active' : '' }}"
               style="font-size:.82rem;padding:7px 10px;">
              <span class="sidebar-icon" style="font-size:.95rem;">&#128031;</span>
              <span>Piscicola</span>
            </a>
            @endif

          </div>
        </div>
        @endif
      </div>
      @endif

      {{-- Personas (siempre — gestión de trabajadores aplica a todos) --}}
      <a href="{{ route('personas.index') }}"   class="sidebar-item {{ request()->routeIs('personas.*') ? 'active' : '' }}">
        <span class="sidebar-icon">👥</span><span>Personas</span>
      </a>

      {{-- Agenda (siempre — todos manejan tareas) --}}
      <a href="{{ route('calendario.index') }}" class="sidebar-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📅</span><span>Agenda</span>
      </a>

      {{-- Cosechas: solo si tiene cultivos --}}
      @if(in_array('cultivos', $lineasActivas))
      <a href="{{ route('cosechas.index') }}"   class="sidebar-item {{ request()->routeIs('cosechas.*') ? 'active' : '' }}">
        <span class="sidebar-icon">🌾</span><span>Cosechas</span>
      </a>
      @endif

      {{-- Inventario (siempre — agroinsumos, alimento balanceado, medicamentos) --}}
      <a href="{{ route('inventario.index') }}" class="sidebar-item {{ request()->routeIs('inventario.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📦</span><span>Inventario</span>
      </a>

      {{-- Reportes (siempre) --}}
      <a href="{{ route('reportes.index') }}"   class="sidebar-item {{ request()->routeIs('reportes.*') || request()->routeIs('rentabilidad.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📊</span><span>Reportes</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="{{ route('perfil.index') }}" class="sidebar-item {{ request()->routeIs('perfil.*') ? 'active' : '' }}">
        <span class="sidebar-icon">👤</span><span>Mi Perfil</span>
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="sidebar-item sidebar-logout" onclick="return confirm('¿Cerrar sesión?')">
          <span class="sidebar-icon">🚪</span><span>Cerrar sesión</span>
        </button>
      </form>
    </div>
  </aside>
  @endif

  {{-- MAIN CONTENT --}}
  <main class="main-content" id="mainContent">

    {{-- TOP BAR --}}
    @if(session('usuario_id'))
    <header class="top-bar">
      <div class="top-bar-left">
        @hasSection('back_url')
        <a href="@yield('back_url')" class="btn-back">←</a>
        @else
        <button class="btn-back mobile-only" onclick="history.back()">←</button>
        @endif
        <h1 class="top-bar-title">@yield('page_title', 'Agrogranja')</h1>
      </div>
      <div class="top-bar-right">
        <a href="{{ route('perfil.index') }}" class="top-avatar" title="Mi perfil">👤</a>
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
          @csrf
          <button type="submit" class="btn-logout" title="Cerrar sesión" onclick="return confirm('¿Cerrar sesión?')">🚪</button>
        </form>
      </div>
    </header>
    @endif

    {{-- FLASH MESSAGES --}}
    @if(session('msg'))
    <div class="alert alert-{{ session('msgType','success') }} alert-flash" id="flashMsg">
      @if(session('msgType') === 'success') ✅ @elseif(session('msgType') === 'warning') ⚠️ @else ❌ @endif
      {{ session('msg') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error alert-flash">
      ❌ {{ $errors->first() }}
    </div>
    @endif

    {{-- PAGE CONTENT --}}
    <div class="page-content">
      @yield('content')
    </div>

    {{-- BOTTOM NAV (modo móvil)
         Tiene solo 5 slots. Priorizamos así:
           1. Inicio (siempre)
           2. Cultivos si tiene cultivos, si no → Animales
           3. Gastos (siempre)
           4. Agenda (siempre)
           5. Reportes (siempre)
         Si tiene CULTIVOS + ANIMALES, mostramos ambos y quitamos Gastos
         del bottom (sigue accesible desde el menú lateral en PC y desde
         el menú principal del dashboard).
    --}}
    @if(session('usuario_id'))
    @php
      $tieneCultivos = in_array('cultivos', $lineasActivas);
      $tieneAnimales = \App\Models\LineaProductiva::tieneAnimales();
      $bothPrimary   = $tieneCultivos && $tieneAnimales;
    @endphp
    <nav class="bottom-nav mobile-nav">
      <a href="{{ route('dashboard') }}"        class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span>🏠</span><span>Inicio</span></a>

      @if($tieneCultivos)
      <a href="{{ route('cultivos.index') }}"   class="nav-item {{ request()->routeIs('cultivos.*') ? 'active' : '' }}"><span>🌱</span><span>Cultivos</span></a>
      @endif

      @if($tieneAnimales)
      <a href="{{ route('animales.index') }}"   class="nav-item {{ request()->routeIs('animales.*') ? 'active' : '' }}"><span>🐄</span><span>Animales</span></a>
      @endif

      @if(!$bothPrimary)
      <a href="{{ route('gastos.index') }}"     class="nav-item {{ request()->routeIs('gastos.*') ? 'active' : '' }}"><span>💰</span><span>Gastos</span></a>
      @endif

      <a href="{{ route('calendario.index') }}" class="nav-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}"><span>📅</span><span>Agenda</span></a>
      <a href="{{ route('reportes.index') }}"   class="nav-item {{ request()->routeIs('reportes.*') || request()->routeIs('rentabilidad.*') ? 'active' : '' }}"><span>📊</span><span>Reportes</span></a>
    </nav>
    @endif

  </main>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
<script>
(function(){
  var sub   = document.getElementById('subAnimales');
  var arrow = document.getElementById('arrowAnimales');
  function setEstado(abrir){
    if(!sub) return;
    sub.style.maxHeight = abrir ? '300px' : '0px';
    if(arrow) arrow.innerHTML = abrir ? '&#9650;' : '&#9660;';
    try{ localStorage.setItem('agrSidebarAnim', abrir?'1':'0'); }catch(e){}
  }
  // Restaurar estado
  try{
    var esAnimal = /\/(animales|bovino|avicola|porcicola|piscicola|produccion-animal)/.test(window.location.pathname);
    if(esAnimal){ setEstado(true); }
    else{
      var g = localStorage.getItem('agrSidebarAnim');
      if(g==='1') setEstado(true);
      else if(g==='0') setEstado(false);
    }
  }catch(e){}
  window.toggleGrupoAnimales = function(){
    if(!sub) return;
    setEstado(sub.style.maxHeight==='0px');
  };
})();
</script>

</body>
</html>