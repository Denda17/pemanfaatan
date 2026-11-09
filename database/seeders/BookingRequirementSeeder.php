<?php

namespace Database\Seeders;

use App\Models\BookingRequirement;
use Illuminate\Database\Seeder;

class BookingRequirementSeeder extends Seeder
{
    public function run(): void
    {
        $requirements = [
            [
                'kode' => 'REQ_KTP_NIB',
                'nama_persyaratan' => 'Identitas Pemohon (KTP / NIB & Akta Usaha)',
                'deskripsi' => 'Fotokopi KTP bagi pemohon perorangan, atau NIB, NPWP, dan Akta Pendirian bagi Badan Usaha / Koperasi.',
                'is_required' => true,
                'urutan' => 1,
            ],
            [
                'kode' => 'REQ_SURAT_PERMOHONAN',
                'nama_persyaratan' => 'Surat Permohonan Sewa Resmi',
                'deskripsi' => 'Surat resmi yang ditujukan kepada Pengelola Barang / Kepala BPKAD Pemprov Jabar mengenai maksud dan jangka waktu sewa.',
                'is_required' => true,
                'urutan' => 2,
            ],
            [
                'kode' => 'REQ_PROPOSAL',
                'nama_persyaratan' => 'Proposal Rencana Pemanfaatan',
                'deskripsi' => 'Dokumen rencana kegiatan usaha, estimasi investasi, serta rancangan denah/tata letak pemanfaatan.',
                'is_required' => true,
                'urutan' => 3,
            ],
            [
                'kode' => 'REQ_SURVEY_LAPANGAN',
                'nama_persyaratan' => 'Berita Acara Survey / Peninjauan Lapangan',
                'deskripsi' => 'Dokumen hasil verifikasi fisik batas tanah/bangunan oleh tim teknis UPTD Pengamanan dan Pemanfaatan Aset.',
                'is_required' => true,
                'urutan' => 4,
            ],
            [
                'kode' => 'REQ_PENILAIAN_TARIF',
                'nama_persyaratan' => 'Laporan Penilaian Nilai Wajar / Tarif Sewa',
                'deskripsi' => 'Hasil penilaian tarif sewa tahunan oleh Penilai Publik / Tim Penilai BMD sesuai formula Permendagri No. 7 Tahun 2024.',
                'is_required' => true,
                'urutan' => 5,
            ],
            [
                'kode' => 'REQ_PERSETUJUAN_SEKDA',
                'nama_persyaratan' => 'Surat Persetujuan Pengelola Barang / Kepgub',
                'deskripsi' => 'Surat persetujuan dari Sekretaris Daerah selaku Pengelola Barang atau Keputusan Gubernur.',
                'is_required' => true,
                'urutan' => 6,
            ],
            [
                'kode' => 'REQ_DRAFT_PKS',
                'nama_persyaratan' => 'Naskah Perjanjian Sewa (PKS)',
                'deskripsi' => 'Perjanjian Kerjasama Sewa yang telah ditandatangani kedua belah pihak di atas meterai.',
                'is_required' => true,
                'urutan' => 7,
            ],
            [
                'kode' => 'REQ_BUKTI_BAYAR',
                'nama_persyaratan' => 'Bukti Setor PAD (STS Kas Daerah)',
                'deskripsi' => 'Surat Tanda Setoran (STS) lunas ke Rekening Kas Umum Daerah (RKUD) Pemprov Jawa Barat.',
                'is_required' => true,
                'urutan' => 8,
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
