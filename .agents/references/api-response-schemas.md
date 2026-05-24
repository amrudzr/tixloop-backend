# api-response-schemas.md

*   **Purpose**: Reference schemas for REST API endpoints.
*   **Scope**: Success/Error structures.
*   **Ownership**: API Designer.
*   **Priority**: High.
*   **Dependency**: None.

---

## 1. Response Formats

### Success Response
All successful responses must follow this structure:
```json
{
  "success": true,
  "message": "Success message.",
  "data": {}
}
```

### Error Response
All error responses (including 400, 422, etc.) must follow this structure:
```json
{
  "success": false,
  "message": "Error reason overview.",
  "errors": {}
}
```

### Paginated Response
```json
{
  "success": true,
  "message": "Success",
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```
