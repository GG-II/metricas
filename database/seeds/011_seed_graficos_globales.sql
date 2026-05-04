-- =============================================
-- Seed: Gráficos para Áreas Globales
-- Dashboards ejecutivos con métricas consolidadas
-- =============================================

-- =============================================
-- OBTENER IDs DE ÁREAS GLOBALES
-- =============================================

SET @area_financieras = (SELECT id FROM areas WHERE slug = 'metricas-financieras');
SET @area_comerciales = (SELECT id FROM areas WHERE slug = 'metricas-comerciales');
SET @area_operativas = (SELECT id FROM areas WHERE slug = 'metricas-operativas');
SET @area_kpis = (SELECT id FROM areas WHERE slug = 'kpis-estrategicos');

-- =============================================
-- OBTENER IDs DE MÉTRICAS GLOBALES
-- =============================================

SET @m_total_colocacion = (SELECT id FROM metricas WHERE slug = 'total-colocacion-creditos');
SET @m_total_incremento = (SELECT id FROM metricas WHERE slug = 'total-incremento-cartera');
SET @m_total_asociados = (SELECT id FROM metricas WHERE slug = 'total-asociados-nuevos');
SET @m_total_tarjetas = (SELECT id FROM metricas WHERE slug = 'total-tarjetas-debito');

-- Obtener métricas de cada agencia para comparaciones
SET @m_colocacion_sj = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-sj');
SET @m_colocacion_heredia = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-heredia');
SET @m_colocacion_cartago = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-cartago');
SET @m_colocacion_alajuela = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-alajuela');

SET @m_asociados_sj = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-sj');
SET @m_asociados_heredia = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-heredia');
SET @m_asociados_cartago = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-cartago');

-- =============================================
-- ÁREA GLOBAL: MÉTRICAS FINANCIERAS
-- =============================================

-- KPI con Meta: Total Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'kpi-with-goal', 'Total Colocación Créditos',
 JSON_OBJECT('metrica_id', @m_total_colocacion, 'color', '#3b82f6', 'icono', 'coin', 'mostrar_meta', true),
 0, 0, 6, 3, 'todos', 1, 1);

-- KPI con Meta: Total Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'kpi-with-goal', 'Total Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_total_incremento, 'color', '#10b981', 'icono', 'trending-up', 'mostrar_meta', true),
 6, 0, 6, 3, 'todos', 2, 1);

-- Línea con Meta: Evolución Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'line-with-goal', 'Evolución Colocación vs Meta',
 JSON_OBJECT('metrica_id', @m_total_colocacion, 'color', '#3b82f6', 'periodos', 12, 'mostrar_meta', true),
 0, 3, 12, 4, 'todos', 3, 1);

-- Barra Horizontal: Ranking de Agencias
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'horizontal-bar', 'Colocación por Agencia',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_colocacion_sj, @m_colocacion_heredia, @m_colocacion_cartago, @m_colocacion_alajuela)),
 0, 7, 6, 4, 'todos', 4, 1);

-- Donut: Distribución de Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'donut', 'Participación por Agencia',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_colocacion_sj, @m_colocacion_heredia, @m_colocacion_cartago, @m_colocacion_alajuela)),
 6, 7, 6, 4, 'todos', 5, 1);

-- Gauge con Meta: Cumplimiento General
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'gauge-with-goal', 'Cumplimiento Meta Mensual',
 JSON_OBJECT('metrica_id', @m_total_colocacion, 'color', '#3b82f6', 'mostrar_meta', true),
 0, 11, 6, 3, 'todos', 6, 1);

-- Bullet Chart: Colocación vs Meta
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_financieras, 'bullet', 'Progreso Colocación',
 JSON_OBJECT('metrica_id', @m_total_colocacion, 'color', '#3b82f6', 'mostrar_meta', true),
 6, 11, 6, 3, 'todos', 7, 1);

-- =============================================
-- ÁREA GLOBAL: MÉTRICAS COMERCIALES
-- =============================================

-- KPI con Meta: Total Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'kpi-with-goal', 'Total Asociados Nuevos',
 JSON_OBJECT('metrica_id', @m_total_asociados, 'color', '#8b5cf6', 'icono', 'user-plus', 'mostrar_meta', true),
 0, 0, 6, 3, 'todos', 1, 1);

-- KPI con Meta: Total Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'kpi-with-goal', 'Total Tarjetas Débito',
 JSON_OBJECT('metrica_id', @m_total_tarjetas, 'color', '#f59e0b', 'icono', 'credit-card', 'mostrar_meta', true),
 6, 0, 6, 3, 'todos', 2, 1);

