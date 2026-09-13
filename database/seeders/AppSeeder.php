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
                'version' => '1.1.2',
                'is_free' => false,
                // Legacy flat-price fallback (see App::hasEditions()) — the
                // real price comes from the editions below, same pattern as
                // Hummus House.
                'price_cents' => 299,
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
                'demo_version' => '1.1.2',
                'demo_instructions' => 'Bread Maker runs entirely in your browser. Try a Quick Start preset, or dial in your own hydration, flour blend, and pan size — nothing you enter here is saved outside this device. Buy the Android or Windows app below for an installable version.',
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
     * Real, adjustable edition pricing — same structure and price points as
     * Hummus House's and La Cucina Italiana's editions. The owner can
     * retune all three from Apps → Bread Maker → Editions.
     */
    private function seedBreadMakerEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'android', 'name' => 'Android', 'description' => 'For Android phones and tablets.', 'price_cents' => 299, 'sort_order' => 0, 'grants' => [[$android, 'download']]],
            ['slug' => 'windows', 'name' => 'Windows', 'description' => 'For compatible Windows PCs and tablets.', 'price_cents' => 299, 'sort_order' => 1, 'grants' => [[$windows, 'download']]],
            ['slug' => 'android-windows', 'name' => 'Android + Windows Bundle', 'description' => "One purchase. Install the app on your compatible Android and Windows devices, subject to the app's license terms.", 'price_cents' => 499, 'sort_order' => 2, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
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
                'is_free' => false,
                'price_cents' => 299,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                // Platform Selection & Purchase System (§22/§25/§26): sold
                // as separate Android/Windows/Bundle editions below, rather
                // than the single flat price_cents above (kept only as a
                // legacy fallback — see App::hasEditions()). No Web/Complete
                // edition yet: the only web build today is the Live Demo,
                // not a separately hosted paid web app.
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
     * Real, adjustable edition pricing for the first app configured with
     * the Platform Selection & Purchase System (not placeholders) — see
     * "Claude Prompt — Niagara Inde Apps Platform Selection & Purchase
     * System.md". Android/Windows match the app's existing $2.99 price;
     * the Bundle is a modest discount versus buying both separately. The
     * owner can retune all three from Apps → Hummus House → Editions.
     */
    private function seedHummusHouseEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'android', 'name' => 'Android', 'description' => 'For Android phones and tablets.', 'price_cents' => 299, 'sort_order' => 0, 'grants' => [[$android, 'download']]],
            ['slug' => 'windows', 'name' => 'Windows', 'description' => 'For compatible Windows PCs and tablets.', 'price_cents' => 299, 'sort_order' => 1, 'grants' => [[$windows, 'download']]],
            ['slug' => 'android-windows', 'name' => 'Android + Windows Bundle', 'description' => "One purchase. Install the app on your compatible Android and Windows devices, subject to the app's license terms.", 'price_cents' => 499, 'sort_order' => 2, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
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
                'is_free' => false,
                'price_cents' => null,
                'currency' => 'USD',
                'status' => 'published',
                'is_featured' => false,
                'direct_purchase_enabled' => true,
                // Sold as separate Android/Windows/Bundle editions, same
                // model as Hummus House — see seedLaCucinaItalianaEditions().
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
     * Real, adjustable edition pricing — same structure and price points as
     * Hummus House's editions (seedHummusHouseEditions). The owner can
     * retune all three from Apps → La Cucina Italiana → Editions.
     */
    private function seedLaCucinaItalianaEditions(App $app): void
    {
        $android = Platform::where('code', 'android')->first();
        $windows = Platform::where('code', 'windows')->first();

        $editions = [
            ['slug' => 'android', 'name' => 'Android', 'description' => 'For Android phones and tablets.', 'price_cents' => 299, 'sort_order' => 0, 'grants' => [[$android, 'download']]],
            ['slug' => 'windows', 'name' => 'Windows', 'description' => 'For compatible Windows PCs and tablets.', 'price_cents' => 299, 'sort_order' => 1, 'grants' => [[$windows, 'download']]],
            ['slug' => 'android-windows', 'name' => 'Android + Windows Bundle', 'description' => "One purchase. Install the app on your compatible Android and Windows devices, subject to the app's license terms.", 'price_cents' => 499, 'sort_order' => 2, 'featured' => true, 'grants' => [[$android, 'download'], [$windows, 'download']]],
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
     * aspect). Published with a working demo so it's visible on the
     * site, but deliberately **not yet purchasable**:
     * `direct_purchase_enabled` stays false and no price/edition is set
     * because `PROJECT_SPEC.md`'s `PRICE_OR_PRODUCT_IDS` is still an
     * owner-provided placeholder in the app's own repository — never
     * invent a price here. Flip `direct_purchase_enabled` to true and add
     * pricing/editions (see seedHummusHouseEditions for the pattern) once
     * the owner supplies real numbers.
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
ephemeris engine — Sun through Pluto, Whole Sign or Placidus houses, your
Ascendant and Midheaven — never a fabricated placement. Sky Now and Moon
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
MD,
                'category_id' => $category?->id,
                'version' => '1.0.0',
                'is_free' => false,
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
                'demo_version' => '1.0.0',
                'demo_instructions' => 'This is the real app, running in your browser. Complete onboarding with any birth date/time/place to see your own natal chart and today\'s Daily Card — nothing you enter here leaves this browser.',
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
            ['title' => 'Real natal chart, Pluto included', 'description' => 'Sun through Pluto, Whole Sign or Placidus houses, Ascendant and Midheaven — independently verified against reference astronomical data, never fabricated.', 'icon' => '🪐', 'sort_order' => 1],
            ['title' => 'Sky Now & Moon Center', 'description' => 'Live current planetary positions, Moon phase, illumination, and the next New and Full Moon.', 'icon' => '🌙', 'sort_order' => 2],
            ['title' => 'Year of Stars', 'description' => 'Your full collection of 365 (366 in a leap year) Daily Cards, revealed one day at a time.', 'icon' => '📅', 'sort_order' => 3],
            ['title' => 'Real synastry & compatibility', 'description' => 'Inter-chart aspects scored across seven areas, always shown with the specific aspects behind the score — never an unexplained percentage.', 'icon' => '💞', 'sort_order' => 4],
            ['title' => 'Searchable Grimoire', 'description' => 'All 12 signs, all 10 planets, all 12 houses, and all 5 aspects, with substantial reference content and one search box.', 'icon' => '📖', 'sort_order' => 5],
            ['title' => 'Private Celestial Journal', 'description' => 'Reflections tied to dates, cards, transits, and Moon phases — stored on your device by default, no account required.', 'icon' => '📓', 'sort_order' => 6],
        ];

        foreach ($features as $feature) {
            $app->features()->updateOrCreate(['title' => $feature['title']], $feature);
        }
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
