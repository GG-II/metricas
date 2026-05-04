# CHEATSHEET - Sistema de Métricas

**Referencia Rápida de 2 Páginas**

---

## Roles y Permisos Rápidos

| Rol | Puede ver | Puede editar | Acceso admin |
|-----|-----------|--------------|--------------|
| **Super Admin** | Todo | Todo | ✅ Total |
| **Dept Admin** | Su dept | Su dept | ⚙️ Áreas, métricas |
| **Area Admin** | Solo su área | Solo su área | ⚙️ Métricas de su área |
| **Viewer** | Su dept | ❌ Nada | ❌ No |

---

## Tareas Frecuentes

### Ingresar Valores
```
1. Captura de Valores
2. Seleccionar Área + Período
3. Ingresar valores
4. Guardar

Semáforo:
🟢 90-100%+ = Cumple
🟡 70-89% = Cerca
🔴 <70% = No cumple
```

### Crear Meta Anual
```
1. Admin > Metas
2. Seleccionar métrica
3. Nueva Meta > Anual
4. Año + Valor + Comparación
5. Guardar

💡 Se divide automáticamente /12
```

### Crear Gráfico
```
1. Admin > Gráficos
2. Seleccionar área
3. Nuevo Gráfico
4. Tipo + Título + Tamaño
5. Métrica + Config
6. Guardar

Tamaños:
- Completo: 100% ancho
- Medio: 50% ancho
- Tercio: 33% ancho
```

---

## Tipos de Gráficos Más Usados

| Tipo | Uso |
|------|-----|
| **KPI con Meta** | Valor grande + % cumplimiento |
| **Línea con Meta** | Tendencia mensual vs objetivo |
| **Gauge** | Porcentaje 0-100% |
| **Barra Horizontal** | Comparar varias métricas |
| **Donut** | Distribución porcentual |

---

## Tipos de Métricas

| Tipo | Descripción | Ejemplo |
|------|-------------|---------|
| **Simple** | Ingreso manual | Colocación de créditos |
| **Calculada** | Suma/promedio de otras | Eficiencia = Créditos/Gastos |
| **Global** | Consolida áreas | Clientes totales (suma agencias) |

---

## Tipos de Comparación (Metas)

- **≥ Mayor o igual:** Para ingresos, ventas, clientes (más es mejor)
- **≤ Menor o igual:** Para gastos, morosidad, quejas (menos es mejor)
- **= Igual:** Para cumplimiento exacto

---

## Atajos de Navegación

- **Selector de Áreas:** Barra superior izquierda
- **Selector de Período:** Barra superior centro
- **Modo Oscuro:** Ícono luna/sol (barra superior derecha)
- **Perfil de Usuario:** Esquina superior derecha

---

## Troubleshooting Express

| Problema | Solución |
|----------|----------|
| No puedo login | Verifica usuario/contraseña. Contacta super admin |
| No veo selector de áreas | Normal si eres area_admin |
| Gráfico sin datos | Verifica que haya valores en ese período |
| No puedo crear meta mensual | Ya existe o excede meta anual |
| Semáforo incorrecto | Revisa tipo de comparación de la meta |
| Métrica calculada = 0 | Ingresa valores en métricas base primero |

---

## Validaciones Automáticas

✅ **Metas:**
- Suma mensual ≤ Meta anual
- Meta anual ≥ Suma mensual existente
- No duplicar períodos

✅ **Usuarios:**
- area_admin requiere área asignada
- dept_admin requiere departamento asignado

✅ **Métricas:**
- Métricas globales solo para super_admin
- Métricas calculadas validan métricas base

---

## Orden de Configuración Inicial

```
1️⃣ Departamentos
2️⃣ Áreas
3️⃣ Períodos
4️⃣ Usuarios
5️⃣ Métricas
6️⃣ Metas
7️⃣ Gráficos
8️⃣ Valores
```

---

## Fórmulas Útiles

**Porcentaje de cumplimiento:**
```
(Valor Real / Meta) × 100
```

**Ticket Promedio:**
```
Monto Total / Cantidad
```

**Variación vs Período Anterior:**
```
((Actual - Anterior) / Anterior) × 100
```

---

## Semáforos de Cumplimiento

| Color | Rango | Significado |
|-------|-------|-------------|
| 🟢 Verde | 90-100%+ | Cumple o supera |
| 🟡 Amarillo | 70-89% | Cerca, requiere atención |
| 🔴 Rojo | <70% | No cumple, acción urgente |

