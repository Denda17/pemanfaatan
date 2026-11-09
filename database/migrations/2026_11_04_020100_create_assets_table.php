<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('kode_aset')->nullable()->index();
            $table->string('nama_objek');
            $table->string('slug')->unique();
            $table->string('bentuk_objek')->default('Tanah'); // Tanah, Tanah dan Bangunan, Bangunan, Ruko, dll
            $table->decimal('luas_tanah', 12, 2)->nullable();
            $table->decimal('luas_bangunan', 12, 2)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kabupaten_kota')->nullable()->index();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('status_ketersediaan')->default('tersedia')->index(); // tersedia, proses_booking, tersewa, pemeliharaan
            $table->decimal('estimasi_tarif_tahunan', 15, 2)->nullable();
            $table->string('peruntukan')->nullable(); // Komersial, Tower, UMKM, Parkir, Kantor, Pertanian, dll
            $table->text('deskripsi')->nullable();
            $table->jsonb('fasilitas')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
