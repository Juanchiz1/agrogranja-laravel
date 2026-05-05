@extends('layouts.app')
@section('title','Sanidad Porcina')
@section('page_title','💉 Sanidad Porcina')
@section('back_url', route('porcicola.piara'))

@push('head')
<link rel="stylesheet" href="{{ asset('css/porcicola.css') }}">
@endpush

@section('content')

{{-- INFO --}}
<div class="alerta-porc info" style="margin-bottom:10px;">
  <span>💡</span>
  <div style="font-size:.8rem;">
    <strong>Protocolos clave en Colombia:</strong>
    PPC (cada 6 meses), Parvovirus (anual), Leptospirosis (cada 6 meses), Desparasitación (cada 3 meses).
    Lechones: Hierro dextrano día 3, Ronco/Pata (Mycoplasma) día 7, PPC día 45.
  </div>
</div>

{{-- VENCIDAS --}}
@if($vencidas->count())
<div class="section-card" style="border-left:4px solid #dc2626;">
  <div class="section-title" style="color:#dc2626;margin-bottom:8px;">
    ❌ Vencidas ({{ $vencidas->count() }})
  </div>
  @foreach($vencidas as $s)
  <div class="sanidad-porc-card vencida">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.88rem;">{{ $s->nombre_protocolo }}</div>
        <div style="font-size:.74rem;color:#991b1b;">
          @if($s->nombre_lote)🐖 {{ $s->nombre_lote }} · @endif
          Venció: {{ \Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y') }}
          · {{ str_replace('_',' ',$s->tipo) }}
        </div>
        @if($s->dosis)<div style="font-size:.72rem;color:#64748b;">Dosis: {{ $s->dosis }}</div>@endif
      </div>
      <button onclick="openAplicar({{ $s->id }},'{{ addslashes($s->nombre_protocolo) }}')"
              class="btn btn-sm btn-primary" style="white-space:nowrap;margin-left:8px;">Aplicar</button>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- PRÓXIMAS --}}
