<?php $__env->startSection('title','Agenda'); ?>
<?php $__env->startSection('page_title','📅 Agenda'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<style>
.agenda-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:16px; }
.agenda-stat { background:var(--surface); border-radius:var(--radius-lg); padding:10px 8px;
  text-align:center; box-shadow:var(--shadow-sm); }
.agenda-stat-val { font-size:1.3rem; font-weight:800; }
.agenda-stat-label { font-size:.65rem; color:var(--text-secondary); margin-top:1px; }
.tabs-agenda { display:flex; gap:5px; margin-bottom:16px; overflow-x:auto; padding-bottom:2px; }
.tab-agenda { flex-shrink:0; padding:7px 12px; border-radius:99px; border:1.5px solid var(--border);
  background:var(--surface); font-size:.8rem; font-weight:600; cursor:pointer;
  color:var(--text-secondary); white-space:nowrap; text-decoration:none; display:inline-block; }
.tab-agenda.active { background:var(--verde-dark); color:#fff; border-color:var(--verde-dark); }
.tab-agenda.vencida { border-color:#fca5a5; color:#dc2626; }
.tab-agenda.vencida.active { background:#dc2626; color:#fff; border-color:#dc2626; }
.tarea-card { background:var(--surface); border-radius:var(--radius-lg); padding:12px 14px;
  margin-bottom:10px; box-shadow:var(--shadow-sm); display:flex; gap:12px; align-items:flex-start; }
.tarea-card.alta    { border-left:3px solid var(--rojo); }
.tarea-card.media   { border-left:3px solid #f59e0b; }
.tarea-card.baja    { border-left:3px solid var(--verde-dark); }
.tarea-card.vencida { border-left:3px solid #dc2626; background:#fef2f2; }
.tarea-card.completada { opacity:.6; }
.tarea-icon { width:36px; height:36px; border-radius:var(--radius-md); background:var(--verde-bg);
  display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
.tarea-body { flex:1; min-width:0; }
.tarea-titulo { font-weight:700; font-size:.9rem; }
.tarea-meta  { font-size:.75rem; color:var(--text-secondary); margin-top:3px; display:flex; flex-wrap:wrap; gap:4px; }
.tarea-chips { display:flex; flex-wrap:wrap; gap:4px; margin-top:5px; }
.chip { display:inline-flex; align-items:center; gap:3px; font-size:.7rem; padding:2px 7px;
  border-radius:99px; font-weight:600; }
.chip-verde  { background:var(--verde-bg); color:var(--verde-dark); }
.chip-purple { background:#fdf4ff; color:#7e22ce; }
.chip-blue   { background:#eff6ff; color:#1d4ed8; }
.chip-red    { background:#fef2f2; color:#dc2626; }
.chip-orange { background:#fff7ed; color:#9a3412; }
.tarea-actions { display:flex; gap:6px; align-items:center; flex-shrink:0; }
.fecha-dia { font-size:.72rem; font-weight:700; }
.vencida-banner { background:#fef2f2; border:1px solid #fca5a5; border-radius:var(--radius-lg);
  padding:12px 14px; margin-bottom:14px; }
/* Calendar */
.calendar-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:3px; }
.cal-day { text-align:center; padding:5px 2px; border-radius:6px; font-size:.8rem;
  cursor:default; color:var(--text-secondary); }
.cal-day.today { background:var(--verde-dark); color:#fff; font-weight:800; border-radius:99px; }
.cal-day.has-task { font-weight:700; color:var(--verde-dark); position:relative; }
.cal-day.has-task::after { content:'•'; position:absolute; bottom:0; left:50%; transform:translateX(-50%);
  color:var(--verde-mid); font-size:.6rem; line-height:1; }
.cal-day.has-task-high { color:var(--rojo); font-weight:800; }
.cal-day.has-task-high::after { color:var(--rojo); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:10px;">
  <button onclick="openModal('modalAyudaAgenda')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<div class="agenda-stats">
  <div class="agenda-stat" style="<?php echo e($statsVencidas > 0 ? 'background:#fef2f2' : ''); ?>">
    <div class="agenda-stat-val" style="<?php echo e($statsVencidas > 0 ? 'color:var(--rojo)' : 'color:var(--texto)'); ?>"><?php echo e($statsVencidas); ?></div>
    <div class="agenda-stat-label" style="<?php echo e($statsVencidas > 0 ? 'color:#dc2626' : ''); ?>">vencidas</div>
  </div>
  <div class="agenda-stat" style="background:#eff6ff;">
    <div class="agenda-stat-val" style="color:#1d4ed8;"><?php echo e($statsHoy); ?></div>
    <div class="agenda-stat-label">hoy</div>
  </div>
  <div class="agenda-stat">
    <div class="agenda-stat-val text-green"><?php echo e($statsProximas); ?></div>
    <div class="agenda-stat-label">próx. 7 días</div>
  </div>
  <div class="agenda-stat" style="background:var(--verde-bg);">
    <div class="agenda-stat-val text-green"><?php echo e($statsCompletadas); ?></div>
    <div class="agenda-stat-label">hechas este mes</div>
  </div>
</div>


<?php if($statsVencidas > 0 && $tab !== 'vencidas'): ?>
<div class="vencida-banner">
  <div style="font-weight:700;color:#dc2626;margin-bottom:8px;">⚠️ <?php echo e($statsVencidas); ?> tarea(s) vencida(s)</div>
  <?php $__currentLoopData = $vencidas->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="font-size:.82rem;color:#7f1d1d;margin-bottom:4px;">
    · <?php echo e($v->titulo); ?> <span style="color:#6b7280;">— <?php echo e(\Carbon\Carbon::parse($v->fecha)->diffForHumans()); ?></span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <a href="?tab=vencidas" style="font-size:.78rem;color:#dc2626;font-weight:700;">Ver todas →</a>
</div>
<?php endif; ?>


<div class="tabs-agenda">
  <a href="?tab=hoy<?php echo e(request()->has('tipo') ? '&tipo='.request('tipo') : ''); ?>" class="tab-agenda <?php echo e($tab==='hoy' ? 'active':''); ?>">
    🌅 Hoy <?php if($statsHoy): ?><span style="background:<?php echo e($tab==='hoy'?'rgba(255,255,255,.3)':'var(--verde-dark)'); ?>;color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:3px;"><?php echo e($statsHoy); ?></span><?php endif; ?>
  </a>
  <a href="?tab=vencidas" class="tab-agenda vencida <?php echo e($tab==='vencidas' ? 'active':''); ?>">
    ⚠️ Vencidas <?php if($statsVencidas): ?><span style="background:<?php echo e($tab==='vencidas'?'rgba(255,255,255,.3)':'#fca5a5'); ?>;color:<?php echo e($tab==='vencidas'?'#fff':'#dc2626'); ?>;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:3px;"><?php echo e($statsVencidas); ?></span><?php endif; ?>
  </a>
  <a href="?tab=proximas" class="tab-agenda <?php echo e($tab==='proximas' ? 'active':''); ?>">⏳ Próximas</a>
  <a href="?tab=todas" class="tab-agenda <?php echo e($tab==='todas' ? 'active':''); ?>">📋 Todas</a>
  <a href="?tab=completadas" class="tab-agenda <?php echo e($tab==='completadas' ? 'active':''); ?>">✅ Hechas</a>
</div>


<?php if(in_array($tab, ['todas','proximas','vencidas'])): ?>
<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
  <select name="tipo" class="form-control" style="flex:1;min-width:130px;" onchange="this.form.submit()">
    <option value="">Tipo de tarea</option>
    <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <optgroup label="<?php echo e($grupo); ?>">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php echo e(request('tipo')===$k?'selected':''); ?>><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </optgroup>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <select name="prioridad" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Prioridad</option>
    <option <?php echo e(request('prioridad')==='alta'?'selected':''); ?> value="alta">🔴 Alta</option>
    <option <?php echo e(request('prioridad')==='media'?'selected':''); ?> value="media">🟡 Media</option>
    <option <?php echo e(request('prioridad')==='baja'?'selected':''); ?> value="baja">🟢 Baja</option>
  </select>
  <select name="asociado" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Todo</option>
    <option <?php echo e(request('asociado')==='cultivo'?'selected':''); ?> value="cultivo">🌱 Cultivos</option>
    <option <?php echo e(request('asociado')==='animal'?'selected':''); ?> value="animal">🐄 Animales</option>
  </select>
  <?php if(request('tipo') || request('prioridad') || request('asociado')): ?>
    <a href="?tab=<?php echo e($tab); ?>" class="btn btn-ghost" style="font-size:.8rem;">✕ Limpiar</a>
  <?php endif; ?>
</form>
<?php endif; ?>


<?php if($tab === 'todas'): ?>
<?php
  $mesDate = \Carbon\Carbon::parse($mes.'-01');
  $diasEnMes = $mesDate->daysInMonth;
  $diaSemanaInicio = $mesDate->dayOfWeekIso;
  $hoy2 = now()->toDateString();
  $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
  <div class="flex items-center justify-between mb-3">
    <a href="?tab=todas&mes=<?php echo e(\Carbon\Carbon::parse($mes.'-01')->subMonth()->format('Y-m')); ?>" class="btn btn-sm btn-secondary btn-icon">‹</a>
    <span class="font-bold"><?php echo e($meses[$mesDate->month]); ?> <?php echo e($mesDate->year); ?></span>
    <a href="?tab=todas&mes=<?php echo e(\Carbon\Carbon::parse($mes.'-01')->addMonth()->format('Y-m')); ?>" class="btn btn-sm btn-secondary btn-icon">›</a>
  </div>
  <div style="display:grid;grid-template-columns:repeat(7,1fr);gap:2px;text-align:center;font-size:.68rem;color:var(--text-muted);margin-bottom:6px;">
    <?php $__currentLoopData = ['L','M','X','J','V','S','D']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div style="font-weight:700;"><?php echo e($d); ?></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <div class="calendar-grid">
    <?php for($b=1; $b<$diaSemanaInicio; $b++): ?><div></div><?php endfor; ?>
    <?php for($d=1; $d<=$diasEnMes; $d++): ?>
      <?php $ds=$mes.'-'.str_pad($d,2,'0',STR_PAD_LEFT); $cls='cal-day'; if($ds===$hoy2) $cls.=' today'; if(in_array($ds,$diasConTareas)) $cls.=' has-task'; ?>
      <div class="<?php echo e($cls); ?>"><?php echo e($d); ?></div>
    <?php endfor; ?>
  </div>
</div>
<?php endif; ?>


<?php
$tiposPlanos2 = [];
foreach($tipos as $grupo => $items) foreach($items as $k=>$v) $tiposPlanos2[$k]=$v;
?>

<?php if($tareas->isEmpty()): ?>
<div class="empty-state">
  <div class="emoji"><?php echo e($tab==='hoy'?'🌅':($tab==='vencidas'?'✅':'📅')); ?></div>
  <p><?php echo e($tab==='hoy'?'No hay tareas para hoy. 🎉':($tab==='vencidas'?'Sin tareas vencidas. ✅':'No hay tareas aquí.')); ?></p>
</div>
<?php else: ?>


<?php
  $agrupadas = in_array($tab,['todas','proximas','hoy']) ? $tareas->groupBy('fecha') : collect([''=>$tareas]);
  $hoy3 = now()->toDateString();
?>

<?php $__currentLoopData = $agrupadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fecha => $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php if($fecha && in_array($tab,['todas','proximas'])): ?>
<div style="font-size:.78rem;font-weight:700;color:var(--text-secondary);margin:14px 0 6px;text-transform:uppercase;letter-spacing:.05em;">
  <?php
    $f = \Carbon\Carbon::parse($fecha);
    $label = $fecha===$hoy3 ? '📍 Hoy — '.$f->format('d/m') : ($fecha===now()->addDay()->toDateString() ? '👉 Mañana — '.$f->format('d/m') : $f->isoFormat('dddd D/MM'));
  ?>
  <?php echo e($label); ?>

</div>
<?php endif; ?>

<?php $__currentLoopData = $grupo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $icono = $tiposPlanos2[$t->tipo] ?? '📝 '.ucfirst($t->tipo);
  $icono = explode(' ', $icono)[0]; // solo emoji
  $esVencida = !$t->completada && $t->fecha < $hoy3;
  $pc = ['alta'=>'alta','media'=>'media','baja'=>'baja'][$t->prioridad] ?? 'baja';
?>
<div class="tarea-card <?php echo e($t->completada?'completada':($esVencida?'vencida':$pc)); ?>">
  <div class="tarea-icon"><?php echo e($icono); ?></div>
  <div class="tarea-body">
    <div class="tarea-titulo" style="<?php echo e($t->completada?'text-decoration:line-through;color:var(--text-muted)':''); ?>"><?php echo e($t->titulo); ?></div>

    <div class="tarea-meta">
      <span>📅 <?php echo e(\Carbon\Carbon::parse($t->fecha)->format('d/m/Y')); ?><?php echo e($t->hora ? ' · ⏰ '.substr($t->hora,0,5) : ''); ?></span>
      <?php if($esVencida): ?><span style="color:#dc2626;font-weight:700;">⚠️ <?php echo e(\Carbon\Carbon::parse($t->fecha)->diffForHumans()); ?></span><?php endif; ?>
      <?php if($t->responsable): ?><span>👤 <?php echo e($t->responsable); ?></span><?php endif; ?>
    </div>

    <div class="tarea-chips">
      <span class="chip <?php echo e(['alta'=>'chip-red','media'=>'chip-orange','baja'=>'chip-verde'][$t->prioridad]??'chip-verde'); ?>"><?php echo e(['alta'=>'🔴','media'=>'🟡','baja'=>'🟢'][$t->prioridad]??''); ?> <?php echo e(ucfirst($t->prioridad)); ?></span>
      <?php if($t->cultivo_nombre): ?><span class="chip chip-verde">🌱 <?php echo e($t->cultivo_nombre); ?></span><?php endif; ?>
      <?php if($t->animal_nombre ?? null): ?><span class="chip chip-purple">🐄 <?php echo e($t->animal_especie); ?> - <?php echo e($t->animal_nombre); ?></span><?php endif; ?>
      <?php if($t->notas): ?><span class="chip chip-blue">📝 Con notas</span><?php endif; ?>
      <?php if($t->notas_completada ?? null): ?><span class="chip chip-verde">✅ <?php echo e(Str::limit($t->notas_completada, 25)); ?></span><?php endif; ?>
    </div>
  </div>

  <div class="tarea-actions" onclick="event.stopPropagation()">
    <?php if(!$t->completada): ?>
      <button onclick="openModal('completarTarea<?php echo e($t->id); ?>')" class="btn btn-sm btn-secondary btn-icon" title="Marcar como hecha">✓</button>
      <button onclick="openModal('editTarea<?php echo e($t->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <?php endif; ?>
    <form method="POST" action="<?php echo e(route('tareas.destroy',$t->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>


<?php if(!$t->completada): ?>
<div class="modal-overlay" id="completarTarea<?php echo e($t->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">✅ Completar tarea</h3>
    <p style="font-size:.88rem;color:var(--text-secondary);margin-bottom:14px;"><?php echo e($t->titulo); ?></p>
    <form method="POST" action="<?php echo e(route('tareas.completar',$t->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>¿Cómo quedó? (opcional)</label>
        <textarea name="notas_completada" class="form-control" rows="3" placeholder="Ej: Se aplicaron 3 litros, todo bien. / Nació una ternera sana."></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('completarTarea<?php echo e($t->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">✅ Marcar como hecha</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editTarea<?php echo e($t->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar tarea</h3>
    <form method="POST" action="<?php echo e(route('tareas.update',$t->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" required value="<?php echo e($t->titulo); ?>"></div>
      <div class="form-group"><label>Tipo</label>
        <select name="tipo" class="form-control">
          <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <optgroup label="<?php echo e($grupo); ?>">
              <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php echo e($t->tipo===$k?'selected':''); ?>><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Prioridad</label>
          <select name="prioridad" class="form-control">
            <option <?php echo e($t->prioridad==='alta'?'selected':''); ?> value="alta">🔴 Alta</option>
            <option <?php echo e($t->prioridad==='media'?'selected':''); ?> value="media">🟡 Media</option>
            <option <?php echo e($t->prioridad==='baja'?'selected':''); ?> value="baja">🟢 Baja</option>
          </select>
        </div>
        <div class="form-group"><label>Responsable</label><input type="text" name="responsable" class="form-control" value="<?php echo e($t->responsable); ?>" placeholder="Nombre"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($t->fecha); ?>"></div>
        <div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control" value="<?php echo e($t->hora); ?>"></div>
      </div>
      <div class="form-group"><label>Cultivo relacionado</label>
        <select name="cultivo_id" class="form-control"><option value="">Ninguno</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>" <?php echo e($t->cultivo_id==$cv->id?'selected':''); ?>>🌱 <?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Animal relacionado</label>
        <select name="animal_id" class="form-control"><option value="">Ninguno</option>
          <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($an->id); ?>" <?php echo e(($t->animal_id??null)==$an->id?'selected':''); ?>>🐄 <?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2"><?php echo e($t->notas); ?></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editTarea<?php echo e($t->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevaTarea" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">📅 Nueva tarea</h3>
    <form method="POST" action="<?php echo e(route('tareas.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Título *</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Riego lote de maíz" required></div>
      <div class="form-group"><label>Tipo de tarea</label>
        <select name="tipo" class="form-control">
          <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <optgroup label="<?php echo e($grupo); ?>">
              <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($v); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Prioridad</label>
          <select name="prioridad" class="form-control">
            <option value="alta">🔴 Alta</option>
            <option value="media" selected>🟡 Media</option>
            <option value="baja">🟢 Baja</option>
          </select>
        </div>
        <div class="form-group"><label>Responsable</label><input type="text" name="responsable" class="form-control" placeholder="¿Quién lo hace?"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div>
        <div class="form-group"><label>Hora</label><input type="time" name="hora" class="form-control"></div>
      </div>
      <div class="form-group"><label>Asociar a cultivo</label>
        <select name="cultivo_id" class="form-control"><option value="">Ninguno</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>">🌱 <?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Asociar a animal</label>
        <select name="animal_id" class="form-control"><option value="">Ninguno</option>
          <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($an->id); ?>">🐄 <?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Detalles..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaTarea')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyudaAgenda" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">📅 ¿Cómo funciona la Agenda?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">La agenda es tu <strong style="color:var(--verde-dark)">centro de control diario</strong>. Programa todo lo que hay que hacer en la finca y lleva seguimiento.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">🌅</span><div><strong>Hoy</strong><br>Ve solo las tareas del día. Si está vacío, ¡todo al día!</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">⚠️</span><div><strong>Vencidas</strong><br>Tareas que ya pasaron su fecha sin completarse. Están resaltadas en rojo.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">🌱🐄</span><div><strong>Asociar a cultivo o animal</strong><br>Cada tarea puede vincularse a un cultivo o un animal. Así también aparecen en sus fichas.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">👤</span><div><strong>Responsable</strong><br>Asigna la tarea a un jornalero, socio o familiar para saber quién la debe hacer.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">✅</span><div><strong>Completar con notas</strong><br>Al marcar una tarea como hecha puedes escribir qué pasó — un registro de lo ocurrido.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📋</span><div><strong>20+ tipos de tarea</strong><br>Desde riego y fumigación hasta vacunas animales, pagos a proveedores y revisión de inventario.</div></div>
      </div>
      <p style="margin-top:14px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Las próximas dosis de medicamentos/vacunas de animales también aparecen aquí como tareas automáticas.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaAgenda')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevaTarea')">+</button>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/calendario.blade.php ENDPATH**/ ?>