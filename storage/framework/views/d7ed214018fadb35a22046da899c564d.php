
<?php $__env->startSection('title', 'Producción animal'); ?>
<?php $__env->startSection('page_title', 'Producción continua'); ?>
<?php $__env->startSection('back_url', isset($animalId) && $animalId ? route('animales.show', $animalId) : route('animales.index')); ?>

<?php $__env->startSection('content'); ?>


<?php if($resumenMes->count()): ?>
<div class="stats-grid" style="grid-template-columns:repeat(<?php echo e(min($resumenMes->count(), 3)); ?>,1fr);margin-bottom:14px;">
<?php $__currentLoopData = $resumenMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<div class="stat-card" style="background:var(--verde-bg);">
    <div class="stat-value text-green" style="font-size:1.1rem;">
        <?php echo e(number_format($r->total_qty, 1)); ?>

        <span style="font-size:.7rem;font-weight:400;"><?php echo e($r->tipo_produccion === 'leche' ? 'L' : ($r->tipo_produccion === 'huevos' ? 'und' : $r->tipo_produccion)); ?></span>
    </div>
    <div class="stat-label">
        <?php echo e(ucfirst($r->tipo_produccion)); ?> · <?php echo e($r->dias); ?> día(s)
        <?php if($r->total_valor): ?> · $<?php echo e(number_format($r->total_valor,0,',','.')); ?> <?php endif; ?>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>
<?php endif; ?>


<div class="card mb-3">
    <p class="font-bold mb-3">
        <?php if($animalId && $animales->where('id',$animalId)->first()): ?>
            🐄 Registrar producción de <strong><?php echo e($animales->where('id',$animalId)->first()->nombre_lote ?? $animales->where('id',$animalId)->first()->especie); ?></strong>
        <?php else: ?>
            Registrar producción
        <?php endif; ?>
    </p>

    <form method="POST" action="<?php echo e(route('produccion-animal.store')); ?>">
        <?php echo csrf_field(); ?>

        
        <?php if($animalId && $animales->where('id',$animalId)->first()): ?>
            <input type="hidden" name="animal_id" value="<?php echo e($animalId); ?>">
        <?php else: ?>
            <select name="animal_id" id="sel-animal" class="form-control mb-2" required onchange="actualizarTipos(this)">
                <option value="">Seleccionar animal</option>
                <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($a->id); ?>"
                        data-produccion="<?php echo e($a->produccion); ?>"
                        <?php echo e($animalId == $a->id ? 'selected' : ''); ?>>
                    <?php echo e($a->nombre_lote ?? $a->especie); ?> (<?php echo e($a->produccion); ?>)
                </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        <?php endif; ?>

        
        <div style="margin-bottom:12px;">
            <p class="text-xs text-gray font-bold" style="margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">¿Para qué período?</p>
            <div style="display:flex;gap:6px;flex-wrap:wrap;" id="chips-periodo">
                <button type="button" class="chip-periodo active" data-periodo="dia"
                        onclick="setPeriodo('dia',this)">
                    Día de hoy
                </button>
                <button type="button" class="chip-periodo" data-periodo="semana"
                        onclick="setPeriodo('semana',this)">
                    Esta semana
                </button>
                <button type="button" class="chip-periodo" data-periodo="mes"
                        onclick="setPeriodo('mes',this)">
                    Este mes
                </button>
            </div>
            <input type="hidden" name="periodo" id="campo-periodo" value="dia">
        </div>

        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <input type="date" name="fecha" id="campo-fecha" class="form-control"
                   value="<?php echo e(now()->toDateString()); ?>" required>
            <select name="tipo_produccion" id="sel-tipo" class="form-control" required onchange="actualizarUnidad(this)">
                <option value="leche">🥛 Leche</option>
                <option value="huevos">🥚 Huevos</option>
                <option value="lana">🐑 Lana</option>
                <option value="miel">🍯 Miel</option>
                <option value="otro">📦 Otro</option>
            </select>
        </div>

        
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:8px;margin-top:8px;">
            <input type="number" name="cantidad" id="campo-cantidad" class="form-control"
                   placeholder="Cantidad" step="0.1" min="0" required>
            <select name="unidad" id="sel-unidad" class="form-control">
                <option value="litros">Litros</option>
                <option value="unidades">Unidades</option>
                <option value="docenas">Docenas</option>
                <option value="kg">kg</option>
            </select>
        </div>

        
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:8px;">
            <input type="number" name="precio_unitario" class="form-control"
                   placeholder="Precio unitario (opcional)" step="100">
            <input type="text" name="comprador" class="form-control"
                   placeholder="Comprador (opcional)">
        </div>

        
        <label style="display:flex;align-items:center;gap:8px;margin-top:10px;font-size:.88rem;cursor:pointer;">
            <input type="checkbox" name="vendido" id="chk-vendido" value="1">
            Marcar como vendido — crea ingreso automáticamente
        </label>

        
        <input type="text" name="notas" id="campo-notas" class="form-control mt-2"
               placeholder="Notas (opcional)">

        <button type="submit" class="btn btn-primary w-full mt-3">
            Registrar producción
        </button>
    </form>
