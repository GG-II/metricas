# Checklist de Verificación Pre-Despliegue

## 🔐 Seguridad y Autenticación

- [ ] **Login funciona correctamente**
  - Probar con credenciales válidas
  - Probar con credenciales inválidas
  - Verificar redirección después del login

- [ ] **Roles y Permisos**
  - [ ] Super Admin: acceso a todo
  - [ ] Dept Admin: solo su departamento
  - [ ] Dept Viewer: solo su área
  - Intentar acceder a áreas/departamentos no autorizados

- [ ] **Sesiones**
  - Timeout de sesión funciona
  - Logout funciona correctamente
  - No se puede acceder sin login

- [ ] **Validación de Inputs**
  - Formularios validan datos
  - No hay SQL injection (todas las queries usan prepared statements)
  - No hay XSS (htmlspecialchars en outputs)

## 📊 Funcionalidad de Gráficos

- [ ] **Los 24 tipos de gráficos renderizan correctamente**
  - [ ] KPI Card
  - [ ] Línea
  - [ ] Barras
  - [ ] Dona
  - [ ] Área
  - [ ] Progreso
  - [ ] Gauge
  - [ ] Multi Barras
  - [ ] KPI con Meta
  - [ ] Bullet Chart
  - [ ] Gauge con Meta
  - [ ] Línea con Meta
  - [ ] Comparación
  - [ ] Tabla de Datos
  - [ ] Barras Horizontales
  - [ ] Gráfico Mixto
  - [ ] Líneas Múltiples
  - [ ] Barras Porcentuales
  - [ ] Comparación de Períodos
  - [ ] Radar de Comparación
  - [ ] Dispersión
  - [ ] Sparkline
  - [ ] Área Apilada
  - [ ] Barras Apiladas

- [ ] **CRUD de Gráficos**
  - [ ] Crear gráfico nuevo
  - [ ] Editar gráfico existente
  - [ ] Eliminar gráfico (confirmación con SweetAlert2)
  - [ ] Configuración se guarda correctamente

- [ ] **Modo Edición (GridStack)**
  - [ ] Activar/desactivar modo edición
  - [ ] Mover gráficos (drag & drop)
  - [ ] Redimensionar gráficos
  - [ ] Scroll funciona con gráficos altos
  - [ ] Guardar layout
  - [ ] Layout persiste después de recargar

- [ ] **Filtrado por Período**
  - [ ] Cambiar período actualiza todos los gráficos
  - [ ] Gráficos muestran datos del período correcto
  - [ ] Gráficos sin datos muestran mensaje apropiado

## 📈 Métricas y Datos

- [ ] **Gestión de Métricas**
  - [ ] Crear métrica nueva
  - [ ] Editar métrica
  - [ ] Desactivar/activar métrica
  - [ ] Tipos de valor (numero, decimal, porcentaje, tiempo)

- [ ] **Valores de Métricas**
  - [ ] Insertar valores manualmente
  - [ ] Valores se muestran correctamente en gráficos
  - [ ] Históricos funcionan (6, 12, 24 meses)

- [ ] **Metas**
  - [ ] Crear meta mensual
  - [ ] Crear meta anual
  - [ ] Cálculo de cumplimiento correcto
  - [ ] Gráficos con metas muestran comparación
  - [ ] Solo métricas con metas aparecen en gráficos que las requieren

## 🏢 Configuración

- [ ] **Departamentos**
  - [ ] Crear departamento
  - [ ] Editar departamento
  - [ ] Desactivar departamento
  - [ ] Colores e íconos funcionan

- [ ] **Áreas**
  - [ ] Crear área
  - [ ] Editar área
  - [ ] Asignar a departamento
  - [ ] Desactivar área
  - [ ] Navegación entre áreas funciona

- [ ] **Usuarios**
  - [ ] Crear usuario
  - [ ] Editar usuario
  - [ ] Cambiar rol
  - [ ] Asignar departamento/área
  - [ ] Desactivar usuario
  - [ ] Avatar funciona

- [ ] **Períodos**
  - [ ] Períodos activos disponibles
  - [ ] Selector de período funciona
  - [ ] Activar/desactivar períodos

## 🎨 Interfaz de Usuario

- [ ] **Modo Claro/Oscuro**
  - [ ] Toggle funciona
  - [ ] Preferencia se guarda
  - [ ] Todos los componentes se ven bien en ambos modos
  - [ ] SweetAlert2 respeta el tema

- [ ] **Navegación**
  - [ ] Navbar de departamentos funciona
  - [ ] Navbar de áreas funciona
  - [ ] Títulos de áreas completos (no truncados)
  - [ ] Colores de área/departamento se aplican correctamente
  - [ ] Texto blanco cuando área está activa

- [ ] **Responsive**
  - [ ] Dashboard se ve bien en desktop
  - [ ] Dashboard se ve bien en tablet
  - [ ] Dashboard se ve bien en móvil
  - [ ] GridStack responsive

