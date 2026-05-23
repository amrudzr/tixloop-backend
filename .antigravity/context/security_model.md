# Security Model

## Objective

Protect ticket ownership, transactions, and marketplace trust.

---

# Core Security Principles

- trust-first
- ownership integrity
- escrow protection
- traceability
- fraud prevention
- auditability

---

# Security Layers

## Authentication Layer

Uses:
- PASETO access tokens
- refresh token rotation
- protected API access

Purpose:
verify user identity securely.

---

## Authorization Layer

Uses:
- ownership validation
- policy-based access
- role restrictions

Purpose:
prevent unauthorized actions.

---

## Verification Layer

Includes:
- email verification
- phone verification
- KTP verification
- selfie verification
- liveness verification

Purpose:
reduce fake accounts and scalping abuse.

---

## Marketplace Protection Layer

Includes:
- hard cap pricing
- purchase limits
- verified sellers
- suspicious activity detection
- ownership tracking

Purpose:
maintain fair resale ecosystem.

---

## Escrow Protection Layer

Funds are:
- temporarily locked
- conditionally released
- protected during disputes

Purpose:
prevent fraud and fake ticket resale.

---

## Audit Layer

Tracks:
- ownership transfer
- price changes
- authentication events
- escrow actions
- dispute activity

Purpose:
support investigation and accountability.

---

# Threat Models

## Fake Ticket Resale

Mitigation:
- ownership validation
- escrow
- verification
- audit logs

---

## Double Selling

Mitigation:
- single active listing
- ownership history
- listing lock

---

## Scalping Abuse

Mitigation:
- hard cap pricing
- reputation system
- purchase limits

---

## Account Abuse

Mitigation:
- token expiration
- verification levels
- suspicious activity detection

---

# Security Priorities

1. ownership integrity
2. transaction safety
3. anti-fraud
4. traceability
5. trust preservation

---

# Security Philosophy

TixLoop prioritizes:
- fairness
- transparency
- secure circulation
- marketplace accountability

Security should:
- protect users
- maintain trust
- support sustainable ticket circulation

---

# Avoid

- weak ownership validation
- insecure uploads
- long-lived tokens
- unprotected routes
- missing audit trails