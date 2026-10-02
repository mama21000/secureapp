# SecureApp: Security-Hardened Money-Transfer Web App

A peer-to-peer money-transfer web app built to defend against the OWASP Top 10.
Stack: PHP 8.2, MySQL 8, Apache (TLS 1.3 only), Docker Compose.

## Security features
- CSRF tokens on every POST, and a nonce-based Content Security Policy against XSS
- Prepared statements (SQL injection), bcrypt password hashing
- Session hardening: ID rotation, idle/absolute timeouts, IP + User-Agent fingerprinting
- Brute-force protection: IP- and username-level login lockouts
- ACID money transfers with row-level locking (`SELECT ... FOR UPDATE`) in deterministic order to prevent race conditions and deadlocks
- Safe avatar uploads: MIME sniffing, size/dimension limits, re-encoding to WebP
- Apache hardening: HTTPS redirect, HSTS, Slowloris timeouts, request size limits

## My contributions (Apoorv Singh)
- Login brute-force protection (IP and username lockouts)
- Server-side validation layer (`app/Core/Validator.php`) for registration, profiles and transfers
- Centralized exception and error handling with server-side logging

Team project, originally developed at github.com/sureshbaddipudi/secureapp.

---
