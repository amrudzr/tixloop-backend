---
description: 
---

*   **Purpose**: Log creation flow upon task completion.
*   **Scope**: Execution trace files output.
*   **Ownership**: Developer Agent.
*   **Priority**: Critical.
*   **Dependency**: rules/logging/logging-standard.md.

---

## 1. Post-Execution Checklist
Immediately after completing any code change or planning step:
1.  **Retrieve Current Sequence**: Read `.antigravity/logs/execution` to find the latest zero-padded index (e.g. `001`).
2.  **Determine Slug**: Select a kebab-case slug for the change (e.g., `user-login`).
3.  **Compile Details**:
    *   Objective of work.
    *   List of affected files.
    *   Architectural choices made.
    *   Limitations.
    *   Immediate next actions.
4.  **Create Log File**: Write the log using the pattern `[sequence]_[YYYY-MM-DD]_[slug].md` to the appropriate subdirectory in `.antigravity/logs/`.
