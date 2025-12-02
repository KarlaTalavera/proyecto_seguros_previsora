<?php
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';

// Devolver JSON
header('Content-Type: application/json');

$modeloSiniestro = new ModeloSiniestro();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determinar el rol del usuario
$rol = $_SESSION['rol'] ?? null;
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678';

switch ($accion) {
    // OBTENER TODOS LOS SINIESTROS (para administrador)
    case 'obtener_todos_siniestros':
        if ($rol !== 'administrador') {
            $respuesta['message'] = 'Acceso denegado: Se requiere rol de administrador.';
            break;
        }
        
        $siniestros = $modeloSiniestro->obtenerTodosSiniestros();
        if ($siniestros !== false) {
            $respuesta = ['success' => true, 'siniestros' => $siniestros];
        } else {
            $respuesta['message'] = 'Error al consultar la base de datos o sin siniestros registrados.';
        }
        break;

    // OBTENER SINIESTROS DE AGENTE (para agentes)
    case 'obtener_siniestros_agente':
        if (!$cedula_agente) {
            $respuesta['message'] = 'Acceso denegado: Sesión de agente no válida.';
            break;
        }
        
        $siniestros = $modeloSiniestro->obtenerSiniestrosDeAgente($cedula_agente);
        if ($siniestros !== false) {
            $respuesta = ['success' => true, 'siniestros' => $siniestros];
        } else {
            $respuesta['message'] = 'Error al consultar la base de datos o sin siniestros registrados.';
        }
        break;

    // CREAR NUEVO SINIESTRO
    case 'crear_siniestro':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        // Para administrador, usar agente del formulario; para agente, usar sesión
        $agente_gestion = ($rol === 'administrador' && isset($_POST['cedula_agente_gestion'])) 
            ? $_POST['cedula_agente_gestion'] 
            : $cedula_agente;

        if (!$agente_gestion) {
            $respuesta['message'] = 'Agente de gestión no especificado.';
            break;
        }

        $data = [
            'id_poliza' => $_POST['id_poliza'] ?? '',
            'fecha_incidente' => $_POST['fecha_incidente'] ?? date('Y-m-d'),
            'descripcion' => $_POST['descripcion'] ?? '',
            'monto_reclamo' => $_POST['monto_reclamo'] ?? 0.0,
            'estado' => $_POST['estado'] ?? 'ABIERTO'
        ];
        
        $resultado = $modeloSiniestro->crearSiniestro($data, $agente_gestion);
        $respuesta = $resultado;
        break;

    // OBTENER DETALLES DE SINIESTRO
    case 'obtener_siniestro':
        $id_siniestro = (int)($_GET['id_siniestro'] ?? 0);
        if ($id_siniestro > 0) {
            $siniestro = $modeloSiniestro->obtenerSiniestroPorId($id_siniestro);
            if ($siniestro) {
                $respuesta = ['success' => true, 'data' => $siniestro];
            } else {
                $respuesta['message'] = 'Siniestro no encontrado.';
            }
        } else {
            $respuesta['message'] = 'ID de siniestro inválido.';
        }
        break;

    // ACTUALIZAR SINIESTRO
    case 'actualizar_siniestro':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }

        $id_siniestro = (int)($_POST['id_siniestro'] ?? 0);
        
        if ($id_siniestro === 0) {
            $respuesta['message'] = 'ID de siniestro no proporcionado.';
            break;
        }

        $data = [
            'fecha_incidente' => $_POST['fecha_incidente'] ?? '',
            'descripcion' => $_POST['descripcion'] ?? '',
            'monto_reclamo' => $_POST['monto_reclamo'] ?? 0,
            'estado' => $_POST['estado'] ?? 'ABIERTO'
        ];

        $resultado = $modeloSiniestro->actualizarSiniestro($data, $id_siniestro);
        $respuesta = $resultado;
        break;

    // REGISTRAR PAGO
    case 'registrar_pago':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }

        $id_siniestro = (int)($_POST['id_siniestro'] ?? 0);
        $monto_pago = (float)($_POST['monto_pago'] ?? 0);
        $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($id_siniestro > 0 && $monto_pago > 0) {
            $resultado = $modeloSiniestro->registrarPago($id_siniestro, $monto_pago, $fecha_pago);
            $respuesta = $resultado;
        } else {
            $respuesta = ['success' => false, 'message' => 'Datos de pago inválidos.'];
        }
        break;

    // ELIMINAR SINIESTRO
    case 'eliminar_siniestro':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }

        $id_siniestro = (int)($_POST['id_siniestro'] ?? 0);
        
        if ($id_siniestro > 0) {
            // Verificar permisos (solo administrador puede eliminar)
            if ($rol !== 'administrador') {
                $respuesta['message'] = 'Acceso denegado: Se requiere rol de administrador para eliminar siniestros.';
                break;
            }
            
            $resultado = $modeloSiniestro->eliminarSiniestro($id_siniestro);
            $respuesta = $resultado;
        } else {
            $respuesta['message'] = 'ID de siniestro inválido.';
        }
        break;

    // OBTENER PÓLIZAS ACTIVAS (para formularios)
    case 'obtener_polizas_activas':
        $polizas = $modeloSiniestro->obtenerPolizasActivas();
        $respuesta = ['success' => true, 'polizas' => $polizas];
        break;

    // OBTENER AGENTES ACTIVOS (para formularios)
    case 'obtener_agentes_activos':
        $agentes = $modeloSiniestro->obtenerAgentesActivos();
        $respuesta = ['success' => true, 'agentes' => $agentes];
        break;

    // ESTADÍSTICAS
    case 'obtener_estadisticas':
        if ($rol !== 'administrador') {
            $respuesta['message'] = 'Acceso denegado: Se requiere rol de administrador.';
            break;
        }
        
        $estadisticas = $modeloSiniestro->obtenerEstadisticas();
        $respuesta = ['success' => true, 'estadisticas' => $estadisticas];
        break;

    // BUSCAR SINIESTROS
    case 'buscar_siniestros':
        $filtros = [
            'estado' => $_GET['estado'] ?? '',
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'numero_poliza' => $_GET['numero_poliza'] ?? ''
        ];
        
        $siniestros = $modeloSiniestro->buscarSiniestros($filtros);
        $respuesta = ['success' => true, 'siniestros' => $siniestros];
        break;

    default:
        $respuesta['message'] = 'Acción no reconocida.';
        break;
}

echo json_encode($respuesta);
?>