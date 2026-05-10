
<?php $__env->startSection('title','Reportes'); ?>
<?php $__env->startSection('page_title','Reportes y Rentabilidad'); ?>
<?php $__env->startSection('back_url', route('inicio')); ?>

<?php $__env->startPush('head'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<form method="GET" action="<?php echo e(route('reportes.index')); ?>"
      style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center;">
  <select name="anio" class="form-control" style="max-width:100px;" onchange="this.form.submit()">
    <?php $__currentLoopData = $aniosDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($a); ?>" <?php echo e($anio == $a ? 'selected' : ''); ?>><?php echo e($a); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <select name="mes" class="form-control" style="max-width:130px;" onchange="this.form.submit()">
    <option value="">Año completo</option>
    <?php $__currentLoopData = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $nm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($i+1); ?>" <?php echo e($mes == $i+1 ? 'selected' : ''); ?>><?php echo e($nm); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <span style="font-size:.78rem;color:#64748b;">
    <?php echo e(\Carbon\Carbon::parse($inicio)->format('d/m/Y')); ?>

    — <?php echo e(\Carbon\Carbon::parse($fin)->format('d/m/Y')); ?>

  </span>
</form>


<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px;">
  <div style="background:#f0fdf4;border-radius:14px;padding:14px;text-align:center;border-left:4px solid #16a34a;">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Ingresos</div>
    <div style="font-size:1.3rem;font-weight:900;color:#16a34a;">
      $<?php echo e($totalIngresos >= 1000000 ? round($totalIngresos/1000000,1).'M' : number_format($totalIngresos/1000,0).'k'); ?>

    </div>
  </div>
  <div style="background:#fef2f2;border-radius:14px;padding:14px;text-align:center;border-left:4px solid #dc2626;">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Gastos</div>
    <div style="font-size:1.3rem;font-weight:900;color:#dc2626;">
      $<?php echo e($totalGastos >= 1000000 ? round($totalGastos/1000000,1).'M' : number_format($totalGastos/1000,0).'k'); ?>

    </div>
  </div>
  <div style="background:<?php echo e($balanceTotal >= 0 ? '#f0fdf4' : '#fef2f2'); ?>;
              border-radius:14px;padding:14px;text-align:center;
              border-left:4px solid <?php echo e($balanceTotal >= 0 ? '#16a34a' : '#dc2626'); ?>;">
    <div style="font-size:.7rem;color:#64748b;text-transform:uppercase;font-weight:600;">Balance</div>
    <div style="font-size:1.3rem;font-weight:900;color:<?php echo e($balanceTotal >= 0 ? '#16a34a' : '#dc2626'); ?>;">
      <?php echo e($balanceTotal >= 0 ? '+' : '-'); ?>$<?php echo e(abs($balanceTotal) >= 1000000 ? round(abs($balanceTotal)/1000000,1).'M' : number_format(abs($balanceTotal)/1000,0).'k'); ?>

    </div>
  </div>
</div>


<?php if(count($rentabilidadLineas) > 0): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">Rentabilidad por linea productiva</div>

  <?php $maxAbsRent = max(1, max(array_map(fn($r) => abs($r['rentabilidad']), $rentabilidadLineas))); ?>

  <?php $__currentLoopData = $rentabilidadLineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lineaKey => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $pctBarra = round((abs($r['rentabilidad']) / $maxAbsRent) * 100);
    $colorBarra = $r['es_rentable'] ? '#16a34a' : '#dc2626';
    $margenColor = $r['margen'] >= 20 ? '#15803d' : ($r['margen'] >= 0 ? '#b45309' : '#dc2626');
  ?>
  <div style="margin-bottom:14px;padding-bottom:14px;border-bottom:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
      <div>
        <span style="font-size:.95rem;font-weight:700;">
          <?php echo e($r['emoji']); ?> <?php echo e($r['nombre']); ?>

        </span>
        <span style="font-size:.72rem;color:#64748b;margin-left:8px;">
          Margen: <strong style="color:<?php echo e($margenColor); ?>;"><?php echo e($r['margen']); ?>%</strong>
        </span>
      </div>
      <div style="text-align:right;">
        <div style="font-size:.95rem;font-weight:800;color:<?php echo e($r['es_rentable'] ? '#16a34a' : '#dc2626'); ?>;">
          <?php echo e($r['es_rentable'] ? '+' : ''); ?>$<?php echo e(number_format($r['rentabilidad']/1000,1)); ?>k
        </div>
        <div style="font-size:.68rem;color:#94a3b8;">rentabilidad neta</div>
      </div>
    </div>
    <div style="background:#e2e8f0;border-radius:6px;height:8px;margin-bottom:4px;overflow:hidden;">
      <div style="width:<?php echo e($pctBarra); ?>%;height:100%;border-radius:6px;background:<?php echo e($colorBarra); ?>;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#64748b;">
      <span style="color:#16a34a;">Ingresos: $<?php echo e(number_format($r['ingresos']/1000,1)); ?>k</span>
      <span style="color:#dc2626;">Gastos: $<?php echo e(number_format($r['gastos']/1000,1)); ?>k</span>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if(count($kpisEspecificos) > 0): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">KPIs especificos por linea</div>
  <?php $__currentLoopData = $kpisEspecificos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lk => $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="margin-bottom:14px;">
    <div style="font-weight:700;font-size:.88rem;margin-bottom:8px;">
      <?php echo e($k['emoji']); ?> <?php echo e($k['titulo']); ?>

    </div>
    <?php $__currentLoopData = $k['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div style="display:flex;justify-content:space-between;align-items:flex-start;
                padding:6px 0;border-bottom:1px solid #f1f5f9;font-size:.82rem;">
      <div>
        <span style="color:#475569;"><?php echo e($item['kpi']); ?></span>
        <?php if(isset($item['meta'])): ?>
        <div style="font-size:.68rem;color:#94a3b8;margin-top:1px;">Meta: <?php echo e($item['meta']); ?></div>
        <?php endif; ?>
      </div>
      <span style="font-weight:800;color:#1e293b;white-space:nowrap;margin-left:8px;">
        <?php echo e($item['valor']); ?>

      </span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if(count($chartLineas) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Comparativo entre lineas</div>
  <div style="position:relative;height:220px;">
    <canvas id="chartLineasComp"></canvas>
  </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Evolucion mensual <?php echo e($anio); ?></div>
  <div style="position:relative;height:180px;">
    <canvas id="chartEvolucion"></canvas>
  </div>
</div>


<?php if($detalleIngresos->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Ingresos por tipo</div>
  <?php $__currentLoopData = $detalleIngresos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $pctI = $totalIngresos > 0 ? round(($d->total / $totalIngresos) * 100, 1) : 0; ?>
  <div style="display:flex;justify-content:space-between;align-items:center;
              padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div style="flex:1;">
      <span style="font-weight:600;"><?php echo e(ucfirst(str_replace('_',' ',$d->tipo ?? 'otro'))); ?></span>
      <span style="font-size:.7rem;color:#94a3b8;margin-left:6px;"><?php echo e($d->registros); ?> registros</span>
      <div style="background:#e2e8f0;border-radius:3px;height:4px;margin-top:3px;overflow:hidden;">
        <div style="width:<?php echo e($pctI); ?>%;height:100%;background:#16a34a;border-radius:3px;"></div>
      </div>
    </div>
    <div style="text-align:right;margin-left:12px;">
      <span style="font-weight:700;color:#16a34a;">+$<?php echo e(number_format($d->total/1000,1)); ?>k</span>
      <div style="font-size:.68rem;color:#94a3b8;"><?php echo e($pctI); ?>%</div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($detalleGastos->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Gastos por categoria</div>
  <?php $__currentLoopData = $detalleGastos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $pctG = $totalGastos > 0 ? round(($d->total / $totalGastos) * 100, 1) : 0; ?>
  <div style="display:flex;justify-content:space-between;align-items:center;
              padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div style="flex:1;">
      <span style="font-weight:600;"><?php echo e(ucfirst($d->categoria ?? 'otros')); ?></span>
      <span style="font-size:.7rem;color:#94a3b8;margin-left:6px;"><?php echo e($d->registros); ?> registros</span>
      <div style="background:#e2e8f0;border-radius:3px;height:4px;margin-top:3px;overflow:hidden;">
        <div style="width:<?php echo e($pctG); ?>%;height:100%;background:#dc2626;border-radius:3px;"></div>
      </div>
    </div>
    <div style="text-align:right;margin-left:12px;">
      <span style="font-weight:700;color:#dc2626;">-$<?php echo e(number_format($d->total/1000,1)); ?>k</span>
      <div style="font-size:.68rem;color:#94a3b8;"><?php echo e($pctG); ?>%</div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
<?php if(count($chartLineas) > 1): ?>
(function(){
  var ctx = document.getElementById('chartLineasComp');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode(array_map(fn($l) => ucfirst(str_replace('_',' ',$l)), $chartLineas)); ?>,
      datasets: [
        { label: 'Ingresos',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $chartIngresos)); ?>,
          backgroundColor: 'rgba(22,163,74,.75)', borderColor: '#16a34a', borderWidth: 1 },
        { label: 'Gastos',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $chartGastos)); ?>,
          backgroundColor: 'rgba(220,38,38,.6)', borderColor: '#dc2626', borderWidth: 1 },
        { label: 'Rentabilidad neta',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $chartRentab)); ?>,
          backgroundColor: 'rgba(37,99,235,.6)', borderColor: '#2563eb', borderWidth: 1 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { ticks: { font: { size: 9 },
             callback: function(v){ return '$'+v+'k'; } } }
      }
    }
  });
})();
<?php endif; ?>

