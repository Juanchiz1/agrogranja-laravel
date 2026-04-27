<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-bottom:14px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
.cat-title{background:#e8f5ee;font-weight:bold;color:#1A4731;padding:7px 8px;font-size:10px}
.alerta{color:#dc2626;font-weight:bold}
.ok{color:#166534}
</style></head><body>
<h1>📦 Inventario de Insumos</h1>
<div class="sub">{{ $usuario->nombre_finca ?? $usuario->nombre }} · Generado {{ now()->format('d/m/Y H:i') }}</div>

<table style="width:auto;margin-bottom:16px">
<tr>
  <td style="padding:8px 14px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#1A4731">{{ $stats['total'] }}</div><div style="font-size:9px;color:#666">Total insumos</div></td>
  <td style="width:10px"></td>
  <td style="padding:8px 14px;background:#f0fdf4;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#1A4731">${{ number_format($stats['valor_total'],0,',','.') }}</div><div style="font-size:9px;color:#666">Valor inventario</div></td>
  <td style="width:10px"></td>
  <td style="padding:8px 14px;background:#fef2f2;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#dc2626">{{ $stats['bajo_stock'] }}</div><div style="font-size:9px;color:#666">Bajo stock mínimo</div></td>
  <td style="width:10px"></td>
  <td style="padding:8px 14px;background:#fef9c3;border-radius:6px;text-align:center"><div style="font-size:15px;font-weight:bold;color:#92400e">{{ $stats['por_vencer'] }}</div><div style="font-size:9px;color:#666">Por vencer (30 días)</div></td>
</tr>
</table>

@foreach($porCategoria as $categoria => $items)
<div class="cat-title">{{ $categoria }} ({{ $items->count() }} items)</div>
<table>
<thead><tr><th>Nombre</th><th>Stock actual</th><th>Mínimo</th><th>Unidad</th><th>Precio unit.</th><th>Valor total</th><th>Vencimiento</th><th>Estado</th></tr></thead>
<tbody>
@foreach($items as $i)
@php $bajo = $i->cantidad_actual <= $i->stock_minimo; @endphp
<tr>
  <td>{{ $i->nombre }}</td>
  <td class="{{ $bajo ? 'alerta' : 'ok' }}">{{ $i->cantidad_actual }}</td>
  <td>{{ $i->stock_minimo }}</td>
  <td>{{ $i->unidad }}</td>
  <td>{{ $i->precio_unitario ? '$'.number_format($i->precio_unitario,0,',','.') : '—' }}</td>
  <td>${{ number_format($i->cantidad_actual*($i->precio_unitario??0),0,',','.') }}</td>
  <td>{{ $i->fecha_vencimiento ? \Carbon\Carbon::parse($i->fecha_vencimiento)->format('d/m/Y') : '—' }}</td>
  <td class="{{ $bajo ? 'alerta' : 'ok' }}">{{ $bajo ? '⚠ Bajo' : 'OK' }}</td>
</tr>
@endforeach
</tbody>
</table>
@endforeach
</body></html>