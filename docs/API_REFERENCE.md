# API REST - Referencia Completa

**Base URL:** `/metricas/api`  
**Versión:** 1.0  
**Autenticación:** Bearer Token

---

## Índice

1. [Autenticación](#autenticación)
2. [Estructura de Respuestas](#estructura-de-respuestas)
3. [Códigos de Estado](#códigos-de-estado)
4. [Endpoints](#endpoints)
   - [Métricas](#métricas)
   - [Valores](#valores)
   - [Períodos](#períodos)
   - [Áreas](#áreas)
   - [Departamentos](#departamentos)
   - [Metas](#metas)
5. [Ejemplos de Integración](#ejemplos-de-integración)
6. [Rate Limiting](#rate-limiting)
7. [Errores Comunes](#errores-comunes)

---

## Autenticación

### Obtener Token

**Interfaz Web:**
1. Login al sistema
2. Ir a: Perfil → Tokens de API
3. Click en "Generar Token"
4. Copiar token (se muestra solo una vez)

**Formato del Token:**
- Longitud: 64 caracteres hexadecimales
- Generado con: `bin2hex(random_bytes(32))`
- Almacenado como: SHA256 hash

### Usar Token en Requests

**Header requerido:**
```http
Authorization: Bearer {tu-token-aquí}
```

**Ejemplo cURL:**
```bash
curl -H "Authorization: Bearer abc123def456..." \
     http://localhost/metricas/api/metricas
```

**Ejemplo JavaScript:**
```javascript
fetch('http://localhost/metricas/api/metricas', {
    headers: {
        'Authorization': 'Bearer abc123def456...'
    }
})
.then(response => response.json())
.then(data => console.log(data));
```

**Ejemplo PHP:**
```php
$ch = curl_init('http://localhost/metricas/api/metricas');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer abc123def456...'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$data = json_decode($response, true);
```

---

## Estructura de Respuestas

### Respuesta Exitosa

```json
{
  "success": true,
  "data": { /* payload */ }
}
```

### Respuesta con Error

```json
{
  "error": "Error Type",
  "message": "Descripción del error"
}
```

### Respuesta con Mensaje

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": { /* payload */ }
}
```

---

## Códigos de Estado

| Código | Significado | Cuándo se usa |
|--------|-------------|---------------|
| 200 | OK | GET exitoso |
| 201 | Created | POST exitoso (recurso creado) |
| 204 | No Content | OPTIONS (CORS preflight) |
| 400 | Bad Request | Parámetros faltantes o inválidos |
| 401 | Unauthorized | Token ausente, inválido o expirado |
| 403 | Forbidden | Sin permisos para el recurso |
| 404 | Not Found | Recurso no existe |
| 405 | Method Not Allowed | Método HTTP no soportado |
| 500 | Internal Server Error | Error del servidor |

---

## Endpoints

### Métricas

#### **GET /metricas**

Lista todas las métricas según permisos del usuario.

**Query Parameters:**
- `area_id` (opcional): Filtrar por área específica

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     "http://localhost/metricas/api/metricas?area_id=1"
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "area_id": 1,
      "nombre": "Proyectos Activos",
      "descripcion": "Número de proyectos en desarrollo",
      "slug": "proyectos-activos",
      "unidad": "proyectos",
      "tipo_valor": "numero",
      "icono": "briefcase",
      "es_calculada": 0,
      "tiene_meta": 1,
      "orden": 1,
      "activo": 1,
      "created_at": "2026-01-15 10:30:00",
      "updated_at": "2026-01-15 10:30:00"
    }
  ]
}
```

**Permisos:**
- Super Admin: Todas las métricas
- Admin Depto: Solo de su departamento
- Visualizador: Solo de áreas asignadas

---

#### **GET /metricas/{id}**

Obtiene una métrica específica.

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/metricas/api/metricas/1
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Proyectos Activos",
    "area_id": 1,
    "unidad": "proyectos",
    "tipo_valor": "numero",
    "tiene_meta": 1,
    "activo": 1
  }
}
```

**Response 404:**
```json
{
  "error": "Not Found",
  "message": "Métrica no encontrada"
}
```

**Response 403:**
```json
{
  "error": "Forbidden"
}
```

---

#### **POST /metricas**

Crea una nueva métrica.

**Requiere:** Rol admin (super_admin o dept_admin)

**Request Body:**
```json
{
  "nombre": "Nuevos Clientes",
  "area_id": 2,
  "descripcion": "Clientes adquiridos en el período",
  "unidad": "clientes",
  "tipo_valor": "numero",
  "icono": "user-plus",
  "es_calculada": false,
  "tiene_meta": true
}
```

**Campos requeridos:**
- `nombre` (string): Nombre de la métrica
- `area_id` (int): ID del área
- `unidad` (string): Unidad de medida

**Campos opcionales:**
- `descripcion` (string): Descripción
- `tipo_valor` (enum): "numero" o "decimal" (default: "numero")
- `icono` (string): Tabler icon (default: "chart-line")
- `es_calculada` (bool): Default false
- `tiene_meta` (bool): Default false

**Request:**
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Nuevos Clientes",
    "area_id": 2,
    "unidad": "clientes",
    "tipo_valor": "numero",
    "tiene_meta": true
  }' \
  http://localhost/metricas/api/metricas
```

**Response 201:**
```json
{
  "success": true,
  "data": {
    "id": 15
  }
}
```

**Response 400:**
```json
{
  "error": "Bad Request",
  "message": "Campo 'nombre' requerido"
}
```

**Response 403:**
```json
{
  "error": "Forbidden",
  "message": "Se requiere rol admin"
}
```

---

#### **PUT /metricas/{id}**

Actualiza una métrica existente.

**Requiere:** Rol admin con acceso al área

**Request Body:** Mismos campos que POST (todos opcionales)

**Request:**
```bash
curl -X PUT \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Proyectos Activos Actualizados",
    "descripcion": "Nueva descripción"
  }' \
  http://localhost/metricas/api/metricas/1
```

**Response 200:**
```json
{
  "success": true,
  "message": "Métrica actualizada"
}
```

---

#### **DELETE /metricas/{id}**

Elimina (desactiva) una métrica.

**Requiere:** Rol admin con acceso al área

**Request:**
```bash
curl -X DELETE \
  -H "Authorization: Bearer TOKEN" \
  http://localhost/metricas/api/metricas/1
```

**Response 200:**
```json
{
  "success": true,
  "message": "Métrica eliminada"
}
```

---

### Valores

#### **GET /valores**

Obtiene un valor específico de métrica y período.

**Query Parameters (requeridos):**
- `metrica_id`: ID de la métrica
- `periodo_id`: ID del período

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     "http://localhost/metricas/api/valores?metrica_id=1&periodo_id=10"
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 42,
    "metrica_id": 1,
    "periodo_id": 10,
    "valor_numero": 18,
    "valor_decimal": null,
    "nota": "Incremento por campaña",
    "usuario_registro_id": 3,
    "created_at": "2026-04-01 14:30:00"
  }
}
```

**Response 200 (sin valor):**
```json
{
  "success": true,
  "data": null
}
```

---

#### **GET /valores/historico**

Obtiene histórico de valores de una métrica.

**Query Parameters:**
- `metrica_id` (requerido): ID de la métrica
- `periodos` (opcional): Número de períodos (default: 12)

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     "http://localhost/metricas/api/valores/historico?metrica_id=1&periodos=6"
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "periodo_id": 8,
      "periodo_nombre": "Agosto 2025",
      "ejercicio": 2025,
      "periodo": 8,
      "valor": 12,
      "nota": null,
      "fecha_registro": "2025-08-05 10:00:00"
    },
    {
      "periodo_id": 9,
      "periodo_nombre": "Septiembre 2025",
      "ejercicio": 2025,
      "periodo": 9,
      "valor": 15,
      "nota": "Lanzamiento producto",
      "fecha_registro": "2025-09-03 11:20:00"
    }
  ]
}
```

---

#### **POST /valores**

Crea o actualiza un valor de métrica.

**Requiere:** Rol admin con acceso al área

**Request Body:**
```json
{
  "metrica_id": 1,
  "periodo_id": 12,
  "valor": 22,
  "nota": "Cierre de año con campaña especial"
}
```

**Campos requeridos:**
- `metrica_id` (int)
- `periodo_id` (int)
- `valor` (number): Se guardará en `valor_numero` o `valor_decimal` según tipo de métrica

**Campos opcionales:**
- `nota` (string): Observaciones

**Request:**
```bash
curl -X POST \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "metrica_id": 1,
    "periodo_id": 12,
    "valor": 22,
    "nota": "Cierre de año"
  }' \
  http://localhost/metricas/api/valores
```

**Response 201:**
```json
{
  "success": true,
  "data": {
    "id": 87
  },
  "message": "Valor guardado correctamente"
}
```

**Comportamiento:**
- Si ya existe valor para esa métrica/período: **actualiza**
- Si no existe: **crea nuevo**
- Se determina automáticamente si usar `valor_numero` o `valor_decimal` según `tipo_valor` de la métrica

---

### Períodos

#### **GET /periodos**

Lista períodos.

**Query Parameters:**
- `ejercicio` (opcional): Filtrar por año fiscal
- `activos` (opcional): "1" solo activos, "0" todos (default: "1")

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     "http://localhost/metricas/api/periodos?ejercicio=2026&activos=1"
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "nombre": "Enero 2026",
      "ejercicio": 2026,
      "periodo": 1,
      "fecha_inicio": "2026-01-01",
      "fecha_fin": "2026-01-31",
      "es_actual": 0,
      "activo": 1,
      "created_at": "2026-01-01 00:00:00"
    },
    {
      "id": 11,
      "nombre": "Febrero 2026",
      "ejercicio": 2026,
      "periodo": 2,
      "fecha_inicio": "2026-02-01",
      "fecha_fin": "2026-02-28",
      "es_actual": 0,
      "activo": 1,
      "created_at": "2026-02-01 00:00:00"
    }
  ]
}
```

---

#### **GET /periodos/{id}**

Obtiene un período específico.

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/metricas/api/periodos/10
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "nombre": "Enero 2026",
    "ejercicio": 2026,
    "periodo": 1,
    "fecha_inicio": "2026-01-01",
    "fecha_fin": "2026-01-31",
    "es_actual": 0,
    "activo": 1
  }
}
```

---

#### **GET /periodos/actual**

Obtiene el período marcado como actual.

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/metricas/api/periodos/actual
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 14,
    "nombre": "Abril 2026",
    "ejercicio": 2026,
    "periodo": 4,
    "es_actual": 1,
    "activo": 1
  }
}
```

**Response 404:**
```json
{
  "error": "Not Found",
  "message": "No hay período actual configurado"
}
```

---

### Áreas

#### **GET /areas**

Lista áreas según permisos.

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/metricas/api/areas
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "departamento_id": 1,
      "nombre": "Desarrollo",
      "descripcion": "Área de desarrollo de software",
      "icono": "code",
      "color": "#3b82f6",
      "orden": 1,
      "activo": 1
    }
  ]
}
```

---

#### **GET /areas/{id}**

Obtiene un área específica.

---

#### **GET /areas/{id}/metricas**

Obtiene todas las métricas de un área.

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     http://localhost/metricas/api/areas/1/metricas
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Proyectos Activos",
      "area_id": 1,
      "unidad": "proyectos"
    },
    {
      "id": 2,
      "nombre": "Bugs Resueltos",
      "area_id": 1,
      "unidad": "bugs"
    }
  ]
}
```

---

### Departamentos

#### **GET /departamentos**

Lista departamentos según permisos.

---

#### **GET /departamentos/{id}**

Obtiene un departamento específico.

---

#### **GET /departamentos/{id}/areas**

Obtiene áreas de un departamento.

---

### Metas

#### **GET /metas**

Obtiene metas de una métrica.

**Query Parameters:**
- `metrica_id` (requerido): ID de la métrica

**Request:**
```bash
curl -H "Authorization: Bearer TOKEN" \
     "http://localhost/metricas/api/metas?metrica_id=1"
