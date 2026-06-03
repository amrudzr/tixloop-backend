# Frontend API Contract Document

This document serves as the single source of truth for frontend developers integration with the TixLoop backend. All documented paths, methods, payloads, and behaviors reflect the actual backend implementation.

---

## 1. Authentication

All authentication routes are prefixed with `/api/v1/auth`.

### Register User
* **Method**: `POST`
* **URL**: `/api/v1/auth/register`
* **Auth Requirement**: None
* **Request Payload**:
  ```json
  {
    "name": "Jane Doe",
    "email": "jane@example.com",
    "phone": "+628123456789",
    "password": "securepassword123",
    "password_confirmation": "securepassword123"
  }
  ```
* **Validation Rules**:
  * `name`: string, required, max 255 characters
  * `email`: string, required, valid email, unique in `users` table
  * `phone`: string, required, unique in `users` table
  * `password`: string, required, min 8 characters, must match `password_confirmation`
* **Response Payload (HTTP 201 Created)**:
  ```json
  {
    "success": true,
    "message": "Registration successful",
    "data": {
      "access_token": "1|laravelsanctumtokencharacters...",
      "token_type": "Bearer",
      "user": {
        "id": "01h7x22b271... (ULID)",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "phone": "+628123456789",
        "email_verified_at": null,
        "created_at": "2026-06-03T11:04:55.000000Z"
      }
    }
  }
  ```
* **Validation Errors (HTTP 422 Unprocessable Content)**:
  ```json
  {
    "success": false,
    "message": "The email field is required. (and/or other validation errors)",
    "errors": {
      "email": [
        "The email has already been taken."
      ],
      "password": [
        "The password field confirmation does not match."
      ]
    }
  }
  ```

---

### Login User
* **Method**: `POST`
* **URL**: `/api/v1/auth/login`
* **Auth Requirement**: None (Rate limited: `throttle:login`)
* **Request Payload**:
  ```json
  {
    "email": "jane@example.com",
    "password": "securepassword123"
  }
  ```
* **Validation Rules**:
  * `email`: string, required, valid email
  * `password`: string, required
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Login successful",
    "data": {
      "access_token": "2|laravelsanctumtokencharacters...",
      "token_type": "Bearer",
      "user": {
        "id": "01h7x22b271... (ULID)",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "phone": "+628123456789",
        "email_verified_at": null,
        "created_at": "2026-06-03T11:04:55.000000Z"
      }
    }
  }
  ```
* **Authentication Errors (HTTP 401 Unauthorized)**:
  ```json
  {
    "success": false,
    "message": "Invalid credentials"
  }
  ```

---

### Logout User
* **Method**: `POST`
* **URL**: `/api/v1/auth/logout`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Request Payload**: None
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Logged out successfully",
    "data": null
  }
  ```

---

### Refresh Token
* **Method**: `POST`
* **URL**: `/api/v1/auth/refresh`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Request Payload**: None
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Token refreshed successfully",
    "data": {
      "access_token": "3|laravelsanctumtokencharacters...",
      "token_type": "Bearer",
      "user": {
        "id": "01h7x22b271...",
        "name": "Jane Doe",
        "email": "jane@example.com",
        "phone": "+628123456789",
        "email_verified_at": null,
        "created_at": "2026-06-03T11:04:55.000000Z"
      }
    }
  }
  ```

---

### Get Authenticated User Details
* **Method**: `GET`
* **URL**: `/api/v1/auth/me`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Request Payload**: None
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Success",
    "data": {
      "id": "01h7x22b271...",
      "name": "Jane Doe",
      "email": "jane@example.com",
      "phone": "+628123456789",
      "email_verified_at": null,
      "created_at": "2026-06-03T11:04:55.000000Z"
    }
  }
  ```

---

## 2. Seller Ticket Management

All tickets endpoints are prefixed with `/api/v1/tickets`. They require authentication (`auth:sanctum`).

### Browse Owned Tickets
* **Method**: `GET`
* **URL**: `/api/v1/tickets`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Query Parameters**:
  * `search` (string, optional) - searches event name, venue name, and city.
