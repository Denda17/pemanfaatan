<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BeautyContestVisualStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_winner_status_page_displays_dedicated_celebration_banner_and_notes(): void
    {
        $asset = Asset::factory()->create([
            'nama_objek' => 'Ruko Braga Blok B-2',
            'status_ketersediaan' => 'tersedia',
        ]);

        $winner = User::factory()->create([
            'role' => 'user',
            'name' => 'CV Berkah Sinergi Jabar',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BMD-TEST-WIN-01',
            'asset_id' => $asset->id,
            'user_id' => $winner->id,
            'nama_pemohon' => $winner->name,
            'nomor_whatsapp' => '081234567891',
            'email' => $winner->email,
            'tipe_pemohon' => 'perusahaan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'document_review',
            'is_beauty_contest_winner' => true,
            'beauty_contest_status' => 'completed',
            'catatan_admin' => 'Ditetapkan sebagai pemenang Beauty Contest. Selamat anda telah menjadi pemenang.',
            'tanggal_booking' => now(),
        ]);

        $response = $this->actingAs($winner, 'web')->get(route('booking.status', $booking->kode_booking));

        $response->assertOk();
        $response->assertSee('Pemenang Beauty Contest');
        $response->assertSee('Selamat! Anda Ditetapkan Sebagai Pemenang Seleksi Sewa BMD');
        $response->assertSee('Catatan Resmi Penetapan Hasil Evaluasi UPTD:');
        $response->assertSee('Ditetapkan sebagai pemenang Beauty Contest. Selamat anda telah menjadi pemenang.');
        $response->assertSee('Tahap 3 dari 6 (Mitra Terpilih)');

        // Tidak boleh menampilkan banner kedaluwarsa 5 hari kerja
        $response->assertDontSee('Masa Tenggang Pengunggahan Berkas Telah Berakhir');
        $response->assertDontSee('Batas Waktu 5 Hari Kerja Terlewati');
    }

    public function test_loser_status_page_displays_dedicated_notice_banner_and_explanation(): void
    {
        $asset = Asset::factory()->create([
            'nama_objek' => 'Ruko Braga Blok B-2',
            'status_ketersediaan' => 'tersedia',
        ]);

        $loser = User::factory()->create([
            'role' => 'user',
            'name' => 'PT Kreatif Nusantara Mandiri',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BMD-TEST-LOSE-01',
            'asset_id' => $asset->id,
            'user_id' => $loser->id,
            'nama_pemohon' => $loser->name,
            'nomor_whatsapp' => '081234567892',
            'email' => $loser->email,
            'tipe_pemohon' => 'perusahaan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'status' => 'rejected',
            'is_beauty_contest_winner' => false,
            'beauty_contest_status' => 'completed',
            'alasan_penolakan' => 'Tidak terpilih dalam seleksi Beauty Contest (skor evaluasi berada di bawah mitra terpilih).',
            'catatan_admin' => 'Terima kasih atas partisipasinya dalam Beauty Contest. Objek ditetapkan kepada mitra dengan nilai tertinggi sesuai Berita Acara Seleksi.',
            'tanggal_booking' => now(),
        ]);

        $response = $this->actingAs($loser, 'web')->get(route('booking.status', $booking->kode_booking));

        $response->assertOk();
        $response->assertSee('Hasil Seleksi Beauty Contest');
        $response->assertSee('Belum Terpilih Sebagai Mitra');
        $response->assertSee('Pemberitahuan Hasil Seleksi Terbuka (Beauty Contest)');
        $response->assertSee('Keterangan Resmi Hasil Evaluasi Tim Penilai UPTD:');
        $response->assertSee('Tidak terpilih dalam seleksi Beauty Contest (skor evaluasi berada di bawah mitra terpilih).');
        $response->assertSee('Terima kasih atas partisipasinya dalam Beauty Contest.');
        $response->assertSee('Permohonan Selesai (Ditolak)');

        // Tidak boleh menampilkan banner kedaluwarsa 5 hari kerja
        $response->assertDontSee('Masa Tenggang Pengunggahan Berkas Telah Berakhir');
        $response->assertDontSee('Batas Waktu 5 Hari Kerja Terlewati');
    }
}
