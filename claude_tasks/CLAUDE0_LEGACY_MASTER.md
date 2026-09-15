# CLAUDE0_LEGACY_MASTER.md — PRESERVED ORIGINAL MASTER INSTRUCTIONS

# CLAUDE.md --- Mandatory App Development Instructions

## Mandatory Project Instructions

This file contains mandatory instructions for Claude Code when working
on this application.

Before modifying, generating, rebuilding, debugging, testing, or
releasing this application, Claude MUST read:

1.  `PROJECT_SPEC.md`
2.  Every `.md` file in the `docs/` directory (including `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md`)
3.  Any additional project-specific Markdown instruction files in the
    project root

These documents define the mandatory engineering, UI/UX, image-storage,
testing, build, release, and quality standards for this project.

Do not begin implementation until these files have been read.

## Niagara Indie Apps Website Deployment

When work involves adding, updating, publishing, synchronizing, or deploying an application on the Niagara Indie Apps website, Claude MUST also follow `docs/07_NIAGARA_WEBSITE_DEPLOYMENT.md`.

The completed, tested localhost Niagara Indie Apps website is the source of truth for website source/content. Do not assume GitHub or Hostinger is current. Website deployment is separate from app build/release packaging and entitlement behavior; `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md` and `docs/06_RELEASE_CHANNEL_CONTRACT.md` remain authoritative for those concerns.

The actual Niagara Indie Apps website project should maintain its own root-level `DEPLOYMENT.md` for audited GitHub/Hostinger/environment-specific deployment facts. Never put secrets in that file.

## Existing Application First

Before making changes:

1.  Inspect the existing project structure.
2.  Inspect `pubspec.yaml` when this is a Flutter project.
3.  Inspect the existing source code.
4.  Identify the application's current architecture.
5.  Identify existing working functionality.
6.  Compare the current implementation against `PROJECT_SPEC.md` and the
    standards in `docs/`.
7.  Preserve working functionality unless a change is necessary to
    satisfy a requirement or correct a defect.

Do not unnecessarily rewrite an existing working application.

## Flutter Requirements

This is a Flutter/Dart application unless `PROJECT_SPEC.md` explicitly
states otherwise.

Before making substantial changes, verify the Flutter project and
dependencies.

After changes, run:

``` bash
flutter pub get
flutter analyze
```

Run available automated tests:

``` bash
flutter test
```

Fix errors introduced by your changes.

Warnings should also be reviewed and corrected when reasonably possible.

## Large Image Library Architecture

Applications may contain hundreds or thousands of content images.

Do NOT automatically bundle the complete full-resolution image library
into the APK, AAB, IPA, MSIX, or other application package.

For large image libraries:

-   Full-resolution content images should normally be stored externally
    and retrieved when required.
-   Use appropriately sized thumbnails for lists, search results, grids,
    cards, and other compact displays.
-   Load images lazily.
-   Do not decode thousands of images into memory.
-   Cache downloaded images on persistent storage where appropriate.
-   The cache MUST be bounded and manageable.
-   Provide placeholders while images are loading.
-   Provide graceful error handling when an image cannot be retrieved.
-   Previously cached images should remain usable offline where
    practical.
-   If an uncached image is unavailable because the device is offline,
    display an appropriate local placeholder.
-   Essential UI graphics, icons, splash assets, logos, and deliberately
    selected offline assets may remain bundled with the application.
-   Do not add thousands of high-resolution images to `assets/images/`
    merely because the source images exist in the repository.

Follow the more detailed requirements in the engineering standards.

## Image Optimization

When processing content images:

-   Prefer WebP or another efficient format supported by the target
    platform.
-   Do not use a 1200x1200 image where a small thumbnail is sufficient.
-   Generate or use appropriately sized display versions.
-   Preserve visual quality while avoiding unnecessarily large files.
-   Avoid loading images at substantially higher resolution than the
    displayed size.
-   Use memory-efficient image decoding.

For applications with very large libraries, separate:

1.  Thumbnail images
2.  Normal display images
3.  High-resolution/original images when genuinely required

## Offline Image Support

If `PROJECT_SPEC.md` enables offline image downloading, implement it as
an explicit user-controlled feature.

Examples:

-   Download images for offline use
-   Download selected collection
-   Remove downloaded images
-   Clear image cache

Do not silently download the entire image library.

Clearly distinguish between temporary cached images and intentionally
downloaded offline content.

## Application Size

Application package size is a design constraint.

Before release, inspect the resulting build size.

If the application package is unexpectedly large:

1.  Investigate bundled assets.
2.  Look for duplicate images.
3.  Look for unnecessarily high-resolution images.
4.  Look for unused dependencies.
5.  Check whether content images were accidentally packaged with the
    application.
6.  Correct the cause before considering the build complete.

