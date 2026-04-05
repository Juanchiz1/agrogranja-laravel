
<?php $__env->startSection('title','Cosechas'); ?>
<?php $__env->startSection('page_title','🌾 Registro de Cosechas'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startSection('content'); ?>


<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green">$<?php echo e(number_format($totalMes/1000,0)); ?>k</div>
    <div class="stat-label">Valor mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-green"><?php echo e($cantidadMes); ?></div>
    <div class="stat-label">Registros mes</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green">$<?php echo e(number_format($totalAnio/1000,0)); ?>k</div>
    <div class="stat-label">Valor año</div>
  </div>
</div>


<?php if($topProductos->count()): ?>
<p class="section-title">Top productos este año</p>
<?php $__currentLoopData = $topProductos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="flex items-center justify-between mb-2">
  <span class="text-sm font-bold"><?php echo e($tp->producto); ?></span>
  <span class="text-sm" style="color:var(--verde-dark);">
    <?php echo e(number_format($tp->total_qty,1)); ?> · $<?php echo e(number_format($tp->total_valor,0,',','.')); ?>

  </span>
</div>
<div class="progress-bar mb-2">
  <div class="progress-fill" style="width:<?php echo e($topProductos->max('total_valor') > 0 ? round($tp->total_valor/$topProductos->max('total_valor')*100) : 0); ?>%"></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<form method="GET" class="flex gap-2 mt-3 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar producto..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <input type="month" name="mes" class="form-control" style="width:130px;" value="<?php echo e(request('mes')); ?>" onchange="this.form.submit()">
  <select name="calidad" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Calidad</option>
    <option <?php echo e(request('calidad')==='excelente'?'selected':''); ?> value="excelente">Excelente</option>
    <option <?php echo e(request('calidad')==='buena'?'selected':''); ?>     value="buena">Buena</option>
    <option <?php echo e(request('calidad')==='regular'?'selected':''); ?>   value="regular">Regular</option>
    <option <?php echo e(request('calidad')==='baja'?'selected':''); ?>      value="baja">Baja</option>
  </select>
  <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>


<?php if($cosechas->isEmpty()): ?>
<div class="empty-state">
  <div class="emoji">🌾</div>
  <p><strong>Sin cosechas registradas.</strong></p>
  <p>Toca + para registrar tu primera cosecha.</p>
</div>
<?php else: ?>
<?php
  $calidadColor = ['excelente'=>'badge-green','buena'=>'badge-green','regular'=>'badge-orange','baja'=>'badge-red'];
  $destinoIcon  = ['venta'=>'💵','autoconsumo'=>'🏠','almacenaje'=>'📦'];
?>
<?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg);font-size:1.3rem;">🌾</div>
  <div class="item-body">
    <div class="item-title"><?php echo e($cs->producto); ?></div>
    <div class="item-sub">
      <?php echo e(number_format($cs->cantidad,1)); ?> <?php echo e($cs->unidad); ?>

      · <?php echo e(\Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y')); ?>

      <?php if($cs->cultivo_nombre): ?> · <?php echo e($cs->cultivo_nombre); ?><?php endif; ?>
      <?php if($cs->destino): ?> · <?php echo e($destinoIcon[$cs->destino] ?? ''); ?> <?php echo e($cs->destino); ?><?php endif; ?>
    </div>
  </div>
  <div class="flex gap-2 items-center" style="flex-shrink:0;">
    <div style="text-align:right;">
      <?php if($cs->valor_estimado): ?>
      <div class="font-bold text-green text-sm">$<?php echo e(number_format($cs->valor_estimado,0,',','.')); ?></div>
      <?php endif; ?>
      <span class="badge <?php echo e($calidadColor[$cs->calidad] ?? 'badge-green'); ?>"><?php echo e($cs->calidad); ?></span>
    </div>
    <button onclick="openModal('editCosecha<?php echo e($cs->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="<?php echo e(route('cosechas.destroy',$cs->id)); ?>" onsubmit="return confirm('¿Eliminar?')">
      <?php echo csrf_field(); ?><button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editCosecha<?php echo e($cs->id); ?>" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cosecha</h3>
    <form method="POST" action="<?php echo e(route('cosechas.update',$cs->id)); ?>">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('pages._cosecha_form', ['c' => $cs, 'cultivos' => $cultivos], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCosecha<?php echo e($cs->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevaCosecha" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌾 Registrar cosecha</h3>
    <form method="POST" action="<?php echo e(route('cosechas.store')); ?>">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('pages._cosecha_form', ['c' => null, 'cultivos' => $cultivos], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaCosecha')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cosecha</button>
      </div>
    </form>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevaCosecha')">+</button>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/cosechas.blade.php ENDPATH**/ ?>