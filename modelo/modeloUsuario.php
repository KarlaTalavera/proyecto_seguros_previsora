<?php
require_once dirname(__DIR__) . '/config/conexion.php';

/**
 * Modelo de negocio y DTO para usuarios.
 */
class modeloUsuario {
    private $cedula;
    private $nombre;
    private $apellido;
    private $email;
    private $password_hash;
    private $telefono;
    private $id_rol;
    private $nombre_rol;
    private $foto_perfil;
    private $db;

    public function __construct(array $data = null) {
        if ($data) {
            $this->cedula = $data['cedula'] ?? null;
            $this->nombre = $data['nombre'] ?? null;
            $this->apellido = $data['apellido'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password_hash = $data['password_hash'] ?? null;
            $this->telefono = $data['telefono'] ?? null;
            $this->id_rol = $data['id_rol'] ?? null;
            $this->nombre_rol = $data['nombre_rol'] ?? null;
            $this->foto_perfil = $data['foto_perfil'] ?? null;
        } else {
            try {
                $base_datos = new Base_Datos();
                $this->db = $base_datos->Conexion_Base_Datos();
            } catch (\Exception $e) {
                error_log('Error al inicializar la conexión en modeloUsuario: ' . $e->getMessage());
                $this->db = null;
            }
        }
    }

    public function setCedula($cedula) { $this->cedula = $cedula; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function setApellido($apellido) { $this->apellido = $apellido; }
    public function setEmail($email) { $this->email = $email; }
    public function setPasswordHash($password_hash) { $this->password_hash = $password_hash; }
    public function setTelefono($telefono) { $this->telefono = $telefono; }
    public function setIdRol($id_rol) { $this->id_rol = $id_rol; }
    public function setNombreRol($nombre_rol) { $this->nombre_rol = $nombre_rol; }
    public function setFotoPerfil($foto) { $this->foto_perfil = $foto; }

    public function getCedula() { return $this->cedula; }
    public function getNombre() { return $this->nombre; }
    public function getApellido() { return $this->apellido; }
    public function getEmail() { return $this->email; }
    public function getPasswordHash() { return $this->password_hash; }
    public function getTelefono() { return $this->telefono; }
    public function getIdRol() { return $this->id_rol; }
    public function getFotoPerfil() { return $this->foto_perfil; }
    public function getNombreRol() { return strtolower($this->nombre_rol ?? ''); }

    public function getNombreCompleto(): string {
        return trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? ''));
    }

    private function normalizarRol(string $rol): string {
        return strtolower(trim($rol));
    }

    private function tablaYClavePorRol(string $rol): ?array {
        return match ($this->normalizarRol($rol)) {
            'administrador' => ['tabla' => 'administrador', 'clave' => 'cedula_admin'],
            'agente' => ['tabla' => 'agente', 'clave' => 'cedula_agente'],
            'asegurado', 'cliente' => ['tabla' => 'cliente', 'clave' => 'cedula_asegurado'],
            default => null,
        };
    }

    private function obtenerNombreRolPorId(int $id_rol): ?string {
        if (!$this->db) {
            return null;
        }

        try {
            $stmt = $this->db->prepare('SELECT nombre_rol FROM rol WHERE id_rol = :id LIMIT 1');
            $stmt->bindParam(':id', $id_rol, PDO::PARAM_INT);
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return $valor ? strtolower($valor) : null;
        } catch (\PDOException $e) {
            error_log('Error obtenerNombreRolPorId: ' . $e->getMessage());
            return null;
        }
    }

