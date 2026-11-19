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
        Schema::create('asset_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->string('nomor_unit')->comment('Nomor/Kode unit ruko, misal A11-56, B3-72, C14');
            $table->string('blok', 50)->nullable()->comment('Blok ruko, misal Blok A, B, C, D, E');
            $table->string('kategori_zona')->comment('Klasifikasi zona: hook_luar, super_premium, premium, hook_dalam, basic, khusus, amat_khusus');
            $table->string('kategori_label')->nullable()->comment('Label tampilan: Hook Luar, Super Premium, dll.');
            $table->decimal('luas_tanah', 10, 2)->nullable();
            $table->decimal('luas_bangunan', 10, 2)->nullable();
            $table->decimal('tarif_tahunan', 15, 2)->default(0);
            $table->enum('status_ketersediaan', ['tersedia', 'proses_booking', 'tersewa'])->default('tersedia');
            $table->dateTime('booking_opened_at')->nullable();
            $table->dateTime('booking_deadline_at')->nullable();
            $table->string('foto_utama')->nullable();
            $table->json('foto_galeri')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('svg_key')->nullable()->comment('ID element svg/hotspot pada site plan visual');
            $table->timestamps();

            $table->index(['asset_id', 'status_ketersediaan']);
            $table->index(['asset_id', 'kategori_zona']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_units');
    }
};
