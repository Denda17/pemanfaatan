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
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('skema_pembayaran', 30)->default('sekaligus')->after('durasi_sewa_tahun')->index();
            $table->string('proposal_bisnis_path')->nullable()->after('skema_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['skema_pembayaran']);
            $table->dropColumn(['skema_pembayaran', 'proposal_bisnis_path']);
        });
    }
};
