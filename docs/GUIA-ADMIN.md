# Guía de Administrador - Sistema de Métricas

Guía completa para administradores del sistema que gestionan departamentos, áreas, usuarios, métricas y gráficos.

## Tabla de Contenidos

1. [Roles y Permisos](#roles-y-permisos)
2. [Gestión de Departamentos](#gestión-de-departamentos)
3. [Gestión de Áreas](#gestión-de-áreas)
4. [Gestión de Usuarios](#gestión-de-usuarios)
5. [Gestión de Métricas](#gestión-de-métricas)
6. [Gestión de Gráficos](#gestión-de-gráficos)
7. [Configuración de Metas](#configuración-de-metas)
8. [Registro de Valores](#registro-de-valores)
9. [Períodos](#períodos)
10. [Mantenimiento](#mantenimiento)

---

## Roles y Permisos

### Super Administrador (`super_admin`)

**Acceso:**
- ✅ Todos los departamentos y áreas
- ✅ Crear/editar/eliminar departamentos
- ✅ Crear/editar/eliminar áreas
- ✅ Gestionar todos los usuarios
- ✅ Gestionar todas las métricas
- ✅ Gestionar todos los gráficos
- ✅ Configurar sistema

**Casos de uso:**
- Gerencia general
- TI / Sistemas
- Administración central

### Administrador de Departamento (`dept_admin`)

**Acceso:**
- ✅ Su departamento y todas sus áreas
- ✅ Crear/editar áreas de su departamento
- ✅ Gestionar usuarios de su departamento
- ✅ Gestionar métricas de su departamento
- ✅ Gestionar gráficos de su departamento
- ❌ No puede crear departamentos
- ❌ No puede acceder a otros departamentos

**Casos de uso:**
- Jefe de departamento
- Gerente de área
- Coordinador

### Visualizador (`dept_viewer`)

**Acceso:**
- ✅ Solo su área asignada (solo lectura)
- ❌ No puede editar nada
- ❌ No puede acceder a otras áreas
- ❌ No puede gestionar métricas ni gráficos

**Casos de uso:**
- Empleados del departamento
- Stakeholders externos
- Visualización en pantallas

---

## Gestión de Departamentos

### Crear Departamento

1. Menu: **Admin** → **Departamentos**
2. Click **"+ Nuevo Departamento"**
3. Completa el formulario:
   - **Nombre**: Nombre del departamento (ej: "Ventas")
   - **Color**: Color identificador (hex, ej: #3b82f6)
   - **Icono**: Icono de Tabler (ej: "shopping-cart")
   - **Orden**: Orden de visualización (número)
4. Click **"Guardar"**

**Validaciones:**
- Nombre es requerido y único
- Color debe ser formato hex válido
- Icono debe existir en Tabler Icons

### Editar Departamento

1. En la lista de departamentos, click **✏️ Editar**
2. Modifica los campos necesarios
3. Click **"Guardar Cambios"**

### Desactivar Departamento

1. Click **🗑️ Eliminar**
2. Confirma la acción
3. El departamento se marca como inactivo (no se elimina)

**Nota:** Al desactivar un departamento:
- No aparecerá en selectores
- Los usuarios asignados no podrán acceder
- Los datos históricos se conservan

### Reactivar Departamento

1. En la lista, activa el filtro **"Ver inactivos"**
2. Click **"Activar"** en el departamento
3. El departamento vuelve a estar disponible

---

## Gestión de Áreas

### Crear Área

1. Menu: **Admin** → **Áreas**
2. Click **"+ Nueva Área"**
3. Completa el formulario:
   - **Departamento**: Selecciona el departamento padre
   - **Nombre**: Nombre del área (ej: "Ventas Online")
   - **Slug**: URL-friendly name (auto-generado)
   - **Descripción**: Descripción opcional
   - **Color**: Color identificador
   - **Icono**: Icono de Tabler
   - **Orden**: Orden de visualización
4. Click **"Guardar"**

### Editar Área

1. En la lista de áreas, click **✏️ Editar**
2. Modifica los campos necesarios
3. Click **"Guardar Cambios"**

### Mover Área a Otro Departamento

1. Edita el área
2. Cambia el campo **"Departamento"**
3. Guarda los cambios

**Advertencia:** Esto puede afectar los permisos de usuarios asignados.

### Desactivar Área

Similar a desactivar departamentos. Los datos se conservan.

---

## Gestión de Usuarios

### Crear Usuario

1. Menu: **Admin** → **Usuarios**
2. Click **"+ Nuevo Usuario"**
3. Completa el formulario:
   - **Nombre completo**: Nombre del usuario
   - **Username**: Nombre de usuario (único)
   - **Email**: Correo electrónico
   - **Contraseña**: Contraseña inicial
   - **Rol**: super_admin, dept_admin, dept_viewer
   - **Departamento**: Asignar departamento (excepto super_admin)
   - **Área**: Asignar área específica (para dept_viewer)
4. Click **"Crear Usuario"**

**Validaciones:**
- Username debe ser único
- Email debe ser válido y único
- Contraseña mínimo 6 caracteres
- dept_viewer requiere área asignada

### Editar Usuario

1. Click **✏️ Editar** en el usuario
2. Modifica campos (no puedes cambiar username)
3. Para cambiar contraseña, marca **"Cambiar contraseña"**
4. Click **"Guardar Cambios"**

### Cambiar Rol

1. Edita el usuario
2. Cambia el campo **"Rol"**
3. Ajusta departamento/área según el nuevo rol
4. Guarda

**Importante:**
- Al cambiar a `super_admin`: Quita departamento y área
- Al cambiar a `dept_admin`: Asigna departamento
- Al cambiar a `dept_viewer`: Asigna departamento y área

### Desactivar Usuario

1. Click **🗑️ Eliminar**
2. Confirma
3. El usuario no podrá acceder pero sus datos se conservan

---

## Gestión de Métricas

### Crear Métrica

1. Menu: **Admin** → **Métricas**
2. Selecciona el **Área**
3. Click **"+ Nueva Métrica"**
4. Completa el formulario:
   - **Nombre**: Nombre de la métrica (ej: "Ventas Mensuales")
   - **Slug**: Auto-generado
   - **Descripción**: Descripción opcional
   - **Tipo de Valor**:
     - `numero`: Enteros (ej: 150 ventas)
     - `decimal`: Decimales (ej: 1250.50 USD)
     - `porcentaje`: % (ej: 85.5%)
     - `tiempo`: Horas/días (ej: 3.5 horas)
   - **Unidad**: Unidad de medida (ej: "ventas", "USD", "%")
   - **Icono**: Icono representativo
   - **¿Tiene meta?**: Marcar si tendrá objetivos
   - **¿Es calculada?**: Para métricas derivadas (futuro)
5. Click **"Guardar"**

### Editar Métrica

1. Click **✏️ Editar**
2. Modifica campos
3. **Advertencia:** Cambiar tipo de valor puede afectar gráficos existentes
4. Guarda

### Desactivar Métrica

1. Click **🗑️ Desactivar**
2. Los gráficos que usan esta métrica mostrarán advertencia
3. Los datos históricos se conservan

---

## Gestión de Gráficos

### Acceder a Configuración de Gráficos

1. Ve al dashboard del área
2. Click **"Modo Edición"**
3. Click **"+ Agregar Gráfico"**

### Crear Gráfico

1. En modo edición, click **"+ Agregar Gráfico"**
2. Selecciona el **Tipo de gráfico** (ver catálogo completo más abajo)
3. Configura el gráfico según el tipo:
   - **Título**: Nombre del widget
   - **Métrica(s)**: Selecciona las métricas a visualizar
   - **Colores**: Personaliza colores
   - **Períodos**: Cuántos meses mostrar (históricos)
   - **Altura**: Altura del widget
   - **Opciones específicas**: Cada tipo tiene opciones únicas
4. Click **"Guardar"**

El gráfico aparece automáticamente en el dashboard.

### Editar Gráfico

1. En modo edición, click **⚙️** en el gráfico
2. Modifica la configuración
3. Click **"Guardar"**
4. El gráfico se actualiza automáticamente

### Eliminar Gráfico

1. En modo edición, click **🗑️** en el gráfico
2. Confirma la eliminación (SweetAlert2)
3. El gráfico se elimina permanentemente

### Reorganizar Gráficos

**En modo edición:**

1. **Mover**: Arrastra el gráfico desde la cabecera
2. **Redimensionar**: Arrastra desde la esquina inferior derecha
3. **Guardar Layout**: Click **"Guardar y Salir"**

El layout se guarda automáticamente.

---

## Configuración de Metas

### Crear Meta Mensual

1. Menu: **Admin** → **Metas**
2. Click **"+ Nueva Meta"**
3. Completa:
   - **Métrica**: Selecciona la métrica
   - **Tipo**: Mensual
   - **Período**: Selecciona mes y año
   - **Valor Objetivo**: Meta a alcanzar
   - **Tipo de Comparación**:
     - `mayor_mejor`: Mayor valor es mejor (ej: ventas)
     - `menor_mejor`: Menor valor es mejor (ej: costos)
4. Click **"Guardar"**

### Crear Meta Anual

1. Similar a meta mensual
2. **Tipo**: Anual
3. **Ejercicio**: Selecciona el año
4. La meta se distribuye automáticamente por mes

### Editar Meta

1. Click **✏️ Editar**
2. Modifica el valor objetivo
3. Guarda

**Nota:** Los gráficos se actualizan automáticamente.

### Desactivar Meta

Las metas se pueden desactivar sin eliminarlas para conservar el histórico.

---

## Registro de Valores

### Métodos de Registro

#### 1. Manual (Formulario)

1. Menu: **Valores** → **Registrar**
2. Selecciona:
   - **Área**
   - **Métrica**
   - **Período**
3. Ingresa el valor
4. Click **"Guardar"**

#### 2. Importación Masiva (CSV)

1. Menu: **Valores** → **Importar**
2. Descarga plantilla CSV
3. Completa con tus datos:
   ```csv
   area_id,metrica_id,periodo_id,valor
   1,5,10,1250.50
   1,6,10,85
   ```
4. Sube el archivo
5. Revisa vista previa
6. Click **"Confirmar Importación"**

#### 3. API REST (Automatizado)

Ver [API-GRAFICOS.md](API-GRAFICOS.md) para detalles técnicos.

### Validaciones

- Período debe estar activo
- No se permiten valores duplicados (métrica + período)
- Tipo de valor debe coincidir con la métrica

---

## Períodos

### Gestión de Períodos

1. Menu: **Admin** → **Períodos**
2. Los períodos se crean automáticamente por año

### Activar/Desactivar Período

1. En la lista de períodos
2. Toggle **Activo/Inactivo**
3. Los períodos inactivos no aparecen en selectores

**Casos de uso:**
- Desactiva períodos futuros aún sin datos
- Desactiva períodos muy antiguos

### Crear Períodos para Nuevo Año

Los períodos se crean automáticamente, pero si necesitas crearlos manualmente:

```sql
INSERT INTO periodos (ejercicio, periodo, nombre, activo)
VALUES 
  (2027, 1, 'Enero 2027', 1),
  (2027, 2, 'Febrero 2027', 1),
  ...
  (2027, 12, 'Diciembre 2027', 1);
```

---

## Mantenimiento

### Backup de Base de Datos

**Frecuencia recomendada:** Diaria

```bash
# Backup completo
mysqldump -u usuario -p metricas_sistema > backup_$(date +%Y%m%d).sql

# Backup solo estructura
mysqldump -u usuario -p --no-data metricas_sistema > estructura.sql

# Backup solo datos
mysqldump -u usuario -p --no-create-info metricas_sistema > datos.sql
```

### Restaurar Backup

```bash
mysql -u usuario -p metricas_sistema < backup_20260425.sql
```

### Limpiar Datos Antiguos

**Valores de métricas de más de 5 años:**

```sql
DELETE vm FROM valores_metricas vm
JOIN periodos p ON vm.periodo_id = p.id
WHERE p.ejercicio < YEAR(NOW()) - 5;
```

**Advertencia:** Ejecuta esto con precaución y después de hacer backup.

### Optimizar Base de Datos

```sql
OPTIMIZE TABLE valores_metricas;
OPTIMIZE TABLE configuracion_graficos;
OPTIMIZE TABLE metas_metricas;
```

### Verificar Integridad

Ejecuta el script de verificación:

```bash
php verify_database.php
```

### Limpiar Datos de Prueba

Antes de producción:

```bash
php cleanup_test_data.php
```

---

## Catálogo de Gráficos

### Gráficos Básicos

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `kpi_card` | KPI Card | 1 | Tarjeta con valor y comparación |
| `kpi_with_goal` | KPI con Meta | 1 con meta | KPI con indicador de cumplimiento |
| `progress` | Progreso | 1 con meta | Barra de progreso |
| `sparkline` | Sparkline | 1 | Miniatura de tendencia |

### Gráficos de Líneas

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `line` | Línea | 1 | Evolución temporal |
| `line_with_goal` | Línea con Meta | 1 con meta | Línea vs meta |
| `multi_line` | Líneas Múltiples | 2-5 | Comparación de tendencias |
| `area` | Área | 1 | Área sombreada |
| `stacked_area` | Área Apilada | 2-4 | Áreas apiladas |

### Gráficos de Barras

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `bar` | Barras | 1 | Barras verticales |
| `horizontal_bar` | Barras Horizontales | 2-6 | Barras horizontales |
| `multi_bar` | Multi Barras | 3-6 | Barras agrupadas |
| `stacked_bar` | Barras Apiladas | 2-4 | Barras apiladas |
| `percentage_bar` | Barras Porcentuales | 2-4 | Distribución % |

### Medidores

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `gauge` | Gauge | 1 | Medidor circular |
| `gauge_with_goal` | Gauge con Meta | 1 con meta | Medidor con zonas |
| `bullet` | Bullet Chart | 1 con meta | Medidor compacto |

### Comparativos

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `comparison` | Comparación | 2 | Compara 2 métricas |
| `period_comparison` | Comparación Períodos | 1 | Compara 2 períodos |
| `radar_comparison` | Radar | 3-5 | Comparación radial |

### Otros

| Tipo | Nombre | Métricas | Descripción |
|------|--------|----------|-------------|
| `donut` | Dona | 2-6 | Distribución circular |
| `mixed` | Mixto | 2 | Barras + líneas |
| `scatter` | Dispersión | 2 | Relación XY |
| `data_table` | Tabla | 1-6 | Vista tabular |

---

## Mejores Prácticas

### Organización de Departamentos y Áreas

✅ **Buenas prácticas:**
- Usa nombres claros y descriptivos
- Mantén una jerarquía lógica
- Asigna colores consistentes (ej: todos los dept de ventas en azul)
- Usa íconos representativos

❌ **Evita:**
- Nombres genéricos ("Área 1", "Departamento A")
- Demasiados niveles de jerarquía
- Colores muy similares entre departamentos

### Gestión de Métricas

✅ **Buenas prácticas:**
- Nombres descriptivos y únicos
- Unidades de medida claras
- Define metas realistas
- Documenta cómo se calcula cada métrica

❌ **Evita:**
- Métricas duplicadas
- Cambiar tipo de valor de métricas en uso
- Metas inalcanzables o muy bajas

### Configuración de Dashboards

✅ **Buenas prácticas:**
- Agrupa gráficos relacionados
- Los KPIs importantes arriba
- Gráficos de tendencia en el centro
- Tablas detalladas abajo
- Máximo 12 widgets por dashboard

❌ **Evita:**
- Dashboard muy cargados (lentitud)
- Gráficos sin título claro
- Muchos colores diferentes
- Widgets muy pequeños

---

## Solución de Problemas Comunes

### Gráfico no muestra datos

**Verificar:**
1. ¿La métrica tiene valores para ese período?
2. ¿La métrica está activa?
3. ¿El período está activo?
4. ¿La configuración del gráfico es correcta?

### Usuario no puede acceder

**Verificar:**
1. ¿El usuario está activo?
2. ¿Tiene rol asignado?
3. ¿Tiene departamento/área asignada?
4. ¿El departamento/área están activos?

### Layout no se guarda

**Verificar:**
1. ¿Estás en modo edición?
2. ¿Hiciste clic en "Guardar y Salir"?
3. ¿Hay errores en consola del navegador?
4. ¿Tienes permisos de admin?

---

## Recursos Adicionales

- [Guía de Usuario](GUIA-USUARIO.md)
- [Guía de Desarrollo](GUIA-DESARROLLO.md)
- [API de Gráficos](API-GRAFICOS.md)
- [Base de Datos](DATABASE.md)
- [Checklist de Despliegue](../CHECKLIST-DESPLIEGUE.md)

---

**¿Necesitas ayuda?** Contacta a soporte técnico.
