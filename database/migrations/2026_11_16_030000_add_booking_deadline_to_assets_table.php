<?php

declare(strict_types=1);

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
            $table->timestamp('booking_opened_at')->nullable()->after('status_ketersediaan');
            $table->timestamp('booking_deadline_at')->nullable()->index()->after('booking_opened_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex(['booking_deadline_at']);
            $table->dropColumn(['booking_opened_at', 'booking_deadline_at']);
        });
    }
};
