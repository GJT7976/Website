<?php

namespace App\Services;

use RuntimeException;

/**
 * Signs the entitlement payload returned from license activate/validate so
 * a Flutter client can cache it and verify PRO status offline (§14)
 * without trusting a bare local `isPro = true` boolean. Uses libsodium's
 * Ed25519 (`sodium_crypto_sign_detached`), built into PHP — no extra
 * dependency. The private key lives only in this app's `.env`
 * (`LICENSE_SIGNING_PRIVATE_KEY`, see config/licensing.php); the public
 * key is safe to embed in a Flutter app and is what it uses to verify.
 *
 * Generate a keypair with `php artisan license:keys:generate`.
 */
class LicenseTokenSigner
{
    /**
     * @return array{payload: array<string, mixed>, signature: string}
     */
    public function sign(array $payload): array
    {
        $secretKey = $this->secretKey();
        $encoded = $this->canonicalJson($payload);

        $signature = base64_encode(sodium_crypto_sign_detached($encoded, $secretKey));

        return ['payload' => $payload, 'signature' => $signature];
    }

    public function verify(array $payload, string $signatureBase64): bool
    {
        $publicKey = $this->publicKey();

        if ($publicKey === null) {
            return false;
        }

        $signature = base64_decode($signatureBase64, true);

        if ($signature === false) {
            return false;
        }

        return sodium_crypto_sign_verify_detached($signature, $this->canonicalJson($payload), $publicKey);
    }

    /**
     * @return array{secret: string, public: string} base64-encoded halves
     */
    public static function generateKeypair(): array
    {
        $keypair = sodium_crypto_sign_keypair();

        return [
            'secret' => base64_encode(sodium_crypto_sign_secretkey($keypair)),
            'public' => base64_encode(sodium_crypto_sign_publickey($keypair)),
        ];
    }

    private function secretKey(): string
    {
        $configured = config('licensing.signing_private_key');

        if (! $configured) {
            throw new RuntimeException('LICENSE_SIGNING_PRIVATE_KEY is not configured. Run `php artisan license:keys:generate`.');
        }

        $decoded = base64_decode($configured, true);

        if ($decoded === false || strlen($decoded) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
            throw new RuntimeException('LICENSE_SIGNING_PRIVATE_KEY is not a valid signing key.');
        }

        return $decoded;
    }

    private function publicKey(): ?string
    {
        $configured = config('licensing.signing_public_key');

        if (! $configured) {
            return null;
        }

        $decoded = base64_decode($configured, true);

        return ($decoded !== false && strlen($decoded) === SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES) ? $decoded : null;
    }

    /**
     * Deterministic encoding (sorted keys) so the same payload always
     * signs/verifies to the same bytes regardless of array construction
     * order.
     */
    private function canonicalJson(array $payload): string
    {
        ksort($payload);

        return json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }
}
