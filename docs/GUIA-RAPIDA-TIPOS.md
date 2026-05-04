# Guía Rápida: Tipos de Departamento

**Sistema de Métricas v2.1 - Cooperativa**

---

## 🎯 En 30 Segundos

El sistema ahora organiza todo en **3 tipos** de departamentos:

```
🏦 AGENCIAS          🏢 CORPORATIVO        🌍 GLOBAL
(Sucursales)        (Centrales)           (Ejecutivo)
                                          
San José            TI Corporativo        Solo super_admin
Heredia             RRHH                  Métricas consolidadas
Cartago             Servicios             Dashboards ejecutivos
Alajuela            ...                   Vista de toda la org
```

---

## 📊 Estructura

```
Departamento (tipo: agencia | corporativo | global)
  └── Área
       └── Métrica
            ├── Valores
            └── Metas
```

---

## 🔄 Flujo de Métricas

### Métricas Normales

```
Usuario → Valor → Dashboard del área
```

### Métricas Globales (Calculadas)

```
Agencia SJ:    45.5M ┐
Agencia Heredia: 32.8M ├──→ SUMA ──→ Total: 130.3M
Agencia Cartago: 28.3M │              (Dashboard Global)
Agencia Alajuela: 23.7M ┘
```

---

## 🎨 Navegación (Super Admin)

### Página de Inicio con 3 Pestañas

**1. 🏦 Red de Agencias**
```
┌─────────────────┐  ┌─────────────────┐
│ San José        │  │ Heredia         │
│ • Caja          │  │ • Caja          │
│ • Créditos      │  │ • Créditos      │
│ • Cuentas       │  │ • Cuentas       │
└─────────────────┘  └─────────────────┘
```

**2. 🏢 Corporativo**
```
┌─────────────────┐  ┌─────────────────┐
│ TI Corporativo  │  │ RRHH            │
│ • Backend       │  │ • Reclutamiento │
│ • Frontend      │  │ • Capacitación  │
└─────────────────┘  └─────────────────┘
```

**3. 🌍 Global** (Solo Super Admin)
```
┌──────────────────────────┐
│ Métricas Financieras     │
│ Métricas Comerciales     │
│ Métricas Operativas      │
│ KPIs Estratégicos        │
└──────────────────────────┘
```

---

## 🔐 Permisos

| Usuario      | Ve Agencias | Ve Corporativo | Ve Global |
|--------------|-------------|----------------|-----------|
| super_admin  | ✅ Todas    | ✅ Todos       | ✅ Sí     |
| dept_admin   | ⚠️ Solo su dept | ⚠️ Solo su dept | ❌ No |
| dept_viewer  | ⚠️ Solo su dept | ⚠️ Solo su dept | ❌ No |

---

## 💡 Ejemplo Real

**Meta Anual:** 980M en colocación de créditos

**Dashboard Agencia San José:**
- Colocación Abril: **45.5M**
- Meta: 20.4M/mes
- Cumplimiento: **223%** ✅

**Dashboard Global:**
- Total Colocación: **130.3M** (suma de 4 agencias)
- Meta: 81.67M/mes
- Cumplimiento: **159%** ✅

---

## 🚀 Crear Nueva Agencia

1. **Admin > Departamentos > Nuevo**
   - Nombre: "Agencia Limón"
   - Tipo: **agencia**
   - Color: #10b981

2. **Admin > Áreas > Nuevas**
   - Caja, Créditos, Cuentas

3. **Admin > Métricas > Nuevas**
   - Colocación, Asociados, etc.

4. **Admin > Métricas Globales > Editar**
   - Agregar componente: Colocación Limón
   - ✅ Se suma automáticamente al total

---

## 🚀 Crear Nueva Área Global

1. **Admin > Áreas > Nueva**
   - Departamento: **Global**
   - Nombre: "Satisfacción Cliente"
   - Color: #8b5cf6 (morado)

2. **Admin > Métricas > Nueva Calculada**
   - Operación: PROMEDIO
   - Componentes: NPS de cada agencia

3. **Admin > Gráficos > Nuevos**
   - Gauge, Radar, Tendencia

✅ Aparece en pestaña Global

---

## 📁 Archivos Clave

**Documentación:**
- `docs/TIPOS-DEPARTAMENTO-Y-FLUJO.md` - Doc completa
- `PLAN-DE-PRUEBAS.md` - 15 casos de prueba

**Código:**
- `src/Models/Departamento.php` - Métodos por tipo
- `src/Services/MetricaCalculadaService.php` - Lógica global
- `views/home_selector.php` - Navegación por pestañas

**Datos:**
- `database/migrations/008_add_tipo_departamentos.sql`
- `database/seeds/009_seed_tipos_departamento_demo.sql`

---

## 🎨 Colores por Tipo

| Tipo        | Color   | Código    | Icono          |
|-------------|---------|-----------|----------------|
| 🏦 Agencia  | Verde   | #10b981   | building-bank  |
| 🏢 Corporativo | Azul | #3b82f6   | building       |
| 🌍 Global   | Morado  | #8b5cf6   | world          |

---

## ⚡ Comandos Rápidos

**Cargar datos demo:**
```bash
php run_seed_demo.php
```

**Ver departamentos por tipo:**
```sql
SELECT tipo, COUNT(*) FROM departamentos GROUP BY tipo;
```

**Ver áreas globales:**
```sql
SELECT a.nombre 
FROM areas a 
JOIN departamentos d ON a.departamento_id = d.id 
WHERE d.tipo = 'global';
```

---

## ✅ Checklist Post-Implementación

- [x] Migración ejecutada
- [x] Datos demo cargados
- [x] 4 agencias creadas
- [x] 5 áreas globales configuradas
- [x] 4 métricas calculadas globales
- [x] 67 gráficos creados
- [x] Navegación por pestañas funcional
- [x] Permisos configurados
- [x] Tests pasando (8/8)

---

## 📞 Links Útiles

- **Dashboard:** `http://localhost/metricas/public/`
- **Admin Áreas:** `http://localhost/metricas/public/admin/areas.php`
- **Admin Métricas:** `http://localhost/metricas/public/admin/metricas.php`

---

**Versión:** 2.1  
**Fecha:** Abril 2026  
**Estado:** ✅ En Producción
