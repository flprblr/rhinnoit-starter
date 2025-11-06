## Documentación de API - Passport

---

## Administradores

### Cómo crear credenciales para un usuario externo

1. **Crear un usuario** en el sistema con email y contraseña

2. **Crear un cliente OAuth** (requiere configuración de Passport):
   - Ejecutar `php artisan passport:client` para crear un cliente OAuth
   - O usar el método programático: `Passport::client()->create()`
   - El sistema generará automáticamente:
     - `client_id`: Identificador único del cliente
     - `client_secret`: Secreto del cliente (mostrado una vez, guárdalo)

3. **Compartir las credenciales** con el usuario externo:
   - `client_id` del cliente OAuth creado
   - `client_secret` del cliente OAuth
   - Email del usuario asignado
   - Contraseña del usuario asignado

**Nota**: El usuario externo puede usar estas credenciales para obtener tokens mediante el endpoint `/api/passport/token` usando el flujo OAuth2 "Password Grant".

---

## Consumidores

### Credenciales que necesitas

El administrador te habrá proporcionado:

- **Client ID**: Identificador de tu cliente OAuth
- **Client Secret**: Secreto del cliente (mantén esto seguro)
- **Username**: Email del usuario asignado a tu cliente
- **Password**: Contraseña del usuario asignado
- **Scopes** (opcional): Permisos específicos que necesitas (ej: "read", "write")

**Importante**: Los usuarios externos NO se registran. Un administrador crea tu cliente OAuth y usuario que luego pueden obtener tokens mediante los endpoints de la API.

### Cómo usar la API

#### Paso 1: Obtener tu token de acceso

**POST** `/api/passport/token`

**Headers**:
```
Content-Type: application/x-www-form-urlencoded
Accept: application/json
```

**Body (form-urlencoded)**:

```
grant_type=password
client_id={TU_CLIENT_ID}
client_secret={TU_CLIENT_SECRET}
username=usuario@empresa.com
password=tu_password
scope=read write
```

**Campos opcionales**:
- `scope`: String con scopes separados por espacios (ej: "read write admin"). Si no se especifica, se otorgan todos los scopes disponibles.

**Ejemplo con curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "grant_type=password&client_id=1&client_secret=xyz&username=usuario@empresa.com&password=password123&scope=read write"
```

**Respuesta**:

```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 1296000,
  "refresh_token": "def50200abc...",
  "scope": "read write"
}
```

**Guarda estos valores**:
- `token`: Tu token de acceso (lo usarás en cada solicitud)
- `refresh_token`: Para renovar el token cuando expire
- `expires_in`: Segundos hasta que expire el token (por defecto: 15 días)

#### Paso 2: Usar el token en tus solicitudes

Agrega el header `Authorization` en todas las solicitudes protegidas:

```
Authorization: Bearer {tu_token}
```

#### Paso 3: Consumir endpoints protegidos

**Ejemplo - Obtener usuario actual**:

```bash
curl -X GET http://localhost:8000/api/passport/user \
  -H "Authorization: Bearer {tu_token}"
```

**Respuesta**:

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

#### Paso 4: Refrescar el token (cuando expire)

Cuando tu `token` expire, usa el `refresh_token` para obtener uno nuevo sin volver a autenticarte:

**POST** `/api/passport/token`

**Body (form-urlencoded)**:

```
grant_type=refresh_token
refresh_token={TU_REFRESH_TOKEN}
client_id={TU_CLIENT_ID}
client_secret={TU_CLIENT_SECRET}
scope=read write
```

**Ejemplo con curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "grant_type=refresh_token&refresh_token={REFRESH_TOKEN}&client_id=1&client_secret=xyz&scope=read write"
```

**Respuesta**: Igual que obtener token, pero con nuevos valores de `token` y `refresh_token`.

---

## Todos los endpoints disponibles

### Autenticación con Tokens

