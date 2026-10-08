-- Actualización del esquema creado por 01_comercial.sql.
-- Aplicar en la base del CRM con la aplicación en mantenimiento.
-- Conserva clientes, asesores, prospectos, asignaciones e historial.
-- No cambia ningún id_empresa de COMI ni fija los IDs 55/56 en la base.
BEGIN;
SET LOCAL lock_timeout = '10s';
LOCK TABLE comercial_prospectos IN ACCESS EXCLUSIVE MODE;

ALTER TABLE comercial_prospectos
    ADD COLUMN IF NOT EXISTS id_empresa_origen INTEGER;

-- Antes, el cliente y el equipo pertenecían a la misma empresa.
UPDATE comercial_prospectos
SET id_empresa_origen = id_empresa
WHERE id_empresa_origen IS NULL;

ALTER TABLE comercial_prospectos
    ALTER COLUMN id_empresa_origen SET NOT NULL;

-- Nombres generados por PostgreSQL para las restricciones del SQL 01 original.
-- La FK (id_empresa,id_asesor) permanece: el asesor sigue siendo interno.
ALTER TABLE comercial_prospectos
    DROP CONSTRAINT IF EXISTS comercial_prospectos_id_empresa_id_cliente_fkey,
    DROP CONSTRAINT IF EXISTS comercial_prospectos_id_empresa_id_cliente_key;

-- Se recrean para permitir repetir este parche sin duplicar restricciones.
ALTER TABLE comercial_prospectos
    DROP CONSTRAINT IF EXISTS comercial_prospectos_origen_cliente_fk,
    DROP CONSTRAINT IF EXISTS comercial_prospectos_propietaria_fk,
    DROP CONSTRAINT IF EXISTS comercial_prospectos_propietaria_origen_cliente_uk;

ALTER TABLE comercial_prospectos
    ADD CONSTRAINT comercial_prospectos_origen_cliente_fk
        FOREIGN KEY (id_empresa_origen, id_cliente)
        REFERENCES clientes(id_empresa, id_cliente),
    ADD CONSTRAINT comercial_prospectos_propietaria_fk
        FOREIGN KEY (id_empresa) REFERENCES empresas(id_empresa),
    ADD CONSTRAINT comercial_prospectos_propietaria_origen_cliente_uk
        UNIQUE (id_empresa, id_empresa_origen, id_cliente);

CREATE INDEX IF NOT EXISTS comercial_prospectos_origen_cliente_idx
    ON comercial_prospectos(id_empresa_origen, id_cliente);
COMMIT;
