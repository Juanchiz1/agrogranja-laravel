<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:sans-serif; font-size:11px; color:#1e293b; }
  .header { background:linear-gradient(135deg,#3d8b3d,#2d6a2d); color:#fff; padding:20px; text-align:center; }
  .header h1 { font-size:22px; font-weight:800; }
  .header p  { font-size:11px; opacity:.8; margin-top:4px; }

  .balance { margin:16px 20px; padding:14px; border-radius:10px; text-align:center; }
  .balance.positivo { background:#edf7ed; border:2px solid #3d8b3d; }
  .balance.negativo { background:#fef2f2; border:2px solid #dc2626; }
  .balance .valor { font-size:28px; font-weight:800; }
  .balance .label { font-size:11px; color:#64748b; margin-top:4px; }

  .grid { display:flex; gap:10px; margin:0 20px 16px; }
  .card { flex:1; padding:12px; border-radius:8px; text-align:center; }
  .card-green  { background:#edf7ed; }
  .card-brown  { background:#fdf3ea; }
  .card-blue   { background:#eff6ff; }
  .card .val { font-size:17px; font-weight:800; }
  .card .lbl { font-size:9px; color:#64748b; margin-top:2px; }

  .section { margin:0 20px 10px; }
  .section h2 { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; padding:6px 0; border-bottom:2px solid #e2e8f0; margin-bottom:8px; }

  .month-table { width:100%; border-collapse:collapse; }
  .month-table th { background:#f1f5f9; padding:5px 8px; text-align:center; font-size:10px; }
  .month-table td { padding:5px 8px; text-align:center; font-size:10px; border-bottom:1px solid #f1f5f9; }
  .pos { color:#3d8b3d; font-weight:700; }
  .neg { color:#dc2626; font-weight:700; }

  .footer { text-align:center; color:#94a3b8; font-size:9px; margin-top:20px; padding-bottom:10px; }
</style>
</head>
<body>

<div class="header">
  <h1>Reporte General {{ $anio }}</h1>
  <p>{{ $usuario->nombre_finca ?? 'Mi Finca' }} · {{ $usuario->departamento ?? '' }}{{ $usuario->municipio ? ', '.$usuario->municipio : '' }}</p>
  <p style="margin-top:6px;font-size:10px;">Generado el {{ now()->format('d/m/Y \a \l\a\s H:i') }}</p>
</div>

<div class="balance {{ $balance >= 0 ? 'positivo' : 'negativo' }}" style="margin-top:16px;">
  <div class="valor" style="color:{{ $balance >= 0 ? '#3d8b3d' : '#dc2626' }}">
    ${{ number_format(abs($balance),0,',','.') }}
  </div>
  <div class="label">Balance {{ $anio }} — {{ $balance >= 0 ? 'Ganancia' : 'Perdida' }}</div>
</div>

<div class="grid">
  <div class="card card-green">
    <div class="val" style="color:#3d8b3d">${{ number_format($totalIngresos/1000,0) }}k</div>
    <div class="lbl">Ingresos</div>
  </div>
  <div class="card card-brown">
    <div class="val" style="color:#7a4f2a">${{ number_format($totalGastos/1000,0) }}k</div>
    <div class="lbl">Gastos</div>
  </div>
  <div class="card card-green">
    <div class="val" style="color:#3d8b3d">${{ number_format($totalCosechas/1000,0) }}k</div>
    <div class="lbl">Val. cosechas</div>
  </div>
  <div class="card card-blue">
    <div class="val" style="color:#2563eb">{{ $cultivos->count() }}</div>
    <div class="lbl">Cultivos</div>
  </div>
</div>

<div class="section">
  <h2>Balance mensual</h2>
  <table class="month-table">
    <thead>
      <tr>
        <th>Mes</th>
        @foreach(['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'] as $m)
        <th>{{ $m }}</th>
        @endforeach
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Ingresos</strong></td>
        @for($m=1;$m<=12;$m++)
        <td class="pos">${{ number_format(($ingresosMes[$m]??0)/1000,0) }}k</td>
        @endfor
      </tr>
      <tr>
        <td><strong>Gastos</strong></td>
        @for($m=1;$m<=12;$m++)
        <td class="neg">${{ number_format(($gastosMes[$m]??0)/1000,0) }}k</td>
        @endfor
      </tr>
      <tr style="background:#f8f5ee;">
        <td><strong>Balance</strong></td>
        @for($m=1;$m<=12;$m++)
        @php $bal = ($ingresosMes[$m]??0) - ($gastosMes[$m]??0); @endphp
        <td class="{{ $bal >= 0 ? 'pos' : 'neg' }}">${{ number_format($bal/1000,0) }}k</td>
        @endfor
      </tr>
    </tbody>
  </table>
</div>

<div class="footer">
  Agrogranja — Sistema de Gestión para Fincas | agrogranja.app
</div>
</body>
</html>