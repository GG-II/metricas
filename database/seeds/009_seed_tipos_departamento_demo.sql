-- =============================================
-- Seed: Datos de Prueba para Tipos de Departamento
-- Cooperativa con Agencias, Corporativo y Global
-- =============================================

-- =============================================
-- 1. CREAR AGENCIAS
-- =============================================

INSERT INTO departamentos (nombre, descripcion, tipo, color, icono, activo, orden) VALUES
('Agencia San José', 'Agencia Central - Oficina Principal', 'agencia', '#10b981', 'building-bank', 1, 1),
('Agencia Heredia', 'Agencia Norte - Zona Urbana', 'agencia', '#059669', 'building-bank', 1, 2),
('Agencia Cartago', 'Agencia Este - Valle Central', 'agencia', '#047857', 'building-bank', 1, 3),
('Agencia Alajuela', 'Agencia Oeste - Aeropuerto', 'agencia', '#065f46', 'building-bank', 1, 4);

-- Obtener IDs de las agencias
SET @agencia_sj = (SELECT id FROM departamentos WHERE nombre = 'Agencia San José');
SET @agencia_heredia = (SELECT id FROM departamentos WHERE nombre = 'Agencia Heredia');
SET @agencia_cartago = (SELECT id FROM departamentos WHERE nombre = 'Agencia Cartago');
SET @agencia_alajuela = (SELECT id FROM departamentos WHERE nombre = 'Agencia Alajuela');

-- =============================================
-- 2. CREAR ÁREAS OPERATIVAS EN AGENCIAS
-- =============================================

-- Áreas para Agencia San José
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(@agencia_sj, 'Caja', 'caja-sj', 'Operaciones de caja y atención al público', '#10b981', 'cash', 1, 1),
(@agencia_sj, 'Créditos', 'creditos-sj', 'Colocación y gestión de créditos', '#3b82f6', 'coin', 1, 2),
(@agencia_sj, 'Cuentas Nuevas', 'cuentas-nuevas-sj', 'Apertura de cuentas y asociados nuevos', '#8b5cf6', 'user-plus', 1, 3),
(@agencia_sj, 'Atención Cliente', 'atencion-sj', 'Servicio al cliente y consultas', '#f59e0b', 'help', 1, 4);

-- Áreas para Agencia Heredia
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(@agencia_heredia, 'Caja', 'caja-heredia', 'Operaciones de caja y atención al público', '#10b981', 'cash', 1, 1),
(@agencia_heredia, 'Créditos', 'creditos-heredia', 'Colocación y gestión de créditos', '#3b82f6', 'coin', 1, 2),
(@agencia_heredia, 'Cuentas Nuevas', 'cuentas-nuevas-heredia', 'Apertura de cuentas y asociados nuevos', '#8b5cf6', 'user-plus', 1, 3);

-- Áreas para Agencia Cartago
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(@agencia_cartago, 'Caja', 'caja-cartago', 'Operaciones de caja y atención al público', '#10b981', 'cash', 1, 1),
(@agencia_cartago, 'Créditos', 'creditos-cartago', 'Colocación y gestión de créditos', '#3b82f6', 'coin', 1, 2),
(@agencia_cartago, 'Cuentas Nuevas', 'cuentas-nuevas-cartago', 'Apertura de cuentas y asociados nuevos', '#8b5cf6', 'user-plus', 1, 3);

-- Áreas para Agencia Alajuela
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(@agencia_alajuela, 'Caja', 'caja-alajuela', 'Operaciones de caja y atención al público', '#10b981', 'cash', 1, 1),
(@agencia_alajuela, 'Créditos', 'creditos-alajuela', 'Colocación y gestión de créditos', '#3b82f6', 'coin', 1, 2);

-- =============================================
-- 3. CREAR ÁREAS GLOBALES ADICIONALES
-- =============================================

SET @dept_global = (SELECT id FROM departamentos WHERE tipo = 'global' LIMIT 1);

