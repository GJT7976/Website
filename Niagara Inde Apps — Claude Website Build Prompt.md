# NIAGARA INDE APPS
## MASTER WEBSITE BUILD PROMPT FOR CLAUDE CODE

Build a complete, production-quality website and administration system for:

**NIAGARA INDE APPS**

This is an independent Canadian app-development business based in Niagara, Ontario, Canada.

The website must showcase, demonstrate, sell, and support software applications created by Niagara Inde Apps.

This is NOT a WordPress project.

Do NOT use:

- WordPress
- Wix
- Squarespace
- Webflow
- Shopify site builder
- Elementor
- Joomla
- Drupal
- visual page builders
- no-code builders

Build the website from real source code.

---

# 1. RECOMMENDED TECHNOLOGY

Use:

- Laravel
- PHP
- MySQL / MariaDB
- Blade templates
- Tailwind CSS
- Alpine.js where lightweight JavaScript interaction is useful
- Vite
- Stripe official SDK/API
- Composer
- npm

Use Laravel as the primary backend framework.

The completed website must run:

1. locally on a Windows development computer
2. on localhost before deployment
3. on a conventional Hostinger PHP/MySQL hosting environment
4. through HTTPS in production

Avoid an architecture that requires an expensive permanent Node.js server unless absolutely necessary.

Laravel should perform the primary server-side work.

---

# 2. LOCAL DEVELOPMENT

The owner must be able to preview and operate the entire website locally before uploading it.

Support a straightforward Windows localhost environment.

Preferred options:

- Laragon
- Laravel's local development server
- MySQL/MariaDB
- optionally Docker only if it substantially improves the workflow

Provide clear documentation in:

`LOCAL_SETUP.md`

Include exact instructions for:

- installing dependencies
- copying `.env.example` to `.env`
- creating the local database
- configuring MySQL
- generating the Laravel application key
- running migrations
- running seeders
- installing frontend dependencies
- starting Vite
- starting Laravel
- opening the website in a browser
- accessing the administrator panel

Typical development URL:

`http://127.0.0.1:8000`

or an equivalent Laragon local domain.

Do not require an Internet connection for ordinary local website development except for installing dependencies or accessing Stripe's external services.

---

# 3. HOSTING TARGET

The production hosting target is:

**Hostinger**

Architect the application around conventional:

- PHP
- Laravel
- MySQL/MariaDB
- HTTPS
- public_html/public routing
- environment variables
- cron jobs where supported
- SMTP/email configuration

Create:

`HOSTINGER_DEPLOYMENT.md`

Document:

- production `.env`
- MySQL setup
- document root
- Laravel public directory
- Composer deployment
- migrations
- storage linking
- permissions
- Stripe webhook setup
- SSL
- email
- scheduled jobs
- backups
- production caching

Do not hard-code localhost URLs.

Use environment configuration.

---

# 4. BRAND

Business name:

**NIAGARA INDE APPS**

The spelling **INDE** is intentional.

Do not automatically change it to "Indie."

Brand positioning:

**Smart Tools for Real People**

Additional brand concepts:

- Canadian-built software
- independent development
- practical software
- small-business tools
- offline-capable applications
- privacy-conscious software
- useful rather than complicated
- Niagara roots
- global potential

Possible supporting language:

**Ideas. Innovation. Independent.**

**Local Roots. Global Ideas.**

**Independent Apps for a Stronger Tomorrow.**

Do not overload the site with slogans.

Keep the presentation professional.

---

# 5. FONT

Use the:

**TOLA font**

throughout the public website and administration interface wherever appropriate.

Implement it properly with CSS `@font-face` if the licensed font files are supplied.

Create typography tokens for:

- display heading
- H1
- H2
- H3
- body
- small text
- buttons
- labels
- forms
- navigation

If TOLA font files have not yet been supplied:

- create the font infrastructure
- use a visually compatible temporary fallback
- document the missing TOLA font files as an owner action
- do NOT download an unlicensed copy
- do NOT silently substitute another font permanently

---

# 6. VISUAL DESIGN

Create a sophisticated, modern, light-oriented design.

Do NOT make the website predominantly dark.

Use:

- white
- soft off-white
- pale grey
- subtle blue
- Niagara-inspired green
- restrained darker navy/charcoal for typography
- small amounts of stronger green for important calls to action

The result should feel:

- modern
- Canadian
- trustworthy
- creative
- polished
- independent
- technically competent
- approachable

Avoid:

- black full-page backgrounds
- neon cyberpunk styling
- generic SaaS purple gradients
- excessive glassmorphism
- excessive animations
- crowded pages
- stock-template appearance

