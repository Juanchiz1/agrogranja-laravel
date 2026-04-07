<?php $__env->startSection('title','Reportes'); ?>
<?php $__env->startSection('page_title','📊 Reportes'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>
<?php $__env->startPush('head'); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>

<div class="flex items-center gap-2 mb-3">
  <a href="?tab=<?php echo e($tab); ?>&anio=<?php echo e($anio-1); ?>" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;">Año <?php echo e($anio); ?></span>
  <a href="?tab=<?php echo e($tab); ?>&anio=<?php echo e($anio+1); ?>" class="btn btn-sm btn-secondary btn-icon">›</a>
</div>

<?php $__env->startPush('head'); ?>
<style>
  .btn-export {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
  }
  .btn-export:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
  }
  .btn-pdf-red { background: #dc2626; color: white; }
  .btn-pdf-red:hover { background: #b91c1c; }
  .btn-pdf-green { background: #3d8b3d; color: white; }
  .btn-pdf-green:hover { background: #2d6a2d; }
  .btn-excel-green { background: #059669; color: white; }
  .btn-excel-green:hover { background: #047857; }
  .btn-pdf-brown { background: #7a4f2a; color: white; }
  .btn-pdf-brown:hover { background: #5c3b1f; }
  .btn-excel-brown { background: #7a4f2a; color: white; }
  .btn-excel-brown:hover { background: #5c3b1f; }
  .btn-pdf-orange { background: #ea580c; color: white; }
  .btn-pdf-orange:hover { background: #c2410c; }
  .btn-excel-orange { background: #ea580c; color: white; }
  .btn-excel-orange:hover { background: #c2410c; }
</style>
<?php $__env->stopPush(); ?>


<div class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <a href="<?php echo e(route('exportar.reporte.pdf', ['anio' => $anio])); ?>" class="btn-export btn-pdf-red">📄 Reporte general PDF</a>
  <a href="<?php echo e(route('exportar.cultivos.pdf')); ?>" class="btn-export btn-pdf-green">🌱 Cultivos PDF</a>
  <a href="<?php echo e(route('exportar.cultivos.excel')); ?>" class="btn-export btn-excel-green">📊 Cultivos Excel</a>
  <a href="<?php echo e(route('exportar.gastos.pdf', ['anio' => $anio])); ?>" class="btn-export btn-pdf-brown">💰 Gastos PDF</a>
  <a href="<?php echo e(route('exportar.gastos.excel', ['anio' => $anio])); ?>" class="btn-export btn-excel-brown">📊 Gastos Excel</a>
  <a href="<?php echo e(route('exportar.cosechas.pdf', ['anio' => $anio])); ?>" class="btn-export btn-pdf-orange">🌾 Cosechas PDF</a>
  <a href="<?php echo e(route('exportar.cosechas.excel', ['anio' => $anio])); ?>" class="btn-export btn-excel-orange">📊 Cosechas Excel</a>
</div>

<div class="tabs">
  <button class="tab-btn <?php echo e($tab==='resumen'?'active':''); ?>"  onclick="location.href='?tab=resumen&anio=<?php echo e($anio); ?>'">📋 Resumen</button>
  <button class="tab-btn <?php echo e($tab==='gastos'?'active':''); ?>"   onclick="location.href='?tab=gastos&anio=<?php echo e($anio); ?>'">💰 Gastos</button>
  <button class="tab-btn <?php echo e($tab==='cultivos'?'active':''); ?>" onclick="location.href='?tab=cultivos&anio=<?php echo e($anio); ?>'">🌱 Cultivos</button>
</div>

<?php $balance = $totalIngresos - $totalGastos; ?>

<?php if($tab === 'resumen'): ?>
<div class="grid-2 mb-3">
  <div class="stat-card" style="background:var(--verde-bg);"><div class="stat-value text-green" style="font-size:1rem;">$<?php echo e(number_format($totalIngresos,0,',','.')); ?></div><div class="stat-label">Total ingresos</div></div>
  <div class="stat-card" style="background:var(--marron-bg);"><div class="stat-value text-brown" style="font-size:1rem;">$<?php echo e(number_format($totalGastos,0,',','.')); ?></div><div class="stat-label">Total gastos</div></div>
</div>
<div class="card mb-3" style="background:<?php echo e($balance >= 0 ? 'var(--verde-bg)' : '#fef2f2'); ?>;text-align:center;">
  <p class="text-xs font-bold text-gray" style="text-transform:uppercase;">Balance del año</p>
  <p style="font-size:2rem;font-weight:800;color:<?php echo e($balance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">$<?php echo e(number_format($balance,0,',','.')); ?></p>
  <p class="text-xs text-gray"><?php echo e($balance >= 0 ? '✅ Operando con ganancia' : '⚠️ Gastos superan ingresos'); ?></p>
</div>
<div class="card mb-3">
  <p class="font-bold mb-3">Ingresos vs Gastos por mes</p>
  <canvas id="chartMensual" height="180"></canvas>
</div>
<?php if($tareasStats): ?>
<div class="card">
  <div class="flex items-center justify-between mb-2"><p class="font-bold">Cumplimiento de tareas</p><span class="font-bold text-green"><?php echo e($tareasStats->total > 0 ? round($tareasStats->completadas/$tareasStats->total*100) : 0); ?>%</span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:<?php echo e($tareasStats->total > 0 ? round($tareasStats->completadas/$tareasStats->total*100) : 0); ?>%"></div></div>
  <p class="text-xs text-gray mt-2"><?php echo e($tareasStats->completadas ?? 0); ?> de <?php echo e($tareasStats->total); ?> tareas completadas</p>
</div>
<?php endif; ?>

<?php elseif($tab === 'gastos'): ?>
<div class="card mb-3"><p class="font-bold mb-3">Gastos mensuales <?php echo e($anio); ?></p><canvas id="chartGastosMes" height="180"></canvas></div>
<p class="section-title">Por categoría</p>
<?php $__currentLoopData = $gastosCat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="mb-3">
  <div class="flex items-center justify-between mb-2"><span class="text-sm font-bold"><?php echo e($gc->categoria); ?></span><span class="font-bold text-brown text-sm">$<?php echo e(number_format($gc->total,0,',','.')); ?></span></div>
  <div class="progress-bar"><div class="progress-fill" style="width:<?php echo e($totalGastos > 0 ? min(100,round($gc->total/$totalGastos*100)) : 0); ?>%;background:var(--marron-light);"></div></div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php if($gastosCat->count()): ?>
<div class="card mt-3"><p class="font-bold mb-3">Distribución por categoría</p><canvas id="chartCategorias" height="200"></canvas></div>
<?php endif; ?>

<?php elseif($tab === 'cultivos'): ?>
<div class="grid-3 mb-3">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($cultivosEst['activo'] ?? 0); ?></div><div class="stat-label">Activos</div></div>
  <div class="stat-card"><div class="stat-value text-orange"><?php echo e($cultivosEst['cosechado'] ?? 0); ?></div><div class="stat-label">Cosechados</div></div>
  <div class="stat-card"><div class="stat-value text-brown"><?php echo e($cultivosEst['vendido'] ?? 0); ?></div><div class="stat-label">Vendidos</div></div>
</div>
<div class="card"><p class="font-bold mb-3">Estado de cultivos</p><canvas id="chartCultivos" height="200"></canvas></div>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';
const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
const gastosArr = <?php echo json_encode($gastosArr, 15, 512) ?>;
const ingresosArr = <?php echo json_encode($ingresosArr, 15, 512) ?>;

<?php if($tab === 'resumen'): ?>
new Chart(document.getElementById('chartMensual'), {
  type: 'bar', data: { labels: meses, datasets: [
    { label:'Ingresos', data:ingresosArr, backgroundColor:'rgba(61,139,61,.75)', borderRadius:5 },
    { label:'Gastos',   data:gastosArr,   backgroundColor:'rgba(122,79,42,.65)', borderRadius:5 }
  ]}, options: { responsive:true, plugins:{legend:{position:'bottom'}}, scales:{ y:{ ticks:{ callback: v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php elseif($tab === 'gastos'): ?>
new Chart(document.getElementById('chartGastosMes'), {
  type:'line', data:{ labels:meses, datasets:[{ label:'Gastos', data:gastosArr, borderColor:'#7a4f2a', backgroundColor:'rgba(122,79,42,.1)', fill:true, tension:.4, pointRadius:4 }]},
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php $catLabels = $gastosCat->pluck('categoria'); $catValues = $gastosCat->pluck('total'); $colors = ['#3d8b3d','#7a4f2a','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#64748b','#059669']; ?>
<?php if($gastosCat->count()): ?>
new Chart(document.getElementById('chartCategorias'), {
  type:'doughnut', data:{ labels:<?php echo json_encode($catLabels, 15, 512) ?>, datasets:[{ data:<?php echo json_encode($catValues, 15, 512) ?>, backgroundColor:<?php echo json_encode(array_slice($colors, 0, $gastosCat->count())) ?>, borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom', labels:{boxWidth:12,font:{size:11}}}} }
});
<?php endif; ?>
<?php elseif($tab === 'cultivos'): ?>
new Chart(document.getElementById('chartCultivos'), {
  type:'doughnut', data:{ labels:['Activos','Cosechados','Vendidos'], datasets:[{ data:[<?php echo e($cultivosEst['activo']??0); ?>,<?php echo e($cultivosEst['cosechado']??0); ?>,<?php echo e($cultivosEst['vendido']??0); ?>], backgroundColor:['#3d8b3d','#ea580c','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}} }
});
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/reportes.blade.php ENDPATH**/ ?>