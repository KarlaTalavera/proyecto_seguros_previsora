<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloSiniestro {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (\Exception $e) {
            error_log("Error al inicializar la conexión en ModeloSiniestro: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function obtenerSiniestrosPorAgente($cedula_agente) {
        if (!$this->db) return false;

        $sql = "SELECT s.*, p.numero_poliza, c.cedula_asegurado, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_cliente,
                       t.nombre as tipo_poliza
                FROM siniestro s
                JOIN poliza p ON s.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                WHERE s.cedula_agente_gestion = :cedula_agente
                ORDER BY s.fecha_reporte DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestros: " . $e->getMessage());
            return false;
        }
    }
    public function obtenerSiniestroPorId($id_siniestro) {
        if (!$this->db) return false;

        $sql = "SELECT s.*, p.numero_poliza, c.cedula_asegurado, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_cliente,
                       t.nombre as tipo_poliza, u.telefono, u.email,
                       dp.monto_prima, dp.fecha_inicio, dp.fecha_fin
                FROM siniestro s
                JOIN poliza p ON s.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                WHERE s.id_siniestro = :id_siniestro";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_siniestro', $id_siniestro);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestro: " . $e->getMessage());
            return false;
        }
    }

    public function crearSiniestro($data) {
        if (!$this->db) return false;

        // Generar número de siniestro automático
        $numero_siniestro = 'S-' . date('Ymd-His');

        $sql = "INSERT INTO siniestro (numero_siniestro, descripcion, monto_estimado, estado, id_poliza, cedula_agente_gestion) 
                VALUES (:numero_siniestro, :descripcion, :monto_estimado, 'ABIERTO', :id_poliza, :cedula_agente)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':numero_siniestro', $numero_siniestro);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':monto_estimado', $data['monto_estimado']);
            $stmt->bindParam(':id_poliza', $data['id_poliza']);
            $stmt->bindParam(':cedula_agente', $data['cedula_agente']);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error de DB al crear siniestro: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarSiniestro($id_siniestro, $data) {
        if (!$this->db) return false;

        $sql = "UPDATE siniestro 
                SET descripcion = :descripcion, 
                    monto_estimado = :monto_estimado,
                    estado = :estado
                WHERE id_siniestro = :id_siniestro";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':monto_estimado', $data['monto_estimado']);
            $stmt->bindParam(':estado', $data['estado']);
            $stmt->bindParam(':id_siniestro', $id_siniestro);
            
            return $stmt->execute();
        } catch (\PDOException $e) {
            error_log("Error de DB al actualizar siniestro: " . $e->getMessage());
            return false;
        }
    }

     public function obtenerPolizasActivasAgente($cedula_agente) {
        if (!$this->db) return false;

        $sql = "SELECT p.id_poliza, p.numero_poliza, 
                       CONCAT(u.nombre, ' ', u.apellido) as nombre_cliente,
                       t.nombre as tipo_poliza
                FROM poliza p
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                WHERE p.cedula_agente = :cedula_agente 
                AND p.estado = 'ACTIVA'";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener pólizas activas: " . $e->getMessage());
            return false;
        }
    }
}
?>

