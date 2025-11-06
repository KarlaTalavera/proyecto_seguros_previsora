<?php
// Script de ayuda para inspeccionar y corregir cedula_agente_gestion en la tabla `siniestro`.
// Uso: editar $apply = false para hacer un "dry run" (solo mostrar) y $agentCedula para la cédula del agente.

require_once dirname(__DIR__) . '/config/conexion.php';

$apply = false; // poner true para aplicar la actualización
$agentCedula = 'V12345678'; // cédula de Santiago Rodriguez (ajustar si es otra)

$db = Conexion::conectar();
if (!$db) {
    echo "No se pudo conectar a la base de datos\n";
    exit(1);
}

echo "=== Inspección de cedula_agente_gestion (dry run) ===\n";

$distinct = $db->query("SELECT DISTINCT cedula_agente_gestion FROM siniestro ORDER BY cedula_agente_gestion");
$vals = $distinct->fetchAll(PDO::FETCH_COLUMN);
echo "Valores distintos encontrados (" . count($vals) . "):\n";
foreach ($vals as $v) {
    echo " - " . ($v === null ? '[NULL]' : $v) . "\n";
}

// Mostrar algunas filas problemáticas (no pertenecen al agente)
echo "\nFilas con cedula_agente_gestion diferente de $agentCedula:\n";
$stmt = $db->prepare("SELECT id_siniestro, cedula_agente_gestion, fecha_siniestro, descripcion FROM siniestro WHERE cedula_agente_gestion IS NOT NULL AND cedula_agente_gestion <> :agent LIMIT 200");
$stmt->bindParam(':agent', $agentCedula);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$rows) {
    echo "No se encontraron filas fuera del agente especificado.\n";
} else {
    foreach ($rows as $r) {
        printf("%d | %s | %s | %s\n", $r['id_siniestro'], $r['cedula_agente_gestion'], $r['fecha_siniestro'], substr($r['descripcion'] ?? '', 0, 80));
    }
}

// Mostrar filas donde cedula_agente_gestion coincide con cedula de cliente (probable causa)
echo "\nFilas donde cedula_agente_gestion es cedula de cliente:\n";
$sql = "SELECT s.id_siniestro, s.cedula_agente_gestion, c.nombre, c.apellido
        FROM siniestro s JOIN cliente c ON s.cedula_agente_gestion = c.cedula_asegurado
        WHERE s.cedula_agente_gestion <> :agent LIMIT 200";
$stmt2 = $db->prepare($sql);
$stmt2->bindParam(':agent', $agentCedula);
$stmt2->execute();
$bad = $stmt2->fetchAll(PDO::FETCH_ASSOC);
if (!$bad) {
    echo "No se encontraron siniestros apuntando a cedulas de cliente.\n";
} else {
    foreach ($bad as $b) {
        printf("%d | %s | %s %s\n", $b['id_siniestro'], $b['cedula_agente_gestion'], $b['nombre'], $b['apellido']);
    }
}

if ($apply) {
    echo "\nAplicando corrección: actualizando cedula_agente_gestion -> $agentCedula en coincidencias con clientes...\n";
    try {
        // Crear respaldo rápido (tabla) antes de modificar
        $db->exec("CREATE TABLE IF NOT EXISTS siniestro_backup AS SELECT * FROM siniestro");
        $update = $db->prepare("UPDATE siniestro s JOIN cliente c ON s.cedula_agente_gestion = c.cedula_asegurado SET s.cedula_agente_gestion = :agent WHERE s.cedula_agente_gestion <> :agent");
        $update->bindParam(':agent', $agentCedula);
        $n = $update->execute();
        echo "Actualización ejecutada. Compruebe los resultados en la base de datos.\n";
    } catch (PDOException $e) {
        echo "Error ejecutando actualización: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nDry run completado. Para aplicar la corrección: edite este archivo y ponga \$apply = true, luego ejecute desde CLI o navegador.\n";
}

echo "\nSugerencias de verificación:\n";
echo " - SELECT COUNT(*) FROM siniestro WHERE cedula_agente_gestion = '$agentCedula';\n";
echo " - SELECT COUNT(*) FROM siniestro WHERE cedula_agente_gestion IS NULL;\n";
echo " - Si necesita revertir: TRUNCATE siniestro; INSERT INTO siniestro SELECT * FROM siniestro_backup;\n";

?>
