<?php $__env->startSection('title','Perfil'); ?>
<?php $__env->startSection('page_title','👤 Mi Perfil'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>
<?php $__env->startSection('content'); ?>

<div style="text-align:center;padding:20px 0 14px;">
  <div style="width:78px;height:78px;border-radius:50%;background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:2.3rem;margin:0 auto 10px;box-shadow:var(--shadow-md);">👤</div>
  <h2 style="font-family:var(--font-serif);font-size:1.2rem;"><?php echo e($user->nombre); ?></h2>
  <p class="text-xs text-gray"><?php echo e($user->email); ?></p>
  <?php if($user->nombre_finca): ?><p class="text-xs text-green font-bold mt-2">🏡 <?php echo e($user->nombre_finca); ?></p><?php endif; ?>
</div>

<div class="stats-grid mb-3">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($stats['cultivos']); ?></div><div class="stat-label">Cultivos</div></div>
  <div class="stat-card"><div class="stat-value text-brown"><?php echo e($stats['gastos']); ?></div><div class="stat-label">Gastos</div></div>
  <div class="stat-card"><div class="stat-value text-orange"><?php echo e($stats['tareas']); ?></div><div class="stat-label">Tareas</div></div>
</div>

<div class="tabs">
  <button class="tab-btn <?php echo e($tab==='perfil'?'active':''); ?>"    onclick="location.href='?tab=perfil'">👤 Datos</button>
  <button class="tab-btn <?php echo e($tab==='seguridad'?'active':''); ?>" onclick="location.href='?tab=seguridad'">🔐 Seguridad</button>
  <button class="tab-btn <?php echo e($tab==='cuenta'?'active':''); ?>"    onclick="location.href='?tab=cuenta'">⚙️ Cuenta</button>
</div>

<?php if($tab === 'perfil'): ?>
<div class="card">
  <form method="POST" action="<?php echo e(route('perfil.update')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group"><label>Nombre completo</label><input type="text" name="nombre" class="form-control" required value="<?php echo e($user->nombre); ?>"></div>
    <div class="form-group"><label>Nombre de la finca</label><input type="text" name="finca" class="form-control" placeholder="Mi Finca" value="<?php echo e($user->nombre_finca); ?>"></div>
    <div class="grid-2">
      <div class="form-group"><label>Departamento</label><input type="text" name="departamento" class="form-control" value="<?php echo e($user->departamento); ?>"></div>
      <div class="form-group"><label>Municipio</label><input type="text" name="municipio" class="form-control" value="<?php echo e($user->municipio); ?>"></div>
    </div>
    <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" value="<?php echo e($user->telefono); ?>"></div>
    <div class="form-group"><label>Correo electrónico</label><input type="email" class="form-control" value="<?php echo e($user->email); ?>" disabled style="opacity:.6;"></div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar cambios</button>
  </form>
</div>

<?php elseif($tab === 'seguridad'): ?>
<div class="card">
  <?php if($errors->has('password_actual')): ?><div class="alert alert-error mb-3">❌ <?php echo e($errors->first('password_actual')); ?></div><?php endif; ?>
  <form method="POST" action="<?php echo e(route('perfil.password')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group"><label>Contraseña actual</label><input type="password" name="password_actual" class="form-control" required placeholder="••••••"></div>
    <div class="form-group"><label>Nueva contraseña</label><input type="password" name="password_nueva" class="form-control" required placeholder="Mínimo 6 caracteres"></div>
    <div class="form-group"><label>Confirmar nueva contraseña</label><input type="password" name="password_confirmar" class="form-control" required placeholder="Repetir contraseña"></div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Cambiar contraseña</button>
  </form>
</div>

<?php elseif($tab === 'cuenta'): ?>
<div class="card mb-3" style="background:var(--verde-bg);">
  <p class="font-bold mb-2">📊 Mis datos</p>
  <p class="text-sm text-gray"><?php echo e($stats['cultivos']); ?> cultivos · <?php echo e($stats['gastos']); ?> gastos · <?php echo e($stats['ingresos']); ?> ingresos · <?php echo e($stats['tareas']); ?> tareas</p>
  <p class="text-xs text-gray mt-2">Miembro desde <?php echo e(\Carbon\Carbon::parse($user->created_at ?? $user->creado_en ?? now())->format('d/m/Y')); ?></p>
</div>
<form method="POST" action="<?php echo e(route('logout')); ?>" onsubmit="return confirm('¿Cerrar sesión?')">
  <?php echo csrf_field(); ?>
  <button type="submit" class="btn btn-danger btn-full" style="padding:13px;">🚪 Cerrar sesión</button>
</form>
<?php endif; ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/perfil.blade.php ENDPATH**/ ?>