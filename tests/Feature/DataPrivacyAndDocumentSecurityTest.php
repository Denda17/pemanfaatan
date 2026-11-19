<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use App\Services\DocumentWatermarkService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataPrivacyAndDocumentSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    public function test_guest_is_redirected_to_login_when_accessing_booking_document(): void
    {
        $asset = Asset::first() ?? Asset::factory()->create();
        $user = User::factory()->create(['role' => 'peminat']);

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Pemohon Asli',
            'nik_atau_nib' => '3201010101900001',
            'nomor_whatsapp' => '081234567890',
            'email' => $user->email,
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $req = BookingRequirement::first() ?? BookingRequirement::create([
            'kode' => 'REQ_KTP',
            'nama_persyaratan' => 'KTP Pemohon',
            'is_required' => true,
            'urutan' => 1,
        ]);

        $filePath = 'booking-documents/test_ktp.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 dummy pdf content');

        $doc = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $req->id,
            'file_path' => $filePath,
            'file_name' => 'ktp_asli.pdf',
            'file_size' => 1024,
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'pending',
        ]);

        $response = $this->get(route('booking.documents.view', $doc));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_user_receives_403_when_accessing_other_users_document(): void
    {
        $asset = Asset::first() ?? Asset::factory()->create();
        $owner = User::factory()->create(['role' => 'peminat']);
        $stranger = User::factory()->create(['role' => 'peminat']);

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $owner->id,
            'nama_pemohon' => 'Pemohon A',
            'nik_atau_nib' => '3204010101880002',
            'nomor_whatsapp' => '081234567891',
            'email' => $owner->email,
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $req = BookingRequirement::first();
        $filePath = 'booking-documents/confidential.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 secret document content');

        $doc = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $req->id,
            'file_path' => $filePath,
            'file_name' => 'confidential.pdf',
            'file_size' => 2048,
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'pending',
        ]);

        // Pengguna lain (stranger) mencoba mengakses dokumen
        $response = $this->actingAs($stranger, 'web')->get(route('booking.documents.view', $doc));
        $response->assertStatus(403);
    }

    public function test_owner_can_view_and_download_their_own_document(): void
    {
        $asset = Asset::first() ?? Asset::factory()->create();
        $owner = User::factory()->create(['role' => 'peminat']);

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $owner->id,
            'nama_pemohon' => 'Pemohon A',
            'nik_atau_nib' => '3204010101880002',
            'nomor_whatsapp' => '081234567891',
            'email' => $owner->email,
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $req = BookingRequirement::first();
        $filePath = 'booking-documents/ktp_owner.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 owner ktp content');

        $doc = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $req->id,
            'file_path' => $filePath,
            'file_name' => 'ktp_owner.pdf',
            'file_size' => 2048,
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'pending',
        ]);

        // Pemilik dapat melihat berkas
        $viewRes = $this->actingAs($owner, 'web')->get(route('booking.documents.view', $doc));
        $viewRes->assertStatus(200);

        // Pemilik dapat mengunduh berkas
        $downloadRes = $this->actingAs($owner, 'web')->get(route('booking.documents.download', $doc));
        $downloadRes->assertStatus(200);
    }

    public function test_admin_can_view_document_and_audit_log_is_triggered(): void
    {
        $asset = Asset::first() ?? Asset::factory()->create();
        $owner = User::factory()->create(['role' => 'peminat']);
        $admin = User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $owner->id,
            'nama_pemohon' => 'Pemohon A',
            'nik_atau_nib' => '3204010101880002',
            'nomor_whatsapp' => '081234567891',
            'email' => $owner->email,
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $req = BookingRequirement::first();
        $filePath = 'booking-documents/surat_permohonan.pdf';
        Storage::disk('local')->put($filePath, '%PDF-1.4 surat content');

        $doc = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $req->id,
            'file_path' => $filePath,
            'file_name' => 'surat_permohonan.pdf',
            'file_size' => 2048,
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'pending',
        ]);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message) use ($admin, $booking) {
                return str_contains($message, '[AUDIT PDP]') &&
                    str_contains($message, (string) $admin->id) &&
                    str_contains($message, $booking->kode_booking);
            });

        $res = $this->actingAs($admin, 'web')->get(route('booking.documents.view', $doc));
        $res->assertStatus(200);
    }

    public function test_document_upload_is_stored_on_private_disk_not_public(): void
    {
        $asset = Asset::first() ?? Asset::factory()->create();
        $applicant = User::factory()->create(['role' => 'peminat']);

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $applicant->id,
            'nama_pemohon' => 'Siti Aminah',
            'nik_atau_nib' => '3201015504950003',
            'nomor_whatsapp' => '085712345678',
            'email' => $applicant->email,
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $req = BookingRequirement::where('kode', 'REQ_KTP')->first() ?? BookingRequirement::first();

        $dummyKtp = UploadedFile::fake()->create('ktp_pemohon.pdf', 500, 'application/pdf');

        $response = $this->actingAs($applicant, 'web')->post(route('booking.upload-dokumen', $booking->kode_booking), [
            'booking_requirement_id' => $req->id,
            'dokumen_file' => $dummyKtp,
        ]);

        $response->assertSessionHas('success');

        $savedDoc = BookingDocument::where('booking_id', $booking->id)
            ->where('booking_requirement_id', $req->id)
            ->first();

        $this->assertNotNull($savedDoc);
        // Dokumen harus berada di private disk ('local')
        Storage::disk('local')->assertExists($savedDoc->file_path);
        // Dokumen TIDAK boleh berada di public disk
        Storage::disk('public')->assertMissing($savedDoc->file_path);
    }

    public function test_watermarking_service_executes_on_image_upload(): void
    {
        $service = app(DocumentWatermarkService::class);

        // Buat file gambar sementara (PNG)
        $img = imagecreatetruecolor(400, 250);
        $bg = imagecolorallocate($img, 240, 240, 240);
        imagefilledrectangle($img, 0, 0, 400, 250, $bg);
        $tempPath = tempnam(sys_get_temp_dir(), 'ktp_').'.png';
        imagepng($img, $tempPath);
        imagedestroy($img);

        $uploadedFile = new UploadedFile($tempPath, 'ktp_scan.png', 'image/png', null, true);

        $service->applyWatermarkIfImage($uploadedFile, 'BK-TEST-001');

        $this->assertFileExists($tempPath);

        // Bersihkan file temporer
        @unlink($tempPath);
    }

    public function test_booking_masked_nik_accessor(): void
    {
        $booking = new Booking;
        $booking->nik_atau_nib = '3204123456780001';

        $this->assertEquals('3204 •••• •••• 0001', $booking->masked_nik_atau_nib);

        $bookingShort = new Booking;
        $bookingShort->nik_atau_nib = '12345678';
        $this->assertEquals('12 •••• 78', $bookingShort->masked_nik_atau_nib);

        $bookingEmpty = new Booking;
        $this->assertEquals('-', $bookingEmpty->masked_nik_atau_nib);
    }
}
