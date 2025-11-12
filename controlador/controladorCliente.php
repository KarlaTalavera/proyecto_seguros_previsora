<?php
require_once '../modelo/modeloCliente.php';

header('Content-Type: application/json');

$modeloCliente = new ModeloCliente();
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

switch ($accion) {
    case 'crear_cliente':
        $data = [
            'cedula_asegurado' => $_POST['cedula_asegurado'] ?? '',
            'nombre_o_empresa' => $_POST['nombre_o_empresa'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'telefono'         => $_POST['telefono'] ?? '',
            'tipo'             => $_POST['tipo'] ?? 'Natural', 
            'direccion'        => $_POST['direccion'] ?? '',
        ];
        
        if (empty($data['cedula_asegurado']) || empty($data['nombre_o_empresa']) || empty($data['email']) || empty($data['telefono'])) {
            $respuesta['message'] = 'Faltan campos obligatorios.';
            break;
        }

        $respuesta = $modeloCliente->crearCliente($data);
        break;

    case 'actualizar_cliente':
        $data = [
            'id_cliente'       => (int)($_POST['id_cliente'] ?? 0),
            'cedula_asegurado' => $_POST['cedula_asegurado'] ?? '',
            'nombre_o_empresa' => $_POST['nombre_o_empresa'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'telefono'         => $_POST['telefono'] ?? '',
            'tipo'             => $_POST['tipo'] ?? 'Natural',
            'direccion'        => $_POST['direccion'] ?? '',
        ];

        if (empty($data['id_cliente']) || empty($data['cedula_asegurado']) || empty($data['nombre_o_empresa']) || empty($data['email']) || empty($data['telefono'])) {
            $respuesta['message'] = 'Faltan campos obligatorios o ID de cliente.';
            break;
        }

        $respuesta = $modeloCliente->actualizarCliente($data);
        break;

    case 'eliminar_cliente':
        $id_cliente = (int)($_POST['id_cliente'] ?? 0);
        if ($id_cliente > 0) {
            $respuesta = $modeloCliente->eliminarCliente($id_cliente);
        } else {
            $respuesta['message'] = 'ID de cliente inválido.';
        }
        break;

    default:
        break;
}

echo json_encode($respuesta);
?>