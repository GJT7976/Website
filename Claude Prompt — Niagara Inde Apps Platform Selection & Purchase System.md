# NIAGARA INDE APPS — PLATFORM SELECTION, BUY & DOWNLOAD SYSTEM

Modify the Niagara Inde Apps website purchasing system so applications are sold according to the platform/license selected by the customer.

This requirement applies to the public App pages, Stripe checkout, customer orders, downloads, administrator backend, sales records, and application management.

The customer must NEVER be given developer/build files that are not intended for end users.

---

# 1. CUSTOMER-FACING RULE

Do not simply display:

**BUY APP**

without establishing which platform/version the customer is purchasing.

For applications available on multiple platforms, the purchasing workflow must be:

**Choose Platform → See Price → Buy Now → Stripe Checkout → My Downloads / Access**

The customer should understand what they are purchasing without needing to understand Flutter terminology.

---

# 2. SUPPORTED SALES OPTIONS

Allow the administrator to enable any combination of these options PER APP:

### Android

Customer receives the signed production APK for direct installation.

Display publicly as:

**Android**

Supporting text:

"For Android phones and tablets."

Do NOT call it "APK Version" in the main customer interface.

---

### Windows

Customer receives the appropriate production Windows installer.

Display publicly as:

**Windows**

Supporting text:

"For compatible Windows PCs and tablets."

---

### Android + Windows

Customer receives access to BOTH platform versions.

Display as:

**Android + Windows Bundle**

Supporting text:

"One purchase. Install the app on your compatible Android and Windows devices, subject to the app's license terms."

Do NOT put the two files into one ZIP merely because they were purchased together.

After purchase, provide separate download buttons.

Example:

**Download for Android**

**Download for Windows**

---

### Web/PWA

Where a commercial web version exists, display:

**Web App**

Supporting text:

"Use the app from a compatible web browser. No installation required."

Do NOT give customers the Flutter Web build files.

The website provides access to the hosted application.

---

### Complete / All-Platform Edition

If enabled by the administrator:

**Complete Edition**

Supporting text:

"Get all customer platforms included with this app."

The exact included platforms must be displayed before purchase.

For example:

✓ Android  
✓ Windows  
✓ Web App Access

Do not promise iPhone/iPad/macOS unless those platforms are actually available.

---

# 3. FILES THAT MUST NEVER BE SOLD TO ORDINARY CUSTOMERS

Never expose or include:

- `.aab`
- Flutter source code
- Dart source
- Git repository
- Android signing keystore
- passwords
- API secrets
- Stripe secrets
- `.env`
- database credentials
- developer configuration
- debug builds
- unsigned builds
- source maps containing sensitive information
- private certificates

The Android `.aab` is an internal publishing artifact.

It must remain in the developer/admin release system and must NEVER appear in the public customer download area.

---

# 4. ANDROID DIRECT SALES

Niagara Inde Apps sells applications directly from its own website.

Therefore support direct delivery of a properly signed:

`.apk`

The APK must be:

- release build
- production signed
- versioned
- associated with the correct app
- associated with the correct platform entitlement
- protected against unauthorized public download as reasonably possible

Never distribute a debug APK.

---

# 5. ANDROID CUSTOMER NOTICE

When Android Direct Download is enabled, show a short notice BEFORE purchase.

Use customer-friendly wording similar to:

**Direct Android Download**

"This Android app is purchased and downloaded directly from Niagara Inde Apps rather than through Google Play. Android may ask you to allow installation from this source when installing the app."

Also provide:

**Learn how to install**

The installation help page should explain the process without frightening the customer.

Include:

"Only install Niagara Inde Apps software obtained from our official website."

Do not imply that Android's security warning is an error.

---

# 6. OPTIONAL GOOGLE PLAY OPTION

Administrator must be able to configure Android delivery as:

- Direct Download
- Google Play
- Both
- Not Available

If Google Play is selected, store:

- Google Play URL

If Both is selected, customer can see:

**Get on Google Play**

and/or

**Buy Direct**

depending on the app's sales configuration.

Do not upload or automate Google Play publishing through the public website.

---

# 7. WINDOWS DELIVERY

Allow administrator to upload/configure the proper customer-facing Windows package.

Potential types:

- `.msix`
- `.msixbundle`
- `.exe` installer

Customer-facing wording remains simply:

**Windows**

Do not make ordinary customers choose among MSIX/MSIXBundle/EXE unless there is a legitimate compatibility reason.

---

# 8. OPTIONAL MICROSOFT STORE

