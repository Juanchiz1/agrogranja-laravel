<?php $__env->startSection('title','Reportes'); ?>
<?php $__env->startSection('page_title','📊 Reportes'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/reportes.css')); ?>">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="flex items-center gap-2 mb-4">
  <a href="?tab=<?php echo e($tab); ?>&anio=<?php echo e($anio-1); ?>" class="btn btn-sm btn-secondary btn-icon">‹</a>
  <span class="font-bold" style="flex:1;text-align:center;font-size:1.1rem;">Año <?php echo e($anio); ?></span>
  <a href="?tab=<?php echo e($tab); ?>&anio=<?php echo e($anio+1); ?>" class="btn btn-sm btn-secondary btn-icon">›</a>
</div>


<div class="tabs-rep">
  <a href="?tab=resumen&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='resumen'?'active':''); ?>">📋 Resumen</a>
  <a href="?tab=finanzas&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='finanzas'?'active':''); ?>">💰 Finanzas</a>
  <a href="?tab=cultivos&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='cultivos'?'active':''); ?>">🌱 Cultivos</a>
  <a href="?tab=animales&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='animales'?'active':''); ?>">🐄 Animales</a>
  <a href="?tab=rentabilidad&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='rentabilidad'?'active':''); ?>">💹 Rentabilidad</a>
  <a href="?tab=exportar&anio=<?php echo e($anio); ?>" class="tab-rep <?php echo e($tab==='exportar'?'active':''); ?>">📤 Exportar</a>
</div>

<?php
  $meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
  $colores10 = ['#3d8b3d','#7a4f2a','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#64748b','#059669'];
  $tiposLabels = ['venta'=>'Venta','cosecha_propia'=>'Cosecha','animal'=>'Animal','subproducto'=>'Subproducto','subsidio'=>'Subsidio','servicio'=>'Servicio','alquiler'=>'Alquiler','otro'=>'Otro'];
?>


<?php if($tab === 'resumen'): ?>


<div class="stats-row">
  <div class="stat-mini" style="background:var(--verde-bg)">
    <div class="stat-mini-val text-green">$<?php echo e($totalIngresos>=1000?number_format($totalIngresos/1000,1).'k':number_format($totalIngresos,0,',','.')); ?></div>
    <div class="stat-mini-label">Ingresos</div>
  </div>
  <div class="stat-mini" style="background:var(--marron-bg)">
    <div class="stat-mini-val" style="color:var(--marron)">$<?php echo e($totalGastos>=1000?number_format($totalGastos/1000,1).'k':number_format($totalGastos,0,',','.')); ?></div>
    <div class="stat-mini-label">Gastos</div>
  </div>
  <div class="stat-mini" style="background:<?php echo e($balance>=0?'var(--verde-bg)':'#fef2f2'); ?>">
    <div class="stat-mini-val" style="color:<?php echo e($balance>=0?'var(--verde-dark)':'var(--rojo)'); ?>">$<?php echo e(abs($balance)>=1000?number_format(abs($balance)/1000,1).'k':number_format(abs($balance),0,',','.')); ?></div>
    <div class="stat-mini-label">Balance</div>
  </div>
  <div class="stat-mini" style="background:var(--verde-bg)">
    <div class="stat-mini-val text-green"><?php echo e($totalCosechas); ?></div>
    <div class="stat-mini-label">Cosechas</div>
  </div>
</div>


<div class="rep-card">
  <div class="rep-card-title">📈 Balance neto mensual — <?php echo e($anio); ?></div>
  <canvas id="chartBalance" height="160"></canvas>
</div>


<div class="rep-card">
  <div class="rep-card-title">💹 Ingresos vs Gastos por mes</div>
  <canvas id="chartMensual" height="170"></canvas>
</div>


<?php if(count($insights)): ?>
<div class="rep-card">
  <div class="rep-card-title">💡 Análisis automático</div>
  <?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ins): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="insight-card insight-<?php echo e($ins['tipo']); ?>">
    <span><?php echo e(['positivo'=>'✅','negativo'=>'⚠️','info'=>'📊','alerta'=>'🚨'][$ins['tipo']]); ?></span>
    <span><?php echo e($ins['texto']); ?></span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="rep-card">
  <div class="rep-card-title">🎯 ¿Dónde se van los gastos?</div>
  <canvas id="chartGastosOrigen" height="200"></canvas>
