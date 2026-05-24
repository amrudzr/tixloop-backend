---
trigger: always_on
---

*   **Purpose**: Define architectural patterns and structural boundaries for the codebase.
*   **Scope**: Software architecture design and implementation.
*   **Ownership**: Software Architect.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Stack and Technology Boundary
*   **Primary Backend**: Laravel 13 Monolith.
*   **Database**: PostgreSQL / SQLite (for testing).
*   **Auth**: Laravel Sanctum for API token issuance.
*   **Excluded Tech (Drift Guard)**: SvelteKit and other separate frontend frameworks must not have code written inside this backend repository.

---

## 2. Code Patterns
*   **Thin Controllers**: Controllers must coordinate requests, trigger validation, delegate business operations to services/actions, and serialize responses. Do not put business logic or data manipulation directly in controllers.
*   **Service Layer**: Business logic lives exclusively inside Services and Actions.
*   **API Resources**: All responses returned to callers must pass through Laravel Eloquent API Resources. Do not return raw models.
*   **Data Transport**: Use explicit requests and DTOs where parameters grow complex.
*   **Repository Pattern**: Strictly optional; only implement if query complexity grows excessively and requires encapsulation. Do not use for simple CRUD.
