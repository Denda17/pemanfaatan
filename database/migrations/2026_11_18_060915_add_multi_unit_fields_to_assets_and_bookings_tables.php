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
            $table->string('tipe_aset', 50)->default('tunggal')->after('bentuk_objek')->comment('tunggal atau kompleks_multi_unit');
            $table->string('site_plan_type', 50)->nullable()->after('site_plan_url')->comment('image_map, interactive_svg, grid_only');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('asset_unit_id')->nullable()->after('asset_id')->constrained('asset_units')->nullOnDelete();
            $table->json('selected_unit_ids')->nullable()->after('asset_unit_id')->comment('Array ID asset_units jika memilih sewa multi-unit / gandeng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['asset_unit_id']);
            $table->dropColumn(['asset_unit_id', 'selected_unit_ids']);
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn(['tipe_aset', 'site_plan_type']);
        });
    }
};
