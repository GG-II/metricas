# Manual de Usuario - Sistema de Métricas

**Versión:** 2.2  
**Fecha:** Abril 2026  
**Organización:** [Nombre de la Cooperativa]

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Roles y Permisos](#roles-y-permisos)
3. [Acceso al Sistema](#acceso-al-sistema)
4. [Guía por Rol](#guía-por-rol)
   - 4.1 [Super Administrador](#super-administrador)
   - 4.2 [Administrador de Departamento](#administrador-de-departamento)
   - 4.3 [Administrador de Área](#administrador-de-área)
   - 4.4 [Visualizador](#visualizador)
5. [Tareas Comunes](#tareas-comunes)
   - 5.1 [Ingresar Valores de Métricas](#ingresar-valores-de-métricas)
   - 5.2 [Definir Metas](#definir-metas)
   - 5.3 [Crear y Configurar Gráficos](#crear-y-configurar-gráficos)
   - 5.4 [Consultar Dashboards](#consultar-dashboards)
6. [Configuración Inicial](#configuración-inicial)
7. [Preguntas Frecuentes](#preguntas-frecuentes)
8. [Troubleshooting](#troubleshooting)
9. [Glosario](#glosario)

---

## 1. Introducción

### ¿Qué es el Sistema de Métricas?

El Sistema de Métricas es una plataforma web diseñada para gestionar, monitorear y visualizar indicadores de desempeño (KPIs) de su organización. Permite:

- **Organizar** la estructura de la cooperativa en departamentos y áreas
- **Definir** métricas específicas para cada área operativa
- **Establecer** metas anuales y mensuales
- **Registrar** valores reales periódicamente
- **Visualizar** el desempeño mediante dashboards interactivos
- **Analizar** cumplimiento de objetivos con semáforos automáticos

### Beneficios Clave

✅ **Centralización:** Toda la información en un solo lugar  
✅ **Tiempo real:** Dashboards actualizados automáticamente  
✅ **Colaboración:** Múltiples usuarios con roles diferenciados  
✅ **Trazabilidad:** Historial completo de cambios  
✅ **Toma de decisiones:** Visualización clara del desempeño

---

## 2. Roles y Permisos

El sistema cuenta con **4 roles** diferentes. Cada rol tiene permisos específicos según su nivel de responsabilidad.

### Tabla Comparativa de Roles

| Característica | Super Admin | Admin Departamento | Admin Área | Visualizador |
|----------------|-------------|-------------------|------------|--------------|
| **Alcance** | Todo el sistema | Su departamento | Solo su área | Su departamento |
| **Ver todas las áreas** | ✅ Sí | ✅ Sí | ❌ No | ✅ Sí |
| **Navegar entre áreas** | ✅ Sí | ✅ Sí | ❌ No | ✅ Sí |
| **Crear usuarios** | ✅ Sí | ❌ No | ❌ No | ❌ No |
| **Crear departamentos** | ✅ Sí | ❌ No | ❌ No | ❌ No |
| **Crear áreas** | ✅ Sí | ✅ En su dept | ❌ No | ❌ No |
| **Crear métricas** | ✅ Sí | ✅ En su dept | ✅ En su área | ❌ No |
| **Definir metas** | ✅ Sí | ✅ En su dept | ✅ En su área | ❌ No |
| **Ingresar valores** | ✅ Sí | ✅ En su dept | ✅ En su área | ❌ No |
| **Crear gráficos** | ✅ Sí | ✅ En su dept | ✅ En su área | ❌ No |
| **Ver métricas globales** | ✅ Sí | ❌ No | ❌ No | ❌ No |

### Descripción de Roles

#### Super Administrador (super_admin)
- **Responsabilidad:** Configuración y administración completa del sistema
- **Usuarios típicos:** Gerente General, Director TI, Administrador del Sistema
- **Acceso:** Ilimitado a todas las funciones y datos

#### Administrador de Departamento (dept_admin)
- **Responsabilidad:** Gestión completa de su departamento
- **Usuarios típicos:** Gerente de Agencia, Director de Área Corporativa
- **Acceso:** Todas las áreas dentro de su departamento

#### Administrador de Área (area_admin)
- **Responsabilidad:** Gestión de un área específica
- **Usuarios típicos:** Supervisor de Caja, Jefe de Créditos, Coordinador de Cuentas
- **Acceso:** Solo su área asignada (no puede ver otras áreas)

#### Visualizador (dept_viewer)
- **Responsabilidad:** Consulta de información sin capacidad de edición
- **Usuarios típicos:** Analistas, Auditoría, Junta Directiva
- **Acceso:** Ver todas las áreas de su departamento en modo solo lectura

[INSERTAR CAPTURA DE: Pantalla de login mostrando los diferentes usuarios]

---

## 3. Acceso al Sistema

### Inicio de Sesión

1. Abra su navegador web (Chrome, Firefox, Edge)
2. Ingrese la URL: `http://[servidor]/metricas/public/`
3. Ingrese su **usuario** y **contraseña**
4. Haga clic en **"Iniciar Sesión"**

[INSERTAR CAPTURA DE: Pantalla de login]

### Primera Vez en el Sistema

Al ingresar por primera vez:

1. Se le pedirá cambiar su contraseña temporal
2. Use una contraseña segura (mínimo 8 caracteres, letras y números)
3. Anote su contraseña en un lugar seguro

**⚠️ Importante:** Si olvida su contraseña, contacte al Super Administrador.

### Navegación Principal

El sistema cuenta con:

- **Barra superior:** Logo, navegación de áreas, modo oscuro, perfil de usuario
- **Menú principal:** Acceso rápido a módulos según su rol
- **Dashboard:** Área principal con gráficos y métricas
- **Breadcrumbs:** Migas de pan para saber dónde está

[INSERTAR CAPTURA DE: Interfaz principal del sistema con elementos señalados]

### Modo Oscuro

Para activar/desactivar el modo oscuro:

1. Haga clic en el ícono de luna/sol en la barra superior
2. El sistema recordará su preferencia

---

## 4. Guía por Rol

### 4.1 Super Administrador

Como Super Administrador, usted es responsable de la **configuración inicial** y **administración completa** del sistema.

#### 4.1.1 Configuración Inicial del Sistema

**Orden recomendado de configuración:**

**Paso 1: Crear Departamentos**

1. Vaya a **Administración > Departamentos**
2. Haga clic en **"Nuevo Departamento"**
3. Complete el formulario:
   - **Nombre:** Ej: "Agencia San José", "TI", "RRHH"
   - **Tipo:** Agencia (física) o Corporativo (administrativo)
   - **Código:** (Opcional) Código interno
   - **Descripción:** Breve descripción
4. Haga clic en **"Guardar"**

[INSERTAR CAPTURA DE: Formulario de crear departamento]

**💡 Tip:** Cree también el departamento especial **"Global"** para métricas consolidadas de toda la organización.

**Paso 2: Crear Áreas**

1. Vaya a **Administración > Áreas**
2. Haga clic en **"Nueva Área"**
3. Complete el formulario:
   - **Departamento:** Seleccione el departamento padre
   - **Nombre:** Ej: "Créditos", "Caja", "Cuentas Nuevas"
   - **Color:** Elija un color identificador (se usa en gráficos)
   - **Descripción:** Funciones del área
4. Haga clic en **"Guardar"**

[INSERTAR CAPTURA DE: Formulario de crear área]

**Ejemplo de estructura:**
```
📁 Agencia San José
  └─ Créditos
  └─ Caja
  └─ Cuentas Nuevas
  └─ Atención al Cliente

📁 TI
  └─ Desarrollo
  └─ Infraestructura
  └─ Soporte

📁 Global
  └─ Métricas Consolidadas
```

**Paso 3: Configurar Períodos**

1. Vaya a **Administración > Períodos**
2. Haga clic en **"Nuevo Período"**
3. Complete:
   - **Ejercicio:** Año fiscal (Ej: 2026)
   - **Mes:** Seleccione el mes
   - **Nombre:** Se genera automáticamente (Ej: "Enero 2026")
4. Haga clic en **"Guardar"**

**💡 Tip:** Puede crear períodos por lotes seleccionando el año y marcando todos los meses.

[INSERTAR CAPTURA DE: Crear períodos]

**Paso 4: Crear Usuarios**

1. Vaya a **Administración > Usuarios**
2. Haga clic en **"Nuevo Usuario"**
3. Complete el formulario:
   - **Nombre completo:** Juan Pérez
   - **Usuario:** jperez
   - **Email:** jperez@cooperativa.com
   - **Contraseña:** Temporal (el usuario la cambiará)
   - **Rol:** Seleccione según responsabilidad
   - **Departamento:** (Requerido para todos excepto super_admin)
   - **Área:** (Requerido solo para area_admin)
4. Haga clic en **"Guardar"**

[INSERTAR CAPTURA DE: Formulario de crear usuario]

**⚠️ Importante:**
- **dept_admin** requiere departamento asignado
- **area_admin** requiere departamento Y área asignada
- La contraseña temporal debe comunicarse de forma segura

#### 4.1.2 Configuración de Métricas

**Paso 5: Crear Métricas**

1. Vaya a **Administración > Métricas**
2. Haga clic en **"Nueva Métrica"**
3. Complete el formulario:

**Información básica:**
- **Área:** Seleccione el área propietaria
- **Nombre:** Descripción clara (Ej: "Colocación de Créditos")
- **Unidad:** ₡, USD, %, cantidad, etc.
- **Ícono:** Icono visual para dashboards

**Configuración:**
- **Tipo de métrica:**
  - **Simple:** Valor que se ingresa manualmente
  - **Calculada:** Suma/promedio de otras métricas del área
  - **Global:** Consolida métricas de múltiples áreas
- **Tiene meta:** ¿Se puede definir objetivos? (Sí/No)
- **Frecuencia de actualización:** Diaria, semanal, mensual

[INSERTAR CAPTURA DE: Formulario de crear métrica]

**Ejemplos de métricas por tipo:**

| Tipo | Ejemplo | Descripción |
|------|---------|-------------|
| Simple | Colocación de Créditos | Se ingresa el monto mensual |
| Calculada | Eficiencia Operativa | Calcula (Créditos / Gastos) × 100 |
| Global | Clientes Totales | Suma asociados de todas las agencias |

**Paso 6: Definir Metas**

1. Vaya a **Administración > Metas**
2. Seleccione una métrica de la lista izquierda
3. Haga clic en **"Nueva Meta"**
4. Seleccione tipo de meta:

**Meta Anual:**
- **Ejercicio:** Año (Ej: 2026)
- **Valor objetivo:** Ej: ₡72,000,000
- **Comparación:** Mayor o igual (cumple si supera)
- El sistema divide automáticamente: ₡6,000,000/mes

**Meta Mensual:**
- **Período:** Ej: Enero 2026
- **Valor objetivo:** Ej: ₡6,500,000
- **Comparación:** Mayor o igual

[INSERTAR CAPTURA DE: Formulario de crear meta con info dinámica]

**💡 Tip:** Cree primero la meta anual. El sistema le ayudará a distribuir las metas mensuales y validará que no excedan el total.

**Paso 7: Configurar Gráficos**

1. Vaya a **Administración > Gráficos**
2. Seleccione el área donde crear el gráfico
3. Haga clic en **"Nuevo Gráfico"**
4. Complete:
   - **Tipo de gráfico:** Seleccione entre 24+ opciones
   - **Título:** Nombre del gráfico
   - **Tamaño:** Completo, medio, tercio
   - **Posición:** Orden en el dashboard
   - **Métrica(s):** Seleccione qué métricas mostrar
   - **Configuración específica:** Según el tipo de gráfico

[INSERTAR CAPTURA DE: Catálogo de tipos de gráficos disponibles]

**Tipos de gráficos más usados:**

| Tipo | Uso Recomendado |
|------|----------------|
| **KPI con Meta** | Mostrar valor actual vs objetivo |
| **Línea con Meta** | Tendencia mensual vs objetivo |
| **Barra Horizontal** | Comparar múltiples métricas |
| **Gauge** | Porcentaje de cumplimiento |
| **Donut** | Distribución porcentual |
| **Comparación de Períodos** | Este mes vs mes anterior |

#### 4.1.3 Gestión de Usuarios

**Ver lista de usuarios:**

1. Vaya a **Administración > Usuarios**
2. Verá todos los usuarios con badges de color según rol:
   - 🔴 Rojo: Super Admin
   - 🔵 Azul: Admin Departamento
   - 🟣 Morado: Admin Área
   - 🟢 Verde: Visualizador

[INSERTAR CAPTURA DE: Lista de usuarios con badges]

**Editar un usuario:**

1. Haga clic en el botón **"Editar"** del usuario
2. Modifique los campos necesarios
3. **No puede cambiar:** el username
4. **Puede cambiar:** nombre, email, rol, departamento, área
5. Haga clic en **"Actualizar"**

**Desactivar un usuario:**

1. Haga clic en **"Editar"**
2. Desmarque **"Usuario activo"**
3. Haga clic en **"Actualizar"**
4. El usuario ya no podrá iniciar sesión

**⚠️ No elimine usuarios,** solo desactívelos para mantener trazabilidad.

**Resetear contraseña:**

1. Edite el usuario
2. Ingrese una nueva contraseña temporal
3. Guarde
4. Comunique la contraseña al usuario de forma segura

---

### 4.2 Administrador de Departamento

Como Administrador de Departamento, usted gestiona **todas las áreas** dentro de su departamento.

#### 4.2.1 Dashboard de su Departamento

Al iniciar sesión, verá:

1. **Selector de áreas:** En la barra superior
2. **Dashboard del área seleccionada:** Con todos los gráficos configurados
3. **Navegación entre áreas:** Puede cambiar entre áreas de su departamento

[INSERTAR CAPTURA DE: Dashboard de departamento con selector de áreas]

#### 4.2.2 Gestionar Áreas de su Departamento

Aunque no puede crear el departamento, sí puede:

**Crear áreas dentro de su departamento:**

1. Vaya a **Administración > Áreas**
2. Haga clic en **"Nueva Área"**
3. El campo **Departamento** ya está preseleccionado (su departamento)
4. Complete nombre, color, descripción
5. Haga clic en **"Guardar"**

**Editar áreas existentes:**

1. En la lista de áreas, haga clic en **"Editar"**
2. Modifique los campos necesarios
3. Haga clic en **"Actualizar"**

#### 4.2.3 Gestionar Métricas

Puede crear y editar métricas para cualquier área de su departamento:

1. Vaya a **Administración > Métricas**
2. Haga clic en **"Nueva Métrica"**
3. Seleccione el **área** dentro de su departamento
4. Complete el formulario (ver sección 4.1.2)
5. Haga clic en **"Guardar"**

**Ver métricas existentes:**

- Puede filtrar por área usando el selector
- Solo verá métricas de su departamento

#### 4.2.4 Definir Metas

1. Vaya a **Administración > Metas**
2. Seleccione una métrica de su departamento
3. Cree metas anuales y/o mensuales
4. El sistema valida automáticamente coherencia entre metas

**💡 Ejemplo práctico:**

```
Métrica: Colocación de Créditos (Agencia San José - Créditos)

Meta Anual 2026: ₡72,000,000
  ↓
Sistema sugiere: ₡6,000,000/mes

Puede ajustar meses específicos:
  - Enero: ₡5,000,000 (mes bajo)
  - Diciembre: ₡8,000,000 (mes alto)

Sistema valida que la suma ≤ ₡72,000,000
```

#### 4.2.5 Configurar Dashboards

1. Vaya a **Administración > Gráficos**
2. Seleccione el área
3. Cree gráficos para visualizar métricas
4. Ordene los gráficos arrastrando (drag & drop)
5. Previsualice cómo se verá el dashboard

[INSERTAR CAPTURA DE: Configuración de dashboard con drag & drop]

#### 4.2.6 Ingresar Valores

Como dept_admin, puede ingresar valores en cualquier área de su departamento:

1. Vaya a **Captura de Valores**
2. Seleccione **Área** y **Período**
3. Ingrese los valores de las métricas
4. El sistema muestra:
   - ✅ Verde: Cumple meta
   - ⚠️ Amarillo: Cerca de meta
   - ❌ Rojo: No cumple meta
5. Puede agregar **Notas** para contexto
6. Haga clic en **"Guardar"**

[INSERTAR CAPTURA DE: Formulario de captura de valores con semáforos]

---

### 4.3 Administrador de Área

Como Administrador de Área, su acceso está **limitado a una sola área específica**. No puede navegar a otras áreas.

#### 4.3.1 Acceso Restringido

Al iniciar sesión:

1. Es redirigido automáticamente a su área asignada
2. **No verá** el selector de áreas en la barra superior
3. **Solo puede** gestionar su área
4. **No puede** ver dashboards de otras áreas

[INSERTAR CAPTURA DE: Vista de area_admin sin selector de áreas]

#### 4.3.2 Gestionar Métricas de su Área

Puede crear métricas solo para su área:

1. Vaya a **Administración > Métricas**
2. El campo **Área** ya está preseleccionado (su área asignada)
3. Complete el formulario
4. Haga clic en **"Guardar"**

**⚠️ Limitación:** No puede crear métricas calculadas globales (esas son solo para super_admin).

#### 4.3.3 Definir Metas

1. Vaya a **Administración > Metas**
2. Solo verá métricas de su área
3. Defina metas anuales y mensuales
4. El sistema valida automáticamente

#### 4.3.4 Configurar Dashboard

1. Vaya a **Administración > Gráficos**
2. Cree y organice gráficos para su área
3. Configure tipos de gráficos según sus necesidades
4. Previsualice el dashboard

#### 4.3.5 Ingresar Valores

1. Vaya a **Captura de Valores**
2. El **Área** ya está preseleccionada
3. Seleccione el **Período**
4. Ingrese valores
5. Agregue notas si es necesario
6. Haga clic en **"Guardar"**

**💡 Caso de uso típico:**

```
Usuario: María López
Rol: area_admin
Área asignada: Cuentas Nuevas (Agencia Heredia)

Puede hacer:
  ✅ Ingresar asociados nuevos mensuales
  ✅ Configurar meta de tarjetas emitidas
  ✅ Ver cumplimiento de su área
  ✅ Crear gráficos para su dashboard

NO puede hacer:
  ❌ Ver área de Créditos
  ❌ Ver área de Caja
  ❌ Navegar a otras agencias
  ❌ Crear usuarios
```

---

### 4.4 Visualizador

Como Visualizador, tiene acceso de **solo lectura** a las áreas de su departamento.

#### 4.4.1 Consultar Dashboards

1. Al iniciar sesión, verá el dashboard del área predeterminada
2. Puede navegar entre áreas usando el selector superior
3. Puede cambiar de período para ver datos históricos
4. Puede activar/desactivar modo oscuro

[INSERTAR CAPTURA DE: Dashboard en modo visualizador]

#### 4.4.2 Ver Detalles de Métricas

1. Haga clic en cualquier gráfico
2. Verá información detallada:
   - Valor actual
   - Meta (si existe)
   - Porcentaje de cumplimiento
   - Historial de valores
   - Notas agregadas

[INSERTAR CAPTURA DE: Modal de detalle de métrica]

#### 4.4.3 Limitaciones

Como visualizador **NO puede:**

- ❌ Ingresar valores
- ❌ Definir metas
- ❌ Crear/editar métricas
- ❌ Configurar gráficos
- ❌ Gestionar usuarios
- ❌ Modificar estructura

**Solo puede:** Ver y consultar información.

---

## 5. Tareas Comunes

### 5.1 Ingresar Valores de Métricas

**¿Quién puede hacerlo?** super_admin, dept_admin, area_admin

**Pasos:**

1. Vaya a **Captura de Valores** (desde el menú principal)
2. Seleccione el **Área** (si tiene acceso a múltiples)
3. Seleccione el **Período** (mes/año)
4. El sistema mostrará todas las métricas simples del área

[INSERTAR CAPTURA DE: Pantalla de captura de valores]

**Para cada métrica:**

1. Ingrese el valor en el campo correspondiente
2. El sistema muestra inmediatamente:
   - **Semáforo de cumplimiento:**
     - 🟢 Verde (90-100%+): Cumple o supera meta
     - 🟡 Amarillo (70-89%): Cerca de cumplir
     - 🔴 Rojo (<70%): No cumple
   - **Porcentaje de cumplimiento:** Ej: 105% (supera meta en 5%)
   - **Diferencia vs meta:** Ej: +₡500,000 (sobre la meta)

3. **(Opcional)** Agregue una **nota** para dar contexto:
   ```
   Ejemplo: "Se alcanzó meta gracias a campaña especial de Navidad"
   ```

4. Haga clic en **"Guardar"**

**💡 Tips:**

- Puede ingresar valores parciales y regresar después
- Los valores se guardan individualmente (no pierde trabajo si sale)
- Las métricas calculadas se actualizan automáticamente

**Ejemplo práctico:**

```
Área: Créditos (Agencia San José)
Período: Marzo 2026

Métricas a ingresar:
┌─────────────────────────────┬──────────────┬────────┬──────────────┐
│ Métrica                     │ Valor        │ Meta   │ Cumplimiento │
├─────────────────────────────┼──────────────┼────────┼──────────────┤
│ Colocación de Créditos      │ ₡6,800,000   │ ₡6,000 │ 113% 🟢      │
│ Cantidad de Créditos        │ 45           │ 40     │ 112% 🟢      │
│ Morosidad (%)               │ 2.1%         │ <3%    │ ✓ Cumple 🟢  │
└─────────────────────────────┴──────────────┴────────┴──────────────┘

Métrica calculada (automática):
  Ticket Promedio = ₡6,800,000 / 45 = ₡151,111
```

[INSERTAR CAPTURA DE: Valores ingresados con semáforos]

### 5.2 Definir Metas

**¿Quién puede hacerlo?** super_admin, dept_admin, area_admin

**Flujo recomendado:**

#### Paso 1: Crear Meta Anual

1. Vaya a **Administración > Metas**
2. Seleccione la métrica en la lista izquierda
3. Haga clic en **"Nueva Meta"**
4. Seleccione **"Meta Anual"**
5. Ingrese:
   - **Ejercicio:** 2026
   - **Valor objetivo:** ₡72,000,000
   - **Comparación:** Mayor o igual
6. Haga clic en **"Crear"**

[INSERTAR CAPTURA DE: Formulario meta anual]

El sistema muestra:
```
Meta Anual: ₡72,000,000
Promedio mensual: ₡6,000,000
```

#### Paso 2: Crear Metas Mensuales (Opcional)

Si desea personalizar algunos meses:

1. Haga clic en **"Nueva Meta"**
2. Seleccione **"Meta Mensual"**
3. Seleccione el **período** (Ej: Diciembre 2026)
4. El sistema muestra info dinámica:

```
📊 Información de Meta:

Meta anual: ₡72,000,000
✅ Ya asignado en 11 meses: ₡66,000,000 (91.7%)
📅 Meses sin asignar: 1
💡 Valor sugerido: ₡6,000,000
⚠️ Máximo permitido: ₡6,000,000
```

5. Ingrese el valor (el sistema valida que no exceda la meta anual)
6. Haga clic en **"Crear"**

**💡 Validaciones automáticas:**

- ✅ La suma de metas mensuales no puede exceder la meta anual
- ✅ Si modifica la meta anual, debe ser ≥ suma de mensuales existentes
- ✅ El sistema sugiere valores para distribuir equitativamente

**Caso especial: Sin meta anual**

Si crea metas mensuales sin meta anual:

```
ℹ️ Sin meta anual configurada

Ya asignado en 3 meses: ₡18,500,000
Puedes ingresar cualquier valor para este mes.

💡 Tip: Considera crear una meta anual primero para mejor control.
```

[INSERTAR CAPTURA DE: Información dinámica de metas]

### 5.3 Crear y Configurar Gráficos

**¿Quién puede hacerlo?** super_admin, dept_admin, area_admin

#### Catálogo de Tipos de Gráficos

El sistema ofrece **24+ tipos de gráficos**. Los más usados:

**Gráficos de Métricas Individuales:**

| Tipo | Descripción | Cuándo usar |
|------|-------------|-------------|
| **KPI Card** | Número grande con etiqueta | Mostrar valor actual simple |
| **KPI con Meta** | Valor + meta + % cumplimiento | KPI con objetivo definido |
| **Gauge** | Velocímetro circular | Porcentaje de 0-100% |
| **Gauge con Meta** | Gauge + comparación vs meta | Cumplimiento visual de meta |
| **Barra de Porcentaje** | Barra horizontal con % | Progreso lineal |

**Gráficos de Tendencia:**

| Tipo | Descripción | Cuándo usar |
|------|-------------|-------------|
| **Línea** | Línea de tendencia temporal | Ver evolución en el tiempo |
| **Línea con Meta** | Línea + línea de meta | Comparar tendencia vs objetivo |
| **Área** | Gráfico de área rellena | Enfatizar volumen acumulado |
| **Área Apilada** | Múltiples áreas apiladas | Mostrar composición temporal |
| **Sparkline** | Mini gráfico de tendencia | Indicador compacto |

**Gráficos Comparativos:**

| Tipo | Descripción | Cuándo usar |
|------|-------------|-------------|
| **Barra Horizontal** | Barras horizontales | Comparar múltiples métricas |
| **Barra Vertical** | Barras verticales | Comparar valores por categoría |
| **Barra Apilada** | Barras con segmentos | Mostrar composición |
| **Donut** | Gráfico de dona | Distribución porcentual |
| **Radar** | Gráfico radial | Comparar dimensiones múltiples |

**Gráficos Avanzados:**

| Tipo | Descripción | Cuándo usar |
|------|-------------|-------------|
| **Comparación de Períodos** | Este mes vs mes anterior | Análisis de cambios |
| **Bullet Chart** | Barra con rangos de desempeño | Cumplimiento con contexto |
| **Mixed** | Barras + líneas combinadas | Métricas de diferentes escalas |
| **Scatter** | Puntos de dispersión | Correlaciones |

[INSERTAR CAPTURA DE: Galería visual de tipos de gráficos]

#### Crear un Gráfico Paso a Paso

**Ejemplo: KPI con Meta**

1. Vaya a **Administración > Gráficos**
2. Seleccione el **área**
3. Haga clic en **"Nuevo Gráfico"**
4. Complete el formulario:

**Configuración básica:**
```
Tipo de gráfico: KPI con Meta
Título: Colocación Mensual
Tamaño: Tercio (ocupa 1/3 del ancho)
Posición: 1 (aparece primero)
```

**Selección de métrica:**
```
Métrica: Colocación de Créditos
Período: Período actual
```

**Configuración específica (KPI con Meta):**
```
Formato de número: ₡#,##0
Mostrar porcentaje de cumplimiento: ✓ Sí
Color cuando cumple: Verde
Color cuando no cumple: Rojo
```

5. Haga clic en **"Guardar"**
6. El gráfico aparece inmediatamente en el dashboard

[INSERTAR CAPTURA DE: Formulario configuración de gráfico KPI con Meta]

**Ejemplo: Gráfico de Línea con Meta**

1. Tipo: **Línea con Meta**
2. Título: **Tendencia Anual de Colocación**
3. Tamaño: **Completo**
4. Métrica: **Colocación de Créditos**
5. Configuración específica:
   ```
   Período de inicio: Enero 2026
   Período de fin: Diciembre 2026
   Mostrar meta como línea punteada: ✓ Sí
   Suavizar curva: ✓ Sí
   Color de línea: Azul
   Color de meta: Naranja
   ```

[INSERTAR CAPTURA DE: Gráfico de línea con meta resultante]

#### Organizar Dashboard

Para reordenar gráficos:

1. Use el campo **"Posición"** al editar gráfico
2. O arrastre y suelte (drag & drop) en el dashboard
3. Los gráficos se acomodan automáticamente según tamaño:
   - **Completo:** Ocupa todo el ancho
   - **Medio:** 2 por fila
   - **Tercio:** 3 por fila

**💡 Buenas prácticas:**

- Coloque los KPIs más importantes arriba (posición 1-3)
- Use gráficos de tendencia en el medio
- Detalles y desgloses al final
- No sobrecargue: máximo 8-10 gráficos por dashboard

### 5.4 Consultar Dashboards

**¿Quién puede hacerlo?** Todos los roles

#### Navegar entre Áreas

1. Use el **selector de áreas** en la barra superior
2. Seleccione el área que desea consultar
3. El dashboard se actualiza automáticamente

[INSERTAR CAPTURA DE: Selector de áreas desplegado]

**Limitaciones por rol:**
- **area_admin:** No verá el selector (solo tiene un área)
- **dept_viewer:** Solo verá áreas de su departamento
- **super_admin:** Verá todas las áreas de todos los departamentos

#### Cambiar Período

1. Use el **selector de período** en la barra superior
2. Seleccione el mes/año que desea consultar
3. Todos los gráficos se actualizan con datos de ese período

**💡 Tip:** Use esto para análisis histórico y tendencias.

#### Interpretar Gráficos

**Semáforos de cumplimiento:**

- 🟢 **Verde (90-100%+):** Cumple o supera meta
- 🟡 **Amarillo (70-89%):** Cerca de cumplir, requiere atención
- 🔴 **Rojo (<70%):** No cumple, acción urgente necesaria

**Iconos comunes:**

- 📈 **Flecha arriba:** Mejora vs período anterior
- 📉 **Flecha abajo:** Disminución vs período anterior
- ℹ️ **Info:** Información adicional disponible
- ⚠️ **Advertencia:** Requiere atención

#### Ver Detalles

1. Haga clic en cualquier gráfico
2. Se abrirá un modal con:
   - Valor actual detallado
   - Meta (si existe)
   - Historial de valores
   - Notas agregadas
   - Gráfico ampliado

[INSERTAR CAPTURA DE: Modal de detalle expandido]

#### Exportar Dashboard

**(Funcionalidad futura)**

1. Haga clic en **"Exportar"**
2. Seleccione formato: PDF o Excel
3. El sistema genera un reporte con todos los gráficos

---

## 6. Configuración Inicial

### Checklist de Implementación

Para poner el sistema en producción, siga este orden:

**Semana 1: Estructura**
- [ ] Crear todos los departamentos
- [ ] Crear áreas dentro de cada departamento
- [ ] Configurar períodos (año actual + anterior para histórico)
- [ ] Crear usuarios con roles apropiados

**Semana 2: Métricas**
- [ ] Definir métricas simples por área
- [ ] Configurar métricas calculadas
- [ ] Crear métricas globales (consolidadas)
- [ ] Validar que todas las unidades sean correctas

**Semana 3: Objetivos**
- [ ] Definir metas anuales para métricas clave
- [ ] Distribuir metas mensuales
- [ ] Validar coherencia de metas

**Semana 4: Visualización**
- [ ] Configurar dashboards por área
- [ ] Crear gráficos relevantes
- [ ] Organizar layout de dashboards
- [ ] Capacitar usuarios

**Semana 5: Datos Históricos (Opcional)**
- [ ] Ingresar valores de meses anteriores
- [ ] Validar cálculos de métricas calculadas
- [ ] Verificar dashboards con datos reales

**Semana 6: Producción**
- [ ] Pruebas finales con usuarios
- [ ] Ajustes de configuración
- [ ] Go-live oficial
- [ ] Soporte post-implementación

### Datos de Ejemplo Recomendados

Para pruebas, use estos valores de ejemplo:

**Agencia (Créditos):**
```
Métricas:
  - Colocación de Créditos: ₡5,000,000 - ₡8,000,000
  - Cantidad de Créditos: 35 - 50
  - Morosidad: 1.5% - 3.5%
  - Ticket Promedio: (Calculada: Colocación / Cantidad)

Metas Anuales:
  - Colocación: ₡72,000,000 (₡6,000,000/mes)
  - Cantidad: 480 (40/mes)
  - Morosidad: <3%
```

**Agencia (Cuentas Nuevas):**
```
Métricas:
  - Asociados Nuevos: 80 - 120
  - Tarjetas Emitidas: 50 - 80
  - Aportaciones Captadas: ₡2,500,000 - ₡4,000,000

Metas Anuales:
  - Asociados Nuevos: 1,200 (100/mes)
  - Tarjetas: 840 (70/mes)
  - Aportaciones: ₡42,000,000 (₡3,500,000/mes)
```

---

## 7. Preguntas Frecuentes

**P: ¿Puedo cambiar mi rol después de creado?**  
R: Sí, un super_admin puede editar tu usuario y cambiar el rol. Los permisos se actualizan inmediatamente.

**P: ¿Qué pasa si ingreso un valor incorrecto?**  
R: Puedes volver a **Captura de Valores**, seleccionar el mismo período, y sobrescribir el valor. El sistema mantiene historial de cambios.

**P: ¿Puedo eliminar una meta?**  
R: Sí, en **Administración > Metas**, haz clic en el botón de eliminar. La meta se marca como inactiva (no se borra, para mantener historial).

**P: ¿Las métricas calculadas se actualizan automáticamente?**  
R: Sí, cada vez que ingresas un valor de las métricas base, las calculadas se recalculan automáticamente.

**P: ¿Puedo ver datos de años anteriores?**  
R: Sí, usa el **selector de período** para cambiar a meses anteriores. El sistema mantiene todo el historial.

**P: ¿Qué hago si no veo un área que debería ver?**  
R: Verifica con tu super_admin que:
  1. El área esté marcada como "activa"
  2. Pertenezca a tu departamento (si eres dept_admin o dept_viewer)
  3. Sea tu área asignada (si eres area_admin)

**P: ¿Puedo cambiar el color de un área después de creada?**  
R: Sí, en **Administración > Áreas**, edita el área y cambia el color. Los gráficos se actualizan automáticamente.

**P: ¿Cómo defino una meta "menor o igual" (Ej: morosidad)?**  
R: Al crear la meta, selecciona **"≤ Menor o igual"** en el campo Tipo de Comparación. El semáforo se invierte (verde cuando está por debajo).

**P: ¿Puedo tener múltiples metas para la misma métrica?**  
R: Sí, pero solo **una meta anual por ejercicio** y **una meta mensual por período**. No puedes tener 2 metas para Enero 2026.

**P: ¿Qué significa el badge "12 meses" en metas mensuales?**  
R: Indica que hay 12 metas mensuales configuradas para ese año (todos los meses tienen meta).

**P: ¿Puedo exportar datos a Excel?**  
R: (Funcionalidad futura) Próximamente se agregará exportación de reportes.

---

## 8. Troubleshooting

### Problemas Comunes y Soluciones

#### "No puedo iniciar sesión"

**Posibles causas:**
1. Usuario o contraseña incorrectos
2. Usuario desactivado
3. Navegador con cookies bloqueadas

**Soluciones:**
- Verifica mayúsculas/minúsculas en usuario y contraseña
- Contacta al super_admin para verificar que tu usuario esté activo
- Usa navegador Chrome o Firefox actualizado
- Limpia caché y cookies del navegador

#### "No veo el selector de áreas"

**Causa:** Eres usuario **area_admin**

**Solución:** Esto es normal. Los area_admin solo tienen acceso a un área y no pueden navegar a otras. Contacta a tu super_admin si necesitas acceso a más áreas.

#### "No puedo crear una meta mensual"

**Posibles causas:**
1. Ya existe una meta para ese período
2. El valor excede la meta anual
3. El período ya pasó y está bloqueado

**Soluciones:**
- Verifica que no haya ya una meta para ese mes (el sistema oculta períodos usados)
- Si tienes meta anual, el sistema te indica el máximo permitido
- Contacta al super_admin si necesitas editar períodos pasados

#### "El gráfico no muestra datos"

**Posibles causas:**
1. No hay valores ingresados para el período seleccionado
2. La métrica está mal configurada
3. Error en configuración del gráfico

**Soluciones:**
- Cambia el período a uno donde sepas que hay datos
- Verifica en **Captura de Valores** que existan valores para esa métrica
- Edita el gráfico y verifica que la métrica seleccionada sea correcta

#### "La métrica calculada muestra 0"

**Posibles causas:**
1. Las métricas base no tienen valores
2. La fórmula está mal configurada
3. División entre cero

**Soluciones:**
- Ingresa valores en las métricas base primero
- Contacta al super_admin para revisar la fórmula
- Si es un promedio, verifica que el divisor no sea 0

#### "No puedo ver el departamento Global"

**Causa:** No eres **super_admin**

**Solución:** Solo super_admin puede acceder a métricas globales. Esto es por diseño para mantener seguridad de datos consolidados.

#### "El semáforo está en rojo pero cumplí la meta"

**Posible causa:** Tipo de comparación incorrecto

**Solución:** 
- Si es una métrica "menor es mejor" (Ej: morosidad, gastos), la meta debe ser **"≤ Menor o igual"**
- Si es "mayor es mejor" (Ej: ingresos, clientes), debe ser **"≥ Mayor o igual"**
- Edita la meta y cambia el tipo de comparación

#### "El dashboard se ve descuadrado en móvil"

**Solución:**
- El sistema es responsive, pero algunos gráficos complejos se ven mejor en pantallas grandes
- Usa modo horizontal (landscape) en tablet/móvil
- Considera crear dashboards específicos para móvil con gráficos más simples

#### "Olvidé mi contraseña"

**Solución:**
- Contacta al **super_admin**
- Ellos pueden asignarte una contraseña temporal
- Al ingresar, cámbiala inmediatamente

---

## 9. Glosario

**Área:** División operativa dentro de un departamento. Ejemplo: Créditos, Caja, Cuentas Nuevas.

**Dashboard:** Tablero de control visual con múltiples gráficos que muestran métricas del área.

**Departamento:** División organizacional de primer nivel. Ejemplo: Agencia San José, TI, RRHH.

**Ejercicio:** Año fiscal. Ejemplo: 2026.

**KPI (Key Performance Indicator):** Indicador clave de desempeño. Métrica crítica para medir éxito.

**Meta:** Objetivo numérico a alcanzar para una métrica. Puede ser anual o mensual.

**Métrica:** Indicador medible que representa un aspecto del desempeño. Ejemplo: Colocación de Créditos.

**Métrica Calculada:** Métrica cuyo valor se obtiene matemáticamente de otras métricas (suma, promedio, etc.).

**Métrica Global:** Métrica consolidada que agrupa datos de múltiples áreas. Solo visible para super_admin.

**Métrica Simple:** Métrica cuyo valor se ingresa manualmente.

**Período:** Intervalo de tiempo para medir métricas. Generalmente mensual. Ejemplo: Enero 2026.

**Semáforo:** Indicador visual de cumplimiento (verde/amarillo/rojo) según % de meta alcanzado.

**Tipo de Comparación:** Define cómo se evalúa el cumplimiento de una meta:
  - **Mayor o igual (≥):** Cumple si el valor es mayor o igual a la meta
  - **Menor o igual (≤):** Cumple si el valor es menor o igual a la meta
  - **Igual (=):** Cumple solo si el valor es exactamente la meta

**Unidad:** Tipo de medida de una métrica. Ejemplo: ₡ (colones), USD, %, cantidad, kg, etc.

---

## Contacto y Soporte

**Soporte Técnico:**  
Email: [soporte@cooperativa.com]  
Teléfono: [número]  
Horario: Lunes a viernes, 8:00 AM - 5:00 PM

**Administrador del Sistema:**  
[Nombre del Super Admin]  
Email: [email]  
Extensión: [ext]

**Documentación Adicional:**  
- Manual Técnico (para desarrolladores)
- Guía de API
- Videos tutoriales: [URL]

---

**Fin del Manual de Usuario**

*Sistema de Métricas v2.2 - Abril 2026*
