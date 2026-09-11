<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_tax_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tax_rule_id')->nullable()->constrained()->nullOnDelete();

            // Snapshotted — this row must keep reporting what was
            // actually charged even if the tax_rules row is later edited.
            $table->string('tax_name_snapshot');
            $table->decimal('percentage_snapshot', 5, 3);
            $table->unsignedInteger('amount_cents');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_tax_lines');
    }
};
