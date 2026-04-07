<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:sans-serif; font-size:11px; color:#1e293b; }
  .header { background:#7a4f2a; color:#fff; padding:16px 20px; margin-bottom:14px; }
  .header h1 { font-size:18px; font-weight:800; }
  .header p  { font-size:11px; opacity:.85; margin-top:3px; }

  .stats { display:flex; gap:10px; margin:0 20px 14px; }
  .stat  { flex:1; background:#fdf3ea; border-radius:8px; padding:10px; text-align:center; }
  .stat .value { font-size:18px; font-weight:800; color:#7a4f2a; }
  .stat .label { font-size:10px; color:#64748b; }

  .section-title { font-size:12px; font-weight:700; color:#7a4f2a; margin:14px 20px 6px; text-transform:uppercase; letter-spacing:.5px; }

  table { width:calc(100% - 40px); margin:0 20px; border-collapse:collapse; }
  thead th { background:#7a4f2a; color:#fff; padding:6px 8px; text-align:left; font-size:10px; font-weight:700; }
  tbody tr:nth-child(even) { background:#fdf3ea; }
  tbody td { padding:5px 8px; border-bottom:1px solid #e2e8f0; }

  .total-row td { background:#7a4f2a; color:#fff; font-weight:700; padding:7px 8px; }
  .cat-table { width:calc(50% - 30px); margin:0 20px; }
  .footer { text-align:center; color:#94a3b8; font-size:9px; margin-top:16px; }
</style>
</head>
<body>

<div class="header">
  <h1>💰 Reporte de Gastos <?php echo e($anio); ?> — Agrogranja</h1>
  <p><?php echo e($usuario->nombre_finca ?? 'Mi Finca'); ?> | Generado el <?php echo e(now()->format('d/m/Y H:i')); ?></p>
</div>

<div class="stats">
  <div class="stat">
    <div class="value">$<?php echo e(number_format($totalAnio/1000,0)); ?>k</div>
    <div class="label">Total <?php echo e($anio); ?></div>
  </div>
  <div class="stat">
    <div class="value"><?php echo e($gastos->count()); ?></div>
    <div class="label">Registros</div>
  </div>
  <div class="stat">
    <div class="value"><?php echo e($porCategoria->count()); ?></div>
    <div class="label">Categorías</div>
  </div>
  <div class="stat">
    <div class="value">$<?php echo e(number_format($gastos->count() ? $totalAnio/$gastos->count() : 0, 0, ',', '.')); ?></div>
    <div class="label">Promedio</div>
  </div>
</div>

<div class="section-title">Por categoría</div>
<table class="cat-table">
  <thead><tr><th>Categoría</th><th>Total</th><th>%</th></tr></thead>
  <tbody>
    <?php $__currentLoopData = $porCategoria->sortByDesc(fn($v)=>$v); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($cat); ?></td>
      <td>$<?php echo e(number_format($total,0,',','.')); ?></td>
      <td><?php echo e($totalAnio > 0 ? round($total/$totalAnio*100,1) : 0); ?>%</td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>

<div class="section-title">Detalle de gastos</div>
<table>
  <thead>
    <tr>
      <th>#</th><th>Fecha</th><th>Categoría</th><th>Descripción</th>
      <th>Proveedor</th><th>Cultivo</th><th style="text-align:right">Valor</th>
    </tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $gastos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $g): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($i+1); ?></td>
      <td><?php echo e($g->fecha ? \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') : '-'); ?></td>
      <td><?php echo e($g->categoria); ?></td>
      <td><?php echo e($g->descripcion); ?></td>
      <td style="color:#64748b"><?php echo e($g->proveedor ?? '-'); ?></td>
      <td style="color:#64748b"><?php echo e($g->cultivo_nombre ?? '-'); ?></td>
      <td style="text-align:right;font-weight:600;color:#7a4f2a">$<?php echo e(number_format($g->valor,0,',','.')); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <tr class="total-row">
      <td colspan="6">TOTAL <?php echo e($anio); ?></td>
      <td style="text-align:right">$<?php echo e(number_format($totalAnio,0,',','.')); ?></td>
    </tr>
  </tbody>
</table>

<div class="footer">Agrogranja — Sistema de Gestión para Fincas</div>
</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/gastos-pdf.blade.php ENDPATH**/ ?>