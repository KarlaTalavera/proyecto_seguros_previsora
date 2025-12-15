<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloSolicitud.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
if (!$usuarioActual || !($_SESSION['usuario_conectado'] ?? false)) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida.']);
    exit;
}

function solicitud_esAdmin($usuario): bool {
    if (!$usuario) {
        return false;
    }
    if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
        return strtolower((string)$usuario->getNombreRol()) === 'administrador';
    }
    if (is_array($usuario) && isset($usuario['rol'])) {
        return strtolower((string)$usuario['rol']) === 'administrador';
    }
    return false;
}

function solicitud_esAgente($usuario): bool {
    if (!$usuario) {
        return false;
    }
    if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
        return strtolower((string)$usuario->getNombreRol()) === 'agente';
    }
    if (is_array($usuario) && isset($usuario['rol'])) {
        return strtolower((string)$usuario['rol']) === 'agente';
    }
    return false;
}

function solicitud_esCliente($usuario): bool {
    if (!$usuario) {
        return false;
    }
    if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
        $rol = strtolower((string)$usuario->getNombreRol());
        return in_array($rol, ['asegurado', 'cliente'], true);
    }
    if (is_array($usuario) && isset($usuario['rol'])) {
        $rol = strtolower((string)$usuario['rol']);
        return in_array($rol, ['asegurado', 'cliente'], true);
    }
    return false;
}

function solicitud_agenteTienePermiso(string $permiso): bool {
    if (!isset($_SESSION['permisos_usuario']) || !is_array($_SESSION['permisos_usuario'])) {
        return false;
    }
    return in_array($permiso, $_SESSION['permisos_usuario'], true);
}

function solicitud_obtenerCedula($usuario): ?string {
    if (!$usuario) {
        return null;
    }
    if (is_object($usuario) && method_exists($usuario, 'getCedula')) {
        return $usuario->getCedula();
    }
    if (is_array($usuario) && isset($usuario['cedula'])) {
        return (string)$usuario['cedula'];
    }
    return null;
}

$modelo = new ModeloSolicitud();
$accion = $_REQUEST['accion'] ?? '';

