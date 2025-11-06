## API Documentation - Passport

---

## Administrators

### How to create credentials for an external user

1. **Create a user** in the system with email and password

2. **Create an OAuth client** (requires Passport configuration):
   - Run `php artisan passport:client` to create an OAuth client
   - Or use the programmatic method: `Passport::client()->create()`
   - The system will automatically generate:
     - `client_id`: Unique client identifier
     - `client_secret`: Client secret (shown once, save it)

3. **Share the credentials** with the external user:
   - `client_id` of the created OAuth client
   - `client_secret` of the OAuth client
   - Email of the assigned user
   - Password of the assigned user

**Note**: The external user can use these credentials to obtain tokens through the `/api/passport/token` endpoint using the OAuth2 "Password Grant" flow.

---

## Consumers

### Credentials you need

The administrator will provide you:

- **Client ID**: Your OAuth client identifier
- **Client Secret**: Client secret (keep this secure)
- **Username**: Email of the user assigned to your client
- **Password**: Password of the assigned user
- **Scopes** (optional): Specific permissions you need (e.g., "read", "write")

**Important**: External users do NOT self-register. An administrator creates your OAuth client and user who can then obtain tokens through the API endpoints.

### How to use the API

#### Step 1: Get your access token

**POST** `/api/passport/token`

**Headers**:
```
Content-Type: application/x-www-form-urlencoded
Accept: application/json
```

**Body (form-urlencoded)**:

```
grant_type=password
client_id={YOUR_CLIENT_ID}
client_secret={YOUR_CLIENT_SECRET}
username=user@company.com
password=your_password
scope=read write
```

**Optional fields**:
- `scope`: String with scopes separated by spaces (e.g., "read write admin"). If not specified, all available scopes are granted.

**Example with curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "grant_type=password&client_id=1&client_secret=xyz&username=user@company.com&password=password123&scope=read write"
```

**Response**:

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

**Save these values**:
- `token`: Your access token (you'll use it in every request)
- `refresh_token`: To renew the token when it expires
- `expires_in`: Seconds until the token expires (default: 15 days)

#### Step 2: Use the token in your requests

Add the `Authorization` header to all protected requests:

```
Authorization: Bearer {your_token}
```

#### Step 3: Use protected endpoints

**Example - Get current user**:

```bash
curl -X GET http://localhost:8000/api/passport/user \
  -H "Authorization: Bearer {your_token}"
```

**Response**:

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

#### Step 4: Refresh the token (when it expires)

When your `token` expires, use the `refresh_token` to get a new one without re-authenticating:

**POST** `/api/passport/token`

**Body (form-urlencoded)**:

```
grant_type=refresh_token
refresh_token={YOUR_REFRESH_TOKEN}
client_id={YOUR_CLIENT_ID}
client_secret={YOUR_CLIENT_SECRET}
scope=read write
```

**Example with curl**:

```bash
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -H "Accept: application/json" \
  -d "grant_type=refresh_token&refresh_token={REFRESH_TOKEN}&client_id=1&client_secret=xyz&scope=read write"
```

**Response**: Same as getting a token, but with new `token` and `refresh_token` values.

---

## All available endpoints

### Token Authentication

#### 1) Obtain Token (Password Grant)

- Path: `POST /api/passport/token`
- Authentication: Not required
- Rate Limit: 5 requests per minute
- Content-Type: `application/x-www-form-urlencoded`
- Request Body (form-urlencoded):

```
grant_type=password
client_id=1
client_secret=xyz
username=user@company.com
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

#### 2) Refresh Token (Refresh Token Grant)

- Path: `POST /api/passport/token`
- Authentication: Not required
- Rate Limit: 5 requests per minute
- Content-Type: `application/x-www-form-urlencoded`
- Request Body (form-urlencoded):

```
grant_type=refresh_token
refresh_token=def50200abc...
client_id=1
client_secret=xyz
scope=read write
```

- Response JSON: Same as getting a token, with new values

#### 3) Get Current User

- Path: `GET /api/passport/user`
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

#### 4) Verify Token

- Path: `GET /api/passport/verify`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 5) List User Tokens

- Path: `GET /api/passport/tokens`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 6) Get Token Information

- Path: `GET /api/passport/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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

#### 7) Revoke Current Token

- Path: `POST /api/passport/revoke`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "Token revoked successfully."
}
```

#### 8) Revoke Token by ID

