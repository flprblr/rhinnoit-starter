## API Externa - Passport (OAuth2)

API para empresas externas que se conectan a nuestro sistema. Usa OAuth2 para autenticación segura.

### Cómo funciona

1. **La empresa externa se registra** y recibe `client_id` y `client_secret`
2. **Obtiene un token** usando esos credenciales + email/password de un usuario
3. **Usa el token** en todas las solicitudes con `Authorization: Bearer {token}`
4. **Puede refrescar el token** cuando expire usando el `refresh_token`

### ¿Qué credenciales necesito?

- **Client ID**: Se asigna cuando la empresa se registra
- **Client Secret**: Secreto que se comparte con la empresa (debe mantenerse seguro)
- **Username**: Email del usuario
- **Password**: Contraseña del usuario

**Nota**: Primero debes registrarte como empresa externa para obtener tu `client_id` y `client_secret`.

### Preparación inicial

El administrador del sistema debe ejecutar:

```bash
php artisan passport:install
php artisan passport:client --password
```

Esto genera las credenciales que se comparten con la empresa externa:

- `client_id`: Ejemplo: `12345`
- `client_secret`: Ejemplo: `abc123xyz789secret`

### Cómo usar el token

Después de obtener el token, úsalo así en todas las solicitudes:

```
Authorization: Bearer {access_token}
```

### Endpoints

#### 1. Obtener Token

**POST** `/api/passport/token`

**Body (form-urlencoded)**:

```
grant_type=password
client_id={TU_CLIENT_ID}
client_secret={TU_CLIENT_SECRET}
username=usuario@empresa.com
password=tu_password
scope=
```

**Respuesta**:

```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 31536000,
    "refresh_token": "def50200abc..."
}
```

**Ejemplo curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=12345&client_secret=abc123&username=usuario@empresa.com&password=tu_password&scope="
```

#### 2. Refrescar Token (cuando expire)

**POST** `/api/passport/token`

**Body (form-urlencoded)**:

```
grant_type=refresh_token
refresh_token={TU_REFRESH_TOKEN}
client_id={TU_CLIENT_ID}
client_secret={TU_CLIENT_SECRET}
scope=
```

**Respuesta** (igual que obtener token, pero con nuevos tokens):

```json
{
    "success": true,
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "expires_in": 31536000,
    "refresh_token": "def50200xyz..."
}
```

**Ejemplo curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token&refresh_token={REFRESH_TOKEN}&client_id=12345&client_secret=abc123&scope="
```

#### 3. Obtener Usuario Actual

**GET** `/api/passport/user`

**Headers**:

```
Authorization: Bearer {access_token}
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

**Ejemplo curl**:

```bash
curl -X GET http://localhost:8000/api/passport/user \
  -H "Authorization: Bearer {access_token}"
```

#### 4. Revocar Token Actual

**POST** `/api/passport/revoke`

**Headers**:

```
Authorization: Bearer {access_token}
```

**Respuesta**:

```json
{
    "success": true,
    "message": "Token revocado exitosamente."
}
```

#### 5. Revocar Todos los Tokens

**POST** `/api/passport/revoke-all`

**Headers**:

```
Authorization: Bearer {access_token}
```

**Respuesta**:

```json
{
    "success": true,
    "message": "Todos los tokens han sido revocados exitosamente."
}
```

### Errores comunes

- **401**: Credenciales incorrectas (`client_id`, `client_secret` o usuario/password inválidos)
- **400**: Parámetros faltantes o `grant_type` incorrecto
- **401**: Token inválido o expirado (necesitas refrescarlo o obtener uno nuevo)

### Resumen

- **Tipo de autenticación**: OAuth2 (Bearer Token)
- **Formato del token**: JWT (JSON Web Token)
- **Cómo obtenerlo**: `client_id` + `client_secret` + email + password
- **Cómo usarlo**: `Authorization: Bearer {access_token}` en headers
- **Ventaja**: Incluye `refresh_token` para renovar sin volver a autenticarse
