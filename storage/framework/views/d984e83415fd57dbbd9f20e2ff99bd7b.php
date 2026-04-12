<div class="form-group">
  <label>Nombre del insumo *</label>
  <input type="text" name="nombre" class="form-control" required
    placeholder="Ej: Glifosato 480SL, Urea 46%..."
    value="<?php echo e($ins->nombre ?? old('nombre')); ?>">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Categoría *</label>
    <select name="categoria" class="form-control" required>
      <option value="">Seleccionar</option>
      <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option <?php echo e(($ins->categoria ?? '') === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
  <div class="form-group">
    <label>Unidad de medida *</label>
    <select name="unidad" class="form-control" required>
      <option value="">Seleccionar</option>
      <?php $__currentLoopData = ['kg','g','litros','ml','bultos','costales','cajas','unidades','rollos','metros']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option <?php echo e(($ins->unidad ?? '') === $u ? 'selected' : ''); ?>><?php echo e($u); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
</div>

<?php if($nuevo): ?>
<div class="form-group">
  <label>Cantidad actual *</label>
  <input type="number" step="0.1" name="cantidad_actual" class="form-control" required
    placeholder="0" value="<?php echo e(old('cantidad_actual', 0)); ?>">
</div>
<?php endif; ?>

<div class="form-group">
  <label>Stock mínimo *</label>
  <p style="font-size:.75rem;color:var(--gris);margin-bottom:5px;">
    Se generará una alerta cuando el stock baje de este valor.
  </p>
  <input type="number" step="0.1" name="stock_minimo" class="form-control" required
    placeholder="0" value="<?php echo e($ins->stock_minimo ?? old('stock_minimo', 0)); ?>">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Precio unitario (COP)</label>
    <input type="number" step="100" name="precio_unitario" class="form-control"
      placeholder="0" value="<?php echo e($ins->precio_unitario ?? ''); ?>">
  </div>
  <div class="form-group">
    <label>Proveedor habitual</label>
    <input type="text" name="proveedor" class="form-control"
      placeholder="Nombre del proveedor" value="<?php echo e($ins->proveedor ?? ''); ?>">
  </div>
</div>

<div class="form-group">
  <label>Fecha de vencimiento</label>
  <input type="date" name="fecha_vencimiento" class="form-control"
    value="<?php echo e($ins->fecha_vencimiento ?? ''); ?>">
</div>

<div class="form-group">
  <label>Notas</label>
  <textarea name="notas" class="form-control"
    placeholder="Instrucciones de uso, dilución, almacenamiento..."><?php echo e($ins->notas ?? ''); ?></textarea>
</div><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/_inventario_form.blade.php ENDPATH**/ ?>