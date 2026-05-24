---
trigger: always_on
---

# Backend Rules

## Purpose
Define backend engineering standards for TixLoop Laravel services.

---

## Architecture

- Use Service Layer pattern.
- Controllers must remain thin.
- Business logic belongs in Services.
- Validation belongs in Form Requests.
- Use Actions only for reusable isolated operations.
- Avoid Repository pattern unless multiple data sources exist.

---

## API Standards

- Use RESTful naming.
- Use `/api/v1`.
- JSON response must use ApiResponse helper.
- Always return consistent structure:
  - success
  - message
  - data
  - meta

---

## Database Rules

- Use UUID for public entities.
- Prevent N+1 query issues.
- Always use foreign keys.
- Add indexes for searchable columns.

---

## Security

- Use Sanctum/PASETO authentication.
- Never expose internal IDs publicly.
- Validate authorization policies.

---

## Performance

- Eager load relations.
- Use pagination for collections.
- Cache expensive queries.

---

## Testing

- Every endpoint requires Feature Test.
- Critical services require Unit Test.
- Auth flow must be tested completely.

---

## Logging

- All major feature implementations must generate execution logs.
- Architecture decisions must generate planning logs.