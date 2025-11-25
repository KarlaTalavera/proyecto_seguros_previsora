<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuarioActual = $_SESSION['datos_usuario'] ?? null;
$permisosSesion = [];
if (isset($_SESSION['permisos_usuario']) && is_array($_SESSION['permisos_usuario'])) {
    $permisosSesion = $_SESSION['permisos_usuario'];
}

if (!function_exists('controladorPoliza_esAdmin')) {
    function controladorPoliza_esAdmin($usuario)
    {
        if (!$usuario) {
            return false;
        }
        if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
            return strtolower((string)$usuario->getNombreRol()) === 'administrador';
        }
        if (is_array($usuario) && isset($usuario['rol'])) {
            return strtolower((string)$usuario['rol']) === 'administrador' || strtoupper((string)$usuario['rol']) === 'ADMIN';
        }
        return false;
    }

    function controladorPoliza_esAgente($usuario)
    {
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

    function controladorPoliza_tienePermiso($permiso, $usuario, array $permisosSesion)
    {
        if (!$permiso) {
            return true;
        }
        if (controladorPoliza_esAdmin($usuario)) {
            return true;
        }
        if (!controladorPoliza_esAgente($usuario)) {
            return true;
        }
        return in_array($permiso, $permisosSesion, true);
    }

    function controladorPoliza_normalizarCategoriaNombre($nombre)
    {
        $nombre = strtolower(trim((string)$nombre));
        $replacements = [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'ñ' => 'n',
        ];
        $nombre = strtr($nombre, $replacements);
        return preg_replace('/[^a-z0-9]+/', '', $nombre);
    }

    function controladorPoliza_permisoDesdeCategoriaNombre($nombre)
    {
        $slug = controladorPoliza_normalizarCategoriaNombre($nombre);
        $map = [
            'personas' => 'poliza_categoria_personas',
            'automovil' => 'poliza_categoria_automovil',
            'vehicular' => 'poliza_categoria_automovil',
            'patrimonial' => 'poliza_categoria_patrimoniales',
            'patrimoniales' => 'poliza_categoria_patrimoniales',
        ];
        return $map[$slug] ?? null;
    }

    function controladorPoliza_obtenerPermisoCategoriaPorId($idCategoria, $modelo)
    {
        static $cache = [];
        $idCategoria = (int)$idCategoria;
        if ($idCategoria <= 0) {
            return null;
        }
        if (!array_key_exists($idCategoria, $cache)) {
            $categoria = $modelo->obtenerCategoriaPorId($idCategoria);
            $cache[$idCategoria] = $categoria ? controladorPoliza_permisoDesdeCategoriaNombre($categoria['nombre'] ?? '') : null;
        }
        return $cache[$idCategoria];
    }

    function controladorPoliza_categoriaPermitida($idCategoria, $usuario, array $permisosSesion, $modelo)
    {
        if (controladorPoliza_esAdmin($usuario) || !controladorPoliza_esAgente($usuario)) {
            return true;
        }
        $permisoNecesario = controladorPoliza_obtenerPermisoCategoriaPorId($idCategoria, $modelo);
        if (!$permisoNecesario) {
            return true;
        }
        return controladorPoliza_tienePermiso($permisoNecesario, $usuario, $permisosSesion);
    }

    function controladorPoliza_tipoPermitido($idTipoPoliza, $usuario, array $permisosSesion, $modelo)
    {
        if (controladorPoliza_esAdmin($usuario) || !controladorPoliza_esAgente($usuario)) {
            return true;
        }
        $categoriaId = $modelo->obtenerCategoriaIdPorTipo((int)$idTipoPoliza);
        if ($categoriaId === null) {
            return false;
        }
        return controladorPoliza_categoriaPermitida($categoriaId, $usuario, $permisosSesion, $modelo);
    }

    function controladorPoliza_denegarPermiso()
    {
        echo json_encode(['success' => false, 'message' => 'No tiene permiso para realizar esta acción.']);
        exit;
    }

    function controladorPoliza_requierePermiso($permiso, $usuario, array $permisosSesion)
    {
        if (!controladorPoliza_tienePermiso($permiso, $usuario, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
    }
}

if (!$usuarioActual || !($_SESSION['usuario_conectado'] ?? false)) {
    echo json_encode(['success' => false, 'message' => 'Sesión no válida. Inicie sesión nuevamente.']);
    exit;
}

$modelo = new ModeloPoliza();
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$response = ['success' => false, 'message' => 'Accion no reconocida'];

switch ($accion) {
    case 'categorias':
        if (!controladorPoliza_tienePermiso('poliza_crear', $usuarioActual, $permisosSesion) &&
            !controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
        $categorias = $modelo->obtenerCategorias();
        if (controladorPoliza_esAgente($usuarioActual)) {
            $categorias = array_values(array_filter($categorias, function ($categoria) use ($usuarioActual, $permisosSesion, $modelo) {
                $idCategoria = isset($categoria['id_categoria']) ? (int)$categoria['id_categoria'] : 0;
                if ($idCategoria <= 0) {
                    return true;
                }
                return controladorPoliza_categoriaPermitida($idCategoria, $usuarioActual, $permisosSesion, $modelo);
            }));
        }
        $response = ['success' => true, 'data' => $categorias];
        break;

    case 'ramos':
        if (!controladorPoliza_tienePermiso('poliza_crear', $usuarioActual, $permisosSesion) &&
            !controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
        $id = isset($_GET['id_categoria']) ? (int)$_GET['id_categoria'] : (isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0);
        if ($id <= 0) {
            $response = ['success' => false, 'message' => 'Categoria no valida'];
        } else {
            if (!controladorPoliza_categoriaPermitida($id, $usuarioActual, $permisosSesion, $modelo)) {
                controladorPoliza_denegarPermiso();
            }
            $response = ['success' => true, 'data' => $modelo->obtenerRamosPorCategoria($id)];
        }
        break;

    case 'coberturas':
        if (!controladorPoliza_tienePermiso('poliza_crear', $usuarioActual, $permisosSesion) &&
            !controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
        $id = isset($_GET['id_tipo_poliza']) ? (int)$_GET['id_tipo_poliza'] : (isset($_POST['id_tipo_poliza']) ? (int)$_POST['id_tipo_poliza'] : 0);
        if ($id <= 0) {
            $response = ['success' => false, 'message' => 'Ramo no valido'];
        } else {
            if (!controladorPoliza_tipoPermitido($id, $usuarioActual, $permisosSesion, $modelo)) {
                controladorPoliza_denegarPermiso();
            }
            $response = ['success' => true, 'data' => $modelo->obtenerCoberturasPorRamo($id)];
        }
        break;

    case 'agentes':
        if (!controladorPoliza_tienePermiso('poliza_crear', $usuarioActual, $permisosSesion) &&
            !controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
        $response = ['success' => true, 'data' => $modelo->obtenerAgentesActivos()];
        break;

    case 'clientes':
        if (!controladorPoliza_tienePermiso('poliza_crear', $usuarioActual, $permisosSesion) &&
            !controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
            controladorPoliza_denegarPermiso();
        }
        $response = ['success' => true, 'data' => $modelo->obtenerClientes()];
        break;

    case 'listar':
        controladorPoliza_requierePermiso('poliza_ver_lista', $usuarioActual, $permisosSesion);
        $cedula = $_GET['cedula_agente'] ?? $_POST['cedula_agente'] ?? null;
        if (is_string($cedula)) {
            $cedula = trim($cedula);
        } else {
            $cedula = null;
        }
        if (controladorPoliza_esAgente($usuarioActual)) {
            $cedula = $usuarioActual->getCedula();
        }
        $polizas = $modelo->obtenerPolizas($cedula ?: null);
        foreach ($polizas as &$poliza) {
            $id = isset($poliza['id_poliza']) ? (int)$poliza['id_poliza'] : 0;
            if ($id > 0) {
                $idAttr = htmlspecialchars((string)$id, ENT_QUOTES, 'UTF-8');
                $acciones = [];
                if (controladorPoliza_tienePermiso('poliza_editar', $usuarioActual, $permisosSesion)) {
                    $acciones[] = '<span class="poliza-accion" data-action="editar" data-id="' . $idAttr . '" title="Editar"><i class="fas fa-pencil-alt"></i></span>';
                }
                if (controladorPoliza_tienePermiso('poliza_eliminar', $usuarioActual, $permisosSesion)) {
                    $acciones[] = '<span class="poliza-accion" data-action="eliminar" data-id="' . $idAttr . '" title="Eliminar"><i class="fas fa-trash"></i></span>';
                }
                if (controladorPoliza_tienePermiso('poliza_ver_lista', $usuarioActual, $permisosSesion)) {
                    $acciones[] = '<span class="poliza-accion" data-action="detalle" data-id="' . $idAttr . '" title="Detalle"><i class="fas fa-eye"></i></span>';
                }
                $poliza['acciones'] = implode('', $acciones);
            } else {
                $poliza['acciones'] = '';
            }
        }
        unset($poliza);
        $response = ['success' => true, 'data' => $polizas];
        break;

    case 'detalle':
        controladorPoliza_requierePermiso('poliza_ver_lista', $usuarioActual, $permisosSesion);
        $id = isset($_GET['id_poliza']) ? (int)$_GET['id_poliza'] : (isset($_POST['id_poliza']) ? (int)$_POST['id_poliza'] : 0);
        if ($id <= 0) {
            $response = ['success' => false, 'message' => 'Identificador de póliza no válido'];
            break;
        }
        $detalle = $modelo->obtenerPolizaPorId($id);
        if ($detalle) {
            if (controladorPoliza_esAgente($usuarioActual) && isset($detalle['cedula_agente']) && method_exists($usuarioActual, 'getCedula')) {
                if ($detalle['cedula_agente'] !== $usuarioActual->getCedula()) {
                    controladorPoliza_denegarPermiso();
                }
            }
            $response = ['success' => true, 'data' => $detalle];
        } else {
            $response = ['success' => false, 'message' => 'No se encontró la póliza solicitada'];
        }
        break;

    case 'siguiente_numero':
        controladorPoliza_requierePermiso('poliza_crear', $usuarioActual, $permisosSesion);
        $numero = $modelo->obtenerProximoNumeroPoliza();
        if ($numero) {
            $response = ['success' => true, 'numero' => $numero];
        } else {
            $response = ['success' => false, 'message' => 'No se pudo obtener el número de póliza'];
        }
        break;

    case 'eliminar':
        controladorPoliza_requierePermiso('poliza_eliminar', $usuarioActual, $permisosSesion);
        $id = isset($_POST['id_poliza']) ? (int)$_POST['id_poliza'] : 0;
        if ($id <= 0) {
            $response = ['success' => false, 'message' => 'Identificador de póliza no válido'];
            break;
        }
        if (controladorPoliza_esAgente($usuarioActual) && method_exists($usuarioActual, 'getCedula')) {
            $detalle = $modelo->obtenerPolizaPorId($id);
            if (!$detalle || $detalle['cedula_agente'] !== $usuarioActual->getCedula()) {
                controladorPoliza_denegarPermiso();
            }
        }
        $response = $modelo->eliminarPoliza($id);
        break;

    case 'actualizar':
        controladorPoliza_requierePermiso('poliza_editar', $usuarioActual, $permisosSesion);
        $payload = [];
        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false && !empty($raw)) {
            $payload = json_decode($raw, true) ?: [];
        } else {
            $payload = $_POST;
        }

        if (!is_array($payload)) {
            $response = ['success' => false, 'message' => 'Datos de entrada invalidos'];
            break;
        }

        $id = isset($payload['id_poliza']) ? (int)$payload['id_poliza'] : (isset($_POST['id_poliza']) ? (int)$_POST['id_poliza'] : 0);
        if ($id <= 0) {
            $response = ['success' => false, 'message' => 'Identificador de póliza no válido'];
            break;
        }

        if (controladorPoliza_esAgente($usuarioActual) && method_exists($usuarioActual, 'getCedula')) {
            $detalle = $modelo->obtenerPolizaPorId($id);
            if (!$detalle || $detalle['cedula_agente'] !== $usuarioActual->getCedula()) {
                controladorPoliza_denegarPermiso();
            }
            $payload['cedula_agente'] = $usuarioActual->getCedula();
        }

        if (isset($payload['coberturas']) && is_string($payload['coberturas'])) {
            $payload['coberturas'] = explode(',', $payload['coberturas']);
        }
        if (!empty($payload['coberturas']) && is_array($payload['coberturas'])) {
            $payload['coberturas'] = array_map('intval', $payload['coberturas']);
        } else {
            $payload['coberturas'] = [];
        }

        if (isset($payload['id_tipo_poliza'])) {
            $payload['id_tipo_poliza'] = (int)$payload['id_tipo_poliza'];
            if (!controladorPoliza_tipoPermitido($payload['id_tipo_poliza'], $usuarioActual, $permisosSesion, $modelo)) {
                controladorPoliza_denegarPermiso();
            }
        }

        if (isset($payload['estado'])) {
            $payload['estado'] = strtoupper(trim($payload['estado']));
        }

        unset($payload['numero_poliza'], $payload['id_poliza']);
        $resultado = $modelo->actualizarPoliza($id, $payload);
        $response = $resultado;
        if (!isset($response['success'])) {
            $response['success'] = false;
        }
        break;

    case 'crear':
        controladorPoliza_requierePermiso('poliza_crear', $usuarioActual, $permisosSesion);
        $payload = [];
        $raw = file_get_contents('php://input');
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false && !empty($raw)) {
            $payload = json_decode($raw, true) ?: [];
        } else {
            $payload = $_POST;
        }

        if (!is_array($payload)) {
            $response = ['success' => false, 'message' => 'Datos de entrada invalidos'];
            break;
        }

        if (controladorPoliza_esAgente($usuarioActual) && method_exists($usuarioActual, 'getCedula')) {
            $payload['cedula_agente'] = $usuarioActual->getCedula();
        }

        if (isset($payload['coberturas']) && is_string($payload['coberturas'])) {
            $payload['coberturas'] = explode(',', $payload['coberturas']);
        }
        if (!empty($payload['coberturas']) && is_array($payload['coberturas'])) {
            $payload['coberturas'] = array_map('intval', $payload['coberturas']);
        } else {
            $payload['coberturas'] = [];
        }

        unset($payload['numero_poliza']);
        $payload['estado'] = 'ACTIVA';
        $payload['id_tipo_poliza'] = isset($payload['id_tipo_poliza']) ? (int)$payload['id_tipo_poliza'] : 0;
        if (!controladorPoliza_tipoPermitido($payload['id_tipo_poliza'], $usuarioActual, $permisosSesion, $modelo)) {
            controladorPoliza_denegarPermiso();
        }
        $payload['id_cliente'] = isset($payload['id_cliente']) ? (int)$payload['id_cliente'] : 0;
        $payload['numero_cuotas'] = isset($payload['numero_cuotas']) ? (int)$payload['numero_cuotas'] : 0;
        $payload['monto_prima_total'] = isset($payload['monto_prima_total']) ? (float)$payload['monto_prima_total'] : 0.0;

        $resultado = $modelo->crearPoliza($payload);
        $response = $resultado;
        if (!isset($response['success'])) {
            $response['success'] = false;
        }
        break;

    default:
        $response = ['success' => false, 'message' => 'Accion no reconocida'];
}

echo json_encode($response);
