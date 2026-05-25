# Product Requirements Document (PRD)

# TixLoop — Circular Ticketing Marketplace Platform

**Version:** 1.0
**Status:** Draft MVP PRD
**Competition:** OLIVIA XI 2026 — Web Technology
**Project Codename:** TixLoop
**Tagline:** *“Keep Tickets Moving.”*
**Document Owner:** TixLoop Team
**Last Updated:** 25 May 2026

---

# 1. Product Overview

## 1.1 Product Description

TixLoop adalah platform marketplace resale tiket berbasis web yang dirancang untuk menciptakan ekosistem secondary ticketing yang aman, adil, transparan, dan berkelanjutan.

Platform ini memungkinkan pengguna menjual kembali tiket yang tidak dapat digunakan melalui sistem resale resmi dengan mekanisme:

* escrow protection,
* anti-scalper pricing,
* ownership tracking,
* dan Burn Prevention Engine.

TixLoop mengadopsi prinsip circular economy untuk mengurangi ticket waste dan mendukung Sustainable Development Goals (SDG) 12.5.

---

## 1.2 Background Problem

Industri event di Indonesia mengalami pertumbuhan pesat, terutama pada:

* konser,
* festival,
* olahraga,
* seminar,
* dan entertainment events.

Namun terdapat beberapa masalah utama:

### Ticket Waste

Banyak tiket hangus karena pemilik tidak dapat hadir.

### Scalping

Tiket dijual kembali dengan harga tidak masuk akal.

### Fraud & Fake Ticket

Pasar resale informal rawan penipuan.

### Unfair Access

Fans asli kalah dengan bot dan reseller profesional.

### Lack of Official Resale Infrastructure

Belum ada marketplace resale resmi yang aman dan terstruktur.

---

## 1.3 Product Vision

> “Membangun ekosistem circular ticketing pertama di Indonesia yang aman, fair, dan sustainable.”

---

## 1.4 Product Mission

* Mengurangi pemborosan tiket (ticket waste)
* Menciptakan resale marketplace yang aman
* Mengurangi praktik scalping
* Membantu fans mendapatkan tiket dengan harga fair
* Mendukung SDG 12.5 melalui circular digital asset system

---

# 2. SDG Alignment

## 2.1 Primary SDG

### SDG 12 — Responsible Consumption and Production

#### Target 12.5

> “Substantially reduce waste generation through prevention, reduction, recycling and reuse.”

---

## 2.2 TixLoop SDG Contribution

| SDG Principle         | TixLoop Implementation              |
| --------------------- | ----------------------------------- |
| Prevention            | Prevent ticket waste via resale     |
| Reduction             | Reduce unsold/unused tickets        |
| Reuse                 | Enable ownership recirculation      |
| Resource Optimization | Maximize event capacity utilization |

---

## 2.3 Core Sustainability Narrative

TixLoop mengubah tiket dari:

> “single-use digital product”

menjadi:

> “circular digital asset”

---

# 3. Product Goals

## 3.1 Business Goals

* Membuat resale tiket lebih aman
* Mengurangi dominasi pasar scalper
* Menjadi platform resale terpercaya
* Membuka peluang partnership organizer

---

## 3.2 User Goals

### Buyer

* Mendapat tiket valid dengan harga fair

### Seller

* Mengurangi kerugian tiket hangus

### Organizer

* Mengontrol resale ecosystem

---

## 3.3 Competition Goals (OLIVIA)

* Menunjukkan inovasi teknologi berbasis SDG
* Menampilkan MVP yang feasible
* Menunjukkan measurable impact

---

# 4. Success Metrics

## 4.1 Marketplace Metrics

| Metric                    | Target MVP |
| ------------------------- | ---------- |
| Successful Resale Rate    | >35%       |
| Ticket Recovery Rate      | >40%       |
| Verified Listing Ratio    | >70%       |
| Fraud Prevention Accuracy | >80%       |

---

## 4.2 Sustainability Metrics

| Metric                   | Description                  |
| ------------------------ | ---------------------------- |
| Rupiah Waste Prevented   | Total economic value saved   |
| Tickets Rescued          | Total recirculated tickets   |
| Recovery Rate            | Percentage of saved tickets  |
| Ticket Circulation Count | Number of successful resales |

