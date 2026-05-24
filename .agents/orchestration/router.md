# router.md

*   **Purpose**: Central routing table mapping user requirements to operational workflows.
*   **Scope**: Task categorization and process selection.
*   **Ownership**: AI Orchestrator.
*   **Priority**: Critical.
*   **Dependency**: None.

---

## 1. Routing Table
Upon receiving a prompt or task from the user, the AI MUST match the keywords or files to the following routing rules:

| Condition | Action | Workflow Path |
| :--- | :--- | :--- |
| Contains database schema changes, migrations, or tables modifications | Load | `workflows/migration/migration-flow.md` |
| Contains API endpoint creation, controller modifications, or routes additions | Load | `workflows/feature-development/api-generation.md` |
| General feature development, Service layer updates, or MVP task | Load | `workflows/feature-development/feature-execution.md` |
| Code review requests, optimization analysis, or Pint formatting audits | Load | `workflows/code-review/backend-review.md` |
| Security verification, access-control policy creation, or abuse verification | Load | `workflows/security/security-review.md` |
| Tasks requiring planning checklists, phase prioritization, or scoping | Load | `workflows/planning/planning-flow.md` |

---

## 2. Sequence Lifecycle
1.  **Identify**: Check condition match using the table above.
2.  **Load**: Load files matched by the mapping.
3.  **Execute**: Run every numbered phase in the workflow sequentially.
4.  **Validate**: Run validation tools and review policies.
5.  **Log**: Execute logging workflow to generate an incremental sequence trace.
