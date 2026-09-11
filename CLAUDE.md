# Niagara Inde Apps — Project Notes

Read `README.md`, `ARCHITECTURE.md`, and the master spec
(`Niagara Inde Apps — Claude Website Build Prompt.md`) before making
significant changes. Phase 1 (foundation/core) and Phase 2 (Stripe
checkout + Canadian tax engine + Orders/Sales) are both implemented.
Full Accounting/Expenses/Audit log/2FA/Backups remain a further phase,
deliberately not implemented yet — don't fake data for them.

## Commands

- Tests: `php artisan test`
- Build assets: `npm run build` / dev: `npm run dev`
- Migrate + seed: `php artisan migrate --seed` (seeders use
  `updateOrCreate`, safe to re-run)
- First admin account: `php artisan make:admin` (interactive — never add a
  seeded default password)

## Things that will bite you if you forget them

- **Demo builds go in `public/demo-builds/{slug}/`, never
  `public/demos/{slug}/`.** A literal `public/demos` directory collides
  with the `/demos` catalogue route at the web-server level (both PHP's
  dev server and Apache/Nginx check disk before routing to Laravel) and
  silently 404s. See `DEMO_DEPLOYMENT.md`.
- **Tailwind v4 theme colors are plain utility classes, not arbitrary
  brackets.** Colors are defined as `--color-*` tokens in the `@theme`
  block of `resources/css/app.css` (e.g. `--color-niagara-500`), which
  auto-generates real utilities like `bg-niagara-500` / `text-navy-soft`.
  Never write `text-[--color-navy]` — that's invalid CSS (sets `color:
  --color-navy` literally); just use `text-navy`.
- **Laravel's attribute-based `#[Fillable([...])]` inserts explicit
  `NULL` for any fillable column omitted from a `create()`/factory call,
  overriding the migration's `->default(...)`.** Any code (or test
  factory) that creates a model must explicitly set every fillable
  boolean/enum column it cares about rather than relying on the DB
  default. `App\Models\App` and `App\Models\User`'s factories set these
  explicitly for this reason — follow the same pattern for new models.
- **Money is integer cents**, never floats (`price_cents`,
  `sale_price_cents`). The admin form accepts dollars and converts; always
  convert at the boundary, not inside a model.
- **`is_featured` gates homepage visibility, deliberately.** An app only
  appears on the homepage (Featured Apps *and* the Live Demo section) if
  it is both `published` and `is_featured`. This was specifically
  requested so a newly added/demoed app's imagery doesn't appear on the
  landing page until someone consciously features it — don't "simplify"
  the homepage query to drop the `is_featured` check.
- **Roles are a single `role` enum column on `users`**
  (`owner`/`content_editor`), not a permissions package — intentional,
  see `ARCHITECTURE.md`. `EnsureUserHasRole` (`role:owner` middleware
  alias) gates Settings/Users only.
- **SVG uploads are rejected on purpose** (stored-XSS risk — uploads are
  served unsanitized). Don't add `svg` back to the media MIME allow-list
  without adding sanitization first.
- **Only `App\Services\StripeCheckout` calls the Stripe SDK.** Everything
  else (`CheckoutController`, `Admin\OrderController`) goes through it, and
  `App\Services\StripeWebhookHandler` has *zero* Stripe-SDK/HTTP
  dependency of its own — it just takes a constructed `\Stripe\Event`. This
  is what makes the webhook tests fast and network-free
  (`\Stripe\Event::constructFrom([...])`); don't fold Stripe API calls back
  into the handler or the controllers.
- **A payment is only ever confirmed by the webhook, never by the
  checkout success page.** `CheckoutController@success` just displays
  whatever the order's current status is. If you're tempted to mark an
  order paid from the success-page controller "to make local testing
  easier" — don't; use `stripe listen --forward-to` instead (see
  `STRIPE_SETUP.md`).
- **`TaxCalculator::calculate()` returns `tax_name`/`percentage` keys, but
  `sales_tax_lines` columns are `tax_name_snapshot`/`percentage_snapshot`.**
  `CheckoutController` maps between them explicitly — mass-assigning the
  calculator's array straight into `$order->taxLines()->create()` will
  fail on the NOT NULL columns. Keep that mapping if you touch this code.

## Fonts

TOLA hasn't been supplied yet. Lato (public/fonts/lato/, OFL-licensed) is
the real interim font, not a placeholder — see `@font-face` blocks in
`resources/css/app.css`.