Use plenty of white space.

Use rounded cards selectively.

Use soft shadows.

Use polished iconography.

Use strong product imagery.

---

# 7. HOME PAGE VISUAL DIRECTION

The landing page should have a visually impressive hero inspired by Niagara.

Create a hero composition with:

- subtle Niagara Falls imagery or Niagara-inspired landscape imagery
- app/device mockups
- desktop/tablet/phone application interfaces
- Niagara Inde Apps branding
- clean white foreground space
- green/blue accent elements

Do not allow the Niagara background to make text difficult to read.

Use overlays, crops, blur, or controlled opacity when necessary.

Suggested hero message:

# NIAGARA INDE APPS

## Smart Tools for Real People

Supporting copy:

"We build practical, easy-to-use applications for small businesses, independent creators, and everyday users across Canada and beyond."

Primary CTA:

**Explore Our Apps**

Secondary CTA:

**Try a Live Demo**

Possible tertiary CTA:

**About Niagara Inde Apps**

---

# 8. HOME PAGE STRUCTURE

Create an excellent home page containing approximately these sections.

## Header

Logo.

Navigation:

- Home
- Apps
- Live Demos
- Pricing
- About
- Support
- Contact

Right side:

- Search
- Cart when relevant
- Currency
- Sign In / Account when implemented

Sticky header on desktop where appropriate.

Responsive mobile navigation.

---

## Hero

Large, visually impressive Niagara/app composition.

Prominent product imagery.

Strong CTA buttons.

---

## Trust / Product Principles

Four concise value points such as:

### Canadian Focus
Built with Canadian businesses in mind.

### Simple & Powerful
Useful software without unnecessary complexity.

### Offline & Online
Applications designed for real-world conditions.

### Real Support
Software created and maintained by an independent developer.

---

## Featured Apps

Dynamic database-driven section.

Show approximately 3–6 selected applications.

Each card:

- app icon
- app name
- short description
- supported platforms
- price or "Free"
- Learn More
- Try Demo when available
- Buy / Get App

Do not hard-code the app catalogue into Blade files.

It must come from the database/admin backend.

---

## Why Niagara Inde Apps

Possible areas:

- practical software
- independent development
- Canadian roots
- small-business understanding
- privacy conscious
- offline capability
- sensible pricing
- cross-platform applications

---

## Live Demo Section

Show apps with web demos available.

CTA:

**Try It Before You Buy It**

Each demo card links to a dedicated demo environment.

---

## Business Types

Show examples visually:

- retail shops
- cafés
- restaurants
- market vendors
- hobby farms
- food trucks
- trades
- service businesses
- home businesses
- independent operators

---

## Canadian / Niagara Story

Create a tasteful Niagara section.

Use Niagara imagery.

Example:

**Local Roots. Global Ideas.**

Keep this section attractive without making the whole site look like a tourism website.

---

## Newsletter / Product Updates

Optional but prepare architecture.

Do not activate bulk email collection unless owner enables it and appropriate privacy/consent wording is provided.

---

## Footer

Include:

- Apps
- Live Demos
- Support
- Contact
- About
- Privacy Policy
- Terms
- Refund Policy
- License
- Cookie information if needed
- Accessibility
- business contact information

Include:

**© Niagara Inde Apps**

and configurable year.

---

# 9. APPS DIRECTORY

Create:

`/apps`

This page lists all published applications.

Admin must be able to:

- create
- edit
- delete/archive
- publish
- unpublish
- reorder
- feature

apps.

Filters:

- All
- Business
- Food & Recipes
- Productivity
- Utilities
- Education
- Lifestyle
- Other

Optional platform filters:

- Android
- Windows
- Web
- PWA
- iOS
- macOS

Each app should have:

- icon
- title
- tagline
- description
- version
- category
- screenshots
- feature graphic
- platform badges
- pricing
- demo availability
- purchase button
- support button

---

# 10. INDIVIDUAL APP ADVERTISING PAGE

Every application requires its own public marketing page.

Example:

`/apps/niagara-pos`

The page should support:

## Hero

- icon
- application name
- feature graphic
- tagline
- platform badges
- price
- Try Demo
- Buy / Download

## Overview

## Features

## Screenshots

Use proper screenshot gallery/lightbox.

## What It Does

## Who It Is For

## Supported Platforms

## Version / Updates

## Requirements

## Privacy

## Support

## FAQ

## Pricing

## Reviews/Testimonial capability

Keep reviews disabled initially unless legitimate reviews exist.

