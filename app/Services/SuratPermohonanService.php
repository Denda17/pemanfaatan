<?php

namespace App\Services;

use App\Models\Booking;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Carbon;
use ZipArchive;

class SuratPermohonanService
{
    /**
     * Convert number to Indonesian words (terbilang).
     */
    public static function terbilang(int|float $angka): string
    {
        $angka = (int) abs($angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];

        if ($angka < 12) {
            return $baca[$angka];
        } elseif ($angka < 20) {
            return trim(self::terbilang($angka - 10).' Belas');
        } elseif ($angka < 100) {
            return trim(self::terbilang(intdiv($angka, 10)).' Puluh '.self::terbilang($angka % 10));
        } elseif ($angka < 200) {
            return trim('Seratus '.self::terbilang($angka - 100));
        } elseif ($angka < 1000) {
            return trim(self::terbilang(intdiv($angka, 100)).' Ratus '.self::terbilang($angka % 100));
        } elseif ($angka < 2000) {
            return trim('Seribu '.self::terbilang($angka - 1000));
        } elseif ($angka < 1000000) {
            return trim(self::terbilang(intdiv($angka, 1000)).' Ribu '.self::terbilang($angka % 1000));
        } elseif ($angka < 1000000000) {
            return trim(self::terbilang(intdiv($angka, 1000000)).' Juta '.self::terbilang($angka % 1000000));
        } elseif ($angka < 1000000000000) {
            return trim(self::terbilang(intdiv($angka, 1000000000)).' Miliar '.self::terbilang($angka % 1000000000));
        }

        return (string) $angka;
    }

    /**
     * Tentukan klasifikasi jenis objek sewa secara dinamis berdasarkan bentuk objek aset / unit terpilih.
     * Mengembalikan salah satu dari: "Tanah", "Tanah dan Bangunan", atau "Bangunan".
     */
    public static function getJenisObjekSewa(Booking $booking): string
    {
        $asset = $booking->asset;
        $bentuk = strtolower(trim((string) ($asset?->bentuk_objek ?? '')));

        $hasTanah = str_contains($bentuk, 'tanah');
        $hasBangunan = str_contains($bentuk, 'bangunan');

        // 1. Cek dari teks eksplisit bentuk_objek aset
        if ($hasTanah && $hasBangunan) {
            return 'Tanah dan Bangunan';
        }
        if ($hasTanah && ! $hasBangunan) {
            return 'Tanah';
        }
        if ($hasBangunan && ! $hasTanah) {
            return 'Bangunan';
        }

        // 2. Fallback berdasarkan unit ruko yang dipilih (untuk multi-unit cluster)
        $selectedUnits = $booking->selected_units;
        if ($selectedUnits->isNotEmpty()) {
            $totalTanah = (float) $selectedUnits->sum('luas_tanah');
            $totalBangunan = (float) $selectedUnits->sum('luas_bangunan');

            if ($totalTanah > 0 && $totalBangunan > 0) {
                return 'Tanah dan Bangunan';
            }
            if ($totalBangunan > 0) {
                return 'Bangunan';
            }

            return 'Tanah';
        }

        // 3. Fallback berdasarkan data luas tanah / bangunan aset induk
        $luasTanah = (float) ($asset?->luas_tanah ?? 0);
        $luasBangunan = (float) ($asset?->luas_bangunan ?? 0);

        if ($luasTanah > 0 && $luasBangunan > 0) {
            return 'Tanah dan Bangunan';
        }
        if ($luasBangunan > 0) {
            return 'Bangunan';
        }

        return 'Tanah';
    }

