# Guía de Screenshots para Documentación

Esta guía describe qué capturas de pantalla tomar para completar la documentación visual del sistema.

---

## 📸 Screenshots Requeridos

### 1. Login y Acceso

#### `01-login.png`
**Vista:** Página de login  
**URL:** `/metricas/public/login.php`  
**Capturar:**
- Formulario de login completo
- Logo del sistema
- Campo email y password
- Botón "Iniciar Sesión"

**Propósito:** Mostrar punto de entrada al sistema

---

### 2. Dashboard Principal

#### `02-dashboard-overview.png`
**Vista:** Dashboard completo  
**URL:** `/metricas/public/index.php`  
**Capturar:**
- Header con selector de período
- Navegación de áreas (pestañas)
- Grid con 4-6 gráficos visibles
- Botón "Exportar" y "Modo Edición"

**Propósito:** Vista general del dashboard

---

#### `02b-dashboard-mobile.png`
**Vista:** Dashboard en móvil  
**Dispositivo:** Móvil (375px width)  
**Capturar:**
- Vista responsive
- Navegación colapsada
- Gráficos adaptados

**Propósito:** Demostrar responsive design

---

### 3. Tipos de Gráficos

#### `03-chart-line-with-goal.png`
**Gráfico:** Línea con Meta  
**Capturar:**
- Gráfico mostrando línea azul (valores)
- Línea roja punteada (meta)
- Leyenda
- Título del gráfico

---

#### `03b-chart-kpi-card.png`
**Gráfico:** KPI Card  
**Capturar:**
- Valor grande destacado
- Indicador de tendencia (↗️)
- Badge de cumplimiento (verde)
- Comparación vs período anterior

---

#### `03c-chart-period-comparison.png`
**Gráfico:** Comparación de Períodos  
**Estilo:** Cards  
**Capturar:**
- 2 tarjetas lado a lado
- Valores de ambos períodos
- % de cambio
- Indicador visual

---

#### `03d-chart-multi-line.png`
**Gráfico:** Multi-línea  
**Capturar:**
- 3-4 líneas de diferentes colores
- Leyenda identificando métricas
- Tooltip al hacer hover

---

### 4. Modo Edición

#### `04-edit-mode.png`
**Vista:** Dashboard en modo edición  
**URL:** `/metricas/public/index.php?edit=1`  
**Capturar:**
- Botón "Agregar Gráfico" destacado
- Gráficos con icono de eliminar
- Indicadores de drag handle
- Botón "Guardar y Salir"

**Propósito:** Mostrar capacidad de personalización

---

#### `04b-drag-and-drop.png`
**Vista:** Gráfico siendo arrastrado  
**Capturar:**
- Gráfico en estado "dragging"
- Ghost/outline del gráfico
- Posición nueva

**Propósito:** Demostrar drag & drop

---

### 5. Catálogo de Gráficos

#### `05-chart-catalog.png`
**Vista:** Selección de tipo de gráfico  
**URL:** `/metricas/public/admin/graficos.php`  
**Capturar:**
- Grid de tipos de gráficos
- Previews visuales
- Nombres y descripciones
- Categorías (Básico, Comparación, etc.)

**Propósito:** Mostrar variedad de visualizaciones

---

### 6. Configuración de Gráfico

#### `06-chart-config.png`
**Vista:** Modal de configuración  
**Capturar:**
- Formulario de configuración
- Selector de métrica
- Opciones específicas del tipo
- Botones Cancelar/Guardar

**Propósito:** Proceso de crear gráfico

---

### 7. Administración de Métricas

#### `07-metricas-list.png`
**Vista:** Lista de métricas  
**URL:** `/metricas/public/admin/metricas.php`  
**Capturar:**
- Tabla con métricas
- Filtros (área, activas/todas)
- Botón "Nueva Métrica"
- Botón "Exportar"
- Acciones (editar, metas, valores)

---

#### `07b-metrica-form.png`
**Vista:** Formulario de métrica  
**Capturar:**
- Modal/formulario completo
- Campos: nombre, área, unidad, tipo
- Checkboxes (tiene meta, es calculada)
- Selector de ícono

---

### 8. Gestión de Metas

#### `08-metas-list.png`
**Vista:** Lista de metas de una métrica  
**Capturar:**
- Tabla de metas
- Columnas: valor objetivo, períodos, sentido
- Estados (activo/inactivo)
- Botón "Nueva Meta"

---

#### `08b-meta-form.png`
**Vista:** Formulario de meta  
**Capturar:**
- Campos de valor objetivo
- Selectores de período inicio/fin
- Radio buttons de sentido

---

### 9. Registro de Valores

#### `09-valores-form.png`
**Vista:** Formulario de registro de valor  
**Capturar:**
- Selector de período
- Campo de valor numérico
- Campo de nota (opcional)
- Botón guardar

---

#### `09b-valores-historico.png`
**Vista:** Histórico de valores  
**Capturar:**
- Tabla con períodos
- Valores registrados
- Notas
- Quien registró y cuándo

---

### 10. Gestión de Usuarios