@if($proximas->count())
<div class="section-card">
  <div class="section-title" style="color:#b45309;margin-bottom:8px;">
    ⚠️ Próximas 15 días ({{ $proximas->count() }})
  </div>
  @foreach($proximas as $s)
  <div class="sanidad-porc-card proxima">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-weight:700;font-size:.88rem;">{{ $s->nombre_protocolo }}</div>
        <div style="font-size:.74rem;color:#92400e;">
          @if($s->nombre_lote)🐖 {{ $s->nombre_lote }} · @endif
          {{ \Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y') }}
          (en {{ now()->diffInDays($s->fecha_programada) }} días)
        </div>
        <span style="font-size:.7rem;background:#e2e8f0;color:#475569;padding:1px 7px;border-radius:8px;">
          {{ str_replace('_',' ',$s->via_administracion) }}
        </span>
        @if($s->dosis)<span style="font-size:.7rem;color:#64748b;margin-left:6px;">· {{ $s->dosis }}</span>@endif
      </div>
      <button onclick="openAplicar({{ $s->id }},'{{ addslashes($s->nombre_protocolo) }}')"
              class="btn btn-sm btn-secondary" style="white-space:nowrap;margin-left:8px;">Aplicar</button>
    </div>
  </div>
  @endforeach
</div>
@endif

{{-- PENDIENTES FUTURAS --}}
<div class="section-card">
  <div class="section-header">
    <div class="section-title">📅 Protocolos activos</div>
    <button onclick="openModal('modalSanidadPersonal')" class="btn btn-sm btn-ghost">+ Personalizado</button>
  </div>
  @forelse($pendientesFut as $s)
  <div class="sanidad-porc-card" style="margin-bottom:6px;">
    <div style="display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-weight:700;font-size:.87rem;">{{ $s->nombre_protocolo }}</div>
        <div style="font-size:.74rem;color:#64748b;">
          @if($s->nombre_lote)🐖 {{ $s->nombre_lote }} · @endif
          {{ \Carbon\Carbon::parse($s->fecha_programada)->format('d/m/Y') }}
          · {{ str_replace('_',' ',$s->tipo) }}
        </div>
      </div>
      <button onclick="openAplicar({{ $s->id }},'{{ addslashes($s->nombre_protocolo) }}')"
              class="btn btn-sm btn-ghost" style="font-size:.74rem;">Aplicar</button>
    </div>
  </div>
  @empty
  @if($vencidas->isEmpty() && $proximas->isEmpty())
  <div style="text-align:center;padding:20px;color:#94a3b8;">
    <div style="font-size:2.5rem;">💉</div>
    <p>Sin protocolos configurados.</p>
    <p style="font-size:.82rem;">Los protocolos se crean automáticamente al registrar cerdos en Animales.</p>
  </div>
  @endif
  @endforelse
</div>

{{-- APLICADAS --}}
@if($aplicadas->count())
<div class="section-card">
  <div class="section-title" style="margin-bottom:8px;">✅ Aplicadas ({{ $aplicadas->count() }})</div>
  @foreach($aplicadas->take(6) as $s)
  <div class="sanidad-porc-card ok" style="margin-bottom:4px;">
    <div style="display:flex;justify-content:space-between;align-items:center;font-size:.83rem;">
      <div>
        <span style="font-weight:700;">✅ {{ $s->nombre_protocolo }}</span>
        @if($s->nombre_lote)<span style="color:#64748b;"> · {{ $s->nombre_lote }}</span>@endif
        <div style="font-size:.72rem;color:#64748b;">
          Aplicada: {{ \Carbon\Carbon::parse($s->fecha_aplicada)->format('d/m/Y') }}
          @if($s->producto_usado) · {{ $s->producto_usado }}@endif
          @if($s->proxima_aplicacion)
            · Próxima: {{ \Carbon\Carbon::parse($s->proxima_aplicacion)->format('d/m/Y') }}
          @endif
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
@endif

<div style="margin-bottom:80px;"></div>

{{-- MODAL APLICAR --}}
<div id="modalAplicar" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">💉 Aplicar — <span id="nombreProtocolo"></span></div>
    <form id="formAplicar" method="POST" action="">
      @csrf
      <div class="form-group">
        <label>Fecha de aplicación *</label>
        <input type="date" name="fecha_aplicada" class="form-control" value="{{ now()->toDateString() }}" required>
      </div>
      <div class="form-group">
        <label>Producto comercial usado</label>
        <input type="text" name="producto_usado" class="form-control" placeholder="Ej: Porcilis PCV ID">
      </div>
      <div class="form-group">
        <label>Dosis aplicada</label>
        <input type="text" name="dosis" class="form-control" placeholder="Ej: 2 mL/animal">
      </div>
      <div class="form-group">
        <label>Observaciones</label>
        <textarea name="observaciones" class="form-control" rows="2"
                  placeholder="Reacción, número de animales tratados..."></textarea>
      </div>
      <div style="display:flex;gap:8px;margin-top:6px;">
        <button type="button" onclick="closeModal('modalAplicar')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Confirmar</button>
      </div>
    </form>
  </div>
</div>

{{-- MODAL PERSONALIZADO --}}
<div id="modalSanidadPersonal" class="modal-overlay" style="display:none;">
  <div class="modal-sheet">
    <div class="modal-handle"></div>
    <div class="modal-title">➕ Nuevo protocolo sanitario</div>
    <form method="POST" action="{{ route('porcicola.sanidad.personalizado') }}">
      @csrf
      <div class="form-group">
        <label>Animal / lote (opcional)</label>
        <select name="animal_id" class="form-control">
          <option value="">Toda la piara</option>
          @foreach($porcinos as $p)
          <option value="{{ $p->id }}">{{ $p->nombre_lote }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Nombre del protocolo *</label>
        <input type="text" name="nombre_protocolo" class="form-control" required
               placeholder="Ej: Ronco y Pata, Erisipela...">
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Tipo *</label>
          <select name="tipo" class="form-control" required>
            <option value="vacuna">Vacuna</option>
            <option value="desparasitante">Desparasitante</option>
            <option value="antibiotico">Antibiótico</option>
            <option value="vitamina">Vitamina</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div class="form-group">
          <label>Vía *</label>
          <select name="via_administracion" class="form-control" required>
            <option value="intramuscular">Intramuscular</option>
            <option value="subcutanea">Subcutánea</option>
            <option value="oral">Oral</option>
            <option value="topica">Tópica</option>
            <option value="agua">Agua de bebida</option>
          </select>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Fecha programada *</label>
          <input type="date" name="fecha_programada" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Repetir cada (días)</label>
          <input type="number" name="frecuencia_dias" class="form-control" min="0" placeholder="Ej: 180">
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="form-group">
          <label>Dosis</label>
          <input type="text" name="dosis" class="form-control" placeholder="Ej: 2 mL/animal">
        </div>
        <div class="form-group">
          <label>Producto comercial</label>
          <input type="text" name="producto_usado" class="form-control" placeholder="Nombre comercial">
        </div>
      </div>
      <div style="background:#eff6ff;border-radius:8px;padding:8px 10px;font-size:.78rem;color:#1d4ed8;margin-bottom:10px;">
        ✅ Se generará una tarea en la Agenda con prioridad alta.
      </div>
      <div style="display:flex;gap:8px;">
        <button type="button" onclick="closeModal('modalSanidadPersonal')" class="btn btn-secondary" style="flex:1;">Cancelar</button>
        <button type="submit" class="btn btn-primary" style="flex:1;">Programar</button>
      </div>
    </form>
  </div>
</div>

@endsection

@push('scripts')
<script>
function openModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='flex'; document.body.style.overflow='hidden'; }
function closeModal(id) { var m=document.getElementById(id); if(!m)return; m.style.display='none'; document.body.style.overflow=''; }
document.querySelectorAll('.modal-overlay').forEach(function(m){ m.addEventListener('click',function(e){ if(e.target===this) closeModal(this.id); }); });
function openAplicar(id, nombre) {
  document.getElementById('nombreProtocolo').textContent = nombre;
  document.getElementById('formAplicar').action = '/porcicola/sanidad/' + id + '/aplicar';
  openModal('modalAplicar');
}
</script>
@endpush