<?php
require_once dirname(__DIR__) . '/config/conexion.php';

class ModeloReporte {
    private $db;
    // Guarda el último mensaje de error del modelo para depuración
    private $lastError = '';

    public function __construct() {
        try {
            $base_datos = new Base_Datos();
            $this->db = $base_datos->Conexion_Base_Datos();
        } catch (Exception $e) {
            error_log('Error inicializando DB en ModeloReporte: ' . $e->getMessage());
        }
    }

    /**
     * R1 - Pólizas por vencer/renovar en los próximos $dias días.
     * Si $cedula_agente se provee, filtra por ese agente.
     */
    public function polizasPorVencer(int $dias = 30, string $cedula_agente = null) {
        if (!$this->db) return false;
        // Evitar pasar el parámetro dentro de INTERVAL (puede fallar en algunos drivers).
        // Calculamos la fecha límite en PHP y la pasamos como parámetro seguro.
        $fecha_limite = date('Y-m-d', strtotime("+{$dias} days"));
        $sql = "SELECT p.id_poliza, p.numero_poliza, p.cedula_agente, dp.fecha_fin, dp.monto_prima_total,
               u.nombre AS nombre_agente, u.apellido AS apellido_agente,
               t.nombre AS producto
        FROM poliza p
        JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
        LEFT JOIN usuario u ON p.cedula_agente = u.cedula
        LEFT JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
        WHERE dp.fecha_fin BETWEEN CURDATE() AND :fecha_limite";
        if ($cedula_agente) {
            $sql .= " AND p.cedula_agente = :cedula_agente";
        }
        $sql .= " ORDER BY dp.fecha_fin ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha_limite', $fecha_limite);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            // reset lastError on success
            $this->lastError = '';
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Guardar mensaje para que el controlador pueda devolverlo en modo debug
            $this->lastError = $e->getMessage();
            error_log('Error R1 polizasPorVencer: ' . $this->lastError);
            return false;
        }
    }

    /**
     * Retorna el último mensaje de error ocurrido en el modelo (solo para depuración).
     * @return string
     */
    public function getLastError() {
        return $this->lastError;
    }

    /**
     * Cartera pendiente: primas vencidas agrupadas por antigüedad.
     * Se mantiene para cálculos internos (por ejemplo, KPI de agentes).
     */
    public function carteraPendiente(string $cedula_agente = null) {
        if (!$this->db) return false;

        // Total pendientes (estado != 'PAGADO') que ya vencieron
        // Usar COALESCE para que SUM devuelva 0 en vez de NULL cuando no hay filas
        $sqlTotal = "SELECT COUNT(*) AS count_pending,
                COALESCE(SUM(GREATEST(pc.monto_programado - COALESCE(pc.monto_pagado, 0), 0)), 0) AS total_pending
             FROM poliza_cuota pc
             JOIN poliza p ON pc.id_poliza = p.id_poliza
             WHERE pc.estado IN ('PENDIENTE','ATRASADO') AND pc.fecha_vencimiento < CURDATE()";
        if ($cedula_agente) $sqlTotal .= " AND p.cedula_agente = :cedula_agente";

        // Buckets de antigüedad
        $sqlBuckets = <<<SQL
            SELECT
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), pc.fecha_vencimiento) BETWEEN 1 AND 30 THEN GREATEST(pc.monto_programado - COALESCE(pc.monto_pagado, 0), 0) ELSE 0 END), 0) AS b_0_30,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), pc.fecha_vencimiento) BETWEEN 31 AND 60 THEN GREATEST(pc.monto_programado - COALESCE(pc.monto_pagado, 0), 0) ELSE 0 END), 0) AS b_31_60,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), pc.fecha_vencimiento) BETWEEN 61 AND 90 THEN GREATEST(pc.monto_programado - COALESCE(pc.monto_pagado, 0), 0) ELSE 0 END), 0) AS b_61_90,
                COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), pc.fecha_vencimiento) > 90 THEN GREATEST(pc.monto_programado - COALESCE(pc.monto_pagado, 0), 0) ELSE 0 END), 0) AS b_90p
            FROM poliza_cuota pc
            JOIN poliza p ON pc.id_poliza = p.id_poliza
            WHERE pc.estado IN ('PENDIENTE','ATRASADO') AND pc.fecha_vencimiento < CURDATE()
        SQL;
        if ($cedula_agente) $sqlBuckets .= " AND p.cedula_agente = :cedula_agente";

        try {
            $stmt = $this->db->prepare($sqlTotal);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC);
            // Normalizar tipos y valores por seguridad
            $total = [
                'count_pending' => isset($total['count_pending']) ? (int)$total['count_pending'] : 0,
                'total_pending' => isset($total['total_pending']) ? (float)$total['total_pending'] : 0.0
            ];

            $stmt2 = $this->db->prepare($sqlBuckets);
            if ($cedula_agente) $stmt2->bindParam(':cedula_agente', $cedula_agente);
            $stmt2->execute();
            $buckets = $stmt2->fetch(PDO::FETCH_ASSOC);
            $buckets = [
                'b_0_30' => isset($buckets['b_0_30']) ? (float)$buckets['b_0_30'] : 0.0,
                'b_31_60' => isset($buckets['b_31_60']) ? (float)$buckets['b_31_60'] : 0.0,
                'b_61_90' => isset($buckets['b_61_90']) ? (float)$buckets['b_61_90'] : 0.0,
                'b_90p' => isset($buckets['b_90p']) ? (float)$buckets['b_90p'] : 0.0
            ];

            return ['total' => $total, 'buckets' => $buckets];
        } catch (PDOException $e) {
            error_log('Error R4 carteraPendiente: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Distribución de pólizas por estado (Activa, Pendiente, etc.).
     * Devuelve un arreglo de filas con estado y total de pólizas.
     */
    public function polizasPorEstado(string $cedula_agente = null) {
        if (!$this->db) return false;

        $sql = "SELECT p.estado, COUNT(*) AS total
                FROM poliza p";
        if ($cedula_agente) {
            $sql .= " WHERE p.cedula_agente = :cedula_agente";
        }
        $sql .= " GROUP BY p.estado ORDER BY total DESC";

        try {
            $stmt = $this->db->prepare($sql);
            if ($cedula_agente) {
                $stmt->bindParam(':cedula_agente', $cedula_agente);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($row) {
                return [
                    'estado' => $row['estado'] ?? 'SIN ESTADO',
                    'total' => isset($row['total']) ? (int)$row['total'] : 0
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('Error polizasPorEstado: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * R8 - Ranking de productividad por agente: número de pólizas y prima total en un periodo.
     * Si $cedula_agente se provee, devuelve sólo ese agente (útil para Agente rol).
     */
    public function rankingProductividad(int $months = 12, string $cedula_agente = null, int $limit = 10) {
        if (!$this->db) return false;
        $sql = "SELECT p.cedula_agente, u.nombre, u.apellido, COUNT(DISTINCT p.id_poliza) AS num_polizas,
               SUM(dp.monto_prima_total) AS suma_primas
                FROM poliza p
                JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza
                LEFT JOIN usuario u ON p.cedula_agente = u.cedula
                WHERE dp.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)";
        if ($cedula_agente) $sql .= " AND p.cedula_agente = :cedula_agente";
        $sql .= " GROUP BY p.cedula_agente ORDER BY num_polizas DESC, suma_primas DESC LIMIT :limit";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':months', $months, PDO::PARAM_INT);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error R8 rankingProductividad: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pólizas por ramo (categoría): devuelve conteo por categoría
     */
    public function polizasPorRamo(string $cedula_agente = null) {
        if (!$this->db) return false;
        $sql = "SELECT c.nombre AS categoria, COUNT(*) AS total
                FROM poliza p
                JOIN tipo_poliza t ON p.id_tipo_poliza = t.id_tipo_poliza
                JOIN categoria_poliza c ON t.id_categoria = c.id_categoria";
        if ($cedula_agente) $sql .= " WHERE p.cedula_agente = :cedula_agente";
        $sql .= " GROUP BY c.id_categoria ORDER BY total DESC";

        try {
            $stmt = $this->db->prepare($sql);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error polizasPorRamo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Tendencia de siniestros en los últimos $months meses (por mes)
     */
    public function tendenciaSiniestros(int $months = 12, string $cedula_agente = null) {
        if (!$this->db) return false;
        $sql = "SELECT DATE_FORMAT(fecha_reporte, '%Y-%m') AS ym, COUNT(*) AS total
                FROM siniestro s
                WHERE fecha_reporte >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)";
        if ($cedula_agente) $sql .= " AND s.cedula_agente_gestion = :cedula_agente";
        $sql .= " GROUP BY ym ORDER BY ym ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':months', $months, PDO::PARAM_INT);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Build full months list to include zero months
            $labels = [];
            $data = [];
            $start = new DateTime();
            $start->modify('-' . ($months-1) . ' months');
            $map = [];
            foreach ($rows as $r) {
                $map[$r['ym']] = (int)$r['total'];
            }
            $period = new DatePeriod($start, new DateInterval('P1M'), $months);
            foreach ($period as $dt) {
                $ym = $dt->format('Y-m');
                $labels[] = $dt->format('M Y');
                $data[] = isset($map[$ym]) ? $map[$ym] : 0;
            }

            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log('Error tendenciaSiniestros: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * KPIs resumen: porcentaje de pólizas con pagos pendientes, primas pagadas, agentes activos, siniestros abiertos.
     * Si $cedula_agente se provee, filtra las métricas por ese agente cuando aplica.
     */
    public function kpisResumen(string $cedula_agente = null) {
        if (!$this->db) return false;

        try {
            // Total de pólizas (para calcular el porcentaje con pagos pendientes)
            $sqlTotalPolizas = "SELECT COUNT(*) AS total_polizas FROM poliza p";
            if ($cedula_agente) $sqlTotalPolizas .= " WHERE p.cedula_agente = :cedula_agente";
            $stmtTotal = $this->db->prepare($sqlTotalPolizas);
            if ($cedula_agente) $stmtTotal->bindParam(':cedula_agente', $cedula_agente);
            $stmtTotal->execute();
            $rowTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC);
            $total_polizas = isset($rowTotal['total_polizas']) ? (int)$rowTotal['total_polizas'] : 0;

            // Pólizas que tienen al menos una cuota pendiente o atrasada
            $sqlPendientes = "SELECT COUNT(DISTINCT p.id_poliza) AS polizas_pendientes
                              FROM poliza p
                              JOIN poliza_cuota pc ON p.id_poliza = pc.id_poliza
                              WHERE pc.estado IN ('PENDIENTE','ATRASADO')";
            if ($cedula_agente) $sqlPendientes .= " AND p.cedula_agente = :cedula_agente";
            $stmtPend = $this->db->prepare($sqlPendientes);
            if ($cedula_agente) $stmtPend->bindParam(':cedula_agente', $cedula_agente);
            $stmtPend->execute();
            $rowPend = $stmtPend->fetch(PDO::FETCH_ASSOC);
            $polizas_pendientes = isset($rowPend['polizas_pendientes']) ? (int)$rowPend['polizas_pendientes'] : 0;

            $porcentaje_pendientes = 0.0;
            if ($total_polizas > 0) {
                $porcentaje_pendientes = round(($polizas_pendientes / $total_polizas) * 100, 2);
            }

            // Primas pagadas: suma de las cuotas marcadas como pagadas
            $sqlPag = "SELECT COALESCE(SUM(COALESCE(pc.monto_pagado, pc.monto_programado)),0) AS primas_pagadas
                        FROM poliza_cuota pc
                        JOIN poliza p ON pc.id_poliza = p.id_poliza
                        WHERE pc.estado = 'PAGADO'";
            if ($cedula_agente) $sqlPag .= " AND p.cedula_agente = :cedula_agente";
            $stmtPag = $this->db->prepare($sqlPag);
            if ($cedula_agente) $stmtPag->bindParam(':cedula_agente', $cedula_agente);
            $stmtPag->execute();
            $pag = $stmtPag->fetch(PDO::FETCH_ASSOC);
            $primas_pagadas = isset($pag['primas_pagadas']) ? (float)$pag['primas_pagadas'] : 0.0;

            // Agentes activos (no filtra por agente): count usuarios con id_rol = 2 y activo = 1
            $sqlAg = "SELECT COUNT(*) AS agentes_activos FROM usuario WHERE id_rol = 2 AND activo = 1";
            $stmtAg = $this->db->prepare($sqlAg);
            $stmtAg->execute();
            $ag = $stmtAg->fetch(PDO::FETCH_ASSOC);
            $agentes_activos = isset($ag['agentes_activos']) ? (int)$ag['agentes_activos'] : 0;

            // Siniestros abiertos: aquellos cuyo estado != 'CERRADO'
            $sqlOpen = "SELECT COUNT(*) AS siniestros_abiertos FROM siniestro WHERE estado != 'CERRADO'";
            if ($cedula_agente) $sqlOpen .= " AND cedula_agente_gestion = :cedula_agente";
            $stmtOpen = $this->db->prepare($sqlOpen);
            if ($cedula_agente) $stmtOpen->bindParam(':cedula_agente', $cedula_agente);
            $stmtOpen->execute();
            $op = $stmtOpen->fetch(PDO::FETCH_ASSOC);
            $sini_abiertos = isset($op['siniestros_abiertos']) ? (int)$op['siniestros_abiertos'] : 0;

            return [
                'polizas_total' => $total_polizas,
                'polizas_pendientes' => $polizas_pendientes,
                'polizas_pendientes_pct' => $porcentaje_pendientes,
                'primas_pagadas' => $primas_pagadas,
                'agentes_activos' => $agentes_activos,
                'siniestros_abiertos' => $sini_abiertos
            ];
        } catch (PDOException $e) {
            error_log('Error kpisResumen: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * KPIs específicos de un agente: número de pólizas, primas suscritas, cartera pendiente y nuevas en 12 meses
     */
    public function kpisAgente(string $cedula_agente) {
        if (!$this->db) return false;
        try {
            $sqlPol = "SELECT COUNT(DISTINCT p.id_poliza) AS polizas_count FROM poliza p WHERE p.cedula_agente = :cedula";
            $stmt = $this->db->prepare($sqlPol);
            $stmt->bindParam(':cedula', $cedula_agente);
            $stmt->execute();
            $pol = $stmt->fetch(PDO::FETCH_ASSOC);
            $polizas_count = isset($pol['polizas_count']) ? (int)$pol['polizas_count'] : 0;

            $sqlPrima = "SELECT COALESCE(SUM(dp.monto_prima_total),0) AS prima_suscrita FROM detalle_poliza dp JOIN poliza p ON dp.id_poliza = p.id_poliza WHERE p.cedula_agente = :cedula";
            $stmt2 = $this->db->prepare($sqlPrima);
            $stmt2->bindParam(':cedula', $cedula_agente);
            $stmt2->execute();
            $pr = $stmt2->fetch(PDO::FETCH_ASSOC);
            $prima_suscrita = isset($pr['prima_suscrita']) ? (float)$pr['prima_suscrita'] : 0.0;

            // Cartera pendiente para el agente (usar método existente)
            $cartera = $this->carteraPendiente($cedula_agente);
            $cartera_total = 0.0;
            if ($cartera && isset($cartera['total']) && isset($cartera['total']['total_pending'])) {
                $cartera_total = (float)$cartera['total']['total_pending'];
            }

            // Nuevas pólizas en los últimos 12 meses
            $sqlNew = "SELECT COUNT(DISTINCT p.id_poliza) AS nuevas_12m FROM poliza p JOIN detalle_poliza dp ON p.id_poliza = dp.id_poliza WHERE p.cedula_agente = :cedula AND dp.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)";
            $stmt3 = $this->db->prepare($sqlNew);
            $stmt3->bindParam(':cedula', $cedula_agente);
            $stmt3->execute();
            $nw = $stmt3->fetch(PDO::FETCH_ASSOC);
            $nuevas_12m = isset($nw['nuevas_12m']) ? (int)$nw['nuevas_12m'] : 0;

            return [
                'polizas_count' => $polizas_count,
                'prima_suscrita' => $prima_suscrita,
                'cartera_pendiente' => $cartera_total,
                'nuevas_12m' => $nuevas_12m
            ];
        } catch (PDOException $e) {
            error_log('Error kpisAgente: ' . $e->getMessage());
            return false;
        }
    }

    /**
    * Ventas por mes para un agente (suma de monto_prima_total por fecha_inicio) en los últimos $months meses
     */
    public function ventasPorMesAgente(int $months = 6, string $cedula_agente = null) {
        if (!$this->db) return false;
        $sql = "SELECT DATE_FORMAT(dp.fecha_inicio, '%Y-%m') AS ym, COALESCE(SUM(dp.monto_prima_total),0) AS total
                FROM detalle_poliza dp JOIN poliza p ON dp.id_poliza = p.id_poliza
                WHERE dp.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL :months MONTH)";
        if ($cedula_agente) $sql .= " AND p.cedula_agente = :cedula_agente";
        $sql .= " GROUP BY ym ORDER BY ym ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':months', $months, PDO::PARAM_INT);
            if ($cedula_agente) $stmt->bindParam(':cedula_agente', $cedula_agente);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Build full months list with zeros where no data
            $labels = [];
            $data = [];
            $start = new DateTime();
            $start->modify('-' . ($months-1) . ' months');
            $map = [];
            foreach ($rows as $r) $map[$r['ym']] = (float)$r['total'];
            $period = new DatePeriod($start, new DateInterval('P1M'), $months);
            foreach ($period as $dt) {
                $ym = $dt->format('Y-m');
                $labels[] = $dt->format('M Y');
                $data[] = isset($map[$ym]) ? $map[$ym] : 0;
            }
            return ['labels' => $labels, 'data' => $data];
        } catch (PDOException $e) {
            error_log('Error ventasPorMesAgente: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Pólizas por tipo de cliente (Natural vs Juridico) para un agente
     */
    public function polizasPorTipoClienteAgente(string $cedula_agente) {
        if (!$this->db) return false;
        // Heurística: cedula_asegurado starting with 'J' or 'G' => Juridico, else Natural
        $sql = "SELECT tipo_cliente, COUNT(*) AS total FROM (
            SELECT p.id_poliza, c.cedula_asegurado,
              CASE WHEN LEFT(c.cedula_asegurado,1) IN ('J','G') THEN 'Juridico' ELSE 'Natural' END AS tipo_cliente
            FROM poliza p JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE p.cedula_agente = :cedula
        ) t GROUP BY tipo_cliente";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':cedula', $cedula_agente);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error polizasPorTipoClienteAgente: ' . $e->getMessage());
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
