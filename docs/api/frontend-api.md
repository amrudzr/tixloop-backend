# 📘 TixLoop Frontend API Documentation

## Base URL

```txt
https://api.tixloop.id/api/v1
```

---

# 🔐 AUTHENTICATION

Semua endpoint protected menggunakan:

```http
Authorization: Bearer {token}
```

## Authentication Flow

```txt
Register
→ Login
→ Receive Access Token
→ Access Protected API
→ Refresh Token
→ Logout
```

---

# 📦 STANDARD RESPONSE FORMAT

## Success Response

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

---

## Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": [
      "Email already taken"
    ]
  }
}
```

---

# 🔑 AUTH API

# POST /auth/register

## Description
Register user baru.

## Request

```json
{
  "email": "user@mail.com",
  "phone": "08123456789",
  "password": "password123",
  "password_confirmation": "password123"
}
```

## Validation Rules

| Field | Rules |
|---|---|
| email | required,email,unique |
| phone | required,unique |
| password | required,min:8,confirmed |

---

# 👤 USER PROFILE API

# GET /me

## Description
Get authenticated user profile.

## Auth Required
✅ Yes

---

# 🪪 VERIFICATION API

# POST /verification/ktp

## Content-Type

```txt
multipart/form-data
```

---

# 🎫 EVENTS API

# GET /events

## Query Parameters

| Parameter | Type |
|---|---|
| category | string |
| city | string |
| tier | string |
| trending | boolean |
| search | string |
| page | integer |

---

# 🎟️ TICKET API

# POST /tickets/upload

## Form Data

| Field | Type |
|---|---|
| event_id | string |
| ticket_file | file |
| physical_photo | file |
| ticket_type | string |

---

# 🛒 MARKETPLACE LISTING API

# POST /marketplace/listings

## Business Rules

- Max markup 115%
- Floor price 45%
- One listing per ticket

---

# 🔥 BURN PREVENTION API

# GET /marketplace/listings/{listing_id}/burn-status

---

# 💳 TRANSACTION API

# POST /checkout

---

# 🔒 ESCROW API

# GET /transactions/{transaction_id}/escrow

---

# 📍 VENUE VALIDATION API

# POST /venue/checkin

---

# 📊 DASHBOARD API

# GET /dashboard/national

---

# 🚨 ANTI SCALPER API

# GET /anti-scalper/purchase-limit/{event_id}

---

# ⚖️ DISPUTE API

# POST /disputes

---

# 🛡️ ADMIN API

# POST /admin/verifications/{verification_id}/approve

---

# 📱 FRONTEND IMPLEMENTATION NOTES

## Recommended API Layer

```txt
src/lib/api/
├── auth.ts
├── marketplace.ts
├── tickets.ts
├── transactions.ts
├── dashboard.ts
```

---

# Recommended Frontend Route Structure

```txt
src/routes/
├── login
├── register
├── marketplace
├── event/[id]
├── ticket/[id]
├── dashboard
├── profile
├── disputes
```
