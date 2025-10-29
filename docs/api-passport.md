## API Externa - Passport (OAuth2)

API para empresas externas que se conectan a nuestro sistema. Usa OAuth2 para autenticación segura.

---

## 👨‍💼 Para Administradores

**Requisito**: Debes tener el permiso `api.passport` para acceder al CRUD de gestión de clientes OAuth.

### Cómo crear credenciales para una empresa externa

1. **Ir al CRUD de Clientes OAuth** en el panel administrativo
    - Solo visible si tienes el permiso `api.passport`

2. **Crear un nuevo cliente OAuth** para la empresa externa:
    - Nombre del cliente/empresa
    - URL de redirección (si aplica)
    - El sistema generará automáticamente:
        - `client_id`: Identificador único del cliente
        - `client_secret`: Secreto del cliente (mostrado una vez, guárdalo)

3. **Crear o asignar un usuario** para ese cliente:
    - Desde el CRUD de Usuarios, crear un nuevo usuario
    - O asignar un usuario existente al cliente OAuth

4. **Compartir las credenciales** con la empresa externa:
    - `client_id` del cliente OAuth creado
    - `client_secret` del cliente OAuth
    - Email del usuario asignado
    - Contraseña del usuario asignado

**Nota**: La empresa externa ya puede usar estas credenciales para obtener tokens. Los clientes OAuth quedan asociados al administrador que los creó y al usuario asignado.

---

## 🏢 Para Consumidores (Empresas Externas)

### Credenciales que necesitas

El administrador te habrá proporcionado:

- **Client ID**: Identificador de tu cliente OAuth
- **Client Secret**: Secreto del cliente (mantén esto seguro)
- **Username**: Email del usuario asignado a tu cliente
- **Password**: Contraseña del usuario asignado

**Importante**: Las empresas externas NO se registran. Un administrador con permiso `api.passport` crea tu cliente OAuth y usuario desde el CRUD.

### Cómo usar la API

#### Paso 1: Obtener tu token de acceso

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

**Ejemplo curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=12345&client_secret=abc123&username=usuario@empresa.com&password=tu_password&scope="
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

**Guarda estos valores**:

- `token`: Tu token de acceso (lo usarás en cada solicitud)
- `refresh_token`: Para renovar el token cuando expire

#### Paso 2: Usar el token en tus solicitudes

Agrega el header `Authorization` en todas las solicitudes protegidas:

```
Authorization: Bearer {access_token}
```

#### Paso 3: Consumir endpoints protegidos

**Ejemplo - Obtener usuario actual**:

```bash
curl -X GET http://localhost:8000/api/passport/user \
  -H "Authorization: Bearer {access_token}"
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

Cuando tu `access_token` expire, usa el `refresh_token` para obtener uno nuevo sin volver a autenticarte:

**POST** `/api/passport/token`

**Body (form-urlencoded)**:

```
grant_type=refresh_token
refresh_token={TU_REFRESH_TOKEN}
client_id={TU_CLIENT_ID}
client_secret={TU_CLIENT_SECRET}
scope=
```

**Ejemplo curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token&refresh_token={REFRESH_TOKEN}&client_id=12345&client_secret=abc123&scope="
```

**Respuesta**: Igual que obtener token, pero con nuevos valores de `token` y `refresh_token`.

### Todos los endpoints disponibles

#### 1. Obtener Token

**POST** `/api/passport/token`  
Obtiene un nuevo token de acceso (usar `grant_type=password`)

#### 2. Refrescar Token

**POST** `/api/passport/token`  
Renueva tu token cuando expire (usar `grant_type=refresh_token`)

#### 3. Obtener Usuario Actual

**GET** `/api/passport/user`  
Requiere autenticación: `Authorization: Bearer {access_token}`

#### 4. Revocar Token Actual

**POST** `/api/passport/revoke`  
Requiere autenticación: `Authorization: Bearer {access_token}`

#### 5. Revocar Todos los Tokens

**POST** `/api/passport/revoke-all`  
Requiere autenticación: `Authorization: Bearer {access_token}`

### Errores comunes

- **401**: Credenciales incorrectas (`client_id`, `client_secret` o usuario/password inválidos)
- **400**: Parámetros faltantes o `grant_type` incorrecto
- **401**: Token inválido o expirado (necesitas refrescarlo o obtener uno nuevo)

### Resumen

- **Tipo de autenticación**: OAuth2 (Bearer Token)
- **Formato del token**: JWT (JSON Web Token)
- **Cómo obtenerlo**: `client_id` + `client_secret` + email + password
- **Cómo usarlo**: `Authorization: Bearer {access_token}` en headers
- **Ventaja**: Incluye `refresh_token` para renovar sin volver a autenticarte
- **Registro**: No hay auto-registro. Solo administradores con permiso `api.passport` pueden crear clientes OAuth.