</div>


<div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
    <form method="GET" style="flex:1;min-width:120px;">
        <?php if($animalId): ?><input type="hidden" name="animal_id" value="<?php echo e($animalId); ?>"><?php endif; ?>
        <input type="month" name="mes" class="form-control"
               value="<?php echo e($mes); ?>" onchange="this.form.submit()">
    </form>
    <?php if(!$animalId): ?>
    <form method="GET" style="flex:1;min-width:140px;">
        <input type="hidden" name="mes" value="<?php echo e($mes); ?>">
        <select name="animal_id" class="form-control" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php $__currentLoopData = $animales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option value="<?php echo e($a->id); ?>" <?php echo e($animalId == $a->id ? 'selected' : ''); ?>>
                <?php echo e($a->nombre_lote ?? $a->especie); ?>

            </option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
    </form>
    <?php endif; ?>
</div>


<?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
<div class="list-item">
    <div class="item-icon" style="background:var(--verde-bg);font-size:1.3rem;">
        <?php if($r->tipo_produccion==='leche'): ?> 🥛
        <?php elseif($r->tipo_produccion==='huevos'): ?> 🥚
        <?php elseif($r->tipo_produccion==='lana'): ?> 🐑
        <?php elseif($r->tipo_produccion==='miel'): ?> 🍯
        <?php else: ?> 📦 <?php endif; ?>
    </div>
    <div class="item-body">
        <div class="item-title">
            <strong><?php echo e(number_format($r->cantidad, 1)); ?> <?php echo e($r->unidad); ?></strong>
            de <?php echo e($r->tipo_produccion); ?>

            <?php if(!$animalId): ?> — <?php echo e($r->nombre_lote ?? $r->especie); ?> <?php endif; ?>
        </div>
        <div class="item-sub">
    <?php echo e(\Carbon\Carbon::parse($r->fecha)->format('d/m/Y')); ?>

    <?php if($r->vendido): ?>
        · <span style="color:var(--verde-dark);font-weight:600;">
            ✓ Vendido<?php echo e($r->comprador ? ' a ' . $r->comprador : ''); ?>

          </span>
    <?php endif; ?>
    <?php if($r->notas): ?> · <?php echo e($r->notas); ?> <?php endif; ?>
</div>
    </div>
    <div style="text-align:right;flex-shrink:0;">
        <?php if($r->valor_total): ?>
        <div class="font-bold text-green text-sm">$<?php echo e(number_format($r->valor_total,0,',','.')); ?></div>
        <?php endif; ?>
        <?php if($r->ingreso_creado): ?>
        <div style="font-size:10px;color:var(--verde-dark);">✓ Ingreso</div>
        <?php endif; ?>
    </div>
    <form method="POST" action="<?php echo e(route('produccion-animal.destroy',$r->id)); ?>"
          style="margin-left:6px;" onsubmit="return confirm('¿Eliminar?')">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
        <button type="submit" class="btn btn-sm btn-danger-ghost" style="padding:4px 8px;">✕</button>
    </form>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
