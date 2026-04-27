<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    // ── Helpers ─────────────────────────────────────────────────────────

    private function uid(): int
    {
        return (int) session('usuario_id');
    }

    private function usuario(): object
    {
        return DB::table('usuarios')->find($this->uid());
    }

    private function anio(Request $request): int
    {
        return (int) ($request->anio ?? now()->year);
    }

    private function formatCOP(float $valor): string
    {
        return '$' . number_format($valor, 0, ',', '.');
    }

    // ────────────────────────────────────────────────────────────────────
    // CULTIVOS — PDF
    // ────────────────────────────────────────────────────────────────────

    public function cultivosPdf(Request $request)
    {
        $uid      = $this->uid();
        $usuario  = $this->usuario();
        $cultivos = DB::table('cultivos')
            ->where('usuario_id', $uid)
            ->orderBy('estado')
            ->orderBy('fecha_siembra', 'desc')
            ->get();

        $stats = [
            'activos'    => $cultivos->where('estado', 'activo')->count(),
            'cosechados' => $cultivos->where('estado', 'cosechado')->count(),
            'vendidos'   => $cultivos->where('estado', 'vendido')->count(),
            'total_area' => $cultivos->sum('area'),
        ];

        $pdf = Pdf::loadView('exports.cultivos-pdf', compact('cultivos', 'usuario', 'stats'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont'  => 'sans-serif',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
            ]);

        $filename = 'cultivos_' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
    }

    // ────────────────────────────────────────────────────────────────────
    // CULTIVOS — EXCEL
    // ────────────────────────────────────────────────────────────────────

    public function cultivosExcel(Request $request): StreamedResponse
    {
        $uid      = $this->uid();
        $usuario  = $this->usuario();
        $cultivos = DB::table('cultivos')
            ->where('usuario_id', $uid)
            ->orderBy('estado')
            ->orderBy('fecha_siembra', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Cultivos');

        // ── Encabezado de finca ──────────────────────────────────────
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'AGROGRANJA - Reporte de Cultivos');
        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', ($usuario->nombre_finca ?? 'Mi Finca') . ' | Generado: ' . now()->format('d/m/Y H:i'));

        $this->styleHeader($sheet, 'A1:G1', '3d8b3d');
        $this->styleHeader($sheet, 'A2:G2', '5aad5a', 11);

        // ── Cabecera de tabla ────────────────────────────────────────
        $headers = ['#', 'Nombre / Lote', 'Tipo', 'Fecha siembra', 'Área', 'Unidad', 'Estado'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '4', $h);
            $col++;
        }
        $this->styleTableHeader($sheet, 'A4:G4');

        // ── Datos ────────────────────────────────────────────────────
        $row = 5;
        foreach ($cultivos as $i => $c) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $c->nombre);
            $sheet->setCellValue('C' . $row, $c->tipo);
            $sheet->setCellValue('D' . $row, $c->fecha_siembra ? \Carbon\Carbon::parse($c->fecha_siembra)->format('d/m/Y') : '-');
            $sheet->setCellValue('E' . $row, $c->area ?? '-');
            $sheet->setCellValue('F' . $row, $c->unidad ?? '-');
            $sheet->setCellValue('G' . $row, ucfirst($c->estado));

            // Color por estado
            $color = match($c->estado) {
                'activo'    => 'edf7ed',
                'cosechado' => 'fff3e0',
                'vendido'   => 'fdf3ea',
                default     => 'ffffff',
            };
            $sheet->getStyle("A{$row}:G{$row}")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);

            $this->styleBorderRow($sheet, "A{$row}:G{$row}");
            $row++;
        }

        // ── Totales ──────────────────────────────────────────────────
        $row++;
        $sheet->setCellValue('A' . $row, 'TOTALES');
        $sheet->setCellValue('B' . $row, 'Activos: ' . $cultivos->where('estado','activo')->count());
        $sheet->setCellValue('C' . $row, 'Cosechados: ' . $cultivos->where('estado','cosechado')->count());
        $sheet->setCellValue('D' . $row, 'Vendidos: ' . $cultivos->where('estado','vendido')->count());
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);

        // ── Anchos ──────────────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);

        return $this->downloadExcel($spreadsheet, 'cultivos_' . now()->format('Y-m-d') . '.xlsx');
    }

    // ────────────────────────────────────────────────────────────────────
    // GASTOS — PDF
    // ────────────────────────────────────────────────────────────────────

    public function gastosPdf(Request $request)
    {
        $uid     = $this->uid();
        $usuario = $this->usuario();
        $anio    = $this->anio($request);

        $gastos = DB::table('gastos as g')
            ->leftJoin('cultivos as c', 'c.id', '=', 'g.cultivo_id')
            ->where('g.usuario_id', $uid)
            ->whereYear('g.fecha', $anio)
            ->select('g.*', 'c.nombre as cultivo_nombre')
            ->orderBy('g.fecha', 'desc')
            ->get();

        $totalAnio   = $gastos->sum('valor');
        $porCategoria = $gastos->groupBy('categoria')->map(fn($g) => $g->sum('valor'));
        $porMes       = $gastos->groupBy(fn($g) => \Carbon\Carbon::parse($g->fecha)->format('m'))->map(fn($g) => $g->sum('valor'));

        $pdf = Pdf::loadView('exports.gastos-pdf', compact(
            'gastos', 'usuario', 'anio', 'totalAnio', 'porCategoria', 'porMes'
        ))
        ->setPaper('a4', 'portrait')
        ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        return $pdf->download('gastos_' . $anio . '_' . now()->format('Y-m-d') . '.pdf');
    }

    // ────────────────────────────────────────────────────────────────────
    // GASTOS — EXCEL
    // ────────────────────────────────────────────────────────────────────

    public function gastosExcel(Request $request): StreamedResponse
    {
        $uid     = $this->uid();
        $usuario = $this->usuario();
        $anio    = $this->anio($request);

        $gastos = DB::table('gastos as g')
            ->leftJoin('cultivos as c', 'c.id', '=', 'g.cultivo_id')
            ->where('g.usuario_id', $uid)
            ->whereYear('g.fecha', $anio)
            ->select('g.*', 'c.nombre as cultivo_nombre')
            ->orderBy('g.fecha', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();

        // ── Hoja 1: Detalle ──────────────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Detalle gastos');

        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', "AGROGRANJA - Gastos {$anio}");
        $this->styleHeader($sheet, 'A1:H1', '7a4f2a');

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', ($usuario->nombre_finca ?? 'Mi Finca') . ' | Total: $' . number_format($gastos->sum('valor'), 0, ',', '.'));
        $this->styleHeader($sheet, 'A2:H2', 'a07050', 11);

        $headers = ['#', 'Fecha', 'Categoría', 'Descripción', 'Cantidad', 'Proveedor', 'Cultivo', 'Valor (COP)'];
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col . '4', $h); $col++; }
        $this->styleTableHeader($sheet, 'A4:H4', '7a4f2a');

        $row = 5;
        foreach ($gastos as $i => $g) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $g->fecha ? \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') : '-');
            $sheet->setCellValue('C' . $row, $g->categoria);
            $sheet->setCellValue('D' . $row, $g->descripcion);
            $sheet->setCellValue('E' . $row, $g->cantidad ? $g->cantidad . ' ' . $g->unidad_cantidad : '-');
            $sheet->setCellValue('F' . $row, $g->proveedor ?? '-');
            $sheet->setCellValue('G' . $row, $g->cultivo_nombre ?? '-');
            $sheet->setCellValue('H' . $row, (float) $g->valor);
            $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fdf3ea');
            }
            $this->styleBorderRow($sheet, "A{$row}:H{$row}");
            $row++;
        }

        // Fila de total
        $row++;
        $sheet->setCellValue('G' . $row, 'TOTAL:');
        $sheet->setCellValue('H' . $row, $gastos->sum('valor'));
        $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("G{$row}:H{$row}")->getFont()->setBold(true);
        $sheet->getStyle("G{$row}:H{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('fdf3ea');

        foreach (['A'=>5,'B'=>13,'C'=>18,'D'=>32,'E'=>14,'F'=>18,'G'=>20,'H'=>15] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        // ── Hoja 2: Por categoría ────────────────────────────────────
        $sheet2 = $spreadsheet->createSheet()->setTitle('Por categoria');
        $sheet2->setCellValue('A1', 'Categoría');
        $sheet2->setCellValue('B1', 'Total (COP)');
        $sheet2->setCellValue('C1', '% del total');
        $this->styleTableHeader($sheet2, 'A1:C1', '7a4f2a');

        $porCat  = $gastos->groupBy('categoria')->map(fn($g) => $g->sum('valor'))->sortByDesc(fn($v) => $v);
        $totalG  = $gastos->sum('valor');
        $r2 = 2;
        foreach ($porCat as $cat => $total) {
            $sheet2->setCellValue('A' . $r2, $cat);
            $sheet2->setCellValue('B' . $r2, $total);
            $sheet2->getStyle('B' . $r2)->getNumberFormat()->setFormatCode('#,##0');
            $sheet2->setCellValue('C' . $r2, $totalG > 0 ? round($total / $totalG * 100, 1) . '%' : '0%');
            $r2++;
        }
        $sheet2->getColumnDimension('A')->setWidth(22);
        $sheet2->getColumnDimension('B')->setWidth(16);
        $sheet2->getColumnDimension('C')->setWidth(12);

        $spreadsheet->setActiveSheetIndex(0);
        return $this->downloadExcel($spreadsheet, 'gastos_' . $anio . '_' . now()->format('Ymd') . '.xlsx');
    }

    // ────────────────────────────────────────────────────────────────────
    // COSECHAS — PDF
    // ────────────────────────────────────────────────────────────────────

    public function cosechasPdf(Request $request)
    {
        $uid      = $this->uid();
        $usuario  = $this->usuario();
        $anio     = $this->anio($request);

        $cosechas = DB::table('cosechas as cs')
            ->leftJoin('cultivos as c', 'c.id', '=', 'cs.cultivo_id')
            ->where('cs.usuario_id', $uid)
            ->whereYear('cs.fecha_cosecha', $anio)
            ->select('cs.*', 'c.nombre as cultivo_nombre')
            ->orderBy('cs.fecha_cosecha', 'desc')
            ->get();

        $totalValor    = $cosechas->sum('valor_estimado');
        $porProducto   = $cosechas->groupBy('producto')->map(fn($g) => [
            'qty'   => $g->sum('cantidad'),
            'valor' => $g->sum('valor_estimado'),
            'unidad'=> $g->first()->unidad,
        ]);

        $pdf = Pdf::loadView('exports.cosechas-pdf', compact(
            'cosechas', 'usuario', 'anio', 'totalValor', 'porProducto'
        ))
        ->setPaper('a4', 'landscape')
        ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        return $pdf->download('cosechas_' . $anio . '_' . now()->format('Ymd') . '.pdf');
    }

    // ────────────────────────────────────────────────────────────────────
    // COSECHAS — EXCEL
    // ────────────────────────────────────────────────────────────────────

    public function cosechasExcel(Request $request): StreamedResponse
    {
        $uid      = $this->uid();
        $usuario  = $this->usuario();
        $anio     = $this->anio($request);

        $cosechas = DB::table('cosechas as cs')
            ->leftJoin('cultivos as c', 'c.id', '=', 'cs.cultivo_id')
            ->where('cs.usuario_id', $uid)
            ->whereYear('cs.fecha_cosecha', $anio)
            ->select('cs.*', 'c.nombre as cultivo_nombre')
            ->orderBy('cs.fecha_cosecha', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Cosechas');

        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', "AGROGRANJA - Cosechas {$anio}");
        $this->styleHeader($sheet, 'A1:I1', '3d8b3d');

        $headers = ['#','Fecha','Producto','Cantidad','Unidad','Calidad','Destino','Comprador','Valor (COP)'];
        $col = 'A';
        foreach ($headers as $h) { $sheet->setCellValue($col . '3', $h); $col++; }
        $this->styleTableHeader($sheet, 'A3:I3');

        $row = 4;
        foreach ($cosechas as $i => $cs) {
            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $cs->fecha_cosecha ? \Carbon\Carbon::parse($cs->fecha_cosecha)->format('d/m/Y') : '-');
            $sheet->setCellValue('C' . $row, $cs->producto);
            $sheet->setCellValue('D' . $row, (float) $cs->cantidad);
            $sheet->setCellValue('E' . $row, $cs->unidad);
            $sheet->setCellValue('F' . $row, ucfirst($cs->calidad ?? '-'));
            $sheet->setCellValue('G' . $row, ucfirst($cs->destino ?? '-'));
            $sheet->setCellValue('H' . $row, $cs->comprador ?? '-');
            $sheet->setCellValue('I' . $row, (float) ($cs->valor_estimado ?? 0));
            $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
            $this->styleBorderRow($sheet, "A{$row}:I{$row}");
            $row++;
        }

        // Total
        $row++;
        $sheet->setCellValue('H' . $row, 'TOTAL:');
        $sheet->setCellValue('I' . $row, $cosechas->sum('valor_estimado'));
        $sheet->getStyle('I' . $row)->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("H{$row}:I{$row}")->getFont()->setBold(true);

        foreach (['A'=>4,'B'=>13,'C'=>22,'D'=>11,'E'=>10,'F'=>11,'G'=>13,'H'=>20,'I'=>15] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }

        return $this->downloadExcel($spreadsheet, 'cosechas_' . $anio . '_' . now()->format('Ymd') . '.xlsx');
    }

    // ────────────────────────────────────────────────────────────────────
    // REPORTE GENERAL — PDF
    // ────────────────────────────────────────────────────────────────────

    public function reporteGeneralPdf(Request $request)
    {
        $uid     = $this->uid();
        $usuario = $this->usuario();
        $anio    = $this->anio($request);

        $gastos   = DB::table('gastos')->where('usuario_id', $uid)->whereYear('fecha', $anio)->get();
        $ingresos = DB::table('ingresos')->where('usuario_id', $uid)->whereYear('fecha', $anio)->get();
        $cosechas = DB::table('cosechas')->where('usuario_id', $uid)->whereYear('fecha_cosecha', $anio)->get();
        $cultivos = DB::table('cultivos')->where('usuario_id', $uid)->get();

        $totalGastos    = $gastos->sum('valor');
        $totalIngresos  = $ingresos->sum('valor_total');
        $totalCosechas  = $cosechas->sum('valor_estimado');
        $balance        = $totalIngresos - $totalGastos;

        // Gastos por mes (array 1-12)
        $gastosMes = array_fill(1, 12, 0);
        foreach ($gastos as $g) {
            $mes = (int) \Carbon\Carbon::parse($g->fecha)->format('n');
            $gastosMes[$mes] += $g->valor;
        }

        $ingresosMes = array_fill(1, 12, 0);
        foreach ($ingresos as $i) {
            $mes = (int) \Carbon\Carbon::parse($i->fecha)->format('n');
            $ingresosMes[$mes] += $i->valor_total;
        }

        $pdf = Pdf::loadView('exports.reporte-general-pdf', compact(
            'usuario', 'anio',
            'totalGastos', 'totalIngresos', 'totalCosechas', 'balance',
            'gastos', 'ingresos', 'cosechas', 'cultivos',
            'gastosMes', 'ingresosMes'
        ))
        ->setPaper('a4', 'portrait')
        ->setOptions(['defaultFont' => 'sans-serif', 'isHtml5ParserEnabled' => true]);

        return $pdf->download('reporte_general_' . $anio . '.pdf');
    }

    // ────────────────────────────────────────────────────────────────────
    // Helpers de estilos Excel
    // ────────────────────────────────────────────────────────────────────

    private function styleHeader($sheet, string $range, string $bgColor, int $size = 13): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'size' => $size, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension((int) explode(':', $range)[0][1])->setRowHeight(22);
    }

    private function styleTableHeader($sheet, string $range, string $bgColor = '3d8b3d'): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'color' => ['rgb' => $bgColor]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
    }

    private function styleBorderRow($sheet, string $range): void
    {
        $sheet->getStyle($range)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('e2e8f0');
    }

    private function downloadExcel(Spreadsheet $spreadsheet, string $filename): StreamedResponse
    {
        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    /* ─── PDF ANIMALES ─────────────────────────────────────────── */
public function animalesPdf(Request $request)
{
    $uid      = session('usuario_id');
    $usuario  = DB::table('usuarios')->find($uid);
    $estado   = $request->estado ?? 'todos';

    $query = DB::table('animales')->where('usuario_id', $uid);
    if ($estado !== 'todos') $query->where('estado', $estado);
    $animales = $query->orderBy('especie')->orderBy('nombre_lote')->get();

    // Gastos e ingresos por animal
    $animales = $animales->map(function ($a) use ($uid) {
        $a->total_gastos   = DB::table('gastos')->where('usuario_id',$uid)->where('animal_id',$a->id)->sum('valor');
        $a->total_ingresos = DB::table('ingresos')->where('usuario_id',$uid)->where('animal_id',$a->id)->sum('valor_total');
        $a->balance        = $a->total_ingresos - $a->total_gastos;
        return $a;
    });

    $stats = [
        'total'   => $animales->count(),
        'activos' => $animales->where('estado','activo')->count(),
        'vendidos'=> $animales->where('estado','vendido')->count(),
        'ingresos'=> $animales->sum('total_ingresos'),
        'gastos'  => $animales->sum('total_gastos'),
    ];

    $pdf = app('dompdf.wrapper');
    $pdf->loadView('exports.animales-pdf', compact('animales','usuario','estado','stats'));
    $pdf->setPaper('A4','portrait');
    return $pdf->download('animales-'.now()->format('Y-m-d').'.pdf');
}

/* ─── PDF NÓMINA / PAGOS PERSONAS ──────────────────────────── */
public function nominaPdf(Request $request)
{
    $uid     = session('usuario_id');
    $usuario = DB::table('usuarios')->find($uid);
    $mes     = $request->mes ?? now()->format('Y-m');
    [$anio, $numMes] = explode('-', $mes);

    $pagos = DB::table('persona_pagos as pp')
        ->join('personas as per', 'per.id', '=', 'pp.persona_id')
        ->leftJoin('cultivos as c', 'c.id', '=', 'pp.cultivo_id')
        ->leftJoin('animales as a', 'a.id', '=', 'pp.animal_id')
        ->where('pp.usuario_id', $uid)
        ->whereYear('pp.fecha', $anio)
        ->whereMonth('pp.fecha', $numMes)
        ->select(
            'pp.*',
            'per.nombre as trabajador',
            'per.cargo',
            'per.tipo_contrato',
            'c.nombre as cultivo_nombre',
            'a.nombre_lote as animal_nombre'
        )
        ->orderBy('per.nombre')
        ->orderBy('pp.fecha')
        ->get();

    // Agrupar por trabajador
    $porTrabajador = $pagos->groupBy('persona_id')->map(function ($pagos_trabajador) {
        $primero = $pagos_trabajador->first();
        return (object)[
            'nombre'       => $primero->trabajador,
            'cargo'        => $primero->cargo,
            'tipo_contrato'=> $primero->tipo_contrato,
            'total'        => $pagos_trabajador->sum('valor'),
            'dias'         => $pagos_trabajador->sum('dias'),
            'pagos'        => $pagos_trabajador,
        ];
    });

    $totalNomina = $pagos->sum('valor');
    $totalDias   = $pagos->sum('dias');

    $mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $mesNombre = $mesesNombres[(int)$numMes] . ' ' . $anio;

    $pdf = app('dompdf.wrapper');
    $pdf->loadView('exports.nomina-pdf', compact(
        'pagos','porTrabajador','usuario','mesNombre','totalNomina','totalDias'
    ));
    $pdf->setPaper('A4','portrait');
    return $pdf->download('nomina-'.$mes.'.pdf');
}

/* ─── PDF INVENTARIO ────────────────────────────────────────── */
public function inventarioPdf(Request $request)
{
    $uid     = session('usuario_id');
    $usuario = DB::table('usuarios')->find($uid);

    $insumos = DB::table('inventario')
        ->where('usuario_id', $uid)
        ->orderBy('categoria')
        ->orderBy('nombre')
        ->get();

    // Agrupar por categoría
    $porCategoria = $insumos->groupBy('categoria');

    $stats = [
        'total'         => $insumos->count(),
        'valor_total'   => $insumos->sum(fn($i) => $i->cantidad_actual * ($i->precio_unitario ?? 0)),
        'bajo_stock'    => $insumos->filter(fn($i) => $i->cantidad_actual <= $i->stock_minimo)->count(),
        'por_vencer'    => $insumos->filter(fn($i) => $i->fecha_vencimiento &&
                           \Carbon\Carbon::parse($i->fecha_vencimiento)->isFuture() &&
                           \Carbon\Carbon::parse($i->fecha_vencimiento)->diffInDays(now()) <= 30)->count(),
    ];

    $pdf = app('dompdf.wrapper');
    $pdf->loadView('exports.inventario-pdf', compact('insumos','porCategoria','usuario','stats'));
    $pdf->setPaper('A4','portrait');
    return $pdf->download('inventario-'.now()->format('Y-m-d').'.pdf');
}

/* ─── PDF RENTABILIDAD ──────────────────────────────────────── */
public function rentabilidadPdf(Request $request)
{
    $uid     = session('usuario_id');
    $usuario = DB::table('usuarios')->find($uid);
    $anio    = $request->anio ?? now()->year;

    $cultivos = DB::table('cultivos')->where('usuario_id',$uid)->get();

    $datos = $cultivos->map(function ($c) use ($uid, $anio) {
        $gastos     = DB::table('gastos')->where('usuario_id',$uid)->where('cultivo_id',$c->id)->whereYear('fecha',$anio)->sum('valor');
        $manoObra   = DB::table('persona_pagos')->where('usuario_id',$uid)->where('cultivo_id',$c->id)->whereYear('fecha',$anio)->sum('valor');
        $ingresos   = DB::table('ingresos')->where('usuario_id',$uid)->where('cultivo_id',$c->id)->whereYear('fecha',$anio)->sum('valor_total');
        $cosechas   = DB::table('cosechas')->where('usuario_id',$uid)->where('cultivo_id',$c->id)->whereYear('fecha_cosecha',$anio)->sum('valor_estimado');
        $ingresoReal = $ingresos > 0 ? $ingresos : $cosechas;
        $costoTotal  = $gastos + $manoObra;
        $rent        = $ingresoReal - $costoTotal;
        $roi         = $costoTotal > 0 ? round(($rent/$costoTotal)*100,1) : 0;
        return (object)[
            'nombre'     => $c->nombre, 'tipo' => $c->tipo,
            'estado'     => $c->estado, 'area' => $c->area, 'unidad' => $c->unidad,
            'ingresos'   => (float)$ingresoReal, 'gastos'   => (float)$gastos,
            'mano_obra'  => (float)$manoObra,    'costo_total'=> (float)$costoTotal,
            'rentabilidad'=> (float)$rent,        'roi'      => $roi,
        ];
    })->sortByDesc('rentabilidad')->values();

    $resumen = [
        'total_ingresos' => $datos->sum('ingresos'),
        'total_costos'   => $datos->sum('costo_total'),
        'total_rent'     => $datos->sum('rentabilidad'),
        'mejor'          => $datos->where('rentabilidad','>',0)->first(),
    ];

    $pdf = app('dompdf.wrapper');
    $pdf->loadView('exports.rentabilidad-pdf', compact('datos','usuario','anio','resumen'));
    $pdf->setPaper('A4','portrait');
    return $pdf->download('rentabilidad-'.$anio.'.pdf');
}

/* ─── PDF PRODUCCIÓN ANIMAL ─────────────────────────────────── */
public function produccionPdf(Request $request)
{
    $uid     = session('usuario_id');
    $usuario = DB::table('usuarios')->find($uid);
    $mes     = $request->mes ?? now()->format('Y-m');
    [$anio, $numMes] = explode('-', $mes);

    $registros = DB::table('animal_produccion as ap')
        ->join('animales as a', 'a.id', '=', 'ap.animal_id')
        ->where('ap.usuario_id', $uid)
        ->whereYear('ap.fecha', $anio)
        ->whereMonth('ap.fecha', $numMes)
        ->select('ap.*','a.nombre_lote','a.especie')
        ->orderBy('a.nombre_lote')
        ->orderBy('ap.fecha')
        ->get();

    $resumen = $registros->groupBy('tipo_produccion')->map(function ($items, $tipo) {
        return (object)[
            'tipo'        => $tipo,
            'total_qty'   => $items->sum('cantidad'),
            'unidad'      => $items->first()->unidad,
            'total_valor' => $items->sum('valor_total'),
            'dias'        => $items->count(),
            'vendido'     => $items->where('vendido',1)->sum('cantidad'),
        ];
    });

    $mesesNombres = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $mesNombre = $mesesNombres[(int)$numMes] . ' ' . $anio;

    $pdf = app('dompdf.wrapper');
    $pdf->loadView('exports.produccion-pdf', compact('registros','resumen','usuario','mesNombre'));
    $pdf->setPaper('A4','portrait');
    return $pdf->download('produccion-'.$mes.'.pdf');
}
}