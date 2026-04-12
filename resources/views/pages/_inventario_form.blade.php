<div class="form-group">
  <label>Nombre del insumo *</label>
  <input type="text" name="nombre" class="form-control" required
    placeholder="Ej: Glifosato 480SL, Urea 46%..."
    value="{{ $ins->nombre ?? old('nombre') }}">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Categoría *</label>
    <select name="categoria" class="form-control" required>
      <option value="">Seleccionar</option>
      @foreach($categorias as $cat)
      <option {{ ($ins->categoria ?? '') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
      @endforeach
    </select>
  </div>
  <div class="form-group">
    <label>Unidad de medida *</label>
    <select name="unidad" class="form-control" required>
      <option value="">Seleccionar</option>
      @foreach(['kg','g','litros','ml','bultos','costales','cajas','unidades','rollos','metros'] as $u)
      <option {{ ($ins->unidad ?? '') === $u ? 'selected' : '' }}>{{ $u }}</option>
      @endforeach
    </select>
  </div>
</div>

@if($nuevo)
<div class="form-group">
  <label>Cantidad actual *</label>
  <input type="number" step="0.1" name="cantidad_actual" class="form-control" required
    placeholder="0" value="{{ old('cantidad_actual', 0) }}">
</div>
@endif

<div class="form-group">
  <label>Stock mínimo *</label>
  <p style="font-size:.75rem;color:var(--gris);margin-bottom:5px;">
    Se generará una alerta cuando el stock baje de este valor.
  </p>
  <input type="number" step="0.1" name="stock_minimo" class="form-control" required
    placeholder="0" value="{{ $ins->stock_minimo ?? old('stock_minimo', 0) }}">
</div>

<div class="grid-2">
  <div class="form-group">
    <label>Precio unitario (COP)</label>
    <input type="number" step="100" name="precio_unitario" class="form-control"
      placeholder="0" value="{{ $ins->precio_unitario ?? '' }}">
  </div>
  <div class="form-group">
    <label>Proveedor habitual</label>
    <input type="text" name="proveedor" class="form-control"
      placeholder="Nombre del proveedor" value="{{ $ins->proveedor ?? '' }}">
  </div>
</div>

<div class="form-group">
  <label>Fecha de vencimiento</label>
  <input type="date" name="fecha_vencimiento" class="form-control"
    value="{{ $ins->fecha_vencimiento ?? '' }}">
</div>

<div class="form-group">
  <label>Notas</label>
  <textarea name="notas" class="form-control"
    placeholder="Instrucciones de uso, dilución, almacenamiento...">{{ $ins->notas ?? '' }}</textarea>
</div>