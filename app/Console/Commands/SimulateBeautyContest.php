<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class SimulateBeautyContest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bmd:demo-beauty-contest {--clean : Hapus seluruh data simulasi dan kembalikan aset ke tersedia} {--award-winner : Tetapkan BMD-DEMO-BC-02 sebagai pemenang simulasi}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyiapkan atau membersihkan data simulasi Beauty Contest untuk pengujian end-to-end';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('clean')) {
            return $this->cleanSimulationData();
        }

        if ($this->option('award-winner')) {
            $winner = Booking::where('kode_booking', 'BMD-DEMO-BC-02')->first();
            if ($winner) {
                $winner->awardBeautyContestWinner(null, 'Selamat anda telah menjadi pemenang');
                $this->info("✓ Pemenang berhasil ditetapkan kepada {$winner->nama_pemohon} ({$winner->kode_booking}).");
                $this->line('- Pemenang: /status-booking/BMD-DEMO-BC-02 (Diterima)');
                $this->line('- Peserta Kalah: /status-booking/BMD-DEMO-BC-01 (Ditolak)');
            } else {
                $this->error('Data booking simulasi BMD-DEMO-BC-02 tidak ditemukan. Jalankan tanpa opsi untuk membuat data.');
            }

            return Command::SUCCESS;
        }

        return $this->createSimulationData();
    }

    /**
     * Bersihkan seluruh data simulasi yang pernah dibuat.
     */
    protected function cleanSimulationData(): int
    {
        $this->warn('Membersihkan data simulasi Beauty Contest...');

        // 1. Hapus dokumen dummy dari disk storage
        if (Storage::disk('local')->exists('booking-documents/demo')) {
            Storage::disk('local')->deleteDirectory('booking-documents/demo');
            $this->line('✓ Berkas dummy di storage/app/private/booking-documents/demo berhasil dihapus.');
        }

        // 2. Hapus data booking simulasi (cascade akan menghapus booking_documents)
        $simulatedBookings = Booking::where('kode_booking', 'like', 'BMD-DEMO-BC-%')->get();
        $countBookings = $simulatedBookings->count();
        foreach ($simulatedBookings as $b) {
            $b->delete();
        }
        $this->line("✓ {$countBookings} data permohonan booking simulasi berhasil dihapus.");

        // 3. Hapus user peminat demo
        $deletedUsers = User::whereIn('email', [
            'pt.kreatif@demo.test',
            'cv.berkah@demo.test',
        ])->delete();
        $this->line("✓ {$deletedUsers} akun peminat dummy berhasil dihapus.");

        // 4. Kembalikan status Asset 42 (Ruko Braga) ke tersedia
        $asset = Asset::find(42);
        if ($asset) {
            $asset->update([
                'status_ketersediaan' => 'tersedia',
                'booking_opened_at' => null,
                'booking_deadline_at' => null,
            ]);
            $this->line("✓ Status aset #{$asset->id} ({$asset->nama_objek}) dikembalikan ke 'tersedia'.");
        }

        $this->newLine();
        $this->info('=== DATA SIMULASI BERHASIL DIBERSIHKAN DENGAN TUNTAS ===');

        return Command::SUCCESS;
    }

    /**
     * Buat data simulasi lengkap Opsi A.
     */
    protected function createSimulationData(): int
    {
        $this->info('=== MEMBUAT DATA SIMULASI BEAUTY CONTEST (OPSI A) ===');

        // Bersihkan dulu jika ada data demo sebelumnya agar fresh & idempotent
        $this->cleanSimulationData();

        $asset = Asset::find(42);
        if (! $asset) {
            $asset = Asset::where('tipe_aset', 'tunggal')->where('status_ketersediaan', 'tersedia')->firstOrFail();
        }

        // 1. Set batas waktu aset ke 2 hari yang lalu (Masa tenggang 5 hari kerja berakhir)
        $asset->update([
            'status_ketersediaan' => 'pending',
            'booking_opened_at' => Carbon::now()->subDays(7),
            'booking_deadline_at' => Carbon::now()->subDays(2),
        ]);
        $this->line("1. Menggunakan Aset #{$asset->id}: {$asset->nama_objek}");
        $this->line("   - Batas Waktu Permohonan: {$asset->booking_deadline_at->format('d/m/Y H:i')} WIB (Sudah Lewat)");

        // 2. Buat Dummy PDF fisik di storage lokal
        $dummyPdfContent = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF\n";
        $pdfPath = 'booking-documents/demo/dokumen_kelengkapan_simulasi.pdf';
        Storage::disk('local')->put($pdfPath, $dummyPdfContent);

        // 3. Buat User 1 (PT Kreatif Nusantara Mandiri)
        $user1 = User::create([
            'name' => 'PT Kreatif Nusantara Mandiri',
            'email' => 'pt.kreatif@demo.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '081223344551',
            'instansi' => 'PT Kreatif Nusantara Mandiri',
            'tipe_pemohon' => 'perusahaan',
            'nib' => '0123456789012',
            'npwp' => '01.234.567.8-429.000',
            'alamat' => 'Jl. Ir. H. Juanda No. 120, Dago, Kota Bandung',
            'is_active' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        // 4. Buat User 2 (CV Berkah Sinergi Jabar)
        $user2 = User::create([
            'name' => 'CV Berkah Sinergi Jabar',
            'email' => 'cv.berkah@demo.test',
            'password' => Hash::make('password'),
            'role' => 'user',
            'phone' => '081223344552',
            'instansi' => 'CV Berkah Sinergi Jabar',
            'tipe_pemohon' => 'perusahaan',
            'nib' => '9876543210987',
            'npwp' => '98.765.432.1-429.000',
            'alamat' => 'Jl. Braga No. 45, Sumur Bandung, Kota Bandung',
            'is_active' => true,
            'email_verified_at' => Carbon::now(),
        ]);

        $this->line('2. Dibuatkan 2 Akun Pengguna Peminat Sah:');
        $this->line('   - Peminat 1: pt.kreatif@demo.test (Pass: password)');
        $this->line('   - Peminat 2: cv.berkah@demo.test (Pass: password)');

        // 5. Buat Permohonan Booking 1
        $booking1 = Booking::create([
            'kode_booking' => 'BMD-DEMO-BC-01',
            'asset_id' => $asset->id,
            'user_id' => $user1->id,
            'nama_pemohon' => $user1->name,
            'nik_atau_nib' => $user1->nib,
            'nama_instansi' => $user1->instansi,
            'nomor_whatsapp' => $user1->phone,
            'email' => $user1->email,
            'tipe_pemohon' => 'perusahaan',
            'jabatan_pemohon' => 'Direktur Utama',
            'rencana_peruntukan' => 'Kafe Modern, Co-Working Space, dan Galeri Kreatif Produk Jabar Juara',
            'durasi_sewa_tahun' => 3,
            'skema_pembayaran' => 'bertahap',
            'tanggal_booking' => Carbon::now()->subDays(6),
            'status' => 'document_review',
        ]);

        // 6. Buat Permohonan Booking 2
        $booking2 = Booking::create([
            'kode_booking' => 'BMD-DEMO-BC-02',
            'asset_id' => $asset->id,
            'user_id' => $user2->id,
            'nama_pemohon' => $user2->name,
            'nik_atau_nib' => $user2->nib,
            'nama_instansi' => $user2->instansi,
            'nomor_whatsapp' => $user2->phone,
            'email' => $user2->email,
            'tipe_pemohon' => 'perusahaan',
            'jabatan_pemohon' => 'Direktur Operasional',
            'rencana_peruntukan' => 'Hub Distribusi Logistik UMKM dan Gerai Penjualan Retail Kerajinan Daerah',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'tanggal_booking' => Carbon::now()->subDays(6),
            'status' => 'document_review',
        ]);

        // 7. Unggah seluruh 8 dokumen persyaratan wajib untuk kedua pemohon
        $requiredRequirements = BookingRequirement::whereIn('kode', [
            'REQ_SURAT_PERMOHONAN',
            'REQ_SURAT_PERNYATAAN',
            'REQ_SURAT_BADAN_HUKUM',
            'REQ_KTP',
            'REQ_NPWP',
            'REQ_NIB',
            'REQ_AKTA_PENDIRIAN',
            'REQ_PROPOSAL',
        ])->get();

        foreach ([$booking1, $booking2] as $index => $booking) {
            foreach ($requiredRequirements as $req) {
                BookingDocument::create([
                    'booking_id' => $booking->id,
                    'booking_requirement_id' => $req->id,
                    'file_path' => $pdfPath,
                    'file_name' => str_replace(' ', '_', $req->nama_persyaratan).'.pdf',
                    'file_size' => strlen($dummyPdfContent),
                    'file_type' => 'application/pdf',
                    'nomor_dokumen' => 'DOC/DEMO/'.($index + 1).'/'.$req->kode.'/2026',
                    'tanggal_dokumen' => Carbon::now()->subDays(5),
                    'status_verifikasi' => 'verified',
                ]);
            }
        }

        $this->line('3. Seluruh 8 Dokumen Persyaratan Wajib telah lengkap 100% untuk kedua permohonan.');
        $this->newLine();
        $this->info('=== DATA SIMULASI BERHASIL DIBUAT DENGAN LENGKAP ===');
        $this->newLine();

        $this->comment('PANDUAN LANGKAH SELANJUTNYA UNTUK ANDA:');
        $this->line('1. Buka browser dan login ke akun peminat di URL: /masuk');
        $this->line('   - Akun 1: pt.kreatif@demo.test (Pass: password)');
        $this->line('   - Link Langsung: /status-booking/BMD-DEMO-BC-01');
        $this->line('   - Akun 2: cv.berkah@demo.test (Pass: password)');
        $this->line('   - Link Langsung: /status-booking/BMD-DEMO-BC-02');
        $this->newLine();
        $this->line('2. Jalankan perintah otomatisasi evaluasi deadline:');
        $this->info('   php artisan bmd:check-booking-deadlines');
        $this->line('   (Sistem akan mendeteksi deadline habis dan mengubah kedua status menjadi "beauty_contest")');
        $this->newLine();
        $this->line('3. Buka Panel Admin Filament di URL: /admin/bookings');
        $this->line('   - Lihat kedua peminat dengan badge ungu "Beauty Contest"');
        $this->line('   - Klik tombol "Atur Jadwal Beauty Contest" (isi tanggal, lokasi/link Zoom, dan catatan)');
        $this->line('   - Klik tombol "Tetapkan Pemenang" untuk salah satu peminat');
        $this->newLine();
        $this->line('4. Jika simulasi sudah selesai, bersihkan seluruh data dengan:');
        $this->comment('   php artisan bmd:demo-beauty-contest --clean');

        return Command::SUCCESS;
    }
}
