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
      <a href="{{ route('dashboard') }}"        class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span class="sidebar-icon">🏠</span><span>Inicio</span>
      </a>
      <a href="{{ route('cultivos.index') }}"   class="sidebar-item {{ request()->routeIs('cultivos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">🌱</span><span>Cultivos</span>
      </a>
      <a href="{{ route('gastos.index') }}"     class="sidebar-item {{ request()->routeIs('gastos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">💰</span><span>Gastos</span>
      </a>
      <a href="{{ route('ingresos.index') }}"   class="sidebar-item {{ request()->routeIs('ingresos.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📈</span><span>Ingresos</span>
      </a>
      <a href="{{ route('animales.index') }}"   class="sidebar-item {{ request()->routeIs('animales.*') ? 'active' : '' }}">
        <span class="sidebar-icon">🐄</span><span>Animales</span>
      </a>
      <a href="{{ route('personas.index') }}"   class="sidebar-item {{ request()->routeIs('personas.*') ? 'active' : '' }}">
        <span class="sidebar-icon">👥</span><span>Personas</span>
      </a>
      <a href="{{ route('calendario.index') }}" class="sidebar-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📅</span><span>Agenda</span>
      </a>
      <a href="{{ route('cosechas.index') }}"   class="sidebar-item {{ request()->routeIs('cosechas.*') ? 'active' : '' }}">
        <span class="sidebar-icon">🌾</span><span>Cosechas</span>
      </a>
      <a href="{{ route('inventario.index') }}" class="sidebar-item {{ request()->routeIs('inventario.*') ? 'active' : '' }}">
        <span class="sidebar-icon">📦</span><span>Inventario</span>
      </a>
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

    {{-- BOTTOM NAV (modo móvil) --}}
    @if(session('usuario_id'))
    <nav class="bottom-nav mobile-nav">
      <a href="{{ route('dashboard') }}"        class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"><span>🏠</span><span>Inicio</span></a>
      <a href="{{ route('cultivos.index') }}"   class="nav-item {{ request()->routeIs('cultivos.*') ? 'active' : '' }}"><span>🌱</span><span>Cultivos</span></a>
      <a href="{{ route('gastos.index') }}"     class="nav-item {{ request()->routeIs('gastos.*') ? 'active' : '' }}"><span>💰</span><span>Gastos</span></a>
      <a href="{{ route('calendario.index') }}" class="nav-item {{ request()->routeIs('calendario.*') ? 'active' : '' }}"><span>📅</span><span>Agenda</span></a>
      <a href="{{ route('reportes.index') }}"   class="nav-item {{ request()->routeIs('reportes.*') || request()->routeIs('rentabilidad.*') ? 'active' : '' }}"><span>📊</span><span>Reportes</span></a>
    </nav>
    @endif

  </main>
</div>

<script src="{{ asset('js/app.js') }}"></script>
@stack('scripts')
</body>
</html>