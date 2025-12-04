<?php
// controladorSiniestro.php - CORREGIDO
session_start();
require_once dirname(__DIR__) . '/modelo/modeloSiniestro.php';

// Devolver JSON
header('Content-Type: application/json');

$modeloSiniestro = new ModeloSiniestro();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

// Verificar si hay sesión activa
if (!isset($_SESSION['rol'])) {
    // Para desarrollo, establecer valores por defecto
    $_SESSION['rol'] = 'agente';
    $_SESSION['agente_cedula'] = 'V12345678';
    $_SESSION['usuario_nombre'] = 'Santiago Rodriguez';
}

// Determinar el rol del usuario
$rol = $_SESSION['rol'] ?? 'agente';
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678';

switch ($accion) {
    // OBTENER SINIESTRO POR ID
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

    // CREAR NUEVO SINIESTRO
    case 'crear_siniestro':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        $data = [
            'id_poliza' => $_POST['id_poliza'] ?? '',
            'fecha_incidente' => $_POST['fecha_incidente'] ?? date('Y-m-d'),
            'descripcion' => $_POST['descripcion'] ?? '',
            'monto_reclamo' => $_POST['monto_reclamo'] ?? 0.0,
            'estado' => $_POST['estado'] ?? 'ABIERTO'
        ];
        
        $resultado = $modeloSiniestro->crearSiniestro($data, $cedula_agente);
        $respuesta = $resultado;
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

    default:
        $respuesta['message'] = 'Acción no reconocida: ' . $accion;
        break;
}

echo json_encode($respuesta);
?>