* **Response Payload (HTTP 200 OK)**:
  > [!NOTE]
  > Exposes `ticket_code` and `ticket_proof_path` only to the current owner (or admin role).
  
  ```json
  {
    "success": true,
    "message": "Tickets retrieved successfully",
    "data": [
      {
        "id": "01h7x22b281...",
        "event_id": "01h7x22b291...",
        "current_owner_id": "01h7x22b271...",
        "original_buyer_id": "01h7x22b271...",
        "ticket_code": "TIX-123456",
        "seat_number": "Row A Seat 12",
        "ticket_proof_path": "proofs/ticket_123.pdf",
        "ticket_proof_type": "pdf",
        "proof_uploaded_at": "2026-06-03T11:04:55.000000Z",
        "status": "aktif",
        "created_at": "2026-06-03T11:04:55.000000Z",
        "updated_at": "2026-06-03T11:04:55.000000Z",
        "event": {
          "id": "01h7x22b291...",
          "event_name": "Coldplay Live in Jakarta",
          "event_category": "Concert",
          "event_datetime": "2026-11-15T20:00:00.000000Z",
          "venue_name": "Gelora Bung Karno",
          "city": "Jakarta",
          "event_poster_url": "https://example.com/poster.jpg"
        },
        "current_owner": {
          "id": "01h7x22b271...",
          "name": "Jane Doe",
          "email": "jane@example.com",
          "phone": "+628123456789",
          "email_verified_at": null,
          "created_at": "2026-06-03T11:04:55.000000Z"
        }
      }
    ],
    "links": {
      "first": "http://localhost/api/v1/tickets?page=1",
      "last": "http://localhost/api/v1/tickets?page=1",
      "prev": null,
      "next": null
    },
    "meta": {
      "current_page": 1,
      "from": 1,
      "last_page": 1,
      "path": "http://localhost/api/v1/tickets",
      "per_page": 15,
      "to": 1,
      "total": 1
    }
  }
  ```

---

### View Ticket Detail
* **Method**: `GET`
* **URL**: `/api/v1/tickets/{id}`
* **Auth Requirement**: Bearer Token (`auth:sanctum`). User must own this ticket or be an admin.
* **Path Parameters**:
  * `id` (string, required) - ULID of the ticket.
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Ticket retrieved successfully",
    "data": {
      "id": "01h7x22b281...",
      "event_id": "01h7x22b291...",
      "current_owner_id": "01h7x22b271...",
      "original_buyer_id": "01h7x22b271...",
      "ticket_code": "TIX-123456",
      "seat_number": "Row A Seat 12",
      "ticket_proof_path": "proofs/ticket_123.pdf",
      "ticket_proof_type": "pdf",
      "proof_uploaded_at": "2026-06-03T11:04:55.000000Z",
      "status": "aktif",
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:04:55.000000Z"
    }
  }
  ```

---

### Upload Ticket
* **Method**: `POST`
* **URL**: `/api/v1/tickets/upload`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Request Payload** (Must be sent as `multipart/form-data`):
  * `event_id` (string, required) - ULID of the target event. Must exist in `events` database.
  * `ticket_code` (string, required, max 50 chars) - The unique voucher/barcode code of the ticket.
  * `seat_number` (string, optional, max 255 chars) - Seat/row description.
  * `original_price` (numeric, required, greater than 0) - Face value of the ticket.
  * `ticket_proof` (file, required, max 2048 KB) - File attachment of proof (JPG, JPEG, PNG, or PDF).
  * `physical_photo` (file, optional, max 2048 KB) - Photo of physical ticket card/paper (JPG, JPEG, PNG).
* **Response Payload (HTTP 201 Created)**:
  ```json
  {
    "success": true,
    "message": "Ticket uploaded successfully",
    "data": {
      "id": "01h7x22b281...",
      "event_id": "01h7x22b291...",
      "current_owner_id": "01h7x22b271...",
      "original_buyer_id": "01h7x22b271...",
      "ticket_code": "TIX-123456",
      "seat_number": "Row A Seat 12",
      "ticket_proof_path": "proofs/ticket_123.pdf",
      "ticket_proof_type": "pdf",
      "proof_uploaded_at": "2026-06-03T11:04:55.000000Z",
      "status": "aktif",
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:04:55.000000Z",
      "event": {
        "id": "01h7x22b291...",
        "event_name": "Coldplay Live in Jakarta"
      }
    }
  }
  ```

---

## 3. Marketplace

All marketplace routes are prefixed with `/api/v1/marketplace`.

### Browse Listings
* **Method**: `GET`
* **URL**: `/api/v1/marketplace/listings`
* **Auth Requirement**: None
* **Query Parameters**:
  * `search` (string, optional, max 255 chars) - Searches by event name, venue name, or city.
  * `per_page` (integer, optional, default: 15, range: 1-50)
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Marketplace listings retrieved successfully",
    "data": [
      {
        "id": "01h7x22b2a1...",
        "current_asking_price": 1200000.00,
        "ticket": {
          "id": "01h7x22b281...",
          "event": {
            "id": "01h7x22b291...",
            "name": "Coldplay Live in Jakarta",
            "category": "Concert",
            "venue": "Gelora Bung Karno",
            "city": "Jakarta",
            "date": "2026-11-15T20:00:00.000000Z"
          },
          "type": "VIP"
        },
        "seller": {
          "id": "01h7x22b271...",
          "name": "Jane Doe"
        },
        "listed_at": "2026-06-03T11:04:55.000000Z"
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

---

### Listing Detail
* **Method**: `GET`
* **URL**: `/api/v1/marketplace/listings/{id}`
* **Auth Requirement**: None
* **Path Parameters**:
  * `id` (string, required) - ULID of the resale listing.
* **Constraints**: Listing must be active (`listing_status = 'aktif'`) and verified (`verification_status = 'verified'`).
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Marketplace listing retrieved successfully",
    "data": {
      "id": "01h7x22b2a1...",
      "current_asking_price": 1200000.00,
      "original_price": 1500000.00,
      "floor_price": 1000000.00,
      "hard_cap_price": 1800000.00,
      "verification_status": "verified",
      "listing_status": "aktif",
      "ticket": {
        "id": "01h7x22b281...",
        "event": {
          "id": "01h7x22b291...",
          "name": "Coldplay Live in Jakarta",
          "category": "Concert",
          "venue": "Gelora Bung Karno",
          "city": "Jakarta",
          "date": "2026-11-15T20:00:00.000000Z"
        },
        "metadata": {
          "type": "VIP",
          "gate": "Gate 3"
        }
      },
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Doe"
      },
      "listed_at": "2026-06-03T11:04:55.000000Z",
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:04:55.000000Z"
    }
  }
  ```