```

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 5,
      "metrica_id": 1,
      "valor_objetivo": 20,
      "periodo_inicio_id": 10,
      "periodo_fin_id": null,
      "sentido": "mayor_mejor",
      "activo": 1,
      "created_at": "2026-01-15 10:00:00"
    }
  ]
}
```

---

#### **POST /metas**

Crea una meta.

**Requiere:** Rol admin

**Request Body:**
```json
{
  "metrica_id": 1,
  "valor_objetivo": 25,
  "periodo_inicio_id": 15,
  "periodo_fin_id": null,
  "sentido": "mayor_mejor"
}
```

**Campos:**
- `metrica_id` (int, requerido)
- `valor_objetivo` (number, requerido): Meta a alcanzar
- `periodo_inicio_id` (int, requerido): Desde cuándo aplica
- `periodo_fin_id` (int, opcional): Hasta cuándo (null = indefinido)
- `sentido` (enum): "mayor_mejor" o "menor_mejor"

---

#### **PUT /metas/{id}**

Actualiza una meta.

---

#### **DELETE /metas/{id}**

Elimina (desactiva) una meta.

---

## Ejemplos de Integración

### Python

```python
import requests

API_URL = "http://localhost/metricas/api"
TOKEN = "tu-token-aquí"

headers = {
    "Authorization": f"Bearer {TOKEN}"
}

# Obtener métricas
response = requests.get(f"{API_URL}/metricas", headers=headers)
metricas = response.json()["data"]

# Crear valor
nuevo_valor = {
    "metrica_id": 1,
    "periodo_id": 12,
    "valor": 30
}
response = requests.post(
    f"{API_URL}/valores",
    json=nuevo_valor,
    headers=headers
)
print(response.json())
```