#### `10-usuarios-list.png`
**Vista:** Lista de usuarios  
**URL:** `/metricas/public/admin/usuarios.php`  
**Capturar:**
- Tabla de usuarios
- Columnas: nombre, email, rol, departamento
- Estado (activo/inactivo)
- Acciones (editar, eliminar)

---

#### `10b-usuario-form.png`
**Vista:** Formulario de usuario  
**Capturar:**
- Campos básicos (nombre, email, password)
- Selector de rol
- Selector de departamento
- Multi-select de áreas (para visualizador)

---

### 11. Exportación

#### `11-export-modal.png`
**Vista:** Modal de exportación  
**Capturar:**
- Opciones de formato (CSV/PDF)
- Selector de períodos
- Contador de métricas seleccionadas
- Botones Cancelar/Exportar

---

#### `11b-export-csv.png`
**Vista:** Archivo CSV abierto en Excel  
**Capturar:**
- Tabla de datos exportados
- Headers de columnas
- Datos de múltiples períodos
- Formato correcto (UTF-8)

---

#### `11c-export-pdf.png`
**Vista:** HTML imprimible para PDF  
**Capturar:**
- Header del reporte
- Tabla de datos
- Footer con fecha generación
- Botones "Imprimir" y "Cerrar"

---

### 12. API - Gestión de Tokens

#### `12-api-tokens.png`
**Vista:** Panel de tokens de API  
**URL:** `/metricas/public/admin/api-tokens.php`  
**Capturar:**
- Formulario "Generar Token"
- Lista de tokens existentes
- Columnas: nombre, preview, último uso, usos totales
- Estados (activo/revocado/expirado)

---

#### `12b-token-generated.png`
**Vista:** Token recién generado  
**Capturar:**
- Alert de éxito
- Token completo visible
- Botón "Copiar"
- Advertencia "no podrás verlo nuevamente"

---

### 13. API - Documentación

#### `13-api-docs.png`
**Vista:** Documentación HTML de API  
**URL:** `/metricas/api`  
**Capturar:**
- Header con info de API
- Sección de autenticación
- Lista de endpoints por recurso
- Ejemplos de request/response

---

### 14. Temas (Light/Dark)

#### `14-theme-light.png`
**Vista:** Dashboard en tema claro  
**Capturar:**
- Vista completa del dashboard
- Paleta de colores clara

---

#### `14b-theme-dark.png`
**Vista:** Dashboard en tema oscuro  
**Capturar:**
- Mismo dashboard en dark mode
- Toggle de tema visible

---

### 15. Permisos y Roles

#### `15-super-admin-view.png`
**Vista:** Dashboard como Super Admin  
**Capturar:**
- Selector de departamentos visible
- Acceso a todas las áreas
- Botones de admin visibles

---

#### `15b-viewer-view.png`
**Vista:** Dashboard como Visualizador  
**Capturar:**
- Solo áreas asignadas visibles
- SIN botón "Modo Edición"
- SIN acceso a administración

---

### 16. Gestión de Departamentos

#### `16-departamentos.png`
**Vista:** Lista de departamentos  
**URL:** `/metricas/public/admin/departamentos.php`  
**Capturar:**
- Cards o tabla de departamentos
- Iconos y colores
- Acciones (editar, áreas)

---

### 17. Gestión de Áreas

#### `17-areas.png`
**Vista:** Lista de áreas  
**URL:** `/metricas/public/admin/areas.php`  
**Capturar:**
- Tabla de áreas
- Agrupación por departamento
- Orden, iconos, colores

---

### 18. Gestión de Períodos

#### `18-periodos.png`
**Vista:** Lista de períodos  
**URL:** `/metricas/public/admin/periodos.php`  
**Capturar:**
- Tabla de períodos
- Columnas: nombre, ejercicio, fechas
- Indicador de período actual
- Estados (activo/inactivo)

---

### 19. Caché Manager (CLI)

#### `19-cache-stats.png`
**Vista:** Terminal con output de caché  
**Comando:** `php cache-manager.php --stats`  
**Capturar:**
- Estadísticas de caché
- Total de entradas
- Entradas expiradas
- Tamaño en MB

---

### 20. Integración API (Código)

#### `20-api-integration-python.png`
**Vista:** Código Python haciendo request  
**Capturar:**
- Snippet de código
- Request a API
- Response JSON
- Syntax highlighting

---

#### `20b-api-integration-postman.png`
**Vista:** Postman con request a API  
**Capturar:**
- URL del endpoint
- Header de Authorization
- Body (si POST)
- Response JSON

---

## 📐 Especificaciones Técnicas

### Resoluciones

- **Desktop screenshots:** 1920x1080 (escala al 100%)
- **Mobile screenshots:** 375x812 (iPhone X/11)
- **Recortes:** Ajustar para remover espacio innecesario

### Formato

- **Formato:** PNG (mejor calidad para UI)
- **Compresión:** Media (balance calidad/tamaño)
- **Tamaño máximo:** <500KB por imagen

### Herramientas Recomendadas

**Captura:**
- Windows: Snipping Tool / Snip & Sketch
- Mac: Cmd+Shift+4
- Browser DevTools: Responsive mode para móvil

