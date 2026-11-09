<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique(); // e.g. BK-202611-0001
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_pemohon');
            $table->string('nik_atau_nib')->nullable();
            $table->string('nama_instansi')->nullable();
            $table->string('nomor_whatsapp')->index();
            $table->string('email')->nullable();
            $table->text('rencana_peruntukan')->nullable();
            $table->integer('durasi_sewa_tahun')->default(1);
            $table->string('status')->default('pending')->index(); 
            // pending, contacted, document_review, field_survey, approved, contract_signed, rejected
            $table->text('catatan_admin')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('tanggal_booking')->useCurrent();
            $table->timestamp('tanggal_dihubungi')->nullable();
            $table->foreignId('petugas_uptd_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
