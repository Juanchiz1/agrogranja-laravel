
<?php $__env->startSection('title','Personas'); ?>
<?php $__env->startSection('page_title','👥 Personas'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/personas.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaPersonas')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px;">
  <div class="stat-card"><div class="stat-value text-green"><?php echo e($statsPorTipo->sum()); ?></div><div class="stat-label">Total</div></div>
  <div class="stat-card"><div class="stat-value" style="color:#9a3412;"><?php echo e($statsPorTipo['trabajador']??0); ?></div><div class="stat-label">Trabajadores</div></div>
  <div class="stat-card" style="background:var(--verde-bg)"><div class="stat-value text-green">$<?php echo e(number_format($pagadoMes/1000,1)); ?>k</div><div class="stat-label">Pagado mes</div></div>
  <div class="stat-card"><div class="stat-value"><?php echo e(($statsPorTipo['proveedor']??0) + ($statsPorTipo['comprador']??0)); ?></div><div class="stat-label">Comerciales</div></div>
</div>


<div class="tabs-personas">
  <a href="?tab=todos"      class="tab-pers <?php echo e($tab==='todos'      ?'active':''); ?>">👥 Todos (<?php echo e($statsPorTipo->sum()); ?>)</a>
  <a href="?tab=trabajador" class="tab-pers <?php echo e($tab==='trabajador' ?'active':''); ?>">👷 Trabajadores (<?php echo e($statsPorTipo['trabajador']??0); ?>)</a>
  <a href="?tab=proveedor"  class="tab-pers <?php echo e($tab==='proveedor'  ?'active':''); ?>">🏪 Proveedores (<?php echo e($statsPorTipo['proveedor']??0); ?>)</a>
  <a href="?tab=comprador"  class="tab-pers <?php echo e($tab==='comprador'  ?'active':''); ?>">🛒 Compradores (<?php echo e($statsPorTipo['comprador']??0); ?>)</a>
  <a href="?tab=vecino"     class="tab-pers <?php echo e($tab==='vecino'     ?'active':''); ?>">🏘️ Vecinos (<?php echo e($statsPorTipo['vecino']??0); ?>)</a>
  <a href="?tab=familiar"   class="tab-pers <?php echo e($tab==='familiar'   ?'active':''); ?>">👨‍👩‍👧 Familia (<?php echo e($statsPorTipo['familiar']??0); ?>)</a>
</div>


<form method="GET" class="flex gap-2 mb-3">
  <input type="hidden" name="tab" value="<?php echo e($tab); ?>">
  <div class="search-box" style="flex:1;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar persona..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>


<?php if($tab === 'todos' && $ultimasLabores->count()): ?>
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
  <p style="font-weight:700;font-size:.85rem;margin-bottom:10px;">🔨 Últimas actividades</p>
  <?php $__currentLoopData = $ultimasLabores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ul): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid var(--border);">
    <span style="font-size:.9rem;">🔨</span>
    <div style="flex:1;">
      <div style="font-size:.85rem;font-weight:600;"><?php echo e($ul->persona_nombre); ?></div>
      <div style="font-size:.75rem;color:var(--text-secondary);"><?php echo e($ul->descripcion); ?><?php if($ul->cultivo_nombre): ?> · 🌱 <?php echo e($ul->cultivo_nombre); ?><?php endif; ?></div>
    </div>
    <div style="font-size:.72rem;color:var(--text-muted);"><?php echo e(\Carbon\Carbon::parse($ul->fecha)->format('d/m')); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<?php if($personas->isEmpty()): ?>
<div class="empty-state"><div class="emoji">👥</div><p><strong>Sin personas registradas.</strong></p><p>Toca + para agregar la primera.</p></div>
<?php else: ?>
<?php $__currentLoopData = $personas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
  $tipoInfo = $tipos[$p->tipo] ?? ['label'=>ucfirst($p->tipo),'emoji'=>'👤'];
  $tipoClass = 'tipo-'.($p->tipo ?? 'contacto');
