-- =====================================================================
-- db/03_citas_agenda.sql
-- Tabla y stored functions para agendamiento de citas en COMI.
-- =====================================================================

BEGIN;

CREATE TABLE IF NOT EXISTS citas_agenda (
    id                  SERIAL PRIMARY KEY,
    conversation_id     UUID NOT NULL,
    nombre_contacto     VARCHAR(200) NOT NULL,
    telefono            VARCHAR(40)  NOT NULL,
    correo              VARCHAR(150),
    nombre_empresa      VARCHAR(150),
    objetivo_reunion    TEXT,
    fecha_hora_cita     TIMESTAMPTZ NOT NULL,
    estado              VARCHAR(25) NOT NULL DEFAULT 'pendiente_aprobacion'
                        CHECK (estado IN (
                            'pendiente_aprobacion',
                            'confirmada',
                            'rechazada',
                            'reprogramada'
                        )),
    notas               TEXT,
    created_at          TIMESTAMPTZ DEFAULT NOW(),
    updated_at          TIMESTAMPTZ DEFAULT NOW(),
    CONSTRAINT uq_cita_conversation UNIQUE (conversation_id)
);

CREATE INDEX IF NOT EXISTS idx_citas_fecha  ON citas_agenda (fecha_hora_cita);
CREATE INDEX IF NOT EXISTS idx_citas_estado ON citas_agenda (estado);

-- 1. fn_verificar_disponibilidad
-- Retorna TRUE si el slot está libre (no hay cita confirmada o pendiente en ese bloque de 60 minutos).
CREATE OR REPLACE FUNCTION fn_verificar_disponibilidad(p_fecha_hora TIMESTAMPTZ)
RETURNS BOOLEAN
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_ocupado BOOLEAN;
    v_fecha_local TIMESTAMP WITHOUT TIME ZONE;
BEGIN
    IF p_fecha_hora IS NULL OR p_fecha_hora <= NOW() THEN
        RETURN FALSE;
    END IF;

    v_fecha_local := p_fecha_hora AT TIME ZONE 'America/Mexico_City';

    IF EXTRACT(ISODOW FROM v_fecha_local) NOT BETWEEN 1 AND 6
       OR EXTRACT(HOUR FROM v_fecha_local) < 9
       OR EXTRACT(HOUR FROM v_fecha_local) >= 18
       OR EXTRACT(MINUTE FROM v_fecha_local) <> 0
       OR EXTRACT(SECOND FROM v_fecha_local) <> 0 THEN
        RETURN FALSE;
    END IF;

    SELECT EXISTS (
        SELECT 1
        FROM citas_agenda
        WHERE estado IN ('pendiente_aprobacion', 'confirmada')
          AND fecha_hora_cita < (p_fecha_hora + INTERVAL '60 minutes')
          AND (fecha_hora_cita + INTERVAL '60 minutes') > p_fecha_hora
    ) INTO v_ocupado;

    RETURN NOT v_ocupado;
END;
$$;

-- 2. fn_obtener_slots_disponibles
-- Retorna los próximos N slots libres a partir de p_fecha_base, en bloques de 60 min, de lunes a sábado 9:00–18:00.
CREATE OR REPLACE FUNCTION fn_obtener_slots_disponibles(
    p_fecha_base TIMESTAMPTZ,
    p_limite INT DEFAULT 3
)
RETURNS TABLE(slot TIMESTAMPTZ)
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_fecha_minima TIMESTAMPTZ;
    v_start_local TIMESTAMP WITHOUT TIME ZONE;
BEGIN
    IF p_limite < 1 OR p_limite > 10 THEN
        RAISE EXCEPTION 'p_limite debe estar entre 1 y 10';
    END IF;
    IF p_fecha_base IS NULL THEN
        RAISE EXCEPTION 'p_fecha_base es obligatoria';
    END IF;

    v_fecha_minima := GREATEST(p_fecha_base, NOW());
    v_start_local := date_trunc(
        'hour',
        v_fecha_minima AT TIME ZONE 'America/Mexico_City'
    );
    IF v_start_local < (v_fecha_minima AT TIME ZONE 'America/Mexico_City') THEN
        v_start_local := v_start_local + INTERVAL '1 hour';
    END IF;

    RETURN QUERY
    WITH RECURSIVE candidate_slots AS (
        SELECT v_start_local AS candidate_local
        UNION ALL
        SELECT candidate_local + INTERVAL '1 hour'
        FROM candidate_slots
        WHERE candidate_local < v_start_local + INTERVAL '30 days'
    )
    SELECT c.candidate_local AT TIME ZONE 'America/Mexico_City'
    FROM candidate_slots c
    WHERE c.candidate_local AT TIME ZONE 'America/Mexico_City' > NOW()
      -- Lunes (1) a Sábado (6)
      AND EXTRACT(ISODOW FROM c.candidate_local) BETWEEN 1 AND 6
      -- Horario comercial 9:00 a 18:00 (slots 9:00 a 17:00 con duración de 1 hr)
      AND EXTRACT(HOUR FROM c.candidate_local) >= 9
      AND EXTRACT(HOUR FROM c.candidate_local) < 18
      -- Verificar disponibilidad de slot
      AND fn_verificar_disponibilidad(
          c.candidate_local AT TIME ZONE 'America/Mexico_City'
      ) = TRUE
    ORDER BY c.candidate_local
    LIMIT p_limite;
