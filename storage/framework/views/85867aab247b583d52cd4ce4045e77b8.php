
<?php $__env->startSection('title','Cosecha Piscicola'); ?>
<?php $__env->startSection('page_title','Cosecha'); ?>
<?php $__env->startSection('back_url', route('piscicola.estanques')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/piscicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if($cosechas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Indicadores historicos</div>
  <div class="cosecha-indicadores">
    <div class="cos-ind" style="background:#f0f9ff;">
      <div class="cos-ind-val" style="color:var(--pisc-azul);"><?php echo e(round($totalCosechado,1)); ?> kg</div>
      <div class="cos-ind-lbl">Total cosechado</div>
    </div>
    <div class="cos-ind" style="background:<?php echo e($caPromedio <= 1.8 ? '#f0fdf4' : '#fffbeb'); ?>;">
      <div class="cos-ind-val" style="color:<?php echo e($caPromedio <= 1.8 ? '#15803d' : '#d97706'); ?>;">
        <?php echo e($caPromedio > 0 ? $caPromedio : 'N/D'); ?>

      </div>
      <div class="cos-ind-lbl">CA promedio</div>
    </div>
    <div class="cos-ind" style="background:<?php echo e($sobrevPromedio >= 85 ? '#f0fdf4' : '#fffbeb'); ?>;">
      <div class="cos-ind-val" style="color:<?php echo e($sobrevPromedio >= 85 ? '#15803d' : '#d97706'); ?>;">
        <?php echo e($sobrevPromedio > 0 ? $sobrevPromedio.'%' : 'N/D'); ?>

      </div>
      <div class="cos-ind-lbl">Sobrevivencia prom.</div>
    </div>
  </div>
</div>
<?php endif; ?>


<?php if($siembrasActivas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Listas para cosechar</div>
  <?php $__currentLoopData = $siembrasActivas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $diasCult    = \Carbon\Carbon::parse($s->fecha_siembra)->diffInDays(now());
    $biomasaAct  = round((float)($s->biomasa_actual_kg ?? 0), 1);
    $pesoPromG   = round((float)($s->peso_promedio_actual_g ?? 0), 0);
    $aliAcum     = round((float)($s->alimento_acumulado_kg ?? 0), 1);
    $listaParaCosechar = ($pesoPromG >= 250 || $diasCult >= 180);
  ?>
  <div style="padding:10px 0;border-bottom:1px solid #e2e8f0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;"><?php echo e($s->nombre_estanque); ?></div>
        <div style="font-size:.75rem;color:#64748b;">
          <?php echo e($s->especie_cultivada); ?> · Dia <?php echo e($diasCult); ?> de cultivo
          <?php if($s->area_m2): ?> · <?php echo e($s->area_m2); ?> m²<?php endif; ?>
        </div>
        <div style="font-size:.75rem;color:#64748b;margin-top:2px;">
          Biomasa: <?php echo e($biomasaAct); ?> kg · Peso prom: <?php echo e($pesoPromG); ?> g
          · Alimento total: <?php echo e($aliAcum); ?> kg
        </div>
      </div>
      <div>
        <?php if($listaParaCosechar): ?>
        <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:700;">
          Lista
        </span>
        <?php else: ?>
        <span style="background:#fffbeb;color:#b45309;padding:2px 8px;border-radius:10px;font-size:.72rem;">
          En proceso
        </span>
        <?php endif; ?>
      </div>
    </div>
    <button onclick="openCosechaModal(<?php echo e($s->id); ?>,<?php echo e($s->estanque_id); ?>,'<?php echo e(addslashes($s->nombre_estanque)); ?>',<?php echo e($s->cantidad_actual ?? $s->cantidad_alevinos); ?>,<?php echo e($aliAcum); ?>)"
            class="btn btn-sm btn-primary" style="margin-top:8px;font-size:.78rem;">
      Registrar cosecha
    </button>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php else: ?>
<div class="section-card" style="text-align:center;padding:20px;color:#64748b;">
  No hay siembras activas para cosechar.
  <br><a href="<?php echo e(route('piscicola.siembra')); ?>" style="color:var(--pisc-azul);">Ver siembras</a>
</div>
<?php endif; ?>


<?php if($cosechas->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Historial de cosechas</div>
  <?php $__currentLoopData = $cosechas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $cFecha = \Carbon\Carbon::parse($c->fecha)->format('d/m/Y');
    $cCA    = $c->conversion_alimenticia;
    $cSobr  = $c->sobrevivencia;
    $cRend  = $c->rendimiento_kg_m2;
    $caColor = '#1e293b';
    if ($cCA !== null) {
        $caColor = $cCA <= 1.5 ? '#15803d' : ($cCA <= 2.0 ? '#d97706' : '#dc2626');
    }
  ?>
  <div style="border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;margin-bottom:8px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.9rem;"><?php echo e($c->nombre_estanque); ?></div>
        <div style="font-size:.74rem;color:#64748b;"><?php echo e($cFecha); ?> · <?php echo e($c->dias_cultivo ?? '—'); ?> dias</div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:1.1rem;font-weight:800;color:var(--pisc-azul);"><?php echo e($c->biomasa_cosechada_kg); ?> kg</div>
        <?php if($c->valor_total_cop): ?>
        <div style="font-size:.72rem;color:#15803d;font-weight:700;">
          $<?php echo e(number_format($c->valor_total_cop, 0, ',', '.')); ?>

        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="cosecha-indicadores" style="margin-top:8px;">
      <div class="cos-ind" style="background:#f8fafc;">
        <div class="cos-ind-val" style="color:<?php echo e($caColor); ?>;font-size:.9rem;">
          <?php echo e($cCA ?? 'N/D'); ?>

        </div>
        <div class="cos-ind-lbl">CA</div>
      </div>
      <div class="cos-ind" style="background:#f8fafc;">
        <div class="cos-ind-val" style="color:<?php echo e(($cSobr ?? 0) >= 85 ? '#15803d' : '#d97706'); ?>;font-size:.9rem;">
          <?php echo e($cSobr !== null ? $cSobr.'%' : 'N/D'); ?>

        </div>
        <div class="cos-ind-lbl">Sobrevivencia</div>
      </div>
      <div class="cos-ind" style="background:#f8fafc;">
        <div class="cos-ind-val" style="font-size:.9rem;">
          <?php echo e($cRend !== null ? $cRend.' kg/m²' : 'N/D'); ?>

        </div>
        <div class="cos-ind-lbl">Rendimiento</div>
      </div>
    </div>
    <?php if($c->peso_promedio_final_g): ?>
    <div style="font-size:.72rem;color:#64748b;margin-top:4px;">
      Peso final prom: <?php echo e($c->peso_promedio_final_g); ?> g ·
      <?php echo e($c->cantidad_cosechada); ?> peces cosechados
      <?php if($c->comprador): ?> · Comprador: <?php echo e($c->comprador); ?><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalCosecha" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar cosecha — <span id="nomEstCosecha"></span></div>
    <form method="POST" action="<?php echo e(route('piscicola.cosecha.store')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="siembra_id" id="siembraIdCosecha">
      <input type="hidden" name="estanque_id" id="estanqueIdCosecha">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha de cosecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Cantidad cosechada *</label>
          <input type="number" name="cantidad_cosechada" id="cantCosecha" class="form-control" min="1" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Peso total (kg) *</label>
          <input type="number" name="peso_total_kg" class="form-control" step="0.1" min="0" required>
        </div>
        <div class="form-group">
          <label>Peso promedio final (g)</label>
          <input type="number" name="peso_promedio_final_g" class="form-control" step="0.1" min="0"
                 placeholder="Se calcula auto">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Precio de venta (COP/kg)</label>
          <input type="number" name="precio_kg" class="form-control" step="100" min="0" placeholder="Ej: 6000">
        </div>
        <div class="form-group">
          <label>Comprador</label>
          <input type="text" name="comprador" class="form-control" placeholder="Nombre del comprador">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Condicion del producto, destino (fresco, proceso)..."></textarea>
      </div>
      <div style="background:#f0f9ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#0284c7;margin-bottom:10px;">
        El sistema calcula automaticamente: sobrevivencia, CA (con alimento acumulado = <span id="aliAcumInfo">0</span> kg) y rendimiento por m².
        Si hay precio, se crea un ingreso automaticamente.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalCosecha')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar cosecha</button>
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

function openCosechaModal(siembraId, estanqueId, nombre, cantidad, aliAcum) {
  document.getElementById('siembraIdCosecha').value  = siembraId;
  document.getElementById('estanqueIdCosecha').value = estanqueId;
  document.getElementById('nomEstCosecha').textContent = nombre;
  document.getElementById('cantCosecha').value        = cantidad;
  document.getElementById('aliAcumInfo').textContent  = aliAcum;
  openModal('modalCosecha');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/piscicola/cosecha.blade.php ENDPATH**/ ?>