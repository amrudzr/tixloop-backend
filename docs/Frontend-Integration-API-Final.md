# Dokumentasi API Frontend TixLoop (FINAL)

Dokumen ini menjadi referensi utama tim frontend saat melakukan integrasi dengan backend. Dokumen ini disusun berdasarkan source code backend aktual pada *Recovery Sprint*.

---

## 🆕 BARU: Endpoint Event

### 1. Dapatkan Daftar Event
#### URL
`/api/v1/events`
#### Method
`GET`
#### Perlu Login?
Tidak
#### Hak Akses
Publik
#### Parameter Request
- `search` (opsional, string): Mencari nama event, venue, atau kota.
- `category` (opsional, string): Filter kategori event.
- `city` (opsional, string): Filter kota event.
- `per_page` (opsional, integer): Jumlah item per halaman (maksimal 50, default 15).
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Events retrieved successfully",
  "data": [
    {
      "id": "01HXXXXX...",
      "event_name": "Konser Musik",
      "venue_name": "Stadion Utama",
      "city": "Jakarta",
      "event_datetime": "2026-10-10 19:00:00"
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```
#### Contoh Response Gagal
Tidak ada (selalu mengembalikan array kosong jika tidak ditemukan).
#### Aturan Validasi
- `search`: string, maksimal 255 karakter.
- `per_page`: integer, minimal 1, maksimal 50.
#### Catatan Integrasi Frontend
Paginasi menggunakan standar Laravel API Resource. Response meta berada di luar `data`, formatnya langsung `response.meta` dan `response.data`.

### 2. Detail Event
#### URL
`/api/v1/events/{id}`
#### Method
`GET`
#### Perlu Login?
Tidak
#### Hak Akses
Publik
#### Parameter Request
- `id` (path): ID event (ULID).
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Event retrieved successfully",
  "data": {
    "id": "01HXXXXX...",
    "event_name": "Konser Musik"
  }
}
```
#### Contoh Response Gagal
```json
{
  "success": false,
  "message": "Model not found."
}
```
#### Aturan Validasi
- `id` harus valid dan ada di tabel `events`.
#### Catatan Integrasi Frontend
Gunakan endpoint ini untuk memuat detail spesifik event pada halaman upload tiket jika diperlukan.

---

## 🆕 BARU: Dashboard Penjual (My Listings)

### 3. Daftar Listing Milik Penjual
#### URL
`/api/v1/marketplace/my-listings`
#### Method
`GET`
#### Perlu Login?
Ya
#### Hak Akses
Pengguna Terautentikasi (Penjual)
#### Parameter Request
- `status` (opsional, string): Filter status listing (misal: `aktif`, `ditolak`, `terjual`).
- `per_page` (opsional, integer): Jumlah item (default 15).
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Seller listings retrieved successfully",
  "data": [ ... ],
  "meta": { "current_page": 1, "last_page": 1, "total": 10 }
}
```
#### Contoh Response Gagal
```json
{
  "message": "Unauthenticated."
}
```
#### Aturan Validasi
Divalidasi dari parameter query string.
#### Catatan Integrasi Frontend
Gunakan ini untuk Seller Dashboard. Berbeda dengan `/api/v1/marketplace/listings` yang untuk pembeli, endpoint ini juga menampilkan listing yang `ditolak` beserta alasannya.

---

## 🆕 BARU: Riwayat Transaksi

### 4. Daftar Transaksi
#### URL
`/api/v1/transactions`
#### Method
`GET`
#### Perlu Login?
Ya
#### Hak Akses
Pengguna Terautentikasi (sebagai pembeli atau penjual)
#### Parameter Request
Tidak ada.
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Transaction history retrieved successfully",
  "data": [
    {
      "id": "01HYYYYY...",
      "status": "held",
      "ticket": { ... }
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```
#### Contoh Response Gagal
```json
{
  "message": "Unauthenticated."
}
```
#### Aturan Validasi
Tidak ada.
#### Catatan Integrasi Frontend
Format respons paginasi telah diperbarui. Gunakan `response.data` (array data) dan `response.meta` (informasi halaman). **Bukan** `response.data.data`.

### 5. Selesaikan Transaksi (Release Escrow)
#### URL
`/api/v1/transactions/{id}/release-escrow`
#### Method
`POST`
#### Perlu Login?
Ya
#### Hak Akses
Pembeli dari transaksi tersebut.
#### Parameter Request
- `id` (path): ID transaksi.
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Escrow released. Ownership transferred.",
  "data": { ... }
}
```
#### Contoh Response Gagal
```json
{
  "message": "This action is unauthorized."
}
```
#### Aturan Validasi
Transaksi harus berstatus `held` (escrow aktif) dan pengguna harus pembeli yang sah.
#### Catatan Integrasi Frontend
Tombol "Selesaikan Transaksi" di Frontend hanya memanggil endpoint ini. Ini adalah langkah tambahan wajib agar dana diteruskan ke penjual dan tiket sah berpindah kepemilikan.

---

## 🆕 BARU: Riwayat Kepemilikan Tiket

### 6. Riwayat Perpindahan Tiket
#### URL
`/api/v1/tickets/{id}/history`
#### Method
`GET`
#### Perlu Login?
Ya
#### Hak Akses
Pemilik tiket saat ini atau Admin.
#### Parameter Request
- `id` (path): ID tiket.
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Ticket ownership history retrieved successfully",
  "data": [
    {
      "previous_owner": { "id": "1", "name": "Budi" },
      "new_owner": { "id": "2", "name": "Andi" },
      "transferred_at": "2026-06-10 15:00:00"
    }
  ]
}
```
#### Contoh Response Gagal
```json
{
  "message": "This action is unauthorized."
}
```
#### Aturan Validasi
Tiket harus ada dan pengguna harus berwenang melihatnya.
#### Catatan Integrasi Frontend
Data diurutkan dari perpindahan terbaru ke yang terlama. Tampilkan ini pada komponen *Ownership History* di halaman detail tiket milik pengguna.

