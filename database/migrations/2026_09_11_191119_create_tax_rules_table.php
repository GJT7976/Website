<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rules', function (Blueprint $table) {
            $table->id();
            $table->char('country', 2); // ISO 3166-1 alpha-2, e.g. CA
            $table->string('province')->nullable(); // null = whole country
            $table->string('tax_name'); // GST, HST, PST, QST, ...
            $table->decimal('percentage', 5, 3); // e.g. 13.000
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->boolean('active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['country', 'province', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rules');
    }
};
