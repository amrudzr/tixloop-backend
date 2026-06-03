# auto-log-hooks.md

*   **Purpose**: Log generation standards and templates for all activity categories.
*   **Scope**: Logs layout templates.
*   **Ownership**: Quality Assurance.
*   **Priority**: High.
*   **Dependency**: rules/logging/logging-standard.md.

---

## 1. Log Categories & Trigger Events

| Log Category | Trigger Event | Destination Subdirectory |
| :--- | :--- | :--- |
| **Planning Log** | Prioritization, roadmapping, scoping, or planning phase completion. | `logs/planning/` |
| **Execution Log** | Feature implementation, route creation, or code adjustment completion. | `logs/execution/` |
| **Validation Log** | Successful run of the test suite and validation scripts. | `logs/execution/` (validation logs) |
| **Review Log** | Code review completions, Pint styling fixes, and N+1 query checks. | `logs/code-review/` |
| **Migration Log** | Creating, running, and verifying database schemas. | `logs/execution/` (database logs) |

---

## 2. Template Schemas

### Planning Log Schema
```markdown
# Planning Log: [Feature/Activity Name]
- **Sequence**: [3-digit index]
- **Date**: YYYY-MM-DD
- **Scope**: [Included & Excluded items]
- **Dependencies**: [Pre-requisite packages/classes]
- **Risks**: [Technical or timeline risks evaluated]
```

### Execution Log Schema
See `.agents/rules/logging/logging-standard.md` for standard execution log format.

### Validation Log Schema
```markdown
# Validation Log: [Activity Name]
- **Sequence**: [3-digit index]
- **Date**: YYYY-MM-DD
- **Test Status**: [Pass/Fail count]
- **Linter Status**: [Styling status and Pint warnings fixed]
- **Unresolved Warnings**: [Non-blocking anomalies]
```

### Review Log Schema
```markdown
# Review Log: [Review Name]
- **Sequence**: [3-digit index]
- **Date**: YYYY-MM-DD
- **Reviewed Scope**: [File paths audited]
- **Security Check**: [Access policies validated]
- **Performance Check**: [Eager loading and query counts verified]
```

### Migration Log Schema
```markdown
# Migration Log: [Table Name]
- **Sequence**: [3-digit index]
- **Date**: YYYY-MM-DD
- **Table Name**: [Target table]
- **Fields Added**: [Fields list with data types]
- **FK Constraints**: [Foreign key declarations]
```
