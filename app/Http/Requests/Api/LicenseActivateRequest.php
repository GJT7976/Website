<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class LicenseActivateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:32'],
            'app_id' => ['required', 'integer', 'exists:apps,id'],
            // §8: only the platform the license was actually bought for,
            // never a raw OS string the client could send arbitrarily.
            'platform' => ['required', 'string', 'in:android,windows'],
            // §8: an opaque installation identifier the app generates
            // itself — never a MAC address, IMEI, serial, or product key.
            'device_id' => ['required', 'string', 'min:8', 'max:255'],
            'app_version' => ['nullable', 'string', 'max:50'],
        ];
    }
}
