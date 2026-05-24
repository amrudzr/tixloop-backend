---
description: 
---

# backend-review.md

*   **Purpose**: Code audit and optimization guidelines.
*   **Scope**: Pull request and code quality verification.
*   **Ownership**: Code Reviewer.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Mandatory Checks
Verify files before finalization against the following quality rules:
*   **Validation Check**: Ensure all inputs are validated through specialized FormRequest classes.
*   **Auth Policies**: Ensure routes are properly guarded and call policies for entity authorization.
*   **N+1 Query Detection**: Review database hits in controllers and services. Use eager loading (`with`) where necessary.
*   **Dead Code Elimination**: Strip unused imports, commented-out test remnants, or unused methods.
*   **Formatting Check**: Execute the Pint formatter command:
    `vendor/bin/pint --dirty --format agent`
