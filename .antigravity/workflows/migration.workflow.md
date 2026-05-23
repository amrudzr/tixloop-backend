# Migration Workflow

## Before Migration

Check:
- FK dependencies
- ownership relations
- audit impact
- indexing requirements

---

## Migration Rules

- use ULID
- include timestamps
- include foreign keys
- maintain integrity

---

## After Migration

Validate:
- indexes
- constraints
- naming consistency