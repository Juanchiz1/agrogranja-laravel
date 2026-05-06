
<?php $__env->startSection('title','Reproductivo'); ?>
<?php $__env->startSection('page_title','Reproductivo Porcicola'); ?>
<?php $__env->startSection('back_url', route('porcicola.piara')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/porcicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div class="section-card">
  <div class="piara-stats" style="grid-template-columns:repeat(3,1fr);">
    <div class="piara-stat">
      <div class="piara-stat-ico">&#128055;</div>
      <div class="piara-stat-val"><?php echo e($promedioNacidos); ?></div>
      <div class="piara-stat-lbl">Lechones/camada</div>
    </div>
    <div class="piara-stat verde">
      <div class="piara-stat-ico">&#127868;</div>
      <div class="piara-stat-val"><?php echo e($promedioDestete); ?></div>
      <div class="piara-stat-lbl">Destetados/camada</div>
    </div>
    <div class="piara-stat naranja">
      <div class="piara-stat-ico">&#128128;</div>
      <div class="piara-stat-val"><?php echo e($pctMortPreD); ?>%</div>
      <div class="piara-stat-lbl">Mort. pre-destete</div>
    </div>
  </div>
</div>


<div class="section-card" style="padding:12px 14px;">
  <div style="display:flex;justify-content:space-between;align-items:center;">
    <div style="font-size:.85rem;color:#64748b;">
      <?php echo e($hembras->count()); ?> hembra(s) de cria · <?php echo e($verracos->count()); ?> verraco(s)
    </div>
    <?php if($hembras->count()): ?>
    <button onclick="openModal('modalServicio')" class="btn btn-sm btn-primary">
      + Servicio / Monta
    </button>
    <?php endif; ?>
  </div>
</div>


<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">Camadas</div>

  <?php $__empty_1 = true; $__currentLoopData = $camadas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $tieneVerraco  = !empty($cam->verraco_descripcion);
    $tieneProb     = !empty($cam->fecha_probable_parto);
    $tieneParto    = !empty($cam->fecha_parto_real);
    $tieneDestete  = !empty($cam->fecha_destete);
    $tienePesoN    = !empty($cam->peso_promedio_nacer_kg);
    $tienePesoD    = !empty($cam->peso_promedio_destete_kg);

    $esPendiente   = ($cam->resultado_diagnostico === 'pendiente');
    $esPositivo    = ($cam->resultado_diagnostico === 'positivo');
    $esNegativo    = ($cam->resultado_diagnostico === 'negativo');

    $puedeRegistrarParto = ($esPositivo && !$tieneParto);
    $puedeDestetar       = ($tieneParto && !$tieneDestete);

    $diasLactancia  = 0;
    $btnDesteteClass = 'btn-ghost';
    if ($puedeDestetar) {
        $diasLactancia  = now()->diffInDays(\Carbon\Carbon::parse($cam->fecha_parto_real));
        $btnDesteteClass = ($diasLactancia >= 21) ? 'btn-primary' : 'btn-ghost';
    }

    $estadoTexto = isset($cam->estado_legible) ? $cam->estado_legible : 'Sin estado';
    $estadoColor = isset($cam->estado_color)   ? $cam->estado_color   : '#94a3b8';

    $fechaServFmt  = \Carbon\Carbon::parse($cam->fecha_servicio)->format('d/m/Y');
    $fechaProbFmt  = $tieneProb   ? \Carbon\Carbon::parse($cam->fecha_probable_parto)->format('d/m/Y') : '';
    $fechaPartoFmt = $tieneParto  ? \Carbon\Carbon::parse($cam->fecha_parto_real)->format('d/m/Y') : '';
    $fechaDestFmt  = $tieneDestete ? \Carbon\Carbon::parse($cam->fecha_destete)->format('d/m/Y') : '';

    $tipoServLabel = ($cam->tipo_servicio === 'inseminacion_artificial') ? 'IA' : 'Monta';
    $lechVivos   = (int)($cam->lechones_nacidos_vivos ?? 0);
    $lechMuertos = (int)($cam->lechones_nacidos_muertos ?? 0);
    $lechDestet  = (int)($cam->lechones_destetados ?? 0);
  ?>
  <div class="camada-row">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:6px;">
      <div>
        <div style="font-weight:700;font-size:.9rem;">
          <?php echo e($cam->nombre_lote); ?>

          <span style="font-weight:400;font-size:.78rem;color:#94a3b8;">· Camada #<?php echo e($cam->numero_camada); ?></span>
        </div>
        <div style="font-size:.74rem;color:#64748b;margin-top:2px;">
          Servicio: <?php echo e($fechaServFmt); ?>

          <?php if($tieneVerraco): ?>
          · <?php echo e($cam->verraco_descripcion); ?>

          <?php endif; ?>
          · <?php echo e($tipoServLabel); ?>

        </div>
        <?php if($tieneProb): ?>
        <div style="font-size:.74rem;color:#b45309;">Parto probable: <?php echo e($fechaProbFmt); ?></div>
        <?php endif; ?>
      </div>
      <div>
        <span class="camada-estado" style="color:<?php echo e($estadoColor); ?>;"><?php echo e($estadoTexto); ?></span>
      </div>
    </div>

    <?php if($tieneParto): ?>
    <div style="margin-top:8px;display:flex;gap:14px;flex-wrap:wrap;font-size:.78rem;background:#f8fafc;border-radius:8px;padding:8px 10px;">
      <span><strong><?php echo e($lechVivos); ?></strong> vivos</span>
      <span><strong><?php echo e($lechMuertos); ?></strong> muertos</span>
      <?php if($tienePesoN): ?>
      <span><?php echo e($cam->peso_promedio_nacer_kg); ?> kg/lechon</span>
      <?php endif; ?>
      <?php if($tieneDestete): ?>
      <span>
        Destete: <?php echo e($fechaDestFmt); ?> · <?php echo e($lechDestet); ?> destetados
        <?php if($tienePesoD): ?>
        · <?php echo e($cam->peso_promedio_destete_kg); ?> kg/u
        <?php endif; ?>
      </span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
      <?php if($esPendiente): ?>
      <button onclick="openDiagnostico(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>')"
              class="btn btn-sm btn-secondary" style="font-size:.75rem;">
        Diagnostico prenez
      </button>
      <?php endif; ?>
      <?php if($puedeRegistrarParto): ?>
      <button onclick="openParto(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>',<?php echo e($cam->numero_camada); ?>)"
              class="btn btn-sm btn-primary" style="font-size:.75rem;">
        Registrar parto
      </button>
      <?php endif; ?>
      <?php if($puedeDestetar): ?>
      <button onclick="openDestete(<?php echo e($cam->id); ?>,'<?php echo e(addslashes($cam->nombre_lote)); ?>',<?php echo e($lechVivos); ?>)"
              class="btn btn-sm <?php echo e($btnDesteteClass); ?>" style="font-size:.75rem;">
        Destetar (dia <?php echo e($diasLactancia); ?>)
      </button>
      <?php endif; ?>
    </div>
  </div>

  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:24px;color:#64748b;">
    <div style="font-size:2.5rem;">&#128055;</div>
    <p>Sin camadas registradas.</p>
    <?php if($hembras->count()): ?>
    <button onclick="openModal('modalServicio')" class="btn btn-sm btn-primary" style="margin-top:8px;">
      Registrar primer servicio
    </button>
    <?php else: ?>
    <p style="font-size:.82rem;margin-top:8px;">Registra hembras de cria en Animales primero.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>


<div id="modalServicio" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar servicio / monta</div>
    <form method="POST" action="<?php echo e(route('porcicola.reproductivo.servicio')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Hembra de cria *</label>
        <select name="cerda_id" class="form-control" required>
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $hembras; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($h->id); ?>"><?php echo e($h->nombre_lote); ?> (<?php echo e($h->num_partos ?? 0); ?> partos)</option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo_servicio" class="form-control" required onchange="toggleVerraco(this.value)">
            <option value="monta_natural">Monta natural</option>
            <option value="inseminacion_artificial">Inseminacion artificial</option>
          </select>
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha_servicio" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
      </div>
      <div class="form-group" id="wrapVerraco">
        <label id="labelVerraco">Verraco</label>
        <select name="verraco_descripcion" id="selectVerraco" class="form-control" onchange="syncVerraco(this)">
          <option value="">Sin seleccionar</option>
          <?php $__currentLoopData = $verracos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($v->nombre_lote); ?><?php echo e($v->raza_porcina ? ' ('.$v->raza_porcina.')' : ''); ?>">
            <?php echo e($v->nombre_lote); ?><?php echo e($v->raza_porcina ? ' · '.$v->raza_porcina : ''); ?>

          </option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <option value="__manual__">Ingresar manualmente...</option>
        </select>
      </div>
      <div class="form-group" id="wrapVerracoManual" style="display:none;">
        <label>Nombre del verraco / codigo semen</label>
        <input type="text" name="verraco_descripcion_manual" class="form-control" placeholder="Ej: Duroc Campeon">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        Gestacion porcina: <strong>114 dias</strong>. Se generaran tareas de diagnostico y preparacion en la Agenda.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalServicio')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalDiagnostico" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Diagnostico de prenez — <span id="nombreCerdaDiag"></span></div>
    <form id="formDiagnostico" method="POST" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Fecha del diagnostico *</label>
        <input type="date" name="fecha_diagnostico" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div class="form-group">
        <label>Resultado *</label>
        <select name="resultado_diagnostico" class="form-control" required>
          <option value="positivo">Positivo — prenada</option>
          <option value="negativo">Negativo — repetir servicio</option>
        </select>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalDiagnostico')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalParto" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar parto — <span id="nombreCerdaParto"></span></div>
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
          <label>Muertos</label>
          <input type="number" name="lechones_nacidos_muertos" class="form-control" min="0" value="0">
        </div>
        <div class="form-group">
          <label>Momificados</label>
          <input type="number" name="lechones_momificados" class="form-control" min="0" value="0">
        </div>
      </div>
      <div class="form-group">
        <label>Peso total camada al nacer (kg)</label>
        <input type="number" name="peso_camada_nacer_kg" class="form-control" step="0.1" min="0"
               placeholder="Ej: 14.5 — ideal 1.3-1.6 kg/lechon">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#15803d;margin-bottom:10px;">
        Tareas generadas en Agenda: hierro dextrano (dia 3) y destete (dia 24).
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalParto')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar parto</button>
      </div>
    </form>
  </div>
</div>


<div id="modalDestete" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Registrar destete — <span id="nombreCerdaDestete"></span></div>
    <form method="POST" action="<?php echo e(route('porcicola.reproductivo.destete')); ?>">
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
        <label>Peso total camada al destete (kg)</label>
        <input type="number" name="peso_camada_destete_kg" class="form-control" step="0.1" min="0"
               placeholder="Ej: 60 kg — ideal 6-8 kg/lechon">
      </div>
      <div class="form-group">
        <label>Causa mortalidad pre-destete</label>
        <input type="text" name="causa_mortalidad" class="form-control"
               placeholder="Aplastamiento, hipoglicemia...">
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        Tarea de retorno a celo generada (5-7 dias post-destete).
      </div>
      <div style="display:flex;gap:8px;">
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
    lbl.textContent = 'Codigo semen / pajilla';
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