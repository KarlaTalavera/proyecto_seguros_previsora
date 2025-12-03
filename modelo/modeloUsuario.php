<?php
/**
 * Se asume que este path es correcto y que contiene la clase 'Base_Datos'.
 */
require_once dirname(__DIR__) . '/config/conexion.php'; 

/**
 * Clase que actúa como Modelo de Negocio (maneja la DB) y Entidad (DTO).
 */
class modeloUsuario {
    // --- ATRIBUTOS DE LA ENTIDAD (USUARIO) ---
    private $cedula;
    private $nombre;
    private $apellido;
    private $email;
    private $password_hash;
    private $telefono;
    private $id_rol;
    private $nombre_rol;

    // --- ATRIBUTO DEL MODELO (DB) ---
    private $db;

    // --- Constructor (Permite inicializar como Modelo o como Entidad) ---
    public function __construct(array $data = null) {
        if ($data) {
            // Caso 1: Se usa como Entidad/DTO para poblar con datos de la DB
            $this->cedula = $data['cedula'] ?? null;
            $this->nombre = $data['nombre'] ?? null;
            $this->apellido = $data['apellido'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password_hash = $data['password_hash'] ?? null;
            $this->telefono = $data['telefono'] ?? null;
            $this->id_rol = $data['id_rol'] ?? null;
            $this->nombre_rol = $data['nombre_rol'] ?? null;
        } else {
            // Caso 2: Se usa como Modelo (sin argumentos) para inicializar la conexión a la DB
            try {
                require_once dirname(__DIR__) . '/config/conexion.php';
                $base_datos = new Base_Datos();
                $this->db = $base_datos->Conexion_Base_Datos();
                
                if (!$this->db) {
                    throw new Exception("No se pudo establecer la conexión a la base de datos.");
                }
            } catch (\Exception $e) {
                error_log("Error al inicializar la conexión en modeloUsuario: " . $e->getMessage());
                throw $e; // Relanzar la excepción
            }
        }
    }

    // --- Setters (Se mantienen) ---
    public function setCedula($cedula) { $this->cedula = $cedula; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setEmail($email) { $this->email = $email; }
    public function setPasswordHash($password_hash) { $this->password_hash = $password_hash; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setIdRol($id_rol) { $this->id_rol = $id_rol; }
    public function setNombreRol($nombre_rol) { $this->nombre_rol = $nombre_rol; }

    // --- Getters (Se mantienen) ---
    public function getCedula() { return $this->cedula; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getEmail() { return $this->email; }
    public function getPasswordHash() { return $this->password_hash; }
    public function getTelefono() { return $this->telefono; }
    public function getIdRol() { return $this->id_rol; }
    
    // Este es crucial para tu controlador (retorna el rol en minúsculas)
    public function getNombreRol() { return strtolower($this->nombre_rol ?? ''); } 

    public function getNombreCompleto() {
        return $this->nombre . ' ' . $this->apellido;
    }

    // ====================================================================
    // --- MÉTODOS DE AUTENTICACIÓN ---
    // ====================================================================

    /**
     * Busca un usuario por su cédula o email en la base de datos.
     * @param string $identificador La cédula o el email del usuario.
     * @return array|false Retorna un array con los datos del usuario incluyendo el rol, o false si no lo encuentra.
     */
    protected function getUsuarioPorIdentificador(string $identificador) {
        if (!$this->db) {
            error_log("Error: Conexión a DB no inicializada en el Modelo.");
            return false;
        }
        
       $sql = "SELECT 
                    u.cedula,
                    u.email,
                    u.password_hash,
                    u.activo,
                    u.id_rol,
                    r.nombre_rol,
                    -- Recuperamos datos personales dependiendo de donde existan
                    COALESCE(ag.nombre, ad.nombre, cl.nombre) AS nombre,
                    COALESCE(ag.apellido, ad.apellido, cl.apellido) AS apellido,
                    COALESCE(ag.telefono, ad.telefono, cl.telefono) AS telefono
                FROM 
                    usuario u
                JOIN 
                    rol r ON u.id_rol = r.id_rol
                -- Joins a las tablas específicas
                LEFT JOIN agente ag ON u.cedula = ag.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente cl ON u.cedula = cl.cedula_asegurado
                WHERE 
                    u.cedula = :identificador OR u.email = :identificador
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
            
        } catch (\PDOException $e) {
            error_log("Error de DB al buscar usuario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Intenta autenticar a un usuario con su identificador y clave.
     * @param string $identificador Cédula o email del usuario.
     * @param string $clave Contraseña en texto plano ingresada por el usuario.
     * @return modeloUsuario|false Retorna un objeto modeloUsuario si la autenticación es exitosa, o false si falla.
     */
    public function login(string $identificador, string $clave) {
        // 1. Buscar los datos del usuario, incluyendo el hash de la clave
        $data = $this->getUsuarioPorIdentificador($identificador);

        if ($data) {
            // 2. Verificar la clave hash (se asume que 'password_hash' en la DB almacena el hash seguro)
            if (password_verify($clave, $data['password_hash'])) { 
                // Autenticación exitosa: se crea una instancia de sí mismo (DTO) con los datos
                // NOTA: El controlador espera un objeto que tenga el método getNombreRol()
                return new modeloUsuario($data);
            }
        }

        // Si el usuario no existe o la contraseña es incorrecta
        return false;
    }

    // ====================================================================
    // --- MÉTODOS DE GESTIÓN DE USUARIOS (CRUD) ---
    // ====================================================================

    /**
     * Obtiene todos los usuarios de la base de datos con su respectivo rol.
     * @return array|false Un array de arrays asociativos con los datos de los usuarios, o false si hay un error.
     */
    public function obtenerTodosLosUsuarios() {
        if (!$this->db) {
            error_log("Error: Conexión a DB no inicializada en el Modelo.");
            return false;
        }

        $sql = "SELECT 
                    u.cedula,
                    u.email,
                    u.activo,
                    u.id_rol,
                    r.nombre_rol,
                    -- Datos personales de agente
                    ag.nombre AS agente_nombre,
                    ag.apellido AS agente_apellido,
                    ag.telefono AS agente_telefono,
                    -- Datos personales de administrador
                    ad.nombre AS admin_nombre,
                    ad.apellido AS admin_apellido,
                    ad.telefono AS admin_telefono,
                    -- Datos personales de cliente
                    cl.nombre AS cliente_nombre,
                    cl.apellido AS cliente_apellido,
                    cl.telefono AS cliente_telefono,
                    -- Combinamos todos usando COALESCE
                    COALESCE(ag.nombre, ad.nombre, cl.nombre) AS nombre,
                    COALESCE(ag.apellido, ad.apellido, cl.apellido) AS apellido,
                    COALESCE(ag.telefono, ad.telefono, cl.telefono) AS telefono
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN agente ag ON u.cedula = ag.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente cl ON u.cedula = cl.cedula_asegurado
                ORDER BY u.apellido, u.nombre";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener todos los usuarios: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodosLosAgentes() {
        if (!$this->db) {
            error_log("Error: Conexión a DB no inicializada en el Modelo.");
            return false;
        }

        $sql = "SELECT 
                    u.cedula,
                    u.email,
                    u.activo,
                    r.nombre_rol,
                    ag.nombre,
                    ag.apellido,
                    ag.telefono
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                JOIN agente ag ON u.cedula = ag.cedula_agente
                WHERE r.nombre_rol = 'agente'
                ORDER BY ag.apellido, ag.nombre";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener todos los agentes: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Verifica si una cédula ya existe en la tabla usuario.
     * @param string $cedula
     * @return bool
     */
    public function existeCedula(string $cedula) {
        if (!$this->db) return false;
        $sql = "SELECT COUNT(*) FROM usuario WHERE cedula = :cedula";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error DB existeCedula: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un email ya existe en la tabla usuario.
     * @param string $email
     * @return bool
     */
    public function existeEmail(string $email) {
        if (!$this->db) return false;
        $sql = "SELECT COUNT(*) FROM usuario WHERE email = :email";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log("Error DB existeEmail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Valida el formato de la cédula según prefijos esperados.
     * - Personas: V12345678 (V + 7-8 dígitos)
     * - Empresas / entidades / extranjeros: J, G, E, EM seguidos de 7-8 dígitos y un guion con dígito de chequeo: J12345678-9
     * @param string $cedula
     * @return bool
     */
    public function validarFormatoCedula(string $cedula) {
        $ced = strtoupper(trim($cedula));
        // Personas (V)
        if (preg_match('/^V\d{7,8}$/i', $ced)) {
            return true;
        }
        // Entidades: J, G, E, EM con dígito verificador separado por guion
        if (preg_match('/^(J|G|E|EM)\d{7,8}-\d{1}$/i', $ced)) {
            return true;
        }
        return false;
    }

    /**
     * Crea un nuevo usuario en la base de datos.
     * @param array $data (cedula, nombre, apellido, email, password, telefono, id_rol)
     * @return array ['success' => bool, 'message' => string]
     */
    public function crearUsuario(array $data) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        // Extraer datos
        $cedula = $data['cedula'] ?? '';
        $email = $data['email'] ?? '';
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $password = $data['password'] ?? null;
        $telefono = $data['telefono'] ?? null;
        $id_rol = $data['id_rol'] ?? 2; // por defecto agente

        // Validaciones
        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($email)) {
            return ['success' => false, 'message' => 'Faltan campos obligatorios.'];
        }

        // Validar formato de cédula
        if (!$this->validarFormatoCedula($cedula)) {
            return ['success' => false, 'message' => 'Formato de cédula inválido.'];
        }

        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Formato de correo electrónico inválido.'];
        }

        // Unicidad
        if ($this->existeCedula($cedula)) {
            return ['success' => false, 'message' => 'La cédula ya está registrada.'];
        }
        if ($this->existeEmail($email)) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }

        // Validar password
        if (!empty($password) && strlen($password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        // Generar hash de contraseña
        if (empty($password)) {
            $password = bin2hex(random_bytes(4));
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // INICIAR TRANSACCIÓN
        $this->db->beginTransaction();

        try {
            // 1. Insertar en usuario (solo los campos que existen en la tabla usuario)
            $sql1 = "INSERT INTO usuario (cedula, email, password_hash, activo, id_rol) 
                    VALUES (:cedula, :email, :password_hash, 1, :id_rol)";
            
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->bindParam(':cedula', $cedula);
            $stmt1->bindParam(':email', $email);
            $stmt1->bindParam(':password_hash', $password_hash);
            $stmt1->bindParam(':id_rol', $id_rol);
            $stmt1->execute();

            // 2. Insertar en la tabla específica según el rol
            if ($id_rol == 2) { // Agente
                $sql2 = "INSERT INTO agente (cedula_agente, nombre, apellido, telefono) 
                        VALUES (:cedula, :nombre, :apellido, :telefono)";
            } elseif ($id_rol == 1) { // Administrador
                $sql2 = "INSERT INTO administrador (cedula_admin, nombre, apellido, telefono) 
                        VALUES (:cedula, :nombre, :apellido, :telefono)";
            } elseif ($id_rol == 3) { // Cliente
                $sql2 = "INSERT INTO cliente (cedula_asegurado, nombre, apellido, telefono) 
                        VALUES (:cedula, :nombre, :apellido, :telefono)";
            }

            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindParam(':cedula', $cedula);
            $stmt2->bindParam(':nombre', $nombre);
            $stmt2->bindParam(':apellido', $apellido);
            $stmt2->bindParam(':telefono', $telefono);
            $stmt2->execute();

            // Confirmar transacción
            $this->db->commit();

            return ['success' => true, 'message' => 'Usuario creado correctamente.', 'password' => $password];

        } catch (\PDOException $e) {
            // Revertir transacción en caso de error
            $this->db->rollBack();
            error_log("Error de DB al crear usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al insertar en la base de datos: ' . $e->getMessage()];
        }
    }

    /**
     * Actualiza los datos de un usuario/agente, incluyendo la opción de cambiar la contraseña.
     */
    public function actualizarUsuario(string $cedula_original, string $cedula, string $nombre, string $apellido, string $email, string $telefono, string $password = null) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        // INICIAR TRANSACCIÓN
        $this->db->beginTransaction();

        try {
            // 1. Actualizar tabla usuario (solo email y contraseña)
            $sqlUsuario = "UPDATE usuario SET email = :email";
            $params = [':email' => $email, ':cedula_original' => $cedula_original];

            if (!empty($password)) {
                if (strlen($password) < 8) {
                    throw new Exception('La nueva contraseña debe tener al menos 8 caracteres.');
                }
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sqlUsuario .= ", password_hash = :password_hash";
                $params[':password_hash'] = $password_hash;
            }
            
            $sqlUsuario .= " WHERE cedula = :cedula_original";

            $stmtUsuario = $this->db->prepare($sqlUsuario);
            foreach ($params as $key => &$value) {
                $stmtUsuario->bindParam($key, $value);
            }
            $stmtUsuario->execute();

            // 2. Determinar el rol para saber en qué tabla actualizar
            // Primero obtenemos el rol del usuario
            $sqlRol = "SELECT id_rol FROM usuario WHERE cedula = :cedula";
            $stmtRol = $this->db->prepare($sqlRol);
            $stmtRol->bindParam(':cedula', $cedula_original);
            $stmtRol->execute();
            $rolData = $stmtRol->fetch(PDO::FETCH_ASSOC);
            
            if (!$rolData) {
                throw new Exception('Usuario no encontrado.');
            }

            $id_rol = $rolData['id_rol'];

            // 3. Actualizar en la tabla específica según el rol
            if ($id_rol == 2) { // Agente
                $tabla = 'agente';
                $campoCedula = 'cedula_agente';
            } elseif ($id_rol == 1) { // Administrador
                $tabla = 'administrador';
                $campoCedula = 'cedula_admin';
            } elseif ($id_rol == 3) { // Cliente
                $tabla = 'cliente';
                $campoCedula = 'cedula_asegurado';
            } else {
                throw new Exception('Rol no válido.');
            }

            $sqlEspecifica = "UPDATE $tabla SET 
                            $campoCedula = :cedula_nueva,
                            nombre = :nombre,
                            apellido = :apellido,
                            telefono = :telefono
                            WHERE $campoCedula = :cedula_original";

            $stmtEspecifica = $this->db->prepare($sqlEspecifica);
            $stmtEspecifica->bindParam(':cedula_nueva', $cedula);
            $stmtEspecifica->bindParam(':nombre', $nombre);
            $stmtEspecifica->bindParam(':apellido', $apellido);
            $stmtEspecifica->bindParam(':telefono', $telefono);
            $stmtEspecifica->bindParam(':cedula_original', $cedula_original);
            $stmtEspecifica->execute();

            // Confirmar transacción
            $this->db->commit();

            return ['success' => true, 'message' => 'Usuario actualizado correctamente.'];

        } catch (\Exception $e) {
            // Revertir transacción en caso de error
            $this->db->rollBack();
            error_log("Error de DB al actualizar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function obtenerAgenteLoggeado(string $cedula_agente): array|false {
        if (!$this->db) return false;

        // CONSULTA CLAVE: JOIN con la tabla 'rol'
       $sql = "SELECT 
                    u.cedula,
                    u.email,
                    u.password_hash,
                    u.activo,
                    u.id_rol,
                    r.nombre_rol,
                    -- Recuperamos datos personales dependiendo de donde existan
                    COALESCE(ag.nombre, ad.nombre, cl.nombre) AS nombre,
                    COALESCE(ag.apellido, ad.apellido, cl.apellido) AS apellido,
                    COALESCE(ag.telefono, ad.telefono, cl.telefono) AS telefono
                FROM 
                    usuario u
                JOIN 
                    rol r ON u.id_rol = r.id_rol
                -- Joins a las tablas específicas
                LEFT JOIN agente ag ON u.cedula = ag.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente cl ON u.cedula = cl.cedula_asegurado
                WHERE 
                    u.cedula = :identificador OR u.email = :identificador
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC); 
            
        } catch (\PDOException $e) {
            error_log("Error de DB al buscar usuario: " . $e->getMessage());
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

    public function actualizarPerfil(array $datos, array $archivoFoto = null) {
        if (!$this->db) return ['success' => false, 'message' => 'Error de conexión.'];

        $cedula = $datos['cedula'];
        $rol_nombre = strtolower($datos['rol_nombre']); // Necesitamos saber si es agente, admin, etc.
        
        try {
            $this->db->beginTransaction();

            // 1. Manejo de la Foto (Si se subió una nueva)
            $sqlFoto = "";
            $paramsUsuario = [
                ':email' => $datos['email'],
                ':cedula' => $cedula
            ];

            if ($archivoFoto && $archivoFoto['error'] === UPLOAD_ERR_OK) {
                // Lógica simple de subida
                $ext = pathinfo($archivoFoto['name'], PATHINFO_EXTENSION);
                $nombreArchivo = 'perfil_' . $cedula . '_' . time() . '.' . $ext;
                $rutaDestino = dirname(__DIR__) . '/assets/img/usuarios/' . $nombreArchivo;
                
                if (move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                    $sqlFoto = ", foto_perfil = :foto";
                    $paramsUsuario[':foto'] = $nombreArchivo;
                }
            }

            // 2. Manejo de Contraseña (Solo si el usuario escribió una nueva)
            $sqlPass = "";
            if (!empty($datos['password_nueva'])) {
                $sqlPass = ", password_hash = :pass";
                $paramsUsuario[':pass'] = password_hash($datos['password_nueva'], PASSWORD_DEFAULT);
            }

            // 3. Actualizar tabla USUARIO (Datos comunes)
            $sqlUsuario = "UPDATE usuario SET email = :email $sqlFoto $sqlPass WHERE cedula = :cedula";
            $stmtU = $this->db->prepare($sqlUsuario);
            $stmtU->execute($paramsUsuario);

            // 4. Actualizar tabla ESPECÍFICA (Datos personales)
            // Aquí decidimos a qué tabla ir según el rol
            $tablaDestino = match ($rol_nombre) {
                'administrador' => 'administrador',
                'agente'        => 'agente',
                'asegurado'     => 'cliente', // Ojo: en tu BD la tabla es 'cliente'
                default         => null
            };

            if ($tablaDestino) {
                // El nombre del campo ID varía: cedula_agente, cedula_admin, cedula_asegurado
                $campoClave = match ($tablaDestino) {
                    'administrador' => 'cedula_admin',
                    'agente'        => 'cedula_agente',
                    'cliente'       => 'cedula_asegurado',
                };

                $sqlRol = "UPDATE $tablaDestino SET 
                           nombre = :nombre, 
                           apellido = :apellido, 
                           telefono = :telefono 
                           WHERE $campoClave = :cedula";
                
                $stmtR = $this->db->prepare($sqlRol);
                $stmtR->execute([
                    ':nombre'   => $datos['nombre'],
                    ':apellido' => $datos['apellido'],
                    ':telefono' => $datos['telefono'],
                    ':cedula'   => $cedula
                ]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Perfil actualizado correctamente.', 'foto' => $paramsUsuario[':foto'] ?? null];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error actualizarPerfil: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar los datos: ' . $e->getMessage()];
        }
    }
    public function eliminarUsuario(string $cedula): array
    {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }
        
        try {
            $this->db->beginTransaction();
            
            // Primero eliminamos de las tablas dependientes (por las restricciones de clave foránea)
            // Eliminar de agente_permiso
            $sql1 = "DELETE FROM agente_permiso WHERE cedula_agente = :cedula";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->bindParam(':cedula', $cedula);
            $stmt1->execute();
            
            // Eliminar de agente
            $sql2 = "DELETE FROM agente WHERE cedula_agente = :cedula";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindParam(':cedula', $cedula);
            $stmt2->execute();
            
            // Finalmente eliminar de usuario (se eliminará en cascada por las FK)
            $sql3 = "DELETE FROM usuario WHERE cedula = :cedula";
            $stmt3 = $this->db->prepare($sql3);
            $stmt3->bindParam(':cedula', $cedula);
            $stmt3->execute();
            
            $this->db->commit();
            
            if ($stmt3->rowCount() > 0) {
                return ['success' => true, 'message' => 'Agente eliminado correctamente.'];
            } else {
                return ['success' => false, 'message' => 'No se encontró el agente para eliminar.'];
            }
            
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Error de DB al eliminar usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el agente: ' . $e->getMessage()];
        }
    }

    public function obtenerUsuarioPorCedula(string $cedula) {
        if (!$this->db) return false;

        $sql = "SELECT 
                    u.cedula,
                    u.email,
                    u.activo,
                    r.nombre_rol AS rol,
                    COALESCE(a.nombre, ad.nombre, c.nombre) AS nombre,
                    COALESCE(a.apellido, ad.apellido, c.apellido) AS apellido,
                    COALESCE(a.telefono, ad.telefono, c.telefono) AS telefono
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN agente a ON u.cedula = a.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente c ON u.cedula = c.cedula_asegurado
                WHERE u.cedula = :cedula";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener usuario por cédula: " . $e->getMessage());
            return false;
        }
    }


}
