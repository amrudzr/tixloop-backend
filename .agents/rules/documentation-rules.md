---
trigger: always_on
---

# behavior.md

*   **Purpose**: General cognitive rules, naming conventions, and validation guidelines for AI execution.
*   **Scope**: Interaction rules, naming policy, code reviews, documentation, and research.
*   **Ownership**: AI Orchestrator.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Naming Conventions

### File Casing
*   **PHP Classes (Models, Controllers, Requests, Services)**: MUST use **PascalCase** matching the class name to maintain compatibility with PSR-4 autoloading (e.g. `RegisterRequest.php`).
*   **JSON Fields**: MUST use **snake_case** (e.g. `access_token`).
*   **API Routes**: MUST use **kebab-case** (e.g. `/marketplace/listings`).
*   **Log Files**: MUST use **snake_case** format starting with zero-padded sequence numbers (e.g. `001_2026-05-23_auth-service.md`).

---

## 2. Cognitive Constraints
*   **Anti-Overengineering**: Focus solely on current MVP specifications. Do not build premature generic abstractions, unnecessary repositories, or complex event networks.
*   **Consistency**: Keep state, variable declarations, and architectural patterns aligned with existing sibling files.
*   **Evidence-Based Research**: Document all technical assumptions with output traces, errors, or source references.

---

## 3. Code Review & Documentation Rules
*   **Security & Safety**: Always inspect validation layers, route authorization, and transaction scopes before declaring work complete.
*   **Documentation Integrity**: Document major structural changes in corresponding configuration files or architectural logs. Keep documentation clean, clear, and direct. Avoid generic, descriptive filler.
