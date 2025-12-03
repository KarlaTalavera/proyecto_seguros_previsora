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

    // MÉTODOS PARA ADMINISTRADOR - OBTENER TODOS LOS SINIESTROS
    public function obtenerTodosSiniestros() {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        s.id_siniestro,
                        s.numero_siniestro,
                        s.id_poliza,
                        p.numero_poliza,
                        s.fecha_reporte,
                        s.descripcion,
                        s.monto_estimado,
                        s.estado,
                        s.cedula_agente_gestion,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS nombre_cliente,
                        CONCAT(ag.nombre, ' ', ag.apellido) AS nombre_agente
                    FROM siniestro s
                    JOIN poliza p ON s.id_poliza = p.id_poliza
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    LEFT JOIN agente ag ON s.cedula_agente_gestion = ag.cedula_agente
                    ORDER BY s.fecha_reporte DESC";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener todos los siniestros: " . $e->getMessage());
            return [];
        }
    }

    // MÉTODO PARA AGENTES
    public function obtenerSiniestrosDeAgente(string $cedula_agente) {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        s.id_siniestro,
                        s.numero_siniestro,
                        s.id_poliza,
                        p.numero_poliza,
                        s.fecha_reporte,
                        s.descripcion,
                        s.monto_estimado,
                        s.estado,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS nombre_cliente
                    FROM siniestro s
                    JOIN poliza p ON s.id_poliza = p.id_poliza
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    WHERE s.cedula_agente_gestion = :cedula_agente
                    ORDER BY s.fecha_reporte DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestros del agente: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER SINIESTRO POR ID
    public function obtenerSiniestroPorId(int $id_siniestro) {
        if (!$this->db) return null;
        
        try {
            $sql = "SELECT 
                        s.*,
                        p.numero_poliza,
                        CONCAT(cl.nombre, ' ', cl.apellido) AS nombre_cliente,
                        cl.cedula_asegurado AS cedula_cliente,
                        CONCAT(ag.nombre, ' ', ag.apellido) AS nombre_agente
                    FROM siniestro s
                    JOIN poliza p ON s.id_poliza = p.id_poliza
                    JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    LEFT JOIN agente ag ON s.cedula_agente_gestion = ag.cedula_agente
                    WHERE s.id_siniestro = :id_siniestro";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestro por ID: " . $e->getMessage());
            return null;
        }
    }

    // CREAR NUEVO SINIESTRO
    public function crearSiniestro(array $data, string $cedula_agente) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        // Validar datos requeridos
        $id_poliza = $data['id_poliza'] ?? null;
        if (!$id_poliza) {
            return ['success' => false, 'message' => 'ID de póliza no proporcionado.'];
        }

        // Generar número de siniestro único
        $numero_siniestro = 'S-' . $id_poliza . '-' . date('Ymd-His');
        
        try {
            $sql = "INSERT INTO siniestro (
                        numero_siniestro,
                        id_poliza, 
                        cedula_agente_gestion, 
                        fecha_reporte,
                        descripcion, 
                        monto_estimado,
                        estado
                    ) VALUES (
                        :numero_siniestro,
                        :id_poliza, 
                        :cedula_agente, 
                        :fecha_reporte, 
                        :descripcion, 
                        :monto_estimado, 
                        :estado
                    )";

            $stmt = $this->db->prepare($sql);
            
            // Preparar datos
            $monto_estimado = (float)($data['monto_reclamo'] ?? 0.0);
            $estado = strtoupper($data['estado'] ?? 'ABIERTO');
            $fecha_reporte = $data['fecha_incidente'] ?? date('Y-m-d H:i:s');

            $stmt->bindParam(':numero_siniestro', $numero_siniestro);
            $stmt->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->bindParam(':fecha_reporte', $fecha_reporte);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':monto_estimado', $monto_estimado);
            $stmt->bindParam(':estado', $estado);

            $stmt->execute();
            
            $id_siniestro = $this->db->lastInsertId();
            
            return [
                'success' => true, 
                'message' => 'Siniestro registrado exitosamente.',
                'id_siniestro' => $id_siniestro,
                'numero_siniestro' => $numero_siniestro
            ];

        } catch (\PDOException $e) {
            error_log("Error de DB al crear siniestro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos durante la creación: ' . $e->getMessage()];
        }
    }

    // ACTUALIZAR SINIESTRO
    public function actualizarSiniestro(array $data, int $id_siniestro) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }
        
        try {
            $sql = "UPDATE siniestro SET 
                        fecha_reporte = :fecha_reporte, 
                        descripcion = :descripcion, 
                        estado = :estado,
                        monto_estimado = :monto_estimado
                    WHERE id_siniestro = :id_siniestro";
            
            $stmt = $this->db->prepare($sql);
            
            $monto_estimado = (float)($data['monto_reclamo'] ?? 0.0);
            $estado = strtoupper($data['estado'] ?? 'ABIERTO');

            $stmt->bindParam(':fecha_reporte', $data['fecha_incidente']);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':monto_estimado', $monto_estimado);
            $stmt->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            
            $stmt->execute();
            
            return [
                'success' => true, 
                'message' => 'Siniestro actualizado exitosamente.',
                'rows_affected' => $stmt->rowCount()
            ];

        } catch (\PDOException $e) {
            error_log("Error de DB al actualizar siniestro ID: $id_siniestro. Mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al guardar los cambios del siniestro.'];
        }
    }

    // REGISTRAR PAGO DE SINIESTRO
    public function registrarPago(int $id_siniestro, float $monto_pago, string $fecha_pago) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }
        
        try {
            // Actualizar el estado a CERRADO cuando se registra un pago
            $sql = "UPDATE siniestro SET 
                        estado = 'CERRADO'
                    WHERE id_siniestro = :id_siniestro";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'success' => true, 
                'message' => 'Pago registrado y siniestro cerrado exitosamente.',
                'rows_affected' => $stmt->rowCount()
            ];

        } catch (\PDOException $e) {
            error_log("Error de DB al registrar pago de siniestro ID: $id_siniestro. Mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al registrar el pago.'];
        }
    }

    // ELIMINAR SINIESTRO
    public function eliminarSiniestro(int $id_siniestro) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }
        
        try {
            $sql = "DELETE FROM siniestro WHERE id_siniestro = :id_siniestro";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return [
                    'success' => true, 
                    'message' => 'Siniestro eliminado exitosamente.',
                    'rows_affected' => $stmt->rowCount()
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'No se encontró el siniestro para eliminar.'
                ];
            }

        } catch (\PDOException $e) {
            error_log("Error de DB al eliminar siniestro ID: $id_siniestro. Mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al eliminar el siniestro.'];
        }
    }

    // OBTENER PÓLIZAS ACTIVAS (para formularios)
    public function obtenerPolizasActivas() {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        p.id_poliza,
                        p.numero_poliza,
                        CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente,
                        tp.nombre AS tipo_poliza
                    FROM poliza p
                    JOIN cliente c ON p.id_cliente = c.id_cliente
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    WHERE p.estado = 'ACTIVA'
                    ORDER BY p.numero_poliza";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener pólizas activas: " . $e->getMessage());
            return [];
        }
    }

    // OBTENER AGENTES ACTIVOS (para formularios)
    public function obtenerAgentesActivos() {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        a.cedula_agente,
                        CONCAT(a.nombre, ' ', a.apellido) AS nombre_completo,
                        a.telefono
                    FROM agente a
                    JOIN usuario u ON a.cedula_agente = u.cedula
                    WHERE u.activo = 1
                    ORDER BY a.nombre, a.apellido";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener agentes activos: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerIdPolizaPorNumeroPublico(string $numero_poliza): ?int {
        return $this->obtenerIdPolizaPorNumero($numero_poliza);
    }

    // MÉTODO AUXILIAR: OBTENER ID DE PÓLIZA POR NÚMERO
    private function obtenerIdPolizaPorNumero(string $numero_poliza): ?int {
        if (!$this->db) return null;
        
        try {
            $sql = "SELECT id_poliza FROM poliza WHERE numero_poliza = :numero_poliza";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':numero_poliza', $numero_poliza);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? (int)$resultado['id_poliza'] : null;
        } catch (\PDOException $e) {
            error_log("Error de DB (obtenerIdPolizaPorNumero): " . $e->getMessage());
            return null;
        }
    }

    // ESTADÍSTICAS DE SINIESTROS
    public function obtenerEstadisticas() {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        estado,
                        COUNT(*) as total,
                        SUM(monto_estimado) as monto_total
                    FROM siniestro
                    GROUP BY estado";
            
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }

    // BUSCAR SINIESTROS POR FILTROS
    public function buscarSiniestros(array $filtros) {
        if (!$this->db) return [];
        
        try {
            $sql = "SELECT 
                        s.*,
                        p.numero_poliza,
                        CONCAT(c.nombre, ' ', c.apellido) AS nombre_cliente
                    FROM siniestro s
                    JOIN poliza p ON s.id_poliza = p.id_poliza
                    JOIN cliente c ON p.id_cliente = c.id_cliente
                    WHERE 1=1";
            
            $params = [];
            
            // Filtro por estado
            if (!empty($filtros['estado'])) {
                $sql .= " AND s.estado = :estado";
                $params[':estado'] = $filtros['estado'];
            }
            
            // Filtro por fecha desde
            if (!empty($filtros['fecha_desde'])) {
                $sql .= " AND s.fecha_reporte >= :fecha_desde";
                $params[':fecha_desde'] = $filtros['fecha_desde'];
            }
            
            // Filtro por fecha hasta
            if (!empty($filtros['fecha_hasta'])) {
                $sql .= " AND s.fecha_reporte <= :fecha_hasta";
                $params[':fecha_hasta'] = $filtros['fecha_hasta'];
            }
            
            // Filtro por póliza
            if (!empty($filtros['numero_poliza'])) {
                $sql .= " AND p.numero_poliza LIKE :numero_poliza";
                $params[':numero_poliza'] = '%' . $filtros['numero_poliza'] . '%';
            }
            
            $sql .= " ORDER BY s.fecha_reporte DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al buscar siniestros: " . $e->getMessage());
            return [];
        }
    }
}
?>