-- Crear áreas globales para organizar métricas consolidadas
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(@dept_global, 'Métricas Financieras', 'metricas-financieras', 'Consolidado de métricas financieras de toda la cooperativa', '#8b5cf6', 'currency-dollar', 1, 1),
(@dept_global, 'Métricas Comerciales', 'metricas-comerciales', 'Consolidado de métricas comerciales y ventas', '#ec4899', 'shopping-cart', 1, 2),
(@dept_global, 'Métricas Operativas', 'metricas-operativas', 'Consolidado de métricas de eficiencia operacional', '#f59e0b', 'settings', 1, 3),
(@dept_global, 'KPIs Estratégicos', 'kpis-estrategicos', 'Indicadores clave de desempeño ejecutivo', '#ef4444', 'chart-dots-3', 1, 4);

-- =============================================
-- 4. CREAR MÉTRICAS EN AGENCIAS
-- =============================================

-- Obtener IDs de áreas de agencias
SET @area_creditos_sj = (SELECT id FROM areas WHERE slug = 'creditos-sj');
SET @area_creditos_heredia = (SELECT id FROM areas WHERE slug = 'creditos-heredia');
SET @area_creditos_cartago = (SELECT id FROM areas WHERE slug = 'creditos-cartago');
SET @area_creditos_alajuela = (SELECT id FROM areas WHERE slug = 'creditos-alajuela');

SET @area_cuentas_sj = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-sj');
SET @area_cuentas_heredia = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-heredia');
SET @area_cuentas_cartago = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-cartago');

-- Métricas de Créditos en cada agencia
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, orden, activo) VALUES
-- San José
(@area_creditos_sj, 'Colocación de Créditos', 'colocacion-creditos-sj', 'Monto total de créditos colocados en el mes', 'Millones ₡', 'decimal', 'coin', 1, 1),
(@area_creditos_sj, 'Incremento Cartera', 'incremento-cartera-sj', 'Incremento en la cartera de créditos', 'Millones ₡', 'decimal', 'trending-up', 2, 1),
(@area_creditos_sj, 'Cantidad Créditos', 'cantidad-creditos-sj', 'Número de créditos otorgados', 'Créditos', 'numero', 'list-numbers', 3, 1),

-- Heredia
(@area_creditos_heredia, 'Colocación de Créditos', 'colocacion-creditos-heredia', 'Monto total de créditos colocados en el mes', 'Millones ₡', 'decimal', 'coin', 1, 1),
(@area_creditos_heredia, 'Incremento Cartera', 'incremento-cartera-heredia', 'Incremento en la cartera de créditos', 'Millones ₡', 'decimal', 'trending-up', 2, 1),
(@area_creditos_heredia, 'Cantidad Créditos', 'cantidad-creditos-heredia', 'Número de créditos otorgados', 'Créditos', 'numero', 'list-numbers', 3, 1),

-- Cartago
(@area_creditos_cartago, 'Colocación de Créditos', 'colocacion-creditos-cartago', 'Monto total de créditos colocados en el mes', 'Millones ₡', 'decimal', 'coin', 1, 1),
(@area_creditos_cartago, 'Incremento Cartera', 'incremento-cartera-cartago', 'Incremento en la cartera de créditos', 'Millones ₡', 'decimal', 'trending-up', 2, 1),
(@area_creditos_cartago, 'Cantidad Créditos', 'cantidad-creditos-cartago', 'Número de créditos otorgados', 'Créditos', 'numero', 'list-numbers', 3, 1),

-- Alajuela
(@area_creditos_alajuela, 'Colocación de Créditos', 'colocacion-creditos-alajuela', 'Monto total de créditos colocados en el mes', 'Millones ₡', 'decimal', 'coin', 1, 1),
(@area_creditos_alajuela, 'Incremento Cartera', 'incremento-cartera-alajuela', 'Incremento en la cartera de créditos', 'Millones ₡', 'decimal', 'trending-up', 2, 1);