A successful compile does NOT by itself mean the application is
release-ready.

## Data and Content

Do not delete, rename, or substantially alter application content
without a requirement to do so.

Preserve:

-   Recipes
-   Drink records
-   User data models
-   Favorites
-   Collections
-   Search functionality
-   Settings
-   Existing application features

unless the project specification requires a change.

When migrations are required, protect existing user data whenever
reasonably possible.

## UI and UX

Follow the project's UI/UX standards.

The application should:

-   Work on supported phone and tablet sizes.
-   Avoid overflow errors.
-   Support scrolling where necessary.
-   Use readable typography.
-   Maintain consistent spacing.
-   Provide appropriate loading states.
-   Provide appropriate empty states.
-   Provide appropriate error states.
-   Avoid blocking the interface unnecessarily.

Do not sacrifice usability merely to make a screen visually impressive.

## Performance

Performance is a release requirement.

Avoid:

-   Loading huge datasets into widgets unnecessarily.
-   Loading thousands of images simultaneously.
-   Unbounded caches.
-   Rebuilding expensive widgets unnecessarily.
-   Blocking the UI thread with heavy work.
-   Repeated network requests for unchanged resources.

Use pagination, lazy loading, caching, indexing, and other appropriate
techniques when dealing with large datasets.

## Security and Privacy

Follow all privacy requirements in the project documentation.

Do not introduce:

-   Analytics
-   Tracking
-   Advertising SDKs
-   External data collection
-   New permissions
-   Cloud services

unless they are explicitly required by the project specification.

If remote image hosting is required, use it only for delivery of
application content unless additional functionality has been explicitly
authorized.

Never embed private credentials, API secrets, signing passwords, or
access tokens in source code.

## Build and Release

When implementation is complete, release builds are a mandatory part of
completion unless `PROJECT_SPEC.md` explicitly excludes a platform.

Before creating release artifacts:

1.  Run `flutter pub get`.
2.  Run `flutter analyze`.
3.  Run all available automated tests with `flutter test`.
4.  Correct errors caused by the work.
5.  Review warnings and correct them when reasonably possible.
6.  Verify production application names, package identifiers, versions,
    icons, splash assets, permissions, URLs, and configuration.
7.  Verify that no placeholder identifiers such as `com.example.*`, test
    keys, dummy URLs, unfinished TODO items, private credentials,
    signing passwords, or access tokens remain in the production
    configuration.
8.  Build every required release artifact.
9.  Verify that every required artifact actually exists.
10. Inspect release sizes for unexpected growth.
11. Copy the final distributable artifacts into the project's
    `completed/` directory.
12. Report the exact final artifact paths.

A successful development run or successful compile does NOT by itself
mean the application is release-ready.

### Mandatory Release-Channel Separation

`docs/06_RELEASE_CHANNEL_CONTRACT.md` is the final authority for website-vs-Google-Play entitlement behavior and overrides conflicting older wording.

The project has two separate commercial entitlement channels. Both may use `PRO_LOCKS_ENABLED=true`; therefore the build MUST also select an entitlement provider with `ENTITLEMENT_CHANNEL`.

#### 1. Website direct download — FREE INSTALL + WEBSITE PRO UNLOCK

Build website artifacts with:

```text
PRO_LOCKS_ENABLED=true
ENTITLEMENT_CHANNEL=WEBSITE
```

- Android: production-signed release APK.
- Windows: MSIX/MSIXBundle or approved installer, plus release `.exe` when required.
- Web/PWA: hosted deployable build suitable for compatible browsers/PWA installation, including iOS/macOS use where supported.
- The installer/download itself is free. Free-mode functionality remains available according to the product specification.
- Pro unlock is purchased through Niagara Indie Apps and validated through the WEBSITE entitlement system.
- Default website license: permanent one-time Pro unlock, maximum 2 activated devices unless `PROJECT_SPEC.md` overrides it.
- Google Play Billing MUST NOT control website builds.
- For Web/PWA, prefer account/server-side entitlement rather than browser-only permanent license storage.

Package verified website outputs under `completed/Website_Delivery/`.

#### 2. Google Play — PLAY ENTITLEMENT

Build the Google Play AAB with:

```text
PRO_LOCKS_ENABLED=true
ENTITLEMENT_CHANNEL=GOOGLE_PLAY
```

The Play AAB uses the approved Google Play Billing/entitlement flow. Keep it under `completed/PROLOCKS/Google_Play/` and never offer the AAB as a website customer download.

#### Centralized entitlement implementation

Use centralized build-time configuration, for example `PRO_LOCKS_ENABLED` plus `ENTITLEMENT_CHANNEL`. Do not hand-edit feature code between channels and do not scatter payment-provider checks throughout the UI. Before release, independently verify Free mode, valid Pro activation, invalid/over-limit activation, website entitlement behavior, Play entitlement behavior, and the configured device limit.

