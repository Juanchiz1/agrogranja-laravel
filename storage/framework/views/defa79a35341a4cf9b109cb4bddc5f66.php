<?php $__env->startSection('title','Perfil'); ?>
<?php $__env->startSection('page_title','👤 Mi Perfil'); ?>
<?php $__env->startSection('back_url', route('dashboard')); ?>

<?php $__env->startPush('head'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/perfil.css')); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/lineas-productivas.css')); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<?php $tabActual = request('tab','datos'); ?>


<div style="text-align:center;padding:16px 0 20px;">
  
  <div class="perfil-avatar-wrap">
    <label for="fotoPerfilInput" style="cursor:pointer;">
      <?php if(isset($user->foto_perfil) && $user->foto_perfil): ?>
        <img src="<?php echo e(asset($user->foto_perfil)); ?>" class="perfil-avatar" style="overflow:hidden;">
      <?php else: ?>
        <div class="perfil-avatar">👤</div>
      <?php endif; ?>
      <div class="perfil-avatar-edit" title="Cambiar foto">📷</div>
    </label>
  </div>

  <h2 style="font-family:var(--font-serif);font-size:1.25rem;margin-bottom:4px;"><?php echo e($user->nombre); ?></h2>
  <p style="font-size:.82rem;color:var(--text-secondary);"><?php echo e($user->email); ?></p>
  <?php if($user->nombre_finca): ?>
    <p style="font-size:.82rem;color:var(--verde-dark);font-weight:600;margin-top:4px;">🏡 <?php echo e($user->nombre_finca); ?></p>
  <?php endif; ?>
  <?php if(isset($user->departamento) && $user->departamento): ?>
    <p style="font-size:.75rem;color:var(--text-muted);">📍 <?php echo e($user->departamento); ?><?php echo e($user->municipio ? ', '.$user->municipio : ''); ?></p>
  <?php endif; ?>
  <?php if(isset($user->tipo_produccion) && $user->tipo_produccion): ?>
    <span style="background:var(--verde-bg);color:var(--verde-dark);font-size:.72rem;padding:2px 10px;border-radius:99px;font-weight:600;display:inline-block;margin-top:6px;"><?php echo e($user->tipo_produccion); ?></span>
  <?php endif; ?>
</div>


<div class="stat-perfil-grid">
  <div class="stat-perfil"><div class="stat-perfil-val"><?php echo e($stats['cultivos']); ?></div><div class="stat-perfil-lbl">Cultivos</div></div>
  <div class="stat-perfil"><div class="stat-perfil-val"><?php echo e($stats['animales']); ?></div><div class="stat-perfil-lbl">Animales</div></div>
  <div class="stat-perfil"><div class="stat-perfil-val"><?php echo e($stats['cosechas']); ?></div><div class="stat-perfil-lbl">Cosechas</div></div>
</div>


<?php $balanceAnio = $stats['total_ingresos'] - $stats['total_gastos']; ?>
<div class="balance-card" style="background:<?php echo e($balanceAnio >= 0 ? 'var(--verde-bg)' : '#fef2f2'); ?>;">
  <div>
    <div style="font-size:.78rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Balance <?php echo e(now()->year); ?></div>
    <div style="font-size:1.4rem;font-weight:800;color:<?php echo e($balanceAnio >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>;">
      <?php echo e($balanceAnio >= 0 ? '+' : ''); ?>$<?php echo e(number_format($balanceAnio,0,',','.')); ?>

    </div>
  </div>
  <div style="text-align:right;font-size:.78rem;">
    <div style="color:var(--verde-dark);">↑ $<?php echo e(number_format($stats['total_ingresos'],0,',','.')); ?></div>
    <div style="color:var(--rojo);">↓ $<?php echo e(number_format($stats['total_gastos'],0,',','.')); ?></div>
  </div>
</div>


