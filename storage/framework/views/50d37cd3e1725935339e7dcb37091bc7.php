<?php $__env->startSection('title','Inicio'); ?>
<?php $__env->startSection('page_title', '🌾 ' . (now()->hour < 12 ? 'Buenos días' : (now()->hour < 18 ? 'Buenas tardes' : 'Buenas noches')) . ', ' . explode(' ', $user->nombre)[0]); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/dashboard.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/dashboard-adaptativo.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php $balance = $ingresosMes - $gastosMes; ?>


<?php if(!session('encuesta_respondida_en') || \Carbon\Carbon::parse(session('encuesta_respondida_en'))->diffInDays(now()) > 90): ?>
<div class="encuesta-banner">
    <div class="encuesta-banner-text">
        <p class="encuesta-banner-titulo">📋 ¿Cómo te ha ido con Agrogranja?</p>
        <p class="encuesta-banner-sub">Responde una encuesta rápida · solo 5 minutos</p>
    </div>
    <div class="encuesta-banner-actions">
        <a href="<?php echo e(route('encuesta.show')); ?>" class="encuesta-banner-btn">
            Responder encuesta
        </a>
        <form method="POST" action="<?php echo e(route('encuesta.ignorar')); ?>" style="margin:0;">
            <?php echo csrf_field(); ?>
            <button type="submit" class="encuesta-banner-close" title="Cerrar">×</button>
        </form>
    </div>
</div>
<?php endif; ?>


<?php if($tareasVencidas > 0): ?>
<div class="dash-alert rojo" onclick="location.href='<?php echo e(route('calendario.index')); ?>?tab=vencidas'">
  <span>⚠️ <?php echo e($tareasVencidas); ?> tarea(s) vencida(s) sin completar</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
<?php endif; ?>

<?php if($alertasInventario > 0): ?>
<div class="dash-alert" onclick="location.href='<?php echo e(route('inventario.alertas')); ?>'">
  <span>📦 <?php echo e($alertasInventario); ?> insumo(s) con stock bajo mínimo</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
<?php endif; ?>

<?php if(\App\Models\LineaProductiva::tieneAnimales() && $proximasDosis->count()): ?>
<div class="dash-alert azul" onclick="location.href='<?php echo e(route('animales.index')); ?>'">
  <span>💊 <?php echo e($proximasDosis->count()); ?> dosis de medicamento próxima(s)</span>
  <span style="font-size:.8rem;">Ver →</span>
</div>
<?php endif; ?>


<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:14px;">
  <?php if(in_array('cultivos', $lineasActivas)): ?>
    <div class="quick-stat"><div class="quick-stat-val"><?php echo e($cultivosActivos); ?></div><div class="quick-stat-lbl">Cultivos activos</div></div>
  <?php elseif(\App\Models\LineaProductiva::tieneAnimales()): ?>
    <div class="quick-stat"><div class="quick-stat-val"><?php echo e($animalesActivos); ?></div><div class="quick-stat-lbl">Animales activos</div></div>
  <?php else: ?>
    <div class="quick-stat"><div class="quick-stat-val"><?php echo e($cultivosActivos); ?></div><div class="quick-stat-lbl">Cultivos activos</div></div>
  <?php endif; ?>
  <div class="quick-stat"><div class="quick-stat-val" style="color:var(--marron);">$<?php echo e(number_format($gastosMes/1000,0)); ?>k</div><div class="quick-stat-lbl">Gastos mes</div></div>
  <div class="quick-stat"><div class="quick-stat-val text-green">$<?php echo e(number_format($ingresosMes/1000,0)); ?>k</div><div class="quick-stat-lbl">Ingresos mes</div></div>
  <div class="quick-stat" style="<?php echo e($tareasPend>0?'background:#fef2f2':''); ?>"><div class="quick-stat-val" style="<?php echo e($tareasPend>0?'color:var(--rojo)':''); ?>"><?php echo e($tareasPend); ?></div><div class="quick-stat-lbl">Tareas pend.</div></div>
</div>


