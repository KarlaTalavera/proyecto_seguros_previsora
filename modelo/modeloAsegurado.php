<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloAsegurado {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log('Error inicializando DB en ModeloAsegurado: ' . $e->getMessage());
        }
    }

    // Obtener todos los asegurados con información de póliza y agente
    public function obtenerAseguradosCompletos(?string $cedula_agente = null) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        a.id_asegurado,
                        a.cedula,
                        a.nombre,
                        a.apellido,
                        a.fecha_nacimiento,
                        a.parentesco,
                        a.sexo,
                        a.id_poliza,
                        p.numero_poliza,
                        p.cedula_agente,
                        ag.nombre AS agente_nombre,
                        ag.apellido AS agente_apellido,
                        tp.nombre AS tipo_poliza,
                        cp.nombre AS categoria_poliza,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS cliente_principal
                    FROM asegurado a
                    JOIN poliza p ON a.id_poliza = p.id_poliza
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    JOIN categoria_poliza cp ON tp.id_categoria = cp.id_categoria
                    LEFT JOIN agente ag ON p.cedula_agente = ag.cedula_agente
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    WHERE 1=1";

            if ($cedula_agente !== null) {
                $sql .= " AND p.cedula_agente = :cedula_agente";
            }

            $sql .= " ORDER BY a.apellido, a.nombre";

            $stmt = $this->db->prepare($sql);
            if ($cedula_agente !== null) {
                $stmt->bindParam(':cedula_agente', $cedula_agente);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerAseguradosCompletos: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener un asegurado por ID
    public function obtenerAseguradoPorId(int $id_asegurado) {
        if (!$this->db) return null;
        try {
            $sql = "SELECT 
                        a.*,
                        p.numero_poliza,
                        p.cedula_agente,
                        p.id_cliente,
                        ag.nombre AS agente_nombre,
                        ag.apellido AS agente_apellido,
                        tp.nombre AS tipo_poliza,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS cliente_principal
                    FROM asegurado a
                    JOIN poliza p ON a.id_poliza = p.id_poliza
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    LEFT JOIN agente ag ON p.cedula_agente = ag.cedula_agente
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    WHERE a.id_asegurado = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_asegurado, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerAseguradoPorId: ' . $e->getMessage());
            return null;
        }
    }

    // Obtener asegurados por póliza
    public function obtenerAseguradosPorPoliza(int $id_poliza) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        a.*,
                        TIMESTAMPDIFF(YEAR, a.fecha_nacimiento, CURDATE()) AS edad
                    FROM asegurado a
                    WHERE a.id_poliza = :id_poliza
                    ORDER BY a.parentesco, a.apellido, a.nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerAseguradosPorPoliza: ' . $e->getMessage());
            return [];
        }
    }

    // Obtener pólizas disponibles para asociar asegurados
    public function obtenerPolizasParaAsegurado(?string $cedula_agente = null) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        p.id_poliza,
                        p.numero_poliza,
                        tp.nombre AS tipo_poliza,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS cliente,
                        p.cedula_agente
                    FROM poliza p
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    WHERE p.estado != 'ELIMINADA'";

            if ($cedula_agente !== null) {
                $sql .= " AND p.cedula_agente = :cedula_agente";
            }

            $sql .= " ORDER BY p.numero_poliza";

            $stmt = $this->db->prepare($sql);
            if ($cedula_agente !== null) {
                $stmt->bindParam(':cedula_agente', $cedula_agente);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerPolizasParaAsegurado: ' . $e->getMessage());
            return [];
        }
    }

    // Crear un nuevo asegurado
    // Crear un nuevo asegurado
    public function crearAsegurado(array $datos) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        // Debug: Ver qué datos estamos recibiendo
        error_log("Datos para crear asegurado: " . print_r($datos, true));

        $required = ['id_poliza', 'nombre', 'apellido', 'fecha_nacimiento', 'sexo'];
        foreach ($required as $field) {
            if (!isset($datos[$field]) || $datos[$field] === '') {
                return ['success' => false, 'message' => 'Falta el campo: ' . $field];
            }
        }

        try {
            // Primero, verificar si la póliza existe
            $sqlCheck = "SELECT COUNT(*) FROM poliza WHERE id_poliza = :id_poliza";
            $stmtCheck = $this->db->prepare($sqlCheck);
            $stmtCheck->bindParam(':id_poliza', $datos['id_poliza'], PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetchColumn() == 0) {
                return ['success' => false, 'message' => 'La póliza seleccionada no existe'];
            }

            $sql = "INSERT INTO asegurado 
                    (id_poliza, cedula, nombre, apellido, fecha_nacimiento, parentesco, sexo) 
                    VALUES 
                    (:id_poliza, :cedula, :nombre, :apellido, :fecha_nacimiento, :parentesco, :sexo)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_poliza', $datos['id_poliza'], PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':parentesco', $datos['parentesco']);
            $stmt->bindParam(':sexo', $datos['sexo']);
            
            $result = $stmt->execute();
            
            if ($result) {
                $id = $this->db->lastInsertId();
                error_log("Asegurado creado con ID: " . $id);
                return ['success' => true, 'id_asegurado' => $id, 'message' => 'Asegurado creado exitosamente'];
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("Error al ejecutar INSERT: " . print_r($errorInfo, true));
                return ['success' => false, 'message' => 'Error al crear el asegurado: ' . ($errorInfo[2] ?? 'Error desconocido')];
            }
        } catch (PDOException $e) {
            error_log('Error crearAsegurado: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos: ' . $e->getMessage()];
        }
    }

    // Actualizar asegurado
    public function actualizarAsegurado(int $id_asegurado, array $datos) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        try {
            $sql = "UPDATE asegurado SET 
                    cedula = :cedula,
                    nombre = :nombre,
                    apellido = :apellido,
                    fecha_nacimiento = :fecha_nacimiento,
                    parentesco = :parentesco,
                    sexo = :sexo
                    WHERE id_asegurado = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_asegurado, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $datos['cedula']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':apellido', $datos['apellido']);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento']);
            $stmt->bindParam(':parentesco', $datos['parentesco']);
            $stmt->bindParam(':sexo', $datos['sexo']);
            $stmt->execute();

            return ['success' => true, 'message' => 'Asegurado actualizado exitosamente'];
        } catch (PDOException $e) {
            error_log('Error actualizarAsegurado: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar el asegurado'];
        }
    }

    // Eliminar asegurado
    public function eliminarAsegurado(int $id_asegurado) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión no disponible'];
        }

        try {
            $sql = "DELETE FROM asegurado WHERE id_asegurado = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_asegurado, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Asegurado no encontrado'];
            }

            return ['success' => true, 'message' => 'Asegurado eliminado exitosamente'];
        } catch (PDOException $e) {
            error_log('Error eliminarAsegurado: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el asegurado'];
        }
    }

    // Obtener estadísticas de asegurados
    public function obtenerEstadisticasAsegurados(?string $cedula_agente = null) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT 
                        COUNT(*) as total_asegurados,
                        SUM(CASE WHEN sexo = 'M' THEN 1 ELSE 0 END) as hombres,
                        SUM(CASE WHEN sexo = 'F' THEN 1 ELSE 0 END) as mujeres,
                        COUNT(DISTINCT id_poliza) as polizas_con_asegurados
                    FROM asegurado a
                    JOIN poliza p ON a.id_poliza = p.id_poliza
                    WHERE 1=1";

            if ($cedula_agente !== null) {
                $sql .= " AND p.cedula_agente = :cedula_agente";
            }

            $stmt = $this->db->prepare($sql);
            if ($cedula_agente !== null) {
                $stmt->bindParam(':cedula_agente', $cedula_agente);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerEstadisticasAsegurados: ' . $e->getMessage());
            return [];
        }
      }
}
?>