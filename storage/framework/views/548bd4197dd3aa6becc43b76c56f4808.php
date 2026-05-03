
<?php $__env->startSection('title','Sanidad Bovina'); ?>
<?php $__env->startSection('page_title','💉 Sanidad Programada'); ?>
<?php $__env->startSection('back_url', route('bovino.hato')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/bovino.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<?php if($vencidos->count()): ?>
<div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:12px;padding:12px 14px;margin-bottom:12px;">
  <div style="font-weight:700;color:#dc2626;margin-bottom:4px;">🔴 <?php echo e($vencidos->count()); ?> protocolo(s) vencido(s)</div>
  <?php $__currentLoopData = $vencidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="font-size:.82rem;color:#7f1d1d;"><?php echo e($v->nombre_protocolo); ?> — vencido desde <?php echo e(\Carbon\Carbon::parse($v->proxima_aplicacion)->format('d/m/Y')); ?></div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Protocolos activos</div>
    <button onclick="openModal('modalPersonalizado')" class="btn btn-sm btn-ghost">＋ Personalizado</button>
  </div>

  <?php $__currentLoopData = $protocolos->where('activo',1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $hoy = now()->toDateString();
    $prox = $p->proxima_aplicacion;
    if (!$prox)              { $urgencia='sin-fecha'; $color='#94a3b8'; $bg='#f8fafc'; }
    elseif ($prox < $hoy)   { $urgencia='vencido';   $color='#dc2626'; $bg='#fef2f2'; }
    elseif ($prox <= now()->addDays(7)->toDateString())  { $urgencia='urgente'; $color='#f59e0b'; $bg='#fffbeb'; }
    elseif ($prox <= now()->addDays(30)->toDateString()) { $urgencia='proximo';  $color='#3b82f6'; $bg='#eff6ff'; }
    else                    { $urgencia='ok';         $color='#22c55e'; $bg='#f0fdf4'; }
    $iconos = ['vencido'=>'🔴','urgente'=>'🟡','proximo'=>'🔵','ok'=>'🟢','sin-fecha'=>'⚪'];
  ?>
  <div class="sanidad-card" style="border-left-color:<?php echo e($color); ?>;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap;">
      <div>
        <div style="font-weight:700;font-size:.92rem;">
          <?php echo e($iconos[$urgencia]); ?> <?php echo e($p->nombre_protocolo); ?>

        </div>
        <div style="font-size:.78rem;color:#64748b;margin-top:3px;">
          Cada <?php echo e($p->frecuencia_dias); ?> días
          <?php if($p->via_administracion): ?> · Vía: <?php echo e($p->via_administracion); ?> <?php endif; ?>
          <?php if($p->ultima_aplicacion): ?> · Última: <?php echo e(\Carbon\Carbon::parse($p->ultima_aplicacion)->format('d/m/Y')); ?> <?php endif; ?>
        </div>
        <?php if($prox): ?>
        <div style="font-size:.82rem;font-weight:600;margin-top:4px;color:<?php echo e($color); ?>;">
          Próxima: <?php echo e(\Carbon\Carbon::parse($prox)->format('d/m/Y')); ?>

          <?php if($prox < $hoy): ?>
            (<?php echo e(\Carbon\Carbon::parse($prox)->diffInDays(now())); ?> días de atraso)
          <?php elseif($prox !== $hoy): ?>
            (en <?php echo e(now()->diffInDays($prox)); ?> días)
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="font-size:.82rem;color:#94a3b8;margin-top:4px;">Sin fecha registrada — registra la primera aplicación</div>
        <?php endif; ?>
      </div>
      <button onclick="openAplicar(<?php echo e($p->id); ?>,'<?php echo e(addslashes($p->nombre_protocolo)); ?>','<?php echo e($p->producto_usado); ?>','<?php echo e($p->dosis); ?>')"
              class="btn btn-sm <?php echo e($urgencia==='ok' ? 'btn-ghost' : 'btn-primary'); ?>"
              style="white-space:nowrap;">
        💉 <?php echo e($prox ? 'Registrar aplicación' : 'Primera aplicación'); ?>

      </button>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if($historial->count()): ?>
<div class="section-card">
  <div class="section-title" style="margin-bottom:10px;">📜 Últimas aplicaciones</div>
  <?php $__currentLoopData = $historial; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="display:flex;justify-content:space-between;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.83rem;">
    <span><?php echo e(str_replace('[Bovino Sanidad] ','',$h->titulo)); ?></span>
    <span style="color:#64748b;"><?php echo e(\Carbon\Carbon::parse($h->fecha_completada)->format('d/m/Y')); ?></span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>

<div style="margin-bottom:80px;"></div>


<div id="modalAplicar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💉 <span id="aplicarNombre"></span></div>
    <form method="POST" id="formAplicar" action="">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Fecha de aplicación *</label>
        <input type="date" name="fecha_aplicacion" class="form-control" value="<?php echo e(now()->toDateString()); ?>" required>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Producto</label>
          <input type="text" name="producto_usado" id="aplicarProducto" class="form-control" placeholder="Nombre comercial">
        </div>
        <div class="form-group">
          <label>Dosis</label>
          <input type="text" name="dosis" id="aplicarDosis" class="form-control" placeholder="Ej: 5ml por animal">
        </div>
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"></textarea>
      </div>
      <div style="font-size:.78rem;color:#64748b;background:#eff6ff;padding:8px 10px;border-radius:8px;margin-bottom:12px;">
        📅 Se generará una tarea en Agenda para la próxima aplicación.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalAplicar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Registrar</button>
      </div>
    </form>
  </div>
</div>


<div id="modalPersonalizado" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">＋ Protocolo personalizado</div>
    <form method="POST" action="<?php echo e(route('bovino.sanidad.personalizado')); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Nombre del protocolo *</label>
        <input type="text" name="nombre_protocolo" class="form-control" required placeholder="Ej: Vitamina E + Selenio">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Frecuencia (días) *</label>
          <input type="number" name="frecuencia_dias" class="form-control" min="1" required placeholder="Ej: 90">
        </div>
        <div class="form-group">
          <label>Vía</label>
          <select name="via_administracion" class="form-control">
            <option value="intramuscular">Intramuscular</option>
            <option value="subcutanea">Subcutánea</option>
            <option value="oral">Oral</option>
            <option value="topica">Tópica</option>
            <option value="otra">Otra</option>
          </select>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalPersonalizado')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Agregar</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function openModal(id){ var m=document.getElementById(id); m.style.display='flex'; m.classList.add('open'); document.body.style.overflow='hidden'; }
function closeModal(id){ var m=document.getElementById(id); m.style.display='none'; m.classList.remove('open'); document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });

function openAplicar(id, nombre, producto, dosis) {
  document.getElementById('aplicarNombre').textContent = nombre;
  document.getElementById('aplicarProducto').value = producto || '';
  document.getElementById('aplicarDosis').value = dosis || '';
  document.getElementById('formAplicar').action = '/bovino/sanidad/'+id+'/aplicar';
  openModal('modalAplicar');
}
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/bovino/sanidad.blade.php ENDPATH**/ ?>