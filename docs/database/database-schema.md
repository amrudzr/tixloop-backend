# Database Schema

## Main Tables
- users
- tickets
- resale_listings
- transactions
- escrow_records
- waste_metrics

---

# TixLoop Database Schema (3NF) — Final

## Core Identity & Authentication

### users

- id (PK, ULID)
- email (unique)
- password_hash
- phone (unique)
- created_at, updated_at, deleted_at

### user_profiles

- id (PK, ULID)
- user_id (FK → users.id, unique)
- full_name - Nama lengkap sesuai KTP
- date_of_birth - Tanggal lahir
- avatar_url
- preferred_language - Bahasa preferensi (ID/EN)
- created_at, updated_at

### user_verifications

- id (PK, ULID)
- user_id (FK → users.id, unique)
- nik_ktp (unique, varchar(16)) - Nomor Induk Kependudukan
- ktp_photo_url - URL foto KTP yang diunggah
- selfie_photo_url - URL foto selfie dengan KTP
- liveness_verified_at - Timestamp verifikasi liveness detection
- verification_status (enum) - Status: 'pending', 'diverifikasi', 'ditolak'
- verified_at
- verified_by_admin_id (FK → users.id, nullable)
- rejection_reason - Alasan penolakan verifikasi
- created_at, updated_at

### user_reputation

- id (PK, ULID)
- user_id (FK → users.id, unique)
- score (tinyint, 0-100) - Skor reputasi pengguna
- total_sales - Total tiket berhasil dijual
- total_purchases - Total tiket berhasil dibeli
- cancel_count - Jumlah pembatalan transaksi
- dispute_count - Jumlah sengketa yang terlibat
- last_calculated_at
- created_at, updated_at

### user_suspensions

- id (PK, ULID)
- user_id (FK → users.id)
- suspended_at
- suspended_until - Tanggal akhir masa penangguhan
- reason - Alasan penangguhan akun
- suspended_by_admin_id (FK → users.id)
- is_permanent (boolean) - Penangguhan permanen
- created_at

---

## Event Organizers & Events

### event_organizers

- id (PK, ULID)
- organization_name - Nama organisasi penyelenggara
- legal_entity_number - Nomor legalitas (NIB/SIUP)
- tax_number - NPWP organisasi
- official_email (unique)
- official_phone
- website_url
- verification_tier (enum) - Tingkat: 'basic', 'verified', 'official_partner'
- api_key_hash - Hash kunci API untuk integrasi
- webhook_url - URL webhook untuk notifikasi real-time
- revenue_share_percentage (decimal 5,2) - Persentase royalti (2-3%)
- verified_at
- contract_signed_at
- created_at, updated_at, deleted_at

### eo_legal_documents

- id (PK, ULID)
- organizer_id (FK → event_organizers.id)
- document_type (enum) - Jenis: 'akta_notaris', 'nib', 'siup', 'surat_kuasa'
- file_url
- expiry_date - Tanggal kadaluarsa dokumen
- uploaded_at
- verified_at
- verified_by_admin_id (FK → users.id, nullable)

### events

- id (PK, ULID)
- organizer_id (FK → event_organizers.id)
- event_name - Nama acara
- event_category (enum) - Kategori: 'konser', 'festival', 'olahraga', 'seminar', 'bioskop', 'theatre', 'komunitas'
- event_tier (enum) - Tier: 'international', 'regional', 'national', 'local', 'community'
- event_datetime - Tanggal dan waktu pelaksanaan
- venue_name - Nama lokasi venue
- venue_address - Alamat lengkap venue
- venue_latitude (decimal 10,8)
- venue_longitude (decimal 11,8)
- venue_capacity (int) - Kapasitas maksimal venue
- resale_allowed (boolean) - Izin resale tiket
- resale_max_markup_percent (decimal 5,2) - Markup maksimal yang diizinkan
- event_poster_url
- event_description (text)
- created_at, updated_at, deleted_at

---

## Ticket Management

### ticket_templates

- id (PK, ULID)
- event_id (FK → events.id)
- template_name - Nama kategori tiket (VIP, Regular, Tribune A)
- original_price (decimal 12,2) - Harga resmi dari EO
- ticket_type (enum) - Tipe: 'fisik_kertas', 'fisik_gelang', 'digital_pdf', 'digital_qr', 'digital_wallet'
- format_specifications (json) - Spesifikasi format (seat_number, barcode_type, rfid_uid)
- quantity_issued (int) - Jumlah tiket diterbitkan
- created_at, updated_at

### tickets

- id (PK, ULID)
- template_id (FK → ticket_templates.id)
- current_owner_id (FK → users.id)
- original_buyer_id (FK → users.id)
- ticket_code (varchar(50), indexed)
- seat_number (nullable)
- ticket_metadata (json)
- physical_photo_url (nullable)
- qr_secret_key (nullable)
- device_binding_id (nullable)
- status (enum) - 'aktif', 'terdaftar_jual', 'dalam_escrow', 'terjual', 'hangus', 'digunakan'
- burned_at (nullable)
- used_at (nullable)
- created_at, updated_at
- UNIQUE(ticket_code, current_owner_id)

