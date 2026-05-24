# API Rules

## API Versioning

All endpoints must use:

/api/v1

---

## Response Format

### Success Response

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
````

### Error Response

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

---

## Controllers

Controllers must remain thin.

Controllers SHOULD NOT:

* contain business logic
* contain complex validation
* directly manipulate models extensively

Controllers SHOULD:

* call services
* return API responses
* coordinate requests

---

## Validation

All validation must use:

* Form Request classes

Do not validate directly inside controllers.

---

## Business Logic

Business logic must live inside:

* Services
* Actions

Avoid fat controllers.

---

## Authentication

Protected routes must use:

* Bearer token authentication
* Laravel Sanctum

---

## API Resources

All API serialization must use:

* Laravel API Resources

Avoid returning raw models directly.

---

## Naming Conventions

### Routes

Use kebab-case:

* /marketplace/listings

### JSON Fields

Use snake_case:

* access_token
* refresh_token

---

## Status Codes

Use proper HTTP status codes:

* 200 OK
* 201 Created
* 401 Unauthorized
* 403 Forbidden
* 404 Not Found
* 422 Validation Error
* 500 Internal Server Error

---

## MVP Constraints

Avoid:

* overengineering
* premature abstractions
* unnecessary repository pattern
* microservices
* complex event sourcing

Focus on:

* stable API
* frontend compatibility
* fast iteration

---

## Authentication Rules

Primary authentication identity:
- email

Phone number:
- used as secondary user data
- used for verification and trust features
- not used for primary login in MVP

Avoid:
- OTP authentication
- SMS login
- social login
during MVP phase.