Do not create fake customer testimonials.

---

# 11. WEB DEMO SYSTEM

This is a major requirement.

An app marketing page may contain:

**TRY LIVE DEMO**

The button should open a demo page such as:

`/demo/niagara-pos`

or:

`/demos/niagara-pos`

Each demo may host or embed a Flutter Web build.

Create a proper demo management system.

Admin app fields should include:

- demo enabled
- demo type
- demo URL
- local demo build path if applicable
- demo instructions
- demo warning
- reset mode
- demo version

Possible demo URL:

`https://niagaraindeapps.com/demo/niagara-pos/`

A Flutter Web demonstration may live in a dedicated directory under the production site.

The normal public site and the demo should remain logically separated.

---

# 12. DEMO SAFETY

A demonstration environment must NOT connect to real customer production data.

Demo data must be:

- fake/sample data
- isolated
- resettable

Place a visible:

**DEMO MODE**

indicator inside or around the embedded experience when practical.

Provide:

**Reset Demo**

when possible.

The demo must not:

- charge real cards
- send real payroll
- modify real company data
- use production customer records

---

# 13. ADMINISTRATION BACKEND

Build a professional private administrator dashboard.

Suggested URL:

`/admin`

Require authentication.

Do not expose admin functionality publicly.

Use roles/permissions.

At minimum:

### Owner / Administrator
Full access.

### Content Editor
Can manage appropriate website content but not financial/security settings.

Additional roles can be added later.

---

# 14. ADMIN DASHBOARD

Show useful information:

- apps published
- draft apps
- sales today
- sales month-to-date
- sales year-to-date
- tax collected
- orders
- recent purchases
- product/app revenue
- demo usage if privacy-respecting statistics are later enabled
- recent admin actions

Do not add invasive tracking merely to populate graphs.

---

# 15. ADMIN — APP MANAGEMENT

Create full CRUD.

Administrator can:

- Add App
- Edit App
- Archive App
- Publish App
- Unpublish App
- Feature App
- Duplicate App entry where useful

Fields:

- app name
- slug
- tagline
- short description
- long description
- category
- icon
- feature graphic
- screenshots
- platforms
- version
- release date
- last updated
- regular price
- sale price
- currency
- free/paid
- Stripe product ID
- Stripe price ID
- Google Play URL
- Microsoft Store URL
- Apple URL
- direct purchase enabled
- web demo enabled
- demo URL/path
- documentation URL
- privacy policy
- support information
- system requirements
- status
- featured order
- SEO metadata

---

# 16. CONTENT MANAGEMENT

Create backend editing for:

- homepage sections
- hero text
- navigation
- footer
- about page
- contact information
- FAQs
- support content
- legal pages
- product/application pages

The owner must not need to edit PHP/Blade source simply to change ordinary text.

Do not build a bloated generic WordPress clone.

Create purposeful content editing for this business.

---

# 17. MEDIA LIBRARY

Create an admin media system.

Support:

- app icons
- screenshots
- feature graphics
- hero graphics
- general images

Allow:

- upload
- select
- replace
- delete when unused
- alt text
- title
- description

Generate reasonable thumbnails where appropriate.

Protect against dangerous uploads.

Validate:

- MIME
- extension
- dimensions
- file size

---

# 18. STRIPE

Integrate Stripe using official supported Stripe libraries.

Never store complete card information.

Stripe handles payment-card information.

Create a purchase flow.

Example:

App Page

→ Buy Now

→ Cart/Checkout

→ customer information

→ applicable taxes

→ Stripe payment

→ order created

→ confirmation

→ receipt/invoice

→ access/download instructions

Use:

- Stripe Checkout

or

- Stripe Elements

Choose the solution that gives the best balance of security, Hostinger compatibility, and maintainability.

---

# 19. STRIPE CONFIGURATION

Use environment variables:

`STRIPE_PUBLIC_KEY`

`STRIPE_SECRET_KEY`

`STRIPE_WEBHOOK_SECRET`

Never commit credentials to Git.

Support:

- Stripe test mode
- Stripe production mode

Document how to switch environments safely.

---

# 20. STRIPE WEBHOOKS

Use secure webhook handling.

Handle appropriate events such as:

- completed checkout
- payment succeeded
- failed payment
- refunded transaction

Verify webhook signatures.

Do not trust success solely because the browser returns to a success URL.

Use webhook-confirmed state for financial records when appropriate.

---

# 21. ONTARIO / CANADIAN SALES TAX

Business is based in Ontario, Canada.

Design the tax engine for Canadian software/app sales.

Support:

