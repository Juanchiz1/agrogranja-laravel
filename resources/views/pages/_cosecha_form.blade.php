<div class="form-group">
  <label>Producto *</label>
  <input type="text" name="producto" class="form-control" required
    placeholder="Ej: Maíz amarillo, Yuca criolla..."
    value="{{ $c->producto ?? old('producto') }}">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Cantidad *</label>
    <input type="number" step="0.1" name="cantidad" class="form-control" required
      placeholder="0" value="{{ $c->cantidad ?? '' }}"
      oninput="calcValor()">
  </div>
  <div class="form-group">
    <label>Unidad *</label>
    <select name="unidad" class="form-control" required>
      <option value="">Seleccionar</option>
      @foreach(['kg','toneladas','bultos','arrobas','litros','unidades','cajas','costales'] as $u)
      <option {{ ($c->unidad ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Precio unitario (COP)</label>
    <input type="number" step="100" name="precio_unitario" class="form-control"
      id="precioUnit_{{ $c->id ?? 'new' }}"
      placeholder="0" value="{{ $c->precio_unitario ?? '' }}"
      oninput="calcValor()">
  </div>
  <div class="form-group">
    <label>Valor estimado (COP)</label>
    <input type="number" step="100" name="valor_estimado" class="form-control"
      id="valorEst_{{ $c->id ?? 'new' }}"
      placeholder="Autocalculado"
      value="{{ $c->valor_estimado ?? '' }}">
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Fecha de cosecha *</label>
    <input type="date" name="fecha_cosecha" class="form-control" required
      value="{{ $c->fecha_cosecha ?? date('Y-m-d') }}">
  </div>
  <div class="form-group">
    <label>Calidad</label>
    <select name="calidad" class="form-control">
      @foreach(['excelente'=>'⭐ Excelente','buena'=>'👍 Buena','regular'=>'👌 Regular','baja'=>'👎 Baja'] as $v => $label)
      <option value="{{ $v }}" {{ ($c->calidad ?? 'buena') === $v ? 'selected' : '' }}>{{ $label }}</option>
      @endforeach
    </select>
  </div>
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Destino</label>
    <select name="destino" class="form-control">
      <option value="">Sin especificar</option>
      <option {{ ($c->destino ?? '') === 'venta' ? 'selected' : '' }} value="venta">💵 Venta</option>
      <option {{ ($c->destino ?? '') === 'autoconsumo' ? 'selected' : '' }} value="autoconsumo">🏠 Autoconsumo</option>
      <option {{ ($c->destino ?? '') === 'almacenaje' ? 'selected' : '' }} value="almacenaje">📦 Almacenaje</option>
    </select>
  </div>
  <div class="form-group">
    <label>Comprador</label>
    <input type="text" name="comprador" class="form-control"
      placeholder="Nombre del comprador"
      value="{{ $c->comprador ?? '' }}">
  </div>
</div>

@if($cultivos->count())
<div class="form-group">
  <label>Cultivo de origen</label>
  <select name="cultivo_id" class="form-control">
    <option value="">Ninguno</option>
    @foreach($cultivos as $cv)
    <option value="{{ $cv->id }}" {{ ($c->cultivo_id ?? 0) == $cv->id ? 'selected' : '' }}>
      {{ $cv->nombre }}
    </option>
    @endforeach
  </select>
</div>
<div class="form-group" style="display:flex;align-items:center;gap:10px;">
  <input type="checkbox" name="marcar_cosechado" id="marcarCosechado" value="1" style="width:18px;height:18px;">
  <label for="marcarCosechado" style="margin:0;text-transform:none;font-size:.88rem;color:var(--negro);">
    Marcar cultivo como "Cosechado" automáticamente
  </label>
</div>
@endif

<div class="form-group">
  <label>Observaciones</label>
  <textarea name="observaciones" class="form-control"
    placeholder="Condiciones de la cosecha, notas de calidad...">{{ $c->observaciones ?? '' }}</textarea>
</div>

<script>
function calcValor() {
  const cant  = parseFloat(document.querySelector('[name="cantidad"]')?.value) || 0;
  const punit = parseFloat(document.querySelector('[name="precio_unitario"]')?.value) || 0;
  const est   = document.querySelector('[name="valor_estimado"]');
  if (cant && punit && est) est.value = Math.round(cant * punit);
}
</script>