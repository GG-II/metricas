# Guía de Inicio Rápido

Configura el Sistema de Métricas en **15 minutos**.

---

## ✅ Pre-requisitos

- PHP 8.1+ instalado
- MySQL 5.7+ / MariaDB 10.3+
- Apache con mod_rewrite
- Composer instalado

---

## 🚀 Instalación en 5 Pasos

### **1. Clonar Proyecto**

```bash
cd C:\xampp\htdocs
git clone <repository-url> metricas
cd metricas
```

### **2. Instalar Dependencias**

```bash
composer install
```

### **3. Crear Base de Datos**

```sql
CREATE DATABASE metricas_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **4. Configurar Conexión**

Crear `config/database.php`:

```php
<?php
return [
    'host' => 'localhost',
    'database' => 'metricas_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### **5. Importar Schema**

```bash
mysql -u root -p metricas_db < database/schema.sql
```

O via phpMyAdmin: Importar → `database/schema.sql`

---

## ✨ Primer Acceso

### Iniciar Apache

```bash
# Windows (XAMPP)
C:\xampp\xampp-control.exe
# Iniciar Apache y MySQL

# Linux
sudo systemctl start apache2
sudo systemctl start mysql
```

### Acceder al Sistema

**URL:** `http://localhost/metricas/public/login.php`

**Credenciales iniciales:**
- Email: `admin@sistema.com`
- Password: `admin123`

**⚠️ Cambiar contraseña inmediatamente**

---

## 🎯 Configuración Inicial (10 minutos)

### **Paso 1: Crear Departamento**

1. Ir a: **Administración → Departamentos**
2. Click "Nuevo Departamento"
3. Completar:
   - **Nombre:** "Ventas"
   - **Ícono:** "shopping-cart"
   - **Color:** `#22c55e`
4. Guardar

### **Paso 2: Crear Área**

1. Ir a: **Administración → Áreas**
2. Click "Nueva Área"
3. Completar:
   - **Nombre:** "Comercial"
   - **Departamento:** Ventas
   - **Ícono:** "chart-line"
4. Guardar

### **Paso 3: Crear Período**

1. Ir a: **Administración → Períodos**
2. Click "Nuevo Período"
3. Completar:
   - **Nombre:** "Abril 2026"
   - **Ejercicio:** 2026
   - **Período:** 4
   - **Fecha inicio:** 2026-04-01
   - **Fecha fin:** 2026-04-30
   - ☑️ **Es actual**
   - ☑️ **Activo**
4. Guardar

### **Paso 4: Crear Primera Métrica**

1. Ir a: **Administración → Métricas**
2. Click "Nueva Métrica"
3. Completar:
   - **Nombre:** "Ventas del Mes"
   - **Área:** Comercial
   - **Unidad:** "unidades"
   - **Tipo de valor:** Numérico
   - **Ícono:** "shopping-bag"
   - ☑️ **Tiene meta**
4. Guardar

### **Paso 5: Definir Meta**

1. En la fila de "Ventas del Mes", click "Metas"
2. Click "Nueva Meta"
3. Completar:
   - **Valor objetivo:** 100
   - **Período inicio:** Abril 2026
   - **Período fin:** (dejar vacío = indefinido)
   - **Sentido:** Mayor es mejor
4. Guardar

### **Paso 6: Registrar Valor**

1. En la fila de "Ventas del Mes", click "Valores"
2. Click "Registrar Valor"
3. Completar:
   - **Período:** Abril 2026
   - **Valor:** 85
4. Guardar

### **Paso 7: Crear Gráfico en Dashboard**

1. Ir al **Dashboard** (Inicio)
2. Seleccionar área "Comercial"
3. Click **"Modo Edición"**
4. Click **"Agregar Gráfico"**
5. Seleccionar: **"KPI con Meta"**
6. Configurar:
   - **Métrica:** Ventas del Mes
7. Guardar
8. Click **"Guardar y Salir"**

**🎉 ¡Listo! Ya tienes tu primer dashboard funcionando**

---

## 🔧 Configuración Avanzada (Opcional)

### Habilitar Caché

Crear carpeta con permisos:

```bash
mkdir -p storage/cache
chmod 755 storage/cache  # Linux
```

Usar en consultas frecuentes:

```php
use App\Utils\Cache;

$metricas = Cache::remember('metricas_area_1', 300, function() {
    return $metricaModel->getByArea(1);
});
```

### Configurar API REST

#### 1. Generar Token

1. Login al sistema
2. Ir a: **Perfil → Tokens de API**
3. Click "Generar Token"
4. Copiar token

#### 2. Probar Endpoint

```bash
curl -H "Authorization: Bearer TU-TOKEN-AQUÍ" \
     http://localhost/metricas/api/metricas
```

Debería retornar JSON con métricas.

### Lazy Loading de Gráficos

Incluir script en dashboard:

```html
<script src="/metricas/public/assets/js/lazy-charts.js"></script>
```

Los gráficos se cargarán automáticamente al hacer scroll.

---

## 📊 Crear Más Gráficos

### Gráfico de Línea con Meta

1. Dashboard → Modo Edición → Agregar Gráfico
2. Seleccionar: **"Línea con Meta"**
3. Configurar:
   - **Métrica:** Ventas del Mes
   - **Períodos a mostrar:** 12
4. Guardar

### Gráfico de Comparación

1. Crear 2-3 métricas más
2. Dashboard → Agregar Gráfico
3. Seleccionar: **"Multi-Línea"**
4. Seleccionar múltiples métricas
5. Guardar

### Ver Catálogo Completo

**Ubicación:** `Administración → Gráficos`

Verás 21 tipos de gráficos con:
- Descripción
- Vista previa
- Casos de uso sugeridos

---

## 👥 Crear Usuarios

### Usuario Administrador de Departamento

1. Ir a: **Administración → Usuarios**
2. Click "Nuevo Usuario"
3. Completar:
   - **Nombre:** "María Sánchez"
   - **Email:** maria@empresa.com
   - **Contraseña:** temporal123
   - **Rol:** Admin Departamento
   - **Departamento:** Ventas
4. Guardar

María podrá:
- ✅ Ver dashboards de Ventas
- ✅ Crear/editar métricas de Ventas
- ✅ Registrar valores
- ✅ Crear gráficos
- ❌ Ver otros departamentos

### Usuario Visualizador

1. Crear usuario
2. Configurar:
   - **Rol:** Visualizador
   - **Áreas:** Seleccionar Comercial
3. Guardar

El usuario solo podrá:
- ✅ Ver dashboard de Comercial
- ❌ Editar métricas
- ❌ Agregar gráficos

---

## 📥 Exportar Datos

### Desde Dashboard

1. Ir al Dashboard
2. Click **"Exportar"**
3. Seleccionar:
   - **Formato:** Excel (CSV) o PDF
   - **Períodos:** 6, 12, 24 o 36 meses
4. Click "Exportar"

### Desde Admin de Métricas

1. **Administración → Métricas**
2. Filtrar por área (opcional)
3. Click **"Exportar"**
4. Descarga tabla actual con histórico

---

## 🔌 Integrar con API

### Ejemplo Python

```python
import requests

API_URL = "http://localhost/metricas/api"
TOKEN = "tu-token-aquí"

# Obtener métricas
response = requests.get(
    f"{API_URL}/metricas",
    headers={"Authorization": f"Bearer {TOKEN}"}
)
metricas = response.json()["data"]
print(metricas)

# Registrar valor
nuevo_valor = {
    "metrica_id": 1,
    "periodo_id": 14,
    "valor": 95
}
response = requests.post(
    f"{API_URL}/valores",
    json=nuevo_valor,
    headers={"Authorization": f"Bearer {TOKEN}"}
)
print(response.json())
```

### Ejemplo JavaScript

```javascript
const TOKEN = 'tu-token-aquí';
const API_URL = 'http://localhost/metricas/api';

fetch(`${API_URL}/metricas`, {
    headers: {
        'Authorization': `Bearer ${TOKEN}`
    }
})
.then(res => res.json())
.then(data => console.log(data.data));
```

---

## 🆘 Problemas Comunes

### "No puedo iniciar sesión"

**Solución:**
1. Verificar que Apache y MySQL estén iniciados
2. Verificar URL: `http://localhost/metricas/public/login.php`
3. Usar credenciales por defecto: `admin@sistema.com` / `admin123`

### "No veo ningún dashboard"

**Solución:**
1. Verificar que el usuario tenga departamento o áreas asignadas
2. Crear al menos un departamento, área y métrica
3. Logout y login de nuevo

### "Gráfico no se muestra"

**Solución:**
1. Registrar al menos un valor para la métrica
2. Verificar que el período esté activo
3. Abrir consola del navegador (F12) y buscar errores

### "API retorna 401"

**Solución:**
1. Verificar que el token esté correcto
2. Comprobar header: `Authorization: Bearer {token}`
3. Regenerar token si expiró

### "Dashboard carga lento"

**Solución:**
1. Habilitar caché (ver arriba)
2. Reducir número de gráficos (máx 12)
3. Ejecutar: `php cache-manager.php --stats`

---

## 📚 Próximos Pasos

1. **Leer documentación completa:** `README.md`
2. **Explorar API:** `docs/API_REFERENCE.md`
3. **Ver ejemplos de gráficos:** Dashboard → Modo Edición → Catálogo
4. **Configurar usuarios y permisos:** Administración → Usuarios
5. **Importar datos vía API:** Python/Node.js scripts

---

## 🎓 Recursos

- **Documentación completa:** `/README.md`
- **Referencia API:** `/docs/API_REFERENCE.md`
- **Documentación interactiva API:** `http://localhost/metricas/api`
- **Tabler Icons:** https://tabler-icons.io
- **ApexCharts Docs:** https://apexcharts.com/docs

---

**¿Necesitas ayuda?**
- Email: soporte@tuempresa.com
- Issues: [GitHub Issues]

---

**Creado en 15 minutos ✨**
