
<?php $__env->startSection('title','Ordeños'); ?>
<?php $__env->startSection('page_title','🥛 Ordeños'); ?>
<?php $__env->startSection('back_url', route('bovino.hato')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bovino.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card" style="padding:12px 14px;">
  <form method="GET" action="<?php echo e(route('bovino.ordenos')); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <label style="font-size:.82rem;font-weight:600;color:#64748b;">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="<?php echo e($fecha); ?>"
           style="max-width:160px;" onchange="this.form.submit()">
    <span style="font-size:.8rem;color:#64748b;">
      Total: <strong style="color:#15803d;"><?php echo e(number_format($totalDia,1)); ?> L</strong>
    </span>
    <?php if($totalDia > 0): ?>
    <button type="button" onclick="openModal('modalVenta')"
            class="btn btn-sm btn-primary" style="margin-left:auto;">
      💰 Vender producción
    </button>
    <?php endif; ?>
  </form>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐄 Registro por vaca</div>
    <button onclick="openModal('modalOrdeno')" class="btn btn-sm btn-primary">+ Individual</button>
  </div>

  <?php if($vacasProductoras->isEmpty()): ?>
    <div style="text-align:center;padding:24px;color:#64748b;">
      <div style="font-size:2rem;margin-bottom:8px;">🐄</div>
      <p style="margin-bottom:12px;">No hay vacas con lactancia activa.</p>
      <button onclick="openModal('modalLactancia')" class="btn btn-sm btn-primary">
        🐄 Iniciar lactancia
      </button>
    </div>
  <?php else: ?>
  <div class="ordeno-tabla">
    <div class="ordeno-header">
      <span>Vaca</span><span>AM 🌅</span><span>PM 🌆</span><span>Total</span>
    </div>
    <?php $__currentLoopData = $vacasProductoras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vaca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $am = null; $pm = null;
      if (isset($ordenosDelDia[$vaca->id])) {
          foreach ($ordenosDelDia[$vaca->id] as $o) {
              if ($o->sesion === 'am') $am = $o;
              if ($o->sesion === 'pm') $pm = $o;
          }
      }
      $totalVaca = ($am->litros ?? 0) + ($pm->litros ?? 0);
    ?>
    <div class="ordeno-row">
      <div class="ordeno-nombre">
        <?php echo e($vaca->nombre_lote); ?>

        <?php if($vaca->raza): ?><div style="font-size:.7rem;color:#94a3b8;"><?php echo e($vaca->raza); ?></div><?php endif; ?>
      </div>
      <div class="ordeno-celda">
        <?php if($am): ?>
          <span class="ordeno-litros-ok"><?php echo e($am->litros); ?> L</span>
          <form method="POST" action="<?php echo e(route('bovino.ordenos.delete',$am->id)); ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
            <?php echo csrf_field(); ?> <button class="btn-icon-del" type="submit">✕</button>
          </form>
        <?php else: ?>
          <button onclick="openOrdenoRapido(<?php echo e($vaca->id); ?>,'<?php echo e(addslashes($vaca->nombre_lote)); ?>','am','<?php echo e($fecha); ?>')"
                  class="btn-ordeno-add" title="Registrar AM">＋</button>
        <?php endif; ?>
      </div>
      <div class="ordeno-celda">
        <?php if($pm): ?>
          <span class="ordeno-litros-ok"><?php echo e($pm->litros); ?> L</span>
          <form method="POST" action="<?php echo e(route('bovino.ordenos.delete',$pm->id)); ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar?')">
            <?php echo csrf_field(); ?> <button class="btn-icon-del" type="submit">✕</button>
          </form>
        <?php else: ?>
          <button onclick="openOrdenoRapido(<?php echo e($vaca->id); ?>,'<?php echo e(addslashes($vaca->nombre_lote)); ?>','pm','<?php echo e($fecha); ?>')"
                  class="btn-ordeno-add" title="Registrar PM">＋</button>
        <?php endif; ?>
      </div>
      <div class="ordeno-total"><?php echo e($totalVaca > 0 ? number_format($totalVaca,1).' L' : '—'); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php endif; ?>
</div>


<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">📈 Producción últimos 30 días</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartOrdenos"></canvas>
  </div>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐄 Gestionar lactancias</div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;">
    <button onclick="openModal('modalLactancia')" class="btn btn-sm btn-secondary">
      ＋ Iniciar lactancia
    </button>
    <?php if($vacasProductoras->count()): ?>
    <button onclick="openModal('modalSecar')" class="btn btn-sm btn-ghost">
      💤 Secar vaca
    </button>
    <?php endif; ?>
  </div>
</div>

<div style="margin-bottom:80px;"></div>




<div id="modalOrdeno" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🥛 Registrar ordeño</div>
    <form method="POST" action="<?php echo e(route('bovino.ordenos.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Vaca *</label>
        <select name="animal_id" id="selectVacaModal" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $vacasProductoras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($v->id); ?>"><?php echo e($v->nombre_lote); ?><?php echo e($v->raza ? ' ('.$v->raza.')' : ''); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" id="inputFechaModal" class="form-control" value="<?php echo e($fecha); ?>" required>
        </div>
        <div class="form-group">
          <label>Sesión *</label>
          <select name="sesion" id="selectSesionModal" class="form-control" required>
            <option value="am">🌅 AM (mañana)</option>
            <option value="pm">🌆 PM (tarde)</option>
            <option value="unica">🕐 Única</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Litros *</label>
          <input type="number" name="litros" class="form-control" step="0.1" min="0" placeholder="0.0" required>
        </div>
        <div class="form-group">
          <label>Temp. leche (°C)</label>
          <input type="number" name="temperatura_leche" class="form-control" step="0.1" placeholder="Ej: 38.5">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Calostro, mastitis, etc.">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalOrdeno')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalLactancia" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🐄 Iniciar lactancia</div>
    <form method="POST" action="<?php echo e(route('bovino.ordenos.lactancia')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Vaca *</label>
        <select name="animal_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__empty_1 = true; $__currentLoopData = $todasBovinas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
          <option value="<?php echo e($b->id); ?>">
            <?php echo e($b->nombre_lote); ?><?php echo e($b->raza ? ' ('.$b->raza.')' : ''); ?>

            <?php if($b->categoria_bovina): ?> · <?php echo e(str_replace('_',' ',$b->categoria_bovina)); ?> <?php endif; ?>
          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
          <option value="" disabled>Sin bovinos registrados — ve a Animales primero</option>
          <?php endif; ?>
        </select>
        <?php if($todasBovinas->isEmpty()): ?>
        <div style="font-size:.75rem;color:#ef4444;margin-top:4px;">
          ⚠️ Registra al menos una vaca en el módulo de Animales antes de iniciar una lactancia.
        </div>
        <?php endif; ?>
      </div>
      <div class="form-group">
        <label>Fecha de inicio *</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Ej: Inicio después del parto #2..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalLactancia')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Iniciar lactancia</button>
      </div>
    </form>
  </div>
</div>


<div id="modalSecar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💤 Secar vaca</div>
    <p style="font-size:.85rem;color:#64748b;margin-bottom:14px;">
      Cierra la lactancia activa de la vaca seleccionada y la retira del registro de ordeños.
    </p>
    <form id="formSecar" method="POST" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Vaca *</label>
        <select id="selectVacaSecar" class="form-control" required onchange="setLactanciaSecar(this)">
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $vacasProductoras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($v->lactancia_id); ?>" data-lid="<?php echo e($v->lactancia_id); ?>">
            <?php echo e($v->nombre_lote); ?><?php echo e($v->raza ? ' ('.$v->raza.')' : ''); ?>

          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Fecha de secado *</label>
        <input type="date" name="fecha_secado" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalSecar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Secar vaca</button>
      </div>
    </form>
  </div>
</div>


<div id="modalVenta" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💰 Vender producción de leche</div>
    <form method="POST" action="<?php echo e(route('bovino.produccion.vender')); ?>">
      <?php echo csrf_field(); ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de venta *</label>
          <select name="tipo_venta" class="form-control" required>
            <option value="diaria">📅 Diaria</option>
            <option value="mensual">🗓️ Mensual (acumulado)</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e($fecha); ?>" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Litros *</label>
          <input type="number" name="litros" id="inputLitrosVenta"
                 class="form-control" step="0.1" min="0.01"
                 value="<?php echo e($totalDia); ?>" required oninput="calcTotalVenta()">
        </div>
        <div class="form-group">
          <label>Precio / litro (COP) *</label>
          <input type="number" name="precio_litro" id="inputPrecioLitro"
                 class="form-control" step="50" min="0"
                 placeholder="Ej: 1200" required oninput="calcTotalVenta()">
        </div>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:10px;text-align:center;margin-bottom:12px;">
        <div style="font-size:.72rem;color:#64748b;">Total a cobrar</div>
        <div id="totalVentaDisplay" style="font-size:1.4rem;font-weight:800;color:#15803d;">$0</div>
      </div>
      <?php if(isset($personas) && $personas->count()): ?>
      <div class="form-group">
        <label>Comprador (de tu lista)</label>
        <select name="persona_id" class="form-control">
          <option value="">— Sin asignar —</option>
          <?php $__currentLoopData = $personas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $per): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($per->id); ?>"><?php echo e($per->nombre); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="form-group">
        <label>Nombre del comprador (texto libre)</label>
        <input type="text" name="comprador" class="form-control"
               placeholder="Ej: Lácteos del Valle, Don Pedro...">
      </div>
      <div style="font-size:.78rem;color:#64748b;background:#eff6ff;
                  padding:8px 10px;border-radius:8px;margin-bottom:12px;">
        ✅ Se creará un <strong>Ingreso</strong> automáticamente en el módulo de Finanzas.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalVenta')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar venta</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'flex'; m.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'none'; m.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(m) {
  m.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});

