<?php $__env->startSection('title','Iniciar sesión'); ?>
<?php $__env->startSection('content'); ?>
<div class="welcome-bg" style="padding-bottom:0;justify-content:flex-end;">
  <div style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding-bottom:28px;">
    <div style="font-size:3rem;">🌾</div>
    <h2 style="font-family:var(--font-serif);color:#fff;font-size:1.6rem;margin-top:8px;">Bienvenido de vuelta</h2>
    <p style="color:rgba(255,255,255,.8);font-size:.88rem;margin-top:6px;">Ingresa a tu cuenta de Agrogranja</p>
  </div>
  <div class="auth-card">
    <?php if($errors->any()): ?>
    <div class="alert alert-error mb-3">❌ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>
    <h2>Iniciar sesión</h2>
    <p class="sub">Accede a tu cuenta</p>
    <form method="POST" action="<?php echo e(route('login.post')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Correo electrónico</label>
        <input type="email" name="email" class="form-control" placeholder="tu@correo.com" required value="<?php echo e(old('email')); ?>">
      </div>
      <div class="form-group">
        <label>Contraseña</label>
        <input type="password" name="password" class="form-control" placeholder="••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="padding:13px;font-size:.95rem;margin-top:4px;">Ingresar →</button>
    </form>
    <p style="text-align:center;margin-top:18px;font-size:.85rem;color:var(--gris);">
      ¿No tienes cuenta? <a href="<?php echo e(route('register')); ?>" style="color:var(--verde-dark);font-weight:700;">Regístrate</a>
    </p>
    <p style="text-align:center;margin-top:8px;font-size:.78rem;"><a href="<?php echo e(route('welcome')); ?>" style="color:#aaa;">← Volver</a></p>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>