?>
<div class="persona-card" onclick="window.location='<?php echo e(route('personas.show',$p->id)); ?>'">
  <div class="flex gap-3 items-center">
    
    <div class="persona-avatar">
      <?php if($p->foto): ?>
        <img src="<?php echo e(asset($p->foto)); ?>" style="width:100%;height:100%;object-fit:cover;">
      <?php else: ?>
        <?php echo e(mb_strtoupper(mb_substr($p->nombre,0,1))); ?>

      <?php endif; ?>
    </div>

    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <span style="font-weight:700;font-size:.9rem;"><?php echo e($p->nombre); ?></span>
        <?php if($p->favorito): ?> <span>⭐</span><?php endif; ?>
        <span class="tipo-badge <?php echo e($tipoClass); ?>"><?php echo e($tipoInfo['emoji']); ?> <?php echo e($tipoInfo['label']); ?></span>
      </div>
      <div style="font-size:.76rem;color:var(--text-secondary);margin-top:2px;">
        <?php if($p->cargo): ?> <?php echo e($p->cargo); ?> · <?php endif; ?>
        <?php if($p->telefono): ?> 📞 <?php echo e($p->telefono); ?><?php endif; ?>
      </div>
      <?php if($p->tipo === 'trabajador' && $p->valor_jornal): ?>
        <div style="font-size:.73rem;color:var(--text-muted);">
          💰 $<?php echo e(number_format($p->valor_jornal,0,',','.')); ?>/<?php echo e(['jornal'=>'día','mensual'=>'mes','destajo'=>'destajo'][$p->tipo_contrato??'jornal']??'día'); ?>

        </div>
      <?php endif; ?>
    </div>

    <div class="flex gap-1 items-center" onclick="event.stopPropagation()">
      <a href="<?php echo e(route('personas.show',$p->id)); ?>" class="btn btn-sm btn-secondary btn-icon">👁️</a>
      <button onclick="openModal('editPersona<?php echo e($p->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="<?php echo e(route('personas.destroy',$p->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>
</div>


<div class="modal-overlay" id="editPersona<?php echo e($p->id); ?>" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar persona</h3>
    <form method="POST" action="<?php echo e(route('personas.update',$p->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <input type="hidden" name="back" value="list">
      <div class="grid-2">
        <div class="form-group"><label>Tipo *</label>
          <select name="tipo" class="form-control" required>
            <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$ti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>" <?php echo e($p->tipo===$k?'selected':''); ?>><?php echo e($ti['emoji']); ?> <?php echo e($ti['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="<?php echo e($p->nombre); ?>"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" value="<?php echo e($p->telefono); ?>"></div>
        <div class="form-group"><label>Documento</label><input type="text" name="documento" class="form-control" value="<?php echo e($p->documento); ?>"></div>
      </div>
      <div class="form-group"><label>Cargo / rol</label><input type="text" name="cargo" class="form-control" value="<?php echo e($p->cargo); ?>" placeholder="Jornalero, Ordeñador..."></div>
      <div class="grid-2">
        <div class="form-group"><label>Tipo contrato</label>
          <select name="tipo_contrato" class="form-control">
            <option value="">N/A</option>
            <option <?php echo e(($p->tipo_contrato??'')==='jornal'?'selected':''); ?> value="jornal">Jornal/Día</option>
            <option <?php echo e(($p->tipo_contrato??'')==='mensual'?'selected':''); ?> value="mensual">Mensual</option>
            <option <?php echo e(($p->tipo_contrato??'')==='destajo'?'selected':''); ?> value="destajo">Destajo</option>
          </select>
        </div>
        <div class="form-group"><label>Valor jornal/día</label><input type="number" step="1000" name="valor_jornal" class="form-control" value="<?php echo e($p->valor_jornal); ?>"></div>
      </div>
      <div class="form-group"><label>Labores que realiza</label><input type="text" name="labores" class="form-control" value="<?php echo e($p->labores); ?>" placeholder="Siembra, fumigación, ordeño..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editPersona<?php echo e($p->id); ?>')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevaPersona" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">👥 Nueva persona</h3>
    <form method="POST" action="<?php echo e(route('personas.store')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required id="tipoNuevoP" onchange="toggleCamposLaborales()">
          <?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k=>$ti): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($k); ?>"><?php echo e($ti['emoji']); ?> <?php echo e($ti['label']); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Nombre completo *</label><input type="text" name="nombre" class="form-control" required placeholder="Nombre de la persona"></div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
        <div class="form-group"><label>Documento (cédula/NIT)</label><input type="text" name="documento" class="form-control" placeholder="Número"></div>
      </div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Opcional"></div>
      <div class="form-group"><label>Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Opcional"></div>

      
      <div id="camposLaborales" style="display:none;border-top:1px solid var(--border);padding-top:14px;margin-top:4px;">
        <p style="font-size:.78rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;margin-bottom:10px;">💼 Información laboral</p>
        <div class="form-group"><label>Cargo</label><input type="text" name="cargo" class="form-control" placeholder="Jornalero, Ordeñador, Administrador..."></div>
        <div class="grid-2">
          <div class="form-group"><label>Tipo de contrato</label>
            <select name="tipo_contrato" class="form-control">
              <option value="">Seleccionar</option>
              <option value="jornal">Jornal (por día)</option>
              <option value="mensual">Mensual fijo</option>
              <option value="destajo">Destajo (por tarea)</option>
              <option value="temporal">Temporal</option>
            </select>
          </div>
          <div class="form-group"><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso" class="form-control"></div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Valor jornal/día (COP)</label><input type="number" step="1000" name="valor_jornal" class="form-control" placeholder="0"></div>
          <div class="form-group"><label>Salario mensual (COP)</label><input type="number" step="10000" name="valor_mensual" class="form-control" placeholder="0"></div>
        </div>
        <div class="form-group"><label>Labores que realiza</label><input type="text" name="labores" class="form-control" placeholder="Siembra, fumigación, ordeño, corte..."></div>
      </div>

      <div class="form-group"><label>📷 Foto (opcional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevaPersona')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyudaPersonas" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">👥 ¿Cómo funciona Personas?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí registras <strong style="color:var(--verde-dark)">todas las personas</strong> que tienen relación con tu finca.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">👷</span><div><strong>Trabajadores</strong><br>Registra jornaleros y empleados con su tipo de contrato, jornal diario o salario mensual, y labores que desempeñan.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">💰</span><div><strong>Control de pagos</strong><br>En la ficha de cada trabajador puedes registrar cada pago, los días trabajados y asociarlo a un cultivo o animal. El pago se crea automáticamente como gasto.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">🔨</span><div><strong>Registro de labores</strong><br>Anota qué actividad realizó cada persona, en qué cultivo o animal, las horas y los insumos que usó.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">🏪🛒</span><div><strong>Proveedores y compradores</strong><br>Centraliza todos tus contactos comerciales aquí, integrado con los módulos de Gastos e Ingresos.</div></div>
        <div style="display:flex;gap:10px;"><span style="font-size:1.2rem;">⭐</span><div><strong>Favoritos</strong><br>Marca las personas más frecuentes con ⭐ para encontrarlas primero.</div></div>
      </div>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaPersonas')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevaPersona')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
function toggleCamposLaborales() {
  const tipo = document.getElementById('tipoNuevoP').value;
  document.getElementById('camposLaborales').style.display = tipo === 'trabajador' ? 'block' : 'none';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/personas.blade.php ENDPATH**/ ?>