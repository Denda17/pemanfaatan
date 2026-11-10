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
        Schema::create('asset_pemanfaatan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_pemanfaatan_id')
                ->constrained('asset_pemanfaatan')
                ->cascadeOnDelete();
            $table->unsignedInteger('termin_ke')->default(1);
            $table->string('tahun_ke', 20)->nullable(); // Misal: "Tahun 1 (2024)", "2025"
            $table->decimal('nominal_kewajiban', 15, 2)->default(0);
            $table->decimal('nominal_disetor', 15, 2)->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->date('tanggal_bayar')->nullable();
            $table->string('nomor_sts', 100)->nullable(); // Nomor Surat Tanda Setor / NTPN / Referensi BJB Kasda
            $table->string('status_pembayaran', 30)->default('menunggu_pembayaran')->index(); // lunas, menunggu_pembayaran, jatuh_tempo, batal
            $table->string('bukti_bayar_path')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_pemanfaatan_payments');
    }
};
