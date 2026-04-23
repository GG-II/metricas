# 📊 Sistema de Métricas Multi-Departamento - Documentación Completa

**Proyecto:** Sistema de Métricas con Multi-Tenancy  
**Ubicación:** `htdocs/metricas/`  
**Código Legacy:** `htdocs/metricas/legacy/` (proyecto metricas-it original)  
**Versión:** 2.0  
**Fecha Inicio:** Abril 2026  

---

## 📋 TABLA DE CONTENIDOS

1. [Visión General del Proyecto](#visión-general)
2. [Arquitectura del Sistema](#arquitectura)
3. [Stack Tecnológico](#stack-tecnológico)
4. [Estructura de Directorios](#estructura-de-directorios)
5. [Base de Datos](#base-de-datos)
6. [Sistema de Permisos](#sistema-de-permisos)
7. [Código Reutilizable del Legacy](#código-reutilizable)
8. [Guía de Implementación Fase por Fase](#guía-de-implementación)
9. [Componentes Clave](#componentes-clave)
10. [Testing y Datos de Prueba](#testing)

---

## 🎯 VISIÓN GENERAL

### Contexto

El sistema actual (`legacy/`) es un dashboard de métricas IT funcional pero con arquitectura plana:
- 5 áreas independientes (Software, Infraestructura, Soporte, Ciberseguridad, Medios Digitales)
- 2 roles simples (admin, viewer)
- Sin agrupación por departamentos
- Sin permisos granulares

### Objetivo del Nuevo Sistema

Crear un sistema de métricas con **multi-tenancy** y **jerarquía organizacional**:

```
Sistema
├── Departamentos (nivel superior - nuevo)
│   ├── TI Corporativo
│   │   ├── Software Factory
│   │   ├── Infraestructura
│   │   └── Ciberseguridad
│   └── Servicios
│       ├── Soporte
│       └── Medios Digitales
└── Usuarios (3 roles con scope)
    ├── super_admin (ve TODO)
    ├── dept_admin (ve/edita SU departamento)
    └── dept_viewer (ve SU área asignada)
```

### Principios de Diseño

1. **Reutilizar lo que funciona** - No reinventar la rueda
2. **Arquitectura limpia** - Código organizado y mantenible
3. **Permisos desde el inicio** - Security by design
4. **Escalable** - Preparado para crecer
5. **Testing friendly** - Datos de prueba claros

---

## 🏗️ ARQUITECTURA

### Diagrama de Arquitectura

```
┌─────────────────────────────────────────────────────────┐
│                     FRONTEND (Views)                     │
│  ┌─────────────┐  ┌──────────────┐  ┌────────────────┐ │
│  │  Dashboard  │  │  Admin Panel │  │  Login/Logout  │ │
│  └─────────────┘  └──────────────┘  └────────────────┘ │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                      │
│  ┌──────────────────┐  ┌────────────────────────────┐  │
│  │  AuthMiddleware  │  │  PermissionMiddleware      │  │
│  └──────────────────┘  └────────────────────────────┘  │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                   BUSINESS LOGIC                         │
│  ┌────────────┐  ┌─────────────┐  ┌──────────────────┐ │
│  │ Controllers│  │  Services   │  │  ChartRegistry   │ │
│  └────────────┘  └─────────────┘  └──────────────────┘ │
└─────────────────────────────────────────────────────────┘
                          ↓
┌─────────────────────────────────────────────────────────┐
│                      DATA LAYER                          │
│  ┌──────────┐  ┌───────────┐  ┌──────────┐  ┌────────┐ │
│  │Departamen│  │   Areas   │  │ Metricas │  │Usuarios│ │
│  │    to    │  │           │  │          │  │        │ │
│  └──────────┘  └───────────┘  └──────────┘  └────────┘ │
└─────────────────────────────────────────────────────────┘
                          ↓
                   ┌──────────────┐
                   │   MySQL DB   │
                   └──────────────┘
```

### Flujo de Request

```
1. Usuario accede → 2. AuthMiddleware (¿logueado?)
                  ↓
3. PermissionMiddleware (¿tiene permiso?)
                  ↓
4. Controller procesa request
                  ↓
5. Service ejecuta lógica de negocio
                  ↓
6. Model consulta/modifica BD
                  ↓
7. View renderiza resultado
                  ↓
8. Response al usuario
```

---

## 💻 STACK TECNOLÓGICO

### Backend
- **PHP 8.1+** (vanilla, sin frameworks pesados)
- **MySQL 8.0+** con PDO
- **Composer** para autoloading y dependencias

### Frontend
- **Bootstrap 5.3** (framework CSS)
- **Tabler UI** (tema admin)
- **ApexCharts** (gráficos interactivos)
- **GridStack.js** (drag & drop dashboard)
- **Vanilla JavaScript** (sin jQuery)

### Herramientas Desarrollo
- **XAMPP/WAMP** (entorno local)
- **Git** (control de versiones)
- **PHPUnit** (testing - opcional)

### Librerías JavaScript Clave

```html
<!-- ApexCharts para gráficos -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- GridStack para drag & drop -->
<script src="https://cdn.jsdelivr.net/npm/gridstack@9.0.0/dist/gridstack-all.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
```

---

## 📁 ESTRUCTURA DE DIRECTORIOS

### Nueva Estructura (Mejorada)

```
htdocs/metricas/
│
├── legacy/                          # Código original (SOLO REFERENCIA)
│   └── (todo metricas-it original)
│
├── config/                          # Configuración
│   ├── database.php                 # Conexión DB
│   ├── auth.php                     # Config autenticación
│   ├── permissions.php              # Definición de permisos
│   └── app.php                      # Config general
│
├── src/                             # Código fuente principal
│   ├── Models/                      # Modelos de datos
│   │   ├── Model.php                # Clase base (heredar)
│   │   ├── Departamento.php         # NUEVO
│   │   ├── Area.php                 # Modificado del legacy
│   │   ├── Metrica.php              # Del legacy
│   │   ├── ValorMetrica.php         # Del legacy (corregido)
│   │   ├── Usuario.php              # Modificado del legacy
│   │   ├── Periodo.php              # Del legacy
│   │   ├── Grafico.php              # Del legacy
│   │   └── Meta.php                 # Del legacy
│   │
│   ├── Controllers/                 # Controladores (NUEVO patrón)
│   │   ├── DashboardController.php
│   │   ├── MetricaController.php
│   │   ├── GraficoController.php
│   │   ├── AdminController.php
│   │   └── AuthController.php
│   │
│   ├── Middleware/                  # Middleware (NUEVO)
│   │   ├── AuthMiddleware.php
│   │   └── PermissionMiddleware.php
│   │
│   ├── Services/                    # Lógica de negocio (NUEVO)
│   │   ├── PermissionService.php
│   │   ├── MetricaService.php
│   │   └── DashboardService.php
│   │
│   └── Utils/                       # Utilidades
│       ├── ChartRegistry.php        # Del legacy (copiar)
│       └── Helpers.php              # Funciones helper
│
├── public/                          # Todo lo público (document root)
│   ├── index.php                    # Dashboard principal
│   ├── login.php                    # Login
│   ├── logout.php                   # Logout
│   │
│   ├── admin/                       # Panel administración
│   │   ├── departamentos.php        # CRUD departamentos
│   │   ├── areas.php                # CRUD áreas
│   │   ├── metricas.php             # CRUD métricas
│   │   ├── graficos.php             # CRUD gráficos
│   │   ├── usuarios.php             # CRUD usuarios
│   │   └── metas.php                # CRUD metas
│   │
│   ├── api/                         # Endpoints AJAX
│   │   ├── guardar-layout.php
│   │   ├── obtener-datos.php
│   │   └── validar-permisos.php
│   │
│   └── assets/                      # Recursos estáticos
│       ├── css/
│       │   ├── tabler.min.css       # Del CDN o local
│       │   └── custom.css           # Estilos personalizados
│       ├── js/
│       │   ├── app.js               # JavaScript principal
│       │   └── dashboard.js         # JS del dashboard
│       └── uploads/                 # Archivos subidos
│           └── avatars/
│
├── views/                           # Vistas (templates)
│   ├── layouts/
│   │   ├── app.php                  # Layout principal
│   │   ├── header.php               # Header reutilizable
│   │   ├── sidebar.php              # Sidebar (con jerarquía)
│   │   └── footer.php
│   │
│   ├── components/                  # Componentes reutilizables
│   │   ├── charts/                  # COPIAR DEL LEGACY
│   │   │   ├── kpi-card.php
│   │   │   ├── line.php
│   │   │   ├── bar.php
│   │   │   ├── donut.php
│   │   │   ├── gauge.php
│   │   │   ├── comparison.php
│   │   │   ├── progress.php
│   │   │   ├── multi-bar.php
│   │   │   ├── data-table.php
│   │   │   └── burndown.php        # NUEVO (para metas)
│   │   │
│   │   └── widgets/
│   │       └── widget-base.php
│   │
│   ├── dashboard/
│   │   └── index.php                # Vista del dashboard
│   │
│   └── admin/
│       ├── departamentos/
│       ├── areas/
│       └── usuarios/
│
├── database/
│   ├── schema.sql                   # Schema completo
│   ├── migrations/                  # Migraciones (opcional)
│   │   ├── 001_create_departamentos.sql
│   │   ├── 002_create_areas.sql
│   │   └── ...
│   └── seeds/                       # Datos de prueba
│       ├── 001_seed_departamentos.sql
│       ├── 002_seed_areas.sql
│       ├── 003_seed_usuarios.sql
│       └── 004_seed_metricas.sql
│
├── tests/                           # Tests (opcional pero recomendado)
│   └── (archivos de testing)
│
├── logs/                            # Logs del sistema
│   └── php-errors.log
│
├── .env                             # Variables de entorno (NO commitear)
├── .env.example                     # Template de .env
├── .gitignore
├── composer.json                    # Dependencias PHP
├── composer.lock
└── README.md                        # Este archivo
```

### Cambios Clave vs Legacy

| Aspecto | Legacy | Nuevo |
|---------|--------|-------|
| **Estructura** | Todo en raíz | Organizado por función |
| **Modelos** | En `/models` raíz | En `/src/Models` |
| **Vistas** | Mezcladas con lógica | Separadas en `/views` |
| **Controladores** | No existen | `/src/Controllers` |
| **Middleware** | No existe | `/src/Middleware` |
| **Public Root** | Raíz del proyecto | `/public` |
| **Config** | Un solo `config.php` | Múltiples en `/config` |
| **Autoloading** | `require_once` manual | Composer autoload |

---

## 🗄️ BASE DE DATOS

### Schema Completo

```sql
-- =============================================
-- TABLA: departamentos (NUEVA)
-- =============================================
CREATE TABLE departamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icono VARCHAR(50) DEFAULT 'building',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_activo (activo),
    INDEX idx_orden (orden)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: areas (MODIFICADA - agregar departamento_id)
-- =============================================
CREATE TABLE areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    departamento_id INT NOT NULL,              -- NUEVO
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    descripcion TEXT,
    color VARCHAR(7) DEFAULT '#3b82f6',
    icono VARCHAR(50) DEFAULT 'chart-bar',
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE RESTRICT,
    INDEX idx_departamento (departamento_id),
    INDEX idx_activo (activo),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: usuarios (MODIFICADA - nuevos roles y scope)
-- =============================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    rol ENUM('super_admin', 'dept_admin', 'dept_viewer') NOT NULL DEFAULT 'dept_viewer',  -- MODIFICADO
    departamento_id INT DEFAULT NULL,          -- NUEVO (NULL para super_admin)
    area_id INT DEFAULT NULL,                  -- NUEVO (solo para dept_viewer)
    foto_perfil VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE SET NULL,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_rol (rol),
    INDEX idx_departamento (departamento_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: periodos (SIN CAMBIOS - del legacy)
-- =============================================
CREATE TABLE periodos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    ejercicio INT NOT NULL,
    periodo INT NOT NULL,
    nombre VARCHAR(50) NOT NULL,
    fecha_inicio DATE,
    fecha_fin DATE,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_ejercicio_periodo (ejercicio, periodo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metricas (SIN CAMBIOS - del legacy)
-- =============================================
CREATE TABLE metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    descripcion TEXT,
    unidad VARCHAR(50),
    tipo_valor ENUM('numero', 'porcentaje', 'tiempo', 'decimal') DEFAULT 'numero',
    icono VARCHAR(50) DEFAULT 'chart-line',
    es_calculada TINYINT(1) DEFAULT 0,
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    UNIQUE KEY uk_area_slug (area_id, slug),
    INDEX idx_area (area_id),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metricas_componentes (SIN CAMBIOS)
-- =============================================
CREATE TABLE metricas_componentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_calculada_id INT NOT NULL,
    metrica_componente_id INT NOT NULL,
    operacion ENUM('suma', 'resta', 'promedio') DEFAULT 'suma',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    
    FOREIGN KEY (metrica_calculada_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (metrica_componente_id) REFERENCES metricas(id) ON DELETE CASCADE,
    INDEX idx_calculada (metrica_calculada_id),
    INDEX idx_componente (metrica_componente_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: valores_metricas (SIN CAMBIOS)
-- =============================================
CREATE TABLE valores_metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_id INT NOT NULL,
    periodo_id INT NOT NULL,
    valor_numero INT DEFAULT NULL,
    valor_decimal DECIMAL(10,2) DEFAULT NULL,
    nota TEXT,
    usuario_registro_id INT DEFAULT NULL,
    usuario_modificacion_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (metrica_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_modificacion_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    UNIQUE KEY uk_metrica_periodo (metrica_id, periodo_id),
    INDEX idx_metrica (metrica_id),
    INDEX idx_periodo (periodo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: configuracion_graficos (SIN CAMBIOS)
-- =============================================
CREATE TABLE configuracion_graficos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    area_id INT NOT NULL,
    tipo VARCHAR(50) NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    configuracion JSON NOT NULL,
    grid_x INT DEFAULT 0,
    grid_y INT DEFAULT 0,
    grid_w INT DEFAULT 4,
    grid_h INT DEFAULT 3,
    visible_para ENUM('admin', 'viewer', 'todos') DEFAULT 'todos',
    orden INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (area_id) REFERENCES areas(id) ON DELETE CASCADE,
    INDEX idx_area (area_id),
    INDEX idx_tipo (tipo),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: metas_metricas (DEL LEGACY - ya existe)
-- =============================================
CREATE TABLE metas_metricas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    metrica_id INT NOT NULL,
    periodo_id INT NOT NULL,
    valor_objetivo DECIMAL(10,2) NOT NULL,
    tipo_comparacion ENUM('mayor_igual', 'menor_igual', 'igual', 'rango') DEFAULT 'mayor_igual',
    valor_min DECIMAL(10,2) DEFAULT NULL,
    valor_max DECIMAL(10,2) DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (metrica_id) REFERENCES metricas(id) ON DELETE CASCADE,
    FOREIGN KEY (periodo_id) REFERENCES periodos(id) ON DELETE CASCADE,
    UNIQUE KEY uk_metrica_periodo (metrica_id, periodo_id),
    INDEX idx_metrica (metrica_id),
    INDEX idx_periodo (periodo_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: audit_log (NUEVA - muy recomendada)
-- =============================================
CREATE TABLE audit_log (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    departamento_id INT DEFAULT NULL,
    accion ENUM('view', 'create', 'update', 'delete', 'login', 'logout') NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id INT,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (departamento_id) REFERENCES departamentos(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_departamento (departamento_id),
    INDEX idx_accion (accion),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- TABLA: log_actividad (DEL LEGACY - opcional mantener)
-- =============================================
CREATE TABLE log_actividad (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT,
    accion VARCHAR(100),
    tabla_afectada VARCHAR(100),
    registro_id INT,
    descripcion TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Relaciones Clave

```
departamentos (1) ─────< (N) areas
areas (1) ─────< (N) metricas
metricas (1) ─────< (N) valores_metricas
periodos (1) ─────< (N) valores_metricas
usuarios (N) >───── (1) departamentos [dept_admin, dept_viewer]
usuarios (N) >───── (1) areas [solo dept_viewer]
```

---

## 🔐 SISTEMA DE PERMISOS

### Roles y Alcance

| Rol | Scope | Descripción |
|-----|-------|-------------|
| **super_admin** | Global | Acceso total al sistema. Ve y edita TODO. |
| **dept_admin** | Departamento específico | Ve y edita solo su departamento y sus áreas. |
| **dept_viewer** | Área específica | Solo VE (read-only) su área asignada. |

### Matriz de Permisos Detallada

| Recurso | super_admin | dept_admin | dept_viewer |
|---------|-------------|------------|-------------|
| **Departamentos** |
| - Listar | ✅ Todos | ✅ El suyo | ✅ El suyo |
| - Ver detalle | ✅ Todos | ✅ El suyo | ✅ El suyo |
| - Crear | ✅ | ❌ | ❌ |
| - Editar | ✅ Todos | ❌ | ❌ |
| - Eliminar | ✅ Todos | ❌ | ❌ |
| **Áreas** |
| - Listar | ✅ Todas | ✅ De su dept | ✅ La suya |
| - Ver detalle | ✅ Todas | ✅ De su dept | ✅ La suya |
| - Crear | ✅ | ✅ En su dept | ❌ |
| - Editar | ✅ Todas | ✅ De su dept | ❌ |
| - Eliminar | ✅ Todas | ✅ De su dept | ❌ |
| **Métricas** |
| - Listar | ✅ Todas | ✅ De su dept | ✅ De su área |
| - Ver detalle | ✅ Todas | ✅ De su dept | ✅ De su área |
| - Crear | ✅ | ✅ En su dept | ❌ |
| - Editar | ✅ Todas | ✅ De su dept | ❌ |
| - Eliminar | ✅ Todas | ✅ De su dept | ❌ |
| **Valores de Métricas** |
| - Ver | ✅ Todos | ✅ De su dept | ✅ De su área |
| - Ingresar/Editar | ✅ Todos | ✅ De su dept | ❌ |
| **Gráficos** |
| - Ver dashboard | ✅ Todos | ✅ De su dept | ✅ De su área |
| - Configurar | ✅ Todos | ✅ De su dept | ❌ |
| - Modo edición | ✅ Todos | ✅ De su dept | ❌ |
| **Usuarios** |
| - Listar | ✅ Todos | ✅ De su dept | ❌ |
| - Ver detalle | ✅ Todos | ✅ De su dept | ❌ |
| - Crear | ✅ | ❌ | ❌ |
| - Editar | ✅ Todos | ❌ | ❌ |
| - Eliminar | ✅ Todos | ❌ | ❌ |
| **Metas** |
| - Ver | ✅ Todas | ✅ De su dept | ✅ De su área |
| - Configurar | ✅ Todas | ✅ De su dept | ❌ |

### Implementación de Permisos

#### Archivo: `src/Services/PermissionService.php`

```php
<?php

class PermissionService {
    
    /**
     * Verifica si el usuario puede ver un departamento
     */
    public static function canViewDepartamento($user, $departamento_id) {
        if ($user['rol'] === 'super_admin') {
            return true;
        }
        
        return $user['departamento_id'] == $departamento_id;
    }
    
    /**
     * Verifica si el usuario puede editar un departamento
     */
    public static function canEditDepartamento($user, $departamento_id) {
        return $user['rol'] === 'super_admin';
    }
    
    /**
     * Verifica si el usuario puede ver un área
     */
    public static function canViewArea($user, $area_id) {
        if ($user['rol'] === 'super_admin') {
            return true;
        }
        
        global $db;
        $stmt = $db->prepare("SELECT departamento_id FROM areas WHERE id = ?");
        $stmt->execute([$area_id]);
        $area = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$area) return false;
        
        if ($user['rol'] === 'dept_admin') {
            return $area['departamento_id'] == $user['departamento_id'];
        }
        
        if ($user['rol'] === 'dept_viewer') {
            return $area_id == $user['area_id'];
        }
        
        return false;
    }
    
    /**
     * Verifica si el usuario puede editar un área
     */
    public static function canEditArea($user, $area_id) {
        if ($user['rol'] === 'super_admin') {
            return true;
        }
        
        if ($user['rol'] === 'dept_admin') {
            global $db;
            $stmt = $db->prepare("SELECT departamento_id FROM areas WHERE id = ?");
            $stmt->execute([$area_id]);
            $area = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $area && $area['departamento_id'] == $user['departamento_id'];
        }
        
        return false;
    }
    
    /**
     * Obtiene lista de departamentos que el usuario puede ver
     */
    public static function getDepartamentosPermitidos($user) {
        global $db;
        
        if ($user['rol'] === 'super_admin') {
            $stmt = $db->query("SELECT * FROM departamentos WHERE activo = 1 ORDER BY orden");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($user['departamento_id']) {
            $stmt = $db->prepare("SELECT * FROM departamentos WHERE id = ? AND activo = 1");
            $stmt->execute([$user['departamento_id']]);
            return [$stmt->fetch(PDO::FETCH_ASSOC)];
        }
        
        return [];
    }
    
    /**
     * Obtiene lista de áreas que el usuario puede ver
     */
    public static function getAreasPermitidas($user, $departamento_id = null) {
        global $db;
        
        if ($user['rol'] === 'super_admin') {
            $sql = "SELECT * FROM areas WHERE activo = 1";
            if ($departamento_id) {
                $sql .= " AND departamento_id = ?";
                $stmt = $db->prepare($sql . " ORDER BY orden");
                $stmt->execute([$departamento_id]);
            } else {
                $stmt = $db->query($sql . " ORDER BY orden");
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($user['rol'] === 'dept_admin') {
            $sql = "SELECT * FROM areas WHERE departamento_id = ? AND activo = 1 ORDER BY orden";
            $stmt = $db->prepare($sql);
            $stmt->execute([$user['departamento_id']]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        if ($user['rol'] === 'dept_viewer' && $user['area_id']) {
            $stmt = $db->prepare("SELECT * FROM areas WHERE id = ? AND activo = 1");
            $stmt->execute([$user['area_id']]);
            return [$stmt->fetch(PDO::FETCH_ASSOC)];
        }
        
        return [];
    }
}
```

---

## 📦 CÓDIGO REUTILIZABLE DEL LEGACY

### Qué Copiar Directamente (100%)

#### 1. Componentes de Gráficos

**Ubicación Legacy:** `legacy/components/charts/`  
**Ubicación Nueva:** `views/components/charts/`  
**Archivos a copiar:**

```bash
# Copiar TODOS estos archivos tal cual:
- kpi-card.php          # ✅ Funciona perfecto
- line.php              # ✅ Funciona perfecto
- bar.php               # ✅ Funciona perfecto
- donut.php             # ✅ Funciona perfecto
- gauge.php             # ✅ Funciona perfecto
- comparison.php        # ✅ Funciona perfecto
- progress.php          # ✅ Funciona perfecto
- multi-bar.php         # ✅ Funciona perfecto
- data-table.php        # ✅ Funciona perfecto
```

**Nota:** NO necesitan modificación, solo copiar. Ya tienen la estructura correcta.

#### 2. ChartRegistry

**Ubicación Legacy:** `legacy/models/ChartRegistry.php`  
**Ubicación Nueva:** `src/Utils/ChartRegistry.php`  

**Cambios mínimos:** Solo actualizar rutas de los componentes:

```php
// Cambiar:
require_once BASE_PATH . '/components/charts/kpi-card.php';

// Por:
require_once BASE_PATH . '/views/components/charts/kpi-card.php';
```

#### 3. CSS y JavaScript

**Archivos a copiar:**

```bash
legacy/assets/css/custom.css           → public/assets/css/custom.css
legacy/assets/css/dashboard-grid.css   → public/assets/css/dashboard-grid.css
legacy/assets/js/app.js                → public/assets/js/app.js (revisar rutas)
```

### Qué Adaptar (Modificación Necesaria)

#### 1. Modelos Base

**Modelo:** `legacy/models/Model.php`  
**Acción:** Copiar y agregar métodos de filtrado por departamento

**Agregar estos métodos:**

```php
/**
 * Filtrar por departamento (para dept_admin)
 */
protected function filterByDepartamento($query, $user) {
    if ($user['rol'] === 'super_admin') {
        return $query; // Sin filtro
    }
    
    if ($user['rol'] === 'dept_admin') {
        // Agregar JOIN con areas y filtrar por departamento
        $query .= " AND a.departamento_id = " . (int)$user['departamento_id'];
    }
    
    return $query;
}
```

#### 2. ValorMetrica.php

**Ubicación Legacy:** `legacy/models/ValorMetrica.php`  
**Problema:** Tenía error de sintaxis (llave extra en línea 261)  
**Acción:** Copiar la versión CORREGIDA que ya generamos  
**Ubicación Nueva:** `src/Models/ValorMetrica.php`

#### 3. Meta.php

**Ubicación Legacy:** `legacy/models/Meta.php`  
**Acción:** Copiar tal cual (ya está bien)  
**Ubicación Nueva:** `src/Models/Meta.php`

### Qué NO Copiar (Reescribir)

#### 1. Sistema de Autenticación

**Legacy:** `legacy/login.php` tiene lógica mezclada  
**Nuevo:** Separar en Controller + Middleware

**Razón:** Necesitamos validar roles nuevos (super_admin, dept_admin, dept_viewer)

#### 2. Navegación/Navbar

**Legacy:** Navbar plano con tabs de áreas  
**Nuevo:** Navegación jerárquica (Departamentos → Áreas)

**Razón:** Nueva estructura organizacional

#### 3. CRUD de Usuarios

**Legacy:** Solo maneja admin/viewer  
**Nuevo:** Debe manejar 3 roles + asignaciones de dept/área

---

## 🚀 GUÍA DE IMPLEMENTACIÓN FASE POR FASE

### FASE 1: SETUP INICIAL (DÍA 1)

#### 1.1 Crear Estructura de Directorios

```bash
cd htdocs/metricas/
mkdir -p config src/{Models,Controllers,Middleware,Services,Utils} public/{admin,api,assets/{css,js,uploads}} views/{layouts,components/{charts,widgets},dashboard,admin} database/{migrations,seeds} logs tests
```

#### 1.2 Configurar Composer

**Archivo:** `composer.json`

```json
{
    "name": "metricas/sistema",
    "description": "Sistema de Métricas Multi-Departamento",
    "autoload": {
        "psr-4": {
            "App\\Models\\": "src/Models/",
            "App\\Controllers\\": "src/Controllers/",
            "App\\Middleware\\": "src/Middleware/",
            "App\\Services\\": "src/Services/",
            "App\\Utils\\": "src/Utils/"
        },
        "files": [
            "src/Utils/Helpers.php"
        ]
    },
    "require": {
        "php": ">=8.1"
    }
}
```

Ejecutar:
```bash
composer install
```

#### 1.3 Crear Base de Datos

```bash
# En MySQL/PHPMyAdmin:
CREATE DATABASE metricas_sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 1.4 Configuración Base

**Archivo:** `config/database.php`

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'metricas_sistema',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
];
```

**Archivo:** `config/app.php`

```php
<?php
return [
    'name' => 'Sistema de Métricas',
    'version' => '2.0',
    'timezone' => 'America/Guatemala',
    'base_url' => '/metricas',
    'environment' => 'development', // development | production
];
```

#### 1.5 Ejecutar Schema SQL

```bash
# Importar database/schema.sql a la BD metricas_sistema
```

---

### FASE 2: MODELOS Y LÓGICA BASE (DÍA 2-3)

#### 2.1 Modelo Base

**Copiar:** `legacy/models/Model.php` → `src/Models/Model.php`

**Modificar:** Agregar namespace y mejoras

```php
<?php
namespace App\Models;

use PDO;

class Model {
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        $this->db = $this->getConnection();
    }
    
    private function getConnection() {
        static $db = null;
        
        if ($db === null) {
            $config = require __DIR__ . '/../../config/database.php';
            
            $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
            $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        }
        
        return $db;
    }
    
    // ... resto de métodos CRUD del legacy ...
}
```

#### 2.2 Modelos Específicos

**Crear estos modelos en orden:**

1. **Departamento.php** (NUEVO)
2. **Area.php** (copiar + modificar)
3. **Usuario.php** (copiar + modificar)
4. **Periodo.php** (copiar)
5. **Metrica.php** (copiar)
6. **ValorMetrica.php** (copiar versión corregida)
7. **Grafico.php** (copiar)
8. **Meta.php** (copiar)

**Ejemplo: Departamento.php**

```php
<?php
namespace App\Models;

class Departamento extends Model {
    protected $table = 'departamentos';
    
    public function getAll() {
        $stmt = $this->db->query("
            SELECT * FROM {$this->table} 
            WHERE activo = 1 
            ORDER BY orden, nombre
        ");
        return $stmt->fetchAll();
    }
    
    public function getWithAreas($id) {
        $stmt = $this->db->prepare("
            SELECT d.*, 
                   COUNT(a.id) as total_areas
            FROM {$this->table} d
            LEFT JOIN areas a ON d.id = a.departamento_id AND a.activo = 1
            WHERE d.id = ?
            GROUP BY d.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
```

#### 2.3 Helpers

**Archivo:** `src/Utils/Helpers.php`

```php
<?php

/**
 * Obtener usuario actual de sesión
 */
function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("
        SELECT u.*, d.nombre as departamento_nombre, a.nombre as area_nombre
        FROM usuarios u
        LEFT JOIN departamentos d ON u.departamento_id = d.id
        LEFT JOIN areas a ON u.area_id = a.id
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verificar si está logueado
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Verificar si es super admin
 */
function isSuperAdmin() {
    $user = getCurrentUser();
    return $user && $user['rol'] === 'super_admin';
}

/**
 * Obtener conexión a BD
 */
function getDB() {
    static $db = null;
    
    if ($db === null) {
        $config = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
        $db = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    }
    
    return $db;
}

/**
 * Redirigir
 */
function redirect($url) {
    $config = require __DIR__ . '/../../config/app.php';
    header('Location: ' . $config['base_url'] . $url);
    exit;
}

/**
 * Sanitizar entrada
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

// ... más helpers del legacy/includes/functions.php ...
```

---

### FASE 3: AUTENTICACIÓN Y PERMISOS (DÍA 3-4)

#### 3.1 Middleware de Autenticación

**Archivo:** `src/Middleware/AuthMiddleware.php`

```php
<?php
namespace App\Middleware;

class AuthMiddleware {
    public static function handle() {
        session_start();
        
        if (!isLoggedIn()) {
            redirect('/login.php');
        }
    }
}
```

#### 3.2 Middleware de Permisos

**Archivo:** `src/Middleware/PermissionMiddleware.php`

```php
<?php
namespace App\Middleware;

use App\Services\PermissionService;

class PermissionMiddleware {
    
    public static function requireSuperAdmin() {
        if (!isSuperAdmin()) {
            http_response_code(403);
            die('Acceso denegado: Se requiere rol Super Admin');
        }
    }
    
    public static function requireDeptAdmin() {
        $user = getCurrentUser();
        if (!in_array($user['rol'], ['super_admin', 'dept_admin'])) {
            http_response_code(403);
            die('Acceso denegado: Se requiere rol Admin');
        }
    }
    
    public static function canAccessArea($area_id) {
        $user = getCurrentUser();
        if (!PermissionService::canViewArea($user, $area_id)) {
            http_response_code(403);
            die('Acceso denegado: No tienes permiso para ver esta área');
        }
    }
}
```

#### 3.3 Login

**Archivo:** `public/login.php`

```php
<?php
session_start();
require_once '../vendor/autoload.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Completa todos los campos';
    } else {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT u.*, d.nombre as departamento_nombre, a.nombre as area_nombre
            FROM usuarios u
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN areas a ON u.area_id = a.id
            WHERE u.username = ? AND u.activo = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['rol'];
            redirect('/index.php');
        } else {
            $error = 'Credenciales incorrectas';
        }
    }
}

// ... HTML del login (copiar del legacy y adaptar) ...
```

---

### FASE 4: DATOS DE PRUEBA (DÍA 4)

#### 4.1 Seeds de Departamentos

**Archivo:** `database/seeds/001_seed_departamentos.sql`

```sql
-- Insertar departamentos de prueba
INSERT INTO departamentos (nombre, descripcion, color, icono, orden) VALUES
('TI Corporativo', 'Departamento de Tecnologías de la Información', '#3b82f6', 'server', 1),
('Servicios', 'Departamento de Atención al Cliente', '#10b981', 'headset', 2);
```

#### 4.2 Seeds de Áreas

**Archivo:** `database/seeds/002_seed_areas.sql`

```sql
-- Áreas de TI Corporativo (dept_id = 1)
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, orden) VALUES
(1, 'Software Factory', 'software', 'Desarrollo de software', '#3b82f6', 'code', 1),
(1, 'Infraestructura', 'infraestructura', 'Servidores y redes', '#8b5cf6', 'server', 2),
(1, 'Ciberseguridad', 'ciberseguridad', 'Seguridad informática', '#ef4444', 'shield', 3);

-- Áreas de Servicios (dept_id = 2)
INSERT INTO areas (departamento_id, nombre, slug, descripcion, color, icono, orden) VALUES
(2, 'Soporte', 'soporte', 'Mesa de ayuda', '#10b981', 'headset', 1),
(2, 'Medios Digitales', 'medios', 'Marketing digital', '#f59e0b', 'photo', 2);
```

#### 4.3 Seeds de Usuarios

**Archivo:** `database/seeds/003_seed_usuarios.sql`

```sql
-- Super Admin
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrador', 'superadmin@metricas.com', 'super_admin', NULL, NULL);
-- password: password

-- Admin TI Corporativo
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('admin_ti', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin TI', 'admin_ti@metricas.com', 'dept_admin', 1, NULL);
-- password: password

-- Viewer de Software Factory
INSERT INTO usuarios (username, password, nombre, email, rol, departamento_id, area_id) VALUES
('viewer_software', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Viewer Software', 'viewer_software@metricas.com', 'dept_viewer', 1, 1);
-- password: password
```

#### 4.4 Seeds de Períodos

**Archivo:** `database/seeds/004_seed_periodos.sql`

```sql
-- Crear períodos de 2026
INSERT INTO periodos (ejercicio, periodo, nombre, fecha_inicio, fecha_fin) VALUES
(2026, 1, 'Enero 2026', '2026-01-01', '2026-01-31'),
(2026, 2, 'Febrero 2026', '2026-02-01', '2026-02-28'),
(2026, 3, 'Marzo 2026', '2026-03-01', '2026-03-31'),
(2026, 4, 'Abril 2026', '2026-04-01', '2026-04-30'),
(2026, 5, 'Mayo 2026', '2026-05-01', '2026-05-31'),
(2026, 6, 'Junio 2026', '2026-06-01', '2026-06-30');
```

#### 4.5 Seeds de Métricas (ejemplo para Software Factory)

**Archivo:** `database/seeds/005_seed_metricas.sql`

```sql
-- Métricas de Software Factory (area_id = 1)
INSERT INTO metricas (area_id, nombre, slug, unidad, tipo_valor, icono, orden) VALUES
(1, 'Proyectos Activos', 'proyectos_activos', 'proyectos', 'numero', 'folder', 1),
(1, 'Proyectos Finalizados', 'proyectos_finalizados', 'proyectos', 'numero', 'check', 2),
(1, 'Bugs en Producción', 'bugs_produccion', 'bugs', 'numero', 'bug', 3),
(1, 'Releases', 'releases', 'releases', 'numero', 'rocket', 4);

-- Métricas de Infraestructura (area_id = 2)
INSERT INTO metricas (area_id, nombre, slug, unidad, tipo_valor, icono, orden) VALUES
(2, 'Disponibilidad Servidores', 'disponibilidad', '%', 'porcentaje', 'server', 1),
(2, 'Incidentes Críticos', 'incidentes', 'incidentes', 'numero', 'alert-triangle', 2);
```

---

### FASE 5: DASHBOARD BÁSICO (DÍA 5-7)

#### 5.1 Index Principal

**Archivo:** `public/index.php`

```php
<?php
session_start();
require_once '../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Services\PermissionService;

// Requerir autenticación
AuthMiddleware::handle();

$user = getCurrentUser();

// Obtener departamentos permitidos
$departamentos = PermissionService::getDepartamentosPermitidos($user);

// Departamento actual (de query string o primero disponible)
$dept_id = $_GET['dept'] ?? ($departamentos[0]['id'] ?? null);

if (!$dept_id) {
    die('No tienes acceso a ningún departamento');
}

// Obtener áreas del departamento
$areas = PermissionService::getAreasPermitidas($user, $dept_id);

// Área actual (de query string o primera disponible)
$area_id = $_GET['area'] ?? ($areas[0]['id'] ?? null);

// ... resto de lógica del dashboard ...
// ... incluir vista ...

require_once '../views/dashboard/index.php';
```

#### 5.2 Vista del Dashboard

**Archivo:** `views/dashboard/index.php`

```php
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Navegación de Departamentos -->
<div class="navbar navbar-expand-md navbar-light border-bottom">
    <div class="container-xl">
        <div class="navbar-nav">
            <?php foreach ($departamentos as $dept): ?>
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle <?php echo $dept['id'] == $dept_id ? 'active' : ''; ?>" 
                       data-bs-toggle="dropdown">
                        <i class="ti ti-<?php echo $dept['icono']; ?> me-1"></i>
                        <?php echo $dept['nombre']; ?>
                    </a>
                    <div class="dropdown-menu">
                        <?php 
                        $dept_areas = PermissionService::getAreasPermitidas($user, $dept['id']);
                        foreach ($dept_areas as $area): 
                        ?>
                            <a class="dropdown-item" href="?dept=<?php echo $dept['id']; ?>&area=<?php echo $area['id']; ?>">
                                <i class="ti ti-<?php echo $area['icono']; ?> me-2"></i>
                                <?php echo $area['nombre']; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Dashboard Grid -->
<div class="page-wrapper">
    <div class="page-body">
        <div class="container-xl">
            
            <!-- Breadcrumb -->
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="page-pretitle">
                            <?php echo $departamento_actual['nombre']; ?>
                        </div>
                        <h2 class="page-title">
                            <?php echo $area_actual['nombre']; ?>
                        </h2>
                    </div>
                    <?php if (in_array($user['rol'], ['super_admin', 'dept_admin'])): ?>
                    <div class="col-auto">
                        <a href="?dept=<?php echo $dept_id; ?>&area=<?php echo $area_id; ?>&edit=1" 
                           class="btn btn-primary">
                            <i class="ti ti-edit me-1"></i>
                            Modo Edición
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Grid de Gráficos (copiar del legacy) -->
            <div class="grid-stack" id="dashboard-grid">
                <?php foreach ($graficos as $grafico): ?>
                    <!-- Renderizar gráfico usando ChartRegistry -->
                <?php endforeach; ?>
            </div>
            
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
```

---

### FASE 6: ADMIN PANEL (DÍA 8-10)

#### 6.1 CRUD Departamentos (solo super_admin)

**Archivo:** `public/admin/departamentos.php`

```php
<?php
session_start();
require_once '../../vendor/autoload.php';

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Models\Departamento;

AuthMiddleware::handle();
PermissionMiddleware::requireSuperAdmin();

$deptModel = new Departamento();
$departamentos = $deptModel->getAll();

// ... lógica CRUD ...
// ... incluir vista ...
```

#### 6.2 CRUD Áreas (super_admin y dept_admin)

Similar estructura, con filtrado por permisos

#### 6.3 CRUD Usuarios (solo super_admin)

Incluir selectores de departamento y área según rol

---

### FASE 7: INTEGRAR SISTEMA DE METAS (DÍA 11-12)

#### 7.1 Copiar Modelo Meta

```bash
cp legacy/models/Meta.php src/Models/Meta.php
```

Agregar namespace y ajustar

#### 7.2 Copiar Funciones de Metas

Del `legacy/includes/functions.php` copiar a `src/Utils/Helpers.php`:
- `calcularCumplimiento()`
- `formatearMeta()`
- `getTipoMetaSugerido()`
- `obtenerResumenCumplimientoArea()`
- `badge_cumplimiento()`

#### 7.3 Crear CRUD de Metas

**Archivo:** `public/admin/metas.php`

Copiar estructura del legacy y adaptar con permisos

#### 7.4 Agregar Burndown Chart

**Archivo:** `views/components/charts/burndown.php`

Copiar del plan original (ya diseñado)

---

## 🧪 TESTING Y DATOS DE PRUEBA

### Usuarios de Prueba

| Username | Password | Rol | Departamento | Área |
|----------|----------|-----|--------------|------|
| `superadmin` | `password` | super_admin | - | - |
| `admin_ti` | `password` | dept_admin | TI Corporativo | - |
| `viewer_software` | `password` | dept_viewer | TI Corporativo | Software Factory |

### Casos de Prueba

#### Test 1: Super Admin ve TODO
1. Login como `superadmin`
2. Debería ver ambos departamentos: TI Corporativo y Servicios
3. Puede navegar a todas las áreas
4. Tiene acceso al panel admin completo

#### Test 2: Dept Admin ve solo su departamento
1. Login como `admin_ti`
2. Solo ve departamento "TI Corporativo"
3. Ve las 3 áreas: Software, Infraestructura, Ciberseguridad
4. Puede editar métricas y gráficos de su departamento
5. NO puede acceder a admin/departamentos.php (403)
6. NO puede acceder a admin/usuarios.php (403)

#### Test 3: Dept Viewer ve solo su área
1. Login como `viewer_software`
2. Solo ve departamento "TI Corporativo"
3. Solo ve área "Software Factory"
4. Dashboard en modo solo lectura (sin botón "Modo Edición")
5. NO puede acceder a ningún admin/* (403)

---

## 📝 CONVENCIONES DE CÓDIGO

### Nombres de Variables

```php
// Usar snake_case
$departamento_id
$area_actual
$user_rol

// NO usar camelCase
$departamentoId  // ❌
$areaActual      // ❌
```

### Nombres de Clases

```php
// PascalCase
class DepartamentoController
class PermissionService

// Con namespace
namespace App\Models;
class Departamento extends Model
```

### Nombres de Archivos

```php
// kebab-case para vistas
departamento-form.php
grafico-config.php

// PascalCase para clases
DepartamentoController.php
PermissionService.php
```

### SQL

```sql
-- Nombres de tablas: snake_case, plural
departamentos
valores_metricas

-- Nombres de columnas: snake_case
departamento_id
fecha_creacion
```

---

## 🚨 ERRORES COMUNES A EVITAR

### 1. Error: "Call to undefined function"

**Causa:** No se cargó Composer autoload  
**Solución:** Agregar al inicio de cada archivo PHP público:

```php
require_once __DIR__ . '/../vendor/autoload.php';
```

### 2. Error: Llave extra en ValorMetrica.php

**Causa:** Copiar el archivo con el error del legacy  
**Solución:** Usar la versión CORREGIDA que ya generamos

### 3. Error 403 en todas las páginas

**Causa:** Permisos muy estrictos  
**Solución:** Revisar `PermissionService` y asegurarse que los checks sean correctos

### 4. Error: Departamentos no aparecen

**Causa:** Usuario sin departamento asignado  
**Solución:** Verificar en BD que usuarios tengan `departamento_id` correcto

---

## 📚 RECURSOS Y REFERENCIAS

### Documentación

- **PHP PDO:** https://www.php.net/manual/es/book.pdo.php
- **Bootstrap 5:** https://getbootstrap.com/docs/5.3/
- **ApexCharts:** https://apexcharts.com/docs/
- **GridStack:** https://gridstackjs.com/

### Código Legacy

- **Ubicación:** `htdocs/metricas/legacy/`
- **Archivos clave a revisar:**
  - `legacy/components/charts/` (todos los gráficos)
  - `legacy/models/ChartRegistry.php` (arquitectura modular)
  - `legacy/assets/js/app.js` (lógica GridStack)

---

## ✅ CHECKLIST DE DESARROLLO

### Setup Inicial
- [ ] Estructura de directorios creada
- [ ] Composer configurado
- [ ] Base de datos creada
- [ ] Schema ejecutado
- [ ] Seeds ejecutados

### Modelos
- [ ] Model.php (base)
- [ ] Departamento.php
- [ ] Area.php
- [ ] Usuario.php
- [ ] Periodo.php
- [ ] Metrica.php
- [ ] ValorMetrica.php
- [ ] Grafico.php
- [ ] Meta.php

### Middleware y Permisos
- [ ] AuthMiddleware.php
- [ ] PermissionMiddleware.php
- [ ] PermissionService.php

### Frontend Básico
- [ ] Login funcional
- [ ] Navegación jerárquica
- [ ] Dashboard con GridStack
- [ ] Componentes de gráficos copiados

### Admin Panel
- [ ] CRUD Departamentos
- [ ] CRUD Áreas
- [ ] CRUD Métricas
- [ ] CRUD Gráficos
- [ ] CRUD Usuarios
- [ ] CRUD Metas

### Testing
- [ ] Login con cada rol funciona
- [ ] Permisos correctos por rol
- [ ] Dashboard muestra datos correctos
- [ ] Gráficos renderizan bien

---

## 🎯 PRÓXIMOS PASOS (POST-MVP)

Después de tener el MVP funcional:

1. **Alertas automáticas** cuando métricas caen
2. **Exportación a PDF/Excel** de dashboards
3. **Comentarios en métricas** (tabla ya existe)
4. **Timeline de eventos** (tabla ya existe)
5. **API REST** para integraciones externas
6. **Notificaciones push** (email, Slack)
7. **Roles personalizados** (más allá de los 3 básicos)
8. **Dashboards públicos** (compartir con externos)

---

## 📞 CONTACTO Y SOPORTE

Este documento fue creado por: **Claude (Anthropic)**  
Fecha: Abril 2026  
Versión: 1.0  

Para dudas sobre implementación, consultar:
- Este README completo
- Código legacy en `/legacy`
- Schema de BD en `/database/schema.sql`

---

**¡ÉXITO CON EL DESARROLLO! 🚀**
