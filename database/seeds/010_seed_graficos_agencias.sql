-- =============================================
-- Seed: Gráficos para Dashboards de Agencias
-- Crea gráficos coherentes para cada área de las agencias
-- =============================================

-- =============================================
-- OBTENER IDs DE ÁREAS
-- =============================================

-- Agencia San José
SET @area_caja_sj = (SELECT id FROM areas WHERE slug = 'caja-sj');
SET @area_creditos_sj = (SELECT id FROM areas WHERE slug = 'creditos-sj');
SET @area_cuentas_sj = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-sj');
SET @area_atencion_sj = (SELECT id FROM areas WHERE slug = 'atencion-sj');

-- Agencia Heredia
SET @area_caja_heredia = (SELECT id FROM areas WHERE slug = 'caja-heredia');
SET @area_creditos_heredia = (SELECT id FROM areas WHERE slug = 'creditos-heredia');
SET @area_cuentas_heredia = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-heredia');

-- Agencia Cartago
SET @area_caja_cartago = (SELECT id FROM areas WHERE slug = 'caja-cartago');
SET @area_creditos_cartago = (SELECT id FROM areas WHERE slug = 'creditos-cartago');
SET @area_cuentas_cartago = (SELECT id FROM areas WHERE slug = 'cuentas-nuevas-cartago');

-- Agencia Alajuela
SET @area_caja_alajuela = (SELECT id FROM areas WHERE slug = 'caja-alajuela');
SET @area_creditos_alajuela = (SELECT id FROM areas WHERE slug = 'creditos-alajuela');

-- =============================================
-- OBTENER IDs DE MÉTRICAS
-- =============================================

-- San José - Créditos
SET @m_colocacion_sj = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-sj');
SET @m_incremento_sj = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-sj');
SET @m_cantidad_sj = (SELECT id FROM metricas WHERE slug = 'cantidad-creditos-sj');

-- San José - Cuentas
SET @m_asociados_sj = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-sj');
SET @m_tarjetas_sj = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-sj');
SET @m_cic_sj = (SELECT id FROM metricas WHERE slug = 'cic-virtual-sj');

-- Heredia - Créditos
SET @m_colocacion_heredia = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-heredia');
SET @m_incremento_heredia = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-heredia');
SET @m_cantidad_heredia = (SELECT id FROM metricas WHERE slug = 'cantidad-creditos-heredia');

-- Heredia - Cuentas
SET @m_asociados_heredia = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-heredia');
SET @m_tarjetas_heredia = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-heredia');

-- Cartago - Créditos
SET @m_colocacion_cartago = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-cartago');
SET @m_incremento_cartago = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-cartago');
SET @m_cantidad_cartago = (SELECT id FROM metricas WHERE slug = 'cantidad-creditos-cartago');

-- Cartago - Cuentas
SET @m_asociados_cartago = (SELECT id FROM metricas WHERE slug = 'asociados-nuevos-cartago');
SET @m_tarjetas_cartago = (SELECT id FROM metricas WHERE slug = 'tarjetas-debito-cartago');

-- Alajuela - Créditos
SET @m_colocacion_alajuela = (SELECT id FROM metricas WHERE slug = 'colocacion-creditos-alajuela');
SET @m_incremento_alajuela = (SELECT id FROM metricas WHERE slug = 'incremento-cartera-alajuela');

-- =============================================
-- AGENCIA SAN JOSÉ - ÁREA CRÉDITOS
-- =============================================

-- KPI: Colocación de Créditos
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'kpi-card', 'Colocación Mensual',
 JSON_OBJECT('metrica_id', @m_colocacion_sj, 'color', '#3b82f6', 'icono', 'coin'),
 0, 0, 3, 2, 'todos', 1, 1);

-- KPI: Incremento Cartera
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'kpi-card', 'Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_incremento_sj, 'color', '#10b981', 'icono', 'trending-up'),
 3, 0, 3, 2, 'todos', 2, 1);

-- KPI: Cantidad de Créditos
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'kpi-card', 'Créditos Otorgados',
 JSON_OBJECT('metrica_id', @m_cantidad_sj, 'color', '#8b5cf6', 'icono', 'list-numbers'),
 6, 0, 3, 2, 'todos', 3, 1);

-- Línea: Evolución Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'line', 'Evolución Colocación de Créditos',
 JSON_OBJECT('metrica_id', @m_colocacion_sj, 'color', '#3b82f6', 'periodos', 12),
 0, 2, 6, 4, 'todos', 4, 1);

