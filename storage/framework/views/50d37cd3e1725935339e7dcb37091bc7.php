<?php $__env->startSection('title','Inicio'); ?>
<?php $__env->startSection('page_title', '🌾 ' . (now()->hour < 12 ? 'Buenos días' : (now()->hour < 18 ? 'Buenas tardes' : 'Buenas noches')) . ', ' . explode(' ', $user->nombre)[0]); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($cultivosActivos); ?></div><div class="stat-label">Cultivos activos</div></div>
  <div class="stat-card"><div class="stat-value text-brown">$<?php echo e(number_format($gastosMes/1000,0)); ?>k</div><div class="stat-label">Gastos mes</div></div>
  <div class="stat-card"><div class="stat-value text-orange"><?php echo e($tareasPend); ?></div><div class="stat-label">Tareas pend.</div></div>
</div>


<?php $balance = $ingresosMes - $gastosMes; ?>
<div class="card mb-3" style="background:<?php echo e($balance >= 0 ? 'var(--verde-bg)' : '#fef2f2'); ?>">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gris);">Balance <?php echo e(now()->locale('es')->monthName); ?></p>
      <p style="font-size:1.7rem;font-weight:800;color:<?php echo e($balance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;margin-top:4px;">
        $<?php echo e(number_format($balance, 0, ',', '.')); ?>

      </p>
    </div>
    <div style="text-align:right;">
      <p class="text-xs text-gray">Ingresos: <strong class="text-green">$<?php echo e(number_format($ingresosMes,0,',','.')); ?></strong></p>
      <p class="text-xs text-gray mt-2">Gastos: <strong class="text-red">$<?php echo e(number_format($gastosMes,0,',','.')); ?></strong></p>
    </div>
  </div>
</div>


<div class="menu-grid">
  <a href="<?php echo e(route('cultivos.index')); ?>" class="menu-card"><div class="menu-icon" style="background:#edf7ed;">🌱</div><span class="menu-label">Cultivos y Animales</span></a>
  <a href="<?php echo e(route('gastos.index')); ?>"   class="menu-card"><div class="menu-icon" style="background:#fdf3ea;">💰</div><span class="menu-label">Gastos e Insumos</span></a>
  <a href="<?php echo e(route('ingresos.index')); ?>" class="menu-card"><div class="menu-icon" style="background:#eff6ff;">📈</div><span class="menu-label">Ingresos y Ventas</span></a>
  <a href="<?php echo e(route('calendario.index')); ?>"class="menu-card"><div class="menu-icon" style="background:#fdf2f8;">📅</div><span class="menu-label">Agenda y Tareas</span></a>
  <a href="<?php echo e(route('animales.index')); ?>" class="menu-card"><div class="menu-icon" style="background:#f5f3ff;">🐄</div><span class="menu-label">Mis Animales</span></a>
  <a href="<?php echo e(route('reportes.index')); ?>" class="menu-card"><div class="menu-icon" style="background:#eef2ff;">📊</div><span class="menu-label">Reportes</span></a>
  <a href="<?php echo e(route('cosechas.index')); ?>" class="menu-card"><div class="menu-icon" style="background:#f0fdf4;">🌾</div><span class="menu-label">Cosechas y<br>Producción</span></a>
</div>


<?php if($tareasHoy->count()): ?>
<p class="section-title">📌 Tareas de hoy</p>
<?php $__currentLoopData = $tareasHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $ic = ['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝'][$t->tipo] ?? '📝'; $pc = ['alta'=>'var(--rojo)','media'=>'var(--naranja)','baja'=>'var(--verde-dark)'][$t->prioridad] ?? 'var(--gris)'; ?>
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)"><?php echo e($ic); ?></div>
  <div class="item-body"><div class="item-title"><?php echo e($t->titulo); ?></div><div class="item-sub" style="color:<?php echo e($pc); ?>">Prioridad <?php echo e($t->prioridad); ?></div></div>
  <form method="POST" action="<?php echo e(route('tareas.completar', $t->id)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-secondary">✓ Listo</button></form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if($recentCultivos->count()): ?>
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌱 Últimos cultivos</p>
  <a href="<?php echo e(route('cultivos.index')); ?>" class="text-sm text-green font-bold">Ver todos</a>
</div>
<?php $__currentLoopData = $recentCultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $b = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado] ?? 'badge-green'; ?>
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)">🌿</div>
  <div class="item-body"><div class="item-title"><?php echo e($c->nombre); ?></div><div class="item-sub"><?php echo e($c->tipo); ?> · <?php echo e(\Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y')); ?></div></div>
  <span class="badge <?php echo e($b); ?>"><?php echo e($c->estado); ?></span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/dashboard.blade.php ENDPATH**/ ?>