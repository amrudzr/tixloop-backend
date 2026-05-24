# memory-loading.md

*   **Purpose**: Define standards for accessing and loading historical context registers.
*   **Scope**: Context recall.
*   **Ownership**: AI Orchestrator.
*   **Priority**: High.
*   **Dependency**: context-loader.md.

---

## 1. Context Verification Priority
When loading history or resolving codebase patterns, follow this priority sequence:

```
1. Knowledge Items (KIs) - Repository-specific distilled context.
     ▼
2. Past execution logs (.antigravity/logs/execution/*) - Sequence history.
     ▼
3. Active database schema & class analysis.
```

---

## 2. Ingestion Checkpoints
Before beginning code modification, verify history to confirm:
*   Have similar service classes or actions already been implemented in this phase?
*   What were the decisions and issues recorded in the latest sequence logs?
*   Are there any patterns that should be reused to maintain naming and class structures?
