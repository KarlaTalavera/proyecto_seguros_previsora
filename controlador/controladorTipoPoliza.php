<?php
require_once dirname(__DIR__) . '/modelo/ModeloTipoPoliza.php';

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['datos_usuario']) || !($_SESSION['usuario_conectado'] ?? false)) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida. Inicie sesión nuevamente.']);
    exit;
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$modelo = new ModeloTipoPoliza();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';

switch ($accion) {
    case 'listar':
        $tipos = $modelo->obtenerTiposPolizaCompletos();
        echo json_encode(['success' => true, 'data' => $tipos]);
        break;

    case 'obtener':
        $id = isset($_GET['id_tipo_poliza']) ? (int)$_GET['id_tipo_poliza'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        $tipo = $modelo->obtenerTipoPorId($id);
        if ($tipo) {
            echo json_encode(['success' => true, 'data' => $tipo]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tipo de póliza no encontrado']);
        }
        break;

    case 'coberturas':
        $id = isset($_GET['id_tipo_poliza']) ? (int)$_GET['id_tipo_poliza'] : 0;
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        $coberturas = $modelo->obtenerCoberturasPorTipo($id);
        echo json_encode(['success' => true, 'data' => $coberturas]);
        break;

    case 'crear':
        if (!isset($_POST['nombre']) || !isset($_POST['id_categoria'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        $nombre = trim($_POST['nombre']);
        $id_categoria = (int)$_POST['id_categoria'];
        
        if (empty($nombre) || $id_categoria <= 0) {
            echo json_encode(['success' => false, 'message' => 'Nombre y categoría son requeridos']);
            exit;
        }
        
        $resultado = $modelo->crearTipoPoliza($nombre, $id_categoria);
        echo json_encode($resultado);
        break;

    case 'actualizar':
        if (!isset($_POST['id_tipo_poliza']) || !isset($_POST['nombre']) || !isset($_POST['id_categoria'])) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        $id = (int)$_POST['id_tipo_poliza'];
        $nombre = trim($_POST['nombre']);
        $id_categoria = (int)$_POST['id_categoria'];
        
        if ($id <= 0 || empty($nombre) || $id_categoria <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        
        $resultado = $modelo->actualizarTipoPoliza($id, $nombre, $id_categoria);
        echo json_encode($resultado);
        break;

    case 'eliminar':
        if (!isset($_POST['id_tipo_poliza'])) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            exit;
        }
        
        $id = (int)$_POST['id_tipo_poliza'];
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        
        $resultado = $modelo->eliminarTipoPoliza($id);
        echo json_encode($resultado);
        break;

    case 'estadisticas':
        $estadisticas = $modelo->obtenerEstadisticasTipos();
        echo json_encode(['success' => true, 'data' => $estadisticas]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
}
?>