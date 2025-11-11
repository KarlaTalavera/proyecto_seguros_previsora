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
    }

    /**
     * Obtiene todos los permisos disponibles en el sistema.
     * @return array|false Un array de permisos o false si hay un error.
     */
    public function obtenerTodosLosPermisos() {
        if (!$this->db) return false;

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
     * Actualiza los permisos para un agente usando INSERT ... ON DUPLICATE KEY UPDATE.
     * Esto asegura que exista una fila para cada permiso y actualiza su estado.
     * @param string $cedula_agente La cédula del agente.
     * @param array $permisos_activos Un array de IDs de los permisos que deben estar ACTIVOS.
     * @return bool True si la operación fue exitosa, false en caso de error.
     */
    public function actualizarPermisosDeAgente($cedula_agente, array $permisos_activos) {
        if (!$this->db) return false;

        $this->db->beginTransaction();
        try {
            // Primero, obtenemos la lista completa de posibles permisos
            $todos_los_permisos = $this->obtenerTodosLosPermisos();
            if ($todos_los_permisos === false) {
                $this->db->rollBack();
                return false;
            }

            $sql = "INSERT INTO agente_permiso (cedula_agente, id_permiso, tiene_permiso) 
                    VALUES (:cedula_agente, :id_permiso, :tiene_permiso)
                    ON DUPLICATE KEY UPDATE tiene_permiso = VALUES(tiene_permiso)";
            
            $stmt = $this->db->prepare($sql);

            foreach ($todos_los_permisos as $permiso) {
                $id_permiso = $permiso['id_permiso'];
                // Verificamos si el permiso está en la lista de activos que nos pasaron
                $tiene_permiso = in_array($id_permiso, $permisos_activos) ? 1 : 0;

                $stmt->bindParam(':cedula_agente', $cedula_agente);
                $stmt->bindParam(':id_permiso', $id_permiso, PDO::PARAM_INT);
                $stmt->bindParam(':tiene_permiso', $tiene_permiso, PDO::PARAM_INT);
                $stmt->execute();
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error de DB al actualizar permisos: " . $e->getMessage());
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