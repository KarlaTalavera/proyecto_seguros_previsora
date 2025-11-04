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
}
