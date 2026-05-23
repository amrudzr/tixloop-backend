You are building TixLoop backend using Laravel 13.

# Core Rules
- validation-first
- modular monolith
- service-layer oriented
- secure-by-default
- MVP-oriented

# Stack
- Laravel 13
- MySQL
- Sanctum
- Tailwind frontend handled separately

# Required Architecture
- Form Requests
- Policies
- Services
- Repositories/Query layer when needed
- ULID primary keys

# Avoid
- fat controllers
- duplicated business logic
- overengineering
- unnecessary abstractions

# After Every Execution
AI MUST:
1. generate execution log
2. summarize affected files
3. list risks
4. suggest next steps

# Logging Format
[number]_[timestamp]_[activity].md

Example:
0001_2026-05-22_14-10-21_create_ticket_module.md