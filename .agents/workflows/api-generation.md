---
description: 
---

# api-generation.md

*   **Purpose**: Step-by-step process for endpoint generation.
*   **Scope**: Controller, Request, Resource, Policy, and Router files generation.
*   **Ownership**: Backend Lead.
*   **Priority**: High.
*   **Dependency**: rules/backend/api-development.md.

---

## 1. Execution Steps

### Step 1: Endpoint Design
*   Define the REST verb (`GET`, `POST`, `PUT`, `DELETE`).
*   Define URL matching `/api/v1/[resource-name]`.
*   Establish matching JSON responses according to reference schemas.

### Step 2: Component Generation
*   Use Artisan: `php artisan make:request [Name]Request` for validation.
*   Use Artisan: `php artisan make:resource [Name]Resource` for serialization.
*   Use Artisan: `php artisan make:policy [Name]Policy` for authorization.
*   Use Artisan: `php artisan make:controller Api/v1/[Name]Controller` for coordination. Keep it thin!

### Step 3: Implement Services
*   Encapsulate business logic in a Service or Action class. Controller calls service, receives DTO/model, passes it to the Resource.

### Step 4: Verification
*   Write Feature tests verifying both valid execution and invalid authorization/validation.
