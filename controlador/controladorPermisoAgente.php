<?php
require_once '../modelo/modeloUsuario.php';
require_once '../modelo/modeloPermiso.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$sesionActiva = isset($_SESSION['usuario_conectado']) ? (bool)$_SESSION['usuario_conectado'] : false;

if (!$sesionActiva || !$usuarioActual) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'Sesión no válida.']);
    exit;
}

$esAdmin = false;
if (is_object($usuarioActual) && method_exists($usuarioActual, 'getNombreRol')) {
    $esAdmin = strtolower((string)$usuarioActual->getNombreRol()) === 'administrador';
} elseif (is_array($usuarioActual) && isset($usuarioActual['rol'])) {
    $esAdmin = strtolower((string)$usuarioActual['rol']) === 'administrador';
}

if (!$esAdmin) {
    echo json_encode(['estado' => 'error', 'mensaje' => 'No tiene permisos suficientes.']);
    exit;
}

$modeloPermiso = new ModeloPermiso();
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$respuesta = ['estado' => 'error', 'mensaje' => 'Acción no válida o no proporcionada.'];

switch ($accion) {
    // Caso para obtener la lista de permisos para un agente específico
    case 'obtener_permisos_agente':
        $cedula = isset($_GET['cedula_agente']) ? $_GET['cedula_agente'] : null;
        if ($cedula) {
            $todos_permisos = $modeloPermiso->obtenerTodosLosPermisos();
            $permisos_del_agente = $modeloPermiso->obtenerPermisosDeAgente($cedula);

            if ($todos_permisos !== false && $permisos_del_agente !== false) {
                $respuesta = [
                    'estado' => 'exito',
                    'todos_los_permisos' => $todos_permisos,
                    'permisos_del_agente' => $permisos_del_agente
                ];
            } else {
                $respuesta['mensaje'] = 'Error al consultar la base de datos.';
            }
        } else {
            $respuesta['mensaje'] = 'No se especificó la cédula del agente.';
        }
        break;

    // Caso para guardar los cambios en los permisos
    case 'actualizar_permisos_agente':
        $cedula = isset($_POST['cedula_agente']) ? $_POST['cedula_agente'] : null;
        // Si no se envía ningún permiso, será un array vacío (todos desmarcados)
        $permisos_activos = isset($_POST['permisos']) ? (array)$_POST['permisos'] : [];

        if ($cedula) {
            if ($modeloPermiso->actualizarPermisosDeAgente($cedula, $permisos_activos)) {
                $respuesta = ['estado' => 'exito', 'mensaje' => 'Los permisos se han actualizado correctamente.'];
            } else {
                $respuesta['mensaje'] = 'Error al actualizar los permisos en la base de datos.';
            }
        } else {
            $respuesta['mensaje'] = 'No se especificó la cédula del agente para la actualización.';
        }
        break;
}

// Devolvemos la respuesta en formato JSON
echo json_encode($respuesta);
?>
