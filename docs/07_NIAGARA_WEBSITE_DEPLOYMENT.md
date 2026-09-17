# Niagara Indie Apps Website Deployment Standard

## Purpose

This document governs publishing completed app/site changes to the Niagara Indie Apps production website.

This is a WEBSITE DEPLOYMENT standard. It does not replace or redefine the app build/release rules in `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md` or the entitlement/channel rules in `docs/06_RELEASE_CHANNEL_CONTRACT.md`.

## Source of Truth

The complete, tested localhost Niagara Indie Apps website is the source of truth for website source code, pages, app listings, text, images, styling, navigation, pricing presentation, and intended downloadable products.

Do not assume the existing GitHub repository or Hostinger deployment is current or complete. Audit them before deployment.

Production-only data and secrets are NOT replaced from localhost. Preserve customer accounts, purchases, orders, license keys, device activations, payment records, production database records, environment variables, credentials, private keys, SSL configuration, and other server-only configuration.

## Required Deployment Flow

The preferred flow, when GitHub is the configured production source, is:

`LOCALHOST -> GITHUB -> HOSTINGER -> https://niagaraindieapps.com -> LIVE VERIFICATION`

Do not create a second competing deployment method. First determine how Hostinger is actually configured. If Hostinger deploys directly rather than from GitHub, document the existing method and use the correct safe path.

## Before Any Deployment

1. Inspect the complete localhost website/project recursively.
2. Identify the framework, frontend, backend, routes, app records, assets, downloads, APIs, database configuration, build process, and production requirements.
3. Verify the intended localhost website works.
4. Identify the GitHub repository and production branch, if GitHub is used.
5. Compare localhost with GitHub. Do not assume GitHub is correct.
6. Inspect the current Hostinger deployment, document root, build/output settings, environment configuration, database connection, and GitHub integration if present.
7. Compare the live production website with localhost.
8. Back up the current production website and any production database that could be affected before destructive changes or migrations.
9. Ensure a practical rollback path exists.

## GitHub Rules

If GitHub is part of the deployment architecture, synchronize the correct localhost website source to the correct repository and branch.

Do not commit secrets, passwords, API keys, access tokens, signing credentials, private keys, `.env` secrets, customer information, or payment credentials.

Use Git/diff tools to identify changes. Do not blindly replace the repository.

After a successful deployment, GitHub must represent the deployed source release rather than being left behind localhost or production.

## Production Data Protection

Website synchronization does not mean copying a development database over production.

Never overwrite legitimate production-only data with localhost data. When schema changes are required, use a safe migration and preserve production records.

Never delete a production app merely because it is absent from one local directory. Report unexplained inventory differences before removal unless the user explicitly instructed removal.

## Production Build Checks

Before deployment, run the site's required production build and validation steps. Correct build failures and check for:

- `localhost` or `127.0.0.1` production references
- local development API/database URLs
- hard-coded Windows paths such as `C:\`, `D:\`, or `F:\`
- missing dependencies or imports
- missing images/assets
- missing environment variables
- development-only settings
- incorrect production routes or output directories

A successful build alone does not prove deployment success.

## Deploy the Complete Intended Website State

When a new app is completed and added to localhost, deploy all legitimate website changes needed to make production represent the current completed localhost website. Do not upload only one new app page if other required local website changes belong to the same release.

For each new or updated app, verify applicable app name, description, category, pricing, app page, screenshots, feature graphics, banners, icons, purchase controls, signed APK offering, Windows MSIX/installer/EXE offering, Web/PWA information, license information, routes, assets, and supporting records.

App artifact creation and entitlement behavior remain governed by `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md` and `docs/06_RELEASE_CHANNEL_CONTRACT.md`.

## Live Verification Is Mandatory

After deployment, test `https://niagaraindieapps.com` rather than assuming a successful Git push means a successful release.

Verify the homepage, navigation, app catalog, search/categories/filters when present, app detail pages, images, icons, feature graphics, pricing, purchase controls, download links, APK/Windows offerings, Web/PWA links, privacy/contact/terms pages when present, mobile/desktop layout, HTTPS, CSS, JavaScript, and server responses.

Check for 404s, broken images, broken downloads, JavaScript errors, server errors, missing CSS, missing apps, incorrect prices/links, and mixed-content warnings.

Perform a final localhost-versus-production comparison. Production should contain the same intended apps, text, images, navigation, categories, pricing, features, pages, styling, and download options, except for legitimate production-specific configuration.

## Future New-App Workflow

Whenever the user says a new app has been completed/added to the Niagara Indie Apps localhost website:

1. Inspect and validate the localhost changes.
2. Identify every website file/record required by the new app and any other legitimate local changes.
3. Audit/synchronize the correct GitHub repository and production branch when GitHub is used.
4. Commit with a meaningful message and push only after validation.
5. Deploy through the established Hostinger deployment path.
6. Verify the live production site.
7. Confirm the new app's page, images, pricing, purchase/download links, and supported platforms.
8. Confirm existing apps still work.
9. Confirm localhost, GitHub, and production correspond to the same intended release.

Do not report success until the live website has actually been verified.

## Website-Root DEPLOYMENT.md

The actual Niagara Indie Apps WEBSITE project should maintain a root-level `DEPLOYMENT.md` containing environment-specific operational facts such as:

- production domain
- GitHub repository
- production branch
- Hostinger deployment method
- Hostinger document root
- build command
- build output directory
- required environment-variable NAMES (not secret values)
- database migration procedure
- backup procedure
- deployment procedure
- rollback procedure
- last successful deployment date
- last deployed Git commit

Do not put passwords, tokens, API keys, private keys, or other secret values in `DEPLOYMENT.md`.

If `DEPLOYMENT.md` does not yet exist in the website root, create it only after auditing the real deployment configuration; do not guess its values.

## Final Deployment Report

After a deployment, report:

- localhost status
- GitHub repository and branch used, if applicable
- deployed Git commit, if applicable
- Hostinger deployment method
- production URL
- localhost app count
- production app count
- whether inventories match
- significant files/pages corrected
- tests performed
- remaining problems

End the deployment report with exactly one of:

`DEPLOYMENT VERIFIED — LOCALHOST, GITHUB AND PRODUCTION ARE SYNCHRONIZED`

or

`DEPLOYMENT NOT VERIFIED — ACTION REQUIRED`

Never claim synchronization unless the live production website was checked.