    protected function getUsuarioPorIdentificador(string $identificador) {
        if (!$this->db) {
            error_log('Error: Conexión a DB no inicializada en el Modelo.');
            return false;
        }

        $sql = 'SELECT
                    u.cedula,
                    u.email,
                    u.password_hash,
                    u.activo,
                    u.id_rol,
                    u.foto_perfil,
                    r.nombre_rol,
                    COALESCE(ag.nombre, ad.nombre, cl.nombre, \'\') AS nombre,
                    COALESCE(ag.apellido, ad.apellido, cl.apellido, \'\') AS apellido,
                    COALESCE(ag.telefono, ad.telefono, cl.telefono, \'\') AS telefono
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN agente ag ON u.cedula = ag.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente cl ON u.cedula = cl.cedula_asegurado
                WHERE u.cedula = :identificador OR u.email = :identificador
                LIMIT 1';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':identificador', $identificador);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error de DB al buscar usuario: ' . $e->getMessage());
            return false;
        }
    }

    public function login(string $identificador, string $clave) {
        $data = $this->getUsuarioPorIdentificador($identificador);
        if ($data && password_verify($clave, $data['password_hash'])) {
            return new self($data);
        }

        return false;
    }

    public function obtenerTodosLosUsuarios() {
        if (!$this->db) {
            error_log('Error: Conexión a DB no inicializada en el Modelo.');
            return false;
        }

        $sql = 'SELECT
                    u.cedula,
                    u.email,
                    u.activo,
                    u.id_rol,
                    u.foto_perfil,
                    r.nombre_rol,
                    ag.nombre AS agente_nombre,
                    ag.apellido AS agente_apellido,
                    ag.telefono AS agente_telefono,
                    ad.nombre AS admin_nombre,
                    ad.apellido AS admin_apellido,
                    ad.telefono AS admin_telefono,
                    cl.nombre AS cliente_nombre,
                    cl.apellido AS cliente_apellido,
                    cl.telefono AS cliente_telefono,
                    COALESCE(ag.nombre, ad.nombre, cl.nombre, \'\') AS nombre,
                    COALESCE(ag.apellido, ad.apellido, cl.apellido, \'\') AS apellido,
                    COALESCE(ag.telefono, ad.telefono, cl.telefono, \'\') AS telefono
                FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN agente ag ON u.cedula = ag.cedula_agente
                LEFT JOIN administrador ad ON u.cedula = ad.cedula_admin
                LEFT JOIN cliente cl ON u.cedula = cl.cedula_asegurado
                ORDER BY apellido, nombre';

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('Error de DB al obtener todos los usuarios: ' . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodosLosAgentes() {
        if (!$this->db) {
            error_log('Error: Conexión a DB no inicializada en el Modelo.');
            return false;
        }

        $sql = "SELECT
                    u.cedula,
                    u.email,
                    u.activo,
                    u.foto_perfil,
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
            error_log('Error de DB al obtener todos los agentes: ' . $e->getMessage());
            return false;
        }
    }

    public function existeCedula(string $cedula): bool {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuario WHERE cedula = :cedula');
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Error DB existeCedula: ' . $e->getMessage());
            return false;
        }
    }

    public function existeEmail(string $email): bool {
        if (!$this->db) {
            return false;
        }

        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuario WHERE email = :email');
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Error DB existeEmail: ' . $e->getMessage());
            return false;
        }
    }

    private function emailEnUsoPorOtro(string $email, string $cedula): bool {
        if (!$this->db) {
            return true;
        }

        try {
            $stmt = $this->db->prepare('SELECT COUNT(*) FROM usuario WHERE email = :email AND cedula != :cedula');
            $stmt->execute([':email' => $email, ':cedula' => $cedula]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (\PDOException $e) {
            error_log('Error emailEnUsoPorOtro: ' . $e->getMessage());
            return true;
        }
    }

    public function validarFormatoCedula(string $cedula): bool {
        $ced = strtoupper(trim($cedula));
        if (preg_match('/^V\d{7,8}$/', $ced)) {
            return true;
        }
        if (preg_match('/^(J|G|E|EM)\d{7,8}-\d$/', $ced)) {
            return true;
        }
        return false;
    }

    public function crearUsuario(array $data): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        $cedula = strtoupper(trim($data['cedula'] ?? ''));
        $email = trim($data['email'] ?? '');
        $nombre = trim($data['nombre'] ?? '');
        $apellido = trim($data['apellido'] ?? '');
        $password = $data['password'] ?? null;
        $telefono = trim($data['telefono'] ?? '') ?: null;
        $direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
        $direccion = $direccion !== '' ? $direccion : null;
        $fecha_nacimiento = $data['fecha_nacimiento'] ?? null;
        if ($fecha_nacimiento) {
            $fecha_nacimiento = trim($fecha_nacimiento);
            $fecha_dt = \DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if (!$fecha_dt || $fecha_dt->format('Y-m-d') !== $fecha_nacimiento) {
                return ['success' => false, 'message' => 'Fecha de nacimiento inválida.'];
            }
            $fecha_nacimiento = $fecha_dt->format('Y-m-d');
        } else {
            $fecha_nacimiento = null;
        }

        $id_rol = (int) ($data['id_rol'] ?? 0);
        $rol_nombre = $data['rol_nombre'] ?? null;

        if (!$cedula || !$nombre || !$apellido || !$email || !$id_rol) {
            return ['success' => false, 'message' => 'Faltan campos obligatorios.'];
        }

        if (!$this->validarFormatoCedula($cedula)) {
            return ['success' => false, 'message' => 'Formato de cédula inválido. Ejemplo: V12345678 o J12345678-9'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Formato de correo electrónico inválido.'];
        }

        if ($this->existeCedula($cedula)) {
            return ['success' => false, 'message' => 'La cédula ya está registrada.'];
        }

        if ($this->existeEmail($email)) {
            return ['success' => false, 'message' => 'El correo electrónico ya está registrado.'];
        }

        if (!empty($password) && strlen($password) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        if (empty($password)) {
            $password = bin2hex(random_bytes(4));
        }
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        if (!$rol_nombre) {
            $rol_nombre = $this->obtenerNombreRolPorId($id_rol);
        }
        if (!$rol_nombre) {
            return ['success' => false, 'message' => 'Rol no reconocido.'];
        }

        $tablaInfo = $this->tablaYClavePorRol($rol_nombre);
        if (!$tablaInfo) {
            return ['success' => false, 'message' => 'No se pudo determinar la tabla destino del rol.'];
        }

        try {
            $this->db->beginTransaction();

            $sqlUsuario = 'INSERT INTO usuario (cedula, email, password_hash, activo, id_rol, foto_perfil)
                           VALUES (:cedula, :email, :password_hash, 1, :id_rol, :foto)';
            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute([
                ':cedula' => $cedula,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':id_rol' => $id_rol,
                ':foto' => 'undraw_profile.svg',
            ]);

            $tabla = $tablaInfo['tabla'];
            $clave = $tablaInfo['clave'];

            if (in_array($this->normalizarRol($rol_nombre), ['cliente', 'asegurado'], true)) {
                $sqlDetalle = "INSERT INTO {$tabla} ({$clave}, nombre, apellido, telefono, direccion, fecha_nacimiento)
                                VALUES (:cedula, :nombre, :apellido, :telefono, :direccion, :fecha_nacimiento)";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    ':cedula' => $cedula,
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':telefono' => $telefono,
                    ':direccion' => $direccion,
                    ':fecha_nacimiento' => $fecha_nacimiento,
                ]);
            } else {
                $sqlDetalle = "INSERT INTO {$tabla} ({$clave}, nombre, apellido, telefono)
                                VALUES (:cedula, :nombre, :apellido, :telefono)";
                $stmtDetalle = $this->db->prepare($sqlDetalle);
                $stmtDetalle->execute([
                    ':cedula' => $cedula,
                    ':nombre' => $nombre,
                    ':apellido' => $apellido,
                    ':telefono' => $telefono,
                ]);
            }

            $this->db->commit();

            return ['success' => true, 'message' => 'Usuario creado correctamente.', 'password' => $password];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log('Error de DB al crear usuario: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al insertar en la base de datos.'];
        }
    }

    public function obtenerUsuarioPorCedula(string $cedula) {
        $data = $this->getUsuarioPorIdentificador($cedula);
        if (!$data) {
            return false;
        }

        return [
            'cedula' => $data['cedula'],
            'email' => $data['email'],
            'nombre' => $data['nombre'] ?? '',
            'apellido' => $data['apellido'] ?? '',
            'telefono' => $data['telefono'] ?? '',
            'rol' => strtolower($data['nombre_rol'] ?? ''),
            'foto_perfil' => $data['foto_perfil'] ?? 'undraw_profile.svg',
        ];
    }

    public function actualizarUsuario(string $cedula_original, string $cedula, string $nombre, string $apellido, string $email, string $telefono, string $password = null): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        if ($cedula_original !== $cedula) {
            return ['success' => false, 'message' => 'Por ahora no es posible cambiar la cédula desde este formulario.'];
        }

        $datosOriginales = $this->obtenerUsuarioPorCedula($cedula_original);
        if (!$datosOriginales) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        return $this->actualizarPerfil([
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => $email,
            'telefono' => $telefono,
            'password_nueva' => $password ?? '',
            'rol_nombre' => $datosOriginales['rol'],
            'foto_actual' => $datosOriginales['foto_perfil'] ?? null,
        ]);
    }

    public function obtenerAgenteLoggeado(string $cedula_agente): array|false {
        $data = $this->getUsuarioPorIdentificador($cedula_agente);
        return $data ?: false;
    }

    public function Conexion_Base_Datos(array $data = null): void {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (\Exception $e) {
            error_log('Error inicializando DB en modeloUsuario: ' . $e->getMessage());
            $this->db = null;
        }
    }

    public function desactivarUsuario(string $cedula): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        $cedula = strtoupper(trim($cedula));
        if ($cedula === '') {
            return ['success' => false, 'message' => 'Cédula no proporcionada.'];
        }

        try {
            $stmt = $this->db->prepare('UPDATE usuario SET activo = 0 WHERE cedula = :cedula');
            $stmt->bindParam(':cedula', $cedula);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Usuario no encontrado o ya desactivado.'];
            }

            return ['success' => true, 'message' => 'Usuario desactivado correctamente.'];
        } catch (\PDOException $e) {
            error_log('Error desactivarUsuario: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo desactivar el usuario.'];
        }
    }

    public function eliminarUsuario(string $cedula): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión a la base de datos.'];
        }

