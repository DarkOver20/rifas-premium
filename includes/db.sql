CREATE DATABASE IF NOT EXISTS rifas_premium;
USE rifas_premium;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion TEXT,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario',
    avatar VARCHAR(255) DEFAULT 'default.jpg'
);

-- Tabla de eventos (rifas)
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT,
    imagen VARCHAR(255),
    precio_boleto DECIMAL(10,2) NOT NULL,
    total_boletos INT NOT NULL,
    boletos_disponibles INT NOT NULL,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    estado ENUM('activo', 'finalizado', 'proximamente') DEFAULT 'proximamente',
    premio_principal TEXT,
    premios_secundarios TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de boletos
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    numero_boleto VARCHAR(10) NOT NULL,
    estado ENUM('disponible', 'reservado', 'pagado', 'ganador') DEFAULT 'disponible',
    usuario_id INT,
    transaccion_id INT,
    fecha_reserva DATETIME,
    fecha_pago DATETIME,
    FOREIGN KEY (evento_id) REFERENCES eventos(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    UNIQUE KEY (evento_id, numero_boleto)
);

-- Tabla de transacciones
CREATE TABLE IF NOT EXISTS transacciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    evento_id INT NOT NULL,
    referencia VARCHAR(50) NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('transferencia', 'efectivo', 'pago_movil', 'otros'),
    comprobante VARCHAR(255),
    estado ENUM('pendiente', 'aprobado', 'rechazado') DEFAULT 'pendiente',
    fecha_transaccion DATETIME DEFAULT CURRENT_TIMESTAMP,
    admin_id INT,
    fecha_revision DATETIME,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (evento_id) REFERENCES eventos(id),
    FOREIGN KEY (admin_id) REFERENCES usuarios(id)
);

-- Tabla de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);

-- Insertar usuarios
INSERT INTO usuarios (nombre, email, password, telefono, direccion, rol) VALUES
('Admin Principal', 'admin@rifaspremium.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04141234567', 'Av. Principal, Caracas', 'admin'),
('Juan Pérez', 'juan@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04142345678', 'Calle 1, Valencia', 'usuario'),
('María González', 'maria@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04243456789', 'Av. Bolívar, Maracaibo', 'usuario'),
('Carlos Rodríguez', 'carlos@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04164567890', 'Calle 5, Mérida', 'usuario'),
('Ana López', 'ana@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04165678901', 'Urbanización Las Acacias, Caracas', 'usuario'),
('Pedro Martínez', 'pedro@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04266789012', 'Calle Comercio, Barquisimeto', 'usuario'),
('Luisa Hernández', 'luisa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04167890123', 'Av. Libertador, Puerto La Cruz', 'usuario'),
('Jorge Díaz', 'jorge@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04268901234', 'Sector La Victoria, Maracay', 'usuario'),
('Sofía Rojas', 'sofia@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04160012345', 'Urbanización El Paraíso, Caracas', 'usuario'),
('Miguel Sánchez', 'miguel@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04261123456', 'Calle 10, Valencia', 'usuario');

