<?php
/**
 * Script de mantenimiento para corregir filas con id_solicitud = 0
 * y convertir las columnas en PRIMARY KEY AUTO_INCREMENT.
 * Ejecútalo desde la raíz del proyecto:
 * php scripts/fix_solicitud_ids.php
 */
require_once __DIR__ . '/../config/conexion.php';

function salida($msg) {
    echo $msg . PHP_EOL;
}

try {
    $base = new Base_Datos();
    $db = $base->Conexion_Base_Datos();
    if (!($db instanceof PDO)) {
        salida('No se pudo obtener la conexión PDO.');
        exit(1);
    }
} catch (Exception $e) {
    salida('Error conectando a la base de datos: ' . $e->getMessage());
    exit(1);
}

function fixTable(PDO $db, string $table) {
    salida("Procesando tabla: $table");
    try {
        // intentamos iniciar una transacción; algunas operaciones (ALTER TABLE) pueden auto-commit
        if (!$db->inTransaction()) {
            $db->beginTransaction();
        }

        // Buscar filas con id_solicitud = 0
        $stmt0 = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE id_solicitud = 0");
        $stmt0->execute();
        $count0 = (int)$stmt0->fetchColumn();
        salida("Filas con id_solicitud=0: $count0");

        if ($count0 > 0) {
            // Obtener max id actual
            $stmtMax = $db->prepare("SELECT COALESCE(MAX(id_solicitud), 0) FROM `$table`");
            $stmtMax->execute();
            $max = (int)$stmtMax->fetchColumn();

            // Actualizar filas 0 asignando ids nuevos incrementales
            $stmtSelect = $db->prepare("SELECT ROW_NUMBER() OVER (ORDER BY fecha_creacion, id_solicitud) AS rn, id_solicitud FROM `$table` WHERE id_solicitud = 0");
            // ROW_NUMBER() requiere MySQL 8+. Si no está disponible, haremos un update simple asignando max+1.
            $useRowNumber = true;
            try {
                $stmtSelect->execute();
                $rows = $stmtSelect->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                // Fallback: asignar max+1 a todas las filas con id 0 (no ideal but workable)
                $useRowNumber = false;
                $rows = [];
            }

            if ($useRowNumber && count($rows) > 0) {
                foreach ($rows as $r) {
                    $max++;
                    $update = $db->prepare("UPDATE `$table` SET id_solicitud = :newid WHERE id_solicitud = 0 LIMIT 1");
                    $update->bindParam(':newid', $max, PDO::PARAM_INT);
                    $update->execute();
                    salida("Asignado nuevo id $max a una fila con id 0");
                }
            } else {
                // Simple update: set the first found 0 -> max+1, then next -> max+2, etc.
                $stmtCount0 = $db->prepare("SELECT COUNT(*) FROM `$table` WHERE id_solicitud = 0");
                $stmtCount0->execute();
                $toFix = (int)$stmtCount0->fetchColumn();
                for ($i = 0; $i < $toFix; $i++) {
                    $max++;
                    $update = $db->prepare("UPDATE `$table` SET id_solicitud = :newid WHERE id_solicitud = 0 LIMIT 1");
                    $update->bindParam(':newid', $max, PDO::PARAM_INT);
                    $update->execute();
                    salida("Asignado nuevo id $max a una fila con id 0 (fallback)");
                }
            }
        }

        // Verificar si ya existe PK y AUTO_INCREMENT
        $stmtInfo = $db->prepare("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
        $stmtInfo->execute();
        $hasPK = (bool)$stmtInfo->fetch(PDO::FETCH_ASSOC);

        if (!$hasPK) {
            // Modificar columna para AUTO_INCREMENT y agregar PRIMARY KEY
            // ALTER TABLE hace COMMIT implícito en MySQL; si falla, lo manejamos en el catch
            $db->exec("ALTER TABLE `$table` MODIFY id_solicitud INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id_solicitud)");
            salida("Columna id_solicitud modificada a AUTO_INCREMENT y PRIMARY KEY añadida en $table.");
        } else {
            salida("La tabla $table ya tiene PRIMARY KEY.");
        }

        if ($db->inTransaction()) {
            $db->commit();
        }
        salida("Procesamiento de $table completado correctamente.");
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            try { $db->rollBack(); } catch (Exception $__) {}
        }
        salida('Error procesando ' . $table . ': ' . $e->getMessage());
    }
}

fixTable($db, 'solicitud_poliza');
fixTable($db, 'solicitud_siniestro');

salida('Script finalizado. Por favor, revise que los ids asignados son correctos y reinicie sesión en la aplicación si corresponde.');

?>