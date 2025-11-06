-- Seed de datos para desarrollo: Seguros La Previsora
-- Inserta categorías, tipos de póliza, coberturas, usuarios (clientes), clientes, pólizas, detalle_poliza, pago_prima y poliza_cobertura
-- IMPORTANTE: ejecuta en un entorno de desarrollo. No ejecutar en producción.

START TRANSACTION;

-- Categorías
INSERT INTO categoria_poliza (id_categoria, nombre) VALUES
(1, 'Personas'),
(2, 'Automóvil'),
(3, 'Patrimoniales');

-- Tipos de póliza (id_tipo_poliza)
INSERT INTO tipo_poliza (id_tipo_poliza, nombre, descripcion, id_categoria) VALUES
(1, 'Accidentes Personales', 'Indemnización por muerte accidental', 1),
(2, 'AP Escolar Colectiva', 'Cobertura para estudiantes', 1),
(3, 'Salud - Telemedicina y Domiciliaria', 'Atención a distancia y domiciliar', 1),
(4, 'Funerario Previ Serenidad', 'Cobertura de gastos funerarios', 1),
(5, 'R.C.V. Vehículos', 'Responsabilidad civil vehicular', 2),
(6, 'Combinado Residencial', 'Cobertura integral del hogar', 3),
(7, 'Combinado Empresarial', 'Protección integral comercial', 3),
(8, 'Incendio', 'Cobertura por incendio y riesgos afines', 3),
(9, 'Sustracción Ilegítima', 'Cobertura por robo/asalto', 3);

-- Coberturas (ejemplos)
INSERT INTO cobertura (id_cobertura, nombre, detalle) VALUES
(1, 'Asistencia Vial', 'Grúa y asistencia mecánica'),
(2, 'GPS', 'Seguimiento GPS opcional'),
(3, 'Robo Total', 'Cobertura por sustracción ilegítima');

-- Usuarios (clientes) - usamos un hash de ejemplo ya presente en el dump para simplificar
-- Nota: los usuarios de agentes ya vienen en el dump (p.ej. V12345678)
INSERT INTO usuario (cedula, nombre, apellido, email, password_hash, telefono, activo, id_rol) VALUES
('V20000001', 'Juan', 'Pérez', 'juan.perez@example.com', '$2y$10$xQNNf3KGSblr4UhPyxzmM.edawtvKfeb1t4xDk0K3K9r40GMDRQR2', '04141234567', 1, 3),
('V20000002', 'María', 'Gómez', 'maria.gomez@example.com', '$2y$10$xQNNf3KGSblr4UhPyxzmM.edawtvKfeb1t4xDk0K3K9r40GMDRQR2', '04147654321', 1, 3);

-- Clientes (referenciando las cédulas anteriores)
INSERT INTO cliente (id_cliente, cedula_asegurado, direccion, fecha_nacimiento) VALUES
(1, 'V20000001', 'Av. Principal, Caracas', '1985-04-12'),
(2, 'V20000002', 'Calle Falsa 123, Valencia', '1990-07-02');

-- Pólizas (asignadas al agente ya existente V12345678)
INSERT INTO poliza (id_poliza, numero_poliza, estado, id_cliente, cedula_agente, id_tipo_poliza) VALUES
(100, 'POL-1001', 'ACTIVA', 1, 'V12345678', 5),
(101, 'POL-1002', 'ACTIVA', 2, 'V12345678', 6),
(102, 'POL-1003', 'PENDIENTE', 1, 'V12345678', 1),
(103, 'POL-1004', 'ACTIVA', 2, 'V12345678', 3),
(104, 'POL-1005', 'VENCER', 1, 'V12345678', 8),
(105, 'POL-1006', 'ACTIVA', 2, 'V12345678', 9);

-- Detalle de póliza (fechas y primas). Hacemos algunas con vencimiento cercano para R1
INSERT INTO detalle_poliza (id_poliza, fecha_inicio, fecha_fin, monto_prima) VALUES
(100, DATE_SUB(CURDATE(), INTERVAL 11 MONTH), DATE_ADD(CURDATE(), INTERVAL 20 DAY), 450.00),
(101, DATE_SUB(CURDATE(), INTERVAL 6 MONTH), DATE_ADD(CURDATE(), INTERVAL 40 DAY), 1200.00),
(102, DATE_SUB(CURDATE(), INTERVAL 1 MONTH), DATE_ADD(CURDATE(), INTERVAL 10 DAY), 95.00),
(103, DATE_SUB(CURDATE(), INTERVAL 3 MONTH), DATE_ADD(CURDATE(), INTERVAL 90 DAY), 300.00),
(104, DATE_SUB(CURDATE(), INTERVAL 1 YEAR), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 800.00),
(105, DATE_SUB(CURDATE(), INTERVAL 2 MONTH), DATE_ADD(CURDATE(), INTERVAL 365 DAY), 1500.00);

-- Pagos de primas (ejemplos)
INSERT INTO pago_prima (id_pago_prima, id_poliza, numero_cuota, monto_cuota, fecha_vencimiento, fecha_pago_efectuado, estado_pago) VALUES
(1000, 100, 1, 450.00, DATE_ADD(CURDATE(), INTERVAL 20 DAY), NULL, 'PENDIENTE'),
(1001, 101, 1, 1200.00, DATE_ADD(CURDATE(), INTERVAL 40 DAY), NULL, 'PENDIENTE'),
(1002, 102, 1, 95.00, DATE_ADD(CURDATE(), INTERVAL 10 DAY), NULL, 'PENDIENTE'),
(1003, 103, 1, 300.00, DATE_ADD(CURDATE(), INTERVAL 90 DAY), NULL, 'PENDIENTE'),
(1004, 104, 1, 800.00, DATE_ADD(CURDATE(), INTERVAL 5 DAY), NULL, 'PENDIENTE'),
(1005, 105, 1, 1500.00, DATE_ADD(CURDATE(), INTERVAL 365 DAY), NULL, 'PENDIENTE');

-- Poliza-Cobertura
INSERT INTO poliza_cobertura (id_poliza, id_cobertura) VALUES
(101, 1),
(101, 2),
(105, 3);

COMMIT;