-- Insertar eventos
INSERT INTO eventos (titulo, descripcion, imagen, precio_boleto, total_boletos, boletos_disponibles, fecha_inicio, fecha_fin, estado, premio_principal, premios_secundarios) VALUES
('Rifa de Automóvil Toyota Corolla', 'Participa por un flamante Toyota Corolla 2023', 'toyota-corolla.jpg', 50.00, 1000, 650, '2023-06-01 00:00:00', '2023-12-31 23:59:59', 'activo', 'Toyota Corolla 2023 modelo SE', '1er premio: $5,000\n2do premio: Viaje a Margarita\n3er premio: Smart TV 55"'),
('Sorteo de Casa en la Playa', 'Hermosa casa frente al mar en Higuerote', 'casa-playa.jpg', 100.00, 500, 120, '2023-07-15 00:00:00', '2023-11-30 23:59:59', 'activo', 'Casa de 3 habitaciones en Higuerote', '1er premio: $10,000\n2do premio: Auto compacto\n3er premio: Crucero para 2 personas'),
('Rifa de Motocicleta Harley Davidson', 'Participa por una Harley Davidson Sportster', 'harley-davidson.jpg', 30.00, 1500, 980, '2023-08-01 00:00:00', '2024-01-31 23:59:59', 'activo', 'Harley Davidson Sportster 2023', '1er premio: $3,000\n2do premio: Equipo de motociclista completo\n3er premio: Viaje a los Roques'),
('Sorteo de Viaje a Europa', 'Viaje para 2 personas a 5 países de Europa', 'viaje-europa.jpg', 75.00, 800, 800, '2023-09-01 00:00:00', '2023-12-15 23:59:59', 'proximamente', 'Viaje todo incluido a España, Francia, Italia, Alemania y Suiza', '1er premio: $7,000\n2do premio: Viaje a Miami\n3er premio: Crucero por el Caribe'),
('Rifa de Smartphone iPhone 15 Pro', 'Participa por el nuevo iPhone 15 Pro 256GB', 'iphone15.jpg', 10.00, 2000, 1500, '2023-08-15 00:00:00', '2023-10-31 23:59:59', 'activo', 'iPhone 15 Pro 256GB', '1er premio: iPad Pro\n2do premio: Apple Watch\n3er premio: AirPods Pro'),
('Sorteo de Computadora Gamer', 'Potente PC Gamer con RTX 4090', 'pc-gamer.jpg', 25.00, 1200, 950, '2023-07-01 00:00:00', '2023-09-30 23:59:59', 'finalizado', 'PC Gamer con Intel i9, RTX 4090, 32GB RAM', '1er premio: Monitor 4K 32"\n2do premio: Teclado y mouse gamer\n3er premio: Consola PS5'),
('Rifa de Colección de Vinos', 'Excelente colección de 12 vinos premium', 'vinos-premium.jpg', 15.00, 600, 200, '2023-06-10 00:00:00', '2023-08-31 23:59:59', 'finalizado', 'Colección de 12 vinos de alta gama', '1er premio: Cena para 2 en restaurante gourmet\n2do premio: Curso de sommelier\n3er premio: Suscripción a club de vinos'),
('Sorteo de Bicicleta Eléctrica', 'Bicicleta eléctrica de última generación', 'bicicleta-electrica.jpg', 20.00, 1000, 400, '2023-08-20 00:00:00', '2023-10-15 23:59:59', 'activo', 'Bicicleta eléctrica modelo XT-500', '1er premio: $1,000\n2do premio: Equipo ciclista completo\n3er premio: Suscripción anual a gimnasio'),
('Rifa de Joyería en Oro', 'Hermoso juego de collar y aretes en oro 18k', 'joyeria-oro.jpg', 40.00, 700, 300, '2023-07-05 00:00:00', '2023-09-15 23:59:59', 'finalizado', 'Juego completo de joyería en oro 18k', '1er premio: Reloj de oro\n2do premio: Anillo de diamantes\n3er premio: Pulsera de plata'),
('Sorteo de Consola PS5 + Juegos', 'Consola PS5 con 10 juegos incluidos', 'ps5-juegos.jpg', 12.00, 1500, 1100, '2023-09-10 00:00:00', '2023-11-30 23:59:59', 'proximamente', 'Consola PS5 + 10 juegos físicos', '1er premio: TV 4K 50"\n2do premio: Suscripción PS Plus por 1 año\n3er premio: Mando adicional');

-- Generar boletos para cada evento
DELIMITER //
CREATE PROCEDURE generar_boletos(IN evento_id INT, IN total_boletos INT)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE numero_boleto VARCHAR(10);
    
    WHILE i <= total_boletos DO
        SET numero_boleto = LPAD(i, LENGTH(total_boletos), '0');
        
        INSERT INTO boletos (evento_id, numero_boleto) VALUES (evento_id, numero_boleto);
        
        SET i = i + 1;
    END WHILE;
