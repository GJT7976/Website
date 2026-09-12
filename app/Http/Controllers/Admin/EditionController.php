<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\AppEdition;
use App\Models\Platform;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EditionController extends Controller
{
    public function store(Request $request, App $app): RedirectResponse
    {
        $data = $this->validated($request);

        $edition = $app->editions()->create([
            ...$data,
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'currency' => $app->currency ?? 'CAD',
        ]);

        AuditLogger::record('edition.created', $edition, null, $edition->only(['name', 'price_cents', 'active']));

        return back()->with('status', "Edition \"{$edition->name}\" added. Now set which platforms it includes below.");
    }

    public function update(Request $request, App $app, AppEdition $edition): RedirectResponse
    {
        abort_unless($edition->app_id === $app->id, 404);

        $data = $this->validated($request, $edition);
        $before = $edition->only(['name', 'price_cents', 'active']);

        $edition->update([
            ...$data,
            'slug' => $data['slug'] ?: Str::slug($data['name']),
        ]);

        AuditLogger::record('edition.updated', $edition, $before, $edition->only(['name', 'price_cents', 'active']));

        return back()->with('status', "Edition \"{$edition->name}\" updated.");
    }

    public function destroy(App $app, AppEdition $edition): RedirectResponse
    {
        abort_unless($edition->app_id === $app->id, 404);
        $before = $edition->only(['name', 'price_cents']);
        $edition->delete();

        AuditLogger::record('edition.deleted', null, $before, null, $before['name']);

        return back()->with('status', 'Edition removed.');
    }

    /**
     * Replace this edition's entitlement template — which platforms/
     * access-types it grants (§16). Existing customer_entitlements already
     * granted from past purchases are untouched; this only changes what
     * future purchases of this edition will grant.
     */
    public function syncEntitlements(Request $request, App $app, AppEdition $edition): RedirectResponse
    {
        abort_unless($edition->app_id === $app->id, 404);

        $data = $request->validate([
            'grants' => ['array'],
            'grants.*' => ['string'], // "{platform_id}:{access_type}"
        ]);

        $edition->entitlements()->delete();

        foreach ($data['grants'] ?? [] as $grant) {
            [$platformId, $accessType] = explode(':', $grant, 2);

            if (! Platform::whereKey($platformId)->exists() || ! in_array($accessType, ['download', 'web_access'], true)) {
                continue;
            }

            $edition->entitlements()->create(['platform_id' => $platformId, 'access_type' => $accessType]);
        }

        return back()->with('status', "\"{$edition->name}\" now includes: ".(collect($edition->includedPlatforms())->pluck('platform_name')->implode(', ') ?: 'nothing yet'));
    }

    private function validated(Request $request, ?AppEdition $edition = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'name' => $data['name'],
            'slug' => $data['slug'] ?? null,
            'description' => $data['description'] ?? null,
            'price_cents' => (int) round($data['price'] * 100),
            'active' => $request->boolean('active'),
            'featured' => $request->boolean('featured'),
            'sort_order' => $data['sort_order'] ?? $edition?->sort_order ?? 0,
        ];
    }
}