-- Métricas de Cuentas Nuevas
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, orden, activo) VALUES
-- San José
(@area_cuentas_sj, 'Asociados Nuevos', 'asociados-nuevos-sj', 'Cantidad de asociados nuevos captados', 'Asociados', 'numero', 'user-plus', 1, 1),
(@area_cuentas_sj, 'Tarjetas Débito', 'tarjetas-debito-sj', 'Tarjetas de débito emitidas', 'Tarjetas', 'numero', 'credit-card', 2, 1),
(@area_cuentas_sj, 'CIC Virtual', 'cic-virtual-sj', 'Usuarios registrados en CIC Virtual', 'Usuarios', 'numero', 'device-mobile', 3, 1),

-- Heredia
(@area_cuentas_heredia, 'Asociados Nuevos', 'asociados-nuevos-heredia', 'Cantidad de asociados nuevos captados', 'Asociados', 'numero', 'user-plus', 1, 1),
(@area_cuentas_heredia, 'Tarjetas Débito', 'tarjetas-debito-heredia', 'Tarjetas de débito emitidas', 'Tarjetas', 'numero', 'credit-card', 2, 1),

-- Cartago
(@area_cuentas_cartago, 'Asociados Nuevos', 'asociados-nuevos-cartago', 'Cantidad de asociados nuevos captados', 'Asociados', 'numero', 'user-plus', 1, 1),
(@area_cuentas_cartago, 'Tarjetas Débito', 'tarjetas-debito-cartago', 'Tarjetas de débito emitidas', 'Tarjetas', 'numero', 'credit-card', 2, 1);

-- =============================================
-- 5. CREAR MÉTRICAS CALCULADAS GLOBALES
-- =============================================

SET @area_financieras = (SELECT id FROM areas WHERE slug = 'metricas-financieras');
SET @area_comerciales = (SELECT id FROM areas WHERE slug = 'metricas-comerciales');

-- Métricas calculadas globales - Financieras
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, orden, activo) VALUES
(@area_financieras, 'Total Colocación Créditos', 'total-colocacion-creditos', 'Suma de colocación de todas las agencias', 'Millones ₡', 'decimal', 'coin', 1, 1, 1),
(@area_financieras, 'Total Incremento Cartera', 'total-incremento-cartera', 'Suma del incremento de cartera de todas las agencias', 'Millones ₡', 'decimal', 'trending-up', 1, 2, 1);

-- Métricas calculadas globales - Comerciales
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, orden, activo) VALUES
(@area_comerciales, 'Total Asociados Nuevos', 'total-asociados-nuevos', 'Suma de asociados nuevos de todas las agencias', 'Asociados', 'numero', 'user-plus', 1, 1, 1),
(@area_comerciales, 'Total Tarjetas Débito', 'total-tarjetas-debito', 'Suma de tarjetas de débito emitidas', 'Tarjetas', 'numero', 'credit-card', 1, 2, 1);

-- =============================================
-- 6. CONFIGURAR COMPONENTES DE MÉTRICAS CALCULADAS
-- =============================================

-- Total Colocación Créditos = suma de colocación de todas las agencias
SET @metrica_total_colocacion = (SELECT id FROM metricas WHERE slug = 'total-colocacion-creditos');
SET @metrica_colocacion_sj = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-sj');
SET @metrica_colocacion_heredia = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-heredia');
SET @metrica_colocacion_cartago = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-cartago');
SET @metrica_colocacion_alajuela = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-alajuela');

INSERT INTO metricas_componentes (metrica_calculada_id, metrica_componente_id, operacion, orden, activo) VALUES
(@metrica_total_colocacion, @metrica_colocacion_sj, 'suma', 0, 1),
(@metrica_total_colocacion, @metrica_colocacion_heredia, 'suma', 1, 1),
(@metrica_total_colocacion, @metrica_colocacion_cartago, 'suma', 2, 1),
(@metrica_total_colocacion, @metrica_colocacion_alajuela, 'suma', 3, 1);

-- Total Incremento Cartera
SET @metrica_total_incremento = (SELECT id FROM metricas WHERE slug = 'total-incremento-cartera');
SET @metrica_incremento_sj = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-sj');
SET @metrica_incremento_heredia = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-heredia');
SET @metrica_incremento_cartago = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-cartago');
SET @metrica_incremento_alajuela = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-alajuela');

