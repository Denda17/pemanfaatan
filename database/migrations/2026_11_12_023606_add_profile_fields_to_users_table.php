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
        Schema::table('users', function (Blueprint $table) {
            $table->string('tipe_pemohon')->default('perorangan')->after('role'); // perorangan, badan_usaha
            $table->string('nik', 20)->nullable()->after('tipe_pemohon');
            $table->string('nib', 30)->nullable()->after('nik');
            $table->string('npwp', 30)->nullable()->after('nib');
            $table->text('alamat')->nullable()->after('instansi');
            $table->boolean('is_active')->default(true)->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['tipe_pemohon', 'nik', 'nib', 'npwp', 'alamat', 'is_active']);
        });
    }
};