END;
$$;

-- 3. fn_registrar_cita_pendiente
-- INSERT en citas_agenda con estado pendiente_aprobacion. Si ya existe, hace UPDATE (UPSERT).
CREATE OR REPLACE FUNCTION fn_registrar_cita_pendiente(
    p_conversation_id UUID,
    p_nombre VARCHAR,
    p_telefono VARCHAR,
    p_correo VARCHAR,
    p_empresa VARCHAR,
    p_objetivo TEXT,
    p_fecha_hora TIMESTAMPTZ
)
RETURNS TABLE(out_id INT, out_conversation_id UUID)
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_ocupado BOOLEAN;
    v_fecha_local TIMESTAMP WITHOUT TIME ZONE;
BEGIN
    PERFORM pg_advisory_xact_lock(hashtext('citas_agenda_slots'));

    IF p_fecha_hora IS NULL OR p_fecha_hora <= NOW() THEN
        RAISE EXCEPTION 'La fecha de la cita debe estar en el futuro';
    END IF;

    v_fecha_local := p_fecha_hora AT TIME ZONE 'America/Mexico_City';
    IF EXTRACT(ISODOW FROM v_fecha_local) NOT BETWEEN 1 AND 6
       OR EXTRACT(HOUR FROM v_fecha_local) < 9
       OR EXTRACT(HOUR FROM v_fecha_local) >= 18
       OR EXTRACT(MINUTE FROM v_fecha_local) <> 0
       OR EXTRACT(SECOND FROM v_fecha_local) <> 0 THEN
        RAISE EXCEPTION 'El horario solicitado está fuera del horario de atención';
    END IF;

    SELECT EXISTS (
        SELECT 1
        FROM citas_agenda
        WHERE conversation_id <> p_conversation_id
          AND estado IN ('pendiente_aprobacion', 'confirmada')
          AND fecha_hora_cita < (p_fecha_hora + INTERVAL '60 minutes')
          AND (fecha_hora_cita + INTERVAL '60 minutes') > p_fecha_hora
    ) INTO v_ocupado;
    IF v_ocupado THEN
        RAISE EXCEPTION 'El horario solicitado ya no está disponible';
    END IF;

    RETURN QUERY
    INSERT INTO citas_agenda (
        conversation_id,
        nombre_contacto,
        telefono,
        correo,
        nombre_empresa,
        objetivo_reunion,
        fecha_hora_cita,
        estado,
        updated_at
    )
    VALUES (
        p_conversation_id,
        p_nombre,
        p_telefono,
        p_correo,
        p_empresa,
        p_objetivo,
        p_fecha_hora,
        'pendiente_aprobacion',
        NOW()
    )
    ON CONFLICT (conversation_id)
    DO UPDATE SET
        nombre_contacto = EXCLUDED.nombre_contacto,
        telefono = EXCLUDED.telefono,
        correo = COALESCE(EXCLUDED.correo, citas_agenda.correo),
        nombre_empresa = COALESCE(EXCLUDED.nombre_empresa, citas_agenda.nombre_empresa),
        objetivo_reunion = COALESCE(EXCLUDED.objetivo_reunion, citas_agenda.objetivo_reunion),
        fecha_hora_cita = EXCLUDED.fecha_hora_cita,
        estado = 'pendiente_aprobacion',
        updated_at = NOW()
    WHERE citas_agenda.estado = 'pendiente_aprobacion'
    RETURNING citas_agenda.id, citas_agenda.conversation_id;
END;
$$;

-- 4. fn_actualizar_campo_cita
-- Actualiza un campo específico de la cita pendiente usando EXECUTE format(...). Solo si estado = 'pendiente_aprobacion'.
CREATE OR REPLACE FUNCTION fn_actualizar_campo_cita(
    p_conversation_id UUID,
    p_campo VARCHAR,
    p_valor TEXT
)
RETURNS BOOLEAN
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_col_name TEXT;
    v_sql TEXT;
    v_rows INT;
    v_fecha_cita TIMESTAMPTZ;
    v_fecha_local TIMESTAMP WITHOUT TIME ZONE;