---

## 4.3 UX Metrics

| Metric                | Target     |
| --------------------- | ---------- |
| Mobile responsiveness | 100%       |
| Average checkout flow | <3 minutes |
| Page load speed       | <3 seconds |

---

# 5. User Personas

---

## 5.1 Buyer Persona

### Name

Rina — Concert Fan

### Goals

* Mendapat tiket asli
* Harga resale masuk akal
* Transaksi aman

### Pain Points

* Scalper
* Tiket palsu
* Harga terlalu tinggi

---

## 5.2 Seller Persona

### Name

Andi — Ticket Owner

### Goals

* Menjual tiket dengan cepat
* Mengurangi kerugian
* Aman dari fraud buyer

### Pain Points

* Tiket hangus
* Sulit mencari buyer terpercaya

---

## 5.3 Organizer Persona

### Name

PT Eventindo

### Goals

* Mengurangi fake ticket
* Mengontrol resale
* Mendapat revenue share

### Pain Points

* Scalping
* Reputasi event rusak

---

# 6. MVP Scope

# 6.1 MVP Features (Required)

## Authentication

* Register
* Login
* Session authentication

---

## Marketplace

* Ticket listing
* Ticket detail
* Search & filter
* Marketplace categories

---

## Sell Ticket

* Create listing
* Upload ticket image
* Set resale price
* Preview listing

---

## Burn Prevention Engine

* Automatic price reduction
* Countdown timer
* Floor price logic

---

## Verification Layer

* Verified seller badge
* Escrow protected badge
* Ticket validation status

---

## Waste Dashboard

* Ticket rescued metric
* Waste prevented metric
* Recovery rate
* Trending event overview

---

## Basic Organizer Section

* Organizer profile
* Official organizer badge

---

# 6.2 Future Scope (Post-MVP)

## Advanced Features

* OCR ticket parsing
* AI fraud detection
* Dynamic QR validation
* Real-time organizer API integration
* KYC liveness verification
* NFT ticket support
* Automated dispute system
* Payment gateway integration

---

# 7. User Flow

# 7.1 Buyer Flow

1. User browse marketplace
2. User open ticket detail
3. User verify listing trust indicators
4. User purchase ticket
5. Escrow simulation activated
6. Ticket ownership transferred

---

# 7.2 Seller Flow

1. Seller upload ticket
2. Seller create listing
3. Burn Prevention Engine activated
4. Buyer purchases ticket
5. Escrow simulated
6. Seller receives payment confirmation

---

# 7.3 Organizer Flow

1. Organizer register
2. Organizer create event
3. Organizer manage ticket policy
4. Organizer monitor resale activity

---

# 8. Functional Requirements

# 8.1 Marketplace Listing

## Requirements

* Display ticket cards
* Filter by category
* Filter by event date
* Show pricing comparison
* Show seller reputation

---

# 8.2 Burn Prevention Engine

## Description

Sistem dynamic pricing untuk mencegah tiket hangus.

## Rules

* Auto drop every 8 hours
* Default drop: 8%
* Minimum floor price: 45%
* Stop when sold

## Objectives

* Increase ticket circulation
* Reduce ticket waste
* Prevent extreme markup

---

# 8.3 Verification System

## Requirements

* Verified seller badge
* Escrow protected indicator
* Ticket ownership status

---

# 8.4 Waste Dashboard

## Requirements

* Total tickets rescued
* Economic value saved
* Recovery rate
* Active resale statistics

---

# 9. Non-Functional Requirements

## Performance

* Fast page load
* Optimized marketplace browsing

---

## Security

* Session protection
* Input validation
* CSRF protection
* Secure authentication

---

## Accessibility

* Mobile-first design
* Responsive layout
* Clear CTA hierarchy

---

## Scalability

* Modular backend architecture
* Reusable frontend components

---

# 10. Technical Architecture

# 10.1 Backend Stack