    /**
     * Get path of template file.
     */
    public static function getTemplatePath(): string
    {
        $candidates = [
            base_path('docs/data/Template Surat Permohonan Sewa Calon Penyewa.docx'),
            base_path('docs/data/DRAFT Surat permohonan_Sewa_Calon_penyewa (Pengelola Barang) 2026.docx'),
        ];

        foreach ($candidates as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return $candidates[0];
    }

    /**
     * Generate an auto-filled docx file for a booking.
     * Returns the absolute path of the generated temp file.
     */
    public static function generate(Booking $booking): string
    {
        $templatePath = self::getTemplatePath();
        if (! file_exists($templatePath)) {
            throw new \RuntimeException('Template surat permohonan tidak ditemukan di server.');
        }

        $tempDir = storage_path('app/temp-documents');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir.'/Surat_Permohonan_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $booking->kode_booking).'.docx';
        copy($templatePath, $outputPath);

        $zip = new ZipArchive;
        if ($zip->open($outputPath) !== true) {
            throw new \RuntimeException('Gagal memproses file template DOCX.');
        }

        $xml = $zip->getFromName('word/document.xml');
        if (! $xml) {
            $zip->close();
            throw new \RuntimeException('Format file template tidak valid.');
        }

        $doc = new DOMDocument;
        // Preserve formatting and avoid XML parser errors with special entities
        libxml_use_internal_errors(true);
        $doc->loadXML($xml);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $user = $booking->user;
        $asset = $booking->asset;

        $isBadanUsaha = $booking->tipe_pemohon === 'badan_usaha' || (! empty($booking->nama_instansi) && $booking->nama_instansi !== 'Perorangan');
        $namaPemohon = $booking->nama_pemohon;
        $namaInstansi = $isBadanUsaha ? ($booking->nama_instansi ?: '-') : 'Perorangan';
        $jabatan = $booking->jabatan_pemohon ?: ($isBadanUsaha ? 'Pimpinan / Penanggung Jawab' : 'Pemohon Perseorangan');
        $nikNib = $booking->nik_atau_nib ?: ($user?->nik ?? $user?->nib ?? '-');
        $alamat = $user?->alamat ?: 'Sesuai KTP / Dokumen Domisili Pemohon';
        $nomorKontak = $booking->nomor_whatsapp ?: ($user?->phone ?: '-');

        $lokasiAset = $asset ? ($asset->alamat ? $asset->alamat.', '.$asset->kabupaten_kota : ($asset->kabupaten_kota ?: 'Objek Aset BMD Pemprov Jawa Barat')) : 'Objek Aset BMD Pemprov Jawa Barat';

        // Tentukan klasifikasi objek sewa secara dinamis: "Tanah", "Tanah dan Bangunan", atau "Bangunan"
        $jenisObjek = self::getJenisObjekSewa($booking);
        $perihalText = "Permohonan Sewa {$jenisObjek} Milik Pemerintah Provinsi Jawa Barat";

        // Hitung luas tanah & luas bangunan (dukung aset reguler maupun multi-unit cluster)
        $selectedUnits = $booking->selected_units;
        if ($selectedUnits->isNotEmpty()) {
            $luasTanah = (int) round((float) $selectedUnits->sum('luas_tanah'));
            $luasBangunan = (int) round((float) $selectedUnits->sum('luas_bangunan'));
        } else {
            $luasTanah = (int) round((float) ($asset?->luas_tanah ?? 0));
            $luasBangunan = (int) round((float) ($asset?->luas_bangunan ?? 0));
        }

        $luasTanahTerbilang = $luasTanah > 0 ? trim(self::terbilang($luasTanah).' meter persegi') : '-';
        $luasBangunanTerbilang = $luasBangunan > 0 ? trim(self::terbilang($luasBangunan).' meter persegi') : '-';

        $durasi = (int) $booking->durasi_sewa_tahun;
        $durasiTerbilang = self::terbilang($durasi);

        $estimasiTahunan = (float) ($asset?->estimasi_tarif_tahunan ?? 0);
        $tarifFormat = $estimasiTahunan > 0 ? number_format($estimasiTahunan, 0, ',', '.') : '50.000.000';

        $skemaBayar = $booking->skema_pembayaran === 'bertahap'
            ? 'pembayaran bertahap (termin tahunan)'
            : 'pembayaran dibayarkan sekaligus di awal periode sewa';

        $peruntukan = $booking->rencana_peruntukan ?: 'Pemanfaatan Usaha Sesuai Ketentuan';
        $tanggalSurat = Carbon::parse($booking->tanggal_booking ?? now())->translatedFormat('d F Y');
        $nomorSurat = '001/SEWA-BMD/'.preg_replace('/[^A-Za-z0-9]/', '', $booking->kode_booking).'/'.date('Y');

        $paragraphs = $xpath->query('//w:p');

        for ($i = 0; $i < $paragraphs->length; $i++) {
            $p = $paragraphs->item($i);
            $text = trim($p->textContent);

            // 1. Header KOP
            if ($i === 0 && ! $isBadanUsaha) {
                self::setParagraphText($doc, $p, 'PERMOHONAN SEWA BARANG MILIK DAERAH (PERORANGAN)');
            } elseif ($i === 0 && $isBadanUsaha) {
                self::setParagraphText($doc, $p, strtoupper($namaInstansi));
            }

            if ($i === 1 && ! $isBadanUsaha) {
                self::setParagraphText($doc, $p, 'Pemohon Perorangan / Warga Masyarakat');
            } elseif ($i === 1 && $isBadanUsaha) {
                self::setParagraphText($doc, $p, 'Badan Usaha / Koperasi / Korporasi');
            }

            // 2. Tanggal & Nomor Surat
            if ($i === 7) {
                self::setParagraphText($doc, $p, 'Bandung, '.$tanggalSurat);
            }
            if ($i === 8) {
                self::setParagraphText($doc, $p, 'Nomor : '.$nomorSurat);
            }

            // 2.5 Perihal Surat: Menyesuaikan apakah Tanah, Tanah dan Bangunan, atau Bangunan
            if ($i === 26 || (str_contains($text, 'Permohonan Sewa') && str_contains($text, 'Pemerintah Provinsi'))) {
                self::setParagraphText($doc, $p, $perihalText, '24');
            }

            // 3. Identitas Pemohon
            if ($i === 36) {
                self::setParagraphText($doc, $p, 'Nama : '.$namaPemohon.($isBadanUsaha ? ' (Mewakili '.$namaInstansi.')' : ''));
            }
            if ($i === 37) {
                self::setParagraphText($doc, $p, ($isBadanUsaha ? 'NIB / NIK : ' : 'NIK : ').$nikNib);
            }
            if ($i === 38) {
                self::setParagraphText($doc, $p, 'Alamat : '.$alamat.' (No. Kontak/WA: '.$nomorKontak.')');
            }

            // 4. Paragraf Inti Permohonan: Sesuaikan klausul objek sewa dan luas
            if ($i === 40) {
                if ($jenisObjek === 'Tanah') {
                    $objekSewaText = 'sewa tanah';
                    $luasSewaText = "dengan luas tanah {$luasTanah} m2 ({$luasTanahTerbilang})";
                } elseif ($jenisObjek === 'Bangunan') {
                    $objekSewaText = 'sewa bangunan';
                    $luasSewaText = "dengan luas bangunan {$luasBangunan} m2 ({$luasBangunanTerbilang})";
                } else {
                    $objekSewaText = 'sewa tanah dan bangunan';
                    $luasSewaText = "dengan luas tanah {$luasTanah} m2 ({$luasTanahTerbilang}) dan bangunan {$luasBangunan} m2 ({$luasBangunanTerbilang})";
                }

                $bodyText = "Dengan ini bermaksud mengajukan permohonan pemanfaatan {$objekSewaText} milik Pemerintah Provinsi Jawa Barat yang berlokasi di {$lokasiAset}, {$luasSewaText} untuk digunakan sebagai {$peruntukan}. Rencana jangka waktu sewa selama {$durasi} ({$durasiTerbilang}) tahun, penawaran sewa sebesar Rp {$tarifFormat} per tahun, dan {$skemaBayar} yang akan digunakan untuk {$peruntukan}.";
                self::setParagraphText($doc, $p, $bodyText);
            }

            // 5. TTD Pemohon Bagian 1
            if ($i === 45) {
                self::setParagraphText($doc, $p, $namaPemohon.($isBadanUsaha ? ' ('.$namaInstansi.')' : ''));
            }

            // 6. Surat Pernyataan Kesediaan (Bagian 2)
            if ($i === 55) {
                self::setParagraphText($doc, $p, 'Nama : '.$namaPemohon);
            }
            if ($i === 56) {
                self::setParagraphText($doc, $p, 'Badan Hukum/Usaha : '.$namaInstansi);
            }
            if ($i === 57) {
                self::setParagraphText($doc, $p, 'Jabatan : '.$jabatan);
            }
            if ($i === 58) {
                self::setParagraphText($doc, $p, 'Alamat : '.$alamat);
            }
            if ($i === 59) {
                $objekLahan = $jenisObjek === 'Tanah' ? 'tanah' : ($jenisObjek === 'Bangunan' ? 'bangunan' : 'tanah dan bangunan');
                $luasPernyataan = $jenisObjek === 'Tanah'
                    ? "seluas {$luasTanah} m2 ({$luasTanahTerbilang})"
                    : ($jenisObjek === 'Bangunan' ? "seluas {$luasBangunan} m2 ({$luasBangunanTerbilang})" : "seluas tanah {$luasTanah} m2 ({$luasTanahTerbilang}) dan bangunan {$luasBangunan} m2 ({$luasBangunanTerbilang})");

                $pernyataanText = "Dengan ini menyatakan kesediaan untuk menyewa {$objekLahan} milik Pemerintah Provinsi Jawa Barat yang terletak di {$lokasiAset} {$luasPernyataan}, dengan komitmen:";
                self::setParagraphText($doc, $p, $pernyataanText);
            }
            if ($i === 61) {
                $komitmenText = "Bersedia mengikuti aturan dan ketentuan yang berlaku selama jangka waktu sewa tersebut yaitu selama {$durasi} ({$durasiTerbilang}) tahun yang akan digunakan untuk {$peruntukan}.";
                self::setParagraphText($doc, $p, $komitmenText);
            }
            if ($i === 64) {
                self::setParagraphText($doc, $p, 'Bandung, '.$tanggalSurat);
            }
            if ($i === 69) {
                self::setParagraphText($doc, $p, '( '.$namaPemohon.' )');
            }

            // 7. Surat Keterangan Khusus Badan Usaha (Bagian 3)
            if ($i === 83) {
                self::setParagraphText($doc, $p, 'Nama : '.$namaPemohon);
            }
            if ($i === 84) {
                self::setParagraphText($doc, $p, 'Jabatan : '.($isBadanUsaha ? $jabatan : '-'));
            }
            if ($i === 85) {
                self::setParagraphText($doc, $p, 'Badan Hukum/Usaha : '.$namaInstansi);
            }
            if ($i === 86) {
                self::setParagraphText($doc, $p, 'Alamat Kantor : '.$alamat);
            }
            if ($i === 87) {
                if ($isBadanUsaha) {
                    $objekLahan = $jenisObjek === 'Tanah' ? 'tanah' : ($jenisObjek === 'Bangunan' ? 'bangunan' : 'tanah dan bangunan');
                    $luasKet = $jenisObjek === 'Tanah'
                        ? "seluas {$luasTanah} m2"
                        : ($jenisObjek === 'Bangunan' ? "seluas {$luasBangunan} m2" : "seluas tanah {$luasTanah} m2 dan bangunan {$luasBangunan} m2");

                    $ketText = "Dengan ini menerangkan bahwa benar yang akan melakukan sewa {$objekLahan} Pemerintah Provinsi Jawa Barat {$luasKet} yang berlokasi di {$lokasiAset} adalah {$namaInstansi}.";
                } else {
                    $ketText = '[Bagian Surat Keterangan Badan Usaha ini tidak berlaku bagi pemohon perorangan]';
                }
                self::setParagraphText($doc, $p, $ketText);
            }
            if ($i === 90) {
                self::setParagraphText($doc, $p, 'Bandung, '.$tanggalSurat);
            }
            if ($i === 95) {
                self::setParagraphText($doc, $p, '( '.$namaPemohon.' )');
            }
        }

        $zip->addFromString('word/document.xml', $doc->saveXML());
        $zip->close();

        return $outputPath;
    }

    /**
     * Generate raw template docx with Perihal customized according to asset type.
     */
    public static function generateRaw(Booking $booking): string
    {
        $templatePath = self::getTemplatePath();
        if (! file_exists($templatePath)) {
            throw new \RuntimeException('Template surat permohonan tidak ditemukan di server.');
        }

        $tempDir = storage_path('app/temp-documents');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $outputPath = $tempDir.'/DRAFT_Surat_Permohonan_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $booking->kode_booking).'.docx';
        copy($templatePath, $outputPath);

        $zip = new ZipArchive;
        if ($zip->open($outputPath) !== true) {
            return $templatePath;
        }

        $xml = $zip->getFromName('word/document.xml');
        if (! $xml) {
            $zip->close();

            return $templatePath;
        }

        $doc = new DOMDocument;
        libxml_use_internal_errors(true);
        $doc->loadXML($xml);
        libxml_clear_errors();

        $xpath = new DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $jenisObjek = self::getJenisObjekSewa($booking);
        $perihalText = "Permohonan Sewa {$jenisObjek} Milik Pemerintah Provinsi Jawa Barat";

        $paragraphs = $xpath->query('//w:p');
        for ($i = 0; $i < $paragraphs->length; $i++) {
            $p = $paragraphs->item($i);
            $text = trim($p->textContent);

            if ($i === 26 || (str_contains($text, 'Permohonan Sewa') && str_contains($text, 'Pemerintah Provinsi'))) {
                self::setParagraphText($doc, $p, $perihalText, '24');
                break;
            }
        }

        $zip->addFromString('word/document.xml', $doc->saveXML());
        $zip->close();

        return $outputPath;
    }

    /**
     * Replace all runs within a paragraph with a single run containing text.
     */
    protected static function setParagraphText(DOMDocument $doc, \DOMNode $p, string $text, string $fontSize = '22'): void
    {
        // Preserve paragraph properties (<w:pPr>) if exists
        $pPr = null;
        foreach ($p->childNodes as $child) {
            if ($child->nodeName === 'w:pPr') {
                $pPr = $child->cloneNode(true);
                break;
            }
        }

        // Remove all children
        while ($p->firstChild) {
            $p->removeChild($p->firstChild);
        }

        // Restore paragraph properties
        if ($pPr) {
            $p->appendChild($pPr);
        }

        // Create new run <w:r>
        $r = $doc->createElement('w:r');
        $rPr = $doc->createElement('w:rPr');

        $rFonts = $doc->createElement('w:rFonts');
        $rFonts->setAttribute('w:ascii', 'Arial');
        $rFonts->setAttribute('w:hAnsi', 'Arial');
        $rPr->appendChild($rFonts);

        $sz = $doc->createElement('w:sz');
        $sz->setAttribute('w:val', $fontSize);
        $rPr->appendChild($sz);

        $r->appendChild($rPr);

        $t = $doc->createElement('w:t');
        $t->setAttribute('xml:space', 'preserve');
        $t->textContent = $text;

        $r->appendChild($t);
        $p->appendChild($r);
    }
}
