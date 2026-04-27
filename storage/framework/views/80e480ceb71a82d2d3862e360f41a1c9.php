

<?php $__env->startSection('content'); ?>

<style>
    .enc-wrap {
        max-width: 680px;
        margin: 0 auto;
        padding: 1rem 1rem 5rem;
    }

    .enc-header {
        background: #e8f5ee;
        border-radius: 14px;
        padding: 1.1rem 1.3rem;
        margin-bottom: 1.25rem;
    }
    .enc-header h5 {
        color: #085041;
        font-weight: 700;
        margin: 0 0 3px;
        font-size: 16px;
    }
    .enc-header p {
        color: #2d8c6a;
        font-size: 13px;
        margin: 0;
    }

    .enc-seccion {
        background: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.3rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.06);
    }
    .enc-seccion-titulo {
        font-size: 13px;
        font-weight: 700;
        color: #1D9E75;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8f5ee;
        margin-bottom: 1.1rem;
    }

    .enc-pregunta {
        margin-bottom: 1.4rem;
    }
    .enc-pregunta:last-child {
        margin-bottom: 0;
    }
    .enc-pregunta-label {
        font-size: 14px;
        font-weight: 600;
        color: #1a3a1a;
        margin-bottom: 10px;
        line-height: 1.4;
        display: block;
    }
    .enc-pregunta-label .enc-badge {
        display: inline-block;
        background: #e8f5ee;
        color: #085041;
        font-size: 11px;
        font-weight: 700;
        padding: 1px 7px;
        border-radius: 99px;
        margin-right: 6px;
        vertical-align: middle;
    }

    .enc-opciones {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }
    .enc-opcion {
        display: flex;
        align-items: center;
        gap: 10px;
        background: #f8fbf9;
        border: 1.5px solid #e0ede7;
        border-radius: 10px;
        padding: 9px 13px;
        cursor: pointer;
        transition: border-color 0.15s, background 0.15s;
        font-size: 14px;
        color: #2a3a2a;
    }
    .enc-opcion:hover {
        border-color: #1D9E75;
        background: #f0faf5;
    }
    .enc-opcion input[type="radio"],
    .enc-opcion input[type="checkbox"] {
        accent-color: #1D9E75;
        width: 17px;
        height: 17px;
        flex-shrink: 0;
        cursor: pointer;
        margin: 0;
    }

    .enc-nps {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }
    .enc-nps-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .enc-nps-item input[type="radio"] {
        display: none;
    }
    .enc-nps-item label {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 10px;
        border: 1.5px solid #e0ede7;
        background: #f8fbf9;
        font-size: 14px;
        font-weight: 600;
        color: #2a3a2a;
        cursor: pointer;
        transition: all 0.15s;
        margin: 0;
    }
    .enc-nps-item label:hover {
        border-color: #1D9E75;
        background: #f0faf5;
        color: #085041;
    }
    .enc-nps-item input:checked + label {
        border-color: #1D9E75;
        background: #1D9E75;
        color: #fff;
    }

    .enc-estrellas {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .enc-estrella-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .enc-estrella-item input[type="radio"] {
        display: none;
    }
    .enc-estrella-item label {
        display: flex;
        align-items: center;
        gap: 5px;
        padding: 9px 16px;
        border-radius: 10px;
        border: 1.5px solid #e0ede7;
        background: #f8fbf9;
        font-size: 14px;
        font-weight: 600;
        color: #2a3a2a;
        cursor: pointer;
        transition: all 0.15s;
        margin: 0;
    }
    .enc-estrella-item label:hover {
        border-color: #f0a500;
        background: #fffbf0;
        color: #c07800;
    }
    .enc-estrella-item input:checked + label {
        border-color: #f0a500;
        background: #fff3d0;
        color: #8a5a00;
    }

    textarea.enc-textarea {
        width: 100%;
        border: 1.5px solid #e0ede7;
        border-radius: 10px;
        padding: 10px 13px;
        font-size: 14px;
        color: #2a3a2a;
        background: #f8fbf9;
        resize: vertical;
        outline: none;
        transition: border-color 0.15s;
        font-family: inherit;
        box-sizing: border-box;
    }
    textarea.enc-textarea:focus {
        border-color: #1D9E75;
        background: #fff;
    }
    textarea.enc-textarea::placeholder {
        color: #aabcaa;
    }

    .enc-submit {
        width: 100%;
        background: #1D9E75;
        color: #fff;
        border: none;
        border-radius: 12px;
        padding: 14px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        margin-top: 0.5rem;
        transition: background 0.15s;
    }
    .enc-submit:hover {
        background: #178a64;
    }

    .enc-divider {
        height: 1px;
        background: #e8f5ee;
        margin: 1.1rem 0;
    }

    .enc-nota {
        text-align: center;
        font-size: 12px;
        color: #aaa;
        margin-top: 10px;
    }

    .enc-hint {
        font-size: 12px;
        font-weight: 400;
        color: #888;
    }
</style>

<div class="enc-wrap">

    <?php if(session('error')): ?>
        <div style="background:#fff0f0; border:1px solid #f5c0c0; border-radius:10px; padding:10px 14px; margin-bottom:1rem; color:#a00; font-size:14px;">
            <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <div class="enc-header">
        <h5>¿Cómo te ha ayudado Agrogranja?</h5>
        <p>Encuesta de impacto · ~5 minutos · tus respuestas son anónimas</p>
    </div>

    <form method="POST" action="<?php echo e(route('encuesta.store')); ?>">
        <?php echo csrf_field(); ?>

        
        <div class="enc-seccion">
            <div class="enc-seccion-titulo">1. Perfil del productor</div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P1</span>
                    ¿Qué tipo de producción manejas principalmente?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Cultivos agrícolas', 'Ganadería / animales', 'Avicultura', 'Mixto (animales y cultivos)', 'Otro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p1" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P2</span>
                    ¿Cuántas hectáreas aproximadamente abarca tu finca?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Menos de 1 ha', '1–5 ha', '5–20 ha', 'Más de 20 ha']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p2" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P3</span>
                    Antes de usar Agrogranja, ¿cómo registrabas la información de tu finca?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Cuadernos / papel', 'Memoria (no registraba)', 'Excel / hojas de cálculo', 'Otra aplicación', 'No registraba nada']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p3" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P4</span>
                    ¿Cuánto tiempo llevas usando Agrogranja?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Menos de 1 mes', '1–3 meses', '3–6 meses', 'Más de 6 meses']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p4" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="enc-seccion">
            <div class="enc-seccion-titulo">2. Facilidad de uso y adopción</div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P5</span>
                    ¿Qué tan fácil fue aprender a usar Agrogranja sin necesitar ayuda?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Muy difícil', 'Difícil', 'Regular', 'Fácil', 'Muy fácil']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p5" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P6</span>
                    ¿Qué módulos usas con mayor frecuencia?
                    <span class="enc-hint">(puedes elegir varios)</span>
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Animales', 'Cultivos', 'Cosechas', 'Gastos', 'Ingresos', 'Inventario', 'Tareas', 'Personas / empleados', 'Reportes', 'Calendario']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="checkbox" name="p6[]" value="<?php echo e($op); ?>">
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P7</span>
                    ¿Desde qué dispositivo accedes más a Agrogranja?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['Celular', 'Computador', 'Tableta', 'Indistinto']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p7" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="enc-seccion">
            <div class="enc-seccion-titulo">3. Impacto en la gestión de la finca</div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P8</span>
                    ¿Sientes que tienes mejor control de los costos y gastos de tu finca desde que usas Agrogranja?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No, igual que antes', 'Un poco mejor', 'Bastante mejor', 'Mucho mejor']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p8" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P9</span>
                    ¿Ha mejorado tu capacidad para decidir qué cultivar, cuándo vender o qué comprar?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No ha cambiado', 'Algo ha mejorado', 'Ha mejorado bastante', 'Ha mejorado mucho']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p9" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P10</span>
                    ¿Agrogranja te ha ayudado a reducir pérdidas (animales sin tratar, cultivos descuidados, insumos vencidos)?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No', 'En algo sí', 'Sí, notablemente', 'No aplica a mi caso']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p10" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P11</span>
                    ¿Cuánto tiempo semanal crees que ahorras al tener la información de tu finca organizada en la app?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No ahorro tiempo', 'Menos de 1 hora', '1–3 horas', 'Más de 3 horas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p11" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P12</span>
                    ¿Has notado alguna mejora en tus ingresos o rentabilidad desde que empezaste a usar Agrogranja?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No he notado cambio', 'Creo que sí, aunque no puedo medirlo', 'Sí, he tenido mejoras claras', 'Es muy pronto para saberlo']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p12" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        
        <div class="enc-seccion">
            <div class="enc-seccion-titulo">4. Impacto en la vida del productor</div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P13</span>
                    ¿Sientes más tranquilidad o confianza al tener el control de tu finca mejor organizado?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No, igual que antes', 'Un poco más', 'Bastante más', 'Mucho más']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p13" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P14</span>
                    ¿Has compartido o recomendado Agrogranja a otros productores de tu comunidad?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No', 'Lo he mencionado pero no la han usado', 'Sí, la he recomendado activamente']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p14" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P15</span>
                    ¿Crees que una herramienta como Agrogranja puede hacer una diferencia real para los pequeños agricultores de tu región?
                </label>
                <div class="enc-opciones">
                    <?php $__currentLoopData = ['No lo creo', 'Tal vez', 'Sí, puede ayudar', 'Definitivamente sí']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="enc-opcion">
                        <input type="radio" name="p15" value="<?php echo e($op); ?>" required>
                        <?php echo e($op); ?>

                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P16</span>
                    En general, ¿cómo calificarías el impacto de Agrogranja en tu trabajo diario?
                </label>
                <div class="enc-estrellas">
                    <?php $__currentLoopData = [1 => '1 ★', 2 => '2 ★★', 3 => '3 ★★★', 4 => '4 ★★★★', 5 => '5 ★★★★★']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="enc-estrella-item">
                        <input type="radio" name="p16" value="<?php echo e($n); ?>" id="p16_<?php echo e($n); ?>" required>
                        <label for="p16_<?php echo e($n); ?>"><?php echo e($label); ?></label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <small style="color:#aaa; font-size:12px; margin-top:6px; display:block;">
                    1 = ningún impacto &nbsp;·&nbsp; 5 = impacto muy positivo
                </small>
            </div>
        </div>

        
        <div class="enc-seccion">
            <div class="enc-seccion-titulo">5. Sugerencias y mejoras</div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P17</span>
                    ¿Qué funcionalidad le agregarías a Agrogranja que te haría la vida más fácil?
                </label>
                <textarea name="p17" class="enc-textarea" rows="3"
                    placeholder="Escribe aquí tu sugerencia..."></textarea>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P18</span>
                    ¿Hay algo en la app que te resulte difícil, confuso o que no uses porque no lo entiendes?
                </label>
                <textarea name="p18" class="enc-textarea" rows="3"
                    placeholder="Cuéntanos qué parte te genera dificultad..."></textarea>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P19</span>
                    Del 1 al 10, ¿qué tan probable es que recomiendes Agrogranja a otro productor?
                </label>
                <div class="enc-nps">
                    <?php $__currentLoopData = range(1,10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $n): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="enc-nps-item">
                        <input type="radio" name="p19" value="<?php echo e($n); ?>" id="p19_<?php echo e($n); ?>" required>
                        <label for="p19_<?php echo e($n); ?>"><?php echo e($n); ?></label>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <small style="color:#aaa; font-size:12px; margin-top:6px; display:block;">
                    1 = muy poco probable &nbsp;·&nbsp; 10 = definitivamente sí
                </small>
            </div>

            <div class="enc-divider"></div>

            <div class="enc-pregunta">
                <label class="enc-pregunta-label">
                    <span class="enc-badge">P20</span>
                    ¿Algún comentario adicional sobre tu experiencia?
                    <span class="enc-hint">(opcional)</span>
                </label>
                <textarea name="p20" class="enc-textarea" rows="3"
                    placeholder="Cualquier comentario es bienvenido..."></textarea>
            </div>
        </div>

        <button type="submit" class="enc-submit">
            Enviar respuestas
        </button>

        <p class="enc-nota">
            Tus respuestas son anónimas y solo se usan con fines de investigación.
        </p>

    </form>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/encuesta.blade.php ENDPATH**/ ?>