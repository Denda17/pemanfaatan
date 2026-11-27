<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->string('kategori_regulasi', 40)
                ->default('pengelolaan_bmd')
                ->after('slug')
                ->index();
        });

        DB::table('regulations')
            ->where('tingkat', 'sop')
            ->orWhere('jenis', 'like', '%SOP%')
            ->update(['kategori_regulasi' => 'sop']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regulations', function (Blueprint $table) {
            $table->dropIndex(['kategori_regulasi']);
            $table->dropColumn('kategori_regulasi');
        });
    }
};
