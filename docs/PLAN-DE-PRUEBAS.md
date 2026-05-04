# Plan de Pruebas - Tipos de Departamento y Áreas Globales

**Fecha:** 28 de Abril 2026  
**Usuario de Prueba:** super_admin  
**Datos de Prueba:** ✅ Cargados

---

## 📊 Datos de Prueba Creados

### Agencias (4):
- 🏦 Agencia San José (Agencia Central)
- 🏦 Agencia Heredia (Agencia Norte)
- 🏦 Agencia Cartago (Agencia Este)
- 🏦 Agencia Alajuela (Agencia Oeste)

### Áreas Globales (5):
- 🌍 Métricas Consolidadas (original)
- 💰 Métricas Financieras
- 🎯 Métricas Comerciales
- ⚙️ Métricas Operativas
- ⭐ KPIs Estratégicos

### Métricas Globales Calculadas (4):
- Total Colocación Créditos: **130.3 millones** (meta: 81.67M/mes)
- Total Incremento Cartera: **60.8 millones** (meta: 40.83M/mes)
- Total Asociados Nuevos: **850 asociados** (meta: 500/mes) ✅
- Total Tarjetas Débito: **475 tarjetas** (meta: 358/mes) ✅

---

## 🧪 PLAN DE PRUEBAS COMPLETO

### ✅ PRUEBA 1: Navegación Principal por Tipos

**Objetivo:** Verificar que la página de inicio muestra departamentos organizados por tipos

**Pasos:**
1. Ir a `http://localhost/metricas/public/`
2. Login como **super_admin**
3. **Deberías ver** una página con 3 pestañas:
   - 🏦 Red de Agencias (badge: 4)
   - 🏢 Corporativo (badge: variable)
   - 🌍 Global (badge: "Super Admin")

**Resultado Esperado:**
- ✅ 3 pestañas visibles
- ✅ Pestaña "Red de Agencias" activa por defecto
- ✅ Se muestran 4 tarjetas de agencias con sus áreas

**Verificar:**
```
□ Pestaña "Red de Agencias" muestra 4 agencias
□ Cada agencia tiene sus áreas listadas (Caja, Créditos, Cuentas)
□ Click en área redirige al dashboard de esa área
□ Diseño responsive y colores correctos
```

---

### ✅ PRUEBA 2: Pestaña Red de Agencias

**Objetivo:** Verificar visualización de agencias

**Pasos:**
1. En la página de inicio, asegúrate de estar en pestaña "Red de Agencias"
2. Revisar cada tarjeta de agencia

**Resultado Esperado:**
- ✅ Agencia San José muestra 4 áreas:
  - Caja
  - Créditos
  - Cuentas Nuevas
  - Atención Cliente
- ✅ Agencia Heredia muestra 3 áreas
- ✅ Agencia Cartago muestra 3 áreas
- ✅ Agencia Alajuela muestra 2 áreas
- ✅ Todas con icono 🏦 y color verde

**Verificar:**
```
□ Tarjetas muestran badge "Agencia"
□ Color verde (#10b981) consistente
□ Icono building-bank visible
□ Áreas clickeables
```

---

### ✅ PRUEBA 3: Pestaña Corporativo

**Objetivo:** Verificar departamentos corporativos

**Pasos:**
1. Click en pestaña "🏢 Corporativo"
2. Revisar departamentos mostrados

**Resultado Esperado:**
- ✅ Se muestran departamentos corporativos:
  - TI Corporativo
  - Servicios
  - Recursos Humanos
  - Pruebas
- ✅ Color azul para corporativos
- ✅ Cada uno con sus áreas

**Verificar:**
```
□ Solo muestra departamentos tipo="corporativo"
□ NO muestra agencias ni global
□ Diseño consistente con agencias
```

---

### ✅ PRUEBA 4: Pestaña Global (Solo Super Admin)

**Objetivo:** Verificar áreas globales y restricción de acceso

**Pasos:**
1. Click en pestaña "🌍 Global"
2. Revisar áreas globales mostradas