END //
DELIMITER ;

-- Llamar al procedimiento para cada evento
CALL generar_boletos(1, 1000);
CALL generar_boletos(2, 500);
CALL generar_boletos(3, 1500);
CALL generar_boletos(4, 800);
CALL generar_boletos(5, 2000);
CALL generar_boletos(6, 1200);
CALL generar_boletos(7, 600);
CALL generar_boletos(8, 1000);
CALL generar_boletos(9, 700);
CALL generar_boletos(10, 1500);

-- Insertar transacciones y asignar algunos boletos
-- Transacción 1 (aprobada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision) 
VALUES (2, 1, 'TRANSF-001', 200.00, 'transferencia', 'aprobado', 1, '2023-07-15 10:30:00');

UPDATE boletos SET estado = 'pagado', usuario_id = 2, transaccion_id = 1, fecha_reserva = '2023-07-10 09:15:00', fecha_pago = '2023-07-15 10:30:00'
WHERE evento_id = 1 AND numero_boleto IN ('0001', '0002', '0003', '0004');

-- Transacción 2 (pendiente)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado) 
VALUES (3, 1, 'PAGO-002', 150.00, 'pago_movil', 'pendiente');

UPDATE boletos SET estado = 'reservado', usuario_id = 3, transaccion_id = 2, fecha_reserva = '2023-07-18 14:20:00'
WHERE evento_id = 1 AND numero_boleto IN ('0005', '0006', '0007');

-- Transacción 3 (rechazada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision, notas) 
VALUES (4, 2, 'EFECT-003', 300.00, 'efectivo', 'rechazado', 1, '2023-07-20 16:45:00', 'Comprobante no válido');

UPDATE boletos SET estado = 'disponible', usuario_id = NULL, transaccion_id = NULL, fecha_reserva = NULL
WHERE evento_id = 2 AND numero_boleto IN ('0001', '0002', '0003');

-- Transacción 4 (aprobada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision) 
VALUES (5, 3, 'TRANSF-004', 90.00, 'transferencia', 'aprobado', 1, '2023-08-01 11:20:00');

UPDATE boletos SET estado = 'pagado', usuario_id = 5, transaccion_id = 4, fecha_reserva = '2023-07-25 10:00:00', fecha_pago = '2023-08-01 11:20:00'
WHERE evento_id = 3 AND numero_boleto IN ('0123', '0456', '0789');

-- Transacción 5 (pendiente)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado) 
VALUES (6, 5, 'PAGO-005', 50.00, 'pago_movil', 'pendiente');

UPDATE boletos SET estado = 'reservado', usuario_id = 6, transaccion_id = 5, fecha_reserva = '2023-08-05 15:30:00'
WHERE evento_id = 5 AND numero_boleto IN ('1001', '1002', '1003', '1004', '1005');

-- Transacción 6 (aprobada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision) 
VALUES (7, 6, 'TRANSF-006', 75.00, 'transferencia', 'aprobado', 1, '2023-07-30 09:15:00');

UPDATE boletos SET estado = 'pagado', usuario_id = 7, transaccion_id = 6, fecha_reserva = '2023-07-28 16:45:00', fecha_pago = '2023-07-30 09:15:00'
WHERE evento_id = 6 AND numero_boleto IN ('0001', '0002', '0003');

-- Transacción 7 (aprobada con boleto ganador)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision) 
VALUES (8, 7, 'TRANSF-007', 60.00, 'transferencia', 'aprobado', 1, '2023-08-10 14:30:00');

UPDATE boletos SET estado = 'pagado', usuario_id = 8, transaccion_id = 7, fecha_reserva = '2023-08-05 11:20:00', fecha_pago = '2023-08-10 14:30:00'
WHERE evento_id = 7 AND numero_boleto IN ('0101', '0102', '0103', '0104');

