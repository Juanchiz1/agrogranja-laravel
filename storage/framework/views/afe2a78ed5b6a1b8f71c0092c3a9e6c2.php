<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:8px;margin-bottom:16px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
.trabajador-title{background:#f0fdf4;font-weight:bold;color:#1A4731;padding:8px}
.total-row td{font-weight:bold;background:#f9fafb;border-top:2px solid #1A4731}
</style></head><body>
<h1>👷 Nómina — <?php echo e($mesNombre); ?></h1>
<div class="sub"><?php echo e($usuario->nombre_finca ?? $usuario->nombre); ?> · Generado <?php echo e(now()->format('d/m/Y H:i')); ?></div>

<table style="width:auto;margin-bottom:16px">
<tr>
  <td style="padding:8px 16px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:16px;font-weight:bold;color:#1A4731">$<?php echo e(number_format($totalNomina,0,',','.')); ?></div><div style="font-size:9px;color:#666">Total pagado</div></td>
  <td style="width:12px"></td>
  <td style="padding:8px 16px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:16px;font-weight:bold;color:#1A4731"><?php echo e($porTrabajador->count()); ?></div><div style="font-size:9px;color:#666">Trabajadores</div></td>
  <td style="width:12px"></td>
  <td style="padding:8px 16px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:16px;font-weight:bold;color:#1A4731"><?php echo e($totalDias); ?></div><div style="font-size:9px;color:#666">Días trabajados</div></td>
</tr>
</table>

<?php $__currentLoopData = $porTrabajador; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="trabajador-title"><?php echo e($t->nombre); ?><?php echo e($t->cargo ? ' — '.$t->cargo : ''); ?> · Total: $<?php echo e(number_format($t->total,0,',','.')); ?><?php echo e($t->dias ? ' · '.$t->dias.' días' : ''); ?></div>
<table>
<thead><tr><th>Fecha</th><th>Tipo</th><th>Días</th><th>Concepto</th><th>Cultivo / Animal</th><th>Valor</th></tr></thead>
<tbody>
<?php $__currentLoopData = $t->pagos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<tr>
  <td><?php echo e(\Carbon\Carbon::parse($p->fecha)->format('d/m/Y')); ?></td>
  <td><?php echo e(ucfirst($p->tipo_pago)); ?></td>
  <td><?php echo e($p->dias ?? '—'); ?></td>
  <td><?php echo e($p->concepto ?? '—'); ?></td>
  <td><?php echo e($p->cultivo_nombre ?? $p->animal_nombre ?? '—'); ?></td>
  <td>$<?php echo e(number_format($p->valor,0,',','.')); ?></td>
</tr>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</tbody>
</table>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body></html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/nomina-pdf.blade.php ENDPATH**/ ?>