
<?php $__env->startSection('title','Conversión Alimenticia'); ?>
<?php $__env->startSection('page_title','🌾 Conversión Alimenticia'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="alerta-avi info" style="margin-bottom:10px;">
  <span>💡</span>
  <div style="font-size:.8rem;">
    <strong>Conversión Alimenticia (CA)</strong> = kg alimento ÷ kg producción.
    Postura estándar: <strong>2.0-2.5</strong>. Engorde estándar: <strong>1.6-1.9</strong>.
    <em>Menor valor = mayor eficiencia.</em>
  </div>
</div>


<?php if($caPromLote): ?>
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📊 CA promedio por lote (últimas 4 semanas)</div>
    <button onclick="openModal('modalConversion')" class="btn btn-sm btn-primary">+ Registrar</button>
  </div>
  <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $ca = $caPromLote[$l->id] ?? 0; ?>
  <?php if($ca > 0): ?>
  <div class="ca-card">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-weight:700;font-size:.88rem;"><?php echo e($l->nombre_lote); ?></div>
        <div style="font-size:.75rem;color:#64748b;"><?php echo e($l->cantidad); ?> aves · <?php echo e($l->especie); ?></div>
      </div>
      <div class="<?php echo e($ca <= 2.0 ? 'ca-val-ok' : ($ca <= 2.5 ? 'ca-val-med' : 'ca-val-bad')); ?>">
        <?php echo e($ca); ?>

      </div>
    </div>
    <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">
      <?php if($ca <= 2.0): ?> ✅ Excelente eficiencia
      <?php elseif($ca <= 2.5): ?> ⚠️ Normal
      <?php else: ?> ❌ Alta — revisar alimento y salud
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Historial de conversiones</div>
    <?php if($lotes->count() && !$caPromLote): ?>
    <button onclick="openModal('modalConversion')" class="btn btn-sm btn-primary">+ Registrar</button>
    <?php endif; ?>
  </div>
  <?php $__empty_1 = true; $__currentLoopData = $conversiones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <div class="ca-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.87rem;"><?php echo e($conv->nombre_lote); ?></div>
        <div style="font-size:.75rem;color:#64748b;">
          Sem <?php echo e($conv->semana); ?> · <?php echo e($conv->tipo); ?> ·
          <?php echo e(\Carbon\Carbon::parse($conv->fecha_inicio)->format('d/m')); ?> al
          <?php echo e(\Carbon\Carbon::parse($conv->fecha_fin)->format('d/m/Y')); ?>

        </div>
        <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;">
          Alimento: <?php echo e($conv->alimento_consumido_kg); ?> kg ·
          Producción: <?php echo e($conv->produccion_kg); ?> kg
        </div>
      </div>
      <?php if($conv->conversion_alimenticia): ?>
      <div class="<?php echo e($conv->conversion_alimenticia <= 2.0 ? 'ca-val-ok' : ($conv->conversion_alimenticia <= 2.5 ? 'ca-val-med' : 'ca-val-bad')); ?>">
        <?php echo e($conv->conversion_alimenticia); ?>

      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:24px;color:#94a3b8;font-size:.85rem;">
    Sin registros de conversión alimenticia.
    <br><button onclick="openModal('modalConversion')" class="btn btn-sm btn-primary" style="margin-top:10px;">+ Primer registro</button>
  </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>


<div id="modalConversion" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🌾 Registrar conversión alimenticia</div>
    <form method="POST" action="<?php echo e(route('avicola.conversion.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Lote *</label>
        <select name="animal_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($l->id); ?>"><?php echo e($l->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Semana *</label>
          <input type="number" name="semana" class="form-control" min="1" max="120" required placeholder="Semana">
        </div>
        <div class="form-group">
          <label>Tipo</label>
          <select name="tipo" class="form-control">
            <option value="postura">Postura</option>
            <option value="engorde">Engorde</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha inicio *</label>
          <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Fecha fin *</label>
          <input type="date" name="fecha_fin" class="form-control" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Alimento consumido (kg) *</label>
          <input type="number" name="alimento_consumido_kg" class="form-control"
                 step="0.1" min="0" required placeholder="Ej: 350"
                 oninput="calcCA()">
        </div>
        <div class="form-group">
          <label>Producción (kg) *</label>
          <input type="number" name="produccion_kg" class="form-control"
                 step="0.1" min="0.001" required placeholder="Ej: 160"
                 oninput="calcCA()">
          <div style="font-size:.68rem;color:#64748b;margin-top:2px;">
            Postura: kg de huevo. Engorde: kg ganados
          </div>
        </div>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:10px;text-align:center;margin-bottom:10px;">
        <div style="font-size:.72rem;color:#64748b;">CA Calculada</div>
        <div id="caCalculada" style="font-size:1.4rem;font-weight:800;color:#f59e0b;">—</div>
        <div id="caJuicio" style="font-size:.72rem;color:#64748b;"></div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control"
               placeholder="Tipo de alimento, temperatura, cambios...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalConversion')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
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

function calcCA() {
  var ali  = parseFloat(document.querySelector('[name=alimento_consumido_kg]').value) || 0;
  var prod = parseFloat(document.querySelector('[name=produccion_kg]').value)         || 0;
  var el   = document.getElementById('caCalculada');
  var juicio = document.getElementById('caJuicio');
  if (ali > 0 && prod > 0) {
    var ca = Math.round((ali/prod)*1000)/1000;
    el.textContent = ca;
    if (ca <= 2.0)      { el.style.color = '#16a34a'; juicio.textContent = '✅ Excelente eficiencia'; }
    else if (ca <= 2.5) { el.style.color = '#f59e0b'; juicio.textContent = '⚠️ Normal para postura'; }
    else                { el.style.color = '#dc2626'; juicio.textContent = '❌ Alta — revisar manejo'; }
  } else {
    el.textContent = '—';
    juicio.textContent = '';
  }
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/conversion.blade.php ENDPATH**/ ?>