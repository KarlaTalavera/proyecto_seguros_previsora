<?php
require_once dirname(__DIR__) . '/modelo/modeloReporte.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/servicios/ReporteDashboardExporter.php';

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

$accion = $_REQUEST['accion'] ?? '';
$modelo = new ModeloReporte();
$exporter = new ReporteDashboardExporter();
$usuario = $_SESSION['datos_usuario'] ?? null; // instancia de modeloUsuario si autenticado
$rol = $usuario ? $usuario->getNombreRol() : null;
$permisosSesion = [];
if (isset($_SESSION['permisos_usuario']) && is_array($_SESSION['permisos_usuario'])) {
    $permisosSesion = $_SESSION['permisos_usuario'];
}

if (!function_exists('controladorReporte_esAdmin')) {
    function controladorReporte_esAdmin($usuario)
    {
        return $usuario && method_exists($usuario, 'getNombreRol') && $usuario->getNombreRol() === 'administrador';
    }

    function controladorReporte_esAgente($usuario)
    {
        return $usuario && method_exists($usuario, 'getNombreRol') && $usuario->getNombreRol() === 'agente';
    }

    function controladorReporte_tienePermiso($permiso, $usuario, array $permisosSesion)
    {
        if (!$permiso) {
            return true;
        }
        if (controladorReporte_esAdmin($usuario)) {
            return true;
        }
        if (!controladorReporte_esAgente($usuario)) {
            return true;
        }
        return in_array($permiso, $permisosSesion, true);
    }

    function controladorReporte_denegarPermiso()
    {
        echo json_encode(['success' => false, 'message' => 'No tiene permiso para generar este reporte.']);
        exit;
    }

    function controladorReporte_requierePermiso($permiso, $usuario, array $permisosSesion)
    {
        if (!controladorReporte_tienePermiso($permiso, $usuario, $permisosSesion)) {
            controladorReporte_denegarPermiso();
        }
    }
}

if (!$usuario || !($_SESSION['usuario_conectado'] ?? false)) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida. Inicie sesión nuevamente.']);
    exit;
}

$permisoPorAccion = [
    'r1' => 'reportes_generar_polizas',
    'r4' => 'reportes_generar_polizas',
    'r8' => 'reportes_generar_polizas',
    'r_categoria' => 'reportes_generar_polizas',
    'r_ramo' => 'reportes_generar_polizas',
    'r_agente_ventas' => 'reportes_generar_polizas',
    'kpis' => 'reportes_generar_polizas',
    'kpis_agente' => 'reportes_generar_polizas',
    'r_tipo_cliente' => 'reportes_generar_clientes',
    'r_siniestros' => 'reportes_generar_siniestros',
    'exportar_dashboard' => 'reportes_generar_polizas',
    'exportar_grafico' => 'reportes_generar_polizas'
];

$permisoRequerido = $permisoPorAccion[$accion] ?? null;
if ($rol === 'agente') {
    $accionesPropias = ['r1', 'r4', 'r8', 'r_categoria', 'r_ramo', 'r_agente_ventas', 'kpis', 'kpis_agente', 'r_tipo_cliente', 'r_siniestros', 'exportar_grafico'];
    if (in_array($accion, $accionesPropias, true)) {
        $permisoRequerido = null;
    }
}
if ($permisoRequerido) {
    controladorReporte_requierePermiso($permisoRequerido, $usuario, $permisosSesion);
}

$response = ['success' => false, 'message' => 'Acción no reconocida.'];

