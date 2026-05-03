
<?php $__env->startSection('title','Reportes Bovinos'); ?>
<?php $__env->startSection('page_title','📊 Reportes Bovinos'); ?>
<?php $__env->startSection('back_url', route('bovino.hato')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bovino.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="hato-stats" style="margin-bottom:14px;">
  <div class="hato-stat">
    <div class="hato-stat-ico">🥛</div>
    <div class="hato-stat-val"><?php echo e($vacasConLactancia->count()); ?></div>
    <div class="hato-stat-lbl">En producción</div>
  </div>
  <div class="hato-stat" style="border-left-color:#94a3b8;">
    <div class="hato-stat-ico">💤</div>
    <div class="hato-stat-val" style="color:#64748b;"><?php echo e($vacasSecas->count()); ?></div>
    <div class="hato-stat-lbl">Secas</div>
  </div>
  <div class="hato-stat" style="border-left-color:#3b82f6;">
    <div class="hato-stat-ico">📈</div>
    <div class="hato-stat-val" style="color:#1d4ed8;"><?php echo e($promedioHato); ?> L</div>
    <div class="hato-stat-lbl">Prom. L/vaca/día</div>
  </div>
  <div class="hato-stat" style="border-left-color:#f59e0b;">
    <div class="hato-stat-ico">📅</div>
    <div class="hato-stat-val" style="color:#b45309;"><?php echo e($ipPromedio ?? '—'); ?></div>
    <div class="hato-stat-lbl">IEP promedio (días)</div>
  </div>
</div>


<?php if(!empty($produccionPorVaca)): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🥛 Producción por vaca (últimos 30 días)</div>
  <div class="tabla-reporte">
    <div class="tabla-header"><span>Vaca</span><span>Raza</span><span>Promedio L/día</span><span>Total 30d</span><span>Días ordeñada</span></div>
    <?php $__currentLoopData = $produccionPorVaca; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $pct = $promedioHato > 0 ? min(100, round(($pv['promedio']/$promedioHato)*100)) : 0; ?>
    <div class="tabla-row">
      <span style="font-weight:600;"><?php echo e($pv['nombre']); ?></span>
      <span style="color:#94a3b8;font-size:.8rem;"><?php echo e($pv['raza'] ?? '—'); ?></span>
      <span>
        <span style="font-weight:700;color:<?php echo e($pv['promedio'] >= $promedioHato ? '#15803d' : '#dc2626'); ?>"><?php echo e($pv['promedio']); ?></span>
        <div style="background:#e2e8f0;border-radius:4px;height:5px;width:100%;margin-top:2px;overflow:hidden;">
          <div style="height:100%;background:#15803d;width:<?php echo e($pct); ?>%;"></div>
        </div>
      </span>
      <span><?php echo e($pv['total30d']); ?> L</span>
      <span><?php echo e($pv['dias']); ?>/30</span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php endif; ?>


<?php if(!empty($diasAbiertos)): ?>
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Días abiertos</div>
    <span style="font-size:.78rem;color:#64748b;">Prom: <strong><?php echo e($daPromedio ?? '—'); ?> días</strong></span>
  </div>
  <div style="font-size:.75rem;color:#64748b;margin-bottom:10px;">
    Meta: &lt;85 días · Alerta: 85-120 · Crítico: &gt;120
  </div>
  <?php $__currentLoopData = $diasAbiertos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $da): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $color = $da['estado']==='ok' ? '#22c55e' : ($da['estado']==='alerta' ? '#f59e0b' : '#ef4444');
    $bg    = $da['estado']==='ok' ? '#f0fdf4' : ($da['estado']==='alerta' ? '#fffbeb' : '#fef2f2');
  ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 10px;border-radius:8px;background:<?php echo e($bg); ?>;margin-bottom:6px;">
    <div>
      <strong><?php echo e($da['nombre']); ?></strong>
      <div style="font-size:.75rem;color:#64748b;">Último parto: <?php echo e(\Carbon\Carbon::parse($da['ultimo_parto'])->format('d/m/Y')); ?></div>
    </div>
    <div style="font-weight:800;font-size:1.1rem;color:<?php echo e($color); ?>;">
      <?php echo e($da['dias']); ?> días
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if(!empty($gpdPorAnimal)): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">⚖️ Ganancia diaria de peso (GPD)</div>
  <div class="tabla-reporte">
    <div class="tabla-header"><span>Animal</span><span>GPD (kg/día)</span><span>Peso actual</span><span>Meta</span><span>Estado</span></div>
    <?php $__currentLoopData = $gpdPorAnimal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $gc = $g['estado']==='bueno' ? '#15803d' : ($g['estado']==='regular' ? '#b45309' : '#dc2626');
      $gt = $g['estado']==='bueno' ? 'Excelente' : ($g['estado']==='regular' ? 'Normal' : 'Bajo');
    ?>
    <div class="tabla-row">
      <span style="font-weight:600;"><?php echo e($g['nombre']); ?></span>
      <span style="font-weight:800;color:<?php echo e($gc); ?>;"><?php echo e($g['gpd'] >= 0 ? '+' : ''); ?><?php echo e($g['gpd']); ?></span>
      <span><?php echo e($g['peso_actual'] ? $g['peso_actual'].' kg' : '—'); ?></span>
      <span><?php echo e($g['meta_kg'] ? $g['meta_kg'].' kg' : '—'); ?></span>
      <span style="font-size:.75rem;color:<?php echo e($gc); ?>;"><?php echo e($gt); ?></span>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php endif; ?>


<?php if($vacasSecas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">💤 Vacas secas</div>
  <?php $__currentLoopData = $vacasSecas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="padding:6px 0;border-bottom:1px solid #e2e8f0;font-size:.85rem;">
    <?php echo e($vs->nombre_lote); ?> <?php if($vs->raza): ?><span style="color:#94a3b8;">· <?php echo e($vs->raza); ?></span><?php endif; ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/bovino/reportes.blade.php ENDPATH**/ ?>