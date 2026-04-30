@extends('layouts.app')
@section('title','Perfil')
@section('page_title','👤 Mi Perfil')
@section('back_url', route('dashboard'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/perfil.css') }}">
<link rel="stylesheet" href="{{ asset('css/lineas-productivas.css') }}">
@endpush

@section('content')
@php $tabActual = request('tab','datos'); @endphp

{{-- HEADER PERFIL --}}
<div style="text-align:center;padding:16px 0 20px;">
  {{-- Avatar --}}
  <div class="perfil-avatar-wrap">
    <label for="fotoPerfilInput" style="cursor:pointer;">
      @if(isset($user->foto_perfil) && $user->foto_perfil)
        <img src="{{ asset($user->foto_perfil) }}" class="perfil-avatar" style="overflow:hidden;">
      @else
        <div class="perfil-avatar">👤</div>
      @endif
      <div class="perfil-avatar-edit" title="Cambiar foto">📷</div>
    </label>
  </div>

  <h2 style="font-family:var(--font-serif);font-size:1.25rem;margin-bottom:4px;">{{ $user->nombre }}</h2>
  <p style="font-size:.82rem;color:var(--text-secondary);">{{ $user->email }}</p>
  @if($user->nombre_finca)
    <p style="font-size:.82rem;color:var(--verde-dark);font-weight:600;margin-top:4px;">🏡 {{ $user->nombre_finca }}</p>
  @endif
  @if(isset($user->departamento) && $user->departamento)
    <p style="font-size:.75rem;color:var(--text-muted);">📍 {{ $user->departamento }}{{ $user->municipio ? ', '.$user->municipio : '' }}</p>
  @endif
  @if(isset($user->tipo_produccion) && $user->tipo_produccion)
    <span style="background:var(--verde-bg);color:var(--verde-dark);font-size:.72rem;padding:2px 10px;border-radius:99px;font-weight:600;display:inline-block;margin-top:6px;">{{ $user->tipo_produccion }}</span>
  @endif
</div>

{{-- STATS --}}
<div class="stat-perfil-grid">
  <div class="stat-perfil"><div class="stat-perfil-val">{{ $stats['cultivos'] }}</div><div class="stat-perfil-lbl">Cultivos</div></div>
  <div class="stat-perfil"><div class="stat-perfil-val">{{ $stats['animales'] }}</div><div class="stat-perfil-lbl">Animales</div></div>
  <div class="stat-perfil"><div class="stat-perfil-val">{{ $stats['cosechas'] }}</div><div class="stat-perfil-lbl">Cosechas</div></div>
</div>

{{-- Balance año --}}
@php $balanceAnio = $stats['total_ingresos'] - $stats['total_gastos']; @endphp
<div class="balance-card" style="background:{{ $balanceAnio >= 0 ? 'var(--verde-bg)' : '#fef2f2' }};">
  <div>
    <div style="font-size:.78rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;">Balance {{ now()->year }}</div>
    <div style="font-size:1.4rem;font-weight:800;color:{{ $balanceAnio >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }};">
      {{ $balanceAnio >= 0 ? '+' : '' }}${{ number_format($balanceAnio,0,',','.') }}
    </div>
  </div>
  <div style="text-align:right;font-size:.78rem;">
    <div style="color:var(--verde-dark);">↑ ${{ number_format($stats['total_ingresos'],0,',','.') }}</div>
    <div style="color:var(--rojo);">↓ ${{ number_format($stats['total_gastos'],0,',','.') }}</div>
  </div>
</div>

{{-- TABS --}}
<div class="tabs-perfil">
  <a href="?tab=datos"        class="tab-perfil {{ $tabActual==='datos'        ? 'active' : '' }}">👤 Datos</a>
  <a href="?tab=finca"        class="tab-perfil {{ $tabActual==='finca'        ? 'active' : '' }}">🏡 Mi Finca</a>
  <a href="?tab=financiero"   class="tab-perfil {{ $tabActual==='financiero'   ? 'active' : '' }}">💳 Financiero</a>
  <a href="?tab=preferencias" class="tab-perfil {{ $tabActual==='preferencias' ? 'active' : '' }}">⚙️ Preferencias</a>
  <a href="?tab=seguridad"    class="tab-perfil {{ $tabActual==='seguridad'    ? 'active' : '' }}">🔐 Seguridad</a>
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
    <a href="{{ route('encuesta.show') }}"
       style="background:#1D9E75; color:#fff; padding:8px 18px; border-radius:9px; text-decoration:none; font-size:13px; font-weight:500; white-space:nowrap;">
        Responder encuesta
    </a>
</div>

{{-- ===== TAB: DATOS PERSONALES ===== --}}
@if($tabActual === 'datos')
<div class="card">
  <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tab_actual" value="datos">

    {{-- Input oculto para foto --}}
    <input type="file" id="fotoPerfilInput" name="foto_perfil" accept="image/*" style="display:none;" onchange="this.form.submit()">

    <div class="form-group">
      <label>Nombre completo *</label>
      <input type="text" name="nombre" class="form-control" required value="{{ $user->nombre }}">
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Departamento</label>
        <input type="text" name="departamento" class="form-control" value="{{ $user->departamento }}">
      </div>
      <div class="form-group">
        <label>Municipio</label>
        <input type="text" name="municipio" class="form-control" value="{{ $user->municipio }}">
      </div>
    </div>
    <div class="form-group">
      <label>Teléfono / WhatsApp</label>
      <input type="tel" name="telefono" class="form-control" value="{{ $user->telefono }}" placeholder="3001234567">
    </div>
    <div class="form-group">
      <label>Correo electrónico</label>
      <input type="email" class="form-control" value="{{ $user->email }}" disabled style="opacity:.6;">
      <small style="font-size:.73rem;color:var(--text-muted);">El correo no se puede cambiar.</small>
    </div>
    <div class="form-group">
      <label>Cédula / NIT</label>
      <input type="text" name="rut" class="form-control" value="{{ $user->rut ?? '' }}" placeholder="Número de identificación">
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar datos personales</button>
  </form>
</div>

{{-- ===== TAB: MI FINCA ===== --}}
@elseif($tabActual === 'finca')
<div class="card">
  {{-- Foto de la finca --}}
  @if(isset($user->foto_finca) && $user->foto_finca)
    <img src="{{ asset($user->foto_finca) }}" class="finca-foto" onclick="document.getElementById('fotoFincaInput').click()">
  @else
    <div class="finca-foto-placeholder" onclick="document.getElementById('fotoFincaInput').click()">
      🏡
      <div style="font-size:.8rem;color:var(--verde-dark);margin-top:6px;">Toca para agregar foto de tu finca</div>
    </div>
  @endif

  <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tab_actual" value="finca">
    <input type="file" id="fotoFincaInput" name="foto_finca" accept="image/*" style="display:none;" onchange="this.form.submit()">

    <div class="form-group">
      <label>Nombre de la finca</label>
      <input type="text" name="nombre_finca" class="form-control" placeholder="Ej: Finca La Esperanza" value="{{ $user->nombre_finca }}">
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Área total (hectáreas)</label>
        <input type="number" step="0.1" name="hectareas_total" class="form-control" placeholder="0.0" value="{{ $user->hectareas_total ?? '' }}">
      </div>
      <div class="form-group">
        <label>Tipo de producción</label>
        <select name="tipo_produccion" class="form-control">
          <option value="">Seleccionar...</option>
          @foreach(['Agrícola','Ganadera','Mixta (agrícola + ganadera)','Piscícola','Avícola','Apícola','Agroforestal','Otro'] as $tp)
            <option {{ ($user->tipo_produccion ?? '') === $tp ? 'selected' : '' }}>{{ $tp }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="form-group">
      <label>Descripción de la finca</label>
      <textarea name="descripcion_finca" class="form-control" rows="3" placeholder="Cuéntanos sobre tu finca: qué produce, su historia, condiciones especiales...">{{ $user->descripcion_finca ?? '' }}</textarea>
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Departamento</label>
        <input type="text" name="departamento" class="form-control" value="{{ $user->departamento }}">
      </div>
      <div class="form-group">
        <label>Municipio</label>
        <input type="text" name="municipio" class="form-control" value="{{ $user->municipio }}">
      </div>
    </div>
    <div class="form-group">
      <label>📍 Coordenadas GPS (opcional)</label>
      <div class="grid-2">
        <input type="number" step="0.0000001" name="latitud"  class="form-control" placeholder="Latitud"  value="{{ $user->latitud  ?? '' }}">
        <input type="number" step="0.0000001" name="longitud" class="form-control" placeholder="Longitud" value="{{ $user->longitud ?? '' }}">
      </div>
      <small style="font-size:.73rem;color:var(--text-muted);">Puedes obtenerlas abriendo Google Maps y manteniendo presionada tu finca.</small>
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar información de la finca</button>
  </form>

  {{-- Resumen de la finca --}}
  <div style="margin-top:20px;padding:14px;background:var(--verde-bg);border-radius:var(--radius-lg);">
    <p style="font-weight:700;margin-bottom:10px;color:var(--verde-dark);">📊 Resumen de actividad</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:.83rem;">
      <div>🌱 <strong>{{ $stats['cultivos'] }}</strong> cultivos registrados</div>
      <div>🐄 <strong>{{ $stats['animales'] }}</strong> animales activos</div>
      <div>🌾 <strong>{{ $stats['cosechas'] }}</strong> cosechas</div>
      <div>📅 <strong>{{ $stats['tareas'] }}</strong> tareas</div>
      <div>💰 <strong>{{ $stats['gastos'] }}</strong> gastos registrados</div>
      <div>📈 <strong>{{ $stats['ingresos'] }}</strong> ingresos registrados</div>
    </div>
    @if(isset($user->creado_en) && $user->creado_en)
      <p style="font-size:.75rem;color:var(--text-muted);margin-top:10px;">
        Miembro desde {{ \Carbon\Carbon::parse($user->creado_en)->format('d/m/Y') }}
        ({{ \Carbon\Carbon::parse($user->creado_en)->diffForHumans() }})
      </p>
    @endif
  </div>
</div>

{{-- ===== TAB: FINANCIERO ===== --}}
@elseif($tabActual === 'financiero')
<div style="background:#eff6ff;border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:16px;border-left:3px solid #3b82f6;">
  <p style="font-size:.82rem;color:#1e40af;">🔒 Esta información es privada y solo visible por ti. Se puede usar como referencia para tus reportes.</p>
</div>
<div class="card">
  <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tab_actual" value="financiero">
    <input type="hidden" name="nombre" value="{{ $user->nombre }}">

    <div class="form-group">
      <label>Entidad bancaria</label>
      <select name="entidad_bancaria" class="form-control">
        <option value="">Seleccionar...</option>
        @foreach(['Bancolombia','Davivienda','BBVA','Banco de Bogotá','Banco Popular','Banco Agrario','Nequi','Daviplata','Bancamía','Banco W','Otro'] as $banco)
          <option {{ ($user->entidad_bancaria ?? '') === $banco ? 'selected' : '' }}>{{ $banco }}</option>
        @endforeach
      </select>
    </div>
    <div class="grid-2">
      <div class="form-group">
        <label>Número de cuenta</label>
        <input type="text" name="num_cuenta" class="form-control" placeholder="Número de cuenta" value="{{ $user->num_cuenta ?? '' }}">
      </div>
      <div class="form-group">
        <label>Tipo de cuenta</label>
        <select name="tipo_cuenta" class="form-control">
          <option value="">Seleccionar</option>
          <option {{ ($user->tipo_cuenta ?? '') === 'Ahorros' ? 'selected' : '' }}>Ahorros</option>
          <option {{ ($user->tipo_cuenta ?? '') === 'Corriente' ? 'selected' : '' }}>Corriente</option>
          <option {{ ($user->tipo_cuenta ?? '') === 'Nequi/Daviplata' ? 'selected' : '' }}>Nequi/Daviplata</option>
        </select>
      </div>
    </div>
    <button type="submit" class="btn btn-primary btn-full mt-2">Guardar datos financieros</button>
  </form>
</div>

{{-- Resumen financiero del año --}}
<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:14px;">💹 Mi desempeño {{ now()->year }}</p>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px;">
    <div style="background:var(--verde-bg);border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Total ingresos</div>
      <div style="font-weight:800;color:var(--verde-dark);">${{ number_format($stats['total_ingresos'],0,',','.') }}</div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.72rem;color:var(--text-secondary);">Total gastos</div>
      <div style="font-weight:800;color:var(--rojo);">${{ number_format($stats['total_gastos'],0,',','.') }}</div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.87rem;">
    <span>Balance neto {{ now()->year }}</span>
    <strong style="color:{{ $balanceAnio >= 0 ? 'var(--verde-dark)' : 'var(--rojo)' }}">
      {{ $balanceAnio >= 0 ? '+' : '' }}${{ number_format($balanceAnio,0,',','.') }}
    </strong>
  </div>
</div>

{{-- ===== TAB: PREFERENCIAS ===== --}}
@elseif($tabActual === 'preferencias')
<div class="card">
  <form method="POST" action="{{ route('perfil.notificaciones') }}">
    @csrf

    <div class="pref-row">
      <div>
        <div class="pref-label">🔔 Recordatorio de tareas</div>
        <div class="pref-desc">Alertas cuando hay tareas vencidas o próximas</div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" name="notif_tareas" {{ ($user->notif_tareas ?? 1) ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="pref-row">
      <div>
        <div class="pref-label">📦 Alertas de inventario</div>
        <div class="pref-desc">Avisos cuando el stock baja del mínimo</div>
      </div>
      <label class="toggle-switch">
        <input type="checkbox" name="notif_stock" {{ ($user->notif_stock ?? 1) ? 'checked' : '' }}>
        <span class="toggle-slider"></span>
      </label>
    </div>

    <div class="pref-row">
      <div>
        <div class="pref-label">💱 Moneda</div>
        <div class="pref-desc">Moneda principal para mostrar valores</div>
      </div>
      <select name="moneda" class="form-control" style="width:100px;font-size:.82rem;">
        <option value="COP" {{ ($user->moneda ?? 'COP') === 'COP' ? 'selected' : '' }}>COP $</option>
        <option value="USD" {{ ($user->moneda ?? 'COP') === 'USD' ? 'selected' : '' }}>USD $</option>
        <option value="EUR" {{ ($user->moneda ?? 'COP') === 'EUR' ? 'selected' : '' }}>EUR €</option>
      </select>
    </div>

    <button type="submit" class="btn btn-primary btn-full mt-3">Guardar preferencias</button>
  </form>
</div>

{{-- ===== LÍNEAS PRODUCTIVAS ===== --}}
@php
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
@endphp

<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:6px;">🚜 Líneas productivas</p>
  <p style="font-size:.82rem;color:var(--gris);margin-bottom:14px;">
    Activa o desactiva lo que manejas. La app mostrará solo los módulos que necesitas.
  </p>

  <form method="POST" action="{{ route('perfil.lineas') }}">
    @csrf

    @if($errors->has('lineas'))
      <div class="alert alert-error mb-3">❌ {{ $errors->first('lineas') }}</div>
    @endif

    <div class="lineas-grid lineas-grid-compact">
      @foreach($lineas as $linea)
        @php
          $marcada = isset($lineasUsuario[$linea->codigo]) && $lineasUsuario[$linea->codigo]->activa;
        @endphp
        <label class="linea-card {{ $marcada ? 'selected' : '' }}" data-codigo="{{ $linea->codigo }}">
          <input type="checkbox" name="lineas[]" value="{{ $linea->codigo }}"
                 class="linea-check"
                 onchange="this.closest('.linea-card').classList.toggle('selected', this.checked); togglePerfilConfig('{{ $linea->codigo }}', this.checked);"
                 {{ $marcada ? 'checked' : '' }}>
          <div class="linea-emoji">{{ $linea->emoji }}</div>
          <div class="linea-nombre">{{ $linea->nombre }}</div>
        </label>
      @endforeach
    </div>

    <div id="perfilConfigContainer" style="margin-top:18px;">
      @foreach($lineas as $linea)
        @php
          $codigo  = $linea->codigo;
          $cfg     = $configCamposPerfil[$codigo] ?? null;
          $actual  = $lineasUsuario[$codigo] ?? null;
          $marcada = $actual && $actual->activa;
          $meta    = $actual && $actual->metadata ? json_decode($actual->metadata, true) : [];
        @endphp

        @if($cfg)
        <div class="config-block {{ $marcada ? '' : 'hidden' }}" data-config-perfil-for="{{ $codigo }}">
          <div class="config-block-header">
            <span class="config-emoji">{{ $linea->emoji }}</span>
            <span class="config-titulo">{{ $linea->nombre }}</span>
          </div>

          <div class="grid-2">
            <div class="form-group">
              <label>{{ $cfg['cantidad_label'] }}</label>
              <input type="number" min="0" step="1"
                     name="config[{{ $codigo }}][cantidad]"
                     class="form-control"
                     placeholder="{{ $cfg['cantidad_ph'] }}"
                     value="{{ $actual->cantidad_aprox ?? '' }}">
            </div>
            <div class="form-group">
              <label>Escala</label>
              <select name="config[{{ $codigo }}][escala]" class="form-control">
                @foreach(['pequena'=>'Pequeña','mediana'=>'Mediana','grande'=>'Grande'] as $val => $lbl)
                  <option value="{{ $val }}" {{ ($actual->escala ?? 'pequena') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
              </select>
            </div>
          </div>

          @if(!empty($cfg['extras']))
            @foreach($cfg['extras'] as $extra)
              <div class="form-group">
                <label>{{ $extra['label'] }}</label>
                <input type="text"
                       name="config[{{ $codigo }}][{{ $extra['name'] }}]"
                       class="form-control"
                       placeholder="{{ $extra['ph'] }}"
                       value="{{ $meta[$extra['name']] ?? '' }}">
              </div>
            @endforeach
          @endif

          @if(!empty($cfg['opciones']))
            <div class="form-group">
              <label>Tipo (puedes marcar varios)</label>
              <div class="opciones-pills">
                @foreach($cfg['opciones'] as $op)
                  @php $opMarcada = !empty($meta[$op['name']]); @endphp
                  <label class="opcion-pill {{ $opMarcada ? 'selected' : '' }}">
                    <input type="checkbox"
                           name="config[{{ $codigo }}][{{ $op['name'] }}]"
                           value="1"
                           {{ $opMarcada ? 'checked' : '' }}
                           onchange="this.closest('.opcion-pill').classList.toggle('selected', this.checked);">
                    {{ $op['label'] }}
                  </label>
                @endforeach
              </div>
            </div>
          @endif
        </div>
        @endif
      @endforeach
    </div>

    <button type="submit" class="btn btn-primary btn-full mt-3">Guardar líneas productivas</button>
  </form>
</div>

@push('scripts')
<script>
  function togglePerfilConfig(codigo, mostrar) {
    const block = document.querySelector('[data-config-perfil-for="' + codigo + '"]');
    if (!block) return;
    if (mostrar) block.classList.remove('hidden');
    else block.classList.add('hidden');
  }
</script>
@endpush

{{-- Acciones de cuenta --}}
<div class="card mt-3">
  <p style="font-weight:700;margin-bottom:12px;">⚙️ Cuenta</p>
  <div style="font-size:.85rem;color:var(--text-secondary);margin-bottom:16px;">
    <div>📧 {{ $user->email }}</div>
    @if(isset($user->creado_en) && $user->creado_en)
      <div style="margin-top:4px;">🗓️ Miembro desde {{ \Carbon\Carbon::parse($user->creado_en)->format('d/m/Y') }}</div>
    @endif
  </div>
  <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('¿Cerrar sesión?')">
    @csrf
    <button type="submit" class="btn btn-danger btn-full">🚪 Cerrar sesión</button>
  </form>
</div>

{{-- ===== TAB: SEGURIDAD ===== --}}
@elseif($tabActual === 'seguridad')
<div class="card">
  @if($errors->has('password_actual'))
    <div class="alert alert-error mb-3">❌ {{ $errors->first('password_actual') }}</div>
  @endif
  <p style="font-weight:700;margin-bottom:14px;">🔐 Cambiar contraseña</p>
  <form method="POST" action="{{ route('perfil.password') }}">
    @csrf
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
    Última sesión: {{ \Carbon\Carbon::parse($user->actualizado_en ?? $user->creado_en ?? now())->format('d/m/Y H:i') }}
  </div>
</div>
@endif

@endsection