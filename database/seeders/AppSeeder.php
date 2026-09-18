<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\AppEdition;
use App\Models\EditionEntitlement;
use App\Models\Media;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBreadMaker();
        $this->seedHummusHouse();
        $this->seedLaCucinaItaliana();
        $this->seedCelestialGrimoire();
        $this->seedMarginPos();
        $this->seedBarTenderAtlas();
        $this->seedTheStockPot();
        $this->seedPlaceholderApps();
        $this->seedTestPurchaseApp();
    }

    /**
     * Plain `App::updateOrCreate()` isn't actually safe to re-run once a
     * seeded app has ever been soft-deleted (Admin → Apps → Delete):
     * Eloquent's default query excludes trashed rows, so the lookup
     * finds nothing and falls through to an INSERT, which then collides
     * with the unique `slug` constraint the trashed row still occupies.
     * `withTrashed()` on the lookup finds it either way, and an explicit
     * `restore()` brings a previously-deleted seed app back — this
     * method exists purely to be re-run without ever failing.
     */
    private function updateOrCreateApp(array $attributes, array $values): App
    {
        $app = App::withTrashed()->updateOrCreate($attributes, $values);

        if ($app->trashed()) {
            $app->restore();
        }

        return $app;
    }

    /**
     * Bread Maker — a real, published app. Its assets (icon + two
     * screenshots captured from the actual running demo) come from
     * storage/app/public/media/seed, copied there from the source project
     * at C:\Users\User\Documents\APKs\BreadMaker\completed\web-pwa.
     *
     * is_featured is deliberately false: the owner asked that this app's
     * imagery not appear on the homepage/landing page. It still shows in
     * full on /apps and its own /apps/bread-maker page.
     *
     * Originally seeded free/web-only (Phase 1, before an Android or
     * Windows build existed). The source project has since shipped a real
     * signed Android APK and a Windows NSIS installer (v1.1.2,
     * completed/Website_Delivery) — sold here the same way as Hummus House
     * and La Cucina Italiana: per-platform editions via
     * seedBreadMakerEditions(), with the app itself no longer free.
     */
    private function seedBreadMaker(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'bread-maker'],
            [
                'name' => 'Bread Maker',
                'tagline' => "Baker's Percentage Calculator",
                'short_description' => 'Professional baker\'s percentage calculator for artisan bread recipes — hydration, fermentation, pan sizing and more.',
                'long_description' => <<<'MD'
Bread Maker scales real artisan bread recipes the way a professional bakery
does: by baker's percentage, with every ingredient expressed as a percentage
of total flour weight. Set a target hydration and the app automatically
rebalances your water as you add eggs, milk, or other wet ingredients so the
dough stays exactly where you want it.

Build a recipe from a blend of flours, choose a preferment (biga, poolish,
sponge, or a mother/levain), and let the app work out quantities for you —
whether you're entering a flour weight directly or scaling to fill specific
round or rectangular pans. A built-in desired dough temperature (DDT)
calculator helps you hit consistent fermentation every time, and a
fermentation schedule with timers keeps a multi-stage bake on track.

Everything runs locally in your browser or as an installed app — no account,
no internet connection required after the first load, and your recipes stay
on your device with backup/restore built in.
MD,
                'category_id' => $category?->id,
                'version' => '1.1.3',
                // 2026-09-16: converted to free-install + Pro Upgrade (the
                // app's own website-license system was verified real and
                // correctly configured for app_id=1 before this change —
                // see F:\website's session notes). Was previously sold as
                // paid per-platform editions; is_free=true + a single
                // pro-upgrade edition below matches every other app
                // retrofitted the same day. Existing uploaded releases are
                // unchanged and become free downloads automatically via
                // FreeDownloadController.
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'static_web',
                // Deliberately NOT public/demos/{slug}/ — that exact path
                // collides with the /demos catalogue route at the
                // web-server level (PHP's built-in server, and Apache/
                // Nginx in production, resolve a request against a real
                // directory on disk before Laravel's router ever runs).
                // See DEMO_DEPLOYMENT.md.
                'demo_url' => '/demo-builds/bread-maker/index.html',
                'demo_version' => '1.1.3',
                'demo_instructions' => 'Bread Maker runs entirely in your browser. Try a Quick Start preset, or dial in your own hydration, flour blend, and pan size — nothing you enter here is saved outside this device. Buy the Android or Windows app below for an installable version (with a 3-day free trial of Pro).',
                'demo_warning' => null,
                'demo_reset_mode' => 'Reload the page — the calculator has no server-side state to reset.',
                'support_info' => 'For questions about Bread Maker, use the Contact page and select this app.',
                'system_requirements' => 'Android 6.0+, Windows 10 (64-bit) or later, or any modern web browser. Installable as a Progressive Web App (PWA) for offline use.',
                'seo_title' => "Bread Maker — Baker's Percentage Calculator",
                'seo_description' => 'Baker\'s percentage calculator for artisan bread: hydration, preferments, pan scaling, and dough temperature. Available for Android, Windows, and in your browser.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedBreadMakerEditions($app);
        $this->deactivateOtherEditions($app, ['pro-upgrade']);

        $icon = $this->seedMedia('bread-maker-icon.png', 'image/png', 1024, 1024, 'Bread Maker app icon');
        $feature = $this->seedMedia('bread-maker-feature.png', 'image/png', 1024, 500, 'Bread Maker feature graphic');
        $shot1 = $this->seedMedia('bread-maker-shot-1.png', 'image/png', 390, 844, 'Bread Maker — quick start presets and dough amount');
        $shot2 = $this->seedMedia('bread-maker-shot-2.png', 'image/png', 390, 700, 'Bread Maker — flour blend selection');

        $app->media()->sync([
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
            $shot1->id => ['type' => 'screenshot', 'sort_order' => 0],
            $shot2->id => ['type' => 'screenshot', 'sort_order' => 1],
        ]);

        $features = [
            ['title' => 'Baker\'s percentage engine', 'description' => 'Every ingredient scales automatically from a percentage of total flour weight, the way professional bakeries work.', 'icon' => '🌾', 'sort_order' => 0],
            ['title' => 'Hydration control', 'description' => 'Set a target hydration and the app rebalances water automatically as wet ingredients are added.', 'icon' => '💧', 'sort_order' => 1],
            ['title' => 'Preferments built in', 'description' => 'Biga, poolish, sponge, or mother/levain — pick a preferment and the flour/hydration split is handled for you.', 'icon' => '🧬', 'sort_order' => 2],
            ['title' => 'Pan size scaling', 'description' => 'Enter flour weight directly, or scale a recipe to fill specific round or rectangular pans.', 'icon' => '🥖', 'sort_order' => 3],
            ['title' => 'Dough temperature calculator', 'description' => 'A desired dough temperature (DDT) calculator helps hit consistent fermentation bake after bake.', 'icon' => '🌡', 'sort_order' => 4],
            ['title' => 'Fermentation schedule & timers', 'description' => 'Plan a multi-stage bake and keep it on track with built-in timers.', 'icon' => '⏱', 'sort_order' => 5],
            ['title' => 'Metric or imperial', 'description' => 'Switch between grams and ounces at any time.', 'icon' => '⚖️', 'sort_order' => 6],
            ['title' => 'Works offline', 'description' => 'Installable as a Progressive Web App; your recipes stay on your device with backup/restore.', 'icon' => '📶', 'sort_order' => 7],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * 2026-09-16: replaced the old split Android/Windows/Bundle editions
     * with a single flat "Pro Upgrade" — the app is now a free install
     * (see is_free above) and this edition's only job is to grant the
     * license that unlocks Pro via www/license-client.js (app_id=1), not
     * to gate the download. Same pattern as every other app retrofitted
     * this session — see The Stock Pot's seeder methods for the fullest
     * doc-comment explanation of why.
     *
     * 2026-09-17: the app itself (v1.1.3) added a 3-day free trial of Pro
     * ahead of the license-key unlock — this edition's price/mechanics are
     * unchanged, only the description below now discloses the trial.
     */
    private function seedBreadMakerEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'pro-upgrade', 'name' => 'Pro Upgrade', 'description' => 'Bread Maker is free to download and use, with a 3-day free trial of Pro built in. This one-time purchase keeps Pro unlocked permanently via a license key, activated in-app on your Android and/or Windows devices.', 'price_cents' => 299, 'sort_order' => 0, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
        ];

        foreach ($editions as $data) {
            $edition = AppEdition::updateOrCreate(
                ['app_id' => $app->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'currency' => $app->currency ?? 'USD',
                    'active' => true,
                    'featured' => $data['featured'] ?? false,
                    'sort_order' => $data['sort_order'],
                ]
            );

            foreach ($data['grants'] as [$platform, $accessType]) {
                if (! $platform) {
                    continue;
                }

                EditionEntitlement::updateOrCreate([
                    'app_edition_id' => $edition->id,
                    'platform_id' => $platform->id,
                    'access_type' => $accessType,
                ]);
            }
        }
    }

    /**
     * Hummus House — a real Flutter app (Android AAB/APK, Windows
     * installer, Web/PWA build; iOS not built/verified). Source:
     * F:\Apps\Hummus House\completed. Its web build is a genuine Flutter
     * web release, deployed at public/demo-builds/hummus-house/ with its
     * <base href> patched to that subdirectory (Flutter defaults to "/",
     * which breaks asset loading outside the domain root — see
     * DEMO_DEPLOYMENT.md). Recipe count, categories, and language list
     * below are read directly from the app's own recipes.json/guide/*.json
     * bundled in that build, not invented.
     */
    private function seedHummusHouse(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'hummus-house'],
            [
                'name' => 'Hummus House',
                'tagline' => '52 Recipes, 5 Languages',
                'short_description' => 'A modern hummus recipe app with step-by-step Cook Mode, a shopping list, and a meal planner — 52 recipes across 5 categories.',
                'long_description' => <<<'MD'
Hummus House is a recipe app built around one thing done well: hummus, in
every direction. 52 recipes span five categories — Classic, Sesame Hummus,
Flavored Sesame Hummus, Flavored Chickpea Hummus, and Alternative-Base
Hummus — from a straightforward classic house blend to sun-dried tomato
sesame, harissa, and beyond.

Cook Mode walks through each recipe step by step with built-in timers (the
screen stays on while you cook), so there's no scrolling back and forth
with sticky hands. Save favorites, build a shopping list automatically
from the recipes you've picked, and use the planner to line up what you're
making across the week. Search finds a recipe by ingredient or name in
seconds.

The app is available in English, French, Spanish, German, and Italian.
MD,
                'category_id' => $category?->id,
                'version' => '1.0',
                // 2026-09-16: converted to free-install + Pro Upgrade — the
                // app's own website-license system (app_id=2) was verified
                // real before this change. See seedHummusHouseEditions().
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'flutter_web',
                'demo_url' => '/demo-builds/hummus-house/index.html',
                'demo_version' => '1.0',
                'demo_instructions' => 'This is the real app, running in your browser. Browse recipes, try Cook Mode, and build a shopping list — everything you do stays in this browser only.',
                'demo_warning' => 'This demo is a full Flutter web build (~65 MB) — first load can take a few seconds on a slower connection.',
                'demo_reset_mode' => 'Favorites, planner, and shopping list are saved in this browser only. Clearing this site\'s data in your browser resets the demo.',
                'support_info' => 'For questions about Hummus House, use the Contact page and select this app.',
                'system_requirements' => 'Android 8+, Windows 10/11 (64-bit), or any modern web browser.',
                'seo_title' => 'Hummus House — Hummus Recipes, Cook Mode & Meal Planner',
                'seo_description' => '52 hummus recipes across 5 categories, with step-by-step Cook Mode, a shopping list, and a meal planner. Available in 5 languages.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedHummusHouseEditions($app);
        $this->deactivateOtherEditions($app, ['pro-upgrade']);

        $icon = $this->seedMedia('icon.png', 'image/png', 512, 512, 'Hummus House app icon', 'hummus-house');
        $feature = $this->seedMedia('feature.png', 'image/png', 1024, 500, 'Hummus House feature graphic', 'hummus-house');

        $screenshots = [
            ['file' => 'shot-1-home.png', 'alt' => 'Hummus House — home screen with featured recipes'],
            ['file' => 'shot-2-recipe-ingredients.png', 'alt' => 'Hummus House — recipe ingredients'],
            ['file' => 'shot-3-recipe-instructions.png', 'alt' => 'Hummus House — recipe instructions'],
            ['file' => 'shot-4-cook-mode.png', 'alt' => 'Hummus House — step-by-step Cook Mode with timer'],
            ['file' => 'shot-5-shopping-list.png', 'alt' => 'Hummus House — shopping list'],
            ['file' => 'shot-6-planner.png', 'alt' => 'Hummus House — meal planner'],
            ['file' => 'shot-7-search.png', 'alt' => 'Hummus House — recipe search'],
            ['file' => 'shot-8-settings.png', 'alt' => 'Hummus House — settings and language options'],
        ];

        $mediaSync = [
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
        ];

        foreach ($screenshots as $index => $shot) {
            $media = $this->seedMedia($shot['file'], 'image/png', 1080, 1920, $shot['alt'], 'hummus-house');
            $mediaSync[$media->id] = ['type' => 'screenshot', 'sort_order' => $index];
        }

        $app->media()->sync($mediaSync);

        $features = [
            ['title' => '52 recipes, 5 categories', 'description' => 'Classic, Sesame Hummus, Flavored Sesame, Flavored Chickpea, and Alternative-Base Hummus.', 'icon' => '🥣', 'sort_order' => 0],
            ['title' => 'Step-by-step Cook Mode', 'description' => 'Built-in timers and a screen that stays on while you cook — no scrolling back and forth.', 'icon' => '👨‍🍳', 'sort_order' => 1],
            ['title' => 'Shopping list', 'description' => 'Build a shopping list automatically from the recipes you\'ve picked.', 'icon' => '🛒', 'sort_order' => 2],
            ['title' => 'Meal planner', 'description' => 'Line up what you\'re making across the week.', 'icon' => '📅', 'sort_order' => 3],
            ['title' => 'Search & favorites', 'description' => 'Find a recipe by ingredient or name, and save favorites for later.', 'icon' => '🔍', 'sort_order' => 4],
            ['title' => '5 languages', 'description' => 'English, French, Spanish, German, and Italian.', 'icon' => '🌐', 'sort_order' => 5],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * 2026-09-16: replaced the old split Android/Windows/Bundle editions
     * with a single flat "Pro Upgrade" ($2.99) — the app is now a free
     * install (see is_free above); this edition's only job is to grant the
     * license that unlocks Premium via lib/services/license_client_service.dart
     * (app_id=2), not to gate the download.
     */
    private function seedHummusHouseEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'pro-upgrade', 'name' => 'Pro Upgrade', 'description' => 'Hummus House is free to download and use. This one-time purchase unlocks Premium on up to 2 of your Android and/or Windows devices via a license key.', 'price_cents' => 299, 'sort_order' => 0, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
        ];

        foreach ($editions as $data) {
            $edition = AppEdition::updateOrCreate(
                ['app_id' => $app->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'currency' => $app->currency ?? 'USD',
                    'active' => true,
                    'featured' => $data['featured'] ?? false,
                    'sort_order' => $data['sort_order'],
                ]
            );

            foreach ($data['grants'] as [$platform, $accessType]) {
                if (! $platform) {
                    continue;
                }

                EditionEntitlement::updateOrCreate([
                    'app_edition_id' => $edition->id,
                    'platform_id' => $platform->id,
                    'access_type' => $accessType,
                ]);
            }
        }
    }

    /**
     * La Cucina Italiana — a real Flutter app (Android APK, Windows MSIX,
     * Web/PWA build; iOS not built). Source: F:\Apps\La Cucina Italiana. Its
     * web build is a genuine Flutter web release, deployed at
     * public/demo-builds/la-cucina-italiana/ with its <base href> patched
     * to that subdirectory, same as Hummus House — see DEMO_DEPLOYMENT.md.
     * Recipe/region counts and feature list below are read directly from
     * the app's own docs/feature_inventory.md and docs/final_report.md, not
     * invented.
     *
     * The base app is never gated (every recipe, Cook Mode, search,
     * shopping list, backup/restore, etc. are free in every edition sold
     * here). Separately, the Android build offers its own one-time
     * "Premium" in-app purchase (docs/premium_selection.md) through Google
     * Play Billing — unrelated to, and not unlocked by, buying the app on
     * this website; Play Billing only exists once the app is installed via
     * Google Play, so it isn't purchasable from the sideloaded direct
     * download or from the Windows build.
     */
    private function seedLaCucinaItaliana(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'la-cucina-italiana'],
            [
                'name' => 'La Cucina Italiana',
                'tagline' => 'Authentic Recipes, Timeless Flavors',
                'short_description' => '200 authentic Italian pasta recipes with step-by-step Cook Mode, a serving scaler, a shopping list, and validated offline backup — available in 5 languages.',
                'long_description' => <<<'MD'
La Cucina Italiana is an offline-first Italian pasta recipe app built around
a 200-dish corpus spanning more than 60 regions — from Northern classics to
Roman, Southern, Traditional, and Italian-American styles. Every recipe
ships with a real photo, and a mathematical serving scaler rebalances
quantities to 2, 4, 6, or a custom serving count in one tap.

Cook Mode walks through each recipe step by step, keeps the screen awake,
and remembers your place if you get interrupted — with timers automatically
parsed straight out of the instructions, so nothing needs setting up by
hand. The built-in Pasta Guide covers genuine technique (salting water like
the sea, saving pasta water, matching shapes to sauces) rather than generic
tips.

Save favorites, add personal ratings and notes, organize recipes into
collections, and build a shopping list automatically as you go. Everything
is stored locally with validated, versioned backup and restore — no
account, and no internet connection required after install. The app is
available in English, Italian, Spanish, French, and German.

The base app is entirely free to use — no recipe, feature, or screen is
ever locked behind a paywall. On Android, an optional one-time "Premium"
upgrade is available separately through Google Play (unlimited collections,
a full-resolution offline photo pack, and recipe/shopping-list PDF export);
it's a separate in-app purchase, not something unlocked by buying the app
here.
MD,
                'category_id' => $category?->id,
                'version' => '1.0.0',
                // 2026-09-16: converted to free-install + Pro Upgrade — the
                // app's own website-license system (app_id=6) was verified
                // real before this change. See seedLaCucinaItalianaEditions().
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'flutter_web',
                // Deliberately NOT public/demos/{slug}/ — see DEMO_DEPLOYMENT.md.
                'demo_url' => '/demo-builds/la-cucina-italiana/index.html',
                'demo_version' => '1.0.0',
                'demo_instructions' => 'This is the real app, running in your browser. Browse all 200 recipes, try Cook Mode, and build a shopping list — everything you do stays in this browser only. The optional Premium upgrade requires Google Play and isn\'t purchasable in this demo.',
                'demo_warning' => 'This demo is a full Flutter web build (~73 MB) — first load can take a few seconds on a slower connection.',
                'demo_reset_mode' => 'Favorites, notes, collections, and the shopping list are saved in this browser only. Clearing this site\'s data in your browser resets the demo.',
                'support_info' => 'For questions about La Cucina Italiana, use the Contact page and select this app.',
                'system_requirements' => 'Android 8+, Windows 10/11 (64-bit), or any modern web browser.',
                'seo_title' => 'La Cucina Italiana — Authentic Italian Pasta Recipes',
                'seo_description' => '200 authentic Italian pasta recipes with Cook Mode, a serving scaler, a shopping list, and offline backup. Available in English, Italian, Spanish, French, and German.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedLaCucinaItalianaEditions($app);
        $this->deactivateOtherEditions($app, ['pro-upgrade']);

        // feature.png is the polished marketing banner supplied in the
        // app's Website_Delivery folder (not the plain Play Store
        // feature-graphic asset) — used as-is at its native 2048x768.
        $icon = $this->seedMedia('icon.png', 'image/png', 512, 512, 'La Cucina Italiana app icon', 'la-cucina-italiana');
        $feature = $this->seedMedia('feature.png', 'image/png', 2048, 768, 'La Cucina Italiana — authentic recipes, timeless flavors', 'la-cucina-italiana');

        $screenshots = [
            ['file' => 'shot-1-home.png', 'alt' => 'La Cucina Italiana — home screen with recipes grouped by region'],
            ['file' => 'shot-2-browse.png', 'alt' => 'La Cucina Italiana — browsing sauces by family'],
            ['file' => 'shot-3-recipe.png', 'alt' => 'La Cucina Italiana — recipe detail with ingredients and serving scaler'],
            ['file' => 'shot-4-cook-mode.png', 'alt' => 'La Cucina Italiana — step-by-step Cook Mode'],
            ['file' => 'shot-5-search.png', 'alt' => 'La Cucina Italiana — recipe search across all 200 recipes'],
            ['file' => 'shot-6-shopping-list.png', 'alt' => 'La Cucina Italiana — shopping list'],
            ['file' => 'shot-7-guide.png', 'alt' => 'La Cucina Italiana — the Pasta Guide technique reference'],
            ['file' => 'shot-8-settings.png', 'alt' => 'La Cucina Italiana — settings, language, and Premium'],
        ];

        $mediaSync = [
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
        ];

        foreach ($screenshots as $index => $shot) {
            $media = $this->seedMedia($shot['file'], 'image/png', 1080, 1920, $shot['alt'], 'la-cucina-italiana');
            $mediaSync[$media->id] = ['type' => 'screenshot', 'sort_order' => $index];
        }

        $app->media()->sync($mediaSync);

        $features = [
            ['title' => '200-recipe offline corpus', 'description' => '200 authentic Italian pasta recipes with real photos, spanning 60+ regions — no internet connection required.', 'icon' => '🍝', 'sort_order' => 0],
            ['title' => 'Serving scaler', 'description' => 'Scale any recipe to 2, 4, 6, or a custom serving count — quantities recalculate automatically.', 'icon' => '⚖️', 'sort_order' => 1],
            ['title' => 'Auto-parsed step timers', 'description' => 'Cooking steps carry their own timers, parsed straight from the instructions.', 'icon' => '⏱', 'sort_order' => 2],
            ['title' => 'Cook Mode', 'description' => 'A hands-free, step-by-step cooking view that remembers your place and keeps the screen awake.', 'icon' => '👨‍🍳', 'sort_order' => 3],
            ['title' => 'The Pasta Guide', 'description' => 'A genuine technique reference — salting water, saving pasta water, matching shapes to sauces, and more.', 'icon' => '📖', 'sort_order' => 4],
            ['title' => 'Shopping list, favorites & collections', 'description' => 'Build a shopping list, save favorites, add personal ratings and notes, and organize recipes into collections.', 'icon' => '🛒', 'sort_order' => 5],
            ['title' => 'Validated local backup & restore', 'description' => 'Versioned backup and restore — your data stays on your device, and no account is required.', 'icon' => '💾', 'sort_order' => 6],
            ['title' => '5 languages', 'description' => 'English, Italian, Spanish, French, and German.', 'icon' => '🌐', 'sort_order' => 7],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * 2026-09-16: replaced the old split Android/Windows/Bundle editions
     * with a single flat "Pro Upgrade" ($2.99) — the app is now a free
     * install (see is_free above); this edition's only job is to grant the
     * license that unlocks Premium via lib/services/license_client_service.dart
     * (app_id=6), not to gate the download.
     */
    private function seedLaCucinaItalianaEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'pro-upgrade', 'name' => 'Pro Upgrade', 'description' => 'La Cucina Italiana is free to download and use. This one-time purchase unlocks Premium on up to 2 of your Android and/or Windows devices via a license key.', 'price_cents' => 299, 'sort_order' => 0, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
        ];

        foreach ($editions as $data) {
            $edition = AppEdition::updateOrCreate(
                ['app_id' => $app->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'currency' => $app->currency ?? 'USD',
                    'active' => true,
                    'featured' => $data['featured'] ?? false,
                    'sort_order' => $data['sort_order'],
                ]
            );

            foreach ($data['grants'] as [$platform, $accessType]) {
                if (! $platform) {
                    continue;
                }

                EditionEntitlement::updateOrCreate([
                    'app_edition_id' => $edition->id,
                    'platform_id' => $platform->id,
                    'access_type' => $accessType,
                ]);
            }
        }
    }

    /**
     * Celestial Grimoire — a real, feature-complete astrology app (natal
     * charts, Pluto included; Whole Sign and Placidus houses; a daily
     * deterministic "celestial card"; real synastry; a searchable
     * Grimoire reference library covering every sign/planet/house/
     * aspect). Localized into English, Romanian, French, Italian, and
     * Spanish (2026-09-18 REPAIR, see the app's own `docs/DECISIONS.md`).
     *
     * Same-day REPAIR also switched the website edition from "paid
     * upfront, fully unlocked" to a free download with a 3-day trial and
     * a permanent license-key unlock afterward (matching Bread Maker's
     * model — see `is_free` above, now `true`). Published with a working
     * demo so it's visible on the site, but deliberately **not yet
     * purchasable**: `direct_purchase_enabled` stays false and no price/
     * edition is set because `PROJECT_SPEC.md`'s `PRICE_OR_PRODUCT_IDS` is
     * still an owner-provided placeholder in the app's own repository —
     * never invent a price here. Flip `direct_purchase_enabled` to true
     * and add a "Pro Upgrade"-style license edition (see
     * seedBreadMakerEditions for the pattern) once the owner supplies real
     * numbers.
     */
    private function seedCelestialGrimoire(): void
    {
        $category = AppCategory::where('slug', 'lifestyle')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'celestial-grimoire'],
            [
                'name' => 'Celestial Grimoire',
                'tagline' => 'A Personal Astrology Companion',
                'short_description' => 'Natal charts, daily celestial cards, transits & lunar guidance — a serious astrology companion, not a generic horoscope app.',
                'long_description' => <<<'MD'
Celestial Grimoire is built around one idea: a genuine, personalized
celestial card for every day of the year, generated from real planetary
positions rather than a generic horoscope template.

Your natal chart is calculated from a real, independently-verified
ephemeris engine — Sun through Pluto plus the lunar nodes and Chiron,
Whole Sign or Placidus houses, your Ascendant and Midheaven — never a
fabricated placement. An interactive chart wheel shows it all at a glance.
Sky Now and Moon
Center show the current sky live: today's planetary positions, Moon phase,
illumination, and the next New and Full Moon. Year of Stars collects your
daily cards across the full 365 (366 in a leap year) day cycle.

Compatibility goes beyond a single unexplained percentage: real inter-chart
aspects are calculated and scored across seven areas — emotional,
communication, attraction, long-term tendencies, friendship, conflict, and
growth — each shown alongside the specific aspects behind it. The Grimoire
itself is a searchable reference library covering all 12 zodiac signs, all
10 classical and modern planets, all 12 houses, and all 5 major aspects,
each with substantial, real content rather than a two-line summary.

A private Celestial Journal ties reflections to dates, cards, transits, and
Moon phases — stored locally on your device by default, with no account
required.

Available in English, Romanian, French, Italian, and Spanish, with a
language picker in onboarding and Settings.

Free to download and use for 3 days from first launch; after the trial, a
one-time license key keeps everything unlocked permanently.
MD,
                'category_id' => $category?->id,
                'version' => '1.1.0',
                'is_free' => true,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                // Not yet configured for sale — see the class-level doc
                // comment above. Left unset rather than guessed.
                'direct_purchase_enabled' => false,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'flutter_web',
                'demo_url' => '/demo-builds/celestial-grimoire/index.html',
                'demo_version' => '1.1.0',
                'demo_instructions' => 'This is the real app, running in your browser. Complete onboarding with any birth date/time/place to see your own natal chart and today\'s Daily Card — nothing you enter here leaves this browser. Pick your language on the onboarding language step or later in Settings (English, Romanian, French, Italian, and Spanish are all supported). The downloadable Android and Windows apps below include a 3-day free trial, after which a one-time license key keeps everything unlocked permanently.',
                'demo_warning' => 'This demo is a full Flutter web build (~45 MB) — first load can take a few seconds on a slower connection.',
                'demo_reset_mode' => 'Your profile, chart, and journal are saved in this browser only. Clearing this site\'s data in your browser resets the demo.',
                'support_info' => 'For questions about Celestial Grimoire, use the Contact page and select this app.',
                'system_requirements' => 'Android 8+, Windows 10/11 (64-bit), or any modern web browser.',
                'seo_title' => 'Celestial Grimoire — Natal Charts, Daily Cards & Astrology Reference',
                'seo_description' => 'A personal astrology companion with real calculated natal charts (including Pluto), a daily deterministic celestial card, synastry, and a searchable sign/planet/house/aspect reference library.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $icon = $this->seedMedia('celestial-grimoire-icon.png', 'image/png', 1254, 1254, 'Celestial Grimoire app icon', 'celestial-grimoire');
        $feature = $this->seedMedia('celestial-grimoire-feature.png', 'image/png', 1774, 887, 'Celestial Grimoire feature graphic', 'celestial-grimoire');
        $shot1 = $this->seedMedia('celestial-grimoire-shot-1.png', 'image/png', 1080, 2400, 'Celestial Grimoire — Home screen with current sky, Moon, and today\'s Daily Card', 'celestial-grimoire');
        $shot2 = $this->seedMedia('celestial-grimoire-shot-2.png', 'image/png', 1080, 2400, 'Celestial Grimoire — searchable Grimoire reference library', 'celestial-grimoire');

        $app->media()->sync([
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
            $shot1->id => ['type' => 'screenshot', 'sort_order' => 0],
            $shot2->id => ['type' => 'screenshot', 'sort_order' => 1],
        ]);

        $features = [
            ['title' => 'Daily Celestial Card', 'description' => 'A deterministic, collectible card generated from real planetary positions, your natal chart, and the day\'s Moon phase — the same day always reveals the same card.', 'icon' => '✨', 'sort_order' => 0],
            ['title' => 'Real natal chart, nodes & Chiron included', 'description' => 'Sun through Pluto plus the lunar nodes and Chiron, Whole Sign or Placidus houses, Ascendant and Midheaven, all on an interactive chart wheel — independently verified against reference astronomical data, never fabricated.', 'icon' => '🪐', 'sort_order' => 1],
            ['title' => 'Sky Now & Moon Center', 'description' => 'Live current planetary positions, Moon phase, illumination, and the next New and Full Moon.', 'icon' => '🌙', 'sort_order' => 2],
            ['title' => 'Year of Stars', 'description' => 'Your full collection of 365 (366 in a leap year) Daily Cards, revealed one day at a time.', 'icon' => '📅', 'sort_order' => 3],
            ['title' => 'Real synastry & compatibility', 'description' => 'Inter-chart aspects scored across seven areas, always shown with the specific aspects behind the score — never an unexplained percentage.', 'icon' => '💞', 'sort_order' => 4],
            ['title' => 'Searchable Grimoire', 'description' => 'All 12 signs, all 10 planets, all 12 houses, and all 5 aspects, with substantial reference content and one search box.', 'icon' => '📖', 'sort_order' => 5],
            ['title' => 'Private Celestial Journal', 'description' => 'Reflections tied to dates, cards, transits, and Moon phases — stored on your device by default, no account required.', 'icon' => '📓', 'sort_order' => 6],
            ['title' => 'Five languages', 'description' => 'English, Romanian, French, Italian, and Spanish — pick your language during onboarding or anytime in Settings.', 'icon' => '🌐', 'sort_order' => 7],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
        // Prunes the stale pre-rename "Real natal chart, Pluto included"
        // row left behind when that feature was renamed to "...nodes &
        // Chiron included" in an earlier release — updateOrCreate() only
        // ever adds/updates by title, never removes a title that's no
        // longer in the list above.
        $app->features()->whereNotIn('title', array_column($features, 'title'))->delete();
    }

    /**
     * Margin POS — a real Flutter app (Android APK/AAB, real-signed with an
     * upload keystore generated 2026-09-13; Web/PWA demo build). Source:
     * C:\Users\User\Documents\APKs\POV. Description, feature list, and
     * pricing below are read from that project's own PROJECT_SPEC.md,
     * docs/play_store/store_listing.md, and docs/PROJECT_STATUS.md, not
     * invented.
     *
     * The base app is free for a single-register, owner-operator business —
     * point of sale, product catalogue, inventory, recipe costing, tax
     * configuration, and full/selective encrypted backup are all
     * unrestricted. **Margin POS Pro** (employees & Canadian payroll, the
     * accounting dashboard, reports, and multi-register LAN mode) is a
     * separate one-time Google Play Billing in-app purchase inside the
     * Android app itself — unrelated to, and not unlocked by, buying
     * anything on this website, exactly the same relationship Android's
     * separate Play Billing "Premium" purchase has to La Cucina Italiana's
     * website listing (see that method's doc comment above). The Web demo
     * below is instead built with Margin POS's own `FORCE_PRO` compile flag
     * (`--dart-define=FORCE_PRO=true`) so visitors can try every feature —
     * safe only because the demo has no backend and no real purchase flow
     * to bypass (see `docs/DECISIONS.md` in the app's own repository, the
     * `FORCE_PRO` entry).
     *
     * Android, Windows, and an Android+Windows Bundle are all sold here —
     * see seedMarginPosEditions(). The Windows edition is now a real Inno
     * Setup installer (`windows_installer/margin_pos.iss` in the source
     * project; the earlier loose exe + DLL folder was not a real,
     * customer-downloadable artifact under `App\Services\ReleaseLibrary`'s
     * rules). Both platforms' actual release files were uploaded through
     * `ReleaseLibrary::storeUploadedFile` (real checksummed files on the
     * private release disk, marked current) — not seeded, since release
     * uploads are an intentionally manual/owner-controlled step in this
     * codebase (see `ADMIN_GUIDE.md`); done once, directly, for this launch.
     */
    private function seedMarginPos(): void
    {
        $category = AppCategory::where('slug', 'business')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'margin-pos'],
            [
                'name' => 'Margin POS',
                'tagline' => 'Real Margin Tracking for Food Businesses',
                'short_description' => 'Offline-first point of sale, ingredient costing, inventory, and Canadian payroll for food trucks, cafés, and small food businesses — ring a sale without the internet.',
                'long_description' => <<<'MD'
Margin POS is an offline-first point-of-sale and small-business management
app built for owner-operated food businesses — food trucks, concession
stands, cafés, bakeries, delis, and market vendors. Ringing a sale never
requires an internet connection: your product catalogue, inventory, sales
history, and accounting data all live on your device.

Beyond checkout, Margin POS converts bulk purchases into a precise cost per
gram, millilitre, or unit, and rolls ingredient and packaging costs up
through recipes automatically — so every product shows real gross margin,
markup, and food-cost percentage, not a guess. Historical costs are locked
in at sale time, so a later price change never rewrites yesterday's profit.
Inventory tracks stock with supplier and purchase history, low-stock
alerts, and a mandatory reason on every adjustment.

The core app — point of sale, product catalogue, inventory, recipe
costing, tax configuration, and full/selective encrypted backup — is free
for a single-register, owner-operator business, full stop. Margin POS Pro
is a single one-time purchase (inside the Android app, via Google Play
Billing) that adds what a growing business needs next: multi-register mode
(turn one device into a Host and pair additional registers over Wi-Fi or a
phone hotspot, no cloud service involved), employee timekeeping and
CRA-oriented Canadian payroll, a real double-entry accounting dashboard,
and traceable sales/profitability/tax reports.

Available in English, French, Spanish, German, Italian, Portuguese,
Polish, Ukrainian, Simplified Chinese, and Arabic with full
right-to-left support.
MD,
                'category_id' => $category?->id,
                'version' => '1.0.0',
                // 2026-09-16: converted to free-install + Pro Upgrade — the
                // app's own website-license system (app_id=9) was verified
                // real before this change; already the most complete
                // implementation of any app on this site (Ed25519 offline
                // verification, background revalidation). See
                // seedMarginPosEditions().
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'flutter_web',
                // Deliberately NOT public/demos/{slug}/ — see DEMO_DEPLOYMENT.md.
                'demo_url' => '/demo-builds/margin-pos/index.html',
                'demo_version' => '1.0.0',
                'demo_instructions' => "This is the real app, running in your browser, with Margin POS Pro unlocked so you can try everything — point of sale, inventory & recipe costing, employees & payroll, accounting, reports, and backup/restore. Complete the short setup wizard with any business details to get started. Nothing you enter here leaves this browser. (Multi-register LAN pairing needs a second real device on the same network, so it won't do much solo in a demo.)",
                'demo_warning' => 'This demo is a full Flutter web build (~44 MB) — first load can take a few seconds on a slower connection.',
                'demo_reset_mode' => 'Everything you set up (business, products, sales, employees…) is saved in this browser only. Clearing this site\'s data in your browser resets the demo.',
                'support_info' => 'For questions about Margin POS, use the Contact page and select this app.',
                'system_requirements' => 'Android 8.0+, or Windows 10/11 (64-bit). Any modern web browser for the demo.',
                'seo_title' => 'Margin POS — Offline POS & Real Margin Tracking',
                'seo_description' => 'Offline-first point of sale, ingredient costing, inventory, and Canadian payroll for food trucks, cafés, and small food businesses. Free for one register; Pro adds multi-register mode, payroll, accounting, and reports.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedMarginPosEditions($app);
        $this->deactivateOtherEditions($app, ['pro-upgrade']);

        $icon = $this->seedMedia('icon.png', 'image/png', 512, 512, 'Margin POS app icon', 'margin-pos');
        $feature = $this->seedMedia('feature.png', 'image/png', 1024, 500, 'Margin POS feature graphic', 'margin-pos');

        $screenshots = [
            ['file' => 'shot-1-pos.png', 'alt' => 'Margin POS — point of sale grid with cart'],
            ['file' => 'shot-2-dashboard.png', 'alt' => 'Margin POS — dashboard with register status and today\'s metrics'],
            ['file' => 'shot-3-accounting.png', 'alt' => 'Margin POS — accounting dashboard with revenue, COGS, and gross profit'],
            ['file' => 'shot-4-products.png', 'alt' => 'Margin POS — product list with live cost and margin'],
        ];

        $mediaSync = [
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
        ];

        foreach ($screenshots as $index => $shot) {
            $media = $this->seedMedia($shot['file'], 'image/png', 1080, 1920, $shot['alt'], 'margin-pos');
            $mediaSync[$media->id] = ['type' => 'screenshot', 'sort_order' => $index];
        }

        $app->media()->sync($mediaSync);

        $features = [
            ['title' => 'Works completely offline', 'description' => 'Ring a cash, debit, or credit sale without an internet connection — ever.', 'icon' => '📡', 'sort_order' => 0],
            ['title' => 'Real per-item margin', 'description' => 'Every product shows real gross margin, markup, and food-cost percentage — computed from actual ingredient costs, not guessed.', 'icon' => '📊', 'sort_order' => 1],
            ['title' => 'Bulk-purchase & portion costing', 'description' => 'Convert a 50 lb bag of flour into a precise cost per gram, millilitre, or unit, and cost recipes down to the portion.', 'icon' => '⚖️', 'sort_order' => 2],
            ['title' => 'Full point of sale', 'description' => 'Product grid with images and search, +/− quantity tiles, line & order discounts, split tenders, held orders, and a persistent cart panel on tablets.', 'icon' => '🛒', 'sort_order' => 3],
            ['title' => 'Inventory with an audit trail', 'description' => 'Low-stock alerts and full stock-movement history — every adjustment requires a reason and records a before/after count.', 'icon' => '📦', 'sort_order' => 4],
            ['title' => 'Validated backup & restore', 'description' => 'Full or selective backup, optional passphrase encryption, and an automatic safety copy taken before every restore.', 'icon' => '💾', 'sort_order' => 5],
            ['title' => 'Multi-register LAN mode (Pro)', 'description' => 'Turn one device into a Host and pair additional registers over Wi-Fi or a phone hotspot — no cloud service involved.', 'icon' => '📶', 'sort_order' => 6],
            ['title' => 'Payroll & accounting (Pro)', 'description' => 'Employee timekeeping, CRA-oriented Canadian payroll (CPP/CPP2/EI), and a real double-entry accounting dashboard with traceable reports.', 'icon' => '🧮', 'sort_order' => 7],
            ['title' => '10 languages', 'description' => 'English, French, Spanish, German, Italian, Portuguese, Polish, Ukrainian, Simplified Chinese, and Arabic with full right-to-left support.', 'icon' => '🌐', 'sort_order' => 8],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * 2026-09-16: replaced the old split Android $2.99 / Windows $2.00 /
     * Bundle $4.99 editions (owner-directed 2026-09-13) with a single flat
     * "Pro Upgrade" ($2.99) — the app is now a free install (see is_free
     * above); this edition's only job is to grant the license that
     * unlocks Margin POS Pro via
     * lib/src/pro/website_license_entitlement_service.dart (app_id=9), not
     * to gate the download. The old asymmetric $2.00 Windows price is
     * folded into this single $2.99 price for catalog consistency with
     * every other app — owner can retune from Apps → Margin POS →
     * Editions.
     */
    private function seedMarginPosEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'pro-upgrade', 'name' => 'Pro Upgrade', 'description' => 'Margin POS is free to download and use for a single-register business. This one-time purchase unlocks Margin POS Pro (multi-register mode, payroll, accounting, reports) on up to 2 of your Android and/or Windows devices via a license key.', 'price_cents' => 299, 'sort_order' => 0, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
        ];

        foreach ($editions as $data) {
            $edition = AppEdition::updateOrCreate(
                ['app_id' => $app->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'currency' => $app->currency ?? 'USD',
                    'active' => true,
                    'featured' => $data['featured'] ?? false,
                    'sort_order' => $data['sort_order'],
                ]
            );

            foreach ($data['grants'] as [$platform, $accessType]) {
                if (! $platform) {
                    continue;
                }

                EditionEntitlement::updateOrCreate([
                    'app_edition_id' => $edition->id,
                    'platform_id' => $platform->id,
                    'access_type' => $accessType,
                ]);
            }
        }
    }

    /**
     * Bar Tender Atlas — a real Flutter app, built for Android and Windows
     * only (current project governance excludes MSIX and Web/PWA — see the
     * app's own CLAUDE.md/PROJECT_SPEC.md). Source:
     * C:\Users\User\Documents\APKs\BarTenderAtlas. Description, feature
     * list, monetization, and system requirements below come from that
     * project's own README.md, PROJECT_SPEC.md and CHANGELOG.md, not
     * invented. Icon, feature graphic, and screenshots are the app's real
     * Google Play store-listing assets (store_assets/google-play/ in the
     * app repo as of the 2026-09-17 release cycle), copied into
     * database/seeders/assets/bar-tender-atlas/.
     *
     * On Google Play, the app is free forever (2,035 recipes, My Bar,
     * Bartender Mode, the recipe editor, backup/restore, etc.) with a
     * separate one-time "Atlas Pro" purchase (product id atlas_pro,
     * US$2.99) through Google Play Billing inside the Android app —
     * unrelated to, and not unlocked by, anything bought on this website,
     * exactly the same relationship La Cucina Italiana's and Margin POS's
     * Play Billing premiums have to their own website listings (see those
     * methods' doc comments above).
     *
     * The WEBSITE channel is a different model: there's no separate free
     * tier here — the app itself runs a 3-day/72h trial
     * (lib/domain/licensing/, lib/data/licensing/) and then locks entirely
     * until a purchased Niagara Indie Apps license key is activated, so the
     * single "unlock" edition below buys continued use of the whole app,
     * not a Pro feature tier. Converted 2026-09-17 from the old three-tier
     * paid-per-platform-download model (android/windows/bundle editions
     * gating the file itself) to this free-install + single-unlock model —
     * same pattern as seedBreadMaker()/seedTheStockPot() — once two
     * prerequisites were resolved the same day: (1) LICENSE_API_BASE_URL
     * was filled in (this app shares the same license backend/app
     * registration — app_id=10 — as every other app on this domain, see
     * F:\website\LICENSE_SYSTEM.md), and (2) the app's HttpLicenseClient,
     * which previously called a guessed/incompatible endpoint shape, was
     * rewritten to match the real `/api/license/*` contract with offline
     * Ed25519 token verification (see PROJECT_SPEC.md and the app's own
     * CHANGELOG.md for that day's entry).
     *
     * As of the 2026-09-17 release, Windows ships as an Inno Setup
     * installer EXE (unsigned — Windows SmartScreen may warn on first run),
     * not MSIX; the app dropped MSIX entirely. The old self-signed MSIX
     * certificate reference below no longer applies to current builds.
     *
     * No live demo is enabled — the app's own docs flag an unresolved owner
     * decision: every recipe photo (1,979 of 2,035 drinks) ships bundled
     * rather than remote-hosted, which is also why the Google Play AAB is
     * ~325 MB. A Web/PWA build isn't produced at all under current
     * governance, so there is nothing to point a demo at; revisit if that
     * changes.
     */
    private function seedBarTenderAtlas(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'bar-tender-atlas'],
            [
                'name' => 'Bar Tender Atlas',
                'tagline' => 'Every Spirit, Every Style',
                'short_description' => '2,000 offline cocktail recipes with My Bar bottle tracking, guided Bartender Mode, and a full editor for adding your own drinks.',
                'long_description' => <<<'MD'
Bar Tender Atlas is an offline-first cocktail reference built around a
2,000-drink bundled catalog, enriched with history, tips, common mistakes,
variations, ABV, and food pairings where the source data supports it. An
ingredient guide covers roughly 117 spirits, liqueurs, mixers, and garnishes,
each with uses, substitutes, brands, and shelf life. Search and filter by
text, style, base spirit, difficulty, method, or flavor — or filter to just
the drinks you can make right now.

My Bar tracks the bottles you actually own, so the catalog can tell you
what's makeable today. Bartender Mode walks a recipe one step at a time with
the screen kept awake and a timer for each step. Want to make something
that isn't in the catalog? A full recipe editor — ingredients, timed steps,
glass, method, tags, and a photo — lets you add your own drinks, and they
join search, My Bar, collections, and Bartender Mode exactly like any
bundled recipe, including in backup/export.

Round it out with favorites, collections, a shopping list, personal ratings
and notes, and a Guide covering glassware, ice, technique, bar tools, and an
oz⇄ml converter. The UI ships in English (complete), with French, Spanish,
German, and Italian in draft. Every core feature listed here is free
forever; a separate one-time "Atlas Pro" purchase inside the Android app
(via Google Play Billing) is available but never required.
MD,
                'category_id' => $category?->id,
                'version' => '1.1.0',
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => false,
                'support_info' => 'For questions about Bar Tender Atlas, use the Contact page and select this app.',
                'system_requirements' => 'Android 7.0 or later, or Windows 10/11 (64-bit).',
                'seo_title' => 'Bar Tender Atlas — 2,000 Offline Cocktail Recipes',
                'seo_description' => '2,000 offline cocktail recipes with My Bar bottle tracking, guided Bartender Mode, an ingredient guide, and a full editor for adding your own drinks.',
            ]
        );

        $platformCodes = ['android', 'windows'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedBarTenderAtlasEditions($app);
        $this->deactivateOtherEditions($app, ['unlock']);

        $icon = $this->seedMedia('icon.png', 'image/png', 512, 512, 'Bar Tender Atlas app icon', 'bar-tender-atlas');
        $feature = $this->seedMedia('feature.png', 'image/png', 1024, 500, 'Bar Tender Atlas feature graphic', 'bar-tender-atlas');

        $screenshots = [
            ['file' => 'shot-1-home.png', 'alt' => 'Bar Tender Atlas — home screen'],
            ['file' => 'shot-2-browse.png', 'alt' => 'Bar Tender Atlas — browsing the recipe catalog'],
            ['file' => 'shot-3-recipe.png', 'alt' => 'Bar Tender Atlas — recipe detail'],
            ['file' => 'shot-4-cook-mode.png', 'alt' => 'Bar Tender Atlas — Bartender Mode step-by-step view'],
            ['file' => 'shot-5-ingredients.png', 'alt' => 'Bar Tender Atlas — ingredient guide'],
            ['file' => 'shot-6-my-bar.png', 'alt' => 'Bar Tender Atlas — My Bar bottle tracking'],
            ['file' => 'shot-7-saved.png', 'alt' => 'Bar Tender Atlas — favorites and collections'],
            ['file' => 'shot-8-guide.png', 'alt' => 'Bar Tender Atlas — glassware and technique guide'],
            ['file' => 'shot-9-atlas-pro.png', 'alt' => 'Bar Tender Atlas — Atlas Pro'],
            ['file' => 'shot-10-add-recipe.png', 'alt' => 'Bar Tender Atlas — adding your own recipe'],
        ];

        $mediaSync = [
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
        ];

        foreach ($screenshots as $index => $shot) {
            $media = $this->seedMedia($shot['file'], 'image/png', 1056, 2112, $shot['alt'], 'bar-tender-atlas');
            $mediaSync[$media->id] = ['type' => 'screenshot', 'sort_order' => $index];
        }

        $app->media()->sync($mediaSync);

        $features = [
            ['title' => '2,000 offline recipes', 'description' => 'A bundled cocktail catalog with history, tips, common mistakes, variations, ABV, and food pairings where available.', 'icon' => '🍸', 'sort_order' => 0],
            ['title' => 'Ingredient guide', 'description' => 'Roughly 117 spirits, liqueurs, mixers, and garnishes, each with uses, substitutes, brands, and shelf life.', 'icon' => '🧪', 'sort_order' => 1],
            ['title' => 'My Bar', 'description' => 'Mark the bottles you own and see every drink you can make right now.', 'icon' => '🍾', 'sort_order' => 2],
            ['title' => 'Bartender Mode', 'description' => 'One step at a time, screen kept awake, with a timer for each step.', 'icon' => '⏱', 'sort_order' => 3],
            ['title' => 'Add your own recipes', 'description' => 'A full editor — ingredients, timed steps, glass, method, tags, and a photo — with custom drinks included everywhere bundled ones are.', 'icon' => '✍️', 'sort_order' => 4],
            ['title' => 'Favorites, collections & shopping list', 'description' => 'Save favorites, organize collections, build a shopping list, and add personal ratings and notes.', 'icon' => '📝', 'sort_order' => 5],
            ['title' => 'The Guide', 'description' => 'Glassware, ice, technique, bar tools, and an oz\u2194ml converter.', 'icon' => '📖', 'sort_order' => 6],
            ['title' => '5 UI languages', 'description' => 'English (complete), with French, Spanish, German, and Italian in draft.', 'icon' => '🌐', 'sort_order' => 7],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * 2026-09-17: replaced the old three-tier android/windows/bundle
     * paid-download editions with a single flat "unlock" edition, now that
     * the app is a free install gated by its own 3-day trial rather than a
     * paid download — see seedBarTenderAtlas()'s doc comment for the full
     * rationale. $2.99 is the app's own PROJECT_SPEC.md
     * WEBSITE_UNLOCK_PRICE default (unchanged from the old Android/Windows
     * per-platform price — a single edition covering both platforms at the
     * old single-platform price, same as Bread Maker's pattern).
     */
    private function seedBarTenderAtlasEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'unlock', 'name' => 'Unlock', 'description' => 'Bar Tender Atlas is free to try for 3 days on Android and Windows. This one-time purchase keeps it unlocked permanently via a license key, activated in-app on up to 2 of your devices.', 'price_cents' => 299, 'sort_order' => 0, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
        ];

        foreach ($editions as $data) {
            $edition = AppEdition::updateOrCreate(
                ['app_id' => $app->id, 'slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'price_cents' => $data['price_cents'],
                    'currency' => $app->currency ?? 'USD',
                    'active' => true,
                    'featured' => $data['featured'] ?? false,
                    'sort_order' => $data['sort_order'],
                ]
            );

            foreach ($data['grants'] as [$platform, $accessType]) {
                if (! $platform) {
                    continue;
                }

                EditionEntitlement::updateOrCreate([
                    'app_edition_id' => $edition->id,
                    'platform_id' => $platform->id,
                    'access_type' => $accessType,
                ]);
            }
        }
    }

    /**
     * The Stock Pot — a real Flutter app (Android APK, Windows Inno Setup
     * installer, Web/PWA build; iOS not built). Source:
     * C:\Users\User\Documents\APKs\The Stock Pot\completed\Website_Delivery.
     * Recipe counts by category are read directly from the app's own
     * assets/data/recipes.json (500 total: 150 Soup, 110 Stew, 60 Chowder,
     * 60 Traditional Gravy, 70 No-Drippings Gravy, 50 Stock/Broth/Base),
     * and the feature list below matches the app's actual controllers
     * (test/*.dart), not invented claims. Localization (PROJECT_SPEC's
     * aspirational 5-language list) is NOT implemented in the app — no
     * lib/l10n or .arb files exist — so it is deliberately omitted here.
     *
     * Unlike every other app seeded above (all is_free=false, sold as
     * paid downloads), The Stock Pot's install itself is free
     * (is_free=true) — only a single "Pro Upgrade" edition is sold, which
     * issues an in-app license rather than gating the download. The
     * actual installer files are served free and unauthenticated via
     * FreeDownloadController (app/Http/Controllers/FreeDownloadController.php,
     * route apps.free-download) — a new, separate path from
     * DownloadController's entitlement-gated one every other app uses —
     * and the show page renders a dedicated "Download Free" + "Unlock
     * Pro" layout (resources/views/apps/partials/{free-download,
     * pro-unlock}.blade.php) instead of the generic edition-picker, so a
     * buyer never reads this as "pay $2.99 to get the app." See
     * seedTheStockPotEditions() for how the edition/license side works.
     *
     * Icon is the app's real assets/icon.png. The two marketing graphics
     * (feature_graphic.png, banner.png) are the owner-supplied promotional
     * images for this launch — feature_graphic.png as the primary feature
     * graphic, banner.png as an additional screenshot-slot image, since the
     * app has no captured in-app UI screenshots yet.
     */
    private function seedTheStockPot(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = $this->updateOrCreateApp(
            ['slug' => 'the-stock-pot'],
            [
                'name' => 'The Stock Pot',
                'tagline' => 'Real Recipes. Real Food. A Better Table.',
                'short_description' => '500 soup, stew, chowder, gravy, and stock/broth recipes with an ingredient scaler, guided cook timers, a shopping list, and offline-first backup.',
                'long_description' => <<<'MD'
The Stock Pot is a 500-recipe collection built around one kitchen staple:
what goes in the pot. 150 soups, 110 stews, 60 chowders, 130 gravies (both
traditional pan-drippings and no-drippings versions), and 50 stocks, broths,
and bases — real recipes with real ingredient lists and step-by-step
instructions, not generated filler.

A mathematical ingredient scaler rebalances every quantity — whole numbers,
fractions, mixed numbers, and ranges — to whatever serving count you need.
Cook timers are built into the steps that need them, with Stock Pot Pro
allowing up to three running at once for a multi-stage recipe. Save
favorites, organize recipes into collections, add personal ratings and
notes, and build a shopping list automatically as you cook.

Everything is stored locally with validated backup and restore — no
account, and no internet connection required after install. The interface
adapts to wider screens with a master-detail layout on tablets and desktop.
Switch between Metric and US measurements at any time.
MD,
                'category_id' => $category?->id,
                'version' => '1.3.0',
                // The app itself is a free install — only Pro is sold, via
                // the single edition below. Every other app on this site
                // is is_free=false because the purchase IS the download;
                // here the download is free and the edition instead grants
                // an in-app Pro license. See seedTheStockPotEditions() and
                // resources/views/apps/partials/{free-download,pro-unlock}.blade.php.
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                'android_delivery_mode' => 'direct',
                'windows_delivery_mode' => 'direct',
                'license_type' => 'personal',
                'update_policy' => 'updates_included',
                'demo_enabled' => true,
                'demo_type' => 'flutter_web',
                // Deliberately NOT public/demos/{slug}/ — see DEMO_DEPLOYMENT.md.
                'demo_url' => '/demo-builds/the-stock-pot/index.html',
                'demo_version' => '1.2.0',
                'demo_instructions' => 'This is the real app, running in your browser. Browse all 500 recipes, scale servings, and try the shopping list — everything you do stays in this browser only.',
                'demo_warning' => 'This demo is a full Flutter web build (~97 MB, since all 499 available recipe photos are bundled) — first load can take a while on a slower connection.',
                'demo_reset_mode' => 'Favorites, collections, notes, and the shopping list are saved in this browser only. Clearing this site\'s data in your browser resets the demo.',
                'support_info' => 'For questions about The Stock Pot, use the Contact page and select this app.',
                'system_requirements' => 'Android 5.0+ or Windows 10 (64-bit) or later, or any modern web browser.',
                'seo_title' => 'The Stock Pot — 500 Soup, Stew, Chowder & Gravy Recipes',
                'seo_description' => '500 soup, stew, chowder, gravy, and stock/broth recipes with an ingredient scaler, guided cook timers, a shopping list, and offline-first backup. Available for Android, Windows, and in your browser.',
            ]
        );

        $platformCodes = ['android', 'windows', 'web', 'pwa'];
        $platformIds = Platform::whereIn('code', $platformCodes)->pluck('id');
        $app->platforms()->sync($platformIds);

        $this->seedTheStockPotEditions($app);

        $icon = $this->seedMedia('icon.png', 'image/png', 1254, 1254, 'The Stock Pot app icon', 'the-stock-pot');
        $feature = $this->seedMedia('feature.png', 'image/png', 1793, 877, 'The Stock Pot — Real Recipes. Real Food. A Better Table.', 'the-stock-pot');
        $banner = $this->seedMedia('banner.png', 'image/png', 1794, 877, 'The Stock Pot — Soups, Stews, Chowders, Gravies, 500 recipes', 'the-stock-pot');

        $app->media()->sync([
            $icon->id => ['type' => 'icon', 'sort_order' => 0],
            $feature->id => ['type' => 'feature_graphic', 'sort_order' => 0],
            $banner->id => ['type' => 'screenshot', 'sort_order' => 0],
        ]);

        $features = [
            ['title' => '500 recipes, 6 categories', 'description' => '150 Soups, 110 Stews, 60 Chowders, 60 Traditional and 70 No-Drippings Gravies, and 50 Stocks, Broths & Bases.', 'icon' => '🍲', 'sort_order' => 0],
            ['title' => 'Ingredient scaler', 'description' => 'Rebalances whole numbers, fractions, mixed numbers, and ranges to any serving count.', 'icon' => '⚖️', 'sort_order' => 1],
            ['title' => 'Cook timers', 'description' => 'Built into the steps that need them — Stock Pot Pro allows up to three running at once for multi-stage recipes.', 'icon' => '⏱', 'sort_order' => 2],
            ['title' => 'Shopping list', 'description' => 'Build a shopping list automatically from the recipes you\'ve picked.', 'icon' => '🛒', 'sort_order' => 3],
            ['title' => 'Favorites, collections & notes', 'description' => 'Save favorites, organize recipes into collections, and add personal ratings and notes.', 'icon' => '📝', 'sort_order' => 4],
            ['title' => 'Validated backup & restore', 'description' => 'Your data stays on your device, with validated export/import and no account required.', 'icon' => '💾', 'sort_order' => 5],
            ['title' => 'Wide-screen layout', 'description' => 'A master-detail view on tablets and desktop, not just a stretched phone screen.', 'icon' => '🖥', 'sort_order' => 6],
            ['title' => 'Metric & US measurements', 'description' => 'Switch between measurement systems at any time.', 'icon' => '📏', 'sort_order' => 7],
            ['title' => 'Share & print', 'description' => 'Share a recipe as text or print/save it as a PDF, right from the recipe page.', 'icon' => '🖨️', 'sort_order' => 8],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
    }

    /**
     * Owner-directed 2026-09-15: a single "Pro Upgrade" at $2.99 USD,
     * rather than the split Android/Windows/Bundle editions every other
     * app on this site uses. This matches the app's own PROJECT_SPEC.md
     * design (one unified one-time Pro unlock, not a per-platform price),
     * and the EditionEntitlement grants below (android + windows, both
     * 'download') make it resolve to the license system's
     * 'android_windows_bundle' platform_entitlement — a single purchase,
     * one license, good for 2 devices total across both platforms — see
     * LICENSE_SYSTEM.md. The installer itself is free regardless (see
     * FreeDownloadController); buying this edition is purely how a
     * customer gets the license key that unlocks Pro in the installed
     * app via its license-activation screen (Settings → Unlock Pro),
     * which calls POST /api/license/activate.
     */
    private function seedTheStockPotEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $edition = AppEdition::updateOrCreate(
            ['app_id' => $app->id, 'slug' => 'pro-upgrade'],
            [
                'name' => 'Pro Upgrade',
                'description' => 'The Stock Pot is free to download and use. This one-time purchase unlocks Stock Pot Pro (up to 3 concurrent cook timers) on up to 2 of your Android and/or Windows devices via a license key.',
                'price_cents' => 299,
                'currency' => $app->currency ?? 'USD',
                'active' => true,
                'featured' => true,
                'sort_order' => 0,
            ]
        );

        foreach ([$android, $windows] as $platform) {
            if (! $platform) {
                continue;
            }

            EditionEntitlement::updateOrCreate([
                'app_edition_id' => $edition->id,
                'platform_id' => $platform->id,
                'access_type' => 'download',
            ]);
        }
    }

    /**
     * 2026-09-16: when an app is converted from split per-platform editions
     * to a single flat "Pro Upgrade" edition, AppEdition::updateOrCreate()
     * keys on slug and so never removes the old android/windows/bundle
     * rows — they'd otherwise sit there still `active`, and the edition
     * picker would show 4 confusing options instead of 1. Called right
     * after each such app's seedXEditions() with the slug(s) it just
     * created, to deactivate everything else.
     */
    private function deactivateOtherEditions(App $app, array $keepSlugs): void
    {
        $app->editions()->whereNotIn('slug', $keepSlugs)->update(['active' => false]);
    }

    private function seedMedia(string $filename, string $mime, int $width, int $height, string $alt, string $sourceDir = 'bread-maker'): Media
    {
        // Bread Maker's original assets keep their historical flat path
        // (media/seed/{filename}); every app added since is namespaced
        // under media/seed/{sourceDir}/ to avoid filename collisions
        // (e.g. two apps both shipping an "icon.png").
        $sourcePath = $sourceDir === 'bread-maker' ? "media/seed/{$filename}" : "media/seed/{$sourceDir}/{$filename}";
        $destination = storage_path("app/public/{$sourcePath}");

        // storage/app/public/ is git-ignored (runtime uploads), so these
        // seed images are tracked under database/seeders/assets/ instead
        // and copied into place here — this makes `migrate --seed` work
        // correctly on a fresh clone, not just on the machine that
        // originally captured them.
        if (! is_file($destination)) {
            if (! is_dir(dirname($destination))) {
                mkdir(dirname($destination), 0755, true);
            }

            copy(__DIR__."/assets/{$sourceDir}/{$filename}", $destination);
        }

        $size = filesize($destination);

        return Media::updateOrCreate(
            ['path' => $sourcePath],
            [
                'disk' => 'public',
                'mime' => $mime,
                'size' => $size,
                'width' => $width,
                'height' => $height,
                'alt_text' => $alt,
                'title' => $alt,
            ]
        );
    }

    /**
     * Two clearly placeholder/unpublished apps so the admin and catalogue
     * UI can be exercised with more than one row and with filters — no
     * screenshots, feature graphics, or feature claims are invented for
     * them (spec §59/§79 warn against implying unreleased/unavailable
     * apps or features are real). They stay in "draft" status and will
     * never appear on the public site unless someone deliberately
     * publishes them with real content.
     */
    private function seedPlaceholderApps(): void
    {
        $business = AppCategory::where('slug', 'business')->first();
        $utilities = AppCategory::where('slug', 'utilities')->first();

        $placeholders = [
            [
                'slug' => 'seed-sample-business-app',
                'name' => '[SEED] Sample Business App',
                'tagline' => 'Placeholder for local admin/catalogue testing only',
                'short_description' => 'This is seed/demo content used to test the admin and catalogue UI locally. It is not a real, released application and is kept unpublished.',
                'category_id' => $business?->id,
            ],
            [
                'slug' => 'seed-sample-utility-app',
                'name' => '[SEED] Sample Utility App',
                'tagline' => 'Placeholder for local admin/catalogue testing only',
                'short_description' => 'This is seed/demo content used to test the admin and catalogue UI locally. It is not a real, released application and is kept unpublished.',
                'category_id' => $utilities?->id,
            ],
        ];

        foreach ($placeholders as $placeholder) {
            $this->updateOrCreateApp(
                ['slug' => $placeholder['slug']],
                $placeholder + [
                    'status' => 'draft',
                    'is_featured' => false,
                    'is_free' => true,
                ]
            );
        }
    }

    /**
     * A deliberately fake, clearly-labeled paid app used only to exercise
     * the Stripe Checkout flow end-to-end locally. Kept as a draft — never
     * publicly visible on /apps — since /checkout/{slug} requires an app to
     * be published (same rule as any real app), publish it temporarily
     * from Admin → Apps when you want to manually test checkout/tax
     * changes without touching a real product's price, then unpublish it
     * again. Now that real paid apps exist (e.g. Hummus House), this
     * exists purely as a low-stakes fallback for that.
     */
    private function seedTestPurchaseApp(): void
    {
        $this->updateOrCreateApp(
            ['slug' => 'seed-test-purchase-app'],
            [
                'name' => '[SEED] Test Purchase App',
                'tagline' => 'For testing the Stripe checkout flow locally only',
                'short_description' => 'Not a real product. Exists only so the Buy Now / Stripe Checkout / tax calculation flow can be tested end-to-end with Stripe test keys. Kept as a draft — never shown on the public catalogue.',
                'status' => 'draft',
                'is_featured' => false,
                'is_free' => false,
                'price_cents' => 499,
                'currency' => 'CAD',
                'direct_purchase_enabled' => true,
            ]
        );
    }
}
