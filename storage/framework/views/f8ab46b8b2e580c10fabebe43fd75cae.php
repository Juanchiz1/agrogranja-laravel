
<?php $__env->startSection('title','Rentabilidad'); ?>
<?php $__env->startSection('page_title','Rentabilidad por Cultivo'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="flex items-center gap-2 mb-3" style="flex-wrap:wrap;">
  <a href="?anio=<?php echo e($anio-1); ?>&orden=<?php echo e($orden); ?>" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;"><?php echo e($anio); ?></span>
  <a href="?anio=<?php echo e($anio+1); ?>&orden=<?php echo e($orden); ?>" class="btn btn-sm btn-secondary btn-icon">›</a>
  <select onchange="location.href='?anio=<?php echo e($anio); ?>&orden='+this.value" class="form-control" style="width:140px;">
    <?php $__currentLoopData = ['rentabilidad'=>'Mejor balance','ingresos'=>'Mayor ingreso','gastos'=>'Mayor gasto','nombre'=>'Nombre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($val); ?>" <?php echo e($orden === $val ? 'selected' : ''); ?>><?php echo e($lbl); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
</div>


<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:14px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1rem;">$<?php echo e(number_format($totalIngresos/1000,0)); ?>k</div>
    <div class="stat-label">Total ingresos</div>
  </div>
  <div class="stat-card" style="background:var(--marron-bg);">
    <div class="stat-value text-brown" style="font-size:1rem;">$<?php echo e(number_format($totalGastos/1000,0)); ?>k</div>
    <div class="stat-label">Total gastos</div>
  </div>
  <div class="stat-card" style="background:<?php echo e($totalBalance >= 0 ? 'var(--verde-bg)' : '#fef2f2'); ?>;">
    <div class="stat-value" style="font-size:1rem;color:<?php echo e($totalBalance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">
      $<?php echo e(number_format(abs($totalBalance)/1000,0)); ?>k
    </div>
    <div class="stat-label">Balance <?php echo e($totalBalance >= 0 ? '▲' : '▼'); ?></div>
  </div>
</div>


<?php if($mejorCultivo || $peorCultivo): ?>
<div class="grid-2 mb-3">
  <?php if($mejorCultivo && $mejorCultivo->rentabilidad > 0): ?>
  <div class="card" style="background:var(--verde-bg);border-left:3px solid var(--verde-dark);">
    <p class="text-xs font-bold text-gray" style="text-transform:uppercase;margin-bottom:6px;">⭐ Mejor cultivo</p>
    <p class="font-bold"><?php echo e($mejorCultivo->nombre); ?></p>
    <p class="text-green font-bold" style="font-size:1.1rem;">$<?php echo e(number_format($mejorCultivo->rentabilidad,0,',','.')); ?></p>
    <p class="text-xs text-gray">ROI: <?php echo e($mejorCultivo->roi); ?>%</p>
  </div>
  <?php endif; ?>
  <?php if($peorCultivo && $peorCultivo->rentabilidad < 0): ?>
  <div class="card" style="background:#fef2f2;border-left:3px solid var(--rojo);">
    <p class="text-xs font-bold text-gray" style="text-transform:uppercase;margin-bottom:6px;">⚠️ Atención</p>
    <p class="font-bold"><?php echo e($peorCultivo->nombre); ?></p>
    <p style="color:var(--rojo);font-weight:700;font-size:1.1rem;">$<?php echo e(number_format($peorCultivo->rentabilidad,0,',','.')); ?></p>
    <p class="text-xs text-gray">ROI: <?php echo e($peorCultivo->roi); ?>%</p>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<div class="card mb-3">
  <p class="font-bold mb-3">Ingresos vs Gastos por cultivo</p>
  <canvas id="chartComparativo" height="220"></canvas>
</div>


<?php if($datos->isEmpty()): ?>
<div class="empty-state">
  <div class="emoji">🌱</div>
  <p>No hay cultivos con datos para <?php echo e($anio); ?>.</p>
</div>
<?php else: ?>
<?php $__currentLoopData = $datos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $positivo = $d->rentabilidad >= 0;
  $maxVal   = max($d->gastos, $d->ingresos, 1);
  $pctG     = round($d->gastos / $maxVal * 100);
  $pctI     = round($d->ingresos / $maxVal * 100);
  $estadoBadge = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$d->estado] ?? 'badge-green';
?>

<div class="card mb-2" style="border-left:3px solid <?php echo e($positivo ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">

  
  <div class="flex items-center justify-between mb-3">
    <div class="flex items-center gap-2">
      <span style="font-size:1.2rem;">🌿</span>
      <div>
        <div class="font-bold"><?php echo e($d->nombre); ?></div>
        <div class="text-xs text-gray"><?php echo e($d->tipo); ?><?php echo e($d->area ? ' · '.$d->area.' '.$d->unidad : ''); ?></div>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="badge <?php echo e($estadoBadge); ?>"><?php echo e($d->estado); ?></span>
      <a href="<?php echo e(route('rentabilidad.detalle', $d->id)); ?>?anio=<?php echo e($anio); ?>"
         class="btn btn-sm btn-secondary">Ver detalle →</a>
    </div>
  </div>

  
  <div style="margin-bottom:10px;">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs text-gray">💰 Gastos</span>
      <span class="text-xs font-bold text-brown">$<?php echo e(number_format($d->gastos,0,',','.')); ?></span>
    </div>
    <div class="progress-bar" style="height:8px;">
      <div class="progress-fill" style="width:<?php echo e($pctG); ?>%;background:var(--marron-light);"></div>
    </div>

    <div class="flex items-center justify-between mt-2 mb-2">
      <span class="text-xs text-gray">📈 Ingresos</span>
      <span class="text-xs font-bold text-green">$<?php echo e(number_format($d->ingresos,0,',','.')); ?></span>
    </div>
    <div class="progress-bar" style="height:8px;">
      <div class="progress-fill" style="width:<?php echo e($pctI); ?>%;background:var(--verde-dark);"></div>
    </div>
  </div>

  
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px;background:var(--gris-light);border-radius:10px;">
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:<?php echo e($positivo ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">
        <?php echo e($positivo ? '+' : ''); ?>$<?php echo e(number_format($d->rentabilidad,0,',','.')); ?>

      </div>
      <div class="text-xs text-gray">Balance</div>
    </div>
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:<?php echo e($d->roi >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">
        <?php echo e($d->roi); ?>%
      </div>
      <div class="text-xs text-gray">ROI</div>
    </div>
    <div style="text-align:center;">
      <div class="font-bold" style="font-size:.95rem;color:var(--gris);">
        <?php echo e($d->margen); ?>%
      </div>
      <div class="text-xs text-gray">Margen</div>
    </div>
  </div>

  
  <?php if($d->gastosPorCategoria->count()): ?>
  <div style="margin-top:10px;">
    <p class="text-xs text-gray font-bold" style="margin-bottom:6px;">Principales gastos:</p>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <?php $__currentLoopData = $d->gastosPorCategoria->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <span style="background:#fff;border:1px solid var(--gris-border);border-radius:20px;padding:2px 9px;font-size:.72rem;">
        <?php echo e($cat->categoria); ?>: $<?php echo e(number_format($cat->total/1000,0)); ?>k
      </span>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
  <?php endif; ?>

</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';

const labels   = <?php echo json_encode($chartLabels, 15, 512) ?>;
const gastos   = <?php echo json_encode($chartGastos, 15, 512) ?>;
const ingresos = <?php echo json_encode($chartIngresos, 15, 512) ?>;

// Acortar etiquetas largas
const labelsCortos = labels.map(l => l.length > 12 ? l.substring(0,12)+'…' : l);

new Chart(document.getElementById('chartComparativo'), {
  type: 'bar',
  data: {
    labels: labelsCortos,
    datasets: [
      {
        label: 'Ingresos',
        data: ingresos,
        backgroundColor: 'rgba(61,139,61,.75)',
        borderRadius: 5,
        borderSkipped: false,
      },
      {
        label: 'Gastos',
        data: gastos,
        backgroundColor: 'rgba(122,79,42,.65)',
        borderRadius: 5,
        borderSkipped: false,
      },
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' },
      tooltip: {
        callbacks: {
          label: ctx => ' $' + ctx.raw.toLocaleString('es-CO')
        }
      }
    },
    scales: {
      y: {
        ticks: { callback: v => '$' + (v/1000).toFixed(0) + 'k' }
      }
    }
  }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/rentabilidad.blade.php ENDPATH**/ ?>