- Path: `DELETE /api/passport/tokens/{id}`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "Token revoked successfully."
}
```

#### 9) Revoke All Tokens

- Path: `POST /api/passport/revoke-all`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
- Response JSON:

```json
{
  "success": true,
  "message": "All tokens have been revoked successfully."
}
```

#### 10) Revoke All Except Current

- Path: `POST /api/passport/revoke-others`
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

#### 11) Revoke Expired Tokens (for the user)

- Path: `POST /api/passport/revoke-expired`
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

#### 12) Revoke Refresh Token

- Path: `POST /api/passport/revoke-refresh-token`
- Authentication: `Authorization: Bearer {token}`
- Rate Limit: 60 requests per minute
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
  "message": "Refresh token revoked successfully."
}
```

---

## OAuth2 Scopes

Passport allows you to assign specific scopes to tokens to control what actions they can perform.

### Create Token with Scopes

When creating a token, you can specify the scopes:

```
grant_type=password
client_id=1
client_secret=xyz
username=user@company.com
password=password123
scope=read write
```

### Verify Scopes in Controllers

In your controllers, you can verify if the token has a specific scope:

```php
if ($request->user()->tokenCan('write')) {
    // User has write permission
}
```

### Protect Routes with Scope Middleware

You can protect routes using the scope middleware:

```php
// Requires all specified scopes
Route::get('/data', [DataController::class, 'index'])
    ->middleware(['auth:api', 'scopes:read,write']);

// Requires at least one of the scopes
Route::post('/data', [DataController::class, 'store'])
    ->middleware(['auth:api', 'scope:write,admin']);
```

### Special Scopes

- `*`: Token with all scopes (default if no scope is specified)
- `read`: Token with read permission only
- `write`: Token with write permission
- `admin`: Token with admin permissions

---

## Refresh Tokens

Passport includes refresh tokens that allow you to renew the access token without re-authenticating.

### How it works

1. When obtaining a token, you receive a `refresh_token`
2. When the `token` expires, use the `refresh_token` to get a new one
3. The `refresh_token` can also expire (default: 30 days)
4. If the `refresh_token` expires, you must re-authenticate

### Usage Example

```bash
# Get initial token
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=password&client_id=1&client_secret=xyz&username=user@example.com&password=pass"

# Response includes refresh_token
{
  "token": "eyJ0eXAi...",
  "refresh_token": "def50200abc...",
  "expires_in": 1296000
}

# Refresh token when it expires
curl -X POST http://localhost:8000/api/passport/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=refresh_token&refresh_token=def50200abc...&client_id=1&client_secret=xyz"
```

---

## Common errors

- **400**: Missing parameters or incorrect `grant_type`
- **401**: Invalid credentials (`client_id`, `client_secret` or invalid username/password)
- **401**: Invalid or expired token (you need to refresh it or get a new one)
- **403**: Token does not have the required scope
- **404**: Token not found
- **429**: Too many requests (rate limit exceeded)

---

## Summary

- **Authentication type**: OAuth2 (Bearer Token)
- **Token format**: JWT (JSON Web Token)
- **How to obtain**: `client_id` + `client_secret` + `username` + `password` + `scope` (optional)
- **How to use**: `Authorization: Bearer {token}` header
- **Advantage**: Includes `refresh_token` to renew without re-authenticating
- **Registration**: No self-signup. Administrators create OAuth clients and users who can then obtain tokens through the API endpoints.
- **Rate Limiting**: Implemented to protect against abuse
- **Token Scopes**: Full support for granular permission control
- **Token Expiration**: Access tokens expire in 15 days, refresh tokens in 30 days

---

## 🧹 Automatic maintenance

- Expired token cleanup: Expired tokens can be manually revoked using the admin endpoints.
- Expired refresh tokens are automatically removed when attempting to use them.

---

## 🔒 Security

- **Rate Limiting**: All endpoints have rate limits configured to prevent abuse
- **JWT Tokens**: Cryptographically signed tokens that cannot be modified
- **Client Secret**: Must be kept secure and never exposed in the frontend
- **Refresh Tokens**: Stored securely and can be revoked individually
- **Token Scopes**: Granular permission control per token
- **Automatic Expiration**: Tokens expire automatically for enhanced security

---

## 📝 Important notes

- **Client ID and Client Secret**: These are sensitive credentials. The `client_secret` is only shown once when creating the client.
- **Refresh Tokens**: Must be stored securely. If compromised, they can be used to obtain new tokens.
- **Scopes**: Always request only the scopes you actually need (principle of least privilege).
- **HTTPS**: Always use HTTPS in production to protect credentials in transit.

