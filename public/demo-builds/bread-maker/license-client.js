'use strict';
/*
 * Niagara Indie Apps website license client (Bread Maker).
 *
 * Mirrors the Flutter sibling apps' LicenseClientService: activate/validate/
 * deactivate against /api/license/* (see the website repo's
 * LICENSE_SYSTEM.md), with offline Ed25519 signature verification via
 * TweetNaCl (vendor/nacl-fast.min.js, loaded before this file) so a cached
 * unlock survives without a network connection. Used only when
 * window.ENTITLEMENT_CHANNEL === 'WEBSITE' (see index.html's isProLocked()).
 *
 * Storage: plain localStorage, matching this app's existing architecture
 * (lang/theme/units/saved recipes all already live there) — no new native
 * plugin dependency introduced for this.
 */
const LicenseClient = (() => {
  const PREFIX = 'breadmaker_license_';

  function readLS(key) {
    try { return localStorage.getItem(PREFIX + key); } catch (_) { return null; }
  }
  function writeLS(key, val) {
    try { localStorage.setItem(PREFIX + key, val); } catch (_) { /* ignore */ }
  }
  function removeLS(key) {
    try { localStorage.removeItem(PREFIX + key); } catch (_) { /* ignore */ }
  }

  // Opaque per-install identifier the app generates itself — never a
  // hardware serial, MAC address, or Windows product key (see §8 of the
  // licensing spec). Persisted once, reused for every activate/validate
  // call from this install.
  function deviceId() {
    let id = readLS('device_id');
    if (id) return id;
    const bytes = new Uint8Array(16);
    crypto.getRandomValues(bytes);
    bytes[6] = (bytes[6] & 0x0f) | 0x40; // version 4
    bytes[8] = (bytes[8] & 0x3f) | 0x80; // variant 10
    const hex = Array.from(bytes, (b) => b.toString(16).padStart(2, '0'));
    id = `${hex.slice(0, 4).join('')}-${hex.slice(4, 6).join('')}-${hex.slice(6, 8).join('')}-${hex.slice(8, 10).join('')}-${hex.slice(10, 16).join('')}`;
    writeLS('device_id', id);
    return id;
  }

  function base64ToBytes(b64) {
    const bin = atob(b64);
    const bytes = new Uint8Array(bin.length);
    for (let i = 0; i < bin.length; i++) bytes[i] = bin.charCodeAt(i);
    return bytes;
  }

  // Reproduces App\Services\LicenseTokenSigner::canonicalJson() on the
  // server byte-for-byte: top-level keys sorted ascending (matches PHP's
  // ksort() for these plain lowercase/underscore keys) and forward slashes
  // left unescaped (JSON.stringify never escapes '/', matching PHP's
  // JSON_UNESCAPED_SLASHES). The signature verifies only if this produces
  // the exact bytes the server signed — do not "clean up" this without
  // re-checking against a real captured token.
  function canonicalJson(payload) {
    const sorted = {};
    Object.keys(payload).sort().forEach((k) => { sorted[k] = payload[k]; });
    return JSON.stringify(sorted);
  }

  function verifyToken(payload, signatureB64, publicKeyB64) {
    try {
      const msg = new TextEncoder().encode(canonicalJson(payload));
      const sig = base64ToBytes(signatureB64);
      const pub = base64ToBytes(publicKeyB64);
      return nacl.sign.detached.verify(msg, sig, pub);
    } catch (_) {
      return false;
    }
  }

  function savedLicenseKey() {
    return readLS('key');
  }

  // Locally-cached entitlement, verified offline against the embedded
  // public key without any network round-trip. Returns null when there is
  // nothing cached, the cached signature no longer verifies, or the cached
  // token's valid_until has passed.
  function cachedEntitlement(publicKeyB64) {
    const payloadJson = readLS('token_payload');
    const signature = readLS('token_signature');
    if (!payloadJson || !signature) return null;
    let payload;
    try { payload = JSON.parse(payloadJson); } catch (_) { return null; }
    if (!verifyToken(payload, signature, publicKeyB64)) return null;
    const validUntil = payload.valid_until ? new Date(payload.valid_until) : null;
    if (!validUntil || Number.isNaN(validUntil.getTime()) || new Date() > validUntil) {
      return { unlocked: false, reason: 'expired' };
    }
    return { unlocked: true };
  }

  async function call(apiBase, path, body, publicKeyB64) {
    let decoded;
    try {
      const resp = await fetch(apiBase + path, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(body),
      });
      if (!resp.ok) return { unlocked: false, reason: 'network_error' };
      decoded = await resp.json();
    } catch (_) {
      return { unlocked: false, reason: 'network_error' };
    }
    if (!decoded.unlocked) {
      return { unlocked: false, reason: decoded.reason || 'unknown', message: decoded.message || null };
    }
    const token = decoded.token;
    if (!token) return { unlocked: false, reason: 'unknown' };
    if (!verifyToken(token.payload, token.signature, publicKeyB64)) {
      return { unlocked: false, reason: 'signature_invalid' };
    }
    writeLS('token_payload', JSON.stringify(token.payload));
    writeLS('token_signature', token.signature);
    return { unlocked: true };
  }

  // `platform` is 'android' or 'windows' (never inferred silently — see
  // docs/06_RELEASE_CHANNEL_CONTRACT.md's "never infer entitlement
  // behavior" rule; the call site passes the platform this build targets).
  async function activate(apiBase, appId, publicKeyB64, licenseKey, platform, appVersion) {
    const normalized = (licenseKey || '').trim().toUpperCase();
    const result = await call(apiBase, '/api/license/activate', {
      license_key: normalized,
      app_id: appId,
      platform,
      device_id: deviceId(),
      app_version: appVersion,
    }, publicKeyB64);
    if (result.unlocked) {
      writeLS('key', normalized);
      writeLS('platform', platform);
    }
    return result;
  }

  // Periodic online re-validation of an already-activated device (§13).
  // Call opportunistically (app start) — there is no push mechanism, so a
  // revoked license only stops working once this has run again.
  async function revalidate(apiBase, publicKeyB64) {
    const key = savedLicenseKey();
    if (!key) return { unlocked: false, reason: 'no_cached_license' };
    return call(apiBase, '/api/license/validate', { license_key: key, device_id: deviceId() }, publicKeyB64);
  }

  function clear() {
    removeLS('key');
    removeLS('platform');
    removeLS('token_payload');
    removeLS('token_signature');
  }

  // Customer self-service device removal (§10). Clears the local cache
  // only on a confirmed server-side deactivation.
  async function deactivate(apiBase) {
    const key = savedLicenseKey();
    if (!key) return false;
    try {
      const resp = await fetch(apiBase + '/api/license/deactivate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ license_key: key, device_id: deviceId() }),
      });
      if (!resp.ok) return false;
      const decoded = await resp.json();
      const ok = decoded.ok === true;
      if (ok) clear();
      return ok;
    } catch (_) {
      return false;
    }
  }

  return {
    deviceId, savedLicenseKey, cachedEntitlement, activate, revalidate, deactivate, clear,
    // exposed for the test page only
    canonicalJson, verifyToken,
  };
})();

// Node test harness only (test/license-client.test.js) — no effect in the
// browser/Capacitor/Electron runtime, where this file is loaded as a plain
// <script> and LicenseClient is used as a global.
if (typeof module !== 'undefined' && module.exports) {
  module.exports = LicenseClient;
}
