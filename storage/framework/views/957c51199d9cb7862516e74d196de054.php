<?php $__env->startSection('title','Ingresos'); ?>
<?php $__env->startSection('page_title','📈 Ingresos y Ventas'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>
<?php $__env->startSection('content'); ?>

<div class="stats-grid" style="grid-template-columns:1fr 1fr;margin-bottom:16px;">
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1.1rem;">$<?php echo e(number_format($totalMes,0,',','.')); ?></div><div class="stat-label">Este mes</div></div>
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1.1rem;">$<?php echo e(number_format($totalAnio,0,',','.')); ?></div><div class="stat-label">Este año</div></div>
</div>

<?php if($ingresos->isEmpty()): ?>
<div class="empty-state"><div class="emoji">📈</div><p>No hay ingresos registrados.</p></div>
<?php else: ?>
<?php $__currentLoopData = $ingresos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="list-item">
  <div class="item-icon" style="background:#eff6ff;font-size:1.3rem;">💵</div>
  <div class="item-body">
    <div class="item-title"><?php echo e($i->descripcion); ?></div>
    <div class="item-sub"><?php echo e(\Carbon\Carbon::parse($i->fecha)->format('d/m/Y')); ?><?php echo e($i->comprador ? ' · '.$i->comprador : ''); ?><?php echo e($i->cultivo_nombre ? ' · '.$i->cultivo_nombre : ''); ?></div>
  </div>
  <div class="flex gap-2 items-center">
    <span class="font-bold text-green text-sm">$<?php echo e(number_format($i->valor_total,0,',','.')); ?></span>
    <button onclick="openModal('editIngreso<?php echo e($i->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="<?php echo e(route('ingresos.destroy',$i->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?><button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<div class="modal-overlay" id="editIngreso<?php echo e($i->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar ingreso</h3>
    <form method="POST" action="<?php echo e(route('ingresos.update',$i->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="<?php echo e($i->descripcion); ?>"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="<?php echo e($i->cantidad); ?>"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" value="<?php echo e($i->unidad); ?>"></div></div>
      <div class="grid-2"><div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" class="form-control" value="<?php echo e($i->precio_unitario); ?>"></div><div class="form-group"><label>Total *</label><input type="number" step="100" name="valor_total" class="form-control" required value="<?php echo e($i->valor_total); ?>"></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($i->fecha); ?>"></div><div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" value="<?php echo e($i->comprador); ?>"></div></div>
      <?php if($cultivos->count()): ?><div class="form-group"><label>Cultivo origen</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option><?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>" <?php echo e($i->cultivo_id==$cv->id?'selected':''); ?>><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editIngreso<?php echo e($i->id); ?>')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<div class="modal-overlay" id="modalNuevoIngreso" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💵 Registrar ingreso</h3>
    <form method="POST" action="<?php echo e(route('ingresos.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Venta de maíz" required></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0" oninput="calcTotal()"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" placeholder="kg, bultos..."></div></div>
      <div class="grid-2"><div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0" oninput="calcTotal()"></div><div class="form-group"><label>Total (COP) *</label><input type="number" step="100" name="valor_total" class="form-control" required placeholder="0" id="valorTotal"></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div><div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" placeholder="Nombre"></div></div>
      <?php if($cultivos->count()): ?><div class="form-group"><label>Cultivo origen</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option><?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoIngreso')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" style="background:var(--verde-dark);" onclick="openModal('modalNuevoIngreso')">+</button>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/ingresos.blade.php ENDPATH**/ ?>