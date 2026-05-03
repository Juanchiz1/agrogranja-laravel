
<?php $__env->startSection('title','Pesaje'); ?>
<?php $__env->startSection('page_title','⚖️ Pesaje y GPD'); ?>
<?php $__env->startSection('back_url', route('bovino.hato')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bovino.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="section-card">
  <div class="section-header">
    <div class="section-title">⚖️ Control de peso</div>
    <button onclick="openModal('modalPeso')" class="btn btn-sm btn-primary">＋ Registrar peso</button>
  </div>

  <?php if($bovinos->isEmpty()): ?>
    <p style="text-align:center;color:#64748b;padding:20px;">No hay bovinos activos.</p>
  <?php else: ?>
  <?php $__currentLoopData = $pesajeData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $gpd   = $data['gpd'];
    $meta  = $data['meta_kg'];
    $actual= $data['peso_actual'];
    $pct   = ($actual && $meta) ? min(100, round(($actual/$meta)*100)) : null;
    $gpdColor = !$gpd ? '#94a3b8' : ($gpd >= 0.5 ? '#22c55e' : ($gpd >= 0.2 ? '#f59e0b' : '#ef4444'));
  ?>
  <div class="pesaje-card">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
      <div>
        <strong><?php echo e($data['bovino']->nombre_lote); ?></strong>
        <?php if($data['bovino']->raza): ?><span style="font-size:.72rem;color:#94a3b8;"> · <?php echo e($data['bovino']->raza); ?></span><?php endif; ?>
      </div>
      <div style="display:flex;gap:12px;align-items:center;">
        <div style="text-align:center;">
          <div style="font-size:1.1rem;font-weight:800;color:#1e293b;">
            <?php echo e($actual ? number_format($actual,0).' kg' : '—'); ?>

          </div>
          <div style="font-size:.68rem;color:#94a3b8;">Peso actual</div>
        </div>
        <div style="text-align:center;">
          <div style="font-size:1.1rem;font-weight:800;color:<?php echo e($gpdColor); ?>;">
            <?php echo e($gpd !== null ? ($gpd >= 0 ? '+' : '').$gpd.' kg/d' : '—'); ?>

          </div>
          <div style="font-size:.68rem;color:#94a3b8;">GPD</div>
        </div>
        <?php if($meta): ?>
        <div style="text-align:center;">
          <div style="font-size:1rem;font-weight:700;color:#3b82f6;"><?php echo e($meta); ?> kg</div>
          <div style="font-size:.68rem;color:#94a3b8;">Meta</div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if($pct !== null): ?>
    <div style="margin-top:8px;">
      <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-bottom:3px;">
        <span>Progreso a meta</span><span><?php echo e($pct); ?>%</span>
      </div>
      <div style="background:#e2e8f0;border-radius:50px;height:6px;overflow:hidden;">
        <div style="height:100%;background:<?php echo e($pct >= 90 ? '#22c55e' : ($pct >= 60 ? '#3b82f6' : '#f59e0b')); ?>;border-radius:50px;width:<?php echo e($pct); ?>%;"></div>
      </div>
    </div>
    <?php endif; ?>

    <?php if($data['pesos']->count() >= 2): ?>
    <div style="margin-top:6px;font-size:.75rem;color:#64748b;">
      📅 Último pesaje: <?php echo e(\Carbon\Carbon::parse($data['pesos'][0]->fecha)->format('d/m/Y')); ?>

      · Anterior: <?php echo e(\Carbon\Carbon::parse($data['pesos'][1]->fecha)->format('d/m/Y')); ?>

      (<?php echo e(\Carbon\Carbon::parse($data['pesos'][1]->fecha)->diffInDays($data['pesos'][0]->fecha)); ?> días)
    </div>
    <?php endif; ?>

    <div style="margin-top:6px;">
      <button onclick="abrirPeso(<?php echo e($data['bovino']->id); ?>,'<?php echo e($data['bovino']->nombre_lote); ?>',<?php echo e($actual ?? 'null'); ?>,<?php echo e($meta ?? 'null'); ?>)"
              class="btn btn-sm btn-ghost">⚖️ Registrar peso</button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>

<div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px;margin-bottom:14px;font-size:.82rem;color:#1d4ed8;">
  💡 <strong>GPD (Ganancia Diaria de Peso):</strong> Se calcula como la diferencia entre los dos últimos pesajes dividida por los días entre ellos.
  Referencia: ≥ 0.5 kg/día = excelente · 0.2-0.5 = normal · &lt; 0.2 = revisar alimentación.
</div>

<div style="margin-bottom:80px;"></div>


<div id="modalPeso" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">⚖️ Registrar peso — <span id="pesoNombreAnimal"></span></div>
    <form method="POST" action="<?php echo e(route('bovino.pesaje.store')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="animal_id" id="pesoAnimalId">
      <div class="form-group">
        <label>Animal *</label>
        <select name="animal_id" id="selectAnimalPeso" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $bovinos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($b->id); ?>"><?php echo e($b->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Peso (kg) *</label>
          <input type="number" name="peso" id="inputPesoKg" class="form-control"
                 step="0.1" min="0" required placeholder="Ej: 320.5">
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Meta de sacrificio (kg)</label>
        <input type="number" name="peso_meta_kg" id="inputMetaKg" class="form-control"
               step="1" min="0" placeholder="Ej: 450">
        <div style="font-size:.72rem;color:#64748b;margin-top:3px;">Solo si quieres actualizar la meta.</div>
      </div>
      <div class="form-group">
        <label>Notas</label>
        <input type="text" name="notas" class="form-control" placeholder="Condición corporal, observaciones...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalPeso')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id){ var m=document.getElementById(id); m.style.display='flex'; m.classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ var m=document.getElementById(id); m.style.display='none'; m.classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

function abrirPeso(id, nombre, pesoActual, meta) {
  document.getElementById('pesoNombreAnimal').textContent = nombre;
  document.getElementById('selectAnimalPeso').value = id;
  if (pesoActual) document.getElementById('inputPesoKg').placeholder = 'Actual: '+pesoActual+' kg';
  if (meta)       document.getElementById('inputMetaKg').value = meta;
  openModal('modalPeso');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/bovino/pesaje.blade.php ENDPATH**/ ?>