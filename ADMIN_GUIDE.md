# Admin Guide

A day-to-day guide to running the Niagara Inde Apps website through the
admin backend at `/admin` — no code or Blade template editing required for
anything covered here.

## Signing in

Go to `/admin`. If you don't have an account yet, an administrator creates
one from the server via `php artisan make:admin` (see `LOCAL_SETUP.md` /
`HOSTINGER_DEPLOYMENT.md`) — there is no public sign-up.

Two roles exist:
- **Owner** — full access, including Settings and Users.
- **Content Editor** — everything except Settings and Users (so a
  non-owner can manage apps, media, and content without touching business
  settings or other accounts).

## Adding a new app

1. **Apps → Add App.**
2. Fill in **Core Details**: name (the slug/URL auto-generates from the
   name if you leave it blank), tagline, short description (used on cards),
   long description (the "Overview" section on the app's page), category,
   status. Leave Status as **Draft** until you're ready to publish.
3. **Platforms** — tick every platform the app supports.
4. **Pricing** — tick "This app is free," or enter a price (and an
   optional sale price). Add external store links (Google Play, Microsoft
   Store, Apple) if the app is sold there — these show as buttons on the
   app's page. *(Direct in-browser checkout isn't built yet — see
   `STRIPE_SETUP.md`.)*
5. **Demo** — if there's a web demo, enable it and set the Demo URL (see
   `DEMO_DEPLOYMENT.md` for exactly where the demo build's files need to
   go).
6. **Support & Requirements**, **SEO** — fill in what's relevant.
7. Click **Create App**. You're now on the edit page, where two more
   sections appear:
   - **Images** — upload an icon, a feature graphic, and screenshots.
   - **Feature Bullets** — short feature callouts shown on the app's page.
8. When ready, set **Status → Published** (from the edit form, or the
   Publish button in the Apps list).

## Featuring an app on the homepage

An app only appears on the homepage's Featured Apps and Live Demo sections
if it is **both published and marked Featured**. This is deliberate — a
newly added app's images never show up on the landing page until someone
consciously decides to feature it. Toggle this from the Apps list
("Feature" button) or the checkbox on the app's edit form.

## Media Library

**Media → Media Library** lists everything uploaded through the site,
independent of which app(s) use it. You can upload here directly, or
upload straight from an app's edit page (which attaches it automatically).
An image still attached to an app can't be deleted until it's removed from
that app first — this prevents accidentally breaking a live page.

## Editing site content (no code required)

- **Content → Pages** — About, Privacy, Terms, Refunds. Each page is a
  list of sections (heading + body text); add, edit, reorder, or remove
  sections freely.
