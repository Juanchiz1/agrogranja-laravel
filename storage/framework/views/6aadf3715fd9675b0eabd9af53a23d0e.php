<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:12px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
tr:nth-child(even) td{background:#f9fafb}
.badge-activo{color:#166534;background:#dcfce7;padding:2px 7px;border-radius:10px}
.badge-vendido{color:#92400e;background:#fef3c7;padding:2px 7px;border-radius:10px}
.stats{display:flex;gap:20px;margin-bottom:16px}
.stat{background:#f0fdf4;padding:10px 14px;border-radius:8px;border-left:3px solid #1A4731}
.stat-val{font-size:15px;font-weight:bold;color:#1A4731}
.stat-lbl{font-size:9px;color:#666}
.pos{color:#166534;font-weight:bold}
.neg{color:#dc2626;font-weight:bold}
</style></head><body>
<h1>🐄 Reporte de Animales</h1>
<div class="sub"><?php echo e($usuario->nombre_finca ?? $usuario->nombre); ?> · Generado <?php echo e(now()->format('d/m/Y H:i')); ?></div>

<table style="width:auto;margin-bottom:12px">
<tr>
  <td style="padding:6px 12px;background:#f0fdf4;border-radius:6px;text-align:center"><div class="stat-val"><?php echo e($stats['total']); ?></div><div class="stat-lbl">Total animales</div></td>
  <td style="width:10px"></td>
  <td style="padding:6px 12px;background:#f0fdf4;border-radius:6px;text-align:center"><div class="stat-val"><?php echo e($stats['activos']); ?></div><div class="stat-lbl">Activos</div></td>
  <td style="width:10px"></td>
  <td style="padding:6px 12px;background:#fef9c3;border-radius:6px;text-align:center"><div class="stat-val">$<?php echo e(number_format($stats['ingresos'],0,',','.')); ?></div><div class="stat-lbl">Total ingresos</div></td>
  <td style="width:10px"></td>
  <td style="padding:6px 12px;background:#fef2f2;border-radius:6px;text-align:center"><div class="stat-val">$<?php echo e(number_format($stats['gastos'],0,',','.')); ?></div><div class="stat-lbl">Total gastos</div></td>
</tr>
</table>

<table>
<thead><tr>
  <th>Animal / Lote</th><th>Especie</th><th>Cantidad</th>
  <th>Produce</th><th>Estado</th><th>Ingresos</th><th>Gastos</th><th>Balance</th>
</tr></thead>
<tbody>
<?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
  <td><?php echo e($a->nombre_lote ?? '—'); ?></td>
  <td><?php echo e($a->especie); ?></td>
  <td><?php echo e($a->cantidad); ?></td>
  <td><?php echo e($a->produccion ?? '—'); ?></td>
  <td><span class="<?php echo e($a->estado === 'activo' ? 'badge-activo' : 'badge-vendido'); ?>"><?php echo e($a->estado); ?></span></td>
  <td>$<?php echo e(number_format($a->total_ingresos,0,',','.')); ?></td>
  <td>$<?php echo e(number_format($a->total_gastos,0,',','.')); ?></td>
  <td class="<?php echo e($a->balance >= 0 ? 'pos' : 'neg'); ?>">$<?php echo e(number_format($a->balance,0,',','.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
</body></html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/animales-pdf.blade.php ENDPATH**/ ?>