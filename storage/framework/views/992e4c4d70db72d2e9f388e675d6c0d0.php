
<?php $__env->startSection('title','Reportes Porcícola'); ?>
<?php $__env->startSection('page_title','📈 Reportes Porcícola'); ?>
<?php $__env->startSection('back_url', route('porcicola.piara')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card">
  <div class="section-title" style="margin-bottom:12px;">🐷 Métricas reproductivas</div>
  <div class="piara-stats">
    <div class="piara-stat">
      <div class="piara-stat-ico">🍼</div>
      <div class="piara-stat-val"><?php echo e($totalCamadas); ?></div>
      <div class="piara-stat-lbl">Camadas totales</div>
    </div>
    <div class="piara-stat verde">
      <div class="piara-stat-ico">🐷</div>
      <div class="piara-stat-val"><?php echo e($promedioNacidos); ?></div>
      <div class="piara-stat-lbl">Lechones/camada</div>
    </div>
    <div class="piara-stat azul">
      <div class="piara-stat-ico">✅</div>
      <div class="piara-stat-val"><?php echo e($promDestetados); ?></div>
      <div class="piara-stat-lbl">Destetados/camada</div>
    </div>
    <div class="piara-stat <?php echo e($pctMortPreD > 10 ? '' : 'naranja'); ?>">
      <div class="piara-stat-ico">💀</div>
      <div class="piara-stat-val" style="color:<?php echo e($pctMortPreD > 10 ? '#dc2626' : '#1e293b'); ?>;"><?php echo e($pctMortPreD); ?>%</div>
      <div class="piara-stat-lbl">Mort. pre-destete</div>
    </div>
  </div>
  <?php if($promedioNacidos > 0): ?>
  <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-top:8px;font-size:.78rem;">
    <?php if($promedioNacidos >= 11): ?> 🏆 Excelente prolificidad (> 11 lechones)
    <?php elseif($promedioNacidos >= 9): ?> ✅ Buena prolificidad (9-10 lechones)
    <?php else: ?> ⚠️ Prolificidad baja (< 9 lechones — revisar nutrición y manejo reproductivo)
    <?php endif; ?>
    · Eficiencia destete: <?php echo e($totalCamadas > 0 ? round($promDestetados / $promedioNacidos * 100, 1) : 0); ?>%
    · Mortalidad pre-destete: <?php echo e($pctMortPreD <= 8 ? '✅ Normal' : '❌ Alta (meta < 8%)'); ?>

  </div>
  <?php endif; ?>
</div>


<?php if($mejorCA): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🏆 Mejor conversión del mes</div>
  <div style="text-align:center;padding:10px;">
    <div style="font-size:.85rem;color:#64748b;"><?php echo e($mejorCA->nombre_lote); ?> · Semana <?php echo e($mejorCA->semana); ?></div>
    <div class="<?php echo e($mejorCA->conversion_alimenticia <= 2.8 ? 'ca-val-ok' : 'ca-val-med'); ?>" style="font-size:2rem;">
      <?php echo e($mejorCA->conversion_alimenticia); ?>

    </div>
    <div style="font-size:.75rem;color:#94a3b8;">kg alimento / kg ganancia</div>
  </div>
</div>
<?php endif; ?>


<?php if($hembrasParidad->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐷 Distribución de partos (hembras activas)</div>
  <?php $totalHembras = $hembrasParidad->sum('cantidad'); ?>
  <?php $__currentLoopData = $hembrasParidad; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $hp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:2px;">
      <span>
        <?php echo e($hp->num_partos == 0 ? 'Primerizas (0 partos)' : 'Parto #'.$hp->num_partos); ?>

        <?php if($hp->num_partos >= 8): ?><span style="font-size:.7rem;color:#dc2626;"> · Considerar descarte</span><?php endif; ?>
      </span>
      <strong><?php echo e($hp->cantidad); ?></strong>
    </div>
    <div style="background:#e2e8f0;border-radius:4px;height:6px;overflow:hidden;">
      <div style="width:<?php echo e($totalHembras > 0 ? round(($hp->cantidad/$totalHembras)*100) : 0); ?>%;
                  height:100%;border-radius:4px;
                  background:<?php echo e($hp->num_partos <= 6 ? '#ec4899' : '#94a3b8'); ?>;"></div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <div style="font-size:.75rem;color:#64748b;margin-top:6px;">
    💡 Vida productiva ideal de la cerda: partos 2-7. Después del parto 8, evaluar descarte.
  </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐖 Estado actual de la piara</div>
  <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $iconos = ['lechon'=>'🐷','levante'=>'🐖','ceba'=>'🏋️','hembra_cria'=>'🐷','verraco'=>'🐗','otro'=>'🐾'];
    $ico = $iconos[$l->categoria_porcina ?? 'otro'] ?? '🐾';
    $esHembra = $l->categoria_porcina === 'hembra_cria';
  ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:700;"><?php echo e($ico); ?> <?php echo e($l->nombre_lote); ?></div>
      <div style="font-size:.72rem;color:#64748b;">
        <?php echo e(str_replace('_',' ',$l->categoria_porcina ?? 'sin categoría')); ?>

        <?php if($l->raza_porcina): ?> · <?php echo e($l->raza_porcina); ?><?php endif; ?>
        <?php if($esHembra && ($l->num_partos ?? 0) > 0): ?> · <?php echo e($l->num_partos); ?> partos@endif
      </div>
    </div>
    <div style="text-align:right;">
      <div style="font-weight:700;color:#f97316;"><?php echo e(number_format($l->cantidad)); ?> animales</div>
      <?php if($l->peso_promedio): ?>
      <div style="font-size:.7rem;color:#94a3b8;"><?php echo e($l->peso_promedio); ?> kg prom.</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#94a3b8;">Sin lotes porcinos activos.</div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/porcicola/reportes.blade.php ENDPATH**/ ?>