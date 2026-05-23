Generate Laravel migrations for TixLoop.

Rules:
- use ULID
- use foreign key constraints
- maintain auditability
- nullable only when justified
- follow naming consistency

Always:
- include indexes
- include timestamps
- explain relations

Avoid:
- polymorphic abuse
- unnecessary JSON fields
- weak integrity design