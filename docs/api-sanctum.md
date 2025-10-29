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

**Ejemplo - Obtener usuario actual**:

```bash
curl -X GET http://localhost:8000/api/sanctum/user \
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

### Todos los endpoints disponibles

#### 1. Obtener Token

**POST** `/api/sanctum/token`  
Obtiene un nuevo token de acceso.

#### 2. Obtener Usuario Actual

**GET** `/api/sanctum/user`  
Requiere autenticación: `Authorization: Bearer {token}`

#### 3. Revocar Token Actual

**POST** `/api/sanctum/revoke`  
Requiere autenticación: `Authorization: Bearer {token}`

#### 4. Revocar Todos los Tokens

**POST** `/api/sanctum/revoke-all`  
Requiere autenticación: `Authorization: Bearer {token}`

### Errores comunes

- **422**: Credenciales incorrectas o campos faltantes
- **401**: Token inválido o expirado

### Resumen

- **Tipo de autenticación**: Bearer Token
- **Formato del token**: Texto plano simple
- **Cómo obtenerlo**: Email + Password + Device Name
- **Cómo usarlo**: `Authorization: Bearer {token}` en headers
- **Registro**: No hay auto-registro. Solo administradores con permiso `api.sanctum` pueden crear credenciales.