-- Marcar un boleto como ganador
UPDATE boletos SET estado = 'ganador' WHERE evento_id = 7 AND numero_boleto = '0102';

-- Transacción 8 (pendiente)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado) 
VALUES (9, 8, 'PAGO-008', 80.00, 'pago_movil', 'pendiente');

UPDATE boletos SET estado = 'reservado', usuario_id = 9, transaccion_id = 8, fecha_reserva = '2023-08-15 10:45:00'
WHERE evento_id = 8 AND numero_boleto IN ('0201', '0202', '0203', '0204');

-- Transacción 9 (rechazada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision, notas) 
VALUES (10, 9, 'EFECT-009', 160.00, 'efectivo', 'rechazado', 1, '2023-08-18 16:20:00', 'Pago no verificado');

UPDATE boletos SET estado = 'disponible', usuario_id = NULL, transaccion_id = NULL, fecha_reserva = NULL
WHERE evento_id = 9 AND numero_boleto IN ('0051', '0052', '0053', '0054');

-- Transacción 10 (aprobada)
INSERT INTO transacciones (usuario_id, evento_id, referencia, monto, metodo_pago, estado, admin_id, fecha_revision) 
VALUES (2, 10, 'TRANSF-010', 48.00, 'transferencia', 'aprobado', 1, '2023-09-05 11:10:00');

UPDATE boletos SET estado = 'pagado', usuario_id = 2, transaccion_id = 10, fecha_reserva = '2023-09-01 09:30:00', fecha_pago = '2023-09-05 11:10:00'
WHERE evento_id = 10 AND numero_boleto IN ('1001', '1002', '1003', '1004');

-- Insertar notificaciones
INSERT INTO notificaciones (usuario_id, titulo, mensaje, leida) VALUES
(2, 'Pago Aprobado', 'Tu pago por los boletos para la Rifa de Automóvil Toyota Corolla ha sido aprobado', TRUE),
(2, 'Boleto Ganador', '¡Felicidades! Has ganado en la Rifa de Colección de Vinos con el boleto #0102', FALSE),
(3, 'Pago Pendiente', 'Tu pago por los boletos para la Rifa de Automóvil Toyota Corolla está pendiente de revisión', FALSE),
(4, 'Pago Rechazado', 'Tu pago para el Sorteo de Casa en la Playa fue rechazado. Razón: Comprobante no válido', TRUE),
(5, 'Pago Aprobado', 'Tu pago por los boletos para la Rifa de Motocicleta Harley Davidson ha sido aprobado', TRUE),
(6, 'Pago Pendiente', 'Tu pago por los boletos para la Rifa de Smartphone iPhone 15 Pro está pendiente de revisión', FALSE),
(7, 'Pago Aprobado', 'Tu pago por los boletos para el Sorteo de Computadora Gamer ha sido aprobado', TRUE),
(8, 'Pago Aprobado', 'Tu pago por los boletos para la Rifa de Colección de Vinos ha sido aprobado', TRUE),
(9, 'Pago Pendiente', 'Tu pago por los boletos para el Sorteo de Bicicleta Eléctrica está pendiente de revisión', FALSE),
(10, 'Pago Rechazado', 'Tu pago para la Rifa de Joyería en Oro fue rechazado. Razón: Pago no verificado', TRUE);

-- Actualizar contadores de boletos disponibles en eventos
UPDATE eventos SET boletos_disponibles = (
    SELECT COUNT(*) FROM boletos WHERE evento_id = eventos.id AND estado = 'disponible'
);
CREATE TABLE `metodos_pago` (
  `id` INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `nombre` VARCHAR(255) NOT NULL,
  `detalles` TEXT, -- Aquí almacenaremos los detalles variables en formato JSON
  `icono` VARCHAR(255), -- Ruta o nombre del archivo del icono
  `activo` TINYINT(1) DEFAULT 1, -- Para habilitar o deshabilitar métodos de pago
  `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);