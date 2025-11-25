<?php
require_once dirname(__DIR__) . '/modelo/modeloReporte.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

$accion = $_REQUEST['accion'] ?? '';
$modelo = new ModeloReporte();
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
    'r_ramo' => 'reportes_generar_polizas',
    'r_agente_ventas' => 'reportes_generar_polizas',
    'kpis' => 'reportes_generar_polizas',
    'kpis_agente' => 'reportes_generar_polizas',
    'r_tipo_cliente' => 'reportes_generar_clientes',
    'r_siniestros' => 'reportes_generar_siniestros'
];

$permisoRequerido = $permisoPorAccion[$accion] ?? null;
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
        $response = ['success' => true, 'data' => $data];
        break;

    case 'r_ramo': // pólizas por ramo (categoría)
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->polizasPorRamo($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener pólizas por ramo.'];
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

    default:
        $response = ['success' => false, 'message' => 'Acción no reconocida.'];
}

echo json_encode($response);
?>
