<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_pemanfaatan', function (Blueprint $table) {
            $table->string('kontak_mitra')->nullable()->after('jenis_mitra');
        });
    }

    public function down(): void
    {
        Schema::table('asset_pemanfaatan', function (Blueprint $table) {
            $table->dropColumn('kontak_mitra');
        });
    }
};
