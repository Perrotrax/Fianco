-- Migración completa: WebAuthn, Wallet y Módulos ERP

USE gestor_gastos;

-- 1) Soporte WebAuthn (actualizado a VARCHAR para evitar problemas de codificación de bytes en PHP)
CREATE TABLE IF NOT EXISTS webauthn_credentials (
  id_cred INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  credential_id VARCHAR(512) NOT NULL,
  public_key TEXT NOT NULL,
  sign_count BIGINT DEFAULT 0,
  transports VARCHAR(255) DEFAULT NULL,
  aaguid VARCHAR(64) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  UNIQUE KEY uq_credential_id (credential_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Asegurar columna webauthn_enabled en usuarios
SET @dbname = DATABASE();
SET @tablename = "usuarios";
SET @columnname = "webauthn_enabled";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
   WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE usuarios ADD COLUMN webauthn_enabled BOOLEAN NOT NULL DEFAULT FALSE AFTER biometrico"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2) Soporte Wallet en usuarios
SET @columnname = "wallet_balance";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
   WHERE TABLE_SCHEMA = @dbname 
     AND TABLE_NAME = @tablename 
     AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE usuarios ADD COLUMN wallet_balance DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER presupuesto"
));
PREPARE stmt FROM @preparedStatement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3) Tabla de Transacciones de Billetera
CREATE TABLE IF NOT EXISTS wallet_transactions (
  id_transaccion INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  tipo VARCHAR(50) NOT NULL, -- 'deposito', 'retiro', 'pago_gasto'
  monto DECIMAL(10, 2) NOT NULL,
  descripcion VARCHAR(255) NOT NULL,
  fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4) Tabla de Proyectos
CREATE TABLE IF NOT EXISTS proyectos (
  id_proyecto INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  codigo VARCHAR(50) NOT NULL,
  presupuesto DECIMAL(10, 2) DEFAULT 0.00,
  gastado DECIMAL(10, 2) DEFAULT 0.00,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) Tabla de Viajes
CREATE TABLE IF NOT EXISTS viajes (
  id_viaje INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  destino VARCHAR(100) NOT NULL,
  fecha_inicio DATE,
  fecha_fin DATE,
  presupuesto DECIMAL(10, 2) DEFAULT 0.00,
  estado VARCHAR(50) DEFAULT 'Planificado', -- 'Planificado', 'En curso', 'Terminado'
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6) Tabla de Anticipos
CREATE TABLE IF NOT EXISTS anticipos (
  id_anticipo INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_viaje INT DEFAULT NULL,
  monto DECIMAL(10, 2) NOT NULL,
  motivo VARCHAR(255) NOT NULL,
  estado VARCHAR(50) DEFAULT 'Pendiente', -- 'Pendiente', 'Aprobado', 'Rechazado'
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_viaje) REFERENCES viajes(id_viaje) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7) Modificaciones a la tabla Gastos para soportar ERP
SET @tablename = "gastos";

-- id_proyecto
SET @columnname = "id_proyecto";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE gastos ADD COLUMN id_proyecto INT DEFAULT NULL AFTER id_usuario"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- id_viaje
SET @columnname = "id_viaje";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE gastos ADD COLUMN id_viaje INT DEFAULT NULL AFTER id_proyecto"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- estado
SET @columnname = "estado";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE gastos ADD COLUMN estado VARCHAR(50) DEFAULT 'Pendiente' AFTER categoria"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- metodo_pago
SET @columnname = "metodo_pago";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE gastos ADD COLUMN metodo_pago VARCHAR(50) DEFAULT 'Efectivo' AFTER estado"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- xml_invoice
SET @columnname = "xml_invoice";
SET @preparedStatement = (SELECT IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = @tablename AND COLUMN_NAME = @columnname) > 0,
  "SELECT 1",
  "ALTER TABLE gastos ADD COLUMN xml_invoice TEXT DEFAULT NULL AFTER metodo_pago"
));
PREPARE stmt FROM @preparedStatement; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Añadir llaves foráneas para id_proyecto e id_viaje si no existen
-- (Usamos una técnica simple: intentar agregarlas ignorando errores si ya existen,
-- pero para evitar fallos de ejecución, en MySQL de desarrollo simplemente las agregamos directamente)
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'gastos' AND CONSTRAINT_NAME = 'fk_gasto_proyecto');
SET @sql_str = IF(@fk_exists > 0, "SELECT 1", "ALTER TABLE gastos ADD CONSTRAINT fk_gasto_proyecto FOREIGN KEY (id_proyecto) REFERENCES proyectos(id_proyecto) ON DELETE SET NULL");
PREPARE stmt FROM @sql_str; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @fk_exists2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = @dbname AND TABLE_NAME = 'gastos' AND CONSTRAINT_NAME = 'fk_gasto_viaje');
SET @sql_str2 = IF(@fk_exists2 > 0, "SELECT 1", "ALTER TABLE gastos ADD CONSTRAINT fk_gasto_viaje FOREIGN KEY (id_viaje) REFERENCES viajes(id_viaje) ON DELETE SET NULL");
PREPARE stmt FROM @sql_str2; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 8) Tabla de Facturas
CREATE TABLE IF NOT EXISTS facturas (
  id_factura INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_gasto INT DEFAULT NULL,
  folio VARCHAR(50) NOT NULL,
  emisor VARCHAR(100) NOT NULL,
  receptor VARCHAR(100) NOT NULL,
  monto DECIMAL(10, 2) NOT NULL,
  iva DECIMAL(10, 2) NOT NULL,
  fecha_emision DATE,
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_gasto) REFERENCES gastos(id_gasto) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9) Tabla de Liquidaciones
CREATE TABLE IF NOT EXISTS liquidaciones (
  id_liquidacion INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  id_viaje INT DEFAULT NULL,
  nombre VARCHAR(100) NOT NULL,
  monto_total DECIMAL(10, 2) NOT NULL,
  monto_anticipos DECIMAL(10, 2) NOT NULL,
  resultado DECIMAL(10, 2) NOT NULL, -- Positivo = Reembolso al empleado, Negativo = Devolución a la empresa
  estado VARCHAR(50) DEFAULT 'Borrador', -- 'Borrador', 'Procesado'
  fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  FOREIGN KEY (id_viaje) REFERENCES viajes(id_viaje) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10) Tabla de Categorías Personalizadas
CREATE TABLE IF NOT EXISTS categorias_custom (
  id_categoria INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  limite_mensual DECIMAL(10, 2) DEFAULT 0.00,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE,
  UNIQUE KEY uq_user_cat (id_usuario, nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunas categorías por defecto para los usuarios existentes
-- (Se hará vía código o PHP en la migración para cada usuario)
