<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BookingDeadlineAndAntiSpamTest extends TestCase
{
    use DatabaseTransactions;

    protected function createAsset(array $attributes = []): Asset
    {
        return Asset::create(array_merge([
            'nama_objek' => 'Aset Test '.uniqid(),
            'kode_aset' => 'AST-'.uniqid(),
            'bentuk_objek' => 'Tanah',
            'status_pengelola' => 'BPKAD Jabar',
            'kabupaten_kota' => 'Kota Bandung',
            'alamat' => 'Jl. Diponegoro No. 22',
            'luas_tanah' => 500,
            'status_ketersediaan' => 'tersedia',
            'estimasi_tarif_tahunan' => 25000000,
        ], $attributes));
    }

    public function test_first_booking_applicant_triggers_5_working_days_deadline(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00'); // Senin, 16 Nov 2026

        $asset = $this->createAsset([
            'status_ketersediaan' => 'tersedia',
            'booking_opened_at' => null,
            'booking_deadline_at' => null,
        ]);

        $applicant1 = User::factory()->create(['role' => 'peminat']);

        $response = $this->actingAs($applicant1, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Ahmad Peminat 1',
            'nomor_whatsapp' => '081211112222',
            'rencana_peruntukan' => 'Usaha Kafe',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
        ]);

        $response->assertSessionHas('success');

        $asset->refresh();
        $this->assertEquals('proses_booking', $asset->status_ketersediaan);
        $this->assertNotNull($asset->booking_opened_at);
        $this->assertNotNull($asset->booking_deadline_at);

        // 5 hari kerja dari Senin 16 Nov -> Jumat 20 Nov 2026 pukul 23:59:59 (Senin-Jumat pada minggu yang sama)
        $expectedDeadline = Carbon::parse('2026-11-20 23:59:59');
        $this->assertEquals($expectedDeadline->toDateTimeString(), $asset->booking_deadline_at->toDateTimeString());

        Carbon::setTestNow();
    }

    public function test_second_applicant_can_submit_booking_within_5_working_days(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');

        $asset = $this->createAsset([
            'status_ketersediaan' => 'tersedia',
            'booking_opened_at' => null,
            'booking_deadline_at' => null,
        ]);

        $applicant1 = User::factory()->create(['role' => 'peminat']);
        $applicant2 = User::factory()->create(['role' => 'peminat']);

        // Pemohon 1 mendaftar pada hari Senin
        $this->actingAs($applicant1, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Pertama',
            'nomor_whatsapp' => '081211112222',
            'rencana_peruntukan' => 'Usaha A',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
        ]);

        // Majukan waktu ke hari Kamis (masih dalam 5 hari kerja)
        Carbon::setTestNow('2026-11-19 14:00:00');

        $this->assertTrue($asset->fresh()->isBookingOpen());

        // Pemohon 2 mendaftar pada hari Kamis
        $response = $this->actingAs($applicant2, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Kedua',
            'nomor_whatsapp' => '081333334444',
            'rencana_peruntukan' => 'Usaha B',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(2, $asset->bookings()->count());

        Carbon::setTestNow();
    }

    public function test_same_applicant_cannot_submit_duplicate_active_booking_on_same_asset(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');

        $asset = $this->createAsset([
            'status_ketersediaan' => 'tersedia',
        ]);

        $applicant = User::factory()->create(['role' => 'peminat']);

        // Pengajuan pertama
        $this->actingAs($applicant, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Setia',
            'nomor_whatsapp' => '081211112222',
            'rencana_peruntukan' => 'Usaha Pertama',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
        ]);

        // Pengajuan kedua oleh pemohon yang sama pada objek yang sama
        $dupResponse = $this->actingAs($applicant, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Setia (Spam)',
            'nomor_whatsapp' => '081211112222',
            'rencana_peruntukan' => 'Usaha Kedua Spam',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
        ]);

        $dupResponse->assertSessionHas('error');
        $this->assertStringContainsString('Setiap pemohon hanya dapat mengajukan 1 kali', session('error'));
        $this->assertEquals(1, $asset->bookings()->count());

        Carbon::setTestNow();
    }

    public function test_booking_submission_is_rejected_after_deadline_passes(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00'); // Senin

        $asset = $this->createAsset([
            'status_ketersediaan' => 'tersedia',
        ]);

        $applicant1 = User::factory()->create(['role' => 'peminat']);
        $applicantTelat = User::factory()->create(['role' => 'peminat']);

        // Peminat 1 daftar
        $this->actingAs($applicant1, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Pertama',
            'nomor_whatsapp' => '081211112222',
            'rencana_peruntukan' => 'Usaha A',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
        ]);

        // Majukan waktu melewati deadline (misal Selasa pekan berikutnya, 24 Nov 2026)
        Carbon::setTestNow('2026-11-24 08:00:00');

        $this->assertTrue($asset->fresh()->isBookingClosed());

        // Peminat lain mencoba submit setelah deadline
        $telatResponse = $this->actingAs($applicantTelat, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Peminat Terlambat',
            'nomor_whatsapp' => '081999998888',
            'rencana_peruntukan' => 'Usaha Telat',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
        ]);

        $telatResponse->assertSessionHas('error');
        $this->assertStringContainsString('telah ditutup', session('error'));
        $this->assertEquals(1, $asset->bookings()->count());

        Carbon::setTestNow();
    }

    public function test_when_all_bookings_are_rejected_asset_reverts_to_tersedia_and_deadline_resets(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');

        $asset = $this->createAsset([
            'status_ketersediaan' => 'tersedia',
        ]);

        $user1 = User::factory()->create(['role' => 'peminat']);
        $user2 = User::factory()->create(['role' => 'peminat']);

        $booking1 = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => 'Pemohon 1',
            'nomor_whatsapp' => '081111',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $booking2 = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => 'Pemohon 2',
            'nomor_whatsapp' => '082222',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $asset->update([
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now(),
            'booking_deadline_at' => now()->addWeekdays(5)->endOfDay(),
        ]);

        // Tolak booking 1 saja -> asset harus tetap proses_booking karena booking 2 masih aktif
        $booking1->update(['status' => 'rejected']);
        $asset->refresh();
        $this->assertEquals('proses_booking', $asset->status_ketersediaan);
        $this->assertNotNull($asset->booking_deadline_at);

        // Tolak booking 2 juga -> semua booking kini rejected!
        $booking2->update(['status' => 'rejected']);
        $asset->refresh();

        // Status aset otomatis kembali ke tersedia dan deadline direset!
        $this->assertEquals('tersedia', $asset->status_ketersediaan);
        $this->assertNull($asset->booking_opened_at);
        $this->assertNull($asset->booking_deadline_at);

        Carbon::setTestNow();
    }

    public function test_asset_with_active_booking_deadline_remains_visible_in_siap_sewa_catalog(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00'); // Senin

        $asset = $this->createAsset([
            'nama_objek' => 'Aset Siap Sewa Tetap Muncul',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now(),
            'booking_deadline_at' => now()->addWeekdays(5)->endOfDay(), // aktif sampai 23 Nov
        ]);

        $this->assertTrue($asset->isBookingOpen());

        // Halaman /aset-siap-sewa harus memuat aset ini
        $response = $this->get('/aset-siap-sewa?keyword=Tetap+Muncul');
        $response->assertStatus(200);
        $response->assertSee('Aset Siap Sewa Tetap Muncul');
        $response->assertSee('Minat Terbuka');

        // Filter status=tersedia di /katalog juga harus memuat aset ini
        $filterResponse = $this->get('/katalog?status=tersedia&keyword=Tetap+Muncul');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Aset Siap Sewa Tetap Muncul');

        Carbon::setTestNow();
    }

    public function test_asset_with_expired_booking_deadline_is_hidden_from_siap_sewa_catalog(): void
    {
        Carbon::setTestNow('2026-11-16 10:00:00');

        $asset = $this->createAsset([
            'nama_objek' => 'Aset Deadline Telah Kadaluarsa',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now()->subDays(10),
            'booking_deadline_at' => now()->subDays(2), // sudah lewat deadline
        ]);

        $this->assertTrue($asset->isBookingClosed());

        // Halaman /aset-siap-sewa TIDAK boleh menampilkan aset ini
        $response = $this->get('/aset-siap-sewa');
        $response->assertStatus(200);
        $response->assertDontSee('Aset Deadline Telah Kadaluarsa');

        // Filter status=tersedia di /katalog juga TIDAK boleh menampilkan aset ini
        $filterResponse = $this->get('/katalog?status=tersedia');
        $filterResponse->assertStatus(200);
        $filterResponse->assertDontSee('Aset Deadline Telah Kadaluarsa');

        Carbon::setTestNow();
    }
}
