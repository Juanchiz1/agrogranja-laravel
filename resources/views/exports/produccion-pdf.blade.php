<!DOCTYPE html><html><head><meta charset="UTF-8">
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:11px;color:#1a1a1a;margin:0;padding:20px}
h1{font-size:18px;color:#1A4731;margin:0 0 4px}
.sub{font-size:10px;color:#666;margin-bottom:16px}
table{width:100%;border-collapse:collapse;margin-top:8px;margin-bottom:14px}
th{background:#1A4731;color:#fff;padding:7px 8px;text-align:left;font-size:10px}
td{padding:6px 8px;border-bottom:1px solid #e5e7eb;font-size:10px}
tr:nth-child(even) td{background:#f9fafb}
.vendido{color:#166534;font-weight:bold}
</style></head><body>
<h1>🥛 Producción Animal — {{ $mesNombre }}</h1>
<div class="sub">{{ $usuario->nombre_finca ?? $usuario->nombre }} · Generado {{ now()->format('d/m/Y H:i') }}</div>

<table style="width:auto;margin-bottom:14px">
<thead><tr><th>Tipo</th><th>Total producido</th><th>Vendido</th><th>Días</th><th>Valor total</th></tr></thead>
<tbody>
@foreach($resumen as $r)
<tr>
  <td>{{ ucfirst($r->tipo) }}</td>
  <td>{{ number_format($r->total_qty,1) }} {{ $r->unidad }}</td>
  <td>{{ number_format($r->vendido,1) }} {{ $r->unidad }}</td>
  <td>{{ $r->dias }} registros</td>
  <td>{{ $r->total_valor ? '$'.number_format($r->total_valor,0,',','.') : '—' }}</td>
</tr>
@endforeach
</tbody>
</table>

<table>
<thead><tr><th>Fecha</th><th>Animal</th><th>Tipo</th><th>Cantidad</th><th>Precio unit.</th><th>Valor</th><th>Estado</th><th>Comprador</th></tr></thead>
<tbody>
@foreach($registros as $r)
<tr>
  <td>{{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}</td>
  <td>{{ $r->nombre_lote ?? $r->especie }}</td>
  <td>{{ ucfirst($r->tipo_produccion) }}</td>
  <td>{{ $r->cantidad }} {{ $r->unidad }}</td>
  <td>{{ $r->precio_unitario ? '$'.number_format($r->precio_unitario,0,',','.') : '—' }}</td>
  <td>{{ $r->valor_total ? '$'.number_format($r->valor_total,0,',','.') : '—' }}</td>
  <td class="{{ $r->vendido?'vendido':'' }}">{{ $r->vendido?'Vendido':'Sin vender' }}</td>
  <td>{{ $r->comprador ?? '—' }}</td>
</tr>
@endforeach
</tbody>
</table>
</body></html>