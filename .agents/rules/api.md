---
trigger: always_on
---

*   **Purpose**: Define development standards for REST API endpoints.
*   **Scope**: Route definitions, validation policies, and controller constraints.
*   **Ownership**: Backend Lead.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Routing Policy
*   **API Prefix**: `/api/v1`
*   **Route Design**: Restful endpoints using resource verbs (`GET`, `POST`, `PUT`, `DELETE`).
*   **Authorization**: All protected endpoints must use Sanctum tokens and be guarded by explicit Laravel policies.

---

## 2. Validation and Safety
*   **Form Requests**: All incoming payload validation must occur inside specialized Form Request classes. Do not use `$request->validate()` directly inside controllers.
*   **Sanitization**: Ensure requests strip unexpected input parameters before model operations are executed.
*   **HTTP Status Codes**: Use exact status codes to represent outcomes:
    *   `200 OK`: Success (read/update).
    *   `201 Created`: Resource successfully created.
    *   `401 Unauthorized`: Token invalid or missing.
    *   `403 Forbidden`: Policy authorization failed.
    *   `404 Not Found`: Model or route does not exist.
    *   `422 Validation Error`: Field validation failed.
    *   `500 Internal Server Error`: Server exception.

---

## 3. MVP Constraints
*   **Primary Identity**: Authentication uses email as the primary key.
*   **Phone Numbers**: Collected for verification metadata, but not used for primary login flows.
*   **Banned Features**: OTP login, SMS verification, and OAuth/social login are excluded from current MVP scope.