---

### Create Listing
* **Method**: `POST`
* **URL**: `/api/v1/marketplace/listings`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Request Payload**:
  ```json
  {
    "ticket_id": "01h7x22b281...",
    "current_asking_price": 1200000.00
  }
  ```
* **Validation Rules**:
  * `ticket_id`: string, required, must exist in `tickets` table
  * `current_asking_price`: numeric, required, greater than 0
* **Response Payload (HTTP 201 Created)**:
  ```json
  {
    "success": true,
    "message": "Marketplace listing created successfully",
    "data": {
      "id": "01h7x22b2a1...",
      "ticket_id": "01h7x22b281...",
      "seller_id": "01h7x22b271...",
      "original_price": 1500000.00,
      "current_asking_price": 1200000.00,
      "floor_price": 1000000.00,
      "hard_cap_price": 1800000.00,
      "verification_status": "pending",
      "listing_status": "aktif",
      "listed_at": null,
      "sold_at": null,
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:04:55.000000Z"
    }
  }
  ```

---

## 4. Admin Verification

All admin operations are prefixed with `/api/v1/admin` and require `auth:sanctum` with the `admin` role.

### Browse Pending Listings
* **Method**: `GET`
* **URL**: `/api/v1/admin/listings`
* **Auth Requirement**: Bearer Token (`auth:sanctum`) + Admin role
* **Query Parameters**:
  * `search` (string, optional)
  * `per_page` (integer, optional, default: 15)
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Pending listings retrieved successfully",
    "data": [
      {
        "id": "01h7x22b2a1...",
        "original_price": "1500000.00",
        "current_asking_price": "1200000.00",
        "floor_price": "1000000.00",
        "hard_cap_price": "1800000.00",
        "verification_status": "pending",
        "listing_status": "aktif",
        "verified_at": null,
        "rejection_reason": null,
        "listed_at": null,
        "created_at": "2026-06-03T11:04:55.000000Z",
        "seller": {
          "id": "01h7x22b271...",
          "name": "Jane Doe",
          "email": "jane@example.com"
        },
        "ticket": {
          "id": "01h7x22b281...",
          "ticket_code": "TIX-123456",
          "seat_number": "Row A Seat 12",
          "ticket_proof_path": "proofs/ticket_123.pdf",
          "ticket_proof_type": "pdf",
          "event": {
            "id": "01h7x22b291...",
            "name": "Coldplay Live in Jakarta",
            "venue": "Gelora Bung Karno",
            "city": "Jakarta"
          }
        },
        "verified_by": null
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 1,
      "per_page": 15,
      "total": 1
    }
  }
  ```

---

### Verify Listing
* **Method**: `POST`
* **URL**: `/api/v1/admin/listings/{id}/verify`
* **Auth Requirement**: Bearer Token (`auth:sanctum`) + Admin role
* **Constraints**: Listing `verification_status` must be `pending`.
* **Request Payload**: None
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Listing verified successfully",
    "data": {
      "id": "01h7x22b2a1...",
      "original_price": "1500000.00",
      "current_asking_price": "1200000.00",
      "floor_price": "1000000.00",
      "hard_cap_price": "1800000.00",
      "verification_status": "verified",
      "listing_status": "aktif",
      "verified_at": "2026-06-03T11:10:00.000000Z",
      "rejection_reason": null,
      "listed_at": "2026-06-03T11:10:00.000000Z",
      "created_at": "2026-06-03T11:04:55.000000Z",
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Doe",
        "email": "jane@example.com"
      },
      "ticket": {
        "id": "01h7x22b281...",
        "ticket_code": "TIX-123456",
        "seat_number": "Row A Seat 12",
        "ticket_proof_path": "proofs/ticket_123.pdf",
        "ticket_proof_type": "pdf",
        "event": {
          "id": "01h7x22b291...",
          "name": "Coldplay Live in Jakarta",
          "venue": "Gelora Bung Karno",
          "city": "Jakarta"
        }
      },
      "verified_by": {
        "id": "01h7x22b200... (Admin ULID)",
        "name": "System Admin"
      }
    }
  }
  ```

