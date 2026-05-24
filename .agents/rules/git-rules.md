---
trigger: always_on
---

*   **Purpose**: Define git branch strategies, commit formats, and release controls.
*   **Scope**: Source control management.
*   **Ownership**: Release Engineer.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Branch Strategy
*   **No Direct Push to Main**: All modifications MUST go through feature branches (e.g. `feature/[name]`, `bugfix/[name]`, `hotfix/[name]`).
*   **Squash Merging**: Always squash commits before merging to main to keep the main branch history clean and linear.

---

## 2. Commit and Changelog Conventions
*   **Commit Messages**: Keep commit messages concise, descriptive, and prefixed (e.g. `feat: add register validation`, `fix: correct token expiry`).
*   **Changelogs**: Generate a clean, bulleted change summary for release tags or major merge events.
