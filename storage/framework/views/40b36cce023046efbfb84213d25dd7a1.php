<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:sans-serif; font-size:10px; color:#1e293b; }
  .header { background:#3d8b3d; color:#fff; padding:14px 20px; margin-bottom:14px; }
  .header h1 { font-size:17px; font-weight:800; }
  .header p  { font-size:10px; opacity:.85; margin-top:3px; }
  .stats { display:flex; gap:8px; margin:0 20px 12px; }
  .stat  { flex:1; background:#edf7ed; border-radius:6px; padding:8px; text-align:center; }
  .stat .value { font-size:16px; font-weight:800; color:#3d8b3d; }
  .stat .label { font-size:9px; color:#64748b; }
  table { width:calc(100% - 40px); margin:0 20px; border-collapse:collapse; }
  thead th { background:#3d8b3d; color:#fff; padding:6px 7px; font-size:9px; font-weight:700; }
  tbody tr:nth-child(even) { background:#edf7ed; }
  tbody td { padding:5px 7px; border-bottom:1px solid #e2e8f0; }
  .total-row td { background:#3d8b3d; color:#fff; font-weight:700; }
  .footer { text-align:center; color:#94a3b8; font-size:9px; margin-top:14px; }
</style>
</head>
<body>
<div class="header">
  <h1>🌾 Reporte de Cosechas <?php echo e($anio); ?></h1>
  <p><?php echo e($usuario->nombre_finca ?? 'Mi Finca'); ?> | Generado el <?php echo e(now()->format('d/m/Y H:i')); ?></p>
</div>

<div class="stats">
  <div class="stat"><div class="value"><?php echo e($cosechas->count()); ?></div><div class="label">Cosechas</div></div>
  <div class="stat"><div class="value"><?php echo e($porProducto->count()); ?></div><div class="label">Productos</div></div>
  <div class="stat"><div class="value">$<?php echo e(number_format($totalValor/1000,0)); ?>k</div><div class="label">Valor estimado</div></div>
</div>

<table>
  <thead>
    <tr><th>#</th><th>Fecha</th><th>Producto</th><th>Cantidad</th><th>Calidad</th><th>Destino</th><th>Comprador</th><th>Cultivo</th><th style="text-align:right">Valor</th></tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($i+1); ?></td>
      <td><?php echo e($cs->fecha_cosecha ? \Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y') : '-'); ?></td>
      <td><strong><?php echo e($cs->producto); ?></strong></td>
      <td><?php echo e(number_format($cs->cantidad,1)); ?> <?php echo e($cs->unidad); ?></td>
      <td><?php echo e(ucfirst($cs->calidad ?? '-')); ?></td>
      <td><?php echo e(ucfirst($cs->destino ?? '-')); ?></td>
      <td><?php echo e($cs->comprador ?? '-'); ?></td>
      <td style="color:#64748b"><?php echo e($cs->cultivo_nombre ?? '-'); ?></td>
      <td style="text-align:right;font-weight:600;color:#3d8b3d">$<?php echo e(number_format($cs->valor_estimado??0,0,',','.')); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="total-row">
      <td colspan="8">TOTAL</td>
      <td style="text-align:right">$<?php echo e(number_format($totalValor,0,',','.')); ?></td>
    </tr>
  </tbody>
</table>
<div class="footer">Agrogranja — Sistema de Gestión para Fincas</div>
</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/cosechas-pdf.blade.php ENDPATH**/ ?>