<?php
require_once '../modelo/modeloPermiso.php';

// Establecer la cabecera para devolver contenido JSON
header('Content-Type: application/json');

$modeloPermiso = new ModeloPermiso();
// Usamos $_REQUEST para ser flexibles con GET o POST
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$respuesta = ['estado' => 'error', 'mensaje' => 'Acción no válida o no proporcionada.'];

switch ($accion) {
    // Caso para obtener la lista de permisos para un agente específico
    case 'obtener_permisos_agente':
        $cedula = isset($_GET['cedula_agente']) ? $_GET['cedula_agente'] : null;
        if ($cedula) {
            $todos_permisos = $modeloPermiso->obtenerTodosLosPermisos();
            $permisos_agente = $modeloPermiso->obtenerPermisosDeAgente($cedula);

            if ($todos_permisos !== false && $permisos_agente !== false) {
                // Combinamos la lista de todos los permisos con los que el agente ya tiene
                $permisos_con_estado = array_map(function($permiso) use ($permisos_agente) {
                    // Agregamos un campo 'activo' para saber si el checkbox debe estar marcado
                    $permiso['activo'] = in_array($permiso['id_permiso'], $permisos_agente);
                    return $permiso;
                }, $todos_permisos);

                $respuesta = ['estado' => 'exito', 'permisos' => $permisos_con_estado];
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
