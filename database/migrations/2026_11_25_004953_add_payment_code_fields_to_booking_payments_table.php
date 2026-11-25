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
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->string('kode_pembayaran', 100)->nullable()->after('nomor_sts');
            $table->string('kode_bayar_file_path')->nullable()->after('kode_pembayaran');
            $table->string('kode_bayar_file_name')->nullable()->after('kode_bayar_file_path');
            $table->timestamp('kode_diminta_at')->nullable()->after('kode_bayar_file_name');
            $table->timestamp('kode_diterbitkan_at')->nullable()->after('kode_diminta_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('booking_payments', function (Blueprint $table) {
            $table->dropColumn([
                'kode_pembayaran',
                'kode_bayar_file_path',
                'kode_bayar_file_name',
                'kode_diminta_at',
                'kode_diterbitkan_at',
            ]);
        });
    }
};
