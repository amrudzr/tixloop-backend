# Frontend Recovery Sprint Plan (3 Hari)

**Dokumen Referensi**: `frontend-integration-readiness.md`
**Objective**: Mencapai End-to-End MVP Demo Readiness untuk TixLoop dengan mengintegrasikan 5 flow utama backend yang sudah siap.

---

## 📅 Hari 1: Input Data & Dashboard Penjual

### 1. Event Selector (Upload Ticket Blocker)
Komponen ini memblokir user untuk mengupload tiket (BE-01).

- **Endpoint**: `GET /api/v1/events`
- **Komponen**: `EventSelectorDropdown`, `UploadTicketForm`
- **State**: 
  - `events`: Array of objects.
  - `isLoading`: Boolean.
  - `selectedEventId`: String (ULID).
- **Acceptance Criteria**:
  - Dropdown menampilkan daftar event (`event_name`, `event_datetime`, `venue_name`).
  - Menampilkan state loading saat fetching data dari API.
  - Saat form di-submit, `event_id` harus disertakan dalam payload `POST /api/v1/tickets/upload` beserta field baru opsional `ticket_type`.
  - Handle empty state (jika belum ada event di database).
- **Estimasi Jam**: 3 Jam

### 2. Seller Dashboard (My Listings)
Fitur inti untuk penjual memonitor status inventaris tiketnya (BE-04).

- **Endpoint**: `GET /api/v1/marketplace/my-listings`
- **Komponen**: `SellerDashboardPage`, `ListingCard`, `StatusBadge`
- **State**: 
  - `listings`: Array of objects.
  - `isLoading`: Boolean.
  - `error`: String / Null.
- **Acceptance Criteria**:
  - Halaman diproteksi middleware (hanya bisa diakses jika sudah login).
  - Render list `ListingCard` dengan data: harga, nama event, dan seat number.
  - **Badge Mapping**: 
    - `pending` → Pending Review (Kuning)
    - `aktif` → Active (Hijau)
    - `terjual` → Sold (Biru)
    - `ditarik` → Withdrawn (Abu-abu)
  - **Rejection Handling**: Jika `verification_status === "rejected"`, tampilkan warning box berisi `rejection_reason` dan tombol "Re-list".
- **Estimasi Jam**: 5 Jam

---

## 📅 Hari 2: Transaksi & Escrow State

### 3. Transaction History
Menampilkan daftar transaksi user sebagai pembeli maupun penjual (BE-09).

- **Endpoint**: `GET /api/v1/transactions`
- **Komponen**: `TransactionHistoryPage`, `TransactionCard`, `Pagination`
- **State**:
  - `transactions`: Array of objects (diambil dari `response.data`).
  - `paginationMeta`: Object (diambil dari `response.meta` - current_page, last_page).
  - `isLoading`: Boolean.
- **Acceptance Criteria**:
  - Render list transaksi beserta relasi nama lawan transaksi (`buyer.name` / `seller.name`) dan nama event.
  - Implementasi Pagination (Next/Prev page) membaca atribut root `meta` dari JSON response.
  - Tampilkan status pembayaran (`status`) dan status penahanan dana (`escrow_status`).
  - Klik card mengarah ke halaman Transaction Detail.
- **Estimasi Jam**: 5 Jam

### 4. Release Escrow Flow (Phase 2 Escrow)
Membuka kunci dana ke penjual dan mentransfer hak milik tiket ke pembeli (BE-08).

- **Endpoint**: `POST /api/v1/transactions/{id}/release-escrow`
- **Komponen**: `TransactionDetailPage`, `ReleaseEscrowCTA`, `EscrowStatusBanner`
- **State**:
  - `isReleasing`: Boolean (untuk mencegah double-click / race condition).
  - `transactionDetail`: Object (local detail state).
- **Acceptance Criteria**:
  - Pada halaman detail, jika `escrow_status === "held"`, tampilkan banner/tombol untuk "Konfirmasi Terima Tiket / Lepas Dana".
  - Saat diklik, tombol disabled & menunjukkan state loading (AbortController/race-safe pattern).
  - Setelah sukses (200 OK), update state lokal secara optimistik: `escrow_status` menjadi `"released"` dan `status` menjadi `"completed"`.
  - Tampilkan toast/notifikasi sukses.
- **Estimasi Jam**: 3 Jam

---

## 📅 Hari 3: Provenance & E2E Validation

### 5. Ownership History (Ticket Provenance)
Menampilkan jejak riwayat kepemilikan tiket berbasis blockchain-like ledger (BE-12).

- **Endpoint**: `GET /api/v1/tickets/{id}/history`
- **Komponen**: `TicketDetailPage`, `OwnershipHistoryTab`, `TimelineItem`
- **State**:
  - `historyData`: Array of objects.
  - `isLoading`: Boolean.
  - `activeTab`: String (`'details'` | `'history'`).
- **Acceptance Criteria**:
  - Pada halaman Ticket Detail, tambahkan tab navigasi "Provenance / Riwayat".
  - Fetch data saat tab aktif. Tampilkan UI error state 403 gracefully jika user yang sedang login bukan pemilik tiket saat ini (atau bukan admin).
  - Render riwayat secara kronologis menggunakan desain Timeline.
  - Tampilkan teks "First Owner" jika `previous_owner` bernilai `null`, lalu tampilkan `new_owner.name`.
- **Estimasi Jam**: 4 Jam

### 6. Buffer & End-to-End Testing
QA dan finalisasi alur secara keseluruhan.

- **Fokus Pengujian**:
  - Flow Jual: Upload (pilih event) → Admin Verify → Masuk Seller Dashboard.
  - Flow Beli: Checkout → Simulate Payment (Phase 1) → Release Escrow (Phase 2) → Cek Ownership History.
- **Estimasi Jam**: 4 Jam

---

## Catatan Tambahan (Frontend API Integration Patterns)
- **Error Handling**: Gunakan class standard untuk menangkap struktur error Laravel (validasi 422 vs internal 500).
- **Mencegah Double Requests**: Gunakan pola disabled loading state atau `AbortController` terutama pada POST `/release-escrow`.
- **Top-level Data Access**: Perhatikan bahwa untuk list tersandardisasi, array dikembalikan langsung pada root `data` dan paginasi pada `meta` (hindari ekspektasi `response.data.data` yang lawas).
