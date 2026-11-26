<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AddendumPksDocumentTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_addendum_pks_is_optional_and_supports_multiple_documents_per_booking(): void
    {
        $pemohon = User::factory()->create(['role' => 'pemohon']);
        $otherUser = User::factory()->create(['role' => 'pemohon']);
        $admin = User::factory()->create(['role' => 'admin']);
        $asset = Asset::factory()->create(['status_ketersediaan' => 'proses_booking']);

        $booking = Booking::create([
            'kode_booking' => 'BK-ADDENDUM-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $pemohon->id,
            'nama_pemohon' => $pemohon->name,
            'nomor_whatsapp' => '081234567890',
            'jenis_pemohon' => 'perorangan',
            'rencana_peruntukan' => 'Kantor Operasional',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'contract_signed',
            'tanggal_booking' => now(),
        ]);

        $addendumReq = BookingRequirement::where('kode', 'REQ_ADDENDUM_PKS')->firstOrFail();

        $this->assertFalse($addendumReq->is_required);
        $this->assertSame('administrasi', $addendumReq->kategori);
        $this->assertSame('admin', $addendumReq->diunggah_oleh);
        $this->assertTrue($addendumReq->isViewableByApplicant());

        // Tanpa addendum pun, kelengkapan wajib pemohon tidak terpengaruh oleh REQ_ADDENDUM_PKS
        $this->assertNotContains('REQ_ADDENDUM_PKS', $booking->getRequiredRequirementCodes());

        Storage::disk('local')->put('booking-documents/addendum-1.pdf', '%PDF-1.4 addendum 1');
        Storage::disk('local')->put('booking-documents/addendum-2.pdf', '%PDF-1.4 addendum 2');

        $doc1 = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $addendumReq->id,
            'nomor_dokumen' => '001/ADD-PKS/BPKAD/2026',
            'tanggal_dokumen' => now()->subDays(5),
            'file_path' => 'booking-documents/addendum-1.pdf',
            'file_name' => 'addendum-1.pdf',
            'status_verifikasi' => 'verified',
            'catatan' => 'Addendum I - Penyesuaian Luasan',
            'admin_id' => $admin->id,
        ]);

        $doc2 = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $addendumReq->id,
            'nomor_dokumen' => '002/ADD-PKS/BPKAD/2026',
            'tanggal_dokumen' => now(),
            'file_path' => 'booking-documents/addendum-2.pdf',
            'file_name' => 'addendum-2.pdf',
            'status_verifikasi' => 'verified',
            'catatan' => 'Addendum II - Penyesuaian Termin',
            'admin_id' => $admin->id,
        ]);

        $this->actingAs($pemohon)
            ->get(route('booking.status', $booking->kode_booking))
            ->assertOk()
            ->assertSee('Addendum #1')
            ->assertSee('001/ADD-PKS/BPKAD/2026')
            ->assertSee('Addendum #2')
            ->assertSee('002/ADD-PKS/BPKAD/2026')
            ->assertSee('2 Addendum Terbit');

        $this->actingAs($pemohon)
            ->get(route('booking.documents.view', $doc1))
            ->assertOk();

        $this->actingAs($pemohon)
            ->get(route('booking.documents.view', $doc2))
            ->assertOk();

        $this->actingAs($otherUser)
            ->get(route('booking.documents.view', $doc1))
            ->assertForbidden();
    }
}