#### 1) Obtener Token (Password Grant)

- Path: `POST /api/passport/token`
- Autenticación: No requiere
- Rate Limit: 5 requests por minuto
- Content-Type: `application/x-www-form-urlencoded`
- Request Body (form-urlencoded):

```
grant_type=password
client_id=1
client_secret=xyz
username=usuario@empresa.com
password=password123
scope=read write
```

- Response JSON:

```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "token_type": "Bearer",
  "expires_in": 1296000,
  "refresh_token": "def50200abc...",
  "scope": "read write"
}
```

#### 2) Refrescar Token (Refresh Token Grant)

- Path: `POST /api/passport/token`
- Autenticación: No requiere
- Rate Limit: 5 requests por minuto
- Content-Type: `application/x-www-form-urlencoded`
- Request Body (form-urlencoded):

```
grant_type=refresh_token
refresh_token=def50200abc...
client_id=1
client_secret=xyz
scope=read write
```

- Response JSON: Igual que obtener token, con nuevos valores

#### 3) Obtener Usuario Actual

- Path: `GET /api/passport/user`
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

#### 4) Verificar Token

- Path: `GET /api/passport/verify`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "valid": true,
  "token": {
    "id": 10,
    "client_id": 1,
    "scopes": ["read", "write"],
    "expires_at": "2025-11-12 12:00:00",
    "created_at": "2025-10-28 12:00:00"
  }
}
```

#### 5) Listar Tokens del Usuario

- Path: `GET /api/passport/tokens`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "tokens": [
    {
      "id": 10,
      "client_id": 1,
      "scopes": ["read", "write"],
      "revoked": false,
      "expires_at": "2025-11-12 12:00:00",
      "created_at": "2025-10-28 12:00:00"
    }
  ]
}
```

#### 6) Obtener Información de un Token Específico

- Path: `GET /api/passport/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "token": {
    "id": 10,
    "client_id": 1,
    "scopes": ["read", "write"],
    "revoked": false,
    "expires_at": "2025-11-12 12:00:00",
    "created_at": "2025-10-28 12:00:00",
    "updated_at": "2025-10-28 12:00:00"
  }
}
```

#### 7) Revocar Token Actual

- Path: `POST /api/passport/revoke`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Token revocado exitosamente."
}
```

#### 8) Revocar Token por ID

- Path: `DELETE /api/passport/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Token revocado exitosamente."
}
```

#### 9) Revocar Todos los Tokens

- Path: `POST /api/passport/revoke-all`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Response JSON:

```json
{
  "success": true,
  "message": "Todos los tokens han sido revocados exitosamente."
}
```

#### 10) Revocar Todos Excepto el Actual

- Path: `POST /api/passport/revoke-others`
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

#### 11) Revocar Tokens Expirados (del usuario)

- Path: `POST /api/passport/revoke-expired`
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

#### 12) Revocar Refresh Token

- Path: `POST /api/passport/revoke-refresh-token`
- Autenticación: `Authorization: Bearer {token}`
- Rate Limit: 60 requests por minuto
- Request JSON:

```json
{
  "refresh_token": "def50200abc..."
}
```

- Response JSON:

```json
{
  "success": true,
  "message": "Refresh token revocado exitosamente."
}
```

---

## OAuth2 Scopes

Passport permite asignar scopes específicos a los tokens para controlar qué acciones pueden realizar.

### Crear Token con Scopes

Al crear un token, puedes especificar los scopes:

```
grant_type=password
client_id=1
client_secret=xyz
username=usuario@empresa.com
password=password123
scope=read write
```

### Verificar Scopes en Controladores

En tus controladores, puedes verificar si el token tiene un scope específico:

```php
if ($request->user()->tokenCan('write')) {
    // Usuario tiene permiso de escritura
}
```

### Proteger Rutas con Middleware de Scopes

Puedes proteger rutas usando el middleware de scopes:

```php
// Requiere todos los scopes especificados
Route::get('/data', [DataController::class, 'index'])
    ->middleware(['auth:api', 'scopes:read,write']);

