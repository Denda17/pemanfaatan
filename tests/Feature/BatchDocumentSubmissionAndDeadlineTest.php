<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BatchDocumentSubmissionAndDeadlineTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_calculate_booking_deadline_precision_5_working_days(): void
    {
        // 1. Senin -> berakhir di Jumat 23:59:59 WIB pada minggu yang sama
        $senin = Carbon::parse('2026-11-23 10:00:00');
        $deadlineSenin = Asset::calculateBookingDeadline($senin);
        $this->assertEquals('Friday', $deadlineSenin->format('l'));
        $this->assertEquals('2026-11-27 23:59:59', $deadlineSenin->format('Y-m-d H:i:s'));

        // 2. Selasa -> berakhir di Senin 23:59:59 WIB minggu berikutnya
        $selasa = Carbon::parse('2026-11-24 14:00:00');
        $deadlineSelasa = Asset::calculateBookingDeadline($selasa);
        $this->assertEquals('Monday', $deadlineSelasa->format('l'));
        $this->assertEquals('2026-11-30 23:59:59', $deadlineSelasa->format('Y-m-d H:i:s'));

        // 3. Sabtu -> dihitung mulai Senin s.d. Jumat minggu berikutnya
        $sabtu = Carbon::parse('2026-11-28 09:00:00');
        $deadlineSabtu = Asset::calculateBookingDeadline($sabtu);
        $this->assertEquals('Friday', $deadlineSabtu->format('l'));
        $this->assertEquals('2026-12-04 23:59:59', $deadlineSabtu->format('Y-m-d H:i:s'));

        // 4. Minggu -> dihitung mulai Senin s.d. Jumat minggu berikutnya
        $minggu = Carbon::parse('2026-11-29 16:30:00');
        $deadlineMinggu = Asset::calculateBookingDeadline($minggu);
        $this->assertEquals('Friday', $deadlineMinggu->format('l'));
        $this->assertEquals('2026-12-04 23:59:59', $deadlineMinggu->format('Y-m-d H:i:s'));
    }

    public function test_batch_upload_all_required_documents_at_once_succeeds(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-BATCH-'.uniqid(),
            'nama_objek' => 'Tanah Komersial Dago',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_deadline_at' => now()->addDays(3),
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-BATCH-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Denda Pengusaha',
            'nomor_whatsapp' => '087777777701',
            'nik_atau_nib' => '3273010101900001',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Kuliner',
            'status' => 'pending',
        ]);

        // Berkas wajib untuk perorangan <= 5 thn
        $requiredCodes = $booking->getRequiredRequirementCodes();
        $this->assertContains('REQ_SURAT_PERMOHONAN', $requiredCodes);
        $this->assertContains('REQ_SURAT_PERNYATAAN', $requiredCodes);
        $this->assertContains('REQ_KTP', $requiredCodes);
        $this->assertContains('REQ_NPWP', $requiredCodes);

        $payload = [
            'documents' => [
                'REQ_SURAT_PERMOHONAN' => UploadedFile::fake()->create('surat_permohonan.pdf', 500, 'application/pdf'),
                'REQ_SURAT_PERNYATAAN' => UploadedFile::fake()->create('surat_pernyataan.pdf', 400, 'application/pdf'),
                'REQ_KTP' => UploadedFile::fake()->create('ktp_scan.pdf', 300, 'application/pdf'),
                'REQ_NPWP' => UploadedFile::fake()->create('npwp_scan.pdf', 300, 'application/pdf'),
            ],
        ];

        $response = $this->actingAs($user)->post(route('booking.upload-all', $booking->kode_booking), $payload);
        $response->assertRedirect(route('booking.status', $booking->kode_booking));
        $response->assertSessionHas('success');

        // Pastikan ke-4 dokumen tersimpan di database
        $this->assertEquals(4, $booking->documents()->count());
        $booking->refresh();
        $this->assertEquals('document_review', $booking->status);
        $this->assertTrue($booking->getCompletenessStats()['is_complete']);
    }

    public function test_batch_upload_fails_if_any_required_document_is_missing(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-BATCH-FAIL-'.uniqid(),
            'nama_objek' => 'Lahan Kosong Sukajadi',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_deadline_at' => now()->addDays(3),
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-BATCH-FAIL-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Denda Pengusaha',
            'nomor_whatsapp' => '087777777702',
            'nik_atau_nib' => '3273010101900002',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Taman Usaha',
            'status' => 'pending',
        ]);

        // Hanya kirim 2 dari 4 berkas wajib (KTP & NPWP terlewat)
        $payload = [
            'documents' => [
                'REQ_SURAT_PERMOHONAN' => UploadedFile::fake()->create('surat_permohonan.pdf', 500, 'application/pdf'),
                'REQ_SURAT_PERNYATAAN' => UploadedFile::fake()->create('surat_pernyataan.pdf', 400, 'application/pdf'),
            ],
        ];

        $response = $this->actingAs($user)->post(route('booking.upload-all', $booking->kode_booking), $payload);
        $response->assertSessionHasErrors(['documents.REQ_KTP', 'documents.REQ_NPWP']);
        $this->assertEquals(0, $booking->documents()->count());
    }

    public function test_batch_upload_blocked_when_5_day_deadline_expired(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-EXP-'.uniqid(),
            'nama_objek' => 'Lahan Kadaluarsa',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_deadline_at' => now()->subDay(), // Deadline kemarin
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-EXP-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Pemohon Terlambat',
            'nomor_whatsapp' => '087777777703',
            'nik_atau_nib' => '3273010101900003',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Parkir',
            'status' => 'pending',
        ]);

        $this->assertTrue($booking->isExpired());

        $payload = [
            'documents' => [
                'REQ_SURAT_PERMOHONAN' => UploadedFile::fake()->create('surat.pdf', 500, 'application/pdf'),
                'REQ_SURAT_PERNYATAAN' => UploadedFile::fake()->create('pernyataan.pdf', 400, 'application/pdf'),
                'REQ_KTP' => UploadedFile::fake()->create('ktp.pdf', 300, 'application/pdf'),
                'REQ_NPWP' => UploadedFile::fake()->create('npwp.pdf', 300, 'application/pdf'),
            ],
        ];

        $response = $this->actingAs($user)->post(route('booking.upload-all', $booking->kode_booking), $payload);
        $response->assertSessionHas('error');
        $this->assertEquals(0, $booking->documents()->count());
    }

    public function test_revision_document_allows_individual_reupload(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-REV-'.uniqid(),
            'nama_objek' => 'Gedung Kesenian',
            'bentuk_objek' => 'Bangunan',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_deadline_at' => now()->addDays(2),
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-REV-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Mitra Kreatif',
            'nomor_whatsapp' => '087777777704',
            'nik_atau_nib' => '3273010101900004',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Seni',
            'status' => 'document_review',
        ]);

        $reqKtp = BookingRequirement::where('kode', 'REQ_KTP')->firstOrFail();

        // Dokumen KTP berstatus revision dengan catatan admin
        $docKtp = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqKtp->id,
            'file_path' => 'old_ktp.jpg',
            'status_verifikasi' => 'revision',
            'catatan' => 'Scan KTP buram dan tidak terbaca jelas',
        ]);

        $this->assertTrue($booking->hasRevisionDocuments());

        // Pemohon mengunggah ulang hanya file KTP yang diminta revisi
        $response = $this->actingAs($user)->post(route('booking.upload-dokumen', $booking->kode_booking), [
            'booking_requirement_id' => $reqKtp->id,
            'dokumen_file' => UploadedFile::fake()->create('ktp_baru_jelas.pdf', 350, 'application/pdf'),
        ]);

        $response->assertRedirect();
        $docKtp->refresh();
        $this->assertEquals('pending', $docKtp->status_verifikasi);
        $this->assertNull($docKtp->catatan);
        $this->assertEquals('ktp_baru_jelas.pdf', $docKtp->file_name);
    }

    public function test_warning_box_is_displayed_on_booking_status_page(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-WARN-'.uniqid(),
            'nama_objek' => 'Tanah Komersial Dago Warning',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_deadline_at' => now()->addDays(3),
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-WARN-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Peminat Cek Warning',
            'nomor_whatsapp' => '087777777709',
            'nik_atau_nib' => '3273010101900009',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Usaha',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('booking.status', $booking->kode_booking));
        $response->assertOk();
        $response->assertSee('Ketentuan Penting Batas Waktu & Seleksi Peminat');
        $response->assertSee('Batas Waktu Mutlak');
        $response->assertSee('Gugur Otomatis & Terbuka Lagi');
        $response->assertSee('Beauty Contest');
    }

    public function test_all_incomplete_bookings_expire_and_asset_reverts_to_tersedia(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-EXP-'.uniqid(),
            'nama_objek' => 'Lahan Kosong Braga',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now()->subDays(6),
            'booking_deadline_at' => now()->subDay(), // Deadline terlewati
        ]);

        $booking1 = Booking::create([
            'kode_booking' => 'BKG-EXP1-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => 'Peminat Telat 1',
            'nomor_whatsapp' => '087777777711',
            'status' => 'pending',
        ]);

        $booking2 = Booking::create([
            'kode_booking' => 'BKG-EXP2-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => 'Peminat Telat 2',
            'nomor_whatsapp' => '087777777712',
            'status' => 'pending',
        ]);

        // Evaluasi batas waktu
        Booking::evaluateDeadlineForAsset($asset);

        $booking1->refresh();
        $booking2->refresh();
        $asset->refresh();

        $this->assertEquals('expired', $booking1->status);
        $this->assertEquals('expired', $booking2->status);
        $this->assertEquals('tersedia', $asset->status_ketersediaan);
        $this->assertNull($asset->booking_deadline_at);
        $this->assertNull($asset->booking_opened_at);
    }

    public function test_single_complete_booking_is_selected_while_incomplete_expires(): void
    {
        $userWinner = User::factory()->create();
        $userTelat = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-WIN-'.uniqid(),
            'nama_objek' => 'Ruko Dago Juara',
            'bentuk_objek' => 'Tanah dan bangunan',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now()->subDays(6),
            'booking_deadline_at' => now()->subHour(),
        ]);

        $bookingWinner = Booking::create([
            'kode_booking' => 'BKG-WIN-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $userWinner->id,
            'nama_pemohon' => 'Mitra Teladan',
            'nomor_whatsapp' => '087777777721',
            'status' => 'pending',
        ]);

        // Lengkapi seluruh berkas wajib untuk $bookingWinner
        $reqs = BookingRequirement::whereIn('kode', $bookingWinner->getRequiredRequirementCodes())->get();
        foreach ($reqs as $req) {
            BookingDocument::create([
                'booking_id' => $bookingWinner->id,
                'booking_requirement_id' => $req->id,
                'file_path' => 'doc_'.$req->kode.'.pdf',
                'status_verifikasi' => 'pending',
            ]);
        }
        $this->assertTrue($bookingWinner->getCompletenessStats()['is_complete']);

        $bookingTelat = Booking::create([
            'kode_booking' => 'BKG-LOSE-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $userTelat->id,
            'nama_pemohon' => 'Mitra Belum Lengkap',
            'nomor_whatsapp' => '087777777722',
            'status' => 'pending',
        ]);

        // Evaluasi
        Booking::evaluateDeadlineForAsset($asset);

        $bookingWinner->refresh();
        $bookingTelat->refresh();
        $asset->refresh();

        $this->assertEquals('document_review', $bookingWinner->status);
        $this->assertEquals('expired', $bookingTelat->status);
        $this->assertEquals('proses_booking', $asset->status_ketersediaan);
    }

    public function test_multiple_complete_bookings_enter_beauty_contest(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-BC-'.uniqid(),
            'nama_objek' => 'Kawasan Kuliner Riau',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now()->subDays(6),
            'booking_deadline_at' => now()->subMinutes(10),
        ]);

        $b1 = Booking::create([
            'kode_booking' => 'BKG-BC1-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => 'Kandidat A',
            'nomor_whatsapp' => '087777777731',
            'status' => 'pending',
        ]);

        $b2 = Booking::create([
            'kode_booking' => 'BKG-BC2-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => 'Kandidat B',
            'nomor_whatsapp' => '087777777732',
            'status' => 'pending',
        ]);

        // Kedua peminat sama-sama melengkapi berkas tepat waktu
        foreach ([$b1, $b2] as $b) {
            foreach (BookingRequirement::whereIn('kode', $b->getRequiredRequirementCodes())->get() as $req) {
                BookingDocument::create([
                    'booking_id' => $b->id,
                    'booking_requirement_id' => $req->id,
                    'file_path' => 'dokumen_'.$req->kode.'.pdf',
                    'status_verifikasi' => 'pending',
                ]);
            }
            $this->assertTrue($b->getCompletenessStats()['is_complete']);
        }

        // Jalankan evaluasi batas waktu
        Booking::evaluateDeadlineForAsset($asset);

        $b1->refresh();
        $b2->refresh();

        $this->assertEquals('beauty_contest', $b1->status);
        $this->assertEquals('beauty_contest', $b2->status);

        // Halaman status peminat menampilkan banner Beauty Contest
        $resp = $this->actingAs($user1)->get(route('booking.status', $b1->kode_booking));
        $resp->assertOk();
        $resp->assertSee('Tahap Beauty Contest');
        $resp->assertSee('Seleksi Terbuka Pemanfaatan BMD');
    }

    public function test_check_booking_deadlines_command_evaluates_correctly(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-CMD-'.uniqid(),
            'nama_objek' => 'Tanah Sukajadi CMD',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now()->subDays(6),
            'booking_deadline_at' => now()->subHour(),
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-CMD-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Peminat Telat CMD',
            'nomor_whatsapp' => '087777777741',
            'status' => 'pending',
        ]);

        $this->artisan('bmd:check-booking-deadlines')
            ->assertExitCode(0);

        $booking->refresh();
        $asset->refresh();

        $this->assertEquals('expired', $booking->status);
        $this->assertEquals('tersedia', $asset->status_ketersediaan);
    }

    public function test_schedule_beauty_contest_updates_all_competing_bookings(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-SCHED-'.uniqid(),
            'nama_objek' => 'Tanah Komersial Pasteur',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
        ]);

        $b1 = Booking::create([
            'kode_booking' => 'BKG-SCHED1-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => 'Kandidat Pasteur 1',
            'nomor_whatsapp' => '087777777751',
            'status' => 'beauty_contest',
        ]);

        $b2 = Booking::create([
            'kode_booking' => 'BKG-SCHED2-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => 'Kandidat Pasteur 2',
            'nomor_whatsapp' => '087777777752',
            'status' => 'beauty_contest',
        ]);

        $scheduleTime = Carbon::parse('2026-12-03 09:30:00');
        $location = 'Ruang Rapat UPTD Lt. 2';
        $notes = 'Bawa laptop dan draf proposal cetak';

        $updatedCount = $b1->scheduleBeautyContest($scheduleTime, $location, $notes);
        $this->assertEquals(2, $updatedCount);

        $b1->refresh();
        $b2->refresh();

        $this->assertEquals('scheduled', $b1->beauty_contest_status);
        $this->assertEquals('scheduled', $b2->beauty_contest_status);
        $this->assertEquals($location, $b2->beauty_contest_location);
        $this->assertEquals($notes, $b2->beauty_contest_notes);
        $this->assertStringContainsString('03 Desember 2026', $b1->formatted_beauty_contest_schedule);
        $this->assertStringContainsString('wa.me', $b1->beauty_contest_whatsapp_url);

        // Halaman pemohon menampilkan jadwal resmi
        $resp = $this->actingAs($user1)->get(route('booking.status', $b1->kode_booking));
        $resp->assertOk();
        $resp->assertSee('JADWAL RESMI SESI PEMAPARAN (BEAUTY CONTEST)');
        $resp->assertSee('Ruang Rapat UPTD Lt. 2');
        $resp->assertSee('Bawa laptop dan draf proposal cetak');
    }

    public function test_award_beauty_contest_winner_promotes_winner_and_rejects_competitors(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-AWD-'.uniqid(),
            'nama_objek' => 'Gedung Bioskop Antik',
            'bentuk_objek' => 'Bangunan',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
        ]);

        $winner = Booking::create([
            'kode_booking' => 'BKG-AWD1-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => 'Mitra Terpilih',
            'nomor_whatsapp' => '087777777761',
            'status' => 'beauty_contest',
        ]);

        $loser = Booking::create([
            'kode_booking' => 'BKG-AWD2-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => 'Mitra Belum Terpilih',
            'nomor_whatsapp' => '087777777762',
            'status' => 'beauty_contest',
        ]);

        $winner->awardBeautyContestWinner('berita_acara_seleksi_final.pdf', 'Skor bisnis 95/100');

        $winner->refresh();
        $loser->refresh();

        $this->assertEquals('document_review', $winner->status);
        $this->assertTrue($winner->is_beauty_contest_winner);
        $this->assertEquals('completed', $winner->beauty_contest_status);
        $this->assertEquals('berita_acara_seleksi_final.pdf', $winner->beauty_contest_berita_acara_path);

        $this->assertEquals('rejected', $loser->status);
        $this->assertFalse($loser->is_beauty_contest_winner);
        $this->assertEquals('completed', $loser->beauty_contest_status);
        $this->assertStringContainsString('Tidak terpilih dalam seleksi Beauty Contest', $loser->alasan_penolakan);
    }

    public function test_applicant_can_only_view_approved_admin_documents(): void
    {
        $applicant = User::factory()->create(['role' => 'peminat']);
        $admin = User::factory()->create(['role' => 'admin']);

        $asset = Asset::create([
            'kode_aset' => 'AST-DOC-'.uniqid(),
            'nama_objek' => 'Tanah Dokumen Terbuka',
            'bentuk_objek' => 'Tanah',
            'kabupaten_kota' => 'Kota Bandung',
            'status_ketersediaan' => 'proses_booking',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-DOC-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $applicant->id,
            'nama_pemohon' => 'Peminat Dokumen',
            'nomor_whatsapp' => '087777777788',
            'status' => 'document_review',
        ]);

        // Siapkan file di disk fake
        Storage::disk('local')->put('booking-documents/ba_negosiasi.pdf', 'dummy-ba');
        Storage::disk('local')->put('booking-documents/laporan_penilaian.pdf', 'dummy-penilaian');

        $reqBa = BookingRequirement::where('kode', 'REQ_BA_NEGOSIASI')->firstOrFail();
        $reqPenilaian = BookingRequirement::where('kode', 'REQ_LAPORAN_PENILAIAN')->firstOrFail();

        $docBa = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqBa->id,
            'file_path' => 'booking-documents/ba_negosiasi.pdf',
            'file_name' => 'ba_negosiasi.pdf',
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'verified',
        ]);

        $docPenilaian = BookingDocument::create([
            'booking_id' => $booking->id,
            'booking_requirement_id' => $reqPenilaian->id,
            'file_path' => 'booking-documents/laporan_penilaian.pdf',
            'file_name' => 'laporan_penilaian.pdf',
            'file_type' => 'application/pdf',
            'status_verifikasi' => 'verified',
        ]);

        // 1. Peminat BISA melihat & mengunduh BA Negosiasi
        $respBa = $this->actingAs($applicant)->get(route('booking.documents.view', $docBa));
        $respBa->assertOk();

        // 2. Peminat DITOLAK (403 Forbidden) saat mencoba mengakses Laporan Penilaian
        $respPenilaian = $this->actingAs($applicant)->get(route('booking.documents.view', $docPenilaian));
        $respPenilaian->assertForbidden();

        // 3. Admin BISA mengakses kedua dokumen
        $respAdminBa = $this->actingAs($admin)->get(route('booking.documents.view', $docBa));
        $respAdminBa->assertOk();
        $respAdminPenilaian = $this->actingAs($admin)->get(route('booking.documents.view', $docPenilaian));
        $respAdminPenilaian->assertOk();

        // 4. Di halaman status booking peminat:
        // - BA Negosiasi memiliki link "Lihat Berkas Scan"
        // - Laporan Penilaian bertuliskan "Dokumen Arsip Internal Kedinasan" tanpa link unduh/nama file
        $respPage = $this->actingAs($applicant)->get(route('booking.status', $booking->kode_booking));
        $respPage->assertOk();
        $respPage->assertSee('Diinput Admin UPTD (Dapat Diunduh)');
        $respPage->assertSee('Diinput Admin UPTD (Arsip Internal)');
        $respPage->assertSee('Dokumen Arsip Internal Kedinasan (Selesai Diproses UPTD)');
        $respPage->assertSee('Lihat Berkas Scan');
        $respPage->assertDontSee('Laporan_Penilaian_Internal.pdf');
    }
}
