# Guía de Usuario - Sistema de Métricas

Esta guía te ayudará a utilizar el Sistema de Métricas para visualizar y analizar los indicadores de tu área o departamento.

## Tabla de Contenidos

1. [Acceso al Sistema](#acceso-al-sistema)
2. [Navegación Básica](#navegación-básica)
3. [Visualización de Dashboards](#visualización-de-dashboards)
4. [Cambio de Período](#cambio-de-período)
5. [Interpretación de Gráficos](#interpretación-de-gráficos)
6. [Exportación de Reportes](#exportación-de-reportes)
7. [Personalización](#personalización)

---

## Acceso al Sistema

### Iniciar Sesión

1. Accede a la URL del sistema (ej: `https://metricas.tuempresa.com`)
2. Ingresa tu **nombre de usuario**
3. Ingresa tu **contraseña**
4. Haz clic en **"Iniciar Sesión"**

**Primer acceso:**
- Si es tu primer acceso, cambia tu contraseña inmediatamente
- Ve a tu perfil (icono de usuario arriba a la derecha)
- Selecciona "Cambiar Contraseña"

### Recuperar Contraseña

Si olvidaste tu contraseña, contacta al administrador del sistema.

---

## Navegación Básica

### Barra Superior

La barra superior contiene:

**Izquierda:**
- **Logo/Nombre**: Sistema de Métricas
- **Departamentos** (si tienes acceso a más de uno): Selector de departamento
- **Áreas**: Pestañas para cambiar entre áreas

**Derecha:**
- **Modo Claro/Oscuro** 🌙: Toggle para cambiar tema
- **Usuario**: Tu nombre e información
  - Mi Perfil
  - Cerrar Sesión

### Selector de Departamento

Si eres Super Admin o tienes acceso a múltiples departamentos:

1. Haz clic en el dropdown de departamentos
2. Selecciona el departamento que deseas ver
3. Las áreas se actualizarán automáticamente

### Navegación por Áreas

Las áreas se muestran como pestañas debajo de la barra principal:

- **Clic en área**: Muestra el dashboard de esa área
- **Color del área**: Cada área tiene su color identificador
- **Área activa**: Se destaca con fondo de color y texto blanco

---

## Visualización de Dashboards

### Componentes del Dashboard

Un dashboard contiene:

1. **Selector de Período**: En la parte superior derecha
2. **Gráficos/Widgets**: Organizados en una cuadrícula
3. **Botones de Acción** (solo para administradores):
   - Modo Edición
   - Guardar y Salir
   - Agregar Gráfico

### Tipos de Widgets

El dashboard puede contener diferentes tipos de visualizaciones:

#### 1. **KPI Cards**
- Muestra el valor actual de una métrica
- Comparación con período anterior (↑ ↓)
- Porcentaje de cambio
- Color verde (mejora) o rojo (disminución)

#### 2. **Gráficos de Línea**
- Evolución temporal de una o más métricas
- Útil para identificar tendencias
- Puede incluir línea de meta

#### 3. **Gráficos de Barras**
- Comparación de valores
- Puede ser simple, múltiple o apilado
- Útil para comparar períodos o métricas

#### 4. **Medidores (Gauges)**
- Visualización tipo velocímetro
- Muestra qué tan cerca estás de la meta
- Zonas de color (rojo, amarillo, verde)

#### 5. **Gráficos de Dona**
- Distribución porcentual
- Útil para ver composición
- Muestra total en el centro

#### 6. **Tablas de Datos**
- Vista tabular de valores
- Incluye tendencias (↑ ↓)
- Útil para análisis detallado

---

## Cambio de Período

### Selector de Período

Ubicado en la parte superior derecha del dashboard:

1. Haz clic en el selector de período (ej: "Abril 2026")
2. Se despliega un calendario/lista de períodos
3. Selecciona el mes y año que deseas visualizar
4. El dashboard se actualiza automáticamente

**Nota:** Solo puedes seleccionar períodos activos que tengan datos.

### Períodos Disponibles

- **Períodos mensuales**: Enero a Diciembre
- **Año actual**: Los meses del año en curso
- **Años anteriores**: Si hay datos históricos

---

## Interpretación de Gráficos

### Indicadores de Tendencia

#### Flecha Verde ↑
- **Significado**: La métrica aumentó respecto al período anterior
- **Porcentaje**: Muestra cuánto aumentó
- **Bueno si**: La métrica es de tipo "mayor es mejor" (ventas, ingresos, etc.)

#### Flecha Roja ↓
- **Significado**: La métrica disminuyó respecto al período anterior
- **Porcentaje**: Muestra cuánto disminuyó
- **Bueno si**: La métrica es de tipo "menor es mejor" (costos, tiempo de espera, etc.)

### Cumplimiento de Metas

#### Badge Verde "✓ Cumplido"
- La métrica alcanzó o superó la meta
- Porcentaje de cumplimiento ≥ 100%

#### Badge Amarillo "⚠ En Progreso"
- La métrica está cerca de la meta
- Porcentaje de cumplimiento entre 80% y 99%

#### Badge Rojo "✗ No Cumplido"
- La métrica está lejos de la meta
- Porcentaje de cumplimiento < 80%

### Colores en Gráficos

- **Azul**: Valores normales
- **Verde**: Valores positivos o metas cumplidas
- **Rojo**: Valores negativos o metas no cumplidas
- **Naranja**: Advertencias o valores en progreso
- **Morado**: Métricas secundarias

---

## Exportación de Reportes

### Exportar Dashboard (Próximamente)

Funcionalidad en desarrollo:
- Exportar a PDF
- Exportar a Excel
- Programar reportes automáticos
- Envío por correo

### Captura de Pantalla Manual

Mientras tanto, puedes:
1. Usar la tecla "Imprimir Pantalla" (PrtScn)
2. Usar herramientas del navegador:
   - Chrome/Edge: F12 → ... → Capture screenshot
   - Firefox: F12 → ⋮ → Take screenshot

---

## Personalización

### Cambiar Tema (Claro/Oscuro)

1. Haz clic en el icono de sol/luna 🌙 en la barra superior
2. El tema cambia inmediatamente
3. Tu preferencia se guarda automáticamente

**Modo Claro**: Fondo blanco, mejor para ambientes luminosos
**Modo Oscuro**: Fondo oscuro, reduce fatiga visual en ambientes con poca luz

### Personalizar Avatar

1. Haz clic en tu nombre (arriba a la derecha)
2. Selecciona "Mi Perfil"
3. En la sección de avatar:
   - **Subir foto**: Sube una imagen (JPG, PNG)
   - **Elegir icono**: Selecciona un icono de Tabler
   - **Elegir color**: Personaliza el color de fondo

---

## Preguntas Frecuentes

### ¿Por qué no veo datos en algunos gráficos?

**Posibles razones:**
- No hay datos registrados para ese período
- La métrica está desactivada
- No tienes permisos para ver esa métrica

**Solución:**
- Cambia a un período con datos
- Contacta al administrador si crees que es un error

### ¿Cómo sé si una métrica cumplió su meta?

Busca:
- Badge de cumplimiento (verde = cumplido)
- Gráficos con línea punteada (línea de meta)
- Indicadores de progreso (barra o medidor)

### ¿Puedo ver datos de otras áreas?

Depende de tu rol:
- **Super Admin**: Ve todas las áreas
- **Dept Admin**: Ve áreas de su departamento
- **Dept Viewer**: Solo ve su área asignada

### ¿Con qué frecuencia se actualizan los datos?

Los datos se actualizan:
- **En tiempo real** cuando se cambia de período
- **Automáticamente** cuando se guardan nuevos valores
- **Al recargar** la página

---

## Atajos de Teclado

| Atajo | Acción |
|-------|--------|
| `Ctrl + R` | Recargar dashboard |
| `Esc` | Cerrar modal abierto |
| `Tab` | Navegar entre elementos |

---

## Consejos y Mejores Prácticas

### 📊 **Análisis de Tendencias**
- Compara el mismo mes de diferentes años
- Observa patrones estacionales
- Identifica picos y valles

### 🎯 **Seguimiento de Metas**
- Revisa tus dashboards semanalmente
- Identifica métricas en riesgo
- Toma acciones correctivas tempranas

### 📈 **Toma de Decisiones**
- Usa los datos para decisiones informadas
- Comparte insights con tu equipo
- Documenta hallazgos importantes

---

## Soporte

¿Necesitas ayuda?

- **Documentación**: Lee las guías en `/docs`
- **Administrador**: Contacta al admin de tu departamento
- **Soporte técnico**: soporte@tuempresa.com
- **Reportar error**: Usa el sistema de tickets

---

**¡Aprovecha al máximo tus datos!** 📊✨
