# 🚀 Guía de Despliegue - Sistema de Métricas IT

## 📋 Requisitos Previos

- **PHP** >= 8.1
- **MySQL** >= 5.7 o MariaDB >= 10.3
- **Composer** (para dependencias PHP)
- **Servidor Web**: Apache, Nginx o IIS
- **Extensiones PHP**:
  - pdo_mysql
  - mbstring
  - xml
  - zip
  - gd (opcional, para exportación de gráficos)

---

## 🔧 Instalación

### 1. Clonar o Descargar el Proyecto

```bash
cd /var/www/html  # o C:\xampp\htdocs en Windows
git clone <tu-repositorio> metricas
cd metricas
```

### 2. Instalar Dependencias

```bash
composer install --no-dev --optimize-autoloader
```

> **Nota**: En desarrollo usa solo `composer install`

### 3. Configurar Base de Datos

#### 3.1 Crear la base de datos

```sql
CREATE DATABASE metricas_sistema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### 3.2 Importar el esquema

```bash
mysql -u root -p metricas_sistema < database/metricas_sistema.sql
```

### 4. Configurar Variables de Entorno

#### 4.1 Crear archivo `.env`

```bash
cp .env.example .env
```

#### 4.2 Editar `.env` con tus credenciales

```env
# ==========================================
# CONFIGURACIÓN DE BASE DE DATOS
# ==========================================
DB_HOST=localhost
DB_NAME=metricas_sistema
DB_USER=tu_usuario
DB_PASS=tu_password_seguro
DB_CHARSET=utf8mb4

# ==========================================
# CONFIGURACIÓN DEL SISTEMA
# ==========================================
APP_NAME="Sistema de Métricas IT"
APP_VERSION=2.0.0
TIMEZONE=America/Guatemala

# ==========================================
# CONFIGURACIÓN DE ENTORNO
# ==========================================
ENVIRONMENT=production

# ==========================================
# RUTAS (relativas al DocumentRoot)
# ==========================================
# CRÍTICO: Ajusta según tu instalación
#   - Raíz del servidor: BASE_URL=/
#   - Subcarpeta: BASE_URL=/metricas
BASE_URL=/metricas

# ==========================================
# SEGURIDAD
# ==========================================
# Genera una clave aleatoria:
# php -r "echo bin2hex(random_bytes(16));"
APP_KEY=
```

### 5. Configurar Permisos

```bash
# Linux/Mac
chmod -R 755 .
chmod -R 775 public/assets/uploads
chmod 660 .env

# Propietario del servidor web
chown -R www-data:www-data .
```

### 6. Configurar Servidor Web

#### Apache (.htaccess ya incluido)

Asegúrate que `mod_rewrite` esté habilitado:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Virtual Host ejemplo:**

```apache
<VirtualHost *:80>
    ServerName metricas.tudominio.com
    DocumentRoot /var/www/html/metricas/public
    
    <Directory /var/www/html/metricas/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/metricas-error.log
    CustomLog ${APACHE_LOG_DIR}/metricas-access.log combined
</VirtualHost>
```

#### Nginx

```nginx
server {
    listen 80;
    server_name metricas.tudominio.com;
    root /var/www/html/metricas/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### IIS (web.config ya incluido)

1. Instalar **URL Rewrite Module**
2. Configurar el sitio apuntando a `/public`
3. El archivo `web.config` ya está configurado

---

## 🔐 Seguridad

### 1. Proteger `.env`

```apache
# En .htaccess (ya incluido)
<Files .env>
    Require all denied
</Files>
```

### 2. Modo Producción

En `.env`:
```env
ENVIRONMENT=production
```

Esto deshabilitará:
- Mensajes de error detallados
- Stack traces
- Debug mode

### 3. HTTPS (Recomendado)

```bash
# Certificado Let's Encrypt
sudo certbot --apache -d metricas.tudominio.com
```

---

## 👤 Primer Usuario

El sistema crea automáticamente un super_admin:

```
Email: admin@metricas.local
Password: admin123
```

**⚠️ IMPORTANTE**: Cambia la contraseña inmediatamente después del primer login.

---

## ✅ Verificación Post-Instalación

### 1. Verificar Configuración

Accede a: `http://tudominio.com/metricas/public/verificar.php`

Esto mostrará:
- ✓ Conexión a base de datos
- ✓ Extensiones PHP
- ✓ Permisos de archivos
- ✓ Variables de entorno

### 2. Acceder al Sistema

```
URL: http://tudominio.com/metricas/public/
Login: admin@metricas.local
Password: admin123
```

---

## 🔄 Actualización

```bash
# Respaldar base de datos
mysqldump -u root -p metricas_sistema > backup_$(date +%Y%m%d).sql

# Descargar actualizaciones
git pull origin main

# Actualizar dependencias
composer install --no-dev --optimize-autoloader

# Ejecutar migraciones si existen
php database/migrations/run.php

# Limpiar caché (si aplica)
rm -rf cache/*
```

---

## 🐛 Solución de Problemas

### Error 403 Forbidden
- Verificar permisos de carpetas
- Verificar que `AllowOverride All` esté habilitado
- Verificar configuración de BASE_URL en `.env`

### Página en blanco
- Revisar logs: `/logs/php-errors.log`
- Verificar extensiones PHP
- Verificar conexión a base de datos

### Assets no cargan (404)
- Verificar BASE_URL en `.env`
- Debe coincidir con la ruta real del servidor
- Ejemplo: Si accedes via `/metricas/public/`, usa `BASE_URL=/metricas`

### Errores de Base de Datos
- Verificar credenciales en `.env`
- Verificar que la BD esté importada correctamente
- Verificar charset: debe ser `utf8mb4`

---

## 📞 Soporte

- **Documentación**: `/docs/`
- **Guía de Usuario**: `/docs/MANUAL-USUARIO.md`
- **Guía de Desarrollo**: `/docs/GUIA-DESARROLLO.md`
- **API Reference**: `/docs/API_REFERENCE.md`

---

## 📝 Checklist de Despliegue

- [ ] Base de datos creada e importada
- [ ] Archivo `.env` configurado correctamente
- [ ] `BASE_URL` ajustado según tu instalación
- [ ] Dependencias instaladas con Composer
- [ ] Permisos de archivos configurados
- [ ] Servidor web configurado (Apache/Nginx/IIS)
- [ ] `.env` protegido (no accesible vía web)
- [ ] ENVIRONMENT=production en `.env`
- [ ] Contraseña del admin cambiada
- [ ] HTTPS configurado (producción)
- [ ] Backup automático configurado

---

**¡Listo!** Tu Sistema de Métricas IT está desplegado. 🎉
