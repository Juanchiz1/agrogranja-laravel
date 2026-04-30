<?php $__env->startSection('title','Configuración inicial'); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/lineas-productivas.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php
  // Etiquetas y campos de configuración rápida por línea (paso 4).
  // Se generan en PHP para que la vista quede limpia.
  $configCampos = [
    'cultivos' => [
      'cantidad_label'  => 'Hectáreas dedicadas a cultivos',
      'cantidad_ph'     => 'Ej: 2',
      'extras' => [
        ['name'=>'tipos','label'=>'¿Qué cultivas principalmente?','ph'=>'Maíz, yuca, plátano, hortalizas...'],
      ],
      'opciones' => [],
    ],
    'bovino' => [
      'cantidad_label'  => 'Cantidad aproximada de cabezas',
      'cantidad_ph'     => 'Ej: 25',
      'opciones' => [
        ['name'=>'leche',  'label'=>'Lechería'],
        ['name'=>'carne',  'label'=>'Engorde / carne'],
        ['name'=>'cria',   'label'=>'Cría / reproducción'],
      ],
    ],
    'porcino' => [
      'cantidad_label'  => 'Cantidad aproximada de cerdos',
      'cantidad_ph'     => 'Ej: 30',
      'opciones' => [
        ['name'=>'engorde','label'=>'Engorde / ceba'],
        ['name'=>'cria',   'label'=>'Cría (hembras de cría)'],
      ],
    ],
    'avicola' => [
      'cantidad_label'  => 'Cantidad aproximada de aves',
      'cantidad_ph'     => 'Ej: 200',
      'opciones' => [
        ['name'=>'postura','label'=>'Postura (huevos)'],
        ['name'=>'engorde','label'=>'Engorde (pollos)'],
      ],
    ],
    'piscicola' => [
      'cantidad_label'  => 'Cantidad de estanques',
      'cantidad_ph'     => 'Ej: 3',
      'extras' => [
        ['name'=>'especies','label'=>'Especies que cultivas','ph'=>'Tilapia, cachama, bocachico...'],
      ],
      'opciones' => [],
    ],
    'caprino_ovino' => [
      'cantidad_label'  => 'Cantidad aproximada',
      'cantidad_ph'     => 'Ej: 15',
      'opciones' => [
        ['name'=>'leche','label'=>'Leche'],
        ['name'=>'carne','label'=>'Carne'],
        ['name'=>'lana', 'label'=>'Lana'],
      ],
    ],
    'apicola' => [
      'cantidad_label'  => 'Cantidad de colmenas',
      'cantidad_ph'     => 'Ej: 10',
      'opciones' => [],
    ],
    'equino' => [
      'cantidad_label'  => 'Cantidad de animales',
      'cantidad_ph'     => 'Ej: 4',
      'opciones' => [
        ['name'=>'trabajo','label'=>'Trabajo / arriería'],
        ['name'=>'cria',   'label'=>'Cría'],
      ],
    ],
    'cunicola' => [
      'cantidad_label'  => 'Cantidad de jaulas / madres',
      'cantidad_ph'     => 'Ej: 12',
      'opciones' => [],
    ],
  ];
?>


