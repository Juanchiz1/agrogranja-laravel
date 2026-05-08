
<?php $__env->startSection('title','Calidad del Agua'); ?>
<?php $__env->startSection('page_title','Calidad del Agua'); ?>
<?php $__env->startSection('back_url', route('piscicola.estanques')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/piscicola.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<?php if($alertasActivas->count()): ?>
<div class="section-card" style="border-left:4px solid #dc2626;">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    Alertas recientes (<?php echo e($alertasActivas->count()); ?>)
  </div>
  <?php $__currentLoopData = $alertasActivas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $al): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="alerta-pisc critica">
    <span>&#9888;</span>
    <div>
      <strong><?php echo e($al->nombre_estanque); ?></strong>
      — <?php echo e(\Carbon\Carbon::parse($al->fecha)->format('d/m/Y')); ?>

      <div style="font-size:.72rem;margin-top:2px;">
        <?php if($al->oxigeno_mgl !== null && ($al->oxigeno_mgl < 5 || $al->oxigeno_mgl > 8)): ?>
          O2: <?php echo e($al->oxigeno_mgl); ?> mg/L (ideal 5-8) —
        <?php endif; ?>
        <?php if($al->ph !== null && ($al->ph < 6.5 || $al->ph > 8.5)): ?>
          pH: <?php echo e($al->ph); ?> (ideal 6.5-8.5) —
        <?php endif; ?>
        <?php if($al->temperatura_c !== null && ($al->temperatura_c < 25 || $al->temperatura_c > 32)): ?>
          Temp: <?php echo e($al->temperatura_c); ?>°C (ideal 25-32) —
        <?php endif; ?>
        <?php if($al->amoniaco_mg_l !== null && $al->amoniaco_mg_l > 0.05): ?>
          NH3: <?php echo e($al->amoniaco_mg_l); ?> mg/L (max 0.05)
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">Estado actual por estanque</div>
    <button onclick="openModal('modalNuevaCalidad')" class="btn btn-sm btn-primary">+ Registrar</button>
  </div>

  <?php $__empty_1 = true; $__currentLoopData = $estanques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $est): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
  <?php
    $reg = $ultimosRegistros[$est->id] ?? null;
    $tieneReg = !empty($reg);
    // Pre-calcular estados de cada parametro (sin directivas blade en el loop)
    $o2Ok   = true;  $phOk = true; $tempOk = true; $turbOk = true; $nh3Ok = true;
    $o2Str  = 'N/D'; $phStr = 'N/D'; $tempStr = 'N/D'; $turbStr = 'N/D'; $nh3Str = 'N/D';
    $diasUlt = null;

    if ($tieneReg) {
        $diasUlt = now()->diffInDays(\Carbon\Carbon::parse($reg->fecha));
        if ($reg->oxigeno_mgl !== null) {
            $o2Str = $reg->oxigeno_mgl;
            $o2Ok  = ($reg->oxigeno_mgl >= 5 && $reg->oxigeno_mgl <= 8);
        }
        if ($reg->ph !== null) {
            $phStr = $reg->ph;
            $phOk  = ($reg->ph >= 6.5 && $reg->ph <= 8.5);
        }
        if ($reg->temperatura_c !== null) {
            $tempStr = $reg->temperatura_c;
            $tempOk  = ($reg->temperatura_c >= 25 && $reg->temperatura_c <= 32);
        }
        if ($reg->turbidez_cm !== null) {
            $turbStr = $reg->turbidez_cm;
            $turbOk  = ($reg->turbidez_cm >= 25 && $reg->turbidez_cm <= 45);
        }
        if ($reg->amoniaco_mg_l !== null) {
            $nh3Str = $reg->amoniaco_mg_l;
            $nh3Ok  = ($reg->amoniaco_mg_l <= 0.05);
        }
    }
  ?>
  <div style="margin-bottom:14px;">
    <div style="font-weight:700;font-size:.87rem;margin-bottom:4px;">
      <?php echo e($est->nombre); ?>

      <?php if($tieneReg): ?>
      <span style="font-size:.72rem;color:#94a3b8;font-weight:400;">
        — Ultimo: <?php echo e(\Carbon\Carbon::parse($reg->fecha)->format('d/m/Y')); ?>

        (hace <?php echo e($diasUlt); ?> dias)
      </span>
      <?php endif; ?>
    </div>

    <?php if($tieneReg): ?>
    <div class="calidad-grid">
      <div class="calidad-param <?php echo e($o2Ok ? 'ok' : 'alerta'); ?>">
        <div class="calidad-val" style="color:<?php echo e($o2Ok ? '#15803d' : '#dc2626'); ?>;"><?php echo e($o2Str); ?></div>
        <div class="calidad-lbl">O2 mg/L</div>
        <div class="calidad-rango">ideal 5-8</div>
      </div>
      <div class="calidad-param <?php echo e($phOk ? 'ok' : 'alerta'); ?>">
        <div class="calidad-val" style="color:<?php echo e($phOk ? '#15803d' : '#dc2626'); ?>;"><?php echo e($phStr); ?></div>
        <div class="calidad-lbl">pH</div>
        <div class="calidad-rango">ideal 6.5-8.5</div>
      </div>
      <div class="calidad-param <?php echo e($tempOk ? 'ok' : 'alerta'); ?>">
        <div class="calidad-val" style="color:<?php echo e($tempOk ? '#15803d' : '#dc2626'); ?>;"><?php echo e($tempStr); ?><?php echo e($tieneReg && $reg->temperatura_c !== null ? '°C' : ''); ?></div>
        <div class="calidad-lbl">Temp</div>
        <div class="calidad-rango">ideal 25-32°C</div>
      </div>
      <div class="calidad-param <?php echo e($turbOk ? 'ok' : 'alerta'); ?>">
        <div class="calidad-val" style="color:<?php echo e($turbOk ? '#15803d' : '#dc2626'); ?>;"><?php echo e($turbStr); ?><?php echo e($tieneReg && $reg->turbidez_cm !== null ? ' cm' : ''); ?></div>
        <div class="calidad-lbl">Secchi</div>
        <div class="calidad-rango">ideal 25-45 cm</div>
      </div>
      <div class="calidad-param <?php echo e($nh3Ok ? 'ok' : 'alerta'); ?>">
        <div class="calidad-val" style="color:<?php echo e($nh3Ok ? '#15803d' : '#dc2626'); ?>;"><?php echo e($nh3Str); ?></div>
        <div class="calidad-lbl">NH3 mg/L</div>
        <div class="calidad-rango">max 0.05</div>
      </div>
      <div class="calidad-param sin-dato" style="cursor:pointer;" onclick="openCalidadEst(<?php echo e($est->id); ?>,'<?php echo e(addslashes($est->nombre)); ?>')">
        <div class="calidad-val" style="font-size:1.3rem;">+</div>
        <div class="calidad-lbl">Nuevo</div>
        <div class="calidad-rango">registro</div>
      </div>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:10px;background:#f8fafc;border-radius:8px;font-size:.82rem;color:#94a3b8;">
      Sin registros de calidad del agua.
      <button onclick="openCalidadEst(<?php echo e($est->id); ?>,'<?php echo e(addslashes($est->nombre)); ?>')"
              class="btn btn-sm btn-ghost" style="margin-left:8px;font-size:.75rem;">
        + Registrar
      </button>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    No hay estanques registrados.
  </div>
  <?php endif; ?>
