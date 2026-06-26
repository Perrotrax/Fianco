-- 1) Tabla para credenciales WebAuthn
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
  UNIQUE KEY uq_credential_id (credential_id),
  INDEX idx_webauthn_user (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) Marcar usuarios que tienen WebAuthn habilitado (opcional)
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

-- Nota:
-- - Para registrar una nueva credencial, inserta en `webauthn_credentials`:
--     INSERT INTO webauthn_credentials (id_usuario, credential_id, public_key, sign_count, transports, aaguid)
--     VALUES (?, ?, ?, ?, ?, ?);
--   `credential_id` debe guardarse como bytes (VARBINARY) obtenidos del ArrayBuffer (convertido a base64 o binario según tu implementación).
-- - Al verificar una assertion, carga `public_key` y `sign_count` desde esta tabla y actualiza `sign_count` con el nuevo valor devuelto por la assertion.
-- - Conservamos `token_biometrico` y `biometrico` para compatibilidad/fallback, pero la verificación fuerte debe usarse con `webauthn_credentials`.
