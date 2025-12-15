<?php
// Script para notificar a administradores en lotes métricos
require_once dirname(__DIR__) . '/config/conexion.php';
require_once dirname(__DIR__) . '/modelo/modeloNotificacion.php';

$estadoFile = __DIR__ . '/admin_metrics.json';
$base = new Base_Datos();
$db = $base->Conexion_Base_Datos();
$modeloNotificacion = new ModeloNotificacion();

// Cargar estado previo
$estado = [
    'polizas' => ['count' => 0, 'time' => null],
    'siniestros' => ['count' => 0, 'time' => null]
];
if (is_file($estadoFile)) {
    $content = @file_get_contents($estadoFile);
    $decoded = json_decode($content, true);
    if (is_array($decoded)) $estado = array_replace_recursive($estado, $decoded);
}

try {
    // Obtener conteos actuales
    $sqlPol = "SELECT COUNT(*) FROM poliza WHERE estado <> 'ELIMINADA'";
    $curPol = (int)$db->query($sqlPol)->fetchColumn();

    $sqlSin = "SELECT COUNT(*) FROM siniestro WHERE estado = 'CERRADO'";
    $curSin = (int)$db->query($sqlSin)->fetchColumn();

    // POLIZAS: lote de 5
    $lastPolCount = (int)($estado['polizas']['count'] ?? 0);
    $diffPol = $curPol - $lastPolCount;
    if ($diffPol >= 5) {
        $lotes = intdiv($diffPol, 5);
        $lastTime = $estado['polizas']['time'] ? new DateTime($estado['polizas']['time']) : null;
        $ahora = new DateTime();
        $elapsed = $lastTime ? $lastTime->diff($ahora) : null;
        $elapsedStr = $elapsed ? sprintf('%dd %dh %dm', $elapsed->d, $elapsed->h, $elapsed->i) : 'N/D';

        $titulo = "Lote de pólizas: +" . ($lotes * 5) . " pólizas";
        $mensaje = "Se registraron " . ($lotes * 5) . " pólizas desde el último lote. Tiempo transcurrido: $elapsedStr.";
        $enlace = "index.php?vista=polizasAdmin";

        $modeloNotificacion->notificarAdmins($titulo, $mensaje, 'info', $enlace);

        // Actualizar cuenta al múltiplo más cercano
        $estado['polizas']['count'] = $lastPolCount + $lotes * 5;
        $estado['polizas']['time'] = $ahora->format('Y-m-d H:i:s');
    }

    // SINIESTROS: lote de 10 (estado CERRADO)
    $lastSinCount = (int)($estado['siniestros']['count'] ?? 0);
    $diffSin = $curSin - $lastSinCount;
    if ($diffSin >= 10) {
        $lotesSin = intdiv($diffSin, 10);
        $lastTimeSin = $estado['siniestros']['time'] ? new DateTime($estado['siniestros']['time']) : null;
        $ahora = new DateTime();
        $elapsedSin = $lastTimeSin ? $lastTimeSin->diff($ahora) : null;
        $elapsedStrSin = $elapsedSin ? sprintf('%dd %dh %dm', $elapsedSin->d, $elapsedSin->h, $elapsedSin->i) : 'N/D';

        $titulo = "Lote de siniestros cerrados: +" . ($lotesSin * 10) . " siniestros";
        $mensaje = "Se registraron " . ($lotesSin * 10) . " siniestros cerrados desde el último lote. Tiempo transcurrido: $elapsedStrSin.";
        $enlace = "index.php?vista=siniestrosAdmin";

        $modeloNotificacion->notificarAdmins($titulo, $mensaje, 'info', $enlace);

        $estado['siniestros']['count'] = $lastSinCount + $lotesSin * 10;
        $estado['siniestros']['time'] = $ahora->format('Y-m-d H:i:s');
    }

    // Guardar estado
    @file_put_contents($estadoFile, json_encode($estado));

} catch (Exception $e) {
    error_log('Error admin_notificaciones_lotes: ' . $e->getMessage());
}

echo "OK\n";
