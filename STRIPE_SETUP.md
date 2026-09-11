# Stripe Setup — Phase 2 (not yet implemented)

Direct in-browser purchasing is **not implemented yet**. This document
records the plan so Phase 2 has a clear starting point, and explains what
already exists in the data model today.

## What already exists (Phase 1)

- `apps.price_cents` / `apps.sale_price_cents` / `apps.currency` — real
  pricing data, entered and shown today, stored as integer cents.
- `apps.direct_purchase_enabled`, `apps.stripe_product_id`,
  `apps.stripe_price_id` — columns exist so an app can be pre-configured for
  Stripe, but `direct_purchase_enabled` has no effect yet: no checkout
  button is wired to it, and the public app page instead shows external
  store links (Google Play / Microsoft Store / Apple) when set, or a
  "Contact to Purchase" link otherwise, so nothing on the live site
  pretends to sell something it can't yet actually process.
- `.env.example` already has placeholder `STRIPE_KEY` / `STRIPE_SECRET` /
  `STRIPE_WEBHOOK_SECRET` entries.

## Phase 2 plan

1. Add the official Stripe PHP SDK (`stripe/stripe-php`) via Composer.
2. Build the checkout flow: App Page → Buy → Cart/Checkout → customer
   information → tax calculation (needs the tax engine — see below) →
   Stripe Checkout → webhook-confirmed order → receipt.
3. Use **Stripe Checkout** (hosted) rather than raw Stripe Elements unless
   a stronger case emerges for embedding payment fields directly — it's
   less code to maintain securely and keeps card data off this server
   entirely.
4. Implement webhook handling (`checkout.session.completed`,
   `payment_intent.succeeded`, `payment_intent.payment_failed`,
   `charge.refunded`) with signature verification
   (`Stripe\Webhook::constructEvent`), and treat **webhook-confirmed**
   state as the source of truth for financial records — never trust a
   browser redirect back to a "success" URL by itself.
5. This depends on the Phase 2 database tables documented in
   `ARCHITECTURE.md` (`customers`, `orders`, `order_items`, `payments`,
   `refunds`, `tax_rules`, `sales_tax_lines`) not existing yet.
6. Support both Stripe test mode and live mode via `.env`, and document the
   safe way to switch (test keys start with `pk_test_`/`sk_test_`; never
   let a demo environment use live keys).

## Before going live with real payments

- Verify current Canadian/Ontario GST/HST/PST/QST obligations for digital
  goods against authoritative sources (CRA guidance, or an accountant) —
  this codebase's tax engine, once built, will be configurable rather than
  hard-coded specifically so this verification can happen independently of
  a code change.
- Confirm Stripe's own tax tooling (Stripe Tax) fits or conflicts with the
  planned configurable tax engine before enabling both.
