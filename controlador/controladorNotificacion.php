<?php
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';
// Incluir definiciones de clases antes de iniciar la sesión para evitar errores
// al unserializar objetos guardados en la sesión (p.ej. instancia de modeloUsuario).
session_start();

// Siempre devolver JSON
header('Content-Type: application/json; charset=utf-8');

// Buffer output para capturar warnings/notices que emitan HTML
ob_start();

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

$modeloNotificacion = new ModeloNotificacion();
$modeloUsuario = new ModeloUsuario();

$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida'];

try {
    // Verificar sesión
    if (!isset($_SESSION['datos_usuario'])) {
        http_response_code(401);
        $respuesta = ['success' => false, 'message' => 'No autenticado'];
        throw new Exception('No autenticado');
    }

    $usuario = $_SESSION['datos_usuario'];
    $cedulaUsuario = is_object($usuario) && method_exists($usuario, 'getCedula') ? $usuario->getCedula() : '';

    switch ($accion) {
    case 'obtener_notificaciones':
        $soloNoLeidas = isset($_GET['solo_no_leidas']) ? filter_var($_GET['solo_no_leidas'], FILTER_VALIDATE_BOOLEAN) : false;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $tipo = $_GET['tipo'] ?? 'todas';
        
        $resultado = $modeloNotificacion->obtenerNotificacionesUsuario($cedulaUsuario, $limit, $soloNoLeidas, $offset, $tipo);
        
        // Formatear fechas y agregar iconos (proteger contra datos inesperados)
        if (isset($resultado['notificaciones']) && is_array($resultado['notificaciones'])) {
            foreach ($resultado['notificaciones'] as &$notif) {
                try {
                    $notif['fecha_formateada'] = !empty($notif['fecha_creacion']) ? time_elapsed_string($notif['fecha_creacion']) : '';
                } catch (Throwable $e) {
                    $notif['fecha_formateada'] = '';
                    error_log('time_elapsed_string error for notif: ' . ($e->getMessage() ?? ''));
                }
                $notif['icono'] = isset($notif['tipo']) ? obtenerIconoTipo($notif['tipo']) : obtenerIconoTipo('info');
                $notif['color'] = isset($notif['tipo']) ? obtenerColorTipo($notif['tipo']) : obtenerColorTipo('info');
            }
            unset($notif);
        }
        
        $respuesta = [
            'success' => true,
            'notificaciones' => $resultado['notificaciones'],
            'total' => $resultado['total'],
            'total_no_leidas' => $resultado['total_no_leidas']
        ];
        break;
        
    case 'marcar_leida':
        $idNotificacion = (int)($_POST['id_notificacion'] ?? 0);
        if ($idNotificacion > 0) {
            $resultado = $modeloNotificacion->marcarComoLeida($idNotificacion, $cedulaUsuario);
            $respuesta = ['success' => $resultado, 'message' => $resultado ? 'Notificación marcada como leída' : 'Error'];
        } else {
            $respuesta['message'] = 'ID de notificación inválido';
        }
        break;
        
    case 'marcar_todas_leidas':
        $resultado = $modeloNotificacion->marcarTodasComoLeidas($cedulaUsuario);
        $respuesta = ['success' => $resultado, 'message' => $resultado ? 'Todas las notificaciones marcadas como leídas' : 'Error'];
        break;
        
    case 'contar_no_leidas':
        $total = $modeloNotificacion->contarNoLeidas($cedulaUsuario);
        $respuesta = ['success' => true, 'total' => $total];
        break;
        
    case 'crear_notificacion':
        // Solo para pruebas o uso interno
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $respuesta['message'] = 'Método no permitido';
            break;
        }
        
        $destinatario = $_POST['destinatario'] ?? $cedulaUsuario;
        $titulo = $_POST['titulo'] ?? '';
        $mensaje = $_POST['mensaje'] ?? '';
        $tipo = $_POST['tipo'] ?? 'info';
        $enlace = $_POST['enlace'] ?? null;
        
        if (empty($titulo) || empty($mensaje)) {
            $respuesta['message'] = 'Título y mensaje son obligatorios';
            break;
        }
        
        $resultado = $modeloNotificacion->crearNotificacion($destinatario, $titulo, $mensaje, $tipo, $enlace);
        $respuesta = ['success' => $resultado, 'message' => $resultado ? 'Notificación creada' : 'Error'];
        break;
        
    default:
        $respuesta['message'] = 'Acción no reconocida';
        break;
    }
} catch (Throwable $e) {
    // Loguear error para debugging en server logs
    error_log('controladorNotificacion error: ' . $e->getMessage() . " in " . $e->getFile() . ':' . $e->getLine());

    // Recuperar contenido del buffer (warnings/notices) para ayudar a depurar
    $bufferSnippet = '';
    $buf = ob_get_clean();
    if (!empty($buf)) {
        $bufferSnippet = substr(strip_tags($buf), 0, 2000);
        error_log('controladorNotificacion output buffer (on exception): ' . $bufferSnippet);
    }

    // Preserve explicit response codes (e.g. 401) set earlier; only set 500 if still 200
    $currentCode = http_response_code();
    if ($currentCode === 200 && !headers_sent()) {
        http_response_code(500);
    }

    // En entorno local devolvemos detalle mínimo para depuración (quitar en producción si hace falta)
    $respuesta = ['success' => false, 'message' => 'Error del servidor', 'debug' => $e->getMessage(), 'buffer' => $bufferSnippet];
} finally {
    // Si quedó contenido en el buffer, registrarlo y descartarlo
    if (ob_get_level() > 0) {
        $remaining = ob_get_clean();
        if (!empty($remaining)) {
            $snippet = substr(strip_tags($remaining), 0, 1000);
            error_log('controladorNotificacion output buffer (finally): ' . $snippet);
        }
    }

    echo json_encode($respuesta);
}

restore_error_handler();

// Funciones auxiliares
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $string = [
        'y' => 'año',
        'm' => 'mes',
        'd' => 'día',
        'h' => 'hora',
        'i' => 'minuto',
        's' => 'segundo',
    ];
    
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? 'Hace ' . implode(', ', $string) : 'Ahora mismo';
}

function obtenerIconoTipo($tipo) {
    $iconos = [
        'info' => 'fas fa-info-circle',
        'success' => 'fas fa-check-circle',
        'warning' => 'fas fa-exclamation-triangle',
        'danger' => 'fas fa-times-circle',
        'primary' => 'fas fa-bell'
    ];
    return $iconos[$tipo] ?? 'fas fa-bell';
}

function obtenerColorTipo($tipo) {
    $colores = [
        'info' => 'primary',
        'success' => 'success',
        'warning' => 'warning',
        'danger' => 'danger',
        'primary' => 'info'
    ];
    return $colores[$tipo] ?? 'secondary';
}
?>