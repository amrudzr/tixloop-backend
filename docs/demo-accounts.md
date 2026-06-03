# Demo Accounts

This document outlines the pre-configured demo user accounts seeded in the TixLoop database for development, testing, and presentation purposes.

## Authentication Details
- **Default Password**: `password` (for all seeded users)

---

## Core Personas

| Name | Email | Role(s) | Persona |
| :--- | :--- | :--- | :--- |
| **Admin Verifikator** | `admin@tixloop.com` | `admin` | **System Administrator**: Responsible for reviewing resale listings, performing manual verification checks, and approving/rejecting listings. |
| **Budi Santoso** | `student.seller@tixloop.com` | `seller` | **Student Seller**: A seller looking to quickly offload tickets. Often lists tickets at reasonable prices but is prone to high-risk time-to-event burn situations. |
| **Rian Hidayat** | `music.enthusiast@tixloop.com` | `seller`, `buyer` | **Music Enthusiast**: A power user exhibiting dual-role behavior. Actively lists unwanted tickets and buys tickets for events they want to attend. |
| **Siti Rahma** | `concert.hunter@tixloop.com` | `buyer` | **Concert Hunter**: A value-seeking buyer who scours the marketplace for last-minute deals and price drops on upcoming high-demand events. |
| **Diana Putri** | `event.collector@tixloop.com` | `buyer` | **Event Collector**: A buyer who purchases tickets early, specifically targeted towards sports tournaments, festivals, and cultural events. |

---

## Additional Factory-Seeded Users
To satisfy the target volume of **16 users**, the database seeds an additional 11 users with random names and emails. These users are assigned role structures to support marketplace scale simulation:
- **6 Factory Sellers**: Seeded with the `seller` role.
- **5 Factory Buyers**: Seeded with the `buyer` role.