Administrator can configure:

- Direct Windows Download
- Microsoft Store
- Both
- Not Available

Store the Microsoft Store URL when applicable.

---

# 9. WEB/PWA DELIVERY

Treat Web/PWA differently.

Do NOT give the customer:

- web build ZIP
- JavaScript source package
- Flutter Web deployment directory

Instead provide:

**Launch Web App**

The customer uses the hosted version.

Store:

- web app URL
- access type
- whether login is required
- whether purchase grants access
- entitlement status

---

# 10. DEMO IS DIFFERENT FROM PAID WEB APP

Do not confuse:

**Live Demo**

with:

**Web App**

The Live Demo is a restricted/sample experience.

The paid Web App is the actual purchased application.

Possible buttons:

**Try Live Demo**

and

**Buy Web App**

or after purchase:

**Launch Web App**

These are separate entitlements.

---

# 11. PRODUCT PAGE BUYING INTERFACE

Every paid app page should contain a professional purchasing panel.

Example:

## Get Niagara POS

**Choose how you want to use Niagara POS**

### Android

Android phones & tablets

Direct secure download from Niagara Inde Apps

**$X.XX CAD**

[SELECT]

---

### Windows

Windows PCs & tablets

Easy Windows installation

**$X.XX CAD**

[SELECT]

---

### Android + Windows

Use Niagara POS on both platforms.

Includes:

✓ Android  
✓ Windows

**$X.XX CAD**

[SELECT BUNDLE]

---

### Web App

Use Niagara POS through your browser.

No installation required.

**$X.XX CAD**

[SELECT WEB]

---

### Complete Edition

Everything included in this application's Complete Edition.

✓ Android  
✓ Windows  
✓ Web Access

**$X.XX CAD**

[BEST VALUE]

[SELECT COMPLETE]

Only show options actually enabled for that app.

---

# 12. SELECTION UX

Selecting an edition should visually highlight the card.

Then show an Order Summary.

Example:

## Your Selection

**Niagara POS**

Edition:

**Android + Windows Bundle**

Includes:

✓ Android Download  
✓ Windows Download

Price: `$XX.XX CAD`

Applicable tax: `$X.XX`

Total: `$XX.XX CAD`

[BUY NOW]

Do not surprise the customer with a different edition at Stripe checkout.

---

# 13. BUY NOW

The primary purchase button should say:

**Buy Now**

or:

**Buy Niagara POS**

The platform/edition selection must already be established.

Do not make the customer choose their platform after payment.

---

# 14. STRIPE CHECKOUT

When Buy Now is pressed:

1. Validate selected app.
2. Validate selected edition.
3. Retrieve current price from the server/database.
4. Determine configured applicable tax treatment.
5. Create Stripe Checkout session.
6. Attach internal metadata.

Metadata should safely identify:

- internal app ID
- edition ID
- order ID
- currency

Do not trust price information sent by the browser.

All prices must be retrieved server-side.

---

# 15. EDITIONS DATABASE

Create a proper database model.

Example:

`app_editions`

Fields should include approximately:

- id
- app_id
- name
- slug
- description
- price_minor_units
- currency
- active
- featured
- sort_order
- stripe_product_id
- stripe_price_id
- created_at
- updated_at

Examples:

`android`

`windows`

`android-windows`

`web`

`complete`

Do not hard-code these globally because not every app will support every platform.

---

# 16. PLATFORM ENTITLEMENTS

Create:

`edition_entitlements`

Each edition defines what the customer receives.

Examples:

Android Edition:

`android_download = true`

Windows Edition:

`windows_download = true`

Bundle:

`android_download = true`

`windows_download = true`

Complete:

`android_download = true`

`windows_download = true`

`web_access = true`

Use normalized database relations where appropriate rather than an uncontrolled collection of boolean columns if the architecture benefits from it.

---

# 17. RELEASE FILE MANAGEMENT

Create an administrator release-file system.

Admin route concept:

**Apps → Niagara POS → Releases**

For each release store:

- app
- platform
- version
- build number/version code
- release date
- minimum OS
- file
- file size
- checksum
- active/current version
- release notes

Platform examples:

- Android
- Windows

The current production release should be clearly identified.

---

# 18. PROTECTED DOWNLOADS

Paid application files must NOT simply be stored at an obvious public URL such as:

`/downloads/NiagaraPOS.apk`

Do not permit anyone who discovers a URL to bypass purchasing.

Use authenticated/authorized download delivery.

When the customer clicks Download:

1. Identify customer/order.
2. Verify successful payment.
3. Verify entitlement.
4. Verify requested platform.
5. Resolve current authorized release.
6. Generate/stream a protected download.

Signed temporary URLs may be used where appropriate.

---

# 19. MY DOWNLOADS

Create customer area:

**My Account → My Apps**

or:

**My Downloads**

Example:

# My Apps

## Niagara POS

Purchased:

September XX, 2026

Edition:

**Android + Windows Bundle**

### Android

Version 1.2.0

Updated: September XX, 2026

[DOWNLOAD FOR ANDROID]

[INSTALLATION HELP]

### Windows

Version 1.2.0

Updated: September XX, 2026

[DOWNLOAD FOR WINDOWS]

[INSTALLATION HELP]

Do not display AAB.

---

# 20. WEB ENTITLEMENT

If Web access was purchased:

### Web App

Status:

**Active**

[LAUNCH WEB APP]

Do not display downloadable web files.

---

# 21. PURCHASE RECORD

Every completed order must snapshot:

- app name
- edition
- included platforms
- purchase price
- currency
- discounts
- tax
- total
- purchase date
- customer
- Stripe transaction identifiers
- license terms/version if implemented

Changing an app's future price must not alter historical orders.

---

# 22. ADMIN PRODUCT CONFIGURATION

For every app, Admin must have:

## Platforms & Sales

### Android

Available: Yes/No

Delivery:

- Direct APK
- Google Play
- Both

Current release

Google Play URL

Price

---

### Windows

Available: Yes/No

Delivery:

- Direct
- Microsoft Store
- Both

Current release

Microsoft Store URL

Price

---

### Web

Available: Yes/No

Demo available: Yes/No

Paid Web App: Yes/No

Web App URL

Price

---

### Bundles

Enable Android + Windows Bundle: Yes/No

Bundle price

Enable Complete Edition: Yes/No

Complete price

Administrator must be able to change these without editing source code.

---

# 23. ADMIN RELEASE UPLOAD

For Android:

Allow only appropriate customer release types.

Preferred:

`.apk`

Do NOT accidentally make `.aab` customer-downloadable.

If the administration system stores an AAB for internal release management, it must be explicitly marked:

**Developer / Store Publishing Only — Never Customer Download**

and protected by administrator permissions.

For Windows:

Support configured customer installer formats.

---

# 24. VERSION UPDATES

A customer's entitlement belongs to the purchased platform/edition, not to one specific physical file.

Therefore when Niagara Inde Apps uploads:

`Niagara POS Android 1.3.0`

a customer who owns Android should see the current permitted release according to the app's update policy.

Do not require uploading a separate file for every historical order.

---

# 25. UPDATE POLICY

Create configurable app-level/edition-level update policy.

Examples:

**Updates Included**

or future:

**Major Upgrades Sold Separately**

Initially default Niagara Inde Apps apps to:

**Updates Included**

unless administrator changes it.

Do not implement complicated upgrade pricing unless needed.

---

# 26. LICENSE DISPLAY

Before purchase clearly state the license scope.

Do not use vague language such as:

"Buy these files."

Use:

**Software License**

For example:

"Your purchase grants you a license to use the selected Niagara Inde Apps edition according to its license terms."

The administrator should be able to configure whether a product is:

- Personal License
- Single Business License
- Other

---

# 27. POS EXAMPLE

For Niagara POS, prepare support for:

### Android

### Windows

### Android + Windows Bundle

### Web App, if the production hosted version is eventually offered

### Complete Edition

A reasonable business-oriented license label would be:

**Single Business License**

Do not finalize actual prices until configured by the owner.

---

# 28. SHOPPING CART

If only one application is being purchased, Buy Now may go directly to checkout.

If multiple app purchases are supported, provide:

**Add to Cart**

The cart must preserve:

- app
- edition
- quantity where legitimate
- price
- currency

Do not allow quantity to imply multiple licenses unless licensing rules support that.

---

# 29. CUSTOMER RECEIPT

Receipt/order confirmation must identify the purchased edition.

Example:

**Niagara POS**

Android + Windows Bundle

Single Business License

Subtotal

HST/other applicable tax

Total CAD

Do not merely say:

"Niagara POS — 1"

because the customer needs a permanent record of which platform rights were purchased.

---

# 30. SALES REPORTING

Admin sales exports must include:

- app
- edition
- platforms included
- subtotal
- tax
- total
- currency
- Stripe fees where available
- refund
- customer province
- customer country
- transaction date

