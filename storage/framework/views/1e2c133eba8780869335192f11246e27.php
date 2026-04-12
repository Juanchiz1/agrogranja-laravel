
<?php $__env->startSection('title','Inventario'); ?>
<?php $__env->startSection('page_title','Inventario de Insumos'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startSection('content'); ?>


<?php if($alertasStock > 0 || $porVencer > 0): ?>
<div class="alert alert-warning" style="cursor:pointer;" onclick="location.href='<?php echo e(route('inventario.alertas')); ?>'">
  ⚠️
  <?php if($alertasStock > 0): ?> <?php echo e($alertasStock); ?> insumo(s) con stock bajo mínimo <?php endif; ?>
  <?php if($alertasStock > 0 && $porVencer > 0): ?> · <?php endif; ?>
  <?php if($porVencer > 0): ?> <?php echo e($porVencer); ?> por vencer en 30 días <?php endif; ?>
  — <strong>Ver alertas →</strong>
</div>
<?php endif; ?>


<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:14px;">
  <div class="stat-card">
    <div class="stat-value text-green"><?php echo e($totalInsumos); ?></div>
    <div class="stat-label">Total insumos</div>
  </div>
  <div class="stat-card" style="<?php echo e($alertasStock > 0 ? 'background:#fff7ed' : ''); ?>">
    <div class="stat-value" style="color:<?php echo e($alertasStock > 0 ? 'var(--naranja)' : 'var(--verde-dark)'); ?>">
      <?php echo e($alertasStock); ?>

    </div>
    <div class="stat-label">Bajo mínimo</div>
  </div>
  <div class="stat-card" style="<?php echo e($porVencer > 0 ? 'background:#fef2f2' : ''); ?>">
    <div class="stat-value" style="color:<?php echo e($porVencer > 0 ? 'var(--rojo)' : 'var(--verde-dark)'); ?>">
      <?php echo e($porVencer); ?>

    </div>
    <div class="stat-label">Por vencer</div>
  </div>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">
      $<?php echo e(number_format($valorInventario/1000,0)); ?>k
    </div>
    <div class="stat-label">Valor total</div>
  </div>
</div>


<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:120px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar insumo..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <select name="cat" class="form-control" style="width:140px;" onchange="this.form.submit()">
    <option value="">Categoría</option>
    <?php $__currentLoopData = $categorias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option <?php echo e(request('cat') === $cat ? 'selected' : ''); ?>><?php echo e($cat); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <label class="btn btn-secondary" style="display:flex;align-items:center;gap:6px;cursor:pointer;">
    <input type="checkbox" name="alerta" value="1" <?php echo e(request('alerta') ? 'checked' : ''); ?> onchange="this.form.submit()">
    Solo alertas
  </label>
  <button type="submit" class="btn btn-secondary">Filtrar</button>
</form>


<?php if($insumos->isEmpty()): ?>
<div class="empty-state">
  <div class="emoji">📦</div>
  <p><strong>Sin insumos registrados.</strong></p>
  <p>Toca + para agregar tu primer insumo.</p>
</div>
<?php else: ?>
<?php $__currentLoopData = $insumos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $pct       = $ins->stock_minimo > 0 ? min(100, round($ins->cantidad_actual / $ins->stock_minimo * 100)) : 100;
  $alerta    = $ins->cantidad_actual <= $ins->stock_minimo;
  $vencePronto = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->diffInDays(now()) <= 30 && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isFuture();
  $vencido     = $ins->fecha_vencimiento && \Carbon\Carbon::parse($ins->fecha_vencimiento)->isPast();
  $barColor  = $alerta ? 'var(--rojo)' : ($pct < 60 ? 'var(--naranja)' : 'var(--verde-dark)');
?>

<div class="card mb-2" style="<?php echo e($alerta ? 'border-left:3px solid var(--rojo);' : ($vencePronto ? 'border-left:3px solid var(--naranja);' : '')); ?>">
  <div class="flex items-center gap-3">

    
    <div style="width:44px;height:44px;border-radius:11px;background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0;">
      📦
    </div>

    
    <div style="flex:1;min-width:0;">
      <div class="flex items-center gap-2" style="flex-wrap:wrap;">
        <span class="font-bold text-sm"><?php echo e($ins->nombre); ?></span>
        <span class="badge badge-green" style="font-size:.68rem;"><?php echo e($ins->categoria); ?></span>
        <?php if($alerta): ?><span class="badge badge-red" style="font-size:.68rem;">⚠️ Stock bajo</span><?php endif; ?>
        <?php if($vencido): ?><span class="badge badge-red" style="font-size:.68rem;">Vencido</span><?php endif; ?>
        <?php if($vencePronto && !$vencido): ?><span class="badge badge-orange" style="font-size:.68rem;">Por vencer</span><?php endif; ?>
      </div>

      
      <div class="flex items-center gap-2 mt-2">
        <div class="progress-bar" style="flex:1;">
          <div class="progress-fill" style="width:<?php echo e($pct); ?>%;background:<?php echo e($barColor); ?>;"></div>
        </div>
        <span class="text-xs" style="white-space:nowrap;color:<?php echo e($alerta ? 'var(--rojo)' : 'var(--gris)'); ?>;">
          <?php echo e(number_format($ins->cantidad_actual,1)); ?> / <?php echo e(number_format($ins->stock_minimo,1)); ?> <?php echo e($ins->unidad); ?>

        </span>
      </div>

      
      <div class="text-xs text-gray mt-2" style="display:flex;gap:10px;flex-wrap:wrap;">
        <?php if($ins->proveedor): ?><span>🏪 <?php echo e($ins->proveedor); ?></span><?php endif; ?>
        <?php if($ins->precio_unitario): ?><span>💰 $<?php echo e(number_format($ins->precio_unitario,0,',','.')); ?>/<?php echo e($ins->unidad); ?></span><?php endif; ?>
        <?php if($ins->fecha_vencimiento): ?><span style="color:<?php echo e($vencido ? 'var(--rojo)' : ($vencePronto ? 'var(--naranja)' : 'var(--gris)')); ?>">📅 Vence: <?php echo e(\Carbon\Carbon::parse($ins->fecha_vencimiento)->format('d/m/Y')); ?></span><?php endif; ?>
      </div>
    </div>

    
    <div class="flex gap-2" style="flex-shrink:0;align-items:center;">
      <button onclick="openModal('movimiento<?php echo e($ins->id); ?>')"
        class="btn btn-sm btn-primary" title="Registrar movimiento">
        +/-
      </button>
      <button onclick="openModal('editInsumo<?php echo e($ins->id); ?>')"
        class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="<?php echo e(route('inventario.destroy',$ins->id)); ?>" onsubmit="return confirm('Eliminar este insumo?')">
        <?php echo csrf_field(); ?><button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>
</div>


<div class="modal-overlay" id="movimiento<?php echo e($ins->id); ?>" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar movimiento — <?php echo e($ins->nombre); ?></h3>
    <div style="background:var(--verde-bg);border-radius:10px;padding:10px 14px;margin-bottom:14px;">
      <span class="text-sm">Stock actual:</span>
      <strong style="color:<?php echo e($alerta ? 'var(--rojo)' : 'var(--verde-dark)'); ?>">
        <?php echo e(number_format($ins->cantidad_actual,2)); ?> <?php echo e($ins->unidad); ?>

      </strong>
      <span class="text-xs text-gray"> / Mínimo: <?php echo e($ins->stock_minimo); ?> <?php echo e($ins->unidad); ?></span>
    </div>
    <form method="POST" action="<?php echo e(route('inventario.movimiento',$ins->id)); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Tipo de movimiento *</label>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
          <?php $__currentLoopData = ['entrada'=>['🟢','Entrada','verde-bg','verde-dark'],'salida'=>['🔴','Salida','#fef2f2','var(--rojo)'],'ajuste'=>['🔵','Ajuste','#eff6ff','#2563eb']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => [$icn,$lbl,$bg,$color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <label style="cursor:pointer;">
            <input type="radio" name="tipo" value="<?php echo e($tipo); ?>" <?php echo e($tipo==='entrada'?'checked':''); ?> style="display:none;" class="tipo-radio">
            <div class="tipo-btn" data-tipo="<?php echo e($tipo); ?>"
              style="background:var(--gris-light);border:2px solid var(--gris-border);border-radius:10px;padding:10px;text-align:center;transition:all .15s;">
              <div style="font-size:1.3rem;"><?php echo e($icn); ?></div>
              <div style="font-size:.8rem;font-weight:700;"><?php echo e($lbl); ?></div>
            </div>
          </label>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Cantidad *</label>
          <input type="number" step="0.1" name="cantidad" class="form-control" required
            placeholder="0" oninput="calcNuevoStock(<?php echo e($ins->id); ?>, <?php echo e($ins->cantidad_actual); ?>)">
        </div>
        <div class="form-group">
          <label>Precio unitario</label>
          <input type="number" step="100" name="precio_unitario" class="form-control"
            placeholder="$<?php echo e($ins->precio_unitario ?? '0'); ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Nuevo stock estimado</label>
        <div id="nuevoStock<?php echo e($ins->id); ?>" style="font-size:1.1rem;font-weight:700;color:var(--verde-dark);padding:8px;background:var(--verde-bg);border-radius:8px;">
          <?php echo e(number_format($ins->cantidad_actual,2)); ?> <?php echo e($ins->unidad); ?>

        </div>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
        </div>
        <div class="form-group">
          <label>Motivo</label>
          <input type="text" name="motivo" class="form-control" placeholder="Compra, uso, ajuste...">
        </div>
      </div>
      <?php if($cultivos->count()): ?>
      <div class="form-group">
        <label>Cultivo donde se usó</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('movimiento<?php echo e($ins->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="editInsumo<?php echo e($ins->id); ?>" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Editar insumo</h3>
    <form method="POST" action="<?php echo e(route('inventario.update',$ins->id)); ?>">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('pages._inventario_form', ['ins' => $ins, 'categorias' => $categorias, 'nuevo' => false], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editInsumo<?php echo e($ins->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>

<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if($movimientos->count()): ?>
<p class="section-title mt-3">Últimos movimientos</p>
<?php $__currentLoopData = $movimientos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mov): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $ic    = ['entrada'=>'🟢','salida'=>'🔴','ajuste'=>'🔵'][$mov->tipo] ?? '⚪';
  $color = ['entrada'=>'var(--verde-dark)','salida'=>'var(--rojo)','ajuste'=>'#2563eb'][$mov->tipo] ?? 'var(--gris)';
  $signo = $mov->tipo === 'entrada' ? '+' : ($mov->tipo === 'salida' ? '-' : '=');
?>
<div class="list-item">
  <div class="item-icon" style="background:var(--gris-light);font-size:1rem;"><?php echo e($ic); ?></div>
  <div class="item-body">
    <div class="item-title"><?php echo e($mov->insumo_nombre); ?></div>
    <div class="item-sub">
      <?php echo e($mov->motivo ?? ucfirst($mov->tipo)); ?>

      <?php if($mov->cultivo_nombre): ?> · <?php echo e($mov->cultivo_nombre); ?> <?php endif; ?>
      · <?php echo e(\Carbon\Carbon::parse($mov->creado_en)->format('d/m/Y H:i')); ?>

    </div>
  </div>
  <span style="font-weight:700;color:<?php echo e($color); ?>;font-size:.95rem;">
    <?php echo e($signo); ?><?php echo e(number_format($mov->cantidad,1)); ?> <?php echo e($mov->unidad); ?>

  </span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevoInsumo" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Nuevo insumo</h3>
    <form method="POST" action="<?php echo e(route('inventario.store')); ?>">
      <?php echo csrf_field(); ?>
      <?php echo $__env->make('pages._inventario_form', ['ins' => null, 'categorias' => $categorias, 'nuevo' => true], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoInsumo')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar insumo</button>
      </div>
    </form>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevoInsumo')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
// Selector visual de tipo de movimiento
document.querySelectorAll('.tipo-radio').forEach(radio => {
  radio.addEventListener('change', function() {
    document.querySelectorAll('.tipo-btn').forEach(btn => {
      btn.style.background = 'var(--gris-light)';
      btn.style.borderColor = 'var(--gris-border)';
    });
    const btn = document.querySelector(`.tipo-btn[data-tipo="${this.value}"]`);
    if (btn) {
      const colors = { entrada: ['var(--verde-bg)','var(--verde-dark)'], salida: ['#fef2f2','var(--rojo)'], ajuste: ['#eff6ff','#2563eb'] };
      const [bg, border] = colors[this.value] || ['var(--gris-light)','var(--gris-border)'];
      btn.style.background = bg;
      btn.style.borderColor = border;
    }
  });
});

// Calcular nuevo stock dinámicamente
function calcNuevoStock(id, stockActual) {
  const modal    = document.getElementById('movimiento' + id);
  const tipo     = modal.querySelector('input[name="tipo"]:checked')?.value || 'entrada';
  const cantidad = parseFloat(modal.querySelector('input[name="cantidad"]')?.value) || 0;
  const el       = document.getElementById('nuevoStock' + id);
  if (!el) return;

  let nuevo = stockActual;
  if (tipo === 'entrada') nuevo = stockActual + cantidad;
  else if (tipo === 'salida')  nuevo = Math.max(0, stockActual - cantidad);
  else if (tipo === 'ajuste')  nuevo = cantidad;

  el.textContent = nuevo.toFixed(2) + ' ' + el.dataset.unidad;
  el.style.color = nuevo <= parseFloat(el.dataset.minimo || 0) ? 'var(--rojo)' : 'var(--verde-dark)';
}

// Activar primer tipo al cargar
document.querySelectorAll('.tipo-btn[data-tipo="entrada"]').forEach(btn => {
  btn.style.background = 'var(--verde-bg)';
  btn.style.borderColor = 'var(--verde-dark)';
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/inventario.blade.php ENDPATH**/ ?>