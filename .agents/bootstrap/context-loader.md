# context-loader.md

*   **Purpose**: Define the context ingestion procedure for the AI agent at startup.
*   **Scope**: Context parsing, cache load, and dependency analysis.
*   **Ownership**: System Architect.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Context Ingestion Protocol
Before performing any modification or analysis, the AI execution agent must load context by checking the following target areas sequentially:

```
                  [ Task Kickoff ]
                          │
                          ▼
            [ Load Workspace Configurations ]
           (system.md, current-phase.md, etc.)
                          │
                          ▼
             [ Read Prioritization Ledger ]
             (.agents/references/mvp-roadmap.md)
                          │
                          ▼
               [ Scan Execution Logs ]
            (.antigravity/logs/execution/)
```

---

## 2. Ingestion Checklist
1.  **Read Global Environment**: Read host environment bounds (`.agents/rules/system/host-environment.md`).
2.  **Verify Project State**: Parse the current development phase boundaries (`.antigravity/current-phase.md`).
3.  **Resolve Sequence Index**: Search the `logs/execution/` directory, identify the highest zero-padded file prefix (e.g. `005`), and set the next execution sequence to `[prefix + 1]`.
4.  **Confirm Baseline Rules**: Load default architecture limits (`.agents/rules/architecture/guidelines.md`).
