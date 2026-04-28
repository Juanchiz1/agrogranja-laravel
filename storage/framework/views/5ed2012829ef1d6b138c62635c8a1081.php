
<?php $__env->startSection('title', $cultivo->nombre); ?>
<?php $__env->startSection('page_title', ($emojis[$cultivo->tipo] ?? '🌿') . ' ' . $cultivo->nombre); ?>
<?php $__env->startSection('back_url', route('cultivos.index')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/cultivos.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $em = $emojis[$cultivo->tipo] ?? '🌿';
  $badgeClass = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$cultivo->estado] ?? 'badge-green';
  $balance    = $totalIngresos - $totalGastos;
  $diasDesde  = \Carbon\Carbon::parse($cultivo->fecha_siembra)->diffInDays(now());
?>


<div class="cultivo-hero">
  <?php if($cultivo->imagen): ?>
    <img src="<?php echo e(asset($cultivo->imagen)); ?>" alt="<?php echo e($cultivo->nombre); ?>" onclick="openLightbox(this.src)">
  <?php elseif($fotos->count()): ?>
    <img src="<?php echo e(asset($fotos->first()->ruta)); ?>" alt="<?php echo e($cultivo->nombre); ?>" onclick="openLightbox(this.src)">
  <?php else: ?>
    <div class="cultivo-hero-placeholder"><?php echo e($em); ?></div>
  <?php endif; ?>
  <div class="cultivo-hero-badge">
    <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($cultivo->estado); ?></span>
  </div>
</div>


