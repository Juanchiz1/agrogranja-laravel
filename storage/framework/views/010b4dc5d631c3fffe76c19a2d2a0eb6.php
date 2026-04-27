<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:8px;margin-bottom:14px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
tr:nth-child(even) td{background:#f9fafb}
.vendido{color:#166534;font-weight:bold}
</style></head><body>
<h1>🥛 Producción Animal — <?php echo e($mesNombre); ?></h1>
<div class="sub"><?php echo e($usuario->nombre_finca ?? $usuario->nombre); ?> · Generado <?php echo e(now()->format('d/m/Y H:i')); ?></div>

<table style="width:auto;margin-bottom:14px">
<thead><tr><th>Tipo</th><th>Total producido</th><th>Vendido</th><th>Días</th><th>Valor total</th></tr></thead>
<tbody>
<?php $__currentLoopData = $resumen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
  <td><?php echo e(ucfirst($r->tipo)); ?></td>
  <td><?php echo e(number_format($r->total_qty,1)); ?> <?php echo e($r->unidad); ?></td>
  <td><?php echo e(number_format($r->vendido,1)); ?> <?php echo e($r->unidad); ?></td>
  <td><?php echo e($r->dias); ?> registros</td>
  <td><?php echo e($r->total_valor ? '$'.number_format($r->total_valor,0,',','.') : '—'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>

<table>
<thead><tr><th>Fecha</th><th>Animal</th><th>Tipo</th><th>Cantidad</th><th>Precio unit.</th><th>Valor</th><th>Estado</th><th>Comprador</th></tr></thead>
<tbody>
<?php $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
  <td><?php echo e(\Carbon\Carbon::parse($r->fecha)->format('d/m/Y')); ?></td>
  <td><?php echo e($r->nombre_lote ?? $r->especie); ?></td>
  <td><?php echo e(ucfirst($r->tipo_produccion)); ?></td>
  <td><?php echo e($r->cantidad); ?> <?php echo e($r->unidad); ?></td>
  <td><?php echo e($r->precio_unitario ? '$'.number_format($r->precio_unitario,0,',','.') : '—'); ?></td>
  <td><?php echo e($r->valor_total ? '$'.number_format($r->valor_total,0,',','.') : '—'); ?></td>
  <td class="<?php echo e($r->vendido?'vendido':''); ?>"><?php echo e($r->vendido?'Vendido':'Sin vender'); ?></td>
  <td><?php echo e($r->comprador ?? '—'); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
</body></html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/produccion-pdf.blade.php ENDPATH**/ ?>