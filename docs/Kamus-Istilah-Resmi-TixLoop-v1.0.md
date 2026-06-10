# Kamus Istilah Resmi TixLoop v1.0

Kamus ini adalah **Standar Wajib (Single Source of Truth)** bagi seluruh tim TixLoop (Developer, QA, UI/UX, Product, dan Bisnis). Tujuannya adalah memastikan bahwa semua bahasa yang berhadapan dengan pengguna (UI, Frontend Notification, Backend Response, Dokumentasi Pengguna, Proposal, dan Presentasi) menggunakan bahasa Indonesia yang sangat sederhana, konsisten, dan dapat dipahami seketika oleh pengguna non-teknis berusia 17 tahun.

---

## 1. Final Product Terminology (Istilah Terlarang & Penggantinya)

Istilah teknis/bahasa Inggris di bawah ini **dilarang keras** untuk ditampilkan kepada pengguna akhir. Gunakan padanan resminya di semua medium komunikasi.

| Istilah Terlarang (Dihindari) | Istilah Resmi TixLoop (Wajib Digunakan) |
| :--- | :--- |
| **Escrow** | Dana Ditahan / Dana Diteruskan |
| **Listing** | Tiket Dijual |
| **Marketplace** | Jual Beli Tiket |
| **Ledger** | Buku Catatan Transaksi |
| **Ownership Transfer** | Serah Terima Tiket |
| **Ownership History** | Riwayat Kepemilikan |
| **Burn Prevention** | Pencegahan Tiket Hangus |
| **Circular Economy** | Perputaran Tiket |
| **Checkout** | Pembayaran / Beli Tiket |
| **Refund** | Pengembalian Dana |
| **Dashboard Waste** | Dashboard Dampak TixLoop |

---

## 2. Status Badge Standard

Status variabel database bahasa Inggris harus dipetakan ke dalam antarmuka (Badge / Label) pengguna seperti ini:

| Internal Value (Database) | Tampilan Pengguna |
| :--- | :--- |
| `held` | Dana Ditahan |
| `released` | Dana Diteruskan |
| `completed` | Selesai |
| `pending` | Menunggu |
| `verified` | Terverifikasi |
| `rejected` | Ditolak |
| `active` | Aktif |
| `withdrawn` | Ditarik |
| `sold` | Terjual |

---

## 3. HTTP Status Message Standard

Seluruh keluaran error dari Backend API (baik melalui _Exception Handler_ maupun _Middleware_) harus dikonversi menjadi pesan standar berikut:

| HTTP Code | Kondisi Error | Pesan Standar TixLoop |
| :--- | :--- | :--- |
| **401** | *Unauthenticated* | Sesi Anda telah berakhir. Silakan masuk kembali. |
| **403** | *Forbidden* | Anda tidak memiliki akses untuk melakukan tindakan ini. |
| **404** | *Not Found* | Data yang Anda cari tidak ditemukan. |
| **422** | *Validation Error* | Terdapat kesalahan pada isian formulir. Mohon periksa kembali. |
| **429** | *Too Many Requests* | Terlalu banyak permintaan. Silakan tunggu beberapa saat. |
| **500** | *Internal Server Error*| Terjadi kesalahan pada server. Silakan coba lagi nanti. |

---

## 4. Network & Connectivity Message Standard

Ketika Frontend tidak dapat menghubungi Backend (error pada Axios/Fetch), pesan yang ditampilkan harus mengikuti standar berikut:

| Skenario Error | Pesan Standar TixLoop |
| :--- | :--- |
| **Tidak ada internet** (`offline`) | Periksa koneksi internet Anda. |
| **Timeout** (`ECONNABORTED`) | Koneksi terputus karena terlalu lama. Silakan coba lagi. |
| **Server tidak dapat dijangkau** (`ERR_NETWORK`) | Gagal menghubungi server. Pastikan Anda terhubung ke internet. |
| **Server sedang gangguan** (502/503) | Server sedang mengalami gangguan sementara. Silakan coba beberapa saat lagi. |
| **Request dibatalkan** (`canceled`) | Permintaan dibatalkan. |

---

## 5. Frontend Notification Standard

Sistem notifikasi (Toast/Snackbar/Alert) harus memiliki pemisahan yang jelas antara **Judul** dan **Pesan**, serta menghindari pesan teknis (*stack trace* atau *system error*).

| Jenis Toast | Judul Standar | Panduan Pesan |
| :--- | :--- | :--- |
| **Success Toast** | **Berhasil** | Gunakan kalimat aktif dan positif. (Contoh: *"Tiket berhasil ditawarkan di bursa."*) |
| **Error Toast** | **Gagal** | Jelaskan masalah tanpa menyalahkan pengguna atau menyertakan kode teknis. (Contoh: *"Terjadi kesalahan saat mengunggah tiket."*) |
| **Warning Toast** | **Perhatian** | Beri instruksi jelas apa yang harus dilakukan pengguna. (Contoh: *"Harap lengkapi nomor kursi sebelum melanjutkan."*) |
| **Info Toast** | **Informasi** | Gunakan untuk status proses yang sedang berjalan. (Contoh: *"Sedang memproses simulasi pembayaran."*) |

---

## 6. Dokumentasi API Localization

Meskipun _field key_ (contoh: `status: "held"`) tetap menggunakan bahasa Inggris agar _developer_ mudah melakukan integrasi, **dokumentasi API (Swagger/Markdown)** wajib:
1. Menyertakan terjemahan *Kamus Istilah Resmi* pada bagian _description_.
2. Mewajibkan bahwa respons `message` dari API dikirimkan sepenuhnya dalam bahasa Indonesia.

**Contoh Format JSON Standar:**
```json
{
  "success": true,
  "message": "Pembayaran diteruskan. Tiket berhasil dipindah tangan.",
  "data": {
    "status": "released"
  }
}
```
*(Catatan: Developer Frontend diwajibkan menampilkan isi `message` di atas ke layar, bukan membuat pesan hardcoded baru).*

---
*Dokumen ini merupakan standar baku (SOP) TixLoop v1.0. Dilarang menggunakan istilah di luar panduan ini tanpa persetujuan tim produk.*