**Resultado Esperado:**
- ✅ Alert morado explicando que es área global
- ✅ 5 áreas globales visibles:
  - Métricas Consolidadas
  - Métricas Financieras
  - Métricas Comerciales
  - Métricas Operativas
  - KPIs Estratégicos
- ✅ Todas con icono diferente y color morado

**Verificar:**
```
□ Alert "Solo Super Administrador" visible
□ 5 áreas globales listadas
□ Color morado (#8b5cf6) consistente
□ Click en área redirige al dashboard
```

**Prueba de Seguridad:**
- Logout y login como `dept_admin`
- **NO** deberías ver la pestaña Global
- Si intentas acceder manualmente, debería dar error 403

---

### ✅ PRUEBA 5: Admin de Áreas - Badges de Tipo

**Objetivo:** Verificar que el admin muestra tipos de departamento

**Pasos:**
1. Ir a `Admin > Áreas`
2. Observar listado de áreas

**Resultado Esperado:**
- ✅ Cada área muestra badge de tipo de departamento:
  - 🏦 Badge verde para agencias
  - 🏢 Badge azul para corporativo
  - 🌍 Badge morado para global

**Verificar:**
```
□ Badges visibles en listado de áreas
□ Colores correctos según tipo
□ Tooltip muestra tipo al hover
```

---

### ✅ PRUEBA 6: Crear Área en Departamento Global

**Objetivo:** Verificar que super_admin puede crear áreas globales

**Pasos:**
1. Ir a `Admin > Áreas`
2. Click en "Nueva Área"
3. En selector de departamento, buscar "Global (Global)"
4. Crear área de prueba:
   - Departamento: Global
   - Nombre: "Prueba Global"
   - Slug: auto-generado
   - Color: #8b5cf6
   - Icono: test
5. Guardar

**Resultado Esperado:**
- ✅ Departamento Global aparece en el selector
- ✅ Muestra "(Global)" entre paréntesis
- ✅ Área se crea exitosamente
- ✅ Aparece en pestaña Global del inicio

**Verificar:**
```
□ Selector muestra "Global (Global)"
□ Área creada aparece en listado
□ Badge morado visible
□ Área accesible desde inicio
```

---

### ✅ PRUEBA 7: Métricas Disponibles en Área Global

**Objetivo:** Verificar que áreas globales ven métricas de todos los departamentos

**Pasos:**
1. Ir a `Admin > Métricas`
2. Seleccionar área "Métricas Financieras" (global)
3. Click en "Nueva Métrica Calculada"
4. Observar el selector de componentes

**Resultado Esperado:**
- ✅ Selector muestra métricas de TODAS las agencias
- ✅ Métricas agrupadas por: Departamento > Área
- ✅ Incluye métricas de corporativos también
- ✅ NO incluye métricas de otras áreas globales

**Ejemplo de lo que deberías ver:**
```
Agencia San José > Créditos > Colocación de Créditos
Agencia San José > Créditos > Incremento Cartera
Agencia Heredia > Créditos > Colocación de Créditos
...
TI Corporativo > Backend > Deploy exitosos
...
```

**Verificar:**
```
□ Métricas de agencias visibles
□ Métricas de corporativos visibles
□ Métricas de otras áreas globales NO visibles
□ Ordenamiento: agencias primero, luego corporativos
```

---

### ✅ PRUEBA 8: Dashboard de Área de Agencia

**Objetivo:** Verificar dashboard funcional de una agencia

**Pasos:**
1. Desde inicio, click en "Agencia San José > Créditos"
2. Observar dashboard

**Resultado Esperado:**
- ✅ Dashboard carga correctamente
- ✅ Muestra métricas del área:
  - Colocación de Créditos: 45.5 millones
  - Incremento Cartera: 22.5 millones
  - Cantidad Créditos: 285
- ✅ Navegación de áreas funcional
- ✅ Sin errores en consola

