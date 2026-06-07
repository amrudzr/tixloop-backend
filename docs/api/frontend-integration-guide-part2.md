# TixLoop Frontend Integration Guide — Part 2: Tickets & Marketplace

## 4. User Journey — Ticket Wallet

### 4.1 Lihat Tiket Saya

**Tujuan**: User melihat semua tiket yang dimilikinya. Admin dapat melihat semua tiket.

**Endpoint**: `GET /api/v1/tickets`

**Auth**: Bearer Token required

**Query Parameters**:
- `search` (string, optional) — filter berdasarkan event_name, venue_name, city

**Success Response (200)** — Paginated:
```json
{
  "success": true,
  "message": "Tickets retrieved successfully",
  "data": [
    {
      "id": "01JXXXXX",
      "event_id": "01JYYYYY",
      "current_owner_id": "01JZZZZZ",
      "original_buyer_id": "01JZZZZZ",
      "ticket_code": "TIX-0001-ABCD",
      "seat_number": "A-1",
      "ticket_proof_path": "proofs/xxx.pdf",
      "ticket_proof_type": "pdf",
      "proof_uploaded_at": "2026-06-07T16:00:00.000000Z",
      "status": "aktif",
      "created_at": "...",
      "updated_at": "...",
      "event": { "id": "...", "event_name": "Coldplay", "event_category": "Concert", "event_datetime": "...", "venue_name": "GBK", "city": "Jakarta", "event_poster_url": null },
      "current_owner": { "id": "...", "name": "...", "email": "...", "phone": "...", "email_verified_at": null, "created_at": "..." }
    }
  ],
  "links": { "first": "...", "last": "...", "prev": null, "next": null },
  "meta": { "current_page": 1, "from": 1, "last_page": 1, "path": "...", "per_page": 15, "to": 1, "total": 1 }
}
```

**⚠ Field Visibility Rules** (dari `TicketResource.php`):
- `ticket_code` — hanya dikembalikan untuk **pemilik tiket** atau **admin**. Null jika bukan.
- `ticket_proof_path` — hanya dikembalikan untuk **pemilik tiket** atau **admin**. Null jika bukan.

**Failure Scenarios**:
- `401` — unauthenticated

**Ticket Status Values**: `aktif`, `digunakan`, `hangus`

---

### 4.2 Lihat Detail Tiket

**Tujuan**: User melihat detail satu tiket spesifik.

**Endpoint**: `GET /api/v1/tickets/{id}`

**Auth**: Bearer Token required. Hanya pemilik tiket atau admin yang bisa akses.

