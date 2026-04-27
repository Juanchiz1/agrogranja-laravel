<?php $__env->startSection('title','Gastos'); ?>
<?php $__env->startSection('page_title','💰 Gastos'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<style>
.tabs-gastos { display:flex; gap:6px; margin-bottom:16px; overflow-x:auto; padding-bottom:4px; }
.tab-btn { flex-shrink:0; padding:7px 14px; border-radius:99px; border:1.5px solid var(--border);
  background:var(--surface); font-size:.82rem; font-weight:600; cursor:pointer; color:var(--text-secondary); }
.tab-btn.active { background:var(--marron); color:#fff; border-color:var(--marron); }
.tab-panel { display:none; }
.tab-panel.active { display:block; }

.recurrente-card {
  background:var(--surface); border-radius:var(--radius-lg); padding:12px 14px;
  margin-bottom:10px; box-shadow:var(--shadow-sm);
  border-left:4px solid var(--marron-light);
  display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.proveedor-card {
  background:var(--surface); border-radius:var(--radius-lg); padding:12px 14px;
  margin-bottom:10px; box-shadow:var(--shadow-sm);
  display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.vencimiento-badge {
  font-size:.72rem; padding:2px 8px; border-radius:99px; font-weight:700;
}
.vence-hoy    { background:#fef9c3; color:#854d0e; }
.vence-pronto { background:#ffedd5; color:#9a3412; }
.vence-ok     { background:var(--verde-bg); color:var(--verde-dark); }
.asociado-chip {
  display:inline-flex; align-items:center; gap:4px;
  font-size:.72rem; padding:2px 8px; border-radius:99px;
  background:var(--verde-bg); color:var(--verde-dark); font-weight:600;
}
.asociado-chip.animal  { background:#fdf4ff; color:#7e22ce; }
.asociado-chip.cosecha { background:#fff7ed; color:#9a3412; }
.foto-factura-thumb { width:44px; height:44px; border-radius:8px; object-fit:cover; cursor:pointer; border:1.5px solid var(--border); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaGastos')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<div class="card mb-3" style="background:var(--marron-bg)">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Gasto mensual</p>
      <p style="font-size:1.7rem;font-weight:800;color:var(--marron);">$<?php echo e(number_format($totalMes,0,',','.')); ?></p>
    </div>
    <div style="text-align:right;">
      <p class="text-xs text-gray">Este año</p>
      <p class="font-bold text-brown">$<?php echo e(number_format($totalAnio,0,',','.')); ?></p>
    </div>
  </div>
</div>

<?php if($statsCat->count()): ?>
<p class="section-title">Top categorías este mes</p>
<?php $__currentLoopData = $statsCat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-2">
  <div class="flex items-center justify-between mb-1"><span class="text-sm font-bold"><?php echo e($sc->categoria); ?></span><span class="text-sm font-bold text-brown">$<?php echo e(number_format($sc->total,0,',','.')); ?></span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:<?php echo e($totalMes>0?min(100,round($sc->total/$totalMes*100)):0); ?>%;background:var(--marron-light);"></div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php
  $proximos = $recurrentes->filter(fn($r) => $r->proximo_vencimiento && \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false) >= -3)->take(3);
?>
<?php if($proximos->count()): ?>
<div style="background:#fffbeb;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;border-left:4px solid #f59e0b;">
  <p style="font-size:.82rem;font-weight:700;color:#92400e;margin-bottom:8px;">⏰ Gastos recurrentes próximos</p>
  <?php $__currentLoopData = $proximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $dias = \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false); ?>
  <div class="flex items-center justify-between" style="margin-bottom:6px;">
    <span style="font-size:.83rem;"><?php echo e($r->descripcion); ?></span>
    <div class="flex gap-2 items-center">
      <span style="font-weight:700;font-size:.83rem;color:var(--marron);">$<?php echo e(number_format($r->valor,0,',','.')); ?></span>
      <form method="POST" action="<?php echo e(route('gastos.recurrente.generar',$r->id)); ?>"><?php echo csrf_field(); ?>
        <button class="btn btn-sm btn-primary" style="font-size:.72rem;padding:3px 10px;">Registrar</button>
      </form>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="tabs-gastos">
  <button class="tab-btn active" onclick="switchTab('gastos')">📋 Gastos</button>
  <button class="tab-btn" onclick="switchTab('recurrentes')">🔄 Recurrentes <?php if($recurrentes->count()): ?><span style="background:var(--marron);color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:4px;"><?php echo e($recurrentes->count()); ?></span><?php endif; ?></button>
  <button class="tab-btn" onclick="switchTab('proveedores')">🏪 Proveedores <?php if($proveedores->count()): ?><span style="background:var(--verde);color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:4px;"><?php echo e($proveedores->count()); ?></span><?php endif; ?></button>
</div>


<div class="tab-panel active" id="tab-gastos">
  <form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
    <div class="search-box" style="flex:1;min-width:100px;"><span class="search-icon">🔍</span>
      <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
    </div>
    <input type="month" name="mes" class="form-control" style="width:130px;" value="<?php echo e(request('mes')); ?>" onchange="this.form.submit()">
    <select name="cat" class="form-control" style="width:130px;" onchange="this.form.submit()">
      <option value="">Categoría</option>
      <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <optgroup label="<?php echo e($grupo); ?>">
          <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e(request('cat')===$cat?'selected':''); ?> value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </optgroup>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="asociado" class="form-control" style="width:120px;" onchange="this.form.submit()">
      <option value="">Todos</option>
      <option <?php echo e(request('asociado')==='cultivo'?'selected':''); ?> value="cultivo">🌱 Cultivos</option>
      <option <?php echo e(request('asociado')==='animal'?'selected':''); ?> value="animal">🐄 Animales</option>
      <option <?php echo e(request('asociado')==='cosecha'?'selected':''); ?> value="cosecha">🌾 Cosechas</option>
      <option <?php echo e(request('asociado')==='ninguno'?'selected':''); ?> value="ninguno">Sin asociar</option>
    </select>
    <button type="submit" class="btn btn-secondary">🔍</button>
  </form>

  <?php if($gastos->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">💰</div><p>No hay gastos registrados.</p></div>
  <?php else: ?>
  <?php $__currentLoopData = $gastos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $icons=['Semillas'=>'🌰','Fertilizantes'=>'🌿','Abonos orgánicos'=>'♻️','Plaguicidas'=>'🧴','Herbicidas'=>'🌾','Fungicidas'=>'🍄','Insecticidas'=>'🐛','Riego'=>'💧','Herramientas'=>'🔧','Combustible'=>'⛽','Mano de obra'=>'👷','Jornales'=>'👷','Transporte'=>'🚛','Fletes'=>'🚛','Alimento animal'=>'🌾','Veterinario'=>'💉','Medicamentos animales'=>'💊','Vacunas'=>'💉','Mantenimiento'=>'🏗️','Maquinaria'=>'🚜','Arriendo de tierra'=>'🏡','Servicios públicos'=>'💡','Seguros'=>'🛡️','Impuestos'=>'🏛️','Otros'=>'📦'];
    $ic = $icons[$g->categoria] ?? '📦';
  ?>
  <div class="list-item">
    <div class="item-icon" style="background:var(--marron-bg)"><?php echo e($ic); ?></div>
    <div class="item-body">
      <div class="item-title"><?php echo e($g->descripcion); ?></div>
      <div class="item-sub" style="display:flex;flex-wrap:wrap;gap:4px;margin-top:3px;">
        <span><?php echo e($g->categoria); ?></span>
        <?php if($g->cultivo_nombre): ?> <span class="asociado-chip">🌱 <?php echo e($g->cultivo_nombre); ?></span><?php endif; ?>
        <?php if($g->animal_nombre): ?>  <span class="asociado-chip animal">🐄 <?php echo e($g->animal_especie); ?> · <?php echo e($g->animal_nombre); ?></span><?php endif; ?>
        <?php if($g->cosecha_nombre): ?> <span class="asociado-chip cosecha">🌾 <?php echo e($g->cosecha_nombre); ?></span><?php endif; ?>
        <?php if($g->proveedor_nombre_bd ?? $g->proveedor): ?> <span style="font-size:.72rem;color:var(--text-muted);">🏪 <?php echo e($g->proveedor_nombre_bd ?? $g->proveedor); ?></span><?php endif; ?>
        <?php if($g->es_recurrente ?? false): ?> <span style="font-size:.7rem;background:#fff7ed;color:#9a3412;padding:1px 6px;border-radius:99px;">🔄 recurrente</span><?php endif; ?>
      </div>
    </div>
    <div class="flex gap-1 items-center">
      <?php if(isset($g->foto_factura) && $g->foto_factura): ?>
        <img src="<?php echo e(asset($g->foto_factura)); ?>" class="foto-factura-thumb" onclick="openLightbox('<?php echo e(asset($g->foto_factura)); ?>')" title="Ver factura">
      <?php endif; ?>
      <div style="text-align:right;">
        <div class="font-bold text-brown text-sm">$<?php echo e(number_format($g->valor,0,',','.')); ?></div>
        <div style="font-size:.7rem;color:var(--text-muted);"><?php echo e(\Carbon\Carbon::parse($g->fecha)->format('d/m/Y')); ?></div>
      </div>
      <button onclick="openModal('editGasto<?php echo e($g->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="<?php echo e(route('gastos.destroy',$g->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>

  
  <div class="modal-overlay" id="editGasto<?php echo e($g->id); ?>" style="display:none;">
    <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar gasto</h3>
      <form method="POST" action="<?php echo e(route('gastos.update',$g->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
        <div class="form-group"><label>Categoría *</label>
          <select name="categoria" class="form-control" required>
            <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <optgroup label="<?php echo e($grupo); ?>">
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($g->categoria===$cat?'selected':''); ?> value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </optgroup>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="<?php echo e($g->descripcion); ?>"></div>
        <div class="grid-2">
          <div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" value="<?php echo e($g->cantidad); ?>"></div>
          <div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" value="<?php echo e($g->unidad_cantidad); ?>" placeholder="kg, bultos..."></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required value="<?php echo e($g->valor); ?>"></div>
          <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($g->fecha); ?>"></div>
        </div>
        <div class="form-group"><label>N° Factura</label><input type="text" name="factura_numero" class="form-control" value="<?php echo e($g->factura_numero); ?>" placeholder="Opcional"></div>

        
        <div class="form-group"><label>Proveedor</label>
          <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_e<?php echo e($g->id); ?>')">
            <option value="">-- Escribir manualmente --</option>
            <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pv->id); ?>" <?php echo e($g->persona_id==$pv->id?'selected':''); ?>><?php echo e($pv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <input type="text" name="proveedor" id="prov_text_e<?php echo e($g->id); ?>" class="form-control mt-1" placeholder="O escribe el nombre" value="<?php echo e($g->proveedor); ?>">
        </div>

        
        <div class="form-group"><label>Asociar a cultivo</label>
          <select name="cultivo_id" class="form-control">
            <option value="">Sin asociar</option>
            <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>" <?php echo e($g->cultivo_id==$cv->id?'selected':''); ?>><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Asociar a animal</label>
          <select name="animal_id" class="form-control">
            <option value="">Sin asociar</option>
            <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($an->id); ?>" <?php echo e(($g->animal_id ?? null)==$an->id?'selected':''); ?>><?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Asociar a cosecha</label>
          <select name="cosecha_id" class="form-control">
            <option value="">Sin asociar</option>
            <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($co->id); ?>" <?php echo e(($g->cosecha_id ?? null)==$co->id?'selected':''); ?>><?php echo e($co->producto); ?> · <?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>

        <div class="form-group"><label>Foto/Imagen de factura</label>
          <?php if($g->foto_factura): ?><img src="<?php echo e(asset($g->foto_factura)); ?>" style="width:80px;border-radius:8px;margin-bottom:6px;display:block;"><?php endif; ?>
          <input type="file" name="foto_factura" class="form-control" accept="image/*">
        </div>
        <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2"><?php echo e($g->notas ?? ''); ?></textarea></div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editGasto<?php echo e($g->id); ?>')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>


<div class="tab-panel" id="tab-recurrentes">
  <?php if($recurrentes->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">🔄</div><p>No tienes gastos recurrentes.</p><p style="font-size:.85rem;">Son gastos que se repiten automáticamente cada semana, mes, etc.</p></div>
  <?php else: ?>
  <?php $__currentLoopData = $recurrentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $diasR = $r->proximo_vencimiento ? \Carbon\Carbon::parse($r->proximo_vencimiento)->diffInDays(now(), false) : null;
    $vcClass = 'vence-ok';
    if ($diasR !== null) {
      if ($diasR >= 0) $vcClass = 'vence-hoy';
      elseif ($diasR >= -3) $vcClass = 'vence-pronto';
    }
  ?>
  <div class="recurrente-card">
    <div style="flex:1;">
      <div style="font-weight:700;font-size:.9rem;"><?php echo e($r->descripcion); ?></div>
      <div style="font-size:.78rem;color:var(--text-secondary);"><?php echo e($r->categoria); ?> · <?php echo e(ucfirst($r->frecuencia)); ?></div>
      <?php if($r->proveedor): ?><div style="font-size:.75rem;color:var(--text-muted);">🏪 <?php echo e($r->proveedor); ?></div><?php endif; ?>
    </div>
    <div style="text-align:right;display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
      <span style="font-weight:800;color:var(--marron);">$<?php echo e(number_format($r->valor,0,',','.')); ?></span>
      <?php if($r->proximo_vencimiento): ?>
        <span class="vencimiento-badge <?php echo e($vcClass); ?>">
          <?php if($diasR >= 0): ?> Vence hoy
          <?php elseif($diasR >= -3): ?> Vence en <?php echo e(abs($diasR)); ?> días
          <?php else: ?> Próx. <?php echo e(\Carbon\Carbon::parse($r->proximo_vencimiento)->format('d/m')); ?>

          <?php endif; ?>
        </span>
      <?php endif; ?>
      <div class="flex gap-1">
        <form method="POST" action="<?php echo e(route('gastos.recurrente.generar',$r->id)); ?>"><?php echo csrf_field(); ?>
          <button class="btn btn-sm btn-primary" style="font-size:.75rem;">✅ Registrar</button>
        </form>
        <form method="POST" action="<?php echo e(route('gastos.recurrente.destroy',$r->id)); ?>" onsubmit="return confirm('¿Desactivar?')"><?php echo csrf_field(); ?>
          <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
        </form>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>

  <button class="btn btn-primary btn-full mt-2" onclick="openModal('modalNuevoRecurrente')">+ Nuevo gasto recurrente</button>
</div>


<div class="tab-panel" id="tab-proveedores">
  <?php if($proveedores->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">🏪</div><p>Sin proveedores guardados.</p><p style="font-size:.85rem;">Guarda tus proveedores frecuentes para seleccionarlos rápido al registrar gastos.</p></div>
  <?php else: ?>
  <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="proveedor-card">
    <div style="flex:1;">
      <div style="font-weight:700;"><?php echo e($pv->nombre); ?></div>
      <div style="font-size:.78rem;color:var(--text-secondary);"><?php echo e($pv->categoria ?? 'Sin categoría'); ?></div>
      <?php if($pv->telefono): ?><div style="font-size:.75rem;"><a href="tel:<?php echo e($pv->telefono); ?>" style="color:var(--verde-dark);">📞 <?php echo e($pv->telefono); ?></a></div><?php endif; ?>
      <?php if($pv->email): ?><div style="font-size:.75rem;color:var(--text-muted);">📧 <?php echo e($pv->email); ?></div><?php endif; ?>
    </div>
    <form method="POST" action="<?php echo e(route('gastos.proveedor.destroy',$pv->id)); ?>" onsubmit="return confirm('¿Eliminar proveedor?')"><?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>

  <button class="btn btn-primary btn-full mt-2" onclick="openModal('modalNuevoProveedor')">+ Nuevo proveedor</button>
</div>




<div class="modal-overlay" id="modalNuevoGasto" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💰 Registrar gasto</h3>
    <form method="POST" action="<?php echo e(route('gastos.store')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Categoría *</label>
        <select name="categoria" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <optgroup label="<?php echo e($grupo); ?>">
              <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Bulto de urea 46%" required></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" step="0.1" name="cantidad" class="form-control" placeholder="0"></div>
        <div class="form-group"><label>Unidad</label><input type="text" name="unidad_cantidad" class="form-control" placeholder="kg, bultos..."></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div>
      </div>
      <div class="form-group"><label>N° Factura</label><input type="text" name="factura_numero" class="form-control" placeholder="Opcional"></div>

      
      <div class="form-group"><label>Proveedor</label>
        <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_nuevo')">
          <option value="">-- Seleccionar guardado --</option>
          <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pv->id); ?>"><?php echo e($pv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="text" name="proveedor" id="prov_text_nuevo" class="form-control mt-1" placeholder="O escribe el nombre">
      </div>

      
      <div class="form-group"><label>Asociar a cultivo</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Sin asociar</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>">🌱 <?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Asociar a animal</label>
        <select name="animal_id" class="form-control">
          <option value="">Sin asociar</option>
          <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($an->id); ?>">🐄 <?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Asociar a cosecha</label>
        <select name="cosecha_id" class="form-control">
          <option value="">Sin asociar</option>
          <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($co->id); ?>">🌾 <?php echo e($co->producto); ?> · <?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div class="form-group"><label>📷 Foto de factura (opcional)</label><input type="file" name="foto_factura" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>

      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoGasto')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalNuevoProveedor" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🏪 Nuevo proveedor</h3>
    <form method="POST" action="<?php echo e(route('gastos.proveedor.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required placeholder="Ej: Agroquímicos del Valle"></div>
      <div class="form-group"><label>Qué vende / servicio</label><input type="text" name="categoria" class="form-control" placeholder="Ej: Fertilizantes y semillas"></div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Opcional"></div>
      </div>
      <div class="form-group"><label>Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Opcional"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Condiciones de pago, observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoProveedor')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar proveedor</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalNuevoRecurrente" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🔄 Gasto recurrente</h3>
    <p style="font-size:.83rem;color:var(--text-secondary);margin-bottom:14px;">Un gasto recurrente es una plantilla que te recuerda registrar pagos que se repiten (arriendo, servicios, contratos, etc.).</p>
    <form method="POST" action="<?php echo e(route('gastos.recurrente.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Categoría *</label>
        <select name="categoria" class="form-control" required>
          <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <optgroup label="<?php echo e($grupo); ?>">
              <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($cat); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </optgroup>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required placeholder="Ej: Arriendo del lote norte"></div>
      <div class="grid-2">
        <div class="form-group"><label>Valor (COP) *</label><input type="number" step="100" name="valor" class="form-control" required placeholder="0"></div>
        <div class="form-group"><label>Frecuencia *</label>
          <select name="frecuencia" class="form-control" required>
            <option value="semanal">Semanal</option>
            <option value="quincenal">Quincenal</option>
            <option value="mensual" selected>Mensual</option>
            <option value="bimestral">Bimestral</option>
            <option value="trimestral">Trimestral</option>
            <option value="anual">Anual</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Día del mes en que vence</label><input type="number" name="dia_del_mes" class="form-control" min="1" max="28" value="1" placeholder="Ej: 5 = cada día 5 del mes"></div>
      <div class="form-group"><label>Proveedor</label>
        <select name="persona_id" class="form-control" onchange="syncProvText(this,'prov_text_rec')">
          <option value="">-- Seleccionar --</option>
          <?php $__currentLoopData = $proveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($pv->id); ?>"><?php echo e($pv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        <input type="text" name="proveedor" id="prov_text_rec" class="form-control mt-1" placeholder="O escribe el nombre">
      </div>
      <div class="form-group"><label>Asociar a cultivo (opcional)</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Sin asociar</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoRecurrente')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Crear recurrente</button>
      </div>
    </form>
  </div>
</div>


<div id="lightboxG" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxGImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>


<div class="modal-overlay" id="modalAyudaGastos" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">💰 ¿Cómo funciona Gastos?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí registras <strong style="color:var(--marron)">todo lo que gastas en tu finca</strong>. Puedes asociar cada gasto a un cultivo, animal o cosecha para saber exactamente cuánto te cuesta cada uno.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📋</span>
          <div><strong>Gastos</strong><br>Registra cualquier egreso: insumos, jornales, combustible, veterinario, arriendo y más. Puedes adjuntar la foto de la factura.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🔄</span>
          <div><strong>Recurrentes</strong><br>Crea plantillas para gastos que se repiten (arriendo, servicios, contratos). Te avisa cuando se acerca el vencimiento y los registra en un clic.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🏪</span>
          <div><strong>Proveedores</strong><br>Guarda tus proveedores frecuentes con teléfono y email para seleccionarlos rápidamente al registrar un gasto.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🌱🐄🌾</span>
          <div><strong>Asociaciones</strong><br>Cada gasto puede vincularse a un cultivo, un animal o una cosecha. Así sabes el costo real de cada uno en su ficha de rentabilidad.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📷</span>
          <div><strong>Foto de factura</strong><br>Toma una foto de la factura física y adjúntala al gasto para tener todo documentado.</div>
        </div>
      </div>
      <p style="margin-top:16px;padding:10px;background:var(--marron-bg);border-radius:8px;color:var(--marron);font-size:.85rem;">
        💡 <strong>Tip:</strong> Usa los tabs de arriba para navegar entre gastos, recurrentes y proveedores.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaGastos')">¡Entendido!</button>
  </div>
</div>

<button class="fab" style="background:var(--marron);" onclick="openModal('modalNuevoGasto')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
function switchTab(name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-'+name).classList.add('active');
  event.currentTarget.classList.add('active');
}
function syncProvText(sel, textId) {
  const opt = sel.options[sel.selectedIndex];
  if (sel.value) document.getElementById(textId).value = opt.text;
  else document.getElementById(textId).value = '';
}
function openLightbox(src) {
  const lb = document.getElementById('lightboxG');
  document.getElementById('lightboxGImg').src = src;
  lb.style.display = 'flex';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/gastos.blade.php ENDPATH**/ ?>