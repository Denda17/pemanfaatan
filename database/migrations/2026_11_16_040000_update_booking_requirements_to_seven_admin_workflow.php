<?php

use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom kategori dan diunggah_oleh pada tabel booking_requirements jika belum ada
        Schema::table('booking_requirements', function (Blueprint $table) {
            if (! Schema::hasColumn('booking_requirements', 'kategori')) {
                $table->string('kategori')->default('administrasi')->nullable()->after('deskripsi');
            }
            if (! Schema::hasColumn('booking_requirements', 'diunggah_oleh')) {
                $table->string('diunggah_oleh')->default('admin')->after('kategori');
            }
        });

        // 2. Update kelengkapan berkas awal pemohon
        $applicantReqs = [
            'REQ_SURAT_PERMOHONAN' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 1],
            'REQ_SURAT_PERNYATAAN' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 2],
            'REQ_SURAT_BADAN_HUKUM' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 3],
            'REQ_KTP' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 4],
            'REQ_NPWP' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 5],
            'REQ_NIB' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 6],
            'REQ_AKTA_PENDIRIAN' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 7],
            'REQ_PROPOSAL' => ['kategori' => 'pemohon', 'diunggah_oleh' => 'pemohon', 'urutan' => 8],
        ];

        foreach ($applicantReqs as $kode => $attrs) {
            BookingRequirement::where('kode', $kode)->update($attrs);
        }

        // 3. Daftarkan 7 berkas proses administrasi & sewa sesuai instruksi:
        // 1. Laporan Penilaian (notdin dan laporan) [Admin]
        // 2. Berita Acara Negosiasi [Admin]
        // 3. Surat Permohonan Fasilitasi Kepgub [Admin]
        // 4. Keputusan Gubernur [Admin]
        // 5. Surat Pemberitahuan Pembayaran [Admin]
        // 6. Bukti Bayar (oleh mitra) [Khusus Mitra Penyewa]
        // 7. Perjanjian Sewa [Admin]
        $sevenWorkflowReqs = [
            [
                'kode' => 'REQ_LAPORAN_PENILAIAN',
                'nama_persyaratan' => 'Laporan Penilaian (notdin dan laporan)',
                'deskripsi' => 'Nota dinas dan laporan hasil penilaian nilai wajar tarif sewa BMD oleh Penilai Publik / Tim Penilai BMD Jawa Barat.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 9,
            ],
            [
                'kode' => 'REQ_BA_NEGOSIASI',
                'nama_persyaratan' => 'Berita Acara Negosiasi',
                'deskripsi' => 'Berita acara hasil klarifikasi dan negosiasi besaran sewa antara tim UPTD BPKAD Jabar dan calon mitra penyewa.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 10,
            ],
            [
                'kode' => 'REQ_FASILITASI_KEPGUB',
                'nama_persyaratan' => 'Surat Permohonan Fasilitasi Kepgub',
                'deskripsi' => 'Surat resmi permohonan fasilitasi rancangan Keputusan Gubernur kepada Biro Hukum & HAM Setda Provinsi Jawa Barat.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 11,
            ],
            [
                'kode' => 'REQ_KEPUTUSAN_GUBERNUR',
                'nama_persyaratan' => 'Keputusan Gubernur',
                'deskripsi' => 'Surat Keputusan Gubernur Jawa Barat (Kepgub) tentang penetapan persetujuan sewa Barang Milik Daerah.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 12,
            ],
            [
                'kode' => 'REQ_PEMBERITAHUAN_PEMBAYARAN',
                'nama_persyaratan' => 'Surat Pemberitahuan Pembayaran',
                'deskripsi' => 'Surat resmi pemberitahuan tata cara pembayaran, nomor rekening Kas Daerah, dan batas waktu penyetoran sewa.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 13,
            ],
            [
                'kode' => 'REQ_BUKTI_BAYAR',
                'nama_persyaratan' => 'Bukti Bayar (oleh mitra)',
                'deskripsi' => 'Bukti setor uang sewa (Surat Tanda Setoran / STS Kas Daerah) yang diunggah oleh mitra penyewa.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'mitra',
                'is_required' => true,
                'urutan' => 14,
            ],
            [
                'kode' => 'REQ_PERJANJIAN_SEWA',
                'nama_persyaratan' => 'Perjanjian Sewa',
                'deskripsi' => 'Naskah Perjanjian Kerjasama (PKS) Sewa definitif yang telah ditandatangani kedua belah pihak di atas meterai sah.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 15,
            ],
        ];

        foreach ($sevenWorkflowReqs as $reqData) {
            BookingRequirement::updateOrCreate(
                ['kode' => $reqData['kode']],
                $reqData
            );
        }

        // 4. Migrasikan dokumen yang ada dari kode lama ke kode baru jika ada
        $migrationMap = [
            'REQ_PENILAIAN_TARIF' => 'REQ_LAPORAN_PENILAIAN',
            'REQ_SURVEY_LAPANGAN' => 'REQ_LAPORAN_PENILAIAN',
            'REQ_PERSETUJUAN_SEKDA' => 'REQ_KEPUTUSAN_GUBERNUR',
            'REQ_DRAFT_PKS' => 'REQ_PERJANJIAN_SEWA',
        ];

        foreach ($migrationMap as $oldCode => $newCode) {
            $oldModel = BookingRequirement::where('kode', $oldCode)->first();
            $newModel = BookingRequirement::where('kode', $newCode)->first();

            if ($oldModel && $newModel) {
                BookingDocument::where('booking_requirement_id', $oldModel->id)
                    ->update(['booking_requirement_id' => $newModel->id]);
                $oldModel->delete();
            }
        }
    }

    public function down(): void
    {
        Schema::table('booking_requirements', function (Blueprint $table) {
            if (Schema::hasColumn('booking_requirements', 'diunggah_oleh')) {
                $table->dropColumn('diunggah_oleh');
            }
            if (Schema::hasColumn('booking_requirements', 'kategori')) {
                $table->dropColumn('kategori');
            }
        });
    }
};