**Path Parameter**: `id` (ULID tiket)

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Ticket retrieved successfully",
  "data": {
    "id": "01JXXXXX",
    "event_id": "01JYYYYY",
    "current_owner_id": "01JZZZZZ",
    "original_buyer_id": "01JZZZZZ",
    "ticket_code": "TIX-0001-ABCD",
    "seat_number": "A-1",
    "ticket_proof_path": "proofs/xxx.pdf",
    "ticket_proof_type": "pdf",
    "proof_uploaded_at": "...",
    "status": "aktif",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan pemilik tiket dan bukan admin (diverifikasi test `TicketListingTest.php`)
- `404` — tiket tidak ditemukan

---

### 4.3 Upload Tiket

**Tujuan**: User mengupload tiket baru beserta bukti kepemilikan.

**Endpoint**: `POST /api/v1/tickets/upload`

**Auth**: Bearer Token required

**⚠ Content-Type**: `multipart/form-data` (wajib karena ada file upload)

**Form Fields** (dari `StoreTicketRequest.php`):
| Field | Type | Required | Rules |
|-------|------|----------|-------|
| `event_id` | string | Ya | exists:events,id |
| `ticket_code` | string | Ya | max:50 |
| `seat_number` | string | Tidak | max:255 |
| `original_price` | numeric | Ya | gt:0 |
| `ticket_proof` | file | Ya | mimes:jpg,jpeg,png,pdf \| max:2048 KB |
| `physical_photo` | file | Tidak | mimes:jpg,jpeg,png \| max:2048 KB |

**Success Response (201)**:
```json
{
  "success": true,
  "message": "Ticket uploaded successfully",
  "data": {
    "id": "01JXXXXX",
    "event_id": "01JYYYYY",
    "current_owner_id": "01JZZZZZ",
    "original_buyer_id": "01JZZZZZ",
    "ticket_code": "TIX-0001-ABCD",
    "seat_number": "A-1",
    "ticket_proof_path": "proofs/xxx.pdf",
    "ticket_proof_type": "pdf",
    "proof_uploaded_at": "...",
    "status": "aktif",
    "created_at": "...",
    "updated_at": "...",
    "event": { "id": "...", "event_name": "Coldplay" }
  }
}
```

**Failure Scenarios**:
- `401` — unauthenticated
- `422` — required fields missing: `event_id`, `ticket_code`, `original_price`, `ticket_proof`
- `422` — file type tidak valid (hanya jpg,jpeg,png,pdf)
- `422` — file terlalu besar (>2048 KB)

---

## 5. User Journey — Marketplace

### 5.1 Browse Listings

**Tujuan**: Siapa saja (termasuk guest) dapat melihat daftar listing tiket yang aktif dan terverifikasi.

**Endpoint**: `GET /api/v1/marketplace/listings`

**Auth**: Tidak diperlukan

**Query Parameters** (dari `BrowseListingsRequest.php`):
| Parameter | Type | Default | Rules |
|-----------|------|---------|-------|
| `search` | string | - | nullable, max:255 |
| `per_page` | integer | 15 | nullable, min:1, max:50 |

**Search** mencakup: event_name, venue_name, city.

**⚠ Filter otomatis**: Hanya listing dengan `listing_status = aktif` DAN `verification_status = verified` yang tampil (diverifikasi test `MarketplaceBrowseTest.php`).

**Success Response (200)**:
```json
{
  "success": true,
  "message": "Marketplace listings retrieved successfully",
  "data": [
    {
      "id": "01JAAAAA",
      "current_asking_price": "500000.00",
      "ticket": {
        "id": "01JXXXXX",
        "event": {
          "id": "01JYYYYY",
          "name": "Coldplay Concert",
          "category": "Concert",
          "venue": "Gelora Bung Karno",
          "city": "Jakarta",
          "date": "2026-11-15T20:00:00.000000Z"
        },
        "type": "VIP"
      },
      "seller": { "id": "01JZZZZZ", "name": "Jane Doe" },
      "listed_at": "2026-06-07T16:00:00.000000Z"
    }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 1 }
}
```

**⚠ Privacy**: `seller.email` TIDAK dikembalikan di index endpoint (diverifikasi test).

**Failure Scenarios**:
- `422` — `per_page` > 50

---

### 5.2 Detail Listing

**Tujuan**: Melihat detail lengkap satu listing (termasuk harga floor dan hard cap).

**Endpoint**: `GET /api/v1/marketplace/listings/{id}`

**Auth**: Tidak diperlukan

**Constraint**: Listing harus `listing_status = aktif` DAN `verification_status = verified`. Jika tidak → `404`.

**Success Response (200)** (dari `ResaleListingDetailResource.php`):
```json
{
  "success": true,
  "message": "Marketplace listing retrieved successfully",
  "data": {
    "id": "01JAAAAA",
    "current_asking_price": "500000.00",
    "original_price": "500000.00",
    "floor_price": "225000.00",
    "hard_cap_price": "575000.00",
    "verification_status": "verified",
    "listing_status": "aktif",
    "ticket": {
      "id": "01JXXXXX",
      "event": {
        "id": "01JYYYYY",
        "name": "Coldplay Concert",
        "category": "Concert",
        "venue": "Gelora Bung Karno",
        "city": "Jakarta",
        "date": "2026-11-15T20:00:00.000000Z"
      },
      "metadata": { "original_price": 500000, "physical_photo_path": "..." }
    },
    "seller": { "id": "01JZZZZZ", "name": "Jane Doe" },
    "listed_at": "...",
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**Failure Scenarios**:
- `404` — listing tidak ditemukan atau bukan aktif+verified

---

### 5.3 Buat Listing

**Tujuan**: Seller mendaftarkan tiket miliknya ke marketplace.

**Endpoint**: `POST /api/v1/marketplace/listings`

**Auth**: Bearer Token required

**Request** (dari `StoreListingRequest.php`):
```json
{
  "ticket_id": "01JXXXXX",
  "current_asking_price": 500000
}
```

**Validation Rules**:
- `ticket_id`: required, string, exists:tickets
- `current_asking_price`: required, numeric, gt:0

**Business Rules** (diverifikasi oleh `MarketplaceListingTest.php`):
- User harus pemilik tiket → `403` jika bukan
- Tiket harus berstatus `aktif` → `422`
- Tiket tidak boleh sudah punya listing aktif atau ditangguhkan → `422`
- `current_asking_price` tidak boleh di bawah `floor_price` (45% dari original_price) → `422`
- `current_asking_price` tidak boleh di atas `hard_cap_price` (115% dari original_price) → `422`
- Tiket harus memiliki `original_price` di metadata → `422`

**Pricing Formula**:
- `floor_price` = `original_price × 0.45`
- `hard_cap_price` = `original_price × 1.15`

**Success Response (201)** (dari `ResaleListingResource.php`):
```json
{
  "success": true,
  "message": "Marketplace listing created successfully",
  "data": {
    "id": "01JAAAAA",
    "ticket_id": "01JXXXXX",
    "seller_id": "01JZZZZZ",
    "original_price": 500000.0,
    "current_asking_price": 500000.0,
    "floor_price": 225000.0,
    "hard_cap_price": 575000.0,
    "verification_status": "pending",
    "listing_status": "ditangguhkan",
    "listed_at": null,
    "sold_at": null,
    "created_at": "...",
    "updated_at": "..."
  }
}
```

**⚠ Penting**: Listing baru selalu mulai dengan `verification_status = pending` dan `listing_status = ditangguhkan`. Belum bisa dibeli sampai admin memverifikasi.

**Failure Scenarios**:
- `401` — unauthenticated
- `403` — bukan pemilik tiket
- `422` — ticket_id / current_asking_price tidak valid
- `422` — harga di luar range floor/hard_cap