</div>


<?php if($tareasStats && $tareasStats->total > 0): ?>
<div class="rep-card">
  <div class="flex items-center justify-between mb-2">
    <div class="rep-card-title" style="margin:0;">✅ Cumplimiento de tareas</div>
    <span style="font-weight:800;color:var(--verde-dark);"><?php echo e(round($tareasStats->completadas/$tareasStats->total*100)); ?>%</span>
  </div>
  <div style="background:#e5e7eb;border-radius:99px;height:10px;overflow:hidden;margin-bottom:8px;">
    <div style="height:100%;border-radius:99px;background:var(--verde-mid);width:<?php echo e(round($tareasStats->completadas/$tareasStats->total*100)); ?>%;transition:width .5s;"></div>
  </div>
  <div style="font-size:.8rem;color:var(--text-secondary);"><?php echo e(intval($tareasStats->completadas)); ?> de <?php echo e($tareasStats->total); ?> tareas completadas</div>
</div>
<?php endif; ?>


<?php elseif($tab === 'finanzas'): ?>


<?php if($ingresosTipo->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">💰 Ingresos por tipo — <?php echo e($anio); ?></div>
  <canvas id="chartIngresosTipo" height="200"></canvas>
  <div style="margin-top:12px;">
    <?php $__currentLoopData = $ingresosTipo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $it): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $pct = $totalIngresos>0?round($it->total/$totalIngresos*100):0; ?>
    <div class="rank-row">
      <div style="flex:1;font-size:.85rem;font-weight:600;"><?php echo e($tiposLabels[$it->tipo]??ucfirst($it->tipo)); ?></div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:<?php echo e($pct); ?>%;background:var(--verde-mid);"></div></div>
      <div style="font-size:.82rem;font-weight:700;color:var(--verde-dark);min-width:60px;text-align:right;">$<?php echo e(number_format($it->total,0,',','.')); ?></div>
      <div style="font-size:.72rem;color:var(--text-muted);min-width:30px;text-align:right;"><?php echo e($pct); ?>%</div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php endif; ?>


<div class="rep-card">
  <div class="rep-card-title">💸 Gastos por categoría</div>
  <canvas id="chartCategorias" height="220"></canvas>
  <div style="margin-top:12px;">
    <?php $__currentLoopData = $gastosCat->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $gc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $pct = $totalGastos>0?min(100,round($gc->total/$totalGastos*100)):0; ?>
    <div class="rank-row">
      <div style="flex:1;font-size:.85rem;font-weight:600;"><?php echo e($gc->categoria); ?></div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:<?php echo e($pct); ?>%;background:var(--marron-light);"></div></div>
      <div style="font-size:.82rem;font-weight:700;color:var(--marron);min-width:60px;text-align:right;">$<?php echo e(number_format($gc->total,0,',','.')); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>


