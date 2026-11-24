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
            $table->dateTime('beauty_contest_scheduled_at')->nullable()->after('alasan_penolakan');
            $table->string('beauty_contest_location', 255)->nullable()->after('beauty_contest_scheduled_at');
            $table->text('beauty_contest_notes')->nullable()->after('beauty_contest_location');
            $table->string('beauty_contest_status', 30)->default('none')->after('beauty_contest_notes');
            $table->boolean('is_beauty_contest_winner')->default(false)->after('beauty_contest_status');
            $table->string('beauty_contest_berita_acara_path', 255)->nullable()->after('is_beauty_contest_winner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'beauty_contest_scheduled_at',
                'beauty_contest_location',
                'beauty_contest_notes',
                'beauty_contest_status',
                'is_beauty_contest_winner',
                'beauty_contest_berita_acara_path',
            ]);
        });
    }
};
