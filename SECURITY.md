# Security Policy

## Supported Versions

| Version | Supported          |
| ------- | ------------------ |
| 1.0.x   | :white_check_mark: |

## Reporting a Vulnerability

If you discover a security vulnerability, please email: oce.leite1712@gmail.com

## Security Measures

### Authentication & Authorization
- Symfony Security component with bcrypt password hashing
- CSRF protection on all forms
- Role-based access control (ROLE_USER, ROLE_BASIC, ROLE_PREMIUM)
- Session management with secure cookies

### Data Protection
- Environment variables for sensitive data (.env.local)
- SQL injection prevention via Doctrine ORM
- XSS protection via Twig auto-escaping
- File upload validation (type, size)

### Third-Party Security
- Regular dependency updates via Composer
- Security audits with `composer audit`
- Stripe payment integration with webhook signature verification

### Code Quality
- PHPStan static analysis (level 5)
- PHP CS Fixer code formatting (PSR-12)
- BaseController for type-safe authentication

## Last Security Audit

**Date**: 2026-03-29
**Result**:  No vulnerabilities found
**Tool**: `composer audit`
