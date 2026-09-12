<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const GROUPS = ['business', 'site', 'store'];

    public function edit(string $group): View
    {
        if ($group === 'payments') {
            return view('admin.settings.payments', [
                'keyConfigured' => filled(config('services.stripe.key')),
                'secretConfigured' => filled(config('services.stripe.secret')),
                'webhookConfigured' => filled(config('services.stripe.webhook_secret')),
                // Never display the actual key — only whether it looks
                // like a test or live key, from its safe-to-show prefix.
                'mode' => str_starts_with((string) config('services.stripe.key'), 'pk_live_') ? 'live'
                    : (str_starts_with((string) config('services.stripe.key'), 'pk_test_') ? 'test' : null),
            ]);
        }

        abort_unless(in_array($group, self::GROUPS, true), 404);

        return view('admin.settings.edit', [
            'group' => $group,
            'values' => Setting::group($group),
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(in_array($group, self::GROUPS, true), 404);

        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            Setting::set($key, $value, $group);
        }

        AuditLogger::record('settings.updated', null, null, $data, "{$group} settings");

        return redirect()->route('admin.settings.edit', $group)->with('status', 'Settings saved.');
    }
}