<div class="tabs-perfil">
  <a href="?tab=datos"        class="tab-perfil <?php echo e($tabActual==='datos'        ? 'active' : ''); ?>">👤 Datos</a>
  <a href="?tab=finca"        class="tab-perfil <?php echo e($tabActual==='finca'        ? 'active' : ''); ?>">🏡 Mi Finca</a>
  <a href="?tab=financiero"   class="tab-perfil <?php echo e($tabActual==='financiero'   ? 'active' : ''); ?>">💳 Financiero</a>
  <a href="?tab=preferencias" class="tab-perfil <?php echo e($tabActual==='preferencias' ? 'active' : ''); ?>">⚙️ Preferencias</a>
  <a href="?tab=seguridad"    class="tab-perfil <?php echo e($tabActual==='seguridad'    ? 'active' : ''); ?>">🔐 Seguridad</a>
</div>

<div style="background:#f0faf5; border:1px solid #b2dfc9; border-radius:12px; padding:1rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
    <div>
        <p style="font-weight:600; color:#085041; margin:0; font-size:14px;">
            Encuesta de impacto
        </p>
        <p style="font-size:12px; color:#0F6E56; margin:0;">
            Cuéntanos cómo Agrogranja ha cambiado tu trabajo en el campo
        </p>
    </div>
    <a href="<?php echo e(route('encuesta.show')); ?>"
       style="background:#1D9E75; color:#fff; padding:8px 18px; border-radius:9px; text-decoration:none; font-size:13px; font-weight:500; white-space:nowrap;">
        Responder encuesta
    </a>
</div>


<?php if($tabActual === 'datos'): ?>
<div class="card">
  <form method="POST" action="<?php echo e(route('perfil.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="tab_actual" value="datos">

    
    <input type="file" id="fotoPerfilInput" name="foto_perfil" accept="image/*" style="display:none;" onchange="this.form.submit()">

    <div class="form-group">
      <label>Nombre completo *</label>
      <input type="text" name="nombre" class="form-control" required value="<?php echo e($user->nombre); ?>">
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Departamento</label>
        <input type="text" name="departamento" class="form-control" value="<?php echo e($user->departamento); ?>">
      </div>
      <div class="form-group">
        <label>Municipio</label>
        <input type="text" name="municipio" class="form-control" value="<?php echo e($user->municipio); ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Teléfono / WhatsApp</label>
      <input type="tel" name="telefono" class="form-control" value="<?php echo e($user->telefono); ?>" placeholder="3001234567">
    </div>
    <div class="form-group">
      <label>Correo electrónico</label>
      <input type="email" class="form-control" value="<?php echo e($user->email); ?>" disabled style="opacity:.6;">
      <small style="font-size:.73rem;color:var(--text-muted);">El correo no se puede cambiar.</small>
    </div>
    <div class="form-group">
      <label>Cédula / NIT</label>
      <input type="text" name="rut" class="form-control" value="<?php echo e($user->rut ?? ''); ?>" placeholder="Número de identificación">
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar datos personales</button>
  </form>
</div>


