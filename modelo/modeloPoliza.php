<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloPoliza {
    private $db;

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log('Error inicializando DB en ModeloPoliza: ' . $e->getMessage());
        }
    }

    public function obtenerCategorias() {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query('SELECT id_categoria, nombre FROM categoria_poliza ORDER BY nombre');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerCategorias: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerCategoriaPorId(int $id_categoria) {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare('SELECT id_categoria, nombre FROM categoria_poliza WHERE id_categoria = :id LIMIT 1');
            $stmt->bindParam(':id', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ?: null;
        } catch (PDOException $e) {
            error_log('Error obtenerCategoriaPorId: ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerCategoriaIdPorTipo(int $id_tipo_poliza) {
        if (!$this->db) return null;
        try {
            $stmt = $this->db->prepare('SELECT id_categoria FROM tipo_poliza WHERE id_tipo_poliza = :id LIMIT 1');
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($resultado && isset($resultado['id_categoria'])) {
                return (int)$resultado['id_categoria'];
            }
            return null;
        } catch (PDOException $e) {
            error_log('Error obtenerCategoriaIdPorTipo: ' . $e->getMessage());
            return null;
        }
    }

    public function obtenerRamosPorCategoria(int $id_categoria) {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->prepare('SELECT id_tipo_poliza, nombre FROM tipo_poliza WHERE id_categoria = :id ORDER BY nombre');
            $stmt->bindParam(':id', $id_categoria, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerRamosPorCategoria: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerCoberturasPorRamo(int $id_tipo_poliza) {
        if (!$this->db) return [];
        try {
            $sql = 'SELECT c.id_cobertura, c.nombre
                    FROM tipo_poliza_cobertura tpc
                    JOIN cobertura c ON tpc.id_cobertura = c.id_cobertura
                    WHERE tpc.id_tipo_poliza = :id
                    ORDER BY c.nombre';
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_tipo_poliza, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerCoberturasPorRamo: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerAgentesActivos() {
        if (!$this->db) return [];
        try {
            $sql = "SELECT cedula, nombre, apellido FROM usuario WHERE id_rol = 2 AND activo = 1 ORDER BY nombre, apellido";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerAgentesActivos: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerClientes() {
        if (!$this->db) return [];
        try {
            $sql = 'SELECT c.id_cliente, u.cedula, u.nombre, u.apellido
                    FROM cliente c
                    JOIN usuario u ON c.cedula_asegurado = u.cedula
                    ORDER BY u.nombre, u.apellido';
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerClientes: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerTiposPoliza() {
        if (!$this->db) return [];
        try {
            $stmt = $this->db->query('SELECT id_tipo_poliza, nombre FROM tipo_poliza ORDER BY nombre');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerTiposPoliza: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerPolizas(?string $cedula_agente = null) {
        if (!$this->db) return [];
        try {
            $sql = "SELECT
                        p.id_poliza,
                        p.numero_poliza,
                        p.estado,
                        cat.nombre AS categoria,
                        tp.nombre AS ramo,
                        COALESCE(CONCAT(ag.nombre, ' ', ag.apellido), p.cedula_agente) AS agente,
                        COALESCE(CONCAT(cli.nombre, ' ', cli.apellido), 'Sin cliente') AS cliente,
                        dp.fecha_inicio,
                        dp.fecha_fin,
                        dp.monto_prima_total,
                        GROUP_CONCAT(DISTINCT cb.nombre ORDER BY cb.nombre SEPARATOR ', ') AS coberturas
                    FROM poliza p
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    JOIN categoria_poliza cat ON tp.id_categoria = cat.id_categoria
                    JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                    LEFT JOIN usuario ag ON p.cedula_agente = ag.cedula
                    LEFT JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    LEFT JOIN usuario cli ON cl.cedula_asegurado = cli.cedula
                    LEFT JOIN poliza_cobertura pc ON p.id_poliza = pc.id_poliza
                    LEFT JOIN cobertura cb ON pc.id_cobertura = cb.id_cobertura
                    WHERE 1 = 1 AND p.estado <> 'ELIMINADA'";

            if (!empty($cedula_agente)) {
                $sql .= " AND p.cedula_agente = :cedula";
            }

            $sql .= "
                    GROUP BY p.id_poliza
                    ORDER BY p.id_poliza DESC";

            $stmt = $this->db->prepare($sql);
            if (!empty($cedula_agente)) {
                $stmt->bindParam(':cedula', $cedula_agente);
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error obtenerPolizas: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerPolizasDeAgente(string $cedula_agente) {
        $polizas = $this->obtenerPolizas($cedula_agente);
        if (!$polizas) {
            return [];
        }
        return array_map(function ($poliza) {
            return [
                'id' => isset($poliza['id_poliza']) ? (int)$poliza['id_poliza'] : 0,
                'numero_poliza' => $poliza['numero_poliza'] ?? '',
                'producto' => $poliza['ramo'] ?? '',
                'cliente' => $poliza['cliente'] ?? '',
                'vencimiento' => $poliza['fecha_fin'] ?? null,
                'prima' => $poliza['monto_prima_total'] ?? null,
                'estado' => $poliza['estado'] ?? '',
            ];
        }, $polizas);
    }

    public function obtenerPolizaPorId(int $id_poliza) {
        if (!$this->db) {
            return null;
        }

        try {
            $sql = "SELECT
                        p.id_poliza,
                        p.numero_poliza,
                        p.estado,
                        p.id_tipo_poliza,
                        tp.id_categoria,
                        cat.nombre AS categoria,
                        tp.nombre AS ramo,
                        p.id_cliente,
                        p.cedula_agente,
                        COALESCE(CONCAT(ag.nombre, ' ', ag.apellido), p.cedula_agente) AS agente_nombre,
                        dp.fecha_inicio,
                        dp.fecha_fin,
                        dp.monto_prima_total,
                        dp.numero_cuotas,
                        dp.monto_cuota,
                        dp.fecha_primer_vencimiento,
                        dp.frecuencia_pago,
                        COALESCE(CONCAT(cli.nombre, ' ', cli.apellido), '') AS cliente_nombre,
                        cli.cedula AS cliente_cedula
                    FROM poliza p
                    JOIN tipo_poliza tp ON p.id_tipo_poliza = tp.id_tipo_poliza
                    JOIN categoria_poliza cat ON tp.id_categoria = cat.id_categoria
                    JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                    LEFT JOIN cliente cl ON p.id_cliente = cl.id_cliente
                    LEFT JOIN usuario cli ON cl.cedula_asegurado = cli.cedula
                    LEFT JOIN usuario ag ON p.cedula_agente = ag.cedula
                    WHERE p.id_poliza = :id
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmt->execute();
            $poliza = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$poliza) {
                return null;
            }

            $poliza['id_poliza'] = (int)$poliza['id_poliza'];
            $poliza['id_tipo_poliza'] = (int)$poliza['id_tipo_poliza'];
            $poliza['id_categoria'] = isset($poliza['id_categoria']) ? (int)$poliza['id_categoria'] : null;
            $poliza['id_cliente'] = isset($poliza['id_cliente']) ? (int)$poliza['id_cliente'] : null;
            $poliza['numero_cuotas'] = isset($poliza['numero_cuotas']) ? (int)$poliza['numero_cuotas'] : 0;
            $poliza['monto_prima_total'] = isset($poliza['monto_prima_total']) ? (float)$poliza['monto_prima_total'] : 0.0;
            $poliza['monto_cuota'] = isset($poliza['monto_cuota']) ? (float)$poliza['monto_cuota'] : 0.0;

            $sqlCob = 'SELECT c.id_cobertura, c.nombre
                        FROM poliza_cobertura pc
                        JOIN cobertura c ON pc.id_cobertura = c.id_cobertura
                        WHERE pc.id_poliza = :id
                        ORDER BY c.nombre';
            $stmtCob = $this->db->prepare($sqlCob);
            $stmtCob->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmtCob->execute();
            $coberturas = $stmtCob->fetchAll(PDO::FETCH_ASSOC);

            $poliza['coberturas'] = array_map(function ($item) {
                return (int)$item['id_cobertura'];
            }, $coberturas);
            $poliza['coberturas_detalle'] = array_map(function ($item) {
                return [
                    'id_cobertura' => (int)$item['id_cobertura'],
                    'nombre' => $item['nombre']
                ];
            }, $coberturas);

            return $poliza;
        } catch (PDOException $e) {
            error_log('Error obtenerPolizaPorId: ' . $e->getMessage());
            return null;
        }
    }

    public function crearPoliza(array $data, ?string $cedula_agente = null) {
        if ($cedula_agente !== null && empty($data['cedula_agente'])) {
            $data['cedula_agente'] = $cedula_agente;
        }
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexion no disponible'];
        }

        $formatoBasico = isset($data['numero_poliza'], $data['fecha_vencimiento'], $data['prima_anual']) && !isset($data['fecha_inicio']);
        return $formatoBasico ? $this->crearPolizaBasica($data) : $this->crearPolizaDetallada($data);
    }

    private function crearPolizaDetallada(array $data) {
        if (!$this->db) return ['success' => false, 'message' => 'Conexion no disponible'];

    $required = ['id_tipo_poliza', 'cedula_agente', 'id_cliente', 'fecha_inicio', 'fecha_fin', 'monto_prima_total', 'numero_cuotas', 'frecuencia_pago', 'fecha_primer_vencimiento'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['success' => false, 'message' => 'Falta el campo: ' . $field];
            }
        }

        $numero_cuotas = (int)$data['numero_cuotas'];
        if ($numero_cuotas <= 0) {
            return ['success' => false, 'message' => 'El numero de cuotas debe ser mayor a cero'];
        }

        $total = (float)$data['monto_prima_total'];
        if ($total <= 0) {
            return ['success' => false, 'message' => 'La prima total debe ser mayor a cero'];
        }

        $frecuencia = strtoupper((string)$data['frecuencia_pago']);
        $interval = $this->obtenerIntervaloFrecuencia($frecuencia);
        if (!$interval) {
            return ['success' => false, 'message' => 'Frecuencia de pago no valida'];
        }

        $cedulaAgente = $this->sanitizeCedula($data['cedula_agente'] ?? '');
        if (!$cedulaAgente) {
            return ['success' => false, 'message' => 'Cedula de agente no valida'];
        }

        $monto_cuota_base = round($total / $numero_cuotas, 2);
        $fecha_primer_vencimiento = new DateTime($data['fecha_primer_vencimiento']);
        $fechaPrimerVencimientoStr = $fecha_primer_vencimiento->format('Y-m-d');

        try {
            $this->db->beginTransaction();

            $numeroPoliza = $this->generarNumeroPoliza();
            $estadoInicial = 'ACTIVA';

            $sqlPoliza = 'INSERT INTO poliza (numero_poliza, estado, id_cliente, cedula_agente, id_tipo_poliza) VALUES (:numero, :estado, :cliente, :agente, :tipo)';
            $stmt = $this->db->prepare($sqlPoliza);
            $stmt->bindValue(':numero', $numeroPoliza);
            $stmt->bindValue(':estado', $estadoInicial);
            $stmt->bindParam(':cliente', $data['id_cliente'], PDO::PARAM_INT);
            $stmt->bindParam(':agente', $cedulaAgente);
            $stmt->bindParam(':tipo', $data['id_tipo_poliza'], PDO::PARAM_INT);
            $stmt->execute();
            $id_poliza = (int)$this->db->lastInsertId();

            $monto_cuota = $monto_cuota_base;
            $sqlDetalle = 'INSERT INTO detalle_poliza (id_poliza, fecha_inicio, fecha_fin, monto_prima_total, numero_cuotas, monto_cuota, fecha_primer_vencimiento, frecuencia_pago) VALUES (:id, :inicio, :fin, :total, :cuotas, :cuota, :primer, :frecuencia)';
            $stmtDetalle = $this->db->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmtDetalle->bindParam(':inicio', $data['fecha_inicio']);
            $stmtDetalle->bindParam(':fin', $data['fecha_fin']);
            $stmtDetalle->bindParam(':total', $total);
            $stmtDetalle->bindParam(':cuotas', $numero_cuotas, PDO::PARAM_INT);
            $stmtDetalle->bindParam(':cuota', $monto_cuota);
            $stmtDetalle->bindParam(':primer', $fechaPrimerVencimientoStr);
            $stmtDetalle->bindParam(':frecuencia', $frecuencia);
            $stmtDetalle->execute();
            $sqlCuota = 'INSERT INTO poliza_cuota (id_poliza, numero_cuota, fecha_vencimiento, monto_programado, fecha_pago, monto_pagado, estado) VALUES (:poliza, :numero, :vencimiento, :monto, NULL, NULL, "PENDIENTE")';
            $stmtCuota = $this->db->prepare($sqlCuota);
            $restante = $total;
            $fechaActual = clone $fecha_primer_vencimiento;
            for ($i = 1; $i <= $numero_cuotas; $i++) {
                $montoCuota = ($i === $numero_cuotas) ? round($restante, 2) : $monto_cuota_base;
                $restante -= $montoCuota;

                $fechaVencimiento = $fechaActual->format('Y-m-d');

                $stmtCuota->bindValue(':poliza', $id_poliza, PDO::PARAM_INT);
                $stmtCuota->bindValue(':numero', $i, PDO::PARAM_INT);
                $stmtCuota->bindValue(':vencimiento', $fechaVencimiento);
                $stmtCuota->bindValue(':monto', $montoCuota);
                $stmtCuota->execute();

                $fechaActual->add($interval);
            }

            if (!empty($data['coberturas']) && is_array($data['coberturas'])) {
                $sqlCob = 'INSERT INTO poliza_cobertura (id_poliza, id_cobertura) VALUES (:poliza, :cobertura)';
                $stmtCob = $this->db->prepare($sqlCob);
                foreach ($data['coberturas'] as $id_cobertura) {
                    $idCob = (int)$id_cobertura;
                    if ($idCob <= 0) continue;
                    $stmtCob->bindValue(':poliza', $id_poliza, PDO::PARAM_INT);
                    $stmtCob->bindValue(':cobertura', $idCob, PDO::PARAM_INT);
                    $stmtCob->execute();
                }
            }

            $this->db->commit();
            return ['success' => true, 'id_poliza' => $id_poliza, 'numero_poliza' => $numeroPoliza];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error crearPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo crear la poliza'];
        }
    }

    private function crearPolizaBasica(array $data) {
        $numeroPoliza = trim((string)($data['numero_poliza'] ?? ''));
        $estado = strtoupper($data['estado'] ?? 'ACTIVA');
        $idTipo = isset($data['id_tipo_poliza']) ? (int)$data['id_tipo_poliza'] : 0;
        $cedulaAgente = $this->sanitizeCedula($data['cedula_agente'] ?? '');
        $cedulaCliente = $data['cedula_cliente'] ?? '';
        $fechaFin = $data['fecha_vencimiento'] ?? null;
        $primaAnual = isset($data['prima_anual']) ? (float)$data['prima_anual'] : 0.0;

        if ($numeroPoliza === '' || $cedulaCliente === '' || $idTipo <= 0 || !$fechaFin) {
            return ['success' => false, 'message' => 'Datos incompletos para crear la póliza'];
        }

        $idCliente = $this->obtenerIdClientePorCedula($cedulaCliente);
        if (!$idCliente) {
            return ['success' => false, 'message' => 'Cliente no encontrado para la cédula indicada'];
        }

        if (!$cedulaAgente) {
            return ['success' => false, 'message' => 'Cédula de agente no válida'];
        }

        try {
            $this->db->beginTransaction();

            $sqlPoliza = "INSERT INTO poliza (numero_poliza, estado, id_cliente, cedula_agente, id_tipo_poliza)
                          VALUES (:numero_poliza, :estado, :id_cliente, :cedula_agente, :id_tipo_poliza)";
            $stmtPoliza = $this->db->prepare($sqlPoliza);
            $stmtPoliza->bindParam(':numero_poliza', $numeroPoliza);
            $stmtPoliza->bindParam(':estado', $estado);
            $stmtPoliza->bindParam(':id_cliente', $idCliente, PDO::PARAM_INT);
            $stmtPoliza->bindParam(':cedula_agente', $cedulaAgente);
            $stmtPoliza->bindParam(':id_tipo_poliza', $idTipo, PDO::PARAM_INT);
            $stmtPoliza->execute();

            $id_poliza = $this->db->lastInsertId();
            if (!$id_poliza) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'No se pudo obtener el ID de la nueva póliza'];
            }

            $sqlDetalle = "INSERT INTO detalle_poliza (id_poliza, fecha_inicio, fecha_fin, monto_prima)
                           VALUES (:id_poliza, CURDATE(), :fecha_fin, :monto_prima)";
            $stmtDetalle = $this->db->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmtDetalle->bindParam(':fecha_fin', $fechaFin);
            $stmtDetalle->bindParam(':monto_prima', $primaAnual);
            $stmtDetalle->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Póliza creada exitosamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error crearPolizaBasica: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la póliza'];
        }
    }

    public function actualizarPoliza($arg1, $arg2 = null) {
        if (is_array($arg1) && is_int($arg2)) {
            return $this->actualizarPolizaBasica($arg1, $arg2);
        }
        if (is_int($arg1) && is_array($arg2)) {
            $data = $arg2;
            $esBasico = isset($data['numero_poliza']) && !isset($data['monto_prima_total']);
            return $esBasico ? $this->actualizarPolizaBasica($data, $arg1) : $this->actualizarPolizaDetallada($arg1, $data);
        }
        return ['success' => false, 'message' => 'Datos invalidos para actualizar la póliza'];
    }

    private function actualizarPolizaDetallada(int $id_poliza, array $data) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexion no disponible'];
        }

        $estadosPermitidos = ['ACTIVA', 'RENOVAR', 'CANCELADA'];
        $estado = isset($data['estado']) ? strtoupper(trim((string)$data['estado'])) : 'ACTIVA';
        if (!in_array($estado, $estadosPermitidos, true)) {
            $estado = 'ACTIVA';
        }

        $required = ['id_tipo_poliza', 'cedula_agente', 'id_cliente', 'fecha_inicio', 'fecha_fin', 'monto_prima_total', 'numero_cuotas', 'frecuencia_pago', 'fecha_primer_vencimiento'];
        foreach ($required as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return ['success' => false, 'message' => 'Falta el campo: ' . $field];
            }
        }

        $numero_cuotas = (int)$data['numero_cuotas'];
        if ($numero_cuotas <= 0) {
            return ['success' => false, 'message' => 'El numero de cuotas debe ser mayor a cero'];
        }

        $total = (float)$data['monto_prima_total'];
        if ($total <= 0) {
            return ['success' => false, 'message' => 'La prima total debe ser mayor a cero'];
        }

        $frecuencia = strtoupper((string)$data['frecuencia_pago']);
        $interval = $this->obtenerIntervaloFrecuencia($frecuencia);
        if (!$interval) {
            return ['success' => false, 'message' => 'Frecuencia de pago no valida'];
        }

        $monto_cuota_base = round($total / $numero_cuotas, 2);
        $fecha_primer_vencimiento = new DateTime($data['fecha_primer_vencimiento']);
        $fechaPrimerVencimientoStr = $fecha_primer_vencimiento->format('Y-m-d');
        $cedulaAgente = $this->sanitizeCedula($data['cedula_agente'] ?? '');
        if (!$cedulaAgente) {
            return ['success' => false, 'message' => 'Cedula de agente no valida'];
        }

        try {
            $this->db->beginTransaction();

            $sqlPoliza = 'UPDATE poliza SET id_tipo_poliza = :tipo, id_cliente = :cliente, cedula_agente = :agente, estado = :estado WHERE id_poliza = :id';
            $stmtPoliza = $this->db->prepare($sqlPoliza);
            $stmtPoliza->bindParam(':tipo', $data['id_tipo_poliza'], PDO::PARAM_INT);
            $stmtPoliza->bindParam(':cliente', $data['id_cliente'], PDO::PARAM_INT);
            $stmtPoliza->bindParam(':agente', $cedulaAgente);
            $stmtPoliza->bindParam(':estado', $estado);
            $stmtPoliza->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmtPoliza->execute();

            $sqlDetalle = 'UPDATE detalle_poliza SET fecha_inicio = :inicio, fecha_fin = :fin, monto_prima_total = :total, numero_cuotas = :cuotas, monto_cuota = :cuota, fecha_primer_vencimiento = :primer, frecuencia_pago = :frecuencia WHERE id_poliza = :id';
            $stmtDetalle = $this->db->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':inicio', $data['fecha_inicio']);
            $stmtDetalle->bindParam(':fin', $data['fecha_fin']);
            $stmtDetalle->bindParam(':total', $total);
            $stmtDetalle->bindParam(':cuotas', $numero_cuotas, PDO::PARAM_INT);
            $stmtDetalle->bindParam(':cuota', $monto_cuota_base);
            $stmtDetalle->bindParam(':primer', $fechaPrimerVencimientoStr);
            $stmtDetalle->bindParam(':frecuencia', $frecuencia);
            $stmtDetalle->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmtDetalle->execute();
            $stmtDeleteCuotas = $this->db->prepare('DELETE FROM poliza_cuota WHERE id_poliza = :poliza');
            $stmtDeleteCuotas->bindParam(':poliza', $id_poliza, PDO::PARAM_INT);
            $stmtDeleteCuotas->execute();

            $sqlCuota = 'INSERT INTO poliza_cuota (id_poliza, numero_cuota, fecha_vencimiento, monto_programado, fecha_pago, monto_pagado, estado) VALUES (:poliza, :numero, :vencimiento, :monto, NULL, NULL, "PENDIENTE")';
            $stmtCuota = $this->db->prepare($sqlCuota);
            $restante = $total;
            $fechaActual = clone $fecha_primer_vencimiento;
            for ($i = 1; $i <= $numero_cuotas; $i++) {
                $montoCuota = ($i === $numero_cuotas) ? round($restante, 2) : $monto_cuota_base;
                $restante -= $montoCuota;

                $stmtCuota->bindValue(':poliza', $id_poliza, PDO::PARAM_INT);
                $stmtCuota->bindValue(':numero', $i, PDO::PARAM_INT);
                $stmtCuota->bindValue(':vencimiento', $fechaActual->format('Y-m-d'));
                $stmtCuota->bindValue(':monto', $montoCuota);
                $stmtCuota->execute();

                $fechaActual->add($interval);
            }

            $stmtDeleteCob = $this->db->prepare('DELETE FROM poliza_cobertura WHERE id_poliza = :poliza');
            $stmtDeleteCob->bindParam(':poliza', $id_poliza, PDO::PARAM_INT);
            $stmtDeleteCob->execute();

            if (!empty($data['coberturas']) && is_array($data['coberturas'])) {
                $sqlCob = 'INSERT INTO poliza_cobertura (id_poliza, id_cobertura) VALUES (:poliza, :cobertura)';
                $stmtCob = $this->db->prepare($sqlCob);
                foreach ($data['coberturas'] as $id_cobertura) {
                    $idCob = (int)$id_cobertura;
                    if ($idCob <= 0) {
                        continue;
                    }
                    $stmtCob->bindValue(':poliza', $id_poliza, PDO::PARAM_INT);
                    $stmtCob->bindValue(':cobertura', $idCob, PDO::PARAM_INT);
                    $stmtCob->execute();
                }
            }

            $this->db->commit();

            $numero = $this->obtenerNumeroPoliza($id_poliza);
            return ['success' => true, 'message' => 'Póliza actualizada correctamente', 'numero_poliza' => $numero];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error actualizarPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo actualizar la poliza'];
        }
    }

    private function actualizarPolizaBasica(array $data, int $id_poliza) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexion no disponible'];
        }

        $numeroPoliza = trim((string)($data['numero_poliza'] ?? ''));
        $estado = strtoupper($data['estado'] ?? 'ACTIVA');
        $fechaFin = $data['fecha_vencimiento'] ?? null;
        $primaAnual = isset($data['prima_anual']) ? (float)$data['prima_anual'] : 0.0;

        if ($numeroPoliza === '' || !$fechaFin) {
            return ['success' => false, 'message' => 'Datos incompletos para actualizar la póliza'];
        }

        try {
            $this->db->beginTransaction();

            $sqlPoliza = "UPDATE poliza SET numero_poliza = :numero_poliza, estado = :estado WHERE id_poliza = :id_poliza";
            $stmtPoliza = $this->db->prepare($sqlPoliza);
            $stmtPoliza->bindParam(':numero_poliza', $numeroPoliza);
            $stmtPoliza->bindParam(':estado', $estado);
            $stmtPoliza->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmtPoliza->execute();

            $sqlDetalle = "UPDATE detalle_poliza SET fecha_fin = :fecha_fin, monto_prima = :monto_prima WHERE id_poliza = :id_poliza";
            $stmtDetalle = $this->db->prepare($sqlDetalle);
            $stmtDetalle->bindParam(':fecha_fin', $fechaFin);
            $stmtDetalle->bindParam(':monto_prima', $primaAnual);
            $stmtDetalle->bindParam(':id_poliza', $id_poliza, PDO::PARAM_INT);
            $stmtDetalle->execute();

            $this->db->commit();
            return ['success' => true, 'message' => 'Póliza actualizada exitosamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log('Error actualizarPolizaBasica: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error de base de datos al guardar los cambios.'];
        }
    }

    private function sanitizeCedula(?string $cedula): ?string {
        if ($cedula === null) {
            return null;
        }
        $cedula = strtoupper(trim($cedula));
        $cedula = str_replace(['-', '.', ' '], '', $cedula);
        return $cedula !== '' ? $cedula : null;
    }

    private function obtenerIdClientePorCedula(string $cedula_cliente): ?int {
        if (!$this->db) {
            return null;
        }
        $cedula_busqueda = $this->sanitizeCedula($cedula_cliente);
        if (!$cedula_busqueda) {
            return null;
        }

        $sql = "SELECT c.id_cliente
                FROM cliente c
                JOIN usuario u ON c.cedula_asegurado = u.cedula
                WHERE TRIM(u.cedula) = :cedula";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula_busqueda);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            return $resultado ? (int)$resultado['id_cliente'] : null;
        } catch (PDOException $e) {
            error_log('Error obtenerIdClientePorCedula: ' . $e->getMessage());
            return null;
        }
    }

    private function obtenerNumeroPoliza(int $id_poliza): ?string {
        try {
            $stmt = $this->db->prepare('SELECT numero_poliza FROM poliza WHERE id_poliza = :id');
            $stmt->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmt->execute();
            $numero = $stmt->fetchColumn();
            return $numero !== false ? (string)$numero : null;
        } catch (PDOException $e) {
            error_log('Error obtenerNumeroPoliza: ' . $e->getMessage());
            return null;
        }
    }

    private function obtenerIntervaloFrecuencia(string $frecuencia) {
        switch ($frecuencia) {
            case 'MENSUAL':
                return new DateInterval('P1M');
            case 'TRIMESTRAL':
                return new DateInterval('P3M');
            case 'SEMESTRAL':
                return new DateInterval('P6M');
            case 'ANUAL':
                return new DateInterval('P1Y');
            default:
                return null;
        }
    }

    private function generarNumeroPoliza(): string {
        // Bloquea la tabla de pólizas mientras se obtiene el máximo actual para evitar colisiones concurrentes.
        $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(numero_poliza, '-', -1) AS UNSIGNED)) AS max_num FROM poliza FOR UPDATE";
        $stmt = $this->db->query($sql);
        $maxNumero = $stmt ? (int)$stmt->fetchColumn() : 0;
        $siguiente = ($maxNumero > 0) ? $maxNumero + 1 : 1;
        return 'POL-' . $siguiente;
    }

    public function obtenerProximoNumeroPoliza(): ?string {
        if (!$this->db) {
            return null;
        }
        try {
            $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(numero_poliza, '-', -1) AS UNSIGNED)) AS max_num FROM poliza";
            $stmt = $this->db->query($sql);
            $maxNumero = $stmt ? (int)$stmt->fetchColumn() : 0;
            $siguiente = ($maxNumero > 0) ? $maxNumero + 1 : 1;
            return 'POL-' . $siguiente;
        } catch (PDOException $e) {
            error_log('Error obtenerProximoNumeroPoliza: ' . $e->getMessage());
            return null;
        }
    }

    public function eliminarPoliza(int $id_poliza) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Conexion no disponible'];
        }

        try {
            $sql = 'UPDATE poliza SET estado = :estado WHERE id_poliza = :id';
            $stmt = $this->db->prepare($sql);
            $estado = 'ELIMINADA';
            $stmt->bindParam(':estado', $estado);
            $stmt->bindParam(':id', $id_poliza, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Póliza no encontrada'];
            }

            return ['success' => true, 'message' => 'Póliza eliminada correctamente'];
        } catch (PDOException $e) {
            error_log('Error eliminarPoliza: ' . $e->getMessage());
            return ['success' => false, 'message' => 'No se pudo eliminar la póliza'];
        }
    }
}