---

### Reject Listing
* **Method**: `POST`
* **URL**: `/api/v1/admin/listings/{id}/reject`
* **Auth Requirement**: Bearer Token (`auth:sanctum`) + Admin role
* **Constraints**: Listing `verification_status` must be `pending`.
* **Request Payload**:
  ```json
  {
    "rejection_reason": "The uploaded ticket proof PDF does not match the event date."
  }
  ```
* **Validation Rules**:
  * `rejection_reason`: string, required, min 10, max 1000 characters
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Listing rejected successfully",
    "data": {
      "id": "01h7x22b2a1...",
      "original_price": "1500000.00",
      "current_asking_price": "1200000.00",
      "floor_price": "1000000.00",
      "hard_cap_price": "1800000.00",
      "verification_status": "rejected",
      "listing_status": "aktif",
      "verified_at": "2026-06-03T11:12:00.000000Z",
      "rejection_reason": "The uploaded ticket proof PDF does not match the event date.",
      "listed_at": null,
      "created_at": "2026-06-03T11:04:55.000000Z",
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Doe",
        "email": "jane@example.com"
      },
      "ticket": {
        "id": "01h7x22b281...",
        "ticket_code": "TIX-123456",
        "seat_number": "Row A Seat 12",
        "ticket_proof_path": "proofs/ticket_123.pdf",
        "ticket_proof_type": "pdf",
        "event": {
          "id": "01h7x22b291...",
          "name": "Coldplay Live in Jakarta",
          "venue": "Gelora Bung Karno",
          "city": "Jakarta"
        }
      },
      "verified_by": {
        "id": "01h7x22b200...",
        "name": "System Admin"
      }
    }
  }
  ```

---

## 5. Waste Dashboard

Retrieves circular ticket economy aggregates showing saved capacity and ticket reuse.

* **Method**: `GET`
* **URL**: `/api/v1/dashboard/waste`
* **Auth Requirement**: None
* **Query Parameters**:
  * `event_id` (string, optional) - Scope aggregates to a specific event (must be a valid ULID).
  * `category` (string, optional) - Scope aggregates to a specific category (max 100 chars).
  * `start_date` (string, optional, Y-m-d) - Include only listings created on or after date.
  * `end_date` (string, optional, Y-m-d, must be after or equal to `start_date`)
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Waste dashboard metrics retrieved successfully",
    "data": {
      "potential_impact": {
        "potentially_rescued_tickets": 342,
        "verified_listings": 380,
        "active_listings": 342,
        "listing_value_available": 153900000.00
      },
      "listings_breakdown": {
        "total_listings": 400,
        "pending_listings": 15,
        "rejected_listings": 5,
        "total_listing_value": 180000000.00,
        "verified_listing_value": 171000000.00
      },
      "metadata": {
        "filters": {
          "event_id": null,
          "category": "Music",
          "start_date": "2026-01-01",
          "end_date": "2026-06-03"
        },
        "calculated_at": "2026-06-03T11:04:55Z"
      }
    }
  }
  ```

