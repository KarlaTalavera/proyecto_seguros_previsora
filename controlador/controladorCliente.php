<?php
require_once '../modelo/modeloCliente.php';

header('Content-Type: application/json');

$modeloCliente = new ModeloCliente();
$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

// DEPURACIÓN: Mostrar lo que llega
error_log("Acción recibida: " . $accion);
error_log("POST recibido: " . print_r($_POST, true));

switch ($accion) {
    case 'listar_clientes':
        $clientes = $modeloCliente->obtenerTodosLosClientes();
        echo json_encode(['success' => true, 'data' => $clientes]);
        exit;

    case 'crear_cliente':
        $data = [
            'cedula_asegurado' => $_POST['cedula_asegurado'] ?? '',
            'nombre'           => $_POST['nombre'] ?? '',
            'apellido'         => $_POST['apellido'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'telefono'         => $_POST['telefono'] ?? '',
            'direccion'        => $_POST['direccion'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '', // ¡Agrega esta línea!
        ];
        
        // DEPURACIÓN: Verificar cada campo
        error_log("Datos para crear cliente: " . print_r($data, true));
        
        $camposFaltantes = [];
        if (empty($data['cedula_asegurado'])) $camposFaltantes[] = 'cedula_asegurado';
        if (empty($data['nombre'])) $camposFaltantes[] = 'nombre';
        if (empty($data['apellido'])) $camposFaltantes[] = 'apellido';
        if (empty($data['email'])) $camposFaltantes[] = 'email';
        if (empty($data['telefono'])) $camposFaltantes[] = 'telefono';
        
        if (!empty($camposFaltantes)) {
            error_log("Campos faltantes: " . implode(', ', $camposFaltantes));
            $respuesta['message'] = 'Faltan campos obligatorios: ' . implode(', ', $camposFaltantes);
            break;
        }

        $respuesta = $modeloCliente->crearCliente($data);
        break;

    case 'actualizar_cliente':
        $data = [
            'id_cliente'       => (int)($_POST['id_cliente'] ?? 0),
            'cedula_asegurado' => $_POST['cedula_asegurado'] ?? '',
            'nombre'           => $_POST['nombre'] ?? '',
            'apellido'         => $_POST['apellido'] ?? '',
            'email'            => $_POST['email'] ?? '',
            'telefono'         => $_POST['telefono'] ?? '',
            'direccion'        => $_POST['direccion'] ?? '',
            'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? '', // ¡Agrega esta línea!
        ];

        // DEPURACIÓN: Verificar cada campo
        error_log("Datos para actualizar cliente: " . print_r($data, true));
        
        $camposFaltantes = [];
        if (empty($data['id_cliente'])) $camposFaltantes[] = 'id_cliente';
        if (empty($data['cedula_asegurado'])) $camposFaltantes[] = 'cedula_asegurado';
        if (empty($data['nombre'])) $camposFaltantes[] = 'nombre';
        if (empty($data['apellido'])) $camposFaltantes[] = 'apellido';
        if (empty($data['email'])) $camposFaltantes[] = 'email';
        if (empty($data['telefono'])) $camposFaltantes[] = 'telefono';
        
        if (!empty($camposFaltantes)) {
            error_log("Campos faltantes: " . implode(', ', $camposFaltantes));
            $respuesta['message'] = 'Faltan campos obligatorios: ' . implode(', ', $camposFaltantes);
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

error_log("Respuesta enviada: " . print_r($respuesta, true));
echo json_encode($respuesta);
?>