| Component      | Technology        |
| -------------- | ----------------- |
| Framework      | Laravel 13        |
| Authentication | Sanctum / Session |
| Database       | MySQL             |
| Queue          | Laravel Queue     |
| Runtime        | FrankenPHP        |

---

# 10.2 Frontend Stack

| Component | Technology      |
| --------- | --------------- |
| Framework | SvelteKit       |
| Styling   | Tailwind CSS    |
| UI System | Component-based |
| State     | Svelte Store    |

---

# 10.3 Infrastructure

| Component  | Technology       |
| ---------- | ---------------- |
| Hosting    | VPS              |
| Repository | GitHub Private   |
| Deployment | CI/CD Manual MVP |

---

# 11. Database Overview

## Core Entities

| Entity          | Purpose              |
| --------------- | -------------------- |
| users           | Account management   |
| events          | Event data           |
| tickets         | Ticket ownership     |
| resale_listings | Marketplace listing  |
| transactions    | Transaction records  |
| organizers      | Organizer management |
| waste_metrics   | SDG metrics          |

---

# 12. UI/UX Direction

# 12.1 Design Principles

* Marketplace-first
* Trust-centric
* Modern dark mode
* Fast information scanning
* Mobile-first

---

# 12.2 Core UI Priorities

## High Priority

* Homepage
* Marketplace cards
* Ticket detail page
* Dashboard preview

---

## UX Feeling

User harus merasa:

* aman,
* modern,
* profesional,
* bukan marketplace scam.

---

# 13. Security & Trust Model

## Core Trust Components

| Feature              | Purpose                |
| -------------------- | ---------------------- |
| Escrow simulation    | Transaction protection |
| Verified seller      | Trust building         |
| Ownership tracking   | Prevent double selling |
| Anti-scalper pricing | Fair marketplace       |
| Reputation score     | Behavioral trust       |

---

# 14. Risks & Limitations

| Risk                    | Description                           |
| ----------------------- | ------------------------------------- |
| No EO API               | Some organizers lack integration      |
| OCR limitation          | Parsing accuracy may vary             |
| MVP escrow              | Not connected to real payment gateway |
| Limited fraud detection | Advanced AI not included in MVP       |

---

# 15. Demo Scenario (Competition)

## Demo Flow

### Step 1

User opens marketplace homepage

### Step 2

User browses resale tickets

### Step 3

Seller uploads ticket listing

### Step 4

Burn Prevention Engine reduces price

### Step 5

Buyer purchases ticket

### Step 6

Waste Dashboard updates rescued ticket metric

---

# 16. Competitive Advantage

| Feature                | TixLoop | Informal Marketplace |
| ---------------------- | ------- | -------------------- |
| Escrow                 | ✅       | ❌                    |
| Anti-scalper pricing   | ✅       | ❌                    |
| Burn Prevention Engine | ✅       | ❌                    |
| Sustainability metrics | ✅       | ❌                    |
| Ticket verification    | ✅       | ❌                    |

---

# 17. Roadmap

# Phase 1 — Internal MVP

* Homepage
* Marketplace
* Burn Prevention Engine
* Dashboard preview

---

# Phase 2 — Submission Ready

* Auth
* Listing flow
* Verification badges
* Better responsive UX

---

# Phase 3 — Final Competition Ready

* Enhanced dashboard
* Organizer section
* Improved trust system
* Presentation optimization

---

# 18. Core Narrative

> “TixLoop bukan sekadar marketplace tiket.”

> “TixLoop mengubah tiket menjadi circular digital asset untuk mengurangi economic waste dan menciptakan resale ecosystem yang aman, fair, dan sustainable.”

---

# 19. Appendix

## Core Keywords

* Circular Economy
* Ticket Waste
* Burn Prevention Engine
* Anti-Scalper System
* Escrow Protection
* Circular Ticketing Marketplace
* SDG 12.5

---

# 20. Final Notes

PRD ini disusun untuk:

* alignment product vision,
* development guidance,
* UI/UX direction,
* dan kebutuhan presentasi kompetisi OLIVIA XI 2026.

TixLoop difokuskan sebagai:

> MVP yang feasible, demonstrable, dan impactful.

Bukan enterprise-scale production system.
