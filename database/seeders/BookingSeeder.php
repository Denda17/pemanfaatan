<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $requirements = BookingRequirement::all();

        // Buat akun user peminat contoh
        $peminat1 = User::updateOrCreate(
            ['email' => 'andi.wijaya@gmail.com'],
            [
                'name' => 'Andi Wijaya',
                'role' => 'peminat',
                'phone' => '081223344556',
                'instansi' => 'PT Parahyangan Citra Rasa (Kafe & Resto)',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        $peminat2 = User::updateOrCreate(
            ['email' => 'siti.nurhaliza@koperasijabar.co.id'],
            [
                'name' => 'Siti Nurhaliza',
                'role' => 'peminat',
                'phone' => '085799887766',
                'instansi' => 'Koperasi Sentra UMKM Mandiri',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]
        );

        // Ambil beberapa aset
        $assetTersedia = Asset::where('status_ketersediaan', 'tersedia')->first();
        $assetTersewa = Asset::where('status_ketersediaan', 'tersewa')->first();

        if ($assetTersedia) {
            // Booking 1: Status "document_review" (sedang tahap penginputan berkas oleh Admin UPTD)
            $booking1 = Booking::updateOrCreate(
                ['kode_booking' => 'BK-202611-0001'],
                [
                    'asset_id' => $assetTersedia->id,
                    'user_id' => $peminat1->id,
                    'nama_pemohon' => 'Andi Wijaya',
                    'nik_atau_nib' => '3273012304850001 / NIB: 0123456789123',
                    'nama_instansi' => 'PT Parahyangan Citra Rasa',
                    'nomor_whatsapp' => '081223344556',
                    'email' => 'andi.wijaya@gmail.com',
                    'rencana_peruntukan' => 'Pengembangan Gerai Kuliner Khas Jawa Barat & Kafe Kreatif',
                    'durasi_sewa_tahun' => 3,
                    'status' => 'document_review',
                    'catatan_admin' => 'Pemohon sudah dihubungi via WhatsApp pada tanggal 02 Nov. Peminat menyerahkan berkas fisik ke kantor UPTD. Dokumen legalitas badan usaha dan proposal kegiatan telah diinput oleh admin.',
                    'tanggal_booking' => now()->subDays(5),
                    'tanggal_dihubungi' => now()->subDays(3),
                    'petugas_uptd_id' => $admin?->id,
                ]
            );

            // Update status ketersediaan aset menjadi 'proses_booking'
            $assetTersedia->update(['status_ketersediaan' => 'proses_booking']);

            // Input beberapa berkas oleh Admin UPTD
            $reqKtp = $requirements->where('kode', 'REQ_KTP_NIB')->first();
            if ($reqKtp) {
                BookingDocument::updateOrCreate(
                    [
                        'booking_id' => $booking1->id,
                        'booking_requirement_id' => $reqKtp->id,
                    ],
                    [
                        'nomor_dokumen' => 'AHU-0012938.AH.01.01.TAHUN 2023',
                        'tanggal_dokumen' => '2023-05-12',
                        'file_name' => 'Legalitas_PT_Parahyangan_Citra_Rasa.pdf',
                        'file_path' => 'documents/sample_legalitas.pdf',
                        'file_size' => 1245000,
                        'file_type' => 'application/pdf',
                        'status_verifikasi' => 'verified',
                        'catatan' => 'NIB dan Akta Notaris sah dan aktif tercatat di OSS.',
                        'admin_id' => $admin?->id,
                    ]
                );
            }

            $reqSurat = $requirements->where('kode', 'REQ_SURAT_PERMOHONAN')->first();
            if ($reqSurat) {
                BookingDocument::updateOrCreate(
                    [
                        'booking_id' => $booking1->id,
                        'booking_requirement_id' => $reqSurat->id,
                    ],
                    [
                        'nomor_dokumen' => '015/PCR-DIR/X/2026',
                        'tanggal_dokumen' => now()->subDays(4)->format('Y-m-d'),
                        'file_name' => 'Surat_Permohonan_Sewa_BMD.pdf',
                        'file_path' => 'documents/sample_surat_permohonan.pdf',
                        'file_size' => 450000,
                        'file_type' => 'application/pdf',
                        'status_verifikasi' => 'verified',
                        'catatan' => 'Ditujukan kepada Gubernur Jabar c.q. Kepala BPKAD Jabar.',
                        'admin_id' => $admin?->id,
                    ]
                );
            }

            $reqProposal = $requirements->where('kode', 'REQ_PROPOSAL')->first();
            if ($reqProposal) {
                BookingDocument::updateOrCreate(
                    [
                        'booking_id' => $booking1->id,
                        'booking_requirement_id' => $reqProposal->id,
                    ],
                    [
                        'nomor_dokumen' => 'PROP/2026/XI-01',
                        'tanggal_dokumen' => now()->subDays(4)->format('Y-m-d'),
                        'file_name' => 'Proposal_Pemanfaatan_Kuliner.pdf',
                        'file_path' => 'documents/sample_proposal.pdf',
                        'file_size' => 3200000,
                        'file_type' => 'application/pdf',
                        'status_verifikasi' => 'verified',
                        'catatan' => 'Proposal bisnis dan site layout telah disetujui tim teknis.',
                        'admin_id' => $admin?->id,
                    ]
                );
            }
        }

        // Booking 2: Status "pending" (booking baru masuk, menunggu admin UPTD klik chat WA)
        $assetTersedia2 = Asset::where('status_ketersediaan', 'tersedia')->skip(1)->first();
        if ($assetTersedia2) {
            Booking::updateOrCreate(
                ['kode_booking' => 'BK-202611-0002'],
                [
                    'asset_id' => $assetTersedia2->id,
                    'user_id' => $peminat2->id,
                    'nama_pemohon' => 'Siti Nurhaliza',
                    'nik_atau_nib' => '3204126508900003',
                    'nama_instansi' => 'Koperasi Sentra UMKM Mandiri',
                    'nomor_whatsapp' => '085799887766',
                    'email' => 'siti.nurhaliza@koperasijabar.co.id',
                    'rencana_peruntukan' => 'Pusat Oleh-oleh dan Kerajinan Tangan Kreatif Binaan Dekranasda Jabar',
                    'durasi_sewa_tahun' => 2,
                    'status' => 'pending',
                    'catatan_admin' => null,
                    'tanggal_booking' => now()->subHours(3),
                    'tanggal_dihubungi' => null,
                    'petugas_uptd_id' => null,
                ]
            );
        }
    }
}