### ticket_ownership_history

- id (PK, ULID)
- ticket_id (FK → tickets.id)
- previous_owner_id (FK → users.id, nullable)
- new_owner_id (FK → users.id)
- transferred_at
- transfer_reason (enum) - 'pembelian_resale', 'hadiah', 'penggantian_nama'
- transaction_id (FK → transactions.id, nullable)

---

## Resale Listings & Pricing

### resale_listings

- id (PK, ULID)
- ticket_id (FK → tickets.id, unique)
- event_id (FK → events.id)
- seller_id (FK → users.id)
- original_price (decimal 12,2)
- current_asking_price (decimal 12,2)
- floor_price (decimal 12,2)
- hard_cap_price (decimal 12,2)
- burn_prevention_active (boolean)
- last_price_drop_at
- auto_drop_percentage (decimal 5,2)
- auto_drop_interval_hours (int)
- listing_status (enum) - 'aktif', 'ditangguhkan', 'terjual', 'dibatalkan'
- listed_at
- sold_at (nullable)
- created_at, updated_at

### price_history_ledger

- id (PK, ULID)
- listing_id (FK → resale_listings.id)
- previous_price (decimal 12,2)
- new_price (decimal 12,2)
- change_reason (enum) - 'manual_seller', 'auto_burn_prevention', 'admin_adjustment'
- change_metadata (json)
- price_hash (char(64))
- changed_at
- changed_by_user_id (FK → users.id, nullable)

---

## Transactions & Escrow

### transactions

- id (PK, ULID)
- listing_id (FK → resale_listings.id)
- buyer_id (FK → users.id)
- seller_id (FK → users.id)
- ticket_id (FK → tickets.id)
- purchase_price (decimal 12,2)
- platform_fee (decimal 12,2)
- eo_royalty (decimal 12,2)
- seller_net_amount (decimal 12,2)
- payment_method (enum) - 'e_wallet', 'transfer_bank', 'kartu_kredit', 'va'
- payment_reference
- transaction_status (enum) - 'dibuat', 'dibayar', 'dalam_escrow', 'selesai', 'dibatalkan', 'dispute'
- paid_at (nullable)
- completed_at (nullable)
- cancelled_at (nullable)
- cancellation_reason (text, nullable)
- created_at, updated_at

### escrow_records

- id (PK, ULID)
- transaction_id (FK → transactions.id, unique)
- escrow_amount (decimal 12,2)
- escrow_status (enum) - 'ditahan', 'dicairkan_penjual', 'dikembalikan_pembeli', 'dibekukan_dispute'
- held_at
- released_at (nullable)
- release_trigger (enum, nullable)
- auto_release_at
- created_at, updated_at

### escrow_distributions

- id (PK, ULID)
- escrow_id (FK → escrow_records.id)
- recipient_type (enum) - 'seller', 'organizer', 'platform'
- recipient_id (varchar)
- amount (decimal 12,2)
- distribution_status (enum) - 'pending', 'dicairkan', 'gagal'
- disbursed_at (nullable)
- disbursement_reference
- created_at

### venue_checkins

- id (PK, ULID)
- ticket_id (FK → tickets.id)
- transaction_id (FK → transactions.id)
- buyer_id (FK → users.id)
- checkin_latitude (decimal 10,8)
- checkin_longitude (decimal 11,8)
- device_fingerprint
- qr_code_scanned
- verified_by_eo_api (boolean)
- is_verified_successfully (boolean)
- failure_reason_logged (varchar, nullable)
- eo_webhook_response (json, nullable)
- checked_in_at
- created_at

---

## Shipping & Logistics

### shipping_addresses

- id (PK, ULID)
- user_id (FK → users.id)
- recipient_name
- phone_number
- address_line
- city
- province
- postal_code
- is_primary (boolean)
- created_at, updated_at

### shipments

- id (PK, ULID)
- transaction_id (FK → transactions.id)
- shipping_address_id (FK → shipping_addresses.id)
- courier_service (enum) - 'jne', 'jnt', 'sicepat', 'anteraja', 'paxel'
- tracking_number (unique)
- shipment_status (enum) - 'pending', 'dikemas', 'dikirim', 'dalam_perjalanan', 'diterima', 'gagal'
- estimated_delivery_date
- shipped_at (nullable)
- delivered_at (nullable)
- proof_of_delivery_url (nullable)
- created_at, updated_at

---

## Disputes & Resolution

### disputes

- id (PK, ULID)
- transaction_id (FK → transactions.id)
- claimant_id (FK → users.id)
- respondent_id (FK → users.id)
- dispute_type (enum) - 'tiket_palsu', 'tidak_diterima', 'tiket_invalid', 'lainnya'
- description (text)
- video_evidence_url
- additional_evidence (json)
- dispute_status (enum) - 'diajukan', 'ditinjau', 'diselesaikan', 'ditolak'
- resolution_outcome (enum, nullable)
- resolution_notes (text, nullable)
- filed_at
- resolved_at (nullable)
- resolved_by_admin_id (FK → users.id, nullable)
- created_at, updated_at