## 🌐 Navegadores

- [ ] **Chrome** (última versión)
- [ ] **Firefox** (última versión)
- [ ] **Safari** (última versión)
- [ ] **Edge** (última versión)

## ⚡ Rendimiento

- [ ] **Carga de Página**
  - [ ] Dashboard carga en menos de 3 segundos
  - [ ] No hay errores en consola
  - [ ] No hay warnings de PHP

- [ ] **Base de Datos**
  - [ ] Queries optimizadas (no N+1)
  - [ ] Índices en tablas importantes
  - [ ] No hay queries lentas

- [ ] **JavaScript**
  - [ ] ApexCharts se inicializa correctamente
  - [ ] No hay memory leaks
  - [ ] ResizeObserver funciona sin errores

## 🗄️ Base de Datos

- [ ] **Migraciones**
  - [ ] Todas las tablas existen
  - [ ] Columnas correctas (tiene_meta en metricas, etc.)
  - [ ] Relaciones de integridad (foreign keys)
  - [ ] Índices en columnas frecuentes

- [ ] **Datos de Prueba**
  - [ ] Eliminar área/departamento "Pruebas" antes de producción
  - [ ] Verificar que no hay datos de prueba en producción

## 📁 Archivos y Configuración

- [ ] **Configuración**
  - [ ] config.php tiene variables correctas para producción
  - [ ] Credenciales de BD correctas
  - [ ] APP_ENV = 'production'
  - [ ] Error reporting apropiado para producción

- [ ] **Composer**
  - [ ] `composer install --no-dev --optimize-autoloader`
  - [ ] vendor/ no está en git (.gitignore)

- [ ] **.gitignore**
  - [ ] config.php
  - [ ] vendor/
  - [ ] uploads/avatars/*
  - [ ] .env (si existe)
  - [ ] node_modules/

- [ ] **Permisos de Archivos**
  - [ ] uploads/ es escribible
  - [ ] cache/ es escribible (si existe)
  - [ ] logs/ es escribible (si existe)

## 📚 Documentación

- [ ] **README.md**
  - [ ] Instrucciones de instalación
  - [ ] Requisitos del sistema
  - [ ] Configuración inicial

- [ ] **Guías**
  - [ ] GUIA-DESPLIEGUE-AWS-IIS.md está actualizada
  - [ ] INSTRUCCIONES-LOGIN.md está actualizada
  - [ ] CHANGELOG.md está actualizado

## 🧪 Tests de Integración

Ejecutar estos flujos completos:

### Flujo 1: Crear Dashboard desde Cero
1. Login como super_admin
2. Crear departamento "Ventas"
3. Crear área "Ventas Online"
4. Crear métrica "Ingresos Mensuales"
5. Insertar valores para 6 meses
6. Crear meta para la métrica
7. Crear gráfico "KPI con Meta"
8. Verificar que se muestra correctamente

### Flujo 2: Gestión de Permisos
1. Crear usuario dept_viewer
2. Asignar a área específica
3. Login con ese usuario
4. Verificar que solo ve su área
5. Verificar que no puede editar

### Flujo 3: Edición de Layout
1. Login como admin
2. Ir a un dashboard
3. Activar modo edición
4. Mover y redimensionar gráficos
5. Guardar layout
6. Recargar página
7. Verificar que layout se mantuvo

## 🚨 Errores Comunes a Verificar

- [ ] No hay "Array to string conversion"
- [ ] No hay "Undefined index"
- [ ] No hay "Call to undefined method"
- [ ] No hay "Trying to access array offset on value of type bool"
- [ ] No hay errores de JavaScript en consola
- [ ] No hay warnings de SQL

## 📊 Datos de Ejemplo

- [ ] Hay al menos 1 departamento real
- [ ] Hay al menos 1 área real
- [ ] Hay al menos 5 métricas reales
- [ ] Hay valores para los últimos 6 meses
- [ ] Hay al menos 1 usuario de cada rol

## 🔄 Backup

- [ ] Backup de base de datos antes de despliegue
- [ ] Backup de archivos antes de despliegue
- [ ] Plan de rollback definido

## ✅ Despliegue Final

- [ ] Todas las verificaciones anteriores pasadas
- [ ] Código en repositorio actualizado
- [ ] Tags de versión creados
- [ ] Documentación actualizada
- [ ] Equipo notificado del despliegue
- [ ] Ventana de mantenimiento programada (si aplica)

---

## 🛠️ Script de Verificación Rápida

Para ejecutar verificaciones automáticas, puedes usar:

```bash
# Verificar estructura de base de datos
php verify_database.php

# Verificar permisos de archivos
php verify_permissions.php

# Limpiar datos de prueba
php cleanup_test_data.php
```

## 📞 Contactos de Emergencia

- **Desarrollador:** [tu correo]
- **DBA:** [correo DBA]
- **DevOps:** [correo DevOps]

---

**Última actualización:** 2026-04-25
