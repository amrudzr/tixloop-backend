# TixLoop Final API Documentation v1

Dokumen ini merupakan sumber kebenaran utama (*Source of Truth*) untuk integrasi antarmuka klien (Frontend) dengan backend TixLoop. 

**Base URL**: `https://<domain>/api/v1`  
**Format Standar Respons**: Selalu mengembalikan objek JSON dengan properti `success`, `message`, dan `data` (atau `errors` jika gagal). Seluruh pesan menggunakan Bahasa Indonesia.

---

## 1. Authentication

### 1.1 Register User [PENTING]
Pendaftaran pengguna baru ke dalam sistem.

- **URL**: `/auth/register`
- **Method**: `POST`
- **Authentication**: Tidak perlu
- **Request Body**:
  ```json
  {
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "phone": "08123456789",
    "password": "password123",
    "password_confirmation": "password123"
  }
  ```
- **Response Success (201)**: `success: true`, mengembalikan profil `user` dan `access_token` untuk langsung login.
- **Validation Error (422)**: Email sudah terdaftar, Password kurang dari 8 karakter, Konfirmasi password tidak cocok.
- **Catatan Integrasi Frontend**: Simpan `access_token` ke dalam state lokal atau *secure storage* aplikasi, lalu arahkan ke halaman Home.

### 1.2 Login [PENTING]
Autentikasi pengguna untuk mendapatkan token akses.

- **URL**: `/auth/login`
- **Method**: `POST`
- **Authentication**: Tidak perlu
- **Request Body**:
  ```json
  {
    "email": "budi@example.com",
    "password": "password123"
  }
  ```
- **Response Success (200)**: `success: true`, mengembalikan profil `user` dan `access_token`.
- **Response Error (422)**: "Email atau kata sandi yang Anda masukkan salah."
- **Catatan Integrasi Frontend**: Jika pengguna melebihi 5 kali percobaan gagal, API akan mengembalikan status **429 (Terlalu Banyak Permintaan)**.

### 1.3 Ambil Profil Pengguna (Me) [PENTING]
Mendapatkan data pengguna yang sedang *login*.

- **URL**: `/auth/me`
- **Method**: `GET`
- **Authentication**: Bearer Token
- **Response Success (200)**: Mengembalikan objek profil user.

### 1.4 Logout
Keluar dari sistem dan menghancurkan token akses saat ini.

- **URL**: `/auth/logout`
- **Method**: `POST`
- **Authentication**: Bearer Token
- **Response Success (200)**: `message: "Berhasil keluar."`

---

## 2. Event

### 2.1 Daftar Event [BARU] [PENTING]
Mengambil daftar acara/event yang tersedia. Diperlukan untuk Dropdown saat Upload Tiket.

- **URL**: `/events`
- **Method**: `GET`
- **Authentication**: Tidak perlu
- **Query Parameter**: `search` (opsional, untuk mencari nama acara/kota)
- **Response Success (200)**: Array ber-paginasi (dilengkapi `links` dan `meta`) yang berisi id event, nama event, kategori, lokasi, dll.
- **Catatan Integrasi Frontend**: Wajib dipanggil pada form Upload Tiket untuk memetakan ID event yang dipilih pengguna.

---

## 3. Tiket (Ticket Wallet)

### 3.1 Daftar Tiket Saya [PENTING]
Melihat tiket yang saat ini dimiliki oleh pengguna (*My Wallet*).

- **URL**: `/tickets`
- **Method**: `GET`
- **Authentication**: Bearer Token
- **Response Success (200)**: Array paginasi dari tiket-tiket pengguna dengan status (aktif, digunakan, hangus).

### 3.2 Upload Tiket [PENTING]
Menambahkan tiket baru ke sistem.

- **URL**: `/tickets/upload`
- **Method**: `POST`
- **Authentication**: Bearer Token
- **Format Content**: `multipart/form-data`
- **Request Body**:
  - `event_id` (string, ULID dari endpoint `/events`)
  - `ticket_code` (string, kode rahasia tiket)
  - `original_price` (numeric, harga asli saat dibeli)
  - `ticket_proof` (file: jpg/png/pdf, maks 2MB)
  - `ticket_type` (opsional, string, maks 50 karakter) -> **[BARU]**
  - `seat_number` (opsional, string)
  - `physical_photo` (opsional, file gambar tiket fisik)
- **Response Success (201)**: Tiket berhasil diunggah.
- **Validation Error (422)**: Terjadi jika tipe file tidak didukung atau melebihi 2MB.

