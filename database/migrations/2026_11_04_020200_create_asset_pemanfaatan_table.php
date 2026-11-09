<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_pemanfaatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('jenis_pemanfaatan')->default('Sewa'); // Sewa, KSP, BGS, BSG, Pinjam Pakai
            $table->string('nama_mitra');
            $table->string('jenis_mitra')->nullable(); // Perorangan, Perusahaan / PT, Koperasi, BUMD, UMKM
            $table->decimal('besaran_sewa_tahunan', 15, 2)->nullable();
            $table->integer('jangka_waktu_tahun')->nullable();
            $table->decimal('total_sewa', 15, 2)->nullable();
            $table->string('mekanisme_pembayaran')->nullable(); // Sekaligus, Bertahap
            $table->string('nomor_persetujuan')->nullable();
            $table->string('nomor_kepgub')->nullable();
            $table->string('nomor_perjanjian')->nullable();
            $table->date('tanggal_awal')->nullable();
            $table->date('tanggal_akhir')->nullable();
            $table->string('status_perpanjangan')->default('aktif')->index(); // aktif, peringatan_jatuh_tempo, selesai, diperpanjang
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_pemanfaatan');
    }
};
