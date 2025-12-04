<?php
require_once dirname(__DIR__) . '/config/conexion.php';
require_once __DIR__ . '/modeloPermiso.php';

class ModeloSolicitud {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log('Error inicializando DB en ModeloSolicitud: ' . $e->getMessage());
        }
    }

    private function conexionDisponible(): bool {
        return $this->db instanceof PDO;
    }

    public function obtenerIdClientePorCedula(string $cedula): ?int {
        if (!$this->conexionDisponible()) {
            return null;
        }
        try {
            $sql = 'SELECT id_cliente FROM cliente WHERE cedula_asegurado = :cedula LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return $valor !== false ? (int)$valor : null;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerIdClientePorCedula: ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerPolizasActivasPorCliente(string $cedula): array {
        if (!$this->conexionDisponible()) {
            return [];
        }
        try {
            $sql = 'SELECT p.id_poliza, p.numero_poliza, p.estado, tp.nombre AS ramo
                    FROM poliza p
                    INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                    INNER JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    WHERE c.cedula_asegurado = :cedula
                      AND p.estado IN ("ACTIVA", "VENCER", "VIGENTE")
                    ORDER BY p.numero_poliza';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerPolizasActivasPorCliente: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerAgentePreferentePorCliente(int $idCliente): ?string {
        if (!$this->conexionDisponible()) {
            return null;
        }
        try {
            $sql = 'SELECT p.cedula_agente
                    FROM poliza p
                    WHERE p.id_cliente = :idCliente
                      AND p.estado IN ("ACTIVA", "VENCER", "VIGENTE")
                    ORDER BY p.id_poliza DESC
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            $cedula = $stmt->fetchColumn();
            return $cedula !== false ? (string)$cedula : null;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerAgentePreferentePorCliente: ' . $e->getMessage());
            return null;
        }
    }

    private function obtenerNombreAgente(?string $cedulaAgente): ?string {
        if (!$this->conexionDisponible() || !$cedulaAgente) {
            return null;
        }
        try {
            $sql = 'SELECT CONCAT(nombre, " ", apellido) AS nombre
                    FROM agente
                    WHERE cedula_agente = :cedula
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedulaAgente, PDO::PARAM_STR);
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return $valor !== false ? (string)$valor : null;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerNombreAgente: ' . $e->getMessage());
            return null;
        }
    }

    private function obtenerNombreClientePorId(int $idCliente): ?string {
        if (!$this->conexionDisponible()) {
            return null;
        }
        try {
            $sql = 'SELECT CONCAT(nombre, " ", apellido) AS nombre
                    FROM cliente
                    WHERE id_cliente = :id
                    LIMIT 1';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            $valor = $stmt->fetchColumn();
            return $valor !== false ? (string)$valor : null;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerNombreClientePorId: ' . $e->getMessage());
            return null;
        }
    }

    public function crearSolicitudPoliza(array $datos): array {
        if (!$this->conexionDisponible()) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        $idCliente = (int)($datos['id_cliente'] ?? 0);
        $cedulaCliente = trim((string)($datos['cedula_cliente'] ?? ''));
        $idCategoria = (int)($datos['id_categoria'] ?? 0);
        $idTipoPoliza = (int)($datos['id_tipo_poliza'] ?? 0);
        $descripcion = trim((string)($datos['descripcion'] ?? '')) ?: null;
        $contacto = trim((string)($datos['contacto_preferido'] ?? '')) ?: null;

        if ($idCliente <= 0 || !$cedulaCliente || $idCategoria <= 0 || $idTipoPoliza <= 0) {
            return ['success' => false, 'message' => 'Datos incompletos para registrar la solicitud.'];
        }

        try {
            $this->db->beginTransaction();

            $sqlTipo = 'SELECT id_tipo_poliza, id_categoria FROM tipo_poliza WHERE id_tipo_poliza = :id LIMIT 1';
            $stmtTipo = $this->db->prepare($sqlTipo);
            $stmtTipo->bindParam(':id', $idTipoPoliza, PDO::PARAM_INT);
            $stmtTipo->execute();
            $tipoData = $stmtTipo->fetch(PDO::FETCH_ASSOC);
            if (!$tipoData) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El ramo seleccionado no existe.'];
            }
            if ((int)$tipoData['id_categoria'] !== $idCategoria) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El ramo seleccionado no pertenece a la categoría indicada.'];
            }

            $agenteAsignado = $datos['cedula_agente_asignado'] ?? $this->obtenerAgentePreferentePorCliente($idCliente);
            $agenteAsignado = $agenteAsignado ? (string)$agenteAsignado : null;

            $sql = 'INSERT INTO solicitud_poliza
                        (id_cliente, cedula_cliente, id_categoria, id_tipo_poliza, descripcion, contacto_preferido, estado, cedula_agente_asignado)
                    VALUES
                        (:id_cliente, :cedula, :id_categoria, :id_tipo, :descripcion, :contacto, "EN_REVISION", :agente)';
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id_cliente', $idCliente, PDO::PARAM_INT);
            $stmt->bindValue(':cedula', $cedulaCliente, PDO::PARAM_STR);
            $stmt->bindValue(':id_categoria', $idCategoria, PDO::PARAM_INT);
            $stmt->bindValue(':id_tipo', $idTipoPoliza, PDO::PARAM_INT);
            if ($descripcion === null) {
                $stmt->bindValue(':descripcion', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            }
            if ($contacto === null) {
                $stmt->bindValue(':contacto', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':contacto', $contacto, PDO::PARAM_STR);
            }
            if ($agenteAsignado === null) {
                $stmt->bindValue(':agente', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':agente', $agenteAsignado, PDO::PARAM_STR);
            }
            $stmt->execute();

            $idGenerado = (int)$this->db->lastInsertId();
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Solicitud de póliza creada correctamente.',
                'id_solicitud' => $idGenerado,
                'agente_asignado' => $agenteAsignado,
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('ModeloSolicitud.crearSolicitudPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo registrar la solicitud.'];
        }
    }

    public function crearSolicitudSiniestro(array $datos): array {
        if (!$this->conexionDisponible()) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        $idPoliza = (int)($datos['id_poliza'] ?? 0);
        $cedulaCliente = trim((string)($datos['cedula_cliente'] ?? ''));
        $tipoIncidente = trim((string)($datos['tipo_incidente'] ?? ''));
        $descripcion = trim((string)($datos['descripcion'] ?? '')) ?: null;
        $fechaIncidente = $datos['fecha_incidente'] ?? null;
        $lugar = trim((string)($datos['lugar_incidente'] ?? '')) ?: null;

        if ($idPoliza <= 0 || !$cedulaCliente || !$tipoIncidente || !$fechaIncidente) {
            return ['success' => false, 'message' => 'Datos incompletos para reportar el siniestro.'];
        }

        try {
            $this->db->beginTransaction();

            $sqlPoliza = 'SELECT p.id_poliza, p.id_cliente, p.cedula_agente, p.numero_poliza, c.cedula_asegurado
                          FROM poliza p
                          INNER JOIN cliente c ON p.id_cliente = c.id_cliente
                          WHERE p.id_poliza = :id LIMIT 1';
            $stmt = $this->db->prepare($sqlPoliza);
            $stmt->bindParam(':id', $idPoliza, PDO::PARAM_INT);
            $stmt->execute();
            $infoPoliza = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$infoPoliza) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La póliza seleccionada no existe.'];
            }

            if (strcasecmp((string)$infoPoliza['cedula_asegurado'], $cedulaCliente) !== 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La póliza seleccionada no pertenece al cliente.'];
            }

            $idCliente = (int)$infoPoliza['id_cliente'];
            $agenteAsignado = $infoPoliza['cedula_agente'] ? (string)$infoPoliza['cedula_agente'] : null;

            $sql = 'INSERT INTO solicitud_siniestro
                        (id_poliza, cedula_cliente, tipo_incidente, descripcion, fecha_incidente, lugar_incidente, estado, cedula_agente_asignado)
                    VALUES
                        (:id_poliza, :cedula, :tipo, :descripcion, :fecha, :lugar, "EN_REVISION", :agente)';
            $stmtInsert = $this->db->prepare($sql);
            $stmtInsert->bindValue(':id_poliza', $idPoliza, PDO::PARAM_INT);
            $stmtInsert->bindValue(':cedula', $cedulaCliente, PDO::PARAM_STR);
            $stmtInsert->bindValue(':tipo', $tipoIncidente, PDO::PARAM_STR);
            if ($descripcion === null) {
                $stmtInsert->bindValue(':descripcion', null, PDO::PARAM_NULL);
            } else {
                $stmtInsert->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
            }
            $stmtInsert->bindValue(':fecha', $fechaIncidente, PDO::PARAM_STR);
            if ($lugar === null) {
                $stmtInsert->bindValue(':lugar', null, PDO::PARAM_NULL);
            } else {
                $stmtInsert->bindValue(':lugar', $lugar, PDO::PARAM_STR);
            }
            if ($agenteAsignado === null) {
                $stmtInsert->bindValue(':agente', null, PDO::PARAM_NULL);
            } else {
                $stmtInsert->bindValue(':agente', $agenteAsignado, PDO::PARAM_STR);
            }
            $stmtInsert->execute();

            $idGenerado = (int)$this->db->lastInsertId();
            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Siniestro registrado correctamente.',
                'id_solicitud' => $idGenerado,
                'agente_asignado' => $agenteAsignado,
                'id_cliente' => $idCliente,
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('ModeloSolicitud.crearSolicitudSiniestro: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo registrar el siniestro.'];
        }
    }

    private function mapearEstadoPoliza(string $estado): array {
        return match ($estado) {
            'CONTACTADO' => ['label' => 'Contactado', 'variant' => 'pendiente'],
            'EN_PROCESO' => ['label' => 'En proceso', 'variant' => 'info'],
            'APROBADO' => ['label' => 'Aprobado', 'variant' => 'aprobado'],
            'RECHAZADO' => ['label' => 'Rechazado', 'variant' => 'rechazado'],
            'CANCELADO' => ['label' => 'Cancelado', 'variant' => 'neutral'],
            default => ['label' => 'En revisión', 'variant' => 'pendiente'],
        };
    }

    private function mapearEstadoSiniestro(string $estado): array {
        return match ($estado) {
            'CITA_PENDIENTE' => ['label' => 'Cita pendiente', 'variant' => 'pendiente'],
            'EN_GESTION' => ['label' => 'En gestión', 'variant' => 'info'],
            'ESCALADO' => ['label' => 'Escalado', 'variant' => 'warning'],
            'CERRADO' => ['label' => 'Cerrado', 'variant' => 'aprobado'],
            'CANCELADO' => ['label' => 'Cancelado', 'variant' => 'neutral'],
            default => ['label' => 'En revisión', 'variant' => 'pendiente'],
        };
    }

    public function obtenerSolicitudesCliente(int $idCliente): array {
        if (!$this->conexionDisponible()) {
            return [];
        }
        try {
            $sqlPoliza = 'SELECT sp.id_solicitud, "poliza" AS origen, "Póliza" AS tipo,
                                 sp.fecha_creacion, sp.estado, sp.descripcion, sp.contacto_preferido,
                                 cp.nombre AS categoria, tp.nombre AS ramo,
                                 sp.cedula_agente_asignado,
                                 sp.fecha_actualizacion,
                                 sp.nota_interna
                          FROM solicitud_poliza sp
                          INNER JOIN tipo_poliza tp ON sp.id_tipo_poliza = tp.id_tipo_poliza
                          INNER JOIN categoria_poliza cp ON sp.id_categoria = cp.id_categoria
                          WHERE sp.id_cliente = :idCliente';

                 $sqlSiniestro = 'SELECT ss.id_solicitud, "siniestro" AS origen, "Siniestro" AS tipo,
                             ss.fecha_creacion, ss.estado, ss.descripcion, NULL AS contacto_preferido,
                             cat.nombre AS categoria, tp.nombre AS ramo,
                                    ss.cedula_agente_asignado,
                                    ss.fecha_actualizacion,
                                    ss.nota_interna,
                                    ss.tipo_incidente, ss.fecha_incidente, ss.lugar_incidente,
                             p.numero_poliza
                             FROM solicitud_siniestro ss
                             INNER JOIN poliza p ON ss.id_poliza = p.id_poliza
                         INNER JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                         INNER JOIN categoria_poliza cat ON tp.id_categoria = cat.id_categoria
                             WHERE p.id_cliente = :idCliente';

            $stmtPoliza = $this->db->prepare($sqlPoliza);
            $stmtPoliza->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
            $stmtPoliza->execute();
            $polizas = $stmtPoliza->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stmtSiniestro = $this->db->prepare($sqlSiniestro);
            $stmtSiniestro->bindParam(':idCliente', $idCliente, PDO::PARAM_INT);
            $stmtSiniestro->execute();
            $siniestros = $stmtSiniestro->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $resultado = [];

            foreach ($polizas as $fila) {
                $map = $this->mapearEstadoPoliza((string)$fila['estado']);
                $resultado[] = [
                    'id' => (int)$fila['id_solicitud'],
                    'origen' => 'poliza',
                    'tipo' => 'Póliza',
                    'categoria' => $fila['categoria'] ?? '',
                    'ramo' => $fila['ramo'] ?? '',
                    'descripcion' => $fila['descripcion'] ?? '',
                    'contacto' => $fila['contacto_preferido'] ?? '',
                    'estado' => $fila['estado'],
                    'estado_label' => $map['label'],
                    'estado_variant' => $map['variant'],
                    'fecha' => $fila['fecha_creacion'],
                    'fecha_actualizacion' => $fila['fecha_actualizacion'],
                    'nota_interna' => $fila['nota_interna'] ?? null,
                    'asignado' => $this->obtenerNombreAgente($fila['cedula_agente_asignado'] ?? null),
                ];
            }

            foreach ($siniestros as $fila) {
                $map = $this->mapearEstadoSiniestro((string)$fila['estado']);
                $resultado[] = [
                    'id' => (int)$fila['id_solicitud'],
                    'origen' => 'siniestro',
                    'tipo' => 'Siniestro',
                    'categoria' => $fila['categoria'] ?? '',
                    'ramo' => $fila['ramo'] ?? '',
                    'descripcion' => $fila['descripcion'] ?? '',
                    'contacto' => '',
                    'estado' => $fila['estado'],
                    'estado_label' => $map['label'],
                    'estado_variant' => $map['variant'],
                    'fecha' => $fila['fecha_creacion'],
                    'fecha_actualizacion' => $fila['fecha_actualizacion'],
                    'nota_interna' => $fila['nota_interna'] ?? null,
                    'asignado' => $this->obtenerNombreAgente($fila['cedula_agente_asignado'] ?? null),
                    'tipo_incidente' => $fila['tipo_incidente'] ?? '',
                    'fecha_incidente' => $fila['fecha_incidente'] ?? null,
                    'lugar_incidente' => $fila['lugar_incidente'] ?? '',
                    'numero_poliza' => $fila['numero_poliza'] ?? '',
                ];
            }

            usort($resultado, function ($a, $b) {
                return strtotime($b['fecha']) <=> strtotime($a['fecha']);
            });

            return $resultado;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerSolicitudesCliente: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerSolicitudesAsignadas(?string $cedulaAgente, bool $esAdmin = false): array {
        if (!$this->conexionDisponible()) {
            return [];
        }
        try {
            $filtroAgente = '';
            if (!$esAdmin && $cedulaAgente) {
                $filtroAgente = ' AND sp.cedula_agente_asignado = :agente';
            }

            $sqlPoliza = 'SELECT sp.id_solicitud, "poliza" AS origen, sp.fecha_creacion, sp.estado,
                                 sp.descripcion, sp.contacto_preferido, cp.nombre AS categoria,
                                 tp.nombre AS ramo, sp.cedula_agente_asignado, sp.fecha_actualizacion,
                                 sp.nota_interna, sp.id_cliente
                          FROM solicitud_poliza sp
                          INNER JOIN tipo_poliza tp ON sp.id_tipo_poliza = tp.id_tipo_poliza
                          INNER JOIN categoria_poliza cp ON sp.id_categoria = cp.id_categoria
                          WHERE 1 = 1' . $filtroAgente;

                 $sqlSiniestro = 'SELECT ss.id_solicitud, "siniestro" AS origen, ss.fecha_creacion, ss.estado,
                             ss.descripcion, ss.tipo_incidente, ss.fecha_incidente,
                             ss.lugar_incidente, ss.cedula_agente_asignado, ss.fecha_actualizacion,
                             ss.nota_interna, p.id_cliente, p.numero_poliza, tp.nombre AS ramo
                             FROM solicitud_siniestro ss
                             INNER JOIN poliza p ON ss.id_poliza = p.id_poliza
                             INNER JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                             WHERE 1 = 1' . $filtroAgente;

            $resultado = [];

            $stmtPoliza = $this->db->prepare($sqlPoliza);
            if ($filtroAgente) {
                $stmtPoliza->bindParam(':agente', $cedulaAgente, PDO::PARAM_STR);
            }
            $stmtPoliza->execute();
            $polizas = $stmtPoliza->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($polizas as $fila) {
                $map = $this->mapearEstadoPoliza((string)$fila['estado']);
                $resultado[] = [
                    'id' => (int)$fila['id_solicitud'],
                    'origen' => 'poliza',
                    'tipo' => 'Póliza',
                    'categoria' => $fila['categoria'] ?? '',
                    'ramo' => $fila['ramo'] ?? '',
                    'descripcion' => $fila['descripcion'] ?? '',
                    'contacto' => $fila['contacto_preferido'] ?? '',
                    'estado' => $fila['estado'],
                    'estado_label' => $map['label'],
                    'estado_variant' => $map['variant'],
                    'fecha' => $fila['fecha_creacion'],
                    'fecha_actualizacion' => $fila['fecha_actualizacion'],
                    'nota_interna' => $fila['nota_interna'] ?? null,
                    'asignado' => $this->obtenerNombreAgente($fila['cedula_agente_asignado'] ?? null),
                    'cedula_asignado' => $fila['cedula_agente_asignado'] ?? null,
                    'cliente' => $this->obtenerNombreClientePorId((int)$fila['id_cliente']) ?? '',
                ];
            }

            $stmtSiniestro = $this->db->prepare($sqlSiniestro);
            if ($filtroAgente) {
                $stmtSiniestro->bindParam(':agente', $cedulaAgente, PDO::PARAM_STR);
            }
            $stmtSiniestro->execute();
            $siniestros = $stmtSiniestro->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($siniestros as $fila) {
                $map = $this->mapearEstadoSiniestro((string)$fila['estado']);
                $resultado[] = [
                    'id' => (int)$fila['id_solicitud'],
                    'origen' => 'siniestro',
                    'tipo' => 'Siniestro',
                    'categoria' => '',
                    'ramo' => $fila['ramo'] ?? '',
                    'descripcion' => $fila['descripcion'] ?? '',
                    'contacto' => '',
                    'estado' => $fila['estado'],
                    'estado_label' => $map['label'],
                    'estado_variant' => $map['variant'],
                    'fecha' => $fila['fecha_creacion'],
                    'fecha_actualizacion' => $fila['fecha_actualizacion'],
                    'nota_interna' => $fila['nota_interna'] ?? null,
                    'asignado' => $this->obtenerNombreAgente($fila['cedula_agente_asignado'] ?? null),
                    'cedula_asignado' => $fila['cedula_agente_asignado'] ?? null,
                    'cliente' => $this->obtenerNombreClientePorId((int)$fila['id_cliente']) ?? '',
                    'tipo_incidente' => $fila['tipo_incidente'] ?? '',
                    'fecha_incidente' => $fila['fecha_incidente'] ?? null,
                    'lugar_incidente' => $fila['lugar_incidente'] ?? '',
                    'numero_poliza' => $fila['numero_poliza'] ?? '',
                ];
            }

            usort($resultado, function ($a, $b) {
                return strtotime($b['fecha']) <=> strtotime($a['fecha']);
            });

            return $resultado;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.obtenerSolicitudesAsignadas: ' . $e->getMessage());
            return [];
        }
    }

    private function puedeGestionarSolicitud(string $origen, int $idSolicitud, ?string $cedulaAgente, bool $esAdmin): bool {
        if ($esAdmin) {
            return true;
        }
        if (!$this->conexionDisponible() || !$cedulaAgente) {
            return false;
        }
        try {
            if ($origen === 'poliza') {
                $sql = 'SELECT COUNT(*) FROM solicitud_poliza WHERE id_solicitud = :id AND cedula_agente_asignado = :agente';
            } else {
                $sql = 'SELECT COUNT(*) FROM solicitud_siniestro WHERE id_solicitud = :id AND cedula_agente_asignado = :agente';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
            $stmt->bindParam(':agente', $cedulaAgente, PDO::PARAM_STR);
            $stmt->execute();
            return (int)$stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.puedeGestionarSolicitud: ' . $e->getMessage());
            return false;
        }
    }

    public function actualizarEstadoSolicitud(string $origen, int $idSolicitud, string $estado, ?string $nota, ?string $cedulaAgente, bool $esAdmin): array {
        if (!$this->conexionDisponible()) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        $estado = strtoupper(trim($estado));
        $nota = trim((string)$nota) ?: null;

        $estadosValidosPoliza = ['EN_REVISION','CONTACTADO','EN_PROCESO','APROBADO','RECHAZADO','CANCELADO'];
        $estadosValidosSiniestro = ['EN_REVISION','CITA_PENDIENTE','EN_GESTION','ESCALADO','CERRADO','CANCELADO'];

        $validos = $origen === 'poliza' ? $estadosValidosPoliza : $estadosValidosSiniestro;
        if (!in_array($estado, $validos, true)) {
            return ['success' => false, 'message' => 'Estado no permitido.'];
        }

        if (!$this->puedeGestionarSolicitud($origen, $idSolicitud, $cedulaAgente, $esAdmin)) {
            return ['success' => false, 'message' => 'No tiene permisos para actualizar esta solicitud.'];
        }

        try {
            if ($origen === 'poliza') {
                $sql = 'UPDATE solicitud_poliza SET estado = :estado, nota_interna = :nota, fecha_actualizacion = NOW() WHERE id_solicitud = :id';
            } else {
                $sql = 'UPDATE solicitud_siniestro SET estado = :estado, nota_interna = :nota, fecha_actualizacion = NOW() WHERE id_solicitud = :id';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->bindParam(':nota', $nota, PDO::PARAM_STR | PDO::PARAM_NULL);
            $stmt->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
            $stmt->execute();
            return ['success' => true, 'message' => 'Estado actualizado correctamente.'];
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.actualizarEstadoSolicitud: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo actualizar el estado.'];
        }
    }

    public function asignarSolicitudAgente(string $origen, int $idSolicitud, string $cedulaAgente): array {
        if (!$this->conexionDisponible()) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }

        $origen = strtolower(trim($origen));
        if (!in_array($origen, ['poliza', 'siniestro'], true) || $idSolicitud <= 0 || $cedulaAgente === '') {
            return ['success' => false, 'message' => 'Parámetros inválidos para la asignación.'];
        }

        try {
            $sqlExisteAgente = 'SELECT COUNT(*) FROM agente WHERE cedula_agente = :cedula LIMIT 1';
            $stmtAgente = $this->db->prepare($sqlExisteAgente);
            $stmtAgente->bindParam(':cedula', $cedulaAgente, PDO::PARAM_STR);
            $stmtAgente->execute();
            if ((int)$stmtAgente->fetchColumn() === 0) {
                return ['success' => false, 'message' => 'El agente indicado no existe.'];
            }

            $modeloPermiso = new ModeloPermiso();
            $permisosAgente = $modeloPermiso->obtenerNombresPermisosDeAgente($cedulaAgente);
            $puedeGestionar = false;
            foreach ($permisosAgente as $permisoNombre) {
                if (strtolower((string)$permisoNombre) === 'solicitud_gestionar') {
                    $puedeGestionar = true;
                    break;
                }
            }
            if (!$puedeGestionar) {
                return ['success' => false, 'message' => 'El agente seleccionado no tiene permisos para gestionar solicitudes.'];
            }

            if ($origen === 'poliza') {
                $sql = 'UPDATE solicitud_poliza SET cedula_agente_asignado = :cedula, fecha_actualizacion = NOW() WHERE id_solicitud = :id';
            } else {
                $sql = 'UPDATE solicitud_siniestro SET cedula_agente_asignado = :cedula, fecha_actualizacion = NOW() WHERE id_solicitud = :id';
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedulaAgente, PDO::PARAM_STR);
            $stmt->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                $sqlVerificar = $origen === 'poliza'
                    ? 'SELECT cedula_agente_asignado FROM solicitud_poliza WHERE id_solicitud = :id LIMIT 1'
                    : 'SELECT cedula_agente_asignado FROM solicitud_siniestro WHERE id_solicitud = :id LIMIT 1';
                $stmtVerificar = $this->db->prepare($sqlVerificar);
                $stmtVerificar->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
                $stmtVerificar->execute();
                $actual = $stmtVerificar->fetchColumn();
                if ($actual !== false && (string)$actual === $cedulaAgente) {
                    return ['success' => true, 'message' => 'La solicitud ya estaba asignada a este agente.'];
                }
                return ['success' => false, 'message' => 'No fue posible actualizar la solicitud.'];
            }

            return ['success' => true, 'message' => 'Solicitud asignada correctamente.'];
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.asignarSolicitudAgente: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo asignar la solicitud.'];
        }
    }

    public function cancelarSolicitudCliente(string $origen, int $idSolicitud, int $idCliente): array {
        if (!$this->conexionDisponible()) {
            return ['success' => false, 'message' => 'Conexión a la base de datos no disponible.'];
        }
        try {
            if ($origen === 'poliza') {
                $sql = 'UPDATE solicitud_poliza SET estado = "CANCELADO" WHERE id_solicitud = :id AND id_cliente = :cliente';
            } else {
                $sql = 'UPDATE solicitud_siniestro ss
                        INNER JOIN poliza p ON ss.id_poliza = p.id_poliza
                        SET ss.estado = "CANCELADO"
                        WHERE ss.id_solicitud = :id AND p.id_cliente = :cliente';
            }
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $idSolicitud, PDO::PARAM_INT);
            $stmt->bindParam(':cliente', $idCliente, PDO::PARAM_INT);
            $stmt->execute();
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'No se pudo cancelar la solicitud.'];
            }
            return ['success' => true, 'message' => 'Solicitud cancelada.'];
        } catch (PDOException $e) {
            error_log('ModeloSolicitud.cancelarSolicitudCliente: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cancelar la solicitud.'];
        }
    }
}
