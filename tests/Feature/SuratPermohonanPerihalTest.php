<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use App\Services\SuratPermohonanService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use ZipArchive;

class SuratPermohonanPerihalTest extends TestCase
{
    use DatabaseTransactions;

    private function getDocxParagraph(string $filePath, int $index): string
    {
        $zip = new ZipArchive;
        $zip->open($filePath);
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $doc = new DOMDocument;
        $doc->loadXML($xml);
        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $paragraphs = $xpath->query('//w:p');

        return trim($paragraphs->item($index)?->textContent ?? '');
    }

    public function test_surat_permohonan_perihal_dan_isi_menyesuaikan_aset_tanah(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-TEST-T-'.uniqid(),
            'nama_objek' => 'Lahan Kosong Cisaranten',
            'bentuk_objek' => 'Tanah',
            'alamat' => 'Desa Cisaranten, Kec. Cikadu',
            'kabupaten_kota' => 'Kabupaten Cianjur',
            'luas_tanah' => 500,
            'luas_bangunan' => 0,
            'status_ketersediaan' => 'tersedia',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-T-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Denda Sihabudin',
            'nomor_whatsapp' => '089888888801',
            'nik_atau_nib' => '3201234567890001',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 2,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Perkebunan Sayur',
            'status' => 'pending',
        ]);

        $this->assertEquals('Tanah', SuratPermohonanService::getJenisObjekSewa($booking));

        // Test Auto-Fill DOCX
        $outputPath = SuratPermohonanService::generate($booking);
        $perihalText = $this->getDocxParagraph($outputPath, 26);
        $bodyText = $this->getDocxParagraph($outputPath, 40);

        $this->assertEquals('Permohonan Sewa Tanah Milik Pemerintah Provinsi Jawa Barat', $perihalText);
        $this->assertStringContainsString('sewa tanah milik Pemerintah Provinsi Jawa Barat', $bodyText);
        $this->assertStringContainsString('dengan luas tanah 500 m2 (Lima Ratus meter persegi)', $bodyText);
        $this->assertStringNotContainsString('dan bangunan 0 m2', $bodyText);

        // Test Raw Template DOCX
        $rawPath = SuratPermohonanService::generateRaw($booking);
        $rawPerihalText = $this->getDocxParagraph($rawPath, 26);
        $this->assertEquals('Permohonan Sewa Tanah Milik Pemerintah Provinsi Jawa Barat', $rawPerihalText);
    }

    public function test_surat_permohonan_perihal_dan_isi_menyesuaikan_aset_tanah_dan_bangunan(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-TEST-TB-'.uniqid(),
            'nama_objek' => 'Ruko Braga Heritage',
            'bentuk_objek' => 'Tanah dan bangunan',
            'alamat' => 'Jl. Braga No. 20',
            'kabupaten_kota' => 'Kota Bandung',
            'luas_tanah' => 250,
            'luas_bangunan' => 400,
            'status_ketersediaan' => 'tersedia',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-TB-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'PT Braga Kreatif',
            'nomor_whatsapp' => '081234567891',
            'nik_atau_nib' => '0123456789012',
            'tipe_pemohon' => 'badan_usaha',
            'nama_instansi' => 'PT Braga Kreatif',
            'jabatan_pemohon' => 'Direktur Utama',
            'durasi_sewa_tahun' => 5,
            'skema_pembayaran' => 'bertahap',
            'rencana_peruntukan' => 'Kafe & Galeri Seni',
            'status' => 'pending',
        ]);

        $this->assertEquals('Tanah dan Bangunan', SuratPermohonanService::getJenisObjekSewa($booking));

        // Test Auto-Fill DOCX
        $outputPath = SuratPermohonanService::generate($booking);
        $perihalText = $this->getDocxParagraph($outputPath, 26);
        $bodyText = $this->getDocxParagraph($outputPath, 40);

        $this->assertEquals('Permohonan Sewa Tanah dan Bangunan Milik Pemerintah Provinsi Jawa Barat', $perihalText);
        $this->assertStringContainsString('sewa tanah dan bangunan milik Pemerintah Provinsi Jawa Barat', $bodyText);
        $this->assertStringContainsString('dengan luas tanah 250 m2 (Dua Ratus Lima Puluh meter persegi) dan bangunan 400 m2 (Empat Ratus meter persegi)', $bodyText);

        // Test Raw Template DOCX
        $rawPath = SuratPermohonanService::generateRaw($booking);
        $rawPerihalText = $this->getDocxParagraph($rawPath, 26);
        $this->assertEquals('Permohonan Sewa Tanah dan Bangunan Milik Pemerintah Provinsi Jawa Barat', $rawPerihalText);
    }

    public function test_endpoint_download_surat_permohonan(): void
    {
        $user = User::factory()->create();
        $asset = Asset::create([
            'kode_aset' => 'AST-TEST-EP-'.uniqid(),
            'nama_objek' => 'Lahan Tower Cianjur',
            'bentuk_objek' => 'Tanah',
            'alamat' => 'Cikadu',
            'kabupaten_kota' => 'Kabupaten Cianjur',
            'luas_tanah' => 200,
            'luas_bangunan' => 0,
            'status_ketersediaan' => 'tersedia',
        ]);

        $booking = Booking::create([
            'kode_booking' => 'BKG-EP-'.uniqid(),
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'nama_pemohon' => 'Denda Sihabudin',
            'nomor_whatsapp' => '081234567892',
            'nik_atau_nib' => '3201234567890002',
            'tipe_pemohon' => 'perorangan',
            'durasi_sewa_tahun' => 1,
            'skema_pembayaran' => 'sekaligus',
            'rencana_peruntukan' => 'Tower',
            'status' => 'pending',
        ]);

        // Auto download response
        $response = $this->actingAs($user)->get(route('booking.surat-permohonan', $booking->kode_booking));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');

        // Raw download response
        $rawResponse = $this->actingAs($user)->get(route('booking.surat-permohonan', [
            'kode' => $booking->kode_booking,
            'mode' => 'raw',
        ]));
        $rawResponse->assertStatus(200);
        $rawResponse->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
    }
}
