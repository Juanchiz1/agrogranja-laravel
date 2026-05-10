
<?php $__env->startSection('title','Diagnóstico inicial'); ?>
<?php $__env->startSection('page_title','📋 Antes de continuar'); ?>

<?php $__env->startSection('content'); ?>
<style>
.diag-wrap {
    max-width: 560px;
    margin: 0 auto;
    padding: 0 0 6rem;
}
.diag-intro {
    background: var(--verde-bg);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.25rem;
    margin-bottom: 1.25rem;
    border-left: 3px solid var(--verde-dark);
}
.diag-intro h5 {
    color: var(--verde-dark);
    font-weight: 700;
    margin: 0 0 5px;
    font-size: 1rem;
}
.diag-intro p {
    color: var(--text-secondary);
    font-size: .85rem;
    margin: 0;
    line-height: 1.55;
}
.diag-seccion {
    background: var(--surface);
    border-radius: var(--radius-lg);
    padding: 1.1rem 1.2rem;
    margin-bottom: .9rem;
    box-shadow: var(--shadow-sm);
}
.diag-num {
    display: inline-block;
    font-size: .68rem;
    font-weight: 700;
    color: var(--verde-dark);
    background: var(--verde-bg);
    padding: 1px 7px;
    border-radius: 99px;
    margin-right: 6px;
    vertical-align: middle;
}
.diag-label {
    font-size: .9rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: .85rem;
    display: block;
    line-height: 1.4;
}
.diag-label em {
    font-style: italic;
    color: var(--verde-dark);
    font-weight: 700;
}
.diag-options {
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.diag-opt {
    display: flex;
    align-items: center;
    gap: 10px;
    background: var(--bg);
    border: 1.5px solid var(--border);
    border-radius: 10px;
    padding: 9px 12px;
    cursor: pointer;
    font-size: .88rem;
    color: var(--text-primary);
    transition: border-color .15s, background .15s;
    user-select: none;
}
.diag-opt:has(input:checked) {
    border-color: var(--verde-dark);
    background: var(--verde-bg);
    font-weight: 600;
}
.diag-opt input[type="radio"] {
    accent-color: var(--verde-dark);
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    cursor: pointer;
    margin: 0;
}
.diag-footer {
    margin-top: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.diag-omitir {
    text-align: center;
    font-size: .82rem;
    color: var(--text-muted);
    margin-top: 4px;
}
</style>

<div class="diag-wrap">

    <div class="diag-intro">
        <h5>🌱 Ayúdanos a conocerte mejor</h5>
        <p>Antes de continuar, responde 6 preguntas rápidas sobre tu situación <strong>antes</strong> de usar Agrogranja. Tus respuestas son anónimas y nos ayudan a mejorar el software para productores de San Pelayo y la región. Solo toma 2 minutos.</p>
    </div>

    <form method="POST" action="<?php echo e(route('diagnostico.store')); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">1</span>
                ¿Con qué dispositivo accedes principalmente a Agrogranja?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'Celular básico / antiguo',
                    'Smartphone moderno',
                    'Tablet',
                    'Computador o portátil',
                    'Varios dispositivos'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d1" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">2</span>
                ¿Cómo es tu acceso a internet <em>en la finca</em>?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'Bueno — siempre conectado',
                    'Intermitente — a veces falla',
                    'Solo en el casco urbano / pueblo',
                    'No tengo internet en la finca'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d2" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">3</span>
                <em>Antes</em> de Agrogranja, ¿qué tan cómodo te sentías usando apps o tecnología?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'No usaba tecnología',
                    'Solo llamadas y WhatsApp',
                    'Usaba algunas apps básicas',
                    'Me manejo bien con tecnología'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d3" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">4</span>
                ¿Cómo registrabas la información de tu finca <em>antes</em> de Agrogranja?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'En cuadernos o papel',
                    'Solo en la memoria',
                    'En Excel o hojas de cálculo',
                    'En otra aplicación',
                    'No registraba nada'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d4" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">5</span>
                Aproximadamente, ¿cuánto ganabas por mes con la finca <em>antes</em> de usar Agrogranja?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'Menos de $500.000',
                    'Entre $500.000 y $1.000.000',
                    'Entre $1.000.000 y $3.000.000',
                    'Más de $3.000.000',
                    'No lo tenía claro'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d5" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        
        <div class="diag-seccion">
            <span class="diag-label">
                <span class="diag-num">6</span>
                ¿Cuánto gastabas mensualmente en la finca (insumos, personal, etc.) <em>antes</em>?
            </span>
            <div class="diag-options">
                <?php $__currentLoopData = [
                    'Menos de $200.000',
                    'Entre $200.000 y $500.000',
                    'Entre $500.000 y $1.000.000',
                    'Más de $1.000.000',
                    'No lo tenía claro'
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <label class="diag-opt">
                    <input type="radio" name="d6" value="<?php echo e($op); ?>" required>
                    <?php echo e($op); ?>

                </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="diag-footer">
            <button type="submit" class="btn btn-primary btn-full">
                ✅ Enviar y continuar al inicio
            </button>
        </div>
    </form>

    <div class="diag-omitir">
        <form method="POST" action="<?php echo e(route('diagnostico.omitir')); ?>">
            <?php echo csrf_field(); ?>
            <button type="submit" class="btn btn-ghost" style="font-size:.82rem;color:var(--text-muted);">
                Omitir por ahora →
            </button>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/diagnostico.blade.php ENDPATH**/ ?>