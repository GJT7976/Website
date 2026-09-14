<?php

namespace App\Services;

use App\Models\License;

/**
 * Generates customer-friendly, cryptographically random license keys
 * (§3) — never sequential/predictable. Only the sha256 hash of the raw
 * key is ever persisted (see the licenses table); the raw key is handed
 * back once so the caller can put it on the success page / in the
 * license email and then discard it.
 */
class LicenseKeyGenerator
{
    // 32-character alphabet with ambiguous characters (0/O, 1/I/L) removed,
    // so a customer typing the key by hand doesn't get tripped up.
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private const GROUPS = 4;

    private const GROUP_LENGTH = 4;

    /**
     * @return array{raw: string, hash: string}
     */
    public function generate(): array
    {
        do {
            $raw = $this->randomKey();
            $hash = self::hash($raw);
        } while (License::where('license_key_hash', $hash)->exists());

        return ['raw' => $raw, 'hash' => $hash];
    }

    public static function hash(string $rawKey): string
    {
        return hash('sha256', strtoupper(trim($rawKey)));
    }

    private function randomKey(): string
    {
        $groups = [];

        for ($g = 0; $g < self::GROUPS; $g++) {
            $chars = '';

            for ($i = 0; $i < self::GROUP_LENGTH; $i++) {
                $chars .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
            }

            $groups[] = $chars;
        }

        return implode('-', $groups);
    }
}
