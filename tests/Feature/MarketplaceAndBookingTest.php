<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $user = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
        ]);

        $postData = [
            'nama_pemohon' => 'Dedi Koswara',
            'nomor_whatsapp' => '08122334455',
            'nik_atau_nib' => '3273101010880002',
            'nama_instansi' => 'CV Kreatif Sinergi Jabar',
            'email' => 'dedi@kreatifjabar.com',
            'rencana_peruntukan' => 'Pusat Inkubasi Bisnis & Coffee Shop Komunitas',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'bertahap',
        ];

        // Tamu tanpa login dialihkan ke halaman login
        $guestResponse = $this->post('/aset/' . $asset->slug . '/booking', $postData);
        $guestResponse->assertRedirect(route('login'));

        // Pengguna terautentikasi berhasil mengirim pengajuan booking
        $response = $this->actingAs($user, 'web')->post('/aset/' . $asset->slug . '/booking', $postData);

        $booking = Booking::where('nomor_whatsapp', '08122334455')->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals($user->id, $booking->user_id);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(2, $booking->durasi_sewa_tahun);
        $this->assertEquals('bertahap', $booking->skema_pembayaran);
        $this->assertStringStartsWith('BK-', $booking->kode_booking);

        $response->assertRedirect(route('booking.status', $booking->kode_booking));

        // Cek status aset otomatis berubah
        $asset->refresh();
        $this->assertEquals('proses_booking', $asset->status_ketersediaan);
    }

    public function test_booking_more_than_five_years_requires_business_proposal(): void
    {
        Storage::fake('public');

        $asset = Asset::where('status_ketersediaan', 'tersedia')->first() ?? Asset::first();
        $asset->update(['status_ketersediaan' => 'tersedia']);

        $user = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
        ]);

        // 1. Durasi > 5 tahun tanpa proposal bisnis ditolak validasi
        $failResponse = $this->actingAs($user, 'web')->post('/aset/' . $asset->slug . '/booking', [
            'nama_pemohon' => 'Budi Sudarsono',
            'nomor_whatsapp' => '081399887766',
            'rencana_peruntukan' => 'Pusat Distribusi Logistik Regional',
            'durasi_sewa_tahun' => 10,
            'skema_pembayaran' => 'sekaligus',
        ]);
        $failResponse->assertSessionHasErrors('proposal_bisnis');

        // Durasi sewa > 10 tahun ditolak validasi batas maksimal
        $overLimitResponse = $this->actingAs($user, 'web')->post('/aset/' . $asset->slug . '/booking', [
            'nama_pemohon' => 'Budi Sudarsono',
            'nomor_whatsapp' => '081399887766',
            'rencana_peruntukan' => 'Pusat Distribusi Logistik Regional',
            'durasi_sewa_tahun' => 11,
            'skema_pembayaran' => 'sekaligus',
        ]);
        $overLimitResponse->assertSessionHasErrors('durasi_sewa_tahun');

        // 2. Durasi > 5 tahun dengan melampirkan proposal bisnis diterima
        $file = UploadedFile::fake()->create('proposal_rencana_bisnis.pdf', 1024, 'application/pdf');

        $successResponse = $this->actingAs($user, 'web')->post('/aset/' . $asset->slug . '/booking', [
            'nama_pemohon' => 'Budi Sudarsono',
            'nomor_whatsapp' => '081399887766',
            'rencana_peruntukan' => 'Pusat Distribusi Logistik Regional',
            'durasi_sewa_tahun' => 10,
            'skema_pembayaran' => 'sekaligus',
            'proposal_bisnis' => $file,
        ]);

        $booking = Booking::where('nomor_whatsapp', '081399887766')->latest()->first();
        $this->assertNotNull($booking);
        $this->assertEquals(10, $booking->durasi_sewa_tahun);
        $this->assertEquals('sekaligus', $booking->skema_pembayaran);
        $this->assertNotNull($booking->proposal_bisnis_path);
        Storage::disk('public')->assertExists($booking->proposal_bisnis_path);

        // Dokumen proposal juga otomatis masuk ke tabel booking_documents
        $proposalReq = BookingRequirement::where('kode', 'REQ_PROPOSAL')->first();
        $this->assertDatabaseHas('booking_documents', [
            'booking_id' => $booking->id,
            'booking_requirement_id' => $proposalReq->id,
            'file_name' => 'proposal_rencana_bisnis.pdf',
        ]);

        $successResponse->assertRedirect(route('booking.status', $booking->kode_booking));
    }

    public function test_my_bookings_dashboard_and_booking_status_authorization(): void
    {
        $applicant1 = User::factory()->create([
            'name' => 'Pemohon Pertama',
            'email' => 'pemohon1_' . uniqid() . '@example.com',
            'phone' => '081111222333',
            'role' => 'peminat',
            'is_active' => true,
        ]);

        $applicant2 = User::factory()->create([
            'name' => 'Pemohon Kedua',
            'email' => 'pemohon2_' . uniqid() . '@example.com',
            'phone' => '082222333444',
            'role' => 'peminat',
            'is_active' => true,
        ]);

        $asset = Asset::where('status_ketersediaan', 'tersedia')->first() ?? Asset::first();

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $applicant1->id,
            'nama_pemohon' => $applicant1->name,
            'email' => $applicant1->email,
            'nomor_whatsapp' => $applicant1->phone,
            'rencana_peruntukan' => 'Outlet Kuliner dan Co-working Space',
            'durasi_sewa_tahun' => 2,
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        // 1. Tamu tanpa login dialihkan ke halaman login saat akses /pengajuan-saya
        $guestResponse = $this->get('/pengajuan-saya');
        $guestResponse->assertRedirect(route('login'));

        // 2. Pemohon 1 login dan melihat daftar permohonannya
        $myBookingsResponse = $this->actingAs($applicant1, 'web')->get('/pengajuan-saya');
        $myBookingsResponse->assertStatus(200);
        $myBookingsResponse->assertSee('Daftar Pengajuan Sewa Saya');
        $myBookingsResponse->assertSee($booking->kode_booking);
        $myBookingsResponse->assertSee($asset->nama_objek);
        $myBookingsResponse->assertSee('Menunggu Tindak Lanjut');

        // 3. Pemohon 1 dapat melihat detail status pengajuannya sendiri
        $statusDetail = $this->actingAs($applicant1, 'web')->get('/status-booking/' . $booking->kode_booking);
        $statusDetail->assertStatus(200);
        $statusDetail->assertSee($booking->kode_booking);
        $statusDetail->assertSee('Linimasa Progres Permohonan');
        $statusDetail->assertSee('Daftar Berkas Persyaratan Sewa');

        // 4. Pemohon 2 TIDAK BISA mengakses status booking milik Pemohon 1 (403 Forbidden)
        $forbiddenResponse = $this->actingAs($applicant2, 'web')->get('/status-booking/' . $booking->kode_booking);
        $forbiddenResponse->assertStatus(403);

        // 5. Admin UPTD dapat mengakses status booking pemohon manapun
        $admin = User::where('role', 'admin')->first();
        $adminResponse = $this->actingAs($admin, 'web')->get('/status-booking/' . $booking->kode_booking);
        $adminResponse->assertStatus(200);
        $adminResponse->assertSee($booking->kode_booking);

        // 6. Rute legacy /status-booking mengalihkan user login ke /pengajuan-saya
        $redirectResponse = $this->actingAs($applicant1, 'web')->get('/status-booking');
        $redirectResponse->assertRedirect(route('booking.my'));
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

        $regulationList = $this->actingAs($admin, 'web')->get('/admin/regulations');
        $regulationList->assertStatus(200);
        $regulationList->assertSee('Dasar Hukum & SOP');
    }

    public function test_asset_detail_page_contains_permendagri_calculator(): void
    {
        $asset = Asset::where('status_ketersediaan', 'tersedia')->first();
        if (!$asset) {
            $asset = Asset::first();
            $asset->update(['status_ketersediaan' => 'tersedia']);
        }
        $this->assertNotNull($asset);

        // 1. Publik / Tamu melihat simulasi tarif tanpa diskon kategori dan tanpa teks tarif penuh 100%
        $response = $this->get('/aset/' . $asset->slug);
        $response->assertStatus(200);
        $response->assertSee('Simulasi Tarif Sewa BMD');
        $response->assertSee('Permendagri No. 7/2024');
        $response->assertDontSee('Tarif Penuh 100%');
        $response->assertSee('Durasi Sewa');
        $response->assertSee('Gunakan Hasil Simulasi pada Formulir');
        $response->assertDontSee('Pelaku Usaha Mikro, Kecil');
        $response->assertDontSee('Insentif Afirmatif');

        // 2. Pemohon terautentikasi dapat melihat formulir booking lengkap dengan skema pembayaran dan proposal bisnis
        $user = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
        ]);

        $authResponse = $this->actingAs($user, 'web')->get('/aset/' . $asset->slug);
        $authResponse->assertStatus(200);
        $authResponse->assertSee('Formulir Booking Minat Sewa');
        $authResponse->assertSee('Skema Pembayaran');
        $authResponse->assertSee('Dibayar Full Sekaligus');
        $authResponse->assertSee('Pembayaran Bertahap');
        $authResponse->assertSee('Dokumen Proposal Bisnis');
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
        $user = User::factory()->create(['role' => 'peminat']);
        $postData = [
            'nama_pemohon' => 'Ujang Rented Test',
            'nomor_whatsapp' => '081999888777',
            'rencana_peruntukan' => 'Uji Coba Booking Aset Tersewa',
            'durasi_sewa_tahun' => 1,
        ];
        $bookingResponse = $this->actingAs($user, 'web')->post('/aset/' . $rentedAsset->slug . '/booking', $postData);
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

    public function test_dasar_hukum_page_loads_with_all_regulations_and_filters(): void
    {
        // 1. Indeks regulasi
        $response = $this->get('/dasar-hukum');
        $response->assertStatus(200);
        $response->assertSee('Dasar Hukum & Regulasi Pemanfaatan BMD');
        $response->assertSee('Nomor 3 Tahun 2026');
        $response->assertSee('Nomor 20 Tahun 2021');
        $response->assertSee('Permendagri');
        $response->assertSee('Nomor 28 Tahun 2020');
        $response->assertSee('Peraturan Pemerintah (PP)');

        // 2. Filter kategori daerah
        $filterResponse = $this->get('/dasar-hukum?kategori=daerah');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee('Nomor 3 Tahun 2026');
        $filterResponse->assertDontSee('Permendagri No 7 Tahun 2024');

        // 3. Pencarian kata kunci
        $searchResponse = $this->get('/dasar-hukum?q=insentif');
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee('Permendagri');
    }

    public function test_regulation_preview_and_download_stream_pdf(): void
    {
        // 1. Inline preview
        $preview = $this->get('/dasar-hukum/preview/perda-jabar-3-2026');
        $preview->assertStatus(200);
        $preview->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline', (string) $preview->headers->get('Content-Disposition'));

        // 2. Unduh PDF
        $download = $this->get('/dasar-hukum/unduh/perda-jabar-3-2026');
        $download->assertStatus(200);
        $download->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment', (string) $download->headers->get('Content-Disposition'));

        // 3. Non-existent slug returns 404
        $invalid = $this->get('/dasar-hukum/preview/slug-tidak-valid');
        $invalid->assertStatus(404);
    }

    public function test_admin_can_create_and_publish_sop_document(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // Akses halaman tambah regulasi di Filament
        $createPage = $this->actingAs($admin, 'web')->get('/admin/regulations/create');
        $createPage->assertStatus(200);

        // Buat record SOP baru
        $sop = \App\Models\Regulation::updateOrCreate(
            ['slug' => 'sop-survei-lapangan-2026'],
            [
                'nomor' => 'SOP/05/UPTD/2026',
                'tahun' => 2026,
                'jenis' => 'Standar Operasional Prosedur (SOP)',
                'tingkat' => 'sop',
                'penerbit' => 'UPTD Pengamanan dan Pemanfaatan Aset Jabar',
                'judul' => 'SOP Pelaksanaan Survei Lapangan dan Verifikasi Fisik Aset Sewa',
                'deskripsi' => 'Panduan alur petugas lapangan dan peminat sewa dalam pengecekan fisik tanah dan bangunan.',
                'poin_kunci' => [
                    'Waktu survei maksimal 3 hari kerja setelah pengajuan booking',
                    'Berita Acara Peninjauan Lapangan (BAPL) ditandatangani kedua pihak',
                ],
                'file_path' => 'regulasi-docs/Perda Provinsi Jawa Barat Nomor 3 Tahun 2026.pdf',
                'disk' => 'public',
                'download_name' => 'SOP-Survei-Lapangan-UPTD-2026.pdf',
                'badge_color' => 'amber',
                'urutan' => 7,
                'is_active' => true,
            ]
        );

        // Pastikan tampil di portal publik kategori SOP
        $publicSop = $this->get('/dasar-hukum?kategori=sop');
        $publicSop->assertStatus(200);
        $publicSop->assertSee('SOP/05/UPTD/2026');
        $publicSop->assertSee('SOP Pelaksanaan Survei Lapangan');
        $publicSop->assertSee('Internal SOP');

        // Pastikan dokumen dapat dipratinjau
        $sopPreview = $this->get('/dasar-hukum/preview/' . $sop->slug);
        $sopPreview->assertStatus(200);
        $sopPreview->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_user_registration_requires_login_and_does_not_auto_login(): void
    {
        $regResponse = $this->get('/daftar');
        $regResponse->assertStatus(200);
        $regResponse->assertSee('Buat Akun Portal Pemanfaatan');
        $regResponse->assertSee('Perorangan / Pribadi');
        $regResponse->assertSee('Pernyataan Keabsahan Data', false);
        $regResponse->assertSee('disabled', false);

        $email = 'ahmad_' . uniqid() . '@ptpasundan.co.id';
        $registerData = [
            'tipe_pemohon' => 'badan_usaha',
            'name' => 'Ahmad Subagja',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '081298765432',
            'instansi' => 'PT Pasundan Investama Mandiri',
            'nib' => '9120001234567',
            'npwp' => '01.234.567.8-423.000',
            'alamat' => 'Jl. Asia Afrika No. 100, Bandung',
        ];

        // 1. Tanpa ceklis pernyataan keabsahan, registrasi ditolak
        $invalidResponse = $this->post('/daftar', $registerData);
        $invalidResponse->assertSessionHasErrors('agree');

        // 2. Dengan ceklis keabsahan disetujui, pendaftaran berhasil
        $registerData['agree'] = '1';
        $postResponse = $this->post('/daftar', $registerData);

        // Harus dialihkan ke halaman login, BUKAN langsung login
        $postResponse->assertRedirect(route('login'));
        $postResponse->assertSessionHas('success');
        $this->assertFalse(\Illuminate\Support\Facades\Auth::check(), 'User tidak boleh langsung login otomatis setelah mendaftar.');

        // 3. Data tersimpan di database dengan role peminat
        $createdUser = User::where('email', $email)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('peminat', $createdUser->role);
        $this->assertEquals('badan_usaha', $createdUser->tipe_pemohon);
        $this->assertEquals('PT Pasundan Investama Mandiri', $createdUser->instansi);

        // 4. User pemohon login manual di formulir terpadu -> diarahkan ke Pengajuan Saya
        $loginPage = $this->get('/masuk');
        $loginPage->assertStatus(200);
        $loginPage->assertSee('Masuk ke Akun Anda');
        $loginPage->assertSee('Portal Masuk Terpadu');

        $loginResponse = $this->post('/masuk', [
            'email' => $email,
            'password' => 'password123',
        ]);
        $loginResponse->assertRedirect(route('booking.my'));
        $this->assertTrue(\Illuminate\Support\Facades\Auth::check());
        $this->assertEquals($createdUser->id, \Illuminate\Support\Facades\Auth::id());

        // 5. Logout
        $logoutResponse = $this->post('/keluar');
        $logoutResponse->assertRedirect(route('home'));
        $this->assertFalse(\Illuminate\Support\Facades\Auth::check());
    }

    public function test_inactive_user_cannot_login(): void
    {
        $email = 'suspended_' . uniqid() . '@mitra.com';
        $inactiveUser = User::factory()->create([
            'email' => $email,
            'password' => bcrypt('password123'),
            'is_active' => false,
            'role' => 'peminat',
        ]);

        $loginResponse = $this->post('/masuk', [
            'email' => $email,
            'password' => 'password123',
        ]);

        $loginResponse->assertSessionHasErrors('email');
        $this->assertFalse(\Illuminate\Support\Facades\Auth::check());
    }

    public function test_admin_can_access_user_management_in_filament(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $response = $this->actingAs($admin, 'web')->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Manajemen Pengguna');

        $createResponse = $this->actingAs($admin, 'web')->get('/admin/users/create');
        $createResponse->assertStatus(200);
    }

    public function test_unified_login_redirects_admin_to_dashboard_and_regular_user_to_my_bookings(): void
    {
        // 1. Akses URL legacy admin login otomatis redirect ke portal login terpadu
        $adminLoginRedirect = $this->get('/admin/login');
        $adminLoginRedirect->assertRedirect(route('login'));

        // 2. Admin login via formulir terpadu -> diarahkan ke dashboard Filament /admin
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        $adminPost = $this->post('/masuk', [
            'email' => $admin->email,
            'password' => 'password',
        ]);
        $adminPost->assertRedirect('/admin');
        $this->assertEquals($admin->id, \Illuminate\Support\Facades\Auth::id());

        // Saat admin sedang login dan membuka /masuk, otomatis dilempar ke /admin
        $alreadyLoggedInAdmin = $this->get('/masuk');
        $alreadyLoggedInAdmin->assertRedirect('/admin');

        // Logout
        $this->post('/keluar');
        $this->assertFalse(\Illuminate\Support\Facades\Auth::check());

        // 3. Pemohon login via formulir terpadu -> diarahkan ke Pengajuan Saya
        $peminat = User::factory()->create([
            'role' => 'peminat',
            'is_active' => true,
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
        ]);

        $peminatPost = $this->post('/masuk', [
            'email' => $peminat->email,
            'password' => 'password',
        ]);
        $peminatPost->assertRedirect(route('booking.my'));

        // Saat pemohon sedang login dan membuka /masuk, otomatis dilempar ke Pengajuan Saya
        $alreadyLoggedInPeminat = $this->get('/masuk');
        $alreadyLoggedInPeminat->assertRedirect(route('booking.my'));
    }

    public function test_booking_form_adapts_for_individual_vs_business_applicant(): void
    {
        $asset = Asset::where('status_ketersediaan', 'tersedia')->first() ?? Asset::first();

        // 1. Pemohon perorangan: tidak melihat input teks 'Nama Badan Usaha / Perusahaan', kolom instansi otomatis hidden 'Perorangan'
        $individual = User::factory()->create([
            'tipe_pemohon' => 'perorangan',
            'role' => 'peminat',
            'nik' => '3273112233445566',
        ]);

        $resIndiv = $this->actingAs($individual, 'web')->get('/aset/' . $asset->slug);
        $resIndiv->assertStatus(200);
        $resIndiv->assertSee('Perorangan');
        $resIndiv->assertSee('NIK Pemohon (KTP)');
        $resIndiv->assertDontSee('Nama Badan Usaha / Perusahaan');
        $resIndiv->assertSee('value="Perorangan"', false);

        // 2. Pemohon badan usaha: melihat input 'Nama Badan Usaha / Perusahaan' dan label NIB
        $business = User::factory()->create([
            'tipe_pemohon' => 'badan_usaha',
            'role' => 'peminat',
            'instansi' => 'PT Harapan Bangsa Mandiri',
            'nib' => '0129876543210',
        ]);

        $resBiz = $this->actingAs($business, 'web')->get('/aset/' . $asset->slug);
        $resBiz->assertStatus(200);
        $resBiz->assertSee('Badan Usaha');
        $resBiz->assertSee('NIB Perusahaan');
        $resBiz->assertSee('Nama Badan Usaha / Perusahaan');
        $resBiz->assertSee('PT Harapan Bangsa Mandiri');
    }

    public function test_applicant_can_download_surat_permohonan_draft_autofill_and_raw(): void
    {
        $asset = Asset::first();
        $applicant = User::factory()->create(['tipe_pemohon' => 'perorangan', 'role' => 'peminat']);
        $stranger = User::factory()->create(['tipe_pemohon' => 'perorangan', 'role' => 'peminat']);
        $admin = User::where('role', 'admin')->first();

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $applicant->id,
            'nama_pemohon' => $applicant->name,
            'nik_atau_nib' => '3201010101900001',
            'nama_instansi' => 'Perorangan',
            'nomor_whatsapp' => '081299998888',
            'email' => $applicant->email,
            'rencana_peruntukan' => 'Sentra Kuliner Kreatif',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        // 1. Pemohon berhak mengunduh draf terisi otomatis (.docx)
        $downloadRes = $this->actingAs($applicant, 'web')->get('/status-booking/' . $booking->kode_booking . '/surat-permohonan');
        $downloadRes->assertStatus(200);
        $this->assertStringContainsString('Surat_Permohonan_Sewa_', (string) $downloadRes->headers->get('Content-Disposition'));

        // 2. Pemohon dapat mengunduh mode template mentah (?mode=raw)
        $rawRes = $this->actingAs($applicant, 'web')->get('/status-booking/' . $booking->kode_booking . '/surat-permohonan?mode=raw');
        $rawRes->assertStatus(200);
        $this->assertStringContainsString('DRAFT_Surat_Permohonan_Sewa_BMD_2026.docx', (string) $rawRes->headers->get('Content-Disposition'));

        // 3. User asing dilarang mengunduh dokumen permohonan orang lain (403)
        $forbiddenRes = $this->actingAs($stranger, 'web')->get('/status-booking/' . $booking->kode_booking . '/surat-permohonan');
        $forbiddenRes->assertStatus(403);

        // 4. Admin UPTD diperbolehkan mengunduh dokumen pemohon manapun
        $adminRes = $this->actingAs($admin, 'web')->get('/status-booking/' . $booking->kode_booking . '/surat-permohonan');
        $adminRes->assertStatus(200);
    }

    public function test_applicant_can_upload_scanned_documents_and_status_becomes_pending(): void
    {
        Storage::fake('public');

        $asset = Asset::first();
        $applicant = User::factory()->create(['tipe_pemohon' => 'perorangan', 'role' => 'peminat']);
        $reqSurat = BookingRequirement::where('kode', 'REQ_SURAT_PERMOHONAN')->first();

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $applicant->id,
            'nama_pemohon' => $applicant->name,
            'nik_atau_nib' => '3201010101900001',
            'nama_instansi' => 'Perorangan',
            'nomor_whatsapp' => '081299998888',
            'email' => $applicant->email,
            'rencana_peruntukan' => 'Kafe Literasi',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'bertahap',
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        $file = UploadedFile::fake()->create('surat_permohonan_bertandatangan.pdf', 800, 'application/pdf');

        $uploadResponse = $this->actingAs($applicant, 'web')->post('/status-booking/' . $booking->kode_booking . '/upload-dokumen', [
            'booking_requirement_id' => $reqSurat->id,
            'dokumen_file' => $file,
        ]);

        $uploadResponse->assertSessionHas('success');

        $this->assertDatabaseHas('booking_documents', [
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqSurat->id,
            'file_name' => 'surat_permohonan_bertandatangan.pdf',
            'status_verifikasi' => 'pending',
        ]);
    }
}
