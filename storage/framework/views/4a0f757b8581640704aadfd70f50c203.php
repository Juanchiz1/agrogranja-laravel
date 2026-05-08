
<?php $__env->startSection('title','Siembra Piscicola'); ?>
<?php $__env->startSection('page_title','Siembra y Muestreos'); ?>
<?php $__env->startSection('back_url', route('piscicola.estanques')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/piscicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card" style="padding:12px 14px;">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <div style="font-size:.85rem;color:#64748b;">
      <?php echo e($estanques->count()); ?> estanque(s) disponibles
    </div>
    <?php if($estanques->count()): ?>
    <button onclick="openModal('modalNuevaSiembra')" class="btn btn-sm btn-primary">
      + Nueva siembra
    </button>
    <?php endif; ?>
  </div>
</div>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Siembras activas</div>

  
  <?php $__empty_1 = true; $__currentLoopData = $siembras->where('activo', 1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $cantActual    = (int)($s->cantidad_actual ?? $s->cantidad_alevinos);
    $biomasaActual = round((float)($s->biomasa_actual_kg ?? 0), 1);
    $pesoActualG   = round((float)($s->peso_promedio_actual_g ?? 0), 0);
    $tieneMuestreos = $s->muestreos->count() > 0;
    $ultimoMuest    = $tieneMuestreos ? $s->muestreos->last() : null;
    $tasaCrecActual = $ultimoMuest ? $ultimoMuest->ganancia_diaria_g : null;
    $fechaSiembraFmt = \Carbon\Carbon::parse($s->fecha_siembra)->format('d/m/Y');
    $mortTotal       = (int)($s->mortalidad_total ?? 0);
    $sobrev          = round((float)($s->sobrevivencia ?? $s->sobrevivencia_pct ?? 100), 1);
  ?>
  <div class="estanque-card" style="margin-bottom:10px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div class="estanque-nombre"><?php echo e($s->nombre_estanque); ?></div>
        <div class="estanque-sub">
          <?php echo e($s->especie); ?> · Sembrado <?php echo e($fechaSiembraFmt); ?> · Dia <?php echo e($s->dias_cultivo ?? 0); ?>

        </div>
        <?php if(!empty($s->proveedor)): ?>
        <div style="font-size:.72rem;color:#94a3b8;">Proveedor: <?php echo e($s->proveedor); ?></div>
        <?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:1.1rem;font-weight:800;color:var(--pisc-azul);"><?php echo e($biomasaActual); ?> kg</div>
        <div style="font-size:.68rem;color:#94a3b8;">biomasa est.</div>
      </div>
    </div>

    
    <div style="display:flex;gap:14px;flex-wrap:wrap;font-size:.75rem;color:#64748b;margin:8px 0;">
      <span>Sembrados: <strong><?php echo e(number_format($s->cantidad_alevinos)); ?></strong></span>
      <span>Actuales: <strong><?php echo e(number_format($cantActual)); ?></strong></span>
      <?php if($mortTotal > 0): ?>
      <span style="color:#dc2626;">Muertes: <strong><?php echo e($mortTotal); ?></strong></span>
      <?php endif; ?>
      <span>Sobrev: <strong style="color:<?php echo e($sobrev >= 90 ? '#16a34a' : ($sobrev >= 75 ? '#d97706' : '#dc2626')); ?>;">
        <?php echo e($sobrev); ?>%
      </strong></span>
      <?php if($pesoActualG > 0): ?>
      <span>Peso prom: <strong><?php echo e($pesoActualG); ?> g</strong></span>
      <?php endif; ?>
      <?php if($tasaCrecActual !== null): ?>
      <span class="muestreo-crec <?php echo e($tasaCrecActual >= 1.5 ? 'crec-ok' : 'crec-lento'); ?>">
        Crec: <?php echo e($tasaCrecActual); ?> g/dia
      </span>
      <?php endif; ?>
      <?php if(!empty($s->alimento_acumulado_kg) && $s->alimento_acumulado_kg > 0): ?>
      <span>Alimento total: <strong><?php echo e($s->alimento_acumulado_kg); ?> kg</strong></span>
      <?php endif; ?>
    </div>

    
    <?php if($tieneMuestreos): ?>
    <div style="border-top:1px solid #e2e8f0;padding-top:8px;margin-top:4px;">
      <div style="font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:4px;">
        Muestreos (<?php echo e($s->muestreos->count()); ?>)
      </div>
      <?php $__currentLoopData = $s->muestreos->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
      <?php
        $mFecha = \Carbon\Carbon::parse($m->fecha)->format('d/m');
        $mCrecColor = ($m->ganancia_diaria_g !== null && $m->ganancia_diaria_g >= 1.5) ? '#16a34a' : '#d97706';
      ?>
      <div class="muestreo-row">
        <span style="color:#64748b;min-width:45px;"><?php echo e($mFecha); ?></span>
        <span class="muestreo-peso"><?php echo e($m->peso_promedio_g); ?> g</span>
        <?php if(!empty($m->biomasa_estimada_kg)): ?>
        <span style="font-size:.75rem;color:#64748b;"><?php echo e(round($m->biomasa_estimada_kg,1)); ?> kg biomasa</span>
        <?php endif; ?>
        <?php if($m->ganancia_diaria_g !== null): ?>
        <span style="font-size:.72rem;color:<?php echo e($mCrecColor); ?>;">
          +<?php echo e($m->ganancia_diaria_g); ?> g/dia
        </span>
        <?php endif; ?>
      </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px;">
      <button onclick="openNuevoMuestreo(<?php echo e($s->id); ?>,<?php echo e($s->estanque_id); ?>,'<?php echo e(addslashes($s->nombre_estanque)); ?>')"
              class="btn btn-sm btn-primary" style="font-size:.75rem;">
        + Muestreo
      </button>
      <button onclick="openMortalidadModal(<?php echo e($s->id); ?>,<?php echo e($s->estanque_id); ?>,'<?php echo e(addslashes($s->nombre_estanque)); ?>')"
              class="btn btn-sm btn-ghost" style="font-size:.75rem;color:#dc2626;">
        + Mortalidad
      </button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#64748b;">
    <p>No hay siembras activas.</p>
    <?php if($estanques->count()): ?>
    <button onclick="openModal('modalNuevaSiembra')" class="btn btn-sm btn-primary" style="margin-top:8px;">
      + Registrar primera siembra
    </button>
    <?php else: ?>
    <p style="font-size:.82rem;margin-top:6px;">Primero registra un estanque en el Dashboard.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>



<?php if($siembras->where('activo', 0)->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">
    Siembras cosechadas (<?php echo e($siembras->where('activo', 0)->count()); ?>)
  </div>
  <?php $__currentLoopData = $siembras->where('activo', 0); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $fechaSiembraFmt2 = \Carbon\Carbon::parse($s->fecha_siembra)->format('d/m/Y');
    $fechaCosechaFmt2 = !empty($s->fecha_cosecha) ? \Carbon\Carbon::parse($s->fecha_cosecha)->format('d/m/Y') : '';
  ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <div>
      <div style="font-weight:700;"><?php echo e($s->nombre_estanque); ?></div>
      <div style="font-size:.72rem;color:#64748b;">
        <?php echo e($s->especie); ?> · Sembrada <?php echo e($fechaSiembraFmt2); ?>

        <?php if($fechaCosechaFmt2): ?> · Cosechada <?php echo e($fechaCosechaFmt2); ?><?php endif; ?>
        · <?php echo e($s->dias_cultivo ?? 0); ?> dias
      </div>
    </div>
    <div style="text-align:right;">
      <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:10px;font-size:.72rem;font-weight:700;">
        Cosechada
      </span>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalNuevaSiembra" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Nueva siembra de alevinos</div>
    <form method="POST" action="<?php echo e(route('piscicola.siembra.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Estanque *</label>
        <select name="estanque_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $estanques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($e->id); ?>"><?php echo e($e->nombre); ?> (<?php echo e($e->especie_cultivada); ?>)</option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha de siembra *</label>
          <input type="date" name="fecha_siembra" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Especie *</label>
          <input type="text" name="especie" class="form-control" required placeholder="Cachama, Tilapia...">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Cantidad de alevinos *</label>
          <input type="number" name="cantidad_alevinos" class="form-control" min="1" required placeholder="Ej: 3000">
        </div>
        <div class="form-group">
          
          <label>Peso inicial promedio (g) *</label>
          <input type="number" name="peso_promedio_inicial_g" class="form-control"
                 step="0.001" min="0.001" required placeholder="Ej: 2.5">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Proveedor</label>
          <input type="text" name="proveedor" class="form-control" placeholder="Estacion piscicola, vivero...">
        </div>
        <div class="form-group">
          <label>Costo alevinos (COP)</label>
          <input type="number" name="costo_alevinos" class="form-control" step="100" min="0" placeholder="Total">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Procedencia, certificacion sanitaria..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevaSiembra')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar siembra</button>
      </div>
    </form>
  </div>
</div>


<div id="modalNuevoMuestreo" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Muestreo de biomasa — <span id="nomEstMuest2"></span></div>
    <form method="POST" action="<?php echo e(route('piscicola.muestreo.store')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="siembra_id" id="siembraIdMuest2">
      <input type="hidden" name="estanque_id" id="estanqueIdMuest2">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Peces pesados *</label>
          <input type="number" name="peces_muestreados" class="form-control" min="1" value="30" required>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div class="form-group">
          <label>Peso promedio (g) *</label>
          <input type="number" name="peso_promedio_g" class="form-control" step="0.1" min="0" required>
        </div>
        <div class="form-group">
          <label>Peso minimo (g)</label>
          <input type="number" name="peso_minimo_g" class="form-control" step="0.1" min="0">
        </div>
        <div class="form-group">
          <label>Peso maximo (g)</label>
          <input type="number" name="peso_maximo_g" class="form-control" step="0.1" min="0">
        </div>
      </div>
      <div class="form-group">
        <label>Cantidad total estimada en el estanque</label>
        
        <input type="number" name="cantidad_estimada" class="form-control" min="0">
        <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
          Si no la ingresas, se usa la cantidad actual de la siembra.
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control"
               placeholder="Uniformidad, condicion corporal...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevoMuestreo')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar muestreo</button>
      </div>
    </form>
  </div>
</div>


<div id="modalMortalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar mortalidad — <span id="nomEstMort"></span></div>
    <form method="POST" action="<?php echo e(route('piscicola.mortalidad.store')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="siembra_id" id="siembraIdMort">
      <input type="hidden" name="estanque_id" id="estanqueIdMort">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Cantidad de peces *</label>
          <input type="number" name="cantidad" class="form-control" min="1" value="1" required>
        </div>
      </div>
      <div class="form-group">
        <label>Causa *</label>
        <select name="causa" class="form-control" required>
          <option value="calidad_agua">Calidad del agua</option>
          <option value="enfermedad">Enfermedad</option>
          <option value="estres">Estres (transporte, manipulacion)</option>
          <option value="depredador">Depredador</option>
          <option value="manipulacion">Manipulacion</option>
          <option value="causa_desconocida">Causa desconocida</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Descripcion</label>
        <input type="text" name="descripcion" class="form-control"
               placeholder="Sintomas, color, comportamiento observado...">
      </div>
      <div style="background:#fef2f2;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#991b1b;margin-bottom:10px;">
        La cantidad de peces activos en la siembra se actualiza automaticamente.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalMortalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
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

function openNuevoMuestreo(siembraId, estanqueId, nombre) {
  document.getElementById('siembraIdMuest2').value   = siembraId;
  document.getElementById('estanqueIdMuest2').value  = estanqueId;
  document.getElementById('nomEstMuest2').textContent = nombre;
  openModal('modalNuevoMuestreo');
}
function openMortalidadModal(siembraId, estanqueId, nombre) {
  document.getElementById('siembraIdMort').value     = siembraId;
  document.getElementById('estanqueIdMort').value    = estanqueId;
  document.getElementById('nomEstMort').textContent  = nombre;
  openModal('modalMortalidad');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/piscicola/siembra.blade.php ENDPATH**/ ?>