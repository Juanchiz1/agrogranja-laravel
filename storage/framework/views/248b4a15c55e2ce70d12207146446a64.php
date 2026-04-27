
<?php $__env->startSection('title', $animal->nombre_lote ?: $animal->especie); ?>
<?php $__env->startSection('page_title', ($emojis[$animal->especie]??'🐾').' '.($animal->nombre_lote ?: $animal->especie)); ?>
<?php $__env->startSection('back_url', route('animales.index')); ?>

<?php $__env->startPush('head'); ?>
<style>
.animal-hero { border-radius:var(--radius-xl); overflow:hidden; margin-bottom:20px;
  position:relative; background:var(--verde-bg); min-height:180px; display:flex; align-items:flex-end; }
.animal-hero img { width:100%; height:220px; object-fit:cover; display:block; }
.animal-hero-placeholder { width:100%; height:180px; display:flex; align-items:center;
  justify-content:center; font-size:6rem; background:linear-gradient(135deg,var(--verde-bg),#d4edda); }
.section-card { background:var(--surface); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px; box-shadow:var(--shadow-sm); }
.section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; }
.section-title { font-weight:700; font-size:.95rem; color:var(--text-primary); display:flex; align-items:center; gap:8px; }
.info-row { display:flex; justify-content:space-between; padding:7px 0; border-bottom:1px solid var(--border); font-size:.87rem; }
.info-row:last-child { border:none; }
.info-label { color:var(--text-secondary); }
.info-value { font-weight:600; }
.foto-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; }
.foto-thumb { aspect-ratio:1; border-radius:var(--radius-md); overflow:hidden; position:relative; background:var(--verde-bg); cursor:pointer; }
.foto-thumb img { width:100%; height:100%; object-fit:cover; }
.foto-thumb-del { position:absolute; top:4px; right:4px; background:rgba(0,0,0,.55); border:none; border-radius:50%; width:24px; height:24px; font-size:.7rem; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.foto-upload-btn { aspect-ratio:1; border-radius:var(--radius-md); border:2px dashed var(--verde-light); background:var(--verde-bg); display:flex; flex-direction:column; align-items:center; justify-content:center; font-size:.75rem; color:var(--verde-dark); cursor:pointer; gap:4px; }
.peso-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border); font-size:.87rem; }
.peso-row:last-child { border:none; }
/* Timeline */
.timeline { position:relative; padding-left:28px; }
.timeline::before { content:''; position:absolute; left:10px; top:0; bottom:0; width:2px; background:var(--verde-light); }
.tl-item { position:relative; margin-bottom:16px; }
.tl-dot { position:absolute; left:-24px; top:3px; width:14px; height:14px; border-radius:50%; background:var(--verde); border:2px solid var(--surface); box-shadow:0 0 0 2px var(--verde-light); }
.tl-dot.medicamento,.tl-dot.vacuna { background:#6366f1; box-shadow:0 0 0 2px #c7d2fe; }
.tl-dot.gasto { background:#f59e0b; box-shadow:0 0 0 2px #fde68a; }
.tl-dot.peso  { background:#0ea5e9; box-shadow:0 0 0 2px #bae6fd; }
.tl-dot.venta { background:#16a34a; box-shadow:0 0 0 2px #bbf7d0; }
.tl-dot.sacrificio,.tl-dot.enfermedad { background:#dc2626; box-shadow:0 0 0 2px #fca5a5; }
.tl-dot.traslado { background:#d97706; box-shadow:0 0 0 2px #fde68a; }
.tl-body { background:var(--surface); border-radius:var(--radius-md); padding:10px 12px; box-shadow:var(--shadow-sm); }
.tl-title { font-weight:600; font-size:.88rem; }
.tl-desc  { font-size:.8rem; color:var(--text-secondary); margin-top:2px; }
.tl-date  { font-size:.72rem; color:var(--text-muted); margin-top:4px; }
.tl-img   { width:100%; border-radius:6px; margin-top:8px; max-height:150px; object-fit:cover; }
.propietario-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border); }
.propietario-row:last-child { border:none; }
.venta-calc { background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-radius:var(--radius-lg); padding:16px; margin-bottom:16px; border:1px solid #86efac; }
.lightbox { display:none; position:fixed; inset:0; background:rgba(0,0,0,.92); z-index:9999; align-items:center; justify-content:center; }
.lightbox.open { display:flex; }
.lightbox img { max-width:94vw; max-height:88vh; border-radius:10px; }
.lightbox-close { position:absolute; top:18px; right:18px; background:rgba(255,255,255,.15); border:none; color:#fff; font-size:1.4rem; width:40px; height:40px; border-radius:50%; cursor:pointer; }
/* Chips de período en modal producción */
.chip-p { padding:5px 12px; border-radius:99px; border:1.5px solid var(--border);
  background:var(--surface); font-size:.8rem; font-weight:500; cursor:pointer;
  color:var(--text-secondary); transition:all .15s; }
.chip-p.active { background:var(--verde-bg); border-color:var(--verde-dark);
  color:var(--verde-dark); font-weight:700; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $em = $emojis[$animal->especie] ?? '🐾';
  $badgeClass = ['activo'=>'badge-green','vendido'=>'badge-brown','muerte'=>'badge-red'][$animal->estado] ?? 'badge-green';
  $balance = $totalIngresos - $totalGastos;
  $tieneProduccion = $animal->produccion && !in_array(strtolower(trim($animal->produccion)), ['carne','','null']);
?>


<div class="animal-hero">
  <?php if($animal->foto): ?>
    <img src="<?php echo e(asset($animal->foto)); ?>" alt="<?php echo e($animal->nombre_lote); ?>" onclick="openLightbox(this.src)">
  <?php elseif($fotos->count()): ?>
    <img src="<?php echo e(asset($fotos->first()->ruta)); ?>" onclick="openLightbox(this.src)">
  <?php else: ?>
    <div class="animal-hero-placeholder"><?php echo e($em); ?></div>
  <?php endif; ?>
  <div style="position:absolute;top:12px;right:12px;display:flex;gap:6px;">
    <span class="badge <?php echo e($badgeClass); ?>"><?php echo e($animal->estado); ?></span>
    <?php if($animal->favorito): ?> <span style="background:#fef9c3;border-radius:99px;padding:2px 8px;font-size:.8rem;">⭐ Favorito</span><?php endif; ?>
    <?php if($animal->atencion_especial): ?> <span style="background:#fef2f2;border-radius:99px;padding:2px 8px;font-size:.8rem;color:#dc2626;">🚨 Atención</span><?php endif; ?>
  </div>
</div>


<div class="stats-grid mb-3" style="grid-template-columns:repeat(3,1fr);gap:10px;">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($animal->cantidad); ?></div><div class="stat-label">animales</div></div>
  <div class="stat-card"><div class="stat-value" style="<?php echo e($balance<0?'color:var(--rojo)':''); ?>">$<?php echo e(abs($balance)>=1000?round(abs($balance)/1000,1).'k':number_format(abs($balance),0,',','.')); ?></div><div class="stat-label">balance</div></div>
  <div class="stat-card"><div class="stat-value"><?php echo e($animal->peso_promedio ? $animal->peso_promedio.$animal->unidad_peso : '—'); ?></div><div class="stat-label">peso prom.</div></div>
</div>


<?php if($animal->estado === 'activo' && ($animal->precio_kilo || $animal->precio_unidad)): ?>
<div class="venta-calc">
  <p style="font-weight:700;color:#166534;margin-bottom:8px;">💰 Valor estimado de venta</p>
  <?php if($animal->vende_por_kilo && $animal->precio_kilo && $animal->peso_promedio): ?>
    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">
      $<?php echo e(number_format($animal->precio_kilo * $animal->peso_promedio * $animal->cantidad, 0, ',', '.')); ?>

    </div>
    <div style="font-size:.78rem;color:#166534;"><?php echo e($animal->cantidad); ?> animal(es) × <?php echo e($animal->peso_promedio); ?><?php echo e($animal->unidad_peso); ?> × $<?php echo e(number_format($animal->precio_kilo,0,',','.')); ?>/kg</div>
  <?php elseif($animal->precio_unidad): ?>
    <div style="font-size:1.4rem;font-weight:800;color:#16a34a;">
      $<?php echo e(number_format($animal->precio_unidad * $animal->cantidad, 0, ',', '.')); ?>

    </div>
    <div style="font-size:.78rem;color:#166534;"><?php echo e($animal->cantidad); ?> animal(es) × $<?php echo e(number_format($animal->precio_unidad,0,',','.')); ?>/cabeza</div>
  <?php endif; ?>
  <?php if($animal->estado === 'activo'): ?>
  <button onclick="openModal('modalSalida')" class="btn btn-sm btn-primary mt-2" style="font-size:.82rem;">Registrar venta / sacrificio →</button>
  <?php endif; ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Información</div>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">✏️ Editar</button>
  </div>
  <div class="info-row"><span class="info-label">Especie</span><span class="info-value"><?php echo e($em); ?> <?php echo e($animal->especie); ?></span></div>
  <div class="info-row"><span class="info-label">Etapa</span><span class="info-value"><?php echo e(['cria'=>'🐣 Cría','juvenil'=>'🐥 Juvenil','adulto'=>'✅ Adulto'][$animal->etapa_vida??'adulto']); ?></span></div>
  <?php if($animal->ubicacion): ?><div class="info-row"><span class="info-label">Ubicación</span><span class="info-value">📍 <?php echo e($animal->ubicacion); ?></span></div><?php endif; ?>
  <?php if($animal->propietario): ?><div class="info-row"><span class="info-label">Propietario</span><span class="info-value">👤 <?php echo e($animal->propietario); ?></span></div><?php endif; ?>
  <?php if($animal->produccion): ?><div class="info-row"><span class="info-label">Produce</span><span class="info-value">🥛 <?php echo e($animal->produccion); ?></span></div><?php endif; ?>
  <?php if($animal->fecha_nacimiento): ?><div class="info-row"><span class="info-label">Nacimiento</span><span class="info-value">🎂 <?php echo e(\Carbon\Carbon::parse($animal->fecha_nacimiento)->format('d/m/Y')); ?> (<?php echo e(\Carbon\Carbon::parse($animal->fecha_nacimiento)->diffForHumans(null,true)); ?>)</span></div><?php endif; ?>
  <?php if($animal->fecha_ingreso): ?><div class="info-row"><span class="info-label">Ingreso a finca</span><span class="info-value"><?php echo e(\Carbon\Carbon::parse($animal->fecha_ingreso)->format('d/m/Y')); ?></span></div><?php endif; ?>
  <?php if($animal->atencion_motivo): ?><div style="margin-top:10px;padding:8px 10px;background:#fef2f2;border-radius:8px;font-size:.83rem;color:#dc2626;">🚨 <?php echo e($animal->atencion_motivo); ?></div><?php endif; ?>
  <?php if($animal->notas): ?><div style="margin-top:10px;padding:8px 10px;background:var(--verde-bg);border-radius:8px;font-size:.83rem;color:var(--verde-dark);">📝 <?php echo e($animal->notas); ?></div><?php endif; ?>

  
  <div class="flex gap-2 mt-3" style="flex-wrap:wrap;">
    <form method="POST" action="<?php echo e(route('animales.favorito',$animal->id)); ?>"><?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-ghost"><?php echo e($animal->favorito?'⭐ Quitar favorito':'⭐ Marcar favorito'); ?></button>
    </form>
    <form method="POST" action="<?php echo e(route('animales.atencion',$animal->id)); ?>"><?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-ghost" style="<?php echo e($animal->atencion_especial?'color:var(--rojo)':''); ?>">
        <?php echo e($animal->atencion_especial?'✅ Sin atención':'🚨 Necesita atención'); ?>

      </button>
    </form>
    <?php if($animal->estado==='activo'): ?>
    <button onclick="openModal('modalSalida')" class="btn btn-sm btn-ghost" style="color:var(--marron);">
      💰 Venta/Sacrificio
    </button>
    <?php endif; ?>

    
    <?php if($tieneProduccion): ?>
    <button onclick="openModal('modalProduccion')" class="btn btn-sm btn-ghost" style="color:var(--verde-dark);">
      🥛 Registrar producción
    </button>
    <a href="<?php echo e(route('produccion-animal.index')); ?>?animal_id=<?php echo e($animal->id); ?>"
       class="btn btn-sm btn-ghost">
      📊 Ver historial
    </a>
    <?php endif; ?>
  </div>
</div>


<div class="section-card">
  <div class="section-title mb-3">💹 Rentabilidad</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:10px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Ingresos</div>
      <div style="font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($totalIngresos,0,',','.')); ?></div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:10px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Gastos</div>
      <div style="font-weight:800;color:var(--rojo);">$<?php echo e(number_format($totalGastos,0,',','.')); ?></div>
    </div>
  </div>
  <div class="flex justify-between" style="font-size:.85rem;">
    <span>Balance</span>
    <strong style="<?php echo e($balance>=0?'color:var(--verde-dark)':'color:var(--rojo)'); ?>"><?php echo e($balance>=0?'+':'-'); ?>$<?php echo e(number_format(abs($balance),0,',','.')); ?></strong>
  </div>
</div>


<?php if($animal->estado === 'vendido' && $animal->valor_venta): ?>
<?php
    $fechaInicio   = $animal->fecha_ingreso ?? $animal->creado_en;
    $fechaFin      = $animal->fecha_venta   ?? now()->toDateString();
    $gastosPeriodo = DB::table('gastos')
        ->where('usuario_id', session('usuario_id'))
        ->where('animal_id', $animal->id)
        ->whereBetween('fecha', [$fechaInicio, $fechaFin])
        ->sum('valor');
    $rentabilidad = $animal->valor_venta - $gastosPeriodo;
?>
<div class="section-card">
    <div class="section-title mb-3">📊 Rentabilidad estimada</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;">
        <div style="background:#f0faf5;border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Valor venta</p>
            <p style="font-size:1.1rem;font-weight:700;color:#1D9E75;">$<?php echo e(number_format($animal->valor_venta,0,',','.')); ?></p>
        </div>
        <div style="background:#fff8f0;border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Gastos</p>
            <p style="font-size:1.1rem;font-weight:700;color:#B45309;">$<?php echo e(number_format($gastosPeriodo,0,',','.')); ?></p>
        </div>
        <div style="background:<?php echo e($rentabilidad>=0?'#f0faf5':'#fef2f2'); ?>;border-radius:10px;padding:12px;text-align:center;">
            <p style="font-size:11px;color:#6B7280;margin-bottom:4px;">Ganancia neta</p>
            <p style="font-size:1.1rem;font-weight:700;color:<?php echo e($rentabilidad>=0?'#1D9E75':'#DC2626'); ?>;">
                <?php echo e($rentabilidad>=0?'+':''); ?>$<?php echo e(number_format($rentabilidad,0,',','.')); ?>

            </p>
        </div>
    </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header"><div class="section-title">📷 Galería (<?php echo e($fotos->count()); ?>)</div></div>
  <div class="foto-grid">
    <?php $__currentLoopData = $fotos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="foto-thumb">
      <img src="<?php echo e(asset($f->ruta)); ?>" onclick="openLightbox('<?php echo e(asset($f->ruta)); ?>')">
      <form method="POST" action="<?php echo e(route('animales.fotos.delete',[$animal->id,$f->id])); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
        <button class="foto-thumb-del">✕</button>
      </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <label class="foto-upload-btn" for="fotoAnimalInput">
      <span style="font-size:1.5rem;">📷</span><span>Agregar foto</span>
    </label>
  </div>
  <form method="POST" action="<?php echo e(route('animales.fotos.upload',$animal->id)); ?>" enctype="multipart/form-data" id="fotoAnimalForm" style="display:none;margin-top:12px;"><?php echo csrf_field(); ?>
    <input type="file" id="fotoAnimalInput" name="foto" accept="image/*" style="display:none;" onchange="previewAnimalFoto(this)">
    <div id="fotoAnimalPreviewWrap" style="display:none;">
      <img id="fotoAnimalPreview" style="width:100%;border-radius:10px;margin-bottom:10px;max-height:200px;object-fit:cover;">
      <div class="form-group"><label>Título (opcional)</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Julio 2026"></div>
      <div class="flex gap-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="cancelFotoA()">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">📷 Subir</button>
      </div>
    </div>
  </form>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">⚖️ Historial de peso (<?php echo e($pesos->count()); ?>)</div>
    <button onclick="openModal('modalPeso')" class="btn btn-sm btn-primary">+ Pesaje</button>
  </div>
  <?php if($pesos->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin pesajes registrados.</p>
  <?php else: ?>
    <?php $__currentLoopData = $pesos->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="peso-row">
      <div><div style="font-weight:600;"><?php echo e($p->peso); ?> <?php echo e($p->unidad); ?></div><?php if($p->notas): ?><div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($p->notas); ?></div><?php endif; ?></div>
      <div style="font-size:.78rem;color:var(--text-muted);"><?php echo e(\Carbon\Carbon::parse($p->fecha)->format('d/m/Y')); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if($pesos->count() > 1): ?>
    <?php $primero=$pesos->last(); $ultimo=$pesos->first(); $ganancia=$ultimo->peso-$primero->peso; ?>
    <div style="margin-top:10px;padding:8px;background:var(--verde-bg);border-radius:8px;font-size:.82rem;color:var(--verde-dark);">
      📈 Ganancia de peso: <?php echo e($ganancia>0?'+':''); ?><?php echo e($ganancia); ?> <?php echo e($ultimo->unidad); ?> desde <?php echo e(\Carbon\Carbon::parse($primero->fecha)->format('d/m/Y')); ?>

    </div>
    <?php endif; ?>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">👥 Propietarios</div>
    <button onclick="openModal('modalPropietario')" class="btn btn-sm btn-ghost">+ Agregar</button>
  </div>
  <?php if($propietarios->isEmpty()): ?>
    <div style="font-size:.85rem;color:var(--text-secondary);padding:8px 0;"><?php echo e($animal->propietario ?? 'Sin propietario asignado.'); ?></div>
  <?php else: ?>
    <?php $__currentLoopData = $propietarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="propietario-row">
      <div><div style="font-weight:600;"><?php echo e($pr->nombre); ?></div><?php if($pr->telefono): ?><div style="font-size:.75rem;"><a href="tel:<?php echo e($pr->telefono); ?>" style="color:var(--verde-dark);">📞 <?php echo e($pr->telefono); ?></a></div><?php endif; ?></div>
      <div class="flex gap-2 items-center">
        <span class="badge badge-green"><?php echo e($pr->porcentaje); ?>%</span>
        <form method="POST" action="<?php echo e(route('animales.propietario.delete',[$animal->id,$pr->id])); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
          <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
        </form>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>


<?php if($gastos->count()): ?>
<div class="section-card">
  <div class="section-header"><div class="section-title">💰 Gastos (<?php echo e($gastos->count()); ?>)</div><a href="<?php echo e(route('gastos.index')); ?>" class="btn btn-sm btn-ghost">Ver todos →</a></div>
  <?php $__currentLoopData = $gastos->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid var(--border);font-size:.86rem;">
    <div><div style="font-weight:600;"><?php echo e($g->descripcion); ?></div><div style="font-size:.74rem;color:var(--text-secondary);"><?php echo e($g->categoria); ?> · <?php echo e(\Carbon\Carbon::parse($g->fecha)->format('d/m/Y')); ?></div></div>
    <div style="font-weight:700;color:var(--rojo);">-$<?php echo e(number_format($g->valor,0,',','.')); ?></div>
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
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:12px 0;">La línea de tiempo se irá llenando con eventos, pesajes y gastos.</p>
  <?php else: ?>
  <div class="timeline">
    <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="tl-item">
      <div class="tl-dot <?php echo e($item['tipo']); ?>"></div>
      <div class="tl-body">
        <div class="tl-title"><?php echo e($item['titulo']); ?></div>
        <?php if($item['descripcion']): ?><div class="tl-desc"><?php echo e($item['descripcion']); ?></div><?php endif; ?>
        <?php if($item['dosis']??null): ?><div class="tl-desc" style="color:#4f46e5;">💊 Dosis: <?php echo e($item['dosis']); ?></div><?php endif; ?>
        <?php if($item['proxima_dosis']??null): ?><div class="tl-desc" style="color:#f59e0b;">📅 Próxima: <?php echo e(\Carbon\Carbon::parse($item['proxima_dosis'])->format('d/m/Y')); ?></div><?php endif; ?>
        <div class="tl-date">📅 <?php echo e(\Carbon\Carbon::parse($item['fecha'])->format('d/m/Y')); ?></div>
        <?php if($item['foto']??null): ?><img class="tl-img" src="<?php echo e(asset($item['foto'])); ?>" onclick="openLightbox('<?php echo e(asset($item['foto'])); ?>')"><?php endif; ?>
        <?php if($item['origen']==='evento'): ?>
        <form method="POST" action="<?php echo e(route('animales.eventos.delete',[$animal->id,$item['id']])); ?>" onsubmit="return confirm('¿Eliminar?')" style="margin-top:6px;"><?php echo csrf_field(); ?>
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




<?php if($tieneProduccion): ?>
<div class="modal-overlay" id="modalProduccion" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">🥛 Registrar producción</h3>
    <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:14px;">
      <?php echo e($em); ?> <?php echo e($animal->nombre_lote ?? $animal->especie); ?>

      <?php if($animal->produccion): ?> · <strong><?php echo e($animal->produccion); ?></strong><?php endif; ?>
    </p>

    <form method="POST" action="<?php echo e(route('produccion-animal.store')); ?>">
      <?php echo csrf_field(); ?>
      
      <input type="hidden" name="animal_id" value="<?php echo e($animal->id); ?>">

      
      <div class="form-group">
        <label style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;">¿Para qué período?</label>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px;" id="chips-m">
          <button type="button" class="chip-p active" onclick="setPm('dia',this)">Hoy</button>
          <button type="button" class="chip-p" onclick="setPm('semana',this)">Esta semana</button>
          <button type="button" class="chip-p" onclick="setPm('mes',this)">Este mes</button>
        </div>
        <input type="hidden" name="periodo" id="m-periodo" value="dia">
      </div>

      
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha</label>
          <input type="date" name="fecha" id="m-fecha" class="form-control"
                 value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Tipo</label>
          <select name="tipo_produccion" id="m-tipo" class="form-control" required onchange="mActUnidad(this)">
            <?php
              $prodLower = strtolower($animal->produccion ?? '');
              $tipoDefault = str_contains($prodLower,'leche') ? 'leche'
                : (str_contains($prodLower,'huevo') ? 'huevos'
                : (str_contains($prodLower,'lana') ? 'lana'
                : (str_contains($prodLower,'miel') ? 'miel' : 'otro')));
            ?>
            <option value="leche"  <?php echo e($tipoDefault==='leche'  ? 'selected':''); ?>>🥛 Leche</option>
            <option value="huevos" <?php echo e($tipoDefault==='huevos' ? 'selected':''); ?>>🥚 Huevos</option>
            <option value="lana"   <?php echo e($tipoDefault==='lana'   ? 'selected':''); ?>>🐑 Lana</option>
            <option value="miel"   <?php echo e($tipoDefault==='miel'   ? 'selected':''); ?>>🍯 Miel</option>
            <option value="otro"   <?php echo e($tipoDefault==='otro'   ? 'selected':''); ?>>📦 Otro</option>
          </select>
        </div>
      </div>

      
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad *</label>
          <input type="number" name="cantidad" class="form-control"
                 placeholder="0" step="0.1" min="0" required>
        </div>
        <div class="form-group">
          <label>Unidad</label>
          <select name="unidad" id="m-unidad" class="form-control">
            <?php if($tipoDefault==='leche'): ?>
              <option>litros</option><option>ml</option>
            <?php elseif($tipoDefault==='huevos'): ?>
              <option>unidades</option><option>docenas</option>
            <?php elseif($tipoDefault==='lana'): ?>
              <option>kg</option><option>lb</option>
            <?php else: ?>
              <option>unidades</option><option>kg</option><option>litros</option>
            <?php endif; ?>
          </select>
        </div>
      </div>

      
      <div class="grid-2">
        <div class="form-group">
          <label>Precio unitario</label>
          <input type="number" name="precio_unitario" class="form-control"
                 placeholder="Opcional" step="100">
        </div>
        <div class="form-group">
          <label>Comprador</label>
          <input type="text" name="comprador" class="form-control" placeholder="Opcional">
        </div>
      </div>

      
      <label style="display:flex;align-items:center;gap:8px;font-size:.85rem;cursor:pointer;margin-bottom:10px;">
        <input type="checkbox" name="vendido" value="1">
        Marcar como vendido (crea ingreso automáticamente)
      </label>

      <div class="form-group">
        <label>Notas</label>
        <input type="text" name="notas" id="m-notas" class="form-control" placeholder="Opcional">
      </div>

      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalProduccion')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>


<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar animal</h3>
    <form method="POST" action="<?php echo e(route('animales.update',$animal->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <input type="hidden" name="back" value="detalle">
      <div class="form-group"><label>Especie *</label>
        <select name="especie" class="form-control" required>
          <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($animal->especie===$e?'selected':''); ?>><?php echo e($e); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" value="<?php echo e($animal->nombre_lote); ?>"></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="<?php echo e($animal->cantidad); ?>"></div>
        <div class="form-group"><label>Estado</label>
          <select name="estado" class="form-control">
            <option <?php echo e($animal->estado==='activo'?'selected':''); ?> value="activo">Activo</option>
            <option <?php echo e($animal->estado==='vendido'?'selected':''); ?> value="vendido">Vendido</option>
            <option <?php echo e($animal->estado==='muerte'?'selected':''); ?> value="muerte">Baja</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" value="<?php echo e($animal->peso_promedio); ?>"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad_peso" class="form-control">
            <option <?php echo e(($animal->unidad_peso??'kg')==='kg'?'selected':''); ?> value="kg">kg</option>
            <option <?php echo e(($animal->unidad_peso??'kg')==='lb'?'selected':''); ?> value="lb">lb</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" value="<?php echo e($animal->ubicacion); ?>" placeholder="Corral, potrero..."></div>
        <div class="form-group"><label>Etapa de vida</label>
          <select name="etapa_vida" class="form-control">
            <option <?php echo e(($animal->etapa_vida??'adulto')==='cria'?'selected':''); ?> value="cria">🐣 Cría</option>
            <option <?php echo e(($animal->etapa_vida??'adulto')==='juvenil'?'selected':''); ?> value="juvenil">🐥 Juvenil</option>
            <option <?php echo e(($animal->etapa_vida??'adulto')==='adulto'?'selected':''); ?> value="adulto">✅ Adulto</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" value="<?php echo e($animal->propietario); ?>"></div>
      <div class="form-group"><label>Produce</label><input type="text" name="produccion" class="form-control" value="<?php echo e($animal->produccion); ?>" placeholder="Leche, Huevos, Lana, Cría..."></div>
      <div class="grid-2">
        <div class="form-group"><label>Precio/kg (COP)</label><input type="number" step="100" name="precio_kilo" class="form-control" value="<?php echo e($animal->precio_kilo); ?>"></div>
        <div class="form-group"><label>Precio/cabeza (COP)</label><input type="number" step="100" name="precio_unidad" class="form-control" value="<?php echo e($animal->precio_unidad); ?>"></div>
      </div>
      <div class="form-group"><label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="vende_por_kilo" <?php echo e(($animal->vende_por_kilo??1)?'checked':''); ?>> Se vende por kg</label></div>
      <div class="form-group"><label>Motivo atención especial</label><input type="text" name="atencion_motivo" class="form-control" value="<?php echo e($animal->atencion_motivo); ?>" placeholder="Ej: Lesión en pata derecha"></div>
      <div class="form-group"><label>Foto principal</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2"><?php echo e($animal->notas); ?></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalPeso" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">⚖️ Registrar pesaje</h3>
    <form method="POST" action="<?php echo e(route('animales.pesos.store',$animal->id)); ?>"><?php echo csrf_field(); ?>
      <div class="grid-2">
        <div class="form-group"><label>Peso *</label><input type="number" step="0.1" name="peso" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad" class="form-control">
            <option value="kg" <?php echo e(($animal->unidad_peso??'kg')==='kg'?'selected':''); ?>>kg</option>
            <option value="lb" <?php echo e(($animal->unidad_peso??'kg')==='lb'?'selected':''); ?>>lb</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required></div>
      <div class="form-group"><label>Notas</label><input type="text" name="notas" class="form-control" placeholder="Observaciones..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPeso')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar pesaje</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalEvento" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">📝 Registrar evento</h3>
    <form method="POST" action="<?php echo e(route('animales.eventos.store',$animal->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required id="tipoEvento" onchange="toggleDosis()">
          <option value="nota">📝 Nota</option>
          <option value="medicamento">💊 Medicamento</option>
          <option value="vacuna">💉 Vacuna</option>
          <option value="traslado">🏃 Traslado de lote</option>
          <option value="marcado">🏷️ Marcado / herrado</option>
          <option value="nacimiento">🐣 Nacimiento / parto</option>
          <option value="destete">🍼 Destete</option>
          <option value="castracion">✂️ Castración</option>
          <option value="enfermedad">🤒 Enfermedad</option>
          <option value="recuperacion">💪 Recuperación</option>
          <option value="otro">🔧 Otro</option>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="titulo" class="form-control" required placeholder="Ej: Ivermectina 1% · Dosis: 5ml"></div>
      <div id="dosisWrap" style="display:none;">
        <div class="grid-2">
          <div class="form-group"><label>Dosis / cantidad</label><input type="text" name="dosis" class="form-control" placeholder="Ej: 5ml, 1 pastilla"></div>
          <div class="form-group"><label>Próxima dosis</label><input type="date" name="proxima_dosis" class="form-control"></div>
        </div>
      </div>
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
      <div class="form-group"><label>Foto del evento</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEvento')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar evento</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalPropietario" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">👥 Agregar propietario</h3>
    <form method="POST" action="<?php echo e(route('animales.propietario.store',$animal->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required placeholder="Nombre del dueño"></div>
      <div class="grid-2">
        <div class="form-group"><label>% participación</label><input type="number" step="0.1" name="porcentaje" class="form-control" value="50" min="1" max="100"></div>
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
      </div>
      <div class="form-group"><label>Notas</label><input type="text" name="notas" class="form-control" placeholder="Condiciones, acuerdos..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPropietario')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Agregar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalSalida" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar salida</h3>
    <form method="POST" action="<?php echo e(route('animales.salida',$animal->id)); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo de salida *</label>
        <select name="tipo_salida" class="form-control" required id="tipoSalida" onchange="toggleVentaFields()">
          <option value="venta">💰 Venta</option>
          <option value="sacrificio">🔪 Sacrificio</option>
        </select>
      </div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>" required></div>
      <div id="ventaFields">
        <div class="form-group"><label>Valor de venta (COP)</label>
          <input type="number" step="100" name="valor_venta" class="form-control"
            placeholder="<?php echo e($valorVentaEst ? 'Estimado: $'.number_format($valorVentaEst,0,',','.') : 'Se calculará automáticamente'); ?>"
            value="<?php echo e($valorVentaEst); ?>">
        </div>
        <div class="form-group"><label>Comprador</label><input type="text" name="comprador" class="form-control" placeholder="Nombre del comprador"></div>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas_salida" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div style="background:#eff6ff;border-radius:8px;padding:10px;font-size:.82rem;color:#1d4ed8;margin-bottom:12px;">
        💡 Si es una <strong>venta</strong>, se creará automáticamente un ingreso en el módulo de Ingresos.
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalSalida')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Confirmar salida</button>
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
function previewAnimalFoto(input) {
  if (input.files && input.files[0]) {
    const r = new FileReader();
    r.onload = e => {
      document.getElementById('fotoAnimalPreview').src = e.target.result;
      document.getElementById('fotoAnimalPreviewWrap').style.display = 'block';
      document.getElementById('fotoAnimalForm').style.display = 'block';
    };
    r.readAsDataURL(input.files[0]);
  }
}
function cancelFotoA() {
  document.getElementById('fotoAnimalInput').value = '';
  document.getElementById('fotoAnimalPreviewWrap').style.display = 'none';
  document.getElementById('fotoAnimalForm').style.display = 'none';
}
function toggleDosis() {
  const tipo = document.getElementById('tipoEvento').value;
  document.getElementById('dosisWrap').style.display = ['medicamento','vacuna'].includes(tipo) ? 'block' : 'none';
}
function toggleVentaFields() {
  const tipo = document.getElementById('tipoSalida').value;
  document.getElementById('ventaFields').style.display = tipo === 'venta' ? 'block' : 'none';
}

// ── Funciones para modal de producción ─────────────────────────────
const mesesEs = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

function setPm(periodo, btn) {
  document.querySelectorAll('.chip-p').forEach(c => c.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('m-periodo').value = periodo;
  const hoy  = new Date();
  const yyyy = hoy.getFullYear();
  const mm   = String(hoy.getMonth()+1).padStart(2,'0');
  const dd   = String(hoy.getDate()).padStart(2,'0');
  const notas = document.getElementById('m-notas');

  if (periodo === 'dia') {
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    if (notas.value.startsWith('Producción semana') || notas.value.startsWith('Producción mes')) notas.value = '';
  } else if (periodo === 'semana') {
    const lunes = new Date(hoy);
    lunes.setDate(hoy.getDate() - (hoy.getDay() === 0 ? 6 : hoy.getDay()-1));
    const dl = String(lunes.getDate()).padStart(2,'0');
    const ml = String(lunes.getMonth()+1).padStart(2,'0');
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    notas.value = `Producción semana del ${dl}/${ml} al ${dd}/${mm}`;
  } else if (periodo === 'mes') {
    document.getElementById('m-fecha').value = `${yyyy}-${mm}-${dd}`;
    notas.value = `Producción mes de ${mesesEs[hoy.getMonth()]} ${yyyy}`;
  }
}

function mActUnidad(sel) {
  const u = document.getElementById('m-unidad');
  const mapa = { leche:['litros','ml'], huevos:['unidades','docenas'], lana:['kg','lb'], miel:['kg','litros'], otro:['unidades','kg','litros'] };
  const ops = mapa[sel.value] || ['unidades'];
  u.innerHTML = ops.map(o => `<option>${o}</option>`).join('');
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/animal-detalle.blade.php ENDPATH**/ ?>