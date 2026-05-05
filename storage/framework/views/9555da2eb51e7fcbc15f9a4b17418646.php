
<?php $__env->startSection('title','Galpón Avícola'); ?>
<?php $__env->startSection('page_title','🐔 Galpón Avícola'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="galpon-stats">
  <div class="galpon-stat">
    <div class="galpon-stat-ico">🐔</div>
    <div class="galpon-stat-val"><?php echo e(number_format($totalAves)); ?></div>
    <div class="galpon-stat-lbl">Aves activas</div>
  </div>
  <div class="galpon-stat verde">
    <div class="galpon-stat-ico">🥚</div>
    <div class="galpon-stat-val"><?php echo e(number_format($produccionHoy)); ?></div>
    <div class="galpon-stat-lbl">Huevos hoy</div>
  </div>
  <div class="galpon-stat">
    <div class="galpon-stat-ico">📊</div>
    <div class="galpon-stat-val"><?php echo e($posturaHoy ? round($posturaHoy,1).'%' : 'N/R'); ?></div>
    <div class="galpon-stat-lbl">% postura</div>
  </div>
  <div class="galpon-stat rojo">
    <div class="galpon-stat-ico">💀</div>
    <div class="galpon-stat-val"><?php echo e($mortSemana); ?></div>
    <div class="galpon-stat-lbl">Muertes 7d</div>
  </div>
</div>


<?php if($alertasVacunas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🔔 Alertas de vacunación</div>
  <?php $__currentLoopData = $alertasVacunas->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $diasR = now()->diffInDays($v->fecha_programada, false); ?>
  <div class="alerta-avi <?php echo e($diasR < 0 ? 'vencida' : 'proxima'); ?>">
    <span><?php echo e($diasR < 0 ? '❌' : '⚠️'); ?></span>
    <div>
      <strong><?php echo e($v->nombre_vacuna); ?></strong>
      <?php if($v->nombre_lote): ?><span style="color:#64748b;"> · <?php echo e($v->nombre_lote); ?></span><?php endif; ?>
      <br><span style="font-size:.74rem;">
        <?php echo e(\Carbon\Carbon::parse($v->fecha_programada)->format('d/m/Y')); ?>

        <?php if($diasR >= 0): ?>(en <?php echo e($diasR); ?> días)<?php endif; ?>
      </span>
    </div>
    <a href="<?php echo e(route('avicola.vacunacion')); ?>" style="margin-left:auto;font-size:.74rem;">Ver →</a>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="avicola-menu-grid">
    <a href="<?php echo e(route('avicola.postura')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">🥚</div>
      <div class="avicola-menu-lbl">Postura</div>
      <div class="avicola-menu-sub">Registro diario</div>
    </a>
    <a href="<?php echo e(route('avicola.engorde')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">🍗</div>
      <div class="avicola-menu-lbl">Engorde</div>
      <div class="avicola-menu-sub">Pesos semanales</div>
    </a>
    <a href="<?php echo e(route('avicola.mortalidad')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">💀</div>
      <div class="avicola-menu-lbl">Mortalidad</div>
      <div class="avicola-menu-sub">Causas y bajas</div>
    </a>
    <a href="<?php echo e(route('avicola.vacunacion')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">💉</div>
      <div class="avicola-menu-lbl">Vacunación</div>
      <div class="avicola-menu-sub">Calendario</div>
    </a>
    <a href="<?php echo e(route('avicola.conversion')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">🌾</div>
      <div class="avicola-menu-lbl">Conversión</div>
      <div class="avicola-menu-sub">CA alimenticia</div>
    </a>
    <a href="<?php echo e(route('avicola.reportes')); ?>" class="avicola-menu-card">
      <div class="avicola-menu-ico">📈</div>
      <div class="avicola-menu-lbl">Reportes</div>
      <div class="avicola-menu-sub">Análisis</div>
    </a>
  </div>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐣 Lotes activos (<?php echo e($totalLotes); ?>)</div>
    <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-ghost">+ Nuevo lote</a>
  </div>
  <?php $__empty_1 = true; $__currentLoopData = $lotesConEtapa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lote): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $etapaClass = ['cria'=>'cria','levante'=>'levante','postura_produccion'=>'postura','desconocida'=>''][$lote->etapa] ?? '';
    $etapaLabel = ['cria'=>'Cría 0-6sem','levante'=>'Levante 7-18sem','postura_produccion'=>'Postura/Prod.','desconocida'=>'Sin fecha'][$lote->etapa] ?? '';
  ?>
  <div class="lote-card <?php echo e($etapaClass); ?>" style="margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="lote-nombre">🐔 <?php echo e($lote->nombre_lote); ?></div>
        <div class="lote-sub">
          <?php echo e($lote->especie); ?>

          <?php if($lote->tipo_ave): ?> · <?php echo e(str_replace('_',' ',$lote->tipo_ave)); ?><?php endif; ?>
          <?php if($lote->linea_ave): ?> · <?php echo e($lote->linea_ave); ?><?php endif; ?>
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:1.15rem;font-weight:800;color:#ea580c;"><?php echo e(number_format($lote->cantidad)); ?></div>
        <div style="font-size:.68rem;color:#94a3b8;">aves</div>
      </div>
    </div>
    <div style="display:flex;gap:6px;margin-top:8px;align-items:center;flex-wrap:wrap;">
      <?php if($etapaLabel): ?>
      <span class="etapa-badge etapa-<?php echo e($etapaClass); ?>"><?php echo e($etapaLabel); ?></span>
      <?php endif; ?>
      <?php if($lote->semanas !== null): ?>
      <span style="font-size:.72rem;color:#64748b;">Semana <?php echo e($lote->semanas); ?></span>
      <?php endif; ?>
      <a href="<?php echo e(route('avicola.postura')); ?>" class="btn btn-sm btn-ghost" style="margin-left:auto;font-size:.72rem;padding:3px 8px;">
        🥚 Postura
      </a>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#64748b;">
    <div style="font-size:2rem;">🐔</div>
    <p style="margin-bottom:12px;">No hay lotes avícolas activos.</p>
    <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-primary">Registrar lote en Animales</a>
  </div>
  <?php endif; ?>
</div>


<?php if(count($chartLabels) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📈 Producción últimos 30 días</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartProduccion"></canvas>
  </div>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
var ctx = document.getElementById('chartProduccion');
if (ctx) {
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [
        { label: 'Huevos/día', data: <?php echo json_encode($chartHuevos); ?>,
          borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.12)',
          borderWidth:2, pointRadius:2, fill:true, tension:0.4, yAxisID:'y' },
        { label:'% Postura', data: <?php echo json_encode($chartPct); ?>,
          borderColor:'#16a34a', backgroundColor:'transparent',
          borderWidth:1.5, pointRadius:1, borderDash:[4,4], yAxisID:'y2' }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{ font:{size:10} } } },
      scales: {
        x:  { ticks:{font:{size:9},maxTicksLimit:10}, grid:{display:false} },
        y:  { beginAtZero:true, position:'left',  ticks:{font:{size:9}} },
        y2: { beginAtZero:true, position:'right', max:100,
              ticks:{font:{size:9}}, grid:{display:false} }
      }
    }
  });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/galpon.blade.php ENDPATH**/ ?>