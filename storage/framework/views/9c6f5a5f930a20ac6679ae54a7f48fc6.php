
<?php $__env->startSection('title', $persona->nombre); ?>
<?php $__env->startSection('page_title', ($tipos[$persona->tipo]['emoji']??'👤').' '.$persona->nombre); ?>
<?php $__env->startSection('back_url', route('personas.index')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/personas.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  $tipoInfo = $tipos[$persona->tipo] ?? ['label'=>ucfirst($persona->tipo),'emoji'=>'👤'];
  $esTrabajador = $persona->tipo === 'trabajador';
?>


<div style="text-align:center;padding:16px 0 20px;">
  <?php if($persona->foto): ?>
    <img src="<?php echo e(asset($persona->foto)); ?>" class="pers-avatar-lg" style="overflow:hidden;">
  <?php else: ?>
    <div class="pers-avatar-lg"><?php echo e(mb_strtoupper(mb_substr($persona->nombre,0,1))); ?></div>
  <?php endif; ?>
  <div style="font-size:.8rem;font-weight:600;color:var(--text-secondary);"><?php echo e($tipoInfo['emoji']); ?> <?php echo e($tipoInfo['label']); ?></div>
  <?php if($persona->cargo): ?>
    <div style="font-size:.85rem;color:var(--verde-dark);font-weight:600;margin-top:4px;"><?php echo e($persona->cargo); ?></div>
  <?php endif; ?>
</div>


<?php if($esTrabajador): ?>
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
  <div class="stat-card">
    <div class="stat-value text-green" style="font-size:1rem;">$<?php echo e(number_format($totalPagadoMes,0,',','.')); ?></div>
    <div class="stat-label">Pagado mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-green"><?php echo e($totalDias); ?></div>
    <div class="stat-label">Dias trab.</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="font-size:1rem;">$<?php echo e(number_format($totalPagado,0,',','.')); ?></div>
    <div class="stat-label">Total pagado</div>
  </div>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title-row">
    <span>Informacion</span>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">Editar</button>
  </div>

  <?php if($persona->telefono): ?>
  <div class="info-row">
    <span class="info-label">Telefono</span>
    <a href="tel:<?php echo e($persona->telefono); ?>" style="font-weight:600;color:var(--verde-dark);"><?php echo e($persona->telefono); ?></a>
  </div>
  <?php endif; ?>

  <?php if($persona->email): ?>
  <div class="info-row">
    <span class="info-label">Email</span>
    <span style="font-weight:600;"><?php echo e($persona->email); ?></span>
  </div>
  <?php endif; ?>

  <?php if($persona->documento): ?>
  <div class="info-row">
    <span class="info-label">Documento</span>
    <span style="font-weight:600;"><?php echo e($persona->documento); ?></span>
  </div>
  <?php endif; ?>

  <?php if($persona->direccion): ?>
  <div class="info-row">
    <span class="info-label">Direccion</span>
    <span style="font-weight:600;"><?php echo e($persona->direccion); ?></span>
  </div>
  <?php endif; ?>

  <?php if($esTrabajador): ?>

    <?php if($persona->tipo_contrato): ?>
    <div class="info-row">
      <span class="info-label">Contrato</span>
      <span style="font-weight:600;"><?php echo e(ucfirst($persona->tipo_contrato)); ?></span>
    </div>
    <?php endif; ?>

    <?php if($persona->valor_jornal): ?>
    <div class="info-row">
      <span class="info-label">Valor jornal</span>
      <span style="font-weight:600;color:var(--verde-dark);">$<?php echo e(number_format($persona->valor_jornal,0,',','.')); ?>/dia</span>
    </div>
    <?php endif; ?>

    <?php if($persona->valor_mensual): ?>
    <div class="info-row">
      <span class="info-label">Salario mensual</span>
      <span style="font-weight:600;color:var(--verde-dark);">$<?php echo e(number_format($persona->valor_mensual,0,',','.')); ?></span>
    </div>
    <?php endif; ?>

    <?php if($persona->labores): ?>
    <div class="info-row">
      <span class="info-label">Labores</span>
      <span style="font-weight:600;"><?php echo e($persona->labores); ?></span>
    </div>
    <?php endif; ?>

    <?php if($persona->fecha_ingreso): ?>
    <div class="info-row">
      <span class="info-label">Ingreso</span>
      <span style="font-weight:600;"><?php echo e(\Carbon\Carbon::parse($persona->fecha_ingreso)->format('d/m/Y')); ?></span>
    </div>
    <?php endif; ?>

  <?php endif; ?>

  <?php if($persona->notas): ?>
  <div style="margin-top:10px;padding:8px 10px;background:var(--verde-bg);border-radius:8px;font-size:.83rem;color:var(--verde-dark);">
    <?php echo e($persona->notas); ?>

  </div>
  <?php endif; ?>

  <div class="flex gap-2 mt-3">
    <form method="POST" action="<?php echo e(route('personas.favorito',$persona->id)); ?>">
      <?php echo csrf_field(); ?>
      <button class="btn btn-sm btn-ghost">
        <?php if($persona->favorito): ?> Quitar favorito <?php else: ?> Marcar favorito <?php endif; ?>
      </button>
    </form>
  </div>
</div>


<?php if($esTrabajador): ?>
<div class="section-card">
  <div class="section-title-row">
    <span>Pagos (<?php echo e($pagos->count()); ?>)</span>
    <button onclick="openModal('modalPago')" class="btn btn-sm btn-primary">+ Pago</button>
  </div>

  <?php if($pagos->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin pagos registrados.</p>
  <?php else: ?>
    <?php $__currentLoopData = $pagos->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $pgConcepto = $pg->concepto ?? ucfirst($pg->tipo_pago);
      $pgFecha    = \Carbon\Carbon::parse($pg->fecha)->format('d/m/Y');
      $pgMeta     = $pgFecha;
      if($pg->dias)          $pgMeta .= ' - '.$pg->dias.' dias';
      if($pg->cultivo_nombre) $pgMeta .= ' - '.$pg->cultivo_nombre;
    ?>
    <div class="pago-row">
      <div style="width:34px;height:34px;border-radius:50%;background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">$</div>
      <div style="flex:1;">
        <div style="font-size:.87rem;font-weight:600;"><?php echo e($pgConcepto); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($pgMeta); ?></div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($pg->valor,0,',','.')); ?></div>
        <form method="POST" action="<?php echo e(route('personas.pago.delete',[$persona->id,$pg->id])); ?>" onsubmit="return confirm('Eliminar?')">
          <?php echo csrf_field(); ?>
          <button type="submit" class="btn btn-sm btn-ghost" style="font-size:.7rem;padding:1px 6px;color:var(--text-muted);">x Eliminar</button>
        </form>
      </div>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>
<?php endif; ?>


<div class="section-card">
  <div class="section-title-row">
    <span>Registro de labores (<?php echo e($labores->count()); ?>)</span>
    <button onclick="openModal('modalLabor')" class="btn btn-sm btn-primary">+ Labor</button>
  </div>

  <?php if($labores->isEmpty()): ?>
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin labores registradas.</p>
  <?php else: ?>
    <?php $__currentLoopData = $labores->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php
      $lbMeta = \Carbon\Carbon::parse($lb->fecha)->format('d/m/Y');
      if($lb->horas)          $lbMeta .= ' - '.$lb->horas.'h';
      if($lb->cultivo_nombre)  $lbMeta .= ' - '.$lb->cultivo_nombre;
      if($lb->animal_nombre)   $lbMeta .= ' - '.$lb->animal_nombre;
    ?>
    <div class="labor-row">
      <div style="flex:1;">
        <div style="font-size:.87rem;font-weight:600;"><?php echo e($lb->descripcion); ?></div>
        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;"><?php echo e($lbMeta); ?></div>
        <?php if($lb->insumos_usados): ?>
        <div style="font-size:.73rem;color:var(--text-muted);">Insumos: <?php echo e($lb->insumos_usados); ?></div>
        <?php endif; ?>
      </div>
      <form method="POST" action="<?php echo e(route('personas.labor.delete',[$persona->id,$lb->id])); ?>" onsubmit="return confirm('Eliminar?')">
        <?php echo csrf_field(); ?>
        <button type="submit" class="btn btn-sm btn-ghost" style="font-size:.7rem;color:var(--text-muted);">x</button>
      </form>
    </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  <?php endif; ?>
</div>

<div style="margin-bottom:80px;"></div>


<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Editar persona</h3>
    <form method="POST" action="<?php echo e(route('personas.update',$persona->id)); ?>" enctype="multipart/form-data">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="back" value="detalle">
      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" class="form-control" required>
          <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $ti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($k); ?>" <?php echo e($persona->tipo===$k ? 'selected' : ''); ?>><?php echo e($ti['emoji']); ?> <?php echo e($ti['label']); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" class="form-control" required value="<?php echo e($persona->nombre); ?>">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Telefono</label>
          <input type="tel" name="telefono" class="form-control" value="<?php echo e($persona->telefono); ?>">
        </div>
        <div class="form-group">
          <label>Documento</label>
          <input type="text" name="documento" class="form-control" value="<?php echo e($persona->documento); ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Cargo</label>
        <input type="text" name="cargo" class="form-control" value="<?php echo e($persona->cargo); ?>" placeholder="Jornalero, Ordeñador...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Tipo contrato</label>
          <select name="tipo_contrato" class="form-control">
            <option value="">N/A</option>
            <option value="jornal"   <?php echo e(($persona->tipo_contrato??'')==='jornal'   ? 'selected' : ''); ?>>Jornal/Dia</option>
            <option value="mensual"  <?php echo e(($persona->tipo_contrato??'')==='mensual'  ? 'selected' : ''); ?>>Mensual</option>
            <option value="destajo"  <?php echo e(($persona->tipo_contrato??'')==='destajo'  ? 'selected' : ''); ?>>Destajo</option>
          </select>
        </div>
        <div class="form-group">
          <label>Valor jornal</label>
          <input type="number" step="1000" name="valor_jornal" class="form-control" value="<?php echo e($persona->valor_jornal); ?>">
        </div>
      </div>
      <div class="form-group">
        <label>Salario mensual</label>
        <input type="number" step="10000" name="valor_mensual" class="form-control" value="<?php echo e($persona->valor_mensual); ?>">
      </div>
      <div class="form-group">
        <label>Labores</label>
        <input type="text" name="labores" class="form-control" value="<?php echo e($persona->labores); ?>" placeholder="Siembra, fumigacion...">
      </div>
      <div class="form-group">
        <label>Foto</label>
        <?php if($persona->foto): ?>
          <img src="<?php echo e(asset($persona->foto)); ?>" style="width:60px;border-radius:50%;margin-bottom:6px;display:block;">
        <?php endif; ?>
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2"><?php echo e($persona->notas); ?></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalPago" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar pago</h3>
    <form method="POST" action="<?php echo e(route('personas.pago.store',$persona->id)); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Tipo de pago *</label>
        <select name="tipo_pago" class="form-control" required>
          <option value="jornal">Jornal (por dias)</option>
          <option value="mensual">Mensual</option>
          <option value="bono">Bono</option>
          <option value="anticipo">Anticipo</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Concepto</label>
        <input type="text" name="concepto" class="form-control" placeholder="Ej: Semana del 7 al 11...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Dias trabajados</label>
          <input type="number" step="0.5" name="dias" class="form-control" placeholder="0" id="diasPago">
        </div>
        <div class="form-group">
          <label>Valor total (COP) *</label>
          <input type="number" step="1000" name="valor" class="form-control" required placeholder="0" id="valorPago">
        </div>
      </div>
      <div class="form-group">
        <label>Fecha *</label>
        <input type="date" name="fecha" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
      </div>
      <div class="form-group">
        <label>Asociar a cultivo</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Asociar a animal</label>
        <select name="animal_id" class="form-control">
          <option value="">Ninguno</option>
          <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($an->id); ?>"><?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:10px;border:1px solid #86efac;margin-bottom:12px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="crear_gasto" value="1" checked style="width:18px;height:18px;">
          <span style="font-size:.87rem;color:#166534;font-weight:600;">Crear gasto automaticamente en finanzas</span>
        </label>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPago')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar pago</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalLabor" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar labor</h3>
    <form method="POST" action="<?php echo e(route('personas.labor.store',$persona->id)); ?>">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label>Descripcion de la labor *</label>
        <input type="text" name="descripcion" class="form-control" required placeholder="Ej: Fumigacion lote norte...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" required value="<?php echo e(date('Y-m-d')); ?>">
        </div>
        <div class="form-group">
          <label>Horas trabajadas</label>
          <input type="number" step="0.5" name="horas" class="form-control" placeholder="0">
        </div>
      </div>
      <div class="form-group">
        <label>Cultivo relacionado</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          <?php $__currentLoopData = $cultivos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($cv->id); ?>"><?php echo e($cv->nombre); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Animal relacionado</label>
        <select name="animal_id" class="form-control">
          <option value="">Ninguno</option>
          <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $an): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($an->id); ?>"><?php echo e($an->especie); ?> - <?php echo e($an->nombre_lote); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group">
        <label>Insumos usados</label>
        <input type="text" name="insumos_usados" class="form-control" placeholder="Ej: 2 litros Glifosato...">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalLabor')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar labor</button>
      </div>
    </form>
  </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
<?php if($persona->valor_jornal): ?>
document.getElementById('diasPago').addEventListener('input', function() {
  var dias = parseFloat(this.value) || 0;
  if (dias > 0) {
    document.getElementById('valorPago').value = Math.round(dias * <?php echo e($persona->valor_jornal); ?>);
  }
});
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/persona-detalle.blade.php ENDPATH**/ ?>