# CLAUDE3_DEPLOY.md — PRODUCTION WEBSITE DEPLOYMENT

Kickstarter Word: `DEPLOY WEBSITE`

Use this workflow when the user wants the completed localhost Niagara Indie Apps website synchronized with production.

## Required reading
Read completely:
- `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md`
- the website root `DEPLOYMENT.md` if present
- relevant Git/release/security instructions.

## Target
Production domain: `https://niagaraindieapps.com`

The intended flow is:

LOCALHOST → GITHUB → HOSTINGER → niagaraindieapps.com → LIVE VERIFICATION

As of 2026-09-15, the GITHUB → HOSTINGER leg is **automatic** for this
site (Hostinger's Auto-deployment, confirmed working both directions —
see `DEPLOYMENT.md`). This workflow's job has narrowed accordingly:

1. Push to `master` if not already done (this alone deploys the code —
   confirm it actually landed rather than assuming; see step 3).
2. If the change touched migrations or seed data, call
   `POST /deploy-sync` with the bearer token from `DEPLOYMENT.md`'s
   referenced local backup location (never paste the token itself into
   any file in this repo, chat, or memory) — this runs
   `migrate --force` then `db:seed --force` on production and returns
   JSON confirming what ran.
3. LIVE VERIFICATION is still mandatory and still manual (by Claude,
   against the real URLs) — automation covers getting the code/data
   there, not confirming it rendered correctly. Do this step regardless
   of how automated the rest was.

Never assume GitHub or Hostinger is current — verify, don't infer from
"the push succeeded."

Before destructive production changes, preserve a rollback path. Never commit secrets. Never replace production-only customer, order, license, payment, activation, or other live data with development data.

Deployment is not complete until the live website is checked against the intended localhost version and important pages, assets, links, downloads, and app inventory are verified.

If a safe deployment cannot be verified, report that deployment is not verified rather than claiming success.
