-- Agrega campos de foto de perfil en persona
ALTER TABLE persona
    ADD COLUMN IF NOT EXISTS foto_perfil_url VARCHAR(512) NULL AFTER telefono,
    ADD COLUMN IF NOT EXISTS foto_perfil_public_id VARCHAR(255) NULL AFTER foto_perfil_url;
