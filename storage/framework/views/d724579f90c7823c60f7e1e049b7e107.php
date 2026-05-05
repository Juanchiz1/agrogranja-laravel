
<?php $__env->startSection('title','Engorde'); ?>
<?php $__env->startSection('page_title','🍗 Pollos de Engorde'); ?>
<?php $__env->startSection('back_url', route('avicola.galpon')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/avicola.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php if($lotesEngorde->isEmpty()): ?>
<div class="section-card" style="text-align:center;padding:30px;">
  <div style="font-size:3rem;">🍗</div>
  <p style="color:#64748b;margin-bottom:12px;">No hay lotes de engorde activos.</p>
  <p style="font-size:.82rem;color:#94a3b8;margin-bottom:16px;">
    Ve a Animales → edita un lote → asigna <strong>tipo_ave = engorde</strong>.
  </p>
  <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-primary">Ir a Animales</a>
</div>
<?php else: ?>

<?php $__currentLoopData = $pesosConProyeccion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animalId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $lote        = $data['lote'];
  $pesos       = $data['pesos'];
  $tablaStd    = $data['tablaStd'];
  $fechaSacr   = $data['fechaSacrificio'];
  $ultimoPeso  = $data['ultimoPeso'];
  $semActual   = $lote->semanas;
?>
<div class="section-card">
  <div class="section-header">
    <div>
      <div class="lote-nombre">🍗 <?php echo e($lote->nombre_lote); ?></div>
      <div class="lote-sub">
        <?php echo e($lote->cantidad); ?> aves
        <?php if($lote->linea_ave): ?> · <?php echo e($lote->linea_ave); ?><?php endif; ?>
        <?php if($lote->ubicacion): ?> · 📍<?php echo e($lote->ubicacion); ?><?php endif; ?>
        <?php if($semActual !== null): ?> · Semana <?php echo e($semActual); ?><?php endif; ?>
      </div>
    </div>
    <button onclick="openPesoModal(<?php echo e($lote->id); ?>,'<?php echo e(addslashes($lote->nombre_lote)); ?>',<?php echo e($semActual ?? 1); ?>)"
            class="btn btn-sm btn-primary">+ Pesaje</button>
  </div>

  
  <?php if($fechaSacr): ?>
  <div style="background:#fff7ed;border-radius:10px;padding:10px 12px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;">
    <div>
      <div style="font-size:.78rem;color:#ea580c;font-weight:700;">🗓️ Proyección de sacrificio</div>
      <div style="font-weight:800;color:#1e293b;"><?php echo e(\Carbon\Carbon::parse($fechaSacr)->format('d/m/Y')); ?></div>
      <div style="font-size:.72rem;color:#64748b;">
        En <?php echo e(now()->diffInDays($fechaSacr)); ?> días ·
        GPD actual: <?php echo e($ultimoPeso->gpd_g ?? 'N/D'); ?> g/día
      </div>
    </div>
    <?php if($ultimoPeso): ?>
    <div style="text-align:right;">
      <div style="font-size:1.2rem;font-weight:800;color:#ea580c;"><?php echo e(number_format($ultimoPeso->peso_promedio_g)); ?>g</div>
      <div style="font-size:.7rem;color:#94a3b8;">Último peso</div>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  
  <?php if($pesos->count()): ?>
  <div style="margin-bottom:12px;">
    <?php $__currentLoopData = $pesos->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $stdSem = $tablaStd[$p->semana] ?? null;
      $delta  = $stdSem ? ($p->peso_promedio_g - $stdSem->peso_meta_g) : null;
      $pct    = $stdSem && $stdSem->peso_meta_g > 0
                  ? min(130, round(($p->peso_promedio_g/$stdSem->peso_meta_g)*100)) : 100;
    ?>
    <div class="peso-engorde-row">
      <div>
        <span class="peso-semana">Sem <?php echo e($p->semana); ?></span>
        <span style="font-size:.75rem;color:#94a3b8;"> · <?php echo e(\Carbon\Carbon::parse($p->fecha)->format('d/m')); ?></span>
      </div>
      <div style="flex:1;margin:0 10px;">
        <div class="peso-barra-wrap">
          <div class="peso-barra-fill" style="width:<?php echo e($pct); ?>%;background:<?php echo e($delta >= 0 ? '#ea580c' : '#94a3b8'); ?>;"></div>
        </div>
        <?php if($stdSem): ?>
        <div style="font-size:.68rem;color:#94a3b8;">
          Meta: <?php echo e(number_format($stdSem->peso_meta_g)); ?>g
          <?php if($delta !== null): ?>
          <span class="<?php echo e($delta >= 0 ? 'peso-ok' : 'peso-bajo'); ?>">
            (<?php echo e($delta >= 0 ? '+' : ''); ?><?php echo e(round($delta)); ?>g)
          </span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
      <div style="text-align:right;">
        <span class="peso-valor"><?php echo e(number_format($p->peso_promedio_g)); ?>g</span>
        <?php if($p->gpd_g): ?>
        <div style="font-size:.7rem;color:#64748b;"><?php echo e($p->gpd_g); ?>g/día</div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </div>

  
  <?php if($pesos->count() > 1): ?>
  <div style="position:relative;height:130px;">
    <canvas id="chartEngorde<?php echo e($lote->id); ?>"></canvas>
  </div>
  <?php endif; ?>
  <?php else: ?>
  <div style="text-align:center;padding:16px;color:#94a3b8;font-size:.85rem;">
    Sin pesajes registrados aún. Haz clic en "+ Pesaje" para empezar.
  </div>
  <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalPesoEngorde" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">⚖️ Registrar peso semanal — <span id="nombreLotePeso"></span></div>
    <form method="POST" action="<?php echo e(route('avicola.engorde.peso')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="animal_id" id="animalIdPeso">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Semana de vida *</label>
          <input type="number" name="semana" id="semanaInput" class="form-control" min="1" max="20" required>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Peso promedio (g) *</label>
          <input type="number" name="peso_promedio_g" class="form-control" step="1" min="0" required placeholder="Ej: 1250">
        </div>
        <div class="form-group">
          <label>Aves pesadas (muestra)</label>
          <input type="number" name="aves_pesadas" class="form-control" min="0" placeholder="Ej: 20">
        </div>
      </div>
      <div class="form-group">
        <label>Uniformidad (%)</label>
        <input type="number" name="uniformidad_pct" class="form-control" step="0.1" min="0" max="100"
               placeholder="% aves dentro del ±10% del promedio">
        <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
          Buena uniformidad: &gt;80%. Se calcula pesando una muestra de 50-100 aves.
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Alimento, condición sanitaria...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalPesoEngorde')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar peso</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

function openPesoModal(id, nombre, semana) {
  document.getElementById('animalIdPeso').value = id;
  document.getElementById('nombreLotePeso').textContent = nombre;
  document.getElementById('semanaInput').value = (semana || 1);
  openModal('modalPesoEngorde');
}

// Gráficas comparativas por lote
<?php $__currentLoopData = $pesosConProyeccion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animalId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $pesos    = $data['pesos'];
  $tablaStd = $data['tablaStd'];
  $loteId   = $data['lote']->id;
  $semanas  = $pesos->pluck('semana')->toArray();
  $reales   = $pesos->pluck('peso_promedio_g')->map(fn($v) => round($v))->toArray();
  $metas    = $pesos->map(fn($p) => $tablaStd[$p->semana]->peso_meta_g ?? null)->toArray();
?>
(function() {
  var ctx = document.getElementById('chartEngorde<?php echo e($loteId); ?>');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode(array_map(fn($s) => 'S'.$s, $semanas)); ?>,
      datasets: [
        { label: 'Real', data: <?php echo json_encode($reales); ?>,
          borderColor:'#ea580c', backgroundColor:'rgba(234,88,12,.1)',
          borderWidth:2, pointRadius:3, fill:true },
        { label: 'Meta ' + ('<?php echo $data['lote']->linea_ave ?? 'Ross 308'; ?>'),
          data: <?php echo json_encode($metas); ?>,
          borderColor:'#94a3b8', backgroundColor:'transparent',
          borderWidth:1.5, borderDash:[5,5], pointRadius:2 }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{ font:{size:9} } } },
      scales: {
        x: { ticks:{font:{size:9}}, grid:{display:false} },
        y: { beginAtZero:false, ticks:{font:{size:9}} }
      }
    }
  });
})();
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/avicola/engorde.blade.php ENDPATH**/ ?>