INSERT INTO metricas_componentes (metrica_calculada_id, metrica_componente_id, operacion, orden, activo) VALUES
(@metrica_total_incremento, @metrica_incremento_sj, 'suma', 0, 1),
(@metrica_total_incremento, @metrica_incremento_heredia, 'suma', 1, 1),
(@metrica_total_incremento, @metrica_incremento_cartago, 'suma', 2, 1),
(@metrica_total_incremento, @metrica_incremento_alajuela, 'suma', 3, 1);

-- Total Asociados Nuevos
SET @metrica_total_asociados = (SELECT id FROM metricas WHERE slug = 'total-asociados-nuevos');
SET @metrica_asociados_sj = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-sj');
SET @metrica_asociados_heredia = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-heredia');
SET @metrica_asociados_cartago = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-cartago');

INSERT INTO metricas_componentes (metrica_calculada_id, metrica_componente_id, operacion, orden, activo) VALUES
(@metrica_total_asociados, @metrica_asociados_sj, 'suma', 0, 1),
(@metrica_total_asociados, @metrica_asociados_heredia, 'suma', 1, 1),
(@metrica_total_asociados, @metrica_asociados_cartago, 'suma', 2, 1);

-- Total Tarjetas Débito
SET @metrica_total_tarjetas = (SELECT id FROM metricas WHERE slug = 'total-tarjetas-debito');
SET @metrica_tarjetas_sj = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-sj');
SET @metrica_tarjetas_heredia = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-heredia');
SET @metrica_tarjetas_cartago = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-cartago');

INSERT INTO metricas_componentes (metrica_calculada_id, metrica_componente_id, operacion, orden, activo) VALUES
(@metrica_total_tarjetas, @metrica_tarjetas_sj, 'suma', 0, 1),
(@metrica_total_tarjetas, @metrica_tarjetas_heredia, 'suma', 1, 1),
(@metrica_total_tarjetas, @metrica_tarjetas_cartago, 'suma', 2, 1);

-- =============================================
-- 7. CREAR VALORES DE EJEMPLO (Período actual)
-- =============================================

-- Obtener período actual (último período activo)
SET @periodo_actual = (SELECT id FROM periodos WHERE activo = 1 ORDER BY ejercicio DESC, periodo DESC LIMIT 1);

-- Si no existe período, crear uno de prueba
INSERT INTO periodos (ejercicio, periodo, nombre, fecha_inicio, fecha_fin, activo)
SELECT 2026, 4, 'Abril 2026', '2026-04-01', '2026-04-30', 1
WHERE NOT EXISTS (SELECT 1 FROM periodos WHERE ejercicio = 2026 AND periodo = 4);

SET @periodo_actual = (SELECT id FROM periodos WHERE ejercicio = 2026 AND periodo = 4);

-- Valores de Colocación de Créditos (en millones)
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_decimal, nota) VALUES
(@metrica_colocacion_sj, @periodo_actual, 45.5, 'Abril 2026'),
(@metrica_colocacion_heredia, @periodo_actual, 32.8, 'Abril 2026'),
(@metrica_colocacion_cartago, @periodo_actual, 28.3, 'Abril 2026'),
(@metrica_colocacion_alajuela, @periodo_actual, 23.7, 'Abril 2026');

-- Valores de Incremento Cartera (en millones)
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_decimal, nota) VALUES
(@metrica_incremento_sj, @periodo_actual, 22.5, 'Abril 2026'),
(@metrica_incremento_heredia, @periodo_actual, 15.2, 'Abril 2026'),
(@metrica_incremento_cartago, @periodo_actual, 12.8, 'Abril 2026'),
(@metrica_incremento_alajuela, @periodo_actual, 10.3, 'Abril 2026');

-- Valores de Cantidad de Créditos
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
((SELECT id FROM metricas WHERE slug = 'cantidad-creditos-sj'), @periodo_actual, 285, 'Abril 2026'),
((SELECT id FROM metricas WHERE slug = 'cantidad-creditos-heredia'), @periodo_actual, 198, 'Abril 2026'),
((SELECT id FROM metricas WHERE slug = 'cantidad-creditos-cartago'), @periodo_actual, 167, 'Abril 2026');

