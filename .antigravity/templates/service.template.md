# Service Template

## Responsibilities

Service handles:
- business logic
- transaction flow
- ownership logic
- escrow operations

---

# Rules

- controllers remain thin
- use dependency injection
- transaction-safe operations
- reusable methods
- predictable outputs

---

# Required Structure

- validation handled before service
- database transaction when needed
- exception handling
- logging support

---

# Avoid

- direct request handling
- view logic
- duplicated queries