---

## Endpoint Existing (Disempurnakan)

### 7. Upload Tiket
#### URL
`/api/v1/tickets/upload`
#### Method
`POST`
#### Perlu Login?
Ya
#### Hak Akses
Pengguna Terautentikasi
#### Parameter Request
Kosong (Gunakan Form-Data)
#### Body Request (Form-Data)
- `event_id` (string, wajib)
- `ticket_code` (string, wajib)
- `seat_number` (string, opsional)
- `original_price` (numeric, wajib)
- `ticket_proof` (file pdf/jpg/png, wajib)
- `physical_photo` (file jpg/png, opsional)
- `ticket_type` (string, opsional)
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Ticket uploaded successfully",
  "data": { ... }
}
```
#### Contoh Response Gagal
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "ticket_code": ["The ticket code field is required."]
  }
}
```
#### Aturan Validasi
- `event_id` harus ada di tabel `events`.
- `original_price` harus lebih besar dari 0.
- File maksimal 2MB.
#### Catatan Integrasi Frontend
`ticket_type` adalah field tambahan baru untuk mengkategorikan jenis tiket (misal: VIP, Festival). Pastikan input enctype diset ke `multipart/form-data`.

### 8. Daftar Tiket Milik Saya
#### URL
`/api/v1/tickets`
#### Method
`GET`
#### Perlu Login?
Ya
#### Hak Akses
Pengguna Terautentikasi (Pemilik Tiket)
#### Parameter Request
- `search` (opsional): Pencarian nama event.
#### Body Request
Kosong
#### Contoh Response Berhasil
```json
{
  "success": true,
  "message": "Tickets retrieved successfully",
  "data": [ ... ],
  "meta": { ... }
}
```
#### Catatan Integrasi Frontend
Ini bukan untuk mencari tiket yang dijual di marketplace, melainkan tiket yang *sedang dimiliki* oleh pengguna (My Tickets).

### 9. Detail Tiket
#### URL
`/api/v1/tickets/{id}`
#### Method
`GET`
#### Perlu Login?
Ya
#### Hak Akses
Pemilik tiket atau Admin.
#### Catatan Integrasi Frontend
Hanya bisa diakses jika pengguna memiliki tiket tersebut. Untuk tiket di marketplace, gunakan endpoint `/api/v1/marketplace/listings/{id}`.

### 10. Browse Marketplace Listings
#### URL
`/api/v1/marketplace/listings`
#### Method
`GET`
#### Perlu Login?
Tidak
#### Parameter Request
- `search` (string, opsional)
- `per_page` (integer, opsional)
- `deals` (boolean, opsional) -> **[BARU]** Filter untuk menampilkan Deals Lite.
#### Catatan Integrasi Frontend
Endpoint publik untuk mencari tiket yang sedang dijual (hanya yang berstatus `aktif` dan diverifikasi `verified`).

