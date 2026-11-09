<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportBmdExcelCommand extends Command
{
    protected $signature = 'bmd:import-excel {--file=docs/data/Rekap Pemanfaatan BMD (terbaru).xlsx} {--limit=0}';
    protected $description = 'Import rekap data pemanfaatan BMD dari file Excel ke database PostgreSQL';

    protected array $regencies = [
        'Kota Bandung', 'Kota Bogor', 'Kota Bekasi', 'Kota Depok', 'Kota Cirebon', 
        'Kota Sukabumi', 'Kota Tasikmalaya', 'Kota Cimahi', 'Kota Banjar',
        'Kabupaten Bandung Barat', 'Kabupaten Bandung', 'Kabupaten Bekasi', 'Kabupaten Bogor', 
        'Kabupaten Ciamis', 'Kabupaten Cianjur', 'Kabupaten Cirebon', 'Kabupaten Garut', 
        'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Kuningan', 'Kabupaten Majalengka', 
        'Kabupaten Pangandaran', 'Kabupaten Purwakarta', 'Kabupaten Subang', 'Kabupaten Sukabumi', 
        'Kabupaten Sumedang', 'Kabupaten Tasikmalaya'
    ];

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membaca file Excel: {$filePath}...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Exception $e) {
            $this->error("Gagal membuka file Excel: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->info("Total baris terdeteksi: {$highestRow}");

        $limit = (int) $this->option('limit');
        $maxRow = $limit > 0 ? min($highestRow, 3 + $limit) : $highestRow;

        $importedAssets = 0;
        $importedPemanfaatan = 0;

        $this->output->progressStart($maxRow - 3);

        for ($row = 4; $row <= $maxRow; $row++) {
            $objekSewa = trim((string) $sheet->getCell("B{$row}")->getValue());

            if (empty($objekSewa) || str_starts_with(strtolower($objekSewa), 'jumlah') || str_starts_with(strtolower($objekSewa), 'total')) {
                $this->output->progressAdvance();
                continue;
            }

            $noUrut = trim((string) $sheet->getCell("A{$row}")->getValue());
            $kordinatRaw = trim((string) $sheet->getCell("C{$row}")->getValue());
            $luasTanahRaw = $sheet->getCell("D{$row}")->getValue();
            $luasBangunanRaw = $sheet->getCell("E{$row}")->getValue();
            $bentukObjekRaw = trim((string) $sheet->getCell("F{$row}")->getValue());
            $statusPengelola = trim((string) $sheet->getCell("G{$row}")->getValue());
            $mitra = trim((string) $sheet->getCell("H{$row}")->getValue());
            $besaranSewaRaw = $sheet->getCell("I{$row}")->getValue();
            $jangkaWaktuRaw = $sheet->getCell("J{$row}")->getValue();
            $totalSewaRaw = $sheet->getCell("K{$row}")->getValue();
            $pembayaran = trim((string) $sheet->getCell("L{$row}")->getValue());
            $noPersetujuan = trim((string) $sheet->getCell("M{$row}")->getValue());
            $noKepgub = trim((string) $sheet->getCell("N{$row}")->getValue());
            $noPerjanjian = trim((string) $sheet->getCell("O{$row}")->getValue());
            $tglAwalRaw = $sheet->getCell("P{$row}")->getValue();
            $tglAkhirRaw = $sheet->getCell("Q{$row}")->getValue();
            $keterangan = trim((string) $sheet->getCell("T{$row}")->getValue());

            // Parse numbers
            $luasTanah = $this->parseNumeric($luasTanahRaw);
            $luasBangunan = $this->parseNumeric($luasBangunanRaw);
            $besaranSewa = $this->parseNumeric($besaranSewaRaw);
            $jangkaWaktu = (int) $this->parseNumeric($jangkaWaktuRaw);
            $totalSewa = $this->parseNumeric($totalSewaRaw);

            // Parse coordinates
            [$lat, $lng] = $this->parseCoordinates($kordinatRaw);

            // Detect Kabupaten/Kota
            $kabupatenKota = $this->detectKabupatenKota($objekSewa);

            // Parse Dates
            $tglAwal = $this->parseExcelDate($tglAwalRaw);
            $tglAkhir = $this->parseExcelDate($tglAkhirRaw);

            // Tentukan status ketersediaan
            $statusKetersediaan = 'tersewa';
            if ($tglAkhir && $tglAkhir < now()->format('Y-m-d')) {
                $statusKetersediaan = 'tersedia';
            }

            // Tentukan peruntukan
            $peruntukan = $this->detectPeruntukan($objekSewa, $bentukObjekRaw);

            // Simpan / update Aset
            $kodeAset = 'BMD-JBR-' . str_pad((string)($row - 3), 4, '0', STR_PAD_LEFT);
            $bentukObjek = !empty($bentukObjekRaw) ? $bentukObjekRaw : 'Tanah';

            $asset = Asset::updateOrCreate(
                ['kode_aset' => $kodeAset],
                [
                    'nama_objek' => $objekSewa,
                    'slug' => Str::slug($objekSewa) . '-' . strtolower(Str::random(4)),
                    'bentuk_objek' => $bentukObjek,
                    'luas_tanah' => $luasTanah,
                    'luas_bangunan' => $luasBangunan,
                    'alamat' => $objekSewa,
                    'kabupaten_kota' => $kabupatenKota,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_ketersediaan' => $statusKetersediaan,
                    'estimasi_tarif_tahunan' => $besaranSewa ?: ($totalSewa && $jangkaWaktu ? $totalSewa / $jangkaWaktu : null),
                    'peruntukan' => $peruntukan,
                    'deskripsi' => "Aset Barang Milik Daerah Provinsi Jawa Barat di {$kabupatenKota}. Peruntukan: {$peruntukan}.",
                    'fasilitas' => $this->generateDefaultFacilities($bentukObjek),
                    'is_featured' => ($row <= 15), // highlight first 12 as featured
                ]
            );
            $importedAssets++;

            // Simpan data Pemanfaatan jika ada data mitra
            if (!empty($mitra) && $mitra !== '-') {
                // Warning jika tanggal akhir dalam 6 bulan ke depan
                $statusPerpanjangan = 'aktif';
                if ($tglAkhir) {
                    $akhirDate = \Carbon\Carbon::parse($tglAkhir);
                    if ($akhirDate->isPast()) {
                        $statusPerpanjangan = 'selesai';
                    } elseif ($akhirDate->diffInMonths(now()) <= 6) {
                        $statusPerpanjangan = 'peringatan_jatuh_tempo';
                    }
                }

                AssetPemanfaatan::updateOrCreate(
                    [
                        'asset_id' => $asset->id,
                        'nama_mitra' => $mitra,
                    ],
                    [
                        'jenis_pemanfaatan' => 'Sewa',
                        'jenis_mitra' => $this->detectMitraType($mitra),
                        'besaran_sewa_tahunan' => $besaranSewa,
                        'jangka_waktu_tahun' => $jangkaWaktu ?: 1,
                        'total_sewa' => $totalSewa,
                        'mekanisme_pembayaran' => !empty($pembayaran) ? $pembayaran : 'Sekaligus',
                        'nomor_persetujuan' => $noPersetujuan ?: null,
                        'nomor_kepgub' => $noKepgub ?: null,
                        'nomor_perjanjian' => $noPerjanjian ?: null,
                        'tanggal_awal' => $tglAwal,
                        'tanggal_akhir' => $tglAkhir,
                        'status_perpanjangan' => $statusPerpanjangan,
                        'keterangan' => $keterangan ?: null,
                    ]
                );
                $importedPemanfaatan++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Import selesai!");
        $this->info("Total Aset diimport: {$importedAssets}");
        $this->info("Total Data Pemanfaatan diimport: {$importedPemanfaatan}");

        return Command::SUCCESS;
    }

    protected function parseNumeric(mixed $value): ?float
    {
        if ($value === null || $value === '' || $value === '-') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $cleaned = preg_replace('/[^0-9.]/', '', str_replace(',', '.', (string) $value));
        return is_numeric($cleaned) ? (float) $cleaned : null;
    }

    protected function parseCoordinates(?string $raw): array
    {
        if (empty($raw) || $raw === '-' || str_contains($raw, '0° S 0° W')) {
            return [null, null];
        }

        // Cek format decimal e.g. "-6.96913, 107.69636"
        if (preg_match('/(-?\d+\.\d+)\s*,\s*(\d+\.\d+)/', $raw, $matches)) {
            return [(float) $matches[1], (float) $matches[2]];
        }

        // Cek format DMS e.g. 6°55'15.4"S 107°36'35.5"E
        if (preg_match('/(\d+)°(\d+)\'([\d.]+)"([NS])\s*(\d+)°(\d+)\'([\d.]+)"([EW])/i', $raw, $matches)) {
            $lat = (float) $matches[1] + ((float) $matches[2] / 60) + ((float) $matches[3] / 3600);
            if (strtoupper($matches[4]) === 'S') {
                $lat = -$lat;
            }

            $lng = (float) $matches[5] + ((float) $matches[6] / 60) + ((float) $matches[7] / 3600);
            if (strtoupper($matches[8]) === 'W') {
                $lng = -$lng;
            }

            return [round($lat, 7), round($lng, 7)];
        }

        return [null, null];
    }

    protected function detectKabupatenKota(string $text): string
    {
        $textLower = strtolower($text);

        foreach ($this->regencies as $reg) {
            $nameOnly = strtolower(str_replace(['Kota ', 'Kabupaten '], '', $reg));
            if (str_contains($textLower, $nameOnly)) {
                return $reg;
            }
        }

        if (str_contains($textLower, 'braga') || str_contains($textLower, 'gbla') || str_contains($textLower, 'mraj') || str_contains($textLower, 'al jabbar') || str_contains($textLower, 'dago')) {
            return 'Kota Bandung';
        }

        return 'Kota Bandung'; // Default ibukota Jabar
    }

    protected function detectPeruntukan(string $text, string $bentuk): string
    {
        $textLower = strtolower($text);

        if (str_contains($textLower, 'tower') || str_contains($textLower, 'bts')) {
            return 'Tower Telekomunikasi';
        }
        if (str_contains($textLower, 'umkm') || str_contains($textLower, 'kantin') || str_contains($textLower, 'kuliner')) {
            return 'Komersial / UMKM';
        }
        if (str_contains($textLower, 'parkir')) {
            return 'Lahan Parkir';
        }
        if (str_contains($textLower, 'ruko')) {
            return 'Ruko / Ruang Usaha';
        }
        if (str_contains($textLower, 'kantor')) {
            return 'Perkantoran';
        }
        if (str_contains($textLower, 'pertanian') || str_contains($textLower, 'kebun') || str_contains($textLower, 'agro')) {
            return 'Agribisnis / Pertanian';
        }

        return str_contains(strtolower($bentuk), 'bangunan') ? 'Komersial / Bangunan' : 'Lahan Komersial';
    }

    protected function detectMitraType(string $mitra): string
    {
        $mitraLower = strtolower($mitra);

        if (str_starts_with($mitraLower, 'pt ') || str_contains($mitraLower, ' pt') || str_contains($mitraLower, 'persero')) {
            return 'Perusahaan (PT)';
        }
        if (str_starts_with($mitraLower, 'cv ') || str_contains($mitraLower, ' cv')) {
            return 'Perusahaan (CV)';
        }
        if (str_contains($mitraLower, 'koperasi')) {
            return 'Koperasi';
        }
        if (str_contains($mitraLower, 'yayasan')) {
            return 'Yayasan';
        }
        if (str_contains($mitraLower, 'perorangan') || str_starts_with($mitraLower, 'sdr') || str_starts_with($mitraLower, 'ibu') || str_starts_with($mitraLower, 'bapak')) {
            return 'Perorangan';
        }

        return 'Swasta / Mitra';
    }

    protected function parseExcelDate(mixed $raw): ?string
    {
        if (empty($raw) || $raw === '-') {
            return null;
        }

        try {
            if (is_numeric($raw)) {
                $dt = ExcelDate::excelToDateTimeObject((float) $raw);
                return $dt->format('Y-m-d');
            }

            $date = \Carbon\Carbon::parse((string) $raw);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function generateDefaultFacilities(string $bentuk): array
    {
        $common = ['Akses Jalan Aspal / Beton', 'Jaringan Listrik PLN', 'Dekat Pusat Keramaian'];
        if (str_contains(strtolower($bentuk), 'bangunan') || str_contains(strtolower($bentuk), 'ruko')) {
            $common[] = 'Air Bersih (PDAM / Sumur)';
            $common[] = 'Toilet / Sanitasi';
            $common[] = 'Area Parkir Kendaraan';
        }
        return $common;
    }
}
