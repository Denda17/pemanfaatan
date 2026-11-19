<?php

namespace Database\Seeders;

use App\Models\BookingRequirement;
use Illuminate\Database\Seeder;

class BookingRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $requirements = [
            // 1. Template Persyaratan Resmi
            // 1. Template Persyaratan Resmi (Pemohon)
            [
                'kode' => 'REQ_SURAT_PERMOHONAN',
                'nama_persyaratan' => 'Surat Permohonan Sewa',
                'deskripsi' => 'Surat resmi permohonan sewa ditujukan kepada Gubernur Jabar c.q. Kepala BPKAD Jabar. Format resmi terisi otomatis dapat diunduh, dicetak, dan ditandatangani di atas meterai Rp 10.000.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => true,
                'urutan' => 1,
            ],
            [
                'kode' => 'REQ_SURAT_PERNYATAAN',
                'nama_persyaratan' => 'Surat Pernyataan',
                'deskripsi' => 'Surat pernyataan kepatuhan, kesanggupan mematuhi ketentuan sewa BMD, dan keabsahan dokumen bermeterai Rp 10.000.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => true,
                'urutan' => 2,
            ],
            [
                'kode' => 'REQ_SURAT_BADAN_HUKUM',
                'nama_persyaratan' => 'Surat Keterangan Badan Hukum/Usaha',
                'deskripsi' => 'Surat keterangan status badan hukum/usaha dari instansi berwenang/perusahaan. Wajib bagi pemohon Badan Hukum / Usaha.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => false,
                'urutan' => 3,
            ],

            // 2. Dokumen Pendukung Lainnya
            // 2. Dokumen Pendukung Lainnya (Pemohon)
            [
                'kode' => 'REQ_KTP',
                'nama_persyaratan' => 'KTP Pemohon / Penanggung Jawab',
                'deskripsi' => 'Scan / foto e-KTP asli pemohon perorangan atau KTP Direktur / Pimpinan penanggung jawab badan usaha.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => true,
                'urutan' => 4,
            ],
            [
                'kode' => 'REQ_NPWP',
                'nama_persyaratan' => 'NPWP (Pribadi / Badan Usaha)',
                'deskripsi' => 'Scan Kartu NPWP aktif pemohon pribadi atau NPWP badan usaha / lembaga.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => true,
                'urutan' => 5,
            ],
            [
                'kode' => 'REQ_NIB',
                'nama_persyaratan' => 'Nomor Induk Berusaha (NIB)',
                'deskripsi' => 'Dokumen NIB berbasis risiko dari OSS. Wajib untuk Badan Hukum / Usaha, opsional untuk perorangan.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => false,
                'urutan' => 6,
            ],
            [
                'kode' => 'REQ_AKTA_PENDIRIAN',
                'nama_persyaratan' => 'Akta Pendirian & Pengesahan Kemenkumham',
                'deskripsi' => 'Akta pendirian perusahaan/badan hukum beserta Surat Keputusan Pengesahan dari Kemenkumham (khusus Badan Hukum/Usaha).',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => false,
                'urutan' => 7,
            ],
            [
                'kode' => 'REQ_PROPOSAL',
                'nama_persyaratan' => 'Proposal Bisnis & Rencana Pemanfaatan',
                'deskripsi' => 'Dokumen rencana kegiatan usaha, estimasi investasi, dan denah tata letak pemanfaatan. Wajib bagi sewa lebih dari 5 tahun.',
                'kategori' => 'pemohon',
                'diunggah_oleh' => 'pemohon',
                'is_required' => false,
                'urutan' => 8,
            ],

            // 3. Dokumen Proses Telaah & Administrasi Lanjutan oleh Tim UPTD BPKAD Jabar
            // 3. Dokumen Proses Administrasi & Penetapan Sewa (Diinput Admin UPTD & Bukti Bayar oleh Mitra)
            [
                'kode' => 'REQ_SURVEY_LAPANGAN',
                'nama_persyaratan' => 'Berita Acara Survey / Peninjauan Lapangan',
                'deskripsi' => 'Dokumen hasil verifikasi fisik batas tanah/bangunan oleh tim teknis UPTD Pengamanan dan Pemanfaatan Aset.',
                'kode' => 'REQ_LAPORAN_PENILAIAN',
                'nama_persyaratan' => 'Laporan Penilaian (notdin dan laporan)',
                'deskripsi' => 'Nota dinas dan laporan hasil penilaian nilai wajar tarif sewa BMD oleh Penilai Publik / Tim Penilai BMD Jawa Barat.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 9,
            ],
            [
                'kode' => 'REQ_PENILAIAN_TARIF',
                'nama_persyaratan' => 'Laporan Penilaian Nilai Wajar / Tarif Sewa',
                'deskripsi' => 'Hasil penilaian tarif sewa tahunan oleh Penilai Publik / Tim Penilai BMD sesuai formula Permendagri No. 7 Tahun 2024.',
                'kode' => 'REQ_BA_NEGOSIASI',
                'nama_persyaratan' => 'Berita Acara Negosiasi',
                'deskripsi' => 'Berita acara hasil klarifikasi dan negosiasi besaran sewa antara tim UPTD BPKAD Jabar dan calon mitra penyewa.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 10,
            ],
            [
                'kode' => 'REQ_PERSETUJUAN_SEKDA',
                'nama_persyaratan' => 'Surat Persetujuan Pengelola Barang / Kepgub',
                'deskripsi' => 'Surat persetujuan dari Sekretaris Daerah selaku Pengelola Barang atau Keputusan Gubernur.',
                'kode' => 'REQ_FASILITASI_KEPGUB',
                'nama_persyaratan' => 'Surat Permohonan Fasilitasi Kepgub',
                'deskripsi' => 'Surat resmi permohonan fasilitasi rancangan Keputusan Gubernur kepada Biro Hukum & HAM Setda Provinsi Jawa Barat.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 11,
            ],
            [
                'kode' => 'REQ_DRAFT_PKS',
                'nama_persyaratan' => 'Naskah Perjanjian Sewa (PKS)',
                'deskripsi' => 'Perjanjian Kerjasama Sewa yang telah ditandatangani kedua belah pihak di atas meterai.',
                'kode' => 'REQ_KEPUTUSAN_GUBERNUR',
                'nama_persyaratan' => 'Keputusan Gubernur',
                'deskripsi' => 'Surat Keputusan Gubernur Jawa Barat (Kepgub) tentang penetapan persetujuan sewa Barang Milik Daerah.',
                'kategori' => 'administrasi',
                'diunggah_oleh' => 'admin',
                'is_required' => true,
                'urutan' => 12,
            ],
            [
                'kode' => 'REQ_BUKTI_BAYAR',
                'nama_persyaratan' => 'Bukti Setor PAD (STS Kas Daerah)',
                'deskripsi' => 'Surat Tanda Setoran (STS) lunas ke Rekening Kas Umum Daerah (RKUD) Pemprov Jawa Barat.',
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

        foreach ($requirements as $req) {
            BookingRequirement::updateOrCreate(
                ['kode' => $req['kode']],
                $req
            );
        }
    }
}