### 11. Jual Tiket di Marketplace
#### URL
`/api/v1/marketplace/listings`
#### Method
`POST`
#### Perlu Login?
Ya
#### Body Request
- `ticket_id` (string, wajib)
- `current_asking_price` (numeric, wajib)
- `is_auto_drop` (boolean, opsional) -> **[BARU]**
- `floor_price` (numeric, opsional) -> **[BARU]**
#### Catatan Integrasi Frontend
Mengubah status tiket menjadi `listed` dan mendaftarkan ke marketplace. Tiket otomatis masuk antrean verifikasi admin. `floor_price` wajib diisi jika `is_auto_drop` bernilai `true`.

### 12. Checkout Listing
#### URL
`/api/v1/marketplace/listings/{id}/checkout`
#### Method
`POST`
#### Perlu Login?
Ya
#### Catatan Integrasi Frontend
Mereservasi tiket. Membuat record transaksi baru dengan status `pending`.

### 13. Simulasi Pembayaran
#### URL
`/api/v1/transactions/{id}/simulate-payment`
#### Method
`POST`
#### Perlu Login?
Ya
#### Catatan Integrasi Frontend
Memicu Phase 1 dari Escrow. Status transaksi menjadi `held`.

### 14. Detail Transaksi
#### URL
`/api/v1/transactions/{id}`
#### Method
`GET`
#### Perlu Login?
Ya
#### Catatan Integrasi Frontend
Mendapatkan info detail transaksi untuk menampilkan invoice atau halaman sukses bayar.

### 15. Dashboard: Waste
#### URL
`/api/v1/dashboard/waste`
#### Method
`GET`
#### Perlu Login?
Tidak
#### Parameter Request
- `event_id`, `category`, `start_date`, `end_date` (semua opsional)
#### Catatan Integrasi Frontend
Menyediakan metrik untuk SDG Dashboard.

### 16. Dashboard: Burn Prevention
#### URL
`/api/v1/dashboard/burn-prevention`
#### Method
`GET`
#### Perlu Login?
Opsional (Jika login, mengembalikan daftar `user_listings_at_risk`)
#### Parameter Request
- `event_id` (opsional)
#### Catatan Integrasi Frontend
Digunakan bersama dashboard waste untuk melacak tiket yang berisiko tidak terjual (hangus).

---

# Perubahan Penting Sejak Dokumentasi Sebelumnya

1. **Setelah pembayaran berhasil, transaksi belum langsung selesai.**
   Transaksi akan masuk ke status `held` (escrow). Penjual belum menerima uang, dan pembeli belum mendapat akses tiket penuh.
2. **Ada langkah tambahan untuk menyelesaikan transaksi dan memindahkan kepemilikan tiket.**
   Pembeli wajib memanggil POST `/api/v1/transactions/{id}/release-escrow`. Setelah itu, status menjadi `completed` dan kepemilikan tiket berpindah.
3. **GET /transactions sekarang menggunakan format paginasi yang benar:**
   Akses data array tiket di `response.data`, dan informasi halaman di `response.meta`. Bukan lagi `response.data.data`.
4. **Form Upload Tiket sekarang mendukung field `ticket_type`.**
   Frontend bisa mengirimkan kategori tambahan untuk tiket yang diupload.
5. **Riwayat perpindahan kepemilikan tiket sekarang tersedia.**
   Pengguna dapat melacak secara jelas rekam jejak tiket dari tangan ke tangan melalui endpoint history tiket.
6. **Dashboard Penjual menggunakan endpoint khusus.**
   Alih-alih menggunakan endpoint publik, penjual melihat listing mereka lewat `GET /api/v1/marketplace/my-listings`. Ini termasuk listing yang ditolak admin.
7. **Fitur Deals Lite [BARU].**
   Deals Lite hanya menyediakan penanda Deals (`is_auto_drop`) dan `floor_price` dari seller. Belum menyediakan auto drop price, scheduler, maupun dynamic pricing otomatis.

---

# Alur Integrasi Frontend

## Upload Tiket
**Pilih Event → Upload Tiket → Menunggu Verifikasi**
Frontend memanggil `GET /api/v1/events` untuk dropdown. Pengguna mengisi form dan men-submit via `POST /api/v1/tickets/upload`. Tiket masuk ke list `GET /api/v1/tickets`.

## Jual Tiket
**Tiket Disetujui → Masuk Marketplace**
Setelah tiket statusnya diverifikasi, pengguna dari menu "My Tickets" memanggil `POST /api/v1/marketplace/listings`. Tiket menunggu verifikasi listing admin, lalu muncul di `GET /api/v1/marketplace/my-listings`.

