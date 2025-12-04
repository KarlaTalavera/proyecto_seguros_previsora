<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloTipoPoliza {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log('Error inicializando DB en ModeloTipoPoliza: ' . $e->getMessage());
        }
    }

    // Obtener todos los tipos de póliza con información de categoría
    public function obtenerTiposPolizaCompletos() {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        tp.id_tipo_poliza,
                        tp.nombre AS nombre_tipo,
                        tp.id_categoria,
                        cp.nombre AS nombre_categoria,
                        cp.id_categoria
                    FROM tipo_poliza tp
                    JOIN categoria_poliza cp ON tp.id_categoria = cp.id_categoria
                    ORDER BY cp.nombre, tp.nombre";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerTiposPolizaCompletos: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener un tipo específico por ID
    public function obtenerTipoPorId(int $id_tipo_poliza) {
        if (!$this->db) return null;
        try {
            $sql = "SELECT 
                        tp.id_tipo_poliza,
                        tp.nombre AS nombre_tipo,
                        tp.id_categoria,
                        cp.nombre AS nombre_categoria
                    FROM tipo_poliza tp
                    JOIN categoria_poliza cp ON tp.id_categoria = cp.id_categoria
                    WHERE tp.id_tipo_poliza = :id
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerTipoPorId: ' . $e->getMessage());
            return null;
        }
    }

    // Crear nuevo tipo de póliza
    public function crearTipoPoliza(string $nombre, int $id_categoria) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        try {
            // Verificar si ya existe un tipo con ese nombre
            $sqlCheck = "SELECT COUNT(*) FROM tipo_poliza WHERE nombre = :nombre";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Ya existe un tipo de póliza con ese nombre'];
            }

            $sql = "INSERT INTO tipo_poliza (nombre, id_categoria) VALUES (:nombre, :categoria)";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();

            $id = $this->db->lastInsertId();
            return ['success' => true, 'id_tipo_poliza' => $id, 'message' => 'Tipo de póliza creado exitosamente'];
        } catch (PDOException $e) {
            error_log('Error crearTipoPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear el tipo de póliza'];
        }
    }

    // Actualizar tipo de póliza
    public function actualizarTipoPoliza(int $id_tipo_poliza, string $nombre, int $id_categoria) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        try {
            // Verificar si otro tipo tiene el mismo nombre
            $sqlCheck = "SELECT COUNT(*) FROM tipo_poliza WHERE nombre = :nombre AND id_tipo_poliza != :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'Ya existe otro tipo de póliza con ese nombre'];
            }

            $sql = "UPDATE tipo_poliza SET nombre = :nombre, id_categoria = :categoria WHERE id_tipo_poliza = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':categoria', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();

            return ['success' => true, 'message' => 'Tipo de póliza actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log('Error actualizarTipoPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el tipo de póliza'];
        }
    }

    // Eliminar tipo de póliza
    public function eliminarTipoPoliza(int $id_tipo_poliza) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        try {
            // Verificar si hay pólizas usando este tipo
            $sqlCheck = "SELECT COUNT(*) FROM poliza WHERE id_tipo_poliza = :id";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar porque hay pólizas asociadas a este tipo'];
            }

            $sql = "DELETE FROM tipo_poliza WHERE id_tipo_poliza = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Tipo de póliza no encontrado'];
            }

            return ['success' => true, 'message' => 'Tipo de póliza eliminado exitosamente'];
        } catch (PDOException $e) {
            error_log('Error eliminarTipoPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el tipo de póliza'];
        }
    }

    // Obtener coberturas asociadas a un tipo de póliza
    public function obtenerCoberturasPorTipo(int $id_tipo_poliza) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        c.id_cobertura,
                        c.nombre,
                        c.detalle
                    FROM tipo_poliza_cobertura tpc
                    JOIN cobertura c ON tpc.id_cobertura = c.id_cobertura
                    WHERE tpc.id_tipo_poliza = :id
                    ORDER BY c.nombre";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerCoberturasPorTipo: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener estadísticas de tipos de póliza
    public function obtenerEstadisticasTipos() {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        tp.id_tipo_poliza,
                        tp.nombre AS tipo_poliza,
                        cp.nombre AS categoria,
                        COUNT(p.id_poliza) AS total_polizas,
                        SUM(dp.monto_prima_total) AS prima_total,
                        AVG(dp.monto_prima_total) AS prima_promedio
                    FROM tipo_poliza tp
                    LEFT JOIN poliza p ON tp.id_tipo_poliza = p.id_tipo_poliza
                    LEFT JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                    JOIN categoria_poliza cp ON tp.id_categoria = cp.id_categoria
                    WHERE p.estado IS NULL OR p.estado != 'ELIMINADA'
                    GROUP BY tp.id_tipo_poliza, cp.nombre
                    ORDER BY total_polizas DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerEstadisticasTipos: ' . $e->getMessage());
            return [];
        }
    }
}
?>