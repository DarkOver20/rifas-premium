CREATE DATABASE IF NOT EXISTS rifasam;
USE rifasam;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    activo BOOLEAN DEFAULT TRUE,
    rol ENUM('admin') DEFAULT 'admin'
);

-- Tabla de eventos (rifas)
CREATE TABLE IF NOT EXISTS eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    slogan VARCHAR(255),
    descripcion TEXT,
    imagen VARCHAR(255),
    precio_boleto DECIMAL(10,2) NOT NULL,
    total_boletos INT NOT NULL,
    boletos_disponibles INT NOT NULL,
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    estado ENUM('activo', 'finalizado', 'proximamente') DEFAULT 'proximamente',
    premio_principal TEXT,
    boleto_ganador VARCHAR(10),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_estado (estado),
    INDEX idx_fechas (fecha_inicio, fecha_fin)
);

-- Tabla de boletos (optimizada para grandes volúmenes)
CREATE TABLE IF NOT EXISTS boletos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    numero_boleto VARCHAR(10) NOT NULL,
    estado ENUM('disponible', 'reservado', 'pagado', 'ganador') DEFAULT 'disponible',
    transaccion_id INT NULL,
    fecha_reserva DATETIME NULL,
    fecha_pago DATETIME NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos(id) ON DELETE CASCADE,
    INDEX idx_evento_estado (evento_id, estado),
    INDEX idx_numero (numero_boleto),
    UNIQUE KEY (evento_id, numero_boleto)
) ENGINE=InnoDB;

-- Tabla de métodos de pago
CREATE TABLE IF NOT EXISTS metodos_pago (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    detalles TEXT, -- Detalles variables en formato JSON
    icono VARCHAR(255), -- Ruta o nombre del archivo del icono
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de transacciones (antes llamada compras)
CREATE TABLE IF NOT EXISTS transacciones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    evento_id INT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    estado VARCHAR(100) NOT NULL,
    metodo_pago_id INT UNSIGNED NOT NULL,
    referencia_transaccion VARCHAR(255) NOT NULL,
    comprobante_pago VARCHAR(255) NOT NULL,
    boletos_seleccionados TEXT NOT NULL, -- IDs de los boletos en formato JSON
    monto_total DECIMAL(10,2) NOT NULL,
    estado_compra ENUM('pendiente', 'aprobada', 'rechazada') DEFAULT 'pendiente',
    usuario_id INT NULL, -- Quién aprobó/rechazó
    notas TEXT,
    fecha_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_revision DATETIME NULL,
    FOREIGN KEY (evento_id) REFERENCES eventos(id),
    FOREIGN KEY (metodo_pago_id) REFERENCES metodos_pago(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_estado_compra (estado_compra),
    INDEX idx_fecha_compra (fecha_compra)
);

-- Trigger para actualizar boletos_disponibles cuando cambia el estado de un boleto
DELIMITER //
CREATE TRIGGER after_boleto_update
AFTER UPDATE ON boletos
FOR EACH ROW
BEGIN
    IF NEW.estado != OLD.estado THEN
        UPDATE eventos 
        SET boletos_disponibles = (
            SELECT COUNT(*) FROM boletos 
            WHERE evento_id = NEW.evento_id AND estado = 'disponible'
        )
        WHERE id = NEW.evento_id;
    END IF;
END//
DELIMITER ;

-- Trigger para actualizar el boleto ganador
DELIMITER //
CREATE TRIGGER after_boleto_ganador_update
AFTER UPDATE ON eventos
FOR EACH ROW
BEGIN
    IF NEW.boleto_ganador IS NOT NULL AND (OLD.boleto_ganador IS NULL OR NEW.boleto_ganador != OLD.boleto_ganador) THEN
        -- Primero, quitar el estado 'ganador' de cualquier boleto que lo tuviera
        UPDATE boletos 
        SET estado = 'pagado'
        WHERE evento_id = NEW.id AND estado = 'ganador';
        
        -- Luego, asignar el estado 'ganador' al nuevo boleto ganador
        UPDATE boletos 
        SET estado = 'ganador'
        WHERE evento_id = NEW.id AND numero_boleto = NEW.boleto_ganador;
    END IF;
END//
DELIMITER ;