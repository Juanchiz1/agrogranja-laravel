
<?php $__env->startSection('title','Postura'); ?>
<?php $__env->startSection('page_title','🥚 Postura Diaria'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card" style="padding:12px 14px;">
  <form method="GET" action="<?php echo e(route('avicola.postura')); ?>" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <label style="font-size:.82rem;font-weight:600;color:#64748b;">Fecha</label>
    <input type="date" name="fecha" class="form-control" value="<?php echo e($fecha); ?>"
           style="max-width:160px;" onchange="this.form.submit()">
    <div style="margin-left:auto;text-align:right;">
      <div style="font-size:1.1rem;font-weight:800;color:#f59e0b;"><?php echo e(number_format($totalDia)); ?> huevos</div>
      <?php if($pctDia): ?>
      <div style="font-size:.75rem;color:#64748b;"><?php echo e(round($pctDia,1)); ?>% postura promedio</div>
      <?php endif; ?>
    </div>
    <button onclick="openModal('modalPostura')" type="button" class="btn btn-sm btn-primary">+ Registrar</button>
  </form>
</div>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐔 Registro por lote</div>
  <?php if($lotesPonedores->isEmpty()): ?>
  <div style="text-align:center;padding:20px;color:#64748b;">
    <div style="font-size:2rem;">🥚</div>
    <p>No hay lotes ponedores activos.<br>Registra gallinas en el módulo de Animales.</p>
  </div>
  <?php else: ?>
  <div class="postura-tabla">
    <div class="postura-header">
      <span>Lote</span><span>Huevos</span><span>% Postura</span><span>AA/A/B</span><span>Acc.</span>
    </div>
    <?php $__currentLoopData = $lotesPonedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $reg = $posturaDelDia[$l->id] ?? null; ?>
    <div class="postura-row" style="<?php echo e($reg ? 'background:#f0fdf4;' : ''); ?>">
      <div>
        <div style="font-weight:600;font-size:.85rem;"><?php echo e($l->nombre_lote); ?></div>
        <div style="font-size:.7rem;color:#94a3b8;"><?php echo e($l->cantidad); ?> aves</div>
      </div>
      <div style="text-align:center;">
        <?php if($reg): ?>
        <span style="font-weight:800;color:#f59e0b;"><?php echo e($reg->huevos_total); ?></span>
        <?php else: ?>
        <span style="color:#cbd5e1;">—</span>
        <?php endif; ?>
      </div>
      <div style="text-align:center;">
        <?php if($reg && $reg->porcentaje_postura): ?>
        <?php $pct = $reg->porcentaje_postura; ?>
        <span class="<?php echo e($pct >= 85 ? 'postura-pct-ok' : ($pct >= 70 ? 'postura-pct-med' : 'postura-pct-low')); ?>">
          <?php echo e(round($pct,1)); ?>%
        </span>
        <?php else: ?>
        <span style="color:#cbd5e1;">—</span>
        <?php endif; ?>
      </div>
      <div style="font-size:.72rem;color:#64748b;">
        <?php if($reg): ?>
        <?php echo e($reg->huevos_aa); ?>/<?php echo e($reg->huevos_a); ?>/<?php echo e($reg->huevos_b); ?>

        <?php else: ?> —
        <?php endif; ?>
      </div>
      <div>
        <?php if($reg): ?>
        <form method="POST" action="<?php echo e(route('avicola.postura.delete',$reg->id)); ?>" onsubmit="return confirm('¿Eliminar?')">
          <?php echo csrf_field(); ?> <button class="btn-icon-del" type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;font-size:.8rem;">✕</button>
        </form>
        <?php else: ?>
        <button onclick="openPosturaRapida(<?php echo e($l->id); ?>,'<?php echo e(addslashes($l->nombre_lote)); ?>',<?php echo e($l->cantidad); ?>,'<?php echo e($fecha); ?>')"
                style="background:none;border:1px solid #e2e8f0;border-radius:6px;padding:2px 8px;cursor:pointer;font-size:.8rem;color:#64748b;">
          + Reg
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php endif; ?>
</div>


<?php if($clasifMes && $clasifMes->total > 0): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📦 Clasificación mes actual</div>
  <div class="clasif-grid">
    <div class="clasif-card clasif-aa">
      <div class="clasif-val" style="color:#15803d;"><?php echo e(number_format($clasifMes->aa ?? 0)); ?></div>
      <div class="clasif-lbl">Huevos AA<br>Extra grandes</div>
    </div>
    <div class="clasif-card clasif-a">
      <div class="clasif-val" style="color:#2563eb;"><?php echo e(number_format($clasifMes->a_cls ?? 0)); ?></div>
      <div class="clasif-lbl">Huevos A<br>Grandes</div>
    </div>
    <div class="clasif-card clasif-b">
      <div class="clasif-val" style="color:#b45309;"><?php echo e(number_format($clasifMes->b_cls ?? 0)); ?></div>
      <div class="clasif-lbl">Huevos B<br>Medianos</div>
    </div>
    <div class="clasif-card clasif-sucio">
      <div class="clasif-val" style="color:#dc2626;"><?php echo e(number_format($clasifMes->sucios ?? 0)); ?></div>
      <div class="clasif-lbl">Sucios</div>
    </div>
    <div class="clasif-card clasif-roto">
      <div class="clasif-val" style="color:#7e22ce;"><?php echo e(number_format($clasifMes->rotos ?? 0)); ?></div>
      <div class="clasif-lbl">Rotos</div>
    </div>
  </div>
  <?php $totalMes = $clasifMes->total ?? 0; ?>
  <?php if($totalMes > 0): ?>
  <div style="font-size:.78rem;color:#64748b;text-align:center;margin-top:6px;">
    Total mes: <strong><?php echo e(number_format($totalMes)); ?></strong> huevos ·
    Calidad: <strong style="color:#15803d;"><?php echo e(round((($clasifMes->aa + $clasifMes->a_cls) / $totalMes) * 100, 1)); ?>%</strong> AA+A
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php if(count($chartLabels) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📈 Curva de postura — 30 días</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartPostura"></canvas>
  </div>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalPostura" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🥚 Registrar postura</div>
    <form method="POST" action="<?php echo e(route('avicola.postura.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Lote *</label>
        <select name="animal_id" id="selectLotePostura" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $lotesPonedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($l->id); ?>" data-aves="<?php echo e($l->cantidad); ?>">
            <?php echo e($l->nombre_lote); ?> (<?php echo e($l->cantidad); ?> aves)
          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" id="fechaPostura" class="form-control" value="<?php echo e($fecha); ?>" required>
        </div>
        <div class="form-group">
          <label>Aves presentes</label>
          <input type="number" name="aves_presentes" id="avesPresentes" class="form-control"
                 placeholder="Del lote" min="0">
        </div>
      </div>
      <div class="form-group">
        <label>Total huevos *</label>
        <input type="number" name="huevos_total" id="huevosTotal" class="form-control"
               min="0" required placeholder="0" oninput="calcPct()">
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:8px 12px;margin-bottom:10px;font-size:.82rem;">
        % Postura: <strong id="pctCalculado">—</strong>
      </div>
      
      <div style="background:#f8fafc;border-radius:10px;padding:10px;margin-bottom:10px;">
        <div style="font-size:.78rem;font-weight:700;color:#64748b;margin-bottom:8px;">
          📦 Clasificación (opcional)
        </div>
        <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:6px;">
          <div class="form-group">
            <label style="font-size:.7rem;color:#15803d;font-weight:700;">AA</label>
            <input type="number" name="huevos_aa" class="form-control" min="0" value="0" style="text-align:center;">
          </div>
          <div class="form-group">
            <label style="font-size:.7rem;color:#2563eb;font-weight:700;">A</label>
            <input type="number" name="huevos_a" class="form-control" min="0" value="0" style="text-align:center;">
          </div>
          <div class="form-group">
            <label style="font-size:.7rem;color:#b45309;font-weight:700;">B</label>
            <input type="number" name="huevos_b" class="form-control" min="0" value="0" style="text-align:center;">
          </div>
          <div class="form-group">
            <label style="font-size:.7rem;color:#dc2626;font-weight:700;">Sucios</label>
            <input type="number" name="huevos_sucios" class="form-control" min="0" value="0" style="text-align:center;">
          </div>
          <div class="form-group">
            <label style="font-size:.7rem;color:#7e22ce;font-weight:700;">Rotos</label>
            <input type="number" name="huevos_rotos" class="form-control" min="0" value="0" style="text-align:center;">
          </div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Alimento (kg)</label>
          <input type="number" name="alimento_kg" class="form-control" step="0.1" min="0" placeholder="0.0">
        </div>
        <div class="form-group">
          <label>Agua (litros)</label>
          <input type="number" name="agua_litros" class="form-control" step="0.1" min="0" placeholder="0.0">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Ej: Aves estresadas por calor">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalPostura')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

document.getElementById('selectLotePostura').addEventListener('change', function() {
  var aves = this.options[this.selectedIndex].getAttribute('data-aves');
  document.getElementById('avesPresentes').value = aves || '';
  calcPct();
});
function calcPct() {
  var huevos = parseInt(document.getElementById('huevosTotal').value) || 0;
  var aves   = parseInt(document.getElementById('avesPresentes').value) || 0;
  document.getElementById('pctCalculado').textContent = aves > 0 ? Math.round((huevos/aves)*100*10)/10 + '%' : '—';
}
function openPosturaRapida(id, nombre, aves, fecha) {
  document.getElementById('selectLotePostura').value = id;
  document.getElementById('avesPresentes').value = aves;
  document.getElementById('fechaPostura').value = fecha;
  openModal('modalPostura');
}

var ctx = document.getElementById('chartPostura');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [
        { label:'Huevos/día', data:<?php echo json_encode($chartHuevos); ?>,
          borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.12)',
          borderWidth:2, pointRadius:2, fill:true, tension:0.4, yAxisID:'y' },
        { label:'% Postura', data:<?php echo json_encode($chartPct); ?>,
          borderColor:'#16a34a', backgroundColor:'transparent',
          borderWidth:1.5, pointRadius:1, borderDash:[4,4], yAxisID:'y2' }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{font:{size:10}} } },
      scales: {
        x: { ticks:{font:{size:9},maxTicksLimit:12}, grid:{display:false} },
        y: { beginAtZero:true, position:'left', ticks:{font:{size:9}} },
        y2:{ beginAtZero:true, position:'right', max:100, ticks:{font:{size:9}}, grid:{display:false} }
      }
    }
  });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/postura.blade.php ENDPATH**/ ?>