---
description: 
---

*   **Purpose**: Rules and workflow for database schema alterations.
*   **Scope**: Migrations creation and validation.
*   **Ownership**: Database Administrator.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Schema Constraints
*   **Primary Keys**: Use ULIDs (e.g. `$table->ulid('id')->primary()`) or default IDs matching current database standards.
*   **Timestamps**: Always include timestamps (`$table->timestamps()`).
*   **Foreign Keys**: Explicitly define relationship dependencies and cascade actions.

---

## 2. Process
1.  **Before Migration**: Check foreign key order, indexing candidate fields, and impacts on existing data.
2.  **Creation**: Generate migration using Artisan: `php artisan make:migration [name]`.
3.  **Validation**: Test the migration run and rollback:
    *   `php artisan migrate`
    *   `php artisan migrate:rollback`
4.  **Trace**: Document database updates in the execution log.
