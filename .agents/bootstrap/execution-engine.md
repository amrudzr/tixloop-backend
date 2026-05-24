# execution-engine.md

*   **Purpose**: Rules governing how the AI agent acts upon the files and environment during execution.
*   **Scope**: File generation, script execution, and verification.
*   **Ownership**: Developer Agent.
*   **Priority**: Critical.
*   **Dependency**: workflow-lifecycle.md.

---

## 1. Code Generation Protocols
When creating files, the agent MUST:
*   Use Artisan commands first for Laravel classes: `php artisan make:[controller|model|request|policy|resource|test]` with `--no-interaction` to ensure correct framework binding.
*   Preserve all existing unrelated comments, namespace settings, and docstrings.
*   Enforce type hints and return type declarations on all PHP methods.
*   Implement explicit Form Request classes for validation; do not use inline validation in controllers.

---

## 2. Command Execution Protocol
*   Always run commands from the project root directory.
*   Always use PowerShell-compatible syntax (escapes, option syntax) as specified in host-environment settings.
*   When executing testing commands, use compact output formats to reduce token consumption (e.g. `php artisan test --compact`).
