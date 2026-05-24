---
description: 
---

*   **Purpose**: Mandatory checklist for security validations.
*   **Scope**: Access control and transaction verification.
*   **Ownership**: Security Auditor.
*   **Priority**: Critical.
*   **Dependency**: rules/backend/api-development.md.

---

## 1. Access Control Verification
*   **Auth Checks**: Ensure token expiration policies are enforced and routes require Bearer authentication headers.
*   **Route Protections**: Validate that guest-accessible routes are limited solely to registration/login.
*   **Entity Ownership**: Verify that resources are only mutable by their owners. Use policies to check model IDs.

---

## 2. Core Business Rules Security
*   **Ticket Limits**: Verify that hard price ceilings and transaction boundaries are guarded at database level.
*   **Escrow Holds**: Verify states freeze during dispute resolution, blocking arbitrary payouts or ticket transfers.
