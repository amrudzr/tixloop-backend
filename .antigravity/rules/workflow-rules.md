# Workflow Enforcement

All implementation tasks MUST execute the appropriate workflow.

The agent is REQUIRED to:
- identify the task category,
- load the matching workflow,
- execute every workflow phase,
- generate logs,
- update documentation when needed.

Tasks are NOT considered complete until:
- workflow steps are completed,
- logs are written,
- summaries are generated.

IF task contains:
- migration
→ load migration.workflow.md

IF task contains:
- endpoint
→ load api_generation.workflow.md