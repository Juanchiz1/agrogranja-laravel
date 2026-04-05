<div class="form-group">
  <label>Producto *</label>
  <input type="text" name="producto" class="form-control" required
    placeholder="Ej: Maíz amarillo, Yuca criolla..."
    value="<?php echo e($c->producto ?? old('producto')); ?>">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Cantidad *</label>
    <input type="number" step="0.1" name="cantidad" class="form-control" required
      placeholder="0" value="<?php echo e($c->cantidad ?? ''); ?>"
      oninput="calcValor()">
  </div>
  <div class="form-group">
    <label>Unidad *</label>
    <select name="unidad" class="form-control" required>
      <option value="">Seleccionar</option>
      <?php $__currentLoopData = ['kg','toneladas','bultos','arrobas','litros','unidades','cajas','costales']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option <?php echo e(($c->unidad ?? '') === $u ? 'selected' : ''); ?>><?php echo e($u); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Precio unitario (COP)</label>
    <input type="number" step="100" name="precio_unitario" class="form-control"
      id="precioUnit_<?php echo e($c->id ?? 'new'); ?>"
      placeholder="0" value="<?php echo e($c->precio_unitario ?? ''); ?>"
      oninput="calcValor()">
  </div>
  <div class="form-group">
    <label>Valor estimado (COP)</label>
    <input type="number" step="100" name="valor_estimado" class="form-control"
      id="valorEst_<?php echo e($c->id ?? 'new'); ?>"
      placeholder="Autocalculado"
      value="<?php echo e($c->valor_estimado ?? ''); ?>">
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Fecha de cosecha *</label>
    <input type="date" name="fecha_cosecha" class="form-control" required
      value="<?php echo e($c->fecha_cosecha ?? date('Y-m-d')); ?>">
  </div>
  <div class="form-group">
    <label>Calidad</label>
    <select name="calidad" class="form-control">
      <?php $__currentLoopData = ['excelente'=>'⭐ Excelente','buena'=>'👍 Buena','regular'=>'👌 Regular','baja'=>'👎 Baja']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option value="<?php echo e($v); ?>" <?php echo e(($c->calidad ?? 'buena') === $v ? 'selected' : ''); ?>><?php echo e($label); ?></option>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Destino</label>
    <select name="destino" class="form-control">
      <option value="">Sin especificar</option>
      <option <?php echo e(($c->destino ?? '') === 'venta' ? 'selected' : ''); ?> value="venta">💵 Venta</option>
      <option <?php echo e(($c->destino ?? '') === 'autoconsumo' ? 'selected' : ''); ?> value="autoconsumo">🏠 Autoconsumo</option>
      <option <?php echo e(($c->destino ?? '') === 'almacenaje' ? 'selected' : ''); ?> value="almacenaje">📦 Almacenaje</option>
    </select>
  </div>
  <div class="form-group">
    <label>Comprador</label>
    <input type="text" name="comprador" class="form-control"
      placeholder="Nombre del comprador"
      value="<?php echo e($c->comprador ?? ''); ?>">
  </div>
</div>

<?php if($cultivos->count()): ?>
<div class="form-group">
  <label>Cultivo de origen</label>
  <select name="cultivo_id" class="form-control">
    <option value="">Ninguno</option>
    <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($cv->id); ?>" <?php echo e(($c->cultivo_id ?? 0) == $cv->id ? 'selected' : ''); ?>>
      <?php echo e($cv->nombre); ?>

    </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
</div>
<div class="form-group" style="display:flex;align-items:center;gap:10px;">
  <input type="checkbox" name="marcar_cosechado" id="marcarCosechado" value="1" style="width:18px;height:18px;">
  <label for="marcarCosechado" style="margin:0;text-transform:none;font-size:.88rem;color:var(--negro);">
    Marcar cultivo como "Cosechado" automáticamente
  </label>
</div>
<?php endif; ?>

<div class="form-group">
  <label>Observaciones</label>
  <textarea name="observaciones" class="form-control"
    placeholder="Condiciones de la cosecha, notas de calidad..."><?php echo e($c->observaciones ?? ''); ?></textarea>
</div>

<script>
function calcValor() {
  const cant  = parseFloat(document.querySelector('[name="cantidad"]')?.value) || 0;
  const punit = parseFloat(document.querySelector('[name="precio_unitario"]')?.value) || 0;
  const est   = document.querySelector('[name="valor_estimado"]');
  if (cant && punit && est) est.value = Math.round(cant * punit);
}
</script><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/_cosecha_form.blade.php ENDPATH**/ ?>