BEGIN
    CASE lower(trim(p_campo))
        WHEN 'nombre_contacto', 'nombre_completo', 'nombre' THEN v_col_name := 'nombre_contacto';
        WHEN 'telefono', 'tel' THEN v_col_name := 'telefono';
        WHEN 'correo', 'email' THEN v_col_name := 'correo';
        WHEN 'nombre_empresa', 'empresa' THEN v_col_name := 'nombre_empresa';
        WHEN 'objetivo_reunion', 'objetivo' THEN v_col_name := 'objetivo_reunion';
        WHEN 'fecha_hora_cita', 'fecha_hora', 'fecha' THEN v_col_name := 'fecha_hora_cita';
        WHEN 'notas' THEN v_col_name := 'notas';
        ELSE
            RETURN FALSE;
    END CASE;

    IF v_col_name = 'fecha_hora_cita' THEN
        PERFORM pg_advisory_xact_lock(hashtext('citas_agenda_slots'));
        v_fecha_cita := p_valor::TIMESTAMPTZ;
        IF v_fecha_cita <= NOW() THEN
            RETURN FALSE;
        END IF;
        v_fecha_local := v_fecha_cita AT TIME ZONE 'America/Mexico_City';
        IF EXTRACT(ISODOW FROM v_fecha_local) NOT BETWEEN 1 AND 6
           OR EXTRACT(HOUR FROM v_fecha_local) < 9
           OR EXTRACT(HOUR FROM v_fecha_local) >= 18
           OR EXTRACT(MINUTE FROM v_fecha_local) <> 0
           OR EXTRACT(SECOND FROM v_fecha_local) <> 0
           OR EXISTS (
                SELECT 1
                FROM citas_agenda
                WHERE conversation_id <> p_conversation_id
                  AND estado IN ('pendiente_aprobacion', 'confirmada')
                  AND fecha_hora_cita < (v_fecha_cita + INTERVAL '60 minutes')
                  AND (fecha_hora_cita + INTERVAL '60 minutes') > v_fecha_cita
           ) THEN
            RETURN FALSE;
        END IF;

        v_sql := format(
            'UPDATE citas_agenda SET %I = $1::TIMESTAMPTZ, updated_at = NOW() WHERE conversation_id = $2 AND estado = ''pendiente_aprobacion''',
            v_col_name
        );
        EXECUTE v_sql USING v_fecha_cita, p_conversation_id;
    ELSE
        v_sql := format(
            'UPDATE citas_agenda SET %I = $1, updated_at = NOW() WHERE conversation_id = $2 AND estado = ''pendiente_aprobacion''',
            v_col_name
        );
        EXECUTE v_sql USING p_valor, p_conversation_id;
    END IF;

    GET DIAGNOSTICS v_rows = ROW_COUNT;
    RETURN v_rows > 0;
END;
$$;

CREATE OR REPLACE FUNCTION fn_tiene_cita_pendiente(p_conversation_id UUID)
RETURNS BOOLEAN
LANGUAGE sql
STABLE
SET search_path = public, pg_temp
AS $$
    SELECT EXISTS (
        SELECT 1
        FROM citas_agenda
        WHERE conversation_id = p_conversation_id
          AND estado = 'pendiente_aprobacion'
    );
$$;

-- 5. fn_confirmar_cita
-- SET estado = 'confirmada', updated_at = NOW()
CREATE OR REPLACE FUNCTION fn_confirmar_cita(p_conversation_id UUID)
RETURNS BOOLEAN
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_rows INT;
BEGIN
    UPDATE citas_agenda
    SET estado = 'confirmada', updated_at = NOW()
    WHERE conversation_id = p_conversation_id
      AND estado = 'pendiente_aprobacion';

    GET DIAGNOSTICS v_rows = ROW_COUNT;
    RETURN v_rows > 0;
END;
$$;

-- 6. fn_rechazar_cita
-- SET estado = 'rechazada', updated_at = NOW()
CREATE OR REPLACE FUNCTION fn_rechazar_cita(p_conversation_id UUID)
RETURNS BOOLEAN
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_rows INT;
BEGIN
    UPDATE citas_agenda
    SET estado = 'rechazada', updated_at = NOW()
    WHERE conversation_id = p_conversation_id
      AND estado = 'pendiente_aprobacion';

    GET DIAGNOSTICS v_rows = ROW_COUNT;
    RETURN v_rows > 0;
END;
$$;

COMMIT;