
<?php $__env->startSection('title','Produccion Animal'); ?>
<?php $__env->startSection('page_title','Produccion Animal'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/produccion.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<form method="GET" action="<?php echo e(route('produccion-animal.index')); ?>" class="prod-filtros">
  <input type="date" name="fecha" class="form-control" value="<?php echo e($fecha); ?>"
         style="max-width:160px;" onchange="this.form.submit()">
  <select name="animal_id" class="form-control" style="flex:1;max-width:180px;"
          onchange="this.form.submit()">
    <option value="">Todos los animales</option>
    <?php $__currentLoopData = $animalesProductivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <option value="<?php echo e($ap->id); ?>" <?php echo e($animalFiltro == $ap->id ? 'selected' : ''); ?>>
      <?php echo e($ap->nombre_lote ?? $ap->especie); ?> (<?php echo e($ap->produccion); ?>)
    </option>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <a href="<?php echo e(route('produccion-animal.productividad')); ?>" class="btn btn-sm btn-secondary">
    Productividad
  </a>
  <button onclick="openModal('modalNuevaProduccion')" type="button" class="btn btn-sm btn-primary">
    + Registrar
  </button>
</form>


<div class="prod-stats">
  <div class="prod-stat">
    <div class="prod-stat-ico">&#9200;</div>
    <div class="prod-stat-val"><?php echo e($registrosDia); ?></div>
    <div class="prod-stat-lbl">Sesiones hoy</div>
  </div>
  <div class="prod-stat azul">
    <div class="prod-stat-ico">&#9878;</div>
    <div class="prod-stat-val"><?php echo e(number_format($totalDia, 1)); ?></div>
    <div class="prod-stat-lbl">Unidades hoy</div>
  </div>
  <div class="prod-stat naranja">
    <div class="prod-stat-ico">&#36;</div>
    <div class="prod-stat-val">$<?php echo e($valorDia >= 1000 ? round($valorDia/1000,1).'k' : number_format($valorDia,0,',','.')); ?></div>
    <div class="prod-stat-lbl">Valor hoy</div>
  </div>
  <div class="prod-stat morado">
    <div class="prod-stat-ico">&#128200;</div>
    <div class="prod-stat-val"><?php echo e($resMes->count()); ?></div>
    <div class="prod-stat-lbl">Tipos este mes</div>
  </div>
</div>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">
      Produccion del <?php echo e(\Carbon\Carbon::parse($fecha)->format('d/m/Y')); ?>

    </div>
    <button onclick="openModal('modalNuevaProduccion')" type="button"
            class="btn btn-sm btn-primary">+ Sesion</button>
  </div>

  <?php $__empty_1 = true; $__currentLoopData = $prodPorAnimal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $animalId => $registros): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $primer     = $registros->first();
    $nombreAnim = $primer->nombre_lote ?? $primer->especie;
    $fotoAnim   = $primer->animal_foto ?? null;
    $totalAnim  = $registros->sum('cantidad');
    $unidadAnim = $primer->unidad ?? '';
    $valorAnim  = $registros->sum('valor_total');
  ?>
  <div class="sesion-row">
    <div class="sesion-animal">
      <?php if($fotoAnim): ?>
      <img src="<?php echo e(asset($fotoAnim)); ?>" class="sesion-animal-img" alt="<?php echo e($nombreAnim); ?>">
      <?php else: ?>
      <div class="sesion-animal-ico">&#128016;</div>
      <?php endif; ?>
      <div class="sesion-animal-nombre"><?php echo e($nombreAnim); ?></div>
      <div class="sesion-animal-sub">
        <?php echo e(number_format($totalAnim, 1)); ?> <?php echo e($unidadAnim); ?>

      </div>
    </div>
    <div class="sesion-registros">
      <?php $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $sesionClass = $reg->sesion ?? 'unica';
        $sesionMap   = ['am'=>'Ordeno AM','pm'=>'Ordeno PM','noche'=>'Noche',
                        'manana'=>'Manana','tarde'=>'Tarde','unica'=>'Unica',
                        'general'=>'General'];
        $sesionNombre = $sesionMap[$sesionClass] ?? $sesionClass;
        $destMap      = ['venta_directa'=>['dest-venta','Venta'],
                         'consumo_familiar'=>['dest-familia','Familia'],
                         'transformacion'=>['dest-transform','Transform.'],
                         'inventario'=>['dest-inventario','Inventario'],
                         'desperdicio'=>['dest-desperdicio','Desperdicio']];
        $destInfo     = $destMap[$reg->destino ?? 'venta_directa'] ?? ['dest-venta','Venta'];
      ?>
      <div class="sesion-item">
        <div style="display:flex;align-items:center;gap:8px;">
          <span class="sesion-badge <?php echo e($sesionClass); ?>"><?php echo e($sesionNombre); ?></span>
          <span style="font-weight:700;"><?php echo e(number_format((float)$reg->cantidad, 1)); ?> <?php echo e($reg->unidad); ?></span>
          <?php if($reg->transformacion_tipo): ?>
          <span style="font-size:.7rem;color:#b45309;">(<?php echo e($reg->transformacion_tipo); ?>)</span>
          <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:6px;">
          <span class="dest-badge <?php echo e($destInfo[0]); ?>"><?php echo e($destInfo[1]); ?></span>
          <?php if($reg->valor_total): ?>
          <span style="font-size:.75rem;font-weight:600;color:#15803d;">
            $<?php echo e(number_format($reg->valor_total, 0, ',', '.')); ?>

          </span>
          <?php endif; ?>
          <form method="POST"
                action="<?php echo e(route('produccion-animal.destroy', $reg->id)); ?>"
                onsubmit="return confirm('Eliminar?')" style="margin:0;">
            <?php echo csrf_field(); ?>
            <button class="btn btn-sm btn-ghost" style="padding:2px 5px;font-size:.7rem;">
              x
            </button>
          </form>
        </div>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">&#128203;</div>
    <p style="margin-bottom:10px;">Sin registros para el <?php echo e(\Carbon\Carbon::parse($fecha)->format('d/m/Y')); ?>.</p>
    <?php if($animalesProductivos->isEmpty()): ?>
    <p style="font-size:.82rem;">Agrega animales con campo <strong>Produce</strong> en el modulo Animales.</p>
    <?php else: ?>
    <button onclick="openModal('modalNuevaProduccion')" type="button" class="btn btn-sm btn-primary">
      + Registrar primera sesion
    </button>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>