### Metric Definitions
1. **Potentially Rescued Tickets**: Active resale listings that are verified and ready for purchase.
2. **Verified Listings**: Total listings approved by admins.
3. **Active Listings**: Total listings currently visible on the market.
4. **Listing Value Available**: Total asking value of listings ready for instant purchase.
5. **Total Listings**: All tickets ever submitted for listing (any verification or listing status).
6. **Pending Listings**: Submissions waiting for admin approval.
7. **Rejected Listings**: Submissions rejected by admin.
8. **Total Listing Value**: Sum of prices of all submissions.
9. **Verified Listing Value**: Sum of prices of all approved submissions.

---

## 6. Burn Prevention

Retrieves risk assessments for active listings based on remaining Time-to-Event (TTE).

* **Method**: `GET`
* **URL**: `/api/v1/dashboard/burn-prevention`
* **Auth Requirement**: None (Optional Sanctum Auth)
* **Query Parameters**:
  * `event_id` (string, optional) - Scope aggregates to a specific event (ULID).

### Authentication Behavior
* **Unauthenticated Request**: Computes general platform statistics. Returns `user_listings_at_risk` as an empty array.
* **Authenticated Request**: Computes general platform statistics, AND checks the authenticated user's listings to return user-specific risk analysis and pricing adjustments recommendations.

### Risk Level Calculations
* **High Risk**:
  * Verified & Active listings with Time-to-Event (TTE) $\le$ 24 hours.
  * Pending listings with TTE $\le$ 48 hours.
  * *Recommendation*: Drop price to floor price to maximize chance of instant sell, or "Price is at floor. Maximum rescue visibility achieved." if already at floor.
* **Medium Risk**:
  * Verified & Active listings with 24 hours < TTE $\le$ 72 hours.
  * *Recommendation*: Adjust asking price closer to floor to increase visibility.
* **Low Risk**:
  * Verified & Active listings with TTE > 72 hours.
  * *Recommendation*: Listing active. Monitor market activity.
* **Expired**:
  * Event datetime has passed. Ticket has not been sold or used.
  * *Recommendation*: Event has passed. Ticket is potentially wasted.

* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Burn prevention dashboard metrics retrieved successfully",
    "data": {
      "summary": {
        "total_value_at_risk": 5400000.00,
        "high_risk_count": 12,
        "medium_risk_count": 8,
        "low_risk_count": 45,
        "historical_wasted_tickets": 3
      },
      "user_listings_at_risk": [
        {
          "listing_id": "01h7x22b2a1...",
          "event_name": "Coldplay Live in Jakarta",
          "event_datetime": "2026-06-04T12:00:00+07:00",
          "time_to_event_hours": 17.5,
          "current_asking_price": 1200000.00,
          "floor_price": 1000000.00,
          "risk_level": "high",
          "is_at_floor": false,
          "recommendation": "Drop price to floor ($1000000.00) to maximize chance of instant sell."
        }
      ],
      "metadata": {
        "filters": {
          "event_id": null
        },
        "calculated_at": "2026-06-03T11:04:55Z"
      }
    }
  }
  ```

---

## 7. Checkout & Escrow

Transactions coordinate the exchange of ticket ownership for funds held in escrow.

### Checkout Listing
* **Method**: `POST`
* **URL**: `/api/v1/marketplace/listings/{id}/checkout`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Path Parameters**:
  * `id` (string, required) - ULID of the resale listing to purchase.
* **Constraints**:
  * Buyer cannot be the seller of the listing.
  * Listing must be verified and active.
  * No active transaction (`pending` or `paid`) can exist for this listing.
* **Response Payload (HTTP 201 Created)**:
  ```json
  {
    "success": true,
    "message": "Checkout initiated successfully",
    "data": {
      "id": "01h7x22b2c1... (Transaction ULID)",
      "buyer": {
        "id": "01h7x22b2b1...",
        "name": "John Buyer"
      },
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Seller"
      },
      "resale_listing_id": "01h7x22b2a1...",
      "ticket_id": "01h7x22b281...",
      "amount": 1200000.00,
      "service_fee": 0.00,
      "status": "pending",
      "escrow_status": null,
      "payment_reference": null,
      "paid_at": null,
      "released_at": null,
      "completed_at": null,
      "ticket": {
        "id": "01h7x22b281...",
        "current_owner_id": "01h7x22b271..."
      },
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:04:55.000000Z"
    }
  }
  ```

---

### Simulate Payment
* **Method**: `POST`
* **URL**: `/api/v1/transactions/{id}/simulate-payment`
* **Auth Requirement**: Bearer Token (`auth:sanctum`)
* **Path Parameters**:
  * `id` (string, required) - ULID of the transaction.
* **Constraints**:
  * Authenticated user must be the buyer of this transaction.
  * Transaction status must be `pending`.
* **Atomic Ownership Transfer Behavior**:
  Calling this endpoint triggers a database transaction that performs the following sequential actions atomically:
  1. Transaction status transitions to `paid` and `escrow_status` transitions to `held`.
  2. The ticket's `current_owner_id` is updated to the buyer's ID.
  3. A new `TicketOwnershipHistory` record is created logging the swap.
  4. The listing status is set to `terjual` (sold) and `sold_at` is set.
  5. Transaction status transitions to `completed` and `escrow_status` transitions to `released`.
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Payment simulation successful. Ownership transferred.",
    "data": {
      "id": "01h7x22b2c1...",
      "buyer": {
        "id": "01h7x22b2b1...",
        "name": "John Buyer"
      },
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Seller"
      },
      "resale_listing_id": "01h7x22b2a1...",
      "ticket_id": "01h7x22b281...",
      "amount": 1200000.00,
      "service_fee": 0.00,
      "status": "completed",
      "escrow_status": "released",
      "payment_reference": null,
      "paid_at": "2026-06-03T11:05:00.000000Z",
      "released_at": "2026-06-03T11:05:00.000000Z",
      "completed_at": "2026-06-03T11:05:00.000000Z",
      "ticket": {
        "id": "01h7x22b281...",
        "current_owner_id": "01h7x22b2b1..."
      },
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:05:00.000000Z"
    }
  }
  ```

---

### Transaction Detail
* **Method**: `GET`
* **URL**: `/api/v1/transactions/{id}`
* **Auth Requirement**: Bearer Token (`auth:sanctum`). User must be the buyer, seller, or admin.
* **Path Parameters**:
  * `id` (string, required) - ULID of the transaction.
* **Response Payload (HTTP 200 OK)**:
  ```json
  {
    "success": true,
    "message": "Transaction retrieved successfully",
    "data": {
      "id": "01h7x22b2c1...",
      "buyer": {
        "id": "01h7x22b2b1...",
        "name": "John Buyer"
      },
      "seller": {
        "id": "01h7x22b271...",
        "name": "Jane Seller"
      },
      "resale_listing_id": "01h7x22b2a1...",
      "ticket_id": "01h7x22b281...",
      "amount": 1200000.00,
      "service_fee": 0.00,
      "status": "completed",
      "escrow_status": "released",
      "payment_reference": null,
      "paid_at": "2026-06-03T11:05:00.000000Z",
      "released_at": "2026-06-03T11:05:00.000000Z",
      "completed_at": "2026-06-03T11:05:00.000000Z",
      "ticket": {
        "id": "01h7x22b281...",
        "current_owner_id": "01h7x22b2b1..."
      },
      "created_at": "2026-06-03T11:04:55.000000Z",
      "updated_at": "2026-06-03T11:05:00.000000Z"
    }
  }
  ```

