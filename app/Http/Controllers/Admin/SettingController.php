<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const GROUPS = ['business', 'site', 'store'];

    public function edit(string $group): View
    {
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

        return redirect()->route('admin.settings.edit', $group)->with('status', 'Settings saved.');
    }
}