<?php if($topCompradores->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">🏆 Top compradores del año</div>
  <?php $__currentLoopData = $topCompradores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $tc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="rank-row">
    <div class="rank-num"><?php echo e($idx+1); ?></div>
    <div style="flex:1;"><div style="font-weight:600;font-size:.88rem;"><?php echo e($tc->comprador); ?></div><div style="font-size:.73rem;color:var(--text-secondary);"><?php echo e($tc->cnt); ?> venta(s)</div></div>
    <div style="font-weight:700;color:var(--verde-dark);">$<?php echo e(number_format($tc->total,0,',','.')); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($topProveedores->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">🏪 Top proveedores del año</div>
  <?php $__currentLoopData = $topProveedores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="rank-row">
    <div class="rank-num"><?php echo e($idx+1); ?></div>
    <div style="flex:1;"><div style="font-weight:600;font-size:.88rem;"><?php echo e($tp->proveedor); ?></div><div style="font-size:.73rem;color:var(--text-secondary);"><?php echo e($tp->cnt); ?> compra(s)</div></div>
    <div style="font-weight:700;color:var(--marron);">$<?php echo e(number_format($tp->total,0,',','.')); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php elseif($tab === 'cultivos'): ?>

<div class="stats-row">
  <div class="stat-mini"><div class="stat-mini-val text-green"><?php echo e($cultivosEst['activo']??0); ?></div><div class="stat-mini-label">Activos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:#ea580c;"><?php echo e($cultivosEst['cosechado']??0); ?></div><div class="stat-mini-label">Cosechados</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--marron);"><?php echo e($cultivosEst['vendido']??0); ?></div><div class="stat-mini-label">Vendidos</div></div>
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green"><?php echo e($totalCosechas); ?></div><div class="stat-mini-label">Cosechas</div></div>
</div>


<?php if($valorCosechas > 0): ?>
<div class="rep-card">
  <div class="rep-card-title">🌾 Valor de cosechas por mes — <?php echo e($anio); ?></div>
  <canvas id="chartCosechas" height="160"></canvas>
</div>
<?php endif; ?>


<?php if($cultivosTipo->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">🌱 Tipos de cultivo activos</div>
  <canvas id="chartCultivosDonut" height="200"></canvas>
</div>
<?php endif; ?>


<?php if($rentCultivos->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">💹 Rentabilidad por cultivo</div>
  <a href="<?php echo e(route('rentabilidad.index')); ?>" class="btn btn-sm btn-ghost mb-3" style="font-size:.8rem;">Ver detalle completo →</a>
  <?php $__currentLoopData = $rentCultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $color = $rc->balance>=0?'var(--verde-dark)':'var(--rojo)'; ?>
  <div class="rank-row">
    <div style="flex:1;"><div style="font-weight:600;font-size:.87rem;"><?php echo e($rc->nombre); ?></div><div style="font-size:.72rem;color:var(--text-secondary);"><?php echo e($rc->tipo); ?></div></div>
    <div style="text-align:right;">
      <div style="font-size:.75rem;color:var(--text-muted);">G $<?php echo e(number_format($rc->gastos,0,',','.')); ?></div>
      <div style="font-weight:700;color:<?php echo e($color); ?>;"><?php echo e($rc->balance>=0?'+':''); ?>$<?php echo e(number_format($rc->balance,0,',','.')); ?></div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <canvas id="chartRentCultivos" height="200" style="margin-top:16px;"></canvas>
</div>
<?php endif; ?>


<?php elseif($tab === 'animales'): ?>

<div class="stats-row">
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green"><?php echo e($animalesEst['activo']??0); ?></div><div class="stat-mini-label">Activos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--marron)"><?php echo e($animalesEst['vendido']??0); ?></div><div class="stat-mini-label">Vendidos</div></div>
  <div class="stat-mini"><div class="stat-mini-val" style="color:var(--rojo)"><?php echo e($animalesEst['muerte']??0); ?></div><div class="stat-mini-label">Bajas</div></div>
  <?php if($valorHato > 0): ?>
  <div class="stat-mini" style="background:var(--verde-bg)"><div class="stat-mini-val text-green" style="font-size:.9rem;">$<?php echo e(number_format($valorHato/1000,0).'k'); ?></div><div class="stat-mini-label">Valor hato</div></div>
  <?php endif; ?>
</div>


<?php if($animalesPorEspecie->count()): ?>
<div class="rep-card">
  <div class="rep-card-title">🐾 Inventario por especie</div>
  <canvas id="chartAnimalesEspecie" height="200"></canvas>
  <div style="margin-top:14px;">
    <?php $totalAnim = $animalesPorEspecie->sum('total') ?: 1; ?>
    <?php $__currentLoopData = $animalesPorEspecie; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ae): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <div class="rank-row">
      <div style="flex:1;font-weight:600;font-size:.87rem;"><?php echo e($ae->especie); ?></div>
      <div class="rank-bar-wrap"><div class="rank-bar-fill" style="width:<?php echo e(round($ae->total/$totalAnim*100)); ?>%;"></div></div>
      <div style="font-weight:700;color:var(--verde-dark);min-width:40px;text-align:right;"><?php echo e($ae->total); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php endif; ?>


<?php if($valorHato > 0): ?>
<div class="rep-card">
  <div class="rep-card-title">💰 Valor estimado del hato</div>
  <div style="text-align:center;padding:16px 0;">
    <div style="font-size:2rem;font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($valorHato,0,',','.')); ?></div>
    <div style="font-size:.82rem;color:var(--text-secondary);margin-top:4px;">Calculado según precio/kg o precio/cabeza asignado a cada lote</div>
  </div>
  <?php if($ventasAnimales > 0): ?>
  <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px;display:flex;justify-content:space-between;font-size:.87rem;">
    <span>Ventas de animales este año</span>
    <strong style="color:var(--verde-dark)">$<?php echo e(number_format($ventasAnimales,0,',','.')); ?></strong>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php
  $gastosAnimTotal   = DB::table('gastos')->where('usuario_id',session('usuario_id'))->whereYear('fecha',$anio)->whereNotNull('animal_id')->sum('valor');
  $ingresosAnimTotal = DB::table('ingresos')->where('usuario_id',session('usuario_id'))->whereYear('fecha',$anio)->whereNotNull('animal_id')->sum('valor_total');
  $balAnim = $ingresosAnimTotal - $gastosAnimTotal;
?>
<?php if($gastosAnimTotal > 0 || $ingresosAnimTotal > 0): ?>
<div class="rep-card">
  <div class="rep-card-title">📊 Finanzas de animales</div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Ingresos por animales</div>
      <div style="font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($ingresosAnimTotal,0,',','.')); ?></div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Gastos en animales</div>
      <div style="font-weight:800;color:var(--rojo);">$<?php echo e(number_format($gastosAnimTotal,0,',','.')); ?></div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.88rem;">
    <span>Balance ganadería</span>
    <strong style="color:<?php echo e($balAnim>=0?'var(--verde-dark)':'var(--rojo)'); ?>"><?php echo e($balAnim>=0?'+':''); ?>$<?php echo e(number_format($balAnim,0,',','.')); ?></strong>
  </div>
</div>
<?php endif; ?>



<?php elseif($tab === 'rentabilidad'): ?>

<div class="flex gap-2 mb-3 items-center">
  <span style="font-size:.82rem;color:var(--text-secondary);">Ordenar por:</span>
  <select onchange="location.href='?tab=rentabilidad&anio=<?php echo e($anio); ?>&orden='+this.value" class="form-control" style="width:160px;font-size:.82rem;">
    <?php $__currentLoopData = ['rentabilidad'=>'Mejor balance','ingresos'=>'Mayor ingreso','gastos'=>'Mayor gasto','nombre'=>'Nombre']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <option value="<?php echo e($val); ?>" <?php echo e($orden===$val ? 'selected' : ''); ?>><?php echo e($lbl); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
</div>

<?php if($rentMejor || $rentPeor): ?>
<div class="grid-2 mb-3">
  <?php if($rentMejor): ?>
  <div style="background:var(--verde-bg);border-radius:var(--radius-lg);padding:14px;border-left:3px solid var(--verde-dark);">
    <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">&#11088; Mejor cultivo</p>
    <p style="font-weight:700;"><?php echo e($rentMejor->nombre); ?></p>
    <p style="color:var(--verde-dark);font-weight:800;font-size:1.1rem;">$<?php echo e(number_format($rentMejor->rentabilidad,0,',','.')); ?></p>
    <p style="font-size:.78rem;color:var(--text-secondary);">ROI: <?php echo e($rentMejor->roi); ?>%</p>
  </div>
  <?php endif; ?>
  <?php if($rentPeor && $rentPeor->rentabilidad < 0): ?>
  <div style="background:#fef2f2;border-radius:var(--radius-lg);padding:14px;border-left:3px solid var(--rojo);">
    <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:6px;">&#9888; Atencion</p>
    <p style="font-weight:700;"><?php echo e($rentPeor->nombre); ?></p>
    <p style="color:var(--rojo);font-weight:800;font-size:1.1rem;">$<?php echo e(number_format($rentPeor->rentabilidad,0,',','.')); ?></p>
    <p style="font-size:.78rem;color:var(--text-secondary);">ROI: <?php echo e($rentPeor->roi); ?>%</p>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if(count($rentChartLabels) > 0): ?>
<div class="rep-card">
  <div class="rep-card-title">Ingresos vs Gastos por cultivo</div>
  <canvas id="chartRentComp" height="220"></canvas>
</div>
<?php endif; ?>

<?php if($rentDatos->isEmpty()): ?>
<div class="empty-state"><div class="emoji">&#127807;</div><p>No hay cultivos con datos para <?php echo e($anio); ?>.</p></div>
<?php else: ?>
<?php $__currentLoopData = $rentDatos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $dPos  = $d->rentabilidad >= 0;
  $dMaxV = max($d->gastos, $d->ingresos, 1);
  $dPG   = round($d->gastos / $dMaxV * 100);
  $dPI   = round($d->ingresos / $dMaxV * 100);
  $dBadge= ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$d->estado] ?? 'badge-green';
?>
<div class="rep-card" style="border-left:3px solid <?php echo e($dPos ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">
  <div class="flex items-center justify-between mb-3">
    <div><div style="font-weight:700;"><?php echo e($d->nombre); ?></div><div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($d->tipo); ?></div></div>
    <div class="flex items-center gap-2">
      <span class="badge <?php echo e($dBadge); ?>"><?php echo e($d->estado); ?></span>
      <a href="<?php echo e(route('rentabilidad.detalle',$d->id)); ?>?anio=<?php echo e($anio); ?>" class="btn btn-sm btn-secondary" style="font-size:.78rem;">Detalle</a>
    </div>
  </div>
  <div style="margin-bottom:10px;">
    <div class="flex items-center justify-between mb-1"><span style="font-size:.78rem;color:var(--text-secondary);">Gastos</span><span style="font-size:.78rem;font-weight:700;color:var(--marron);">$<?php echo e(number_format($d->gastos,0,',','.')); ?></span></div>
    <div style="background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;"><div style="height:100%;border-radius:99px;background:var(--marron-light);width:<?php echo e($dPG); ?>%;"></div></div>
    <div class="flex items-center justify-between mt-2 mb-1"><span style="font-size:.78rem;color:var(--text-secondary);">Ingresos</span><span style="font-size:.78rem;font-weight:700;color:var(--verde-dark);">$<?php echo e(number_format($d->ingresos,0,',','.')); ?></span></div>
    <div style="background:#e5e7eb;border-radius:99px;height:7px;overflow:hidden;"><div style="height:100%;border-radius:99px;background:var(--verde-dark);width:<?php echo e($dPI); ?>%;"></div></div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;padding:10px;background:#f8fafc;border-radius:10px;">
    <div style="text-align:center;"><div style="font-weight:800;color:<?php echo e($dPos ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">$<?php echo e(number_format($d->rentabilidad,0,',','.')); ?></div><div style="font-size:.7rem;color:var(--text-secondary);">Balance</div></div>
    <div style="text-align:center;"><div style="font-weight:800;color:<?php echo e($d->roi >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;"><?php echo e($d->roi); ?>%</div><div style="font-size:.7rem;color:var(--text-secondary);">ROI</div></div>
    <div style="text-align:center;"><div style="font-weight:800;color:var(--text-secondary);"><?php echo e($d->margen); ?>%</div><div style="font-size:.7rem;color:var(--text-secondary);">Margen</div></div>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php elseif($tab === 'exportar'): ?>

<p style="font-size:.87rem;color:var(--text-secondary);margin-bottom:16px;">Genera reportes en PDF o Excel con todos los datos del año <strong><?php echo e($anio); ?></strong>.</p>


<div class="export-group">
  <div class="export-group-title">📋 Reporte general</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.reporte.pdf', ['anio'=>$anio])); ?>" class="exp-btn exp-pdf">📄 PDF General</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">🌱 Cultivos</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.cultivos.pdf')); ?>" class="exp-btn exp-pdf">📄 PDF</a>
    <a href="<?php echo e(route('exportar.cultivos.excel')); ?>" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">💰 Gastos</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.gastos.pdf', ['anio'=>$anio])); ?>" class="exp-btn exp-brown">📄 PDF</a>
    <a href="<?php echo e(route('exportar.gastos.excel', ['anio'=>$anio])); ?>" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">🌾 Cosechas</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.cosechas.pdf', ['anio'=>$anio])); ?>" class="exp-btn exp-orange">📄 PDF</a>
    <a href="<?php echo e(route('exportar.cosechas.excel', ['anio'=>$anio])); ?>" class="exp-btn exp-excel">📊 Excel</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">🐄 Animales</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.animales.pdf')); ?>" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">📦 Inventario</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.inventario.pdf')); ?>" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">👷 Nómina</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.nomina.pdf')); ?>?mes=<?php echo e(now()->format('Y-m')); ?>" class="exp-btn exp-purple">📄 PDF</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">🥛 Producción</div>
  <div class="export-btns">
    <a href="<?php echo e(route('exportar.produccion.pdf')); ?>?mes=<?php echo e(now()->format('Y-m')); ?>" class="exp-btn exp-purple">📄 PDF</a>
  </div>