-- Valores de Asociados Nuevos
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
(@metrica_asociados_sj, @periodo_actual, 342, 'Abril 2026'),
(@metrica_asociados_heredia, @periodo_actual, 287, 'Abril 2026'),
(@metrica_asociados_cartago, @periodo_actual, 221, 'Abril 2026');

-- Valores de Tarjetas Débito
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
(@metrica_tarjetas_sj, @periodo_actual, 189, 'Abril 2026'),
(@metrica_tarjetas_heredia, @periodo_actual, 152, 'Abril 2026'),
(@metrica_tarjetas_cartago, @periodo_actual, 134, 'Abril 2026');

-- Valores de CIC Virtual
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
((SELECT id FROM metricas WHERE slug = 'cic-virtual-sj'), @periodo_actual, 256, 'Abril 2026');

-- =============================================
-- 8. CALCULAR VALORES DE MÉTRICAS GLOBALES
-- =============================================

-- Total Colocación (suma automática)
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_decimal, nota) VALUES
(@metrica_total_colocacion, @periodo_actual, 130.3, 'Calculado: 45.5 + 32.8 + 28.3 + 23.7');

-- Total Incremento Cartera
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_decimal, nota) VALUES
(@metrica_total_incremento, @periodo_actual, 60.8, 'Calculado: 22.5 + 15.2 + 12.8 + 10.3');

-- Total Asociados Nuevos
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
(@metrica_total_asociados, @periodo_actual, 850, 'Calculado: 342 + 287 + 221');

-- Total Tarjetas Débito
INSERT INTO valores_metricas (metrica_id, periodo_id, valor_numero, nota) VALUES
(@metrica_total_tarjetas, @periodo_actual, 475, 'Calculado: 189 + 152 + 134');

-- =============================================
-- 9. CREAR METAS GLOBALES (basadas en imagen)
-- =============================================

-- Metas para métricas globales financieras
INSERT INTO metas_metricas (metrica_id, periodo_id, valor_objetivo, tipo_comparacion) VALUES
-- Meta anual dividida por 12 meses
(@metrica_total_colocacion, @periodo_actual, 81.67, 'mayor_igual'), -- 980M / 12 = 81.67M por mes
(@metrica_total_incremento, @periodo_actual, 40.83, 'mayor_igual'); -- 490M / 12 = 40.83M por mes

-- Metas para métricas globales comerciales
INSERT INTO metas_metricas (metrica_id, periodo_id, valor_objetivo, tipo_comparacion) VALUES
(@metrica_total_asociados, @periodo_actual, 500, 'mayor_igual'), -- 6000 / 12 = 500 por mes
(@metrica_total_tarjetas, @periodo_actual, 358, 'mayor_igual'); -- 4300 / 12 = 358 por mes

-- =============================================
-- VERIFICACIÓN FINAL
-- =============================================

SELECT 'Resumen de datos creados:' as Titulo;

SELECT
    'Agencias' as Tipo,
    COUNT(*) as Total
FROM departamentos
WHERE tipo = 'agencia'
UNION ALL
SELECT
    'Áreas de Agencias' as Tipo,
    COUNT(*) as Total
FROM areas a
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'agencia'
UNION ALL
SELECT
    'Áreas Globales' as Tipo,
    COUNT(*) as Total
FROM areas a
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'global'
UNION ALL
SELECT
    'Métricas en Agencias' as Tipo,
    COUNT(*) as Total
FROM metricas m
JOIN areas a ON m.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'agencia'
UNION ALL
SELECT
    'Métricas Calculadas Globales' as Tipo,
    COUNT(*) as Total
FROM metricas m
JOIN areas a ON m.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'global' AND m.es_calculada = 1
UNION ALL
SELECT
    'Valores de Métricas Registrados' as Tipo,
    COUNT(*) as Total
FROM valores_metricas;

-- =============================================
-- FIN DEL SEED
-- =============================================