### 3.3 Riwayat Kepemilikan Tiket [BARU] [PENTING]
Melihat jejak perpindahan tangan (kronologi transaksi) dari sebuah tiket.

- **URL**: `/tickets/{id}/history`
- **Method**: `GET`
- **Authentication**: Bearer Token (Hanya pemilik tiket saat ini atau Admin yang dapat mengakses)
- **Response Success (200)**: Array kronologis yang berisi riwayat dengan relasi `previous_owner` dan `new_owner`.
- **Response Error (403)**: Jika akun lain (yang bukan pemilik tiket) mencoba mengakses riwayat ini.
- **Catatan Integrasi Frontend**: Pada entri riwayat cetakan pertama (tiket baru diupload dan belum pernah dijual), nilai `previous_owner` akan me-return `null`. Frontend dapat me-render ini sebagai "Pemilik Pertama".

---

## 4. Jual Beli Tiket (Marketplace)

### 4.1 Browse Tiket [PENTING]
Melihat daftar tiket yang ditawarkan untuk dijual kepada publik.

- **URL**: `/marketplace/listings`
- **Method**: `GET`
- **Authentication**: Tidak perlu
- **Query Parameter**: `search` (opsional)
- **Response Success (200)**: Array tiket yang hanya berstatus `aktif` dan sudah diverifikasi admin.
- **Catatan Integrasi Frontend**: Response ini ber-paginasi, namun mengembalikan parameter `meta` buatan khusus tanpa property `links`. Gunakan `meta.current_page` dan `meta.last_page` untuk kontrol navigasi.

### 4.2 Jual Tiket Saya (Buat Penawaran) [PENTING]
Penjual menawarkan tiket miliknya ke Marketplace.

- **URL**: `/marketplace/listings`
- **Method**: `POST`
- **Authentication**: Bearer Token
- **Request Body**:
  ```json
  {
    "ticket_id": "01JXXXX...",
    "current_asking_price": 400000
  }
  ```
- **Response Success (201)**: "Tiket berhasil ditawarkan untuk dijual. Menunggu verifikasi admin." Status menjadi `ditangguhkan` (pending).
- **Validation Error (422)**: 
  - Harga tidak valid. Harga jual **harus** berada di kisaran 45% hingga 115% dari `original_price` (Aturan ketat).
  - Tiket sedang dalam proses transaksi lain.
- **Catatan Integrasi Frontend**: Cegah pengguna memasukkan harga ngawur sebelum menekan submit dengan memberikan helper text rentang harga.

### 4.3 Daftar Penjualan Saya (Seller Dashboard) [BARU] [PENTING]
Penjual memonitor status inventaris tiket yang sedang ditawarkannya.

- **URL**: `/marketplace/my-listings`
- **Method**: `GET`
- **Authentication**: Bearer Token
- **Response Success (200)**: Daftar listing milik penjual dengan status `pending`, `aktif`, `ditarik`, atau `terjual`.
- **Catatan Integrasi Frontend**: Jika admin menolak tiket (status `rejected`), properti `rejection_reason` akan terisi. Frontend perlu memunculkan tombol "Jual Kembali" untuk listing yang ditolak.

---

## 5. Transaksi (Checkout & Penyelesaian)

### 5.1 Beli Tiket (Checkout) [PENTING]
Pembeli menginisiasi pembelian atas tiket yang ada di marketplace.

- **URL**: `/marketplace/listings/{id}/checkout`
- **Method**: `POST`
- **Authentication**: Bearer Token (Pembeli)
- **Response Success (201)**: Transaksi terbuat dengan status `pending`.
- **Response Error (403)**: "Anda tidak dapat membeli tiket Anda sendiri." (Penjual dilarang membeli tiket miliknya sendiri).

### 5.2 Simulasi Pembayaran (Dana Ditahan / Phase 1) [PENTING]
Pembeli mensimulasikan pembayaran tiket.

- **URL**: `/transactions/{id}/simulate-payment`
- **Method**: `POST`
- **Authentication**: Bearer Token (Pembeli)
- **Response Success (200)**: "Simulasi pembayaran berhasil. Dana ditahan sementara."
- **Catatan Integrasi Frontend**: Transaksi kini berubah status menjadi `status: "paid"` dan `escrow_status: "held"`. Tiket **BELUM** berpindah kepemilikan.

### 5.3 Penyelesaian Transaksi (Pelepasan Dana / Phase 2) [BARU] [PENTING]
Pembeli mengkonfirmasi penerimaan tiket dan melepaskan dana ke penjual.

