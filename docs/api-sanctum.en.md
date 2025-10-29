## API Documentation - Sanctum

---

## Administrators

**Requirement**: You must have the `api.sanctum` permission to access the token management CRUD.

### How to create credentials for a user

1. **Go to the Sanctum API CRUD** in the admin panel
   - Visible only if you have the `api.sanctum` permission

2. **Create credentials for a user**:
   - Select an existing user (or create a new one)
   - Generate a token with a name/identifier (e.g., "intranet-backoffice", "internal-service-1")
   - The token will be associated with that user

3. **Share the credentials** with the user:
   - User email
   - User password (the one you configured)
   - Generated token (or instruct them to use email/password to generate tokens)

**Note**: The internal user can use email/password to obtain tokens, or you can generate predefined tokens from the CRUD and share them directly.

---

## Consumers

### Credentials you need

The administrator will provide you:

- **Email**: Your user email
- **Password**: Your assigned password
- **Device Name**: A unique name for your app/service (e.g., "intranet", "backoffice")

**Important**: Users do not self-register. An administrator with `api.sanctum` permission issues credentials.

### How to use the API

#### Step 1: Get your token

**POST** `/api/sanctum/token`

**Body (JSON)**:

```json
{ "email": "your_email@company.com", "password": "your_password", "device_name": "your-app-name" }
```

**Response**:

```json
{ "success": true, "token": "1|abcdef123456...", "token_type": "Bearer", "user": { "id": 1, "name": "John Doe", "email": "user@company.com" } }
```

#### Step 2: Use the token in your requests

Add the `Authorization` header for all protected requests:

```
Authorization: Bearer {your_token}
```

#### Step 3: Use protected endpoints

**Response - Get current user**:

```json
{ "success": true, "user": { "id": 1, "name": "John Doe", "email": "user@company.com" } }
```

### All available endpoints

#### 1) Obtain Token

- Path: `POST /api/sanctum/token`
- Authentication: Not required
- Response JSON:

```json
{ "success": true, "token": "1|abcdef123456...", "token_type": "Bearer", "user": { "id": 1, "name": "John Doe", "email": "user@company.com" } }
```

#### 2) Get Current User

- Path: `GET /api/sanctum/user`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "user": { "id": 1, "name": "John Doe", "email": "user@company.com" } }
```

#### 3) Revoke Current Token

- Path: `POST /api/sanctum/revoke`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "Token revoked successfully." }
```

#### 4) Revoke All Tokens

- Path: `POST /api/sanctum/revoke-all`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "All tokens have been revoked successfully." }
```

#### 5) List User Tokens

- Path: `GET /api/sanctum/tokens`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "tokens": [ { "id": 10, "name": "intranet", "abilities": ["read", "write"], "last_used_at": "2025-10-29 13:45:00", "expires_at": "2025-11-28 12:00:00", "created_at": "2025-10-28 12:00:00" } ] }
```

#### 6) Revoke Token by ID

- Path: `DELETE /api/sanctum/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "message": "Token revoked successfully." }
```

#### 7) Revoke Tokens by Name

- Path: `POST /api/sanctum/tokens/revoke-by-name`
- Authentication: `Authorization: Bearer {token}`
- Request JSON:

```json
{ "name": "intranet" }
```
- Response JSON:

```json
{ "success": true, "deleted": 2, "message": "Tokens revoked successfully." }
```

#### 8) Revoke All Except Current

- Path: `POST /api/sanctum/tokens/revoke-others`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "deleted": 3, "message": "Other tokens have been revoked." }
```

#### 9) Revoke Expired Tokens (for the user)

- Path: `POST /api/sanctum/tokens/revoke-expired`
- Authentication: `Authorization: Bearer {token}`
- Response JSON:

```json
{ "success": true, "deleted": 1, "message": "Expired tokens revoked." }
```

### Common errors

- **422**: Invalid credentials or missing fields
- **401**: Invalid or expired token

### Summary

- **Authentication type**: Bearer Token
- **Token format**: Plain text
- **How to obtain**: Email + Password + Device Name
- **How to use**: `Authorization: Bearer {token}` header
- **Registration**: No self-signup. Only admins with `api.sanctum` permission can create credentials.

---

## 🧹 Automatic maintenance

- Scheduled cleanup of expired tokens: `sanctum:prune-expired` (hourly scheduler).
- Tokens without `expires_at` are not removed automatically; revoke them via the admin endpoints.