- **FAQs** — general (site-wide) FAQs, or FAQs scoped to a specific app
  (shown on that app's own page).
- **Business/Site/Store Settings** (owner only) — homepage hero text,
  footer tagline, business contact info, default currency, and order
  number prefix.

## Support inbox

Every submission from the public **Contact** page appears under
**Support Inbox**, newest first. Opening one marks it "read"; you can mark
it "resolved" once handled. If a business email is configured (Settings →
Business), a notification email is also sent there.

## Selling an app

1. Set a real price on the app (Apps → the app → Pricing) and check
   **Enable direct in-browser purchase (Stripe Checkout)**.
2. Make sure Stripe keys are configured — check **Settings → Payments**
   (owner only; shows configured/not-configured status, never the actual
   keys) — see `STRIPE_SETUP.md` if they aren't set up yet.
3. A real **Buy Now** button now appears on the app's public page.

## Selling an app by platform (Android / Windows / Bundle / Web / Complete)

For an app sold on more than one platform, add **Editions** instead of (or
alongside) the single flat price above:

1. Open the app (must already be saved) and scroll to **Platform
   Delivery, License & Updates** — set Android/Windows delivery (Direct,
   store, or both), the paid Web App URL if one exists (this is separate
   from the Live Demo), and the license scope shown to customers.
2. Under **Editions**, add one per thing a customer can buy — e.g.
   "Android" at $2.99, "Windows" at $2.99, "Android + Windows Bundle" at
   $4.99. Each edition needs its **Includes** checkboxes set (which
   platform(s)/download-or-web-access it grants) — an edition with nothing
   checked won't give a buyer any access.
3. Under **Releases**, upload the actual file per platform — a real signed
   `.apk` for Android, an `.exe`/`.msix`/`.msixbundle` for Windows — and
   mark one **Current** per platform. Customers always get whichever
   release is current, not the one that happened to exist when they
   bought. An `.aab` can be logged here for your own Play Store record,
   but it's always forced non-customer-downloadable — there's no way to
   accidentally hand a customer a `.aab`.
4. Once an app has any active edition, its public page shows the
   platform-selection panel instead of a single Buy Now button, and the
   old flat-price checkout link stops working for that app (so a customer
   can't skip picking a platform).

Paid customers get access via **My Downloads** — a link mailed with every
receipt (and re-requestable at `/my-downloads` with just their order
email; there's no customer login). **Customer Access** on the app's edit
page and on an individual order lets you manually grant a comped copy or
revoke/restore access — every override records who did it and why on that
access record itself.

## Orders & Sales

- **Orders** lists every checkout attempt with its status (pending, paid,
  failed, refunded). Open one to see the billing details, tax breakdown,
  payment history, and — for a paid order (owner only) — an **Issue
  Refund** button.
- **Sales** shows gross/tax/refunds/net totals for a date range (Today /
  This Month / This Quarter / This Year / Custom), with a CSV export. This
  is a basic sales view — full fiscal-year/by-province accounting reports
  are a further phase.
- **Taxes** (owner only, under the Administration section) is where
  Canadian tax rates live — country, province, name, percentage, effective
  date, and an optional expiry date. Checkout calculates tax by looking up
  whatever rule(s) are active and in-date for the buyer's billing
  province; no rule means no tax is charged, so add rules for every
  jurisdiction you're obligated to collect in. **Verify current
  Canadian/provincial tax requirements against authoritative sources
  before relying on the seeded default for a real sale.**

## Licenses

**Licenses** (under Sales) covers the permanent PRO license keys issued
for website-sold Android APK / Windows MSIX-EXE editions — separate from
Google Play, which uses its own in-app purchase system. See
`LICENSE_SYSTEM.md` for the full design; day-to-day tasks:

- **Search** by customer email, name, or transaction ID, and filter by
  status.
- Open a license to see its entitlement (Android / Windows / bundle),
  amount paid, the order it came from, activated devices with platform
  and activation date, and a history of activation/validation/deactivation
  events.
- **Resend License Email** re-sends the key to the customer (recovered
  from the encrypted column — never the hash — so this works even though
  the license page itself never displays the raw key back to you).
- **Reset Activations** deactivates every device on a license at once —
  use this when a customer needs a clean slate (e.g. they've lost access
  to both devices) rather than deactivating them one at a time.
- **Revoke** disables a license (fraud, abuse); **Restore** re-enables it.
  **Mark Refunded** is for a refund issued outside the normal Stripe
  refund flow — a refund processed via **Orders → Issue Refund** already
  marks any related licenses refunded automatically.
- Deactivating an individual device (from the license's own page) frees
  that slot immediately — the customer can also do this themselves at
  `/license/manage` without contacting you, subject to a self-service
  reset limit (`config/licensing.php`) that exists purely to slow down
  abuse, not to block someone replacing a broken device.

## Users (owner only)

**Users** lets an owner add or edit administrator accounts, change roles,
deactivate an account (blocks sign-in without deleting it), or remove one.
You cannot deactivate/downgrade/delete your own account from here — that
prevents accidentally locking yourself out.

## What's not here yet

Full Accounting (fiscal-year exports, by-province/country breakdowns,
Stripe fees), Expenses, Backups, and the Audit Log are shown in the
sidebar as "Phase 2 — Soon." They're not implemented — no figures are
shown for them anywhere in the admin, rather than displaying placeholder
or fabricated numbers.
