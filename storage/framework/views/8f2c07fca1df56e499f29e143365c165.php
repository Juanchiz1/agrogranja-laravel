
<?php $__env->startSection('title','Piara Porcícola'); ?>
<?php $__env->startSection('page_title','🐷 Piara Porcícola'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $iconosCat = [
    'lechon'           => ['🐷','Lechones','cat-lechon'],
    'levante'          => ['🐖','Levante','cat-levante'],
    'ceba'             => ['🏋️','Ceba','cat-ceba'],
    'hembra_cria'      => ['🐷','Hembras cría','cat-hembra'],
    'verraco'          => ['🐗','Verracos','cat-verraco'],
    'vientre_descarte' => ['🔴','Descarte','cat-lechon'],
    'otro'             => ['🐾','Otros',''],
  ];
?>


<div class="piara-stats">
  <div class="piara-stat">
    <div class="piara-stat-ico">🐷</div>
    <div class="piara-stat-val"><?php echo e(number_format($totalCerdos)); ?></div>
    <div class="piara-stat-lbl">Total piara</div>
  </div>
  <div class="piara-stat naranja">
    <div class="piara-stat-ico">🤰</div>
    <div class="piara-stat-val"><?php echo e($hembrasPreniadas); ?></div>
    <div class="piara-stat-lbl">Preñadas</div>
  </div>
  <div class="piara-stat azul">
    <div class="piara-stat-ico">🍼</div>
    <div class="piara-stat-val"><?php echo e($enLactancia); ?></div>
    <div class="piara-stat-lbl">En lactancia</div>
  </div>
  <div class="piara-stat verde">
    <div class="piara-stat-ico">🏃</div>
    <div class="piara-stat-val"><?php echo e($desteteProximos->count()); ?></div>
    <div class="piara-stat-lbl">Destetar pronto</div>
  </div>
</div>


<?php if($partosProximos->count() || $desteteProximos->count() || $alertasSanidad->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">🔔 Alertas</div>

  <?php $__currentLoopData = $partosProximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $dias = now()->diffInDays($c->fecha_probable_parto, false); ?>
  <div class="alerta-porc <?php echo e($dias <= 3 ? 'urgente' : 'aviso'); ?>">
    <span><?php echo e($dias <= 3 ? '🚨' : '⚠️'); ?></span>
    <div>
      <strong>Parto próximo — <?php echo e($c->nombre_lote); ?></strong>
      · Camada #<?php echo e($c->numero_camada); ?><br>
      <span style="font-size:.74rem;">
        <?php echo e(\Carbon\Carbon::parse($c->fecha_probable_parto)->format('d/m/Y')); ?>

        <?php if($dias >= 0): ?>(en <?php echo e($dias); ?> días)<?php else: ?><?php endif; ?>
      </span>
    </div>
    <a href="<?php echo e(route('porcicola.reproductivo')); ?>" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Ver →</a>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <?php $__currentLoopData = $desteteProximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $diasLact = now()->diffInDays(\Carbon\Carbon::parse($c->fecha_parto_real)); ?>
  <div class="alerta-porc aviso">
    <span>🍼</span>
    <div>
      <strong>Destete — <?php echo e($c->nombre_lote); ?></strong>
      · <?php echo e($c->lechones_nacidos_vivos); ?> lechones<br>
      <span style="font-size:.74rem;"><?php echo e($diasLact); ?> días de lactancia</span>
    </div>
    <a href="<?php echo e(route('porcicola.reproductivo')); ?>" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Destetar →</a>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  <?php $__currentLoopData = $alertasSanidad->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $diasR = now()->diffInDays($s->fecha_programada, false); ?>
  <div class="alerta-porc <?php echo e($diasR < 0 ? 'urgente' : 'aviso'); ?>">
    <span><?php echo e($diasR < 0 ? '❌' : '💉'); ?></span>
    <div>
      <strong><?php echo e($s->nombre_protocolo); ?></strong><br>
      <span style="font-size:.74rem;">
        <?php echo e(\Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y')); ?>

        <?php if($diasR >= 0): ?>(en <?php echo e($diasR); ?> días)<?php else: ?><?php endif; ?>
      </span>
    </div>
    <a href="<?php echo e(route('porcicola.sanidad')); ?>" style="margin-left:auto;font-size:.74rem;white-space:nowrap;">Aplicar →</a>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="porcicola-menu-grid">
    <a href="<?php echo e(route('porcicola.reproductivo')); ?>" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">🤰</div>
      <div class="porcicola-menu-lbl">Reproductivo</div>
      <div class="porcicola-menu-sub">Camadas · Partos</div>
    </a>
    <a href="<?php echo e(route('porcicola.ceba')); ?>" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">🏋️</div>
      <div class="porcicola-menu-lbl">Ceba</div>
      <div class="porcicola-menu-sub">Pesos · CA</div>
    </a>
    <a href="<?php echo e(route('porcicola.sanidad')); ?>" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">💉</div>
      <div class="porcicola-menu-lbl">Sanidad</div>
      <div class="porcicola-menu-sub">PPC · Parvo · Lepto</div>
    </a>
    <a href="<?php echo e(route('porcicola.reportes')); ?>" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">📈</div>
      <div class="porcicola-menu-lbl">Reportes</div>
      <div class="porcicola-menu-sub">Análisis</div>
    </a>
    <a href="<?php echo e(route('animales.index')); ?>" class="porcicola-menu-card">
      <div class="porcicola-menu-ico">📋</div>
      <div class="porcicola-menu-lbl">Inventario</div>
      <div class="porcicola-menu-sub">Registrar cerdos</div>
    </a>
  </div>
</div>


<?php if($inventario->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">🐷 Inventario por categoría</div>
  <div class="inventario-grid">
    <?php $__currentLoopData = $inventario; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $cfg = $iconosCat[$cat->categoria_porcina ?? 'otro'] ?? ['🐷','Otro','']; ?>
    <div class="inv-cat-card">
      <div class="inv-cat-ico"><?php echo e($cfg[0]); ?></div>
      <div class="inv-cat-val"><?php echo e(number_format($cat->total)); ?></div>
      <div class="inv-cat-lbl"><?php echo e($cfg[1]); ?></div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">🐖 Lotes activos</div>
    <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-ghost">+ Nuevo</a>
  </div>
  <?php $__empty_1 = true; $__currentLoopData = $lotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $cfg = $iconosCat[$l->categoria_porcina ?? 'otro'] ?? ['🐷','Otro',''];
    $esHembra = $l->categoria_porcina === 'hembra_cria';
  ?>
  <div class="cerda-card <?php echo e($l->camada_activa ? 'prenada' : ($l->en_lactancia ? 'parida' : 'disponible')); ?>">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="cerda-nombre"><?php echo e($cfg[0]); ?> <?php echo e($l->nombre_lote); ?></div>
        <div class="cerda-sub">
          <?php echo e($l->especie); ?> · <?php echo e(number_format($l->cantidad)); ?> animales
          <?php if($l->raza_porcina): ?> · <?php echo e($l->raza_porcina); ?><?php endif; ?>
          <?php if($l->ubicacion): ?> · 📍<?php echo e($l->ubicacion); ?><?php endif; ?>
        </div>
        <span class="cat-badge <?php echo e($cfg[2]); ?>"><?php echo e($cfg[1]); ?></span>
      </div>
      <div style="text-align:right;">
        <?php if($l->peso_promedio): ?>
        <div style="font-size:1.1rem;font-weight:800;color:#ea580c;"><?php echo e($l->peso_promedio); ?>kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">peso prom.</div>
        <?php endif; ?>
      </div>
    </div>
    <?php if($esHembra): ?>
    <div style="margin-top:8px;font-size:.75rem;color:#64748b;">
      <?php if($l->camada_activa): ?>
        🤰 Preñada · parto probable <?php echo e(\Carbon\Carbon::parse($l->camada_activa->fecha_probable_parto)->format('d/m/Y')); ?>

      <?php elseif($l->en_lactancia): ?>
        🍼 En lactancia · <?php echo e(now()->diffInDays(\Carbon\Carbon::parse($l->en_lactancia->fecha_parto_real))); ?> días
      <?php else: ?>
        ✅ Disponible para servicio
      <?php endif; ?>
      · <?php echo e($l->num_partos ?? 0); ?> partos
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">🐷</div>
    <p>No hay cerdos activos.</p>
    <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-primary">Registrar en Animales</a>
  </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/porcicola/piara.blade.php ENDPATH**/ ?>