<?php $__env->startSection('title','Gastos'); ?>
<?php $__env->startSection('page_title','💰 Gastos e Insumos'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>
<?php $__env->startSection('content'); ?>

<div class="card mb-3" style="background:var(--marron-bg)">
  <div class="flex items-center justify-between">
    <div><p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Gasto mensual</p><p style="font-size:1.7rem;font-weight:800;color:var(--marron);">$<?php echo e(number_format($totalMes,0,',','.')); ?></p></div>
    <div style="text-align:right;"><p class="text-xs text-gray">Este año</p><p class="font-bold text-brown">$<?php echo e(number_format($totalAnio,0,',','.')); ?></p></div>
  </div>
</div>

<?php if($statsCat->count()): ?>
<p class="section-title">Top categorías este mes</p>
<?php $__currentLoopData = $statsCat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-2">
  <div class="flex items-center justify-between mb-2"><span class="text-sm font-bold"><?php echo e($sc->categoria); ?></span><span class="text-sm font-bold text-brown">$<?php echo e(number_format($sc->total,0,',','.')); ?></span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:<?php echo e($totalMes > 0 ? min(100,round($sc->total/$totalMes*100)) : 0); ?>%;background:var(--marron-light);"></div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<form method="GET" class="flex gap-2 mt-3 mb-2" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:100px;"><span class="search-icon">🔍</span><input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;"></div>
  <input type="month" name="mes" class="form-control" style="width:130px;" value="<?php echo e(request('mes')); ?>" onchange="this.form.submit()">
  <select name="cat" class="form-control" style="width:120px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e(request('cat')===$cat?'selected':''); ?>><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
</form>

<?php if($gastos->isEmpty()): ?>
<div class="empty-state"><div class="emoji">💰</div><p>No hay gastos registrados.</p></div>
<?php else: ?>
<?php $__currentLoopData = $gastos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $icons=['Semillas'=>'🌰','Fertilizantes'=>'🌿','Plaguicidas'=>'🧴','Herramientas'=>'🔧','Combustible'=>'⛽','Mano de obra'=>'👷','Transporte'=>'🚛','Alimento animal'=>'🌾','Veterinario'=>'💉','Mantenimiento'=>'🏗️','Otros'=>'📦']; $ic=$icons[$g->categoria]??'📦'; ?>
<div class="list-item">
  <div class="item-icon" style="background:var(--marron-bg)"><?php echo e($ic); ?></div>
  <div class="item-body"><div class="item-title"><?php echo e($g->descripcion); ?></div><div class="item-sub"><?php echo e($g->categoria); ?><?php echo e($g->cultivo_nombre ? ' · '.$g->cultivo_nombre : ''); ?><?php echo e($g->cantidad ? ' · '.$g->cantidad.' '.$g->unidad_cantidad : ''); ?></div></div>
  <div class="flex gap-2 items-center">
    <span class="font-bold text-brown text-sm">$<?php echo e(number_format($g->valor,0,',','.')); ?></span>
    <button onclick="openModal('editGasto<?php echo e($g->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="<?php echo e(route('gastos.destroy',$g->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?><button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<div class="modal-overlay" id="editGasto<?php echo e($g->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar gasto</h3>
    <form method="POST" action="<?php echo e(route('gastos.update',$g->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Categoría *</label><select name="categoria" class="form-control" required><?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($g->categoria===$cat?'selected':''); ?>><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="<?php echo e($g->descripcion); ?>"></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="<?php echo e($g->cantidad); ?>"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" value="<?php echo e($g->unidad_cantidad); ?>"></div></div>
      <div class="grid-2"><div class="form-group"><label>Valor *</label><input type="number" step="100" name="valor" class="form-control" required value="<?php echo e($g->valor); ?>"></div><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($g->fecha); ?>"></div></div>
      <div class="form-group"><label>Proveedor</label><input type="text" name="proveedor" class="form-control" value="<?php echo e($g->proveedor); ?>"></div>
      <?php if($cultivos->count()): ?><div class="form-group"><label>Cultivo asociado</label><select name="cultivo_id" class="form-control"><option value="">Sin asociar</option><?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>" <?php echo e($g->cultivo_id==$cv->id?'selected':''); ?>><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editGasto<?php echo e($g->id); ?>')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<div class="modal-overlay" id="modalNuevoGasto" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar gasto</h3>
    <form method="POST" action="<?php echo e(route('gastos.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Categoría *</label><select name="categoria" class="form-control" required><option value="">Seleccionar...</option><?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Bulto de semillas" required></div>
      <div class="grid-2"><div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0"></div><div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" placeholder="kg, bultos..."></div></div>
      <div class="grid-2"><div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div></div>
      <div class="form-group"><label>Proveedor</label><input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor"></div>
      <?php if($cultivos->count()): ?><div class="form-group"><label>Cultivo asociado</label><select name="cultivo_id" class="form-control"><option value="">Sin asociar</option><?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoGasto')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" style="background:var(--marron);" onclick="openModal('modalNuevoGasto')">+</button>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/gastos.blade.php ENDPATH**/ ?>