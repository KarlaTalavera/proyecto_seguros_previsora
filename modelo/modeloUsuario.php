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
            $this->nombre_rol = $data['nombre_rol'] ?? null; // Viene del JOIN
        } else {
            // Caso 2: Se usa como Modelo (sin argumentos) para inicializar la conexión a la DB
            try {
                // *** CORRECCIÓN: Usar la clase Base_Datos y su método de conexión ***
                $base_datos = new Base_Datos();
                $this->db = $base_datos->Conexion_Base_Datos();
                // *******************************************************************
            } catch (\Exception $e) {
                // Manejar error de conexión
                error_log("Error al inicializar la conexión en modeloUsuario: " . $e->getMessage());
                // Opcionalmente, lanzar o reportar error
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
                    u.*, r.nombre_rol 
                FROM 
                    usuario u
                JOIN 
                    rol r ON u.id_rol = r.id_rol
                WHERE 
                    u.cedula = :identificador OR u.email = :identificador
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC); // Usamos FETCH_ASSOC para consistencia
            
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

        $sql = "SELECT u.*, r.nombre_rol as rol FROM usuario u JOIN rol r ON u.id_rol = r.id_rol ORDER BY u.apellido, u.nombre";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener todos los usuarios: " . $e->getMessage());
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

        $cedula = $data['cedula'] ?? '';
        $email = $data['email'] ?? '';
        $nombre = $data['nombre'] ?? '';
        $apellido = $data['apellido'] ?? '';
        $password = $data['password'] ?? null;
        $telefono = $data['telefono'] ?? null;
        $id_rol = $data['id_rol'] ?? 2; // por defecto agente

        // Validaciones simples
        if (empty($cedula) || empty($nombre) || empty($apellido) || empty($email)) {
            return ['success' => false, 'message' => 'Faltan campos obligatorios.'];
        }

        // Validar formato de cédula según reglas de la aseguradora
        if (!$this->validarFormatoCedula($cedula)) {
            return ['success' => false, 'message' => 'Formato de cédula inválido. Ejemplos válidos: V12345678 o J12345678-9'];
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

        // Validar password si fue provisto
        if (!empty($password) && strlen($password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        // Hash de contraseña: si no se provee, generamos una contraseña aleatoria
        if (empty($password)) {
            $password = bin2hex(random_bytes(4)); // 8 hex chars
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (cedula, nombre, apellido, email, password_hash, telefono, id_rol) VALUES (:cedula, :nombre, :apellido, :email, :password_hash, :telefono, :id_rol)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':apellido', $apellido);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password_hash', $password_hash);
            $stmt->bindParam(':telefono', $telefono);
            $stmt->bindParam(':id_rol', $id_rol);
            $stmt->execute();
            return ['success' => true, 'message' => 'Usuario creado correctamente.', 'password' => $password];
        } catch (\PDOException $e) {
            error_log("Error de DB al crear usuario: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al insertar en la base de datos.'];
        }
    }
        public function obtenerUsuarioPorCedula(string $cedula) {
        if (!$this->db) return false;

        // Se asume que 'rol' es una columna en 'usuario' o que se hace un JOIN a la tabla 'rol'
        $sql = "SELECT u.cedula, u.nombre, u.apellido, u.email, u.telefono, r.nombre_rol AS rol
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
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

    /**
     * Actualiza los datos de un usuario/agente, incluyendo la opción de cambiar la contraseña.
     */
    public function actualizarUsuario(string $cedula_original, string $cedula, string $nombre, string $apellido, string $email, string $telefono, string $password = null) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        $sql = "UPDATE usuario SET cedula = :cedula, nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono";
        $params = [
            ':cedula' => $cedula,
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':email' => $email,
            ':telefono' => $telefono,
            ':cedula_original' => $cedula_original
        ];

        // Si se proporciona una nueva contraseña, la incluimos en la consulta
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return ['success' => false, 'message' => 'La nueva contraseña debe tener al menos 8 caracteres.'];
            }
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = $password_hash;
        }
        
        $sql .= " WHERE cedula = :cedula_original";

        try {
            $stmt = $this->db->prepare($sql);
            
            // Bind de parámetros
            foreach ($params as $key => &$value) {
                $stmt->bindParam($key, $value);
            }
            
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Agente actualizado correctamente.'];
            } else {
                // Esto puede ser porque no hubo cambios o la cédula_original no existe
                return ['success' => false, 'message' => 'No se realizaron cambios o la cédula original no fue encontrada.'];
            }
        } catch (\PDOException $e) {
            // En caso de error, p. ej. cédula duplicada (si se permitiera editar la cédula)
            $msg = "Error de DB al actualizar usuario: " . $e->getMessage();
            error_log($msg);
            return ['success' => false, 'message' => 'Error al actualizar: ' . (strpos($e->getMessage(), 'Duplicate entry') !== false ? 'La cédula o email ya existen.' : 'Error interno.')];
        }
    }

    public function obtenerAgenteLoggeado(string $cedula_agente): array|false {
        if (!$this->db) return false;

        // CONSULTA CLAVE: JOIN con la tabla 'rol'
        $sql = "SELECT u.cedula, u.nombre, u.apellido, u.email, u.telefono, r.nombre AS nombre_rol 
                FROM usuario u 
                JOIN rol r ON u.id_rol = r.id_rol 
                WHERE u.cedula = :cedula"; 

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula_agente);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Error de DB al obtener agente loggeado: " . $e->getMessage());
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