**Verificar:**
```
□ Métricas cargan con valores
□ Navegación entre áreas funciona
□ Breadcrumb muestra: Agencia San José > Créditos
□ Estilo consistente
```

---

### ✅ PRUEBA 9: Dashboard de Área Global

**Objetivo:** Verificar que métricas globales muestran valores calculados

**Pasos:**
1. Desde inicio > Pestaña Global
2. Click en "Métricas Financieras"
3. Observar métricas

**Resultado Esperado:**
- ✅ Dashboard carga correctamente
- ✅ Muestra métricas calculadas globales:
  - Total Colocación Créditos: **130.3 millones**
    - Calculado: 45.5 + 32.8 + 28.3 + 23.7
  - Total Incremento Cartera: **60.8 millones**
    - Calculado: 22.5 + 15.2 + 12.8 + 10.3
- ✅ Valores coinciden con suma manual
- ✅ Nota indica origen: "Calculado: ..."

**Verificar:**
```
□ Valores de métricas globales correctos
□ Coinciden con suma de componentes
□ Dashboard funcional
□ Sin errores
```

---

### ✅ PRUEBA 10: Cumplimiento de Metas Globales

**Objetivo:** Verificar visualización de metas en métricas globales

**Pasos:**
1. En dashboard de "Métricas Financieras"
2. Observar métricas con metas:
   - Total Colocación Créditos
   - Total Incremento Cartera

**Resultado Esperado:**
- ✅ Total Colocación: 130.3M actual vs 81.67M meta
  - **Estado:** ✅ CUMPLIDA (159% de cumplimiento)
  - Semáforo: Verde
- ✅ Total Incremento: 60.8M actual vs 40.83M meta
  - **Estado:** ✅ CUMPLIDA (148% de cumplimiento)
  - Semáforo: Verde

**Verificar:**
```
□ Metas visibles en dashboard
□ % de cumplimiento calculado
□ Semáforo correcto (verde = cumplida)
□ Gráfico muestra progreso hacia meta
```

---

### ✅ PRUEBA 11: Métricas Comerciales Globales

**Objetivo:** Verificar área de métricas comerciales

**Pasos:**
1. Inicio > Global > "Métricas Comerciales"
2. Observar dashboard

**Resultado Esperado:**
- ✅ Total Asociados Nuevos: **850** (meta: 500) ✅
  - Calculado: 342 + 287 + 221
  - Cumplimiento: 170%
- ✅ Total Tarjetas Débito: **475** (meta: 358) ✅
  - Calculado: 189 + 152 + 134
  - Cumplimiento: 132%

**Verificar:**
```
□ Valores correctos
□ Metas cumplidas (ambas en verde)
□ Cálculos correctos
```

---

### ✅ PRUEBA 12: Filtrado por Departamento en Admin

**Objetivo:** Verificar filtros por tipo en administración

**Pasos:**
1. Ir a `Admin > Departamentos`
2. Observar listado

**Resultado Esperado:**
- ✅ Departamentos listados con badge de tipo:
  - Agencias: badge verde
  - Corporativos: badge azul
  - Global: badge morado
- ✅ Estadísticas por tipo visibles

**Verificar:**
```
□ 4 agencias listadas
□ Departamentos corporativos listados
□ 1 departamento global
□ Badges de tipo visibles
```

---

### ✅ PRUEBA 13: Permisos - Dept Admin

**Objetivo:** Verificar que dept_admin NO ve áreas globales

**Pasos:**
1. Logout como super_admin
2. Login como `dept_admin` (si tienes uno)
3. Ir a inicio

**Resultado Esperado:**
- ✅ NO ve pestaña "Global"
- ✅ Solo ve su departamento
- ✅ Si intenta acceder a URL de área global directamente:
  - Error: "Solo super_admin puede acceder"

**Verificar:**
```
□ Pestaña Global NO visible
□ Solo su departamento accesible
□ Restricción de URL funciona
```

---

### ✅ PRUEBA 14: Búsqueda y Navegación

**Objetivo:** Verificar usabilidad general

