# API Conventions

## API Style
RESTful API architecture.

---

# Response Format

```json
{
  "success": true,
  "message": "Request successful",
  "data": {},
  "meta": {}
}
```
---

# Naming Rules

Use plural resources.

Examples:
```json
/tickets
/events
/transactions
```

# Authentication

Use:
PASETO authentication.

Token Types:
- access token
- refresh token

---

# Authorization

Use:
- policies
- ownership validation
- role-based access

---

# Security Rules

- short-lived access token
- refresh token rotation
- token revocation support

---

# Pagination

All listing endpoints must support pagination.

---

# Validation

Validation must happen:
before business logic execution.

Use:
Form Requests.

---

# Error Handling

Use consistent error responses.

Example:
```json
{
  "success": false,
  "message": "Error message",
  "error": "error_code"
}
```

Status Codes:
400 Bad Request
401 Unauthorized
403 Forbidden
404 Not Found
422 Validation Error

# API Priorities
consistency
predictability
maintainability
security
frontend friendliness