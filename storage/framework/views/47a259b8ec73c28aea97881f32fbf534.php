
<?php $__env->startSection('title','Reportes Piscicola'); ?>
<?php $__env->startSection('page_title','Reportes Piscicola'); ?>
<?php $__env->startSection('back_url', route('piscicola.estanques')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/piscicola.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if($totales && $totales->cosechas > 0): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">Totales historicos</div>
  <div class="pisc-stats">
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#9937;</div>
      <div class="pisc-stat-val"><?php echo e($totales->cosechas); ?></div>
      <div class="pisc-stat-lbl">Cosechas</div>
    </div>
    <div class="pisc-stat verde">
      <div class="pisc-stat-ico">&#128031;</div>
      <div class="pisc-stat-val"><?php echo e(round($totales->kg_total ?? 0, 1)); ?></div>
      <div class="pisc-stat-lbl">kg producidos</div>
    </div>
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#128200;</div>
      <div class="pisc-stat-val"><?php echo e(round($totales->ca_promedio ?? 0, 2)); ?></div>
      <div class="pisc-stat-lbl">CA promedio</div>
    </div>
    <div class="pisc-stat">
      <div class="pisc-stat-ico">&#128077;</div>
      <div class="pisc-stat-val"><?php echo e(round($totales->sobrev_promedio ?? 0, 1)); ?>%</div>
      <div class="pisc-stat-lbl">Sobrevivencia</div>
    </div>
  </div>
  <?php if($totales->ingresos_total): ?>
  <div style="background:var(--pisc-bg);border-radius:10px;padding:10px;text-align:center;margin-top:8px;">
    <div style="font-size:.75rem;color:var(--pisc-gris);">Ingresos totales</div>
    <div style="font-size:1.4rem;font-weight:800;color:var(--pisc-verde);">
      $<?php echo e(number_format($totales->ingresos_total, 0, ',', '.')); ?>

    </div>
    <?php if($totales->rendimiento_kg_m2): ?>
    <div style="font-size:.75rem;color:#64748b;">
      Rendimiento promedio: <?php echo e(round($totales->rendimiento_kg_m2, 2)); ?> kg/m²
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php if($siembrasActivas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Siembras activas</div>
  <?php $__currentLoopData = $siembrasActivas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $biomasaS  = round((float)($s->biomasa_actual_kg ?? 0), 1);
    $pesoPromS = round((float)($s->peso_promedio_actual_g ?? 0), 0);
    $sobrevS   = round((float)($s->sobrevivencia), 1);
    $tasaCrecS = $s->ultimo_muestreo ? $s->ultimo_muestreo->ganancia_diaria_g : null;
    $aliAcumS  = round((float)($s->alimento_acumulado_kg ?? 0), 1);
    $rendS     = ($s->area_m2 && $s->area_m2 > 0 && $biomasaS > 0)
                 ? round($biomasaS / $s->area_m2, 2) : null;
  ?>
  <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.9rem;"><?php echo e($s->nombre_estanque); ?></div>
        <div style="font-size:.73rem;color:#64748b;">
          <?php echo e($s->especie_cultivada); ?> · Dia <?php echo e($s->dias_cultivo); ?> · Sembrado <?php echo e($s->cantidad_alevinos); ?> alevinos
        </div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:800;color:var(--pisc-azul);"><?php echo e($biomasaS); ?> kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">biomasa est.</div>
      </div>
    </div>
    <div style="display:flex;gap:12px;flex-wrap:wrap;font-size:.74rem;color:#64748b;margin-top:6px;">
      <?php if($pesoPromS > 0): ?><span>Peso: <strong><?php echo e($pesoPromS); ?> g</strong></span><?php endif; ?>
      <span>Sobreviv: <strong style="color:<?php echo e($sobrevS >= 90 ? '#15803d' : '#d97706'); ?>;"><?php echo e($sobrevS); ?>%</strong></span>
      <?php if($tasaCrecS !== null): ?><span>Crec: <strong><?php echo e($tasaCrecS); ?> g/dia</strong></span><?php endif; ?>
      <span>Alimento acum: <strong><?php echo e($aliAcumS); ?> kg</strong></span>
      <?php if($rendS !== null): ?><span>Rend: <strong><?php echo e($rendS); ?> kg/m²</strong></span><?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($mejoresCosechas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Mejores cosechas (por CA)</div>
  <?php $__currentLoopData = $mejoresCosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $cCA2    = $c->conversion_alimenticia;
    $caColor2 = $cCA2 <= 1.5 ? '#15803d' : ($cCA2 <= 2.0 ? '#d97706' : '#1e293b');
    $cFecha2 = \Carbon\Carbon::parse($c->fecha)->format('d/m/Y');
  ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:600;"><?php echo e($c->nombre_estanque); ?></div>
      <div style="font-size:.72rem;color:#64748b;">
        <?php echo e($cFecha2); ?> · <?php echo e($c->biomasa_cosechada_kg); ?> kg
        <?php if($c->sobrevivencia): ?> · Sobreviv: <?php echo e($c->sobrevivencia); ?>%<?php endif; ?>
      </div>
    </div>
    <div style="font-size:1.1rem;font-weight:800;color:<?php echo e($caColor2); ?>;">
      CA <?php echo e($cCA2); ?>

    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($alertasCalidad->count()): ?>
<div class="section-card">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    Alertas calidad del agua (15 dias)
  </div>
  <?php $__currentLoopData = $alertasCalidad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $al): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $alFecha = \Carbon\Carbon::parse($al->fecha)->format('d/m/Y');
  ?>
  <div class="alerta-pisc critica">
    <span>&#9888;</span>
    <div>
      <strong><?php echo e($al->nombre_estanque); ?></strong> — <?php echo e($alFecha); ?>

      <div style="font-size:.72rem;margin-top:2px;">
        <?php if($al->oxigeno_mgl !== null): ?> O2: <?php echo e($al->oxigeno_mgl); ?> mg/L <?php endif; ?>
        <?php if($al->ph !== null): ?> pH: <?php echo e($al->ph); ?> <?php endif; ?>
        <?php if($al->temperatura_c !== null): ?> Temp: <?php echo e($al->temperatura_c); ?>°C <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if(count($chartMeses) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Produccion mensual (kg)</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartProdMes"></canvas>
  </div>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
var ctxProd = document.getElementById('chartProdMes');
if (ctxProd) {
  new Chart(ctxProd, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($chartMeses); ?>,
      datasets: [{
        label: 'kg cosechados',
        data: <?php echo json_encode($chartKg); ?>,
        backgroundColor: 'rgba(2,132,199,.7)',
        borderColor: '#0284c7', borderWidth: 1,
        borderRadius: 4
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { beginAtZero: true, ticks: { font: { size: 9 } } }
      }
    }
  });
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/piscicola/reportes.blade.php ENDPATH**/ ?>