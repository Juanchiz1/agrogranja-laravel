@extends('layouts.app')
@section('title','Personas')
@section('page_title','👥 Personas')
@section('back_url', route('dashboard'))

@push('head')
<style>
.tabs-personas { display:flex; gap:5px; margin-bottom:16px; overflow-x:auto; padding-bottom:2px; }
.tab-pers { flex-shrink:0; padding:6px 12px; border-radius:99px; border:1.5px solid var(--border);
  background:var(--surface); font-size:.8rem; font-weight:600; color:var(--text-secondary);
  white-space:nowrap; text-decoration:none; display:inline-block; }
.tab-pers.active { background:var(--verde-dark); color:#fff; border-color:var(--verde-dark); }
.persona-card { background:var(--surface); border-radius:var(--radius-lg); padding:14px;
  margin-bottom:10px; box-shadow:var(--shadow-sm); cursor:pointer; }
.persona-avatar { width:44px; height:44px; border-radius:50%; object-fit:cover;
  flex-shrink:0; background:var(--verde-bg); display:flex; align-items:center;
  justify-content:center; font-size:1.3rem; overflow:hidden; }
.tipo-badge { display:inline-flex; align-items:center; gap:3px; font-size:.7rem;
  padding:2px 8px; border-radius:99px; font-weight:600; }
.tipo-trabajador { background:#fff7ed; color:#9a3412; }
.tipo-proveedor  { background:var(--verde-bg); color:var(--verde-dark); }
.tipo-comprador  { background:#eff6ff; color:#1d4ed8; }
.tipo-vecino     { background:#f9fafb; color:#4b5563; }
.tipo-familiar   { background:#fdf4ff; color:#7e22ce; }
.tipo-contacto,.tipo-otro { background:#f1f5f9; color:#475569; }
</style>
@endpush

@section('content')

{{-- AYUDA --}}
<div style="text-align:right;margin-bottom:12px;">
  <button onclick="openModal('modalAyudaPersonas')" class="btn btn-ghost" style="font-size:.85rem;gap:6px;display:inline-flex;align-items:center;">
    <span>❓</span> ¿Cómo funciona?
  </button>
</div>

{{-- STATS --}}
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:16px;">
  <div class="stat-card"><div class="stat-value text-green">{{ $statsPorTipo->sum() }}</div><div class="stat-label">Total</div></div>
  <div class="stat-card"><div class="stat-value" style="color:#9a3412;">{{ $statsPorTipo['trabajador']??0 }}</div><div class="stat-label">Trabajadores</div></div>
  <div class="stat-card" style="background:var(--verde-bg)"><div class="stat-value text-green">${{ number_format($pagadoMes/1000,1) }}k</div><div class="stat-label">Pagado mes</div></div>
  <div class="stat-card"><div class="stat-value">{{ ($statsPorTipo['proveedor']??0) + ($statsPorTipo['comprador']??0) }}</div><div class="stat-label">Comerciales</div></div>
</div>

{{-- TABS POR TIPO --}}
<div class="tabs-personas">
  <a href="?tab=todos"      class="tab-pers {{ $tab==='todos'      ?'active':'' }}">👥 Todos ({{ $statsPorTipo->sum() }})</a>
  <a href="?tab=trabajador" class="tab-pers {{ $tab==='trabajador' ?'active':'' }}">👷 Trabajadores ({{ $statsPorTipo['trabajador']??0 }})</a>
  <a href="?tab=proveedor"  class="tab-pers {{ $tab==='proveedor'  ?'active':'' }}">🏪 Proveedores ({{ $statsPorTipo['proveedor']??0 }})</a>
  <a href="?tab=comprador"  class="tab-pers {{ $tab==='comprador'  ?'active':'' }}">🛒 Compradores ({{ $statsPorTipo['comprador']??0 }})</a>
  <a href="?tab=vecino"     class="tab-pers {{ $tab==='vecino'     ?'active':'' }}">🏘️ Vecinos ({{ $statsPorTipo['vecino']??0 }})</a>
  <a href="?tab=familiar"   class="tab-pers {{ $tab==='familiar'   ?'active':'' }}">👨‍👩‍👧 Familia ({{ $statsPorTipo['familiar']??0 }})</a>
</div>

{{-- BÚSQUEDA --}}
<form method="GET" class="flex gap-2 mb-3">
  <input type="hidden" name="tab" value="{{ $tab }}">
  <div class="search-box" style="flex:1;">
    <span class="search-icon">🔍</span>
    <input type="text" name="q" class="form-control" placeholder="Buscar persona..." value="{{ request('q') }}" style="padding-left:34px;">
  </div>
  <button type="submit" class="btn btn-secondary">🔍</button>
</form>

{{-- ÚLTIMAS LABORES (solo tab todos) --}}
@if($tab === 'todos' && $ultimasLabores->count())
<div style="background:var(--surface);border-radius:var(--radius-lg);padding:12px 14px;margin-bottom:16px;box-shadow:var(--shadow-sm);">
  <p style="font-weight:700;font-size:.85rem;margin-bottom:10px;">🔨 Últimas actividades</p>
  @foreach($ultimasLabores as $ul)
  <div style="display:flex;align-items:center;gap:10px;padding:6px 0;border-bottom:1px solid var(--border);">
    <span style="font-size:.9rem;">🔨</span>
    <div style="flex:1;">
      <div style="font-size:.85rem;font-weight:600;">{{ $ul->persona_nombre }}</div>
      <div style="font-size:.75rem;color:var(--text-secondary);">{{ $ul->descripcion }}@if($ul->cultivo_nombre) · 🌱 {{ $ul->cultivo_nombre }}@endif</div>
    </div>
    <div style="font-size:.72rem;color:var(--text-muted);">{{ \Carbon\Carbon::parse($ul->fecha)->format('d/m') }}</div>
  </div>
  @endforeach
</div>
@endif

{{-- LISTADO --}}
@if($personas->isEmpty())
<div class="empty-state"><div class="emoji">👥</div><p><strong>Sin personas registradas.</strong></p><p>Toca + para agregar la primera.</p></div>
@else
@foreach($personas as $p)
@php
  $tipoInfo = $tipos[$p->tipo] ?? ['label'=>ucfirst($p->tipo),'emoji'=>'👤'];
  $tipoClass = 'tipo-'.($p->tipo ?? 'contacto');
@endphp
<div class="persona-card" onclick="window.location='{{ route('personas.show',$p->id) }}'">
  <div class="flex gap-3 items-center">
    {{-- Avatar --}}
    <div class="persona-avatar">
      @if($p->foto)
        <img src="{{ asset($p->foto) }}" style="width:100%;height:100%;object-fit:cover;">
      @else
        {{ mb_strtoupper(mb_substr($p->nombre,0,1)) }}
      @endif
    </div>

    <div style="flex:1;min-width:0;">
      <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
        <span style="font-weight:700;font-size:.9rem;">{{ $p->nombre }}</span>
        @if($p->favorito) <span>⭐</span>@endif
        <span class="tipo-badge {{ $tipoClass }}">{{ $tipoInfo['emoji'] }} {{ $tipoInfo['label'] }}</span>
      </div>
      <div style="font-size:.76rem;color:var(--text-secondary);margin-top:2px;">
        @if($p->cargo) {{ $p->cargo }} · @endif
        @if($p->telefono) 📞 {{ $p->telefono }}@endif
      </div>
      @if($p->tipo === 'trabajador' && $p->valor_jornal)
        <div style="font-size:.73rem;color:var(--text-muted);">
          💰 ${{ number_format($p->valor_jornal,0,',','.') }}/{{ ['jornal'=>'día','mensual'=>'mes','destajo'=>'destajo'][$p->tipo_contrato??'jornal']??'día' }}
        </div>
      @endif
    </div>

    <div class="flex gap-1 items-center" onclick="event.stopPropagation()">
      <a href="{{ route('personas.show',$p->id) }}" class="btn btn-sm btn-secondary btn-icon">👁️</a>
      <button onclick="openModal('editPersona{{ $p->id }}')" class="btn btn-sm btn-secondary btn-icon">✏️</button>
      <form method="POST" action="{{ route('personas.destroy',$p->id) }}" onsubmit="return confirm('¿Eliminar?')">@csrf
        <button class="btn btn-sm btn-danger btn-icon">🗑️</button>
      </form>
    </div>
  </div>
</div>

{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="editPersona{{ $p->id }}" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">✏️ Editar persona</h3>
    <form method="POST" action="{{ route('personas.update',$p->id) }}" enctype="multipart/form-data">@csrf
      <input type="hidden" name="back" value="list">
      <div class="grid-2">
        <div class="form-group"><label>Tipo *</label>
          <select name="tipo" class="form-control" required>
            @foreach($tipos as $k=>$ti)<option value="{{ $k }}" {{ $p->tipo===$k?'selected':'' }}>{{ $ti['emoji'] }} {{ $ti['label'] }}</option>@endforeach
          </select>
        </div>
        <div class="form-group"><label>Nombre *</label><input type="text" name="nombre" class="form-control" required value="{{ $p->nombre }}"></div>
      </div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" value="{{ $p->telefono }}"></div>
        <div class="form-group"><label>Documento</label><input type="text" name="documento" class="form-control" value="{{ $p->documento }}"></div>
      </div>
      <div class="form-group"><label>Cargo / rol</label><input type="text" name="cargo" class="form-control" value="{{ $p->cargo }}" placeholder="Jornalero, Ordeñador..."></div>
      <div class="grid-2">
        <div class="form-group"><label>Tipo contrato</label>
          <select name="tipo_contrato" class="form-control">
            <option value="">N/A</option>
            <option {{ ($p->tipo_contrato??'')==='jornal'?'selected':'' }} value="jornal">Jornal/Día</option>
            <option {{ ($p->tipo_contrato??'')==='mensual'?'selected':'' }} value="mensual">Mensual</option>
            <option {{ ($p->tipo_contrato??'')==='destajo'?'selected':'' }} value="destajo">Destajo</option>
          </select>
        </div>
        <div class="form-group"><label>Valor jornal/día</label><input type="number" step="1000" name="valor_jornal" class="form-control" value="{{ $p->valor_jornal }}"></div>
      </div>
      <div class="form-group"><label>Labores que realiza</label><input type="text" name="labores" class="form-control" value="{{ $p->labores }}" placeholder="Siembra, fumigación, ordeño..."></div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('editPersona{{ $p->id }}')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Actualizar</button>
      </div>
    </form>
  </div>
</div>
@endforeach
@endif

{{-- MODAL NUEVA PERSONA --}}
<div class="modal-overlay" id="modalNuevaPersona" style="display:none;">
  <div class="modal-sheet"><div class="modal-handle"></div><h3 class="modal-title">👥 Nueva persona</h3>
    <form method="POST" action="{{ route('personas.store') }}" enctype="multipart/form-data">@csrf
      <div class="form-group"><label>Tipo *</label>
        <select name="tipo" class="form-control" required id="tipoNuevoP" onchange="toggleCamposLaborales()">
          @foreach($tipos as $k=>$ti)<option value="{{ $k }}">{{ $ti['emoji'] }} {{ $ti['label'] }}</option>@endforeach
        </select>
      </div>
      <div class="form-group"><label>Nombre completo *</label><input type="text" name="nombre" class="form-control" required placeholder="Nombre de la persona"></div>
      <div class="grid-2">
        <div class="form-group"><label>Teléfono</label><input type="tel" name="telefono" class="form-control" placeholder="3001234567"></div>
        <div class="form-group"><label>Documento (cédula/NIT)</label><input type="text" name="documento" class="form-control" placeholder="Número"></div>
      </div>
      <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" placeholder="Opcional"></div>
      <div class="form-group"><label>Dirección</label><input type="text" name="direccion" class="form-control" placeholder="Opcional"></div>

      {{-- Campos solo para trabajadores --}}
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

{{-- MODAL AYUDA --}}
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

@push('scripts')
<script>
function toggleCamposLaborales() {
  const tipo = document.getElementById('tipoNuevoP').value;
  document.getElementById('camposLaborales').style.display = tipo === 'trabajador' ? 'block' : 'none';
}
</script>
@endpush
@endsection