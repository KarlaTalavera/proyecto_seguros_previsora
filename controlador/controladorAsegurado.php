<?php
require_once dirname(__DIR__) . '/modelo/ModeloAsegurado.php';

// NO imprimir nada antes de esto
ob_start(); // Capturar cualquier salida accidental

header('Content-Type: application/json; charset=utf-8');

// Verificar si la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar sesión
if (!isset($_SESSION['datos_usuario']) || !($_SESSION['usuario_conectado'] ?? false)) {
    ob_clean(); // Limpiar cualquier salida
    echo json_encode(['success' => false, 'message' => 'Sesión no válida. Inicie sesión nuevamente.']);
    exit;
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$modelo = new ModeloAsegurado();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

// Determinar si es agente o administrador
$esAgente = false;
$esAdmin = false;
$cedulaUsuario = '';

if (is_object($usuarioActual) && method_exists($usuarioActual, 'getNombreRol')) {
    $rol = strtolower($usuarioActual->getNombreRol());
    $esAgente = ($rol === 'agente');
    $esAdmin = ($rol === 'administrador');
    
    if (method_exists($usuarioActual, 'getCedula')) {
        $cedulaUsuario = $usuarioActual->getCedula();
    }
}

// Para agentes, restringir a sus propias pólizas
$cedulaAgenteFiltro = $esAgente ? $cedulaUsuario : null;

$response = ['success' => false, 'message' => 'Accion no reconocida'];

try {
    switch ($accion) {
        case 'listar':
            $asegurados = $modelo->obtenerAseguradosCompletos($cedulaAgenteFiltro);
            $response = ['success' => true, 'data' => $asegurados];
            break;

        case 'obtener':
            $id = isset($_GET['id_asegurado']) ? (int)$_GET['id_asegurado'] : 0;
            if ($id <= 0) {
                $response = ['success' => false, 'message' => 'ID inválido'];
                break;
            }
            $asegurado = $modelo->obtenerAseguradoPorId($id);
            if ($asegurado) {
                // Verificar permisos para agentes
                if ($esAgente && $asegurado['cedula_agente'] !== $cedulaUsuario) {
                    $response = ['success' => false, 'message' => 'No tiene permisos para ver este asegurado'];
                    break;
                }
                $response = ['success' => true, 'data' => $asegurado];
            } else {
                $response = ['success' => false, 'message' => 'Asegurado no encontrado'];
            }
            break;

        case 'polizas':
            $polizas = $modelo->obtenerPolizasParaAsegurado($cedulaAgenteFiltro);
            $response = ['success' => true, 'data' => $polizas];
            break;

        case 'por_poliza':
            $id_poliza = isset($_GET['id_poliza']) ? (int)$_GET['id_poliza'] : 0;
            if ($id_poliza <= 0) {
                $response = ['success' => false, 'message' => 'ID de póliza inválido'];
                break;
            }
            $asegurados = $modelo->obtenerAseguradosPorPoliza($id_poliza);
            $response = ['success' => true, 'data' => $asegurados];
            break;

        case 'crear':
            // Verificar que tenemos todos los datos necesarios
            if (!isset($_POST['id_poliza']) || !isset($_POST['nombre']) || !isset($_POST['apellido']) || 
                !isset($_POST['fecha_nacimiento']) || !isset($_POST['sexo'])) {
                $response = ['success' => false, 'message' => 'Datos incompletos para crear el asegurado'];
                break;
            }

            // Validar datos
            $id_poliza = (int)$_POST['id_poliza'];
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $fecha_nacimiento = $_POST['fecha_nacimiento'];
            $sexo = $_POST['sexo'];
            
            if ($id_poliza <= 0) {
                $response = ['success' => false, 'message' => 'Debe seleccionar una póliza válida'];
                break;
            }
            
            if (empty($nombre) || empty($apellido)) {
                $response = ['success' => false, 'message' => 'Nombre y apellido son obligatorios'];
                break;
            }
            
            if (empty($fecha_nacimiento)) {
                $response = ['success' => false, 'message' => 'Fecha de nacimiento es obligatoria'];
                break;
            }
            
            if (!in_array($sexo, ['M', 'F'])) {
                $response = ['success' => false, 'message' => 'Sexo no válido'];
                break;
            }

            $datos = [
                'id_poliza' => $id_poliza,
                'cedula' => $_POST['cedula'] ?? null,
                'nombre' => $nombre,
                'apellido' => $apellido,
                'fecha_nacimiento' => $fecha_nacimiento,
                'parentesco' => $_POST['parentesco'] ?? null,
                'sexo' => $sexo
            ];

            $resultado = $modelo->crearAsegurado($datos);
            $response = $resultado;
            break;

        case 'actualizar':
            if (!isset($_POST['id_asegurado']) || !isset($_POST['nombre']) || !isset($_POST['apellido']) || 
                !isset($_POST['fecha_nacimiento']) || !isset($_POST['sexo'])) {
                $response = ['success' => false, 'message' => 'Datos incompletos'];
                break;
            }

            $id = (int)$_POST['id_asegurado'];
            $datos = [
                'cedula' => $_POST['cedula'] ?? null,
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'parentesco' => $_POST['parentesco'] ?? null,
                'sexo' => $_POST['sexo']
            ];

            // Verificar permisos para agentes
            if ($esAgente) {
                $asegurado = $modelo->obtenerAseguradoPorId($id);
                if (!$asegurado || $asegurado['cedula_agente'] !== $cedulaUsuario) {
                    $response = ['success' => false, 'message' => 'No tiene permisos para editar este asegurado'];
                    break;
                }
            }

            $resultado = $modelo->actualizarAsegurado($id, $datos);
            $response = $resultado;
            break;

        case 'eliminar':
            if (!isset($_POST['id_asegurado'])) {
                $response = ['success' => false, 'message' => 'ID no proporcionado'];
                break;
            }

            $id = (int)$_POST['id_asegurado'];

            // Verificar permisos para agentes
            if ($esAgente) {
                $asegurado = $modelo->obtenerAseguradoPorId($id);
                if (!$asegurado || $asegurado['cedula_agente'] !== $cedulaUsuario) {
                    $response = ['success' => false, 'message' => 'No tiene permisos para eliminar este asegurado'];
                    break;
                }
            }

            $resultado = $modelo->eliminarAsegurado($id);
            $response = $resultado;
            break;

        case 'estadisticas':
            $estadisticas = $modelo->obtenerEstadisticasAsegurados($cedulaAgenteFiltro);
            $response = ['success' => true, 'data' => $estadisticas];
            break;

        default:
            $response = ['success' => false, 'message' => 'Acción no reconocida'];
    }
} catch (Exception $e) {
    error_log('Error en controladorAsegurado: ' . $e->getMessage());
    $response = ['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()];
}

// Limpiar cualquier salida que haya ocurrido antes
ob_clean();
echo json_encode($response);
ob_end_flush();
?>