<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: sans-serif; font-size: 11px; color: #1e293b; background: #fff; }

  .header { background: #3d8b3d; color: #fff; padding: 16px 20px; margin-bottom: 16px; }
  .header h1 { font-size: 20px; font-weight: 800; letter-spacing: -.5px; }
  .header p  { font-size: 11px; opacity: .85; margin-top: 3px; }

  .stats { display: flex; gap: 10px; margin: 0 20px 16px; }
  .stat  { flex: 1; background: #edf7ed; border-radius: 8px; padding: 10px; text-align: center; }
  .stat .value { font-size: 22px; font-weight: 800; color: #3d8b3d; }
  .stat .label { font-size: 10px; color: #64748b; margin-top: 2px; }

  table { width: calc(100% - 40px); margin: 0 20px; border-collapse: collapse; }
  thead th {
    background: #3d8b3d; color: #fff; padding: 7px 8px;
    text-align: left; font-size: 10px; font-weight: 700;
  }
  tbody tr:nth-child(even) { background: #f1f5f9; }
  tbody td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; }

  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: 700; }
  .badge-activo    { background: #edf7ed; color: #3d8b3d; }
  .badge-cosechado { background: #fff3e0; color: #9a3412; }
  .badge-vendido   { background: #fdf3ea; color: #7a4f2a; }

  .footer { text-align: center; color: #94a3b8; font-size: 9px; margin-top: 20px; padding: 0 20px; }
</style>
</head>
<body>

<div class="header">
  <h1>🌱 Reporte de Cultivos — Agrogranja</h1>
  <p><?php echo e($usuario->nombre_finca ?? 'Mi Finca'); ?> | Generado el <?php echo e(now()->format('d/m/Y H:i')); ?></p>
</div>

<div class="stats">
  <div class="stat"><div class="value"><?php echo e($stats['activos']); ?></div><div class="label">Activos</div></div>
  <div class="stat"><div class="value"><?php echo e($stats['cosechados']); ?></div><div class="label">Cosechados</div></div>
  <div class="stat"><div class="value"><?php echo e($stats['vendidos']); ?></div><div class="label">Vendidos</div></div>
  <div class="stat"><div class="value"><?php echo e(number_format($stats['total_area'],1)); ?></div><div class="label">Ha totales</div></div>
</div>

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Nombre / Lote</th>
      <th>Tipo</th>
      <th>Fecha siembra</th>
      <th>Área</th>
      <th>Estado</th>
      <th>Notas</th>
    </tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
      <td><?php echo e($i + 1); ?></td>
      <td><strong><?php echo e($c->nombre); ?></strong></td>
      <td><?php echo e($c->tipo); ?></td>
      <td><?php echo e($c->fecha_siembra ? \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') : '-'); ?></td>
      <td><?php echo e($c->area ? $c->area . ' ' . $c->unidad : '-'); ?></td>
      <td><span class="badge badge-<?php echo e($c->estado); ?>"><?php echo e(ucfirst($c->estado)); ?></span></td>
      <td style="color:#64748b;font-size:10px;"><?php echo e(Str::limit($c->notas, 40)); ?></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>

<div class="footer">
  Agrogranja — Sistema de Gestión para Fincas | Total: <?php echo e($cultivos->count()); ?> cultivos registrados
</div>
</body>
</html><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/exports/cultivos-pdf.blade.php ENDPATH**/ ?>