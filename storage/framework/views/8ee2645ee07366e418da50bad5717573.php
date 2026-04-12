<?php $__env->startSection('title','Animales'); ?>
<?php $__env->startSection('page_title','🐾 Mis Animales'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<style>
.especie-group { margin-bottom:20px; }
.especie-header { display:flex; align-items:center; gap:8px; padding:8px 12px;
  background:var(--verde-bg); border-radius:var(--radius-md);
  font-weight:700; font-size:.9rem; color:var(--verde-dark); margin-bottom:8px; }
.especie-count { background:var(--verde-dark); color:#fff; border-radius:99px;
  padding:1px 8px; font-size:.75rem; }
.animal-flags { display:flex; gap:4px; }
.flag-btn { background:none; border:none; cursor:pointer; font-size:1rem; padding:2px; line-height:1; }
.alert-atencion { background:#fef9c3; border-left:4px solid #f59e0b;
  border-radius:var(--radius-md); padding:10px 14px; margin-bottom:14px; }
.proxima-dosis-card { background:var(--surface); border-radius:var(--radius-md);
  padding:8px 12px; margin-bottom:6px; border-left:3px solid #6366f1;
  display:flex; justify-content:space-between; align-items:center; font-size:.83rem; }
.tabs-esp { display:flex; gap:6px; overflow-x:auto; margin-bottom:16px; padding-bottom:4px; }
.tab-esp { flex-shrink:0; padding:6px 12px; border-radius:99px;
  border:1.5px solid var(--border); background:var(--surface);
  font-size:.8rem; font-weight:600; cursor:pointer; color:var(--text-secondary); }
.tab-esp.active { background:var(--verde-dark); color:#fff; border-color:var(--verde-dark); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>


<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaAnimales')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>


<div class="stats-grid mb-3" style="grid-template-columns:repeat(auto-fill,minmax(80px,1fr));gap:8px;">
  <div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1.3rem;"><?php echo e($totalActivos); ?></div>
    <div class="stat-label">Total activos</div>
  </div>
  <?php $__currentLoopData = $statsPorEspecie->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $em = $emojis[$st->especie] ?? '🐾'; ?>
  <div class="stat-card" style="background:var(--verde-bg);">
    <div style="font-size:1.2rem;"><?php echo e($em); ?></div>
    <div class="stat-value text-green" style="font-size:1.1rem;"><?php echo e($st->total); ?></div>
    <div class="stat-label" style="font-size:.65rem;"><?php echo e(Str::limit($st->especie,8)); ?></div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>


<?php if($atencion > 0): ?>
<div class="alert-atencion">
  <span style="font-weight:700;color:#92400e;">🚨 <?php echo e($atencion); ?> animal(es) necesitan atención especial</span>
  <div style="font-size:.8rem;color:#78350f;margin-top:2px;">Búscalos por el ícono 🚨 en la lista.</div>
</div>
<?php endif; ?>


<?php if($proximasDosis->count()): ?>
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:14px;box-shadow:var(--shadow-sm);">
  <p style="font-size:.8rem;font-weight:700;color:#4f46e5;margin-bottom:8px;">💊 Próximas dosis (7 días)</p>
  <?php $__currentLoopData = $proximasDosis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <div class="proxima-dosis-card">
    <span><?php echo e($emojis[$pd->especie]??'🐾'); ?> <?php echo e($pd->nombre_lote??$pd->especie); ?> · <?php echo e($pd->titulo); ?></span>
    <span style="font-weight:700;color:#4f46e5;"><?php echo e(\Carbon\Carbon::parse($pd->proxima_dosis)->format('d/m')); ?></span>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<form method="GET" class="flex gap-2 mb-3" style="flex-wrap:wrap;">
  <div class="search-box" style="flex:1;min-width:100px;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar lote..." value="<?php echo e(request('q')); ?>" style="padding-left:34px;">
  </div>
  <select name="especie" class="form-control" style="width:130px;" onchange="this.form.submit()">
    <option value="">Especie</option>
    <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e(request('especie')===$e?'selected':''); ?> value="<?php echo e($e); ?>"><?php echo e($emojis[$e]??'🐾'); ?> <?php echo e($e); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <?php if($ubicaciones->count()): ?>
  <select name="ubicacion" class="form-control" style="width:120px;" onchange="this.form.submit()">
    <option value="">Ubicación</option>
    <?php $__currentLoopData = $ubicaciones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ub): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e(request('ubicacion')===$ub?'selected':''); ?>><?php echo e($ub); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </select>
  <?php endif; ?>
  <select name="estado" class="form-control" style="width:110px;" onchange="this.form.submit()">
    <option value="">Estado</option>
    <option <?php echo e(request('estado')==='activo'?'selected':''); ?> value="activo">Activos</option>
    <option <?php echo e(request('estado')==='vendido'?'selected':''); ?> value="vendido">Vendidos</option>
    <option <?php echo e(request('estado')==='muerte'?'selected':''); ?> value="muerte">Bajas</option>
  </select>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>


<?php if($animales->isEmpty()): ?>
<div class="empty-state"><div class="emoji">🐾</div><p>No hay animales registrados.</p></div>
<?php else: ?>
<?php $agrupados = $animales->groupBy('especie'); ?>
<?php $__currentLoopData = $agrupados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $especie => $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="especie-group">
  <div class="especie-header">
    <span><?php echo e($emojis[$especie] ?? '🐾'); ?></span>
    <span><?php echo e($especie); ?></span>
    <span class="especie-count"><?php echo e($grupo->count()); ?> lote(s) · <?php echo e($grupo->sum('cantidad')); ?> animales</span>
  </div>

  <?php $__currentLoopData = $grupo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php
    $b = ['activo'=>'badge-green','vendido'=>'badge-brown','muerte'=>'badge-red'][$a->estado] ?? 'badge-green';
    $etapaEmoji = ['cria'=>'🐣','juvenil'=>'🐥','adulto'=>'✅'][$a->etapa_vida??'adulto'] ?? '✅';
  ?>
  <div class="list-item" style="cursor:pointer;border-left:<?php echo e($a->atencion_especial?'3px solid #f59e0b':($a->favorito?'3px solid #fbbf24':'none')); ?>;" onclick="window.location='<?php echo e(route('animales.show',$a->id)); ?>'">
    
    <div class="item-icon" style="background:var(--verde-bg);position:relative;">
      <?php if($a->foto): ?>
        <img src="<?php echo e(asset($a->foto)); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">
      <?php else: ?>
        <span style="font-size:1.4rem;"><?php echo e($emojis[$a->especie]??'🐾'); ?></span>
      <?php endif; ?>
    </div>

    <div class="item-body">
      <div class="item-title" style="display:flex;align-items:center;gap:5px;">
        <?php echo e($a->nombre_lote ?: $a->especie); ?>

        <?php if($a->favorito): ?> <span title="Favorito">⭐</span><?php endif; ?>
        <?php if($a->atencion_especial): ?> <span title="<?php echo e($a->atencion_motivo); ?>">🚨</span><?php endif; ?>
        <span title="<?php echo e(ucfirst($a->etapa_vida??'adulto')); ?>"><?php echo e($etapaEmoji); ?></span>
      </div>
      <div class="item-sub">
        <?php echo e($a->cantidad); ?> animal(es)
        <?php if($a->peso_promedio): ?> · ~<?php echo e($a->peso_promedio); ?><?php echo e($a->unidad_peso); ?><?php endif; ?>
        <?php if($a->ubicacion): ?> · 📍<?php echo e($a->ubicacion); ?><?php endif; ?>
        <?php if($a->propietario): ?> · 👤<?php echo e(Str::limit($a->propietario,12)); ?><?php endif; ?>
        <?php if($a->produccion): ?> · 🥛<?php echo e($a->produccion); ?><?php endif; ?>
      </div>
    </div>

    <div class="flex gap-2 items-center" onclick="event.stopPropagation()">
      <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;">
        <span class="badge <?php echo e($b); ?>"><?php echo e($a->estado); ?></span>
        <?php if($a->fecha_nacimiento): ?>
          <span style="font-size:.68rem;color:var(--text-muted);">🎂 <?php echo e(\Carbon\Carbon::parse($a->fecha_nacimiento)->diffForHumans(null,true)); ?></span>
        <?php endif; ?>
      </div>
      <a href="<?php echo e(route('animales.show',$a->id)); ?>" class="btn btn-sm btn-secondary btn-icon" title="Ver detalle">👁️</a>
      <button onclick="openModal('editAnimal<?php echo e($a->id); ?>')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="<?php echo e(route('animales.destroy',$a->id)); ?>" onsubmit="return confirm('¿Eliminar?')"><?php echo csrf_field(); ?>
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>

  
  <div class="modal-overlay" id="editAnimal<?php echo e($a->id); ?>" style="display:none;">
    <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar animal</h3>
      <form method="POST" action="<?php echo e(route('animales.update',$a->id)); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
        <input type="hidden" name="back" value="list">
        <div class="form-group"><label>Especie *</label>
          <select name="especie" class="form-control" required>
            <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option <?php echo e($a->especie===$e?'selected':''); ?>><?php echo e($e); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </select>
        </div>
        <div class="form-group"><label>Nombre del lote</label><input type="text" name="nombre_lote" class="form-control" value="<?php echo e($a->nombre_lote); ?>"></div>
        <div class="grid-2">
          <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="<?php echo e($a->cantidad); ?>" min="1"></div>
          <div class="form-group"><label>Estado</label>
            <select name="estado" class="form-control">
              <option <?php echo e($a->estado==='activo'?'selected':''); ?> value="activo">Activo</option>
              <option <?php echo e($a->estado==='vendido'?'selected':''); ?> value="vendido">Vendido</option>
              <option <?php echo e($a->estado==='muerte'?'selected':''); ?> value="muerte">Baja</option>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" value="<?php echo e($a->peso_promedio); ?>"></div>
          <div class="form-group"><label>Unidad</label>
            <select name="unidad_peso" class="form-control">
              <option <?php echo e(($a->unidad_peso??'kg')==='kg'?'selected':''); ?> value="kg">kg</option>
              <option <?php echo e(($a->unidad_peso??'kg')==='lb'?'selected':''); ?> value="lb">lb</option>
            </select>
          </div>
        </div>
        <div class="grid-2">
          <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" value="<?php echo e($a->ubicacion); ?>" placeholder="Corral, potrero..."></div>
          <div class="form-group"><label>Etapa de vida</label>
            <select name="etapa_vida" class="form-control">
              <option <?php echo e(($a->etapa_vida??'adulto')==='cria'?'selected':''); ?> value="cria">🐣 Cría</option>
              <option <?php echo e(($a->etapa_vida??'adulto')==='juvenil'?'selected':''); ?> value="juvenil">🐥 Juvenil</option>
              <option <?php echo e(($a->etapa_vida??'adulto')==='adulto'?'selected':''); ?> value="adulto">✅ Adulto</option>
            </select>
          </div>
        </div>
        <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" value="<?php echo e($a->propietario); ?>" placeholder="Dueño del animal"></div>
        <div class="form-group"><label>Produce</label><input type="text" name="produccion" class="form-control" value="<?php echo e($a->produccion); ?>" placeholder="Leche, Huevos, Cría..."></div>
        <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2"><?php echo e($a->notas); ?></textarea></div>
        <div class="flex gap-2 mt-2">
          <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editAnimal<?php echo e($a->id); ?>')">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php endif; ?>


<div class="modal-overlay" id="modalNuevoAnimal" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">🐾 Nuevo animal</h3>
    <form method="POST" action="<?php echo e(route('animales.store')); ?>" enctype="multipart/form-data"><?php echo csrf_field(); ?>
      <div class="form-group"><label>Especie *</label>
        <select name="especie" class="form-control" required id="especieNuevo" onchange="updateVentaMode()">
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = $especies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option><?php echo e($e); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
      <div class="form-group"><label>Nombre del lote / identificación</label><input type="text" name="nombre_lote" class="form-control" placeholder="Ej: Lote bovino norte, Flor, Gallinero 1"></div>
      <div class="grid-2">
        <div class="form-group"><label>Cantidad</label><input type="number" name="cantidad" class="form-control" value="1" min="1"></div>
        <div class="form-group"><label>Etapa de vida</label>
          <select name="etapa_vida" class="form-control">
            <option value="cria">🐣 Cría</option>
            <option value="juvenil">🐥 Juvenil</option>
            <option value="adulto" selected>✅ Adulto</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Peso promedio</label><input type="number" step="0.1" name="peso_promedio" class="form-control" placeholder="0"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad_peso" class="form-control">
            <option value="kg">kg</option><option value="lb">lb</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha ingreso</label><input type="date" name="fecha_ingreso" class="form-control" value="<?php echo e(date('Y-m-d')); ?>"></div>
        <div class="form-group"><label>Fecha nacimiento</label><input type="date" name="fecha_nacimiento" class="form-control"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control" placeholder="Corral, potrero..."></div>
        <div class="form-group"><label>Propietario</label><input type="text" name="propietario" class="form-control" placeholder="Nombre del dueño"></div>
      </div>
      <div class="form-group"><label>¿Qué produce?</label><input type="text" name="produccion" class="form-control" placeholder="Leche, Huevos, Lana, Cría..."></div>
      <div id="precioKiloWrap" class="form-group" style="display:none;">
        <label>Precio de venta por kg (COP)</label>
        <input type="number" step="100" name="precio_kilo" class="form-control" placeholder="Ej: 8000">
        <input type="hidden" name="vende_por_kilo" value="1">
      </div>
      <div id="precioUniWrap" class="form-group" style="display:none;">
        <label>Precio de venta por cabeza (COP)</label>
        <input type="number" step="100" name="precio_unidad" class="form-control" placeholder="Ej: 25000">
      </div>
      <div class="form-group"><label>📷 Foto del animal (opcional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalNuevoAnimal')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal-overlay" id="modalAyudaAnimales" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div>
    <h3 class="modal-title">🐾 ¿Cómo funciona Animales?</h3>
    <div style="line-height:1.7;color:var(--text-secondary);font-size:.9rem;">
      <p style="margin-bottom:12px;">Aquí llevas el control completo de <strong style="color:var(--verde-dark)">todos los animales de tu finca</strong>, organizados por especie.</p>
      <div style="display:flex;flex-direction:column;gap:10px;">
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">📋</span><div><strong>Por especie y ubicación</strong><br>Los animales se agrupan por especie. Puedes asignarles un corral, potrero o estanque.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">⭐🚨</span><div><strong>Favoritos y atención</strong><br>Marca animales favoritos con ⭐ o señala los que necesitan cuidado especial con 🚨.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">⚖️</span><div><strong>Historial de peso</strong><br>Registra pesajes periódicos para ver la evolución de crecimiento.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">💊</span><div><strong>Medicamentos y eventos</strong><br>Registra vacunas, medicamentos, traslados, partos, marcados. El sistema te avisa cuando se acerca una próxima dosis.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">👥</span><div><strong>Propietarios múltiples</strong><br>Un animal puede tener varios dueños con porcentaje de participación.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">💰</span><div><strong>Venta automática</strong><br>Al registrar la salida, el sistema calcula el valor según precio/kg o precio por cabeza y crea el ingreso automáticamente.</div></div>
        <div style="display:flex;gap:10px;align-items:flex-start;"><span style="font-size:1.2rem;">🥛</span><div><strong>Producción</strong><br>Indica qué produce el animal: leche, huevos, lana, crías, etc.</div></div>
      </div>
      <p style="margin-top:14px;padding:10px;background:var(--verde-bg);border-radius:8px;color:var(--verde-dark);font-size:.85rem;">
        💡 <strong>Tip:</strong> Toca cualquier animal para abrir su ficha completa con galería, historial y línea de tiempo.
      </p>
    </div>
    <button class="btn btn-primary btn-full mt-2" onclick="closeModal('modalAyudaAnimales')">¡Entendido!</button>
  </div>
</div>

<button class="fab" onclick="openModal('modalNuevoAnimal')">+</button>

<?php $__env->startPush('scripts'); ?>
<script>
const porKilo = ['Ganado bovino','Terneros','Cerdos','Cerdas de cría','Cabras','Ovejas','Caballos'];
function updateVentaMode() {
  const esp = document.getElementById('especieNuevo').value;
  document.getElementById('precioKiloWrap').style.display = porKilo.includes(esp) ? 'block' : 'none';
  document.getElementById('precioUniWrap').style.display  = (!porKilo.includes(esp) && esp) ? 'block' : 'none';
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/animales.blade.php ENDPATH**/ ?>