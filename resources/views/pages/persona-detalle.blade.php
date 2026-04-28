@extends('layouts.app')
@section('title', $persona->nombre)
@section('page_title', ($tipos[$persona->tipo]['emoji']??'👤').' '.$persona->nombre)
@section('back_url', route('personas.index'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/personas.css') }}">
@endpush

@section('content')
@php
  $tipoInfo = $tipos[$persona->tipo] ?? ['label'=>ucfirst($persona->tipo),'emoji'=>'👤'];
  $esTrabajador = $persona->tipo === 'trabajador';
@endphp

{{-- HEADER --}}
<div style="text-align:center;padding:16px 0 20px;">
  @if($persona->foto)
    <img src="{{ asset($persona->foto) }}" class="pers-avatar-lg" style="overflow:hidden;">
  @else
    <div class="pers-avatar-lg">{{ mb_strtoupper(mb_substr($persona->nombre,0,1)) }}</div>
  @endif
  <div style="font-size:.8rem;font-weight:600;color:var(--text-secondary);">{{ $tipoInfo['emoji'] }} {{ $tipoInfo['label'] }}</div>
  @if($persona->cargo)
    <div style="font-size:.85rem;color:var(--verde-dark);font-weight:600;margin-top:4px;">{{ $persona->cargo }}</div>
  @endif
</div>

{{-- MINI STATS trabajadores --}}
@if($esTrabajador)
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:16px;">
  <div class="stat-card">
    <div class="stat-value text-green" style="font-size:1rem;">${{ number_format($totalPagadoMes,0,',','.') }}</div>
    <div class="stat-label">Pagado mes</div>
  </div>
  <div class="stat-card">
    <div class="stat-value text-green">{{ $totalDias }}</div>
    <div class="stat-label">Dias trab.</div>
  </div>
  <div class="stat-card">
    <div class="stat-value" style="font-size:1rem;">${{ number_format($totalPagado,0,',','.') }}</div>
    <div class="stat-label">Total pagado</div>
  </div>
</div>
@endif

{{-- INFO --}}
<div class="section-card">
  <div class="section-title-row">
    <span>Informacion</span>
    <button onclick="openModal('modalEditar')" class="btn btn-sm btn-secondary">Editar</button>
  </div>

  @if($persona->telefono)
  <div class="info-row">
    <span class="info-label">Telefono</span>
    <a href="tel:{{ $persona->telefono }}" style="font-weight:600;color:var(--verde-dark);">{{ $persona->telefono }}</a>
  </div>
  @endif

  @if($persona->email)
  <div class="info-row">
    <span class="info-label">Email</span>
    <span style="font-weight:600;">{{ $persona->email }}</span>
  </div>
  @endif

  @if($persona->documento)
  <div class="info-row">
    <span class="info-label">Documento</span>
    <span style="font-weight:600;">{{ $persona->documento }}</span>
  </div>
  @endif

  @if($persona->direccion)
  <div class="info-row">
    <span class="info-label">Direccion</span>
    <span style="font-weight:600;">{{ $persona->direccion }}</span>
  </div>
  @endif

  @if($esTrabajador)

    @if($persona->tipo_contrato)
    <div class="info-row">
      <span class="info-label">Contrato</span>
      <span style="font-weight:600;">{{ ucfirst($persona->tipo_contrato) }}</span>
    </div>
    @endif

    @if($persona->valor_jornal)
    <div class="info-row">
      <span class="info-label">Valor jornal</span>
      <span style="font-weight:600;color:var(--verde-dark);">${{ number_format($persona->valor_jornal,0,',','.') }}/dia</span>
    </div>
    @endif

    @if($persona->valor_mensual)
    <div class="info-row">
      <span class="info-label">Salario mensual</span>
      <span style="font-weight:600;color:var(--verde-dark);">${{ number_format($persona->valor_mensual,0,',','.') }}</span>
    </div>
    @endif

    @if($persona->labores)
    <div class="info-row">
      <span class="info-label">Labores</span>
      <span style="font-weight:600;">{{ $persona->labores }}</span>
    </div>
    @endif

    @if($persona->fecha_ingreso)
    <div class="info-row">
      <span class="info-label">Ingreso</span>
      <span style="font-weight:600;">{{ \Carbon\Carbon::parse($persona->fecha_ingreso)->format('d/m/Y') }}</span>
    </div>
    @endif

  @endif

  @if($persona->notas)
  <div style="margin-top:10px;padding:8px 10px;background:var(--verde-bg);border-radius:8px;font-size:.83rem;color:var(--verde-dark);">
    {{ $persona->notas }}
  </div>
  @endif

  <div class="flex gap-2 mt-3">
    <form method="POST" action="{{ route('personas.favorito',$persona->id) }}">
      @csrf
      <button class="btn btn-sm btn-ghost">
        @if($persona->favorito) Quitar favorito @else Marcar favorito @endif
      </button>
    </form>
  </div>
</div>

{{-- PAGOS --}}
@if($esTrabajador)
<div class="section-card">
  <div class="section-title-row">
    <span>Pagos ({{ $pagos->count() }})</span>
    <button onclick="openModal('modalPago')" class="btn btn-sm btn-primary">+ Pago</button>
  </div>

  @if($pagos->isEmpty())
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin pagos registrados.</p>
  @else
    @foreach($pagos->take(8) as $pg)
    @php
      $pgConcepto = $pg->concepto ?? ucfirst($pg->tipo_pago);
      $pgFecha    = \Carbon\Carbon::parse($pg->fecha)->format('d/m/Y');
      $pgMeta     = $pgFecha;
      if($pg->dias)          $pgMeta .= ' - '.$pg->dias.' dias';
      if($pg->cultivo_nombre) $pgMeta .= ' - '.$pg->cultivo_nombre;
    @endphp
    <div class="pago-row">
      <div style="width:34px;height:34px;border-radius:50%;background:var(--verde-bg);display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0;">$</div>
      <div style="flex:1;">
        <div style="font-size:.87rem;font-weight:600;">{{ $pgConcepto }}</div>
        <div style="font-size:.75rem;color:var(--text-secondary);">{{ $pgMeta }}</div>
      </div>
      <div style="text-align:right;">
        <div style="font-weight:800;color:var(--verde-dark);">${{ number_format($pg->valor,0,',','.') }}</div>
        <form method="POST" action="{{ route('personas.pago.delete',[$persona->id,$pg->id]) }}" onsubmit="return confirm('Eliminar?')">
          @csrf
          <button type="submit" class="btn btn-sm btn-ghost" style="font-size:.7rem;padding:1px 6px;color:var(--text-muted);">x Eliminar</button>
        </form>
      </div>
    </div>
    @endforeach
  @endif
</div>
@endif

{{-- LABORES --}}
<div class="section-card">
  <div class="section-title-row">
    <span>Registro de labores ({{ $labores->count() }})</span>
    <button onclick="openModal('modalLabor')" class="btn btn-sm btn-primary">+ Labor</button>
  </div>

  @if($labores->isEmpty())
    <p style="text-align:center;color:var(--text-secondary);font-size:.85rem;padding:10px 0;">Sin labores registradas.</p>
  @else
    @foreach($labores->take(10) as $lb)
    @php
      $lbMeta = \Carbon\Carbon::parse($lb->fecha)->format('d/m/Y');
      if($lb->horas)          $lbMeta .= ' - '.$lb->horas.'h';
      if($lb->cultivo_nombre)  $lbMeta .= ' - '.$lb->cultivo_nombre;
      if($lb->animal_nombre)   $lbMeta .= ' - '.$lb->animal_nombre;
    @endphp
    <div class="labor-row">
      <div style="flex:1;">
        <div style="font-size:.87rem;font-weight:600;">{{ $lb->descripcion }}</div>
        <div style="font-size:.75rem;color:var(--text-secondary);margin-top:2px;">{{ $lbMeta }}</div>
        @if($lb->insumos_usados)
        <div style="font-size:.73rem;color:var(--text-muted);">Insumos: {{ $lb->insumos_usados }}</div>
        @endif
      </div>
      <form method="POST" action="{{ route('personas.labor.delete',[$persona->id,$lb->id]) }}" onsubmit="return confirm('Eliminar?')">
        @csrf
        <button type="submit" class="btn btn-sm btn-ghost" style="font-size:.7rem;color:var(--text-muted);">x</button>
      </form>
    </div>
    @endforeach
  @endif
</div>

<div style="margin-bottom:80px;"></div>

{{-- MODAL EDITAR --}}
<div class="modal-overlay" id="modalEditar" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Editar persona</h3>
    <form method="POST" action="{{ route('personas.update',$persona->id) }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="back" value="detalle">
      <div class="form-group">
        <label>Tipo *</label>
        <select name="tipo" class="form-control" required>
          @foreach($tipos as $k => $ti)
            <option value="{{ $k }}" {{ $persona->tipo===$k ? 'selected' : '' }}>{{ $ti['emoji'] }} {{ $ti['label'] }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" class="form-control" required value="{{ $persona->nombre }}">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Telefono</label>
          <input type="tel" name="telefono" class="form-control" value="{{ $persona->telefono }}">
        </div>
        <div class="form-group">
          <label>Documento</label>
          <input type="text" name="documento" class="form-control" value="{{ $persona->documento }}">
        </div>
      </div>
      <div class="form-group">
        <label>Cargo</label>
        <input type="text" name="cargo" class="form-control" value="{{ $persona->cargo }}" placeholder="Jornalero, Ordeñador...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Tipo contrato</label>
          <select name="tipo_contrato" class="form-control">
            <option value="">N/A</option>
            <option value="jornal"   {{ ($persona->tipo_contrato??'')==='jornal'   ? 'selected' : '' }}>Jornal/Dia</option>
            <option value="mensual"  {{ ($persona->tipo_contrato??'')==='mensual'  ? 'selected' : '' }}>Mensual</option>
            <option value="destajo"  {{ ($persona->tipo_contrato??'')==='destajo'  ? 'selected' : '' }}>Destajo</option>
          </select>
        </div>
        <div class="form-group">
          <label>Valor jornal</label>
          <input type="number" step="1000" name="valor_jornal" class="form-control" value="{{ $persona->valor_jornal }}">
        </div>
      </div>
      <div class="form-group">
        <label>Salario mensual</label>
        <input type="number" step="10000" name="valor_mensual" class="form-control" value="{{ $persona->valor_mensual }}">
      </div>
      <div class="form-group">
        <label>Labores</label>
        <input type="text" name="labores" class="form-control" value="{{ $persona->labores }}" placeholder="Siembra, fumigacion...">
      </div>
      <div class="form-group">
        <label>Foto</label>
        @if($persona->foto)
          <img src="{{ asset($persona->foto) }}" style="width:60px;border-radius:50%;margin-bottom:6px;display:block;">
        @endif
        <input type="file" name="foto" class="form-control" accept="image/*">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2">{{ $persona->notas }}</textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalEditar')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Guardar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL PAGO --}}
<div class="modal-overlay" id="modalPago" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar pago</h3>
    <form method="POST" action="{{ route('personas.pago.store',$persona->id) }}">
      @csrf
      <div class="form-group">
        <label>Tipo de pago *</label>
        <select name="tipo_pago" class="form-control" required>
          <option value="jornal">Jornal (por dias)</option>
          <option value="mensual">Mensual</option>
          <option value="bono">Bono</option>
          <option value="anticipo">Anticipo</option>
          <option value="otro">Otro</option>
        </select>
      </div>
      <div class="form-group">
        <label>Concepto</label>
        <input type="text" name="concepto" class="form-control" placeholder="Ej: Semana del 7 al 11...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Dias trabajados</label>
          <input type="number" step="0.5" name="dias" class="form-control" placeholder="0" id="diasPago">
        </div>
        <div class="form-group">
          <label>Valor total (COP) *</label>
          <input type="number" step="1000" name="valor" class="form-control" required placeholder="0" id="valorPago">
        </div>
      </div>
      <div class="form-group">
        <label>Fecha *</label>
        <input type="date" name="fecha" class="form-control" required value="{{ date('Y-m-d') }}">
      </div>
      <div class="form-group">
        <label>Asociar a cultivo</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          @foreach($cultivos as $cv)
            <option value="{{ $cv->id }}">{{ $cv->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Asociar a animal</label>
        <select name="animal_id" class="form-control">
          <option value="">Ninguno</option>
          @foreach($animales as $an)
            <option value="{{ $an->id }}">{{ $an->especie }} - {{ $an->nombre_lote }}</option>
          @endforeach
        </select>
      </div>
      <div style="background:#f0fdf4;border-radius:8px;padding:10px;border:1px solid #86efac;margin-bottom:12px;">
        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
          <input type="checkbox" name="crear_gasto" value="1" checked style="width:18px;height:18px;">
          <span style="font-size:.87rem;color:#166534;font-weight:600;">Crear gasto automaticamente en finanzas</span>
        </label>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalPago')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar pago</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL LABOR --}}
<div class="modal-overlay" id="modalLabor" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <h3 class="modal-title">Registrar labor</h3>
    <form method="POST" action="{{ route('personas.labor.store',$persona->id) }}">
      @csrf
      <div class="form-group">
        <label>Descripcion de la labor *</label>
        <input type="text" name="descripcion" class="form-control" required placeholder="Ej: Fumigacion lote norte...">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label>Fecha *</label>
          <input type="date" name="fecha" class="form-control" required value="{{ date('Y-m-d') }}">
        </div>
        <div class="form-group">
          <label>Horas trabajadas</label>
          <input type="number" step="0.5" name="horas" class="form-control" placeholder="0">
        </div>
      </div>
      <div class="form-group">
        <label>Cultivo relacionado</label>
        <select name="cultivo_id" class="form-control">
          <option value="">Ninguno</option>
          @foreach($cultivos as $cv)
            <option value="{{ $cv->id }}">{{ $cv->nombre }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Animal relacionado</label>
        <select name="animal_id" class="form-control">
          <option value="">Ninguno</option>
          @foreach($animales as $an)
            <option value="{{ $an->id }}">{{ $an->especie }} - {{ $an->nombre_lote }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Insumos usados</label>
        <input type="text" name="insumos_usados" class="form-control" placeholder="Ej: 2 litros Glifosato...">
      </div>
      <div class="form-group">
        <label>Notas</label>
        <textarea name="notas" class="form-control" rows="2" placeholder="Observaciones..."></textarea>
      </div>
      <div class="flex gap-2 mt-2">
        <button type="button" class="btn btn-ghost btn-full" onclick="closeModal('modalLabor')">Cancelar</button>
        <button type="submit" class="btn btn-primary btn-full">Registrar labor</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
@if($persona->valor_jornal)
document.getElementById('diasPago').addEventListener('input', function() {
  var dias = parseFloat(this.value) || 0;
  if (dias > 0) {
    document.getElementById('valorPago').value = Math.round(dias * {{ $persona->valor_jornal }});
  }
});
@endif
</script>
@endpush
@endsection