**Pasos:**
1. Navegar entre diferentes tipos de departamentos
2. Acceder a diferentes áreas
3. Verificar breadcrumbs y navegación

**Resultado Esperado:**
- ✅ Navegación fluida entre pestañas
- ✅ Breadcrumbs muestran: Tipo > Departamento > Área
- ✅ Botón "Volver" funciona
- ✅ URLs amigables

**Verificar:**
```
□ Click en pestañas cambia contenido
□ Navegación rápida entre áreas
□ Breadcrumbs correctos
□ Sin errores en consola
```

---

### ✅ PRUEBA 15: Responsive Design

**Objetivo:** Verificar diseño en diferentes tamaños

**Pasos:**
1. Reducir tamaño de ventana del navegador
2. Probar en móvil (F12 > Toggle device toolbar)

**Resultado Esperado:**
- ✅ Pestañas se adaptan (se apilan en móvil)
- ✅ Tarjetas de departamentos responsive
- ✅ Todo el contenido visible
- ✅ Sin scrolls horizontales

**Verificar:**
```
□ Layout responsive
□ Pestañas usables en móvil
□ Tarjetas se adaptan
□ Sin elementos cortados
```

---

## 🐛 Posibles Problemas y Soluciones

### Problema 1: No veo la pestaña Global
**Solución:** Solo super_admin la ve. Verifica que estés logueado como super_admin.

### Problema 2: Métricas globales muestran 0
**Solución:** Los datos de prueba se cargaron. Verifica que el período actual sea "Abril 2026".

### Problema 3: Error al crear área global
**Solución:** Solo super_admin puede crear áreas en departamento Global.

### Problema 4: Selector de métricas vacío en área global
**Solución:** Verifica que existan métricas en agencias/corporativos primero.

---

## 📊 Checklist de Pruebas

### Funcionalidad Básica
- [ ] Navegación por pestañas funciona
- [ ] Agencias listadas correctamente
- [ ] Corporativos listados correctamente
- [ ] Global visible solo para super_admin

### Áreas Globales
- [ ] 5 áreas globales visibles
- [ ] Crear nueva área global funciona
- [ ] Dashboard de área global carga

### Métricas Calculadas
- [ ] Métricas globales muestran valores
- [ ] Cálculos correctos (suma de componentes)
- [ ] Metas visibles y calculadas

### Administración
- [ ] Badges de tipo en admin áreas
- [ ] Selector de departamento muestra tipos
- [ ] Filtrado por tipo funciona

### Permisos
- [ ] Super admin ve todo
- [ ] Dept admin NO ve global
- [ ] Restricciones de URL funcionan

### UX/UI
- [ ] Diseño consistente
- [ ] Colores correctos por tipo
- [ ] Responsive
- [ ] Sin errores en consola

---

## 🎯 Resultado Esperado Final

Después de todas las pruebas:

✅ **Sistema funcional con estructura de cooperativa**  
✅ **4 agencias con áreas operativas**  
✅ **5 áreas globales organizadas por categoría**  
✅ **Métricas calculadas sumando correctamente**  
✅ **Metas globales configuradas y visibles**  
✅ **Permisos funcionando correctamente**  
✅ **UI moderna y organizada por tipos**

---

## 📞 Soporte

Si encuentras algún problema durante las pruebas:

1. Revisar consola del navegador (F12)
2. Verificar permisos de usuario
3. Comprobar que los datos de prueba se cargaron:
   ```sql
   SELECT COUNT(*) FROM departamentos WHERE tipo = 'agencia';
   -- Debe retornar: 4
   ```

**Documentación:**
- `docs/FEATURE-TIPOS-DEPARTAMENTO.md` - Documentación técnica
- `IMPLEMENTATION-SUMMARY.md` - Resumen de implementación
- `PLAN-DE-PRUEBAS.md` - Este archivo

---

**Fecha de Creación:** 28 de Abril 2026  
**Autor:** Sistema de Métricas v2.1  
**Estado:** ✅ Listo para Pruebas
