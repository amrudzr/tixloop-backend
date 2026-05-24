# Logging Rules
Every execution MUST generate logs inside:

.antigravity/logs/

The agent is REQUIRED to:
- create/update execution logs
- summarize changes
- document decisions
- store implementation traces

No task is considered complete before logs are written.

Format:
[number]_[timestamp]_[activity].md

AI MUST ALWAYS CREATE LOGS.

---

Each implementation must generate:
- execution log
- implementation summary
- affected files
- architectural decisions
- blockers/issues