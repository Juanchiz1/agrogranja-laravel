
<?php $__env->startSection('title','Ceba Porcina'); ?>
<?php $__env->startSection('page_title','🏋️ Ceba y Engorde'); ?>
<?php $__env->startSection('back_url', route('porcicola.piara')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="alerta-porc info" style="margin-bottom:10px;">
  <span>💡</span>
  <div style="font-size:.8rem;">
    <strong>CA porcina ideal:</strong> Iniciación 1.8-2.2 · Crecimiento 2.3-2.8 · Finalización 2.8-3.2.
    Meta de sacrificio: <strong>~100 kg</strong> en 16 semanas desde los 20 kg.
  </div>
</div>

<?php if($lotesCeba->isEmpty()): ?>
<div class="section-card" style="text-align:center;padding:30px;">
  <div style="font-size:3rem;">🏋️</div>
  <p style="color:#64748b;margin-bottom:12px;">No hay lotes de ceba o levante activos.</p>
  <p style="font-size:.82rem;color:#94a3b8;margin-bottom:16px;">
    Ve a Animales → edita un cerdo → asigna <strong>categoria_porcina = ceba</strong>.
  </p>
  <a href="<?php echo e(route('animales.index')); ?>" class="btn btn-sm btn-primary">Ir a Animales</a>
</div>
<?php else: ?>

<?php $__currentLoopData = $datosLotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animalId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $lote       = $data['lote'];
  $pesos      = $data['pesos'];
  $conv       = $data['conversiones'];
  $ultimoPeso = $data['ultimoPeso'];
  $fechaSacr  = $data['fechaSacrificio'];
  $pctAvance  = $data['pctAvance'];
  $metaKg     = $data['metaKg'];
  $pesoActual = $data['pesoActual'];
  $std        = $data['tablaStd'];
?>

<div class="section-card">
  <div class="section-header">
    <div>
      <div style="font-weight:800;font-size:.95rem;">🐖 <?php echo e($lote->nombre_lote); ?></div>
      <div style="font-size:.75rem;color:#64748b;">
        <?php echo e($lote->cantidad); ?> animales
        <?php if($lote->raza_porcina): ?> · <?php echo e($lote->raza_porcina); ?><?php endif; ?>
        <?php if($lote->ubicacion): ?> · 📍<?php echo e($lote->ubicacion); ?><?php endif; ?>
      </div>
    </div>
    <button onclick="openPesoModal(<?php echo e($lote->id); ?>,'<?php echo e(addslashes($lote->nombre_lote)); ?>',<?php echo e($pesos->count()+1); ?>)"
            class="btn btn-sm btn-primary">+ Pesaje</button>
  </div>

  
  <div style="margin-bottom:12px;">
    <div style="display:flex;justify-content:space-between;font-size:.78rem;margin-bottom:4px;">
      <span style="color:#64748b;">Entrada: <?php echo e($lote->peso_entrada_kg ?? 20); ?> kg</span>
      <span style="font-weight:700;color:#ea580c;"><?php echo e($pesoActual); ?> kg actual</span>
      <span style="color:#64748b;">Meta: <?php echo e($metaKg); ?> kg</span>
    </div>
    <div class="ceba-avance-wrap">
      <div class="ceba-avance-fill" style="width:<?php echo e($pctAvance); ?>%;"></div>
    </div>
    <div style="font-size:.72rem;color:#64748b;text-align:center;margin-top:3px;">
      <?php echo e($pctAvance); ?>% del objetivo
      <?php if($fechaSacr): ?>
        · Sacrificio proyectado: <strong><?php echo e(\Carbon\Carbon::parse($fechaSacr)->format('d/m/Y')); ?></strong>
        (<?php echo e(now()->diffInDays($fechaSacr)); ?> días)
      <?php endif; ?>
    </div>
  </div>

  
  <?php if($pesos->count()): ?>
  <?php $__currentLoopData = $pesos->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $stdSem = $std[$p->semana] ?? null;
    $delta  = $stdSem ? round($p->peso_promedio_kg - $stdSem->peso_meta_kg, 1) : null;
    $pctBar = $stdSem && $stdSem->peso_meta_kg > 0
        ? min(130, round(($p->peso_promedio_kg / $stdSem->peso_meta_kg) * 100)) : 100;
  ?>
  <div class="peso-row-porc">
    <span class="peso-semana-porc">Sem <?php echo e($p->semana); ?></span>
    <div style="flex:1;margin:0 10px;">
      <div style="background:#e2e8f0;border-radius:4px;height:5px;overflow:hidden;">
        <div style="width:<?php echo e($pctBar); ?>%;height:100%;border-radius:4px;
                    background:<?php echo e($delta !== null && $delta >= 0 ? '#ea580c' : '#94a3b8'); ?>;"></div>
      </div>
      <?php if($stdSem): ?>
      <div style="font-size:.67rem;color:#94a3b8;margin-top:1px;">
        Meta <?php echo e($stdSem->peso_meta_kg); ?> kg
        <?php if($delta !== null): ?>
          <span class="<?php echo e($delta >= 0 ? 'peso-ok-porc' : 'peso-bajo-porc'); ?>">
            (<?php echo e($delta >= 0 ? '+' : ''); ?><?php echo e($delta); ?> kg)
          </span>
        <?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div style="text-align:right;">
      <span style="font-weight:800;color:#ea580c;"><?php echo e($p->peso_promedio_kg); ?> kg</span>
      <?php if($p->gpd_kg): ?>
      <div style="font-size:.7rem;color:#64748b;"><?php echo e($p->gpd_kg); ?> kg/día</div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

  
  <?php if($pesos->count() > 1): ?>
  <div style="position:relative;height:120px;margin-top:10px;">
    <canvas id="chartCeba<?php echo e($lote->id); ?>"></canvas>
  </div>
  <?php endif; ?>
  <?php else: ?>
  <div style="text-align:center;padding:12px;color:#94a3b8;font-size:.83rem;">
    Sin pesajes registrados. Registra el peso de entrada para empezar.
  </div>
  <?php endif; ?>

  
  <?php if($conv->count()): ?>
  <div style="margin-top:10px;border-top:1px solid #e2e8f0;padding-top:8px;">
    <div style="font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:6px;">Conversión alimenticia reciente</div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <?php $__currentLoopData = $conv; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <div style="background:#f8fafc;border-radius:8px;padding:6px 10px;text-align:center;">
        <div style="font-size:.65rem;color:#94a3b8;">Sem <?php echo e($c->semana); ?></div>
        <div class="<?php echo e(($c->conversion_alimenticia ?? 4) <= 2.8 ? 'ca-val-ok' : (($c->conversion_alimenticia ?? 4) <= 3.2 ? 'ca-val-med' : 'ca-val-bad')); ?>"
             style="font-size:.9rem;">
          <?php echo e($c->conversion_alimenticia ?? '—'); ?>

        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      <button onclick="openConvModal(<?php echo e($lote->id); ?>,'<?php echo e(addslashes($lote->nombre_lote)); ?>')"
              class="btn btn-sm btn-ghost" style="font-size:.72rem;align-self:center;">
        + CA
      </button>
    </div>
  </div>
  <?php else: ?>
  <button onclick="openConvModal(<?php echo e($lote->id); ?>,'<?php echo e(addslashes($lote->nombre_lote)); ?>')"
          class="btn btn-sm btn-ghost" style="font-size:.75rem;margin-top:8px;">
    + Registrar conversión alimenticia
  </button>
  <?php endif; ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalPesoCeba" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">⚖️ Pesaje semanal — <span id="nombreLotePeso"></span></div>
    <form method="POST" action="<?php echo e(route('porcicola.ceba.peso')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="animal_id" id="animalIdPeso">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Semana en ceba *</label>
          <input type="number" name="semana" id="semanaInput" class="form-control" min="1" max="20" required>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Peso promedio (kg) *</label>
          <input type="number" name="peso_promedio_kg" class="form-control" step="0.1" min="0" required placeholder="Ej: 45.5">
        </div>
        <div class="form-group">
          <label>Animales pesados</label>
          <input type="number" name="animales_pesados" class="form-control" min="0" placeholder="Muestra">
        </div>
      </div>
      <div class="form-group">
        <label>Uniformidad (%)</label>
        <input type="number" name="uniformidad_pct" class="form-control" step="0.1" min="0" max="100"
               placeholder="% animales dentro del ±10% del promedio">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Alimento, sanidad, condición corporal...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalPesoCeba')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalConvPorcina" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🌾 Conversión alimenticia — <span id="nombreLoteConv"></span></div>
    <form method="POST" action="<?php echo e(route('porcicola.ceba.conversion')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="animal_id" id="animalIdConv">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Semana *</label>
          <input type="number" name="semana" class="form-control" min="1" max="20" required>
        </div>
        <div class="form-group">
          <label>Fase alimento</label>
          <select name="tipo_alimento" class="form-control">
            <option value="iniciacion">Iniciación (20-30 kg)</option>
            <option value="crecimiento">Crecimiento (30-60 kg)</option>
            <option value="finalizacion">Finalización (60-100 kg)</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha inicio *</label>
          <input type="date" name="fecha_inicio" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Fecha fin *</label>
          <input type="date" name="fecha_fin" class="form-control" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Alimento consumido (kg) *</label>
          <input type="number" name="alimento_consumido_kg" class="form-control"
                 step="0.1" min="0" required placeholder="Ej: 280" oninput="calcCAPorcina()">
        </div>
        <div class="form-group">
          <label>Ganancia de peso (kg) *</label>
          <input type="number" name="ganancia_peso_kg" class="form-control"
                 step="0.1" min="0.001" required placeholder="Ej: 100" oninput="calcCAPorcina()">
        </div>
      </div>
      <div style="background:#f8fafc;border-radius:8px;padding:10px;text-align:center;margin-bottom:10px;">
        <div style="font-size:.72rem;color:#64748b;">CA Calculada</div>
        <div id="caCalcPorc" style="font-size:1.4rem;font-weight:800;color:#f97316;">—</div>
        <div id="caJuicioPorc" style="font-size:.72rem;color:#64748b;"></div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Temperatura, sanidad...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalConvPorcina')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
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

function openPesoModal(id, nombre, sem) {
  document.getElementById('animalIdPeso').value = id;
  document.getElementById('nombreLotePeso').textContent = nombre;
  document.getElementById('semanaInput').value = sem;
  openModal('modalPesoCeba');
}
function openConvModal(id, nombre) {
  document.getElementById('animalIdConv').value = id;
  document.getElementById('nombreLoteConv').textContent = nombre;
  openModal('modalConvPorcina');
}
function calcCAPorcina() {
  var ali  = parseFloat(document.querySelector('[name=alimento_consumido_kg]').value) || 0;
  var gan  = parseFloat(document.querySelector('[name=ganancia_peso_kg]').value)      || 0;
  var el   = document.getElementById('caCalcPorc');
  var ju   = document.getElementById('caJuicioPorc');
  if (ali > 0 && gan > 0) {
    var ca = Math.round((ali/gan)*1000)/1000;
    el.textContent = ca;
    el.style.color = ca <= 2.8 ? '#16a34a' : (ca <= 3.2 ? '#f97316' : '#dc2626');
    ju.textContent = ca <= 2.8 ? '✅ Excelente' : (ca <= 3.2 ? '⚠️ Aceptable' : '❌ Alta — revisar');
  } else { el.textContent = '—'; ju.textContent = ''; }
}

// Gráficas comparativas
<?php $__currentLoopData = $datosLotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animalId => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $pesos   = $data['pesos'];
  $tablaStd= $data['tablaStd'];
  $lid     = $data['lote']->id;
  $sems    = $pesos->pluck('semana')->map(fn($s) => 'S'.$s)->toArray();
  $reales  = $pesos->pluck('peso_promedio_kg')->map(fn($v) => round((float)$v,1))->toArray();
  $metas   = $pesos->map(fn($p) => isset($tablaStd[$p->semana]) ? round((float)$tablaStd[$p->semana]->peso_meta_kg,1) : null)->toArray();
?>
(function(){
  var ctx = document.getElementById('chartCeba<?php echo e($lid); ?>');
  if (!ctx) return;
  new Chart(ctx, {
    type:'line',
    data:{
      labels:<?php echo json_encode($sems); ?>,
      datasets:[
        { label:'Real', data:<?php echo json_encode($reales); ?>,
          borderColor:'#f97316', backgroundColor:'rgba(249,115,22,.1)',
          borderWidth:2, pointRadius:3, fill:true },
        { label:'Meta ceba comercial', data:<?php echo json_encode($metas); ?>,
          borderColor:'#94a3b8', backgroundColor:'transparent',
          borderWidth:1.5, borderDash:[5,5], pointRadius:2 }
      ]
    },
    options:{
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{ labels:{font:{size:9}} } },
      scales:{
        x:{ticks:{font:{size:9}}, grid:{display:false}},
        y:{beginAtZero:false, ticks:{font:{size:9}}}
      }
    }
  });
})();
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/porcicola/ceba.blade.php ENDPATH**/ ?>