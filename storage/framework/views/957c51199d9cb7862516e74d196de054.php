<?php $__env->startSection('title','Ingresos'); ?>
<?php $__env->startSection('page_title','📈 Ingresos'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<style>
.tabs-ingresos { display:flex; gap:6px; margin-bottom:16px; overflow-x:auto; padding-bottom:4px; }
.tab-btn-i { flex-shrink:0; padding:7px 14px; border-radius:99px; border:1.5px solid var(--border);
  background:var(--surface); font-size:.82rem; font-weight:600; cursor:pointer; color:var(--text-secondary); }
.tab-btn-i.active { background:var(--verde-dark); color:#fff; border-color:var(--verde-dark); }
.tab-panel-i { display:none; }
.tab-panel-i.active { display:block; }
.tipo-chip {
  display:inline-flex; align-items:center; gap:3px;
  font-size:.72rem; padding:2px 8px; border-radius:99px; font-weight:600;
  background:var(--verde-bg); color:var(--verde-dark);
}
.tipo-chip.animal   { background:#fdf4ff; color:#7e22ce; }
.tipo-chip.cosecha  { background:#fff7ed; color:#9a3412; }
.tipo-chip.subsidio { background:#eff6ff; color:#1d4ed8; }
.tipo-chip.servicio { background:#f0fdf4; color:#166534; }
.tipo-chip.otro     { background:#f9fafb; color:#6b7280; }
.cliente-card {
  background:var(--surface); border-radius:var(--radius-lg); padding:12px 14px;
  margin-bottom:10px; box-shadow:var(--shadow-sm);
  display:flex; align-items:center; justify-content:space-between; gap:10px;
}
.ingreso-foto { width:44px; height:44px; border-radius:8px; object-fit:cover;
  cursor:pointer; border:1.5px solid var(--border); flex-shrink:0; }
.stat-tipo-row { display:flex; align-items:center; justify-content:space-between; padding:6px 0;
  border-bottom:1px solid var(--border); font-size:.85rem; }
.stat-tipo-row:last-child { border:none; }
.top-comprador-row { display:flex; align-items:center; gap:10px; padding:8px 0;
  border-bottom:1px solid var(--border); }
.top-comprador-row:last-child { border:none; }
.comprador-avatar { width:34px; height:34px; border-radius:50%; background:var(--verde-bg);
  display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaIngresos')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<div class="stats-grid" style="grid-template-columns:1fr 1fr;margin-bottom:16px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1.2rem;">$<?php echo e(number_format($totalMes,0,',','.')); ?></div>
    <div class="stat-label">Este mes</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1.2rem;">$<?php echo e(number_format($totalAnio,0,',','.')); ?></div>
    <div class="stat-label">Este año</div>
  </div>
</div>


<?php if($statsTipo->count()): ?>
<div class="section-card" style="background:var(--surface);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
  <p style="font-size:.8rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">Ingresos por tipo — este mes</p>
  <?php $__currentLoopData = $statsTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $tipoInfo = $tipos[$st->tipo] ?? ['label'=>ucfirst($st->tipo),'emoji'=>'📦']; ?>
  <div class="stat-tipo-row">
    <span><?php echo e($tipoInfo['emoji']); ?> <?php echo e($tipoInfo['label']); ?>

      <span style="font-size:.72rem;color:var(--text-muted);">(<?php echo e($st->cnt); ?>)</span>
    </span>
    <strong style="color:var(--verde-dark);">$<?php echo e(number_format($st->total,0,',','.')); ?></strong>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="tabs-ingresos">
  <button class="tab-btn-i active" onclick="switchTabI('ingresos',this)">📋 Ingresos</button>
  <button class="tab-btn-i" onclick="switchTabI('clientes',this)">👥 Clientes
    <?php if($clientes->count()): ?><span style="background:var(--verde-dark);color:#fff;border-radius:99px;padding:1px 6px;font-size:.7rem;margin-left:4px;"><?php echo e($clientes->count()); ?></span><?php endif; ?>
  </button>
  <button class="tab-btn-i" onclick="switchTabI('estadisticas',this)">📊 Estadísticas</button>
</div>


<div class="tab-panel-i active" id="tab-i-ingresos">
  <form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
    <div class="search-box" style="flex:1;min-width:100px;">
      <span class="search-icon">🔍</span>
      <input type="text" name="q" class="form-control" placeholder="Buscar..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
    </div>
    <input type="month" name="mes" class="form-control" style="width:130px;" value="<?php echo e(request('mes')); ?>" onchange="this.form.submit()">
    <select name="tipo" class="form-control" style="width:130px;" onchange="this.form.submit()">
      <option value="">Tipo</option>
      <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php echo e(request('tipo')===$key?'selected':''); ?>><?php echo e($t['emoji']); ?> <?php echo e($t['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </select>
    <select name="asociado" class="form-control" style="width:120px;" onchange="this.form.submit()">
      <option value="">Todos</option>
      <option <?php echo e(request('asociado')==='cultivo'?'selected':''); ?> value="cultivo">🌱 Cultivos</option>
      <option <?php echo e(request('asociado')==='animal'?'selected':''); ?> value="animal">🐄 Animales</option>
      <option <?php echo e(request('asociado')==='cosecha'?'selected':''); ?> value="cosecha">🌾 Cosechas</option>
    </select>
    <button type="submit" class="btn btn-secondary">🔍</button>
  </form>

  <?php if($ingresos->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">📈</div><p>No hay ingresos registrados.</p></div>
  <?php else: ?>
  <?php $__currentLoopData = $ingresos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $tipoInfo = $tipos[$i->tipo ?? 'venta'] ?? ['label'=>'Venta','emoji'=>'💰'];
    $tipoClass = in_array($i->tipo,['animal','cosecha_propia','subsidio','servicio','otro']) ? $i->tipo : '';
  ?>
  <div class="list-item">
    <div class="item-icon" style="background:var(--verde-bg);font-size:1.2rem;"><?php echo e($tipoInfo['emoji']); ?></div>
    <div class="item-body">
      <div class="item-title"><?php echo e($i->descripcion); ?></div>
      <div class="item-sub" style="display:flex;flex-wrap:wrap;gap:4px;margin-top:3px;">
        <span class="tipo-chip <?php echo e($tipoClass); ?>"><?php echo e($tipoInfo['label']); ?></span>
        <?php if($i->cultivo_nombre): ?> <span class="tipo-chip">🌱 <?php echo e($i->cultivo_nombre); ?></span><?php endif; ?>
        <?php if($i->animal_nombre): ?>  <span class="tipo-chip animal">🐄 <?php echo e($i->animal_especie); ?> - <?php echo e($i->animal_nombre); ?></span><?php endif; ?>
        <?php if($i->cosecha_nombre): ?> <span class="tipo-chip cosecha">🌾 <?php echo e($i->cosecha_nombre); ?></span><?php endif; ?>
        <?php if($i->comprador): ?>      <span style="font-size:.72rem;color:var(--text-muted);">👤 <?php echo e($i->comprador); ?></span><?php endif; ?>
      </div>
      <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;"><?php echo e(\Carbon\Carbon::parse($i->fecha)->format('d/m/Y')); ?><?php echo e($i->cantidad ? ' · '.$i->cantidad.' '.($i->unidad??'') : ''); ?></div>
    </div>
    <div class="flex gap-1 items-center">
      <?php if(isset($i->foto_soporte) && $i->foto_soporte): ?>
        <img src="<?php echo e(asset($i->foto_soporte)); ?>" class="ingreso-foto" onclick="openLightboxI('<?php echo e(asset($i->foto_soporte)); ?>')" title="Ver soporte">
      <?php endif; ?>
      <div style="text-align:right;">
        <div class="font-bold text-green text-sm">$<?php echo e(number_format($i->valor_total,0,',','.')); ?></div>
        <?php if($i->precio_unitario && $i->cantidad): ?>
          <div style="font-size:.7rem;color:var(--text-muted);">$<?php echo e(number_format($i->precio_unitario,0,',','.')); ?>/u</div>
        <?php endif; ?>
      </div>
      <button onclick="openModal('editIngreso<?php echo e($i->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="<?php echo e(route('ingresos.destroy',$i->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>

  
  <div class="modal-overlay" id="editIngreso<?php echo e($i->id); ?>" style="display:none;">
    <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar ingreso</h3>
      <form method="POST" action="<?php echo e(route('ingresos.update',$i->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
        <div class="form-group"><label>Tipo de ingreso</label>
          <select name="tipo" class="form-control">
            <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>" <?php echo e(($i->tipo??'venta')===$key?'selected':''); ?>><?php echo e($t['emoji']); ?> <?php echo e($t['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" required value="<?php echo e($i->descripcion); ?>"></div>
        <div class="grid-2">
          <div class="form-group"><label>Cantidad</label><input type="number" step="0.01" name="cantidad" class="form-control" value="<?php echo e($i->cantidad); ?>" oninput="calcTotalE<?php echo e($i->id); ?>()"></div>
          <div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" value="<?php echo e($i->unidad); ?>" placeholder="kg, bultos..."></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" id="pue<?php echo e($i->id); ?>" class="form-control" value="<?php echo e($i->precio_unitario); ?>" oninput="calcTotalE<?php echo e($i->id); ?>()"></div>
          <div class="form-group"><label>Total (COP) *</label><input type="number" step="100" name="valor_total" id="vte<?php echo e($i->id); ?>" class="form-control" required value="<?php echo e($i->valor_total); ?>"></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e($i->fecha); ?>"></div>
          <div class="form-group"><label>Comprador</label><input type="text" name="comprador" id="comp_e<?php echo e($i->id); ?>" class="form-control" value="<?php echo e($i->comprador); ?>"></div>
        </div>
        <div class="form-group"><label>Cliente guardado</label>
          <select name="cliente_id" class="form-control" onchange="syncCliente(this,'comp_e<?php echo e($i->id); ?>')">
            <option value="">-- Seleccionar --</option>
            <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cl->id); ?>" <?php echo e(($i->cliente_id??null)==$cl->id?'selected':''); ?>><?php echo e($cl->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Asociar a cultivo</label>
          <select name="cultivo_id" class="form-control"><option value="">Ninguno</option>
            <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cv->id); ?>" <?php echo e($i->cultivo_id==$cv->id?'selected':''); ?>>🌱 <?php echo e($cv->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Asociar a animal</label>
          <select name="animal_id" class="form-control"><option value="">Ninguno</option>
            <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($an->id); ?>" <?php echo e(($i->animal_id??null)==$an->id?'selected':''); ?>>🐄 <?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Asociar a cosecha</label>
          <select name="cosecha_id" class="form-control"><option value="">Ninguno</option>
            <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($co->id); ?>" <?php echo e(($i->cosecha_id??null)==$co->id?'selected':''); ?>>🌾 <?php echo e($co->producto); ?> · <?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>📷 Soporte / foto del pago</label>
          <?php if($i->foto_soporte ?? null): ?><img src="<?php echo e(asset($i->foto_soporte)); ?>" style="width:80px;border-radius:8px;margin-bottom:6px;display:block;"><?php endif; ?>
          <input type="file" name="foto_soporte" class="form-control" accept="image/*">
        </div>
        <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2"><?php echo e($i->notas); ?></textarea></div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editIngreso<?php echo e($i->id); ?>')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  <script>
  function calcTotalE<?php echo e($i->id); ?>() {
    const c = parseFloat(document.querySelector('[name=cantidad]',document.getElementById('editIngreso<?php echo e($i->id); ?>')).value)||0;
    const p = parseFloat(document.getElementById('pue<?php echo e($i->id); ?>').value)||0;
    if(c&&p) document.getElementById('vte<?php echo e($i->id); ?>').value = Math.round(c*p);
  }
  </script>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>


<div class="tab-panel-i" id="tab-i-clientes">
  <?php if($clientes->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">👥</div><p>Sin clientes guardados.</p><p style="font-size:.85rem;">Guarda tus compradores frecuentes para seleccionarlos rápido.</p></div>
  <?php else: ?>
  <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="cliente-card">
    <div class="flex gap-3 items-center" style="flex:1;">
      <div class="comprador-avatar"><?php echo e(mb_strtoupper(mb_substr($cl->nombre,0,1))); ?></div>
      <div>
        <div style="font-weight:700;"><?php echo e($cl->nombre); ?></div>
        <div style="font-size:.78rem;color:var(--text-secondary);"><?php echo e($cl->tipo ?? 'Comprador'); ?></div>
        <?php if($cl->telefono): ?><div style="font-size:.75rem;"><a href="tel:<?php echo e($cl->telefono); ?>" style="color:var(--verde-dark);">📞 <?php echo e($cl->telefono); ?></a></div><?php endif; ?>
      </div>
    </div>
    <form method="POST" action="<?php echo e(route('ingresos.cliente.destroy',$cl->id)); ?>" onsubmit="return confirm('¿Eliminar cliente?')"><?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
    </form>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
  <button class="btn btn-primary btn-full mt-2" onclick="openModal('modalNuevoCliente')">+ Nuevo cliente</button>
</div>


<div class="tab-panel-i" id="tab-i-estadisticas">
  <?php if($topCompradores->count()): ?>
  <div style="background:var(--surface);border-radius:var(--radius-lg);padding:14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
    <p style="font-size:.8rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">🏆 Top compradores este año</p>
    <?php $__currentLoopData = $topCompradores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="top-comprador-row">
      <div class="comprador-avatar"><?php echo e(mb_strtoupper(mb_substr($tc->comprador,0,1))); ?></div>
      <div style="flex:1;">
        <div style="font-weight:600;font-size:.88rem;"><?php echo e($tc->comprador); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($tc->cnt); ?> transacción(es)</div>
      </div>
      <strong style="color:var(--verde-dark);">$<?php echo e(number_format($tc->total,0,',','.')); ?></strong>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php endif; ?>

  <?php
    $totalPorTipo = collect($tipos)->mapWithKeys(fn($t,$k) => [$k => 0]);
    foreach($ingresos as $ing) {
      $k = $ing->tipo ?? 'venta';
      if(isset($totalPorTipo[$k])) $totalPorTipo[$k] += $ing->valor_total;
    }
    $totalPorTipo = $totalPorTipo->filter(fn($v) => $v > 0)->sortDesc();
    $granTotal = $totalPorTipo->sum() ?: 1;
  ?>

  <?php if($totalPorTipo->count()): ?>
  <div style="background:var(--surface);border-radius:var(--radius-lg);padding:14px;box-shadow:var(--shadow-sm);">
    <p style="font-size:.8rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">📊 Ingresos por tipo (período actual)</p>
    <?php $__currentLoopData = $totalPorTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $tipoI = $tipos[$key] ?? ['label'=>ucfirst($key),'emoji'=>'📦']; ?>
    <div style="margin-bottom:12px;">
      <div class="flex items-center justify-between mb-1">
        <span style="font-size:.85rem;font-weight:600;"><?php echo e($tipoI['emoji']); ?> <?php echo e($tipoI['label']); ?></span>
        <span style="font-size:.85rem;font-weight:700;color:var(--verde-dark);">$<?php echo e(number_format($total,0,',','.')); ?></span>
      </div>
      <div style="background:#e5e7eb;border-radius:99px;height:6px;overflow:hidden;">
        <div style="height:100%;border-radius:99px;background:var(--verde-mid);width:<?php echo e(round($total/$granTotal*100)); ?>%;"></div>
      </div>
      <div style="font-size:.7rem;color:var(--text-muted);margin-top:2px;"><?php echo e(round($total/$granTotal*100)); ?>% del total</div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php else: ?>
  <div class="empty-state"><div class="emoji">📊</div><p>Sin datos suficientes aún.</p></div>
  <?php endif; ?>
</div>




<div class="modal-overlay" id="modalNuevoIngreso" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">💵 Registrar ingreso</h3>
    <form method="POST" action="<?php echo e(route('ingresos.store')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo de ingreso</label>
        <select name="tipo" class="form-control">
          <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($key); ?>"><?php echo e($t['emoji']); ?> <?php echo e($t['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="descripcion" class="form-control" placeholder="Ej: Venta de maíz amarillo" required></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" step="0.01" name="cantidad" class="form-control" placeholder="0" id="cantN" oninput="calcTotalN()"></div>
        <div class="form-group"><label>Unidad</label><input type="text" name="unidad" class="form-control" placeholder="kg, bultos..."></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Precio unitario</label><input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0" id="puN" oninput="calcTotalN()"></div>
        <div class="form-group"><label>Total (COP) *</label><input type="number" step="100" name="valor_total" class="form-control" required placeholder="0" id="vtN"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha</label><input type="date" name="fecha" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div>
        <div class="form-group"><label>Comprador</label><input type="text" name="comprador" id="compN" class="form-control" placeholder="Nombre"></div>
      </div>
      
      <?php if($clientes->count()): ?>
      <div class="form-group"><label>Cliente guardado</label>
        <select name="cliente_id" class="form-control" onchange="syncCliente(this,'compN')">
          <option value="">-- Seleccionar --</option>
          <?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cl->id); ?>"><?php echo e($cl->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <?php endif; ?>
      
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
      <div class="form-group"><label>Asociar a cosecha</label>
        <select name="cosecha_id" class="form-control"><option value="">Ninguno</option>
          <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($co->id); ?>">🌾 <?php echo e($co->producto); ?> · <?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>📷 Soporte / foto del pago (opcional)</label><input type="file" name="foto_soporte" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoIngreso')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalNuevoCliente" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">👥 Nuevo cliente</h3>
    <form method="POST" action="<?php echo e(route('ingresos.cliente.store')); ?>"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required placeholder="Ej: Comerciante del mercado"></div>
      <div class="form-group"><label>Tipo de cliente</label><input type="text" name="tipo" class="form-control" placeholder="Ej: Comerciante, Supermercado, Vecino"></div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Opcional"></div>
      </div>
      <div class="form-group"><label>Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Opcional"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Condiciones de pago, observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoCliente')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cliente</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyudaIngresos" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">📈 ¿Cómo funciona Ingresos?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí registras <strong style="color:var(--verde-dark)">todo el dinero que entra a tu finca</strong>, ya sea por ventas, cosechas, animales, subsidios o servicios.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🏷️</span>
          <div><strong>Tipos de ingreso</strong><br>Clasifica cada entrada: venta directa, cosecha, venta de animal, subproductos (huevos, leche), subsidios o servicios prestados.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🌱🐄🌾</span>
          <div><strong>Asociaciones</strong><br>Vincula cada ingreso a un cultivo, animal o cosecha. Así puedes ver la rentabilidad real de cada uno en sus fichas.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">👥</span>
          <div><strong>Clientes guardados</strong><br>Guarda tus compradores frecuentes con teléfono y tipo para seleccionarlos rápido al registrar.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">🧮</span>
          <div><strong>Cálculo automático</strong><br>Si ingresas cantidad × precio unitario, el total se calcula solo.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📊</span>
          <div><strong>Estadísticas</strong><br>Ve el desglose de ingresos por tipo y tus mejores compradores del año.</div>
        </div>
        <div style="display:flex;gap:10px;align-items:flex-start;">
          <span style="font-size:1.3rem;">📷</span>
          <div><strong>Soporte de pago</strong><br>Adjunta una foto del recibo o transferencia como soporte del ingreso.</div>
        </div>
      </div>
      <p style="margin-top:16px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Usa los tabs para ver tus clientes y estadísticas de ventas del año.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaIngresos')">¡Entendido!</button>
  </div>
</div>


<div id="lightboxI" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxIImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>

<button class="fab" style="background:var(--verde-dark);" onclick="openModal('modalNuevoIngreso')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
function switchTabI(name, btn) {
  document.querySelectorAll('.tab-btn-i').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel-i').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-i-'+name).classList.add('active');
  btn.classList.add('active');
}
function calcTotalN() {
  const c = parseFloat(document.getElementById('cantN').value)||0;
  const p = parseFloat(document.getElementById('puN').value)||0;
  if(c && p) document.getElementById('vtN').value = Math.round(c*p);
}
function syncCliente(sel, targetId) {
  const opt = sel.options[sel.selectedIndex];
  if(sel.value) document.getElementById(targetId).value = opt.text;
  else document.getElementById(targetId).value = '';
}
function openLightboxI(src) {
  document.getElementById('lightboxIImg').src = src;
  document.getElementById('lightboxI').style.display = 'flex';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/ingresos.blade.php ENDPATH**/ ?>