- GST
- HST
- PST
- QST
- zero rate where applicable
- tax exemptions where legitimate
- configurable jurisdiction logic

Do NOT hard-code one permanent Ontario tax rate into random application code.

Create database-backed/effective-dated tax configuration.

Administrator settings:

**Settings → Taxes**

Tax table fields:

- country
- province/territory
- tax name
- percentage
- effective date
- expiry date
- active
- notes/source

Canadian taxation of digital goods/software can depend on:

- seller registration
- buyer location
- province
- type of transaction
- business/customer circumstances
- current law

Therefore make the implementation configurable.

Before production launch, current Canadian/Ontario tax requirements and Stripe tax configuration must be verified against authoritative sources.

Do not falsely claim automatic CRA compliance.

---

# 22. CUSTOMER LOCATION

Checkout should gather sufficient billing/location information to determine applicable tax according to the configured rules.

Potential fields:

- name
- company
- email
- address
- city
- province/state
- postal/ZIP
- country

Only collect information genuinely needed.

---

# 23. PRODUCTS / APP LICENSING

Prepare the architecture to support:

- free apps
- paid apps
- one-time payment
- optional subscriptions in the future
- external-store purchase links
- direct website purchases

Initial focus:

**one-time app purchases**

Do not implement complicated SaaS subscription infrastructure unless needed.

---

# 24. ORDERS

Database order information:

- order ID
- order number
- date/time
- customer
- customer email
- billing location
- items
- subtotal
- discounts
- tax
- total
- currency
- payment provider
- Stripe IDs
- payment status
- refund status
- order status
- notes

Use immutable records for finalized financial transaction amounts.

---

# 25. SALES ADMINISTRATION

Create:

`Admin → Sales`

Features:

- date filtering
- customer search
- app filter
- payment-status filter
- country
- province
- transaction
- tax
- refunds

Show:

- gross sales
- discounts
- refunds
- net sales
- sales taxes
- processor fees where available/configured
- net receipts

---

# 26. CANADIAN INCOME TAX / ACCOUNTING EXPORT

Create accountant-friendly sales reporting.

This is important.

Create:

`Admin → Accounting`

Provide reporting periods:

- Today
- This Month
- This Quarter
- This Year
- Fiscal Year
- Custom

Show:

### Gross Sales

### Refunds

### Net Sales

### GST/HST/PST/QST collected

### Stripe processing fees

### Other selling expenses where entered

### Net receipts

### Sales by application

### Sales by province

### Sales by country

### Tax liability summary

Do NOT call sales-tax collections profit.

Keep revenue and tax liabilities separate.

---

# 27. SALES EXPORT

Allow export to:

- CSV
- XLSX
- accountant-friendly PDF summary

Export transaction-level records.

Suggested fields:

- transaction date
- order number
- customer
- province
- country
- app/product
- quantity
- subtotal
- discount
- tax type
- tax amount
- total
- Stripe fee
- refund
- net receipt
- payment status

Allow fiscal-year export for Canadian income-tax record keeping.

---

# 28. EXPENSE TRACKING

Create an optional lightweight business expense section.

Admin:

`Accounting → Expenses`

Fields:

- date
- vendor
- category
- description
- subtotal
- tax
- total
- payment method
- receipt upload
- notes

Categories configurable.

Examples:

- hosting
- software
- developer fees
- advertising
- Stripe fees
- equipment
- Internet
- office
- professional fees
- licenses
- other

This can provide a simple business overview without pretending to replace accounting software.

---

# 29. PROFIT OVERVIEW

Where enough data exists, show:

Revenue

minus

Refunds

minus

Payment-processing fees

minus

Recorded business expenses

=

Estimated operating result

Clearly label this as a management report.

Do not label it an official tax return.

---

# 30. BACKUPS

Implement website/database backup capability.

Admin:

`Settings → Backup`

Support:

- database backup
- media backup
- full site-data backup

Provide downloadable backup packages where practical.

Do not expose database credentials in backups.

Allow:

- manual backup
- automated server backup instructions
- retention recommendations

For Hostinger deployment, also document available host-level backup options separately from application backups.

---

# 31. SECURITY

Implement:

- Laravel CSRF protection
- validation
- parameterized ORM/database queries
- secure authentication
- password hashing
- rate limiting
- login throttling
- authorization policies
- admin access control
- secure cookies
- session protection
- Stripe signature verification
- file-upload validation
- XSS protection
- appropriate security headers
- environment secrets
- HTTPS enforcement in production

Do not create a default password such as:

`admin/admin`

