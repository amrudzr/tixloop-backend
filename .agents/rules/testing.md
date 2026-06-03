---
trigger: always_on
---

*   **Purpose**: Define the testing strategy and mandatory verification steps.
*   **Scope**: Unit, Feature, and Integration tests.
*   **Ownership**: QA Lead.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Testing Policy
*   **Feature Tests**: Every API endpoint creation or modification MUST be covered by automated Feature tests.
*   **Validation Coverage**: Mandatory tests covering both passing states and validation failure scenarios (e.g. invalid parameter formats).
*   **Auth Routes**: Guarded routes require a test verifying behavior when calls are unauthorized (missing token, invalid token, or expired token).
*   **Abuse Prevention**: Security endpoints (login, registration) must verify abuse scenarios or invalid request sequences.