(function(){
  var ctx = document.getElementById('chartEvolucion');
  if (!ctx) return;
  <?php
    $evMeses = array_column($evolucionMensual, 'mes');
    $evIng   = array_column($evolucionMensual, 'ingresos');
    $evGast  = array_column($evolucionMensual, 'gastos');
    $evRent  = array_column($evolucionMensual, 'rentabilidad');
  ?>
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($evMeses); ?>,
      datasets: [
        { label: 'Ingresos',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $evIng)); ?>,
          borderColor: '#16a34a', backgroundColor: 'rgba(22,163,74,.08)',
          borderWidth: 2, pointRadius: 3, fill: true },
        { label: 'Gastos',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $evGast)); ?>,
          borderColor: '#dc2626', backgroundColor: 'transparent',
          borderWidth: 1.5, borderDash: [4,4], pointRadius: 2 },
        { label: 'Rentabilidad',
          data: <?php echo json_encode(array_map(fn($v) => round($v/1000,1), $evRent)); ?>,
          borderColor: '#2563eb', backgroundColor: 'transparent',
          borderWidth: 2, pointRadius: 3 }
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x: { ticks: { font: { size: 9 } }, grid: { display: false } },
        y: { ticks: { font: { size: 9 },
             callback: function(v){ return '$'+v+'k'; } } }
      }
    }
  });
})();
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/reportes/index.blade.php ENDPATH**/ ?>