### Android Release Requirements

Unless `PROJECT_SPEC.md` explicitly states otherwise, every completed
Flutter application must produce the Android artifacts according to channel:

-   **Website/direct download:** a production-signed release APK (`.apk`) built with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`. The download is free; Pro unlock is validated by the Niagara Indie Apps website entitlement system.
-   **Google Play:** a release Android App Bundle (`.aab`) built with `PRO_LOCKS_ENABLED=true`. This is the Play upload artifact and must retain the approved Pro/Premium locks.
-   The final production application/package ID.
-   Correct version name and version code for the release.

Build as appropriate with:

``` bash
flutter build apk --release
flutter build appbundle --release
```

Do not use `com.example.*` or another placeholder application ID in a
production release.

Follow `docs/APK_REBUILD_STANDARD.md` for Android-specific requirements.

### Windows Release Requirements

Unless `PROJECT_SPEC.md` explicitly excludes Windows, every completed
Flutter application must:

-   Build successfully for Windows in release mode.
-   Produce a Microsoft Store-ready `.msix` or `.msixbundle`.
-   Use the final application name.
-   Use the correct Windows package identity and publisher information
    required for the intended Microsoft Store submission.
-   Include the required Windows icons and package assets.
-   Retain the unpackaged Windows release build when useful for testing.
-   Be suitable for submission through Microsoft Partner Center once the
    project's Store identity/signing requirements are supplied.

Run the appropriate Windows release build and MSIX packaging process
configured by the project.

Do not claim that an MSIX or MSIXBundle exists unless the package was
actually generated and verified.

### Web / Chrome / PWA Release Requirements

Unless `PROJECT_SPEC.md` explicitly excludes Web, every completed
Flutter application must produce a production Web build.

Build with:

``` bash
flutter build web --release
```

The Web release must:

-   Work in current supported desktop browsers, including Chrome and
    Edge, and other browsers supported by the Flutter Web
    implementation.
-   Be ready for deployment to HTTPS web hosting.
-   Be configured as an installable Progressive Web App (PWA) whenever
    practical for the application.
-   Include the required `index.html`, web manifest, icons, Flutter Web
    runtime files, application assets, and other generated production
    files.
-   Use correct paths/base configuration for the intended host.
-   Be suitable for hosting on GitHub Pages or another HTTPS web host
    when that deployment method is selected by the project.

A normal Flutter Web/PWA application is NOT automatically a Chrome Web
Store extension. Do not package or describe it as a Chrome extension
unless `PROJECT_SPEC.md` explicitly requires an extension and the
required extension architecture has been implemented.

### iOS / iPadOS Requirements

Unless `PROJECT_SPEC.md` explicitly excludes Apple platforms:

-   Preserve and maintain the Flutter iOS project.
-   Keep the application build-ready for iPhone and iPad where supported
    by the project.
-   Use the intended production bundle identifier and application
    metadata when those values are available.
-   Include the required application icons, permissions, and iOS
    configuration.
-   Do not claim that an App Store archive or signed iOS release was
    produced unless it was actually built and verified on a compatible
    macOS/Xcode environment.

If the current environment cannot perform the final iOS signing/archive,
state that limitation clearly in the completion report. Do not treat
that environmental limitation as permission to omit the iOS project
configuration.

### Required `completed/` Output Directory

At the project root, maintain a `completed/` directory for final release
outputs.

Use this structure unless the existing project has an equivalent
documented release structure:

``` text
completed/
├── Android/
│   ├── app-release.aab
│   └── app-release.apk
│
├── Windows/
│   ├── AppName.msix or AppName.msixbundle
│   └── release/
│
├── Web/
│   └── web/
│       ├── index.html
│       ├── manifest.json
│       ├── assets/
│       └── other generated production web files
│
├── Store_Assets/
│   ├── Android/
│   ├── Windows/
│   └── Web/
│
└── Documentation/
    ├── README.md
    ├── privacy-policy.html
    └── RELEASE-INSTRUCTIONS.md
```

Do not create fake placeholder release files merely to satisfy this
directory structure. Only copy artifacts that were actually generated or
documentation that is genuinely applicable to the project.

If a platform is explicitly excluded by `PROJECT_SPEC.md`, its output
folder may be omitted.

### Minimum Release Outputs

Unless excluded by `PROJECT_SPEC.md`, the target finished project should
provide:

-   **Android website/direct-download channel:** production-signed release `.apk`, compiled with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`, with Free mode plus website Pro activation.
-   **Android Google Play channel:** release `.aab`, compiled with `PRO_LOCKS_ENABLED=true`, with approved Pro/Premium locks active. These are separate releases and must never be mixed.
-   **Windows:** `.msix`/`.msixbundle` or approved installer, plus the release `.exe` when required by the project.
-   **Web/PWA:** deployable hosted production Web/PWA files, usable on compatible browsers including iOS/macOS where supported.
-   **iOS:** build-ready Flutter iOS project; signed/archive output only
    when a compatible Apple build environment and signing configuration
    are available.