-- Barra: Cantidad de Créditos
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'bar', 'Créditos por Mes',
 JSON_OBJECT('metrica_id', @m_cantidad_sj, 'color', '#8b5cf6', 'periodos', 6),
 6, 2, 6, 4, 'todos', 5, 1);

-- Sparkline: Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_sj, 'sparkline', 'Tendencia Incremento',
 JSON_OBJECT('metrica_id', @m_incremento_sj, 'color', '#10b981', 'periodos', 12),
 9, 0, 3, 2, 'todos', 6, 1);

-- =============================================
-- AGENCIA SAN JOSÉ - ÁREA CUENTAS NUEVAS
-- =============================================

-- KPI: Asociados Nuevos
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_sj, 'kpi-card', 'Asociados Nuevos',
 JSON_OBJECT('metrica_id', @m_asociados_sj, 'color', '#8b5cf6', 'icono', 'user-plus'),
 0, 0, 4, 2, 'todos', 1, 1);

-- KPI: Tarjetas Débito
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_sj, 'kpi-card', 'Tarjetas Emitidas',
 JSON_OBJECT('metrica_id', @m_tarjetas_sj, 'color', '#f59e0b', 'icono', 'credit-card'),
 4, 0, 4, 2, 'todos', 2, 1);

-- KPI: CIC Virtual
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_sj, 'kpi-card', 'Usuarios CIC Virtual',
 JSON_OBJECT('metrica_id', @m_cic_sj, 'color', '#06b6d4', 'icono', 'device-mobile'),
 8, 0, 4, 2, 'todos', 3, 1);

-- Línea: Evolución Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_sj, 'line', 'Captación de Asociados',
 JSON_OBJECT('metrica_id', @m_asociados_sj, 'color', '#8b5cf6', 'periodos', 12),
 0, 2, 6, 4, 'todos', 4, 1);

-- Donut: Distribución Productos
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_sj, 'donut', 'Productos por Tipo',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_asociados_sj, @m_tarjetas_sj, @m_cic_sj)),
 6, 2, 6, 4, 'todos', 5, 1);

-- =============================================
-- AGENCIA HEREDIA - ÁREA CRÉDITOS
-- =============================================

-- KPI: Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_heredia, 'kpi-card', 'Colocación Mensual',
 JSON_OBJECT('metrica_id', @m_colocacion_heredia, 'color', '#3b82f6', 'icono', 'coin'),
 0, 0, 4, 2, 'todos', 1, 1);

-- KPI: Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_heredia, 'kpi-card', 'Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_incremento_heredia, 'color', '#10b981', 'icono', 'trending-up'),
 4, 0, 4, 2, 'todos', 2, 1);

-- KPI: Cantidad
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_heredia, 'kpi-card', 'Créditos Otorgados',
 JSON_OBJECT('metrica_id', @m_cantidad_heredia, 'color', '#8b5cf6', 'icono', 'list-numbers'),
 8, 0, 4, 2, 'todos', 3, 1);

-- Línea: Evolución
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_heredia, 'line', 'Evolución Colocación',
 JSON_OBJECT('metrica_id', @m_colocacion_heredia, 'color', '#3b82f6', 'periodos', 12),
 0, 2, 6, 4, 'todos', 4, 1);

-- Barra: Créditos por Mes
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_heredia, 'bar', 'Créditos Otorgados Mensual',
 JSON_OBJECT('metrica_id', @m_cantidad_heredia, 'color', '#8b5cf6', 'periodos', 6),
 6, 2, 6, 4, 'todos', 5, 1);

-- =============================================
-- AGENCIA HEREDIA - ÁREA CUENTAS NUEVAS
-- =============================================

-- KPI: Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_heredia, 'kpi-card', 'Asociados Nuevos',
 JSON_OBJECT('metrica_id', @m_asociados_heredia, 'color', '#8b5cf6', 'icono', 'user-plus'),
 0, 0, 6, 2, 'todos', 1, 1);

-- KPI: Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_heredia, 'kpi-card', 'Tarjetas Emitidas',
 JSON_OBJECT('metrica_id', @m_tarjetas_heredia, 'color', '#f59e0b', 'icono', 'credit-card'),
 6, 0, 6, 2, 'todos', 2, 1);

-- Línea: Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_heredia, 'line', 'Tendencia Asociados',
 JSON_OBJECT('metrica_id', @m_asociados_heredia, 'color', '#8b5cf6', 'periodos', 12),
 0, 2, 6, 4, 'todos', 3, 1);

-- Barra: Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_heredia, 'bar', 'Tarjetas por Mes',
 JSON_OBJECT('metrica_id', @m_tarjetas_heredia, 'color', '#f59e0b', 'periodos', 6),
 6, 2, 6, 4, 'todos', 4, 1);

