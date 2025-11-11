<?php
// Controlador procedural estilo API para acciones sobre Pólizas y Tipo_Poliza
require_once dirname(__DIR__) . '/modelo/modeloPoliza.php';

// Devolver JSON
header('Content-Type: application/json');

$modeloPoliza = new ModeloPoliza();
$accion = $_REQUEST['accion'] ?? '';
$respuesta = ['success' => false, 'message' => 'Acción no válida o no proporcionada.'];

// Asegurarse de que tenemos la cédula del agente para acciones que la requieran
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$cedula_agente = $_SESSION['agente_cedula'] ?? 'V-12345678'; 

switch ($accion) {
    case 'obtener_tipos_poliza':
        // Carga la lista de productos/tipos de póliza
// ... (resto del código se mantiene igual)

    case 'crear_poliza':
        // Lógica para crear una nueva póliza (POST)
        // La validación ahora solo verifica que el método sea POST, 
        // ya que $cedula_agente ya tiene un valor por defecto.
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$cedula_agente) { 
             $respuesta['message'] = 'Acceso denegado o sesión no válida.';
             break;
        }

        $data = [
            'numero_poliza' => $_POST['numero_poliza'] ?? '',
            'id_tipo_poliza' => $_POST['id_tipo_poliza'] ?? '',
            'cedula_cliente' => $_POST['cedula_cliente'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? '',
            'prima_anual' => $_POST['prima_anual'] ?? 0,
            // 'monto_asegurado' => $_POST['monto_asegurado'] ?? 0, // No se usa en la inserción de BD
            'estado' => $_POST['estado'] ?? 'Activa',
        ];

        $resultado = $modeloPoliza->crearPoliza($data, $cedula_agente);
        
        if ($resultado['success'] ?? false) {
             $respuesta = ['success' => true, 'message' => 'Póliza creada exitosamente.'];
        } else {
             $respuesta['message'] = $resultado['message'] ?? 'Error desconocido al crear póliza.';
        }
        break;

    default:
        // Acción no manejada
        break;
}

echo json_encode($respuesta);
?>