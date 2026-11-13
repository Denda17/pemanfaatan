<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->integer('termin_ke');
            $table->string('tahun_ke')->nullable();
            $table->decimal('nominal_kewajiban', 15, 2)->nullable();
            $table->decimal('nominal_disetor', 15, 2)->nullable();
            $table->string('nomor_sts')->nullable();
            $table->date('tanggal_bayar')->nullable();
            $table->date('tanggal_jatuh_tempo')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('status_pembayaran')->default('menunggu_pembayaran'); // menunggu_pembayaran, pending_verifikasi, lunas, ditolak
            $table->text('catatan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'termin_ke']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_payments');
    }
};