        try {
            $this->db->beginTransaction();

            $sql1 = 'DELETE FROM agente_permiso WHERE cedula_agente = :cedula';
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->bindParam(':cedula', $cedula);
            $stmt1->execute();

            $sql2 = 'DELETE FROM agente WHERE cedula_agente = :cedula';
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->bindParam(':cedula', $cedula);
            $stmt2->execute();

            $sql3 = 'DELETE FROM usuario WHERE cedula = :cedula';
            $stmt3 = $this->db->prepare($sql3);
            $stmt3->bindParam(':cedula', $cedula);
            $stmt3->execute();

            $this->db->commit();

            if ($stmt3->rowCount() > 0) {
                return ['success' => true, 'message' => 'Agente eliminado correctamente.'];
            }

            return ['success' => false, 'message' => 'No se encontró el agente para eliminar.'];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log('Error de DB al eliminar usuario: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el agente: ' . $e->getMessage()];
        }
    }

    public function actualizarPerfil(array $datos, array $archivoFoto = null): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Error de conexión.'];
        }

        $cedula = strtoupper(trim($datos['cedula'] ?? ''));
        $cedulaOriginal = strtoupper(trim($datos['cedula_original'] ?? $cedula));
        $rol_nombre = $this->normalizarRol($datos['rol_nombre'] ?? '');
        $nombre = trim($datos['nombre'] ?? '');
        $apellido = trim($datos['apellido'] ?? '');
        $telefono = trim($datos['telefono'] ?? '');
        $email = trim($datos['email'] ?? '');
        $passwordNueva = $datos['password_nueva'] ?? '';
        $fotoActual = $datos['foto_actual'] ?? null;

        if (!$cedulaOriginal || !$rol_nombre) {
            return ['success' => false, 'message' => 'Datos de identificación incompletos.'];
        }

        if ($cedula === '') {
            $cedula = $cedulaOriginal;
        }

        $cambioCedula = $cedula !== $cedulaOriginal;

        if (!$nombre || !$apellido) {
            return ['success' => false, 'message' => 'Nombre y apellido son obligatorios.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Correo electrónico inválido.'];
        }

        if ($this->emailEnUsoPorOtro($email, $cedulaOriginal)) {
            return ['success' => false, 'message' => 'El correo electrónico pertenece a otro usuario.'];
        }

        if ($passwordNueva !== '' && strlen($passwordNueva) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }

        if ($cambioCedula) {
            if ($rol_nombre !== 'administrador') {
                return ['success' => false, 'message' => 'Solo un administrador puede actualizar su cédula.'];
            }
            if (!$this->validarFormatoCedula($cedula)) {
                return ['success' => false, 'message' => 'Formato de cédula inválido.'];
            }
            if ($this->existeCedula($cedula)) {
                return ['success' => false, 'message' => 'La nueva cédula ya está registrada.'];
            }
        }

        $tablaInfo = $this->tablaYClavePorRol($rol_nombre);
        if (!$tablaInfo) {
            return ['success' => false, 'message' => 'Rol no reconocido para actualizar datos personales.'];
        }

        if (!$fotoActual) {
            $infoActual = $this->obtenerUsuarioPorCedula($cedulaOriginal);
            $fotoActual = $infoActual ? ($infoActual['foto_perfil'] ?? 'undraw_profile.svg') : 'undraw_profile.svg';
        }

        $fotoGuardada = null;
        $directorioFotos = dirname(__DIR__) . '/assets/img/usuarios/';

        if ($archivoFoto && ($archivoFoto['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            if ($archivoFoto['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'message' => 'Error al subir la imagen de perfil.'];
            }

            $extension = strtolower(pathinfo($archivoFoto['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
            if (!in_array($extension, $permitidas, true)) {
                return ['success' => false, 'message' => 'Formato de imagen no permitido.'];
            }

            if (!is_dir($directorioFotos)) {
                mkdir($directorioFotos, 0775, true);
            }

            $nombreArchivo = 'perfil_' . preg_replace('/[^A-Za-z0-9]/', '', $cedula) . '_' . time() . '.' . $extension;
            $rutaDestino = $directorioFotos . $nombreArchivo;

            if (!move_uploaded_file($archivoFoto['tmp_name'], $rutaDestino)) {
                return ['success' => false, 'message' => 'No se pudo guardar la imagen de perfil.'];
            }

            $fotoGuardada = $nombreArchivo;
        }

        try {
            $this->db->beginTransaction();

            $setFoto = $fotoGuardada ? ', foto_perfil = :foto' : '';
            $setPass = $passwordNueva !== '' ? ', password_hash = :pass' : '';
            $setCedula = $cambioCedula ? ', cedula = :cedula_nueva' : '';
            $sqlUsuario = "UPDATE usuario SET email = :email{$setFoto}{$setPass}{$setCedula} WHERE cedula = :cedula_original";

            $paramsUsuario = [
                ':email' => $email,
                ':cedula_original' => $cedulaOriginal,
            ];

            if ($fotoGuardada) {
                $paramsUsuario[':foto'] = $fotoGuardada;
            }
            if ($passwordNueva !== '') {
                $paramsUsuario[':pass'] = password_hash($passwordNueva, PASSWORD_DEFAULT);
            }
            if ($cambioCedula) {
                $paramsUsuario[':cedula_nueva'] = $cedula;
            }

            $stmtUsuario = $this->db->prepare($sqlUsuario);
            $stmtUsuario->execute($paramsUsuario);

            $tabla = $tablaInfo['tabla'];
            $clave = $tablaInfo['clave'];
            $setClave = $cambioCedula ? ", {$clave} = :cedula_nueva" : '';
            $sqlRol = "UPDATE {$tabla} SET nombre = :nombre, apellido = :apellido, telefono = :telefono{$setClave} WHERE {$clave} = :cedula_original";
            $stmtRol = $this->db->prepare($sqlRol);
            $paramsRol = [
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':telefono' => $telefono !== '' ? $telefono : null,
                ':cedula_original' => $cedulaOriginal,
            ];
            if ($cambioCedula) {
                $paramsRol[':cedula_nueva'] = $cedula;
            }
            $stmtRol->execute($paramsRol);

            $this->db->commit();

            if ($fotoGuardada && $fotoActual && $fotoActual !== 'undraw_profile.svg' && $fotoActual !== $fotoGuardada) {
                $rutaAnterior = $directorioFotos . $fotoActual;
                if (is_file($rutaAnterior)) {
                    @unlink($rutaAnterior);
                }
            }

            return [
                'success' => true,
                'message' => 'Perfil actualizado correctamente.',
                'foto' => $fotoGuardada ?? $fotoActual,
                'cedula' => $cambioCedula ? $cedula : $cedulaOriginal,
            ];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            if ($fotoGuardada) {
                $rutaDestino = $directorioFotos . $fotoGuardada;
                if (is_file($rutaDestino)) {
                    @unlink($rutaDestino);
                }
            }
            error_log('Error actualizarPerfil: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al guardar los datos.'];
        }
    }
}
