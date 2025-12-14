<?php
session_start();
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';
require_once dirname(__DIR__) . '/modelo/modeloUsuario.php';

header('Content-Type: application/json');

$modeloNotificacion = new ModeloNotificacion();
$modeloUsuario = new ModeloUsuario();

$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida'];

// Verificar sesión
if (!isset($_SESSION['datos_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
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
        
        // Formatear fechas y agregar iconos
        foreach ($resultado['notificaciones'] as &$notif) {
            $notif['fecha_formateada'] = time_elapsed_string($notif['fecha_creacion']);
            $notif['icono'] = obtenerIconoTipo($notif['tipo']);
            $notif['color'] = obtenerColorTipo($notif['tipo']);
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

echo json_encode($respuesta);

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