### Node.js

```javascript
const axios = require('axios');

const API_URL = 'http://localhost/metricas/api';
const TOKEN = 'tu-token-aquí';

const api = axios.create({
    baseURL: API_URL,
    headers: {
        'Authorization': `Bearer ${TOKEN}`
    }
});

// Obtener histórico
async function getHistorico(metricaId, periodos = 12) {
    const response = await api.get('/valores/historico', {
        params: { metrica_id: metricaId, periodos }
    });
    return response.data.data;
}

// Usar
getHistorico(1, 6).then(data => {
    console.log(data);
});
```

### Excel / Power Query

```m
let
    Token = "tu-token-aquí",
    Url = "http://localhost/metricas/api/metricas",
    Source = Json.Document(Web.Contents(Url, [
        Headers=[
            Authorization="Bearer " & Token
        ]
    ])),
    Data = Source[data],
    ToTable = Table.FromList(Data, Splitter.SplitByNothing(), null, null, ExtraValues.Error),
    Expand = Table.ExpandRecordColumn(ToTable, "Column1", {"id", "nombre", "unidad"})
in
    Expand
```

### Google Sheets / Apps Script

```javascript
function getMetricas() {
  const TOKEN = 'tu-token-aquí';
  const url = 'http://localhost/metricas/api/metricas';
  
  const options = {
    'method': 'get',
    'headers': {
      'Authorization': 'Bearer ' + TOKEN
    }
  };
  
  const response = UrlFetchApp.fetch(url, options);
  const data = JSON.parse(response.getContentText());
  
  return data.data;
}
```

