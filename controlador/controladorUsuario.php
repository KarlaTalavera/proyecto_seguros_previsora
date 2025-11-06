<?php
// Controlador procedural estilo API para acciones sobre usuario (crear, listar, login minimal)
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

// Devolver JSON
header('Content-Type: application/json');

$modelo = new modeloUsuario();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida.'];

switch ($accion) {
    case 'crear_usuario':
        // Esperamos POST
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? null; // opcional

        $data = [
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono,
            'password' => $password,
            'id_rol' => 2 // agente por defecto
        ];

        $result = $modelo->crearUsuario($data);
        if (is_array($result) && ($result['success'] ?? false)) {
            $respuesta = ['success' => true, 'message' => $result['message'], 'password' => $result['password'] ?? null];
        } else {
            $mensaje = is_array($result) ? ($result['message'] ?? 'Error') : 'Error al crear usuario';
            $respuesta = ['success' => false, 'message' => $mensaje];
        }
        break;

    case 'obtener_usuarios':
        $usuarios = $modelo->obtenerTodosLosUsuarios();
        if ($usuarios !== false) {
            $respuesta = ['success' => true, 'usuarios' => $usuarios];
        } else {
            $respuesta = ['success' => false, 'message' => 'Error al obtener usuarios'];
        }
        break;

    default:
        $respuesta = ['success' => false, 'message' => 'Acción no reconocida.'];
}

echo json_encode($respuesta);
?>
