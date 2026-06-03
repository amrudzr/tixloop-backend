---
description: 
---

*   **Purpose**: Final validation checks before code is released or merged into the main branch.
*   **Scope**: Pre-release verification.
*   **Ownership**: Release Engineer.
*   **Priority**: Critical.
*   **Dependency**: rules/git/git-conventions.md.

---

## 1. Pre-Release Checklist
Before finalizing a merge request to the `main` branch, the agent/developer MUST perform the following validations:

### Validation 1: Testing Status
*   Run the test suite: `php artisan test --compact`
*   Verify 100% passing tests.

### Validation 2: Code Styling
*   Execute Pint linter: `vendor/bin/pint --dirty --format agent`
*   Verify no syntax/styling lint issues remain.

### Validation 3: Log Verification
*   Verify that an execution log exists in `.antigravity/logs/execution/` for every feature commit included in this release.
*   Verify sequence numbers are contiguous and have no collisions.

### Validation 4: Documentation Check
*   Ensure that all config changes are documented and API route documentation matches code changes.
