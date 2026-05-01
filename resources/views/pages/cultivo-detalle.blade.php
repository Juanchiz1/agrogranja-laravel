@extends('layouts.app')
@section('title', $cultivo->nombre)
@section('page_title', ($emojis[$cultivo->tipo] ?? '🌿') . ' ' . $cultivo->nombre)
@section('back_url', route('cultivos.index'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/cultivos.css') }}">
<style>
/* ── Estilos Fase 3 para cultivo-detalle ──────────────────── */
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;overflow-y:auto;padding:20px; }
.modal-overlay.open { display:flex !important;align-items:flex-start;justify-content:center; }
.modal-box { background:#ffffff;border-radius:16px;padding:20px;width:100%;max-width:500px;margin:auto; }
.modal-title { font-weight:800;font-size:1.05rem;margin-bottom:16px; }

/* ── Tabs del modal evento avanzado ──────────────────────────
   Se usan colores explícitos (no variables) para garantizar
   visibilidad independientemente del tema o herencias de app.css.
   Inactivo: fondo gris claro + borde gris + texto oscuro.
   Activo:   fondo verde oscuro + texto blanco.
   ────────────────────────────────────────────────────────── */
.tab-group { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px; }
.tab-btn {
  padding: 7px 13px;
  border-radius: 20px;
  border: 1.5px solid #c0ccd8;
  background: #f1f5f9;
  color: #1e293b;
  font-size: .8rem;
  font-weight: 600;
  cursor: pointer;
  font-family: inherit;
  line-height: 1;
  transition: all .15s;
}
.tab-btn:hover {
  border-color: #3d8b3d;
  color: #2d6a2d;
  background: #edf7ed;
}
.tab-btn.active {
  background: #2d6a2d;
  color: #ffffff;
  border-color: #2d6a2d;
}

.fields-group { display:none; }
.fields-group.active { display:block; }
.form-row { display:grid;grid-template-columns:1fr 1fr;gap:10px; }
</style>
@endpush

@section('content')
@php
  $em = $emojis[$cultivo->tipo] ?? '🌿';
  $badgeClass = ['activo'=>'badge-green','cosechado'=>'badge-orange','vendido'=>'badge-brown'][$cultivo->estado] ?? 'badge-green';
  $balance    = $totalIngresos - $totalGastos;
  $diasDesde  = \Carbon\Carbon::parse($cultivo->fecha_siembra)->diffInDays(now());
@endphp

{{-- HERO --}}
<div class="cultivo-hero">
  @if($cultivo->imagen)
    <img src="{{ asset($cultivo->imagen) }}" alt="{{ $cultivo->nombre }}" onclick="openLightbox(this.src)">
  @elseif($fotos->count())
    <img src="{{ asset($fotos->first()->ruta) }}" alt="{{ $cultivo->nombre }}" onclick="openLightbox(this.src)">
  @else
    <div class="cultivo-hero-placeholder">{{ $em }}</div>
  @endif
  <div class="cultivo-hero-badge">
    <span class="badge {{ $badgeClass }}">{{ $cultivo->estado }}</span>
  </div>
</div>

{{-- MINI STATS --}}
<div class="mini-stats">
  <div class="mini-stat">
    <div class="mini-stat-val">{{ $diasDesde }}</div>
    <div class="mini-stat-label">días</div>
  </div>
  <div class="mini-stat">
    <div class="mini-stat-val" style="{{ $balance < 0 ? 'color:var(--rojo)' : '' }}">
      ${{ abs($balance) >= 1000 ? round(abs($balance)/1000,1).'k' : number_format(abs($balance),0,',','.') }}
    </div>
    <div class="mini-stat-label">balance</div>
  </div>
  <div class="mini-stat">
    <div class="mini-stat-val">{{ $tareasPendientes }}</div>
    <div class="mini-stat-label">tareas pend.</div>
  </div>
</div>

{{-- ══ FASE 3: BANNER DE FASE ACTUAL ══════════════════════════════ --}}
@if($cultivo->faseActual)
@php
  $faseActual      = $cultivo->faseActual;
  $porcentajeFenol = $cultivo->porcentajeFenologico();
@endphp
<div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;
     padding:14px 16px;margin-bottom:12px;border-left:4px solid {{ $faseActual->color_hex }};">
  <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;">
    <div>
      <div style="font-size:.75rem;color:#64748b;font-weight:600;margin-bottom:2px;">FASE ACTUAL</div>
      <div style="font-weight:800;font-size:1rem;">{{ $faseActual->icono }} {{ $faseActual->nombre }}</div>
      @if($cultivo->fecha_cambio_fase)
      <div style="font-size:.75rem;color:#64748b;">
        desde {{ \Carbon\Carbon::parse($cultivo->fecha_cambio_fase)->format('d/m/Y') }}
        · {{ \Carbon\Carbon::parse($cultivo->fecha_cambio_fase)->diffInDays(now()) }} días en esta fase
      </div>
      @endif
    </div>
    <a href="{{ route('cultivos.fenologia', $cultivo->id) }}"
       class="btn btn-sm btn-secondary" style="white-space:nowrap;">
       📅 Ver Fenología
    </a>
  </div>
  <div style="margin-top:10px;">
    <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#64748b;margin-bottom:3px;">
      <span>Progreso fenológico</span><span>{{ $porcentajeFenol }}%</span>
    </div>
    <div style="background:#e2e8f0;border-radius:50px;height:8px;overflow:hidden;">
      <div style="height:100%;background:linear-gradient(90deg,{{ $faseActual->color_hex }},{{ $faseActual->color_hex }}CC);
           border-radius:50px;width:{{ $porcentajeFenol }}%;transition:width .6s;"></div>
    </div>
  </div>
</div>

{{-- Alertas de carencia activas --}}
@php
  $alertasCarencia = \App\Models\CultivoEventoAvanzado::where('cultivo_id', $cultivo->id)
      ->where('alerta_cosecha_activa', true)
      ->where('fecha_minima_cosecha', '>=', now()->toDateString())
      ->get();
@endphp
@if($alertasCarencia->count())
<div style="background:#fde8e8;border:1px solid #f5c6cb;border-radius:10px;padding:10px 14px;margin-bottom:12px;">
  <div style="font-weight:700;color:#c0392b;font-size:.85rem;margin-bottom:4px;">⚠️ Período de carencia activo</div>
  @foreach($alertasCarencia as $alerta)
  <div style="font-size:.8rem;color:#721c24;">
    🧪 <strong>{{ $alerta->producto_nombre }}</strong>
    — No cosechar antes del <strong>{{ \Carbon\Carbon::parse($alerta->fecha_minima_cosecha)->format('d/m/Y') }}</strong>
  </div>
  @endforeach
</div>
@endif

@else
{{-- Sin fase asignada --}}
<div style="background:#edf7ed;border:1px dashed #3d8b3d;border-radius:12px;
     padding:12px 16px;margin-bottom:12px;display:flex;align-items:center;justify-content:space-between;gap:8px;">
  <div style="font-size:.85rem;color:#2d6a2d;">
    🌱 <strong>Sin fase fenológica asignada</strong><br>
    <span style="font-size:.78rem;">Asigna una fase para activar el plan de manejo automático.</span>
  </div>
  <a href="{{ route('cultivos.fenologia', $cultivo->id) }}" class="btn btn-sm btn-primary" style="white-space:nowrap;">
    Asignar fase
  </a>
</div>
@endif
{{-- ══ FIN FASE 3 ══════════════════════════════════════════════════ --}}

{{-- INFORMACIÓN DEL CULTIVO --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📋 Información</div>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">✏️ Editar</button>
  </div>
  <div class="info-row"><span class="info-label">Tipo</span><span class="info-value">{{ $em }} {{ $cultivo->tipo }}</span></div>
  <div class="info-row"><span class="info-label">Área</span><span class="info-value">{{ $cultivo->area ? $cultivo->area.' '.$cultivo->unidad : 'No especificada' }}</span></div>
  <div class="info-row"><span class="info-label">Fecha siembra</span><span class="info-value">{{ \Carbon\Carbon::parse($cultivo->fecha_siembra)->format('d/m/Y') }}</span></div>
  <div class="info-row"><span class="info-label">Estado</span><span class="info-value"><span class="badge {{ $badgeClass }}">{{ $cultivo->estado }}</span></span></div>
  @if($cultivo->notas)
  <div style="margin-top:10px;padding:10px;background:#edf7ed;border-radius:8px;font-size:.85rem;color:#2d6a2d;">
    📝 {{ $cultivo->notas }}
  </div>
  @endif
</div>

{{-- RENTABILIDAD --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">💹 Rentabilidad</div>
    <a href="{{ route('rentabilidad.detalle', $cultivo->id) }}" class="btn btn-sm btn-ghost">Ver más →</a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
    <div style="background:#edf7ed;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.75rem;color:#64748b;">Ingresos</div>
      <div style="font-weight:800;color:#2d6a2d;">${{ number_format($totalIngresos,0,',','.') }}</div>
    </div>
    <div style="background:#fef2f2;border-radius:10px;padding:12px;text-align:center;">
      <div style="font-size:.75rem;color:#64748b;">Gastos</div>
      <div style="font-weight:800;color:var(--rojo);">${{ number_format($totalGastos,0,',','.') }}</div>
    </div>
  </div>
  <div style="display:flex;justify-content:space-between;font-size:.85rem;margin-bottom:6px;">
    <span>Balance</span>
    <strong style="{{ $balance >= 0 ? 'color:#2d6a2d' : 'color:var(--rojo)' }}">
      {{ $balance >= 0 ? '+' : '-' }}${{ number_format(abs($balance),0,',','.') }}
    </strong>
  </div>
  @if($totalGastos > 0)
  <div class="rent-bar-wrap">
    <div class="rent-bar-fill" style="width:{{ min(100, ($totalIngresos/$totalGastos)*100) }}%; {{ $balance < 0 ? 'background:var(--rojo)' : '' }}"></div>
  </div>
  <div style="font-size:.72rem;color:#64748b;margin-top:4px;">{{ $totalGastos > 0 ? round(($totalIngresos/$totalGastos)*100) : 0 }}% de gastos recuperados</div>
  @endif
</div>

{{-- GALERÍA DE FOTOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📷 Galería ({{ $fotos->count() }})</div>
  </div>
  <div class="foto-grid">
    @foreach($fotos as $f)
    <div class="foto-thumb">
      <img src="{{ asset($f->ruta) }}" alt="{{ $f->titulo }}" onclick="openLightbox('{{ asset($f->ruta) }}')">
      <form method="POST" action="{{ route('cultivos.fotos.delete',[$cultivo->id,$f->id]) }}" onsubmit="return confirm('¿Eliminar foto?')">
        @csrf
        <button class="foto-thumb-del" type="submit">✕</button>
      </form>
    </div>
    @endforeach
    <label class="foto-upload-btn" for="fotoUploadInput">
      <span style="font-size:1.5rem;">📷</span>
      <span>Agregar foto</span>
    </label>
  </div>
  <form method="POST" action="{{ route('cultivos.fotos.upload',$cultivo->id) }}" enctype="multipart/form-data" id="fotoUploadForm" style="margin-top:12px;display:none;">
    @csrf
    <input type="file" id="fotoUploadInput" name="foto" accept="image/*" style="display:none;">
    <div id="fotoPreviewWrap" style="display:none;">
      <img id="fotoPreview" style="width:100%;border-radius:10px;margin-bottom:10px;max-height:200px;object-fit:cover;">
      <div class="form-group"><label>Título (opcional)</label><input type="text" name="titulo" class="form-control" placeholder="Ej: Semana 3 de crecimiento"></div>
      <div class="form-group"><label>Descripción</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones de la foto..."></textarea></div>
      <div class="flex gap-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="cancelFoto()">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">📷 Subir foto</button>
      </div>
    </div>
  </form>
</div>

{{-- GASTOS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">💰 Gastos ({{ $gastos->count() }})</div>
    <a href="{{ route('gastos.index') }}?cultivo={{ $cultivo->id }}" class="btn btn-sm btn-ghost">Ver todos →</a>
  </div>
  @if($gastos->isEmpty())
    <p style="text-align:center;color:#64748b;font-size:.85rem;padding:12px 0;">Sin gastos registrados para este cultivo.</p>
  @else
    @foreach($gastos->take(4) as $g)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;">
      <div>
        <div style="font-size:.88rem;font-weight:600;">{{ $g->descripcion }}</div>
        <div style="font-size:.75rem;color:#64748b;">{{ $g->categoria }} · {{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</div>
      </div>
      <div style="font-weight:700;color:var(--rojo);">-${{ number_format($g->valor,0,',','.') }}</div>
    </div>
    @endforeach
    @if($gastos->count() > 4)
    <div style="text-align:center;padding-top:10px;"><a href="{{ route('gastos.index') }}" class="btn btn-sm btn-ghost">Ver {{ $gastos->count()-4 }} más →</a></div>
    @endif
  @endif
</div>

{{-- TAREAS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Tareas ({{ $tareasPendientes }} pendientes)</div>
    <a href="{{ route('calendario.index') }}" class="btn btn-sm btn-ghost">Agenda →</a>
  </div>
  @if($tareas->isEmpty())
    <p style="text-align:center;color:#64748b;font-size:.85rem;padding:12px 0;">Sin tareas programadas para este cultivo.</p>
  @else
    @foreach($tareas->take(4) as $t)
    @php $pendiente = !$t->completada; @endphp
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid #e2e8f0;">
      <span style="font-size:1.1rem;">{{ $pendiente ? '⏳' : '✅' }}</span>
      <div style="flex:1;">
        <div style="font-size:.88rem;font-weight:{{ $pendiente ? '600' : '400' }};{{ $pendiente ? '' : 'text-decoration:line-through;color:#94a3b8' }}">{{ $t->titulo }}</div>
        <div style="font-size:.75rem;color:#64748b;">{{ ucfirst($t->tipo) }} · {{ \Carbon\Carbon::parse($t->fecha)->format('d/m/Y') }}</div>
      </div>
      <span class="badge {{ ['alta'=>'badge-red','media'=>'badge-orange','baja'=>'badge-blue'][$t->prioridad] ?? 'badge-blue' }}">{{ $t->prioridad }}</span>
    </div>
    @endforeach
  @endif
</div>

{{-- COSECHAS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">🌾 Cosechas ({{ $cosechas->count() }})</div>
    <a href="{{ route('cosechas.index') }}" class="btn btn-sm btn-ghost">Ver todas →</a>
  </div>
  @if($cosechas->isEmpty())
    <p style="text-align:center;color:#64748b;font-size:.85rem;padding:12px 0;">
      Aún no hay cosechas registradas.
      @if($cultivo->estado === 'activo')
        <br><span style="font-size:.75rem;">Cuando coseches, cambia el estado a "cosechado" y regístrala.</span>
      @endif
    </p>
  @else
    @foreach($cosechas as $co)
    <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;">
      <div>
        <div style="font-size:.88rem;font-weight:600;">{{ $co->producto }}</div>
        <div style="font-size:.75rem;color:#64748b;">{{ \Carbon\Carbon::parse($co->fecha_cosecha)->format('d/m/Y') }} · {{ ucfirst($co->calidad) }}</div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:700;color:#2d6a2d;">{{ $co->cantidad }} {{ $co->unidad }}</div>
        @if($co->valor_estimado)<div style="font-size:.75rem;color:#64748b;">${{ number_format($co->valor_estimado,0,',','.') }}</div>@endif
      </div>
    </div>
    @endforeach
  @endif
</div>

{{-- INVENTARIO / INSUMOS --}}
@if($movimientos->count())
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📦 Insumos usados ({{ $movimientos->count() }})</div>
    <a href="{{ route('inventario.index') }}" class="btn btn-sm btn-ghost">Inventario →</a>
  </div>
  @foreach($movimientos->take(5) as $m)
  <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #e2e8f0;">
    <div>
      <div style="font-size:.88rem;font-weight:600;">{{ $m->insumo_nombre }}</div>
      <div style="font-size:.75rem;color:#64748b;">{{ $m->motivo ?? 'Sin descripción' }} · {{ \Carbon\Carbon::parse($m->fecha)->format('d/m/Y') }}</div>
    </div>
    <div style="font-weight:600;color:var(--rojo);">-{{ $m->cantidad }} {{ $m->insumo_unidad }}</div>
  </div>
  @endforeach
</div>
@endif

{{-- LÍNEA DE TIEMPO --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📜 Línea de tiempo</div>
    <div style="display:flex;gap:6px;">
      <button onclick="openModal('modalEventoAvanzadoDetalle')" class="btn btn-sm btn-secondary">🧪 Aplicación</button>
      <button onclick="openModal('modalEvento')" class="btn btn-sm btn-primary">+ Evento</button>
    </div>
  </div>

  @if($timeline->isEmpty())
    <p style="text-align:center;color:#64748b;font-size:.85rem;padding:12px 0;">La línea de tiempo se irá llenando con tus gastos, tareas y eventos.</p>
  @else
  <div class="timeline">
    @foreach($timeline as $item)
    @php $dotClass = 'tipo-'.str_replace('_','-',$item['tipo'] ?? 'nota'); @endphp
    <div class="tl-item">
      <div class="tl-dot {{ $dotClass }}"></div>
      <div class="tl-body">
        <div class="tl-title">{{ $item['titulo'] }}</div>
        @if($item['descripcion'])<div class="tl-desc">{{ $item['descripcion'] }}</div>@endif
        <div class="tl-date">📅 {{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y') }}</div>
        @if($item['foto'])<img class="tl-img" src="{{ asset($item['foto']) }}" onclick="openLightbox('{{ asset($item['foto']) }}')">@endif
        @if($item['origen'] === 'evento')
        <form method="POST" action="{{ route('cultivos.eventos.delete',[$cultivo->id,$item['id']]) }}" onsubmit="return confirm('¿Eliminar evento?')" style="margin-top:6px;">
          @csrf
          <button class="btn btn-sm btn-ghost" style="font-size:.72rem;padding:2px 8px;color:#94a3b8;">✕ Eliminar</button>
        </form>
        @endif
      </div>
    </div>
    @endforeach
  </div>
  @endif
</div>

<div style="margin-bottom:80px;"></div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- MODALES                                                        --}}
{{-- ══════════════════════════════════════════════════════════════ --}}

{{-- Modal Editar --}}
<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">✏️ Editar cultivo</h3>
    <form method="POST" action="{{ route('cultivos.update',$cultivo->id) }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="back" value="detalle">
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required>
          @foreach($tiposCultivo as $t)<option {{ $cultivo->tipo===$t?'selected':'' }}>{{ $t }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ $cultivo->nombre }}"></div>
      <div class="grid-2">
        <div class="form-group"><label>Fecha siembra</label><input type="date" name="fecha_siembra" class="form-control" value="{{ $cultivo->fecha_siembra }}"></div>
        <div class="form-group"><label>Estado</label>
          <select name="estado" class="form-control">
            <option {{ $cultivo->estado==='activo'?'selected':'' }} value="activo">Activo</option>
            <option {{ $cultivo->estado==='cosechado'?'selected':'' }} value="cosechado">Cosechado</option>
            <option {{ $cultivo->estado==='vendido'?'selected':'' }} value="vendido">Vendido</option>
          </select>
        </div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Área</label><input type="number" step="0.1" name="area" class="form-control" value="{{ $cultivo->area }}"></div>
        <div class="form-group"><label>Unidad</label>
          <select name="unidad" class="form-control">
            <option {{ $cultivo->unidad==='hectareas'?'selected':'' }} value="hectareas">Hectáreas</option>
            <option {{ $cultivo->unidad==='metros2'?'selected':'' }} value="metros2">m²</option>
            <option {{ $cultivo->unidad==='fanegadas'?'selected':'' }} value="fanegadas">Fanegadas</option>
          </select>
        </div>
      </div>
      <div class="form-group"><label>Cambiar foto principal</label><input type="file" name="imagen" class="form-control" accept="image/*"></div>
      <div class="form-group"><label>Notas</label><textarea name="notas" class="form-control">{{ $cultivo->notas }}</textarea></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

{{-- Modal Nuevo Evento --}}
<div class="modal-overlay" id="modalEvento" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">📝 Registrar evento</h3>
    <form method="POST" action="{{ route('cultivos.eventos.store',$cultivo->id) }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group"><label>Tipo de evento *</label>
        <select name="tipo" class="form-control" required>
          <option value="nota">📝 Nota</option>
          <option value="aplicacion">🧪 Aplicación (fertilizante/plaguicida)</option>
          <option value="riego">💧 Riego</option>
          <option value="poda">✂️ Poda</option>
          <option value="otro">🔧 Otro</option>
        </select>
      </div>
      <div class="form-group"><label>Descripción *</label><input type="text" name="titulo" class="form-control" required placeholder="Ej: Aplicación de urea 46%"></div>
      <div class="form-group"><label>Detalle</label><textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones adicionales..."></textarea></div>
      <div class="form-group"><label>Fecha *</label><input type="date" name="fecha" class="form-control" value="{{ date('Y-m-d') }}" required></div>
      @if(isset($personas) && $personas->count())
      <div class="form-group"><label>Realizado por (opcional)</label>
        <select name="persona_id" class="form-control">
          <option value="">Sin asignar</option>
          @foreach($personas as $per)
            <option value="{{ $per->id }}">{{ $per->nombre }}{{ $per->cargo ? ' - '.$per->cargo : '' }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div class="form-group"><label>Foto del evento (opcional)</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEvento')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar evento</button>
      </div>
    </form>
  </div>
</div>

{{-- ── FASE 3: Modal Evento Avanzado ────────────────────────────── --}}
<div id="modalEventoAvanzadoDetalle" class="modal-overlay" style="display:none;">
  <div class="modal-box">
    <div class="modal-title">🧪 Registrar Aplicación / Manejo</div>
    <form method="POST" action="{{ route('cultivos.eventos-avanzados.store', $cultivo->id) }}" enctype="multipart/form-data">
      @csrf

      {{-- Selector de tipo --}}
      <div class="form-group">
        <label style="display:block;margin-bottom:8px;">Tipo de evento</label>
        <div class="tab-group" id="tabGroupDetalle">
          <button type="button" class="tab-btn active"
                  onclick="selectTipoDetalle('aplicacion_agroquimico',this)">🧪 Agroquímico</button>
          <button type="button" class="tab-btn"
                  onclick="selectTipoDetalle('riego',this)">💧 Riego</button>
          <button type="button" class="tab-btn"
                  onclick="selectTipoDetalle('fertilizacion',this)">🌿 Fertilización</button>
          <button type="button" class="tab-btn"
                  onclick="selectTipoDetalle('control_fitosanitario',this)">🐛 Fitosanitario</button>
          <button type="button" class="tab-btn"
                  onclick="selectTipoDetalle('deshierbe',this)">🪚 Deshierbe</button>
        </div>
        <input type="hidden" name="tipo" id="tipo_hidden_detalle" value="aplicacion_agroquimico">
      </div>

      {{-- Campos comunes --}}
      <div class="form-row">
        <div class="form-group">
          <label>Título *</label>
          <input type="text" name="titulo" class="form-control" required placeholder="Ej: Aplicación Roundup">
        </div>
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" value="{{ now()->toDateString() }}" required>
        </div>
      </div>

      {{-- Agroquímico --}}
      <div id="det_fields_aplicacion_agroquimico" class="fields-group active">
        <div class="form-row">
          <div class="form-group">
            <label>Producto *</label>
            <input type="text" name="producto_nombre" class="form-control" placeholder="Nombre comercial">
          </div>
          <div class="form-group">
            <label>Registro ICA</label>
            <input type="text" name="producto_registro_ica" class="form-control" placeholder="ICA-XXXXXX">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Dosis</label>
            <input type="number" name="dosis" class="form-control" step="0.0001" min="0" placeholder="0.00">
          </div>
          <div class="form-group">
            <label>Unidad</label>
            <select name="dosis_unidad" class="form-control">
              <option value="cc/L">cc/L</option>
              <option value="g/L">g/L</option>
              <option value="L/ha">L/ha</option>
              <option value="kg/ha">kg/ha</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>⏱ Período de carencia (días)</label>
          <input type="number" name="periodo_carencia_dias" class="form-control" min="0" placeholder="Ej: 14">
          <div style="font-size:.73rem;color:#64748b;margin-top:3px;">La fecha mínima de cosecha se calcula automáticamente.</div>
        </div>
      </div>

      {{-- Riego --}}
      <div id="det_fields_riego" class="fields-group">
        <div class="form-row">
          <div class="form-group"><label>Volumen (L)</label><input type="number" name="volumen_agua_litros" class="form-control" step="0.01" min="0" placeholder="0"></div>
          <div class="form-group"><label>Duración (min)</label><input type="number" name="duracion_minutos" class="form-control" min="0" placeholder="0"></div>
        </div>
        <div class="form-group">
          <label>Método</label>
          <select name="metodo_riego" class="form-control">
            <option value="manual">Manual</option>
            <option value="goteo">Goteo</option>
            <option value="aspersion">Aspersión</option>
            <option value="surco">Surco</option>
            <option value="otro">Otro</option>
          </select>
        </div>
      </div>

      {{-- Fertilización --}}
      <div id="det_fields_fertilizacion" class="fields-group">
        <div class="form-row">
          <div class="form-group"><label>Fuente / Producto</label><input type="text" name="fuente_fertilizante" class="form-control" placeholder="Urea 46%"></div>
          <div class="form-group">
            <label>Método</label>
            <select name="metodo_aplicacion_fertilizante" class="form-control">
              <option value="edafico">Edáfico (suelo)</option>
              <option value="foliar">Foliar</option>
              <option value="fertiriego">Fertiriego</option>
            </select>
          </div>
        </div>
        <div class="form-row" style="grid-template-columns:1fr 1fr 1fr;">
          <div class="form-group"><label>N (kg/ha)</label><input type="number" name="nitrogeno_n" class="form-control" step="0.01" min="0" placeholder="0"></div>
          <div class="form-group"><label>P (kg/ha)</label><input type="number" name="fosforo_p" class="form-control" step="0.01" min="0" placeholder="0"></div>
          <div class="form-group"><label>K (kg/ha)</label><input type="number" name="potasio_k" class="form-control" step="0.01" min="0" placeholder="0"></div>
        </div>
      </div>

      {{-- Control fitosanitario --}}
      <div id="det_fields_control_fitosanitario" class="fields-group">
        <div class="form-row">
          <div class="form-group"><label>Plaga / Enfermedad</label><input type="text" name="plaga_enfermedad" class="form-control" placeholder="Ej: Trips"></div>
          <div class="form-group">
            <label>Severidad</label>
            <select name="nivel_severidad" class="form-control">
              <option value="bajo">Bajo</option>
              <option value="medio" selected>Medio</option>
              <option value="alto">Alto</option>
              <option value="critico">Crítico</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group"><label>Producto</label><input type="text" name="producto_nombre" class="form-control" placeholder="Nombre del producto"></div>
          <div class="form-group"><label>Dosis</label><input type="number" name="dosis" class="form-control" step="0.001" min="0" placeholder="0"></div>
        </div>
      </div>

      {{-- Deshierbe --}}
      <div id="det_fields_deshierbe" class="fields-group">
        <div class="form-row">
          <div class="form-group">
            <label>Método</label>
            <select name="metodo_deshierbe" class="form-control">
              <option value="manual">Manual</option>
              <option value="mecanico">Mecánico</option>
              <option value="quimico">Químico</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div class="form-group"><label>Área (ha)</label><input type="number" name="area_deshierbada_ha" class="form-control" step="0.0001" min="0" placeholder="0.00"></div>
        </div>
      </div>

      {{-- Finales comunes --}}
      <div class="form-group">
        <label>Descripción / Observaciones</label>
        <textarea name="descripcion" class="form-control" rows="2" placeholder="Observaciones adicionales…"></textarea>
      </div>
      <div class="form-group">
        <label>Foto (opcional)</label>
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalEventoAvanzadoDetalle')"
                class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- Lightbox --}}
<div class="lightbox" id="lightbox" onclick="closeLightbox()">
  <button class="lightbox-close" onclick="closeLightbox()">✕</button>
  <img id="lightboxImg" src="" alt="">
</div>

{{-- ══════════════════════════════════════════════════════════════ --}}
{{-- UN SOLO @push('scripts') — todo el JS aquí                    --}}
{{-- ══════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
// ── Modales ────────────────────────────────────────────────────────
function openModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'flex';
  m.classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeModal(id) {
  var m = document.getElementById(id);
  if (!m) return;
  m.style.display = 'none';
  m.classList.remove('open');
  document.body.style.overflow = '';
}
document.querySelectorAll('.modal-overlay').forEach(function(m) {
  m.addEventListener('click', function(e) {
    if (e.target === this) closeModal(this.id);
  });
});

// ── Lightbox ───────────────────────────────────────────────────────
function openLightbox(src) {
  document.getElementById('lightboxImg').src = src;
  document.getElementById('lightbox').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
  document.body.style.overflow = '';
}

// ── Foto upload preview ────────────────────────────────────────────
document.getElementById('fotoUploadInput').addEventListener('change', function() {
  handleFotoSelect(this);
});
function handleFotoSelect(input) {
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('fotoPreview').src = e.target.result;
      document.getElementById('fotoPreviewWrap').style.display = 'block';
      document.getElementById('fotoUploadForm').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
function cancelFoto() {
  document.getElementById('fotoUploadInput').value = '';
  document.getElementById('fotoPreviewWrap').style.display = 'none';
  document.getElementById('fotoUploadForm').style.display = 'none';
}

// ── FASE 3: Selector de tipo en modal evento avanzado ─────────────
function selectTipoDetalle(tipo, btn) {
  // Actualizar hidden input
  document.getElementById('tipo_hidden_detalle').value = tipo;
  // Tabs: quitar active de todos, poner en el clickeado
  document.querySelectorAll('#tabGroupDetalle .tab-btn').forEach(function(b) {
    b.classList.remove('active');
  });
  btn.classList.add('active');
  // Campos: ocultar todos, mostrar el del tipo seleccionado
  document.querySelectorAll('[id^="det_fields_"]').forEach(function(fg) {
    fg.classList.remove('active');
  });
  var el = document.getElementById('det_fields_' + tipo);
  if (el) el.classList.add('active');
}
</script>
@endpush

@endsection