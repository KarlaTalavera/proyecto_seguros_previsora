<?php
// Controlador procedural estilo API para acciones sobre Pólizas y Tipo_Poliza
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Devolver JSON
header('Content-Type: application/json');

$modeloPoliza = new ModeloPoliza();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

// Asegurarse de que tenemos la cédula del agente para acciones que la requieran
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V-12345678'; 

switch ($accion) {
    case 'obtener_tipos_poliza':
        $tipos_poliza = $modeloPoliza->obtenerTiposPoliza();
        if ($tipos_poliza !== false) {
            $respuesta = ['success' => true, 'tipos_poliza' => $tipos_poliza];
        } else {
            $respuesta['message'] = 'Error al consultar la base de datos.';
        }
        break;
    case 'crear_poliza':
    
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$cedula_agente) { 
             $respuesta['message'] = 'Acceso denegado o sesión no válida.';
             break;
        }

        $data = [
            'numero_poliza' => $_POST['numero_poliza'] ?? '',
            'id_tipo_poliza' => $_POST['id_tipo_poliza'] ?? '',
            'cedula_cliente' => $_POST['cedula_cliente'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
            'prima_anual' => $_POST['prima_anual'] ?? 0,
            // 'monto_asegurado' => $_POST['monto_asegurado'] ?? 0, // No se usa en la inserción de BD
            'estado' => $_POST['estado'] ?? 'Activa',
        ];

        $resultado = $modeloPoliza->crearPoliza($data, $cedula_agente);
        
        if ($resultado['success'] ?? false) {
             $respuesta = ['success' => true, 'message' => 'Póliza creada exitosamente.'];
        } else {
             $respuesta['message'] = $resultado['message'] ?? 'Error desconocido al crear póliza.';
        }
        break;

   case 'obtener_poliza':
        // CASO 1: CARGAR DATOS PARA EDICIÓN (Llamado desde JavaScript con el ID)
        $id_poliza = (int)($_GET['id_poliza'] ?? 0);
        if ($id_poliza > 0) {
            $poliza = $modeloPoliza->obtenerPolizaPorId($id_poliza);
            if ($poliza) {
                // CLAVE: Mapear el estado de la BD (ACTIVA/PENDIENTE) al valor del <select> (Activa/Pendiente)
                $poliza['estado'] = ($poliza['estado'] === 'ACTIVA') ? 'Activa' : 'Pendiente'; 
                $respuesta = ['success' => true, 'data' => $poliza, 'message' => 'Póliza cargada exitosamente.'];
            } else {
                $respuesta['message'] = 'Póliza no encontrada.';
            }
        } else {
            $respuesta['message'] = 'ID de póliza inválido.';
        }
        break;

    // 💡 CASO 2: ACTUALIZAR DATOS DE LA PÓLIZA (POST)
    case 'actualizar_poliza':
        // CASO 2: GUARDAR DATOS MODIFICADOS (Recibe los datos del formulario)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             $respuesta['message'] = 'Acceso denegado o método no permitido.';
             break;
        }

        $id_poliza = (int)($_POST['id_poliza_edicion'] ?? 0);
        
        if ($id_poliza === 0) {
            $respuesta['message'] = 'ID de póliza para edición no proporcionado.';
            break;
        }

        $data = [
            'numero_poliza' => $_POST['numero_poliza'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
            'prima_anual' => $_POST['prima_anual'] ?? 0, 
            'estado' => $_POST['estado'] ?? 'Activa',
        ];

        // Se llama al nuevo método de actualización del Modelo
        $resultado = $modeloPoliza->actualizarPoliza($data, $id_poliza);
        
        if ($resultado['success'] ?? false) {
             $respuesta = ['success' => true, 'message' => 'Póliza actualizada exitosamente.'];
        } else {
            $respuesta['message'] = $resultado['message'] ?? 'Error desconocido al actualizar la póliza.';
        }
        break;

    default:
        // ... (código default)
        break;
}

echo json_encode($respuesta);
?>