-   **Source:** complete Flutter/Dart source project in the same
    repository.

Where applicable to application metadata, the creator/developer name is:

`G Thompson`

Project-specific identity, publisher, signing, Store IDs, package names,
pricing, privacy requirements, and monetization requirements remain
governed by `PROJECT_SPEC.md` and the project documentation.

### Release Artifact Verification

Before declaring the release complete:

1.  Verify the website Android APK exists, is production-signed, was compiled with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`, exposes the intended Free mode, and unlocks Pro only after valid website entitlement activation.
2.  Verify the Google Play Android AAB exists, was compiled separately with `PRO_LOCKS_ENABLED=true`, and retains the intended Pro/Premium locks.
3.  Verify the Windows MSIX/MSIXBundle or approved installer and required release EXE exist, and use the WEBSITE entitlement channel.
4.  Verify the Web production build exists when Web is required.
5.  Verify the iOS project remains build-ready when iOS is required.
6.  Verify the files in `completed/` are actual current release outputs
    rather than stale files from an earlier build.
7.  Verify application names and production identifiers.
8.  Verify versions and build numbers.
9.  Verify icons and splash assets.
10. Verify privacy-policy information where required.
11. Verify no temporary/debug files or obvious development-only
    configuration were included in the release.
12. Verify no secrets or credentials were introduced.
13. Inspect package sizes and confirm large image libraries were not
    accidentally bundled.
14. Report exact artifact paths and any platform that could not be
    built, including the reason.

## Do Not Fake Completion

Never claim that:

-   a build succeeded,
-   tests passed,
-   an APK exists,
-   an AAB exists,
-   an image was generated,
-   an artifact was copied,
-   or a requirement was implemented

unless it was actually performed and verified.

If something cannot be completed, state exactly what remains and why.

## Autonomous Work

Once the project requirements are sufficiently defined, proceed through
implementation without repeatedly asking the owner for approval for
routine technical decisions.

Make reasonable engineering decisions consistent with:

1.  `PROJECT_SPEC.md`
2.  `CLAUDE.md`
3.  Files in `docs/`
4.  Existing application architecture
5.  Flutter/Dart best practices

Ask the owner only when a decision materially changes application
functionality, cost, user data, monetization, privacy, or another
requirement that cannot reasonably be inferred.

## Final Verification

Before declaring the task complete, verify:

-   Project requirements were followed.
-   Existing functionality was preserved.
-   Flutter analysis was performed.
-   Tests were performed where available.
-   Required builds were created.
-   Build artifacts exist.
-   Large image libraries were not accidentally bundled into the
    application.
-   Image loading and caching architecture follows the project
    standards.
-   No obvious temporary/debug files were left in the release.
-   No secrets or credentials were introduced.

Then provide the owner with a concise completion report listing:

-   What was changed
-   What was tested
-   Build results
-   APK/AAB locations
-   Windows MSIX/MSIXBundle location when required
-   Web/PWA build location when required
-   iOS build/archive status when required
-   `completed/` release-output locations
-   Any remaining warnings or limitations

Do not declare the project complete while known required work remains.


## Niagara Inde Apps Website Direct-Sales Release Override

For this starter, `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md` is mandatory and governs customer delivery. When older text in this file describes AAB as a mandatory completed/customer artifact, interpret that requirement as follows:

- **Android customer website release:** always build a production signed `.apk`.
- **Google Play AAB:** build separately for Google Play with `PRO_LOCKS_ENABLED=true`. It is the store-sale artifact, retains the approved Pro/Premium locks, and is never part of Website_Delivery.
- **Windows customer website release:** always produce an `.msix`/`.msixbundle` or explicitly approved professional installer, not merely a raw Windows build folder.
- **Web/PWA:** always produce the deployable production Web/PWA build for hosting as the app's website demo/web-app URL. Do not sell the web build ZIP to customers.
- **Flutter source:** always keep the raw source completely separate from customer/website delivery. Create a private source ZIP under `source_release/` only after verification, excluding secrets and generated release folders.

The required customer-facing release area is `completed/Website_Delivery/`. `completed/Website_Delivery/` MUST NOT contain an AAB, source ZIP, source tree, keystore, signing credentials, debug build, or secret.

Before declaring completion, create and verify `completed/Website_Delivery/WEBSITE_UPLOAD_MANIFEST.md` and report the exact Android APK, Windows installer, Web demo folder/intended URL, and separate private source ZIP paths.
