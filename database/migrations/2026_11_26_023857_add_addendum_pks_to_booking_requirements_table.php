<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('booking_requirements')->updateOrInsert(
            ['kode' => 'REQ_ADDENDUM_PKS'],
            [
                'nama_persyaratan' => 'Addendum Perjanjian Sewa (Opsional)',
                'deskripsi' => 'Dokumen perubahan/addendum atas Perjanjian Kerjasama (PKS) Induk (opsional, dapat lebih dari satu berkas).',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => false,
                'urutan' => 16,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('booking_requirements')->where('kode', 'REQ_ADDENDUM_PKS')->delete();
    }
};