First administrator creation must be secure.

---

# 32. TWO-FACTOR AUTHENTICATION

Prepare administrator accounts for optional 2FA.

Recommended for Owner/Admin.

---

# 33. AUDIT LOG

Track important administrator activity.

Examples:

- app created
- app deleted
- price changed
- tax changed
- order adjusted
- refund processed
- admin created
- permissions changed
- website content changed
- backup created

Fields:

- administrator
- action
- resource
- time
- IP where appropriate
- before/after summary where reasonable

Do not create a normal admin action to erase the audit log casually.

---

# 34. CUSTOMER ACCOUNTS

Architect customer accounts but do not make them mandatory for simple purchases unless needed.

Guest checkout should be possible if appropriate.

Optional customer account:

- orders
- downloads
- licenses
- receipts
- profile
- support

---

# 35. SUPPORT SYSTEM

Create:

`/support`

Support:

- FAQ
- app selection
- installation help
- troubleshooting
- contact support

Prepare optional ticket capability.

Initially email/contact support can be sufficient if a complete ticket system is unnecessary.

---

# 36. CONTACT PAGE

Create a professional contact form.

Fields:

- name
- email
- subject
- app
- message

Protect against spam with:

- honeypot
- rate limiting
- configurable CAPTCHA only if needed

Do not make CAPTCHA unnecessarily intrusive.

---

# 37. ABOUT PAGE

Tell the Niagara Inde Apps story.

Tone:

- independent Canadian developer
- practical applications
- Niagara-based
- focused on useful software
- small-business oriented
- thoughtful pricing
- user ownership/control
- privacy-conscious approach

Do not invent awards, customer counts, company history, employees, partnerships, or testimonials.

---

# 38. SEO

Implement proper:

- titles
- meta descriptions
- canonical URLs
- Open Graph
- Twitter/X cards where appropriate
- structured data
- sitemap.xml
- robots.txt
- clean slugs
- semantic HTML

Each app has its own SEO fields.

Generate application structured data where appropriate.

---

# 39. PERFORMANCE

Target excellent performance.

Use:

- optimized images
- WebP/AVIF where appropriate
- lazy loading
- responsive image sizes
- minimized scripts
- CSS optimization
- Laravel caching
- appropriate database indexes
- pagination
- CDN-ready asset structure

Do not load 5 MB hero images directly.

---

# 40. ACCESSIBILITY

Follow strong accessibility practices.

Include:

- keyboard navigation
- focus states
- color contrast
- meaningful alt text
- form labels
- accessible dialogs
- semantic headings
- sensible ARIA only when necessary
- reduced-motion respect

---

# 41. RESPONSIVE DESIGN

The site must look excellent on:

- phone
- tablet
- laptop
- desktop
- ultrawide screens

Do not simply shrink desktop layouts.

---

# 42. LANGUAGE ARCHITECTURE

Prepare the public site for future multilingual support.

Initial primary language:

**English**

Architect translation files so French and other languages can be added without rewriting templates.

Do not automatically expose unfinished translations.

---

# 43. CURRENCY

Primary:

**CAD**

Architect for future currencies such as:

- USD
- EUR
- GBP

Always store currency code with financial records.

Never assume `$` means CAD internally.

---

# 44. LEGAL PAGES

Create editable drafts/templates for:

- Privacy Policy
- Terms of Use
- Software Purchase Terms
- Refund Policy
- Cookie Policy where needed
- End User License information where relevant

Do not make unverifiable legal claims.

Use admin editing.

---

# 45. DATABASE

Design a proper relational schema.

Likely entities include:

- users
- roles
- permissions
- apps
- app_categories
- app_platforms
- app_media
- app_features
- demos
- customers
- orders
- order_items
- payments
- refunds
- tax_rules
- sales_tax_lines
- expenses
- expense_categories
- pages
- page_sections
- media
- settings
- support_requests
- audit_logs

Use:

- migrations
- foreign keys
- indexes
- timestamps
- soft deletion where appropriate

Never use a collection of JSON files as the primary business database.

---

# 46. DEMO MANAGEMENT

Admin:

`Apps → App → Demo`

Allow:

- Enable Demo
- Demo URL
- Demo type
- Demo version
- Demo instructions
- Reset instructions
- Demo availability
- Demo maintenance message

For embedded demos, create an attractive surrounding frame.

Show:

- app name
- Demo Mode
- Full Screen Demo
- Back to App
- Buy App
- Reset Demo

---

# 47. WEB DEMO ROUTING

Design so a Flutter Web app can be uploaded to:

`public/demos/{app-slug}/`

