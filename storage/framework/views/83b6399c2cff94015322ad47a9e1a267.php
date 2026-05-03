
<?php $__env->startSection('title','Hato Bovino'); ?>
<?php $__env->startSection('page_title','🐄 Mi Hato Bovino'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bovino.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="hato-stats">
  <div class="hato-stat">
    <div class="hato-stat-ico">🐄</div>
    <div class="hato-stat-val"><?php echo e($totalBovinos); ?></div>
    <div class="hato-stat-lbl">Total bovinos</div>
  </div>
  <div class="hato-stat" style="border-left-color:#22c55e;">
    <div class="hato-stat-ico">🥛</div>
    <div class="hato-stat-val" style="color:#15803d;"><?php echo e($vacasProduccion); ?></div>
    <div class="hato-stat-lbl">En producción</div>
  </div>
  <div class="hato-stat" style="border-left-color:#94a3b8;">
    <div class="hato-stat-ico">💤</div>
    <div class="hato-stat-val" style="color:#64748b;"><?php echo e($vacasSecas); ?></div>
    <div class="hato-stat-lbl">Secas</div>
  </div>
  <div class="hato-stat" style="border-left-color:#3b82f6;">
    <div class="hato-stat-ico">🤰</div>
    <div class="hato-stat-val" style="color:#1d4ed8;"><?php echo e($enGestacion); ?></div>
    <div class="hato-stat-lbl">En gestación</div>
  </div>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🥛 Producción hoy</div>
    <a href="<?php echo e(route('bovino.ordenos')); ?>" class="btn btn-sm btn-primary">Registrar ordeño</a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;">
    <div class="prod-card">
      <div class="prod-val"><?php echo e(number_format($litrosHoy,1)); ?> L</div>
      <div class="prod-lbl">Litros hoy</div>
    </div>
    <div class="prod-card">
      <div class="prod-val"><?php echo e(number_format($litrosAyer,1)); ?> L</div>
      <div class="prod-lbl">Litros ayer</div>
    </div>
    <div class="prod-card">
      <div class="prod-val"><?php echo e(number_format($promedio7d,1)); ?> L</div>
      <div class="prod-lbl">Prom. 7 días</div>
    </div>
  </div>
  <div style="position:relative;height:120px;">
    <canvas id="chartProd"></canvas>
  </div>
</div>


<?php if($partosProximos->count() || $alertasSanidad->count() || $diasAbiertosAltos->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">⚠️ Alertas</div>

  <?php $__currentLoopData = $partosProximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="alerta-row alerta-parto">
    <span>🐮</span>
    <div>
      <strong><?php echo e($p->nombre_lote); ?></strong> — Parto esperado
      <span style="color:#1d4ed8;"><?php echo e(\Carbon\Carbon::parse($p->fecha_probable_parto)->format('d/m/Y')); ?></span>
      (<?php echo e(\Carbon\Carbon::parse($p->fecha_probable_parto)->diffInDays(now())); ?> días)
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <?php $__currentLoopData = $alertasSanidad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $vencida = $s->proxima_aplicacion < now()->toDateString(); ?>
  <div class="alerta-row <?php echo e($vencida ? 'alerta-vencida' : 'alerta-proxima'); ?>">
    <span><?php echo e($vencida ? '🔴' : '🟡'); ?></span>
    <div>
      <strong><?php echo e($s->nombre_protocolo); ?></strong> —
      <?php if($vencida): ?>
        Vencida desde <span><?php echo e(\Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y')); ?></span>
      <?php else: ?>
        Próxima: <span><?php echo e(\Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y')); ?></span>
        (<?php echo e(\Carbon\Carbon::parse($s->proxima_aplicacion)->diffInDays(now())); ?> días)
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <?php $__currentLoopData = $diasAbiertosAltos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $da): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="alerta-row alerta-vencida">
    <span>⏱️</span>
    <div>
      <strong><?php echo e($da->nombre_lote); ?></strong> — Días abiertos críticos desde
      <?php echo e(\Carbon\Carbon::parse($da->fecha_parto_real)->format('d/m/Y')); ?>

    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">📋 Módulos bovinos</div>
  <div class="bovino-menu-grid">
    <a href="<?php echo e(route('bovino.ordenos')); ?>" class="bovino-menu-card" style="border-top-color:#22c55e;">
      <div class="bovino-menu-ico">🥛</div>
      <div class="bovino-menu-lbl">Ordeños</div>
      <div class="bovino-menu-sub">Registro AM/PM y curva</div>
    </a>
    <a href="<?php echo e(route('bovino.reproduccion')); ?>" class="bovino-menu-card" style="border-top-color:#3b82f6;">
      <div class="bovino-menu-ico">🐮</div>
      <div class="bovino-menu-lbl">Reproducción</div>
      <div class="bovino-menu-sub">Servicios, preñez, partos</div>
    </a>
    <a href="<?php echo e(route('bovino.sanidad')); ?>" class="bovino-menu-card" style="border-top-color:#f59e0b;">
      <div class="bovino-menu-ico">💉</div>
      <div class="bovino-menu-lbl">Sanidad</div>
      <div class="bovino-menu-sub">Vacunas y desparasitación</div>
    </a>
    <a href="<?php echo e(route('bovino.pesaje')); ?>" class="bovino-menu-card" style="border-top-color:#8b5cf6;">
      <div class="bovino-menu-ico">⚖️</div>
      <div class="bovino-menu-lbl">Pesaje</div>
      <div class="bovino-menu-sub">Pesos y GPD</div>
    </a>
    <a href="<?php echo e(route('bovino.reportes')); ?>" class="bovino-menu-card" style="border-top-color:#ec4899;">
      <div class="bovino-menu-ico">📊</div>
      <div class="bovino-menu-lbl">Reportes</div>
      <div class="bovino-menu-sub">Análisis del hato</div>
    </a>
    <a href="<?php echo e(route('animales.index')); ?>" class="bovino-menu-card" style="border-top-color:#94a3b8;">
      <div class="bovino-menu-ico">🐄</div>
      <div class="bovino-menu-lbl">Mis Animales</div>
      <div class="bovino-menu-sub">Módulo general</div>
    </a>
  </div>
</div>

<div style="margin-bottom:80px;"></div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const ctx = document.getElementById('chartProd');
new Chart(ctx, {
  type: 'line',
  data: {
    labels: <?php echo json_encode($labels); ?>,
    datasets: [{
      data: <?php echo json_encode($valores); ?>,
      borderColor: '#15803d',
      backgroundColor: 'rgba(21,128,61,.1)',
      borderWidth: 2,
      pointRadius: 3,
      fill: true,
      tension: 0.4,
    }]
  },
  options: {
    responsive: true, maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
      x: { ticks: { font: { size: 10 } }, grid: { display: false } },
      y: { ticks: { font: { size: 10 } }, beginAtZero: true,
           title: { display: true, text: 'Litros', font: { size: 10 } } }
    }
  }
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/bovino/hato.blade.php ENDPATH**/ ?>