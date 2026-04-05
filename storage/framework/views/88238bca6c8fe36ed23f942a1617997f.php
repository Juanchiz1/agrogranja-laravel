<?php $__env->startSection('title','Crear cuenta'); ?>
<?php $__env->startSection('content'); ?>
<div style="min-height:100vh;background:var(--crema);">
  <div style="background:linear-gradient(135deg,var(--verde-dark),var(--verde-mid));padding:52px 24px 32px;text-align:center;">
    <div style="font-size:3rem;">🌱</div>
    <h2 style="font-family:var(--font-serif);color:#fff;font-size:1.5rem;margin-top:8px;">Crear tu cuenta</h2>
    <p style="color:rgba(255,255,255,.8);font-size:.88rem;">Gestiona tu finca desde hoy</p>
  </div>
  <div class="auth-card" style="border-radius:var(--radius-xl) var(--radius-xl) 0 0;margin-top:-20px;">
    <?php if($errors->any()): ?>
    <div class="alert alert-error mb-3">❌ <?php echo e($errors->first()); ?></div>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('register.post')); ?>">
      <?php echo csrf_field(); ?>
      <p class="section-title" style="margin-top:0;">Datos personales</p>
      <div class="form-group">
        <label>Nombre completo *</label>
        <input type="text" name="nombre" class="form-control" placeholder="Juan Pérez" required value="<?php echo e(old('nombre')); ?>">
      </div>
      <div class="form-group">
        <label>Correo electrónico *</label>
        <input type="email" name="email" class="form-control" placeholder="juan@finca.com" required value="<?php echo e(old('email')); ?>">
      </div>
      <div class="form-group">
        <label>Contraseña *</label>
        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required>
      </div>
      <div class="form-group">
        <label>Teléfono</label>
        <input type="tel" name="telefono" class="form-control" placeholder="300 123 4567" value="<?php echo e(old('telefono')); ?>">
      </div>
      <p class="section-title">Tu finca</p>
      <div class="form-group">
        <label>Nombre de la finca</label>
        <input type="text" name="finca" class="form-control" placeholder="Finca El Paraíso" value="<?php echo e(old('finca')); ?>">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Departamento</label>
          <select name="departamento" class="form-control">
            <option value="">Seleccionar</option>
            <?php $__currentLoopData = ['Antioquia','Atlántico','Bolívar','Boyacá','Caldas','Caquetá','Cauca','Cesar','Córdoba','Cundinamarca','Huila','Magdalena','Meta','Nariño','Norte de Santander','Quindío','Risaralda','Santander','Sucre','Tolima','Valle del Cauca']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option <?php echo e(old('departamento') === $dep ? 'selected' : ''); ?>><?php echo e($dep); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label>Municipio</label>
          <input type="text" name="municipio" class="form-control" placeholder="Tu municipio" value="<?php echo e(old('municipio')); ?>">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full" style="padding:13px;font-size:.95rem;margin-top:4px;">Crear cuenta 🚀</button>
    </form>
    <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--gris);">
      ¿Ya tienes cuenta? <a href="<?php echo e(route('login')); ?>" style="color:var(--verde-dark);font-weight:700;">Ingresar</a>
    </p>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/register.blade.php ENDPATH**/ ?>