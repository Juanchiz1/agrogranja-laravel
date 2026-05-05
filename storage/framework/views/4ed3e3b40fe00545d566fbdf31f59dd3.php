
<?php $__env->startSection('title','Vacunación'); ?>
<?php $__env->startSection('page_title','💉 Vacunación Avícola'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if($vacunasVencidas->count()): ?>
<div class="section-card" style="border-left:4px solid #dc2626;">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    ❌ Vacunas vencidas (<?php echo e($vacunasVencidas->count()); ?>)
  </div>
  <?php $__currentLoopData = $vacunasVencidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="vacuna-card vencida">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="vacuna-nombre"><?php echo e($v->nombre_vacuna); ?></div>
        <div class="vacuna-fecha">
          <?php if($v->nombre_lote): ?>🐔 <?php echo e($v->nombre_lote); ?> · <?php endif; ?>
          Debía aplicarse: <?php echo e(\Carbon\Carbon::parse($v->fecha_programada)->format('d/m/Y')); ?>

          (<?php echo e(now()->diffInDays($v->fecha_programada)); ?> días atrás)
        </div>
        <span class="vacuna-via"><?php echo e(str_replace('_',' ',$v->via_administracion)); ?></span>
      </div>
      <button onclick="openAplicar(<?php echo e($v->id); ?>,'<?php echo e(addslashes($v->nombre_vacuna)); ?>')"
              class="btn btn-sm btn-primary" style="white-space:nowrap;margin-left:10px;">
        💉 Aplicar
      </button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($vacunasProximas->count()): ?>
<div class="section-card">
  <div class="section-title" style="color:#b45309;margin-bottom:8px;">
    ⚠️ Próximas vacunas — 15 días (<?php echo e($vacunasProximas->count()); ?>)
  </div>
  <?php $__currentLoopData = $vacunasProximas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="vacuna-card proxima">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="vacuna-nombre"><?php echo e($v->nombre_vacuna); ?></div>
        <div class="vacuna-fecha">
          <?php if($v->nombre_lote): ?>🐔 <?php echo e($v->nombre_lote); ?> · <?php endif; ?>
          <?php echo e(\Carbon\Carbon::parse($v->fecha_programada)->format('d/m/Y')); ?>

          (en <?php echo e(now()->diffInDays($v->fecha_programada)); ?> días)
        </div>
        <span class="vacuna-via"><?php echo e(str_replace('_',' ',$v->via_administracion)); ?></span>
        <?php if($v->dosis): ?><span style="font-size:.72rem;color:#64748b;margin-left:6px;">· <?php echo e($v->dosis); ?></span><?php endif; ?>
      </div>
      <button onclick="openAplicar(<?php echo e($v->id); ?>,'<?php echo e(addslashes($v->nombre_vacuna)); ?>')"
              class="btn btn-sm btn-secondary" style="white-space:nowrap;margin-left:10px;">
        💉 Aplicar
      </button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($vacunasPendientes->count()): ?>
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Pendientes</div>
    <button onclick="openModal('modalVacunaPersonal')" class="btn btn-sm btn-ghost">+ Personalizada</button>
  </div>
  <?php $__currentLoopData = $vacunasPendientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="vacuna-card" style="margin-bottom:6px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div class="vacuna-nombre"><?php echo e($v->nombre_vacuna); ?></div>
        <div class="vacuna-fecha">
          <?php if($v->nombre_lote): ?>🐔 <?php echo e($v->nombre_lote); ?> · <?php endif; ?>
          <?php echo e(\Carbon\Carbon::parse($v->fecha_programada)->format('d/m/Y')); ?>

          <?php if($v->dia_vida): ?>(día <?php echo e($v->dia_vida); ?> de vida)<?php endif; ?>
        </div>
        <span class="vacuna-via"><?php echo e(str_replace('_',' ',$v->via_administracion)); ?></span>
      </div>
      <button onclick="openAplicar(<?php echo e($v->id); ?>,'<?php echo e(addslashes($v->nombre_vacuna)); ?>')"
              class="btn btn-sm btn-ghost" style="font-size:.75rem;">Aplicar</button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($vacunasAplicadas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">✅ Aplicadas (<?php echo e($vacunasAplicadas->count()); ?>)</div>
  <?php $__currentLoopData = $vacunasAplicadas->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="vacuna-card aplicada" style="margin-bottom:4px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div class="vacuna-nombre" style="font-size:.85rem;">✅ <?php echo e($v->nombre_vacuna); ?></div>
        <div class="vacuna-fecha">
          <?php if($v->nombre_lote): ?>🐔 <?php echo e($v->nombre_lote); ?> · <?php endif; ?>
          Aplicada: <?php echo e(\Carbon\Carbon::parse($v->fecha_aplicada)->format('d/m/Y')); ?>

        </div>
        <?php if($v->producto_comercial): ?>
        <div style="font-size:.72rem;color:#64748b;">📦 <?php echo e($v->producto_comercial); ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($vacunas->isEmpty()): ?>
<div class="section-card" style="text-align:center;padding:24px;">
  <div style="font-size:2.5rem;">💉</div>
  <p style="color:#64748b;margin-bottom:8px;">Sin protocolos de vacunación configurados.</p>
  <p style="font-size:.82rem;color:#94a3b8;margin-bottom:16px;">
    Registra un lote en Animales con fecha de nacimiento para que el sistema
    genere el calendario automáticamente al ingresar aquí.
  </p>
  <button onclick="openModal('modalVacunaPersonal')" class="btn btn-sm btn-primary">
    + Agregar vacuna manualmente
  </button>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalAplicar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💉 Aplicar vacuna — <span id="nombreVacunaAplic"></span></div>
    <form id="formAplicar" method="POST" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Fecha de aplicación *</label>
        <input type="date" name="fecha_aplicada" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div class="form-group">
        <label>Producto comercial</label>
        <input type="text" name="producto_comercial" class="form-control" placeholder="Ej: Nobilis Gumboro D78">
      </div>
      <div class="form-group">
        <label>Dosis aplicada</label>
        <input type="text" name="dosis" class="form-control" placeholder="Ej: 0.2 mL/ave">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Reacción de las aves, temperatura del producto..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalAplicar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Confirmar aplicación</button>
      </div>
    </form>
  </div>
</div>


<div id="modalVacunaPersonal" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">➕ Nueva vacuna personalizada</div>
    <form method="POST" action="<?php echo e(route('avicola.vacunacion.personalizada')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Lote (opcional — vacía = todos)</label>
        <select name="animal_id" class="form-control">
          <option value="">Todos los lotes</option>
          <?php $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($l->id); ?>"><?php echo e($l->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Nombre de la vacuna *</label>
        <input type="text" name="nombre_vacuna" class="form-control" required
               placeholder="Ej: Newcastle + Bronquitis combinada">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha programada *</label>
          <input type="date" name="fecha_programada" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Vía *</label>
          <select name="via_administracion" class="form-control" required>
            <option value="ocular">Ocular</option>
            <option value="nasal">Nasal</option>
            <option value="agua">Agua de bebida</option>
            <option value="inyectable">Inyectable</option>
            <option value="aspersion">Aspersión</option>
            <option value="ala_web">Ala web</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Dosis</label>
          <input type="text" name="dosis" class="form-control" placeholder="Ej: 1 gota/ave">
        </div>
        <div class="form-group">
          <label>Producto comercial</label>
          <input type="text" name="producto_comercial" class="form-control" placeholder="Ej: Nobilis ND Clone">
        </div>
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        ✅ Se generará automáticamente una tarea en la Agenda con prioridad alta.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalVacunaPersonal')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Programar</button>
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

function openAplicar(id, nombre) {
  document.getElementById('nombreVacunaAplic').textContent = nombre;
  document.getElementById('formAplicar').action = '/avicola/vacunacion/' + id + '/aplicar';
  openModal('modalAplicar');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/vacunacion.blade.php ENDPATH**/ ?>