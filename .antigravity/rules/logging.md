# Standardized Logging Rules

Every feature execution and architectural modification MUST create a corresponding log inside `.antigravity/logs/` before completion.

---

## 1. Unified Naming Convention

To resolve previous conflicts, all logs must use the following chronological format:

```
[sequence]_[YYYY-MM-DD]_[slug].md
```

- **[sequence]**: A 3-digit, zero-padded incremental number (e.g., `001`, `002`) representing the global order of operations.
- **[YYYY-MM-DD]**: The current local date.
- **[slug]**: A short, kebab-case identifier of the activity or feature (e.g., `auth-register`, `escrow-init`).

### Example
`001_2026-05-23_auth-service.md`

---

## 2. Directory Structure & Scopes

Logs are organized under the following subdirectories:

| Directory | Scope |
| :--- | :--- |
| `logs/execution/` | Feature implementations, endpoint creations, database migrations, and active task updates. |
| `logs/architecture/` | Design patterns, API structure changes, boundary definitions, and architectural components. |
| `logs/research/` | Proof-of-concept tests, documentation searches, and exploration results. |
| `logs/planning/` | Initial checklists, specifications, and scope boundary definitions. |
| `logs/code-review/` | Linting corrections, code optimizations, and feedback analysis. |

---

## 3. Log Content Template

To keep logs concise but fully traceable, use the following markdown template:

```markdown
# Execution Log: [Feature Name]

- **Sequence**: [3-digit number]
- **Date**: YYYY-MM-DD
- **Feature**: [Feature name/slug]

## Objective
[1-2 sentences explaining what was built or changed and why.]

## Implementation Summary
- [Bullet points summarizing code adjustments and functionality added.]

## Affected Files
- `[file path 1]` (created/modified/deleted)
- `[file path 2]` (created/modified/deleted)

## Decisions Made
- [Substantive architectural or design decisions, trade-offs, and reasons.]

## Known Limitations
- [Shortcomings, non-MVP scopes omitted, or trade-offs made.]

## Next Actions
- [Immediate follow-up steps for the next iteration or developer.]
```

---

## 4. Enforcement Rule
- No execution or feature task is considered complete until its corresponding log is generated.
- Avoid duplicate summaries across files; let each log be the sole source of truth for that specific execution phase.