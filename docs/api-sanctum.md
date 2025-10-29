## API Interna - Sanctum

API para uso interno dentro de la empresa. Autenticación simple con tokens personales.

### Cómo funciona

1. **Obtienes un token** enviando email y contraseña
2. **Usas el token** en todas las solicitudes agregando `Authorization: Bearer {token}` en los headers
3. **El token funciona** hasta que lo revoques o expires

### ¿Qué credenciales necesito?

- **Email**: Correo del usuario interno
- **Password**: Contraseña del usuario
- **Device Name**: Nombre que identifica tu aplicación/servicio (ej: "intranet", "backoffice")

**No necesitas** `client_id` ni `client_secret`.

### Cómo usar el token

Después de obtener el token, úsalo así en todas las solicitudes:

```
Authorization: Bearer {tu_token_aqui}
```

### Endpoints

#### 1. Obtener Token

**POST** `/api/sanctum/token`

**Body (JSON)**:

```json
{
    "email": "usuario@empresa.com",
    "password": "tu_password",
    "device_name": "nombre-de-tu-app"
}
```

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
    }
}
```

**Ejemplo curl**:

```bash
curl -X POST http://localhost:8000/api/sanctum/token \
  -H "Content-Type: application/json" \
  -d '{
    "email": "usuario@empresa.com",
    "password": "tu_password",
    "device_name": "intranet"
  }'
```

#### 2. Obtener Usuario Actual

**GET** `/api/sanctum/user`

**Headers**:

```
Authorization: Bearer {tu_token}
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
curl -X GET http://localhost:8000/api/sanctum/user \
  -H "Authorization: Bearer {tu_token}"
```

#### 3. Revocar Token Actual

**POST** `/api/sanctum/revoke`

**Headers**:

```
Authorization: Bearer {tu_token}
```

**Respuesta**:

```json
{
    "success": true,
    "message": "Token revocado exitosamente."
}
```

#### 4. Revocar Todos los Tokens

**POST** `/api/sanctum/revoke-all`

**Headers**:

```
Authorization: Bearer {tu_token}
```

**Respuesta**:

```json
{
    "success": true,
    "message": "Todos los tokens han sido revocados exitosamente."
}
```

### Errores comunes

- **422**: Credenciales incorrectas o campos faltantes
- **401**: Token inválido o expirado

### Resumen

- **Tipo de autenticación**: Bearer Token
- **Formato del token**: Texto plano simple
- **Cómo obtenerlo**: Email + Password + Device Name
- **Cómo usarlo**: `Authorization: Bearer {token}` en headers
