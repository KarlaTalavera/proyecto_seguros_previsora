<?php
header('Content-Type: application/json');

require_once dirname(__DIR__) . '/modelo/modeloPermiso.php';

// Validar que se reciba una acción
if (!isset($_REQUEST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada.']);
    exit;
}

$modeloPermiso = new ModeloPermiso();
$action = $_REQUEST['action'];

switch ($action) {
    case 'get_agent_permissions':
        // Validar que se reciba la cédula del agente
        if (!isset($_GET['cedula'])) {
            echo json_encode(['success' => false, 'message' => 'Cédula del agente no proporcionada.']);
            exit;
        }

        $cedula_agente = $_GET['cedula'];
        
        // Obtener todos los permisos disponibles y los del agente
        $todos_los_permisos = $modeloPermiso->obtenerTodosLosPermisos();
        $permisos_del_agente = $modeloPermiso->obtenerPermisosDeAgente($cedula_agente);

        if ($todos_los_permisos === false || $permisos_del_agente === false) {
            echo json_encode(['success' => false, 'message' => 'Error al consultar la base de datos.']);
        } else {
            echo json_encode([
                'success' => true,
                'data' => [
                    'all_permissions' => $todos_los_permisos,
                    'agent_permissions' => $permisos_del_agente
                ]
            ]);
        }
        break;

    case 'update_agent_permissions':
        // Validar que se reciba la cédula y los permisos
        if (!isset($_POST['cedula']) || !isset($_POST['permisos'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos para actualizar.']);
            exit;
        }

        $cedula_agente = $_POST['cedula'];
        // Asegurarse de que permisos sea un array, incluso si llega vacío
        $permisos = is_array($_POST['permisos']) ? $_POST['permisos'] : [];

        // Actualizar los permisos en el modelo
        $resultado = $modeloPermiso->actualizarPermisosDeAgente($cedula_agente, $permisos);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Permisos actualizados correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar los permisos.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
        break;
}
?>