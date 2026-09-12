<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * On-demand backup archives (spec §30). No "status" column — a row is
     * only ever inserted after BackupService confirms the archive was
     * written successfully; a failed attempt writes nothing.
     */
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['database', 'media', 'full']);
            $table->string('disk')->default('local');
            $table->string('path'); // path within the disk, never a public URL
            $table->string('filename');
            $table->unsignedBigInteger('file_size'); // bytes
            $table->string('checksum_sha256', 64);
            $table->string('label')->nullable();

            // Null = created by the `backup:run` artisan command (cron/CLI),
            // not a missing record.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
