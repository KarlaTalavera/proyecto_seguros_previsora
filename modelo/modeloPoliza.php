<?php
/**
 * Se asume que este path es correcto y que contiene la clase 'Base_Datos'.
 */
require_once dirname(__DIR__) . '/config/conexion.php'; 

/**
 * Clase que actúa como Modelo de Negocio para la entidad Póliza y Tipo_Poliza.
 */
class ModeloPoliza {
    private $db;
    
    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (\Exception $e) {
            error_log("Error al inicializar la conexión en ModeloPoliza: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Obtiene todas las pólizas asociadas a un agente.
     */
    public function obtenerPolizasDeAgente(string $cedula_agente) {
        if (!$this->db) return false;

        $sql = "SELECT p.id_poliza AS id, p.numero_poliza, t.nombre AS producto, 
                       CONCAT(u_cliente.nombre, ' ', u_cliente.apellido) AS cliente, 
                       p.fecha_vencimiento AS vencimiento, p.prima_anual AS prima, p.estado
                FROM poliza p
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u_cliente ON c.cedula_asegurado = u_cliente.cedula
                WHERE p.cedula_agente = :cedula_agente
                ORDER BY p.fecha_vencimiento DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener pólizas del agente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el listado de todos los tipos de póliza disponibles (para el <select>).
     */
    public function obtenerTiposPoliza() {
        if (!$this->db) return false;

        $sql = "SELECT id_tipo_poliza, nombre FROM tipo_poliza ORDER BY nombre ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener tipos de póliza: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lógica base para crear una nueva póliza. (Requiere lógica adicional para buscar/crear cliente).
     */
    public function crearPoliza(array $data, string $cedula_agente) {
        if (!$this->db) return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];

        // Validación básica (se omite la lógica de DB compleja por simplicidad)
        if (empty($data['numero_poliza']) || empty($data['id_tipo_poliza']) || empty($data['cedula_cliente'])) {
             return ['success' => false, 'message' => 'Faltan campos obligatorios para la creación.'];
        }

        // SIMULACIÓN: Aquí iría la lógica real para:
        // 1. Obtener o crear el id_cliente a partir de la cedula_cliente.
        // 2. Insertar la póliza con los datos provistos en la tabla `poliza`.

        // Por ahora, solo devolvemos un éxito simulado para que la UI se recargue.
        error_log("Simulación: Intento de creación de Póliza {$data['numero_poliza']} por {$cedula_agente}");
        return ['success' => true, 'message' => 'Póliza creada exitosamente (simulación de inserción).'];
    }
}