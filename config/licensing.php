<?php

return [

    /*
    |--------------------------------------------------------------------
    | Default maximum devices per license
    |--------------------------------------------------------------------
    |
    | §1/§9 of the licensing spec: every website-sold license (Android,
    | Windows, or the Android+Windows bundle) permanently unlocks PRO on
    | up to this many devices TOTAL. Stored per-row on `licenses.
    | maximum_devices` too, so an admin override never has to touch code.
    |
    */
    'default_maximum_devices' => (int) env('LICENSING_DEFAULT_MAX_DEVICES', 2),

    /*
    |--------------------------------------------------------------------
    | Self-service device reset limit
    |--------------------------------------------------------------------
    |
    | §11: prevent a customer from continuously deactivating/reactivating
    | devices to dodge the two-device limit, without punishing someone
    | genuinely replacing a broken or upgraded device. This many
    | customer-initiated deactivations are allowed per license in the
    | trailing window below before self-service is paused and an admin
    | override (Admin\LicenseController::resetActivations) is required.
    |
    */
    'self_service_reset_limit' => (int) env('LICENSING_SELF_SERVICE_RESET_LIMIT', 3),
    'self_service_reset_window_days' => (int) env('LICENSING_SELF_SERVICE_RESET_WINDOW_DAYS', 30),

    /*
    |--------------------------------------------------------------------
    | Offline entitlement signing (§14)
    |--------------------------------------------------------------------
    |
    | Ed25519 keypair (libsodium) used by App\Services\LicenseTokenSigner
    | to sign the entitlement payload returned from /api/license/activate
    | and /api/license/validate, so a Flutter client can cache and verify
    | it offline instead of trusting a bare local boolean. Generate with
    | `php artisan license:keys:generate` — the private key belongs only
    | in this app's .env, never in a Flutter app; the public key printed
    | by that command is what gets embedded client-side.
    |
    */
    'signing_private_key' => env('LICENSE_SIGNING_PRIVATE_KEY'),
    'signing_public_key' => env('LICENSE_SIGNING_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------
    | Management code lifetime
    |--------------------------------------------------------------------
    |
    | §10: how long an emailed one-time device-management code stays valid.
    |
    */
    'management_code_ttl_minutes' => (int) env('LICENSING_MANAGEMENT_CODE_TTL_MINUTES', 15),

];
