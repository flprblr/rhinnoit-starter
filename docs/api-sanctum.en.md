## API Documentation - Sanctum

---

## Administrators

### How to create credentials for a user

1. **Create a user** in the system with email and password

2. **Share the credentials** with the user:
   - User email
   - User password
   - Device Name: A unique name for their application/service (e.g., "intranet-backoffice", "internal-service-1")

**Note**: The user can use their email/password to obtain tokens through the `/api/sanctum/token` endpoint.

---

## Consumers

### Credentials you need

The administrator will provide you:

- **Email**: Your user email
- **Password**: Your assigned password
- **Device Name**: A unique name for your application/service (e.g., "intranet", "backoffice")

**Important**: Users do not self-register. An administrator creates users who can then obtain tokens through the API endpoints.

### How to use the API

#### Step 1: Get your token

**POST** `/api/sanctum/token`

**Body (JSON)**:

```json
{
  "email": "your_email@company.com",
  "password": "your_password",
  "device_name": "your-app-name",
  "abilities": ["read", "write"],
  "expires_at": "2025-12-31T23:59:59"
}
```

**Optional fields**:
- `abilities`: Array of token abilities (default: `["*"]` for all abilities)
- `expires_at`: Token expiration date in ISO 8601 format (e.g., "2025-12-31T23:59:59")

**Response**:

```json
{
  "success": true,
  "token": "1|abcdef123456...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@company.com"
  },
  "token_info": {
    "name": "your-app-name",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31T23:59:59"
  }
}
```

#### Step 2: Use the token in your requests

Add the `Authorization` header for all protected requests:

```
Authorization: Bearer {your_token}
```

#### Step 3: Use protected endpoints

**Response - Get current user**:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@company.com"
  }
}
```

---

## SPA Authentication (Single Page Application)

If you are developing a SPA that runs on the same domain as the API, you can use session-based authentication:

### Step 1: Get CSRF cookie

**GET** `/api/sanctum/csrf-cookie`

**Response**:

```json
{
  "success": true,
  "message": "CSRF cookie set successfully."
}
```

### Step 2: Login

**POST** `/api/sanctum/login`

**Body (JSON)**:

```json
{
  "email": "your_email@company.com",
  "password": "your_password"
}
```

**Response**:

```json
{
  "success": true,
  "message": "Authentication successful.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@company.com"
  }
}
```

### Step 3: Logout

**POST** `/api/sanctum/logout`

**Response**:

```json
{
  "success": true,
  "message": "Session closed successfully."
}
```

---

## All available endpoints

### Token Authentication

#### 1) Obtain Token

- Path: `POST /api/sanctum/token`
- Authentication: Not required
- Rate Limit: 5 requests per minute
- Request JSON:

```json
{
  "email": "user@company.com",
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
    "name": "John Doe",
    "email": "user@company.com"
  },
  "token_info": {
    "name": "intranet",
    "abilities": ["read", "write"],
    "expires_at": "2025-12-31T23:59:59"
  }
}
```

#### 2) Get Current User

- Path: `GET /api/sanctum/user`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@company.com"
  }
}
```

#### 3) Verify Token

- Path: `GET /api/sanctum/verify`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 4) Revoke Current Token

- Path: `POST /api/sanctum/revoke`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "Token revoked successfully."
}
```

#### 5) Revoke All Tokens

- Path: `POST /api/sanctum/revoke-all`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "All tokens have been revoked successfully."
}
```

#### 6) List User Tokens

- Path: `GET /api/sanctum/tokens`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 7) Get Token Information

- Path: `GET /api/sanctum/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 8) Update Token

- Path: `PATCH /api/sanctum/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Request JSON:

```json
{
  "name": "new-name",
  "abilities": ["read"],
  "expires_at": "2026-12-31T23:59:59"
}
```

- Response JSON:

```json
{
  "success": true,
  "message": "Token updated successfully.",
  "token": {
    "id": 10,
    "name": "new-name",
    "abilities": ["read"],
    "expires_at": "2026-12-31T23:59:59",
    "last_used_at": "2025-10-29 13:45:00",
    "created_at": "2025-10-28 12:00:00",
    "updated_at": "2025-10-29 14:00:00"
  }
}
```

