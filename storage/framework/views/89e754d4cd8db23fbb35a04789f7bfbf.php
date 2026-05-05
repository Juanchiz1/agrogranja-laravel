
<?php $__env->startSection('title','Sanidad Porcina'); ?>
<?php $__env->startSection('page_title','💉 Sanidad Porcina'); ?>
<?php $__env->startSection('back_url', route('porcicola.piara')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="alerta-porc info" style="margin-bottom:10px;">
  <span>💡</span>
  <div style="font-size:.8rem;">
    <strong>Protocolos clave en Colombia:</strong>
    PPC (cada 6 meses), Parvovirus (anual), Leptospirosis (cada 6 meses), Desparasitación (cada 3 meses).
    Lechones: Hierro dextrano día 3, Ronco/Pata (Mycoplasma) día 7, PPC día 45.
  </div>
</div>


<?php if($vencidas->count()): ?>
<div class="section-card" style="border-left:4px solid #dc2626;">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    ❌ Vencidas (<?php echo e($vencidas->count()); ?>)
  </div>
  <?php $__currentLoopData = $vencidas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="sanidad-porc-card vencida">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.88rem;"><?php echo e($s->nombre_protocolo); ?></div>
        <div style="font-size:.74rem;color:#991b1b;">
          <?php if($s->nombre_lote): ?>🐖 <?php echo e($s->nombre_lote); ?> · <?php endif; ?>
          Venció: <?php echo e(\Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y')); ?>

          · <?php echo e(str_replace('_',' ',$s->tipo)); ?>

        </div>
        <?php if($s->dosis): ?><div style="font-size:.72rem;color:#64748b;">Dosis: <?php echo e($s->dosis); ?></div><?php endif; ?>
      </div>
      <button onclick="openAplicar(<?php echo e($s->id); ?>,'<?php echo e(addslashes($s->nombre_protocolo)); ?>')"
              class="btn btn-sm btn-primary" style="white-space:nowrap;margin-left:8px;">Aplicar</button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($proximas->count()): ?>
<div class="section-card">
  <div class="section-title" style="color:#b45309;margin-bottom:8px;">
    ⚠️ Próximas 15 días (<?php echo e($proximas->count()); ?>)
  </div>
  <?php $__currentLoopData = $proximas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="sanidad-porc-card proxima">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.88rem;"><?php echo e($s->nombre_protocolo); ?></div>
        <div style="font-size:.74rem;color:#92400e;">
          <?php if($s->nombre_lote): ?>🐖 <?php echo e($s->nombre_lote); ?> · <?php endif; ?>
          <?php echo e(\Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y')); ?>

          (en <?php echo e(now()->diffInDays($s->fecha_programada)); ?> días)
        </div>
        <span style="font-size:.7rem;background:#e2e8f0;color:#475569;padding:1px 7px;border-radius:8px;">
          <?php echo e(str_replace('_',' ',$s->via_administracion)); ?>

        </span>
        <?php if($s->dosis): ?><span style="font-size:.7rem;color:#64748b;margin-left:6px;">· <?php echo e($s->dosis); ?></span><?php endif; ?>
      </div>
      <button onclick="openAplicar(<?php echo e($s->id); ?>,'<?php echo e(addslashes($s->nombre_protocolo)); ?>')"
              class="btn btn-sm btn-secondary" style="white-space:nowrap;margin-left:8px;">Aplicar</button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Protocolos activos</div>
    <button onclick="openModal('modalSanidadPersonal')" class="btn btn-sm btn-ghost">+ Personalizado</button>
  </div>
  <?php $__empty_1 = true; $__currentLoopData = $pendientesFut; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <div class="sanidad-porc-card" style="margin-bottom:6px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-weight:700;font-size:.87rem;"><?php echo e($s->nombre_protocolo); ?></div>
        <div style="font-size:.74rem;color:#64748b;">
          <?php if($s->nombre_lote): ?>🐖 <?php echo e($s->nombre_lote); ?> · <?php endif; ?>
          <?php echo e(\Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y')); ?>

          · <?php echo e(str_replace('_',' ',$s->tipo)); ?>

        </div>
      </div>
      <button onclick="openAplicar(<?php echo e($s->id); ?>,'<?php echo e(addslashes($s->nombre_protocolo)); ?>')"
              class="btn btn-sm btn-ghost" style="font-size:.74rem;">Aplicar</button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <?php if($vencidas->isEmpty() && $proximas->isEmpty()): ?>
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    <div style="font-size:2.5rem;">💉</div>
    <p>Sin protocolos configurados.</p>
    <p style="font-size:.82rem;">Los protocolos se crean automáticamente al registrar cerdos en Animales.</p>
  </div>
  <?php endif; ?>
  <?php endif; ?>
</div>


<?php if($aplicadas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">✅ Aplicadas (<?php echo e($aplicadas->count()); ?>)</div>
  <?php $__currentLoopData = $aplicadas->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="sanidad-porc-card ok" style="margin-bottom:4px;">
    <div style="display:flex;justify-content:space-between;align-items:center;font-size:.83rem;">
      <div>
        <span style="font-weight:700;">✅ <?php echo e($s->nombre_protocolo); ?></span>
        <?php if($s->nombre_lote): ?><span style="color:#64748b;"> · <?php echo e($s->nombre_lote); ?></span><?php endif; ?>
        <div style="font-size:.72rem;color:#64748b;">
          Aplicada: <?php echo e(\Carbon\Carbon::parse($s->fecha_aplicada)->format('d/m/Y')); ?>

          <?php if($s->producto_usado): ?> · <?php echo e($s->producto_usado); ?><?php endif; ?>
          <?php if($s->proxima_aplicacion): ?>
            · Próxima: <?php echo e(\Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y')); ?>

          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalAplicar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💉 Aplicar — <span id="nombreProtocolo"></span></div>
    <form id="formAplicar" method="POST" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Fecha de aplicación *</label>
        <input type="date" name="fecha_aplicada" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div class="form-group">
        <label>Producto comercial usado</label>
        <input type="text" name="producto_usado" class="form-control" placeholder="Ej: Porcilis PCV ID">
      </div>
      <div class="form-group">
        <label>Dosis aplicada</label>
        <input type="text" name="dosis" class="form-control" placeholder="Ej: 2 mL/animal">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Reacción, número de animales tratados..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalAplicar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Confirmar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalSanidadPersonal" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">➕ Nuevo protocolo sanitario</div>
    <form method="POST" action="<?php echo e(route('porcicola.sanidad.personalizado')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Animal / lote (opcional)</label>
        <select name="animal_id" class="form-control">
          <option value="">Toda la piara</option>
          <?php $__currentLoopData = $porcinos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($p->id); ?>"><?php echo e($p->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Nombre del protocolo *</label>
        <input type="text" name="nombre_protocolo" class="form-control" required
               placeholder="Ej: Ronco y Pata, Erisipela...">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo" class="form-control" required>
            <option value="vacuna">Vacuna</option>
            <option value="desparasitante">Desparasitante</option>
            <option value="antibiotico">Antibiótico</option>
            <option value="vitamina">Vitamina</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Vía *</label>
          <select name="via_administracion" class="form-control" required>
            <option value="intramuscular">Intramuscular</option>
            <option value="subcutanea">Subcutánea</option>
            <option value="oral">Oral</option>
            <option value="topica">Tópica</option>
            <option value="agua">Agua de bebida</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha programada *</label>
          <input type="date" name="fecha_programada" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Repetir cada (días)</label>
          <input type="number" name="frecuencia_dias" class="form-control" min="0" placeholder="Ej: 180">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Dosis</label>
          <input type="text" name="dosis" class="form-control" placeholder="Ej: 2 mL/animal">
        </div>
        <div class="form-group">
          <label>Producto comercial</label>
          <input type="text" name="producto_usado" class="form-control" placeholder="Nombre comercial">
        </div>
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        ✅ Se generará una tarea en la Agenda con prioridad alta.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalSanidadPersonal')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
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
  document.getElementById('nombreProtocolo').textContent = nombre;
  document.getElementById('formAplicar').action = '/porcicola/sanidad/' + id + '/aplicar';
  openModal('modalAplicar');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/porcicola/sanidad.blade.php ENDPATH**/ ?>