<?php elseif($tabActual === 'finca'): ?>
<div class="card">
  
  <?php if(isset($user->foto_finca) && $user->foto_finca): ?>
    <img src="<?php echo e(asset($user->foto_finca)); ?>" class="finca-foto" onclick="document.getElementById('fotoFincaInput').click()">
  <?php else: ?>
    <div class="finca-foto-placeholder" onclick="document.getElementById('fotoFincaInput').click()">
      🏡
      <div style="font-size:.8rem;color:var(--verde-dark);margin-top:6px;">Toca para agregar foto de tu finca</div>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?php echo e(route('perfil.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="tab_actual" value="finca">
    <input type="file" id="fotoFincaInput" name="foto_finca" accept="image/*" style="display:none;" onchange="this.form.submit()">

    <div class="form-group">
      <label>Nombre de la finca</label>
      <input type="text" name="nombre_finca" class="form-control" placeholder="Ej: Finca La Esperanza" value="<?php echo e($user->nombre_finca); ?>">
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Área total (hectáreas)</label>
        <input type="number" step="0.1" name="hectareas_total" class="form-control" placeholder="0.0" value="<?php echo e($user->hectareas_total ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Tipo de producción</label>
        <select name="tipo_produccion" class="form-control">
          <option value="">Seleccionar...</option>
          <?php $__currentLoopData = ['Agrícola','Ganadera','Mixta (agrícola + ganadera)','Piscícola','Avícola','Apícola','Agroforestal','Otro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <option <?php echo e(($user->tipo_produccion ?? '') === $tp ? 'selected' : ''); ?>><?php echo e($tp); ?></option>
          <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Descripción de la finca</label>
      <textarea name="descripcion_finca" class="form-control" rows="3" placeholder="Cuéntanos sobre tu finca: qué produce, su historia, condiciones especiales..."><?php echo e($user->descripcion_finca ?? ''); ?></textarea>
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Departamento</label>
        <input type="text" name="departamento" class="form-control" value="<?php echo e($user->departamento); ?>">
      </div>
      <div class="form-group">
        <label>Municipio</label>
        <input type="text" name="municipio" class="form-control" value="<?php echo e($user->municipio); ?>">
      </div>
    </div>
    <div class="form-group">
      <label>📍 Coordenadas GPS (opcional)</label>
      <div class="grid-2">
        <input type="number" step="0.0000001" name="latitud"  class="form-control" placeholder="Latitud"  value="<?php echo e($user->latitud  ?? ''); ?>">
        <input type="number" step="0.0000001" name="longitud" class="form-control" placeholder="Longitud" value="<?php echo e($user->longitud ?? ''); ?>">
      </div>
      <small style="font-size:.73rem;color:var(--text-muted);">Puedes obtenerlas abriendo Google Maps y manteniendo presionada tu finca.</small>
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar información de la finca</button>
  </form>

  
  <div style="margin-top:20px;padding:14px;background:var(--verde-bg);border-radius:var(--radius-lg);">
    <p style="font-weight:700;margin-bottom:10px;color:var(--verde-dark);">📊 Resumen de actividad</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.83rem;">
      <div>🌱 <strong><?php echo e($stats['cultivos']); ?></strong> cultivos registrados</div>
      <div>🐄 <strong><?php echo e($stats['animales']); ?></strong> animales activos</div>
      <div>🌾 <strong><?php echo e($stats['cosechas']); ?></strong> cosechas</div>
      <div>📅 <strong><?php echo e($stats['tareas']); ?></strong> tareas</div>
      <div>💰 <strong><?php echo e($stats['gastos']); ?></strong> gastos registrados</div>
      <div>📈 <strong><?php echo e($stats['ingresos']); ?></strong> ingresos registrados</div>
    </div>
    <?php if(isset($user->creado_en) && $user->creado_en): ?>
      <p style="font-size:.75rem;color:var(--text-muted);margin-top:10px;">
        Miembro desde <?php echo e(\Carbon\Carbon::parse($user->creado_en)->format('d/m/Y')); ?>

        (<?php echo e(\Carbon\Carbon::parse($user->creado_en)->diffForHumans()); ?>)
      </p>
    <?php endif; ?>
  </div>
</div>


<?php elseif($tabActual === 'financiero'): ?>
<div style="background:#eff6ff;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:16px;border-left:3px solid #3b82f6;">
  <p style="font-size:.82rem;color:#1e40af;">🔒 Esta información es privada y solo visible por ti. Se puede usar como referencia para tus reportes.</p>
</div>
<div class="card">
  <form method="POST" action="<?php echo e(route('perfil.update')); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="tab_actual" value="financiero">
    <input type="hidden" name="nombre" value="<?php echo e($user->nombre); ?>">

    <div class="form-group">
      <label>Entidad bancaria</label>
      <select name="entidad_bancaria" class="form-control">
        <option value="">Seleccionar...</option>
        <?php $__currentLoopData = ['Bancolombia','Davivienda','BBVA','Banco de Bogotá','Banco Popular','Banco Agrario','Nequi','Daviplata','Bancamía','Banco W','Otro']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $banco): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <option <?php echo e(($user->entidad_bancaria ?? '') === $banco ? 'selected' : ''); ?>><?php echo e($banco); ?></option>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </select>
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Número de cuenta</label>
        <input type="text" name="num_cuenta" class="form-control" placeholder="Número de cuenta" value="<?php echo e($user->num_cuenta ?? ''); ?>">
      </div>
      <div class="form-group">
        <label>Tipo de cuenta</label>
        <select name="tipo_cuenta" class="form-control">
          <option value="">Seleccionar</option>
          <option <?php echo e(($user->tipo_cuenta ?? '') === 'Ahorros' ? 'selected' : ''); ?>>Ahorros</option>
          <option <?php echo e(($user->tipo_cuenta ?? '') === 'Corriente' ? 'selected' : ''); ?>>Corriente</option>
          <option <?php echo e(($user->tipo_cuenta ?? '') === 'Nequi/Daviplata' ? 'selected' : ''); ?>>Nequi/Daviplata</option>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar datos financieros</button>
  </form>
</div>


<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:14px;">💹 Mi desempeño <?php echo e(now()->year); ?></p>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Total ingresos</div>
      <div style="font-weight:800;color:var(--verde-dark);">$<?php echo e(number_format($stats['total_ingresos'],0,',','.')); ?></div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Total gastos</div>
      <div style="font-weight:800;color:var(--rojo);">$<?php echo e(number_format($stats['total_gastos'],0,',','.')); ?></div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.87rem;">
    <span>Balance neto <?php echo e(now()->year); ?></span>
    <strong style="color:<?php echo e($balanceAnio >= 0 ? 'var(--verde-dark)' : 'var(--rojo)'); ?>">
      <?php echo e($balanceAnio >= 0 ? '+' : ''); ?>$<?php echo e(number_format($balanceAnio,0,',','.')); ?>

    </strong>
  </div>
</div>


<?php elseif($tabActual === 'preferencias'): ?>
<div class="card">
  <form method="POST" action="<?php echo e(route('perfil.notificaciones')); ?>">
    <?php echo csrf_field(); ?>

    <div class="pref-row">
      <div>
        <div class="pref-label">🔔 Recordatorio de tareas</div>
        <div class="pref-desc">Alertas cuando hay tareas vencidas o próximas</div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" name="notif_tareas" <?php echo e(($user->notif_tareas ?? 1) ? 'checked' : ''); ?>>
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="pref-row">
      <div>
        <div class="pref-label">📦 Alertas de inventario</div>
        <div class="pref-desc">Avisos cuando el stock baja del mínimo</div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" name="notif_stock" <?php echo e(($user->notif_stock ?? 1) ? 'checked' : ''); ?>>
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="pref-row">
      <div>
        <div class="pref-label">💱 Moneda</div>
        <div class="pref-desc">Moneda principal para mostrar valores</div>
      </div>
      <select name="moneda" class="form-control" style="width:100px;font-size:.82rem;">
        <option value="COP" <?php echo e(($user->moneda ?? 'COP') === 'COP' ? 'selected' : ''); ?>>COP $</option>
        <option value="USD" <?php echo e(($user->moneda ?? 'COP') === 'USD' ? 'selected' : ''); ?>>USD $</option>
        <option value="EUR" <?php echo e(($user->moneda ?? 'COP') === 'EUR' ? 'selected' : ''); ?>>EUR €</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary btn-full mt-3">Guardar preferencias</button>
  </form>
</div>


<?php
  // Configuración rápida por línea (mismas opciones que el onboarding).
  $configCamposPerfil = [
    'cultivos'      => ['cantidad_label'=>'Hectáreas dedicadas', 'cantidad_ph'=>'Ej: 2',
        'extras'=>[['name'=>'tipos','label'=>'¿Qué cultivas?','ph'=>'Maíz, yuca, plátano...']], 'opciones'=>[]],
    'bovino'        => ['cantidad_label'=>'Cabezas aprox.', 'cantidad_ph'=>'Ej: 25',
        'opciones'=>[['name'=>'leche','label'=>'Lechería'],['name'=>'carne','label'=>'Engorde'],['name'=>'cria','label'=>'Cría']]],
    'porcino'       => ['cantidad_label'=>'Cantidad aprox.', 'cantidad_ph'=>'Ej: 30',
        'opciones'=>[['name'=>'engorde','label'=>'Engorde'],['name'=>'cria','label'=>'Cría']]],
    'avicola'       => ['cantidad_label'=>'Aves aprox.', 'cantidad_ph'=>'Ej: 200',
        'opciones'=>[['name'=>'postura','label'=>'Postura'],['name'=>'engorde','label'=>'Engorde']]],
    'piscicola'     => ['cantidad_label'=>'Estanques', 'cantidad_ph'=>'Ej: 3',
        'extras'=>[['name'=>'especies','label'=>'Especies','ph'=>'Tilapia, cachama...']], 'opciones'=>[]],
    'caprino_ovino' => ['cantidad_label'=>'Cantidad aprox.', 'cantidad_ph'=>'Ej: 15',
        'opciones'=>[['name'=>'leche','label'=>'Leche'],['name'=>'carne','label'=>'Carne'],['name'=>'lana','label'=>'Lana']]],
    'apicola'       => ['cantidad_label'=>'Colmenas', 'cantidad_ph'=>'Ej: 10', 'opciones'=>[]],
    'equino'        => ['cantidad_label'=>'Cantidad', 'cantidad_ph'=>'Ej: 4',
        'opciones'=>[['name'=>'trabajo','label'=>'Trabajo'],['name'=>'cria','label'=>'Cría']]],
    'cunicola'      => ['cantidad_label'=>'Jaulas / madres', 'cantidad_ph'=>'Ej: 12', 'opciones'=>[]],
  ];
?>

<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:6px;">🚜 Líneas productivas</p>
  <p style="font-size:.82rem;color:var(--gris);margin-bottom:14px;">
    Activa o desactiva lo que manejas. La app mostrará solo los módulos que necesitas.
  </p>

  <form method="POST" action="<?php echo e(route('perfil.lineas')); ?>">
    <?php echo csrf_field(); ?>

    <?php if($errors->has('lineas')): ?>
      <div class="alert alert-error mb-3">❌ <?php echo e($errors->first('lineas')); ?></div>
    <?php endif; ?>

    <div class="lineas-grid lineas-grid-compact">
      <?php $__currentLoopData = $lineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $marcada = isset($lineasUsuario[$linea->codigo]) && $lineasUsuario[$linea->codigo]->activa;
        ?>
        <label class="linea-card <?php echo e($marcada ? 'selected' : ''); ?>" data-codigo="<?php echo e($linea->codigo); ?>">
          <input type="checkbox" name="lineas[]" value="<?php echo e($linea->codigo); ?>"
                 class="linea-check"
                 onchange="this.closest('.linea-card').classList.toggle('selected', this.checked); togglePerfilConfig('<?php echo e($linea->codigo); ?>', this.checked);"
                 <?php echo e($marcada ? 'checked' : ''); ?>>
          <div class="linea-emoji"><?php echo e($linea->emoji); ?></div>
          <div class="linea-nombre"><?php echo e($linea->nombre); ?></div>
        </label>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div id="perfilConfigContainer" style="margin-top:18px;">
      <?php $__currentLoopData = $lineas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linea): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
          $codigo  = $linea->codigo;
          $cfg     = $configCamposPerfil[$codigo] ?? null;
          $actual  = $lineasUsuario[$codigo] ?? null;
          $marcada = $actual && $actual->activa;
          $meta    = $actual && $actual->metadata ? json_decode($actual->metadata, true) : [];
        ?>

        <?php if($cfg): ?>
        <div class="config-block <?php echo e($marcada ? '' : 'hidden'); ?>" data-config-perfil-for="<?php echo e($codigo); ?>">
          <div class="config-block-header">
            <span class="config-emoji"><?php echo e($linea->emoji); ?></span>
            <span class="config-titulo"><?php echo e($linea->nombre); ?></span>
          </div>

          <div class="grid-2">
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
              <select name="config[<?php echo e($codigo); ?>][escala]" class="form-control">
                <?php $__currentLoopData = ['pequena'=>'Pequeña','mediana'=>'Mediana','grande'=>'Grande']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <option value="<?php echo e($val); ?>" <?php echo e(($actual->escala ?? 'pequena') === $val ? 'selected' : ''); ?>><?php echo e($lbl); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </select>
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
              <label>Tipo (puedes marcar varios)</label>
              <div class="opciones-pills">
                <?php $__currentLoopData = $cfg['opciones']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php $opMarcada = !empty($meta[$op['name']]); ?>
                  <label class="opcion-pill <?php echo e($opMarcada ? 'selected' : ''); ?>">
                    <input type="checkbox"
                           name="config[<?php echo e($codigo); ?>][<?php echo e($op['name']); ?>]"
                           value="1"
                           <?php echo e($opMarcada ? 'checked' : ''); ?>

                           onchange="this.closest('.opcion-pill').classList.toggle('selected', this.checked);">
                    <?php echo e($op['label']); ?>

                  </label>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </div>
            </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <button type="submit" class="btn btn-primary btn-full mt-3">Guardar líneas productivas</button>
  </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
  function togglePerfilConfig(codigo, mostrar) {
    const block = document.querySelector('[data-config-perfil-for="' + codigo + '"]');
    if (!block) return;
    if (mostrar) block.classList.remove('hidden');
    else block.classList.add('hidden');
  }
</script>
<?php $__env->stopPush(); ?>


<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:12px;">⚙️ Cuenta</p>
  <div style="font-size:.85rem;color:var(--text-secondary);margin-bottom:16px;">
    <div>📧 <?php echo e($user->email); ?></div>
    <?php if(isset($user->creado_en) && $user->creado_en): ?>
      <div style="margin-top:4px;">🗓️ Miembro desde <?php echo e(\Carbon\Carbon::parse($user->creado_en)->format('d/m/Y')); ?></div>
    <?php endif; ?>
  </div>
  <form method="POST" action="<?php echo e(route('logout')); ?>" onsubmit="return confirm('¿Cerrar sesión?')">
    <?php echo csrf_field(); ?>
    <button type="submit" class="btn btn-danger btn-full">🚪 Cerrar sesión</button>
  </form>
</div>


<?php elseif($tabActual === 'seguridad'): ?>
<div class="card">
  <?php if($errors->has('password_actual')): ?>
    <div class="alert alert-error mb-3">❌ <?php echo e($errors->first('password_actual')); ?></div>
  <?php endif; ?>
  <p style="font-weight:700;margin-bottom:14px;">🔐 Cambiar contraseña</p>
  <form method="POST" action="<?php echo e(route('perfil.password')); ?>">
    <?php echo csrf_field(); ?>
    <div class="form-group">
      <label>Contraseña actual</label>
      <input type="password" name="password_actual" class="form-control" required placeholder="••••••">
    </div>
    <div class="form-group">
      <label>Nueva contraseña</label>
      <input type="password" name="password_nueva" class="form-control" required placeholder="Mínimo 6 caracteres">
    </div>
    <div class="form-group">
      <label>Confirmar nueva contraseña</label>
      <input type="password" name="password_confirmar" class="form-control" required placeholder="Repetir contraseña">
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Cambiar contraseña</button>
  </form>
</div>

<div class="card mt-3" style="background:#fef2f2;border:1px solid #fca5a5;">
  <p style="font-weight:700;color:var(--rojo);margin-bottom:8px;">⚠️ Zona de peligro</p>
  <p style="font-size:.83rem;color:var(--text-secondary);margin-bottom:12px;">Si tienes problemas con tu cuenta, contacta al administrador del sistema.</p>
  <div style="font-size:.8rem;color:var(--text-muted);">
    Última sesión: <?php echo e(\Carbon\Carbon::parse($user->actualizado_en ?? $user->creado_en ?? now())->format('d/m/Y H:i')); ?>

  </div>
</div>
<?php endif; ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\Juan Diego\Documents\Universidad Documentos clases\Sem Investigacion\agrogranja-laravel\resources\views/pages/perfil.blade.php ENDPATH**/ ?>