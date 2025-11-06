<?php
require_once dirname(__DIR__) . '/modelo/modeloReporte.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

header('Content-Type: application/json');
if (session_status() == PHP_SESSION_NONE) session_start();

$accion = $_REQUEST['accion'] ?? '';
$modelo = new ModeloReporte();
$usuario = $_SESSION['datos_usuario'] ?? null; // instancia de modeloUsuario si autenticado
$rol = $usuario ? $usuario->getNombreRol() : null;

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

    case 'r4': // cuotas vencidas
        $ced = null;
        if ($rol === 'agente' && $usuario) {
            $ced = $usuario->getCedula();
        } elseif (isset($_GET['cedula_agente'])) {
            $ced = $_GET['cedula_agente'];
        }
        $data = $modelo->carteraPendiente($ced);
        if ($data !== false) $response = ['success' => true, 'data' => $data];
        else $response = ['success' => false, 'message' => 'Error al obtener cartera.'];
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
