# Authentication Architecture

## Authentication Strategy

TixLoop uses:
PASETO authentication.

---

# Why PASETO

Reasons:
- safer than traditional JWT
- prevents algorithm confusion attacks
- modern cryptographic defaults
- cleaner security model

---

# Token Structure

## Access Token

Purpose:
API authorization.

Characteristics:
- short lived
- stateless
- signed securely

---

## Refresh Token

Purpose:
session continuation.

Characteristics:
- rotatable
- revocable
- stored securely

---

# Authentication Flow

login
↓
generate access token
↓
generate refresh token
↓
authenticated API requests
↓
access token expired
↓
refresh token rotation
↓
new access token generated

---

# Security Priorities

1. token integrity
2. token expiration
3. ownership validation
4. replay prevention
5. revocation support

---

# Authorization

Authorization uses:
- policies
- ownership validation
- role checks

---

# Avoid

- long-lived access tokens
- storing sensitive claims
- insecure local storage
- weak token rotation