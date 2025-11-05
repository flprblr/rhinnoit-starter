## Documentación de API - Sanctum

---

## Administradores

### Cómo crear credenciales para un usuario

1. **Crear un usuario** en el sistema con email y contraseña

2. **Compartir las credenciales** con el usuario:
   - Email del usuario
   - Contraseña del usuario
   - Device Name: Un nombre único para su aplicación/servicio (ej: "intranet-backoffice", "servicio-interno-1")

**Nota**: El usuario puede usar su email/password para obtener tokens mediante el endpoint `/api/sanctum/token`.

---

## Consumidores

### Credenciales que necesitas

El administrador te habrá proporcionado:

- **Email**: Tu correo electrónico del usuario creado
- **Password**: Tu contraseña asignada
- **Device Name**: Un nombre único para tu aplicación/servicio (ej: "intranet", "backoffice")

**Importante**: Los usuarios no se registran mediante autoservicio. Un administrador crea usuarios que luego pueden obtener tokens mediante los endpoints de la API.

### Cómo usar la API

#### Paso 1: Obtener tu token

**POST** `/api/sanctum/token`

**Body (JSON)**:

```json
{
  "email": "tu_email@empresa.com",
  "password": "tu_password",
  "device_name": "nombre-de-tu-app",
  "abilities": ["read", "write"],
  "expires_at": "2025-12-31T23:59:59"
}
```

**Campos opcionales**:
- `abilities`: Array de habilidades del token (por defecto: `["*"]` para todas las habilidades)
- `expires_at`: Fecha de expiración del token en formato ISO 8601 (ej: "2025-12-31T23:59:59")

**Respuesta**:

```json
{
  "success": true,
  "token": "1|abcdef123456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  },
  "token_info": {
    "name": "nombre-de-tu-app",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31T23:59:59"
  }
}
```

#### Paso 2: Usar el token en tus solicitudes

Agrega el header `Authorization` en todas las solicitudes protegidas:

```
Authorization: Bearer {tu_token}
```

#### Paso 3: Consumir endpoints protegidos

**Respuesta - Obtener usuario actual**:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  }
}
```

---

## Autenticación SPA (Single Page Application)

Si estás desarrollando una SPA que se ejecuta en el mismo dominio que la API, puedes usar autenticación basada en sesiones:

### Paso 1: Obtener cookie CSRF

**GET** `/api/sanctum/csrf-cookie`

**Respuesta**:

```json
{
  "success": true,
  "message": "Cookie CSRF establecida correctamente."
}
```

### Paso 2: Iniciar sesión

**POST** `/api/sanctum/login`

**Body (JSON)**:

```json
{
  "email": "tu_email@empresa.com",
  "password": "tu_password"
}
```

**Respuesta**:

```json
{
  "success": true,
  "message": "Autenticación exitosa.",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  }
}
```

### Paso 3: Cerrar sesión

**POST** `/api/sanctum/logout`

**Respuesta**:

```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente."
}
```

---

## Todos los endpoints disponibles

### Autenticación con Tokens

#### 1) Obtener Token

- Path: `POST /api/sanctum/token`
- Autenticación: No requiere
- Rate Limit: 5 requests por minuto
- Request JSON:

```json
{
  "email": "usuario@empresa.com",
  "password": "password123",
  "device_name": "intranet",
  "abilities": ["read", "write"],
  "expires_at": "2025-12-31T23:59:59"
}
```

- Response JSON:

```json
{
  "success": true,
  "token": "1|abcdef123456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  },
  "token_info": {
    "name": "intranet",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31T23:59:59"
  }
}
```

#### 2) Obtener Usuario Actual

- Path: `GET /api/sanctum/user`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  }
}
```

#### 3) Verificar Token

- Path: `GET /api/sanctum/verify`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "valid": true,
  "token": {
    "id": 10,
    "name": "intranet",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31T23:59:59",
    "last_used_at": "2025-10-29 13:45:00",
    "created_at": "2025-10-28 12:00:00"
  }
}
```

#### 4) Revocar Token Actual

- Path: `POST /api/sanctum/revoke`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Token revocado exitosamente."
}
```

#### 5) Revocar Todos los Tokens

- Path: `POST /api/sanctum/revoke-all`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Todos los tokens han sido revocados exitosamente."
}
```

#### 6) Listar Tokens del Usuario

- Path: `GET /api/sanctum/tokens`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "tokens": [
    {
      "id": 10,
      "name": "intranet",
      "abilities": ["read", "write"],
      "last_used_at": "2025-10-29 13:45:00",
      "expires_at": "2025-12-31T23:59:59",
      "created_at": "2025-10-28 12:00:00"
    }
  ]
}
```

#### 7) Obtener Información de un Token Específico

- Path: `GET /api/sanctum/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "token": {
    "id": 10,
    "name": "intranet",
    "abilities": ["read", "write"],
    "last_used_at": "2025-10-29 13:45:00",
    "expires_at": "2025-12-31T23:59:59",
    "created_at": "2025-10-28 12:00:00",
    "updated_at": "2025-10-28 12:00:00"
  }
}
```

#### 8) Actualizar Token