-- =============================================
-- AGENCIA CARTAGO - ÁREA CRÉDITOS
-- =============================================

-- KPI: Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_cartago, 'kpi-card', 'Colocación Mensual',
 JSON_OBJECT('metrica_id', @m_colocacion_cartago, 'color', '#3b82f6', 'icono', 'coin'),
 0, 0, 4, 2, 'todos', 1, 1);

-- KPI: Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_cartago, 'kpi-card', 'Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_incremento_cartago, 'color', '#10b981', 'icono', 'trending-up'),
 4, 0, 4, 2, 'todos', 2, 1);

-- KPI: Cantidad
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_cartago, 'kpi-card', 'Créditos Otorgados',
 JSON_OBJECT('metrica_id', @m_cantidad_cartago, 'color', '#8b5cf6', 'icono', 'list-numbers'),
 8, 0, 4, 2, 'todos', 3, 1);

-- Línea: Evolución
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_cartago, 'line', 'Evolución Colocación',
 JSON_OBJECT('metrica_id', @m_colocacion_cartago, 'color', '#3b82f6', 'periodos', 12),
 0, 2, 8, 4, 'todos', 4, 1);

-- Sparkline: Tendencia Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_cartago, 'sparkline', 'Tendencia Cartera',
 JSON_OBJECT('metrica_id', @m_incremento_cartago, 'color', '#10b981', 'periodos', 12),
 8, 2, 4, 4, 'todos', 5, 1);

-- =============================================
-- AGENCIA CARTAGO - ÁREA CUENTAS NUEVAS
-- =============================================

-- KPI: Asociados
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_cartago, 'kpi-card', 'Asociados Nuevos',
 JSON_OBJECT('metrica_id', @m_asociados_cartago, 'color', '#8b5cf6', 'icono', 'user-plus'),
 0, 0, 6, 2, 'todos', 1, 1);

-- KPI: Tarjetas
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_cartago, 'kpi-card', 'Tarjetas Emitidas',
 JSON_OBJECT('metrica_id', @m_tarjetas_cartago, 'color', '#f59e0b', 'icono', 'credit-card'),
 6, 0, 6, 2, 'todos', 2, 1);

-- Línea Multi: Tendencias
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_cuentas_cartago, 'multi-line', 'Tendencias de Captación',
 JSON_OBJECT('metricas', JSON_ARRAY(@m_asociados_cartago, @m_tarjetas_cartago), 'periodos', 12),
 0, 2, 12, 4, 'todos', 3, 1);

-- =============================================
-- AGENCIA ALAJUELA - ÁREA CRÉDITOS
-- =============================================

-- KPI: Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_alajuela, 'kpi-card', 'Colocación Mensual',
 JSON_OBJECT('metrica_id', @m_colocacion_alajuela, 'color', '#3b82f6', 'icono', 'coin'),
 0, 0, 6, 2, 'todos', 1, 1);

-- KPI: Incremento
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_alajuela, 'kpi-card', 'Incremento Cartera',
 JSON_OBJECT('metrica_id', @m_incremento_alajuela, 'color', '#10b981', 'icono', 'trending-up'),
 6, 0, 6, 2, 'todos', 2, 1);

-- Línea: Evolución Colocación
INSERT INTO configuracion_graficos (area_id, tipo, titulo, configuracion, grid_x, grid_y, grid_w, grid_h, visible_para, orden, activo) VALUES
(@area_creditos_alajuela, 'line', 'Evolución Mensual',
 JSON_OBJECT('metrica_id', @m_colocacion_alajuela, 'color', '#3b82f6', 'periodos', 12),
 0, 2, 12, 4, 'todos', 3, 1);

-- =============================================
-- VERIFICACIÓN
-- =============================================

SELECT 'Gráficos creados por agencia:' as Resumen;

SELECT
    d.nombre as Agencia,
    a.nombre as Area,
    COUNT(g.id) as Total_Graficos
FROM departamentos d
JOIN areas a ON d.id = a.departamento_id
LEFT JOIN configuracion_graficos g ON a.id = g.area_id AND g.activo = 1
WHERE d.tipo = 'agencia'
GROUP BY d.id, a.id
ORDER BY d.orden, a.orden;

SELECT 'Total de gráficos creados:' as Info, COUNT(*) as Total
FROM configuracion_graficos g
JOIN areas a ON g.area_id = a.id
JOIN departamentos d ON a.departamento_id = d.id
WHERE d.tipo = 'agencia' AND g.activo = 1;

-- =============================================
-- FIN DEL SEED
-- =============================================
