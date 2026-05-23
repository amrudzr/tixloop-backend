# Controller Template

## Responsibilities

Controllers should:
- receive requests
- validate access
- call services
- return responses

---

# Rules

- thin controllers
- no heavy business logic
- use Form Requests
- use API Resources

---

# Avoid

- direct complex queries
- transaction-heavy logic
- duplicated validation