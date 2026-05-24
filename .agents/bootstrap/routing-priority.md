# routing-priority.md

*   **Purpose**: Resolve task scheduling priorities and automated routing rules.
*   **Scope**: Backlog routing.
*   **Ownership**: Product Owner.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Automated Workflow Routing
Incoming requests must be parsed and routed according to this mapping table:

```
[User Request]
      │
      ▼
[Intent Parser]
      │
      ├─► (Migration Task)  ──► Load [ migration/migration-flow ]
      ├─► (API Task)        ──► Load [ backend/api-development + feature-development/api-generation ]
      ├─► (Security Task)   ──► Load [ security/security-review ]
      └─► (Release Task)    ──► Load [ release/release-check ]
```

---

## 2. Priority Hierarchy
When resource or sequence bottlenecks exist, tasks must be ordered as follows:
1.  **Level 1 — Critical (Blocker)**: Broken authentication/authorization, security breaches, database schema blockages.
2.  **Level 2 — Important (MVP Core)**: Standard CRUD endpoint additions, escrow logic integration, burn prevention calculators.
3.  **Level 3 — Optional**: Styling refinements, helper formatting, verbose CLI traces.
4.  **Level 4 — Future Scope**: Advanced non-MVP integrations, social logins, real-time chats.
