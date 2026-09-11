# Security

## Implemented today (Phase 1)

- **CSRF protection** — Laravel's default CSRF middleware on every state-
  changing form (`@csrf` on all admin and public forms).
- **Password hashing** — bcrypt (Laravel's default `Hash` facade), never
  plaintext, never logged.
- **No default credentials** — the first administrator is created only via
  the interactive `php artisan make:admin` command, which requires a
  12+ character password typed and confirmed; there is no seeded
  `admin/admin`-style account anywhere in this codebase.
- **Login throttling** — `throttle:5,1` on the admin login POST route
  (5 attempts per minute per IP+email combination, Laravel's default
  keying), tested in `tests/Feature/Admin/AuthTest.php`.
- **Role-based authorization** — `EnsureUserHasRole` middleware restricts
  Settings and Users to the `owner` role; a deactivated account
  (`is_active = false`) cannot sign in even with a correct password.
- **Validated, allow-listed file uploads** — the Media Library validates
  MIME type (sniffed from file contents, not just the extension), a
  fixed extension allow-list, and a file-size cap. **SVG uploads are
  deliberately rejected** — an SVG can embed executable script, and
  uploads here are served directly and unsanitized, so accepting SVG
  would be a stored-XSS risk.
- **Parameterized queries** — all data access goes through Eloquent/the
  query builder; no raw string-interpolated SQL exists in this codebase.
- **Mass-assignment protection** — every model declares its fillable
  attributes explicitly (via PHP attributes, e.g. `#[Fillable([...])]`).
- **Contact form spam protection** — a honeypot field (`website`, hidden
  from real users via CSS, expected to stay empty) plus request throttling
  (`throttle:5,1`) on the public contact form. No CAPTCHA is used —
  the spec asks not to make CAPTCHA unnecessarily intrusive, and the
  honeypot + rate limit is a reasonable first line of defense.
- **`APP_DEBUG` discipline** — `.env.example` and `HOSTINGER_DEPLOYMENT.md`
  both call out `APP_DEBUG=false` as required in production so stack traces
  are never shown to a public visitor.
- **Secrets excluded from Git** — `.env`, `database/*.sqlite`, and
  `composer.phar` are all git-ignored; `.env.example` contains placeholders
  only.

## Known gaps — planned for Phase 2

- **Two-factor authentication** for admin accounts.
- **Audit log** of administrator actions (app/price/content changes,
  refunds once they exist, permission changes).
- **Security headers** (CSP, `X-Frame-Options`, etc.) beyond Laravel's
  framework defaults — to be reviewed once the demo iframe/embed strategy
  and any third-party script needs (Stripe.js) are finalized, since a
  strict CSP has to explicitly allow those.
- **Stripe webhook signature verification** — not applicable yet; no
  webhook endpoint exists until Phase 2's checkout is built (see
  `STRIPE_SETUP.md`). It will be implemented from day one of that work,
  not added after the fact.
- **Rate limiting beyond login/contact** — worth revisiting once public
  write-endpoints (e.g. checkout) exist.

## Reporting a concern

There is no public bug bounty program. If you find a security issue in
this codebase, report it through the business contact information on the
site rather than filing a public issue.
