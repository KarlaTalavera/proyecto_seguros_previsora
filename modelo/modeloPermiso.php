<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloPermiso {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (\Exception $e) {
            error_log("Error al inicializar la conexión en ModeloPermiso: " . $e->getMessage());
            $this->db = null;
        }
        $this->asegurarPermisoGestionSolicitudes();
    }

    private function asegurarPermisoGestionSolicitudes(): void {
        if (!$this->db) {
            return;
        }
        try {
            $nombre = 'solicitud_gestionar';
            $descripcion = 'Coordinar solicitudes de pólizas y reportes de siniestros';
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM permiso WHERE nombre_permiso = :nombre LIMIT 1');
            $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt->execute();
            if ((int)$stmt->fetchColumn() === 0) {
                $insert = $this->db->prepare('INSERT INTO permiso (nombre_permiso, descripcion) VALUES (:nombre, :descripcion)');
                $insert->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $insert->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $insert->execute();
            } else {
                $update = $this->db->prepare('UPDATE permiso SET descripcion = :descripcion WHERE nombre_permiso = :nombre');
                $update->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
                $update->bindParam(':nombre', $nombre, PDO::PARAM_STR);
                $update->execute();
            }
        } catch (\PDOException $e) {
            error_log('Error asegurando permiso de solicitudes: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los permisos disponibles en el sistema.
     * @return array|false Un array de permisos o false si hay un error.
     */
    public function obtenerTodosLosPermisos() {
        if (!$this->db) return false;

        $this->asegurarPermisoGestionSolicitudes();

        $sql = "SELECT id_permiso, nombre_permiso, descripcion FROM permiso ORDER BY nombre_permiso ASC";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener todos los permisos: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los IDs de los permisos ACTIVOS asignados a un agente.
     * @param string $cedula_agente La cédula del agente.
     * @return array|false Un array con los IDs de los permisos activos del agente o false si hay un error.
     */
    public function obtenerPermisosDeAgente($cedula_agente) {
        if (!$this->db) return false;

        $sql = "SELECT id_permiso FROM agente_permiso WHERE cedula_agente = :cedula_agente AND tiene_permiso = 1";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener permisos del agente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los nombres de los permisos activos asignados a un agente.
     * @param string $cedula_agente
     * @return array Lista de nombres de permisos activos.
     */
    public function obtenerNombresPermisosDeAgente($cedula_agente) {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT p.nombre_permiso
                FROM agente_permiso ap
                INNER JOIN permiso p ON ap.id_permiso = p.id_permiso
                WHERE ap.cedula_agente = :cedula_agente AND ap.tiene_permiso = 1
                ORDER BY p.nombre_permiso ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_COLUMN, 0) ?: [];
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener nombres de permisos del agente: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Actualiza los permisos para un agente usando INSERT ... ON DUPLICATE KEY UPDATE.
     * Esto asegura que exista una fila para cada permiso y actualiza su estado.
     * @param string $cedula_agente La cédula del agente.
     * @param array $permisos_activos Un array de IDs de los permisos que deben estar ACTIVOS.
     * @return bool True si la operación fue exitosa, false en caso de error.
     */
    public function actualizarPermisosDeAgente($cedula_agente, array $permisos_activos) {
        if (!$this->db) return false;

        try {
            $this->db->beginTransaction();

            // 1. Borrar todos los permisos existentes para este agente.
            $sql_delete = "DELETE FROM agente_permiso WHERE cedula_agente = :cedula_agente";
            $stmt_delete = $this->db->prepare($sql_delete);
            $stmt_delete->bindParam(':cedula_agente', $cedula_agente);
            $stmt_delete->execute();

            // 2. Insertar solo los permisos que vienen activos desde la UI.
            if (!empty($permisos_activos)) {
                $sql_insert = "INSERT INTO agente_permiso (cedula_agente, id_permiso, tiene_permiso) VALUES (:cedula_agente, :id_permiso, 1)";
                $stmt_insert = $this->db->prepare($sql_insert);

                foreach ($permisos_activos as $id_permiso) {
                    $stmt_insert->bindParam(':cedula_agente', $cedula_agente);
                    $stmt_insert->bindParam(':id_permiso', $id_permiso, PDO::PARAM_INT);
                    $stmt_insert->execute();
                }
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error de DB al actualizar permisos (nuevo método): " . $e->getMessage());
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