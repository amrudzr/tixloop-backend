# conflict-resolution.md

*   **Purpose**: Resolve conflicting rules, duplicate guidelines, and tech stack contradictions deterministically.
*   **Scope**: Instruction conflict management.
*   **Ownership**: Software Architect.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Hierarchy of Authority
In case of conflicts between rule files or user directives, follow this strict priority chain:

```
1. system.md & current-phase.md (Current state)
     ▼
2. .agents/rules/system/host-environment.md & behavior.md
     ▼
3. .agents/rules/architecture/guidelines.md (Monolith boundaries)
     ▼
4. .agents/rules/backend/api-development.md & testing-standard.md
     ▼
5. User Request Directives
```

---

## 2. Specific Contradiction Resolution Policies

### Filename vs Class Standard
If a rule demands lowercase or `snake_case` filenames (e.g. `naming-rules.md`) for class files, the Laravel PHP/PSR-4 convention (PascalCase files matching class name) always wins.

### Tech Stack Drift
If an older rule (e.g. `architecture-rules.md`) mentions separate frontend stacks like SvelteKit or React but the active environment is a Laravel-only repository (`tixloop-backend`), ignore frontend framework directives and write only backend-native code.

### Log Filename Standard
In case of log naming layout discrepancies (e.g. `logging.workflow.md` vs `logging.md`), the unified format `[sequence]_[YYYY-MM-DD]_[slug].md` always wins.