- **URL**: `/transactions/{id}/release-escrow`
- **Method**: `POST`
- **Authentication**: Bearer Token (Pembeli)
- **Response Success (200)**: "Pembayaran diteruskan. Kepemilikan tiket berhasil dipindahkan."
- **Response Error (403)**: Jika penjual atau admin mencoba menekan tombol ini.
- **Catatan Integrasi Frontend**: Panggil endpoint ini saat pembeli menekan tombol "Konfirmasi Terima Tiket". Pastikan memberikan loading state yang jelas untuk mencegah double-click (*race condition*).

### 5.4 Riwayat Transaksi Saya [BARU] [PENTING]
Daftar riwayat transaksi (baik sebagai pembeli maupun penjual).

- **URL**: `/transactions`
- **Method**: `GET`
- **Authentication**: Bearer Token
- **Response Success (200)**: Array paginasi riwayat transaksi.

---

## 6. Verifikasi Admin

### 6.1 Daftar Tiket Menunggu Verifikasi
Admin melihat tiket yang didaftarkan penjual.

- **URL**: `/admin/listings`
- **Method**: `GET`
- **Authentication**: Bearer Token (Admin)

### 6.2 Setujui Tiket
- **URL**: `/admin/listings/{id}/verify`
- **Method**: `POST`
- **Authentication**: Bearer Token (Admin)
- **Response Success (200)**: Listing menjadi aktif di marketplace.

### 6.3 Tolak Tiket
- **URL**: `/admin/listings/{id}/reject`
- **Method**: `POST`
- **Authentication**: Bearer Token (Admin)
- **Request Body**: `{"reason": "Bukti pembelian blur"}`

---

## 7. Dashboard Dampak

### 7.1 Data Perputaran Tiket (Waste) [PENTING]
Statistik sampah tiket dan perputaran ekonomi.

- **URL**: `/dashboard/waste`
- **Method**: `GET`
- **Authentication**: Tidak perlu
- **Response Success (200)**: Mengembalikan total volume sekunder, total tiket terjual ulang, tiket yang hangus (expired), dan estimasi karbon.

### 7.2 Pencegahan Tiket Hangus (Burn Prevention) [PENTING]
Statistik alokasi ulang tiket.

- **URL**: `/dashboard/burn-prevention`
- **Method**: `GET`
- **Authentication**: Tidak perlu (Mendukung Auth Opsional jika disertakan)

---

# Perubahan Penting Recovery Sprint

Bagian ini merangkum hal-hal krusial yang diubah selama *Recovery Sprint* dan **WAJIB** disesuaikan oleh tim Frontend:

1. **Endpoint Baru**
   - Pencarian Event (`GET /events`) harus langsung di-plug ke komponen Form Upload.
   - Seller Dashboard (`GET /marketplace/my-listings`) harus diimplementasi agar penjual bisa memantau status tiketnya (Termasuk yang ditolak admin).
   - Ticket History / Provenance (`GET /tickets/{id}/history`) untuk tab kronologi transaksi.

2. **Field Baru**
   - **`ticket_type`**: Frontend bisa menambahkan input teks opsional ini di Form Upload Tiket (contoh: "VIP", "CAT 1").
   - **`escrow_status`**: Properti ini kini tersemat di semua respon Transaksi, dengan nilai `held` (Dana Ditahan) atau `released` (Penyelesaian Transaksi).

3. **Flow Transaksi Terbaru (Dua Fase / Two-Phase Escrow)**
   - Proses transaksi **TIDAK LAGI** selesai hanya dalam satu kali klik. 
   - Klik bayar (`/simulate-payment`) hanya memindahkan dana ke penampungan sementara (*Dana Ditahan*).
   - Tiket baru dikirimkan ke dompet pembeli **setelah** pembeli mengklik konfirmasi terima tiket yang akan memanggil endpoint `POST /transactions/{id}/release-escrow` (*Penyelesaian Transaksi*).

4. **Hal yang Wajib Diperhatikan Frontend**
   - Seluruh teks *error/success* dari API sekarang sepenuhnya berbahasa Indonesia yang siap tampil di *toast notification*. Tidak ada lagi pesan bahasa Inggris.
   - Paginasi data list harus menggunakan pembacaan objek JSON dari `meta.current_page` dan `meta.last_page` untuk merender navigasi halaman (karena struktur `links` root telah ditiadakan pada beberapa route marketplace).
   - Perhatikan *error 403 Forbidden* pada tahap Pembelian. Sistem akan menolak jika pengguna lupa melakukan *switch account* saat mencoba meng-checkout tiket yang dijualnya sendiri.