or an equivalent safe deployment location.

Ensure Laravel routing does not break Flutter Web routes/assets.

Document the correct web `base-href` configuration for Flutter when deployed inside a subdirectory.

Example:

`/demos/niagara-pos/`

Do not assume every Flutter Web build will work at root `/`.

---

# 48. ADMIN UI/UX

The admin interface should be equally professional.

Use a clean light interface.

Desktop:

- left sidebar
- top utility bar
- content workspace

Sections:

- Dashboard
- Apps
- Demos
- Orders
- Sales
- Accounting
- Expenses
- Customers
- Content
- Media
- Support
- Users
- Settings
- Backups
- Audit

Avoid enormous tables without filtering.

Use:

- search
- sorting
- pagination
- filters
- bulk actions only where safe

---

# 49. PUBLIC UI MICROINTERACTIONS

Use restrained animation.

Examples:

- subtle hover elevation
- fade-in content
- product card movement of only a few pixels
- button states
- smooth menu opening
- image transitions

Avoid animation that delays navigation or feels gimmicky.

---

# 50. GRAPHICS

Use custom-feeling brand visuals based around:

- Niagara
- waterfalls
- green leaves
- blue water
- modern applications
- device screens
- app icons
- business tools

Home graphics should feature actual Niagara Inde Apps software wherever possible.

Do not fill the site with unrelated generic stock photographs.

---

# 51. APP SCREEN MOCKUPS

Create reusable device-display components for:

- desktop app screenshot
- tablet screenshot
- phone screenshot
- browser app screenshot

Use them throughout the site.

---

# 52. CALL-TO-ACTION SYSTEM

Create reusable CTA styles.

Primary:

**Explore Apps**

**Try Live Demo**

**Buy Now**

Secondary:

**Learn More**

**View Screenshots**

**Get Support**

Use consistent design.

---

# 53. SEARCH

Implement site search.

Search:

- apps
- FAQs
- support articles

Do not expose private admin/customer information.

---

# 54. ANALYTICS

Do NOT automatically add:

- Google Analytics
- Meta Pixel
- advertising trackers
- cross-site trackers

Create an optional analytics integration setting for later.

If analytics are eventually enabled, update the privacy/cookie system accordingly.

---

# 55. EMAIL

Prepare SMTP email.

Email types:

- purchase receipt
- order confirmation
- refund confirmation
- contact message notification
- support acknowledgment
- administrator security notices

Use environment-based SMTP settings.

Provide branded HTML email templates.

---

# 56. RECEIPT / INVOICE

For direct purchases, generate a professional transaction receipt.

Include:

- Niagara Inde Apps
- order number
- date
- customer
- app
- subtotal
- tax breakdown
- total
- currency
- payment status

Do not call it an official tax invoice unless required information and rules are actually satisfied.

---

# 57. ADMIN SETTINGS

Create organized settings pages.

### Business

- business name
- legal name
- address
- city
- province
- postal code
- country
- email
- telephone
- logo
- business/tax identifiers

### Store

- default currency
- receipt settings
- order prefix

### Taxes

- Canadian tax rules

### Payments

- Stripe configuration status

Never display complete secret keys.

### Email

- SMTP configuration status

### Site

- home settings
- SEO
- branding

### Backup

### Security

---

# 58. ENVIRONMENT FILE

Create a complete `.env.example`.

Include placeholders only.

Never include real secrets.

Examples:

`APP_NAME`

`APP_URL`

`DB_HOST`

`DB_DATABASE`

`DB_USERNAME`

`DB_PASSWORD`

`MAIL_*`

`STRIPE_KEY`

`STRIPE_SECRET`

`STRIPE_WEBHOOK_SECRET`

---

# 59. SEED DATA

Provide realistic demo seed data for local testing.

Example apps:

- Niagara POS
- Recipe/food application
- inventory/business application

Clearly identify all as seed/demo content.

Do not accidentally publish seed customers/orders to production.

---

# 60. AUTOMATED TESTING

Write tests for important functions.

Include:

### Authentication

### Admin permissions

### App CRUD

### Checkout

### Tax calculations

### Stripe webhook handling

### Sales reports

### Exports

### Refunds

### Demo routing

### Public page rendering

### Security/authorization

Use unit and feature tests appropriately.

---

# 61. MONEY HANDLING

Never use ordinary binary floating-point numbers for financial calculations.

Use:

- integer cents/minor units

or a proven decimal-money implementation.

Example:

`$19.99 CAD`

stored internally as:

`1999`

with:

`CAD`

---

