<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:12px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
tr:nth-child(even) td{background:#f9fafb}
.pos{color:#166534;font-weight:bold}
.neg{color:#dc2626;font-weight:bold}
.total-row td{font-weight:bold;background:#f0fdf4;border-top:2px solid #1A4731}
</style></head><body>
<h1>📊 Rentabilidad por Cultivo — <?php echo e($anio); ?></h1>
<div class="sub"><?php echo e($usuario->nombre_finca ?? $usuario->nombre); ?> · Generado <?php echo e(now()->format('d/m/Y H:i')); ?></div>

<table style="width:auto;margin-bottom:16px">
<tr>
  <td style="padding:8px 14px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#1A4731">$<?php echo e(number_format($resumen['total_ingresos'],0,',','.')); ?></div><div style="font-size:9px;color:#666">Total ingresos</div></td>
  <td style="width:8px"></td>
  <td style="padding:8px 14px;background:#fef2f2;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#dc2626">$<?php echo e(number_format($resumen['total_costos'],0,',','.')); ?></div><div style="font-size:9px;color:#666">Total costos</div></td>
  <td style="width:8px"></td>
  <td style="padding:8px 14px;background:<?php echo e($resumen['total_rent']>=0?'#f0fdf4':'#fef2f2'); ?>;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:<?php echo e($resumen['total_rent']>=0?'#1A4731':'#dc2626'); ?>">$<?php echo e(number_format($resumen['total_rent'],0,',','.')); ?></div><div style="font-size:9px;color:#666">Balance neto</div></td>
</tr>
</table>

<table>
<thead><tr>
  <th>Cultivo</th><th>Tipo</th><th>Estado</th>
  <th>Ingresos</th><th>Insumos</th><th>M. de obra</th><th>Costo total</th><th>Balance</th><th>ROI</th>
</tr></thead>
<tbody>
<?php $__currentLoopData = $datos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
  <td><?php echo e($d->nombre); ?></td>
  <td><?php echo e($d->tipo); ?></td>
  <td><?php echo e($d->estado); ?></td>
  <td>$<?php echo e(number_format($d->ingresos,0,',','.')); ?></td>
  <td>$<?php echo e(number_format($d->gastos,0,',','.')); ?></td>
  <td>$<?php echo e(number_format($d->mano_obra,0,',','.')); ?></td>
  <td>$<?php echo e(number_format($d->costo_total,0,',','.')); ?></td>
  <td class="<?php echo e($d->rentabilidad>=0?'pos':'neg'); ?>">$<?php echo e(number_format($d->rentabilidad,0,',','.')); ?></td>
  <td class="<?php echo e($d->roi>=0?'pos':'neg'); ?>"><?php echo e($d->roi); ?>%</td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<tr class="total-row">
  <td colspan="3">TOTAL</td>
  <td>$<?php echo e(number_format($resumen['total_ingresos'],0,',','.')); ?></td>
  <td colspan="2"></td>
  <td>$<?php echo e(number_format($resumen['total_costos'],0,',','.')); ?></td>
  <td class="<?php echo e($resumen['total_rent']>=0?'pos':'neg'); ?>">$<?php echo e(number_format($resumen['total_rent'],0,',','.')); ?></td>
  <td></td>
</tr>
</tbody>
</table>

<?php if($resumen['mejor']): ?>
<p style="margin-top:12px;font-size:10px;color:#166534;">⭐ Mejor cultivo: <strong><?php echo e($resumen['mejor']->nombre); ?></strong> con $<?php echo e(number_format($resumen['mejor']->rentabilidad,0,',','.')); ?> de ganancia (ROI: <?php echo e($resumen['mejor']->roi); ?>%)</p>
<?php endif; ?>
</body></html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/rentabilidad-pdf.blade.php ENDPATH**/ ?>