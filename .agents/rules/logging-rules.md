---
trigger: always_on
---

*   **Purpose**: Rules governing creation and validation of AI execution logs.
*   **Scope**: Execution trace recording.
*   **Ownership**: Quality Assurance / AI Lead.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Unified Naming Convention
Every completed implementation task or structural change MUST generate a markdown log inside `.antigravity/logs/execution/` before completion.
*   **Format**: `[sequence]_[YYYY-MM-DD]_[slug].md`
*   **Fields**:
    *   `[sequence]`: 3-digit zero-padded incremental number (e.g. `001`, `002`).
    *   `[YYYY-MM-DD]`: Current local date in YYYY-MM-DD format.
    *   `[slug]`: Kebab-case description of the feature or fix (e.g. `auth-register`, `migration-escrow`).
*   **Example**: `001_2026-05-23_auth-register.md`

---

## 2. Directory Layout

| Folder | Contents |
| :--- | :--- |
| `.antigravity/logs/execution/` | Feature endpoints, schema migrations, and code modifications. |
| `.antigravity/logs/architecture/` | Design decisions, boundaries, service layer contracts. |
| `.antigravity/logs/research/` | Research findings, experiments, proof-of-concept tests. |
| `.antigravity/logs/planning/` | Planning roadmaps, analyses, scope lists. |
| `.antigravity/logs/code-review/` | Lint fixes, review outputs, response checks. |

---

## 3. Log template format
Logs must strictly implement the following structure:
```markdown
# Execution Log: [Feature Name]

- **Sequence**: [3-digit sequence]
- **Date**: YYYY-MM-DD
- **Feature**: [Feature name/slug]

## Objective
[1-2 sentences of what was changed and why]

## Implementation Summary
- [Bullet points of modifications]

## Affected Files
- `[file path]` (created/modified/deleted)

## Decisions Made
- [Substantive architectural or design choices]

## Known Limitations
- [Shortcomings, deferred items, non-MVP omissions]

## Next Actions
- [Immediate next steps]
```
