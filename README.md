# 🎟️ TixLoop Backend

Backend service untuk TixLoop - Platform Resale Tiket untuk Mengurangi Pemborosan Tiket Event yang Tidak Terpakai akibat Ketidakhadiran Penonton

> “Keep Tickets Moving.”

---

# 🌍 Background

Banyak tiket event tidak digunakan karena:
- perubahan jadwal pengguna
- pembatalan mendadak
- resale yang tidak aman
- scalping dengan harga tidak wajar

TixLoop hadir untuk:
- menjaga tiket tetap beredar
- membantu recovery value tiket
- mengurangi ticket waste
- menciptakan resale marketplace yang lebih fair

---

# 🔥 Core Innovations

## Burn Prevention Engine
Sistem penyesuaian harga otomatis untuk mengurangi risiko tiket hangus mendekati hari event.

## Circular Ticket Ownership
Tiket dapat berpindah ownership secara aman dan traceable.

## Escrow Simulation
Simulasi perlindungan transaksi antara buyer dan seller.

## Anti-Scalper Pricing
Validasi markup resale agar harga tetap fair.

## Waste Dashboard
Visualisasi dampak circular ticketing terhadap pengurangan ticket waste.

---

# 🧱 Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 |
| Database | MySQL |
| Authentication | Sanctum |
| Queue | Redis + Horizon |
| API Style | REST API |
| Architecture | Modular Monolith |

---

# 📦 MVP Scope

## Included
- Authentication
- Ticket marketplace
- Ticket upload
- Burn prevention simulation
- Dashboard metrics
- Escrow simulation
- Verification badge

## Out of Scope
- Blockchain
- NFT
- Enterprise AI fraud detection
- Multi-region infrastructure

---

# 📁 Project Structure

```txt
app/
├── Domain/
├── Services/
├── Actions/
├── Enums/
└── Support/
```

---

# 🔐 API Overview

Base URL:

```txt
/api/v1
```

Standard response format:

```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

Detailed API documentation:
```txt
/docs/api/frontend-api.md
```

---

# 🚀 Local Development Setup

## Requirements
- PHP 8.3+
- Composer
- MySQL
- Node.js
- Redis (optional)

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

---

# 🧪 Development Principles

- MVP-first
- Mobile-first
- Avoid overengineering
- Protect implementation speed
- Focus on demo stability

---

# 👥 Team Workflow

- GitHub Projects for task tracking
- Feature branch workflow
- Conventional commits
- Documentation-driven MVP planning

---

# 📚 Documentation

```txt
/docs
```

Contains:
- architecture
- MVP planning
- glossary
- API documentation
- workflow references

---

# 🏆 OLIVIA 2026

TixLoop dikembangkan sebagai bagian dari kompetisi teknologi OLIVIA 2026 dengan fokus:
- SDG 12.5
- circular economy
- digital trust
- waste reduction