---

## Iconos Comunes del Sistema

| Icono | Significado |
|-------|-------------|
| 📈 | Mejora vs período anterior |
| 📉 | Disminución vs período anterior |
| ℹ️ | Información adicional disponible |
| ⚠️ | Requiere atención |
| ✅ | Completado/Cumplido |
| ❌ | No cumplido/Error |
| 💡 | Tip o sugerencia |

---

## Permisos por Módulo

| Módulo | Super | Dept | Area | Viewer |
|--------|-------|------|------|--------|
| Departamentos | ✅ | ❌ | ❌ | ❌ |
| Áreas | ✅ | ✅ | ❌ | ❌ |
| Períodos | ✅ | ❌ | ❌ | ❌ |
| Usuarios | ✅ | ❌ | ❌ | ❌ |
| Métricas | ✅ | ✅ | ✅ | ❌ |
| Metas | ✅ | ✅ | ✅ | ❌ |
| Gráficos | ✅ | ✅ | ✅ | ❌ |
| Valores | ✅ | ✅ | ✅ | ❌ |
| Dashboards | ✅ | ✅ | ✅ | ✅ |

---

## Configuración de Gráficos - Tamaños

| Tamaño | Ancho | Gráficos por fila |
|--------|-------|-------------------|
| Completo | 100% | 1 |
| Medio | 50% | 2 |
| Tercio | 33% | 3 |

---

## Buenas Prácticas

### Dashboards
- ✅ KPIs importantes arriba (posición 1-3)
- ✅ Gráficos de tendencia en el medio
- ✅ Detalles al final
- ❌ No más de 8-10 gráficos por dashboard

### Metas
- ✅ Crear meta anual primero
- ✅ Distribuir mensuales después
- ✅ Usar tipo de comparación correcto
- ❌ No duplicar períodos

### Valores
- ✅ Ingresar con notas para contexto
- ✅ Validar semáforos antes de guardar
- ✅ Revisar métricas calculadas
- ❌ No dejar períodos sin valores

---

## Configuración Inicial Rápida

**Semana 1: Estructura (Dept, Áreas, Períodos, Usuarios)**

**Semana 2: Métricas (Simples, Calculadas, Globales)**

**Semana 3: Objetivos (Metas anuales y mensuales)**

**Semana 4: Visualización (Dashboards y gráficos)**

**Semana 5: Datos (Valores históricos opcionales)**

**Semana 6: Producción (Pruebas y go-live)**

---

## Ejemplos de Valores Típicos

### Agencia - Créditos
```
Colocación: ₡5M - ₡8M
Cantidad: 35 - 50 créditos
Morosidad: 1.5% - 3.5%
Ticket Promedio: ₡140k - ₡180k
```

### Agencia - Cuentas
```
Asociados Nuevos: 80 - 120
Tarjetas: 50 - 80
Aportaciones: ₡2.5M - ₡4M
```

---

## Contacto Rápido

📧 **Soporte:** [soporte@cooperativa.com]  
📞 **Tel:** [número]  
👤 **Admin:** [nombre super admin]

---

## URLs Útiles

**Sistema:** `http://[servidor]/metricas/public/`  
**Admin:** `http://[servidor]/metricas/public/admin/`  
**Captura:** `http://[servidor]/metricas/public/captura-valores.php`

---

## Atajos de Teclado (Si Están Disponibles)

| Tecla | Acción |
|-------|--------|
| `Ctrl + S` | Guardar (en formularios) |
| `Esc` | Cerrar modal |
| `Tab` | Navegar campos |
| `?` | Ayuda contextual |

---

## Estados de Métricas

| Badge | Significado |
|-------|-------------|
| 🟢 Activa | Métrica en uso |
| ⚪ Inactiva | Métrica deshabilitada |
| 🔵 Tiene Meta | Meta configurada |
| 🟡 Calculada | Valor automático |
| 🟣 Global | Solo super_admin |

---

## Códigos de Color por Rol (Badges)

| Color | Rol |
|-------|-----|
| 🔴 Rojo | Super Admin |
| 🔵 Azul | Admin Departamento |
| 🟣 Morado | Admin Área |
| 🟢 Verde | Visualizador |

---

**Sistema de Métricas v2.2 - Abril 2026**

**Imprime esta hoja y tenla a mano para consulta rápida.**
