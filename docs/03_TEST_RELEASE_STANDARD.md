# Testing, Release, Signing & Store Standard

> Extracted from the user's MASTER PROFESSIONAL APP DESIGN, UI/UX, ENGINEERING & DEVELOPMENT STANDARD. Requirements are preserved; this file is loaded only when relevant.

46. COMPLETE USER-JOURNEY TESTING
Do not test screens only in isolation.
Test realistic journeys.
For example:
Open app ? find item ? inspect item ? perform primary action ? save/complete ? return later ? verify state persisted.
For a recipe app:
Open app ? browse/search recipe ? open recipe ? change servings ? add ingredients ? start Cook Mode ? use timer ? complete cooking ? save notes.
For a record application:
Create record ? edit ? search ? filter ? backup ? restore ? verify record integrity.

47. RESPONSIVE QA
Test representative:
    • narrow phone
    • normal phone
    • large phone
    • tablet portrait
    • tablet landscape
    • large tablet
    • Chromebook where targeted
    • Windows small window
    • Windows maximized window
    • browser narrow width
    • browser wide width
Verify:
    • no overflow
    • readable typography
    • useful layout
    • proper navigation
    • accessible actions
    • correct image behavior
    • sensible density

48. ACCESSIBILITY QA
Test at minimum:
    • keyboard navigation where applicable
    • visible focus
    • text scaling
    • contrast
    • touch targets
    • semantic labels
    • screen-reader structure where available
    • reduced motion where applicable

49. RELEASE BUILDS ARE REAL BUILDS
A debug build running is not completion.
Where supported, build and verify the actual release artifacts required by the project.
Never report:
    • APK
    • AAB
    • EXE
    • installer
    • MSIX
    • Web/PWA
    • signing
    • screenshots
    • store assets
    • tests
as completed unless they were actually created or performed successfully.
If an operation cannot be verified because required hardware, credentials, platform tooling or external services are unavailable, explicitly report:
NOT VERIFIED — MANUAL/EXTERNAL STEP REQUIRED
Never convert an unperformed check into a pass.

50. RELEASE REQUIREMENTS MUST BE PLATFORM-CONDITIONAL
Only require artifacts for platforms actually targeted by the project.
Android target
Apply Android:
    • release APK when required
    • release AAB when required
    • signing
    • permissions
    • Android icon system
    • Google Play preparation
Windows target
Apply:
    • Windows release build
    • application .exe
    • installer where required
    • MSIX where required
    • Windows branding
    • keyboard/mouse testing
Web/PWA target
Apply:
    • release Web build
    • manifest
    • icons
    • responsive browser testing
    • PWA installability when required
    • caching/offline behavior where applicable
Do not attempt irrelevant platform builds merely because another project used them.

51. STORE-ASSET REQUIREMENTS ARE CONDITIONAL
Create Google Play artwork and screenshots only when Android/Google Play distribution is part of the project.
Create Microsoft Store assets only when Microsoft Store distribution is required.
Create other store assets only when those stores are targeted.
Store screenshots must depict the actual finished application.
Never advertise nonexistent functionality.

52. RELEASE ARTIFACT ORGANIZATION
When the project specifies a completed/ directory:
Only verified release deliverables belong there.
Do not place:
    • failed builds
    • debug builds
    • partial builds
    • untested installers
    • fake screenshots
    • placeholder artwork
inside the completed release package.

53. SIGNING AND CREDENTIAL SECURITY
Each released application must use the appropriate signing identity required by its platform.
Android signing credentials must not be committed to Git.
Signing backup requirements elsewhere in the project specification remain authoritative.
Do not store real passwords in documentation.

54. LEGAL REQUIREMENTS MUST MATCH ACTUAL FUNCTIONALITY
Create legal/disclosure material appropriate to actual application behavior.
Do not automatically claim:
    • no internet access
    • no personal-data collection
    • no analytics
    • no advertising
    • no cloud storage
unless those statements are true of the final build.
Privacy Policy, app behavior and store declarations must agree.

55. EXISTING APP / APK REBUILD MODE
If this project is rebuilding an authorized existing application:
Analyze first.
Determine:
    • screens
    • navigation
    • features
    • data
    • workflows
    • assets
    • storage
    • settings
    • permissions
    • behavior
Separate confirmed facts from inference.
Use the existing application as a behavioral reference.
Do NOT blindly copy poor architecture or poor UI.
Do NOT simply paste decompiled code.
Rebuild the functionality using maintainable Flutter/Dart architecture.
Preserve recognizable authorized branding and required functionality while improving:
    • usability
    • accessibility
    • responsiveness
    • architecture
    • validation
    • reliability
    • performance
    • security


## 70. NIAGARA INDE APPS WEBSITE DELIVERY GATE
For all normal Niagara Inde Apps website-direct releases, apply `docs/05_WEBSITE_DIRECT_SALES_RELEASE.md`. The mandatory website deliverables are a signed Android APK, a Windows MSIX/MSIXBundle or approved installer, and a deployable Web/PWA demo build. The Google Play AAB is a separate store-sale build, must use `PRO_LOCKS_ENABLED=true`, and is never a website/customer download. Raw Flutter source must be archived separately outside `completed/Website_Delivery/`.


## 71. RELEASE-CHANNEL ENTITLEMENT VERIFICATION
Two commercial editions must be tested independently.

WEBSITE EDITION:
- Build the Android website artifact as a release APK with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`.
- Verify the APK is cryptographically signed with the production/release signing key; reject unsigned APKs and Android debug-certificate APKs.
- Stage/copy the verified website-channel APK immediately after it is built so a later channel build cannot overwrite Flutter's `app-release.apk` path.
- Verify Free mode works before purchase and a valid Niagara Indie Apps website license/account entitlement unlocks Pro without Google Play Billing.
- Verify Pro gates correctly distinguish Free and Pro functionality and provide the website purchase/activation path.
- Verify the website APK, Windows installer/EXE, and Web/PWA build all use `ENTITLEMENT_CHANNEL=WEBSITE`.

GOOGLE PLAY EDITION:
- Build the Google Play upload artifact as an AAB with `PRO_LOCKS_ENABLED=true`.
- Verify the intended Pro/Premium gates remain active.
- Verify the approved Google Play Billing flow is used when monetization requires it.
- Verify the Play AAB is packaged under `completed/PROLOCKS/Google_Play/` and never under `completed/Website_Delivery/`.
- The normal website APK is Pro-locked and signed, but it must use the WEBSITE entitlement provider; the Play AAB uses GOOGLE_PLAY entitlement.

Do not infer one edition is correct because the other passed. Test both.
