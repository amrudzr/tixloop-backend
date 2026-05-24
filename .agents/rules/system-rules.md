---
trigger: always_on
---

*   **Purpose**: Define the OS and Shell execution target environment.
*   **Scope**: Terminal execution and tooling calls.
*   **Ownership**: System Architect.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Environment Policy
*   **Target OS**: Windows 11
*   **Target Shell**: PowerShell (v5.1+)
*   **Command Formatting**: All proposed execution commands MUST be natively compatible with Windows PowerShell.
*   **Path Conventions**: Always use backslashes (`\`) for Windows path references or system commands, but maintain forward slashes (`/`) for URI-based references.
*   **Banned Actions**: Do not use Bash-only command structures (`export`, `grep`, `awk`, `sed`, `&&` for sequencing if incompatibilities exist, etc.) unless running inside a specific Linux container subshell context.