</div>


<?php if($historial->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">
    Historial 30 dias (<?php echo e($historial->count()); ?> registros)
  </div>
  <?php $__currentLoopData = $historial->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $hFecha = \Carbon\Carbon::parse($h->fecha)->format('d/m/Y');
    $hAlerta = !empty($h->alerta);
  ?>
  <div style="display:flex;justify-content:space-between;align-items:center;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.82rem;">
    <div>
      <span style="font-weight:600;"><?php echo e($h->nombre_estanque); ?></span>
      <span style="color:#94a3b8;font-size:.72rem;"> — <?php echo e($hFecha); ?></span>
    </div>
    <div style="display:flex;gap:8px;font-size:.72rem;color:#64748b;">
      <?php if($h->oxigeno_mgl !== null): ?><span>O2:<?php echo e($h->oxigeno_mgl); ?></span><?php endif; ?>
      <?php if($h->ph !== null): ?><span>pH:<?php echo e($h->ph); ?></span><?php endif; ?>
      <?php if($h->temperatura_c !== null): ?><span><?php echo e($h->temperatura_c); ?>°C</span><?php endif; ?>
      <?php if($hAlerta): ?>
      <span style="background:#fef2f2;color:#dc2626;padding:1px 6px;border-radius:8px;font-weight:700;">
        Alerta
      </span>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalNuevaCalidad" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">Calidad del agua — <span id="nombreEstCalNew"></span></div>
    <form method="POST" action="<?php echo e(route('piscicola.calidad_agua.store')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Estanque *</label>
        <select name="estanque_id" id="calEstanqueId" class="form-control" required
                onchange="document.getElementById('nombreEstCalNew').textContent = this.options[this.selectedIndex].text">
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $estanques; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option value="<?php echo e($e->id); ?>"><?php echo e($e->nombre); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
        </div>
        <div class="form-group">
          <label>Hora</label>
          <input type="time" name="hora" class="form-control" value="<?php echo e(now()->format('H:i')); ?>">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
        <div class="form-group">
          <label>O2 (mg/L)</label>
          <input type="number" name="oxigeno_mg_l" class="form-control" step="0.1" placeholder="5-8">
        </div>
        <div class="form-group">
          <label>pH</label>
          <input type="number" name="ph" class="form-control" step="0.1" placeholder="6.5-8.5">
        </div>
        <div class="form-group">
          <label>Temp (°C)</label>
          <input type="number" name="temperatura_c" class="form-control" step="0.1" placeholder="25-32">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Secchi (cm)</label>
          <input type="number" name="turbidez_cm" class="form-control" step="1" placeholder="25-45">
        </div>
        <div class="form-group">
          <label>NH3 (mg/L)</label>
          <input type="number" name="amoniaco_mg_l" class="form-control" step="0.001" placeholder="max 0.05">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <input type="text" name="observaciones" class="form-control" placeholder="Color, olor, floraciones...">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalNuevaCalidad')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
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

function openCalidadEst(estId, nombre) {
  document.getElementById('calEstanqueId').value = estId;
  document.getElementById('nombreEstCalNew').textContent = nombre;
  openModal('modalNuevaCalidad');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/piscicola/calidad_agua.blade.php ENDPATH**/ ?>