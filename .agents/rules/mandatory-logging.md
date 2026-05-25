---
trigger: always_on
---

# Mandatory Engineering Logging Rules

After every major task execution, the agent MUST:

1. Create execution logs.
2. Create markdown audit reports.
3. Save findings into /.antigravity/logs.
4. Use format from /.antigravity/rules/logging.md and /.antigravity/rules/logging-rules.md
5. Persist architectural decisions.
6. Track completed MVP progress.
7. Generate next-action recommendations.

Required outputs:
- execution log
- audit summary
- technical debt report
- next feature recommendation

Never end execution with analysis-only responses.
Always persist outputs into files.