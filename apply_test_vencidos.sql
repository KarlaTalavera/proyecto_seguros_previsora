-- Marca pagos de ejemplo como vencidos para testing de R4
-- Ejecuta en phpMyAdmin o desde mysql client contra la base 'seguros_la_previsora'

UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 10 DAY) WHERE id_pago_prima = 1000;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 35 DAY) WHERE id_pago_prima = 1001;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 65 DAY) WHERE id_pago_prima = 1002;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 120 DAY) WHERE id_pago_prima = 1003;
UPDATE pago_prima SET fecha_vencimiento = DATE_SUB(CURDATE(), INTERVAL 200 DAY) WHERE id_pago_prima = 1004;

-- Opcional: marcar algunos estados como ATRASADO
UPDATE pago_prima SET estado_pago = 'ATRASADO' WHERE id_pago_prima IN (1000,1001,1002,1003,1004);

SELECT * FROM pago_prima WHERE id_pago_prima IN (1000,1001,1002,1003,1004);
