<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/modelo/modeloReporte.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteDashboardExporter
{
    private ModeloReporte $modelo;

    /**
     * Colores utilizados en las gráficas para mantener consistencia visual.
     *
     * @var array<int, string>
     */
    private array $palette = ['#d3b8ff', '#a98fff', '#7e61ff', '#513dff', '#2d1aff'];

    public function __construct()
    {
        $this->modelo = new ModeloReporte();
    }

    public function obtenerDatosDashboard(?string $cedulaAgente, bool $esAdmin): array
    {
        $datos = [];

        $datos['kpis'] = $esAdmin
            ? $this->modelo->kpisResumen($cedulaAgente)
            : $this->modelo->kpisAgente($cedulaAgente);

        $datos['polizasPorCategoria'] = array_map(function ($row) {
            $row['categoria'] = $this->normalizarNombreCategoria($row['categoria'] ?? 'Sin categoría');
            $row['total'] = isset($row['total']) ? (int)$row['total'] : 0;
            return $row;
        }, $this->modelo->polizasPorCategoria($cedulaAgente) ?: []);
        $datos['tendenciaSiniestros'] = $this->modelo->tendenciaSiniestros(12, $cedulaAgente) ?: ['labels' => [], 'data' => []];
        $datos['polizasPorVencer'] = $this->modelo->polizasPorVencer(30, $cedulaAgente) ?: [];
        $datos['polizasPorEstado'] = $this->modelo->polizasPorEstado($cedulaAgente) ?: [];
        $datos['rankingProductividad'] = $this->modelo->rankingProductividad(12, $cedulaAgente, 10) ?: [];
        
        if ($esAdmin) {
            $datos['balancePrimasSiniestros'] = $this->modelo->balancePrimasSiniestros(12) ?: ['labels' => [], 'data_primas' => [], 'data_siniestros' => []];
        }

        return $datos;
    }

    public function generarPdf(array $datos, array $contexto = []): string
    {
        $puedeRenderizarImagen = extension_loaded('gd');
        $chartUrls = $puedeRenderizarImagen ? $this->generarChartUrls($datos) : [];
        $html = $this->renderizarHtmlDashboard($datos, $contexto, $chartUrls, $puedeRenderizarImagen);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generarExcel(array $datos, array $contexto = []): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($contexto['generado_por'] ?? 'Sistema')
            ->setTitle($contexto['titulo'] ?? 'Resumen de dashboard');

        $hojaResumen = $spreadsheet->getActiveSheet();
        $hojaResumen->setTitle('Resumen general');
        $this->configurarHojaResumen($hojaResumen, $datos, $contexto);

        $hojaPorVencer = $spreadsheet->createSheet();
        $hojaPorVencer->setTitle('Pólizas por vencer');
        $this->configurarHojaPolizasPorVencer($hojaPorVencer, $datos['polizasPorVencer']);

        $hojaRanking = $spreadsheet->createSheet();
        $hojaRanking->setTitle('Ranking productividad');
        $this->configurarHojaRanking($hojaRanking, $datos['rankingProductividad']);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string)ob_get_clean();
    }

    public function generarPdfGrafico(string $grafico, array $datosDashboard, array $contexto = []): string
    {
        $info = $this->extraerInfoGrafico($grafico, $datosDashboard);
        $puedeRenderizarImagen = extension_loaded('gd');
        if (!$puedeRenderizarImagen) {
            $info['chartUrl'] = null;
        }
        $html = $this->renderizarHtmlGrafico($info, $contexto, $puedeRenderizarImagen);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function generarExcelGrafico(string $grafico, array $datosDashboard, array $contexto = []): string
    {
        $info = $this->extraerInfoGrafico($grafico, $datosDashboard);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($contexto['generado_por'] ?? 'Sistema')
            ->setTitle($info['titulo']);

        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle(substr($info['titulo'], 0, 31));

        $fila = 1;
        $hoja->setCellValue('A' . $fila, $info['titulo']);
        $hoja->mergeCells('A' . $fila . ':D' . $fila);
        $hoja->getStyle('A' . $fila)->getFont()->setBold(true)->setSize(14);
        $fila += 2;

        $hoja->setCellValue('A' . $fila, 'Generado por');
        $hoja->setCellValue('B' . $fila, $contexto['generado_por'] ?? 'Sistema');
        $hoja->setCellValue('C' . $fila, 'Fecha');
        $hoja->setCellValue('D' . $fila, $contexto['fecha'] ?? date('d/m/Y H:i'));
        $fila += 2;

        if (!empty($info['descripcion'])) {
            $hoja->setCellValue('A' . $fila, $info['descripcion']);
            $hoja->mergeCells('A' . $fila . ':D' . $fila);
            $hoja->getStyle('A' . $fila)->getFont()->setItalic(true)->getColor()->setARGB('FF555555');
            $fila += 2;
        }

        if (!empty($info['rawRows'])) {
            $columnas = count($info['headers']);
            $ultimaColumna = $this->columnaPorIndice($columnas);
            $hoja->fromArray($info['headers'], null, 'A' . $fila);
            $this->aplicarEstiloEncabezado($hoja, 'A' . $fila . ':' . $ultimaColumna . $fila);
            $fila++;
            foreach ($info['rawRows'] as $row) {
                $hoja->fromArray($row, null, 'A' . $fila);
                $fila++;
            }
            $this->aplicarBordesTabla($hoja, 'A' . ($fila - count($info['rawRows']) - 1) . ':' . $ultimaColumna . ($fila - 1));
            $this->autoDimensionColumnas($hoja, $columnas);

            if ($info['grafico'] === 'ranking') {
                $this->aplicarFormatoEntero($hoja, 'C' . ($fila - count($info['rawRows'])) . ':C' . ($fila - 1));
                $this->aplicarFormatoMoneda($hoja, 'D' . ($fila - count($info['rawRows'])) . ':D' . ($fila - 1));
            } elseif ($info['grafico'] === 'categoria' || $info['grafico'] === 'estado') {
                $this->aplicarFormatoEntero($hoja, 'B' . ($fila - count($info['rawRows'])) . ':B' . ($fila - 1));
            } elseif ($info['grafico'] === 'siniestros') {
                $this->aplicarFormatoEntero($hoja, 'B' . ($fila - count($info['rawRows'])) . ':B' . ($fila - 1));
            }
        } else {
            $hoja->setCellValue('A' . $fila, $info['emptyMessage']);
            $hoja->mergeCells('A' . $fila . ':D' . $fila);
            $hoja->getStyle('A' . $fila)->getFont()->setBold(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return (string)ob_get_clean();
    }

    private function renderizarHtmlDashboard(array $datos, array $contexto, array $chartUrls, bool $puedeRenderizarImagen): string
    {
        $titulo = $contexto['titulo'] ?? 'Resumen del dashboard';
        $generadoPor = $contexto['generado_por'] ?? 'Sistema';
        $fecha = $contexto['fecha'] ?? date('d/m/Y H:i');
        $urls = $chartUrls;
        $gdDisponible = $puedeRenderizarImagen;

        ob_start();
        include dirname(__DIR__) . '/vista/reportes/dashboard_export.php';
        return (string)ob_get_clean();
    }

    private function renderizarHtmlGrafico(array $info, array $contexto, bool $puedeRenderizarImagen): string
    {
        $titulo = $info['titulo'];
        $descripcion = $info['descripcion'];
        $headers = $info['headers'];
        $rows = $info['rows'];
        $chartUrl = $info['chartUrl'];
        $emptyMessage = $info['emptyMessage'];
        $generadoPor = $contexto['generado_por'] ?? 'Sistema';
        $fecha = $contexto['fecha'] ?? date('d/m/Y H:i');
        $gdDisponible = $puedeRenderizarImagen;

        ob_start();
        include dirname(__DIR__) . '/vista/reportes/chart_export.php';
        return (string)ob_get_clean();
    }

    private function generarChartUrls(array $datos): array
    {
        return [
            'polizasPorCategoria' => $this->chartUrlPolizasPorCategoria($datos['polizasPorCategoria'] ?? []),
            'polizasPorEstado' => $this->chartUrlPolizasPorEstado($datos['polizasPorEstado'] ?? []),
            'rankingProductividad' => $this->chartUrlRanking($datos['rankingProductividad'] ?? []),
            'tendenciaSiniestros' => $this->chartUrlSiniestros($datos['tendenciaSiniestros'] ?? ['labels' => [], 'data' => []]),
            'balancePrimasSiniestros' => isset($datos['balancePrimasSiniestros']) ? $this->chartUrlBalance($datos['balancePrimasSiniestros']) : null,
        ];
    }

    private function chartUrlPolizasPorCategoria(array $rows): ?string
    {
        if (empty($rows)) {
            return null;
        }
        $labels = [];
        $values = [];
        $colors = [];
        foreach ($rows as $idx => $row) {
            $nombre = $this->normalizarNombreCategoria($row['categoria'] ?? 'Sin categoría');
            $labels[] = $nombre;
            $values[] = (int)($row['total'] ?? 0);
            $colors[] = $this->colorParaCategoria($nombre, $idx);
        }
        if (!array_sum($values)) {
            return null;
        }
        $config = [
            'type' => 'horizontalBar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Pólizas',
                    'data' => $values,
                    'backgroundColor' => $colors,
                    'borderColor' => $colors,
                    'borderWidth' => 1,
                    'barThickness' => 'flex'
                ]],
            ],
            'options' => [
                'legend' => ['display' => false],
                'layout' => ['padding' => ['left' => 12, 'right' => 24, 'top' => 8, 'bottom' => 12]],
                'scales' => [
                    'xAxes' => [[
                        'ticks' => [
                            'beginAtZero' => true,
                            'min' => 0,
                            'stepSize' => 1,
                            'precision' => 0
                        ],
                        'gridLines' => ['drawBorder' => false]
                    ]],
                    'yAxes' => [[
                        'ticks' => ['autoSkip' => false],
                        'gridLines' => ['drawBorder' => false]
                    ]]
                ],
                'elements' => ['rectangle' => ['borderSkipped' => 'left']],
            ],
        ];
        return $this->crearUrlQuickChart($config, 750, 420, '2.9.4');
    }

    private function chartUrlPolizasPorEstado(array $rows): ?string
    {
        if (empty($rows)) {
            return null;
        }
        $labels = [];
        $values = [];
        $colors = [];
        foreach ($rows as $idx => $row) {
            $labels[] = $row['estado'] ?? 'Sin estado';
            $values[] = (int)($row['total'] ?? 0);
            $colors[] = $this->palette[$idx % count($this->palette)];
        }
        if (!array_sum($values)) {
            return null;
        }
        $config = [
            'type' => 'doughnut',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'data' => $values,
                    'backgroundColor' => $colors,
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['position' => 'bottom']],
            ],
        ];
        return $this->crearUrlQuickChart($config, 600, 400);
    }

    private function chartUrlRanking(array $rows): ?string
    {
        if (empty($rows)) {
            return null;
        }
        $labels = [];
        $values = [];
        foreach ($rows as $row) {
            $labels[] = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')) ?: ($row['cedula_agente'] ?? 'Agente');
            $values[] = (int)($row['num_polizas'] ?? 0);
        }
        if (!array_sum($values)) {
            return null;
        }
        $config = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Pólizas',
                    'data' => $values,
                    'backgroundColor' => $this->palette[4],
                ]],
            ],
            'options' => [
                'indexAxis' => 'y',
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['x' => ['beginAtZero' => true]],
            ],
        ];
        return $this->crearUrlQuickChart($config, 700, 500);
    }

    private function chartUrlSiniestros(array $data): ?string
    {
        if (empty($data['labels']) || empty($data['data'])) {
            return null;
        }
        $values = array_map('intval', $data['data']);
        if (!array_sum($values)) {
            return null;
        }
        $config = [
            'type' => 'line',
            'data' => [
                'labels' => $data['labels'],
                'datasets' => [[
                    'label' => 'Siniestros',
                    'data' => $values,
                    'fill' => true,
                    'backgroundColor' => 'rgba(126,97,255,0.25)',
                    'borderColor' => $this->palette[3],
                    'tension' => 0.35,
                    'pointBackgroundColor' => $this->palette[2],
                ]],
            ],
            'options' => [
                'plugins' => ['legend' => ['display' => false]],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ];
        return $this->crearUrlQuickChart($config);
    }

    private function chartUrlBalance(array $data): ?string
    {
        if (empty($data['labels']) || (empty($data['data_primas']) && empty($data['data_siniestros']))) {
            return null;
        }
        
        $primas = array_map('floatval', $data['data_primas'] ?? []);
        $siniestros = array_map('floatval', $data['data_siniestros'] ?? []);

        if (!array_sum($primas) && !array_sum($siniestros)) {
            return null;
        }

        $config = [
            'type' => 'line',
            'data' => [
                'labels' => $data['labels'],
                'datasets' => [
                    [
                        'label' => 'Primas Cobradas',
                        'data' => $primas,
                        'fill' => false,
                        'borderColor' => '#28a745', // Verde
                        'tension' => 0.35,
                    ],
                    [
                        'label' => 'Siniestros Pagados',
                        'data' => $siniestros,
                        'fill' => false,
                        'borderColor' => '#dc3545', // Rojo
                        'tension' => 0.35,
                    ]
                ],
            ],
            'options' => [
                'plugins' => ['legend' => ['position' => 'top']],
                'scales' => ['y' => ['beginAtZero' => true]],
            ],
        ];
        return $this->crearUrlQuickChart($config, 800, 450);
    }

    private function crearUrlQuickChart(array $config, int $width = 700, int $height = 380, ?string $version = null): ?string
    {
        $json = json_encode($config);
        if (!$json) {
            return null;
        }
        $params = [
            'c' => $json,
            'backgroundColor' => 'white',
            'width' => $width,
            'height' => $height,
            'format' => 'png',
        ];
        if ($version) {
            $params['v'] = $version;
        }
        return 'https://quickchart.io/chart?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function normalizarNombreCategoria(string $nombre): string
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            return 'Sin categoría';
        }
        $lower = mb_strtolower($nombre, 'UTF-8');
        return mb_convert_case($lower, MB_CASE_TITLE, 'UTF-8');
    }

    private function colorParaCategoria(string $nombre, int $index): string
    {
        $nombreMin = mb_strtolower($nombre, 'UTF-8');
        if (strpos($nombreMin, 'auto') !== false) {
            return '#513dff';
        }
        if (strpos($nombreMin, 'persona') !== false) {
            return '#7e61ff';
        }
        if (strpos($nombreMin, 'salud') !== false) {
            return '#2d1aff';
        }
        return $this->palette[$index % count($this->palette)];
    }

    private function extraerInfoGrafico(string $grafico, array $datosDashboard): array
    {
        $grafico = strtolower($grafico);
        if ($grafico === 'ramo') {
            $grafico = 'categoria';
        }
        $infoBase = [
            'grafico' => $grafico,
            'titulo' => 'Gráfica',
            'descripcion' => '',
            'headers' => [],
            'rows' => [],
            'rawRows' => [],
            'chartUrl' => null,
            'emptyMessage' => 'No hay datos disponibles para esta gráfica.',
        ];

        switch ($grafico) {
            case 'categoria':
                $rows = $datosDashboard['polizasPorCategoria'] ?? [];
                $infoBase['titulo'] = 'Pólizas por categoría';
                $infoBase['descripcion'] = 'Cantidad de pólizas agrupadas por categoría de seguro.';
                $infoBase['headers'] = ['Categoría', 'Total'];
                foreach ($rows as $row) {
                    $categoria = $row['categoria'] ?? 'Sin categoría';
                    $total = (int)($row['total'] ?? 0);
                    $infoBase['rows'][] = [$categoria, number_format($total, 0, ',', '.')];
                    $infoBase['rawRows'][] = [$categoria, $total];
                }
                $infoBase['chartUrl'] = $this->chartUrlPolizasPorCategoria($rows);
                $infoBase['emptyMessage'] = 'Sin datos de pólizas por categoría.';
                break;

            case 'estado':
                $rows = $datosDashboard['polizasPorEstado'] ?? [];
                $infoBase['titulo'] = 'Distribución de pólizas por estado';
                $infoBase['descripcion'] = 'Resumen de pólizas clasificadas por estado operativo.';
                $infoBase['headers'] = ['Estado', 'Total'];
                foreach ($rows as $row) {
                    $estado = $row['estado'] ?? 'Sin estado';
                    $total = (int)($row['total'] ?? 0);
                    $infoBase['rows'][] = [$estado, number_format($total, 0, ',', '.')];
                    $infoBase['rawRows'][] = [$estado, $total];
                }
                $infoBase['chartUrl'] = $this->chartUrlPolizasPorEstado($rows);
                $infoBase['emptyMessage'] = 'Sin datos de distribución por estado.';
                break;

            case 'ranking':
                $rows = $datosDashboard['rankingProductividad'] ?? [];
                $infoBase['titulo'] = 'Ranking de productividad (últimos 12 meses)';
                $infoBase['descripcion'] = 'Top de agentes por cantidad de pólizas gestionadas y monto de primas.';
                $infoBase['headers'] = ['Posición', 'Agente', 'Pólizas', 'Primas'];
                foreach ($rows as $idx => $row) {
                    $agente = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')) ?: ($row['cedula_agente'] ?? 'Agente');
                    $polizas = (int)($row['num_polizas'] ?? 0);
                    $primas = (float)($row['monto_primas'] ?? 0);
                    $infoBase['rows'][] = [
                        $idx + 1,
                        $agente,
                        number_format($polizas, 0, ',', '.'),
                        number_format($primas, 2, ',', '.'),
                    ];
                    $infoBase['rawRows'][] = [$idx + 1, $agente, $polizas, $primas];
                }
                $infoBase['chartUrl'] = $this->chartUrlRanking($rows);
                $infoBase['emptyMessage'] = 'Sin datos de productividad disponibles.';
                break;

            case 'siniestros':
                $labels = $datosDashboard['tendenciaSiniestros']['labels'] ?? [];
                $values = $datosDashboard['tendenciaSiniestros']['data'] ?? [];
                $infoBase['titulo'] = 'Tendencia de siniestros (últimos 12 meses)';
                $infoBase['descripcion'] = 'Serie temporal de la cantidad de siniestros registrados por mes.';
                $infoBase['headers'] = ['Mes', 'Total de siniestros'];
                foreach ($labels as $idx => $label) {
                    $total = (int)($values[$idx] ?? 0);
                    $infoBase['rows'][] = [$label, number_format($total, 0, ',', '.')];
                    $infoBase['rawRows'][] = [$label, $total];
                }
                $infoBase['chartUrl'] = $this->chartUrlSiniestros(['labels' => $labels, 'data' => $values]);
                $infoBase['emptyMessage'] = 'Sin registros de siniestros en el periodo seleccionado.';
                break;

            case 'balance':
                $data = $datosDashboard['balancePrimasSiniestros'] ?? [];
                $labels = $data['labels'] ?? [];
                $primas = $data['data_primas'] ?? [];
                $siniestros = $data['data_siniestros'] ?? [];
                
                $infoBase['titulo'] = 'Balance de Primas vs. Siniestros (últimos 12 meses)';
                $infoBase['descripcion'] = 'Comparativo mensual de primas cobradas contra siniestros pagados.';
                $infoBase['headers'] = ['Mes', 'Primas Cobradas', 'Siniestros Pagados', 'Balance'];
                
                foreach ($labels as $idx => $label) {
                    $prima = (float)($primas[$idx] ?? 0);
                    $siniestro = (float)($siniestros[$idx] ?? 0);
                    $balance = $prima - $siniestro;
                    
                    $infoBase['rows'][] = [
                        $label,
                        number_format($prima, 2, ',', '.'),
                        number_format($siniestro, 2, ',', '.'),
                        number_format($balance, 2, ',', '.')
                    ];
                    $infoBase['rawRows'][] = [$label, $prima, $siniestro, $balance];
                }
                
                $infoBase['chartUrl'] = $this->chartUrlBalance($data);
                $infoBase['emptyMessage'] = 'No hay datos de primas o siniestros para generar el balance.';
                break;

            default:
                throw new InvalidArgumentException('Gráfica no soportada: ' . $grafico);
        }

        return $infoBase;
    }

    private function configurarHojaResumen(Worksheet $hoja, array $datos, array $contexto): void
    {
        $fila = 1;
        $hoja->setCellValue('A' . $fila, $contexto['titulo'] ?? 'Resumen del dashboard');
        $hoja->mergeCells('A' . $fila . ':D' . $fila);
        $hoja->getStyle('A' . $fila)->getFont()->setBold(true)->setSize(14);
        $fila += 2;

        $hoja->setCellValue('A' . $fila, 'Generado por');
        $hoja->setCellValue('B' . $fila, $contexto['generado_por'] ?? 'Sistema');
        $hoja->setCellValue('C' . $fila, 'Fecha');
        $hoja->setCellValue('D' . $fila, $contexto['fecha'] ?? date('d/m/Y H:i'));
        $fila += 2;

        $indicadores = [
            ['Indicador', 'Valor'],
            ['Pólizas registradas', (int)($datos['kpis']['polizas_total'] ?? 0)],
            ['Pólizas con pagos pendientes', (int)($datos['kpis']['polizas_pendientes'] ?? 0)],
            ['% con pagos pendientes', (float)($datos['kpis']['polizas_pendientes_pct'] ?? 0) / 100],
            ['Primas pagadas', (float)($datos['kpis']['primas_pagadas'] ?? 0)],
            ['Agentes activos', (int)($datos['kpis']['agentes_activos'] ?? 0)],
            ['Siniestros abiertos', (int)($datos['kpis']['siniestros_abiertos'] ?? 0)],
        ];

        $hoja->fromArray($indicadores, null, 'A' . $fila);
        $this->aplicarEstiloEncabezado($hoja, 'A' . $fila . ':B' . $fila);
        $this->aplicarBordesTabla($hoja, 'A' . $fila . ':B' . ($fila + count($indicadores) - 1));
        $this->autoDimensionColumnas($hoja, 4);
        $this->aplicarFormatoPorcentual($hoja, 'B' . ($fila + 2));
        $this->aplicarFormatoMoneda($hoja, 'B' . ($fila + 3));
        $fila += count($indicadores) + 1;

        $fila = $this->escribirTablaSimple(
            $hoja,
            $fila,
            'Pólizas por categoría',
            ['Categoría', 'Total'],
            array_map(function ($row) {
                return [
                    $row['categoria'] ?? 'Sin categoría',
                    (int)($row['total'] ?? 0),
                ];
            }, $datos['polizasPorCategoria'] ?? [])
        );

        $fila = $this->escribirTablaSimple(
            $hoja,
            $fila + 1,
            'Distribución por estado de pólizas',
            ['Estado', 'Total'],
            array_map(function ($row) {
                return [
                    $row['estado'] ?? 'Sin estado',
                    (int)($row['total'] ?? 0),
                ];
            }, $datos['polizasPorEstado'] ?? [])
        );
    }

    private function configurarHojaPolizasPorVencer(Worksheet $hoja, array $polizas): void
    {
        $headers = ['#', 'Número de póliza', 'Agente', 'Cliente', 'Fecha fin', 'Prima'];
        $hoja->fromArray($headers, null, 'A1');
        $this->aplicarEstiloEncabezado($hoja, 'A1:F1');

        if (empty($polizas)) {
            $hoja->setCellValue('A2', 'Sin pólizas por vencer en los próximos 30 días.');
            $hoja->mergeCells('A2:F2');
            $hoja->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $fila = 2;
            foreach ($polizas as $idx => $row) {
                $hoja->fromArray([
                    $idx + 1,
                    $row['numero_poliza'] ?? '',
                    trim(($row['nombre_agente'] ?? '') . ' ' . ($row['apellido_agente'] ?? '')) ?: ($row['cedula_agente'] ?? ''),
                    trim(($row['nombre_cliente'] ?? '') . ' ' . ($row['apellido_cliente'] ?? '')),
                    $row['fecha_fin'] ?? '',
                    (float)($row['monto_prima_total'] ?? ($row['monto_prima'] ?? 0)),
                ], null, 'A' . $fila);
                $fila++;
            }
            $this->aplicarBordesTabla($hoja, 'A1:F' . ($fila - 1));
            $this->aplicarFormatoMoneda($hoja, 'F2:F' . ($fila - 1));
        }

        $this->autoDimensionColumnas($hoja, 6);
    }

    private function configurarHojaRanking(Worksheet $hoja, array $ranking): void
    {
        $headers = ['Posición', 'Agente', 'Pólizas gestionadas', 'Monto primas'];
        $hoja->fromArray($headers, null, 'A1');
        $this->aplicarEstiloEncabezado($hoja, 'A1:D1');

        if (empty($ranking)) {
            $hoja->setCellValue('A2', 'Sin información de productividad disponible.');
            $hoja->mergeCells('A2:D2');
            $hoja->getStyle('A2')->getFont()->setItalic(true);
        } else {
            $fila = 2;
            foreach ($ranking as $idx => $row) {
                $hoja->fromArray([
                    $idx + 1,
                    trim(($row['nombre'] ?? '') . ' ' . ($row['apellido'] ?? '')) ?: ($row['cedula_agente'] ?? ''),
                    (int)($row['num_polizas'] ?? 0),
                    (float)($row['monto_primas'] ?? 0),
                ], null, 'A' . $fila);
                $fila++;
            }
            $this->aplicarBordesTabla($hoja, 'A1:D' . ($fila - 1));
            $this->aplicarFormatoEntero($hoja, 'C2:C' . ($fila - 1));
            $this->aplicarFormatoMoneda($hoja, 'D2:D' . ($fila - 1));
        }

        $this->autoDimensionColumnas($hoja, 4);
    }

    private function aplicarEstiloEncabezado(Worksheet $hoja, string $rango): void
    {
        $style = $hoja->getStyle($rango);
        $style->getFont()->setBold(true);
        $style->getFont()->getColor()->setRGB('111827');
        $style->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEFF1FF');
    }

    private function aplicarBordesTabla(Worksheet $hoja, string $rango): void
    {
        $hoja->getStyle($rango)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFE5E7EB');
    }

    private function autoDimensionColumnas(Worksheet $hoja, int $cantidadColumnas): void
    {
        for ($i = 1; $i <= $cantidadColumnas; $i++) {
            $columna = $this->columnaPorIndice($i);
            $hoja->getColumnDimension($columna)->setAutoSize(true);
        }
    }

    private function aplicarFormatoMoneda(Worksheet $hoja, string $rango): void
    {
        $hoja->getStyle($rango)->getNumberFormat()->setFormatCode('#,##0.00');
    }

    private function aplicarFormatoPorcentual(Worksheet $hoja, string $celda): void
    {
        $hoja->getStyle($celda)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }

    private function aplicarFormatoEntero(Worksheet $hoja, string $rango): void
    {
        $hoja->getStyle($rango)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
    }

    private function escribirTablaSimple(Worksheet $hoja, int $filaInicio, string $titulo, array $encabezados, array $rows): int
    {
        $hoja->setCellValue('A' . $filaInicio, $titulo);
        $hoja->getStyle('A' . $filaInicio)->getFont()->setBold(true);
        $fila = $filaInicio + 1;

        $columnas = count($encabezados);
        $ultimaColumna = $this->columnaPorIndice($columnas);
        $hoja->fromArray($encabezados, null, 'A' . $fila);
        $this->aplicarEstiloEncabezado($hoja, 'A' . $fila . ':' . $ultimaColumna . $fila);
        $fila++;

        if (empty($rows)) {
            $hoja->setCellValue('A' . $fila, 'Sin datos disponibles');
            $hoja->mergeCells('A' . $fila . ':' . $ultimaColumna . $fila);
            $fila++;
        } else {
            foreach ($rows as $row) {
                $hoja->fromArray($row, null, 'A' . $fila);
                $fila++;
            }
            $this->aplicarBordesTabla($hoja, 'A' . ($filaInicio + 1) . ':' . $ultimaColumna . ($fila - 1));
        }

        $this->autoDimensionColumnas($hoja, $columnas);
        return $fila;
    }

    private function columnaPorIndice(int $indice): string
    {
        $letras = '';
        while ($indice > 0) {
            $modulo = ($indice - 1) % 26;
            $letras = chr(65 + $modulo) . $letras;
            $indice = (int)(($indice - $modulo) / 26);
        }
        return $letras;
    }
}
