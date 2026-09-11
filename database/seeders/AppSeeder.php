<?php

namespace Database\Seeders;

use App\Models\App;
use App\Models\AppCategory;
use App\Models\Media;
use App\Models\Platform;
use Illuminate\Database\Seeder;

class AppSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedBreadMaker();
        $this->seedPlaceholderApps();
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
     */
    private function seedBreadMaker(): void
    {
        $category = AppCategory::where('slug', 'food-recipes')->first();

        $app = App::updateOrCreate(
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
                'version' => '1.0',
                'is_free' => true,
                'price_cents' => null,
                'currency' => 'CAD',
                'status' => 'published',
                'is_featured' => false,
                'demo_enabled' => true,
                'demo_type' => 'static_web',
                // Deliberately NOT public/demos/{slug}/ — that exact path
                // collides with the /demos catalogue route at the
                // web-server level (PHP's built-in server, and Apache/
                // Nginx in production, resolve a request against a real
                // directory on disk before Laravel's router ever runs).
                // See DEMO_DEPLOYMENT.md.
                'demo_url' => '/demo-builds/bread-maker/index.html',
                'demo_version' => '1.0',
                'demo_instructions' => 'Bread Maker runs entirely in your browser. Try a Quick Start preset, or dial in your own hydration, flour blend, and pan size — nothing you enter here is saved outside this device.',
                'demo_warning' => null,
                'demo_reset_mode' => 'Reload the page — the calculator has no server-side state to reset.',
                'support_info' => 'For questions about Bread Maker, use the Contact page and select this app.',
                'system_requirements' => 'Any modern web browser. Installable as a Progressive Web App (PWA) for offline use.',
                'seo_title' => "Bread Maker — Baker's Percentage Calculator",
                'seo_description' => 'Free baker\'s percentage calculator for artisan bread: hydration, preferments, pan scaling, and dough temperature — runs offline as a PWA.',
            ]
        );

        $webPlatform = Platform::where('code', 'web')->first();
        $pwaPlatform = Platform::where('code', 'pwa')->first();
        $app->platforms()->sync(array_filter([$webPlatform?->id, $pwaPlatform?->id]));

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

    private function seedMedia(string $filename, string $mime, int $width, int $height, string $alt): Media
    {
        $sourcePath = "media/seed/{$filename}";
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

            copy(__DIR__."/assets/bread-maker/{$filename}", $destination);
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
            App::updateOrCreate(
                ['slug' => $placeholder['slug']],
                $placeholder + [
                    'status' => 'draft',
                    'is_featured' => false,
                    'is_free' => true,
                ]
            );
        }
    }
}