<form id="onboardForm" method="POST" action="<?php echo e(route('onboarding.complete')); ?>" class="wizard-form">
  <?php echo csrf_field(); ?>

  
  <div class="wizard-step" data-step="0">
    <div class="onboard-emoji">🌾</div>
    <h2 class="onboard-title">¡Bienvenido a<br>Agrogranja!</h2>
    <p class="onboard-desc">
      Vamos a configurar tu finca en 4 pasos. La aplicación se adaptará a lo que tú produces.
    </p>
    <div class="wizard-dots">
      <div class="wizard-dot active"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
    </div>
    <button type="button" class="btn btn-primary wizard-btn-next" onclick="wizardNext()">Empecemos →</button>
  </div>

  
  <div class="wizard-step hidden" data-step="1">
    <div class="onboard-emoji">🏡</div>
    <h2 class="onboard-title">Cuéntanos de tu finca</h2>
    <p class="onboard-desc">Esto nos ayuda a personalizar reportes y recomendaciones.</p>

    <div class="wizard-fields">
      <div class="form-group">
        <label>Nombre de la finca</label>
        <input type="text" name="nombre_finca" class="form-control"
               placeholder="Ej: Finca La Esperanza"
               value="<?php echo e($user->nombre_finca ?? ''); ?>">
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label>Hectáreas (opcional)</label>
          <input type="number" step="0.1" min="0" name="hectareas_total" class="form-control"
                 placeholder="0.0"
                 value="<?php echo e($user->hectareas_total ?? ''); ?>">
        </div>
        <div class="form-group">
          <label>Departamento</label>
          <input type="text" name="departamento" class="form-control"
                 placeholder="Ej: Antioquia"
                 value="<?php echo e($user->departamento ?? ''); ?>">
        </div>
      </div>

      <div class="form-group">
        <label>Municipio</label>
        <input type="text" name="municipio" class="form-control"
               placeholder="Ej: Medellín"
               value="<?php echo e($user->municipio ?? ''); ?>">
      </div>

      <div class="form-group">
        <label>Descripción (opcional)</label>
        <textarea name="descripcion_finca" class="form-control" rows="2"
                  placeholder="Cualquier nota: clima, historia, condiciones especiales..."><?php echo e($user->descripcion_finca ?? ''); ?></textarea>
      </div>
    </div>

    <div class="wizard-dots">
      <div class="wizard-dot"></div>
      <div class="wizard-dot active"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
    </div>
    <div class="wizard-actions">
      <button type="button" class="btn btn-ghost wizard-btn-back" onclick="wizardBack()">← Atrás</button>
      <button type="button" class="btn btn-primary wizard-btn-next" onclick="wizardNext()">Siguiente →</button>
    </div>
  </div>

  
  <div class="wizard-step hidden" data-step="2">
    <div class="onboard-emoji">🚜</div>
    <h2 class="onboard-title">¿Qué manejas en tu finca?</h2>
    <p class="onboard-desc">Selecciona todo lo que aplique. Puedes cambiarlo después en tu perfil.</p>

    <div id="lineasError" class="alert alert-error hidden" style="margin-bottom:12px;">
      Selecciona al menos una línea productiva.
    </div>

    <div class="lineas-grid">
      <?php $__currentLoopData = $lineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $marcada = isset($lineasUsuario[$linea->codigo]);
        ?>
        <label class="linea-card <?php echo e($marcada ? 'selected' : ''); ?>" data-codigo="<?php echo e($linea->codigo); ?>">
          <input type="checkbox" name="lineas[]" value="<?php echo e($linea->codigo); ?>"
                 class="linea-check"
                 onchange="toggleLineaCard(this)"
                 <?php echo e($marcada ? 'checked' : ''); ?>>
          <div class="linea-emoji"><?php echo e($linea->emoji); ?></div>
          <div class="linea-nombre"><?php echo e($linea->nombre); ?></div>
          <div class="linea-desc"><?php echo e($linea->descripcion); ?></div>
        </label>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="wizard-dots">
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot active"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
    </div>
    <div class="wizard-actions">
      <button type="button" class="btn btn-ghost wizard-btn-back" onclick="wizardBack()">← Atrás</button>
      <button type="button" class="btn btn-primary wizard-btn-next" onclick="wizardNextFromLineas()">Siguiente →</button>
    </div>
  </div>

  
  <div class="wizard-step hidden" data-step="3">
    <div class="onboard-emoji">⚙️</div>
    <h2 class="onboard-title">Configura cada actividad</h2>
    <p class="onboard-desc">Datos rápidos. Si no estás seguro, deja vacío y los completas después.</p>

    <div id="configContainer">
      <?php $__currentLoopData = $lineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $codigo  = $linea->codigo;
          $cfg     = $configCampos[$codigo] ?? null;
          $actual  = $lineasUsuario[$codigo] ?? null;
          $meta    = $actual && $actual->metadata ? json_decode($actual->metadata, true) : [];
        ?>

        <?php if($cfg): ?>
        <div class="config-block hidden" data-config-for="<?php echo e($codigo); ?>">
          <div class="config-block-header">
            <span class="config-emoji"><?php echo e($linea->emoji); ?></span>
            <span class="config-titulo"><?php echo e($linea->nombre); ?></span>
          </div>

          <div class="form-group">
            <label><?php echo e($cfg['cantidad_label']); ?></label>
            <input type="number" min="0" step="1"
                   name="config[<?php echo e($codigo); ?>][cantidad]"
                   class="form-control"
                   placeholder="<?php echo e($cfg['cantidad_ph']); ?>"
                   value="<?php echo e($actual->cantidad_aprox ?? ''); ?>">
          </div>

          <div class="form-group">
            <label>Escala</label>
            <div class="escala-pills">
              <?php $__currentLoopData = ['pequena'=>'Pequeña','mediana'=>'Mediana','grande'=>'Grande']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $checked = ($actual->escala ?? 'pequena') === $val; ?>
                <label class="escala-pill <?php echo e($checked ? 'selected' : ''); ?>">
                  <input type="radio"
                         name="config[<?php echo e($codigo); ?>][escala]"
                         value="<?php echo e($val); ?>"
                         <?php echo e($checked ? 'checked' : ''); ?>

                         onchange="syncEscalaPill(this)">
                  <?php echo e($lbl); ?>

                </label>
              <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
          </div>

          <?php if(!empty($cfg['extras'])): ?>
            <?php $__currentLoopData = $cfg['extras']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $extra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <div class="form-group">
                <label><?php echo e($extra['label']); ?></label>
                <input type="text"
                       name="config[<?php echo e($codigo); ?>][<?php echo e($extra['name']); ?>]"
                       class="form-control"
                       placeholder="<?php echo e($extra['ph']); ?>"
                       value="<?php echo e($meta[$extra['name']] ?? ''); ?>">
              </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          <?php endif; ?>

          <?php if(!empty($cfg['opciones'])): ?>
            <div class="form-group">
              <label>¿Qué tipo? (puedes marcar varios)</label>
              <div class="opciones-pills">
                <?php $__currentLoopData = $cfg['opciones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $marcada = !empty($meta[$op['name']]); ?>
                  <label class="opcion-pill <?php echo e($marcada ? 'selected' : ''); ?>">
                    <input type="checkbox"
                           name="config[<?php echo e($codigo); ?>][<?php echo e($op['name']); ?>]"
                           value="1"
                           <?php echo e($marcada ? 'checked' : ''); ?>

                           onchange="syncOpcionPill(this)">
                    <?php echo e($op['label']); ?>

                  </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

      <div id="configVacio" class="hidden" style="text-align:center;padding:24px;color:var(--gris);">
        Vuelve al paso anterior y selecciona al menos una línea productiva.
      </div>
    </div>

    <div class="wizard-dots">
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot active"></div>
      <div class="wizard-dot"></div>
    </div>
    <div class="wizard-actions">
      <button type="button" class="btn btn-ghost wizard-btn-back" onclick="wizardBack()">← Atrás</button>
      <button type="button" class="btn btn-primary wizard-btn-next" onclick="wizardNext()">Siguiente →</button>
    </div>
  </div>

  
  <div class="wizard-step hidden" data-step="4">
    <div class="onboard-emoji">🎉</div>
    <h2 class="onboard-title">¡Todo listo!</h2>
    <p class="onboard-desc">
      Tu Agrogranja se está adaptando a lo que produces. Ahora solo verás los módulos que necesitas.
    </p>

    <div id="resumenLineas" class="resumen-lineas"></div>

    <div class="wizard-dots">
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot"></div>
      <div class="wizard-dot active"></div>
    </div>
    <div class="wizard-actions">
      <button type="button" class="btn btn-ghost wizard-btn-back" onclick="wizardBack()">← Atrás</button>
      <button type="submit" class="btn btn-primary">Empezar 🚀</button>
    </div>
  </div>

</form>

<?php $__env->startPush('scripts'); ?>
<script>
  let wizardCurrentStep = 0;
  const totalSteps = 5;

  function wizardShow(step) {
    document.querySelectorAll('.wizard-step').forEach(function(el) {
      el.classList.add('hidden');
    });
    const target = document.querySelector('.wizard-step[data-step="' + step + '"]');
    if (target) target.classList.remove('hidden');
    wizardCurrentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });

    // Si entramos al paso 3, mostrar solo configs de líneas marcadas.
    if (step === 3) actualizarConfigVisibles();
    // Si entramos al paso 4, generar el resumen.
    if (step === 4) generarResumen();
  }

  function wizardNext() {
    if (wizardCurrentStep < totalSteps - 1) wizardShow(wizardCurrentStep + 1);
  }

  function wizardBack() {
    if (wizardCurrentStep > 0) wizardShow(wizardCurrentStep - 1);
  }

  function wizardNextFromLineas() {
    const checks = document.querySelectorAll('.linea-check:checked');
    const errBox = document.getElementById('lineasError');
    if (checks.length === 0) {
      errBox.classList.remove('hidden');
      return;
    }
    errBox.classList.add('hidden');
    wizardNext();
  }

  function toggleLineaCard(input) {
    const card = input.closest('.linea-card');
    if (input.checked) card.classList.add('selected');
    else card.classList.remove('selected');
  }

  function actualizarConfigVisibles() {
    const seleccionados = Array.from(document.querySelectorAll('.linea-check:checked'))
      .map(function(c) { return c.value; });

    let alguno = false;
    document.querySelectorAll('.config-block').forEach(function(block) {
      const codigo = block.getAttribute('data-config-for');
      if (seleccionados.indexOf(codigo) !== -1) {
        block.classList.remove('hidden');
        alguno = true;
      } else {
        block.classList.add('hidden');
      }
    });
    document.getElementById('configVacio').classList.toggle('hidden', alguno);
  }

  function syncEscalaPill(input) {
    const group = input.closest('.escala-pills');
    group.querySelectorAll('.escala-pill').forEach(function(p) { p.classList.remove('selected'); });
    input.closest('.escala-pill').classList.add('selected');
  }

  function syncOpcionPill(input) {
    const pill = input.closest('.opcion-pill');
    if (input.checked) pill.classList.add('selected');
    else pill.classList.remove('selected');
  }

  function generarResumen() {
    const seleccionados = Array.from(document.querySelectorAll('.linea-check:checked'));
    const cont = document.getElementById('resumenLineas');
    if (seleccionados.length === 0) {
      cont.innerHTML = '<p style="color:var(--rojo);">Sin líneas seleccionadas.</p>';
      return;
    }
    let html = '<p class="resumen-titulo">Tu finca manejará:</p><div class="resumen-chips">';
    seleccionados.forEach(function(c) {
      const card = c.closest('.linea-card');
      const emoji = card.querySelector('.linea-emoji').textContent;
      const nombre = card.querySelector('.linea-nombre').textContent;
      html += '<span class="resumen-chip">' + emoji + ' ' + nombre + '</span>';
    });
    html += '</div>';
    cont.innerHTML = html;
  }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/auth/onboarding.blade.php ENDPATH**/ ?>