# 62. FINANCIAL AUDITABILITY

Do not silently change historical orders when:

- app price changes
- tax rate changes
- app name changes
- customer information changes later

Snapshot relevant financial/order information at purchase time.

---

# 63. REFUNDS

Support Stripe-related refunds properly.

Record:

- original transaction
- refund ID
- date
- amount
- reason
- administrator
- Stripe status

Never erase the original sale.

---

# 64. ADMIN EXPORT

Provide exports for:

- Orders
- Sales
- Taxes
- Expenses
- Customers where privacy permits
- Products/Apps

Use CSV as the universally supported baseline.

---

# 65. DATABASE BACKUP

Admin full backup should include:

- database
- uploaded application media
- configuration that does not expose secrets

Do NOT include:

- `.env`
- payment secret keys
- plaintext credentials

---

# 66. DEPLOYMENT SAFETY

Production configuration:

- `APP_ENV=production`
- `APP_DEBUG=false`

Never expose stack traces or secrets to public visitors.

---

# 67. GIT

Initialize/use Git appropriately.

Create:

`.gitignore`

Exclude:

- `.env`
- vendor
- node_modules
- temporary build files
- logs
- local databases
- backups containing private business data

Commit source structure logically.

---

# 68. DOCUMENTATION

Create:

`README.md`

`LOCAL_SETUP.md`

`HOSTINGER_DEPLOYMENT.md`

`STRIPE_SETUP.md`

`DEMO_DEPLOYMENT.md`

`BACKUP_RESTORE.md`

`ADMIN_GUIDE.md`

`ARCHITECTURE.md`

`SECURITY.md`

---

# 69. FOLDER ORGANIZATION

Keep code professional.

Do not dump business logic into Blade templates.

Use appropriate:

- Controllers
- Models
- Services
- Policies
- Requests
- Jobs
- Events
- Listeners
- Mail classes
- Components
- Database migrations
- Seeders

Stripe and tax logic should have dedicated service classes.

---

# 70. COMPONENT SYSTEM

Create reusable components for:

- navigation
- footer
- app card
- demo card
- platform badge
- price
- button
- feature list
- screenshot gallery
- CTA block
- alerts
- admin data tables
- admin forms
- metrics cards

Do not copy/paste large blocks across pages.

---

# 71. PAGE LIST

Minimum public routes:

`/`

`/apps`

`/apps/{slug}`

`/demos`

`/demo/{slug}`

`/pricing`

`/about`

`/support`

`/contact`

`/privacy`

`/terms`

`/refunds`

Optional:

`/account`

`/account/orders`

---

# 72. ADMIN ROUTES

Protected:

`/admin`

`/admin/apps`

`/admin/apps/create`

`/admin/apps/{id}/edit`

`/admin/demos`

`/admin/orders`

`/admin/sales`

`/admin/accounting`

`/admin/expenses`

`/admin/content`

`/admin/media`

`/admin/support`

`/admin/users`

`/admin/settings`

`/admin/backups`

`/admin/audit`

---

# 73. PUBLIC APPLICATION CARD

Example visual hierarchy:

[APP ICON]

**Niagara POS**

Complete point-of-sale, costing, inventory and business tools.

`Android` `Windows` `Web Demo`

**$X.XX CAD**

[Learn More]

[Try Demo]

[Buy]

Keep cards elegant rather than crowded.

---

# 74. ERROR PAGES

Design custom:

- 404
- 403
- 419
- 429
- 500
- Maintenance

Maintain Niagara Inde Apps branding.

Do not expose technical details.

---

# 75. COOKIE SYSTEM

Do not show a giant cookie banner if the site does not use non-essential cookies.

If only necessary Laravel session/security cookies exist, document them appropriately.

If analytics/marketing cookies are later enabled, implement proper consent before loading them where legally required.

---

# 76. PRIVACY-FIRST APPROACH

Collect the least customer data required.

Do not sell customer information.

Do not use advertising trackers by default.

Allow legitimate business records to be retained according to configured/legal needs.

---

# 77. CREATIVE DIRECTION

The website should visually communicate:

**Niagara + Technology + Independent Canadian Software**

without looking like:

- a tourism site
- a government website
- a giant corporation
- a generic ThemeForest template

Use Niagara as a distinctive brand element rather than the entire subject of the site.

---

# 78. INITIAL HOME PAGE DESIGN

Aim for approximately this composition:

TOP NAVIGATION

↓

LARGE NIAGARA HERO

Left:

NIAGARA INDE APPS

Smart Tools for Real People

Description

Explore Apps

Try Live Demo

