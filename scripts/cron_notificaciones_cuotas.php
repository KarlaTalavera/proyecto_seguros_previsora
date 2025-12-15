<?php
// Script para enviar recordatorios de pago a clientes (ejecutar diario via cron)
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';

$base = new Base_Datos();
$db = $base->Conexion_Base_Datos();
$modeloNotificacion = new ModeloNotificacion();

try {
    $sql = "SELECT pc.id_cuota, pc.id_poliza, pc.fecha_vencimiento, pc.monto_programado, p.id_cliente, c.cedula_asegurado
            FROM poliza_cuota pc
            JOIN poliza p ON pc.id_poliza = p.id_poliza
            JOIN cliente c ON p.id_cliente = c.id_cliente
            WHERE (pc.estado IS NULL OR pc.estado <> 'PAGADO')
              AND DATE(pc.fecha_vencimiento) BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $cuotas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hoy = new DateTime();
    foreach ($cuotas as $cuota) {
        $fechaV = new DateTime($cuota['fecha_vencimiento']);
        $interval = (int)$hoy->diff($fechaV)->format('%a');
        if ($interval >=1 && $interval <=5) {
            // Evitar notificar si ya existe notificación para la misma cuota hoy
            $enlace = "index.php?vista=detalleCuota&id=" . $cuota['id_cuota'];
            $sqlCheck = "SELECT COUNT(*) FROM notificacion WHERE cedula_destino = ? AND enlace = ? AND DATE(fecha_creacion) = CURDATE()";
            $stmtCheck = $db->prepare($sqlCheck);
            $stmtCheck->execute([$cuota['cedula_asegurado'], $enlace]);
            $existeHoy = (int)$stmtCheck->fetchColumn() > 0;

            if ($existeHoy) continue;

            $titulo = "Recordatorio de pago: cuota en $interval días";
            $mensaje = "Tienes una cuota de " . number_format((float)$cuota['monto_programado'],2) . " que vence en $interval día" . ($interval>1? 's':'') . ". Por favor realiza el pago a tiempo.";

            $modeloNotificacion->crearNotificacion($cuota['cedula_asegurado'], $titulo, $mensaje, 'warning', $enlace);
        }
    }
} catch (Exception $e) {
    error_log('Error cron_notificaciones_cuotas: ' . $e->getMessage());
}

echo "OK\n";
