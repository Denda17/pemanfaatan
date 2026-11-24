<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetUnit;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MultiUnitClusterAndReactiveGalleryTest extends TestCase
{
    use DatabaseTransactions;

    private function createMultiUnitAsset(): Asset
    {
        $asset = Asset::create([
            'nama_objek' => 'Kawasan Pertokoan Banceuy Permai',
            'slug' => 'kawasan-pertokoan-banceuy-permai-test',
            'kode_aset' => 'BMD-BANCEUY-TEST',
            'bentuk_objek' => 'Ruko',
            'kabupaten_kota' => 'Kota Bandung',
            'alamat' => 'Jl. Banceuy No. 8 & Jl. ABC, Kota Bandung',
            'status_ketersediaan' => 'tersedia',
            'tipe_aset' => 'kompleks_multi_unit',
            'site_plan_type' => 'interactive_cluster',
            'site_plan_url' => 'siteplans/banceuy-site-plan.jpg',
            'estimasi_tarif_tahunan' => 62500000,
        ]);

        AssetUnit::create([
            'asset_id' => $asset->id,
            'nomor_unit' => 'A11',
            'blok' => 'Blok A',
            'kategori_zona' => 'hook_luar',
            'kategori_label' => 'Hook Luar',
            'tarif_tahunan' => 90000000,
            'luas_tanah' => 45,
            'luas_bangunan' => 90,
            'status_ketersediaan' => 'tersedia',
        ]);

        AssetUnit::create([
            'asset_id' => $asset->id,
            'nomor_unit' => 'A12',
            'blok' => 'Blok A',
            'kategori_zona' => 'premium',
            'kategori_label' => 'Premium',
            'tarif_tahunan' => 83000000,
            'luas_tanah' => 40,
            'luas_bangunan' => 80,
            'status_ketersediaan' => 'tersedia',
        ]);

        AssetUnit::create([
            'asset_id' => $asset->id,
            'nomor_unit' => 'B1',
            'blok' => 'Blok B',
            'kategori_zona' => 'basic',
            'kategori_label' => 'Basic',
            'tarif_tahunan' => 62500000,
            'luas_tanah' => 38,
            'luas_bangunan' => 76,
            'status_ketersediaan' => 'tersedia',
        ]);

        return $asset;
    }

    public function test_multi_unit_asset_displays_cluster_section_and_units_list(): void
    {
        $asset = $this->createMultiUnitAsset();

        $this->assertTrue($asset->isMultiUnit());
        $this->assertEquals(3, $asset->units()->count());

        $user = User::factory()->create(['role' => 'peminat', 'is_active' => true]);
        $response = $this->actingAs($user, 'web')->get('/aset/'.$asset->slug);

        $response->assertStatus(200);
        $response->assertSee('Aset Kompleks Multi-Unit');
        $response->assertSee('Pilihan Unit Ruko Komersial');
        $response->assertSee('Daftar Kartu Unit');
        $response->assertSee('Denah Visual Site Plan');
        $response->assertSee('Hook Luar (Rp 90 Jt)');
        $response->assertSee('Premium (Rp 83 Jt)');
        $response->assertSee('Unit Ruko yang Diajukan');
    }

    public function test_multi_unit_asset_requires_unit_selection_on_booking(): void
    {
        $asset = $this->createMultiUnitAsset();
        $user = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
        ]);

        // Mengirimkan booking tanpa memilih unit ruko
        $uniquePhone = '089911223344';
        $response = $this->actingAs($user, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Budi Santoso',
            'nomor_whatsapp' => $uniquePhone,
            'rencana_peruntukan' => 'Toko Retail Pakaian',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Silakan pilih minimal 1 unit ruko', session('error'));
        $this->assertEquals(0, Booking::where('nomor_whatsapp', $uniquePhone)->count());
    }

    public function test_multi_unit_asset_allows_booking_single_or_multiple_units(): void
    {
        $asset = $this->createMultiUnitAsset();
        $availableUnits = $asset->units()->where('status_ketersediaan', 'tersedia')->take(2)->get();
        $this->assertCount(2, $availableUnits);

        $unitA = $availableUnits[0];
        $unitB = $availableUnits[1];

        $user = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
        ]);

        // Ajukan booking 2 unit ruko sekaligus (sewa gandeng)
        $response = $this->actingAs($user, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'badan_usaha',
            'nama_pemohon' => 'Hendra Gunawan',
            'nomor_whatsapp' => '081987654321',
            'nama_instansi' => 'PT Parahyangan Niaga',
            'rencana_peruntukan' => 'Showroom & Kantor Cabang',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'bertahap',
            'selected_units' => [$unitA->id, $unitB->id],
        ]);

        $booking = Booking::where('nomor_whatsapp', '081987654321')->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals($unitA->id, $booking->asset_unit_id);
        $this->assertEquals([$unitA->id, $unitB->id], $booking->selected_unit_ids);
        $this->assertStringContainsString($unitA->nomor_unit, $booking->unit_names_text);
        $this->assertStringContainsString($unitB->nomor_unit, $booking->unit_names_text);

        // Kedua unit kini berstatus proses_booking dan memiliki countdown booking_deadline_at
        $unitA->refresh();
        $unitB->refresh();
        $this->assertEquals('proses_booking', $unitA->status_ketersediaan);
        $this->assertEquals('proses_booking', $unitB->status_ketersediaan);
        $this->assertNotNull($unitA->booking_deadline_at);
        $this->assertNotNull($unitB->booking_deadline_at);
        $this->assertTrue($unitA->isBookingOpen());
    }

    public function test_booking_on_one_unit_does_not_block_booking_on_other_available_units(): void
    {
        $asset = $this->createMultiUnitAsset();
        $units = $asset->units()->where('status_ketersediaan', 'tersedia')->take(2)->get();
        $unitA = $units[0];
        $unitB = $units[1];

        $user1 = User::factory()->create(['role' => 'peminat', 'is_active' => true]);
        $user2 = User::factory()->create(['role' => 'peminat', 'is_active' => true]);

        // Pemohon 1 mem-booking Unit A
        $this->actingAs($user1, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Pemohon Satu',
            'nomor_whatsapp' => '081111111111',
            'rencana_peruntukan' => 'Usaha Percetakan',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'selected_units' => [$unitA->id],
        ]);

        // Pemohon 2 mem-booking Unit B (harus sukses, tidak terblokir oleh booking Unit A)
        $resUser2 = $this->actingAs($user2, 'web')->post('/aset/'.$asset->slug.'/booking', [
            'tipe_pemohon' => 'perorangan',
            'nama_pemohon' => 'Pemohon Dua',
            'nomor_whatsapp' => '082222222222',
            'rencana_peruntukan' => 'Kedai Kopi',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'selected_units' => [$unitB->id],
        ]);

        $resUser2->assertSessionMissing('error');
        $this->assertDatabaseHas('bookings', [
            'nomor_whatsapp' => '082222222222',
            'asset_unit_id' => $unitB->id,
        ]);
    }
}
