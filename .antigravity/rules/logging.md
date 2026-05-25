# Standardized Logging Rules

Every feature execution and architectural modification MUST create a corresponding log inside `.antigravity/logs/` before completion.

---

## 1. Unified Naming Convention

To resolve previous conflicts, all logs must use the following chronological format:

```
[sequence]_[YYYY-MM-DD]_[slug].md
```

- **[sequence]**: A 3-digit, zero-padded incremental number (e.g., `001`, `002`) representing the global order of operations.
- **[YYYY-MM-DD]**: The current local date.
- **[slug]**: A short, kebab-case identifier of the activity or feature (e.g., `auth-register`, `escrow-init`).

### Example
`001_2026-05-23_auth-service.md`

---

## 2. Directory Structure & Scopes

Logs are organized under the following subdirectories:

| Directory | Scope |
| :--- | :--- |
| `logs/execution/` | Feature implementations, endpoint creations, database migrations, and active task updates. |
| `logs/architecture/` | Design patterns, API structure changes, boundary definitions, and architectural components. |
| `logs/research/` | Proof-of-concept tests, documentation searches, and exploration results. |
| `logs/planning/` | Initial checklists, specifications, and scope boundary definitions. |
| `logs/code-review/` | Linting corrections, code optimizations, and feedback analysis. |
| `logs/testing/` | Automated and manual validation activities including unit tests, feature tests, integration tests, migration verification, security validation, API contract checks, performance checks, raw execution outputs, testing artifacts, and evidence-based stabilization reports. |

---

## 3. Log Content Template

To keep logs concise but fully traceable, use the following markdown template:

```markdown
# Execution Log: [Feature Name]

- **Sequence**: [3-digit number]
- **Date**: YYYY-MM-DD
- **Feature**: [Feature name/slug]

## Objective
[1-2 sentences explaining what was built or changed and why.]

## Implementation Summary
- [Bullet points summarizing code adjustments and functionality added.]

## Affected Files
- `[file path 1]` (created/modified/deleted)
- `[file path 2]` (created/modified/deleted)

## Decisions Made
- [Substantive architectural or design decisions, trade-offs, and reasons.]

## Known Limitations
- [Shortcomings, non-MVP scopes omitted, or trade-offs made.]

## Next Actions
- [Immediate follow-up steps for the next iteration or developer.]
```

---

## 4. Enforcement Rule
- No execution, feature, bugfix, or architectural modification is considered complete until its corresponding log and raw validation outputs are generated and verified.
- Avoid duplicate summaries across files. Every log file must represent a unique sequence of work and be the sole source of truth for that specific execution phase.
- Logs must link to their corresponding planning logs, architecture logs, or research logs where applicable, ensuring a fully traceable lifecycle.

---

## 5. Validation Evidence Standard

Every validation or testing phase documented in a log (execution, testing, or code-review) MUST contain clear, unedited, and verifiable proof of actual execution. The following elements are mandatory:

1. **Executed Command**: The exact command line string run in the terminal (e.g., `php artisan test --compact`, `vendor/bin/pint --dirty --format agent`).
2. **Raw Terminal Output**: A code block containing the exact stdout and stderr from the command. For extremely long outputs (exceeding 100 lines), a representative snippet containing the execution headers, any failures/warnings, and the final summary line is permitted.
3. **Execution Metadata**:
   - **Timestamp**: The local ISO 8601 timestamp (e.g., `2026-05-25T20:45:12+07:00`) when the validation command was run.
   - **Execution Duration**: The runtime reported by the runner (e.g., `duration_ms` or seconds).
4. **Metrics and Counts**:
   - **Pest/PHPUnit**: The total tests run, tests passed, tests failed, and total assertions.
   - **Linter**: The total number of files analyzed, styled, or verified.
5. **Database Migration / Rollback Log**: Whenever migrations are involved, the log must document the raw output of both the forward migration (`php artisan migrate`) and the rollback migration (`php artisan migrate:rollback`) with execution times per file to guarantee rollback stability.
6. **Artifact References**: Clickable markdown links using the `file:///` URI scheme pointing to the raw outputs or artifacts (e.g., `[Test Output Log](file:///c:/projects/laravel/tixloop-backend/.antigravity/logs/testing/artifacts/009_sell-ticket-refactor_console.log)`).

---

## 6. Synthetic Reporting Prohibition

- **Zero Tolerance for Hallucination**: The generation of synthetic, projected, simulated, or fake validation results (e.g., asserting "PASSED" or writing simulated Pest JSON outputs without actually running the tests) is strictly prohibited.
- **Blunt Reporting of Blocks and Failures**: If an execution environment lacks the necessary dependencies (such as missing SQLite extensions) or tests fail, the log MUST record the status as "BLOCKED" or "FAILED" and paste the raw terminal exception or diagnostic error.
- **Auditability and Verification**: Future contributors or CI/CD pipelines must be able to reproduce and audit the validation results using the exact command and context provided in the logs.

---

## 7. Required Testing Artifacts

For every implementation phase, the following testing artifacts must be preserved and stored under the `.antigravity/logs/testing/artifacts/` directory:

1. **Raw Terminal Logs (`.log` or `.txt`)**: Store the full stdout/stderr of the test suite run in a file named `[sequence]_[slug]_console.log`.
2. **Pest Test Results / Reports**: Save the test runner's raw payload (such as XML reports, JSON results, or coverage details) in the artifacts directory.
3. **Pint Format Log**: Save the raw output of `vendor/bin/pint --format agent` to verify code style compliance.
4. **Migration Proofs**: Save the stdout output of migration runs and rollbacks.

All artifact references in the markdown logs must be linked using absolute file URLs.

---

## 8. Completion Gate

Before a feature is considered complete, it must pass through the following strict completion gate:

1. **Schema Integrity Check**: The database schema must be verified using `database-schema` or database queries to ensure it matches the migration specifications exactly.
2. **Test Cleanliness**: Run the full test suite and confirm 100% of tests pass.
3. **Formatting Check**: Run the Pint formatter (`vendor/bin/pint`) to ensure code matches the project's style guidelines.
4. **Log Consistency Check**:
   - Ensure the sequence number is correctly incremented relative to the latest log file.
   - Confirm all modified/created/deleted files are listed under `## Affected Files`.
   - Ensure there is no duplicated description across files.
5. **Artifact Verification**: Confirm that all required raw terminal logs and testing artifacts have been saved, and their markdown links are correct and verified.