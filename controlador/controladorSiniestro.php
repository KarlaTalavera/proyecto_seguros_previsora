<?php
require_once '../modelo/modeloSiniestro.php';

// Establecer la cabecera para devolver contenido JSON
header('Content-Type: application/json');

$modeloSiniestro = new ModeloSiniestro();
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$respuesta = ['estado' => 'error', 'mensaje' => 'Acción no válida o no proporcionada.'];

session_start();
$cedula_agente = isset($_SESSION['usuario']) ? $_SESSION['usuario']->getCedula() : null;

switch ($accion) {
    case 'obtener_siniestros':
        if ($cedula_agente) {
            $siniestros = $modeloSiniestro->obtenerSiniestrosPorAgente($cedula_agente);
            if ($siniestros !== false) {
                $respuesta = ['estado' => 'exito', 'siniestros' => $siniestros];
            } else {
                $respuesta['mensaje'] = 'Error al consultar la base de datos.';
            }
        } else {
            $respuesta['mensaje'] = 'No se pudo identificar al agente.';
        }
        break;

    case 'obtener_siniestro':
        $id_siniestro = isset($_GET['id_siniestro']) ? $_GET['id_siniestro'] : null;
        if ($id_siniestro) {
            $siniestro = $modeloSiniestro->obtenerSiniestroPorId($id_siniestro);
            if ($siniestro) {
                $respuesta = ['estado' => 'exito', 'siniestro' => $siniestro];
            } else {
                $respuesta['mensaje'] = 'Siniestro no encontrado.';
            }
        } else {
            $respuesta['mensaje'] = 'No se especificó el ID del siniestro.';
        }
        break;

    case 'crear_siniestro':
        if ($cedula_agente) {
            $id_poliza = isset($_POST['id_poliza']) ? $_POST['id_poliza'] : null;
            $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : null;
            $monto_estimado = isset($_POST['monto_estimado']) ? $_POST['monto_estimado'] : null;

            if ($id_poliza && $descripcion && $monto_estimado) {
                $data = [
                    'id_poliza' => $id_poliza,
                    'descripcion' => $descripcion,
                    'monto_estimado' => $monto_estimado,
                    'cedula_agente' => $cedula_agente
                ];

                if ($modeloSiniestro->crearSiniestro($data)) {
                    $respuesta = ['estado' => 'exito', 'mensaje' => 'Siniestro reportado correctamente.'];
                } else {
                    $respuesta['mensaje'] = 'Error al guardar el siniestro en la base de datos.';
                }
            } else {
                $respuesta['mensaje'] = 'Todos los campos son obligatorios.';
            }
        } else {
            $respuesta['mensaje'] = 'No se pudo identificar al agente.';
        }
        break;

    case 'actualizar_siniestro':
        $id_siniestro = isset($_POST['id_siniestro']) ? $_POST['id_siniestro'] : null;
        $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : null;
        $monto_estimado = isset($_POST['monto_estimado']) ? $_POST['monto_estimado'] : null;
        $estado = isset($_POST['estado']) ? $_POST['estado'] : null;

        if ($id_siniestro && $descripcion && $monto_estimado && $estado) {
            $data = [
                'descripcion' => $descripcion,
                'monto_estimado' => $monto_estimado,
                'estado' => $estado
            ];

            if ($modeloSiniestro->actualizarSiniestro($id_siniestro, $data)) {
                $respuesta = ['estado' => 'exito', 'mensaje' => 'Siniestro actualizado correctamente.'];
            } else {
                $respuesta['mensaje'] = 'Error al actualizar el siniestro en la base de datos.';
            }
        } else {
            $respuesta['mensaje'] = 'Todos los campos son obligatorios.';
        }
        break;

    case 'obtener_polizas_activas':
        if ($cedula_agente) {
            $polizas = $modeloSiniestro->obtenerPolizasActivasAgente($cedula_agente);
            if ($polizas !== false) {
                $respuesta = ['estado' => 'exito', 'polizas' => $polizas];
            } else {
                $respuesta['mensaje'] = 'Error al consultar las pólizas.';
            }
        } else {
            $respuesta['mensaje'] = 'No se pudo identificar al agente.';
        }
        break;
}

echo json_encode($respuesta);
?>