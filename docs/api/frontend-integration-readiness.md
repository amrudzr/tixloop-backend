# Frontend Integration Readiness Audit
**Date**: 2026-06-10  
**Scope**: BE-01, BE-03, BE-04, BE-08, BE-09, BE-12

---

## 1. Endpoint Availability Matrix

Endpoints **added since the last frontend integration guide** (Part 1–3, dated 2026-06-07) that are not yet in any frontend page mapping:

| Endpoint | Backend Task | Tested | Notes |
|---|---|---|---|
| `GET /api/v1/events` | BE-03 | ✅ | New — was missing from original guide |
| `GET /api/v1/events/{id}` | BE-03 | ✅ | New |
| `GET /api/v1/marketplace/my-listings` | BE-04 | ✅ | New — seller dashboard |
| `GET /api/v1/transactions` | BE-09 | ✅ | New — transaction history list |
| `GET /api/v1/tickets/{id}/history` | BE-12 | ✅ | New — ownership history |
| `POST /api/v1/transactions/{id}/release-escrow` | BE-08 | ✅ | New — Phase 2 escrow release |

---

## 2. Frontend Page Integration Matrix

> **Status key**: ✅ Ready · ⚠ Partial · ❌ Not Integrated · 🔴 Blocking · 🟡 Non-Blocking

### 2.1 Upload Ticket Page (BE-01)

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 1 | Upload Ticket | `POST /api/v1/tickets/upload` | ⚠ Partial | Add `ticket_type` optional field to form | 🟡 Non-Blocking |
| 2 | Upload Ticket | `GET /api/v1/events` | ❌ Not Integrated | Use to populate event selector dropdown | 🔴 Blocking |

**Payload** (`multipart/form-data`):
```
event_id       required  string (ULID from events list)
ticket_code    required  string max:50
seat_number    nullable  string
original_price required  numeric > 0
ticket_proof   required  file (jpg/jpeg/png/pdf, max 2MB)
physical_photo nullable  file (jpg/jpeg/png, max 2MB)
ticket_type    nullable  string max:50   ← NEW (BE-01)
```

---

### 2.2 Events (BE-03)

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 3 | Upload Ticket (Event Picker) | `GET /api/v1/events` | ❌ Not Integrated | Build event selector component | 🔴 Blocking |
| 4 | Event prefill | `GET /api/v1/events/{id}` | ❌ Not Integrated | Optional UX improvement | 🟡 Non-Blocking |

**EventResource fields**: `id, event_name, event_category, event_datetime, venue_name, city, event_poster_url`

---

### 2.3 Seller Dashboard (BE-04)

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 5 | Seller Dashboard | `GET /api/v1/marketplace/my-listings` | ❌ Not Integrated | Build seller listings page (auth required) | 🔴 Blocking |

**SellerListingResource fields**: `id, original_price, current_asking_price, floor_price, hard_cap_price, verification_status, listing_status, rejection_reason (when rejected), ticket.seat_number, ticket.type, ticket.event.*, listed_at, sold_at`

**Status badge mapping**: `pending` → Pending Review · `aktif` → Active · `terjual` → Sold · `ditarik` → Withdrawn  
Show `rejection_reason` + "Re-list" CTA when `verification_status === "rejected"`

---

### 2.4 Escrow Two-Phase Flow (BE-08)

> **Breaking change**: `simulate-payment` is now Phase 1 only (escrow held, not completed).

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 6 | Payment Page | `POST /transactions/{id}/simulate-payment` | ⚠ Partial | Response now: `status = "paid"`, `escrow_status = "held"` (not completed) | 🟡 Non-Blocking |
| 7 | Transaction Detail | `POST /transactions/{id}/release-escrow` | ❌ Not Integrated | New step — buyer triggers ownership transfer | 🔴 Blocking |

**Updated flow**:
```
checkout → simulate-payment → [held state UI] → release-escrow → completed
```

**Phase 2 release-escrow response**: `status = "completed"`, `escrow_status = "released"`, `released_at`, `ticket.current_owner_id` → buyer

---

### 2.5 Transaction History (BE-09)

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 8 | Transaction History | `GET /api/v1/transactions` | ❌ Not Integrated | Build transaction list page (auth required) | 🔴 Blocking |
| 9 | Transaction Detail | `GET /api/v1/transactions/{id}` | ⚠ Partial | Add `escrow_status` display | 🟡 Non-Blocking |

**TransactionResource fields**: `id, buyer.name, seller.name, amount, service_fee, status, escrow_status (NEW), paid_at, released_at, completed_at, ticket.event.event_name, created_at`  
Paginated — use `meta.current_page / last_page / total`.

---

### 2.6 Ownership History (BE-12)

| # | Frontend Page | Endpoint | Status | Required Change | Blocking? |
|---|---|---|---|---|---|
| 10 | Ticket Detail → History Tab | `GET /api/v1/tickets/{id}/history` | ❌ Not Integrated | Add provenance tab (auth: owner or admin) | 🔴 Blocking |

**OwnershipHistoryResource fields**: `id, transaction_id, transferred_at (ISO8601), previous_owner.name (null = first owner), new_owner.name`  
Display as chronological timeline. `previous_owner` is null on first entry.

---

## 3. End-to-End Demo Readiness

| Demo Flow | Status |
|---|---|
| Register → Login | ✅ Ready |
| Upload Ticket with Event Picker | ❌ Events picker not built |
| Seller lists for resale | ✅ Ready |
| Admin verifies listing | ✅ Ready |
| Buyer browses + checkout | ✅ Ready |
| Payment Phase 1 (escrow held) | ⚠ Response contract changed |
| Payment Phase 2 (release escrow) | ❌ Not built |
| Seller Dashboard | ❌ Not built |
| Transaction History | ❌ Not built |
| Ticket Ownership History | ❌ Not built |
| Waste Dashboard | ✅ Ready |
| Burn Prevention | ✅ Ready |

---

## 4. Priority Summary

| Priority | Item | Blocking? |
|---|---|---|
| 🔴 P1 | Event selector (`GET /events`) for Upload page | **Blocking** |
| 🔴 P1 | `release-escrow` UI step (Phase 2) | **Blocking** |
| 🔴 P1 | Seller Dashboard (`GET /my-listings`) | **Blocking** |
| 🔴 P1 | Transaction History page (`GET /transactions`) | **Blocking** |
| 🔴 P1 | Ownership History tab on Ticket Detail | **Blocking** |
| 🟡 P2 | Fix `simulate-payment` response handling (`escrow_status = "held"`) | Non-Blocking |
| 🟡 P2 | Add `ticket_type` to Upload form | Non-Blocking |
| 🟡 P2 | Display `escrow_status` on Transaction Detail | Non-Blocking |
| 🟡 P3 | `GET /events/{id}` event prefill | Non-Blocking |
