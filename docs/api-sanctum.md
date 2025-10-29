## API Interna - Sanctum

API para uso interno dentro de la empresa. Autenticación simple con tokens personales.

---

## 👨‍💼 Para Administradores

**Requisito**: Debes tener el permiso `api.sanctum` para acceder al CRUD de gestión de tokens.

### Cómo crear credenciales para un usuario interno

1. **Ir al CRUD de API Sanctum** en el panel administrativo
    - Solo visible si tienes el permiso `api.sanctum`

2. **Crear credenciales para un usuario**:
    - Seleccionar un usuario existente (o crear uno nuevo)
    - Generar un token con un nombre/identificador (ej: "intranet-backoffice", "servicio-interno-1")
    - El token quedará asociado a ese usuario

3. **Compartir las credenciales** con el usuario interno:
    - Email del usuario
    - Contraseña del usuario (la que tienes configurada)
    - Token generado (o indicar que usen su email/password para generar tokens)

**Nota**: El usuario interno puede usar su email/password para obtener tokens, o puedes generar tokens predefinidos desde el CRUD y compartirlos directamente.

---

## 👤 Para Consumidores (Usuarios Internos)

### Credenciales que necesitas

El administrador te habrá proporcionado:

- **Email**: Tu correo electrónico del usuario creado
- **Password**: Tu contraseña asignada
- **Device Name**: Un nombre único para tu aplicación/servicio (ej: "intranet", "backoffice")

**Importante**: Los usuarios NO se registran. Un administrador con permiso `api.sanctum` crea tu usuario y credenciales desde el CRUD.

### Cómo usar la API

#### Paso 1: Obtener tu token

**POST** `/api/sanctum/token`

**Body (JSON)**:

```json
{
    "email": "tu_email@empresa.com",
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

### Todos los endpoints disponibles

#### 1) Obtener Token

- Path: `POST /api/sanctum/token`
- Autenticación: No requiere
- Response JSON:

```json
{
    "success": true,
    "token": "1|abcdef123456...",
    "token_type": "Bearer",
    "user": { "id": 1, "name": "Juan Pérez", "email": "usuario@empresa.com" }
}
```

#### 2) Obtener Usuario Actual

- Path: `GET /api/sanctum/user`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{
    "success": true,
    "user": { "id": 1, "name": "Juan Pérez", "email": "usuario@empresa.com" }
}
```

#### 3) Revocar Token Actual

- Path: `POST /api/sanctum/revoke`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "Token revocado exitosamente." }
```

#### 4) Revocar Todos los Tokens

- Path: `POST /api/sanctum/revoke-all`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "Todos los tokens han sido revocados exitosamente." }
```

#### 5) Listar Tokens del Usuario

- Path: `GET /api/sanctum/tokens`
- Autenticación: `Authorization: Bearer {token}`
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
            "expires_at": "2025-11-28 12:00:00",
            "created_at": "2025-10-28 12:00:00"
        }
    ]
}
```

#### 6) Revocar Token por ID

- Path: `DELETE /api/sanctum/tokens/{id}`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "Token revocado exitosamente." }
```

#### 7) Revocar Tokens por Nombre

- Path: `POST /api/sanctum/tokens/revoke-by-name`
- Autenticación: `Authorization: Bearer {token}`
- Request JSON:

```json
{ "name": "intranet" }
```

- Response JSON:

```json
{ "success": true, "deleted": 2, "message": "Tokens revocados exitosamente." }
```

#### 8) Revocar Todos Excepto el Actual

- Path: `POST /api/sanctum/tokens/revoke-others`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "deleted": 3, "message": "Se han revocado los demás tokens." }
```

#### 9) Revocar Tokens Expirados (del usuario)

- Path: `POST /api/sanctum/tokens/revoke-expired`
- Autenticación: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "deleted": 1, "message": "Tokens expirados revocados." }
```

### Errores comunes

- **422**: Credenciales incorrectas o campos faltantes
- **401**: Token inválido o expirado

### Resumen

- **Tipo de autenticación**: Bearer Token
- **Formato del token**: Texto plano simple
- **Cómo obtenerlo**: Email + Password + Device Name
- **Cómo usarlo**: `Authorization: Bearer {token}` en headers
- **Registro**: No hay auto-registro. Solo administradores con permiso `api.sanctum` pueden crear credenciales.

---

## 🧹 Mantenimiento automático

- Limpieza de tokens expirados programada: `sanctum:prune-expired` (scheduler cada hora).
- Los tokens sin `expires_at` no se eliminan automáticamente; revócalos con los endpoints de administración.
