<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAndTenantDocumentWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function createAsset(string $kode = 'AST-DOC-001', string $status = 'tersedia'): Asset
    {
        return Asset::create([
            'kode_aset' => $kode.'-'.uniqid(),
            'nama_objek' => 'Gedung Kesenian Jawa Barat',
            'jenis_bmd' => 'bangunan',
            'alamat' => 'Jl. Braga No. 15, Bandung',
            'kabupaten_kota' => 'Kota Bandung',
            'luas_total' => 1200.00,
            'kondisi' => 'baik',
            'status_ketersediaan' => $status,
            'is_featured' => true,
        ]);
    }

    public function test_seven_admin_and_lease_workflow_documents_are_correctly_registered(): void
    {
        $expected = [
            'REQ_LAPORAN_PENILAIAN' => [
                'nama' => 'Laporan Penilaian (notdin dan laporan)',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
            'REQ_BA_NEGOSIASI' => [
                'nama' => 'Berita Acara Negosiasi',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
            'REQ_FASILITASI_KEPGUB' => [
                'nama' => 'Surat Permohonan Fasilitasi Kepgub',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
            'REQ_KEPUTUSAN_GUBERNUR' => [
                'nama' => 'Keputusan Gubernur',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
            'REQ_PEMBERITAHUAN_PEMBAYARAN' => [
                'nama' => 'Surat Pemberitahuan Pembayaran',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
            'REQ_BUKTI_BAYAR' => [
                'nama' => 'Bukti Bayar (oleh mitra)',
                'diunggah_oleh' => 'mitra',
                'kategori' => 'administrasi',
            ],
            'REQ_PERJANJIAN_SEWA' => [
                'nama' => 'Perjanjian Sewa',
                'diunggah_oleh' => 'admin',
                'kategori' => 'administrasi',
            ],
        ];

        foreach ($expected as $kode => $meta) {
            $req = BookingRequirement::where('kode', $kode)->first();
            $this->assertNotNull($req, "Persyaratan {$kode} harus terdaftar di database.");
            $this->assertSame($meta['nama'], $req->nama_persyaratan, "Nama persyaratan {$kode} harus sesuai.");
            $this->assertSame($meta['diunggah_oleh'], $req->diunggah_oleh, "Pihak pengunggah {$kode} harus sesuai.");
            $this->assertSame($meta['kategori'], $req->kategori, "Kategori {$kode} harus administrasi.");
        }
    }

    public function test_mitra_can_upload_bukti_bayar_via_booking_status(): void
    {
        $user = User::factory()->create([
            'role' => 'peminat',
            'email' => 'mitra.sewa.'.uniqid().'@example.com',
            'phone' => '081234567890',
        ]);

        $asset = $this->createAsset('AST-DOC-002');

        $booking = Booking::create([
            'kode_booking' => 'BK-TEST-BB-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Mitra Mandiri Sejahtera',
            'nomor_whatsapp' => '081234567890',
            'email' => $user->email,
            'tipe_pemohon' => 'badan_usaha',
            'nama_instansi' => 'PT Mitra Mandiri',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'bertahap',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $reqBukti = BookingRequirement::where('kode', 'REQ_BUKTI_BAYAR')->firstOrFail();
        $file = UploadedFile::fake()->create('sts_kasda_termin_1.pdf', 300, 'application/pdf');

        $response = $this->actingAs($user, 'web')
            ->post('/status-booking/'.$booking->kode_booking.'/upload-dokumen', [
                'booking_requirement_id' => $reqBukti->id,
                'dokumen_file' => $file,
            ]);

        $response->assertStatus(302);
        $response->assertSessionHas('success');

        // Pastikan dokumen tercatat di tabel booking_documents
        $this->assertDatabaseHas('booking_documents', [
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqBukti->id,
            'file_name' => 'sts_kasda_termin_1.pdf',
            'status_verifikasi' => 'pending',
        ]);

        // Karena skema bertahap, pastikan tersinkron ke termin 1
        $this->assertDatabaseHas('booking_payments', [
            'booking_id' => $booking->id,
            'termin_ke' => 1,
            'file_name' => 'sts_kasda_termin_1.pdf',
            'status_pembayaran' => 'pending_verifikasi',
        ]);
    }

    public function test_mitra_cannot_upload_admin_dossier_documents(): void
    {
        $user = User::factory()->create([
            'role' => 'peminat',
            'email' => 'mitra.blocked.'.uniqid().'@example.com',
            'phone' => '081234567891',
        ]);

        $asset = $this->createAsset('AST-DOC-003');

        $booking = Booking::create([
            'kode_booking' => 'BK-TEST-ADMIN-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Budi Permana',
            'nomor_whatsapp' => '081234567891',
            'email' => $user->email,
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $adminDocCodes = [
            'REQ_LAPORAN_PENILAIAN',
            'REQ_BA_NEGOSIASI',
            'REQ_FASILITASI_KEPGUB',
            'REQ_KEPUTUSAN_GUBERNUR',
            'REQ_PEMBERITAHUAN_PEMBAYARAN',
            'REQ_PERJANJIAN_SEWA',
        ];

        foreach ($adminDocCodes as $kode) {
            $req = BookingRequirement::where('kode', $kode)->firstOrFail();
            $fakeFile = UploadedFile::fake()->create('attempt_'.$kode.'.pdf', 100, 'application/pdf');

            $response = $this->actingAs($user, 'web')
                ->post('/status-booking/'.$booking->kode_booking.'/upload-dokumen', [
                    'booking_requirement_id' => $req->id,
                    'dokumen_file' => $fakeFile,
                ]);

            // Harus ditolak dengan 403 Forbidden
            $response->assertStatus(403);
        }
    }

    public function test_booking_status_page_displays_all_seven_documents_with_appropriate_roles(): void
    {
        $user = User::factory()->create([
            'role' => 'peminat',
            'email' => 'display.test.'.uniqid().'@example.com',
            'phone' => '081234567892',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin.uptd.'.uniqid().'@example.com',
        ]);

        $asset = $this->createAsset('AST-DOC-004');

        $booking = Booking::create([
            'kode_booking' => 'BK-TEST-UI-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Display Test User',
            'nomor_whatsapp' => '081234567892',
            'email' => $user->email,
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'document_review',
            'tanggal_booking' => now(),
        ]);

        // Simulasikan admin telah mengunggah Laporan Penilaian
        $reqPenilaian = BookingRequirement::where('kode', 'REQ_LAPORAN_PENILAIAN')->firstOrFail();
        BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqPenilaian->id,
            'nomor_dokumen' => 'NOTDIN/001/BPKAD/2026',
            'tanggal_dokumen' => now(),
            'file_name' => 'Laporan_Penilaian_Wajar_2026.pdf',
            'file_path' => 'booking-documents/sample_penilaian.pdf',
            'file_size' => 500000,
            'status_verifikasi' => 'verified',
            'admin_id' => $admin->id,
        ]);

        $response = $this->actingAs($user, 'web')->get('/status-booking/'.$booking->kode_booking);

        $response->assertStatus(200);
        $response->assertSee('Berkas Administrasi &amp; Penetapan Sewa (7 Dokumen)', false);
        $response->assertSee('Laporan Penilaian (notdin dan laporan)');
        $response->assertSee('Berita Acara Negosiasi');
        $response->assertSee('Surat Permohonan Fasilitasi Kepgub');
        $response->assertSee('Keputusan Gubernur');
        $response->assertSee('Surat Pemberitahuan Pembayaran');
        $response->assertSee('Bukti Bayar (oleh mitra)');
        $response->assertSee('Perjanjian Sewa');

        // Badge peran
        $response->assertSee('1. Diinput Admin UPTD');
        $response->assertSee('6. Khusus Diinput Mitra Penyewa');

        // File yang sudah diunggah admin tampak di halaman pemohon
        $response->assertSee('Laporan_Penilaian_Wajar_2026.pdf');
    }
}
