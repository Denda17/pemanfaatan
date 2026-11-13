<?php

use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $requirements = [
            // 1. Template Persyaratan Resmi
            [
                'kode' => 'REQ_SURAT_PERMOHONAN',
                'nama_persyaratan' => 'Surat Permohonan Sewa',
                'deskripsi' => 'Surat resmi permohonan sewa ditujukan kepada Gubernur Jabar c.q. Kepala BPKAD Jabar. Format resmi terisi otomatis dapat diunduh, dicetak, dan ditandatangani di atas meterai Rp 10.000.',
                'is_required' => true,
                'urutan' => 1,
            ],
            [
                'kode' => 'REQ_SURAT_PERNYATAAN',
                'nama_persyaratan' => 'Surat Pernyataan',
                'deskripsi' => 'Surat pernyataan kepatuhan, kesanggupan mematuhi ketentuan sewa BMD, dan keabsahan dokumen bermeterai Rp 10.000.',
                'is_required' => true,
                'urutan' => 2,
            ],
            [
                'kode' => 'REQ_SURAT_BADAN_HUKUM',
                'nama_persyaratan' => 'Surat Keterangan Badan Hukum/Usaha',
                'deskripsi' => 'Surat keterangan status badan hukum/usaha dari instansi berwenang/perusahaan. Wajib bagi pemohon Badan Hukum / Usaha.',
                'is_required' => false,
                'urutan' => 3,
            ],

            // 2. Dokumen Pendukung Lainnya
            [
                'kode' => 'REQ_KTP',
                'nama_persyaratan' => 'KTP Pemohon / Penanggung Jawab',
                'deskripsi' => 'Scan / foto e-KTP asli pemohon perorangan atau KTP Direktur / Pimpinan penanggung jawab badan usaha.',
                'is_required' => true,
                'urutan' => 4,
            ],
            [
                'kode' => 'REQ_NPWP',
                'nama_persyaratan' => 'NPWP (Pribadi / Badan Usaha)',
                'deskripsi' => 'Scan Kartu NPWP aktif pemohon pribadi atau NPWP badan usaha / lembaga.',
                'is_required' => true,
                'urutan' => 5,
            ],
            [
                'kode' => 'REQ_NIB',
                'nama_persyaratan' => 'Nomor Induk Berusaha (NIB)',
                'deskripsi' => 'Dokumen NIB berbasis risiko dari OSS. Wajib untuk Badan Hukum / Usaha, opsional untuk perorangan.',
                'is_required' => false,
                'urutan' => 6,
            ],
            [
                'kode' => 'REQ_AKTA_PENDIRIAN',
                'nama_persyaratan' => 'Akta Pendirian & Pengesahan Kemenkumham',
                'deskripsi' => 'Akta pendirian perusahaan/badan hukum beserta Surat Keputusan Pengesahan dari Kemenkumham (khusus Badan Hukum/Usaha).',
                'is_required' => false,
                'urutan' => 7,
            ],
            [
                'kode' => 'REQ_PROPOSAL',
                'nama_persyaratan' => 'Proposal Bisnis & Rencana Pemanfaatan',
                'deskripsi' => 'Dokumen rencana kegiatan usaha, estimasi investasi, dan denah tata letak pemanfaatan. Wajib bagi sewa lebih dari 5 tahun.',
                'is_required' => false,
                'urutan' => 8,
            ],

            // 3. Dokumen Proses Telaah & Administrasi Lanjutan oleh Tim UPTD BPKAD Jabar
            [
                'kode' => 'REQ_SURVEY_LAPANGAN',
                'nama_persyaratan' => 'Berita Acara Survey / Peninjauan Lapangan',
                'deskripsi' => 'Dokumen hasil verifikasi fisik batas tanah/bangunan oleh tim teknis UPTD Pengamanan dan Pemanfaatan Aset.',
                'is_required' => true,
                'urutan' => 9,
            ],
            [
                'kode' => 'REQ_PENILAIAN_TARIF',
                'nama_persyaratan' => 'Laporan Penilaian Nilai Wajar / Tarif Sewa',
                'deskripsi' => 'Hasil penilaian tarif sewa tahunan oleh Penilai Publik / Tim Penilai BMD sesuai formula Permendagri No. 7 Tahun 2024.',
                'is_required' => true,
                'urutan' => 10,
            ],
            [
                'kode' => 'REQ_PERSETUJUAN_SEKDA',
                'nama_persyaratan' => 'Surat Persetujuan Pengelola Barang / Kepgub',
                'deskripsi' => 'Surat persetujuan dari Sekretaris Daerah selaku Pengelola Barang atau Keputusan Gubernur.',
                'is_required' => true,
                'urutan' => 11,
            ],
            [
                'kode' => 'REQ_DRAFT_PKS',
                'nama_persyaratan' => 'Naskah Perjanjian Sewa (PKS)',
                'deskripsi' => 'Perjanjian Kerjasama Sewa yang telah ditandatangani kedua belah pihak di atas meterai.',
                'is_required' => true,
                'urutan' => 12,
            ],
            [
                'kode' => 'REQ_BUKTI_BAYAR',
                'nama_persyaratan' => 'Bukti Setor PAD (STS Kas Daerah)',
                'deskripsi' => 'Surat Tanda Setoran (STS) lunas ke Rekening Kas Umum Daerah (RKUD) Pemprov Jawa Barat.',
                'is_required' => true,
                'urutan' => 13,
            ],
        ];

        foreach ($requirements as $req) {
            BookingRequirement::updateOrCreate(
                ['kode' => $req['kode']],
                $req
            );
        }

        // Migrasikan dokumen yang sebelumnya terhubung ke REQ_KTP_NIB ke REQ_KTP
        $oldReq = BookingRequirement::where('kode', 'REQ_KTP_NIB')->first();
        $newKtpReq = BookingRequirement::where('kode', 'REQ_KTP')->first();

        if ($oldReq && $newKtpReq) {
            BookingDocument::where('booking_requirement_id', $oldReq->id)
                ->update(['booking_requirement_id' => $newKtpReq->id]);
            $oldReq->delete();
        }
    }

    public function down(): void
    {
        // No-op rollback to preserve data integrity
    }
};
