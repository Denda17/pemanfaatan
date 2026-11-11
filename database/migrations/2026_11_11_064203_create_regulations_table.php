<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('regulations', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('nomor');
            $table->integer('tahun');
            $table->string('jenis');
            $table->string('tingkat')->default('daerah')->index();
            $table->string('penerbit');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->json('poin_kunci')->nullable();
            $table->string('file_path');
            $table->string('disk')->default('public');
            $table->string('download_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('badge_color')->default('emerald');
            $table->integer('urutan')->default(0)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regulations');
    }
};
