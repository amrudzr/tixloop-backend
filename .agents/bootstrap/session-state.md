# session-state.md

*   **Purpose**: Define rules for state persistence and execution tracking across turns.
*   **Scope**: Inter-session memory variables.
*   **Ownership**: AI Orchestrator.
*   **Priority**: Medium.
*   **Dependency**: None.

---

## 1. Active State Tracing
The agent MUST trace and log the following session variables at the start of each execution turn:
*   `active_git_branch`: Currently checked out branch.
*   `current_database_connection`: Database configurations and migrations ran.
*   `active_sequence_index`: Resolved next sequential number.
*   `current_validation_state`: Output status of last validation script execution.

---

## 2. Temporary Memory Management
*   Do not leave temporary script files directly in the workspace root.
*   All temporary debug or test scripts must be stored under `.antigravity/tmp/` or the conversation-specific scratch directory, and deleted immediately after the task completes.
