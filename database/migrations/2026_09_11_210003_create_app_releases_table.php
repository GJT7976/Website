<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('app_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('app_id')->constrained()->cascadeOnDelete();
            $table->foreignId('platform_id')->constrained()->cascadeOnDelete();

            $table->string('version');
            $table->string('build_number')->nullable();
            $table->text('release_notes')->nullable();
            $table->string('min_os')->nullable();
            $table->date('released_at');
            $table->boolean('is_current')->default(false);

            // Stored on the private "local" disk (storage/app/private),
            // never web-served directly — see App\Services\ReleaseLibrary.
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_filename');
            $table->unsignedBigInteger('file_size');
            $table->string('checksum_sha256', 64)->nullable();
            $table->string('mime_type')->nullable();

            // Never true for a .aab or any other internal-only artifact —
            // enforced in App\Services\ReleaseLibrary, not just here.
            $table->boolean('customer_downloadable')->default(true);

            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_releases');
    }
};
