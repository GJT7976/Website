# Stripe Setup

Direct in-browser purchasing (Stripe Checkout) is implemented. This
document covers getting real Stripe test keys wired up locally, and what's
still deliberately out of scope.

## How it works

App Page → **Buy Now** (shown when an app has `direct_purchase_enabled`) →
`/checkout/{slug}` billing form → tax calculated from **Admin → Taxes** →
an `Order` (+ `OrderItem` + `SalesTaxLine` rows) is created as `pending` →
redirect to **Stripe Checkout** (hosted, card details never touch this
server) → Stripe redirects back to a success/cancel page → **Stripe's
webhook** (not the browser redirect) confirms payment and marks the order
paid, records a `Payment`, and emails a receipt.

No cart: one app per order, matching the spec's own checkout workflow. A
persistent multi-item cart is a possible future enhancement, not built.

## 1. Get Stripe test-mode keys

1. Create a free account at https://dashboard.stripe.com if you don't have
   one.
2. Make sure **Test mode** is on (toggle, top right of the dashboard).
3. **Developers → API keys** — copy the *Publishable key* (`pk_test_...`)
   and *Secret key* (`sk_test_...`).

## 2. Add them to `.env`

```
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
```

Check **Admin → Payments** (owner only) to confirm they're detected —
it shows configured/not-configured status only, never the actual key
values.

## 3. Set up the webhook (required for orders to ever be marked paid)

Payment confirmation happens **only** via Stripe's webhook — the browser
redirect back to the success page is never trusted by itself. Without a
working webhook, orders will sit at `pending` forever even after a real
payment.

**Local development** — use the [Stripe CLI](https://stripe.com/docs/stripe-cli):

```
stripe login
stripe listen --forward-to 127.0.0.1:8000/stripe/webhook
```

The CLI prints a webhook signing secret (`whsec_...`) — put that in
`.env` as `STRIPE_WEBHOOK_SECRET` and restart `php artisan serve`. Leave
`stripe listen` running while testing checkout locally.

**Production (Hostinger or elsewhere)** — in the Stripe dashboard:
**Developers → Webhooks → Add endpoint**, URL
`https://yourdomain.tld/stripe/webhook`, events: `checkout.session.completed`,
`payment_intent.payment_failed`, `charge.refunded`. Copy its signing
secret into the production `.env` as `STRIPE_WEBHOOK_SECRET`.

## 4. Try a full test purchase locally

1. Make sure `stripe listen` (above) is running.
2. Visit any real published app with purchasing enabled (e.g.
   `/apps/hummus-house`) and click **Buy Now**. To test without touching a
   real product, there's also `[SEED] Test Purchase App` ($4.99 CAD) — it's
   kept as a **draft** so it never appears on the public catalogue; publish
   it temporarily from Admin → Apps first, then unpublish it again after.
3. Fill in the billing form (any name/address; use an Ontario postal code
   to see HST applied from the seeded tax rule) and continue.
4. On Stripe's hosted checkout, use a
   [test card](https://stripe.com/docs/testing) — `4242 4242 4242 4242`,
   any future expiry, any CVC, any postal code.
5. You'll be redirected back to a confirmation page. Within a few seconds
   the `stripe listen` webhook forwards the event, the order flips to
   `paid` in **Admin → Orders**, and a receipt email appears in
   `storage/logs/laravel.log` (`MAIL_MAILER=log` locally).

## 5. Enabling purchase on a real app

Admin → Apps → (the app) → Pricing → set a real price → check **Enable
direct in-browser purchase (Stripe Checkout)** → save. The public app page
immediately shows a real "Buy Now" button.

## Refunds

Admin → Orders → (an order) → **Issue Refund** (owner role only — this is
a financial action, same rule as Settings/Users). Enter an amount to
partially refund, or leave it blank for a full refund. This calls Stripe's
refund API directly; the local `refunds` row is written immediately, and
Stripe's own `charge.refunded` webhook is also handled (so a refund issued
directly in the Stripe dashboard is reflected here too, not just ones
started from this admin).

## Money

All amounts are stored as integer cents, never floats — see
`ARCHITECTURE.md`. Currency is `CAD` throughout for now.

## Switching to live mode

Replace the `pk_test_`/`sk_test_` keys with live (`pk_live_`/`sk_live_`)
ones and set up a **separate** live-mode webhook endpoint/secret — test
and live mode are entirely separate in Stripe. **Admin → Payments** shows
which mode is currently active based on the key prefix. Do this only once
you're ready for real charges, and only after verifying tax obligations
below.

## Before taking real payments

- Verify current Canadian/Ontario GST/HST/PST/QST obligations for digital
  goods against authoritative sources (CRA guidance, or an accountant).
  The seeded Ontario HST rate in **Admin → Taxes** is a starting point,
  not a verified, launch-ready configuration — its own notes field says
  so. Update/add rules there as needed; nothing about tax rates is
  hard-coded in application code.
- Confirm Stripe's own tax tooling (Stripe Tax) fits or conflicts with
  this configurable tax engine before enabling both.

## Out of scope (a further phase)

- Full Accounting: fiscal-year exports, by-province/by-country sales
  breakdowns, Stripe processing-fee reporting, expense tracking, and the
  profit overview. **Admin → Sales** today covers gross/tax/refunds/net
  totals for a date range plus a CSV export — the basic version.
- Customer accounts (guest checkout only, by design).
- Subscriptions — one-time purchases only.
