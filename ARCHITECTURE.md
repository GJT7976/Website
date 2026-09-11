# Architecture

## Stack

Laravel 13 (PHP 8.3), Blade, Tailwind CSS v4 (via the official Vite
plugin), Alpine.js. SQLite for local development, MySQL/MariaDB in
production. No permanent Node.js server — Vite is a build-time tool only.

## Folder layout (non-default additions)

```
app/
  Console/Commands/MakeAdminCommand.php   php artisan make:admin
  Http/Controllers/                       public site controllers
  Http/Controllers/Admin/                 admin backend controllers
  Http/Requests/                          public form requests (Contact)
  Http/Requests/Admin/                    admin form requests (App, Media)
  Http/Middleware/EnsureUserHasRole.php   role:owner / role:content_editor
  Mail/                                   Mailables (contact notification)
  Models/                                 Eloquent models, see schema below
  Services/ImageService.php               GD-based thumbnail generation
  Services/MediaLibrary.php               shared upload handling
database/
  migrations/                             Phase 1 tables (see below)
  seeders/                                AppCategory, Platform, App
                                           (Bread Maker + seed placeholders),
                                           Page, Setting, Faq
resources/views/
  components/                             app-layout, admin-layout, site-nav,
                                           site-footer, app-card, price,
                                           platform-badge, feature-list,
                                           screenshot-gallery, cta-block,
                                           alert, admin/* (data-table,
                                           form-field, metric-card)
  home.blade.php, apps/, demos/, pages/,  public pages
  pricing.blade.php, support.blade.php,
  contact.blade.php
  admin/                                  admin backend views
routes/
  web.php                                 public routes
  admin.php                               admin routes (loaded with the
                                           "admin." name/path prefix from
                                           bootstrap/app.php)
public/
  fonts/lato/                             self-hosted Lato font files
  demo-builds/{slug}/                     static web builds for live demos
                                           (NOT "demos" — see below)
```

## Authentication & roles

There is a single `users` table used only for admin accounts in Phase 1
(no public customer registration exists yet — see "Customer accounts" under
Phase 2 below). A `role` enum (`owner` | `content_editor`) plus `is_active`
drive access:

- The default `auth`/`guest` middleware protect `/admin/*`, redirecting to
  `admin.login` / `admin.dashboard` respectively (configured in
  `bootstrap/app.php` via `redirectGuestsTo`/`redirectUsersTo`, since this
  is currently the only authenticated area on the site).
- A custom `role:owner` middleware (`App\Http\Middleware\EnsureUserHasRole`)
  gates Settings and Users to the `owner` role; everything else
  (Apps, Media, Content, FAQs, Support) is available to both roles.
- The first administrator is always created via the interactive
  `php artisan make:admin` command — never a seeded default password.

This intentionally does not use a package like spatie/laravel-permission or
a separate `admin` guard — two roles with one gate is simple enough that
the extra dependency/complexity isn't justified yet. If roles grow more
complex later, migrating to a permissions package is straightforward.

## Money

All prices are stored as **integer cents** (`price_cents`, `sale_price_cents`
on `apps`), never floats, per the spec's money-handling requirement.
`currency` is stored alongside every price-bearing row (default `CAD`) —
`$` is never assumed to mean CAD internally.

## Database schema — Phase 1 (migrated today)

| Table | Purpose |
|---|---|
| `users` | Admin accounts only. `role`, `is_active` added to Laravel's default table. |
| `app_categories` | Lookup: Business, Food & Recipes, Productivity, Utilities, Education, Lifestyle, Other. |
| `platforms` | Lookup: android, windows, web, pwa, ios, macos. |
| `apps` | The catalogue. Core fields, pricing (cents), store links, demo fields (1:1 — no separate `demos` table needed), SEO fields, `status` (draft/published/archived), `is_featured` (also gates homepage visibility), soft deletes. |
| `app_platform` | Pivot: apps ↔ platforms. |
| `media` | Media library items: disk/path/thumbnail, mime, size, dimensions, alt/title/description, uploader. |
| `app_media` | Pivot: apps ↔ media, with `type` (icon / feature_graphic / screenshot) and `sort_order`. |
| `app_features` | Feature bullets shown on an app's page. |
| `pages` | About/Privacy/Terms/Refunds — slug, title, SEO fields, published. |
| `page_sections` | Ordered content blocks per page (heading/body/optional image) — lets the owner edit these pages without touching Blade. |
| `faqs` | General (site-wide) or app-specific FAQs. |
| `settings` | Key/value site settings, grouped (`business`, `site`, `store`). |
| `support_requests` | Contact form submissions / support inbox. |

## Database schema — Phase 2 (planned, not yet migrated)

Documented here so Phase 2 has an agreed starting point:

| Table | Purpose |
|---|---|
| `customers` | Customer accounts (optional — guest checkout should remain possible). |
| `orders`, `order_items` | Immutable snapshot of what was purchased, at what price, at time of sale. |
| `payments`, `refunds` | Stripe payment/refund records, linked to orders. |
| `tax_rules` | Effective-dated Canadian tax configuration (country/province, tax name, percentage, effective/expiry dates) — never a single hard-coded Ontario rate. |
| `sales_tax_lines` | Tax actually applied to a given order line, snapshotted. |
| `expenses`, `expense_categories` | Lightweight business expense tracking. |
| `audit_logs` | Administrator action history (who/what/when, before/after where reasonable). |

## Demo system — the routing gotcha

Static demo builds live at **`public/demo-builds/{slug}/`**, not
`public/demos/{slug}/` as an earlier draft of this system used. The reason:
`public/demos` as a literal directory collides with the `/demos` catalogue
route at the web-server level — PHP's built-in dev server, and Apache/Nginx
in production (both route "does a file/directory exist on disk?" checks
before handing a request to `index.php`), would resolve `/demos` to that
directory and return a bare 404 without Laravel's router ever running,
breaking the catalogue page. `tests/Feature/BreadMakerSeedTest.php` and
`tests/Feature/Public/PublicPagesTest.php` both assert against this
regression. See `DEMO_DEPLOYMENT.md` for the full demo system.

## Fonts

TOLA is the intended final brand typeface but hasn't been supplied yet.
**Lato** (SIL Open Font License — free to use permanently, files in
`public/fonts/lato/`, license text alongside) is used as the real interim
typeface rather than a generic system-font placeholder. `resources/css/app.css`
has commented-out `@font-face` blocks ready for TOLA `.woff2` files — supply
them at `public/fonts/tola/` and flip the `--font-display`/`--font-body`
theme tokens to `'TOLA'` to switch over.

## Design tokens

Tailwind v4's `@theme` block in `resources/css/app.css` defines the color
palette (`--color-niagara-*` for the brand green, `--color-cta-*` for a
stronger call-to-action green, `--color-water-*` for the blue accent,
`--color-navy*` for text, plus paper/offwhite/mist neutrals) and generates
matching utility classes automatically (e.g. `bg-niagara-500`,
`text-cta-600`) — there's no need for Tailwind's `[--color-x]` arbitrary
bracket syntax for these; a plain utility class name is enough. A parallel
semantic type scale (`.text-display`, `.text-h1`…`.text-small`, `.text-label`,
`.text-nav`) keeps typography consistent without hand-picking font sizes
per component.
