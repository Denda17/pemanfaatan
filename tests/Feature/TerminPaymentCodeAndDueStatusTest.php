<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TerminPaymentCodeAndDueStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_gradual_tenant_must_request_payment_code_before_uploading_termin_2_proof(): void
    {
        Storage::fake('local');

        $asset = Asset::factory()->create(['status_ketersediaan' => 'tersewa']);
        $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $booking = Booking::create([
            'kode_booking' => 'BMD-TEST-TERM-01',
            'asset_id' => $asset->id,
            'user_id' => $tenant->id,
            'nama_pemohon' => $tenant->name,
            'nomor_whatsapp' => '081234567890',
            'email' => $tenant->email,
            'tipe_pemohon' => 'perusahaan',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'bertahap',
            'status' => 'contract_signed',
            'tanggal_booking' => now()->subYear(),
        ]);

        // 1. Belum ada kode bayar -> upload bukti setor termin 2 ditolak
        $respBlocked = $this->actingAs($tenant, 'web')->post(route('booking.upload-termin', $booking->kode_booking), [
            'termin_ke' => 2,
            'bukti_file' => UploadedFile::fake()->create('slip.pdf', 200, 'application/pdf'),
        ]);
        $respBlocked->assertSessionHas('error');

        // 2. Penyewa klik "Minta Kode Pembayaran"
        $respReq = $this->actingAs($tenant, 'web')->post(route('booking.request-termin-code', $booking->kode_booking), [
            'termin_ke' => 2,
        ]);
        $respReq->assertSessionHas('success');

        $payment = BookingPayment::where('booking_id', $booking->id)->where('termin_ke', 2)->firstOrFail();
        $this->assertSame('kode_diminta', $payment->status_pembayaran);
        $this->assertNotNull($payment->kode_diminta_at);

        // Halaman status menampilkan pesan menunggu kode bayar dari admin
        $this->actingAs($tenant, 'web')
            ->get(route('booking.status', $booking->kode_booking))
            ->assertOk()
            ->assertSee('Menunggu Kode Bayar UPTD');

        // 3. Simulasi Admin menerbitkan kode bayar & file billing
        Storage::disk('local')->put('booking-termin-billing/kode_t2.pdf', '%PDF-1.4 dummy');
        $payment->update([
            'kode_pembayaran' => '880099112233',
            'kode_bayar_file_path' => 'booking-termin-billing/kode_t2.pdf',
            'kode_bayar_file_name' => 'kode_t2.pdf',
            'nominal_kewajiban' => 50000000,
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status_pembayaran' => 'kode_terbit',
            'kode_diterbitkan_at' => now(),
        ]);

        // Penyewa dapat melihat kode bayar dan mengunduh berkasnya
        $this->actingAs($tenant, 'web')
            ->get(route('booking.status', $booking->kode_booking))
            ->assertOk()
            ->assertSee('880099112233')
            ->assertSee('Lihat / Unduh Berkas Kode Bayar');

        $this->actingAs($tenant, 'web')
            ->get(route('booking.payments.billing-code', $payment))
            ->assertOk();

        // 4. Penyewa sekarang bisa mengunggah bukti setor termin 2
        $respUpload = $this->actingAs($tenant, 'web')->post(route('booking.upload-termin', $booking->kode_booking), [
            'termin_ke' => 2,
            'nomor_sts' => 'STS-2027-001',
            'bukti_file' => UploadedFile::fake()->create('bukti_t2.pdf', 200, 'application/pdf'),
        ]);
        $respUpload->assertSessionHas('success');

        $payment->refresh();
        $this->assertSame('pending_verifikasi', $payment->status_pembayaran);
        $this->assertSame('STS-2027-001', $payment->nomor_sts);
    }

    public function test_termin_button_color_changes_based_on_due_date_urgency(): void
    {
        $asset = Asset::factory()->create(['status_ketersediaan' => 'tersewa']);
        $tenant = User::factory()->create(['role' => 'user', 'is_active' => true]);

        $booking = Booking::create([
            'kode_booking' => 'BMD-TEST-TERM-02',
            'asset_id' => $asset->id,
            'user_id' => $tenant->id,
            'nama_pemohon' => $tenant->name,
            'nomor_whatsapp' => '081234567891',
            'email' => $tenant->email,
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 4,
            'skema_pembayaran' => 'bertahap',
            'status' => 'contract_signed',
            'tanggal_booking' => now()->subYear(),
        ]);

        // Termin 2: Terlambat 5 hari -> Merah (bg-rose-600)
        BookingPayment::create([
            'booking_id' => $booking->id,
            'termin_ke' => 2,
            'tahun_ke' => 'Tahun ke-2',
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'status_pembayaran' => 'menunggu_pembayaran',
        ]);

        // Termin 3: Hampir habis (10 hari lagi, <= 30 hari) -> Oranye (bg-amber-500)
        BookingPayment::create([
            'booking_id' => $booking->id,
            'termin_ke' => 3,
            'tahun_ke' => 'Tahun ke-3',
            'tanggal_jatuh_tempo' => now()->addDays(10)->toDateString(),
            'status_pembayaran' => 'menunggu_pembayaran',
        ]);

        // Termin 4: Masih jauh (90 hari lagi) -> Biru (bg-blue-600)
        BookingPayment::create([
            'booking_id' => $booking->id,
            'termin_ke' => 4,
            'tahun_ke' => 'Tahun ke-4',
            'tanggal_jatuh_tempo' => now()->addDays(90)->toDateString(),
            'status_pembayaran' => 'menunggu_pembayaran',
        ]);

        $response = $this->actingAs($tenant, 'web')->get(route('booking.status', $booking->kode_booking));

        $response->assertOk();
        $response->assertSee('Minta Kode Pembayaran Termin ke-2 (Terlambat 5 Hari)');
        $response->assertSee('bg-rose-600 hover:bg-rose-700');
        $response->assertSee('Minta Kode Pembayaran Termin ke-3 (Sisa 10 Hari)');
        $response->assertSee('bg-amber-500 hover:bg-amber-600');
        $response->assertSee('Minta Kode Pembayaran Termin ke-4');
        $response->assertSee('bg-blue-600 hover:bg-blue-700');
    }
}