**Edición:**
- **Anotaciones:** Flechas, recuadros, texto explicativo
- **Blur:** Datos sensibles (emails, tokens completos)
- **Highlight:** Elementos importantes

**Herramientas:**
- Greenshot (Windows)
- Monosnap (Mac/Windows)
- Lightshot
- ShareX

---

## 🎨 Guía de Estilo para Screenshots

### DO ✅

- **Usar datos realistas** (no "test", "asdf", etc.)
- **Completar formularios** como usuario real
- **Tener múltiples elementos** (3-5 métricas, no solo 1)
- **Mostrar estados reales** (valores con meta cumplida/incumplida)
- **Incluir UI completa** (headers, sidebars)
- **Usar tema claro** por defecto (mejor contraste)

### DON'T ❌

- **No incluir datos sensibles** reales
- **No usar ventana maximizada** si hace screenshot muy grande
- **No capturar con zoom** extraño del browser
- **No mostrar paneles de desarrollo** abiertos (F12)
- **No incluir notificaciones** del sistema operativo

---

## 📝 Datos de Ejemplo para Screenshots

### Departamentos
- Ventas (icono: shopping-cart, color: #22c55e)
- Operaciones (icono: settings, color: #3b82f6)
- Marketing (icono: speakerphone, color: #f59e0b)

### Áreas
- Ventas → Comercial (icono: chart-line)
- Ventas → Postventa (icono: headset)
- Operaciones → Producción (icono: tools)
- Marketing → Digital (icono: device-desktop)

### Métricas
- Ventas del Mes (unidad: unidades, tipo: número)
- Satisfacción Cliente (unidad: %, tipo: decimal)
- Tiempo de Entrega (unidad: horas, tipo: decimal)
- Nuevos Leads (unidad: leads, tipo: número)

### Valores de Ejemplo
- Ventas del Mes: [85, 92, 78, 105, 98] (meta: 100)
- Satisfacción Cliente: [92.5, 91.3, 94.7, 93.2] (meta: 90.0)

### Usuarios
- Juan Pérez (Super Admin, juan@empresa.com)
- María García (Admin Depto Ventas, maria@empresa.com)
- Carlos López (Visualizador, carlos@empresa.com)

---

## 📂 Organización de Archivos

```
metricas/
├── docs/
│   └── screenshots/
│       ├── 01-login.png
│       ├── 02-dashboard-overview.png
│       ├── 02b-dashboard-mobile.png
│       ├── 03-chart-line-with-goal.png
│       ├── 03b-chart-kpi-card.png
│       ├── ...
│       └── 20b-api-integration-postman.png
```

---

## ✅ Checklist de Screenshots

Marcar al completar:

### Esenciales (Prioridad Alta)
- [ ] 01-login.png
- [ ] 02-dashboard-overview.png
- [ ] 03-chart-line-with-goal.png
- [ ] 03b-chart-kpi-card.png
- [ ] 04-edit-mode.png
- [ ] 05-chart-catalog.png
- [ ] 07-metricas-list.png
- [ ] 11-export-modal.png
- [ ] 12-api-tokens.png
- [ ] 13-api-docs.png

### Importantes (Prioridad Media)
- [ ] 02b-dashboard-mobile.png
- [ ] 03c-chart-period-comparison.png
- [ ] 04b-drag-and-drop.png
- [ ] 06-chart-config.png
- [ ] 07b-metrica-form.png
- [ ] 08-metas-list.png
- [ ] 09-valores-form.png
- [ ] 10-usuarios-list.png
- [ ] 11b-export-csv.png
- [ ] 14-theme-light.png
- [ ] 14b-theme-dark.png

### Complementarios (Prioridad Baja)
- [ ] 03d-chart-multi-line.png
- [ ] 08b-meta-form.png
- [ ] 09b-valores-historico.png
- [ ] 10b-usuario-form.png
- [ ] 11c-export-pdf.png
- [ ] 12b-token-generated.png
- [ ] 15-super-admin-view.png
- [ ] 15b-viewer-view.png
- [ ] 16-departamentos.png
- [ ] 17-areas.png
- [ ] 18-periodos.png
- [ ] 19-cache-stats.png
- [ ] 20-api-integration-python.png
- [ ] 20b-api-integration-postman.png

---

## 🔄 Actualización de Screenshots

Al hacer cambios significativos en UI:

1. Identificar screenshots afectados
2. Recrear con misma configuración
3. Mantener nombres de archivo
4. Actualizar documentación si cambia funcionalidad

---

## 📊 Uso de Screenshots en Documentación

### README.md
Insertar screenshots clave:

```markdown
![Dashboard Overview](docs/screenshots/02-dashboard-overview.png)

*Dashboard principal mostrando múltiples gráficos*
```

### QUICKSTART.md
Screenshots paso a paso:

```markdown
### Paso 1: Login

![Login](docs/screenshots/01-login.png)

Ingresa tus credenciales...
```

### EXECUTIVE_SUMMARY.md
Screenshots de impacto:

```markdown
## Dashboards Interactivos

![](docs/screenshots/02-dashboard-overview.png)

21 tipos de gráficos...
```

---

**Última actualización:** Abril 2026