- Path: `PATCH /api/sanctum/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Request JSON:

```json
{
  "name": "nuevo-nombre",
  "abilities": ["read"],
  "expires_at": "2026-12-31T23:59:59"
}
```

- Response JSON:

```json
{
  "success": true,
  "message": "Token actualizado exitosamente.",
  "token": {
    "id": 10,
    "name": "nuevo-nombre",
    "abilities": ["read"],
    "expires_at": "2026-12-31T23:59:59",
    "last_used_at": "2025-10-29 13:45:00",
    "created_at": "2025-10-28 12:00:00",
    "updated_at": "2025-10-29 14:00:00"
  }
}
```

#### 9) Revocar Token por ID

- Path: `DELETE /api/sanctum/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Token revocado exitosamente."
}
```

#### 10) Revocar Tokens por Nombre

- Path: `POST /api/sanctum/tokens/revoke-by-name`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Request JSON:

```json
{
  "name": "intranet"
}
```

- Response JSON:

```json
{
  "success": true,
  "deleted": 2,
  "message": "Tokens revocados exitosamente."
}
```

#### 11) Revocar Todos Excepto el Actual

- Path: `POST /api/sanctum/tokens/revoke-others`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "deleted": 3,
  "message": "Se han revocado los demás tokens."
}
```

#### 12) Revocar Tokens Expirados (del usuario)

- Path: `POST /api/sanctum/tokens/revoke-expired`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "deleted": 1,
  "message": "Tokens expirados revocados."
}
```

### Autenticación SPA (Single Page Application)

#### 13) Obtener Cookie CSRF

- Path: `GET /api/sanctum/csrf-cookie`
- Autenticación: No requiere
- Rate Limit: 10 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Cookie CSRF establecida correctamente."
}
```

#### 14) Iniciar Sesión (SPA)

- Path: `POST /api/sanctum/login`
- Autenticación: No requiere (usa sesión web)
- Rate Limit: 5 requests por minuto
- Request JSON:

```json
{
  "email": "usuario@empresa.com",
  "password": "password123"
}
```

- Response JSON:

```json
{
  "success": true,
  "message": "Autenticación exitosa.",
  "user": {
    "id": 1,
    "name": "Juan Pérez",
    "email": "usuario@empresa.com"
  }
}
```

#### 15) Cerrar Sesión (SPA)

- Path: `POST /api/sanctum/logout`
- Autenticación: Requiere sesión web activa
- Response JSON:

```json
{
  "success": true,
  "message": "Sesión cerrada exitosamente."
}
```

---

## Token Abilities (Habilidades)

Sanctum permite asignar habilidades específicas a los tokens para controlar qué acciones pueden realizar.

### Crear Token con Habilidades

Al crear un token, puedes especificar las habilidades:

```json
{
  "email": "usuario@empresa.com",
  "password": "password123",
  "device_name": "intranet",
  "abilities": ["read", "write"]
}
```

### Verificar Habilidades en Controladores

En tus controladores, puedes verificar si el token tiene una habilidad específica:

```php
if ($request->user()->tokenCan('write')) {
    // Usuario tiene permiso de escritura
}
```

### Proteger Rutas con Middleware de Habilidades

Puedes proteger rutas usando el middleware de habilidades:

```php
// Requiere todas las habilidades especificadas
Route::get('/data', [DataController::class, 'index'])
    ->middleware(['auth:sanctum', 'abilities:read,write']);

// Requiere al menos una de las habilidades
Route::post('/data', [DataController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:write,admin']);
```

### Habilidades Especiales

- `["*"]`: Token con todas las habilidades (por defecto)
- `["read"]`: Token solo con permiso de lectura
- `["write"]`: Token con permiso de escritura
- `["admin"]`: Token con permisos de administrador

---

## Errores comunes

- **422**: Credenciales incorrectas o campos faltantes
- **401**: Token inválido o expirado
- **403**: Token no tiene la habilidad requerida
- **404**: Token no encontrado
- **429**: Demasiadas solicitudes (rate limit excedido)

---

## Resumen

- **Tipo de autenticación**: Bearer Token o Sesión Web (para SPAs)
- **Formato del token**: Texto plano simple
- **Cómo obtenerlo**: Email + Password + Device Name
- **Cómo usarlo**: `Authorization: Bearer {token}` en headers (para tokens) o cookies de sesión (para SPAs)
- **Registro**: No hay auto-registro. Los administradores crean usuarios que luego pueden obtener tokens mediante los endpoints de la API.
- **Rate Limiting**: Implementado para proteger contra abusos
- **Token Abilities**: Soporte completo para control granular de permisos
- **Expiración de Tokens**: Soporte para tokens con fecha de expiración

---

## 🧹 Mantenimiento automático

- Limpieza de tokens expirados programada: `sanctum:prune-expired` (scheduler cada hora).
- Los tokens sin `expires_at` no se eliminan automáticamente; revócalos con los endpoints de administración.

---

## 🔒 Seguridad

- **Rate Limiting**: Todos los endpoints tienen límites de tasa configurados para prevenir abusos
- **Token Prefix**: Configurable en `.env` con `SANCTUM_TOKEN_PREFIX` para mejorar la seguridad
- **Validación de Expiración**: Los tokens con `expires_at` se validan automáticamente
- **Token Abilities**: Control granular de permisos por token
