<?php
// Controlador procedural estilo API para acciones sobre usuario (crear, listar, login minimal)
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Devolver JSON
header('Content-Type: application/json');

$modelo = new modeloUsuario();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida.'];

switch ($accion) {
    case 'crear_usuario':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getNombreRol() !== 'administrador') {
            $respuesta = ['success' => false, 'message' => 'No autorizado.'];
            break;
        }

        $rolSolicitado = strtolower(trim($_POST['rol'] ?? 'agente'));
        $rolesPermitidos = [
            'agente' => ['id' => 2, 'nombre' => 'agente'],
            'cliente' => ['id' => 3, 'nombre' => 'cliente'],
            'asegurado' => ['id' => 3, 'nombre' => 'asegurado']
        ];

        if (!isset($rolesPermitidos[$rolSolicitado])) {
            $respuesta = ['success' => false, 'message' => 'Rol no permitido para esta operación.'];
            break;
        }

        $cedula = trim($_POST['cedula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? null;
        $direccion = trim($_POST['direccion'] ?? '') ?: null;
        $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '') ?: null;

        $rolDestino = $rolesPermitidos[$rolSolicitado];
        $rolNombre = $rolDestino['nombre'];

        $data = [
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono,
            'password' => $password,
            'id_rol' => $rolDestino['id'],
            'rol_nombre' => $rolNombre,
            'direccion' => $direccion,
            'fecha_nacimiento' => $fechaNacimiento
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
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getNombreRol() !== 'administrador') {
            $respuesta = ['success' => false, 'message' => 'No autorizado.'];
            break;
        }

        $cedulaOriginal = trim($_POST['cedula_original'] ?? '');
        if ($cedulaOriginal === '') {
            $respuesta = ['success' => false, 'message' => 'Cédula original obligatoria.'];
            break;
        }

        $infoUsuario = $modelo->obtenerUsuarioPorCedula($cedulaOriginal);
        if (!$infoUsuario) {
            $respuesta = ['success' => false, 'message' => 'Usuario no encontrado.'];
            break;
        }

        $cedula = trim($_POST['cedula'] ?? $cedulaOriginal);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $passwordNueva = $_POST['password'] ?? '';

        if ($nombre === '' || $apellido === '' || $email === '') {
            $respuesta = ['success' => false, 'message' => 'Nombre, apellido y correo son obligatorios.'];
            break;
        }

        if ($cedula !== $cedulaOriginal && !$modelo->validarFormatoCedula($cedula)) {
            $respuesta = ['success' => false, 'message' => 'Formato de cédula inválido.'];
            break;
        }

        $datosAdmin = [
            'cedula' => $cedula,
            'cedula_original' => $cedulaOriginal,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono,
            'password_nueva' => $passwordNueva,
            'rol_nombre' => $infoUsuario['rol'] ?? '',
            'foto_actual' => $infoUsuario['foto_perfil'] ?? 'undraw_profile.svg'
        ];

        if (($infoUsuario['rol'] ?? '') !== 'administrador') {
            $datosAdmin['cedula'] = $cedulaOriginal;
        }

        $respuesta = $modelo->actualizarPerfil($datosAdmin);
        break;

    case 'eliminar_usuario':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getNombreRol() !== 'administrador') {
            $respuesta = ['success' => false, 'message' => 'No autorizado.'];
            break;
        }

        $cedula = trim($_POST['cedula'] ?? '');
        if ($cedula === '') {
            $respuesta = ['success' => false, 'message' => 'Cédula no proporcionada.'];
            break;
        }

        $respuesta = $modelo->desactivarUsuario($cedula);
        break;

    case 'actualizar_perfil':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido';
            break;
        }

        $datos = [
            'cedula' => $_POST['cedula'] ?? ($_POST['cedula_original'] ?? ''),
            'cedula_original' => $_POST['cedula_original'] ?? '',
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'email' => $_POST['email'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'password_nueva' => $_POST['password'] ?? '',
            'rol_nombre' => $_POST['rol_actual'] ?? '',
            'foto_actual' => $_POST['foto_actual'] ?? ''
        ];

        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getCedula() !== $datos['cedula_original']) {
            $respuesta = ['success' => false, 'message' => 'Error de seguridad: No puede editar este perfil.'];
            break;
        }

        $foto = $_FILES['foto_perfil'] ?? null;

        $resultado = $modelo->actualizarPerfil($datos, $foto);

        if ($resultado['success']) {
            $_SESSION['datos_usuario']->setCedula($resultado['cedula']);
            $_SESSION['datos_usuario']->setNombre($datos['nombre']);
            $_SESSION['datos_usuario']->setApellido($datos['apellido']);
            $_SESSION['datos_usuario']->setEmail($datos['email']);
            $_SESSION['datos_usuario']->setTelefono($datos['telefono']);
            if (!empty($resultado['foto'])) {
                $_SESSION['datos_usuario']->setFotoPerfil($resultado['foto']);
            }
        }

        $respuesta = $resultado;
        break;

    case 'desactivar_usuario':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta = ['success' => false, 'message' => 'Método no permitido.'];
            break;
        }

        if (!isset($_SESSION['datos_usuario']) || $_SESSION['datos_usuario']->getNombreRol() !== 'administrador') {
            $respuesta = ['success' => false, 'message' => 'No autorizado.'];
            break;
        }

        $cedula = trim($_POST['cedula'] ?? '');
        if ($cedula === '') {
            $respuesta = ['success' => false, 'message' => 'Cédula no proporcionada.'];
            break;
        }

        if (strtoupper($cedula) === strtoupper($_SESSION['datos_usuario']->getCedula())) {
            $respuesta = ['success' => false, 'message' => 'No puede desactivar su propio usuario.'];
            break;
        }

        $respuesta = $modelo->desactivarUsuario($cedula);
        break;
    default:
        $respuesta = ['success' => false, 'message' => 'Acción no reconocida.'];
        break;
}

echo json_encode($respuesta);
?>