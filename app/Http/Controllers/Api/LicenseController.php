<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LicenseActivateRequest;
use App\Http\Requests\Api\LicenseDeactivateRequest;
use App\Http\Requests\Api\LicenseRequestCodeRequest;
use App\Http\Requests\Api\LicenseValidateRequest;
use App\Http\Requests\Api\LicenseVerifyCodeRequest;
use App\Models\App;
use App\Services\LicenseService;
use Illuminate\Http\JsonResponse;

/**
 * §26's license API, implemented as this app's normal controller
 * convention rather than the spec's literal example URLs. Every mutating
 * decision (activate/validate/deactivate) is delegated to
 * App\Services\LicenseService — this controller only resolves the license
 * from the request and shapes the JSON response, exactly like
 * Admin\EntitlementController delegates to EntitlementService.
 */
class LicenseController extends Controller
{
    public function __construct(private LicenseService $licenses) {}

    public function activate(LicenseActivateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $license = $this->licenses->resolveByRawKey($data['license_key']);

        if (! $license) {
            return $this->lockedResponse('invalid_license', 'This license key is not valid.');
        }

        $app = App::find($data['app_id']);

        $result = $this->licenses->activate($license, $app, $data['platform'], $data['device_id'], $data['app_version'] ?? null, $request->ip());

        return response()->json($result);
    }

    public function validateLicense(LicenseValidateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $license = $this->licenses->resolveByRawKey($data['license_key']);

        if (! $license) {
            return $this->lockedResponse('invalid_license', 'This license key is not valid.');
        }

        $result = $this->licenses->validate($license, $data['device_id'], $request->ip());

        return response()->json($result);
    }

    public function deactivate(LicenseDeactivateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $license = $this->licenses->resolveByRawKey($data['license_key']);

        if (! $license) {
            return response()->json(['ok' => false, 'message' => 'This license key is not valid.']);
        }

        $device = $license->devices()->where('device_identifier_hash', hash('sha256', trim($data['device_id'])))->active()->first();

        if (! $device) {
            return response()->json(['ok' => false, 'message' => 'This device is not activated on this license.']);
        }

        return response()->json($this->licenses->deactivateByCustomer($device, $request->ip()));
    }

    public function requestManagementCode(LicenseRequestCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->licenses->requestManagementCode($data['email'], $data['license_key']);

        // Deliberately the same response whether or not the email/key
        // matched a license — never confirm/deny a customer's purchase to
        // an anonymous caller.
        return response()->json(['message' => 'If that email address and license key match, we\'ve sent a management code.']);
    }

    public function verifyManagementCode(LicenseVerifyCodeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $url = $this->licenses->verifyManagementCode($data['email'], $data['code']);

        if (! $url) {
            return response()->json(['management_url' => null, 'message' => 'That code is invalid or has expired.'], 422);
        }

        return response()->json(['management_url' => $url]);
    }

    private function lockedResponse(string $reason, string $message): JsonResponse
    {
        return response()->json(['unlocked' => false, 'reason' => $reason, 'message' => $message, 'token' => null]);
    }
}