<div class="empty-state">
    <div class="emoji">🥛</div>
    <p>Sin registros de producción para este período.</p>
    <?php if($animalId): ?>
    <p class="text-xs text-gray mt-2">Registra la producción diaria, semanal o mensual arriba.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<style>
.chip-periodo {
    padding: 6px 14px;
    border-radius: 99px;
    border: 1.5px solid var(--border);
    background: var(--surface);
    font-size: .83rem;
    font-weight: 500;
    cursor: pointer;
    color: var(--text-secondary);
    transition: all .15s;
}
.chip-periodo.active {
    background: var(--verde-bg);
    border-color: var(--verde-dark);
    color: var(--verde-dark);
    font-weight: 700;
}
</style>

<?php $__env->startPush('scripts'); ?>
<script>
// Meses en español para el texto de notas
const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

function setPeriodo(periodo, btn) {
    // Actualizar chips
    document.querySelectorAll('.chip-periodo').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('campo-periodo').value = periodo;

    const hoy   = new Date();
    const yyyy  = hoy.getFullYear();
    const mm    = String(hoy.getMonth()+1).padStart(2,'0');
    const dd    = String(hoy.getDate()).padStart(2,'0');
    const notas = document.getElementById('campo-notas');

    if (periodo === 'dia') {
        document.getElementById('campo-fecha').value = `${yyyy}-${mm}-${dd}`;
        if (notas.value.startsWith('Producción semana') || notas.value.startsWith('Producción mes')) {
            notas.value = '';
        }
    } else if (periodo === 'semana') {
        // Lunes de esta semana
        const lunes = new Date(hoy);
        lunes.setDate(hoy.getDate() - (hoy.getDay() === 0 ? 6 : hoy.getDay()-1));
        const dl = String(lunes.getDate()).padStart(2,'0');
        const ml = String(lunes.getMonth()+1).padStart(2,'0');
        document.getElementById('campo-fecha').value = `${yyyy}-${mm}-${dd}`;
        notas.placeholder = '';
        notas.value = `Producción semana del ${dl}/${ml} al ${dd}/${mm}`;
    } else if (periodo === 'mes') {
        document.getElementById('campo-fecha').value = `${yyyy}-${mm}-${dd}`;
        notas.value = `Producción mes de ${meses[hoy.getMonth()]} ${yyyy}`;
    }
}

// Ajustar unidad según tipo de producción
function actualizarUnidad(sel) {
    const unidad = document.getElementById('sel-unidad');
    const mapa = {
        'leche':  ['litros','ml'],
        'huevos': ['unidades','docenas'],
        'lana':   ['kg','lb'],
        'miel':   ['kg','litros'],
        'otro':   ['unidades','kg','litros'],
    };
    const opciones = mapa[sel.value] || ['unidades'];
    unidad.innerHTML = opciones.map(u => `<option value="${u}">${u.charAt(0).toUpperCase()+u.slice(1)}</option>`).join('');
}

// Pre-seleccionar tipo según producción del animal
function actualizarTipos(sel) {
    const prod = sel.selectedOptions[0]?.dataset.produccion?.toLowerCase() || '';
    const tipo = document.getElementById('sel-tipo');
    if (prod.includes('leche'))  tipo.value = 'leche';
    else if (prod.includes('huevo')) tipo.value = 'huevos';
    else if (prod.includes('lana'))  tipo.value = 'lana';
    else if (prod.includes('miel'))  tipo.value = 'miel';
    actualizarUnidad(tipo);
}

// Inicializar si viene con animal pre-seleccionado
<?php if($animalId): ?>
const animal = document.querySelector('[data-produccion]');
const prodRaw = '<?php echo e(optional($animales->where("id",$animalId)->first())->produccion ?? ""); ?>'.toLowerCase();
const tipo = document.getElementById('sel-tipo');
if (prodRaw.includes('leche'))       tipo.value = 'leche';
else if (prodRaw.includes('huevo'))  tipo.value = 'huevos';
else if (prodRaw.includes('lana'))   tipo.value = 'lana';
else if (prodRaw.includes('miel'))   tipo.value = 'miel';
actualizarUnidad(tipo);
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/produccion-animal.blade.php ENDPATH**/ ?>