<?php if($resMes->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">
    Resumen <?php echo e(\Carbon\Carbon::now()->format('F Y')); ?>

  </div>
  <?php $__currentLoopData = $resMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="display:flex;justify-content:space-between;padding:7px 0;
              border-bottom:1px solid #e2e8f0;font-size:.85rem;">
    <div>
      <span style="font-weight:700;"><?php echo e(ucfirst($rm->tipo_produccion)); ?></span>
      <span style="font-size:.72rem;color:#64748b;margin-left:6px;">
        <?php echo e($rm->registros); ?> registros
      </span>
    </div>
    <div style="text-align:right;">
      <span style="font-weight:800;color:#16a34a;"><?php echo e(number_format($rm->total,1)); ?> <?php echo e($rm->unidad); ?></span>
      <?php if($rm->valor): ?>
      <div style="font-size:.72rem;color:#64748b;">
        $<?php echo e(number_format($rm->valor,0,',','.')); ?>

      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if(count($chartLabels) > 1): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Produccion — ultimos 30 dias</div>
  <div style="position:relative;height:160px;">
    <canvas id="chartProduccion"></canvas>
  </div>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalNuevaProduccion" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar produccion</div>
    <form method="POST" action="<?php echo e(route('produccion-animal.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Animal / Lote *</label>
        <select name="animal_id" class="form-control" required id="selectAnimalProd"
                onchange="actualizarSesiones(this)">
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $animalesProductivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($ap->id); ?>"
                  data-produccion="<?php echo e(strtolower($ap->produccion ?? '')); ?>"
                  data-unidad="<?php echo e($ap->produccion && str_contains(strtolower($ap->produccion),'huevo') ? 'unidades' : 'litros'); ?>">
            <?php echo e($ap->nombre_lote ?? $ap->especie); ?> — <?php echo e($ap->produccion); ?>

          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e($fecha); ?>" required>
        </div>
        <div class="form-group">
          <label>Sesion</label>
          <select name="sesion" id="selectSesion" class="form-control">
            <option value="unica">Unica</option>
            <option value="am">Ordeno AM (manana)</option>
            <option value="pm">Ordeno PM (tarde)</option>
            <option value="noche">Ordeno Noche</option>
            <option value="manana">Recoleccion Manana</option>
            <option value="tarde">Recoleccion Tarde</option>
            <option value="general">General</option>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de produccion *</label>
          <select name="tipo_produccion" class="form-control" required id="selectTipoProd"
                  onchange="actualizarUnidad(this)">
            <option value="leche">Leche</option>
            <option value="huevos">Huevos</option>
            <option value="miel">Miel</option>
            <option value="lana">Lana</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Unidad *</label>
          <select name="unidad" id="selectUnidad" class="form-control" required>
            <option value="litros">Litros</option>
            <option value="unidades">Unidades</option>
            <option value="kg">kg</option>
            <option value="docenas">Docenas</option>
          </select>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Cantidad *</label>
          <input type="number" name="cantidad" class="form-control"
                 step="0.1" min="0.001" required placeholder="0"
                 oninput="calcValor()">
        </div>
        <div class="form-group">
          <label>Precio unitario (COP)</label>
          <input type="number" name="precio_unitario" id="inputPrecio" class="form-control"
                 step="100" min="0" placeholder="Opcional" oninput="calcValor()">
        </div>
      </div>

      
      <div id="valorCalculado" style="display:none;background:#f0fdf4;border-radius:8px;
           padding:8px 12px;margin-bottom:10px;font-size:.78rem;color:#15803d;">
        Valor total estimado: <strong id="valorTotal">—</strong>
      </div>

      <div class="form-group">
        <label>Destino *</label>
        <select name="destino" class="form-control" required id="selectDestino"
                onchange="toggleTransform(this.value)">
          <?php $__currentLoopData = $destinos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($key); ?>"><?php echo e($label); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>

      <div id="wrapTransform" style="display:none;" class="form-group">
        <label>Producto a elaborar</label>
        <select name="transformacion_tipo" class="form-control">
          <option value="">Seleccionar...</option>
          <option value="queso">Queso</option>
          <option value="yogur">Yogur</option>
          <option value="mantequilla">Mantequilla</option>
          <option value="cuajada">Cuajada</option>
          <option value="kumis">Kumis</option>
          <option value="otro">Otro</option>
        </select>
      </div>

      <div class="form-group">
        <label>Comprador / Notas</label>
        <input type="text" name="comprador" class="form-control"
               placeholder="Nombre del comprador o nota...">
      </div>

      
      <div style="background:#f8fafc;border-radius:8px;padding:8px 12px;
                  font-size:.76rem;color:#64748b;margin-bottom:10px;">
        El costo por unidad se calcula automaticamente a partir de los gastos
        del animal en los ultimos 30 dias.
      </div>

      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalNuevaProduccion')"
                class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
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

function actualizarSesiones(sel) {
  var opt   = sel.options[sel.selectedIndex];
  var prod  = (opt.getAttribute('data-produccion') || '').toLowerCase();
  var seSel = document.getElementById('selectSesion');
  var tiSel = document.getElementById('selectTipoProd');

  if (prod.includes('leche')) {
    tiSel.value = 'leche';
    actualizarUnidad(tiSel);
  } else if (prod.includes('huevo')) {
    tiSel.value = 'huevos';
    actualizarUnidad(tiSel);
  } else if (prod.includes('miel')) {
    tiSel.value = 'miel';
  }
}

function actualizarUnidad(sel) {
  var u = document.getElementById('selectUnidad');
  var mapa = { leche:['litros','ml'], huevos:['unidades','docenas'],
               miel:['kg','litros'], lana:['kg','lb'], otro:['unidades','kg','litros'] };
  var ops  = mapa[sel.value] || ['unidades'];
  u.innerHTML = ops.map(function(o){ return '<option value="'+o+'">'+o+'</option>'; }).join('');
}

function toggleTransform(val) {
  document.getElementById('wrapTransform').style.display =
    (val === 'transformacion') ? 'block' : 'none';
}

function calcValor() {
  var cant   = parseFloat(document.querySelector('[name=cantidad]').value) || 0;
  var precio = parseFloat(document.getElementById('inputPrecio').value) || 0;
  var el     = document.getElementById('valorTotal');
  var wrap   = document.getElementById('valorCalculado');
  if (cant > 0 && precio > 0) {
    var total = Math.round(cant * precio);
    el.textContent = '$' + total.toLocaleString('es-CO');
    wrap.style.display = 'block';
  } else {
    wrap.style.display = 'none';
  }
}

<?php if(count($chartLabels) > 1): ?>
(function(){
  var ctx = document.getElementById('chartProduccion');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: <?php echo json_encode($chartLabels); ?>,
      datasets: [{
        label: 'Cantidad',
        data: <?php echo json_encode($chartCantidad); ?>,
        borderColor: '#16a34a',
        backgroundColor: 'rgba(22,163,74,.10)',
        borderWidth: 2, pointRadius: 3, fill: true, yAxisID: 'yCant'
      },{
        label: 'Valor ($)',
        data: <?php echo json_encode($chartValor); ?>,
        borderColor: '#2563eb',
        backgroundColor: 'transparent',
        borderWidth: 1.5, pointRadius: 2,
        borderDash: [5,5], yAxisID: 'yVal'
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { labels: { font: { size: 9 } } } },
      scales: {
        x:    { ticks: { font: { size: 9 } }, grid: { display: false } },
        yCant:{ position: 'left',  ticks: { font: { size: 9 } } },
        yVal: { position: 'right', ticks: { font: { size: 9 },
                callback: function(v){ return '$'+Math.round(v/1000)+'k'; } },
                grid: { display: false } }
      }
    }
  });
})();
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/produccion/index.blade.php ENDPATH**/ ?>