<?php if(!empty($kpisLineas)): ?>
<div class="kpis-lineas-grid">
  <?php $__currentLoopData = $kpisLineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigo => $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="kpi-linea-card kpi-color-<?php echo e($kpi['color']); ?>">
    <div class="kpi-linea-header">
      <span class="kpi-linea-emoji"><?php echo e($kpi['emoji']); ?></span>
      <span class="kpi-linea-titulo"><?php echo e($kpi['titulo']); ?></span>
    </div>
    <div class="kpi-linea-metricas">
      <?php $__currentLoopData = $kpi['metricas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div class="kpi-linea-metrica">
        <div class="kpi-linea-valor"><?php echo e($m['valor']); ?></div>
        <div class="kpi-linea-label"><?php echo e($m['label']); ?></div>
        <?php if(!empty($m['sub'])): ?>
          <div class="kpi-linea-sub"><?php echo e($m['sub']); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="card mb-3" style="background:<?php echo e($balance >= 0 ? 'var(--verde-bg)' : '#fef2f2'); ?>">
  <div class="flex items-center justify-between">
    <div>
      <p class="text-xs" style="font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--gris);">Balance <?php echo e(now()->locale('es')->monthName); ?></p>
      <p style="font-size:1.7rem;font-weight:800;color:<?php echo e($balance >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;margin-top:4px;">
        $<?php echo e(number_format($balance, 0, ',', '.')); ?>

      </p>
    </div>
    <div style="text-align:right;">
      <p class="text-xs text-gray">Ingresos: <strong class="text-green">$<?php echo e(number_format($ingresosMes,0,',','.')); ?></strong></p>
      <p class="text-xs text-gray mt-1">Gastos: <strong style="color:var(--rojo)">$<?php echo e(number_format($gastosMes,0,',','.')); ?></strong></p>
      <?php if($pagadoMesPersonas > 0): ?>
      <p class="text-xs text-gray mt-1">Mano de obra: <strong style="color:#9a3412">$<?php echo e(number_format($pagadoMesPersonas,0,',','.')); ?></strong></p>
      <?php endif; ?>
    </div>
  </div>
</div>


<div class="menu-grid">
  <?php if(in_array('cultivos', $lineasActivas)): ?>
  <a href="<?php echo e(route('cultivos.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#edf7ed;">🌱</div><span class="menu-label">Cultivos</span></a>
  <?php endif; ?>

  <a href="<?php echo e(route('gastos.index')); ?>"      class="menu-card"><div class="menu-icon" style="background:#fdf3ea;">💰</div><span class="menu-label">Gastos</span></a>
  <a href="<?php echo e(route('ingresos.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#eff6ff;">📈</div><span class="menu-label">Ingresos</span></a>
  <a href="<?php echo e(route('calendario.index')); ?>"  class="menu-card"><div class="menu-icon" style="background:#fdf2f8;">📅</div><span class="menu-label">Agenda</span></a>

  <?php if(\App\Models\LineaProductiva::tieneAnimales()): ?>
  <a href="<?php echo e(route('animales.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#f5f3ff;">🐄</div><span class="menu-label">Animales</span></a>
  <?php endif; ?>

  <a href="<?php echo e(route('personas.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#fdf4ff;">👥</div><span class="menu-label">Personas</span></a>

  <?php if(in_array('cultivos', $lineasActivas)): ?>
  <a href="<?php echo e(route('cosechas.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#f0fdf4;">🌾</div><span class="menu-label">Cosechas</span></a>
  <?php endif; ?>

  <a href="<?php echo e(route('inventario.index')); ?>"  class="menu-card"><div class="menu-icon" style="background:#fff7ed;">📦</div><span class="menu-label">Inventario</span></a>
  <a href="<?php echo e(route('reportes.index')); ?>"    class="menu-card"><div class="menu-icon" style="background:#eef2ff;">📊</div><span class="menu-label">Reportes</span></a>
</div>


<?php if($tareasHoy->count()): ?>
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">📌 Tareas de hoy</p>
  <a href="<?php echo e(route('calendario.index')); ?>?tab=hoy" class="text-sm text-green font-bold">Ver todas</a>
</div>
<?php $tiposIcono=['riego'=>'💧','vacunacion'=>'💉','cosecha'=>'🌾','fertilizacion'=>'🌿','fumigacion'=>'🧴','poda'=>'✂️','otro'=>'📝','medicamento'=>'💊','traslado_animal'=>'🏃','parto'=>'🐣','pago_proveedor'=>'🏪']; ?>
<?php $__currentLoopData = $tareasHoy; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $ic=$tiposIcono[$t->tipo]??'📝'; $pc=['alta'=>'var(--rojo)','media'=>'#d97706','baja'=>'var(--verde-dark)'][$t->prioridad]??'var(--text-secondary)'; ?>
<div class="list-item">
  <div class="item-icon" style="background:var(--verde-bg)"><?php echo e($ic); ?></div>
  <div class="item-body">
    <div class="item-title"><?php echo e($t->titulo); ?></div>
    <div class="item-sub" style="color:<?php echo e($pc); ?>"><?php echo e(ucfirst($t->prioridad)); ?></div>
  </div>
  <form method="POST" action="<?php echo e(route('tareas.completar',$t->id)); ?>"><?php echo csrf_field(); ?>
    <button class="btn btn-sm btn-secondary">✓</button>
  </form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if(in_array('cultivos', $lineasActivas) && $recentCultivos->count()): ?>
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌱 Cultivos recientes</p>
  <a href="<?php echo e(route('cultivos.index')); ?>" class="text-sm text-green font-bold">Ver todos</a>
</div>
<?php $__currentLoopData = $recentCultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $b=['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$c->estado]??'badge-green'; ?>
<div class="list-item" style="cursor:pointer;" onclick="window.location='<?php echo e(route('cultivos.show',$c->id)); ?>'">
  <div class="item-icon" style="background:var(--verde-bg)">🌿</div>
  <div class="item-body">
    <div class="item-title"><?php echo e($c->nombre); ?></div>
    <div class="item-sub"><?php echo e($c->tipo); ?> · <?php echo e(\Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y')); ?></div>
  </div>
  <span class="badge <?php echo e($b); ?>"><?php echo e($c->estado); ?></span>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if(in_array('cultivos', $lineasActivas) && $cosechasRecientes->count()): ?>
<div class="flex items-center justify-between mt-3 mb-2">
  <p class="section-title" style="margin:0;">🌾 Últimas cosechas</p>
  <a href="<?php echo e(route('cosechas.index')); ?>" class="text-sm text-green font-bold">Ver todas</a>
</div>
<?php $__currentLoopData = $cosechasRecientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $co): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="list-item">
  <div class="item-icon" style="background:#f0fdf4">🌾</div>
  <div class="item-body">
    <div class="item-title"><?php echo e($co->producto); ?></div>
    <div class="item-sub"><?php echo e(number_format($co->cantidad,1)); ?> <?php echo e($co->unidad); ?> · <?php echo e(\Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y')); ?></div>
  </div>
  <?php if($co->valor_estimado): ?><span style="font-weight:700;color:var(--verde-dark);font-size:.87rem;">$<?php echo e(number_format($co->valor_estimado,0,',','.')); ?></span><?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<?php if(in_array('cultivos', $lineasActivas) && $topCultivo && $topCultivo->rentabilidad > 0): ?>
<div style="background:var(--verde-bg);border-radius:var(--radius-lg);padding:12px 14px;margin-top:16px;border-left:3px solid var(--verde-dark);">
  <p style="font-size:.72rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:4px;">⭐ Cultivo más rentable <?php echo e(now()->year); ?></p>
  <div class="flex justify-between items-center">
    <div>
      <div style="font-weight:700;"><?php echo e($topCultivo->nombre); ?></div>
      <div style="font-size:.78rem;color:var(--text-secondary);"><?php echo e($topCultivo->tipo); ?></div>
    </div>
    <div style="text-align:right;">
      <div style="font-weight:800;color:var(--verde-dark);font-size:1rem;">$<?php echo e(number_format($topCultivo->rentabilidad,0,',','.')); ?></div>
      <a href="<?php echo e(route('rentabilidad.detalle',$topCultivo->id)); ?>" style="font-size:.75rem;color:var(--verde-dark);">Ver detalle →</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/dashboard.blade.php ENDPATH**/ ?>