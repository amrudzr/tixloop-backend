# TixLoop Glossary

Dokumen ini menjadi sumber istilah resmi untuk seluruh ekosistem TixLoop.

---

# 🎟️ Ticket Ownership

Definisi:
Status kepemilikan resmi suatu tiket oleh user tertentu di dalam sistem TixLoop.

Catatan:
- ownership dapat berpindah
- ownership harus traceable
- ownership bukan sekadar data pembelian

Digunakan pada:
- backend
- escrow
- venue validation
- analytics

---

# 🔄 Circular Ticketing

Definisi:
Konsep bahwa tiket dapat terus digunakan dan dipindahkan secara aman sebelum event berlangsung, sehingga mengurangi ticket waste.

Core Principle:
“Keep Tickets Moving.”

---

# 🔥 Burn Prevention Engine

Definisi:
Sistem penyesuaian harga otomatis yang menurunkan harga resale mendekati hari event untuk mengurangi risiko tiket hangus.

Tujuan:
- mengurangi ticket waste
- meningkatkan recovery rate
- menciptakan harga lebih fair

---

# 🔐 Escrow

Definisi:
Mekanisme penahanan dana sementara hingga validasi transfer tiket berhasil dilakukan.

Tujuan:
- mencegah fraud
- melindungi buyer
- melindungi seller

---

# 🛒 Marketplace Listing

Definisi:
Representasi tiket yang dipublikasikan pada marketplace untuk dijual kembali.

Berisi:
- harga resale
- seller
- ticket metadata
- countdown
- verification status

---

# 🧾 Original Ticket Price

Definisi:
Harga resmi tiket ketika pertama kali dijual oleh Event Organizer atau platform primary ticketing.

Digunakan untuk:
- anti-scalper logic
- fair pricing
- burn prevention calculation

---

# 🚫 Scalping

Definisi:
Praktik menjual tiket dengan markup tidak wajar untuk mengambil keuntungan berlebihan.

Status di TixLoop:
Dibatasi melalui:
- max markup
- pricing validation
- behavioral monitoring

---

# 🪪 Verification

Definisi:
Proses validasi identitas user atau validasi keaslian tiket.

Jenis:
- user verification
- seller verification
- ticket verification

---

# 📊 Recovery Rate

Definisi:
Persentase tiket resale yang berhasil terjual kembali sebelum event berlangsung.

Formula:
Recovered Tickets / Total Listed Tickets

---

# 📉 Ticket Waste

Definisi:
Tiket yang tidak digunakan hingga event selesai berlangsung.

Target utama TixLoop:
mengurangi ticket waste melalui resale circulation.

---

# 🔁 Ticket Transfer

Definisi:
Perpindahan ownership tiket dari seller ke buyer melalui mekanisme marketplace dan escrow.

Catatan:
Transfer harus:
- traceable
- validated
- auditable

---

# ⚙️ State Machine

Definisi:
Pendekatan backend yang mengatur perubahan status tiket dan transaksi berdasarkan lifecycle tertentu.

Contoh Ticket State:
- CREATED
- LISTED
- RESERVED
- ESCROW
- TRANSFERRED
- CONSUMED

---

# 🧠 Trust Enforcement System

Definisi:
Pendekatan backend TixLoop yang berfokus pada menjaga integritas transaksi, ownership, dan validasi resale tiket.

Backend TixLoop bukan sekadar CRUD marketplace.

---

# 🏷️ Verified Seller

Definisi:
Seller yang telah melewati proses verifikasi tertentu sehingga memiliki tingkat kepercayaan lebih tinggi.

---

# 📍 Venue Validation

Definisi:
Proses validasi tiket ketika digunakan pada venue/event.

Tujuan:
- memastikan ownership valid
- mencegah duplicate entry
- memastikan escrow dapat diselesaikan

---

# 📈 Waste Dashboard

Definisi:
Dashboard analytics yang menunjukkan dampak circular ticketing dan pengurangan ticket waste.

Contoh Metrics:
- tickets rescued
- waste prevented
- recovery rate
- active resale

---

# 🧩 Modular Monolith

Definisi:
Pendekatan arsitektur backend dimana seluruh sistem berada dalam satu codebase Laravel namun dipisahkan berdasarkan domain dan module boundaries.

Tujuan:
- maintainability
- development speed
- MVP simplicity