### dispute_messages

- id (PK, ULID)
- dispute_id (FK → disputes.id)
- sender_id (FK → users.id)
- message_text (text)
- attachment_url (nullable)
- sent_at
- created_at

---

## Analytics & Monitoring

### waste_metrics

- id (PK, ULID)
- event_id (FK → events.id)
- metric_date (date)
- tickets_burned_count (int)
- tickets_resold_count (int)
- economic_value_saved (decimal 15,2)
- recovery_rate_percent (decimal 5,2)
- created_at, updated_at

### national_waste_dashboard

- id (PK, ULID)
- province
- city
- calculation_date (date)
- total_rupiah_waste_prevented (decimal 18,2)
- total_tickets_circulated (int)
- average_markup_percent (decimal 5,2)
- top_event_id (FK → events.id, nullable)
- created_at, updated_at

---

## Marketplace Behavioral Analytics

### listing_views

- id (PK, ULID)
- listing_id (FK → resale_listings.id)
- viewer_user_id (FK → users.id, nullable)
- session_fingerprint (char(64))
- ip_address (varchar(45))
- device_fingerprint (varchar(255))
- user_agent (text)
- viewed_at
- created_at

### listing_watchlists

- id (PK, ULID)
- listing_id (FK → resale_listings.id)
- user_id (FK → users.id)
- created_at
- UNIQUE(listing_id, user_id)

### search_logs

- id (PK, ULID)
- user_id (FK → users.id, nullable)
- search_keyword (varchar(255))
- original_query (text)
- search_result_count (int)
- filter_metadata (json)
- ip_address (varchar(45))
- searched_at
- created_at

### listing_metrics_cache

- id (PK, ULID)
- listing_id (FK → resale_listings.id, unique)
- total_views (int)
- unique_views (int)
- total_watchlists (int)
- total_transactions (int)
- trending_score (decimal 8,4)
- last_calculated_at
- created_at
- updated_at

---

## Anti-Scalper Enforcement

### purchase_limits

- id (PK, ULID)
- user_id (FK → users.id)
- event_id (FK → events.id)
- tickets_purchased_count (int)
- max_allowed_per_user (int)
- last_purchase_at
- created_at, updated_at

### scalper_flags

- id (PK, ULID)
- user_id (FK → users.id)
- flag_reason (enum) - 'markup_berlebih', 'pembelian_massal', 'duplikasi_identitas'
- flag_severity (enum) - 'rendah', 'sedang', 'tinggi'
- auto_detected (boolean)
- flagged_at
- reviewed_at (nullable)
- reviewed_by_admin_id (FK → users.id, nullable)
- action_taken (enum, nullable)
- created_at, updated_at

---

## System Audit

### audit_logs

- id (PK, ULID)
- user_id (FK → users.id, nullable)
- action_type (enum) - 'create', 'update', 'delete', 'login', 'price_change', 'escrow_release'
- table_name
- record_id (varchar)
- old_values (json, nullable)
- new_values (json, nullable)
- ip_address
- user_agent
- created_at

---

# Trending Score Engine Architecture

## Calculation Formula

```text
Trending Score =
(View Velocity × 0.30)
+ (Watchlist Spike × 0.20)
+ (Transaction Velocity × 0.40)
+ (Near Event Momentum × 0.10)
```

## Anti-Manipulation Logic

- Rate limiting untuk anti refresh spam
- Session fingerprint deduplication
- Bot pattern detection
- Suspicious watchlist spike detection
- Fake transaction filtering

## Execution Strategy

Laravel Scheduled Jobs menghitung ulang trending metrics setiap 15 menit untuk active listings.

## Performance Optimization

Homepage menggunakan cached metrics dari `listing_metrics_cache` untuk menghindari expensive realtime aggregation query.

## Scalability

Dirancang untuk Laravel Queue + Horizon workers dan scalable hingga puluhan ribu listing aktif pada standard VPS.

---

# Key Architectural Changes

## Revision 1 — Circular Ownership
`tickets.ticket_code` menggunakan composite unique `(ticket_code, current_owner_id)` untuk mendukung circular resale.

## Revision 2 — Query Optimization
`resale_listings.event_id` digunakan sebagai direct FK untuk mempercepat marketplace query.

## Revision 3 — State Machine Alignment
`transactions.transaction_status` diselaraskan dengan `escrow_records.escrow_status`.

## Revision 4 — Admin Audit Flags
`venue_checkins` mendukung failed verification filtering tanpa parsing JSON.

## Revision 5 — Audit Trail Integrity
Financial tables menggunakan immutable hard records.

## Revision 6 — Marketplace Behavioral Analytics
Penambahan behavioral analytics untuk:
- trending ticket detection
- marketplace demand analytics
- watchlist spike detection
- search trend analysis
- cached marketplace metrics

## Revision 7 — Lightweight Trending Score Engine
Trending system berbasis:
- transaction velocity
- view velocity
- watchlist spike
- near-event momentum

Dirancang lightweight, scalable, dan feasible untuk MVP OLIVIA 2026.

