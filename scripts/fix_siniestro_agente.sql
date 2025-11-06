-- fix_siniestro_agente.sql
-- Backup + safe-update script to reassign siniestro.cedula_agente_gestion to the correct agent
-- Edit the variable @agent if Santiago's cedula is different.

-- CONFIG: change if needed
SET @agent := 'V12345678';

-- 0) Recommended: make a full SQL dump with mysqldump beforehand (outside this script):
-- mysqldump -u root -p seguros_la_previsora > seguros_la_previsora_full_dump_before_fix.sql

-- 1) Quick backup of the siniestro table (fast but not a replacement for mysqldump)
CREATE TABLE IF NOT EXISTS siniestro_backup AS SELECT * FROM siniestro;

-- 2) Summary counts BEFORE change
SELECT 'BEFORE_COUNTS' AS phase;
SELECT cedula_agente_gestion, COUNT(*) AS cnt
FROM siniestro
GROUP BY cedula_agente_gestion
ORDER BY cnt DESC;

SELECT COUNT(*) AS total_rows FROM siniestro;

-- 3) Show rows that would be affected (not agent)
SELECT id_siniestro, numero_siniestro, fecha_reporte, estado, id_poliza, cedula_agente_gestion
FROM siniestro
WHERE cedula_agente_gestion IS NOT NULL
  AND cedula_agente_gestion <> @agent
ORDER BY id_siniestro;

-- 4) SAFE UPDATE: only change rows whose cedula_agente_gestion references a usuario that is NOT an agent (id_rol <> 2)
--    This preserves rows already assigned to agents and leaves NULLs untouched.
START TRANSACTION;

UPDATE siniestro s
JOIN usuario u ON s.cedula_agente_gestion = u.cedula
SET s.cedula_agente_gestion = @agent
WHERE s.cedula_agente_gestion <> @agent
  AND (u.id_rol IS NULL OR u.id_rol <> 2);

COMMIT;

-- 5) Summary counts AFTER change
SELECT 'AFTER_COUNTS' AS phase;
SELECT cedula_agente_gestion, COUNT(*) AS cnt
FROM siniestro
GROUP BY cedula_agente_gestion
ORDER BY cnt DESC;

SELECT COUNT(*) AS total_rows FROM siniestro;

-- 6) Sample verification: list previously known problematic ids (adjust as needed)
SELECT * FROM siniestro WHERE id_siniestro IN (16,17,20,22,24,26,30,31,35) ORDER BY id_siniestro;

-- 7) Rollback helper (manual):
-- If you prefer to revert to the exact previous state you can run the following commands:
-- TRUNCATE TABLE siniestro;
-- INSERT INTO siniestro SELECT * FROM siniestro_backup;
-- NOTE: If other tables reference siniestro by fk, take care and prefer a mysqldump restore workflow.

-- 8) Clean-up suggestion: once you verify results and you are confident, you may DROP the backup table:
-- DROP TABLE IF EXISTS siniestro_backup;

-- End of script
