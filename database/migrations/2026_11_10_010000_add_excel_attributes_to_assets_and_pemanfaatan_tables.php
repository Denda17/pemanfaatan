<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->string('status_pengelola')->nullable()->default('PENGELOLA')->after('bentuk_objek');
            $table->integer('nomor_urut_excel')->nullable()->index()->after('kode_aset');
        });

        Schema::table('asset_pemanfaatan', function (Blueprint $table) {
            $table->string('status_pengelola')->nullable()->default('PENGELOLA')->after('jenis_pemanfaatan');
            $table->string('jangka_waktu_teks')->nullable()->after('jangka_waktu_tahun');
            $table->string('kategori_status')->nullable()->index()->after('status_perpanjangan');
            $table->integer('nomor_urut_excel')->nullable()->index()->after('asset_id');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['status_pengelola', 'nomor_urut_excel']);
        });

        Schema::table('asset_pemanfaatan', function (Blueprint $table) {
            $table->dropColumn(['status_pengelola', 'jangka_waktu_teks', 'kategori_status', 'nomor_urut_excel']);
        });
    }
};
