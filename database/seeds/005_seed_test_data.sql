-- =============================================
-- SEED DE DATOS DE PRUEBA COMPLETOS
-- 4 Departamentos, 3 Áreas cada uno, 5 Métricas por área
-- =============================================

-- =============================================
-- DEPARTAMENTO 1: Ventas (ya existe ID 1)
-- =============================================

-- Áreas de Ventas
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(1, 'Ventas Directas', 'ventas-directas', 'Ventas realizadas directamente por el equipo comercial', '#10b981', 'cash', 1, 1),
(1, 'E-commerce', 'ecommerce', 'Ventas a través de plataforma digital', '#3b82f6', 'shopping-cart', 1, 2),
(1, 'Distribuidores', 'distribuidores', 'Ventas a través de red de distribuidores', '#8b5cf6', 'truck-delivery', 1, 3);

-- Métricas Ventas Directas
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'ventas-directas'), 'Ingresos Totales', 'ingresos-totales', 'Total de ingresos generados', 'USD', 'numero', 'cash', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'ventas-directas'), 'Nuevos Clientes', 'nuevos-clientes', 'Cantidad de clientes nuevos captados', 'clientes', 'numero', 'user-plus', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'ventas-directas'), 'Ticket Promedio', 'ticket-promedio', 'Valor promedio por transacción', 'USD', 'decimal', 'receipt', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'ventas-directas'), 'Tasa de Conversión', 'tasa-conversion', 'Porcentaje de leads convertidos en ventas', '%', 'porcentaje', 'percentage', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'ventas-directas'), 'Llamadas Realizadas', 'llamadas-realizadas', 'Número de llamadas de prospección', 'llamadas', 'numero', 'phone', 0, 1, 5, 1);

-- Métricas E-commerce
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'ecommerce'), 'Ventas Online', 'ventas-online', 'Total de ventas realizadas online', 'USD', 'numero', 'shopping-cart', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'ecommerce'), 'Visitas al Sitio', 'visitas-sitio', 'Número de visitas únicas', 'visitas', 'numero', 'cursor-click', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'ecommerce'), 'Tasa de Rebote', 'tasa-rebote', 'Porcentaje de visitantes que salen sin interactuar', '%', 'porcentaje', 'arrow-bounce', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'ecommerce'), 'Carritos Abandonados', 'carritos-abandonados', 'Cantidad de carritos no completados', 'carritos', 'numero', 'shopping-cart-off', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'ecommerce'), 'Tiempo Promedio Sesión', 'tiempo-promedio-sesion', 'Duración promedio de visita en minutos', 'minutos', 'decimal', 'clock', 0, 1, 5, 1);

-- Métricas Distribuidores
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'distribuidores'), 'Ventas Distribuidores', 'ventas-distribuidores', 'Total vendido a través de distribuidores', 'USD', 'numero', 'truck-delivery', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'distribuidores'), 'Distribuidores Activos', 'distribuidores-activos', 'Número de distribuidores con ventas', 'distribuidores', 'numero', 'building-store', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'distribuidores'), 'Pedidos Despachados', 'pedidos-despachados', 'Cantidad de pedidos enviados', 'pedidos', 'numero', 'package', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'distribuidores'), 'Tiempo Entrega', 'tiempo-entrega', 'Días promedio de entrega', 'días', 'decimal', 'truck', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'distribuidores'), 'Devoluciones', 'devoluciones', 'Cantidad de productos devueltos', 'unidades', 'numero', 'refresh', 0, 1, 5, 1);

-- =============================================
-- DEPARTAMENTO 2: Operaciones (ya existe ID 2)
-- =============================================

-- Áreas de Operaciones
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
(2, 'Producción', 'produccion', 'Manufactura y producción de productos', '#f59e0b', 'tool', 1, 1),
(2, 'Logística', 'logistica', 'Almacenamiento y distribución', '#ef4444', 'truck', 1, 2),
(2, 'Calidad', 'calidad', 'Control y aseguramiento de calidad', '#06b6d4', 'award', 1, 3);

-- Métricas Producción
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'produccion'), 'Unidades Producidas', 'unidades-producidas', 'Total de unidades manufacturadas', 'unidades', 'numero', 'box', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'produccion'), 'Eficiencia Producción', 'eficiencia-produccion', 'Porcentaje de eficiencia vs capacidad', '%', 'porcentaje', 'gauge', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'produccion'), 'Tiempo Inactividad', 'tiempo-inactividad', 'Horas de máquinas paradas', 'horas', 'decimal', 'clock-pause', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'produccion'), 'Costo por Unidad', 'costo-unidad', 'Costo promedio de producción', 'USD', 'decimal', 'coin', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'produccion'), 'Defectos Producción', 'defectos-produccion', 'Unidades con defectos de fabricación', 'unidades', 'numero', 'alert-triangle', 0, 1, 5, 1);

-- Métricas Logística
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'logistica'), 'Entregas Completadas', 'entregas-completadas', 'Número de entregas exitosas', 'entregas', 'numero', 'truck-delivery', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'logistica'), 'Entregas a Tiempo', 'entregas-tiempo', 'Porcentaje de entregas puntuales', '%', 'porcentaje', 'clock-check', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'logistica'), 'Costo Transporte', 'costo-transporte', 'Gastos en transporte y envío', 'USD', 'numero', 'wallet', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'logistica'), 'Inventario Disponible', 'inventario-disponible', 'Unidades en stock', 'unidades', 'numero', 'package', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'logistica'), 'Rotación Inventario', 'rotacion-inventario', 'Veces que rota el inventario', 'veces', 'decimal', 'rotate', 0, 1, 5, 1);