switch ($accion) {
    case 'listar_cliente':
        if (!solicitud_esCliente($usuarioActual)) {
            echo json_encode(['success' => false, 'message' => 'Solo los clientes pueden consultar sus solicitudes.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        if (!$cedula) {
            echo json_encode(['success' => false, 'message' => 'No se pudo identificar al cliente.']);
            exit;
        }
        $idCliente = $modelo->obtenerIdClientePorCedula($cedula);
        if (!$idCliente) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }
        $data = $modelo->obtenerSolicitudesCliente($idCliente);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'crear_poliza':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        if (!solicitud_esCliente($usuarioActual)) {
            echo json_encode(['success' => false, 'message' => 'Solo los clientes pueden registrar solicitudes.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        $idCliente = $modelo->obtenerIdClientePorCedula($cedula ?? '');
        if (!$cedula || !$idCliente) {
            echo json_encode(['success' => false, 'message' => 'No se pudo identificar al cliente.']);
            exit;
        }
        $categoria = isset($_POST['categoria']) ? (int)$_POST['categoria'] : 0;
        $ramo = isset($_POST['ramo']) ? (int)$_POST['ramo'] : 0;
        $descripcion = $_POST['descripcion'] ?? null;
        $contacto = $_POST['contacto'] ?? null;

        $resultado = $modelo->crearSolicitudPoliza([
            'id_cliente' => $idCliente,
            'cedula_cliente' => $cedula,
            'id_categoria' => $categoria,
            'id_tipo_poliza' => $ramo,
            'descripcion' => $descripcion,
            'contacto_preferido' => $contacto,
        ]);
        echo json_encode($resultado);
        break;

    case 'crear_siniestro':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        if (!solicitud_esCliente($usuarioActual)) {
            echo json_encode(['success' => false, 'message' => 'Solo los clientes pueden registrar siniestros.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        if (!$cedula) {
            echo json_encode(['success' => false, 'message' => 'No se pudo identificar al cliente.']);
            exit;
        }
        $idPoliza = isset($_POST['poliza']) ? (int)$_POST['poliza'] : 0;
        $tipo = trim((string)($_POST['tipo'] ?? ''));
        $descripcion = $_POST['descripcion'] ?? null;
        $fecha = $_POST['fecha'] ?? null;
        $lugar = $_POST['lugar'] ?? null;

        $resultado = $modelo->crearSolicitudSiniestro([
            'id_poliza' => $idPoliza,
            'cedula_cliente' => $cedula,
            'tipo_incidente' => $tipo,
            'descripcion' => $descripcion,
            'fecha_incidente' => $fecha,
            'lugar_incidente' => $lugar,
        ]);
        echo json_encode($resultado);
        break;

    case 'cancelar_cliente':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        if (!solicitud_esCliente($usuarioActual)) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para cancelar la solicitud.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        $idCliente = $modelo->obtenerIdClientePorCedula($cedula ?? '');
        if (!$cedula || !$idCliente) {
            echo json_encode(['success' => false, 'message' => 'No se encontró información del cliente.']);
            exit;
        }
        $origen = $_POST['origen'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if (!in_array($origen, ['poliza', 'siniestro'], true) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
            exit;
        }
        echo json_encode($modelo->cancelarSolicitudCliente($origen, $id, $idCliente));
        break;

    case 'listar_asignadas':
        $esAdmin = solicitud_esAdmin($usuarioActual);
        $esAgente = solicitud_esAgente($usuarioActual);
        if (!$esAdmin && (!$esAgente || !solicitud_agenteTienePermiso('solicitud_gestionar'))) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para ver estas solicitudes.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        $data = $modelo->obtenerSolicitudesAsignadas($cedula, $esAdmin);
        echo json_encode(['success' => true, 'data' => $data]);
        break;

    case 'actualizar_estado':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        $esAdmin = solicitud_esAdmin($usuarioActual);
        $esAgente = solicitud_esAgente($usuarioActual);
        if (!$esAdmin && (!$esAgente || !solicitud_agenteTienePermiso('solicitud_gestionar'))) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para actualizar solicitudes.']);
            exit;
        }
        $origen = $_POST['origen'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $estado = $_POST['estado'] ?? '';
        $nota = $_POST['nota'] ?? null;
        if (!in_array($origen, ['poliza', 'siniestro'], true) || $id <= 0 || !$estado) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
            exit;
        }
        $cedula = solicitud_obtenerCedula($usuarioActual);
        $resultado = $modelo->actualizarEstadoSolicitud($origen, $id, $estado, $nota, $cedula, $esAdmin);
        echo json_encode($resultado);
        break;

    case 'asignar_agente':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }
        
        // Verificar que sea administrador O agente con permiso de reasignación
        $esAdmin = solicitud_esAdmin($usuarioActual);
        $esAgente = solicitud_esAgente($usuarioActual);
        $tienePermisoReasignar = $esAdmin || 
            ($esAgente && solicitud_agenteTienePermiso('solicitud_reasignar'));
        
        if (!$tienePermisoReasignar) {
            echo json_encode(['success' => false, 'message' => 'No tiene permisos para reasignar solicitudes.']);
            exit;
        }
        
        $origen = $_POST['origen'] ?? '';
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $cedulaAgente = isset($_POST['cedula_agente']) ? trim((string)$_POST['cedula_agente']) : '';
        
        if (!in_array($origen, ['poliza', 'siniestro'], true) || $id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos para la asignación.']);
            exit;
        }
        
        // Validar que se seleccionó un agente (puede ser vacío para desasignar)
        if ($cedulaAgente === '') {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un agente válido.']);
            exit;
        }
        
        // Si es agente, solo puede reasignar a sí mismo
        if ($esAgente && !$esAdmin) {
            $cedulaActual = solicitud_obtenerCedula($usuarioActual);
            if ($cedulaAgente !== $cedulaActual) {
                echo json_encode(['success' => false, 'message' => 'Solo puede reasignar solicitudes a su propia cuenta.']);
                exit;
            }
        }
        
        $resultado = $modelo->asignarSolicitudAgente($origen, $id, $cedulaAgente);
        echo json_encode($resultado);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
        break;
}
