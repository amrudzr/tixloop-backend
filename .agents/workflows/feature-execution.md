---
description: 
---

# feature-execution.md

*   **Purpose**: Operational lifecycle of a feature execution cycle.
*   **Scope**: Tasks implementation from kickoff to logging.
*   **Ownership**: Developer Agent.
*   **Priority**: Critical.
*   **Dependency**: rules/logging/logging-standard.md.

---

## 1. Feature Lifecycle Phases

### Phase 1: Context Gathering
*   Read context folders.
*   Locate relevant decisions and prioritization details in `.antigravity/`.

### Phase 2: Design and Impact Analysis
*   Inspect database dependencies and migration requirements.
*   Analyze ownership and escrow impacts.

### Phase 3: Component Implementation
*   Generate needed resources (requests, migrations, models, services, controllers, policies).

### Phase 4: Verification and Quality
*   Validate requests authorization, validation rules, Pint format, and test correctness.

### Phase 5: Log Execution
*   Generate the zero-padded incremental trace log inside `.antigravity/logs/execution/` following the format `[sequence]_[YYYY-MM-DD]_[slug].md`.