-- Métricas Calidad
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'calidad'), 'Productos Inspeccionados', 'productos-inspeccionados', 'Cantidad de productos revisados', 'unidades', 'numero', 'eye-check', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'calidad'), 'Tasa Aprobación', 'tasa-aprobacion', 'Porcentaje de productos aprobados', '%', 'porcentaje', 'check', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'calidad'), 'Productos Rechazados', 'productos-rechazados', 'Unidades que no pasan control', 'unidades', 'numero', 'x', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'calidad'), 'Auditorías Realizadas', 'auditorias-realizadas', 'Número de auditorías de calidad', 'auditorías', 'numero', 'clipboard-check', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'calidad'), 'Tiempo Inspección', 'tiempo-inspeccion', 'Tiempo promedio de inspección', 'minutos', 'decimal', 'hourglass', 0, 1, 5, 1);

-- =============================================
-- DEPARTAMENTO 3: Global (ya existe ID 3)
-- =============================================
-- Ya tiene el área "Métricas Consolidadas" creada

-- =============================================
-- DEPARTAMENTO 4: Recursos Humanos (NUEVO)
-- =============================================

INSERT INTO departamentos (nombre, descripcion, color, icono, activo, orden) VALUES
('Recursos Humanos', 'Gestión del talento humano y cultura organizacional', '#ec4899', 'users', 1, 4);

-- Áreas de RRHH
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, activo, orden) VALUES
((SELECT id FROM departamentos WHERE nombre = 'Recursos Humanos'), 'Reclutamiento', 'reclutamiento', 'Atracción y selección de talento', '#ec4899', 'user-search', 1, 1),
((SELECT id FROM departamentos WHERE nombre = 'Recursos Humanos'), 'Capacitación', 'capacitacion', 'Desarrollo y formación del personal', '#a855f7', 'school', 1, 2),
((SELECT id FROM departamentos WHERE nombre = 'Recursos Humanos'), 'Clima Laboral', 'clima-laboral', 'Bienestar y satisfacción del equipo', '#f472b6', 'mood-smile', 1, 3);

-- Métricas Reclutamiento
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'reclutamiento'), 'Vacantes Abiertas', 'vacantes-abiertas', 'Número de posiciones disponibles', 'vacantes', 'numero', 'briefcase', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'reclutamiento'), 'Candidatos Entrevistados', 'candidatos-entrevistados', 'Personas entrevistadas en el mes', 'candidatos', 'numero', 'users', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'reclutamiento'), 'Contrataciones', 'contrataciones', 'Nuevos empleados incorporados', 'empleados', 'numero', 'user-plus', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'reclutamiento'), 'Tiempo Contratación', 'tiempo-contratacion', 'Días promedio para contratar', 'días', 'decimal', 'calendar', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'reclutamiento'), 'Costo por Contratación', 'costo-contratacion', 'Inversión promedio por empleado', 'USD', 'numero', 'cash', 0, 1, 5, 1);

-- Métricas Capacitación
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'capacitacion'), 'Cursos Impartidos', 'cursos-impartidos', 'Número de capacitaciones realizadas', 'cursos', 'numero', 'certificate', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'capacitacion'), 'Empleados Capacitados', 'empleados-capacitados', 'Personas que recibieron formación', 'empleados', 'numero', 'school', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'capacitacion'), 'Horas Capacitación', 'horas-capacitacion', 'Total de horas de formación', 'horas', 'numero', 'clock', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'capacitacion'), 'Satisfacción Cursos', 'satisfaccion-cursos', 'Calificación promedio de cursos', 'puntos', 'decimal', 'star', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'capacitacion'), 'Inversión Capacitación', 'inversion-capacitacion', 'Presupuesto ejecutado en formación', 'USD', 'numero', 'coin', 0, 1, 5, 1);

-- Métricas Clima Laboral
INSERT INTO metricas (area_id, nombre, slug, descripcion, unidad, tipo_valor, icono, es_calculada, tiene_meta, orden, activo) VALUES
((SELECT id FROM areas WHERE slug = 'clima-laboral'), 'Encuestas Realizadas', 'encuestas-realizadas', 'Número de encuestas de clima', 'encuestas', 'numero', 'forms', 0, 1, 1, 1),
((SELECT id FROM areas WHERE slug = 'clima-laboral'), 'Índice Satisfacción', 'indice-satisfaccion', 'Porcentaje de satisfacción general', '%', 'porcentaje', 'mood-happy', 0, 1, 2, 1),
((SELECT id FROM areas WHERE slug = 'clima-laboral'), 'Rotación Personal', 'rotacion-personal', 'Empleados que dejaron la empresa', 'empleados', 'numero', 'logout', 0, 1, 3, 1),
((SELECT id FROM areas WHERE slug = 'clima-laboral'), 'Días Ausentismo', 'dias-ausentismo', 'Días de ausencia no planificada', 'días', 'numero', 'calendar-off', 0, 1, 4, 1),
((SELECT id FROM areas WHERE slug = 'clima-laboral'), 'NPS Empleados', 'nps-empleados', 'Net Promoter Score interno', 'puntos', 'numero', 'thumb-up', 0, 1, 5, 1);
