# Website Direct-Download and Pro-Unlock Release Standard — Niagara Indie Apps

This standard is mandatory for every completed Flutter application unless `PROJECT_SPEC.md` explicitly excludes a platform.

## Commercial model
Website builds are distributed as free installers/downloads with Pro/Premium locks active. The customer can install and use the permitted Free portion, then buy a permanent Pro unlock from the Niagara Indie Apps website.

The website edition MUST use:

```text
PRO_LOCKS_ENABLED=true
ENTITLEMENT_CHANNEL=WEBSITE
```

The website edition MUST NOT depend on Google Play Billing. A successful Niagara Indie Apps website purchase must create or provide a website entitlement that the app can verify. Unless `PROJECT_SPEC.md` overrides it, one purchased license permanently unlocks Pro on up to 2 activated devices.

Do not hard-code universal unlock keys, payment secrets, signing secrets, private API keys, or a client-side list of valid licenses into the app. License validation should use an approved server/API or signed entitlement design, with sensible offline handling for already activated devices.

## Android — direct website distribution
Always build a production-signed release APK.
- Customer file: signed `.apk`.
- Build with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`.
- Free features must work before purchase as specified by the product.
- Pro features must unlock after a valid website license is activated.
- Reject unsigned APKs and APKs signed with the Android debug certificate.
- Never place AABs, keystores, signing passwords, source code, or secrets in the customer package.

## Windows — direct website distribution
Always build the Windows release. Customer deliverables should include:
- `.msix`/`.msixbundle` or the approved professional installer; and
- the release Windows `.exe` when required by `PROJECT_SPEC.md`.

Build with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`. The same website entitlement/license policy should apply across Android and Windows, subject to the configured two-device activation limit. Do not falsely claim an installer is signed when the required certificate is unavailable.

## Web/PWA — hosted website application
Always build a production Flutter Web/PWA version when Web is supported. This hosted build can serve compatible browsers and installable PWA use, including iOS and macOS where supported by the browser/OS. It is not a native iOS/macOS binary.

Build with `PRO_LOCKS_ENABLED=true` and `ENTITLEMENT_CHANNEL=WEBSITE`. Prefer account-based/server-side Pro entitlement for Web/PWA. Do not rely solely on browser local storage as the permanent proof of purchase.

## Required release layout
```text
completed/
  Website_Delivery/                      # PRO locks ON; website entitlement
    Android/
      <AppName>-<version>-android-signed-prolocked.apk
    Windows/
      <AppName>-<version>-windows.msix   (or approved installer)
      <AppName>-<version>-windows.exe    (when required)
    Web_PWA/
      web/
    WEBSITE_UPLOAD_MANIFEST.md
    RELEASE_NOTES.md

  PROLOCKS/
    Google_Play/                         # PRO locks ON; Google Play entitlement
      <AppName>-<version>-google-play-prolocked.aab
    RELEASE_NOTES.md

source_release/                          # private; never a customer download
  <AppName>-<version>-flutter-source.zip
```

## Website upload manifest
For every release create `completed/Website_Delivery/WEBSITE_UPLOAD_MANIFEST.md` containing the app name/version, APK filename and SHA-256, Windows installer/MSIX filename and SHA-256, Windows EXE filename when applicable, Web/PWA deployment folder/URL, build date, signing/verification status, entitlement channel, activation limit, and an explicit statement that AAB and Flutter source are not customer downloads.

## Completion gate
A website release is complete only when the signed APK exists; required Windows MSIX/installer and EXE exist; Web/PWA exists; all website artifacts were built with Pro locks ON and the WEBSITE entitlement channel; Free mode is verified; valid website purchase/license activation unlocks Pro; invalid/revoked/over-limit activation does not unlock Pro; the default two-device limit or project override is verified; Google Play Billing does not control website builds; Google Play AAB remains separately packaged; and exact output paths are reported.
