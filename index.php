<?php
// =========================================================================
// MOVIMIENTO CLAVE: Incluir la definición de la clase modeloUsuario ANTES 
// de llamar a session_start() para que el objeto pueda ser deserializado 
// correctamente desde la sesión.
// =========================================================================
require_once 'config/conexion.php';
// Asumo que controladorUsuario o bien modeloUsuario.php contienen la clase
// Si modeloUsuario está en la carpeta 'modelo/', lo incluimos explícitamente:
require_once 'modelo/modeloUsuario.php'; 
require_once 'controlador/controladorUsuario.php';


session_start();

// Si no hay usuario en la sesión, se le redirige al login
if (!isset($_SESSION['datos_usuario'])) {
    header('Location: vista/login.php');
    exit();
}

// Obtener el rol del usuario de la sesión
$usuario = $_SESSION['datos_usuario'];
// La línea 16 ahora puede llamar al método sin problema.
$rol = $usuario->getNombreRol(); 

// Definir las vistas permitidas para cada rol
$vistas_permitidas = [
    'administrador' => ['estadisticasAdmin', 'gestionAgente', 'gestionCliente', 'polizasAdmin', 'reportesAdmin', 'siniestrosAdmin', 'login'],
    'agente' => ['estadisticasAgente', 'gestionCliente', 'polizasAgente', 'reportesAgente', 'siniestrosAgente', 'login'],
    'asegurado' => ['polizasCliente', 'solicitudCliente', 'documentacionCliente', 'login']
];

// Definir la vista por defecto para cada rol
$vista_default = [
    'administrador' => 'estadisticasAdmin',
    'agente' => 'estadisticasAgente',
    'asegurado' => 'polizasCliente'
];

// Determinar qué vista cargar
// Si se pasa 'vista' en la URL, se usa esa. Si no, se usa la default del rol.
$vista_solicitada = $_GET['vista'] ?? $vista_default[$rol];

// Verificar si la vista solicitada está permitida para el rol del usuario
if (isset($vistas_permitidas[$rol]) && in_array($vista_solicitada, $vistas_permitidas[$rol])) {
    
    // Construir la ruta al archivo de la vista
    $archivo_vista = __DIR__ . '/vista/' . $vista_solicitada . '.php';

    // Incluir la cabecera común
    require_once __DIR__ . '/vista/parte_superior.php';

    // Verificar que el archivo de la vista exista antes de incluirlo
    if (file_exists($archivo_vista)) {
        include_once $archivo_vista;
    } else {
        // Mostrar un error si el archivo de la vista no se encuentra
        echo "<div class='container-fluid'><h1 class='text-center mt-5'>Error: La vista no pudo ser cargada.</h1><p class='text-center'>El archivo '{$vista_solicitada}.php' no existe.</p></div>";
    }

    // Incluir el pie de página común
    require_once __DIR__ . '/vista/parte_inferior.php';

} else {
    // Si la vista no está permitida o el rol no es válido, mostrar un error de acceso
    require_once __DIR__ . '/vista/parte_superior.php';
    echo "<div class='container-fluid'><h1 class='text-center mt-5'>Acceso Denegado</h1><p class='text-center'>No tienes permiso para acceder a esta página.</p></div>";
    require_once __DIR__ . '/vista/parte_inferior.php';
}
?>
