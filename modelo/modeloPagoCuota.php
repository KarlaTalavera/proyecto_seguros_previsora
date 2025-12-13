<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloPagoCuota {
    private $db;

    public function __construct() {
        try {
            $conexion = new Base_Datos();
            $this->db = $conexion->Conexion_Base_Datos();
        } catch (\Throwable $e) {
            error_log('Error inicializando DB en ModeloPagoCuota: ' . $e->getMessage());
            $this->db = null;
        }
    }

    public function obtenerCuotasDeCliente(string $cedulaCliente): array {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT
                    pc.id_cuota,
                    pc.id_poliza,
                    pc.numero_cuota,
                    pc.fecha_vencimiento,
                    pc.monto_programado,
                    pc.monto_pagado,
                    pc.fecha_pago,
                    pc.estado,
                    p.numero_poliza,
                    tp.nombre AS producto,
                    COALESCE(SUM(CASE WHEN rpc.estado = 'PENDIENTE' THEN rpc.monto_reportado ELSE 0 END), 0) AS monto_reportado_pendiente
                FROM poliza_cuota pc
                JOIN poliza p ON pc.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                LEFT JOIN reporte_pago_cuota rpc ON rpc.id_cuota = pc.id_cuota
                WHERE c.cedula_asegurado = :cedula
                GROUP BY pc.id_cuota
                ORDER BY pc.id_poliza ASC, pc.numero_cuota ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedulaCliente, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (!$rows) {
                return [];
            }

            $agrupado = [];
            foreach ($rows as $row) {
                $agrupado[(int)$row['id_poliza']][] = $row;
            }

            $resultado = [];

            foreach ($agrupado as $polizaId => $cuotasPoliza) {
                $bloqueoActivo = false;
                $ultimaCuotaBloqueo = null;

                foreach ($cuotasPoliza as $row) {
                    $montoProgramado = (float)$row['monto_programado'];
                    $montoPagado = isset($row['monto_pagado']) ? (float)$row['monto_pagado'] : 0.0;
                    $pendientePago = max($montoProgramado - $montoPagado, 0.0);
                    $montoReportadoPendiente = (float)$row['monto_reportado_pendiente'];
                    $montoReportable = max($pendientePago - $montoReportadoPendiente, 0.0);

                    $permiteReportar = !$bloqueoActivo && $montoReportable > 0.0001;
                    $motivoBloqueo = '';

                    if ($bloqueoActivo && $ultimaCuotaBloqueo !== null) {
                        $motivoBloqueo = 'Cuota #' . $ultimaCuotaBloqueo . ' pendiente';
                    } elseif (!$permiteReportar && $pendientePago <= 0.0001) {
                        $motivoBloqueo = 'Sin saldo pendiente';
                    } elseif (!$permiteReportar && $montoReportadoPendiente > 0.0001) {
                        $motivoBloqueo = 'Pago en revisión';
                    }

                    $resultado[] = [
                        'id_cuota' => (int)$row['id_cuota'],
                        'id_poliza' => $polizaId,
                        'numero_cuota' => (int)$row['numero_cuota'],
                        'fecha_vencimiento' => $row['fecha_vencimiento'],
                        'monto_programado' => $montoProgramado,
                        'monto_pagado' => round($montoPagado, 2),
                        'monto_pendiente' => round($pendientePago, 2),
                        'monto_reportado_pendiente' => round($montoReportadoPendiente, 2),
                        'monto_reportable' => round($montoReportable, 2),
                        'permite_reporte' => $permiteReportar,
                        'motivo_bloqueo' => $motivoBloqueo,
                        'estado' => $row['estado'],
                        'fecha_pago' => $row['fecha_pago'],
                        'numero_poliza' => $row['numero_poliza'],
                        'producto' => $row['producto'],
                    ];

                    if ($pendientePago > 0.0001 || $montoReportadoPendiente > 0.0001) {
                        $bloqueoActivo = true;
                        $ultimaCuotaBloqueo = (int)$row['numero_cuota'];
                    }

                    $estadoCuota = strtoupper((string)$row['estado']);

                    if ($pendientePago <= 0.0001 && $montoReportadoPendiente <= 0.0001 && in_array($estadoCuota, ['PAGADO', 'CONDONADO'], true)) {
                        $bloqueoActivo = false;
                        $ultimaCuotaBloqueo = null;
                    }
                }
            }

            return $resultado;
        } catch (PDOException $e) {
            error_log('Error obtenerCuotasDeCliente: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerReportesCliente(string $cedulaCliente): array {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT
                    rpc.id_reporte,
                    rpc.id_cuota,
                    rpc.id_poliza,
                    rpc.monto_reportado,
                    rpc.referencia_pago,
                    rpc.estado,
                    rpc.fecha_reporte,
                    rpc.fecha_revision,
                    rpc.ruta_comprobante,
                    rpc.nota_cliente,
                    rpc.motivo_rechazo,
                    rpc.revisado_por,
                    p.numero_poliza,
                    tp.nombre AS producto,
                    pc.numero_cuota
                FROM reporte_pago_cuota rpc
                JOIN poliza_cuota pc ON rpc.id_cuota = pc.id_cuota
                JOIN poliza p ON rpc.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                WHERE c.cedula_asegurado = :cedula
                ORDER BY rpc.fecha_reporte DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedulaCliente, PDO::PARAM_STR);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'formatearReporte'], $rows ?: []);
        } catch (PDOException $e) {
            error_log('Error obtenerReportesCliente: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerReportesPendientesPorRol(string $rol, string $cedula): array {
        return $this->obtenerReportesPorRol($rol, $cedula, 'PENDIENTE');
    }

    public function obtenerReportesPorRol(string $rol, string $cedula, string $estado = 'PENDIENTE'): array {
        if (!$this->db) {
            return [];
        }

        $sql = "SELECT
                    rpc.id_reporte,
                    rpc.id_cuota,
                    rpc.id_poliza,
                    rpc.monto_reportado,
                    rpc.referencia_pago,
                    rpc.estado,
                    rpc.fecha_reporte,
                    rpc.fecha_revision,
                    rpc.ruta_comprobante,
                    rpc.nota_cliente,
                    rpc.motivo_rechazo,
                    rpc.revisado_por,
                    p.numero_poliza,
                    tp.nombre AS producto,
                    pc.numero_cuota,
                    pc.monto_programado,
                    pc.monto_pagado,
                    pc.estado AS estado_cuota,
                    pc.fecha_vencimiento,
                    pc.fecha_pago,
                    c.cedula_asegurado,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                    u.email AS cliente_email,
                    p.cedula_agente
                FROM reporte_pago_cuota rpc
                JOIN poliza_cuota pc ON rpc.id_cuota = pc.id_cuota
                JOIN poliza p ON rpc.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                WHERE 1 = 1";

        if (strtoupper($estado) !== 'TODOS') {
            $sql .= " AND rpc.estado = :estado";
        }

        if ($rol === 'agente') {
            $sql .= " AND p.cedula_agente = :cedula";
        }

        $sql .= ' ORDER BY rpc.fecha_reporte DESC';

        try {
            $stmt = $this->db->prepare($sql);
            if (strtoupper($estado) !== 'TODOS') {
                $estadoParam = strtoupper($estado);
                $stmt->bindParam(':estado', $estadoParam, PDO::PARAM_STR);
            }
            if ($rol === 'agente') {
                $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return array_map([$this, 'formatearReporte'], $rows ?: []);
        } catch (PDOException $e) {
            error_log('Error obtenerReportesPorRol: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerMetricasGestion(string $rol, string $cedula): array {
        if (!$this->db) {
            return ['pendientes' => 0, 'aprobados_hoy' => 0, 'rechazados_hoy' => 0];
        }

        $sql = "SELECT
                    SUM(CASE WHEN rpc.estado = 'PENDIENTE' THEN 1 ELSE 0 END) AS pendientes,
                    SUM(CASE WHEN rpc.estado = 'APROBADO' AND DATE(rpc.fecha_revision) = CURDATE() THEN 1 ELSE 0 END) AS aprobados_hoy,
                    SUM(CASE WHEN rpc.estado = 'RECHAZADO' AND DATE(rpc.fecha_revision) = CURDATE() THEN 1 ELSE 0 END) AS rechazados_hoy
                FROM reporte_pago_cuota rpc
                JOIN poliza p ON rpc.id_poliza = p.id_poliza
                WHERE 1 = 1";

        try {
            if ($rol === 'agente') {
                $sql .= ' AND p.cedula_agente = :cedula';
            }

            $stmt = $this->db->prepare($sql);
            if ($rol === 'agente') {
                $stmt->bindParam(':cedula', $cedula, PDO::PARAM_STR);
            }
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'pendientes' => isset($data['pendientes']) ? (int)$data['pendientes'] : 0,
                'aprobados_hoy' => isset($data['aprobados_hoy']) ? (int)$data['aprobados_hoy'] : 0,
                'rechazados_hoy' => isset($data['rechazados_hoy']) ? (int)$data['rechazados_hoy'] : 0,
            ];
        } catch (PDOException $e) {
            error_log('Error obtenerMetricasGestion: ' . $e->getMessage());
            return ['pendientes' => 0, 'aprobados_hoy' => 0, 'rechazados_hoy' => 0];
        }
    }

    public function crearReportePago(int $idCuota, string $cedulaReporta, float $monto, string $referencia, string $rutaComprobante, ?string $nota = null): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Base de datos no disponible.'];
        }

        if ($monto <= 0) {
            return ['success' => false, 'message' => 'El monto debe ser mayor a cero.'];
        }

        try {
            $this->db->beginTransaction();

            $infoCuota = $this->obtenerCuotaParaValidacion($idCuota);
            if (!$infoCuota) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La cuota seleccionada no existe.'];
            }

            $numeroCuotaActual = isset($infoCuota['numero_cuota']) ? (int)$infoCuota['numero_cuota'] : null;
            if ($numeroCuotaActual !== null && $numeroCuotaActual > 1) {
                $cuotaBloqueante = $this->obtenerCuotaAnteriorPendiente((int)$infoCuota['id_poliza'], $numeroCuotaActual);
                if ($cuotaBloqueante) {
                    $mensaje = 'Cuota #' . $cuotaBloqueante['numero_cuota'] . ' pendiente.';
                    if ($cuotaBloqueante['monto_reportado_pendiente'] > 0.0001 && $cuotaBloqueante['monto_pendiente'] <= 0.0001) {
                        $mensaje = 'Cuota #' . $cuotaBloqueante['numero_cuota'] . ' en revisión.';
                    }
                    $this->db->rollBack();
                    return ['success' => false, 'message' => $mensaje];
                }
            }

            $montoProgramado = (float)$infoCuota['monto_programado'];
            $montoPagado = isset($infoCuota['monto_pagado']) ? (float)$infoCuota['monto_pagado'] : 0.0;
            $pendiente = max($montoProgramado - $montoPagado, 0.0);

            $montoReportadoPendiente = $this->obtenerSumaReportesPendientes($idCuota);
            $disponible = max($pendiente - $montoReportadoPendiente, 0.0);

            if ($monto > $disponible + 0.0001) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El monto ingresado supera el saldo pendiente de la cuota.'];
            }

            $sqlInsert = "INSERT INTO reporte_pago_cuota (id_cuota, id_poliza, reportado_por, monto_reportado, referencia_pago, ruta_comprobante, nota_cliente)
                          VALUES (:id_cuota, :id_poliza, :reportado_por, :monto, :referencia, :ruta, :nota)";
            $stmt = $this->db->prepare($sqlInsert);
            $stmt->bindParam(':id_cuota', $idCuota, PDO::PARAM_INT);
            $stmt->bindParam(':id_poliza', $infoCuota['id_poliza'], PDO::PARAM_INT);
            $stmt->bindParam(':reportado_por', $cedulaReporta, PDO::PARAM_STR);
            $stmt->bindParam(':monto', $monto);
            $stmt->bindParam(':referencia', $referencia);
            $stmt->bindParam(':ruta', $rutaComprobante);
            $stmt->bindParam(':nota', $nota);
            $stmt->execute();

            $this->db->commit();
            $idInsertado = (int)$this->db->lastInsertId();

            return ['success' => true, 'message' => 'Pago reportado correctamente. Queda pendiente de revisión.', 'id' => $idInsertado];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error crearReportePago: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo registrar el pago.'];
        }
    }

    public function obtenerReporteDetallado(int $idReporte): ?array {
        if (!$this->db) {
            return null;
        }

        $sql = "SELECT
                    rpc.*,
                    pc.numero_cuota,
                    pc.monto_programado,
                    pc.monto_pagado,
                    pc.estado AS estado_cuota,
                    pc.fecha_vencimiento,
                    pc.fecha_pago,
                    p.numero_poliza,
                    p.cedula_agente,
                    tp.nombre AS producto,
                    c.cedula_asegurado,
                    CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre,
                    u.email AS cliente_email
                FROM reporte_pago_cuota rpc
                JOIN poliza_cuota pc ON rpc.id_cuota = pc.id_cuota
                JOIN poliza p ON rpc.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                WHERE rpc.id_reporte = :id
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $idReporte, PDO::PARAM_INT);
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? $this->formatearReporte($data) : null;
        } catch (PDOException $e) {
            error_log('Error obtenerReporteDetallado: ' . $e->getMessage());
            return null;
        }
    }

    public function aprobarReporte(int $idReporte, string $cedulaRevisor): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Base de datos no disponible.'];
        }

        try {
            $this->db->beginTransaction();

            $detalle = $this->bloquearReporte($idReporte);
            if (!$detalle) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El reporte solicitado no existe.'];
            }

            if ($detalle['estado'] !== 'PENDIENTE') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El reporte ya fue procesado anteriormente.'];
            }

            $cuotaInfo = $this->bloquearCuota($detalle['id_cuota']);
            if (!$cuotaInfo) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'La cuota asociada no existe.'];
            }

            $montoProgramado = (float)$cuotaInfo['monto_programado'];
            $montoPagado = isset($cuotaInfo['monto_pagado']) ? (float)$cuotaInfo['monto_pagado'] : 0.0;
            $pendiente = max($montoProgramado - $montoPagado, 0.0);

            if ($detalle['monto_reportado'] > $pendiente + 0.0001) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El monto reportado excede el saldo pendiente actual de la cuota.'];
            }

            $nuevoPagado = $montoPagado + (float)$detalle['monto_reportado'];
            $nuevoPagado = min($nuevoPagado, $montoProgramado);
            $restante = max($montoProgramado - $nuevoPagado, 0.0);
            $estadoCuota = $restante <= 0.0001 ? 'PAGADO' : ($cuotaInfo['estado'] === 'ATRASADO' ? 'ATRASADO' : 'PENDIENTE');
            $fechaPago = $estadoCuota === 'PAGADO' ? date('Y-m-d') : $cuotaInfo['fecha_pago'];

            $sqlUpdateCuota = "UPDATE poliza_cuota
                               SET monto_pagado = :pagado, estado = :estado, fecha_pago = :fecha
                               WHERE id_cuota = :id";
            $stmtCuota = $this->db->prepare($sqlUpdateCuota);
            $stmtCuota->bindParam(':pagado', $nuevoPagado);
            $stmtCuota->bindParam(':estado', $estadoCuota);
            $stmtCuota->bindParam(':fecha', $fechaPago);
            $stmtCuota->bindParam(':id', $detalle['id_cuota'], PDO::PARAM_INT);
            $stmtCuota->execute();

            $sqlUpdateReporte = "UPDATE reporte_pago_cuota
                                 SET estado = 'APROBADO', fecha_revision = NOW(), revisado_por = :revisor, motivo_rechazo = NULL
                                 WHERE id_reporte = :id";
            $stmtReporte = $this->db->prepare($sqlUpdateReporte);
            $stmtReporte->bindParam(':revisor', $cedulaRevisor, PDO::PARAM_STR);
            $stmtReporte->bindParam(':id', $idReporte, PDO::PARAM_INT);
            $stmtReporte->execute();

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Pago aprobado correctamente.',
                'nuevo_estado_cuota' => $estadoCuota,
                'monto_pagado' => round($nuevoPagado, 2),
                'monto_pendiente' => round($restante, 2),
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error aprobarReporte: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo aprobar el reporte.'];
        }
    }

    public function rechazarReporte(int $idReporte, string $cedulaRevisor, string $motivo): array {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Base de datos no disponible.'];
        }

        if (trim($motivo) === '') {
            return ['success' => false, 'message' => 'Debe especificar el motivo de rechazo.'];
        }

        try {
            $this->db->beginTransaction();

            $detalle = $this->bloquearReporte($idReporte);
            if (!$detalle) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El reporte solicitado no existe.'];
            }

            if ($detalle['estado'] !== 'PENDIENTE') {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'El reporte ya fue procesado anteriormente.'];
            }

            $sqlUpdateReporte = "UPDATE reporte_pago_cuota
                                 SET estado = 'RECHAZADO', motivo_rechazo = :motivo, fecha_revision = NOW(), revisado_por = :revisor
                                 WHERE id_reporte = :id";
            $stmt = $this->db->prepare($sqlUpdateReporte);
            $stmt->bindParam(':motivo', $motivo, PDO::PARAM_STR);
            $stmt->bindParam(':revisor', $cedulaRevisor, PDO::PARAM_STR);
            $stmt->bindParam(':id', $idReporte, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Reporte rechazado correctamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error rechazarReporte: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo rechazar el reporte.'];
        }
    }

    public function verificarRelacionClienteCuota(int $idCuota, string $cedulaCliente): bool {
        if (!$this->db) {
            return false;
        }

        $sql = "SELECT 1
                FROM poliza_cuota pc
                JOIN poliza p ON pc.id_poliza = p.id_poliza
                JOIN cliente c ON p.id_cliente = c.id_cliente
                WHERE pc.id_cuota = :cuota AND c.cedula_asegurado = :cedula
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cuota', $idCuota, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $cedulaCliente, PDO::PARAM_STR);
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error verificarRelacionClienteCuota: ' . $e->getMessage());
            return false;
        }
    }

    public function verificarRelacionAgenteCuota(int $idCuota, string $cedulaAgente): bool {
        if (!$this->db) {
            return false;
        }

        $sql = "SELECT 1
                FROM poliza_cuota pc
                JOIN poliza p ON pc.id_poliza = p.id_poliza
                WHERE pc.id_cuota = :cuota AND p.cedula_agente = :cedula
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cuota', $idCuota, PDO::PARAM_INT);
            $stmt->bindParam(':cedula', $cedulaAgente, PDO::PARAM_STR);
            $stmt->execute();
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Error verificarRelacionAgenteCuota: ' . $e->getMessage());
            return false;
        }
    }

    private function obtenerCuotaParaValidacion(int $idCuota): ?array {
        $sql = "SELECT pc.id_cuota, pc.id_poliza, pc.numero_cuota, pc.monto_programado, pc.monto_pagado, pc.estado
                FROM poliza_cuota pc
                WHERE pc.id_cuota = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idCuota, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    private function obtenerCuotaAnteriorPendiente(int $idPoliza, int $numeroCuotaActual): ?array {
        $sql = "SELECT
                    pc.id_cuota,
                    pc.numero_cuota,
                    pc.monto_programado,
                    pc.monto_pagado,
                    pc.estado,
                    COALESCE(SUM(CASE WHEN rpc.estado = 'PENDIENTE' THEN rpc.monto_reportado ELSE 0 END), 0) AS monto_reportado_pendiente
                FROM poliza_cuota pc
                LEFT JOIN reporte_pago_cuota rpc ON pc.id_cuota = rpc.id_cuota AND rpc.estado = 'PENDIENTE'
                WHERE pc.id_poliza = :poliza AND pc.numero_cuota < :numero
                GROUP BY pc.id_cuota
                ORDER BY pc.numero_cuota DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':poliza', $idPoliza, PDO::PARAM_INT);
        $stmt->bindParam(':numero', $numeroCuotaActual, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$data) {
            return null;
        }

        $montoProgramado = (float)$data['monto_programado'];
        $montoPagado = isset($data['monto_pagado']) ? (float)$data['monto_pagado'] : 0.0;
        $montoPendiente = max($montoProgramado - $montoPagado, 0.0);
        $montoPendienteRevision = isset($data['monto_reportado_pendiente']) ? (float)$data['monto_reportado_pendiente'] : 0.0;
        $estado = strtoupper((string)$data['estado']);

        $estaSaldada = $estado === 'PAGADO' && $montoPendiente <= 0.0001 && $montoPendienteRevision <= 0.0001;
        $estaCondonada = $estado === 'CONDONADO' && $montoPendienteRevision <= 0.0001;

        if ($estaSaldada || $estaCondonada) {
            return null;
        }

        return [
            'numero_cuota' => (int)$data['numero_cuota'],
            'monto_pendiente' => round($montoPendiente, 2),
            'monto_reportado_pendiente' => round($montoPendienteRevision, 2),
            'estado' => $estado,
        ];
    }

    private function obtenerSumaReportesPendientes(int $idCuota): float {
        $sql = "SELECT COALESCE(SUM(monto_reportado), 0)
                FROM reporte_pago_cuota
                WHERE id_cuota = :cuota AND estado = 'PENDIENTE'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':cuota', $idCuota, PDO::PARAM_INT);
        $stmt->execute();
        $valor = (float)$stmt->fetchColumn();
        return round($valor, 2);
    }

    private function bloquearReporte(int $idReporte): ?array {
        $sql = "SELECT * FROM reporte_pago_cuota WHERE id_reporte = :id FOR UPDATE";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idReporte, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    private function bloquearCuota(int $idCuota): ?array {
        $sql = "SELECT * FROM poliza_cuota WHERE id_cuota = :id FOR UPDATE";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $idCuota, PDO::PARAM_INT);
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ?: null;
    }

    private function formatearReporte(array $row): array {
        return [
            'id_reporte' => isset($row['id_reporte']) ? (int)$row['id_reporte'] : null,
            'id_cuota' => isset($row['id_cuota']) ? (int)$row['id_cuota'] : null,
            'id_poliza' => isset($row['id_poliza']) ? (int)$row['id_poliza'] : null,
            'numero_cuota' => isset($row['numero_cuota']) ? (int)$row['numero_cuota'] : null,
            'numero_poliza' => $row['numero_poliza'] ?? null,
            'producto' => $row['producto'] ?? null,
            'monto_reportado' => isset($row['monto_reportado']) ? round((float)$row['monto_reportado'], 2) : null,
            'referencia_pago' => $row['referencia_pago'] ?? null,
            'estado' => $row['estado'] ?? null,
            'fecha_reporte' => $row['fecha_reporte'] ?? null,
            'fecha_revision' => $row['fecha_revision'] ?? null,
            'ruta_comprobante' => $row['ruta_comprobante'] ?? null,
            'nota_cliente' => $row['nota_cliente'] ?? null,
            'motivo_rechazo' => $row['motivo_rechazo'] ?? null,
            'revisado_por' => $row['revisado_por'] ?? null,
            'monto_programado' => isset($row['monto_programado']) ? round((float)$row['monto_programado'], 2) : null,
            'monto_pagado' => isset($row['monto_pagado']) ? round((float)$row['monto_pagado'], 2) : null,
            'estado_cuota' => $row['estado_cuota'] ?? null,
            'fecha_pago_cuota' => $row['fecha_pago'] ?? null,
            'fecha_vencimiento' => $row['fecha_vencimiento'] ?? null,
            'cliente_nombre' => $row['cliente_nombre'] ?? null,
            'cliente_email' => $row['cliente_email'] ?? null,
            'cedula_asegurado' => $row['cedula_asegurado'] ?? null,
            'cedula_agente' => $row['cedula_agente'] ?? null,
        ];
    }
}
