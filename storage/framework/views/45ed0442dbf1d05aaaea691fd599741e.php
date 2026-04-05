<?php $__env->startSection('title','Agenda'); ?>
<?php $__env->startSection('page_title','📅 Agenda y Tareas'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>
<?php $__env->startSection('content'); ?>

<div class="tabs">
  <button class="tab-btn <?php echo e($tab==='proximas'?'active':''); ?>" onclick="location.href='?tab=proximas'">⏳ Próximas</button>
  <button class="tab-btn <?php echo e($tab==='tareas'?'active':''); ?>"   onclick="location.href='?tab=tareas'">📋 Todas</button>
  <button class="tab-btn <?php echo e($tab==='completadas'?'active':''); ?>" onclick="location.href='?tab=completadas'">✅ Hechas</button>
</div>

<?php if($tab === 'tareas'): ?>

<?php
  $mesDate = \Carbon\Carbon::parse($mes . '-01');
  $diasEnMes = $mesDate->daysInMonth;
  $diaSemanaInicio = $mesDate->dayOfWeekIso; // 1=Mon
  $hoy = now()->toDateString();
  $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<div class="card mb-3" style="padding:14px;">
  <div class="flex items-center justify-between mb-3">
    <a href="?tab=tareas&mes=<?php echo e(\Carbon\Carbon::parse($mes.'-01')->subMonth()->format('Y-m')); ?>" class="btn btn-sm btn-secondary btn-icon">‹</a>
    <span class="font-bold"><?php echo e($meses[$mesDate->month]); ?> <?php echo e($mesDate->year); ?></span>
    <a href="?tab=tareas&mes=<?php echo e(\Carbon\Carbon::parse($mes.'-01')->addMonth()->format('Y-m')); ?>" class="btn btn-sm btn-secondary btn-icon">›</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;font-size:.68rem;color:var(--gris);margin-bottom:4px;">
    <?php $__currentLoopData = ['L','M','X','J','V','S','D']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><?php echo e($d); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <div class="calendar-grid">
    <?php for($b = 1; $b < $diaSemanaInicio; $b++): ?><div></div><?php endfor; ?>
    <?php for($d = 1; $d <= $diasEnMes; $d++): ?>
      <?php $ds = $mes.'-'.str_pad($d,2,'0',STR_PAD_LEFT); $cls='cal-day'; if($ds===$hoy) $cls.=' today'; if(in_array($ds,$diasConTareas)) $cls.=' has-task'; ?>
      <div class="<?php echo e($cls); ?>"><?php echo e($d); ?></div>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>

<?php $tiposIcon = ['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝']; ?>

<?php if($tareas->isEmpty()): ?>
<div class="empty-state"><div class="emoji">📅</div><p>No hay tareas aquí.</p></div>
<?php else: ?>
<?php $__currentLoopData = $tareas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $ic=$tiposIcon[$t->tipo]??'📝'; $pc=['alta'=>'var(--rojo)','media'=>'var(--naranja)','baja'=>'var(--verde-dark)'][$t->prioridad]??'var(--gris)'; ?>
<div class="list-item" style="<?php echo e($t->completada ? 'opacity:.55' : ''); ?>">
  <div class="item-icon" style="background:var(--verde-bg)"><?php echo e($ic); ?></div>
  <div class="item-body">
    <div class="item-title" style="<?php echo e($t->completada ? 'text-decoration:line-through' : ''); ?>"><?php echo e($t->titulo); ?></div>
    <div class="item-sub"><span style="color:<?php echo e($pc); ?>;font-weight:700;text-transform:capitalize;"><?php echo e($t->prioridad); ?></span><?php echo e($t->hora ? ' · '.substr($t->hora,0,5) : ''); ?><?php echo e($t->cultivo_nombre ? ' · '.$t->cultivo_nombre : ''); ?> · <?php echo e(\Carbon\Carbon::parse($t->fecha)->format('d/m/Y')); ?></div>
  </div>
  <div class="flex gap-2 items-center" style="flex-shrink:0;">
    <?php if(!$t->completada): ?>
    <form method="POST" action="<?php echo e(route('tareas.completar',$t->id)); ?>"><?php echo csrf_field(); ?><button class="btn btn-sm btn-secondary btn-icon">✓</button></form>
    <button onclick="openModal('editTarea<?php echo e($t->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('tareas.destroy',$t->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?><button class="btn btn-sm btn-danger btn-icon">🗑️</button></form>
  </div>
</div>
<?php if(!$t->completada): ?>
<div class="modal-overlay" id="editTarea<?php echo e($t->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar tarea</h3>
    <form method="POST" action="<?php echo e(route('tareas.update',$t->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" required value="<?php echo e($t->titulo); ?>"></div>
      <div class="grid-2"><div class="form-group"><label>Tipo</label><select name="tipo" class="form-control"><?php $__currentLoopData = $tiposIcon; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$ic2): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>" <?php echo e($t->tipo===$v?'selected':''); ?>><?php echo e($ic2); ?> <?php echo e(ucfirst($v)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="form-group"><label>Prioridad</label><select name="prioridad" class="form-control"><option <?php echo e($t->prioridad==='alta'?'selected':''); ?> value="alta">🔴 Alta</option><option <?php echo e($t->prioridad==='media'?'selected':''); ?> value="media">🟡 Media</option><option <?php echo e($t->prioridad==='baja'?'selected':''); ?> value="baja">🟢 Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($t->fecha); ?>"></div><div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control" value="<?php echo e($t->hora); ?>"></div></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control"><?php echo e($t->notas); ?></textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editTarea<?php echo e($t->id); ?>')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Actualizar</button></div>
    </form>
  </div>
</div>
<?php endif; ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<div class="modal-overlay" id="modalNuevaTarea" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">📅 Nueva tarea</h3>
    <form method="POST" action="<?php echo e(route('tareas.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Riego lote de maíz" required></div>
      <div class="grid-2"><div class="form-group"><label>Tipo</label><select name="tipo" class="form-control"><?php $__currentLoopData = $tiposIcon; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$ic): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($v); ?>"><?php echo e($ic); ?> <?php echo e(ucfirst($v)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><div class="form-group"><label>Prioridad</label><select name="prioridad" class="form-control"><option value="alta">🔴 Alta</option><option value="media" selected>🟡 Media</option><option value="baja">🟢 Baja</option></select></div></div>
      <div class="grid-2"><div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div><div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control"></div></div>
      <?php if($cultivos->count()): ?><div class="form-group"><label>Cultivo relacionado</label><select name="cultivo_id" class="form-control"><option value="">Ninguno</option><?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div><?php endif; ?>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" placeholder="Detalles..."></textarea></div>
      <div class="flex gap-2 mt-2"><button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaTarea')">Cancelar</button><button type="submit" class="btn btn-primary btn-full">Guardar</button></div>
    </form>
  </div>
</div>
<button class="fab" onclick="openModal('modalNuevaTarea')">+</button>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/calendario.blade.php ENDPATH**/ ?>