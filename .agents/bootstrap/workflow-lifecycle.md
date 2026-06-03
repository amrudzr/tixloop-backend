# workflow-lifecycle.md

*   **Purpose**: Define the lifecycle phases of a task execution cycle.
*   **Scope**: Tasks execution sequence.
*   **Ownership**: AI Orchestrator.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Autonomous Execution Lifecycle

```
[PLAN] ──> [LOAD CONTEXT] ──> [LOAD RULES] ──> [LOAD WORKFLOWS] ──> [EXECUTE]
                                                                        │
[SUMMARIZE] <── [LOG] <── [TEST] <── [VALIDATE] <───────────────────────┘
```

---

## 2. Phase Policies

### Phase 1: PLAN
*   Analyze user request, outline checkpoints, and define scope.

### Phase 2: LOAD CONTEXT
*   Ingest project details via `context-loader.md` (read phase, database schema, active branch).

### Phase 3: LOAD RULES
*   Load specific domain rules matching the task (e.g. backend api rules, git conventions).

### Phase 4: LOAD WORKFLOWS
*   Load the mapped workflow (e.g. endpoint generation, migration flow) via the router.

### Phase 5: EXECUTE
*   Perform code writing, file updates, and migrations generation.

### Phase 6: VALIDATE
*   Ensure code conforms to architectural patterns (thin controllers, PSR-4 PascalCase filenames, policy authorization).

### Phase 7: TEST
*   Run the test suite (`php artisan test`) and linter (`pint --dirty --format agent`).

### Phase 8: LOG
*   Write standard sequence log to `.antigravity/logs/execution/` naming it `[sequence]_[YYYY-MM-DD]_[slug].md`.

### Phase 9: SUMMARIZE
*   Return completion status, affected files, and next actions to the user.
