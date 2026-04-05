<?php $__env->startSection('title','Bienvenida'); ?>
<?php $__env->startSection('content'); ?>
<div class="welcome-bg">
  <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;">
    <div class="welcome-logo">🌾</div>
    <h1 class="welcome-title">Agrogranja</h1>
    <p class="welcome-sub">Tu finca en la palma de tu mano.<br>Gestiona cultivos, gastos y cosechas.</p>
    <div style="display:flex;flex-direction:column;gap:12px;width:100%;max-width:320px;">
      <a href="<?php echo e(route('register')); ?>" class="btn btn-full" style="background:#fff;color:var(--verde-dark);font-size:1rem;padding:15px;">✨ Crear cuenta gratis</a>
      <a href="<?php echo e(route('login')); ?>" class="btn btn-full btn-ghost" style="color:rgba(255,255,255,.9);border:2px solid rgba(255,255,255,.3);padding:14px;">Ya tengo cuenta</a>
    </div>
  </div>
  <div style="background:rgba(255,255,255,.1);border-radius:20px 20px 0 0;padding:24px 22px;width:100%;max-width:430px;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;text-align:center;">
      <?php $__currentLoopData = [['🌱','Cultivos','Siembras y cosechas'],['💰','Gastos','Insumos y costos'],['📅','Agenda','Actividades'],['📊','Reportes','Análisis y gráficas']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div><div style="font-size:1.7rem;"><?php echo e($f[0]); ?></div><div style="color:#fff;font-weight:700;font-size:.85rem;margin:4px 0 2px;"><?php echo e($f[1]); ?></div><div style="color:rgba(255,255,255,.7);font-size:.72rem;"><?php echo e($f[2]); ?></div></div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <p style="text-align:center;color:rgba(255,255,255,.55);font-size:.72rem;margin-top:16px;">Demo: demo@demo.com / demo123</p>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/welcome.blade.php ENDPATH**/ ?>