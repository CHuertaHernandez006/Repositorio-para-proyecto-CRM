-- ============================================================
-- BACKUP COMPLETO DE BASE DE DATOS - COMICenter
-- Laravel + SQLite
-- Generado: 2026-09-24 18:33:01
-- Estructura + datos
-- ============================================================

PRAGMA foreign_keys = OFF;

-- ============================================================
-- TABLA: cache
-- ============================================================

CREATE TABLE "cache" ("key" varchar not null, "value" text not null, "expiration" integer not null, primary key ("key"));

-- Sin registros.

-- ============================================================
-- TABLA: cache_locks
-- ============================================================

CREATE TABLE "cache_locks" ("key" varchar not null, "owner" varchar not null, "expiration" integer not null, primary key ("key"));

-- Sin registros.

-- ============================================================
-- TABLA: cartera_clientes
-- ============================================================

CREATE TABLE "cartera_clientes" ("id_cartera_cliente" integer primary key autoincrement not null, "id_empresa_crm" integer not null, "id_cliente" integer not null, "created_at" datetime, "updated_at" datetime, foreign key("id_empresa_crm") references "empresas"("id_empresa") on delete cascade, foreign key("id_cliente") references "clientes"("id_cliente") on delete cascade);

-- Datos de cartera_clientes
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (1, 1, 2, '2026-09-18 20:14:39', '2026-09-18 20:14:39');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (2, 1, 3, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (3, 1, 4, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (4, 1, 5, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (5, 1, 6, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (6, 1, 7, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (7, 1, 8, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (8, 1, 9, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (9, 1, 10, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (10, 1, 11, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (11, 1, 12, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (12, 1, 13, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (13, 1, 14, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (14, 1, 15, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (15, 1, 16, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (16, 1, 17, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (17, 1, 18, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (18, 1, 19, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (19, 1, 20, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (20, 1, 21, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (21, 1, 22, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (22, 1, 23, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (23, 1, 24, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (24, 1, 25, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (25, 1, 26, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (26, 1, 27, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (27, 1, 28, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (28, 1, 29, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (29, 1, 30, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (30, 1, 31, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (31, 1, 32, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (32, 1, 33, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (33, 1, 34, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (34, 1, 35, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (35, 1, 36, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (36, 1, 37, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (37, 1, 38, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (38, 1, 39, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (39, 1, 40, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (40, 1, 41, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (41, 1, 42, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (42, 1, 43, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (43, 1, 44, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (44, 1, 45, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (45, 1, 46, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (46, 1, 47, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (47, 1, 48, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (48, 1, 49, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (49, 1, 50, '2026-09-18 20:17:31', '2026-09-18 20:17:31');
INSERT INTO "cartera_clientes" ("id_cartera_cliente", "id_empresa_crm", "id_cliente", "created_at", "updated_at") VALUES (50, 1, 51, '2026-09-18 20:17:31', '2026-09-18 20:17:31');

-- ============================================================
-- TABLA: citas
-- ============================================================

CREATE TABLE "citas" ("id_cita" integer primary key autoincrement not null, "id_cliente" integer not null, "id_usuario" integer not null, "id_llamada" integer, "id_estado_cita" integer not null, "fecha_hora_inicio" datetime not null, "fecha_hora_fin" datetime, "motivo" varchar, "observaciones" text, foreign key("id_cliente") references "clientes"("id_cliente") on delete cascade, foreign key("id_usuario") references "users"("id") on delete cascade, foreign key("id_llamada") references "llamadas"("id_llamada") on delete set null, foreign key("id_estado_cita") references "estado_citas"("id_estado_cita") on delete restrict);

-- Datos de citas
INSERT INTO "citas" ("id_cita", "id_cliente", "id_usuario", "id_llamada", "id_estado_cita", "fecha_hora_inicio", "fecha_hora_fin", "motivo", "observaciones") VALUES (2, 32, 4, NULL, 1, '2026-09-25 16:50:00', NULL, 'sdfg', 'sdfg');
INSERT INTO "citas" ("id_cita", "id_cliente", "id_usuario", "id_llamada", "id_estado_cita", "fecha_hora_inicio", "fecha_hora_fin", "motivo", "observaciones") VALUES (3, 31, 9, NULL, 1, '2026-09-30 23:29:00', NULL, 'no sirve algo', 'no sirve y no puedo trabajar');

-- ============================================================
-- TABLA: clientes
-- ============================================================

CREATE TABLE "clientes" ("id_cliente" integer primary key autoincrement not null, "id_tipo_cliente" integer not null, "id_estado_lead" integer not null, "nombre" varchar not null, "apellido_paterno" varchar not null, "apellido_materno" varchar, "empresa" varchar, "telefono_principal" varchar not null, "telefono_secundario" varchar, "correo" varchar, "pais" varchar, "estado" varchar, "ciudad" varchar, "fuente" varchar, "fecha_registro" date not null default (CURRENT_DATE), "fecha_actualizacion" date not null default (CURRENT_DATE), "created_at" datetime, "updated_at" datetime, "id_empresa" integer, "id_empresa_cliente" integer, foreign key("id_empresa") references empresas("id_empresa") on delete set null on update no action, foreign key("id_empresa_cliente") references "empresas"("id_empresa") on delete set null);

-- Datos de clientes
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (1, 1, 4, 'ALAN  YAEL', 'ORTEGA', 'MENDOZA', 'beenear', '5526973808', NULL, 'Alanyolo237@gmail.com', 'Mexico', 'Michoacán', 'Antúnez (Morelos)', 'comi', '2026-09-17 19:16:51', '2026-09-17 19:16:51', NULL, NULL, 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (2, 1, 1, 'Mauricio', 'Salinas', NULL, 'Grupo Horizonte Capital', '5584731925', NULL, 'mauricio.salinas@grupohorizonte.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (3, 1, 1, 'Patricia', 'Navarro', NULL, 'Nexo Ventures', '5547192836', NULL, 'patricia.navarro@nexoventures.mx', 'Mexico', 'Estado de Mexico', 'Naucalpan', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (4, 1, 1, 'Ricardo', 'Campos', NULL, 'Vertex Labs', '5519284736', NULL, 'ricardo.campos@vertexlabs.mx', 'Mexico', 'CDMX', 'Cuauhtemoc', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (5, 1, 1, 'Andrea', 'Herrera', NULL, 'Altura Technologies', '5582147896', NULL, 'andrea.herrera@alturatech.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (6, 1, 1, 'Fernando', 'Rivas', NULL, 'Delta Analytics', '5578192345', NULL, 'fernando.rivas@deltaanalytics.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (7, 1, 1, 'Sofia', 'Molina', NULL, 'Impulso Capital', '5554127896', NULL, 'sofia.molina@impulsocapital.mx', 'Mexico', 'Sonora', 'Hermosillo', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (8, 1, 1, 'Daniel', 'Fuentes', NULL, 'Nova Digital', '5512356789', NULL, 'daniel.fuentes@novadigital.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (9, 1, 1, 'Valeria', 'Castro', NULL, 'Integra 360', '5534567891', NULL, 'valeria.castro@integra360.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (10, 1, 1, 'Hector', 'Ortega', NULL, 'Orion Consulting', '5576543210', NULL, 'hector.ortega@orionconsulting.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (11, 1, 1, 'Gabriela', 'Vega', NULL, 'Astra Corporate', '5589123456', NULL, 'gabriela.vega@astracorp.mx', 'Mexico', 'Queretaro', 'Queretaro', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (12, 1, 1, 'Luis', 'Santos', NULL, 'Grupo Evolucion', '5511456789', NULL, 'luis.santos@grupoevolucion.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (13, 1, 1, 'Ana', 'Cortes', NULL, 'Vertex Capital', '5522897412', NULL, 'ana.cortes@vertexcapital.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (14, 1, 1, 'Carlos', 'Pineda', NULL, 'Impulso Labs', '5587741234', NULL, 'carlos.pineda@impulsolabs.mx', 'Mexico', 'Puebla', 'Puebla', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (15, 1, 1, 'Mariana', 'Ramirez', NULL, 'Grupo Nexo', '5555567890', NULL, 'mariana.ramirez@gruponexo.mx', 'Mexico', 'Baja California', 'Tijuana', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (16, 1, 1, 'Roberto', 'Lopez', NULL, 'Orion Ventures', '5522223333', NULL, 'roberto.lopez@orionventures.mx', 'Mexico', 'Nuevo Leon', 'San Pedro Garza Garcia', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (17, 1, 1, 'Claudia', 'Fuentes', NULL, 'Integra Pharma', '5571112222', NULL, 'claudia.fuentes@integrapharma.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (18, 1, 1, 'Jorge', 'Vargas', NULL, 'Nova Retail', '5512349999', NULL, 'jorge.vargas@novaretail.mx', 'Mexico', 'Guanajuato', 'Leon', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (19, 1, 1, 'Laura', 'Campos', NULL, 'Delta Fintech', '5578901234', NULL, 'laura.campos@deltafintech.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (20, 1, 1, 'Victor', 'Ramirez', NULL, 'Vertix Logistics', '5556781234', NULL, 'victor.ramirez@vertixlogistics.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (21, 1, 1, 'Monica', 'Herrera', NULL, 'Altura Health', '5519872345', NULL, 'monica.herrera@alturahealth.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (22, 1, 1, 'Rafael', 'Navarro', NULL, 'Blue Core', '5588881122', NULL, 'rafael.navarro@bluecore.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (23, 1, 1, 'Carmen', 'Ortega', NULL, 'Zenith Group', '5577772211', NULL, 'carmen.ortega@zenithgroup.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (24, 1, 1, 'Diego', 'Mendoza', NULL, 'Orbita Technologies', '5511112233', NULL, 'diego.mendoza@orbita.mx', 'Mexico', 'Yucatan', 'Merida', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (25, 1, 1, 'Teresa', 'Paredes', NULL, 'Horizon Legal', '5544445678', NULL, 'teresa.paredes@horizon.mx', 'Mexico', 'CDMX', 'CDMX', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (26, 1, 1, 'Raul', 'Silva', NULL, 'Innova Labs', '5566667890', NULL, 'raul.silva@innova.mx', 'Mexico', 'Puebla', 'Puebla', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (27, 1, 1, 'Susana', 'Martinez', NULL, 'Vortex Media', '5533321122', NULL, 'susana.martinez@vortex.mx', 'Mexico', 'Queretaro', 'Queretaro', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (28, 1, 1, 'Alberto', 'Garcia', NULL, 'TecForce', '5545678901', NULL, 'alberto.garcia@tecforce.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (29, 1, 1, 'Paola', 'Guzman', NULL, 'Capital Edge', '5523459876', NULL, 'paola.guzman@capitaledge.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (30, 1, 1, 'Martin', 'Castro', NULL, 'LogiSync', '5511239988', NULL, 'martin.castro@logisync.mx', 'Mexico', 'Baja California', 'Tijuana', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (31, 1, 1, 'Elena', 'Diaz', NULL, 'NextBridge', '5578002233', NULL, 'elena.diaz@nextbridge.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (32, 1, 1, 'Gerardo', 'Salazar', NULL, 'Quantum Systems', '5519988776', NULL, 'gerardo.salazar@quantum.mx', 'Mexico', 'Guanajuato', 'Leon', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (33, 1, 1, 'Lucia', 'Reyes', NULL, 'OpenMind', '5567665544', NULL, 'lucia.reyes@openmind.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (34, 1, 1, 'Mario', 'Lozano', NULL, 'LinkPro', '5534561234', NULL, 'mario.lozano@linkpro.mx', 'Mexico', 'Queretaro', 'Queretaro', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (35, 1, 1, 'Daniela', 'Rocha', NULL, 'Urbania Group', '5588123456', NULL, 'daniela.rocha@urbania.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (36, 1, 1, 'Sergio', 'Cruz', NULL, 'Adaptive Ventures', '5522123434', NULL, 'sergio.cruz@adaptive.mx', 'Mexico', 'Puebla', 'Puebla', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (37, 1, 1, 'Patricia', 'Velasco', NULL, 'Evolve Partners', '5571234567', NULL, 'patricia.velasco@evolve.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (38, 1, 1, 'Rene', 'Mejia', NULL, 'Trion Capital', '5544332211', NULL, 'rene.mejia@trion.mx', 'Mexico', 'CDMX', 'CDMX', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (39, 1, 1, 'Karla', 'Luna', NULL, 'Bridge Corp', '5566554433', NULL, 'karla.luna@bridgecorp.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (40, 1, 1, 'Oscar', 'Ponce', NULL, 'Zenware', '5599112233', NULL, 'oscar.ponce@zenware.mx', 'Mexico', 'Baja California', 'Tijuana', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (41, 1, 1, 'Irene', 'Morales', NULL, 'Global One', '5533445566', NULL, 'irene.morales@globalone.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (42, 1, 1, 'Adrian', 'Flores', NULL, 'NovaLink', '5588012345', NULL, 'adrian.flores@novalink.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (43, 1, 1, 'Beatriz', 'Soto', NULL, 'Intelliga', '5576547890', NULL, 'beatriz.soto@intelliga.mx', 'Mexico', 'Queretaro', 'Queretaro', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:32', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (44, 1, 1, 'Guillermo', 'Nuñez', NULL, 'Orion Software', '5567893210', NULL, 'guillermo.nunez@orionsoft.mx', 'Mexico', 'CDMX', 'CDMX', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (45, 1, 1, 'Natalia', 'Suarez', NULL, 'Axion Group', '5514567899', NULL, 'natalia.suarez@axion.mx', 'Mexico', 'Yucatan', 'Merida', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (46, 1, 1, 'Francisco', 'Vega', NULL, 'Metrix Solutions', '5587123450', NULL, 'francisco.vega@metrix.mx', 'Mexico', 'Puebla', 'Puebla', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (47, 1, 1, 'Veronica', 'Rios', NULL, 'ClearView', '5512121212', NULL, 'veronica.rios@clearview.mx', 'Mexico', 'Nuevo Leon', 'Monterrey', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (48, 1, 1, 'Enrique', 'Torres', NULL, 'PrimeCore', '5577332211', NULL, 'enrique.torres@primecore.mx', 'Mexico', 'CDMX', 'Mexico City', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (49, 1, 1, 'Silvia', 'Aranda', NULL, 'SmartBridge', '5555667788', NULL, 'silvia.aranda@smartbridge.mx', 'Mexico', 'Guanajuato', 'Leon', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (50, 1, 1, 'Javier', 'Cervantes', NULL, 'AlphaTech', '5544778899', NULL, 'javier.cervantes@alphatech.mx', 'Mexico', 'Jalisco', 'Guadalajara', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);
INSERT INTO "clientes" ("id_cliente", "id_tipo_cliente", "id_estado_lead", "nombre", "apellido_paterno", "apellido_materno", "empresa", "telefono_principal", "telefono_secundario", "correo", "pais", "estado", "ciudad", "fuente", "fecha_registro", "fecha_actualizacion", "created_at", "updated_at", "id_empresa", "id_empresa_cliente") VALUES (51, 1, 1, 'Marisol', 'Rendon', NULL, 'Future Labs', '5533112244', NULL, 'marisol.rendon@futurelabs.mx', 'Mexico', 'CDMX', 'CDMX', 'Importación CSV Demo', '2026-09-18', '2026-09-18', '2026-09-18 18:49:33', '2026-09-18 21:01:53', 1, NULL);

-- ============================================================
-- TABLA: empresas
-- ============================================================

CREATE TABLE "empresas" ("id_empresa" integer primary key autoincrement not null, "nombre" varchar not null, "slug" varchar not null, "estado" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime);

-- Datos de empresas
INSERT INTO "empresas" ("id_empresa", "nombre", "slug", "estado", "created_at", "updated_at") VALUES (1, 'AlanTech Solutions, S.A. de C.V.', 'alantech-solutions', 1, '2026-09-17 18:29:45', '2026-09-17 18:29:45');
INSERT INTO "empresas" ("id_empresa", "nombre", "slug", "estado", "created_at", "updated_at") VALUES (53, 'CharlyTech Solutions, S.A. de C.V.', 'charlytech-solutions', 1, '2026-09-18 21:04:14', '2026-09-18 21:04:14');

-- ============================================================
-- TABLA: estado_citas
-- ============================================================

CREATE TABLE "estado_citas" ("id_estado_cita" integer primary key autoincrement not null, "nombre" varchar not null, "descripcion" varchar, "estado" tinyint(1) not null default '1', "fecha_fin" date);

-- Datos de estado_citas
INSERT INTO "estado_citas" ("id_estado_cita", "nombre", "descripcion", "estado", "fecha_fin") VALUES (1, 'Pendiente', 'La cita está programada pero aún no se ha realizado.', 1, NULL);
INSERT INTO "estado_citas" ("id_estado_cita", "nombre", "descripcion", "estado", "fecha_fin") VALUES (2, 'Confirmada', 'La cita fue confirmada por el cliente.', 1, NULL);
INSERT INTO "estado_citas" ("id_estado_cita", "nombre", "descripcion", "estado", "fecha_fin") VALUES (3, 'Realizada', 'La cita ya se llevó a cabo.', 1, NULL);
INSERT INTO "estado_citas" ("id_estado_cita", "nombre", "descripcion", "estado", "fecha_fin") VALUES (4, 'Cancelada', 'La cita fue cancelada.', 1, NULL);

-- ============================================================
-- TABLA: failed_jobs
-- ============================================================

CREATE TABLE "failed_jobs" ("id" integer primary key autoincrement not null, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime not null default CURRENT_TIMESTAMP);

-- Sin registros.

-- ============================================================
-- TABLA: job_batches
-- ============================================================

CREATE TABLE "job_batches" ("id" varchar not null, "name" varchar not null, "total_jobs" integer not null, "pending_jobs" integer not null, "failed_jobs" integer not null, "failed_job_ids" text not null, "options" text, "cancelled_at" integer, "created_at" integer not null, "finished_at" integer, primary key ("id"));

-- Sin registros.

-- ============================================================
-- TABLA: jobs
-- ============================================================

CREATE TABLE "jobs" ("id" integer primary key autoincrement not null, "queue" varchar not null, "payload" text not null, "attempts" integer not null, "reserved_at" integer, "available_at" integer not null, "created_at" integer not null);

-- Sin registros.

-- ============================================================
-- TABLA: llamadas
-- ============================================================

CREATE TABLE "llamadas" ("id_llamada" integer primary key autoincrement not null, "id_cliente" integer not null, "id_usuario" integer not null, "fecha_hora_inicio" datetime not null, "fecha_hora_fin" datetime, "resultado" varchar, "observaciones" text, foreign key("id_cliente") references "clientes"("id_cliente") on delete cascade, foreign key("id_usuario") references "users"("id") on delete cascade);

-- Sin registros.

-- ============================================================
-- TABLA: migrations
-- ============================================================

CREATE TABLE "migrations" ("id" integer primary key autoincrement not null, "migration" varchar not null, "batch" integer not null);

-- Datos de migrations
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (1, '0001_01_01_000000_create_users_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (2, '0001_01_01_000001_create_cache_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (3, '0001_01_01_000002_create_jobs_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (4, '2026_09_03_202052_create_pruebas_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (5, '2026_09_15_051942_create_roles_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (6, '2026_09_15_054816_create_clientes_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (7, '2026_09_16_091205_create_empresas_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (8, '2026_09_16_091520_add_id_empresa_to_usuarios_table', 1);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (9, '2026_09_17_182347_add_estado_to_users_table', 2);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (10, '2026_09_17_191526_add_id_empresa_to_clientes_table', 3);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (11, '2026_09_18_200629_create_cartera_clientes_table', 4);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (12, '2026_09_18_201955_add_id_empresa_cliente_to_clientes_table', 5);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (13, '2026_09_21_184903_create_citas_table', 6);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (14, '2026_09_21_185117_create_estado_citas_table', 7);
INSERT INTO "migrations" ("id", "migration", "batch") VALUES (15, '2026_09_21_212558_create_llamadas_table', 8);

-- ============================================================
-- TABLA: password_reset_tokens
-- ============================================================

CREATE TABLE "password_reset_tokens" ("email" varchar not null, "token" varchar not null, "created_at" datetime, primary key ("email"));

-- Sin registros.

-- ============================================================
-- TABLA: pruebas
-- ============================================================

CREATE TABLE "pruebas" ("id" integer primary key autoincrement not null, "correo" varchar not null, "password" varchar not null, "created_at" datetime, "updated_at" datetime);

-- Sin registros.

-- ============================================================
-- TABLA: roles
-- ============================================================

CREATE TABLE "roles" ("id_rol" integer primary key autoincrement not null, "nombre" varchar not null, "descripcion" varchar, "estado" tinyint(1) not null default '1', "created_at" datetime, "updated_at" datetime);

-- Datos de roles
INSERT INTO "roles" ("id_rol", "nombre", "descripcion", "estado", "created_at", "updated_at") VALUES (1, 'Administrador', 'Administrador del sistema', 1, '2026-09-17 17:51:25', '2026-09-17 17:51:25');
INSERT INTO "roles" ("id_rol", "nombre", "descripcion", "estado", "created_at", "updated_at") VALUES (2, 'Supervisor', 'Supervisor del sistema', 1, '2026-09-17 17:54:43', '2026-09-17 17:54:43');

-- ============================================================
-- TABLA: sessions
-- ============================================================

CREATE TABLE "sessions" ("id" varchar not null, "user_id" integer, "ip_address" varchar, "user_agent" text, "payload" text not null, "last_activity" integer not null, primary key ("id"));

-- Datos de sessions
INSERT INTO "sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES ('13rZGVzX2RWuu1PJbVLCyVyWPR0L9s27kzEqTvJf', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiMGhBcEtTYUc2aFQ1Y0ZPZnFZS1RFV2NsdHhCSHlqZ280MFpVZFJFRSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jbGllbnRlcy80MC9lZGl0YXIiO3M6NToicm91dGUiO3M6MTM6ImNsaWVudGVzLmVkaXQiO31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToyO30=', 1790196226);
INSERT INTO "sessions" ("id", "user_id", "ip_address", "user_agent", "payload", "last_activity") VALUES ('ird55dhdHDPXC5FHbzHTnViXrfRpfumnOJFRs24O', 2, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36 Edg/153.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiakYwZkxKYW1PdkE3MXZLcm5VT3NVcDl6V1NYU3B2S1lsOTA5WnFWUCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jaXRhcy8yL2VkaXRhciI7czo1OiJyb3V0ZSI7czoxMDoiY2l0YXMuZWRpdCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjI7fQ==', 1790273899);

-- ============================================================
-- TABLA: users
-- ============================================================

CREATE TABLE "users" ("id" integer primary key autoincrement not null, "name" varchar not null, "email" varchar not null, "email_verified_at" datetime, "password" varchar not null, "id_rol" integer not null, "remember_token" varchar, "created_at" datetime, "updated_at" datetime, "id_empresa" integer, "estado" tinyint(1) not null default '1', foreign key("id_empresa") references "empresas"("id_empresa") on delete cascade);

-- Datos de users
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "id_rol", "remember_token", "created_at", "updated_at", "id_empresa", "estado") VALUES (1, 'Test User', 'test@example.com', NULL, '$2y$12$zMSgC/sohsiU.xguqOWUYuyDyPRnDGz65SJb46OFcyA9zEm.5s3Cq', 1, NULL, '2026-09-17 17:51:32', '2026-09-17 17:51:32', NULL, 1);
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "id_rol", "remember_token", "created_at", "updated_at", "id_empresa", "estado") VALUES (2, 'Supervisor', 'supervisor@example.com', NULL, '$2y$12$sLI6uqpy0gEhEbVFxpN37O9w.eQB9xRdYt7vKnsmvZjWgjSRFEeoi', 2, NULL, '2026-09-17 17:54:58', '2026-09-17 18:30:03', 1, 1);
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "id_rol", "remember_token", "created_at", "updated_at", "id_empresa", "estado") VALUES (4, 'Yael', 'alanyael@outlook.com', NULL, '$2y$12$4Z3jukL2xXWTYQwnYKUzc.x6l9hRQeoukKWlwftSVMlwEi5Tzkb9e', 3, NULL, '2026-09-17 18:30:45', '2026-09-23 17:21:01', 1, 0);
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "id_rol", "remember_token", "created_at", "updated_at", "id_empresa", "estado") VALUES (7, 'Admin CharlyTech', 'admin@charlytech.mx', NULL, '$2y$12$TiOKXNzmHe.NDEEfLadW3OKtU6LZgOvC6S1EN.K//ge884CkDYua6', 2, NULL, '2026-09-18 21:04:28', '2026-09-18 21:04:28', 53, 1);
INSERT INTO "users" ("id", "name", "email", "email_verified_at", "password", "id_rol", "remember_token", "created_at", "updated_at", "id_empresa", "estado") VALUES (9, 'Charly Huerta Hernandez', 'carlos@gmail.com', NULL, '$2y$12$DWExSe4RNv5AkvAM0xbJlORB9fASPmgQ8HdUi5puR1LbBEt.ZVkgO', 3, NULL, '2026-09-23 17:23:20', '2026-09-24 18:04:31', 1, 1);

-- ============================================================
-- ÍNDICES
-- ============================================================

CREATE INDEX "cache_expiration_index" on "cache" ("expiration");
CREATE INDEX "cache_locks_expiration_index" on "cache_locks" ("expiration");
CREATE UNIQUE INDEX "cartera_clientes_id_empresa_crm_id_cliente_unique" on "cartera_clientes" ("id_empresa_crm", "id_cliente");
CREATE UNIQUE INDEX "empresas_slug_unique" on "empresas" ("slug");
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");
CREATE INDEX "jobs_queue_index" on "jobs" ("queue");
CREATE UNIQUE INDEX "pruebas_correo_unique" on "pruebas" ("correo");
CREATE INDEX "sessions_last_activity_index" on "sessions" ("last_activity");
CREATE INDEX "sessions_user_id_index" on "sessions" ("user_id");
CREATE UNIQUE INDEX "users_email_unique" on "users" ("email");

PRAGMA foreign_keys = ON;
