<?php
/**
 * Modelo para gestionar operaciones relacionadas con clientes
 */
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloCliente {
    private $db;

    /**
     * Constructor - inicializa la conexión a la base de datos
     */
    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
            
            // Verificar que la conexión se estableció correctamente
            if (!$this->db) {
                error_log("Error: No se pudo establecer conexión a la base de datos");
            }
        } catch (\Exception $e) {
            error_log("Error al inicializar la conexión en ModeloCliente: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * Obtiene todos los clientes registrados en el sistema
     * @return array Lista de clientes o array vacío si hay error
     */
    public function obtenerTodosLosClientes() {
        if (!$this->db) {
            error_log("Error: Conexión a DB no disponible en obtenerTodosLosClientes");
            return [];
        }
        
        $sql = "SELECT 
            c.id_cliente,
            c.cedula_asegurado,
            c.nombre,
            c.apellido,
            c.telefono,
            c.direccion,
            c.fecha_nacimiento,  
            u.email
        FROM cliente c
        INNER JOIN usuario u ON c.cedula_asegurado = u.cedula
        WHERE u.id_rol = 3
        ORDER BY c.id_cliente DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $resultados ? $resultados : [];
            
        } catch (\PDOException $e) {
            error_log("Error en obtenerTodosLosClientes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crea un nuevo cliente en el sistema
     * @param array $data Datos del cliente
     * @return array Resultado de la operación
     */
    public function crearCliente($data) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }
        
        // Validar campos obligatorios
        $camposRequeridos = ['cedula_asegurado', 'nombre', 'apellido', 'email', 'telefono'];
        foreach ($camposRequeridos as $campo) {
            if (empty(trim($data[$campo] ?? ''))) {
                return ['success' => false, 'message' => "El campo '" . ucfirst($campo) . "' es obligatorio"];
            }
        }
        
        // Validar formato de cédula
        if (!$this->validarFormatoCedula($data['cedula_asegurado'])) {
            return ['success' => false, 'message' => 'Formato de cédula inválido. Use V12345678 o J12345678-9'];
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Formato de email inválido'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // 1. Verificar si el usuario ya existe
            $sqlCheckUsuario = "SELECT COUNT(*) FROM usuario WHERE cedula = :cedula OR email = :email";
            $stmtCheck = $this->db->prepare($sqlCheckUsuario);
            $stmtCheck->execute([
                ':cedula' => $data['cedula_asegurado'],
                ':email' => $data['email']
            ]);
            
            if ($stmtCheck->fetchColumn() > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La cédula o email ya están registrados'];
            }
            
            // 2. Crear usuario (rol asegurado = 3)
            $password_hash = password_hash($data['cedula_asegurado'], PASSWORD_DEFAULT); // Contraseña = cédula
            $sqlUsuario = "INSERT INTO usuario (cedula, email, password_hash, activo, id_rol) 
                          VALUES (:cedula, :email, :password_hash, 1, 3)";
            
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':cedula' => $data['cedula_asegurado'],
                ':email' => $data['email'],
                ':password_hash' => $password_hash
            ]);
            
            // 3. Crear cliente
           $sqlCliente = "INSERT INTO cliente (cedula_asegurado, nombre, apellido, telefono, direccion, fecha_nacimiento) 
            VALUES (:cedula, :nombre, :apellido, :telefono, :direccion, :fecha_nacimiento)";

            $stmtCliente = $this->db->prepare($sqlCliente);
            $stmtCliente->execute([
                ':cedula' => $data['cedula_asegurado'],
                ':nombre' => trim($data['nombre']),
                ':apellido' => trim($data['apellido']),
                ':telefono' => trim($data['telefono']),
                ':direccion' => trim($data['direccion'] ?? ''),
                ':fecha_nacimiento' => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
            ]);     
            $idCliente = $this->db->lastInsertId();
            
            $this->db->commit();
            
            return [
                'success' => true, 
                'message' => 'Cliente creado exitosamente',
                'id_cliente' => $idCliente,
                'password_temporal' => $data['cedula_asegurado'] // Para notificar al administrador
            ];
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en crearCliente: " . $e->getMessage());
            
            // Detectar errores específicos
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cedula_asegurado') !== false) {
                    return ['success' => false, 'message' => 'La cédula ya está registrada'];
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    return ['success' => false, 'message' => 'El email ya está registrado'];
                }
            }
            
            return ['success' => false, 'message' => 'Error al crear cliente. Por favor, intente nuevamente'];
        }
    }

    /**
     * Actualiza la información de un cliente existente
     * @param array $data Datos actualizados del cliente
     * @return array Resultado de la operación
     */
    public function actualizarCliente($data) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }
        
        // Validar campos obligatorios
        if (empty($data['id_cliente']) || empty($data['cedula_asegurado']) || empty($data['nombre']) || 
            empty($data['apellido']) || empty($data['email']) || empty($data['telefono'])) {
            return ['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados'];
        }
        
        // Validar email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Formato de email inválido'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // 1. Verificar que el cliente existe
            $sqlCheckCliente = "SELECT cedula_asegurado FROM cliente WHERE id_cliente = :id_cliente";
            $stmtCheck = $this->db->prepare($sqlCheckCliente);
            $stmtCheck->execute([':id_cliente' => $data['id_cliente']]);
            
            $clienteActual = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$clienteActual) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Cliente no encontrado'];
            }
            
            $cedulaActual = $clienteActual['cedula_asegurado'];
            
            // 2. Verificar si el email ya está en uso por otro usuario
            if ($cedulaActual !== $data['cedula_asegurado']) {
                $sqlCheckEmail = "SELECT COUNT(*) FROM usuario WHERE email = :email AND cedula != :cedula";
                $stmtEmail = $this->db->prepare($sqlCheckEmail);
                $stmtEmail->execute([
                    ':email' => $data['email'],
                    ':cedula' => $data['cedula_asegurado']
                ]);
                
                if ($stmtEmail->fetchColumn() > 0) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'El email ya está registrado por otro usuario'];
                }
            }
            
            // 3. Actualizar cliente
            $sqlCliente = "UPDATE cliente 
            SET nombre = :nombre, 
                apellido = :apellido, 
                telefono = :telefono, 
                direccion = :direccion,
                fecha_nacimiento = :fecha_nacimiento
            WHERE id_cliente = :id_cliente";

            $stmtCliente = $this->db->prepare($sqlCliente);
            $stmtCliente->execute([
                ':id_cliente' => $data['id_cliente'],
                ':nombre' => trim($data['nombre']),
                ':apellido' => trim($data['apellido']),
                ':telefono' => trim($data['telefono']),
                ':direccion' => trim($data['direccion'] ?? ''),
                ':fecha_nacimiento' => !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null,
            ]);
            
            // 4. Actualizar usuario (si cambió la cédula o email)
            if ($cedulaActual !== $data['cedula_asegurado']) {
                // Si cambió la cédula, actualizar en usuario
                $sqlUpdateUsuario = "UPDATE usuario 
                                    SET cedula = :nueva_cedula, 
                                        email = :email 
                                    WHERE cedula = :cedula_actual";
                
                $stmtUsuario = $this->db->prepare($sqlUpdateUsuario);
                $stmtUsuario->execute([
                    ':nueva_cedula' => $data['cedula_asegurado'],
                    ':email' => trim($data['email']),
                    ':cedula_actual' => $cedulaActual
                ]);
            } else {
                // Solo actualizar email
                $sqlUpdateEmail = "UPDATE usuario SET email = :email WHERE cedula = :cedula";
                $stmtEmail = $this->db->prepare($sqlUpdateEmail);
                $stmtEmail->execute([
                    ':cedula' => $data['cedula_asegurado'],
                    ':email' => trim($data['email'])
                ]);
            }
            
            $this->db->commit();
            
            // Verificar si se realizaron cambios
            if ($stmtCliente->rowCount() > 0) {
                return ['success' => true, 'message' => 'Cliente actualizado exitosamente'];
            } else {
                return ['success' => true, 'message' => 'No se realizaron cambios en el cliente'];
            }
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en actualizarCliente: " . $e->getMessage());
            
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'cedula') !== false) {
                    return ['success' => false, 'message' => 'La cédula ya está registrada por otro usuario'];
                } elseif (strpos($e->getMessage(), 'email') !== false) {
                    return ['success' => false, 'message' => 'El email ya está registrado por otro usuario'];
                }
            }
            
            return ['success' => false, 'message' => 'Error al actualizar cliente. Por favor, intente nuevamente'];
        }
    }

    /**
     * Elimina un cliente del sistema
     * @param int $id_cliente ID del cliente a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarCliente($id_cliente) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos'];
        }
        
        if (empty($id_cliente) || $id_cliente <= 0) {
            return ['success' => false, 'message' => 'ID de cliente inválido'];
        }
        
        try {
            // 1. Verificar que el cliente existe
            $sqlGetCliente = "SELECT c.*, 
                                     (SELECT COUNT(*) FROM poliza WHERE id_cliente = c.id_cliente) as total_polizas
                              FROM cliente c
                              WHERE c.id_cliente = :id_cliente";
            
            $stmtGet = $this->db->prepare($sqlGetCliente);
            $stmtGet->execute([':id_cliente' => $id_cliente]);
            $cliente = $stmtGet->fetch(PDO::FETCH_ASSOC);
            
            if (!$cliente) {
                return ['success' => false, 'message' => 'Cliente no encontrado'];
            }
            
            // 2. Verificar si tiene pólizas activas
            if ($cliente['total_polizas'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar el cliente porque tiene pólizas asociadas'];
            }
            
            $this->db->beginTransaction();
            
            // 3. Eliminar cliente (esto debería activar ON DELETE CASCADE en usuario si está configurado)
            $sqlDeleteCliente = "DELETE FROM cliente WHERE id_cliente = :id_cliente";
            $stmtDelete = $this->db->prepare($sqlDeleteCliente);
            $stmtDelete->execute([':id_cliente' => $id_cliente]);
            
            // 4. Verificar si hay que eliminar también el usuario
            // (Depende de cómo estén configuradas las claves foráneas)
            $sqlCheckUsuario = "SELECT u.cedula, 
                                       (SELECT COUNT(*) FROM agente WHERE cedula_agente = u.cedula) as es_agente,
                                       (SELECT COUNT(*) FROM administrador WHERE cedula_admin = u.cedula) as es_admin
                                FROM usuario u
                                WHERE u.cedula = :cedula";
            
            $stmtUsuario = $this->db->prepare($sqlCheckUsuario);
            $stmtUsuario->execute([':cedula' => $cliente['cedula_asegurado']]);
            $infoUsuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
            
            // Si el usuario solo es cliente (no es agente ni admin), eliminar usuario
            if ($infoUsuario && $infoUsuario['es_agente'] == 0 && $infoUsuario['es_admin'] == 0) {
                $sqlDeleteUsuario = "DELETE FROM usuario WHERE cedula = :cedula";
                $stmtDeleteUser = $this->db->prepare($sqlDeleteUsuario);
                $stmtDeleteUser->execute([':cedula' => $cliente['cedula_asegurado']]);
            }
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Cliente eliminado exitosamente'];
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error en eliminarCliente: " . $e->getMessage());
            
            // Verificar si hay restricciones de clave foránea
            if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
                return ['success' => false, 'message' => 'No se puede eliminar el cliente porque tiene registros relacionados (pólizas, siniestros, etc.)'];
            }
            
            return ['success' => false, 'message' => 'Error al eliminar cliente. Por favor, intente nuevamente'];
        }
    }

    /**
     * Obtiene un cliente específico por su ID
     * @param int $id_cliente ID del cliente
     * @return array|null Datos del cliente o null si no existe
     */
    public function obtenerClientePorId($id_cliente) {
        if (!$this->db) {
            error_log("Error: Conexión a DB no disponible en obtenerClientePorId");
            return null;
        }
        
        $sql = "SELECT c.id_cliente, c.cedula_asegurado, c.nombre, c.apellido, c.telefono, c.direccion, c.fecha_nacimiento, u.email
                FROM cliente c
                INNER JOIN usuario u ON c.cedula_asegurado = u.cedula
                WHERE c.id_cliente = :id_cliente";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id_cliente' => $id_cliente]);
            
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            return $cliente ? $cliente : null;
            
        } catch (\PDOException $e) {
            error_log("Error en obtenerClientePorId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un cliente por su número de cédula
     * @param string $cedula Número de cédula
     * @return array|null Datos del cliente o null si no existe
     */
    public function obtenerClientePorCedula($cedula) {
        if (!$this->db) {
            return null;
        }
        
        $sql = "SELECT 
                    c.id_cliente,
                    c.cedula_asegurado,
                    c.nombre,
                    c.apellido,
                    c.telefono,
                    c.direccion,
                    u.email
                FROM cliente c
                INNER JOIN usuario u ON c.cedula_asegurado = u.cedula
                WHERE c.cedula_asegurado = :cedula";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cedula' => $cedula]);
            
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            return $cliente ? $cliente : null;
            
        } catch (\PDOException $e) {
            error_log("Error en obtenerClientePorCedula: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Valida el formato de una cédula venezolana
     * @param string $cedula Cédula a validar
     * @return bool True si el formato es válido
     */
    private function validarFormatoCedula($cedula) {
        $cedula = strtoupper(trim($cedula));
        
        // Personas naturales: V + 7-8 dígitos
        if (preg_match('/^V\d{7,8}$/', $cedula)) {
            return true;
        }
        
        // Personas jurídicas: J, G, E, EM + 7-8 dígitos + guion + dígito verificador
        if (preg_match('/^(J|G|E|EM)\d{7,8}-\d{1}$/', $cedula)) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica si una cédula ya está registrada en el sistema
     * @param string $cedula Cédula a verificar
     * @param int $excluirId (opcional) ID de cliente a excluir (para actualizaciones)
     * @return bool True si la cédula ya existe
     */
    public function existeCedula($cedula, $excluirId = null) {
        if (!$this->db) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM cliente WHERE cedula_asegurado = :cedula";
        $params = [':cedula' => $cedula];
        
        if ($excluirId) {
            $sql .= " AND id_cliente != :excluir_id";
            $params[':excluir_id'] = $excluirId;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeCedula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un email ya está registrado en el sistema
     * @param string $email Email a verificar
     * @param string $excluirCedula (opcional) Cédula a excluir (para actualizaciones)
     * @return bool True si el email ya existe
     */
    public function existeEmail($email, $excluirCedula = null) {
        if (!$this->db) {
            return false;
        }
        
        $sql = "SELECT COUNT(*) FROM usuario WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excluirCedula) {
            $sql .= " AND cedula != :excluir_cedula";
            $params[':excluir_cedula'] = $excluirCedula;
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error en existeEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el total de clientes registrados
     * @return int Número total de clientes
     */
    public function contarClientes() {
        if (!$this->db) {
            return 0;
        }
        
        $sql = "SELECT COUNT(*) as total 
                FROM cliente c
                INNER JOIN usuario u ON c.cedula_asegurado = u.cedula
                WHERE u.id_rol = 3";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? (int)$resultado['total'] : 0;
        } catch (\PDOException $e) {
            error_log("Error en contarClientes: " . $e->getMessage());
            return 0;
        }
    }
}
?>