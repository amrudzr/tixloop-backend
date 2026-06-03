---
trigger: always_on
---

# Anti Overengineering Rules

## Purpose

Prevent unnecessary abstractions, premature optimization,
and enterprise complexity in TixLoop.

---

## Core Principles

- Build only what current requirements need.
- Avoid speculative architecture.
- Prefer simplicity over flexibility.
- Prefer readability over cleverness.

---

## Forbidden Patterns

### DO NOT create:

- Repository pattern without multiple data sources
- Event sourcing
- CQRS
- Microservices
- Generic base classes
- Abstract factories
- Dynamic dependency injection layers
- Overly generic interfaces
- Multi-layer DTO mapping

unless explicitly requested.

---

## Laravel Specific

- Prefer native Laravel features first.
- Prefer Eloquent over custom ORM abstraction.
- Prefer FormRequest over custom validators.
- Prefer Policies over custom ACL systems.

---

## Database

- Do not normalize excessively.
- Avoid premature sharding/scaling patterns.
- Avoid unnecessary pivot complexity.

---

## Frontend

- Avoid global state unless truly needed.
- Avoid custom UI framework abstractions.
- Prefer reusable components only after repetition appears.

---

## AI Agent Behavior

Before generating architecture:
- ask:
  - Is this necessary now?
  - Is Laravel already solving this?
  - Is this maintainable by a small team?
  - Does this reduce complexity or add complexity?

If complexity adds no measurable value:
- reject the abstraction.