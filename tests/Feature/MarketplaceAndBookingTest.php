<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceAndBookingTest extends TestCase
{
    public function test_home_page_is_accessible_and_displays_stats(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Marketplace Pemanfaatan');
        $response->assertSee('Aset Terdata');
        $response->assertSee('Alur Pemanfaatan BMD yang Mudah');
    }

    public function test_catalog_page_is_accessible_and_filters_work(): void
    {
        $response = $this->get('/katalog');
        $response->assertStatus(200);
        $response->assertSee('Katalog Pemanfaatan Aset Daerah');
        $response->assertSee('Peta Interaktif Sebaran Aset BMD Jabar');

        // Test with city filter
        $cityResponse = $this->get('/katalog?kabupaten_kota=Kota+Bandung');
        $cityResponse->assertStatus(200);
    }

    public function test_aset_siap_sewa_page_loads_with_available_map_and_dual_actions(): void
    {
        $response = $this->get('/aset-siap-sewa');
        $response->assertStatus(200);
        $response->assertSee('Katalog Aset Daerah Siap Sewa');
        $response->assertSee('Peta Interaktif Sebaran Aset BMD Siap Sewa');
        $response->assertSee('Siap Sewa');
    }

    public function test_asset_detail_page_loads_with_specifications(): void
    {
        $asset = Asset::first();
        $this->assertNotNull($asset);

        $response = $this->get('/aset/' . $asset->slug);

        $response->assertStatus(200);
        $response->assertSee($asset->nama_objek);
        $response->assertSee('Spesifikasi Fisik Objek BMD');
        $response->assertSee('Formulir Booking Minat Sewa');
    }

    public function test_user_can_submit_booking_via_marketplace_form(): void
    {
        $asset = Asset::where('status_ketersediaan', 'tersedia')->first();
        if (!$asset) {
            $asset = Asset::first();
            $asset->update(['status_ketersediaan' => 'tersedia']);
        }

        $postData = [
            'nama_pemohon' => 'Dedi Koswara',
            'nomor_whatsapp' => '08122334455',
            'nik_atau_nib' => '3273101010880002',
            'nama_instansi' => 'CV Kreatif Sinergi Jabar',
            'email' => 'dedi@kreatifjabar.com',
            'rencana_peruntukan' => 'Pusat Inkubasi Bisnis & Coffee Shop Komunitas',
            'durasi_sewa_tahun' => 2,
        ];

        $response = $this->post('/aset/' . $asset->slug . '/booking', $postData);

        $booking = Booking::where('nomor_whatsapp', '08122334455')->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(2, $booking->durasi_sewa_tahun);
        $this->assertStringStartsWith('BK-', $booking->kode_booking);

        $response->assertRedirect(route('booking.status', $booking->kode_booking));

        // Cek status aset otomatis berubah
        $asset->refresh();
        $this->assertEquals('proses_booking', $asset->status_ketersediaan);
    }

    public function test_booking_tracker_page_finds_record_and_displays_status(): void
    {
        $booking = Booking::first();
        $this->assertNotNull($booking);

        // Search by booking code
        $trackerSearch = $this->get('/status-booking?q=' . $booking->kode_booking);
        $trackerSearch->assertStatus(200);
        $trackerSearch->assertSee($booking->kode_booking);

        // Direct status detail
        $statusDetail = $this->get('/status-booking/' . $booking->kode_booking);
        $statusDetail->assertStatus(200);
        $statusDetail->assertSee($booking->kode_booking);
        $statusDetail->assertSee('Linimasa Progres Permohonan');
        $statusDetail->assertSee('Daftar Berkas Persyaratan Sewa');
    }

    public function test_admin_uptd_can_access_filament_panel_and_records(): void
    {
        $admin = User::where('email', 'admin@pemanfaatan.jabarprov.go.id')->first()
            ?? User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'web')->get('/admin');
        $response->assertStatus(200);

        $bookingList = $this->actingAs($admin, 'web')->get('/admin/bookings');
        $bookingList->assertStatus(200);

        $assetList = $this->actingAs($admin, 'web')->get('/admin/assets');
        $assetList->assertStatus(200);

        $pemanfaatanList = $this->actingAs($admin, 'web')->get('/admin/asset-pemanfaatans');
        $pemanfaatanList->assertStatus(200);
        $pemanfaatanList->assertSee('Pindai Jatuh Tempo');
    }

    public function test_asset_detail_page_contains_permendagri_calculator(): void
    {
        $asset = Asset::where('status_ketersediaan', 'tersedia')->first() ?? Asset::first();
        $this->assertNotNull($asset);

        $response = $this->get('/aset/' . $asset->slug);
        $response->assertStatus(200);
        $response->assertSee('Simulasi Tarif Sewa BMD');
        $response->assertSee('Permendagri No. 7/2024');
        $response->assertSee('Pelaku Usaha Mikro, Kecil', false);
        $response->assertSee('Insentif Afirmatif');
        $response->assertSee('Gunakan Hasil Simulasi pada Formulir');
    }

    public function test_rented_asset_disables_booking_button_and_blocks_submission(): void
    {
        $rentedAsset = Asset::where('status_ketersediaan', 'tersewa')->first();
        $this->assertNotNull($rentedAsset, 'Harus ada aset berstatus tersewa.');

        // 1. Detail aset tersewa tetap dapat diakses dengan sukses
        $detailResponse = $this->get('/aset/' . $rentedAsset->slug);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee($rentedAsset->nama_objek);
        $detailResponse->assertSee('Objek ini Berstatus Tersewa Aktif');
        $detailResponse->assertSee('Aset Sedang Tersewa Aktif');

        // 2. Pada halaman beranda & katalog, aset tersewa menampilkan indikator disabled
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Tersewa');

        $catalogResponse = $this->get('/katalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Detail Aset');

        // 3. Pengajuan booking untuk aset berstatus tersewa diblokir
        $postData = [
            'nama_pemohon' => 'Ujang Rented Test',
            'nomor_whatsapp' => '081999888777',
            'rencana_peruntukan' => 'Uji Coba Booking Aset Tersewa',
            'durasi_sewa_tahun' => 1,
        ];
        $bookingResponse = $this->post('/aset/' . $rentedAsset->slug . '/booking', $postData);
        $bookingResponse->assertSessionHas('error', 'Maaf, aset ini sedang dalam masa sewa aktif.');
    }

    public function test_multi_photo_gallery_and_site_plan_render_on_asset_detail(): void
    {
        $asset = Asset::first();
        $this->assertNotNull($asset);

        // Berikan site plan dan galeri untuk pengujian
        $asset->update([
            'site_plan_url' => 'assets/site-plans/sample-plan.jpg',
            'foto_galeri' => [
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab',
            ],
        ]);

        $response = $this->get('/aset/' . $asset->slug);
        $response->assertStatus(200);
        $response->assertSee('Foto Lapangan');
        $response->assertSee('Denah Tapak / Site Plan');
        $response->assertSee('gallery-thumb-btn', false);
    }

    public function test_kasda_payments_relation_and_tracking_work(): void
    {
        $lease = \App\Models\AssetPemanfaatan::where('kategori_status', 'sewa_aktif')->first();
        $this->assertNotNull($lease);

        $this->assertGreaterThan(0, $lease->payments()->count());
        $this->assertGreaterThan(0, $lease->total_disetor);
        $this->assertGreaterThanOrEqual(0, $lease->persentase_pelunasan);

        $payment = $lease->payments()->where('status_pembayaran', 'lunas')->first();
        $this->assertNotNull($payment);
        $this->assertStringStartsWith('STS-', $payment->nomor_sts);
        $this->assertEquals('Lunas (Disetor Kasda)', $payment->status_label);
    }

    public function test_check_expiring_leases_command_runs_successfully(): void
    {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('bmd:check-expiring-leases');
        $this->assertEquals(0, $exitCode);
    }
}
