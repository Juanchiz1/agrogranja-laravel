<!DOCTYPE html>
<html lang="es" data-mode="auto">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#4CAF50">
  <title><?php echo $__env->yieldContent('title', 'Agrogranja'); ?> · Agrogranja</title>
  <link rel="stylesheet" href="<?php echo e(asset('css/app.css')); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Serif+Display&display=swap" rel="stylesheet">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌾</text></svg>">
  <?php echo $__env->yieldPushContent('head'); ?>
</head>
<body>


<?php if(session('usuario_id')): ?>
<button id="modeToggle" class="mode-toggle" title="Cambiar vista">
  <span class="mode-icon-mobile">📱</span>
  <span class="mode-icon-pc">🖥️</span>
  <span class="mode-label-mobile">Vista PC</span>
  <span class="mode-label-pc">Vista Móvil</span>
</button>
<?php endif; ?>

<div class="app-shell" id="appShell">

  
  <?php if(session('usuario_id')): ?>
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <span class="sidebar-logo">
        <img src="<?php echo e(asset('img/logo-seedling-transparente.svg')); ?>" alt="" style="height:60px;width:60px;margin-right:8px;">
      </span>
      <div>
        <div class="sidebar-name">Agrogranja</div>
        <div class="sidebar-finca"><?php echo e(session('usuario_nombre', 'Mi Finca')); ?></div>
      </div>
    </div>

    <nav class="sidebar-nav">
      
      <a href="<?php echo e(route('dashboard')); ?>"        class="sidebar-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
        <span class="sidebar-icon">🏠</span><span>Inicio</span>
      </a>

      
      <?php if(in_array('cultivos', $lineasActivas)): ?>
      <a href="<?php echo e(route('cultivos.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('cultivos.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">🌱</span><span>Cultivos</span>
      </a>
      <?php endif; ?>

      
      <a href="<?php echo e(route('gastos.index')); ?>"     class="sidebar-item <?php echo e(request()->routeIs('gastos.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">💰</span><span>Gastos</span>
      </a>
      <a href="<?php echo e(route('ingresos.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('ingresos.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">📈</span><span>Ingresos</span>
      </a>

      
      <?php if(\App\Models\LineaProductiva::tieneAnimales()): ?>
      <a href="<?php echo e(route('animales.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('animales.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">🐄</span><span>Animales</span>
      </a>
      <?php endif; ?>

      
      <a href="<?php echo e(route('personas.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('personas.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">👥</span><span>Personas</span>
      </a>

      
      <a href="<?php echo e(route('calendario.index')); ?>" class="sidebar-item <?php echo e(request()->routeIs('calendario.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">📅</span><span>Agenda</span>
      </a>

      
      <?php if(in_array('cultivos', $lineasActivas)): ?>
      <a href="<?php echo e(route('cosechas.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('cosechas.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">🌾</span><span>Cosechas</span>
      </a>
      <?php endif; ?>

      
      <a href="<?php echo e(route('inventario.index')); ?>" class="sidebar-item <?php echo e(request()->routeIs('inventario.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">📦</span><span>Inventario</span>
      </a>

      
      <a href="<?php echo e(route('reportes.index')); ?>"   class="sidebar-item <?php echo e(request()->routeIs('reportes.*') || request()->routeIs('rentabilidad.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">📊</span><span>Reportes</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <a href="<?php echo e(route('perfil.index')); ?>" class="sidebar-item <?php echo e(request()->routeIs('perfil.*') ? 'active' : ''); ?>">
        <span class="sidebar-icon">👤</span><span>Mi Perfil</span>
      </a>
      <form method="POST" action="<?php echo e(route('logout')); ?>">
        <?php echo csrf_field(); ?>
        <button type="submit" class="sidebar-item sidebar-logout" onclick="return confirm('¿Cerrar sesión?')">
          <span class="sidebar-icon">🚪</span><span>Cerrar sesión</span>
        </button>
      </form>
    </div>
  </aside>
  <?php endif; ?>

  
  <main class="main-content" id="mainContent">

    
    <?php if(session('usuario_id')): ?>
    <header class="top-bar">
      <div class="top-bar-left">
        <?php if (! empty(trim($__env->yieldContent('back_url')))): ?>
        <a href="<?php echo $__env->yieldContent('back_url'); ?>" class="btn-back">←</a>
        <?php else: ?>
        <button class="btn-back mobile-only" onclick="history.back()">←</button>
        <?php endif; ?>
        <h1 class="top-bar-title"><?php echo $__env->yieldContent('page_title', 'Agrogranja'); ?></h1>
      </div>
      <div class="top-bar-right">
        <a href="<?php echo e(route('perfil.index')); ?>" class="top-avatar" title="Mi perfil">👤</a>
        <form method="POST" action="<?php echo e(route('logout')); ?>" style="margin:0">
          <?php echo csrf_field(); ?>
          <button type="submit" class="btn-logout" title="Cerrar sesión" onclick="return confirm('¿Cerrar sesión?')">🚪</button>
        </form>
      </div>
    </header>
    <?php endif; ?>

    
    <?php if(session('msg')): ?>
    <div class="alert alert-<?php echo e(session('msgType','success')); ?> alert-flash" id="flashMsg">
      <?php if(session('msgType') === 'success'): ?> ✅ <?php elseif(session('msgType') === 'warning'): ?> ⚠️ <?php else: ?> ❌ <?php endif; ?>
      <?php echo e(session('msg')); ?>

    </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
    <div class="alert alert-error alert-flash">
      ❌ <?php echo e($errors->first()); ?>

    </div>
    <?php endif; ?>

    
    <div class="page-content">
      <?php echo $__env->yieldContent('content'); ?>
    </div>

    
    <?php if(session('usuario_id')): ?>
    <?php
      $tieneCultivos = in_array('cultivos', $lineasActivas);
      $tieneAnimales = \App\Models\LineaProductiva::tieneAnimales();
      $bothPrimary   = $tieneCultivos && $tieneAnimales;
    ?>
    <nav class="bottom-nav mobile-nav">
      <a href="<?php echo e(route('dashboard')); ?>"        class="nav-item <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>"><span>🏠</span><span>Inicio</span></a>

      <?php if($tieneCultivos): ?>
      <a href="<?php echo e(route('cultivos.index')); ?>"   class="nav-item <?php echo e(request()->routeIs('cultivos.*') ? 'active' : ''); ?>"><span>🌱</span><span>Cultivos</span></a>
      <?php endif; ?>

      <?php if($tieneAnimales): ?>
      <a href="<?php echo e(route('animales.index')); ?>"   class="nav-item <?php echo e(request()->routeIs('animales.*') ? 'active' : ''); ?>"><span>🐄</span><span>Animales</span></a>
      <?php endif; ?>

      <?php if(!$bothPrimary): ?>
      <a href="<?php echo e(route('gastos.index')); ?>"     class="nav-item <?php echo e(request()->routeIs('gastos.*') ? 'active' : ''); ?>"><span>💰</span><span>Gastos</span></a>
      <?php endif; ?>

      <a href="<?php echo e(route('calendario.index')); ?>" class="nav-item <?php echo e(request()->routeIs('calendario.*') ? 'active' : ''); ?>"><span>📅</span><span>Agenda</span></a>
      <a href="<?php echo e(route('reportes.index')); ?>"   class="nav-item <?php echo e(request()->routeIs('reportes.*') || request()->routeIs('rentabilidad.*') ? 'active' : ''); ?>"><span>📊</span><span>Reportes</span></a>
    </nav>
    <?php endif; ?>

  </main>
</div>

<script src="<?php echo e(asset('js/app.js')); ?>"></script>
<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/layouts/app.blade.php ENDPATH**/ ?>