This enables Niagara Inde Apps to determine which platform editions are selling.

---

# 31. CUSTOMER INSTALLATION HELP

Create:

`/support/install/android`

Explain direct APK installation.

Create:

`/support/install/windows`

Explain Windows installation.

Keep instructions version-aware where necessary.

---

# 32. ANDROID INSTALLATION HELP

Explain that the customer purchased directly from Niagara Inde Apps.

Typical flow:

1. Download Android version from My Apps.
2. Open the downloaded file.
3. Android may request permission to install apps from the browser/file manager being used.
4. Follow Android's security prompt.
5. Install Niagara Inde Apps application.
6. Disable the temporary "install unknown apps" permission afterward if desired.

Exact wording/screens vary by Android version/device.

Do not tell customers to disable Android security globally.

---

# 33. WINDOWS INSTALLATION HELP

Provide appropriate instructions for the actual installer format.

If Windows displays publisher/security information, explain it accurately.

The production application should be properly signed when code-signing infrastructure is available.

Do not tell customers to defeat Windows security protections unnecessarily.

---

# 34. DOWNLOAD SECURITY VS CUSTOMER CONVENIENCE

Do not make legitimate customers fight the website.

Use reasonable controls:

- customer authentication
- order entitlement
- temporary signed download URL
- sensible rate limiting

Do not use one-time-only downloads unless the owner specifically requests it.

Customers may legitimately need to reinstall software.

---

# 35. REFUNDS

If an order is refunded:

Record refund state.

Do not delete the order.

The entitlement policy should be configurable.

Normally a fully refunded direct purchase should no longer grant new downloads/access unless administrator overrides it.

Keep financial and audit history.

---

# 36. ADMIN OVERRIDE

Owner/Admin can manually:

- grant entitlement
- revoke entitlement
- restore entitlement
- resend purchase email

Every override requires an audit-log entry.

---

# 37. DO NOT ZIP MULTI-PLATFORM PURCHASES

For normal customer delivery:

Do NOT package Android and Windows into one ZIP.

Instead:

Purchase:

**Android + Windows Bundle**

Then customer receives:

[Download Android]

[Download Windows]

This improves:

- customer clarity
- update management
- bandwidth
- version control
- support
- platform-specific installation instructions

---

# 38. PUBLIC LANGUAGE

Avoid unnecessary developer jargon.

Customer sees:

**Android**

not:

`release-arm64-v8a.apk`

Customer sees:

**Windows**

not:

`x64.msixbundle`

Customer sees:

**Web App**

not:

`Flutter Web PWA build`

Technical filenames may appear in browser downloads, but the purchasing interface should remain understandable.

---

# 39. CUSTOMER PURCHASE PANEL — FINAL UX

Use this general hierarchy:

# Choose Your Version

Select where you want to use the app.

[ANDROID]

For Android phones and tablets.

$XX.XX CAD

---

[WINDOWS]

For Windows PCs and tablets.

$XX.XX CAD

---

[ANDROID + WINDOWS]

Use both versions.

$XX.XX CAD

**BEST VALUE** when configured.

---

[WEB APP]

Use it in your browser.

$XX.XX CAD

---

[COMPLETE EDITION]

All currently listed platforms included in this edition.

$XX.XX CAD

---

After selection:

# Your Order

Niagara POS

**Android + Windows Bundle**

Single Business License

Includes:

✓ Android  
✓ Windows

Subtotal: `$XX.XX CAD`

Applicable tax calculated according to checkout configuration.

[BUY NOW]

[Try Live Demo]

---

# 40. CRITICAL RULES

1. NEVER sell the AAB to the customer.
2. NEVER expose signing keys.
3. NEVER expose source code unless a separate source-code license is intentionally created.
4. Android direct download = signed release APK.
5. Windows direct download = appropriate production installer.
6. Web/PWA = hosted access, not a ZIP of web files.
7. Bundle customers receive separate platform download buttons.
8. Stripe orders must record the exact purchased edition.
9. Downloads must require a valid entitlement.
10. Prices must be verified server-side.
11. Historical order values must remain immutable.
12. Tax collected must remain separately recorded from revenue.
13. Admin controls which editions each application offers.
14. Customer must know exactly what is included BEFORE paying.
15. Do not hard-code platform prices into templates.
16. Do not display unavailable platforms.
17. Do not call a demo a purchased Web App.
18. Keep customer-facing language simple and professional.

Implement this system as part of the existing Niagara Inde Apps Laravel website without replacing working functionality unnecessarily.