---

## 8. Shared API Standards

### Success Response Format
All successful responses return a 2xx HTTP status and follow a uniform structure:
```json
{
  "success": true,
  "message": "Human readable response description",
  "data": {
    "key": "value"
  }
}
```
If metadata is returned (such as filter inputs or timestamps), it will be added to a root-level `meta` key.

---

### Error Response Format
All application errors (bad request, authentication failure, unauthorized action) return a 4xx or 5xx HTTP status and match the following structure:
```json
{
  "success": false,
  "message": "Error details or validation message",
  "errors": {
    "field_name": [
      "Validation error message detailing why this field failed."
    ]
  }
}
```
`errors` is only present for validation status `422`.

---

### Pagination Format
Paginated lists (e.g., tickets, marketplace, admin listings) wrap the array in the root-level `data` key, and provide `links` and `meta` to navigate pages:
```json
{
  "success": true,
  "message": "Items retrieved successfully",
  "data": [
    { "id": "..." }
  ],
  "links": {
    "first": "http://localhost/api/v1/endpoint?page=1",
    "last": "http://localhost/api/v1/endpoint?page=4",
    "prev": null,
    "next": "http://localhost/api/v1/endpoint?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 4,
    "path": "http://localhost/api/v1/endpoint",
    "per_page": 15,
    "to": 15,
    "total": 60
  }
}
```

---

### Authentication Header
Requests protecting under Sanctum require passing the API token issued during Register/Login in the `Authorization` header:
```http
Authorization: Bearer <access_token>
Accept: application/json
```

---

## 9. Frontend Page Mapping

| Frontend Page / Screen | Required API Endpoints |
| :--- | :--- |
| **Login Screen** | `POST /api/v1/auth/login` |
| **Register Screen** | `POST /api/v1/auth/register` |
| **Seller Ticket Wallet (My Tickets)** | `GET /api/v1/tickets` |
| **Upload Ticket Verification Form** | `POST /api/v1/tickets/upload` |
| **Ticket Details View** | `GET /api/v1/tickets/{id}` |
| **Marketplace Browse** | `GET /api/v1/marketplace/listings` |
| **Listing Details Page** | `GET /api/v1/marketplace/listings/{id}` |
| **Create Listings Form** | `POST /api/v1/marketplace/listings` |
| **Checkout Confirmation** | `POST /api/v1/marketplace/listings/{id}/checkout` |
| **Payment Simulation Screen** | `POST /api/v1/transactions/{id}/simulate-payment` |
| **Transaction Details** | `GET /api/v1/transactions/{id}` |
| **Waste & Impact Dashboard** | `GET /api/v1/dashboard/waste` |
| **Burn Prevention Center** | `GET /api/v1/dashboard/burn-prevention` |
| **Admin Resale Listings Panel** | `GET /api/v1/admin/listings`<br>`GET /api/v1/admin/listings/{id}` |
| **Admin Verification Action Modals** | `POST /api/v1/admin/listings/{id}/verify`<br>`POST /api/v1/admin/listings/{id}/reject` |

---

## 10. Frontend Readiness Summary

All endpoints for the following features are fully implemented, tested, and ready for deployment.

| Screen/Module | Status | Notes |
| :--- | :--- | :--- |
| **Authentication Flow (Register, Login, Me, Logout)** | 🟢 Ready | Rate limiting active on Login route. |
| **Seller Ticket List & Upload** | 🟢 Ready | PDF/Image file upload fully tested. |
| **Marketplace Browse & Detail** | 🟢 Ready | Supports searches on Event Name/Venue/City. |
| **Create Listing** | 🟢 Ready | Auto-determines floors and hard caps. |
| **Checkout & Simulate Payment** | 🟢 Ready | Row-level locking protects double sell race conditions. |
| **Sustainability Waste Dashboard** | 🟢 Ready | Filters by Event/Category/Dates available. |
| **Burn Prevention Dashboard** | 🟢 Ready | User listings at risk returned when authenticated. |
| **Admin Listings Review & Action Panel** | 🟢 Ready | Access is strictly restricted to verified Admins. |