// Requiere al menos uno de los scopes
Route::post('/data', [DataController::class, 'store'])
    ->middleware(['auth:api', 'scope:write,admin']);
```

### Scopes Especiales

- `*`: Token con todos los scopes (por defecto si no se especifica scope)
- `read`: Token solo con permiso de lectura
- `write`: Token con permiso de escritura
- `admin`: Token con permisos de administrador

---

## Refresh Tokens

Passport incluye refresh tokens que permiten renovar el token de acceso sin volver a autenticarte.

### Cómo funciona

1. Al obtener un token, recibes un `refresh_token`
2. Cuando el `token` expire, usa el `refresh_token` para obtener uno nuevo
3. El `refresh_token` también puede expirar (por defecto: 30 días)
4. Si el `refresh_token` expira, debes volver a autenticarte

### Ejemplo de Uso

```bash
# Obtener token inicial
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=1&client_secret=xyz&username=user@example.com&password=pass"

# Respuesta incluye refresh_token
{
  "token": "eyJ0eXAi...",
  "refresh_token": "def50200abc...",
  "expires_in": 1296000
}

# Refrescar token cuando expire
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token&refresh_token=def50200abc...&client_id=1&client_secret=xyz"
```

---

## Errores comunes

- **400**: Parámetros faltantes o `grant_type` incorrecto
- **401**: Credenciales incorrectas (`client_id`, `client_secret` o usuario/password inválidos)
- **401**: Token inválido o expirado (necesitas refrescarlo o obtener uno nuevo)
- **403**: Token no tiene el scope requerido
- **404**: Token no encontrado
- **429**: Demasiadas solicitudes (rate limit excedido)

---

## Resumen

- **Tipo de autenticación**: OAuth2 (Bearer Token)
- **Formato del token**: JWT (JSON Web Token)
- **Cómo obtenerlo**: `client_id` + `client_secret` + `username` + `password` + `scope` (opcional)
- **Cómo usarlo**: `Authorization: Bearer {token}` en headers
- **Ventaja**: Incluye `refresh_token` para renovar sin volver a autenticarte
- **Registro**: No hay auto-registro. Los administradores crean clientes OAuth y usuarios que luego pueden obtener tokens mediante los endpoints de la API.
- **Rate Limiting**: Implementado para proteger contra abusos
- **Token Scopes**: Soporte completo para control granular de permisos
- **Expiración de Tokens**: Tokens de acceso expiran en 15 días, refresh tokens en 30 días

---

## 🧹 Mantenimiento automático

- Limpieza de tokens expirados: Los tokens expirados se pueden revocar manualmente con los endpoints de administración.
- Los refresh tokens expirados se eliminan automáticamente cuando se intenta usarlos.

---

## 🔒 Seguridad

- **Rate Limiting**: Todos los endpoints tienen límites de tasa configurados para prevenir abusos
- **JWT Tokens**: Tokens firmados criptográficamente que no pueden ser modificados
- **Client Secret**: Debe mantenerse seguro y nunca exponerse en el frontend
- **Refresh Tokens**: Almacenados de forma segura y pueden ser revocados individualmente
- **Token Scopes**: Control granular de permisos por token
- **Expiración Automática**: Los tokens expiran automáticamente para mayor seguridad

---

## 📝 Notas importantes

- **Client ID y Client Secret**: Son credenciales sensibles. El `client_secret` solo se muestra una vez al crear el cliente.
- **Refresh Tokens**: Deben almacenarse de forma segura. Si se comprometen, pueden usarse para obtener nuevos tokens.
- **Scopes**: Siempre solicita solo los scopes que realmente necesitas (principio de menor privilegio).
- **HTTPS**: Siempre usa HTTPS en producción para proteger las credenciales en tránsito.

