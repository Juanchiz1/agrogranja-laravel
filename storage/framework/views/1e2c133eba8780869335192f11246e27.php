
<?php $__env->startSection('title','Inventario'); ?>
<?php $__env->startSection('page_title','📦 Inventario'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/inventario.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php $tabActual = request('tab', 'insumos'); ?>


<div style="text-align:right;margin-bottom:10px;">
  <button onclick="openModal('modalAyudaInv')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<?php if($alertasStock > 0 || $porVencer > 0): ?>
<div style="background:#fffbeb;border:1px solid #f59e0b;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;cursor:pointer;" onclick="location.href='<?php echo e(route('inventario.alertas')); ?>'">
  <div style="display:flex;align-items:center;justify-content:space-between;">
    <span style="font-weight:700;color:#92400e;">
      ⚠️
      <?php if($alertasStock > 0): ?> <?php echo e($alertasStock); ?> con stock bajo <?php endif; ?>
      <?php if($alertasStock > 0 && $porVencer > 0): ?> · <?php endif; ?>
      <?php if($porVencer > 0): ?> <?php echo e($porVencer); ?> por vencer <?php endif; ?>
    </span>
    <span style="font-size:.8rem;color:#d97706;font-weight:600;">Ver alertas →</span>
  </div>
</div>
<?php endif; ?>


<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px;gap:8px;">
  <div class="stat-card">
    <div class="stat-value text-green"><?php echo e($totalInsumos); ?></div>
    <div class="stat-label">Total</div>
  </div>
  <div class="stat-card" style="<?php echo e($alertasStock>0 ? 'background:#fff7ed' : ''); ?>">
    <div class="stat-value" style="color:<?php echo e($alertasStock>0 ? '#d97706' : 'var(--verde-dark)'); ?>"><?php echo e($alertasStock); ?></div>
    <div class="stat-label">Bajo mínimo</div>
  </div>
  <div class="stat-card" style="<?php echo e($porVencer>0 ? 'background:#fef2f2' : ''); ?>">
    <div class="stat-value" style="color:<?php echo e($porVencer>0 ? 'var(--rojo)' : 'var(--verde-dark)'); ?>"><?php echo e($porVencer); ?></div>
    <div class="stat-label">Por vencer</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">$<?php echo e(number_format($valorInventario/1000,1)); ?>k</div>
    <div class="stat-label">Valor</div>
  </div>
</div>


<div class="tabs-inv">
  <a href="?tab=insumos" class="tab-inv <?php echo e($tabActual==='insumos' ? 'active' : ''); ?>">📦 Insumos</a>
  <a href="?tab=movimientos" class="tab-inv <?php echo e($tabActual==='movimientos' ? 'active' : ''); ?>">📜 Línea de tiempo</a>
</div>


<?php if($tabActual === 'insumos'): ?>

<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <input type="hidden" name="tab" value="insumos">
  <div class="search-box" style="flex:1;min-width:100px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar insumo..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <select name="cat" class="form-control" style="width:140px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <optgroup label="<?php echo e($grupo); ?>">
        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option <?php echo e(request('cat')===$cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </optgroup>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <select name="uso" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Uso</option>
    <option value="cultivo" <?php echo e(request('uso')==='cultivo' ? 'selected' : ''); ?>>🌱 Cultivos</option>
    <option value="animal"  <?php echo e(request('uso')==='animal'  ? 'selected' : ''); ?>>🐄 Animales</option>
    <option value="general" <?php echo e(request('uso')==='general' ? 'selected' : ''); ?>>📦 General</option>
  </select>
  <label class="btn btn-secondary" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
    <input type="checkbox" name="alerta" value="1" <?php echo e(request('alerta') ? 'checked' : ''); ?> onchange="this.form.submit()">
    ⚠️ Alertas
  </label>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

<?php if($insumos->isEmpty()): ?>
  <div class="empty-state"><div class="emoji">📦</div><p><strong>Sin insumos registrados.</strong></p><p>Toca + para agregar tu primer insumo.</p></div>
<?php else: ?>
  <?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $pct      = $ins->stock_minimo > 0 ? min(100, round($ins->cantidad_actual / $ins->stock_minimo * 100)) : 100;
    $alerta   = $ins->cantidad_actual <= $ins->stock_minimo;
    $vencePronto = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->diffInDays(now()) <= 30 && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isFuture();
    $vencido  = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isPast();
    $barColor = $alerta ? 'var(--rojo)' : ($pct < 60 ? '#f59e0b' : 'var(--verde-dark)');
    $uso      = $ins->uso_principal ?? 'general';
    $iconos   = ['Semillas'=>'🌱','Fertilizantes'=>'🌿','Plaguicidas'=>'🧴','Herbicidas'=>'🌾','Herramientas'=>'🔧','Combustible'=>'⛽','Alimento animal'=>'🌾','Medicamentos veterinarios'=>'💊','Equipos'=>'⚙️'];
    $icono    = $iconos[$ins->categoria] ?? '📦';
    $usoBadges= ['cultivo'=>'uso-cultivo','animal'=>'uso-animal','general'=>'uso-general'];
    $usoLabels= ['cultivo'=>'🌱 Cultivos','animal'=>'🐄 Animales','general'=>'📦 General'];
    $bordeStyle= $alerta ? 'border-left:3px solid var(--rojo)' : ($vencePronto ? 'border-left:3px solid #f59e0b' : '');
  ?>

  <div class="insumo-card" style="<?php echo e($bordeStyle); ?>">
    <div class="flex gap-3 items-start">
      
      <?php if(isset($ins->foto) && $ins->foto): ?>
        <img src="<?php echo e(asset($ins->foto)); ?>" class="insumo-foto" onclick="openLightboxI('<?php echo e(asset($ins->foto)); ?>')">
      <?php else: ?>
        <div class="insumo-foto-ph"><?php echo e($icono); ?></div>
      <?php endif; ?>

      <div style="flex:1;min-width:0;">
        
        <div class="flex items-center gap-2 flex-wrap mb-1">
          <span style="font-weight:700;font-size:.92rem;"><?php echo e($ins->nombre); ?></span>
          <span class="badge badge-green" style="font-size:.68rem;"><?php echo e($ins->categoria); ?></span>
          <span class="uso-badge <?php echo e($usoBadges[$uso] ?? 'uso-general'); ?>"><?php echo e($usoLabels[$uso] ?? '📦 General'); ?></span>
          <?php if($alerta): ?>
            <span class="badge badge-red" style="font-size:.68rem;">⚠️ Stock bajo</span>
          <?php endif; ?>
          <?php if($vencido): ?>
            <span class="badge badge-red" style="font-size:.68rem;">Vencido</span>
          <?php endif; ?>
          <?php if($vencePronto && !$vencido): ?>
            <span class="badge badge-orange" style="font-size:.68rem;">Por vencer</span>
          <?php endif; ?>
        </div>

        
        <div class="flex items-center gap-2 mb-2">
          <div style="flex:1;background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;">
            <div style="height:100%;border-radius:99px;background:<?php echo e($barColor); ?>;width:<?php echo e($pct); ?>%;"></div>
          </div>
          <span style="font-size:.75rem;white-space:nowrap;color:<?php echo e($alerta ? 'var(--rojo)' : 'var(--text-secondary)'); ?>;font-weight:<?php echo e($alerta ? '700' : '400'); ?>;">
            <?php echo e(number_format($ins->cantidad_actual,1)); ?> / <?php echo e(number_format($ins->stock_minimo,1)); ?> <?php echo e($ins->unidad); ?>

          </span>
        </div>

        
        <div style="display:flex;flex-wrap:wrap;gap:8px;font-size:.74rem;color:var(--text-muted);">
          <?php if($ins->proveedor): ?> <span>🏪 <?php echo e($ins->proveedor); ?></span> <?php endif; ?>
          <?php if($ins->precio_unitario): ?> <span>💰 $<?php echo e(number_format($ins->precio_unitario,0,',','.')); ?>/<?php echo e($ins->unidad); ?></span> <?php endif; ?>
          <?php if(isset($ins->ubicacion) && $ins->ubicacion): ?> <span>📍 <?php echo e($ins->ubicacion); ?></span> <?php endif; ?>
          <?php if($ins->fecha_vencimiento): ?>
            <span style="color:<?php echo e($vencido ? 'var(--rojo)' : ($vencePronto ? '#d97706' : 'var(--text-muted)')); ?>">
              📅 Vence: <?php echo e(\Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y')); ?>

            </span>
          <?php endif; ?>
        </div>

        
        <div class="flex gap-2 mt-2">
          <button onclick="openModal('mov<?php echo e($ins->id); ?>')" class="btn btn-sm btn-primary">+/- Stock</button>
          <button onclick="openModal('edit<?php echo e($ins->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
          <form method="POST" action="<?php echo e(route('inventario.destroy',$ins->id)); ?>" onsubmit="return confirm('¿Eliminar?')">
            <?php echo csrf_field(); ?>
            <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  
  <div class="modal-overlay" id="mov<?php echo e($ins->id); ?>" style="display:none;">
    <div class="modal-sheet">
      <div class="modal-handle"></div>
      <h3 class="modal-title">📦 Movimiento — <?php echo e($ins->nombre); ?></h3>
      <div style="background:var(--verde-bg);border-radius:10px;padding:10px 14px;margin-bottom:14px;">
        <span style="font-size:.85rem;">Stock actual: </span>
        <strong style="color:<?php echo e($alerta ? 'var(--rojo)' : 'var(--verde-dark)'); ?>"><?php echo e(number_format($ins->cantidad_actual,2)); ?> <?php echo e($ins->unidad); ?></strong>
        <span style="font-size:.78rem;color:var(--text-muted);"> / Mín: <?php echo e($ins->stock_minimo); ?></span>
      </div>
      <form method="POST" action="<?php echo e(route('inventario.movimiento',$ins->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo" class="form-control" required id="tipoMov<?php echo e($ins->id); ?>" onchange="toggleMovFields('<?php echo e($ins->id); ?>')">
            <option value="salida">Salida (se consume/reduce stock)</option>
            <option value="entrada">Entrada (compra/recepcion)</option>
            <option value="en_uso">En uso (herramienta en uso, stock no cambia)</option>
            <option value="devolucion">Devolucion (retorno de herramienta en uso)</option>
            <option value="ajuste">Ajuste de stock</option>
          </select>
          <small style="font-size:.73rem;color:var(--text-muted);">
            "En uso" es para herramientas: la pala sigue en inventario pero alguien la tiene.
          </small>
        </div>
        <div id="cantidadWrap<?php echo e($ins->id); ?>">
          <div class="grid-2">
            <div class="form-group">
              <label>Cantidad *</label>
              <input type="number" step="0.01" name="cantidad" class="form-control" placeholder="0" value="1">
            </div>
            <div class="form-group">
              <label>Precio unitario</label>
              <input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0">
            </div>
          </div>
        </div>
        <div class="form-group">
          <label>Motivo / descripcion</label>
          <input type="text" name="motivo" class="form-control" placeholder="Ej: Fumigacion lote norte, Compra nueva remesa...">
        </div>
        <div class="form-group">
          <label>Persona (opcional)</label>
          <?php if(isset($trabajadores) && $trabajadores->count()): ?>
          <select name="persona_id" class="form-control">
            <option value="">Sin asignar</option>
            <?php $__currentLoopData = $trabajadores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($wk->id); ?>"><?php echo e($wk->nombre); ?><?php echo e($wk->cargo ? ' ('.$wk->cargo.')' : ''); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
          <?php else: ?>
          <input type="text" name="persona" class="form-control" placeholder="Nombre de quien retira o ingresa">
          <?php endif; ?>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Fecha *</label>
            <input type="date" name="fecha" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
          </div>
          <div class="form-group">
            <label>Proveedor</label>
            <input type="text" name="proveedor_mov" class="form-control" placeholder="<?php echo e($ins->proveedor ?? 'Proveedor'); ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Asociar a cultivo</label>
          <select name="cultivo_id" class="form-control">
            <option value="">Ninguno</option>
            <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($cv->id); ?>">🌱 <?php echo e($cv->nombre); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label>Asociar a animal</label>
          <select name="animal_id" class="form-control">
            <option value="">Ninguno</option>
            <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option value="<?php echo e($an->id); ?>">🐄 <?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div style="background:#f0fdf4;border-radius:8px;padding:10px;border:1px solid #86efac;margin-bottom:12px;">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
            <input type="checkbox" name="crear_gasto" value="1" style="width:18px;height:18px;">
            <span style="font-size:.87rem;color:#166534;font-weight:600;">✅ Crear gasto automáticamente (solo en entradas con precio)</span>
          </label>
        </div>
        <div class="form-group">
          <label>📷 Foto soporte (opcional)</label>
          <input type="file" name="foto_soporte" class="form-control" accept="image/*">
        </div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('mov<?php echo e($ins->id); ?>')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Registrar</button>
        </div>
      </form>
    </div>
  </div>

  
  <div class="modal-overlay" id="edit<?php echo e($ins->id); ?>" style="display:none;">
    <div class="modal-sheet">
      <div class="modal-handle"></div>
      <h3 class="modal-title">✏️ Editar insumo</h3>
      <form method="POST" action="<?php echo e(route('inventario.update',$ins->id)); ?>" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label>Nombre *</label>
          <input type="text" name="nombre" class="form-control" required value="<?php echo e($ins->nombre); ?>">
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Categoría *</label>
            <select name="categoria" class="form-control" required>
              <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grpE => $itemsE): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <optgroup label="<?php echo e($grpE); ?>">
                  <?php $__currentLoopData = $itemsE; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catE): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option <?php echo e($ins->categoria===$catE ? 'selected' : ''); ?>><?php echo e($catE); ?></option>
                  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </optgroup>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
          <div class="form-group">
            <label>Unidad *</label>
            <select name="unidad" class="form-control" required>
              <?php $__currentLoopData = ['kg','litros','unidades','bultos','gramos','ml','toneladas','libras']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uE): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option <?php echo e($ins->unidad===$uE ? 'selected' : ''); ?>><?php echo e($uE); ?></option>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Stock mínimo *</label>
            <input type="number" step="0.1" name="stock_minimo" class="form-control" required value="<?php echo e($ins->stock_minimo); ?>">
          </div>
          <div class="form-group">
            <label>Precio unitario</label>
            <input type="number" step="100" name="precio_unitario" class="form-control" value="<?php echo e($ins->precio_unitario); ?>">
          </div>
        </div>
        <div class="form-group">
          <label>Uso principal</label>
          <select name="uso_principal" class="form-control">
            <option value="cultivo" <?php echo e(($ins->uso_principal??'general')==='cultivo' ? 'selected' : ''); ?>>🌱 Cultivos</option>
            <option value="animal"  <?php echo e(($ins->uso_principal??'general')==='animal'  ? 'selected' : ''); ?>>🐄 Animales</option>
            <option value="general" <?php echo e(($ins->uso_principal??'general')==='general' ? 'selected' : ''); ?>>📦 General</option>
          </select>
        </div>
        <div class="grid-2">
          <div class="form-group">
            <label>Proveedor</label>
            <input type="text" name="proveedor" class="form-control" value="<?php echo e($ins->proveedor); ?>">
          </div>
          <div class="form-group">
            <label>Ubicación</label>
            <input type="text" name="ubicacion" class="form-control" value="<?php echo e($ins->ubicacion ?? ''); ?>" placeholder="Bodega...">
          </div>
        </div>
        <div class="form-group">
          <label>Fecha vencimiento</label>
          <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo e($ins->fecha_vencimiento ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>📷 Foto</label>
          <?php if(isset($ins->foto) && $ins->foto): ?>
            <img src="<?php echo e(asset($ins->foto)); ?>" style="width:70px;border-radius:8px;margin-bottom:6px;display:block;">
          <?php endif; ?>
          <input type="file" name="foto" class="form-control" accept="image/*">
        </div>
        <div class="form-group">
          <label>Notas</label>
          <textarea name="notas" class="form-control" rows="2"><?php echo e($ins->notas); ?></textarea>
        </div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('edit<?php echo e($ins->id); ?>')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>

  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php endif; ?>



<?php if($tabActual === 'movimientos'): ?>
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:16px;box-shadow:var(--shadow-sm);">
  <p style="font-weight:700;font-size:.9rem;margin-bottom:16px;">📜 Historial de movimientos</p>
  <?php if($movimientos->isEmpty()): ?>
    <div class="empty-state"><div class="emoji">📜</div><p>Sin movimientos registrados aún.</p></div>
  <?php else: ?>
    <div class="mov-timeline">
      <?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $signo    = ['entrada'=>'+','salida'=>'-','ajuste'=>'=','en_uso'=>'↗','devolucion'=>'↙'][$mov->tipo]??'';
        $colorVal = ['entrada'=>'var(--verde-dark)','salida'=>'var(--rojo)','ajuste'=>'#2563eb','en_uso'=>'#7c3aed','devolucion'=>'#0891b2'][$mov->tipo]??'var(--text-secondary)';
        $tipoLabel= ['entrada'=>'Entrada','salida'=>'Salida','ajuste'=>'Ajuste','en_uso'=>'En uso','devolucion'=>'Devolucion'][$mov->tipo]??ucfirst($mov->tipo);
      ?>
      <div class="mov-item">
        <div class="mov-dot <?php echo e($mov->tipo); ?>"></div>
        <div class="mov-body">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;">
            <div>
              <div style="font-weight:700;font-size:.88rem;"><?php echo e($mov->insumo_nombre); ?></div>
              <div style="font-size:.78rem;color:var(--text-secondary);margin-top:2px;">
                <?php echo e($mov->motivo ?? ucfirst($mov->tipo)); ?>

                <?php if($mov->cultivo_nombre): ?> · 🌱 <?php echo e($mov->cultivo_nombre); ?> <?php endif; ?>
                <?php if(isset($mov->animal_nombre) && $mov->animal_nombre): ?> · 🐄 <?php echo e($mov->animal_especie); ?> - <?php echo e($mov->animal_nombre); ?> <?php endif; ?>
              </div>
              <?php if(isset($mov->persona_nombre) && $mov->persona_nombre): ?>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">Por: <?php echo e($mov->persona_nombre); ?></div>
              <?php elseif(isset($mov->persona) && $mov->persona): ?>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:2px;">Por: <?php echo e($mov->persona); ?></div>
              <?php endif; ?>
              <div style="font-size:.72rem;color:var(--text-muted);margin-top:2px;">📅 <?php echo e(\Carbon\Carbon::parse($mov->creado_en)->format('d/m/Y H:i')); ?></div>
            </div>
            <div style="text-align:right;flex-shrink:0;">
              <div style="font-weight:800;color:<?php echo e($colorVal); ?>;font-size:.95rem;"><?php echo e($signo); ?><?php echo e(number_format($mov->cantidad,1)); ?></div>
              <div style="font-size:.72rem;color:var(--text-muted);"><?php echo e($mov->unidad); ?></div>
              <?php if($mov->precio_unitario): ?>
                <div style="font-size:.72rem;color:var(--text-muted);">$<?php echo e(number_format($mov->precio_unitario,0,',','.')); ?>/u</div>
              <?php endif; ?>
            </div>
          </div>
          <?php if(isset($mov->foto_soporte) && $mov->foto_soporte): ?>
            <img src="<?php echo e(asset($mov->foto_soporte)); ?>" style="width:100%;max-height:120px;object-fit:cover;border-radius:6px;margin-top:8px;cursor:pointer;" onclick="openLightboxI('<?php echo e(asset($mov->foto_soporte)); ?>')">
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>



<div class="modal-overlay" id="modalNuevoInsumo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📦 Nuevo insumo</h3>
    <form method="POST" action="<?php echo e(route('inventario.store')); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Nombre del insumo *</label>
        <input type="text" name="nombre" class="form-control" required placeholder="Ej: Glifosato 480SL, Urea 46%...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Categoría *</label>
          <select name="categoria" class="form-control" required>
            <option value="">Seleccionar...</option>
            <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grpN => $itemsN): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <optgroup label="<?php echo e($grpN); ?>">
                <?php $__currentLoopData = $itemsN; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catN): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option><?php echo e($catN); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </optgroup>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group">
          <label>Unidad *</label>
          <select name="unidad" class="form-control" required>
            <option value="">Seleccionar</option>
            <?php $__currentLoopData = ['kg','litros','unidades','bultos','gramos','ml','toneladas','libras']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $uN): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <option><?php echo e($uN); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad actual *</label>
          <input type="number" step="0.01" name="cantidad_actual" class="form-control" required value="0">
        </div>
        <div class="form-group">
          <label>Stock mínimo *</label>
          <input type="number" step="0.1" name="stock_minimo" class="form-control" required value="0">
        </div>
      </div>
      <div class="form-group">
        <label>Uso principal</label>
        <select name="uso_principal" class="form-control">
          <option value="cultivo">🌱 Cultivos</option>
          <option value="animal">🐄 Animales</option>
          <option value="general">📦 General</option>
        </select>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Precio unitario (COP)</label>
          <input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0">
        </div>
        <div class="form-group">
          <label>Proveedor habitual</label>
          <input type="text" name="proveedor" class="form-control" placeholder="Nombre del proveedor">
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Ubicación</label>
          <input type="text" name="ubicacion" class="form-control" placeholder="Bodega, estante, nevera...">
        </div>
        <div class="form-group">
          <label>Fecha vencimiento</label>
          <input type="date" name="fecha_vencimiento" class="form-control">
        </div>
      </div>
      <div class="form-group">
        <label>📷 Foto del insumo</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2" placeholder="Instrucciones de uso, dilución, almacenamiento..."></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoInsumo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar insumo</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyudaInv" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📦 ¿Cómo funciona Inventario?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Controla <strong style="color:var(--verde-dark)">todos los insumos y materiales</strong> de tu finca, tanto para cultivos como para animales.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">⚠️</span><div><strong>Alertas automáticas</strong><br>Cuando el stock baja del mínimo o el insumo está por vencer, aparece una alerta.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">📜</span><div><strong>Línea de tiempo</strong><br>Cada entrada, salida o ajuste queda registrado con fecha, motivo y quién lo retiró.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">👤</span><div><strong>Quién retira</strong><br>Al registrar una salida puedes anotar el nombre del jornalero o persona.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">💰</span><div><strong>Gasto automático</strong><br>Al registrar una compra con precio, puedes crear el gasto financiero automáticamente.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">🐄🌱</span><div><strong>Cultivos y animales</strong><br>Cada movimiento puede asociarse a un cultivo o animal específico.</div></div>
      </div>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaInv')">¡Entendido!</button>
  </div>
</div>


<div id="lightboxInv" onclick="this.style.display='none'" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.9);z-index:9999;align-items:center;justify-content:center;">
  <img id="lightboxInvImg" style="max-width:94vw;max-height:88vh;border-radius:10px;">
</div>

<button class="fab" onclick="openModal('modalNuevoInsumo')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
function openLightboxI(src) {
  document.getElementById('lightboxInvImg').src = src;
  document.getElementById('lightboxInv').style.display = 'flex';
}
function toggleMovFields(id) {
  var tipo = document.getElementById('tipoMov'+id).value;
  var cantWrap = document.getElementById('cantidadWrap'+id);
  // en_uso y devolucion no muestran precio, pero si muestran cantidad (para referencia)
  if (cantWrap) {
    cantWrap.style.opacity = (tipo === 'en_uso' || tipo === 'devolucion') ? '0.5' : '1';
  }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/inventario.blade.php ENDPATH**/ ?>