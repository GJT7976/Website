# CLAUDE2_WEBSITE.md — LOCALHOST WEBSITE WORKFLOW

Kickstarter Word: `UPDATE WEBSITE`

Use this workflow to add, remove, or modify content on the LOCALHOST Niagara Indie Apps website.

## Source of truth
The working localhost website is the website-content source of truth.

## Required reading
Read:
- `claude_tasks/CLAUDE0_LEGACY_MASTER.md`
- relevant website/release documents under `docs/`
- `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md` for the boundary between local website work and production deployment.

## Scope
Update and verify the localhost website, including app listings, app pages, descriptions, pricing, images, platform choices, purchase/download information, navigation, categories, and required assets.

Do not claim the live production site has been updated merely because localhost works.

## Production reality as of 2026-09-15 — read `DEPLOYMENT.md` first

The "do not deploy unless DEPLOY WEBSITE is invoked" boundary this file
used to state no longer fully holds for this specific website: Hostinger's
Auto-deployment is confirmed ON (`DEPLOYMENT.md`), so any `git push` to
`master` — including one made incidentally while doing UPDATE WEBSITE
work — deploys that code to production automatically, regardless of which
Kickstarter Word triggered it. There is no way to push code during this
workflow without also deploying it.

What committing/pushing during UPDATE WEBSITE does **not** do by itself:
run `composer install`/`npm run build`, or sync schema/data (migrations,
seeders). If the change needs any of those, either invoke `DEPLOY WEBSITE`
afterward, or — for schema/seed changes only — call the
`POST /deploy-sync` endpoint directly (bearer-token authenticated; token
location and exact usage are in `DEPLOYMENT.md`, never in this file).

Before assuming any of the above still applies to a *different* website
project than this one: check that project's own `DEPLOYMENT.md` for
whether auto-deployment is actually configured there. It is not a given —
this website's setup, not a platform default.
