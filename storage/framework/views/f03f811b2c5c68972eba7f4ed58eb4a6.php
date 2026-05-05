
<?php $__env->startSection('title','Reproductivo'); ?>
<?php $__env->startSection('page_title','🤰 Reproductivo Porcícola'); ?>
<?php $__env->startSection('back_url', route('porcicola.piara')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card">
  <div class="piara-stats" style="grid-template-columns:repeat(3,1fr);">
    <div class="piara-stat">
      <div class="piara-stat-ico">🐷</div>
      <div class="piara-stat-val"><?php echo e($promedioNacidos); ?></div>
      <div class="piara-stat-lbl">Lechones/camada</div>
    </div>
    <div class="piara-stat verde">
      <div class="piara-stat-ico">🍼</div>
      <div class="piara-stat-val"><?php echo e($promedioDestete); ?></div>
      <div class="piara-stat-lbl">Destetados/camada</div>
    </div>
    <div class="piara-stat <?php echo e($pctMortPreD > 10 ? '' : 'naranja'); ?>">
      <div class="piara-stat-ico">💀</div>
      <div class="piara-stat-val" style="color:<?php echo e($pctMortPreD > 10 ? '#dc2626' : '#1e293b'); ?>;"><?php echo e($pctMortPreD); ?>%</div>
      <div class="piara-stat-lbl">Mort. pre-destete</div>
    </div>
  </div>
</div>


<div class="section-card" style="padding:12px 14px;">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <div style="font-size:.85rem;color:#64748b;">
      <?php echo e($hembras->count()); ?> hembras de cría · <?php echo e($verracos->count()); ?> verracos
    </div>
    <?php if($hembras->count()): ?>
    <button onclick="openModal('modalServicio')" class="btn btn-sm btn-primary">
      🐷 + Servicio / Monta
    </button>
    <?php endif; ?>
  </div>
</div>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📋 Camadas</div>

  <?php $__empty_1 = true; $__currentLoopData = $camadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <div class="camada-row">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:6px;">
      <div>
        <div style="font-weight:700;font-size:.9rem;">
          🐷 <?php echo e($cam->nombre_lote); ?>

          <span style="font-weight:400;font-size:.78rem;color:#94a3b8;">· Camada #<?php echo e($cam->numero_camada); ?></span>
        </div>
        <div style="font-size:.74rem;color:#64748b;margin-top:2px;">
          Servicio: <?php echo e(\Carbon\Carbon::parse($cam->fecha_servicio)->format('d/m/Y')); ?>

          <?php if($cam->verraco_descripcion): ?> · <?php echo e($cam->verraco_descripcion); ?><?php endif; ?>
          · <?php echo e($cam->tipo_servicio === 'inseminacion_artificial' ? '💉 IA' : '🐗 Monta'); ?>

        </div>
        <?php if($cam->fecha_probable_parto): ?>
        <div style="font-size:.74rem;color:#b45309;">
          Parto probable: <?php echo e(\Carbon\Carbon::parse($cam->fecha_probable_parto)->format('d/m/Y')); ?>

        </div>
        <?php endif; ?>
      </div>
      <div style="text-align:right;">
        <span class="camada-estado" style="color:<?php echo e($cam->estado_color); ?>;"><?php echo e($cam->estado_legible); ?></span>
      </div>
    </div>

    
    <?php if($cam->fecha_parto_real): ?>
    <div style="margin-top:8px;display:flex;gap:16px;flex-wrap:wrap;font-size:.78rem;background:#f8fafc;border-radius:8px;padding:8px 10px;">
      <span>🐷 <strong><?php echo e($cam->lechones_nacidos_vivos ?? 0); ?></strong> vivos</span>
      <span>💀 <strong><?php echo e($cam->lechones_nacidos_muertos ?? 0); ?></strong> muertos</span>
      <?php if($cam->peso_promedio_nacer_kg): ?>
      <span>⚖️ <strong><?php echo e($cam->peso_promedio_nacer_kg); ?></strong> kg/lechón</span>
      <?php endif; ?>
      <?php if($cam->fecha_destete): ?>
      <span>🍼 Destete: <?php echo e(\Carbon\Carbon::parse($cam->fecha_destete)->format('d/m/Y')); ?>

        · <?php echo e($cam->lechones_destetados); ?> destetados
        <?php if($cam->peso_promedio_destete_kg): ?>· <?php echo e($cam->peso_promedio_destete_kg); ?> kg/u<?php endif; ?>
      </span>
      <?php endif; ?>
    </div>
    @endif

    
    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
      <?php if($cam->resultado_diagnostico === 'pendiente'): ?>
      <button onclick="openDiagnostico(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>')"
              class="btn btn-sm btn-secondary" style="font-size:.75rem;">
        🔬 Diagnóstico preñez
      </button>
      <?php endif; ?>
      <?php if($cam->resultado_diagnostico === 'positivo' && !$cam->fecha_parto_real): ?>
      <button onclick="openParto(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>',<?php echo e($cam->numero_camada); ?>)"
              class="btn btn-sm btn-primary" style="font-size:.75rem;">
        🍼 Registrar parto
      </button>
      <?php endif; ?>
      <?php if($cam->fecha_parto_real && !$cam->fecha_destete): ?>
      <?php $diasLact = now()->diffInDays(\Carbon\Carbon::parse($cam->fecha_parto_real)); ?>
      <button onclick="openDestete(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>',<?php echo e($cam->lechones_nacidos_vivos ?? 0); ?>)"
              class="btn btn-sm <?php echo e($diasLact >= 21 ? 'btn-primary' : 'btn-ghost'); ?>" style="font-size:.75rem;">
        🍾 Destetar (día <?php echo e($diasLact); ?>)
      </button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">🐷</div>
    <p>Sin camadas registradas.</p>
    <?php if($hembras->count()): ?>
    <button onclick="openModal('modalServicio')" class="btn btn-sm btn-primary">Registrar primer servicio</button>
    <?php else: ?>
    <p style="font-size:.82rem;margin-top:8px;">Registra hembras de cría en el módulo de Animales primero.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>


<div id="modalServicio" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🐗 Registrar servicio / monta</div>
    <form method="POST" action="<?php echo e(route('porcicola.reproductivo.servicio')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Hembra de cría *</label>
        <select name="cerda_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $hembras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($h->id); ?>"><?php echo e($h->nombre_lote); ?> (<?php echo e($h->num_partos ?? 0); ?> partos)</option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo de servicio *</label>
          <select name="tipo_servicio" class="form-control" required onchange="toggleVerraco(this.value)">
            <option value="monta_natural">🐗 Monta natural</option>
            <option value="inseminacion_artificial">💉 Inseminación artificial</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha del servicio *</label>
          <input type="date" name="fecha_servicio" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div class="form-group" id="wrapVerraco">
        <label id="labelVerraco">Verraco</label>
        <select name="verraco_descripcion" id="selectVerraco" class="form-control" onchange="syncVerraco(this)">
          <option value="">— Seleccionar —</option>
          <?php $__currentLoopData = $verracos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($v->nombre_lote); ?><?php echo e($v->raza_porcina ? ' ('.$v->raza_porcina.')' : ''); ?>">
            🐗 <?php echo e($v->nombre_lote); ?><?php echo e($v->raza_porcina ? ' · '.$v->raza_porcina : ''); ?>

          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <option value="__manual__">✏️ Ingresar manualmente...</option>
        </select>
      </div>
      <div class="form-group" id="wrapVerracoManual" style="display:none;">
        <label>Verraco / código semen</label>
        <input type="text" name="verraco_descripcion_manual" class="form-control"
               placeholder="Ej: Duroc Campeón / Código SC-123">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Número de montas, condición de la cerda..."></textarea>
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        ✅ Se generarán automáticamente en la Agenda: tarea de diagnóstico de preñez (día 28) y preparación para el parto.
        <br>🐷 Gestación porcina: <strong>114 días</strong> (3 meses, 3 semanas, 3 días)
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalServicio')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar servicio</button>
      </div>
    </form>
  </div>
</div>


<div id="modalDiagnostico" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🔬 Diagnóstico de preñez — <span id="nombreCerdaDiag"></span></div>
    <form id="formDiagnostico" method="POST" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Fecha del diagnóstico *</label>
        <input type="date" name="fecha_diagnostico" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div class="form-group">
        <label>Resultado *</label>
        <select name="resultado_diagnostico" class="form-control" required>
          <option value="positivo">✅ Positivo — preñada</option>
          <option value="negativo">❌ Negativo — repetir servicio</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalDiagnostico')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar resultado</button>
      </div>
    </form>
  </div>
</div>


<div id="modalParto" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🍼 Registrar parto — <span id="nombreCerdaParto"></span></div>
    <form method="POST" action="<?php echo e(route('porcicola.reproductivo.parto')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="camada_id" id="camadaIdParto">
      <div class="form-group">
        <label>Fecha del parto *</label>
        <input type="date" name="fecha_parto_real" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div class="form-group">
          <label>Nacidos vivos *</label>
          <input type="number" name="lechones_nacidos_vivos" class="form-control" min="0" value="10" required>
        </div>
        <div class="form-group">
          <label>Nacidos muertos</label>
          <input type="number" name="lechones_nacidos_muertos" class="form-control" min="0" value="0">
        </div>
        <div class="form-group">
          <label>Momificados</label>
          <input type="number" name="lechones_momificados" class="form-control" min="0" value="0">
        </div>
      </div>
      <div class="form-group">
        <label>Peso total de la camada al nacer (kg)</label>
        <input type="number" name="peso_camada_nacer_kg" class="form-control" step="0.1" min="0"
               placeholder="Ej: 14.5 (10 lechones × 1.45 kg promedio)">
        <div style="font-size:.72rem;color:#64748b;margin-top:2px;">
          Peso promedio ideal al nacer: 1.3-1.6 kg/lechón
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Parto asistido, presentaciones, temperatura..."></textarea>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#15803d;margin-bottom:10px;">
        ✅ Se generará en la Agenda: tarea de <strong>hierro dextrano</strong> (día 3) y <strong>destete</strong> (día 24).
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalParto')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar parto</button>
      </div>
    </form>
  </div>
</div>


<div id="modalDestete" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">🍾 Registrar destete — <span id="nombreCerdaDestete"></span></div>
    <form id="formDestete" method="POST" action="<?php echo e(route('porcicola.reproductivo.destete')); ?>">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="camada_id" id="camadaIdDestete">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha de destete *</label>
          <input type="date" name="fecha_destete" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Lechones destetados *</label>
          <input type="number" name="lechones_destetados" id="inputDestetados" class="form-control" min="0" required>
        </div>
      </div>
      <div class="form-group">
        <label>Peso total de la camada al destete (kg)</label>
        <input type="number" name="peso_camada_destete_kg" class="form-control" step="0.1" min="0"
               placeholder="Ej: 60 kg (destete ideal: 6-8 kg/lechón)">
      </div>
      <div class="form-group">
        <label>Mortalidad pre-destete (causa)</label>
        <input type="text" name="causa_mortalidad" class="form-control"
               placeholder="Aplastamiento, hipoglicemia, diarrea...">
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        ✅ Tarea de retorno a celo generada (5-7 días post-destete).
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalDestete')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar destete</button>
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

function toggleVerraco(tipo) {
  var wVer = document.getElementById('wrapVerraco');
  var lbl  = document.getElementById('labelVerraco');
  if (tipo === 'inseminacion_artificial') {
    lbl.textContent = 'Código de semen / pajilla';
    document.getElementById('wrapVerracoManual').style.display = 'block';
    wVer.style.display = 'none';
  } else {
    lbl.textContent = 'Verraco';
    wVer.style.display = 'block';
    document.getElementById('wrapVerracoManual').style.display = 'none';
  }
}
function syncVerraco(sel) {
  if (sel.value === '__manual__') {
    document.getElementById('wrapVerracoManual').style.display = 'block';
    sel.value = '';
  } else {
    document.getElementById('wrapVerracoManual').style.display = 'none';
  }
}

function openDiagnostico(id, nombre) {
  document.getElementById('nombreCerdaDiag').textContent = nombre;
  document.getElementById('formDiagnostico').action = '/porcicola/reproductivo/' + id + '/diagnostico';
  openModal('modalDiagnostico');
}
function openParto(id, nombre, numCamada) {
  document.getElementById('nombreCerdaParto').textContent = nombre + ' · Camada #' + numCamada;
  document.getElementById('camadaIdParto').value = id;
  openModal('modalParto');
}
function openDestete(id, nombre, lechonesVivos) {
  document.getElementById('nombreCerdaDestete').textContent = nombre;
  document.getElementById('camadaIdDestete').value = id;
  document.getElementById('inputDestetados').value = lechonesVivos;
  openModal('modalDestete');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/porcicola/reproductivo.blade.php ENDPATH**/ ?>