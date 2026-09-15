# CLAUDE5_TESTING.md — AUDIT & TEST WORKFLOW

Kickstarter Word: `TEST PROJECT`

Use this workflow when the user asks for an audit, validation, regression test, release check, or completeness check.

## Required reading
Read:
- `claude_tasks/CLAUDE0_LEGACY_MASTER.md`
- `PROJECT_SPEC.md` when present
- applicable engineering and test standards under `docs/`
- deployment instructions when testing the production website.

## Scope
Test the requested project without silently changing unrelated functionality.

For apps, validate buildability, core functions, assets, release outputs, and channel separation.

For the website, validate navigation, app inventory, app pages, images, pricing, links, downloads, responsive behavior, HTTPS, and obvious console/server errors where accessible.

When comparing localhost with production, report discrepancies explicitly. Do not say they match unless actually verified.