---

## Rate Limiting

**Estado actual:** Deshabilitado

**Futuro (v2.1):**
- Límite: 1000 requests/hora por token
- Headers de respuesta:
  ```
  X-RateLimit-Limit: 1000
  X-RateLimit-Remaining: 987
  X-RateLimit-Reset: 1640995200
  ```
- Error 429 cuando se excede:
  ```json
  {
    "error": "Too Many Requests",
    "message": "Has excedido el límite de 1000 requests por hora",
    "retry_after": 3600
  }
  ```

---

## Errores Comunes

### Token inválido o expirado

```json
{
  "error": "Unauthorized",
  "message": "Token de API inválido o expirado"
}
```

**Solución:** Regenerar token en el panel de usuario

---

### Sin permisos

```json
{
  "error": "Forbidden",
  "message": "No tienes acceso a esta área"
}
```

**Solución:** Contactar admin para asignar permisos

---

### Parámetros faltantes

```json
{
  "error": "Bad Request",
  "message": "Campo 'nombre' requerido"
}
```

**Solución:** Revisar campos requeridos en la documentación

---

### Recurso no encontrado

```json
{
  "error": "Not Found",
  "message": "Métrica no encontrada"
}
```

**Solución:** Verificar que el ID existe y está activo

---

## Notas Adicionales

### CORS

La API incluye headers CORS:
```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

### Formato de Fechas

Todas las fechas usan formato ISO 8601:
```
2026-04-21 14:30:00
```

### Números Decimales

Los valores decimales usan hasta 4 decimales:
```
DECIMAL(15,4)
```

Ejemplo: `1234.5678`

---

**Última actualización:** Abril 2026
