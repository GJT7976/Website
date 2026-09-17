# RELEASE CHANNEL CONTRACT — DO NOT MIX

This rule overrides any older or ambiguous release wording.

## WEBSITE DIRECT-DOWNLOAD / PRO-UNLOCK EDITION
Website installers are distributed free from Niagara Indie Apps. The customer installs the app first and purchases a permanent Pro unlock from the Niagara Indie Apps website.

- Android: production-signed release APK.
- Windows: MSIX/MSIXBundle or approved installer AND the Windows release `.exe` when the project produces it.
- Web/PWA: deployable hosted production Web/PWA build; intended to support browser/PWA use including iOS and macOS where compatible.
- Build flag: `PRO_LOCKS_ENABLED=true`.
- Pro/Premium locks: ON until a valid Niagara Indie Apps website entitlement is verified.
- Google Play Billing: MUST NOT be used by website/direct-download builds.
- Website entitlement provider: Niagara Indie Apps website license/account system.
- Default license policy: permanent one-time Pro unlock, maximum 2 activated devices per purchased license unless `PROJECT_SPEC.md` overrides it.
- Output root: `completed/Website_Delivery/`.
- AAB: NEVER place in Website_Delivery.

For APK/Windows builds, a license key may be entered in-app and must be validated by the approved website licensing service. Do not implement a trivially forgeable hard-coded master key. Store only the minimum local entitlement state needed for reliable use and protect it appropriately.

For Web/PWA, prefer an authenticated customer account/server-side entitlement over a browser-only permanent key because browser storage can be cleared or changed. A website license key may be redeemed into the customer's account if that is the chosen flow.

## GOOGLE PLAY SALE
Google Play publishing is separate. The Play build retains Pro/Premium locks but uses the approved Google Play Billing/Play entitlement flow rather than the Niagara Indie Apps website payment flow.

- Android: release AAB as the normal store upload artifact.
- Build flag: `PRO_LOCKS_ENABLED=true`.
- Entitlement channel: `GOOGLE_PLAY`.
- Pro/Premium locks: ON.
- Google Play Billing / approved Play entitlement system: ON when monetization is configured.
- Niagara Indie Apps website checkout/license entry: OFF in the Play build unless explicitly required for account linking and compliant with store rules.
- Output: `completed/PROLOCKS/Google_Play/`.

## NON-NEGOTIABLE RULE
Both commercial editions may have Pro locks ON, so `PRO_LOCKS_ENABLED` alone is not sufficient to select the payment provider. Use a second centralized release-channel value such as `ENTITLEMENT_CHANNEL=WEBSITE` or `ENTITLEMENT_CHANNEL=GOOGLE_PLAY`. Never infer entitlement behavior from a filename or destination folder. Verify the resulting edition before release.
