

<?php $__env->startSection('title', 'Reportes Avícolas'); ?>
<?php $__env->startSection('page_title', '📈 Reportes Avícolas'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">
    🥚 Resumen mensual de producción
  </div>

  <?php if($prodMes && (($prodMes->total ?? 0) > 0)): ?>
    <div class="galpon-stats">
      <div class="galpon-stat">
        <div class="galpon-stat-ico">🥚</div>
        <div class="galpon-stat-val"><?php echo e(number_format($prodMes->total ?? 0)); ?></div>
        <div class="galpon-stat-lbl">Huevos totales mes</div>
      </div>

      <div class="galpon-stat verde">
        <div class="galpon-stat-ico">📊</div>
        <div class="galpon-stat-val">
          <?php echo e(isset($prodMes->pct_prom) && $prodMes->pct_prom !== null ? round($prodMes->pct_prom, 1).'%' : 'N/R'); ?>

        </div>
        <div class="galpon-stat-lbl">% postura promedio</div>
      </div>

      <div class="galpon-stat azul">
        <div class="galpon-stat-ico">🥚</div>
        <div class="galpon-stat-val">
          <?php echo e(number_format(($prodMes->aa ?? 0) + ($prodMes->a_cls ?? 0) + ($prodMes->b_cls ?? 0))); ?>

        </div>
        <div class="galpon-stat-lbl">Huevos comerciales</div>
      </div>

      <div class="galpon-stat rojo">
        <div class="galpon-stat-ico">💔</div>
        <div class="galpon-stat-val"><?php echo e(number_format($prodMes->bajas ?? 0)); ?></div>
        <div class="galpon-stat-lbl">Rotos/sucios</div>
      </div>
    </div>

    <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-top:8px;font-size:.78rem;">
      AA: <?php echo e(number_format($prodMes->aa ?? 0)); ?>

      · A: <?php echo e(number_format($prodMes->a_cls ?? 0)); ?>

      · B: <?php echo e(number_format($prodMes->b_cls ?? 0)); ?>

      · Bajas: <?php echo e(number_format($prodMes->bajas ?? 0)); ?>

      <br>
      <span style="color:#64748b;">
        💡 Meta sugerida: postura alta y baja proporción de huevos rotos o sucios.
      </span>
    </div>
  <?php else: ?>
    <div style="text-align:center;padding:16px;color:#94a3b8;">
      Aún no hay registros de postura este mes.
    </div>
  <?php endif; ?>
</div>

<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐔 Producción por lote (mes actual)</div>

  <?php $__empty_1 = true; $__currentLoopData = $prodPorLote; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
      $pctPostura = $r->pct_prom !== null ? round($r->pct_prom, 1) : null;
      $huevosAve = ($r->cantidad ?? 0) > 0 ? round($r->total / $r->cantidad, 1) : null;
    ?>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
      <div>
        <div style="font-weight:700;">🐔 <?php echo e($r->nombre_lote); ?></div>

        <div style="font-size:.72rem;color:#64748b;">
          <?php echo e(number_format($r->cantidad)); ?> aves

          <?php if($pctPostura !== null): ?>
            <span> · <?php echo e($pctPostura); ?>% postura prom.</span>
          <?php endif; ?>

          <?php if($huevosAve !== null): ?>
            <span> · <?php echo e($huevosAve); ?> huevos/ave mes</span>
          <?php endif; ?>
        </div>
      </div>

      <div style="text-align:right;">
        <div style="font-weight:700;color:#f97316;">
          <?php echo e(number_format($r->total)); ?> huevos
        </div>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div style="text-align:center;padding:20px;color:#94a3b8;">
      Sin producción registrada por lote en el mes.
    </div>
  <?php endif; ?>
</div>

<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">💀 Mortalidad del mes</div>

  <?php if($mortMes->count()): ?>
    <?php
      $totalMuertesMes = $mortMes->sum('total');
    ?>

    <?php $__currentLoopData = $mortMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $pct = $totalMuertesMes > 0 ? round(($m->total / $totalMuertesMes) * 100, 1) : 0;

        switch ($m->causa) {
          case 'enfermedad_respiratoria':
            $label = 'Enf. respiratoria';
            break;
          case 'enfermedad_digestiva':
            $label = 'Enf. digestiva';
            break;
          case 'estres_calor':
            $label = 'Estrés por calor';
            break;
          case 'trauma':
            $label = 'Trauma / aplastamiento';
            break;
          case 'predador':
            $label = 'Predador';
            break;
          case 'causa_desconocida':
            $label = 'Causa desconocida';
            break;
          default:
            $label = ucfirst(str_replace('_', ' ', $m->causa));
            break;
        }
      ?>

      <div style="margin-bottom:8px;">
        <div style="display:flex;justify-content:space-between;font-size:.82rem;margin-bottom:2px;">
          <span><?php echo e($label); ?></span>
          <span><?php echo e($m->total); ?> aves · <?php echo e($pct); ?>%</span>
        </div>
        <div style="background:#fee2e2;border-radius:4px;height:6px;overflow:hidden;">
          <div style="width:<?php echo e($pct); ?>%;height:100%;border-radius:4px;background:#dc2626;"></div>
        </div>
      </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php else: ?>
    <div style="text-align:center;padding:20px;color:#94a3b8;">
      No hay registros de mortalidad este mes.
    </div>
  <?php endif; ?>
</div>

<?php if($mejorCA): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🏆 Mejor conversión alimenticia del mes</div>
  <div style="text-align:center;padding:10px;">
    <div style="font-size:.85rem;color:#64748b;">
      <?php echo e($mejorCA->nombre_lote); ?> · <?php echo e($mejorCA->tipo === 'engorde' ? 'Engorde' : 'Postura'); ?>

    </div>

    <div style="font-size:2rem; color:<?php echo e($mejorCA->conversion_alimenticia <= 2.2 ? '#16a34a' : '#ea580c'); ?>;">
      <?php echo e($mejorCA->conversion_alimenticia); ?>

    </div>

    <div style="font-size:.75rem;color:#94a3b8;">
      kg alimento / kg producción
    </div>
  </div>
</div>
<?php endif; ?>

<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐣 Lotes avícolas activos</div>

  <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <?php
      $iconos = [
        'Gallinas' => '🐔',
        'Patos' => '🦆',
        'Pavos' => '🦃',
        'Codornices' => '🐥',
        'Aves de corral' => '🐓',
      ];

      $ico = $iconos[$l->especie] ?? '🐦';

      $etapaLabel = [
        'cria' => 'Cría 0-6 sem',
        'levante' => 'Levante 7-18 sem',
        'postura_produccion' => 'Postura / Producción',
        'desconocida' => 'Etapa desconocida',
      ][$l->etapa] ?? 'Etapa desconocida';
    ?>

    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
      <div>
        <div style="font-weight:700;"><?php echo e($ico); ?> <?php echo e($l->nombre_lote); ?></div>
        <div style="font-size:.72rem;color:#64748b;">
          <?php echo e($l->especie); ?>


          <?php if($l->tipo_ave): ?>
            <span> · <?php echo e(str_replace('_', ' ', $l->tipo_ave)); ?></span>
          <?php endif; ?>

          <?php if($l->linea_ave): ?>
            <span> · <?php echo e($l->linea_ave); ?></span>
          <?php endif; ?>

          <?php if($l->semanas !== null): ?>
            <span> · <?php echo e($l->semanas); ?> semanas</span>
          <?php endif; ?>

          <span> · <?php echo e($etapaLabel); ?></span>
        </div>
      </div>

      <div style="text-align:right;">
        <div style="font-weight:700;color:#f97316;">
          <?php echo e(number_format($l->cantidad)); ?> aves
        </div>
      </div>
    </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div style="text-align:center;padding:20px;color:#94a3b8;">
      No hay lotes avícolas activos.
    </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/reportes.blade.php ENDPATH**/ ?>