<?php $__env->startSection('title','Cultivos'); ?>
<?php $__env->startSection('page_title','🌱 Cultivos'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyuda')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

<div class="stats-grid mb-3">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($stats['activo'] ?? 0); ?></div><div class="stat-label">Activos</div></div>
  <div class="stat-card"><div class="stat-value text-orange"><?php echo e($stats['cosechado'] ?? 0); ?></div><div class="stat-label">Cosechados</div></div>
  <div class="stat-card"><div class="stat-value text-brown"><?php echo e($stats['vendido'] ?? 0); ?></div><div class="stat-label">Vendidos</div></div>
</div>

<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <select name="estado" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Todos</option>
    <option <?php echo e(request('estado')==='activo'?'selected':''); ?> value="activo">Activos</option>
    <option <?php echo e(request('estado')==='cosechado'?'selected':''); ?> value="cosechado">Cosechados</option>
    <option <?php echo e(request('estado')==='vendido'?'selected':''); ?> value="vendido">Vendidos</option>
  </select>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

<?php if($cultivos->isEmpty()): ?>
<div class="empty-state">
  <div class="emoji">🌱</div>
  <p><strong>Sin cultivos registrados.</strong></p>
  <p>Toca + para agregar el primero.</p>
  <button onclick="openModal('modalAyuda')" class="btn btn-ghost" style="margin-top:8px;">❓ ¿Cómo funciona?</button>
</div>
<?php else: ?>
<?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $emojis = ['Maíz'=>'🌽','Yuca'=>'🍠','Plátano'=>'🍌','Arroz'=>'🌾','Frijol'=>'🫘','Tomate'=>'🍅','Cebolla'=>'🧅','Ají'=>'🌶️','Papa'=>'🥔','Aguacate'=>'🥑','Café'=>'☕','Ganado bovino'=>'🐄','Cerdos'=>'🐷','Gallinas'=>'🐔'];
  $em = $emojis[$c->tipo] ?? '🌿';
  $b  = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado] ?? 'badge-green';
?>
<div class="list-item" style="cursor:pointer;" onclick="window.location='<?php echo e(route('cultivos.show',$c->id)); ?>'">
  <div class="item-icon" style="background:var(--verde-bg)"><?php echo e($em); ?></div>
  <div class="item-body">
    <div class="item-title"><?php echo e($c->nombre); ?></div>
    <div class="item-sub"><?php echo e($c->tipo); ?><?php echo e($c->area ? ' · '.$c->area.' '.$c->unidad : ''); ?> · <?php echo e(\Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y')); ?></div>
  </div>
  <div class="flex gap-2 items-center" onclick="event.stopPropagation()">
    <span class="badge <?php echo e($b); ?>"><?php echo e($c->estado); ?></span>
    <a href="<?php echo e(route('cultivos.show',$c->id)); ?>" class="btn btn-sm btn-secondary btn-icon" title="Ver detalle">👁️</a>
    <button onclick="openModal('editCultivo<?php echo e($c->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
    <form method="POST" action="<?php echo e(route('cultivos.destroy',$c->id)); ?>" onsubmit="return confirm('¿Eliminar este cultivo?')">
      <?php echo csrf_field(); ?> <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editCultivo<?php echo e($c->id); ?>" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cultivo</h3>
    <form method="POST" action="<?php echo e(route('cultivos.update',$c->id)); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="back" value="list">
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option><?php $__currentLoopData = $tiposCultivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($c->tipo===$t?'selected':''); ?>><?php echo e($t); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?php echo e($c->nombre); ?>"></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="<?php echo e($c->fecha_siembra); ?>"></div>
        <div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option <?php echo e($c->estado==='activo'?'selected':''); ?> value="activo">Activo</option><option <?php echo e($c->estado==='cosechado'?'selected':''); ?> value="cosechado">Cosechado</option><option <?php echo e($c->estado==='vendido'?'selected':''); ?> value="vendido">Vendido</option></select></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" value="<?php echo e($c->area); ?>"></div>
        <div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option <?php echo e($c->unidad==='hectareas'?'selected':''); ?> value="hectareas">Hectáreas</option><option <?php echo e($c->unidad==='metros2'?'selected':''); ?> value="metros2">m²</option><option <?php echo e($c->unidad==='fanegadas'?'selected':''); ?> value="fanegadas">Fanegadas</option></select></div>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control"><?php echo e($c->notas); ?></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editCultivo<?php echo e($c->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌱 Nuevo cultivo</h3>
    <form method="POST" action="<?php echo e(route('cultivos.store')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo *</label><select name="tipo" class="form-control" required><option value="">Seleccionar...</option><?php $__currentLoopData = $tiposCultivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($t); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></div>
      <div class="form-group"><label>Nombre / identificación *</label><input type="text" name="nombre" class="form-control" placeholder="Ej: Maíz Lote Norte" required></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div>
        <div class="form-group"><label>Estado</label><select name="estado" class="form-control"><option value="activo">Activo</option><option value="cosechado">Cosechado</option><option value="vendido">Vendido</option></select></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" placeholder="0.0"></div>
        <div class="form-group"><label>Unidad</label><select name="unidad" class="form-control"><option value="hectareas">Hectáreas</option><option value="metros2">m²</option><option value="fanegadas">Fanegadas</option></select></div>
      </div>
      <div class="form-group"><label>Foto inicial (opcional)</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyuda" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🌱 ¿Cómo funciona Cultivos?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Este módulo es el <strong style="color:var(--verde-dark)">centro de tu finca</strong>. Aquí registras cada cultivo que tienes sembrado y puedes hacerle seguimiento completo.</p>

      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📷</span>
          <div><strong>Galería de fotos</strong><br>Sube fotos del cultivo en diferentes etapas para ver su evolución visual.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📅</span>
          <div><strong>Línea de tiempo</strong><br>Registra eventos como aplicaciones, riegos, podas y cambios de estado. Los gastos, tareas y cosechas aparecen automáticamente.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">💰</span>
          <div><strong>Gastos del cultivo</strong><br>Cuando registras un gasto y lo asocias a este cultivo, aparece aquí para ver cuánto estás invirtiendo.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">✅</span>
          <div><strong>Agenda y tareas</strong><br>Las tareas programadas para este cultivo (riego, fumigación, etc.) se muestran en su ficha.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🌾</span>
          <div><strong>Cosechas</strong><br>Cuando cosechas, el cultivo cambia a estado "cosechado" y queda vinculado al registro de cosecha.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📦</span>
          <div><strong>Inventario</strong><br>Los insumos usados en este cultivo (fertilizantes, semillas) aparecen en su historial.</div>
        </div>
      </div>

      <p style="margin-top:16px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Toca cualquier cultivo de la lista para abrir su ficha completa con toda la información.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyuda')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevo')">+</button>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/cultivos.blade.php ENDPATH**/ ?>