## Beli Tiket
**Pilih Tiket → Checkout → Bayar → Selesaikan Transaksi**
Pilih dari `GET /api/v1/marketplace/listings`.
Panggil `POST /api/v1/marketplace/listings/{id}/checkout`.
Bayar via `POST /api/v1/transactions/{id}/simulate-payment`.
Selesaikan lewat `POST /api/v1/transactions/{id}/release-escrow`.

## Riwayat Transaksi
**Lihat daftar transaksi milik pengguna**
Pengguna membuka menu transaksi. Frontend memanggil `GET /api/v1/transactions` untuk menampilkan daftar pembelian dan penjualan.

## Riwayat Kepemilikan Tiket
**Lihat perpindahan tiket dari pemilik lama ke pemilik baru**
Pada halaman "My Tickets", pengguna melihat detail tiket (`GET /api/v1/tickets/{id}`). Frontend memanggil tambahan `GET /api/v1/tickets/{id}/history` untuk menampilkan lini masa.

---

# Mapping Halaman Frontend

| Halaman Frontend | Endpoint yang Digunakan |
| ---------------- | ----------------------- |
| Login | `POST /api/v1/auth/login` |
| Register | `POST /api/v1/auth/register` |
| Upload Ticket | `GET /api/v1/events` <br> `POST /api/v1/tickets/upload` |
| Marketplace | `GET /api/v1/marketplace/listings` |
| Detail Ticket | `GET /api/v1/tickets/{id}` <br> `GET /api/v1/marketplace/listings/{id}` |
| Seller Dashboard | `GET /api/v1/marketplace/my-listings` |
| Checkout | `POST /api/v1/marketplace/listings/{id}/checkout` <br> `POST /api/v1/transactions/{id}/simulate-payment` |
| Transaction History | `GET /api/v1/transactions` |
| Transaction Detail | `GET /api/v1/transactions/{id}` <br> `POST /api/v1/transactions/{id}/release-escrow` |
| Ownership History | `GET /api/v1/tickets/{id}/history` |
| SDG Dashboard | `GET /api/v1/dashboard/waste` <br> `GET /api/v1/dashboard/burn-prevention` |

---

# Checklist Integrasi Frontend

### Upload Ticket
- **Endpoint:** `GET /events`, `POST /tickets/upload`
- **Data Wajib:** Dropdown event, Form input (ticket_code, original_price, bukti file, ticket_type).
- **Loading:** Tampilkan *spinner* pada tombol upload dan nonaktifkan tombol.
- **Error:** Tangkap `422` dan letakkan pesan error di bawah field yang sesuai.
- **Empty State:** Jika tidak ada event di sistem, tampilkan teks "Belum ada event tersedia".

### Seller Dashboard (My Listings)
- **Endpoint:** `GET /marketplace/my-listings`
- **Data Wajib:** Daftar listing, harga, status verifikasi, tombol buat listing (jika kosong).
- **Loading:** *Skeleton screen* daftar listing.
- **Error:** Tampilkan "Gagal memuat listing, coba lagi".
- **Empty State:** Tampilkan "Anda belum menjual tiket apapun."

### Marketplace Browse
- **Endpoint:** `GET /marketplace/listings`
- **Data Wajib:** Thumbnail event, harga tiket, kota.
- **Loading:** *Skeleton grid* tiket.
- **Error:** Toast notification "Gagal menyambung ke server."
- **Empty State:** Tampilkan ilustrasi "Tiket yang Anda cari belum tersedia."

### Checkout & Payment
- **Endpoint:** `POST /checkout`, `POST /simulate-payment`, `POST /release-escrow`
- **Data Wajib:** Rincian tiket, total harga, tombol bayar, tombol konfirmasi selesai.
- **Loading:** Tombol *disabled* dengan status "Memproses...".
- **Error:** Alert merah jika transaksi gagal, misal `403` jika tiket di-*lock* orang lain.
- **Empty State:** N/A (Halaman ini tidak muncul jika tiket kosong).

### Transaction & Ownership History
- **Endpoint:** `GET /transactions`, `GET /tickets/{id}/history`
- **Data Wajib:** Status transaksi, tanggal transaksi, nama pengguna lama/baru.
- **Loading:** Spinner daftar transaksi.
- **Error:** "Gagal memuat riwayat."
- **Empty State:** "Anda belum pernah bertransaksi" atau "Belum ada riwayat kepemilikan."
