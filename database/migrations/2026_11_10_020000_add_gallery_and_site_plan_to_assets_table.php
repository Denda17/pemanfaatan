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
        Schema::table('assets', function (Blueprint $table) {
            $table->string('foto_utama')->nullable()->after('deskripsi');
            $table->json('foto_galeri')->nullable()->after('foto_utama');
            $table->string('site_plan_url')->nullable()->after('foto_galeri');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['foto_utama', 'foto_galeri', 'site_plan_url']);
        });
    }
};
