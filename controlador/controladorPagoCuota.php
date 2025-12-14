<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloPagoCuota.php';
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$modelo = new ModeloPagoCuota();
$modeloPoliza = new ModeloPoliza();
$modeloNotificacion = new ModeloNotificacion();

$accion = $_REQUEST['accion'] ?? '';

$usuario = $_SESSION['datos_usuario'] ?? null;
$rolSesion = $_SESSION['rol'] ?? '';
if ($rolSesion === '') {
    if (is_object($usuario) && method_exists($usuario, 'getNombreRol')) {
        $rolSesion = strtolower((string)$usuario->getNombreRol());
        $_SESSION['rol'] = $rolSesion;
    } elseif (is_array($usuario) && isset($usuario['rol'])) {
        $rolSesion = strtolower((string)$usuario['rol']);
        $_SESSION['rol'] = $rolSesion;
    }
}
$cedulaSesion = (is_object($usuario) && method_exists($usuario, 'getCedula')) ? $usuario->getCedula() : '';

$respuesta = ['success' => false, 'message' => 'Acción no reconocida.'];

switch ($accion) {
    case 'listar_cuotas_cliente':
        if (!in_array($rolSesion, ['asegurado', 'cliente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $cuotas = $modelo->obtenerCuotasDeCliente($cedulaSesion);
        $respuesta = ['success' => true, 'cuotas' => $cuotas];
        break;

    case 'listar_reportes_cliente':
        if (!in_array($rolSesion, ['asegurado', 'cliente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $reportes = $modelo->obtenerReportesCliente($cedulaSesion);
        $respuesta = ['success' => true, 'reportes' => $reportes];
        break;

    case 'reportar_pago':
        if (!in_array($rolSesion, ['asegurado', 'cliente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }

        $idCuota = isset($_POST['id_cuota']) ? (int)$_POST['id_cuota'] : 0;
        $monto = isset($_POST['monto']) ? (float)$_POST['monto'] : 0.0;
        $referencia = trim($_POST['referencia'] ?? '');
        $nota = isset($_POST['nota']) && $_POST['nota'] !== '' ? trim($_POST['nota']) : null;
        $archivo = $_FILES['comprobante'] ?? null;

        if ($idCuota <= 0) {
            $respuesta['message'] = 'Identificador de cuota inválido.';
            break;
        }

        if ($referencia === '') {
            $respuesta['message'] = 'Debe indicar la referencia o número de transacción.';
            break;
        }

        if (!$archivo || ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $respuesta['message'] = 'Debe adjuntar el comprobante de pago.';
            break;
        }

        if (!$modelo->verificarRelacionClienteCuota($idCuota, $cedulaSesion)) {
            $respuesta['message'] = 'La cuota seleccionada no pertenece a sus pólizas.';
            break;
        }

        $validacionArchivo = validarComprobantePago($archivo);
        if ($validacionArchivo['success'] === false) {
            $respuesta = $validacionArchivo;
            break;
        }

        $rutaRelativa = $validacionArchivo['ruta_relativa'];
        $respuesta = $modelo->crearReportePago($idCuota, $cedulaSesion, $monto, $referencia, $rutaRelativa, $nota);

        if (!$respuesta['success']) {
            eliminarArchivoSiExiste($rutaRelativa);
        } else {
            // Notificar al agente sobre el nuevo reporte de pago
            $detalleReporte = $modelo->obtenerReporteDetallado($respuesta['id']);
            if ($detalleReporte && $detalleReporte['cedula_agente']) {
                $modeloNotificacion->notificarPagoCuota(
                    $respuesta['id'],
                    $detalleReporte['cedula_asegurado'],
                    $detalleReporte['cliente_nombre'],
                    $detalleReporte['monto_reportado'],
                    $detalleReporte['numero_poliza'],
                    $detalleReporte['cedula_agente']
                );
            }
        }
        break;

    case 'listar_reportes_pendientes':
        if (!in_array($rolSesion, ['administrador', 'agente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $reportes = $modelo->obtenerReportesPendientesPorRol($rolSesion, $cedulaSesion);
        $respuesta = ['success' => true, 'reportes' => $reportes];
        break;

    case 'listar_reportes_gestion':
        if (!in_array($rolSesion, ['administrador', 'agente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $estado = strtoupper(trim($_GET['estado'] ?? 'PENDIENTE'));
        if (!in_array($estado, ['PENDIENTE', 'APROBADO', 'RECHAZADO', 'TODOS'], true)) {
            $estado = 'PENDIENTE';
        }
        $reportes = $modelo->obtenerReportesPorRol($rolSesion, $cedulaSesion, $estado);
        $respuesta = ['success' => true, 'reportes' => $reportes];
        break;

    case 'obtener_metricas_gestion':
        if (!in_array($rolSesion, ['administrador', 'agente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $metricas = $modelo->obtenerMetricasGestion($rolSesion, $cedulaSesion);
        $respuesta = ['success' => true, 'metricas' => $metricas];
        break;

    case 'obtener_reporte':
        if (!in_array($rolSesion, ['administrador', 'agente', 'asegurado', 'cliente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $idReporte = isset($_GET['id_reporte']) ? (int)$_GET['id_reporte'] : 0;
        if ($idReporte <= 0) {
            $respuesta['message'] = 'Identificador de reporte inválido.';
            break;
        }
        $detalle = $modelo->obtenerReporteDetallado($idReporte);
        if (!$detalle) {
            $respuesta['message'] = 'Reporte no encontrado.';
            break;
        }
        if (in_array($rolSesion, ['asegurado', 'cliente'], true) && $detalle['cedula_asegurado'] !== $cedulaSesion) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        if ($rolSesion === 'agente' && $detalle['cedula_agente'] !== $cedulaSesion) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $respuesta = ['success' => true, 'reporte' => $detalle];
        break;

    case 'aprobar_reporte':
        if (!in_array($rolSesion, ['administrador', 'agente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }
        $idReporte = isset($_POST['id_reporte']) ? (int)$_POST['id_reporte'] : 0;
        if ($idReporte <= 0) {
            $respuesta['message'] = 'Identificador de reporte inválido.';
            break;
        }
        $detalle = $modelo->obtenerReporteDetallado($idReporte);
        if (!$detalle) {
            $respuesta['message'] = 'Reporte no encontrado.';
            break;
        }
        if ($rolSesion === 'agente' && $detalle['cedula_agente'] !== $cedulaSesion) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $respuesta = $modelo->aprobarReporte($idReporte, $cedulaSesion);
        
        // Notificar al cliente sobre la aprobación
        if ($respuesta['success']) {
            $modeloNotificacion->notificarResultadoPago(
                $idReporte,
                $detalle['cedula_asegurado'],
                'APROBADO',
                $detalle['numero_poliza'],
                ''
            );
        }
        break;

    case 'rechazar_reporte':
        if (!in_array($rolSesion, ['administrador', 'agente'], true)) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido.';
            break;
        }
        $idReporte = isset($_POST['id_reporte']) ? (int)$_POST['id_reporte'] : 0;
        $motivo = trim($_POST['motivo'] ?? '');
        if ($idReporte <= 0) {
            $respuesta['message'] = 'Identificador de reporte inválido.';
            break;
        }
        $detalle = $modelo->obtenerReporteDetallado($idReporte);
        if (!$detalle) {
            $respuesta['message'] = 'Reporte no encontrado.';
            break;
        }
        if ($rolSesion === 'agente' && $detalle['cedula_agente'] !== $cedulaSesion) {
            $respuesta['message'] = 'Acceso no autorizado.';
            break;
        }
        $respuesta = $modelo->rechazarReporte($idReporte, $cedulaSesion, $motivo);
        
        // Notificar al cliente sobre el rechazo
        if ($respuesta['success']) {
            $modeloNotificacion->notificarResultadoPago(
                $idReporte,
                $detalle['cedula_asegurado'],
                'RECHAZADO',
                $detalle['numero_poliza'],
                $motivo
            );
        }
        break;

    default:
        break;
}

echo json_encode($respuesta);

function validarComprobantePago(array $archivo): array {
    if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Error al cargar el comprobante.'];
    }

    $permitidos = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
    $extension = strtolower(pathinfo($archivo['name'] ?? '', PATHINFO_EXTENSION));

    if (!in_array($extension, $permitidos, true)) {
        return ['success' => false, 'message' => 'Formato de comprobante no permitido.'];
    }

    $tamanoMaximo = 5 * 1024 * 1024; // 5 MB
    if (($archivo['size'] ?? 0) > $tamanoMaximo) {
        return ['success' => false, 'message' => 'El comprobante supera el tamaño máximo permitido (5MB).'];
    }

    $directorio = dirname(__DIR__) . '/assets/comprobantes_pagos/';
    if (!is_dir($directorio)) {
        mkdir($directorio, 0775, true);
    }

    $nombreSeguro = 'cmp_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
    $rutaDestino = $directorio . $nombreSeguro;

    if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        return ['success' => false, 'message' => 'No se pudo guardar el comprobante.'];
    }

    $rutaRelativa = 'assets/comprobantes_pagos/' . $nombreSeguro;

    return ['success' => true, 'ruta_relativa' => $rutaRelativa];
}

function eliminarArchivoSiExiste(string $rutaRelativa): void {
    $rutaAbsoluta = dirname(__DIR__) . '/' . ltrim($rutaRelativa, '/');
    if (is_file($rutaAbsoluta)) {
        @unlink($rutaAbsoluta);
    }
}