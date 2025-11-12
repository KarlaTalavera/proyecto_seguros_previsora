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

    private function obtenerIdPolizaPorNumero(string $numero_poliza): ?int {
        if (!$this->db) return null;
        $sql = "SELECT id_poliza FROM poliza WHERE numero_poliza = :numero_poliza";
        try {
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

    public function obtenerSiniestrosDeAgente(string $cedula_agente) {
        if (!$this->db) return false;

        $sql = "SELECT 
                    s.id_siniestro AS id,
                    p.numero_poliza AS poliza,
                    CONCAT(u_cliente.nombre, ' ', u_cliente.apellido) AS cliente,
                    s.fecha_reporte AS fecha_incidente,  -- Mapeado
                    s.monto_estimado AS monto_reclamo, -- Mapeado
                    s.estado AS estado                 -- Mapeado
                FROM siniestro s
                JOIN poliza p ON s.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u_cliente ON c.cedula_asegurado = u_cliente.cedula
                WHERE p.cedula_agente = :cedula_agente
                ORDER BY s.fecha_reporte DESC"; // Ordenado por fecha_reporte

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestros del agente: " . $e->getMessage());
            return false;
        }
    }
    public function obtenerSiniestroPorId(int $id_siniestro) {
        if (!$this->db) return false;

        $sql = "SELECT 
                    s.id_siniestro, s.id_poliza, 
                    s.fecha_reporte AS fecha_incidente, -- Mapeado
                    s.descripcion, 
                    s.estado AS estado,                 -- Mapeado
                    p.numero_poliza,
                    CONCAT(u_cliente.nombre, ' ', u_cliente.apellido) AS nombre_cliente, 
                    u_cliente.cedula AS cedula_cliente,
                    s.monto_estimado AS monto_reclamo, -- Mapeado
                    0.00 AS monto_pago,             -- Campo no existe, se envia 0
                    NULL AS fecha_pago              -- Campo no existe, se envia null
                FROM siniestro s
                JOIN poliza p ON s.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u_cliente ON c.cedula_asegurado = u_cliente.cedula
                WHERE s.id_siniestro = :id_siniestro";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener siniestro por ID: " . $e->getMessage());
            return false;
        }
    }

    public function crearSiniestro(array $data, string $cedula_agente) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        $numero_poliza = $data['numero_poliza'] ?? null;
        if (!$numero_poliza) {
            return ['success' => false, 'message' => 'Número de póliza no proporcionado.'];
        }

        $id_poliza = $this->obtenerIdPolizaPorNumero($numero_poliza); 
        if (!$id_poliza) {
            return ['success' => false, 'message' => 'Póliza no encontrada o inválida. Verifique que el número de póliza exista.'];
        }

        // Generar un número de siniestro simple (Ej: S-100-1699152000)
        $numero_siniestro_gen = 'S-' . $id_poliza . '-' . time();

        // Mapeo de campos del formulario a la BD
        $sql = "INSERT INTO siniestro (
                    id_poliza, 
                    cedula_agente_gestion, 
                    fecha_reporte,  -- Mapeado (fecha_incidente -> fecha_reporte)
                    descripcion, 
                    monto_estimado, -- Mapeado (monto_reclamo -> monto_estimado)
                    estado,
                    numero_siniestro -- Añadido campo NOT NULL
                ) VALUES (
                    :id_poliza, 
                    :cedula_agente, 
                    :fecha_reporte, 
                    :descripcion, 
                    :monto_estimado, 
                    :estado,
                    :numero_siniestro
                )";

        try {
            $stmt = $this->db->prepare($sql);
            
            // Conversión a float
            $monto_estimado = (float)($data['monto_reclamo'] ?? 0.0);
            // Mapeo de estado (a mayúsculas como en la BD)
            $estado_db = strtoupper($data['estado'] ?? 'PENDIENTE');

            // Usamos fecha_incidente del formulario para la columna fecha_reporte
            $fecha_reporte_usar = $data['fecha_incidente'] ?? date('Y-m-d');

            $stmt->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->bindParam(':fecha_reporte', $fecha_reporte_usar);
            $stmt->bindParam(':descripcion', $data['descripcion']);
            $stmt->bindParam(':monto_estimado', $monto_estimado);
            $stmt->bindParam(':estado', $estado_db);
            $stmt->bindParam(':numero_siniestro', $numero_siniestro_gen);

            $stmt->execute();
            
            return ['success' => true, 'message' => 'Siniestro registrado exitosamente.'];

        } catch (\PDOException $e) {
            error_log("Error de DB al crear siniestro: " . $e->getMessage());
            // Devolvemos el mensaje de error de SQL para depuración
            return ['success' => false, 'message' => 'Error de base de datos durante la creación: ' . $e->getMessage()];
        }
    }

    public function actualizarSiniestro(array $data, int $id_siniestro) {
        if (!$this->db) return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        
        try {
            // Actualizar solo la tabla 'siniestro' con los campos que sí existen
            $sql_siniestro = "UPDATE siniestro SET 
                                fecha_reporte = :fecha_incidente, 
                                descripcion = :descripcion, 
                                estado = :estado,
                                monto_estimado = :monto_reclamo
                            WHERE id_siniestro = :id_siniestro";
                            
            $stmt_siniestro = $this->db->prepare($sql_siniestro);
            
            $estado_db = strtoupper($data['estado'] ?? 'PENDIENTE');
            $monto_estimado = (float)($data['monto_reclamo'] ?? 0.0);

            $stmt_siniestro->bindParam(':fecha_incidente', $data['fecha_incidente']); // Mapeado
            $stmt_siniestro->bindParam(':descripcion', $data['descripcion']);
            $stmt_siniestro->bindParam(':estado', $estado_db); // Mapeado
            $stmt_siniestro->bindParam(':monto_reclamo', $monto_estimado); // Mapeado
            $stmt_siniestro->bindParam(':id_siniestro', $id_siniestro, PDO::PARAM_INT);
            
            $stmt_siniestro->execute();
            
            return ['success' => true, 'message' => 'Siniestro actualizado exitosamente.'];

        } catch (\PDOException $e) {
            error_log("Error de DB al actualizar siniestro ID: $id_siniestro. Mensaje: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al guardar los cambios del siniestro.'];
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

    public function Conexion_Base_Datos(array $data = null) {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos(); // <-- Uso del método de tu archivo
        } catch (\Exception $e) {
            error_log('Error inicializando DB en modeloUsuario: ' . $e->getMessage());
            $this->db = null;
        }
    }
}
?>

