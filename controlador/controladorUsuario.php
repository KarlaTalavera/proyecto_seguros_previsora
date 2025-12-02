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

    case 'actualizar_usuario':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido';
            break;
        }

        $cedula_original = trim($_POST['cedula_original'] ?? '');
        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? null;

        // Validaciones básicas
        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($email)) {
            $respuesta = ['success' => false, 'message' => 'Todos los campos obligatorios son requeridos.'];
            break;
        }

        // Si la cédula cambió, validar formato
        if ($cedula !== $cedula_original && !$modelo->validarFormatoCedula($cedula)) {
            $respuesta = ['success' => false, 'message' => 'Formato de cédula inválido.'];
            break;
        }

        // Si el email cambió, verificar que no exista
        if ($email !== $modelo->obtenerUsuarioPorCedula($cedula_original)['email']) {
            if ($modelo->existeEmail($email)) {
                $respuesta = ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
                break;
            }
        }

        $result = $modelo->actualizarUsuario($cedula_original, $cedula, $nombre, $apellido, $email, $telefono, $password);
        $respuesta = $result;
        break;

    case 'eliminar_usuario':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido';
            break;
        }

        $cedula = trim($_POST['cedula'] ?? '');
        
        if (empty($cedula)) {
            $respuesta = ['success' => false, 'message' => 'Cédula requerida.'];
            break;
        }

        // Verificar que el usuario existe y es un agente
        $usuario = $modelo->obtenerUsuarioPorCedula($cedula);
        if (!$usuario || strtolower($usuario['rol']) !== 'agente') {
            $respuesta = ['success' => false, 'message' => 'No se puede eliminar: usuario no encontrado o no es un agente.'];
            break;
        }

        $result = $modelo->eliminarUsuario($cedula);
        $respuesta = $result;
        break;

    case 'actualizar_perfil':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido';
            break;
        }

        // Recibir datos
        $datos = [
            'cedula' => $_POST['cedula'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'password_nueva' => $_POST['password'] ?? '', // Puede estar vacío
            'rol_nombre' => $_POST['rol_actual'] ?? '' // Importante enviar esto desde el form
        ];

        // Verificar sesión por seguridad (que quien edita sea el dueño)
        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getCedula() !== $datos['cedula']) {
            $respuesta = ['success' => false, 'message' => 'Error de seguridad: No puede editar este perfil.'];
            break;
        }

        // Manejo del archivo (foto)
        $foto = $_FILES['foto_perfil'] ?? null;

        $resultado = $modelo->actualizarPerfil($datos, $foto);
        
        if ($resultado['success']) {
            // Actualizar la sesión con los nuevos datos para que se refleje de inmediato
            // (Esto es un truco rápido, lo ideal sería recargar el objeto usuario completo)
            $_SESSION['datos_usuario']->setNombre($datos['nombre']);
            $_SESSION['datos_usuario']->setApellido($datos['apellido']);
            $_SESSION['datos_usuario']->setEmail($datos['email']);
            $_SESSION['datos_usuario']->setTelefono($datos['telefono']);
        }

        $respuesta = $resultado;
        break;

    default:
        $respuesta = ['success' => false, 'message' => 'Acción no reconocida.'];
        break;
}

echo json_encode($respuesta);
?>