switch ($accion) {
    case 'r1': // pólizas por vencer
        $dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 30;
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->polizasPorVencer($dias, $ced);
        if ($data === false) {
            $response = ['success' => false, 'message' => 'Error al obtener pólizas por vencer.'];
            // Si se solicita debug, adjuntar el mensaje del modelo (solo en desarrollo)
            if (isset($_GET['debug']) && method_exists($modelo, 'getLastError')) {
                $response['error'] = $modelo->getLastError();
            }
        } else {
            $response = ['success' => true, 'data' => $data];
        }
        break;

    case 'r4': // distribución de pólizas por estado
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->polizasPorEstado($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener distribución de pólizas.'];
        break;

    case 'r8': // ranking productividad
        $months = isset($_GET['months']) ? (int)$_GET['months'] : 12;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        // Admin puede ver todos; agente solo su propio ranking (o su posición)
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->rankingProductividad($months, $ced, $limit);
        if ($data === false) {
            $message = 'Error al obtener ranking de productividad.';
            if (isset($_GET['debug']) && method_exists($modelo, 'getLastError')) {
                $response = ['success' => false, 'message' => $message, 'error' => $modelo->getLastError()];
            } else {
                $response = ['success' => false, 'message' => $message];
            }
        } else {
            $response = ['success' => true, 'data' => $data];
        }
        break;

    case 'r_categoria': // pólizas por categoría
    case 'r_ramo': // alias legacy
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->polizasPorCategoria($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener pólizas por categoría.'];
        break;

    case 'r_siniestros': // tendencia de siniestros
        $months = isset($_GET['months']) ? (int)$_GET['months'] : 12;
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->tendenciaSiniestros($months, $ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener tendencia de siniestros.'];
        break;

    case 'r_agente_ventas': // ventas por mes para agente (ventas vs meta)
        $months = isset($_GET['months']) ? (int)$_GET['months'] : 6;
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->ventasPorMesAgente($months, $ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener ventas por mes.'];
        break;

    case 'r_tipo_cliente': // polizas por tipo de cliente para agente
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->polizasPorTipoClienteAgente($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener pólizas por tipo de cliente.'];
        break;

    case 'kpis_agente':
        // KPIs específicos para la vista del agente
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->kpisAgente($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener KPIs de agente.'];
        break;

    case 'kpis':
        // Resumen KPI para dashboard
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->kpisResumen($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener KPIs.'];
        break;

    case 'exportar_dashboard':
        $formato = strtolower((string)($_GET['formato'] ?? $_POST['formato'] ?? 'pdf'));
        if (!in_array($formato, ['pdf', 'xlsx'], true)) {
            $response = ['success' => false, 'message' => 'Formato de exportación no soportado.'];
            break;
        }

        $ced = null;
        $esAdmin = controladorReporte_esAdmin($usuario);
        if (!$esAdmin && controladorReporte_esAgente($usuario)) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }

        $datosDashboard = $exporter->obtenerDatosDashboard($ced, $esAdmin);
        $nombre = '';
        if ($usuario && method_exists($usuario, 'getNombre')) {
            $nombre = trim((string)$usuario->getNombre() . ' ' . (string)$usuario->getApellido());
        }
        if ($nombre === '' && isset($_SESSION['datos_usuario']) && is_array($_SESSION['datos_usuario'])) {
            $datosArray = $_SESSION['datos_usuario'];
            $nombre = trim(($datosArray['nombre'] ?? '') . ' ' . ($datosArray['apellido'] ?? ''));
        }
        if ($nombre === '') {
            $nombre = 'Usuario';
        }

        $contexto = [
            'titulo' => $esAdmin ? 'Dashboard gerencial' : 'Dashboard del agente',
            'generado_por' => $nombre,
            'fecha' => date('d/m/Y H:i')
        ];
        $timestamp = date('Ymd_His');

        if ($formato === 'pdf') {
            $contenido = $exporter->generarPdf($datosDashboard, $contexto);
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="dashboard_' . $timestamp . '.pdf"');
            echo $contenido;
            exit;
        }

        if ($formato === 'xlsx') {
            $contenido = $exporter->generarExcel($datosDashboard, $contexto);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="dashboard_' . $timestamp . '.xlsx"');
            header('Content-Length: ' . strlen($contenido));
            echo $contenido;
            exit;
        }
        break;

    case 'exportar_grafico':
        $grafico = strtolower(trim((string)($_GET['grafico'] ?? $_POST['grafico'] ?? '')));
        $formato = strtolower((string)($_GET['formato'] ?? $_POST['formato'] ?? 'pdf'));
        $graficasPermitidas = ['categoria', 'ramo', 'estado', 'ranking', 'siniestros', 'balance'];
        $aliasGrafico = ['ramo' => 'categoria'];
        $graficoInterno = $aliasGrafico[$grafico] ?? $grafico;

        // Seguridad: El reporte de balance es solo para administradores.
        if ($graficoInterno === 'balance' && !controladorReporte_esAdmin($usuario)) {
            controladorReporte_denegarPermiso();
        }

        if (!in_array($grafico, $graficasPermitidas, true)) {
            $response = ['success' => false, 'message' => 'Gráfica no soportada.'];
            break;
        }
        if (!in_array($formato, ['pdf', 'xlsx'], true)) {
            $response = ['success' => false, 'message' => 'Formato de exportación no soportado.'];
            break;
        }

        $ced = null;
        $esAdmin = controladorReporte_esAdmin($usuario);
        if (!$esAdmin && controladorReporte_esAgente($usuario)) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }

        $datosDashboard = $exporter->obtenerDatosDashboard($ced, $esAdmin);

        $nombre = '';
        if ($usuario && method_exists($usuario, 'getNombre')) {
            $nombre = trim((string)$usuario->getNombre() . ' ' . (string)$usuario->getApellido());
        }
        if ($nombre === '' && isset($_SESSION['datos_usuario']) && is_array($_SESSION['datos_usuario'])) {
            $datosArray = $_SESSION['datos_usuario'];
            $nombre = trim(($datosArray['nombre'] ?? '') . ' ' . ($datosArray['apellido'] ?? ''));
        }
        if ($nombre === '') {
            $nombre = 'Usuario';
        }

        $contexto = [
            'generado_por' => $nombre,
            'fecha' => date('d/m/Y H:i'),
        ];
        $timestamp = date('Ymd_His');

        try {
            if ($formato === 'pdf') {
                $contenido = $exporter->generarPdfGrafico($graficoInterno, $datosDashboard, $contexto);
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="grafico_' . $graficoInterno . '_' . $timestamp . '.pdf"');
                echo $contenido;
                exit;
            }

            if ($formato === 'xlsx') {
                $contenido = $exporter->generarExcelGrafico($graficoInterno, $datosDashboard, $contexto);
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="grafico_' . $graficoInterno . '_' . $timestamp . '.xlsx"');
                header('Content-Length: ' . strlen($contenido));
                echo $contenido;
                exit;
            }
        } catch (InvalidArgumentException $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
        }
        break;

    default:
        $response = ['success' => false, 'message' => 'Acción no reconocida.'];
}

echo json_encode($response);
?>
