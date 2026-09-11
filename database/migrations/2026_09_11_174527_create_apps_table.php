<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('app_categories')->nullOnDelete();

            $table->string('version')->nullable();
            $table->date('release_date')->nullable();
            $table->date('updated_on')->nullable();

            // Money is stored as integer minor units (cents) — spec §61.
            // Never use floats for financial amounts.
            $table->unsignedInteger('price_cents')->nullable();
            $table->unsignedInteger('sale_price_cents')->nullable();
            $table->char('currency', 3)->default('CAD');
            $table->boolean('is_free')->default(false);

            $table->string('google_play_url')->nullable();
            $table->string('microsoft_store_url')->nullable();
            $table->string('apple_url')->nullable();

            // Direct Stripe purchase is modeled here but stays inert (no
            // working checkout) until Phase 2 wires up Stripe + tax.
            $table->boolean('direct_purchase_enabled')->default(false);
            $table->string('stripe_product_id')->nullable();
            $table->string('stripe_price_id')->nullable();

            $table->string('documentation_url')->nullable();
            $table->string('privacy_policy_url')->nullable();
            $table->text('support_info')->nullable();
            $table->text('system_requirements')->nullable();

            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('featured_order')->default(0);

            $table->string('seo_title')->nullable();
            $table->string('seo_description')->nullable();

            // Demo fields (1:1 with the app — spec §46 Admin → Apps → App → Demo)
            $table->boolean('demo_enabled')->default(false);
            $table->string('demo_type')->nullable(); // e.g. static_web, flutter_web, embed
            $table->string('demo_url')->nullable();
            $table->string('demo_version')->nullable();
            $table->text('demo_instructions')->nullable();
            $table->text('demo_warning')->nullable();
            $table->string('demo_reset_mode')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apps');
    }
};