function openOrdenoRapido(animalId, nombre, sesion, fecha) {
  var sel = document.getElementById('selectVacaModal');
  if (sel) sel.value = animalId;
  var ss  = document.getElementById('selectSesionModal');
  if (ss)  ss.value  = sesion;
  var fd  = document.getElementById('inputFechaModal');
  if (fd)  fd.value  = fecha;
  openModal('modalOrdeno');
}

function setLactanciaSecar(sel) {
  var lid = sel.options[sel.selectedIndex].getAttribute('data-lid');
  if (lid) document.getElementById('formSecar').action = '/bovino/ordenos/secar/' + lid;
}

function calcTotalVenta() {
  var litros = parseFloat(document.getElementById('inputLitrosVenta').value)  || 0;
  var precio = parseFloat(document.getElementById('inputPrecioLitro').value) || 0;
  var total  = Math.round(litros * precio);
  document.getElementById('totalVentaDisplay').textContent = '$' + total.toLocaleString('es-CO');
}

var ctx = document.getElementById('chartOrdenos');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [{
        label: 'Litros/día',
        data: <?php echo json_encode($chartData); ?>,
        borderColor: '#15803d', backgroundColor: 'rgba(21,128,61,.12)',
        borderWidth: 2, pointRadius: 2, fill: true, tension: 0.4,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font:{size:9}, maxTicksLimit:10 }, grid:{display:false} },
        y: { beginAtZero: true, ticks:{font:{size:10}} }
      }
    }
  });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/bovino/ordenos.blade.php ENDPATH**/ ?>