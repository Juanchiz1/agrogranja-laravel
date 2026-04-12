@php $isNew = !($c ?? null); $uid2 = 'c_'.($c->id ?? 'new'); @endphp

<div class="form-group"><label>Producto *</label>
  <input type="text" name="producto" class="form-control" required placeholder="Ej: Maíz amarillo, Huevos, Leche..." value="{{ $c->producto ?? '' }}">
</div>

<div class="grid-2">
  <div class="form-group"><label>Cantidad *</label>
    <input type="number" step="0.01" name="cantidad" class="form-control" required placeholder="0" value="{{ $c->cantidad ?? '' }}" id="cant_{{ $uid2 }}" oninput="calcValorC('{{ $uid2 }}')">
  </div>
  <div class="form-group"><label>Unidad *</label>
    <select name="unidad" class="form-control" required>
      <option value="">Seleccionar</option>
      @foreach(['kg','toneladas','bultos','arrobas','litros','unidades','cajas','costales','docenas','racimos'] as $u)
      <option {{ ($c->unidad ?? '') == $u ? 'selected' : '' }}>{{ $u }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="grid-2">
  <div class="form-group"><label>Precio unitario (COP)</label>
    <input type="number" step="100" name="precio_unitario" class="form-control" placeholder="0" value="{{ $c->precio_unitario ?? '' }}" id="pu_{{ $uid2 }}" oninput="calcValorC('{{ $uid2 }}')">
  </div>
  <div class="form-group"><label>Valor estimado (COP)</label>
    <input type="number" step="100" name="valor_estimado" class="form-control" placeholder="Autocalculado" value="{{ $c->valor_estimado ?? '' }}" id="ve_{{ $uid2 }}">
  </div>
</div>

<div class="grid-2">
  <div class="form-group"><label>Merma / pérdida (%)</label>
    <input type="number" step="0.1" name="merma_porcentaje" class="form-control" min="0" max="100" placeholder="Ej: 5" value="{{ $c->merma_porcentaje ?? '' }}">
    <small style="color:var(--text-muted);font-size:.73rem;">Se descuenta del valor estimado</small>
  </div>
  <div class="form-group"><label>Fecha de cosecha *</label>
    <input type="date" name="fecha_cosecha" class="form-control" required value="{{ $c->fecha_cosecha ?? date('Y-m-d') }}">
  </div>
</div>

<div class="grid-2">
  <div class="form-group"><label>Calidad</label>
    <select name="calidad" class="form-control">
      @foreach(['excelente'=>'⭐ Excelente','buena'=>'👍 Buena','regular'=>'👌 Regular','baja'=>'👎 Baja'] as $v=>$lbl)
      <option value="{{ $v }}" {{ ($c->calidad ?? 'buena') == $v ? 'selected' : '' }}>{{ $lbl }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group"><label>Destino</label>
    <select name="destino" class="form-control" id="dest_{{ $uid2 }}" onchange="toggleDestFields('{{ $uid2 }}')">
      <option value="">Sin especificar</option>
      @foreach($destinos as $k=>$d)
      <option value="{{ $k }}" {{ ($c->destino ?? '') == $k ? 'selected' : '' }}>{{ $d['label'] }}</option>
      @endforeach
    </select>
  </div>
</div>

{{-- Comprador --}}
<div class="form-group"><label>Comprador / destinatario</label>
  @if($clientes->count())
  <select name="cliente_id" class="form-control mb-1" onchange="syncClienteC(this,'comp_{{ $uid2 }}')">
    <option value="">-- Seleccionar cliente guardado --</option>
    @foreach($clientes as $cl)<option value="{{ $cl->id }}" {{ ($c->cliente_id ?? null) == $cl->id ? 'selected' : '' }}>{{ $cl->nombre }}</option>@endforeach
  </select>
  @endif
  <input type="text" name="comprador" id="comp_{{ $uid2 }}" class="form-control" placeholder="O escribe el nombre" value="{{ $c->comprador ?? '' }}">
</div>

{{-- Almacenaje --}}
<div id="almacenWrap_{{ $uid2 }}" style="display:{{ ($c->destino ?? '') === 'almacenaje' ? 'block' : 'none' }};">
  <div class="grid-2">
    <div class="form-group"><label>📦 Ubicación de almacén</label>
      <input type="text" name="almacen_ubicacion" class="form-control" placeholder="Bodega norte, silo, cuarto frío..." value="{{ $c->almacen_ubicacion ?? '' }}">
    </div>
    <div class="form-group"><label>Consumir/vender antes de</label>
      <input type="date" name="almacen_hasta" class="form-control" value="{{ $c->almacen_hasta ?? '' }}">
    </div>
  </div>
</div>

{{-- Cultivo de origen --}}
@if($cultivos->count())
<div class="form-group"><label>Cultivo de origen</label>
  <select name="cultivo_id" class="form-control">
    <option value="">Ninguno</option>
    @foreach($cultivos as $cv)
    <option value="{{ $cv->id }}" {{ ($c->cultivo_id ?? 0) == $cv->id ? 'selected' : '' }}>{{ $cv->nombre }}</option>
    @endforeach
  </select>
</div>
@if($isNew)
<div class="form-group" style="display:flex;align-items:center;gap:10px;">
  <input type="checkbox" name="marcar_cosechado" id="marcarCos_{{ $uid2 }}" value="1" style="width:18px;height:18px;">
  <label for="marcarCos_{{ $uid2 }}" style="margin:0;font-size:.87rem;color:var(--text-primary);">Marcar cultivo como "cosechado" automáticamente</label>
</div>
@endif
@endif

{{-- Crear ingreso --}}
@if($isNew)
<div id="crearIngresoWrap_{{ $uid2 }}" style="display:none;">
  <div class="form-group" style="background:#f0fdf4;border-radius:8px;padding:10px;border:1px solid #86efac;">
    <div style="display:flex;align-items:center;gap:10px;">
      <input type="checkbox" name="crear_ingreso" id="crearIng_{{ $uid2 }}" value="1" style="width:18px;height:18px;" checked>
      <label for="crearIng_{{ $uid2 }}" style="margin:0;font-size:.87rem;color:#166534;font-weight:600;">
        ✅ Crear ingreso automáticamente en módulo financiero
      </label>
    </div>
    <div style="font-size:.76rem;color:#166534;margin-top:4px;padding-left:28px;">El valor de la cosecha se registrará como ingreso tipo "Cosecha propia".</div>
  </div>
</div>
@endif

{{-- Foto --}}
<div class="form-group"><label>📷 Foto de la cosecha (opcional)</label>
  @if(isset($c->foto) && $c->foto)
    <img src="{{ asset($c->foto) }}" style="width:80px;border-radius:8px;margin-bottom:6px;display:block;">
  @endif
  <input type="file" name="foto" class="form-control" accept="image/*">
</div>

<div class="form-group"><label>Observaciones</label>
  <textarea name="observaciones" class="form-control" rows="2" placeholder="Condiciones de la cosecha, notas de calidad...">{{ $c->observaciones ?? '' }}</textarea>
</div>

<script>
function calcValorC(id) {
  const cant  = parseFloat(document.getElementById('cant_'+id)?.value)||0;
  const punit = parseFloat(document.getElementById('pu_'+id)?.value)||0;
  const ve    = document.getElementById('ve_'+id);
  if(cant && punit && ve) ve.value = Math.round(cant*punit);
}
function toggleDestFields(id) {
  const dest = document.getElementById('dest_'+id)?.value;
  const almacenWrap = document.getElementById('almacenWrap_'+id);
  const crearIng = document.getElementById('crearIngresoWrap_'+id);
  if(almacenWrap) almacenWrap.style.display = dest === 'almacenaje' ? 'block' : 'none';
  const ventaDests = ['venta','intermediario','plaza_mercado','exportacion'];
  if(crearIng) crearIng.style.display = ventaDests.includes(dest) ? 'block' : 'none';
}
function syncClienteC(sel, targetId) {
  const opt = sel.options[sel.selectedIndex];
  if(sel.value) document.getElementById(targetId).value = opt.text;
  else document.getElementById(targetId).value = '';
}
// Init on load
document.addEventListener('DOMContentLoaded', function() {
  try { toggleDestFields('{{ $uid2 }}'); } catch(e) {}
});
</script>