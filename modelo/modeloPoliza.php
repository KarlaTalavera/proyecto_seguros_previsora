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
                       dp.fecha_fin AS vencimiento, dp.monto_prima AS prima, p.estado
                FROM poliza p
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u_cliente ON c.cedula_asegurado = u_cliente.cedula
                JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                WHERE p.cedula_agente = :cedula_agente
                ORDER BY dp.fecha_fin DESC";

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
     * Limpia y estandariza la cédula para la búsqueda en la base de datos.
     */
    private function sanitizeCedula(string $cedula): string {
        $cedula = strtoupper(trim($cedula));
        // Elimina guiones, puntos y espacios, que causan desajustes
        $cedula = str_replace(['-', '.', ' '], '', $cedula); 
        return $cedula;
    }
    /**
     * Obtiene el ID del cliente a partir de su cédula.
     */
    private function obtenerIdClientePorCedula(string $cedula_cliente): ?int {
        if (!$this->db) {
            error_log("Error: La conexión a la DB no está disponible.");
            return null;
        }
        
        $cedula_busqueda = $this->sanitizeCedula($cedula_cliente);
        
        // Modificación: Usamos la función TRIM() de SQL para eliminar posibles espacios 
        // en la columna 'cedula' de la tabla 'usuario' antes de la comparación.
        $sql = "SELECT c.id_cliente 
                FROM cliente c
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                WHERE TRIM(u.cedula) = :cedula"; 
                
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula_busqueda);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado) {
                return (int)$resultado['id_cliente'];
            }
            
            // Si llega aquí, significa que la consulta no encontró resultados.
            error_log("No se encontró id_cliente para la cédula saneada: " . $cedula_busqueda);
            return null;
            
        } catch (\PDOException $e) {
            // Error CRÍTICO: Captura el error de la base de datos (ej. columna no existe, conexión caída)
            error_log("Error de DB (obtenerIdClientePorCedula) al buscar: {$cedula_busqueda}. Mensaje: " . $e->getMessage()); 
            // Devolvemos NULL para que 'crearPoliza' pueda lanzar un error específico.
            return null;
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
     * Lógica real para crear una nueva póliza.
     */
    public function crearPoliza(array $data, string $cedula_agente) {
        if (!$this->db) return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];

        // 1. Obtener el id_cliente
        $id_cliente = $this->obtenerIdClientePorCedula($data['cedula_cliente']);
        
        if (is_null($id_cliente)) {
            return ['success' => false, 'message' => "Cliente con cédula '{$this->sanitizeCedula($data['cedula_cliente'])}' no encontrado. Verifique la cédula o regístrelo primero."];
        }
        
        // 💡 APLICAR SANEAMIENTO A LA CÉDULA DEL AGENTE
        $cedula_agente_saneada = $this->sanitizeCedula($cedula_agente);

        try {
            $this->db->beginTransaction();

            // 2. Insertar en la tabla `poliza`
            $sql_poliza = "INSERT INTO poliza (numero_poliza, estado, id_cliente, cedula_agente, id_tipo_poliza)
                           VALUES (:numero_poliza, :estado, :id_cliente, :cedula_agente, :id_tipo_poliza)";
            $stmt_poliza = $this->db->prepare($sql_poliza);

            $estado_db = ($data['estado'] === 'Activa') ? 'ACTIVA' : 'PENDIENTE'; // Mapeo de estado
            $stmt_poliza->bindParam(':numero_poliza', $data['numero_poliza']);
            $stmt_poliza->bindParam(':estado', $estado_db);
            $stmt_poliza->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
            // USAR LA CÉDULA SANEADA DEL AGENTE
            $stmt_poliza->bindParam(':cedula_agente', $cedula_agente_saneada); 
            $stmt_poliza->bindParam(':id_tipo_poliza', $data['id_tipo_poliza'], PDO::PARAM_INT);
            $stmt_poliza->execute();

            $id_poliza = $this->db->lastInsertId();
            if (!$id_poliza) {
                 $this->db->rollBack();
                 return ['success' => false, 'message' => 'Error al obtener el ID de la nueva póliza.'];
            }

            // 3. Insertar en la tabla `detalle_poliza` (se asume fecha_inicio es la fecha actual del sistema)
            $sql_detalle = "INSERT INTO detalle_poliza (id_poliza, fecha_inicio, fecha_fin, monto_prima)
                            VALUES (:id_poliza, CURDATE(), :fecha_fin, :monto_prima)";
            $stmt_detalle = $this->db->prepare($sql_detalle);
            
            $stmt_detalle->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmt_detalle->bindParam(':fecha_fin', $data['fecha_vencimiento']);
            $stmt_detalle->bindParam(':monto_prima', $data['prima_anual']); 
            $stmt_detalle->execute();
            
            $this->db->commit();
            error_log("Póliza ID: $id_poliza creada exitosamente por $cedula_agente");
            return ['success' => true, 'message' => 'Póliza y detalles creados exitosamente.'];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error de DB al crear póliza (transacción): " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos durante la creación de la póliza: ' . $e->getMessage()];
        }
    }
}