</div>


<div class="export-group">
  <div class="export-group-title">💹 Rentabilidad</div>
  <div class="export-btns">
    <a href="<?php echo e(route('rentabilidad.index')); ?>" class="exp-btn exp-purple">💹 Ver detalle por cultivo</a>
    <a href="<?php echo e(route('exportar.rentabilidad.pdf')); ?>?anio=<?php echo e($anio); ?>" class="exp-btn exp-pdf">📄 PDF</a>
  </div>
</div>


<div style="background:#eff6ff;border-radius:var(--radius-lg);padding:14px;margin-top:8px;">
  <p style="font-size:.82rem;color:#1d4ed8;font-weight:600;margin-bottom:6px;">💡 Consejo para reportes</p>
  <p style="font-size:.8rem;color:#1e40af;">Los PDFs incluyen tablas detalladas con toda la información del año seleccionado. Puedes cambiar el año con las flechas ‹ › arriba de la página.</p>
</div>

<?php endif; ?>


<?php $__env->startPush('scripts'); ?>
<script>
Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
Chart.defaults.color = '#64748b';
const meses = <?php echo json_encode($meses, 15, 512) ?>;
const gastosArr   = <?php echo json_encode($gastosArr, 15, 512) ?>;
const ingresosArr = <?php echo json_encode($ingresosArr, 15, 512) ?>;
const balanceArr  = <?php echo json_encode($balanceArr, 15, 512) ?>;