#### 9) Revoke Token by ID

- Path: `DELETE /api/sanctum/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "Token revoked successfully."
}
```

#### 10) Revoke Tokens by Name

- Path: `POST /api/sanctum/tokens/revoke-by-name`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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
  "message": "Tokens revoked successfully."
}
```

#### 11) Revoke All Except Current

- Path: `POST /api/sanctum/tokens/revoke-others`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "deleted": 3,
  "message": "Other tokens have been revoked."
}
```

#### 12) Revoke Expired Tokens (for the user)

- Path: `POST /api/sanctum/tokens/revoke-expired`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "deleted": 1,
  "message": "Expired tokens revoked."
}
```

### SPA Authentication (Single Page Application)

#### 13) Get CSRF Cookie

- Path: `GET /api/sanctum/csrf-cookie`
- Authentication: Not required
- Rate Limit: 10 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "CSRF cookie set successfully."
}
```

#### 14) Login (SPA)

- Path: `POST /api/sanctum/login`
- Authentication: Not required (uses web session)
- Rate Limit: 5 requests per minute
- Request JSON:

```json
{
  "email": "user@company.com",
  "password": "password123"
}
```

- Response JSON:

```json
{
  "success": true,
  "message": "Authentication successful.",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@company.com"
  }
}
```

#### 15) Logout (SPA)

- Path: `POST /api/sanctum/logout`
- Authentication: Requires active web session
- Response JSON:

```json
{
  "success": true,
  "message": "Session closed successfully."
}
```

---

## Token Abilities

Sanctum allows you to assign specific abilities to tokens to control what actions they can perform.

### Create Token with Abilities

When creating a token, you can specify the abilities:

```json
{
  "email": "user@company.com",
  "password": "password123",
  "device_name": "intranet",
  "abilities": ["read", "write"]
}
```

### Verify Abilities in Controllers

In your controllers, you can verify if the token has a specific ability:

```php
if ($request->user()->tokenCan('write')) {
    // User has write permission
}
```

### Protect Routes with Ability Middleware

You can protect routes using the ability middleware:

```php
// Requires all specified abilities
Route::get('/data', [DataController::class, 'index'])
    ->middleware(['auth:sanctum', 'abilities:read,write']);

// Requires at least one of the abilities
Route::post('/data', [DataController::class, 'store'])
    ->middleware(['auth:sanctum', 'ability:write,admin']);
```

### Special Abilities

- `["*"]`: Token with all abilities (default)
- `["read"]`: Token with read permission only
- `["write"]`: Token with write permission
- `["admin"]`: Token with admin permissions

---

## Common errors

- **422**: Invalid credentials or missing fields
- **401**: Invalid or expired token
- **403**: Token does not have the required ability
- **404**: Token not found
- **429**: Too many requests (rate limit exceeded)

---

## Summary

- **Authentication type**: Bearer Token or Web Session (for SPAs)
- **Token format**: Plain text
- **How to obtain**: Email + Password + Device Name
- **How to use**: `Authorization: Bearer {token}` header (for tokens) or session cookies (for SPAs)
- **Registration**: No self-signup. Administrators create users who can then obtain tokens through the API endpoints.
- **Rate Limiting**: Implemented to protect against abuse
- **Token Abilities**: Full support for granular permission control
- **Token Expiration**: Support for tokens with expiration date

---

## 🧹 Automatic maintenance

- Scheduled cleanup of expired tokens: `sanctum:prune-expired` (hourly scheduler).
- Tokens without `expires_at` are not removed automatically; revoke them via the admin endpoints.

---

## 🔒 Security

- **Rate Limiting**: All endpoints have rate limits configured to prevent abuse
- **Token Prefix**: Configurable in `.env` with `SANCTUM_TOKEN_PREFIX` to improve security
- **Expiration Validation**: Tokens with `expires_at` are automatically validated
- **Token Abilities**: Granular permission control per token
