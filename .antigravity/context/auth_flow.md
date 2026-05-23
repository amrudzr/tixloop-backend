# Authentication Flow

register
↓
verify email
↓
verify phone
↓
login
↓
generate access token
↓
generate refresh token
↓
authenticated requests

---

# Verification Levels

Level 0:
- email
- phone

Level 1:
- KTP
- selfie

Level 2:
- liveness verification

---

# Protected Actions

Require verified authentication:
- selling ticket
- withdrawing funds
- organizer registration

---

# Security Priorities

- token integrity
- refresh rotation
- ownership validation
- replay prevention