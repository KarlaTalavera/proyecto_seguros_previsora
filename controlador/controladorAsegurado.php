<?php
// Agregar esto AL PRINCIPIO, antes de cualquier otra cosa
error_reporting(E_ALL);
ini_set('display_errors', 1); // Temporalmente para debugging
ini_set('log_errors', 1);

// Incluir las clases NECESARIAS antes de session_start()
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/ModeloAsegurado.php';

// Ahora sí iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['datos_usuario']) || !($_SESSION['usuario_conectado'] ?? false)) {
    // Guardar mensaje de error en sesión
    $_SESSION['mensaje'] = 'Sesión no válida. Inicie sesión nuevamente.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../index.php?error=session');
    exit;
}

// Obtener usuario actual
$usuarioActual = $_SESSION['datos_usuario'];

// Verificar que sea un objeto válido
if (!is_object($usuarioActual)) {
    $_SESSION['mensaje'] = 'Error en los datos de sesión. Por favor, inicie sesión nuevamente.';
    $_SESSION['tipo_mensaje'] = 'danger';
    header('Location: ../index.php?error=session');
    exit;
}

// Determinar rol
$esAgente = false;
$esAdmin = false;
$cedulaUsuario = '';

// Verificar que los métodos existan antes de llamarlos
if (method_exists($usuarioActual, 'getNombreRol')) {
    $rol = strtolower($usuarioActual->getNombreRol());
    $esAgente = ($rol === 'agente');
    $esAdmin = ($rol === 'administrador');
}

if (method_exists($usuarioActual, 'getCedula')) {
    $cedulaUsuario = $usuarioActual->getCedula();
}

$cedulaAgenteFiltro = $esAgente ? $cedulaUsuario : null;
$accion = $_POST['accion'] ?? '';
$mensaje = '';
$tipo = 'success';

try {
    $modelo = new ModeloAsegurado();
    
    switch ($accion) {
        case 'crear':
            // Validar campos requeridos
            $camposRequeridos = ['id_poliza', 'nombre', 'apellido', 'fecha_nacimiento', 'sexo'];
            $camposFaltantes = [];
            
            foreach ($camposRequeridos as $campo) {
                if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
                    $camposFaltantes[] = $campo;
                }
            }
            
            if (!empty($camposFaltantes)) {
                throw new Exception('Campos requeridos faltantes: ' . implode(', ', $camposFaltantes));
            }
            
            // Preparar datos
            $datos = [
                'id_poliza' => (int)$_POST['id_poliza'],
                'cedula' => !empty($_POST['cedula']) ? $_POST['cedula'] : null,
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'parentesco' => !empty($_POST['parentesco']) ? $_POST['parentesco'] : null,
                'sexo' => $_POST['sexo']
            ];
            
            // Validaciones adicionales
            if ($datos['id_poliza'] <= 0) {
                throw new Exception('ID de póliza inválido');
            }
            
            if (!in_array($datos['sexo'], ['M', 'F'])) {
                throw new Exception('Sexo no válido');
            }
            
            // Crear asegurado
            $resultado = $modelo->crearAsegurado($datos);
            
            if (!$resultado['success']) {
                throw new Exception($resultado['message']);
            }
            
            $mensaje = 'Asegurado creado exitosamente';
            break;
            
        case 'actualizar':
            // Validar que el ID esté presente
            if (!isset($_POST['id_asegurado']) || empty($_POST['id_asegurado'])) {
                throw new Exception('ID de asegurado no especificado');
            }
            
            // Validar campos requeridos
            $camposRequeridos = ['nombre', 'apellido', 'fecha_nacimiento', 'sexo'];
            $camposFaltantes = [];
            
            foreach ($camposRequeridos as $campo) {
                if (!isset($_POST[$campo]) || empty(trim($_POST[$campo]))) {
                    $camposFaltantes[] = $campo;
                }
            }
            
            if (!empty($camposFaltantes)) {
                throw new Exception('Campos requeridos faltantes: ' . implode(', ', $camposFaltantes));
            }
            
            // Preparar datos
            $datos = [
                'cedula' => !empty($_POST['cedula']) ? $_POST['cedula'] : null,
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'parentesco' => !empty($_POST['parentesco']) ? $_POST['parentesco'] : null,
                'sexo' => $_POST['sexo']
            ];
            
            // Validar sexo
            if (!in_array($datos['sexo'], ['M', 'F'])) {
                throw new Exception('Sexo no válido');
            }
            
            // Actualizar asegurado
            $resultado = $modelo->actualizarAsegurado((int)$_POST['id_asegurado'], $datos);
            
            if (!$resultado['success']) {
                throw new Exception($resultado['message']);
            }
            
            $mensaje = 'Asegurado actualizado exitosamente';
            break;
            
        case 'eliminar':
            // Validar que el ID esté presente
            if (!isset($_POST['id_asegurado']) || empty($_POST['id_asegurado'])) {
                throw new Exception('ID de asegurado no especificado');
            }
            
            // Verificar permisos para agentes
            if ($esAgente) {
                $asegurado = $modelo->obtenerAseguradoPorId((int)$_POST['id_asegurado']);
                if ($asegurado && isset($asegurado['cedula_agente'])) {
                    if ($asegurado['cedula_agente'] !== $cedulaUsuario) {
                        throw new Exception('No tiene permisos para eliminar este asegurado');
                    }
                }
            }
            
            // Eliminar asegurado
            $resultado = $modelo->eliminarAsegurado((int)$_POST['id_asegurado']);
            
            if (!$resultado['success']) {
                throw new Exception($resultado['message']);
            }
            
            $mensaje = 'Asegurado eliminado exitosamente';
            break;
            
        default:
            throw new Exception('Acción no reconocida');
    }
    
} catch (Exception $e) {
    $mensaje = $e->getMessage();
    $tipo = 'danger';
    error_log('Error en controladorAsegurado: ' . $e->getMessage());
}

// Guardar mensaje en sesión y redirigir
$_SESSION['mensaje'] = $mensaje;
$_SESSION['tipo_mensaje'] = $tipo;
header('Location: ../index.php?vista=gestionAsegurado');
exit;
?>