-- Línea: Tendencia Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'line', 'Tendencia Captación Asociados',
 JSON_OBJECT('metrica_id', @m_total_asociados, 'color', '#8b5cf6', 'periodos', 12),
 0, 3, 12, 4, 'todos', 3, 1);

-- Barra Apilada: Asociados por Agencia
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'stacked-bar', 'Asociados por Agencia',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_asociados_sj, @m_asociados_heredia, @m_asociados_cartago), 'periodos', 6),
 0, 7, 12, 4, 'todos', 4, 1);

-- Gauge: Cumplimiento Meta Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'gauge-with-goal', 'Cumplimiento Asociados',
 JSON_OBJECT('metrica_id', @m_total_asociados, 'color', '#8b5cf6', 'mostrar_meta', true),
 0, 11, 6, 3, 'todos', 5, 1);

-- Gauge: Cumplimiento Meta Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_comerciales, 'gauge-with-goal', 'Cumplimiento Tarjetas',
 JSON_OBJECT('metrica_id', @m_total_tarjetas, 'color', '#f59e0b', 'mostrar_meta', true),
 6, 11, 6, 3, 'todos', 6, 1);

-- =============================================
-- ÁREA GLOBAL: MÉTRICAS OPERATIVAS
-- =============================================

-- Multi-línea: Comparación de Agencias
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_operativas, 'multi-line', 'Colocación por Agencia',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_colocacion_sj, @m_colocacion_heredia, @m_colocacion_cartago, @m_colocacion_alajuela), 'periodos', 12),
 0, 0, 12, 5, 'todos', 1, 1);

-- Radar: Comparación Multi-dimensional
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_operativas, 'radar-comparison', 'Comparación de Desempeño',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_colocacion_sj, @m_colocacion_heredia, @m_colocacion_cartago)),
 0, 5, 6, 5, 'todos', 2, 1);

-- Scatter: Correlación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_operativas, 'scatter', 'Colocación vs Asociados',
 JSON_OBJECT('metrica_x', @m_total_asociados, 'metrica_y', @m_total_colocacion),
 6, 5, 6, 5, 'todos', 3, 1);

-- =============================================
-- ÁREA GLOBAL: KPIs ESTRATÉGICOS
-- =============================================

-- KPI Card: Colocación Total
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'kpi-card', 'Colocación Total',
 JSON_OBJECT('metrica_id', @m_total_colocacion, 'color', '#3b82f6', 'icono', 'coin'),
 0, 0, 3, 2, 'todos', 1, 1);

-- KPI Card: Incremento Total
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'kpi-card', 'Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_total_incremento, 'color', '#10b981', 'icono', 'trending-up'),
 3, 0, 3, 2, 'todos', 2, 1);

-- KPI Card: Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'kpi-card', 'Asociados Nuevos',
 JSON_OBJECT('metrica_id', @m_total_asociados, 'color', '#8b5cf6', 'icono', 'user-plus'),
 6, 0, 3, 2, 'todos', 3, 1);

-- KPI Card: Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'kpi-card', 'Tarjetas Débito',
 JSON_OBJECT('metrica_id', @m_total_tarjetas, 'color', '#f59e0b', 'icono', 'credit-card'),
 9, 0, 3, 2, 'todos', 4, 1);

-- Multi-línea: Todas las Métricas Globales
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'multi-line', 'Tendencias Generales',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_total_colocacion, @m_total_incremento), 'periodos', 12),
 0, 2, 12, 5, 'todos', 5, 1);

-- Comparison: Período Actual vs Anterior
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_kpis, 'period-comparison', 'Comparación Mensual',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_total_colocacion, @m_total_asociados)),
 0, 7, 12, 4, 'todos', 6, 1);

-- =============================================
-- VERIFICACIÓN
-- =============================================

SELECT 'Gráficos creados por área global:' as Resumen;

SELECT
    a.nombre as Area_Global,
    COUNT(g.id) as Total_Graficos
FROM areas a
JOIN departamentos d ON a.departamento_id = d.id
LEFT JOIN configuracion_graficos g ON a.id = g.area_id AND g.activo = 1
WHERE d.tipo = 'global'
GROUP BY a.id
ORDER BY a.orden;

SELECT 'Total de gráficos en áreas globales:' as Info, COUNT(*) as Total
FROM configuracion_graficos g
JOIN areas a ON g.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'global' AND g.activo = 1;

-- =============================================
-- FIN DEL SEED
-- =============================================
