## API Interna - Sanctum

API para uso interno dentro de la empresa. Autenticación simple con tokens personales.

---

## 👨‍💼 Para Administradores

### Cómo crear credenciales para un usuario interno

1. **Ir al CRUD de Usuarios** en el panel administrativo
2. **Crear un nuevo usuario** con los siguientes datos:
    - Nombre completo
    - Email único
    - Contraseña segura
    - Roles (si aplica)
3. **Compartir las credenciales** con el usuario interno:
    - Email del usuario
    - Contraseña asignada
    - Indicar que deben usar un `device_name` único (ej: "intranet", "backoffice", "servicio-1")

**Nota**: El usuario interno ya puede usar estas credenciales para obtener tokens. No necesitas hacer nada más.

---

## 👤 Para Consumidores (Usuarios Internos)

### Credenciales que necesitas

El administrador te habrá proporcionado:

- **Email**: Tu correo electrónico
- **Password**: Tu contraseña
- **Device Name**: Un nombre único para tu aplicación/servicio (ej: "intranet", "backoffice")

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
