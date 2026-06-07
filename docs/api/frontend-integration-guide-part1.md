# TixLoop Frontend Integration Guide — Part 1: Architecture & Auth

## 1. Ringkasan Arsitektur API

| Item | Value |
|------|-------|
| Base URL | `https://<domain>/api/v1` |
| API Version | v1 |
| Auth Method | Laravel Sanctum — Bearer Token |
| Content-Type | `application/json` (default), `multipart/form-data` (file uploads) |
| Accept Header | `application/json` (wajib selalu dikirim) |

### Standard Response

```json
{ "success": true, "message": "...", "data": {} }
```

### Error Response

```json
{ "success": false, "message": "...", "errors": { "field": ["msg"] } }
```

`errors` hanya ada pada HTTP 422.

### Paginated Response

```json
{
  "success": true, "message": "...",
  "data": [...],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

### HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Success (read/update) |
| 201 | Created |
| 401 | Unauthenticated (missing/invalid token) |
| 403 | Forbidden (policy denied) |
| 404 | Not Found |
| 422 | Validation Error / Business Rule Error |
| 429 | Too Many Requests (rate limit) |

---

## 2. Authentication Header

```http
Authorization: Bearer <access_token>
Accept: application/json
```

---

## 3. User Journey — Authentication

### 3.1 Register

**Tujuan**: User membuat akun baru.

**Endpoint**: `POST /api/v1/auth/register`

**Auth**: Tidak diperlukan

**Content-Type**: `application/json`

**Request**:
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "08123456789",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Validation Rules** (dari `RegisterRequest.php`):
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `phone`: required, string, unique:users
- `password`: required, string, min:8, confirmed

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "access_token": "1|abc...",
    "token_type": "Bearer",
    "user": {
      "id": "01JXXXXX (ULID)",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789",
      "email_verified_at": null,
      "created_at": "2026-06-07T16:00:00.000000Z"
    }
  }
}
```

**Failure Scenarios**:
- `422` — email already taken → `errors.email: ["The email has already been taken."]`
- `422` — phone already taken → `errors.phone: ["The phone has already been taken."]`
- `422` — password too short (min 8) → `errors.password`
- `422` — password_confirmation mismatch → `errors.password`
- `422` — required fields missing → validation errors per field

**Catatan Frontend**: Simpan `access_token` dan `user` ke local state setelah register berhasil.

---

### 3.2 Login

**Tujuan**: User masuk ke aplikasi.

**Endpoint**: `POST /api/v1/auth/login`

**Auth**: Tidak diperlukan

**Rate Limit**: 5 percobaan per menit. Setelah limit tercapai → `429`.

**Request**:
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Validation Rules** (dari `LoginRequest.php`):
- `email`: required, email
- `password`: required, string

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "2|xyz...",
    "token_type": "Bearer",
    "user": {
      "id": "01JXXXXX",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "08123456789",
      "email_verified_at": null,
      "created_at": "2026-06-07T16:00:00.000000Z"
    }
  }
}
```

**Failure Scenarios**:
- `422` — kredensial salah → `errors.email` (generic message, tidak membocorkan apakah email terdaftar atau tidak — diverifikasi oleh test)
- `422` — field hilang → `errors.email` / `errors.password`
- `429` — rate limit exceeded (>5 attempt/menit) → `{ "success": false, "message": "Too many login attempts. Please try again later." }`

**⚠ Catatan Security**: Login menggunakan error message yang sama untuk email tidak ada dan password salah (user enumeration protection — diverifikasi test `AuthLoginTest.php`).

---

### 3.3 Get Current User

**Tujuan**: Ambil profil user yang sedang login.

**Endpoint**: `GET /api/v1/auth/me`

**Auth**: Bearer Token required

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "id": "01JXXXXX",
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "08123456789",
    "email_verified_at": null,
    "created_at": "2026-06-07T16:00:00.000000Z"
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- **⚠ Security**: Field `password` dan `remember_token` TIDAK pernah dikembalikan.

---

### 3.4 Refresh Token

**Tujuan**: Rotasi token — hapus token lama, buat baru.

**Endpoint**: `POST /api/v1/auth/refresh`

**Auth**: Bearer Token required

**Request**: Tidak ada body

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Token refreshed successfully",
  "data": {
    "access_token": "3|newtoken...",
    "token_type": "Bearer",
    "user": { "id": "...", "name": "...", "email": "...", "phone": "...", "email_verified_at": null, "created_at": "..." }
  }
}
```

**Failure Scenarios**: `401` — token tidak valid.

---

### 3.5 Logout

**Tujuan**: Revoke token aktif saat ini.

**Endpoint**: `POST /api/v1/auth/logout`

**Auth**: Bearer Token required

**Request**: Tidak ada body

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Logged out successfully",
  "data": null
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `401` — token invalid

**⚠ Catatan**: Hanya token yang digunakan untuk request ini yang di-revoke. Token lain di perangkat lain tidak terpengaruh (diverifikasi test `AuthLogoutTest.php`).
