<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama_persyaratan');
            $table->text('deskripsi')->nullable();
            $table->boolean('is_required')->default(true);
            $table->integer('urutan')->default(1);
            $table->timestamps();
        });

        Schema::create('booking_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('booking_requirement_id')->constrained('booking_requirements')->onDelete('cascade');
            $table->string('nomor_dokumen')->nullable();
            $table->date('tanggal_dokumen')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('file_type')->nullable();
            $table->string('status_verifikasi')->default('verified'); // pending, verified, revision, rejected
            $table->text('catatan')->nullable();
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_documents');
        Schema::dropIfExists('booking_requirements');
    }
};
