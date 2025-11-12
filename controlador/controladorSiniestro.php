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
// Esto asegura que $cedula_agente nunca es NULL en modo de prueba
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V12345678';

if (!$cedula_agente && in_array($accion, ['crear_siniestro', 'obtener_siniestros_agente'])) {
     $respuesta['message'] = 'Acceso denegado: Sesión de agente no válida.';
     echo json_encode($respuesta);
     exit;
}

switch ($accion) {
    // Esta acción se podría usar para refrescar la tabla via AJAX
    case 'obtener_siniestros_agente':
        $siniestros = $modeloSiniestro->obtenerSiniestrosDeAgente($cedula_agente);
        if ($siniestros !== false) {
            $respuesta = ['success' => true, 'siniestros' => $siniestros];
        } else {
            $respuesta['message'] = 'Error al consultar la base de datos o sin siniestros registrados.';
        }
        break;

    case 'crear_siniestro':
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$cedula_agente) {
        $respuesta = ['success' => false, 'message' => 'Acceso denegado: Sesión de agente no válida o método no permitido.'];
        break;
    }

    $data = [
        'numero_poliza' => $_POST['numero_poliza'] ?? '',
        'fecha_incidente' => $_POST['fecha_incidente'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'monto_reclamo' => $_POST['monto_reclamo'] ?? 0.0,
        'estado' => $_POST['estado'] ?? 'Pendiente',
        'monto_pago' => $_POST['monto_pago'] ?? 0.0,
        'fecha_pago' => $_POST['fecha_pago'] ?? null // Enviamos null si está vacío
    ];
    
    $resultado = $modeloSiniestro->crearSiniestro($data, $cedula_agente);

    $respuesta = $resultado; // El resultado ya viene como ['success' => bool, 'message' => string]
    break;

   case 'obtener_siniestro':
        // Cargar datos para edición (GET)
        $id_siniestro = (int)($_GET['id_siniestro'] ?? 0);
        if ($id_siniestro > 0) {
            $siniestro = $modeloSiniestro->obtenerSiniestroPorId($id_siniestro);
            if ($siniestro) {
                // Mapear el estado de la BD (MAYÚSCULAS) al valor del <select> (Incial/Título)
                $siniestro['estado'] = match ($siniestro['estado']) {
                    'APROBADO' => 'Aprobado',
                    'RECHAZADO' => 'Rechazado',
                    default => 'Pendiente', 
                };
                // Asegurar que campos opcionales sean manejables
                $siniestro['monto_pago'] = $siniestro['monto_pago'] ?? 0;
                $siniestro['fecha_pago'] = $siniestro['fecha_pago'] ?? '';
                
                $respuesta = ['success' => true, 'data' => $siniestro, 'message' => 'Siniestro cargado exitosamente.'];
            } else {
                $respuesta['message'] = 'Siniestro no encontrado.';
            }
        } else {
            $respuesta['message'] = 'ID de siniestro inválido.';
        }
        break;

    case 'actualizar_siniestro':
        // Guardar datos modificados (POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             $respuesta['message'] = 'Método no permitido.';
             break;
        }

        $id_siniestro = (int)($_POST['id_siniestro_edicion'] ?? 0);
        
        if ($id_siniestro === 0) {
            $respuesta['message'] = 'ID de siniestro para edición no proporcionado.';
            break;
        }

        $data = [
            'fecha_incidente' => $_POST['fecha_incidente_edicion'] ?? '',
            'descripcion' => $_POST['descripcion_edicion'] ?? '',
            'monto_reclamo' => $_POST['monto_reclamo_edicion'] ?? 0, 
            'monto_pago' => $_POST['monto_pago_edicion'] ?? 0, 
            'fecha_pago' => $_POST['fecha_pago_edicion'] ?? '', 
            'estado' => $_POST['estado_edicion'] ?? 'Pendiente',
        ];

        $resultado = $modeloSiniestro->actualizarSiniestro($data, $id_siniestro);
        
        if ($resultado['success'] ?? false) {
             $respuesta = ['success' => true, 'message' => 'Siniestro actualizado exitosamente.'];
        } else {
            $respuesta['message'] = $resultado['message'] ?? 'Error desconocido al actualizar el siniestro.';
        }
        break;

    default:
        break;
}

echo json_encode($respuesta);
?>