# Guía para Completar la Exportación de Reportes a PDF/DOCX

## Problema Actual
Las dependencias de Composer no están instaladas, por lo que la exportación a PDF y DOCX no funciona.

## Solución: Instalar Dependencias

### Paso 1: Instalar Composer (si no lo tienes)

1. Descarga el instalador de Composer:
   - URL directa: https://getcomposer.org/Composer-Setup.exe
   
2. Ejecuta `Composer-Setup.exe`
   - Deja las opciones por defecto
   - Si pide ruta de PHP: `C:\xampp\php\php.exe`
   - Completa la instalación

3. **IMPORTANTE**: Reinicia PowerShell después de instalar

### Paso 2: Verificar que Composer esté instalado

```powershell
composer --version
```

Deberías ver algo como: `Composer version 2.x.x`

### Paso 3: Instalar las dependencias del proyecto

```powershell
cd C:\xampp\htdocs\metricas
composer install --no-interaction --optimize-autoloader
```

Esto instalará:
- ✅ `phpoffice/phpword` (v1.2) - Para exportar a DOCX
- ✅ `tecnickcom/tcpdf` (v6.6) - Para exportar a PDF
- ✅ `dompdf/dompdf` (v2.0) - Para exportar a PDF (alternativo)

### Paso 4: Verificar la instalación

Después de `composer install`, verifica que se creó la carpeta `vendor/`:

```powershell
ls vendor/ | Select-String -Pattern "phpoffice|tecnickcom|dompdf"
```

Deberías ver:
- phpoffice
- tecnickcom
- dompdf

### Paso 5: Probar la exportación

1. Abre un reporte editado en el sistema
2. Ve a **Archivo → Exportar como → PDF** o **DOCX**
3. Debería descargar el archivo correctamente

## Archivos Relevantes

### Backend - Exportación
- **`src/Services/ReporteExportService.php`** - Servicio que maneja todas las exportaciones
  - `exportToPDF()` - Usa TCPDF
  - `exportToDocx()` - Usa PHPOffice\PhpWord
  
- **`public/admin/reportes-export.php`** - Endpoint que recibe las peticiones de exportación
  - Parámetros: `?id={reporte_id}&format={pdf|docx}`

### Frontend - UI
- **`public/admin/reportes-editor.php`** - Menú "Archivo" con opciones de exportar
  - Líneas 546-553: Menú dropdown con PDF y DOCX

## Formatos de Exportación

### PDF
- Usa TCPDF
- Márgenes: 2.54cm (1 pulgada)
- Tamaño: Letter (21.59cm x 27.94cm)
- Incluye: encabezado, pie de página, metadata

### DOCX (Word)
- Usa PHPOffice\PhpWord
- Márgenes: 2.54cm (1 pulgada)
- Tamaño: Letter
- Convierte HTML del editor Quill a formato Word
- Incluye: propiedades del documento, encabezados, estilos

## Solución de Problemas

### Error: "Class TCPDF not found"
**Causa**: Dependencias no instaladas
**Solución**: Ejecutar `composer install`

### Error: "composer: command not found"
**Causa**: Composer no instalado o no en PATH
**Solución**: 
1. Instalar Composer desde https://getcomposer.org/Composer-Setup.exe
2. Reiniciar PowerShell
3. Verificar con `composer --version`

### Error: "Failed to open stream"
**Causa**: Permisos de escritura en `sys_get_temp_dir()`
**Solución**: Verificar permisos de la carpeta temporal de Windows

### Las exportaciones están vacías
**Causa**: El contenido del reporte está vacío o no se guardó
**Solución**: 
1. Asegurarse de guardar el reporte antes de exportar
2. Verificar que el campo `contenido` en la BD no esté vacío

## Tareas Pendientes (para Claude Code en PowerShell)

Cuando ejecutes Claude Code en PowerShell con permisos, pedile que:

1. **Instale las dependencias:**
   ```powershell
   composer install
   ```

2. **Pruebe la exportación** creando un reporte de prueba y exportándolo

3. **Verifique que los archivos se descarguen correctamente**

4. **Si hay errores**, que revise los logs de PHP en `C:\xampp\php\logs\php_error_log`

## Próximos Pasos

Después de que funcione la exportación:
- ✅ Insertar gráficas en los reportes
- ✅ Mejorar el formato de exportación
- ✅ Agregar portada personalizable
- ✅ Incluir tabla de contenidos

---

**Fecha**: 2026-04-22  
**Estado**: Pendiente instalación de dependencias  
**Archivos modificados**: 
- `public/admin/reportes-export.php` (creado)
- `public/admin/reportes-editor.php` (menú simplificado)
- `src/Services/ReporteExportService.php` (ya existe)