Right:

Large tablet/desktop product mockup

phone mockup

application UI

subtle Niagara Falls imagery

↓

TRUST/VALUE ICONS

↓

FEATURED APPLICATIONS

↓

TRY OUR SOFTWARE

Interactive Web Demos

↓

WHY NIAGARA INDE APPS

↓

NIAGARA / CANADA BRAND STORY

↓

SUPPORT / CTA

↓

FOOTER

---

# 79. INITIAL FEATURED PRODUCT

Use **Niagara POS** as an important example application if its assets are available.

Show:

- POS icon
- feature graphic
- POS interface screenshot
- inventory
- sales
- accounting
- payroll
- offline operation
- web demo

Do not imply unavailable features are released unless confirmed by project data.

---

# 80. ADMIN EXPERIENCE

The owner should be able to run ordinary site operations without touching source code.

For example:

To add a new application:

Admin

→ Apps

→ Add App

→ Upload Icon

→ Upload Feature Graphic

→ Enter Name

→ Description

→ Price

→ Select Platforms

→ Add Screenshots

→ Enable Demo

→ Set Demo URL

→ Configure Stripe Product

→ Publish

It should then automatically appear throughout the public website where appropriate.

---

# 81. SALES WORKFLOW

Direct purchase:

User visits app page

↓

Clicks Buy

↓

Checkout

↓

Address/location gathered

↓

Applicable configured tax calculated

↓

Stripe

↓

Verified payment

↓

Order saved

↓

Receipt sent

↓

Download/store/install instructions shown

↓

Sale available in Admin

↓

Tax appears in accounting report

↓

Sale appears in Canadian fiscal-year export

---

# 82. DEMO WORKFLOW

App Advertising Page

↓

Try Live Demo

↓

Demo landing wrapper

↓

Demo Mode explanation

↓

Launch Flutter Web Demo

↓

User tries application

↓

CTA remains available:

**Get Niagara POS**

or relevant app name.

---

# 83. LOCAL TESTING REQUIREMENT

Before production deployment I must be able to test:

- home
- app catalogue
- individual app
- demo
- admin
- add/edit/delete app
- image upload
- Stripe test purchase
- Ontario tax configuration
- sales
- accounting
- CSV export
- backups

all from my Windows development PC.

Document exact steps.

---

# 84. HOSTINGER REQUIREMENT

Do not build something that unnecessarily prevents deployment to a conventional Hostinger PHP/MySQL environment.

If any desired feature requires a Hostinger plan capability that cannot be assumed, isolate the feature and document the requirement instead of making the entire website dependent on it.

---

# 85. PRODUCTION COMPLETION CHECKLIST

Before calling this website finished:

- run Composer install
- run npm install/build
- migrations complete
- seeders tested
- automated tests pass
- production asset build succeeds
- no JavaScript console errors
- no broken links
- no missing images
- admin protected
- CSRF works
- uploads validated
- tax calculations tested
- Stripe test checkout works
- webhook signatures verified
- sales reporting verified
- CSV export verified
- backups verified
- demo routes verified
- responsive layouts checked
- accessibility checked
- SEO metadata checked
- sitemap generated
- APP_DEBUG disabled in production
- secrets excluded from Git

---

# 86. IMPORTANT IMPLEMENTATION PHILOSOPHY

Do not build only a visually attractive homepage.

This must become a genuine business platform.

The priority order is:

1. Correct architecture
2. Security
3. Excellent UI/UX
4. Product/app management
5. Reliable demo hosting
6. Correct purchasing records
7. Configurable Canadian taxes
8. Sales/accounting exports
9. Maintainability
10. Performance

Do not rush into hundreds of lines of UI before establishing the architecture and database.

---

# 87. START HERE

Claude Code:

Begin by creating a project implementation plan.

Then create:

1. architecture
2. database model
3. route map
4. design system
5. public layout
6. admin authentication
7. administration shell
8. app catalogue
9. application detail system
10. media system
11. demo system
12. Stripe integration
13. tax engine
14. orders
15. sales/accounting
16. exports
17. backup
18. testing
19. local documentation
20. Hostinger deployment documentation

Do not stop after generating scaffolding.

Continue through functional implementation.

At every stage preserve working behavior.

Do not replace working modules unnecessarily.

Do not invent secret credentials.

Do not invent legal/tax identifiers.

Do not invent customer testimonials or sales statistics.

Where an owner decision or credential is genuinely required, create a clearly labeled placeholder and continue building everything else that can be completed.

The final result should look and function like a polished independent Canadian software company, not a generic starter template.