<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AppRequest;
use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppFeature;
use App\Models\Media;
use App\Models\Platform;
use App\Services\AuditLogger;
use App\Services\MediaLibrary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppController extends Controller
{
    public function index(Request $request): View
    {
        $query = App::query()->with('category')->orderByDesc('updated_at');

        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        if ($search = $request->string('q')->toString()) {
            $query->where('name', 'like', "%{$search}%");
        }

        return view('admin.apps.index', [
            'apps' => $query->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.apps.form', [
            'app' => new App(['currency' => 'CAD']),
            'categories' => AppCategory::orderBy('sort_order')->get(),
            'platforms' => Platform::orderBy('sort_order')->get(),
        ]);
    }

    public function store(AppRequest $request): RedirectResponse
    {
        $app = App::create($this->mapData($request));
        $app->platforms()->sync($request->input('platforms', []));

        AuditLogger::record('app.created', $app, null, $app->only(['name', 'slug', 'status', 'price_cents']));

        return redirect()->route('admin.apps.edit', $app)->with('status', 'App created.');
    }

    public function edit(App $app): View
    {
        $app->load([
            'media', 'features', 'platforms',
            'editions' => fn ($q) => $q->orderBy('sort_order'),
            'editions.entitlements.platform',
            'releases' => fn ($q) => $q->orderByDesc('released_at'),
            'releases.platform',
            'entitlements' => fn ($q) => $q->orderByDesc('created_at'),
            'entitlements.platform',
            'entitlements.edition',
        ]);

        return view('admin.apps.form', [
            'app' => $app,
            'categories' => AppCategory::orderBy('sort_order')->get(),
            'platforms' => Platform::orderBy('sort_order')->get(),
        ]);
    }

    public function update(AppRequest $request, App $app): RedirectResponse
    {
        $before = $app->only(['name', 'slug', 'status', 'price_cents']);
        $app->update($this->mapData($request));
        $app->platforms()->sync($request->input('platforms', []));

        AuditLogger::record('app.updated', $app, $before, $app->only(['name', 'slug', 'status', 'price_cents']));

        return redirect()->route('admin.apps.edit', $app)->with('status', 'App updated.');
    }

    public function destroy(App $app): RedirectResponse
    {
        $before = $app->only(['name', 'slug', 'status']);
        $app->delete();

        AuditLogger::record('app.deleted', $app, $before, null);

        return redirect()->route('admin.apps.index')->with('status', 'App archived (soft-deleted).');
    }

    public function publish(App $app): RedirectResponse
    {
        $before = $app->status;
        $app->update(['status' => 'published']);

        AuditLogger::record('app.status_changed', $app, ['status' => $before], ['status' => 'published']);

        return back()->with('status', "{$app->name} published.");
    }

    public function unpublish(App $app): RedirectResponse
    {
        $before = $app->status;
        $app->update(['status' => 'draft']);

        AuditLogger::record('app.status_changed', $app, ['status' => $before], ['status' => 'draft']);

        return back()->with('status', "{$app->name} unpublished.");
    }

    public function archive(App $app): RedirectResponse
    {
        $before = $app->status;
        $app->update(['status' => 'archived']);

        AuditLogger::record('app.status_changed', $app, ['status' => $before], ['status' => 'archived']);

        return back()->with('status', "{$app->name} archived.");
    }

    public function toggleFeature(App $app): RedirectResponse
    {
        $app->update(['is_featured' => ! $app->is_featured]);

        return back()->with('status', $app->is_featured ? "{$app->name} is now featured." : "{$app->name} removed from featured.");
    }

    public function duplicate(App $app): RedirectResponse
    {
        $copy = $app->replicate(['slug']);
        $copy->name = $app->name.' (Copy)';
        $copy->slug = Str::slug($app->name.'-copy-'.Str::random(4));
        $copy->status = 'draft';
        $copy->is_featured = false;
        $copy->save();
        $copy->platforms()->sync($app->platforms->pluck('id'));

        foreach ($app->features as $feature) {
            $copy->features()->create($feature->only(['title', 'description', 'icon', 'sort_order']));
        }

        return redirect()->route('admin.apps.edit', $copy)->with('status', "Duplicated as \"{$copy->name}\".");
    }

    public function attachMedia(Request $request, App $app): RedirectResponse
    {
        $request->validate([
            'media_id' => ['required_without:file', 'nullable', 'exists:media,id'],
            'file' => ['required_without:media_id', 'nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp', 'max:8192'],
            'type' => ['required', 'in:icon,feature_graphic,screenshot'],
        ]);

        $media = $request->hasFile('file')
            ? MediaLibrary::storeUploadedFile($request->file('file'), Auth::id())
            : Media::findOrFail($request->input('media_id'));

        if ($request->input('type') === 'icon' || $request->input('type') === 'feature_graphic') {
            // Only one icon / one feature graphic per app — replace, don't stack.
            $existing = $app->media()->wherePivot('type', $request->input('type'))->pluck('media.id');
            $app->media()->detach($existing);
        }

        $nextOrder = $app->media()->wherePivot('type', $request->input('type'))->count();

        $app->media()->attach($media->id, [
            'type' => $request->input('type'),
            'sort_order' => $nextOrder,
        ]);

        return back()->with('status', 'Image attached.');
    }

    public function detachMedia(App $app, Media $media): RedirectResponse
    {
        $app->media()->detach($media->id);

        return back()->with('status', 'Image removed from app.');
    }

    public function addFeature(Request $request, App $app): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:10'],
        ]);

        $app->features()->create([
            ...$data,
            'sort_order' => $app->features()->count(),
        ]);

        return back()->with('status', 'Feature added.');
    }

    public function removeFeature(App $app, AppFeature $feature): RedirectResponse
    {
        abort_unless($feature->app_id === $app->id, 404);
        $feature->delete();

        return back()->with('status', 'Feature removed.');
    }

    private function mapData(AppRequest $request): array
    {
        $data = $request->validated();

        $isFree = $request->boolean('is_free');

        return [
            ...$data,
            'is_free' => $isFree,
            // Price fields are disabled (and so absent from the request)
            // client-side whenever "is_free" is checked — ?? null keeps
            // that safe even if a request omits them some other way.
            'price_cents' => $isFree || ($data['price'] ?? null) === null ? null : (int) round($data['price'] * 100),
            'sale_price_cents' => empty($data['sale_price'] ?? null) ? null : (int) round($data['sale_price'] * 100),
            'currency' => ($data['currency'] ?? null) ?: 'CAD',
            'direct_purchase_enabled' => $request->boolean('direct_purchase_enabled'),
            'is_featured' => $request->boolean('is_featured'),
            'demo_enabled' => $request->boolean('demo_enabled'),
            'android_delivery_mode' => $data['android_delivery_mode'] ?? 'none',
            'windows_delivery_mode' => $data['windows_delivery_mode'] ?? 'none',
            'web_available' => $request->boolean('web_available'),
            'web_login_required' => $request->boolean('web_login_required'),
            'license_type' => $data['license_type'] ?? 'personal',
            'update_policy' => $data['update_policy'] ?? 'updates_included',
            // Laravel's attribute-based #[Fillable] inserts an explicit NULL
            // for any fillable column left out of the attributes array,
            // which bypasses the migration's ->default(0) — so it's set
            // explicitly here rather than relying on that default.
            'featured_order' => $data['featured_order'] ?? 0,
        ];
    }
}