<div class="mini-stats">
  <div class="mini-stat">
    <div class="mini-stat-val"><?php echo e($diasDesde); ?></div>
    <div class="mini-stat-label">días</div>
  </div>
  <div class="mini-stat">
    <div class="mini-stat-val" style="<?php echo e($balance < 0 ? 'color:var(--rojo)' : ''); ?>">
      $<?php echo e(abs($balance) >= 1000 ? round(abs($balance)/1000,1).'k' : number_format(abs($balance),0,',','.')); ?>

    </div>
    <div class="mini-stat-label">balance</div>
  </div>
  <div class="mini-stat">
    <div class="mini-stat-val"><?php echo e($tareasPendientes); ?></div>
    <div class="mini-stat-label">tareas pend.</div>
  </div>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Información</div>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">✏️ Editar</button>
  </div>
  <div class="info-row"><span class="info-label">Tipo</span><span class="info-value"><?php echo e($em); ?> <?php echo e($cultivo->tipo); ?></span></div>
  <div class="info-row"><span class="info-label">Área</span><span class="info-value"><?php echo e($cultivo->area ? $cultivo->area.' '.$cultivo->unidad : 'No especificada'); ?></span></div>
  <div class="info-row"><span class="info-label">Fecha siembra</span><span class="info-value"><?php echo e(\Carbon\Carbon::parse($cultivo->fecha_siembra)->format('d/m/Y')); ?></span></div>
  <div class="info-row"><span class="info-label">Estado</span><span class="info-value"><span class="badge <?php echo e($badgeClass); ?>"><?php echo e($cultivo->estado); ?></span></span></div>
  <?php if($cultivo->notas): ?>
  <div style="margin-top:10px;padding:10px;background:var(--verde-bg);border-radius:8px;font-size:.85rem;color:var(--verde-dark);">
    📝 <?php echo e($cultivo->notas); ?>

  </div>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">💹 Rentabilidad</div>
    <a href="<?php echo e(route('rentabilidad.detalle', $cultivo->id)); ?>" class="btn btn-sm btn-ghost">Ver más →</a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.75rem;color:var(--text-secondary);">Ingresos</div>
      <div style="font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($totalIngresos,0,',','.')); ?></div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.75rem;color:var(--text-secondary);">Gastos</div>
      <div style="font-weight:800;color:var(--rojo);">$<?php echo e(number_format($totalGastos,0,',','.')); ?></div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:6px;">
    <span>Balance</span>
    <strong style="<?php echo e($balance >= 0 ? 'color:var(--verde-dark)' : 'color:var(--rojo)'); ?>">
      <?php echo e($balance >= 0 ? '+' : '-'); ?>$<?php echo e(number_format(abs($balance),0,',','.')); ?>

    </strong>
  </div>
  <?php if($totalGastos > 0): ?>
  <div class="rent-bar-wrap">
    <div class="rent-bar-fill" style="width:<?php echo e(min(100, ($totalIngresos/$totalGastos)*100)); ?>%; <?php echo e($balance < 0 ? 'background:var(--rojo)' : ''); ?>"></div>
  </div>
  <div style="font-size:.72rem;color:var(--text-secondary);margin-top:4px;"><?php echo e($totalGastos > 0 ? round(($totalIngresos/$totalGastos)*100) : 0); ?>% de gastos recuperados</div>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📷 Galería (<?php echo e($fotos->count()); ?>)</div>
  </div>
  <div class="foto-grid">
    <?php $__currentLoopData = $fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="foto-thumb">
      <img src="<?php echo e(asset($f->ruta)); ?>" alt="<?php echo e($f->titulo); ?>" onclick="openLightbox('<?php echo e(asset($f->ruta)); ?>')">
      <form method="POST" action="<?php echo e(route('cultivos.fotos.delete',[$cultivo->id,$f->id])); ?>" onsubmit="return confirm('¿Eliminar foto?')">
        <?php echo csrf_field(); ?>
        <button class="foto-thumb-del" type="submit">✕</button>
      </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    
    <label class="foto-upload-btn" for="fotoUploadInput">
      <span style="font-size:1.5rem;">📷</span>
      <span>Agregar foto</span>
    </label>
  </div>
  <form method="POST" action="<?php echo e(route('cultivos.fotos.upload',$cultivo->id)); ?>" enctype="multipart/form-data" id="fotoUploadForm" style="margin-top:12px;display:none;" id="fotoUploadFormWrapper">
    <?php echo csrf_field(); ?>
    <input type="file" id="fotoUploadInput" name="foto" accept="image/*" style="display:none;" onchange="handleFotoSelect(this)">
    <div id="fotoPreviewWrap" style="display:none;">
      <img id="fotoPreview" style="width:100%;border-radius:10px;margin-bottom:10px;max-height:200px;object-fit:cover;">
      <div class="form-group"><label>Título (opcional)</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Semana 3 de crecimiento"></div>
      <div class="form-group"><label>Descripción</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones de la foto..."></textarea></div>
      <div class="flex gap-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="cancelFoto()">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">📷 Subir foto</button>
      </div>
    </div>
  </form>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">💰 Gastos (<?php echo e($gastos->count()); ?>)</div>
    <a href="<?php echo e(route('gastos.index')); ?>?cultivo=<?php echo e($cultivo->id); ?>" class="btn btn-sm btn-ghost">Ver todos →</a>
  </div>
  <?php if($gastos->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">Sin gastos registrados para este cultivo.</p>
  <?php else: ?>
    <?php $__currentLoopData = $gastos->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
      <div>
        <div style="font-size:.88rem;font-weight:600;"><?php echo e($g->descripcion); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($g->categoria); ?> · <?php echo e(\Carbon\Carbon::parse($g->fecha)->format('d/m/Y')); ?></div>
      </div>
      <div style="font-weight:700;color:var(--rojo);">-$<?php echo e(number_format($g->valor,0,',','.')); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($gastos->count() > 4): ?>
    <div style="text-align:center;padding-top:10px;"><a href="<?php echo e(route('gastos.index')); ?>" class="btn btn-sm btn-ghost">Ver <?php echo e($gastos->count()-4); ?> más →</a></div>
    <?php endif; ?>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Tareas (<?php echo e($tareasPendientes); ?> pendientes)</div>
    <a href="<?php echo e(route('calendario.index')); ?>" class="btn btn-sm btn-ghost">Agenda →</a>
  </div>
  <?php if($tareas->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">Sin tareas programadas para este cultivo.</p>
  <?php else: ?>
    <?php $__currentLoopData = $tareas->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $pendiente = !$t->completada; ?>
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--border);">
      <span style="font-size:1.1rem;"><?php echo e($pendiente ? '⏳' : '✅'); ?></span>
      <div style="flex:1;">
        <div style="font-size:.88rem;font-weight:<?php echo e($pendiente ? '600' : '400'); ?>;<?php echo e($pendiente ? '' : 'text-decoration:line-through;color:var(--text-secondary)'); ?>"><?php echo e($t->titulo); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e(ucfirst($t->tipo)); ?> · <?php echo e(\Carbon\Carbon::parse($t->fecha)->format('d/m/Y')); ?></div>
      </div>
      <span class="badge <?php echo e(['alta'=>'badge-red','media'=>'badge-orange','baja'=>'badge-blue'][$t->prioridad] ?? 'badge-blue'); ?>"><?php echo e($t->prioridad); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🌾 Cosechas (<?php echo e($cosechas->count()); ?>)</div>
    <a href="<?php echo e(route('cosechas.index')); ?>" class="btn btn-sm btn-ghost">Ver todas →</a>
  </div>
  <?php if($cosechas->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">
      Aún no hay cosechas registradas.
      <?php if($cultivo->estado === 'activo'): ?>
        <br><span style="font-size:.75rem;">Cuando coseches, cambia el estado a "cosechado" y regístrala.</span>
      <?php endif; ?>
    </p>
  <?php else: ?>
    <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
      <div>
        <div style="font-size:.88rem;font-weight:600;"><?php echo e($co->producto); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?> · <?php echo e(ucfirst($co->calidad)); ?></div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:700;color:var(--verde-dark);"><?php echo e($co->cantidad); ?> <?php echo e($co->unidad); ?></div>
        <?php if($co->valor_estimado): ?><div style="font-size:.75rem;color:var(--text-secondary);">$<?php echo e(number_format($co->valor_estimado,0,',','.')); ?></div><?php endif; ?>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>


<?php if($movimientos->count()): ?>
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📦 Insumos usados (<?php echo e($movimientos->count()); ?>)</div>
    <a href="<?php echo e(route('inventario.index')); ?>" class="btn btn-sm btn-ghost">Inventario →</a>
  </div>
  <?php $__currentLoopData = $movimientos->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--border);">
    <div>
      <div style="font-size:.88rem;font-weight:600;"><?php echo e($m->insumo_nombre); ?></div>
      <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($m->motivo ?? 'Sin descripción'); ?> · <?php echo e(\Carbon\Carbon::parse($m->fecha)->format('d/m/Y')); ?></div>
    </div>
    <div style="font-weight:600;color:var(--rojo);">-<?php echo e($m->cantidad); ?> <?php echo e($m->insumo_unidad); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📜 Línea de tiempo</div>
    <button onclick="openModal('modalEvento')" class="btn btn-sm btn-primary">+ Evento</button>
  </div>

  <?php if($timeline->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">La línea de tiempo se irá llenando con tus gastos, tareas y eventos.</p>
  <?php else: ?>
  <div class="timeline">
    <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $dotClass = 'tipo-'.str_replace('_','-',$item['tipo'] ?? 'nota');
    ?>
    <div class="tl-item">
      <div class="tl-dot <?php echo e($dotClass); ?>"></div>
      <div class="tl-body">
        <div class="tl-title"><?php echo e($item['titulo']); ?></div>
        <?php if($item['descripcion']): ?><div class="tl-desc"><?php echo e($item['descripcion']); ?></div><?php endif; ?>
        <div class="tl-date">📅 <?php echo e(\Carbon\Carbon::parse($item['fecha'])->format('d/m/Y')); ?></div>
        <?php if($item['foto']): ?><img class="tl-img" src="<?php echo e(asset($item['foto'])); ?>" onclick="openLightbox('<?php echo e(asset($item['foto'])); ?>')"><?php endif; ?>
        <?php if($item['origen'] === 'evento'): ?>
        <form method="POST" action="<?php echo e(route('cultivos.eventos.delete',[$cultivo->id,$item['id']])); ?>" onsubmit="return confirm('¿Eliminar evento?')" style="margin-top:6px;">
          <?php echo csrf_field(); ?>
          <button class="btn btn-sm btn-ghost" style="font-size:.72rem;padding:2px 8px;color:var(--text-muted);">✕ Eliminar</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php endif; ?>
</div>


<div style="margin-bottom:80px;"></div>




<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cultivo</h3>
    <form method="POST" action="<?php echo e(route('cultivos.update',$cultivo->id)); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="back" value="detalle">
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required>
          <?php $__currentLoopData = $tiposCultivo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($cultivo->tipo===$t?'selected':''); ?>><?php echo e($t); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?php echo e($cultivo->nombre); ?>"></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="<?php echo e($cultivo->fecha_siembra); ?>"></div>
        <div class="form-group"><label>Estado</label>
          <select name="estado" class="form-control">
            <option <?php echo e($cultivo->estado==='activo'?'selected':''); ?> value="activo">Activo</option>
            <option <?php echo e($cultivo->estado==='cosechado'?'selected':''); ?> value="cosechado">Cosechado</option>
            <option <?php echo e($cultivo->estado==='vendido'?'selected':''); ?> value="vendido">Vendido</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" value="<?php echo e($cultivo->area); ?>"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad" class="form-control">
            <option <?php echo e($cultivo->unidad==='hectareas'?'selected':''); ?> value="hectareas">Hectáreas</option>
            <option <?php echo e($cultivo->unidad==='metros2'?'selected':''); ?> value="metros2">m²</option>
            <option <?php echo e($cultivo->unidad==='fanegadas'?'selected':''); ?> value="fanegadas">Fanegadas</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Cambiar foto principal</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control"><?php echo e($cultivo->notas); ?></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalEvento" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📝 Registrar evento</h3>
    <form method="POST" action="<?php echo e(route('cultivos.eventos.store',$cultivo->id)); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo de evento *</label>
        <select name="tipo" class="form-control" required>
          <option value="nota">📝 Nota</option>
          <option value="aplicacion">🧪 Aplicación (fertilizante/plaguicida)</option>
          <option value="riego">💧 Riego</option>
          <option value="poda">✂️ Poda</option>
          <option value="otro">🔧 Otro</option>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="titulo" class="form-control" required placeholder="Ej: Aplicación de urea 46%"></div>
      <div class="form-group"><label>Detalle</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones adicionales..."></textarea></div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required></div>
      <?php if(isset($personas) && $personas->count()): ?>
      <div class="form-group"><label>Realizado por (opcional)</label>
        <select name="persona_id" class="form-control">
          <option value="">Sin asignar</option>
          <?php $__currentLoopData = $personas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $per): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($per->id); ?>"><?php echo e($per->nombre); ?><?php echo e($per->cargo ? ' - '.$per->cargo : ''); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group"><label>Foto del evento (opcional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEvento')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar evento</button>
      </div>
    </form>
  </div>
</div>


<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}

// Foto upload preview
document.getElementById('fotoUploadInput').addEventListener('change', function() {
  handleFotoSelect(this);
});
function handleFotoSelect(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('fotoPreview').src = e.target.result;
      document.getElementById('fotoPreviewWrap').style.display = 'block';
      document.getElementById('fotoUploadForm').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function cancelFoto() {
  document.getElementById('fotoUploadInput').value = '';
  document.getElementById('fotoPreviewWrap').style.display = 'none';
  document.getElementById('fotoUploadForm').style.display = 'none';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/cultivo-detalle.blade.php ENDPATH**/ ?>