<?php if($tab === 'resumen'): ?>
// Balance neto por mes
new Chart(document.getElementById('chartBalance'), {
  type:'bar',
  data:{ labels:meses, datasets:[{
    label:'Balance neto',
    data:balanceArr,
    backgroundColor: balanceArr.map(v => v>=0 ? 'rgba(61,139,61,.75)' : 'rgba(220,38,38,.65)'),
    borderRadius:5
  }]},
  options:{ responsive:true, plugins:{legend:{display:false}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' }, grid:{ color:'#f0f0f0' } } } }
});

// Ingresos vs Gastos
new Chart(document.getElementById('chartMensual'), {
  type:'bar',
  data:{ labels:meses, datasets:[
    { label:'Ingresos', data:ingresosArr, backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:gastosArr,   backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' }, grid:{ color:'#f0f0f0' } } } }
});

// Origen gastos
const origenLabels = ['Cultivos','Animales','Finca general'];
const origenData   = [<?php echo e(round($gastosCultivo,0)); ?>, <?php echo e(round($gastosAnimal,0)); ?>, <?php echo e(round($gastosGeneral,0)); ?>];
if(origenData.some(v=>v>0)) {
  new Chart(document.getElementById('chartGastosOrigen'), {
    type:'doughnut',
    data:{ labels:origenLabels, datasets:[{ data:origenData, backgroundColor:['#3d8b3d','#7c3aed','#ea580c'], borderWidth:2, borderColor:'#fff' }]},
    options:{ responsive:true, plugins:{ legend:{ position:'bottom', labels:{ boxWidth:12 } } } }
  });
}

<?php elseif($tab === 'finanzas'): ?>
// Ingresos por tipo
<?php $itLabels=$ingresosTipo->map(fn($t)=>$tiposLabels[$t->tipo]??ucfirst($t->tipo)); $itValues=$ingresosTipo->pluck('total'); ?>
<?php if($ingresosTipo->count()): ?>
new Chart(document.getElementById('chartIngresosTipo'), {
  type:'doughnut',
  data:{ labels:<?php echo json_encode($itLabels, 15, 512) ?>, datasets:[{ data:<?php echo json_encode($itValues, 15, 512) ?>, backgroundColor:['#3d8b3d','#ea580c','#7c3aed','#2563eb','#d97706','#0891b2','#dc2626','#64748b'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12,font:{size:11}}}} }
});
<?php endif; ?>

// Gastos por categoría (horizontal bar)
<?php $catL=$gastosCat->pluck('categoria'); $catV=$gastosCat->pluck('total'); ?>
<?php if($gastosCat->count()): ?>
new Chart(document.getElementById('chartCategorias'), {
  type:'bar',
  data:{ labels:<?php echo json_encode($catL, 15, 512) ?>, datasets:[{ label:'Gastos', data:<?php echo json_encode($catV, 15, 512) ?>, backgroundColor:'rgba(122,79,42,.7)', borderRadius:4 }]},
  options:{ indexAxis:'y', responsive:true, plugins:{legend:{display:false}},
    scales:{ x:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php endif; ?>

<?php elseif($tab === 'cultivos'): ?>
// Cosechas por mes
<?php if($valorCosechas > 0): ?>
new Chart(document.getElementById('chartCosechas'), {
  type:'bar',
  data:{ labels:meses, datasets:[{ label:'Valor cosechado', data:<?php echo json_encode($cosechasArr, 15, 512) ?>, backgroundColor:'rgba(234,88,12,.7)', borderRadius:4 }]},
  options:{ responsive:true, plugins:{legend:{display:false}}, scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php endif; ?>

// Cultivos por tipo (donut)
<?php $ctL=$cultivosTipo->pluck('tipo'); $ctV=$cultivosTipo->pluck('c'); ?>
<?php if($cultivosTipo->count()): ?>
new Chart(document.getElementById('chartCultivosDonut'), {
  type:'doughnut',
  data:{ labels:<?php echo json_encode($ctL, 15, 512) ?>, datasets:[{ data:<?php echo json_encode($ctV, 15, 512) ?>, backgroundColor:['#3d8b3d','#ea580c','#2563eb','#d97706','#7c3aed','#0891b2','#dc2626','#064748','#059669','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12}}} }
});
<?php endif; ?>

// Rentabilidad por cultivo (barras horizontales)
<?php if($rentCultivos->count()): ?>
<?php $rcL=$rentCultivos->pluck('nombre'); $rcG=$rentCultivos->pluck('gastos'); $rcI=$rentCultivos->pluck('ingresos'); ?>
new Chart(document.getElementById('chartRentCultivos'), {
  type:'bar',
  data:{ labels:<?php echo json_encode($rcL, 15, 512) ?>, datasets:[
    { label:'Ingresos', data:<?php echo json_encode($rcI, 15, 512) ?>, backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:<?php echo json_encode($rcG, 15, 512) ?>, backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ indexAxis:'y', responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ x:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php endif; ?>

<?php elseif($tab === 'animales'): ?>
// Animales por especie
<?php $aeL=$animalesPorEspecie->pluck('especie'); $aeV=$animalesPorEspecie->pluck('total'); ?>
<?php if($animalesPorEspecie->count()): ?>
new Chart(document.getElementById('chartAnimalesEspecie'), {
  type:'doughnut',
  data:{ labels:<?php echo json_encode($aeL, 15, 512) ?>, datasets:[{ data:<?php echo json_encode($aeV, 15, 512) ?>, backgroundColor:['#3d8b3d','#ea580c','#7c3aed','#2563eb','#d97706','#0891b2','#dc2626','#64748b','#059669','#7a4f2a'], borderWidth:2, borderColor:'#fff' }]},
  options:{ responsive:true, plugins:{legend:{position:'bottom',labels:{boxWidth:12}}} }
});
<?php endif; ?>

<?php elseif($tab === 'rentabilidad'): ?>
<?php if(count($rentChartLabels) > 0): ?>
new Chart(document.getElementById('chartRentComp'), {
  type:'bar',
  data:{ labels:<?php echo json_encode($rentChartLabels, 15, 512) ?>, datasets:[
    { label:'Ingresos', data:<?php echo json_encode($rentChartIngresos, 15, 512) ?>, backgroundColor:'rgba(61,139,61,.75)', borderRadius:4 },
    { label:'Gastos',   data:<?php echo json_encode($rentChartGastos, 15, 512) ?>,   backgroundColor:'rgba(122,79,42,.65)', borderRadius:4 }
  ]},
  options:{ responsive:true, plugins:{legend:{position:'bottom'}},
    scales:{ y:{ ticks:{ callback:v=>'$'+(v/1000).toFixed(0)+'k' } } } }
});
<?php endif; ?>
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/reportes.blade.php ENDPATH**/ ?>