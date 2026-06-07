# TixLoop Frontend Integration Guide — Part 3: Admin, Checkout & Dashboard

## 6. User Journey — Admin Verification

> **Auth**: Semua admin endpoint memerlukan Bearer Token + role `admin`. Non-admin mendapat `403`.

### 6.1 Lihat Pending Listings

**Endpoint**: `GET /api/v1/admin/listings`

**Auth**: Bearer Token + Admin role

**Query Parameters**:
- `search` (string, optional, max:255) — mencari berdasarkan ticket_code
- `per_page` (integer, optional, min:1, max:50, default:15)

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Pending listings retrieved successfully",
  "data": [
    {
      "id": "01JAAAAA",
      "original_price": "500000.00",
      "current_asking_price": "500000.00",
      "floor_price": "225000.00",
      "hard_cap_price": "575000.00",
      "verification_status": "pending",
      "listing_status": "ditangguhkan",
      "verified_at": null,
      "rejection_reason": null,
      "listed_at": null,
      "created_at": "...",
      "seller": { "id": "...", "name": "Jane", "email": "jane@example.com" },
      "ticket": {
        "id": "...", "ticket_code": "TIX-001", "seat_number": "A-1",
        "ticket_proof_path": "proofs/xxx.pdf", "ticket_proof_type": "pdf",
        "event": { "id": "...", "name": "Coldplay", "venue": "GBK", "city": "Jakarta" }
      },
      "verified_by": null
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan admin
- `422` — per_page di luar range 1-50

---

### 6.2 Lihat Detail Single Listing (Admin)

**Endpoint**: `GET /api/v1/admin/listings/{id}`

**Auth**: Bearer Token + Admin role

**Success Response (200)**: Struktur sama dengan item di index, lengkap dengan relasi ticket+event.

**Failure Scenarios**: `401`, `403`, `404`

---

### 6.3 Verify Listing

**Tujuan**: Admin menyetujui listing — listing menjadi aktif di marketplace.

**Endpoint**: `POST /api/v1/admin/listings/{id}/verify`

**Auth**: Bearer Token + Admin role

**Request**: Tidak ada body

**Constraint**: `verification_status` harus `pending`. Jika sudah verified/rejected → `403`.

**Efek setelah verify** (diverifikasi test `AdminTicketVerificationTest.php`):
- `verification_status` → `verified`
- `listing_status` → `aktif`
- `verified_at` → timestamp sekarang
- `verified_by` → id admin yang melakukan verify
- `listed_at` → timestamp sekarang

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Listing verified successfully",
  "data": {
    "verification_status": "verified",
    "listing_status": "aktif",
    "verified_at": "2026-06-07T16:10:00.000000Z",
    "verified_by": { "id": "...", "name": "System Admin", "email": "admin@example.com" }
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan admin, atau listing sudah verified/rejected
- `404` — listing tidak ditemukan

---

### 6.4 Reject Listing

**Tujuan**: Admin menolak listing dengan alasan.

**Endpoint**: `POST /api/v1/admin/listings/{id}/reject`

**Auth**: Bearer Token + Admin role

**Request** (dari `RejectListingRequest.php`):
```json
{ "rejection_reason": "Bukti tiket tidak terbaca dengan jelas." }
```

**Validation Rules**:
- `rejection_reason`: required, string, min:10, max:1000

**Constraint**: `verification_status` harus `pending`. Jika sudah verified/rejected → `403`.

**Efek setelah reject**:
- `verification_status` → `rejected`
- `listing_status` → `ditangguhkan`
- `verified_by` → id admin
- `rejection_reason` → reason yang dikirim

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Listing rejected successfully",
  "data": {
    "verification_status": "rejected",
    "listing_status": "ditangguhkan",
    "rejection_reason": "Bukti tiket tidak terbaca dengan jelas."
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan admin, atau listing sudah verified/rejected
- `422` — rejection_reason tidak ada atau < 10 karakter

---

## 7. User Journey — Checkout & Transaksi

### 7.1 Checkout Listing

**Tujuan**: Buyer membeli listing tiket dan membuat transaksi pending.

**Endpoint**: `POST /api/v1/marketplace/listings/{id}/checkout`

**Auth**: Bearer Token required

**Request**: Tidak ada body

**Business Rules** (diverifikasi `CheckoutTest.php`):
- Buyer tidak boleh seller listing itu sendiri → `403`
- Listing harus `verification_status = verified` dan `listing_status = aktif` → `422`
- Tidak boleh ada transaksi `pending` atau `paid` aktif untuk listing ini → `422`
- Transaksi dengan status `failed` tidak menghalangi checkout baru

**Success Response (201)** (dari `TransactionResource.php`):
```json
{
  "success": true,
  "message": "Checkout initiated successfully",
  "data": {
    "id": "01JBBBBB",
    "buyer": { "id": "01JCCCCC", "name": "John Buyer" },
    "seller": { "id": "01JZZZZZ", "name": "Jane Seller" },
    "resale_listing_id": "01JAAAAA",
    "ticket_id": "01JXXXXX",
    "amount": 500000.0,
    "service_fee": 0.0,
    "status": "pending",
    "escrow_status": null,
    "payment_reference": null,
    "paid_at": null,
    "released_at": null,
    "completed_at": null,
    "ticket": { "id": "01JXXXXX", "current_owner_id": "01JZZZZZ" },
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — seller mencoba beli tiketnya sendiri
- `404` — listing tidak ditemukan
- `422` — listing belum verified / sudah terjual / ada transaksi pending aktif

---

### 7.2 Simulate Payment

**Tujuan**: Mensimulasikan pembayaran berhasil dan mentransfer kepemilikan tiket ke buyer.

**Endpoint**: `POST /api/v1/transactions/{id}/simulate-payment`

**Auth**: Bearer Token required. Hanya **buyer** transaksi ini yang bisa memanggil.

**Request**: Tidak ada body

**Constraint**: `status` transaksi harus `pending`. Jika completed/failed → `403`.

**Proses Atomik** (diverifikasi `PaymentSimulationTest.php`):
1. `status` → `paid`, `escrow_status` → `held`
2. `ticket.current_owner_id` → buyer_id
3. Record baru di `ticket_ownership_history` (audit trail)
4. `resale_listing.listing_status` → `terjual`, `sold_at` → now
5. `status` → `completed`, `escrow_status` → `released`

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Payment simulation successful. Ownership transferred.",
  "data": {
    "id": "01JBBBBB",
    "buyer": { "id": "01JCCCCC", "name": "John Buyer" },
    "seller": { "id": "01JZZZZZ", "name": "Jane Seller" },
    "resale_listing_id": "01JAAAAA",
    "ticket_id": "01JXXXXX",
    "amount": 500000.0,
    "service_fee": 0.0,
    "status": "completed",
    "escrow_status": "released",
    "payment_reference": null,
    "paid_at": "2026-06-07T16:05:00.000000Z",
    "released_at": "2026-06-07T16:05:00.000000Z",
    "completed_at": "2026-06-07T16:05:00.000000Z",
    "ticket": { "id": "01JXXXXX", "current_owner_id": "01JCCCCC" },
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan buyer, atau seller mencoba, atau user tidak terkait, atau transaksi sudah completed/failed
- `404` — transaksi tidak ditemukan

---

### 7.3 Lihat Detail Transaksi

**Endpoint**: `GET /api/v1/transactions/{id}`

**Auth**: Bearer Token required. Hanya **buyer**, **seller**, atau **admin** yang bisa akses.

**Success Response (200)**: Struktur sama dengan response checkout/simulate-payment di atas.

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — user tidak terkait dengan transaksi
- `404` — tidak ditemukan

---

## 8. User Journey — Dashboard

### 8.1 Waste Dashboard

**Tujuan**: Melihat metrik dampak circular economy TixLoop.

**Endpoint**: `GET /api/v1/dashboard/waste`

**Auth**: Tidak diperlukan (public endpoint)

**Query Parameters**:
| Parameter | Type | Rules |
|-----------|------|-------|
| `event_id` | string | optional, ULID format |
| `category` | string | optional, max:100 |
| `start_date` | string | optional, Y-m-d |
| `end_date` | string | optional, Y-m-d, setelah atau sama dengan start_date |

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Waste dashboard metrics retrieved successfully",
  "data": {
    "potential_impact": {
      "potentially_rescued_tickets": 2,
      "verified_listings": 2,
      "active_listings": 3,
      "listing_value_available": 800000
    },
    "listings_breakdown": {
      "total_listings": 3,
      "pending_listings": 1,
      "rejected_listings": 0,
      "total_listing_value": 1000000,
      "verified_listing_value": 800000
    },
    "metadata": {
      "filters": { "event_id": null, "category": "Music", "start_date": "2026-01-01", "end_date": "2026-06-07" },
      "calculated_at": "2026-06-07T16:00:00Z"
    }
  }
}
```

**Definisi Metrik**:
- `potentially_rescued_tickets` = listing verified + aktif
- `listing_value_available` = sum asking price dari verified + aktif listings
- `active_listings` = semua listing dengan listing_status = aktif (termasuk pending)

**Failure Scenarios**:
- `422` — `event_id` format tidak valid (bukan ULID)
- `422` — `end_date` sebelum `start_date`

---

### 8.2 Burn Prevention Dashboard

**Tujuan**: Analisis risiko tiket hangus berdasarkan waktu tersisa sebelum event.

**Endpoint**: `GET /api/v1/dashboard/burn-prevention`

**Auth**: Optional (via `OptionalSanctumAuth` middleware)

**Query Parameters**:
- `event_id` (string, optional, ULID) — scope ke event tertentu

**Auth Behavior**:
- **Unauthenticated**: Hanya statistik platform umum. `user_listings_at_risk` = `[]`
- **Authenticated**: Platform stats + analisis listing milik user yang berisiko

**Risk Level Definitions**:
| Level | Kondisi |
|-------|---------|
| `high` | Verified+aktif dengan TTE ≤ 24 jam, atau pending dengan TTE ≤ 48 jam |
| `medium` | Verified+aktif dengan 24 jam < TTE ≤ 72 jam |
| `low` | Verified+aktif dengan TTE > 72 jam |
| `expired` | Event sudah lewat |

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Burn prevention dashboard metrics retrieved successfully",
  "data": {
    "summary": {
      "total_value_at_risk": 5400000.0,
      "high_risk_count": 12,
      "medium_risk_count": 8,
      "low_risk_count": 45,
      "historical_wasted_tickets": 3
    },
    "user_listings_at_risk": [
      {
        "listing_id": "01JAAAAA",
        "event_name": "Coldplay",
        "event_datetime": "2026-06-08T12:00:00+07:00",
        "time_to_event_hours": 17.5,
        "current_asking_price": 500000.0,
        "floor_price": 225000.0,
        "risk_level": "high",
        "is_at_floor": false,
        "recommendation": "Drop price to floor (225000.00) to maximize chance of instant sell."
      }
    ],
    "metadata": {
      "filters": { "event_id": null },
      "calculated_at": "2026-06-07T16:00:00Z"
    }
  }
}
```

---

## 9. Endpoint Reference

| Method | Endpoint | Auth | Implemented | Tested |
|--------|----------|------|-------------|--------|
| POST | `/api/v1/auth/register` | None | ✅ | ✅ |
| POST | `/api/v1/auth/login` | None | ✅ | ✅ |
| POST | `/api/v1/auth/logout` | Bearer | ✅ | ✅ |
| POST | `/api/v1/auth/refresh` | Bearer | ✅ | ⚠ No dedicated test |
| GET | `/api/v1/auth/me` | Bearer | ✅ | ✅ |
| GET | `/api/v1/tickets` | Bearer | ✅ | ✅ |
| GET | `/api/v1/tickets/{id}` | Bearer | ✅ | ✅ |
| POST | `/api/v1/tickets/upload` | Bearer | ✅ | ✅ |
| GET | `/api/v1/marketplace/listings` | None | ✅ | ✅ |
| GET | `/api/v1/marketplace/listings/{id}` | None | ✅ | ✅ |
| POST | `/api/v1/marketplace/listings` | Bearer | ✅ | ✅ |
| POST | `/api/v1/marketplace/listings/{id}/checkout` | Bearer | ✅ | ✅ |
| POST | `/api/v1/transactions/{id}/simulate-payment` | Bearer | ✅ | ✅ |
| GET | `/api/v1/transactions/{id}` | Bearer | ✅ | ✅ |
| GET | `/api/v1/dashboard/waste` | None | ✅ | ✅ |
| GET | `/api/v1/dashboard/burn-prevention` | Optional | ✅ | ✅ |
| GET | `/api/v1/admin/listings` | Bearer+Admin | ✅ | ✅ |
| GET | `/api/v1/admin/listings/{id}` | Bearer+Admin | ✅ | ✅ |
| POST | `/api/v1/admin/listings/{id}/verify` | Bearer+Admin | ✅ | ✅ |
| POST | `/api/v1/admin/listings/{id}/reject` | Bearer+Admin | ✅ | ✅ |

---

## 10. Audit Implementasi

| Endpoint di Dokumentasi | Ada di Route | Ada Controller | Ada Test | Status |
|-------------------------|--------------|----------------|----------|--------|
| POST /auth/register | ✅ | ✅ | ✅ | Ready |
| POST /auth/login | ✅ | ✅ | ✅ | Ready |
| POST /auth/logout | ✅ | ✅ | ✅ | Ready |
| POST /auth/refresh | ✅ | ✅ | ⚠ | Need Test |
| GET /auth/me | ✅ | ✅ | ✅ | Ready |
| GET /tickets | ✅ | ✅ | ✅ | Ready |
| GET /tickets/{id} | ✅ | ✅ | ✅ | Ready |
| POST /tickets/upload | ✅ | ✅ | ✅ | Ready |
| GET /marketplace/listings | ✅ | ✅ | ✅ | Ready |
| GET /marketplace/listings/{id} | ✅ | ✅ | ✅ | Ready |
| POST /marketplace/listings | ✅ | ✅ | ✅ | Ready |
| POST /marketplace/listings/{id}/checkout | ✅ | ✅ | ✅ | Ready |
| POST /transactions/{id}/simulate-payment | ✅ | ✅ | ✅ | Ready |
| GET /transactions/{id} | ✅ | ✅ | ✅ | Ready |
| GET /dashboard/waste | ✅ | ✅ | ✅ | Ready |
| GET /dashboard/burn-prevention | ✅ | ✅ | ✅ | Ready |
| GET /admin/listings | ✅ | ✅ | ✅ | Ready |
| GET /admin/listings/{id} | ✅ | ✅ | ✅ | Ready |
| POST /admin/listings/{id}/verify | ✅ | ✅ | ✅ | Ready |
| POST /admin/listings/{id}/reject | ✅ | ✅ | ✅ | Ready |

---

## 11. Temuan & Rekomendasi

### ⚠ Missing Test Coverage
- `POST /api/v1/auth/refresh` — tidak ada dedicated test. Behavior diasumsikan sama dengan login (membuat token baru).

### ⚠ Documentation Mismatches
- **Admin index**: Dokumentasi menyebutkan `listing_status: "aktif"` untuk pending listings. **Implementasi nyata**: status adalah `ditangguhkan` saat pending (diverifikasi test `AdminTicketVerificationTest.php`).
- **Browse listings index**: Dokumentasi lama menyebutkan field `ticket.event.poster_url`. **Implementasi nyata** (`ResaleListingIndexResource.php`): field ini tidak ada di index response.
- **Create listing response**: Dokumentasi menyebutkan `listing_status: "aktif"`. **Implementasi nyata**: `listing_status: "ditangguhkan"` (diverifikasi test `MarketplaceListingTest.php`).

### ⚠ Security Notes
- `ticket_code` dan `ticket_proof_path` disembunyikan dari non-owner (implemented di `TicketResource.php`)
- `seller.email` tidak dikembalikan di public marketplace endpoints (implemented di `UserPublicResource.php`)
- Login menggunakan uniform error response untuk mencegah user enumeration
- Rate limit login: 5 attempts/menit

### ⚠ Missing Frontend Information
- Tidak ada endpoint untuk mendapatkan list events. Frontend perlu event_id saat upload tiket — perlu diclarifikasi bagaimana user memilih event (search endpoint atau hardcoded).
- Tidak ada endpoint untuk user membatalkan listing mereka sendiri.
- `ticket_metadata` di `ResaleListingDetailResource` dikembalikan sebagai raw JSON — frontend perlu handle null case.

---

## 12. Frontend Page Mapping

| Screen | Endpoints |
|--------|-----------|
| Login | `POST /auth/login` |
| Register | `POST /auth/register` |
| My Tickets | `GET /tickets` |
| Ticket Detail | `GET /tickets/{id}` |
| Upload Ticket | `POST /tickets/upload` |
| Marketplace | `GET /marketplace/listings` |
| Listing Detail | `GET /marketplace/listings/{id}` |
| Create Listing | `POST /marketplace/listings` |
| Checkout | `POST /marketplace/listings/{id}/checkout` |
| Payment | `POST /transactions/{id}/simulate-payment` |
| Transaction Detail | `GET /transactions/{id}` |
| Waste Dashboard | `GET /dashboard/waste` |
| Burn Prevention | `GET /dashboard/burn-prevention` |
| Admin Panel | `GET /admin/listings` + `GET /admin/listings/{id}` |
| Admin Actions | `POST /admin/listings/{id}/verify` + `/reject` |
