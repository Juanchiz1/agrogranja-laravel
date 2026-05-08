
<?php $__env->startSection('title','Productividad Animal'); ?>
<?php $__env->startSection('page_title','Productividad por Animal'); ?>
<?php $__env->startSection('back_url', route('produccion-animal.index')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/produccion.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<form method="GET" action="<?php echo e(route('produccion-animal.productividad')); ?>" class="prod-filtros">
  <select name="meses" class="form-control" style="max-width:130px;" onchange="this.form.submit()">
    <option value="1" <?php echo e($meses==1?'selected':''); ?>>Ultimo mes</option>
    <option value="3" <?php echo e($meses==3?'selected':''); ?>>Ultimos 3 meses</option>
    <option value="6" <?php echo e($meses==6?'selected':''); ?>>Ultimos 6 meses</option>
  </select>
  <select name="tipo" class="form-control" style="max-width:140px;" onchange="this.form.submit()">
    <option value="">Todos los tipos</option>
    <?php $__currentLoopData = $tiposDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($t); ?>" <?php echo e($tipo === $t ? 'selected' : ''); ?>><?php echo e(ucfirst($t)); ?></option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <a href="<?php echo e(route('produccion-animal.index')); ?>" class="btn btn-sm btn-secondary">
    Ver por dia
  </a>
</form>


<?php if($topProductor): ?>
<div class="section-card" style="border-left:4px solid #f59e0b;background:#fffbeb;">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;">
    <div>
      <div style="font-size:.72rem;font-weight:700;color:#b45309;text-transform:uppercase;margin-bottom:4px;">
        Mejor productor
      </div>
      <div style="font-size:1.1rem;font-weight:800;color:#1e293b;">
        <?php echo e($topProductor->nombre_lote ?? $topProductor->especie); ?>

      </div>
      <div style="font-size:.78rem;color:#64748b;margin-top:2px;">
        <?php echo e($topProductor->especie); ?>

        &middot; <?php echo e($topProductor->tipo_produccion); ?>

        &middot; <?php echo e($meses); ?> <?php echo e($meses == 1 ? 'mes' : 'meses'); ?>

      </div>
    </div>
    <div style="text-align:right;">
      <div style="font-size:1.3rem;font-weight:900;color:#f59e0b;">
        <?php echo e(number_format($topProductor->prod_por_cabeza, 1)); ?>

      </div>
      <div style="font-size:.7rem;color:#64748b;">
        <?php echo e($topProductor->unidad); ?>/cabeza
      </div>
    </div>
  </div>

  <?php if($tendenciaSemanal->count() > 1): ?>
  <div style="position:relative;height:90px;margin-top:10px;">
    <canvas id="chartTendencia"></canvas>
  </div>
  <?php endif; ?>

  <?php if($topProductor->costo_periodo > 0): ?>
  <div class="costo-grid" style="margin-top:10px;">
    <div class="costo-item">
      <div class="costo-val">$<?php echo e(number_format($topProductor->costo_periodo,0,',','.')); ?></div>
      <div class="costo-lbl">Costo total</div>
    </div>
    <div class="costo-item">
      <div class="costo-val"
           style="color:<?php echo e(($topProductor->costo_unitario ?? 0) > 0 ? '#ea580c' : '#94a3b8'); ?>;">
        $<?php echo e(number_format($topProductor->costo_unitario ?? 0,0,',','.')); ?>

      </div>
      <div class="costo-lbl">Costo/<?php echo e($topProductor->unidad); ?></div>
    </div>
    <div class="costo-item">
      <div class="costo-val"
           style="color:<?php echo e(($topProductor->margen_unitario ?? 0) >= 0 ? '#15803d' : '#dc2626'); ?>;">
        $<?php echo e(number_format($topProductor->margen_unitario ?? 0,0,',','.')); ?>

      </div>
      <div class="costo-lbl">Margen/<?php echo e($topProductor->unidad); ?></div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>


<?php if(count($chartAnimales) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Comparativa de produccion por animal</div>
  <div style="position:relative;height:<?php echo e(min(200, count($chartAnimales) * 30 + 40)); ?>px;">
    <canvas id="chartComparat"></canvas>
  </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">
    Ranking de productividad
    <?php if($porAnimal->count()): ?>
    <span style="font-size:.72rem;font-weight:400;color:#64748b;">
      — <?php echo e($meses); ?> <?php echo e($meses==1?'mes':'meses'); ?> &middot; produccion/cabeza
    </span>
    <?php endif; ?>
  </div>

  <?php
    $maxProd = $porAnimal->max('prod_por_cabeza') ?: 1;
  ?>

  <?php $__empty_1 = true; $__currentLoopData = $porAnimal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $posicion  = $idx + 1;
    $rankClass = $posicion <= 3 ? 'top'.min(3,$posicion) : '';
    $esMejor   = ($posicion === 1);
    $pct       = $maxProd > 0 ? round(($r->prod_por_cabeza / $maxProd) * 100) : 0;
    $totalFmt  = number_format($r->total_cantidad, 1);
    $valorFmt  = $r->total_valor > 0 ? '$'.number_format($r->total_valor,0,',','.') : 'sin valor';
    $promDia   = round((float)($r->promedio_diario ?? 0), 2);
    $diasReg   = (int)$r->dias_registrados;
  ?>
  <div class="prod-animal-card <?php echo e($esMejor ? 'top' : ''); ?>">
    <div style="display:flex;align-items:flex-start;gap:10px;">
      <div class="prod-animal-rank <?php echo e($rankClass); ?>">#<?php echo e($posicion); ?></div>
      <div style="flex:1;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
          <div>
            <div style="font-weight:800;font-size:.9rem;">
              <?php echo e($r->nombre_lote ?? $r->especie); ?>

            </div>
            <div style="font-size:.73rem;color:#64748b;">
              <?php echo e($r->especie); ?> &middot; <?php echo e(ucfirst($r->tipo_produccion)); ?>

              &middot; <?php echo e($diasReg); ?> dias de registro
            </div>
          </div>
          <div style="text-align:right;">
            <div style="font-size:1rem;font-weight:800;color:#16a34a;">
              <?php echo e($r->prod_por_cabeza); ?>

            </div>
            <div style="font-size:.68rem;color:#64748b;"><?php echo e($r->unidad); ?>/cabeza</div>
          </div>
        </div>
        <div class="prod-barra-wrap">
          <div class="prod-barra-fill" style="width:<?php echo e($pct); ?>%;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.73rem;color:#64748b;">
          <span>Total: <strong><?php echo e($totalFmt); ?> <?php echo e($r->unidad); ?></strong></span>
          <span>Valor: <strong style="color:#16a34a;"><?php echo e($valorFmt); ?></strong></span>
          <?php if($promDia > 0): ?>
          <span>Prom/dia: <strong><?php echo e($promDia); ?> <?php echo e($r->unidad); ?></strong></span>
          <?php endif; ?>
        </div>

        <?php if($r->costo_periodo > 0): ?>
        <div style="margin-top:6px;display:flex;gap:8px;flex-wrap:wrap;">
          <span style="background:#fef2f2;border-radius:8px;padding:2px 8px;font-size:.7rem;color:#dc2626;">
            Costo: $<?php echo e(number_format($r->costo_unitario ?? 0, 0, ',', '.')); ?>/<?php echo e($r->unidad); ?>

          </span>
          <?php if($r->margen_unitario !== null): ?>
          <span style="background:<?php echo e($r->margen_unitario >= 0 ? '#f0fdf4' : '#fef2f2'); ?>;
                       border-radius:8px;padding:2px 8px;font-size:.7rem;
                       color:<?php echo e($r->margen_unitario >= 0 ? '#15803d' : '#dc2626'); ?>;">
            Margen: $<?php echo e(number_format($r->margen_unitario, 0, ',', '.')); ?>/<?php echo e($r->unidad); ?>

          </span>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        
        <div style="margin-top:6px;">
          <button onclick="openCalcCostos(<?php echo e($r->id); ?>,'<?php echo e(addslashes($r->nombre_lote ?? $r->especie)); ?>')"
                  class="btn btn-sm btn-ghost" style="font-size:.72rem;">
            Calcular costo periodo
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    <p>Sin produccion registrada en los ultimos <?php echo e($meses); ?> <?php echo e($meses==1?'mes':'meses'); ?>.</p>
    <a href="<?php echo e(route('produccion-animal.index')); ?>" class="btn btn-sm btn-primary"
       style="margin-top:8px;">Ir a Registrar produccion</a>
  </div>
  <?php endif; ?>
</div>


<?php if($distribucionSesion->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Distribucion por sesion del dia</div>
  <?php $maxSesion = $distribucionSesion->max('total') ?: 1; ?>
  <?php $__currentLoopData = $distribucionSesion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ds): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $pctS    = $maxSesion > 0 ? round(($ds->total / $maxSesion) * 100) : 0;
    $sesMap  = ['am'=>'Ordeno AM','pm'=>'Ordeno PM','noche'=>'Noche',
                'manana'=>'Manana','tarde'=>'Tarde','unica'=>'Unica','general'=>'General'];
    $sesNom  = $sesMap[$ds->sesion ?? 'general'] ?? ucfirst($ds->sesion ?? '');
    $colorS  = ['am'=>'#f59e0b','pm'=>'#3b82f6','noche'=>'#7c3aed',
                'manana'=>'#f59e0b','tarde'=>'#3b82f6','unica'=>'#16a34a',
                'general'=>'#64748b'][$ds->sesion ?? 'general'] ?? '#64748b';
  ?>
  <div class="destino-row">
    <span style="min-width:80px;font-size:.8rem;font-weight:600;"><?php echo e($sesNom); ?></span>
    <div class="destino-bar-wrap">
      <div class="destino-bar-fill" style="width:<?php echo e($pctS); ?>%;background:<?php echo e($colorS); ?>;"></div>
    </div>
    <span style="font-size:.8rem;font-weight:700;"><?php echo e(number_format($ds->total,1)); ?></span>
    <span style="font-size:.72rem;color:#94a3b8;margin-left:6px;">(<?php echo e($ds->registros); ?>)</span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($desgloseDest->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Distribucion por destino</div>
  <?php $maxDest = $desgloseDest->max('total') ?: 1; ?>
  <?php $__currentLoopData = $desgloseDest; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $pctD   = $maxDest > 0 ? round(($dd->total / $maxDest) * 100) : 0;
    $destMap2 = ['venta_directa'=>['Venta directa','#16a34a'],
                 'consumo_familiar'=>['Consumo familia','#2563eb'],
                 'transformacion'=>['Transformacion','#f59e0b'],
                 'inventario'=>['Inventario','#7c3aed'],
                 'desperdicio'=>['Desperdicio','#dc2626']];
    $destInfo2 = $destMap2[$dd->destino ?? 'venta_directa'] ?? [$dd->destino,'#64748b'];
  ?>
  <div class="destino-row">
    <span style="min-width:100px;font-size:.8rem;font-weight:600;"><?php echo e($destInfo2[0]); ?></span>
    <div class="destino-bar-wrap">
      <div class="destino-bar-fill" style="width:<?php echo e($pctD); ?>%;background:<?php echo e($destInfo2[1]); ?>;"></div>
    </div>
    <span style="font-size:.8rem;font-weight:700;"><?php echo e(number_format($dd->total,1)); ?></span>
    <?php if($dd->valor > 0): ?>
    <span style="font-size:.72rem;color:#15803d;margin-left:6px;">
      $<?php echo e(number_format($dd->valor,0,',','.')); ?>

    </span>
    <?php endif; ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalCalcCostos" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Calcular costo — <span id="nombreAnimalCosto"></span></div>
    <form method="POST" action="<?php echo e(route('produccion-animal.calcularCostos')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="animal_id" id="animalIdCosto">
      <div style="background:#f0f9ff;border-radius:8px;padding:10px 12px;
                  font-size:.78rem;color:#0369a1;margin-bottom:12px;">
        Calcula el costo por unidad producida sumando todos los gastos del animal
        (alimentacion, sanidad, mano de obra) dividido entre las unidades producidas
        en el periodo.
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha inicio *</label>
          <input type="date" name="fecha_inicio" class="form-control"
                 value="<?php echo e(now()->startOfMonth()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Fecha fin *</label>
          <input type="date" name="fecha_fin" class="form-control"
                 value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalCalcCostos')"
                class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Calcular</button>
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

function openCalcCostos(id, nombre) {
  document.getElementById('animalIdCosto').value      = id;
  document.getElementById('nombreAnimalCosto').textContent = nombre;
  openModal('modalCalcCostos');
}

<?php if($topProductor && $tendenciaSemanal->count() > 1): ?>
(function(){
  var ctx = document.getElementById('chartTendencia');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($tendenciaSemanal->pluck('fecha_inicio')->map(fn($f) => \Carbon\Carbon::parse($f)->format('d/m'))->toArray()); ?>,
      datasets:[{
        data: <?php echo json_encode($tendenciaSemanal->pluck('total')->map(fn($v)=>round((float)$v,1))->toArray()); ?>,
        borderColor: '#f59e0b', backgroundColor: 'rgba(245,158,11,.15)',
        borderWidth: 2, pointRadius: 3, fill: true
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { ticks: { font: { size: 8 } }, grid: { display: false } },
                y: { ticks: { font: { size: 8 } } } }
    }
  });
})();
<?php endif; ?>

<?php if(count($chartAnimales) > 1): ?>
(function(){
  var ctx = document.getElementById('chartComparat');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($chartAnimales); ?>,
      datasets:[{
        label: 'Prod/cabeza',
        data: <?php echo json_encode($chartProd); ?>,
        backgroundColor: 'rgba(22,163,74,.75)',
        borderColor: '#16a34a', borderWidth: 1
      },{
        label: 'Costo/unidad ($)',
        data: <?php echo json_encode($chartCostoU); ?>,
        backgroundColor: 'rgba(220,38,38,.55)',
        borderColor: '#dc2626', borderWidth: 1,
        yAxisID: 'yCosto'
      }]
    },
    options: {
      indexAxis: 'y',
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x:      { ticks: { font: { size: 9 } } },
        y:      { ticks: { font: { size: 9 } }, grid: { display: false } },
        yCosto: { position: 'right', ticks: { font: { size: 9 } }, grid: { display: false } }
      }
    }
  });
})();
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/produccion/productividad.blade.php ENDPATH**/ ?>