
<?php $__env->startSection('title','Mortalidad'); ?>
<?php $__env->startSection('page_title','💀 Mortalidad Avícola'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if($lotes->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📊 % Mortalidad acumulada por lote</div>
  <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $pct = $mortPorLote[$l->id] ?? 0; ?>
  <div style="margin-bottom:10px;">
    <div style="display:flex;justify-content:space-between;font-size:.83rem;margin-bottom:3px;">
      <span style="font-weight:600;"><?php echo e($l->nombre_lote); ?></span>
      <span style="color:<?php echo e($pct >= 5 ? '#dc2626' : ($pct >= 2 ? '#b45309' : '#15803d')); ?>;font-weight:700;">
        <?php echo e($pct); ?>%
      </span>
    </div>
    <div style="background:#e2e8f0;border-radius:4px;height:6px;">
      <div style="width:<?php echo e(min(100,$pct*10)); ?>%;height:100%;border-radius:4px;
                  background:<?php echo e($pct >= 5 ? '#dc2626' : ($pct >= 2 ? '#f59e0b' : '#16a34a')); ?>;"></div>
    </div>
    <div style="font-size:.68rem;color:#94a3b8;margin-top:2px;"><?php echo e($l->cantidad); ?> aves actuales</div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($porCausa->count()): ?>
<div class="section-card">
  <div class="section-header">
    <div class="section-title">💀 Causas — últimos 30 días</div>
    <button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
  </div>
  <?php $__currentLoopData = $porCausa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $causa => $cantidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="mort-row">
    <div>
      <div style="font-weight:600;font-size:.85rem;"><?php echo e($causas[$causa] ?? $causa); ?></div>
    </div>
    <span class="mort-badge"><?php echo e($cantidad); ?> ave(s)</span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Historial — 30 días</div>
    <?php if($porCausa->isEmpty()): ?>
    <button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
    <?php endif; ?>
  </div>
  <?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <div class="mort-row">
    <div>
      <div style="font-weight:600;font-size:.85rem;">
        <?php echo e($r->nombre_lote); ?>

        <span class="mort-badge" style="margin-left:6px;"><?php echo e($r->cantidad); ?> ave(s)</span>
      </div>
      <div style="font-size:.75rem;color:#64748b;">
        <?php echo e($causas[$r->causa] ?? $r->causa); ?>

        <?php if($r->descripcion): ?> · <?php echo e($r->descripcion); ?><?php endif; ?>
      </div>
      <?php if($r->descartadas > 0): ?>
      <div style="font-size:.72rem;color:#b45309;">+ <?php echo e($r->descartadas); ?> descartadas</div>
      <?php endif; ?>
    </div>
    <div style="font-size:.78rem;color:#94a3b8;white-space:nowrap;">
      <?php echo e(\Carbon\Carbon::parse($r->fecha)->format('d/m/Y')); ?>

    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#94a3b8;font-size:.85rem;">
    Sin mortalidad registrada en los últimos 30 días.
    <br><button onclick="openModal('modalMortalidad')" class="btn btn-sm btn-primary" style="margin-top:10px;">+ Registrar</button>
  </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>


<div id="modalMortalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💀 Registrar mortalidad</div>
    <form method="POST" action="<?php echo e(route('avicola.mortalidad.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Lote *</label>
        <select name="animal_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($l->id); ?>"><?php echo e($l->nombre_lote); ?> (<?php echo e($l->cantidad); ?> aves)</option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Cantidad muertas *</label>
          <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
        </div>
      </div>
      <div class="form-group">
        <label>Causa *</label>
        <select name="causa" class="form-control" required>
          <?php $__currentLoopData = $causas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Descartadas (retiradas vivas por baja condición)</label>
        <input type="number" name="descartadas" class="form-control" min="0" value="0">
      </div>
      <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control" rows="2"
                  placeholder="Síntomas observados, medidas tomadas..."></textarea>
      </div>
      <div style="background:#fef2f2;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#991b1b;margin-bottom:10px;">
        ⚠️ El sistema actualizará automáticamente el conteo de aves del lote.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalMortalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/mortalidad.blade.php ENDPATH**/ ?>