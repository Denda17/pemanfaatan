<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetPemanfaatan;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportBmdExcelCommand extends Command
{
    protected $signature = 'bmd:import-excel {--file=docs/data/Rekap Pemanfaatan BMD (terbaru).xlsx} {--clean}';
    protected $description = 'Import dan sinkronisasi 100% data pemanfaatan BMD dari file Excel ke database PostgreSQL';

    protected array $regencies = [
        'Kota Bandung', 'Kota Bogor', 'Kota Bekasi', 'Kota Depok', 'Kota Cirebon', 
        'Kota Sukabumi', 'Kota Tasikmalaya', 'Kota Cimahi', 'Kota Banjar',
        'Kabupaten Bandung Barat', 'Kabupaten Bandung', 'Kabupaten Bekasi', 'Kabupaten Bogor', 
        'Kabupaten Ciamis', 'Kabupaten Cianjur', 'Kabupaten Cirebon', 'Kabupaten Garut', 
        'Kabupaten Indramayu', 'Kabupaten Karawang', 'Kabupaten Kuningan', 'Kabupaten Majalengka', 
        'Kabupaten Pangandaran', 'Kabupaten Purwakarta', 'Kabupaten Subang', 'Kabupaten Sukabumi', 
        'Kabupaten Sumedang', 'Kabupaten Tasikmalaya'
    ];

    protected array $defaultCoordinates = [
        'Kota Bandung' => [-6.917464, 107.619123],
        'Kota Bogor' => [-6.597147, 106.806039],
        'Kota Bekasi' => [-6.238270, 106.975573],
        'Kota Depok' => [-6.402484, 106.794241],
        'Kota Cirebon' => [-6.732043, 108.552316],
        'Kota Sukabumi' => [-6.927703, 106.929944],
        'Kota Tasikmalaya' => [-7.327420, 108.220660],
        'Kota Cimahi' => [-6.872200, 107.542200],
        'Kota Banjar' => [-7.371100, 108.533900],
        'Kabupaten Cianjur' => [-7.294400, 107.179400],
        'Kabupaten Bandung' => [-7.025253, 107.519760],
        'Kabupaten Bandung Barat' => [-6.843600, 107.494700],
        'Kabupaten Bekasi' => [-6.364400, 107.172500],
        'Kabupaten Bogor' => [-6.551800, 106.829800],
        'Kabupaten Ciamis' => [-7.327500, 108.353300],
        'Kabupaten Cirebon' => [-6.764400, 108.478900],
        'Kabupaten Garut' => [-7.227906, 107.908699],
        'Kabupaten Indramayu' => [-6.326400, 108.320000],
        'Kabupaten Karawang' => [-6.304241, 107.307335],
        'Kabupaten Kuningan' => [-6.976400, 108.483900],
        'Kabupaten Majalengka' => [-6.836100, 108.227800],
        'Kabupaten Pangandaran' => [-7.701100, 108.494700],
        'Kabupaten Purwakarta' => [-6.556900, 107.443300],
        'Kabupaten Subang' => [-6.571600, 107.758700],
        'Kabupaten Sukabumi' => [-6.989700, 106.551400],
        'Kabupaten Sumedang' => [-6.858600, 107.926900],
        'Kabupaten Tasikmalaya' => [-7.358600, 108.113300],
    ];

    public function handle(): int
    {
        $filePath = base_path($this->option('file'));

        if (!file_exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            return Command::FAILURE;
        }

        $this->info("Membuka file Excel sumber: {$filePath}...");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Exception $e) {
            $this->error("Gagal membuka file Excel: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Hapus data kotor / legenda yang sebelumnya terimport (misal baris 58)
        $dirtyAssets = Asset::where('nama_objek', 'like', '%BERAKHIR%')
            ->orWhere('nama_objek', 'like', '%hijau :%')
            ->orWhere('nama_objek', 'like', '%ungu :%')
            ->get();
        foreach ($dirtyAssets as $da) {
            AssetPemanfaatan::where('asset_id', $da->id)->delete();
            $da->delete();
            $this->warn("Menghapus data kotor/legend lama: Asset ID {$da->id} ({$da->nama_objek})");
        }

        $parsedRows = [];

        // Parse semua baris data riil
        for ($row = 4; $row <= $highestRow; $row++) {
            $objekSewa = trim((string) $sheet->getCell("B{$row}")->getValue());
            $mitra = trim((string) $sheet->getCell("H{$row}")->getValue());

            // Filter baris kosong, header pemisah, atau legenda
            if (empty($objekSewa) && empty($mitra)) {
                continue;
            }
            if (str_contains(strtoupper($objekSewa), 'PEMANFAATAN YANG SUDAH BERAKHIR')) {
                continue;
            }
            if (str_contains(strtoupper($mitra), 'UNGU : BERMASALAH')) {
                continue;
            }
            if (str_starts_with(strtolower($objekSewa), 'jumlah') || str_starts_with(strtolower($objekSewa), 'total')) {
                continue;
            }
            if (str_contains($objekSewa, 'kalau sudah selesai naikan ke atas')) {
                continue;
            }

            // Tentukan kategori status berdasarkan seksi Excel
            $kategoriStatus = 'sewa_aktif';
            if ($row >= 61) {
                $kategoriStatus = 'berakhir';
            } elseif ($row >= 35) {
                $kategoriStatus = 'dalam_proses';
            }

            $parsedRows[] = [
                'excel_row' => $row,
                'kategori_status' => $kategoriStatus,
                'no_urut' => count($parsedRows) + 1,
                'objek_sewa' => $objekSewa,
                'kordinat_raw' => $sheet->getCell("C{$row}")->getValue(),
                'luas_tanah_raw' => $sheet->getCell("D{$row}")->getValue(),
                'luas_bangunan_raw' => $sheet->getCell("E{$row}")->getValue(),
                'bentuk_objek_raw' => $sheet->getCell("F{$row}")->getValue(),
                'status_pengelola_raw' => $sheet->getCell("G{$row}")->getValue(),
                'mitra' => $mitra,
                'besaran_sewa_raw' => $sheet->getCell("I{$row}")->getCalculatedValue(),
                'jangka_waktu_raw' => $sheet->getCell("J{$row}")->getCalculatedValue(),
                'total_sewa_raw' => $sheet->getCell("K{$row}")->getCalculatedValue(),
                'pembayaran' => trim((string) $sheet->getCell("L{$row}")->getValue()),
                'no_persetujuan' => trim((string) $sheet->getCell("M{$row}")->getValue()),
                'no_kepgub' => trim((string) $sheet->getCell("N{$row}")->getValue()),
                'no_perjanjian' => trim((string) $sheet->getCell("O{$row}")->getValue()),
                'tgl_awal_raw' => $sheet->getCell("P{$row}")->getValue(),
                'tgl_akhir_raw' => $sheet->getCell("Q{$row}")->getValue(),
                'keterangan' => trim((string) $sheet->getCell("T{$row}")->getValue()),
            ];
        }

        $this->info("Ditemukan " . count($parsedRows) . " baris data pemanfaatan BMD valid.");
        $this->output->progressStart(count($parsedRows));

        $importedAssets = 0;
        $importedPemanfaatan = 0;

        foreach ($parsedRows as $item) {
            $noUrut = $item['no_urut'];
            $kodeAset = 'BMD-JBR-' . str_pad((string) $noUrut, 4, '0', STR_PAD_LEFT);
            $objekSewa = $item['objek_sewa'];
            $mitra = $item['mitra'];
            $kategoriStatus = $item['kategori_status'];

            // Parse numbers
            $luasTanah = $this->parseNumeric($item['luas_tanah_raw']);
            $luasBangunan = $this->parseNumeric($item['luas_bangunan_raw']);
            $besaranSewa = $this->parseNumeric($item['besaran_sewa_raw']);
            $totalSewa = $this->parseNumeric($item['total_sewa_raw']);

            // Parse Jangka Waktu
            [$jangkaTahun, $jangkaTeks] = $this->parseJangkaWaktu($item['jangka_waktu_raw']);

            // Detect Kabupaten/Kota
            $kabupatenKota = $this->detectKabupatenKota($objekSewa);

            // Parse coordinates dengan fallback ke kabupaten/kota jika kosong
            [$lat, $lng] = $this->parseCoordinatesClean($item['kordinat_raw'], $kabupatenKota);

            // Parse Dates
            $tglAwal = $this->parseExcelDateClean($item['tgl_awal_raw']);
            $tglAkhir = $this->parseExcelDateClean($item['tgl_akhir_raw']);

            // Tentukan status ketersediaan Aset fisik
            if ($kategoriStatus === 'sewa_aktif') {
                $statusKetersediaan = 'tersewa';
            } elseif ($kategoriStatus === 'dalam_proses') {
                $statusKetersediaan = 'proses_booking';
            } else {
                // kategori 'berakhir': aset siap kembali dikerjasamakan / ditawarkan di marketplace
                $statusKetersediaan = 'tersedia';
            }

            // Tentukan status_perpanjangan Pemanfaatan
            if ($kategoriStatus === 'sewa_aktif') {
                $statusPerpanjangan = 'aktif';
                if ($tglAkhir) {
                    $akhirDate = Carbon::parse($tglAkhir);
                    if ($akhirDate->isPast()) {
                        $statusPerpanjangan = 'selesai';
                    } elseif ($akhirDate->diffInMonths(now()) <= 6) {
                        $statusPerpanjangan = 'peringatan_jatuh_tempo';
                    }
                }
            } elseif ($kategoriStatus === 'dalam_proses') {
                $statusPerpanjangan = 'dalam_proses';
            } else {
                // 'berakhir'
                $ketLower = strtolower($item['keterangan']);
                if (str_contains($ketLower, 'perpanjang')) {
                    $statusPerpanjangan = 'proses_perpanjangan';
                } else {
                    $statusPerpanjangan = 'selesai';
                }
            }

            $bentukObjek = !empty($item['bentuk_objek_raw']) ? trim((string) $item['bentuk_objek_raw']) : 'Tanah';
            $peruntukan = $this->detectPeruntukan($objekSewa, $bentukObjek);
            $statusPengelola = !empty($item['status_pengelola_raw']) ? trim((string) $item['status_pengelola_raw']) : 'PENGELOLA';

            // Simpan / update Aset
            $asset = Asset::updateOrCreate(
                ['kode_aset' => $kodeAset],
                [
                    'nomor_urut_excel' => $noUrut,
                    'nama_objek' => $objekSewa,
                    'slug' => Str::slug($objekSewa) . '-' . strtolower(substr($kodeAset, -4)),
                    'bentuk_objek' => $bentukObjek,
                    'status_pengelola' => $statusPengelola,
                    'luas_tanah' => $luasTanah,
                    'luas_bangunan' => $luasBangunan,
                    'alamat' => $objekSewa,
                    'kabupaten_kota' => $kabupatenKota,
                    'latitude' => $lat,
                    'longitude' => $lng,
                    'status_ketersediaan' => $statusKetersediaan,
                    'estimasi_tarif_tahunan' => $besaranSewa ?: ($totalSewa && $jangkaTahun ? $totalSewa / $jangkaTahun : null),
                    'peruntukan' => $peruntukan,
                    'deskripsi' => "Aset Barang Milik Daerah Provinsi Jawa Barat di {$kabupatenKota}. Status Pengelola: {$statusPengelola}. Peruntukan: {$peruntukan}.",
                    'fasilitas' => $this->generateDefaultFacilities($bentukObjek),
                    'is_featured' => ($noUrut <= 12),
                ]
            );
            $importedAssets++;

            // Simpan / update Data Pemanfaatan
            if (!empty($mitra) && $mitra !== '-') {
                AssetPemanfaatan::updateOrCreate(
                    [
                        'asset_id' => $asset->id,
                    ],
                    [
                        'nomor_urut_excel' => $noUrut,
                        'jenis_pemanfaatan' => 'Sewa',
                        'status_pengelola' => $statusPengelola,
                        'nama_mitra' => $mitra,
                        'jenis_mitra' => $this->detectMitraType($mitra),
                        'besaran_sewa_tahunan' => $besaranSewa,
                        'jangka_waktu_tahun' => $jangkaTahun,
                        'jangka_waktu_teks' => $jangkaTeks,
                        'total_sewa' => $totalSewa,
                        'mekanisme_pembayaran' => !empty($item['pembayaran']) ? $item['pembayaran'] : 'Sekaligus',
                        'nomor_persetujuan' => $item['no_persetujuan'] ?: null,
                        'nomor_kepgub' => $item['no_kepgub'] ?: null,
                        'nomor_perjanjian' => $item['no_perjanjian'] ?: null,
                        'tanggal_awal' => $tglAwal,
                        'tanggal_akhir' => $tglAkhir,
                        'status_perpanjangan' => $statusPerpanjangan,
                        'kategori_status' => $kategoriStatus,
                        'keterangan' => $item['keterangan'] ?: null,
                    ]
                );
                $importedPemanfaatan++;
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();

        $this->info("Import dan sinkronisasi selesai!");
        $this->info("Total Aset: {$importedAssets}");
        $this->info("Total Pemanfaatan: {$importedPemanfaatan}");

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

        // Hapus karakter non-angka kecuali koma atau titik
        $clean = preg_replace('/[^\d.,]/', '', (string) $value);
        if (empty($clean)) {
            return null;
        }

        // Tangani format ribuan Indo / koma desimal
        if (str_contains($clean, ',') && !str_contains($clean, '.')) {
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    protected function parseJangkaWaktu(mixed $raw): array
    {
        if (empty($raw) || $raw === '-') {
            return [null, null];
        }

        $rawStr = trim((string) $raw);

        if (is_numeric($raw)) {
            $val = (int) $raw;
            return [$val, "{$val} tahun"];
        }

        if (preg_match('/(\d+)\s*tahun/i', $rawStr, $m)) {
            return [(int) $m[1], "{$m[1]} tahun"];
        }

        if (preg_match('/(\d+)\s*bulan/i', $rawStr, $m)) {
            $bulan = (int) $m[1];
            $tahun = (int) ceil($bulan / 12);
            return [$tahun, "{$bulan} bulan"];
        }

        return [null, $rawStr];
    }

    protected function parseCoordinatesClean(?string $raw, string $kabupatenKota): array
    {
        if (empty($raw) || $raw === '-' || str_contains($raw, '0° S 0° W')) {
            return $this->defaultCoordinates[$kabupatenKota] ?? [-6.917464, 107.619123];
        }

        $raw = trim($raw);

        // Jika string gabungan misal "-6685981106847020"
        if (preg_match('/^(-6\d{6})(10[67]\d{6})$/', $raw, $m)) {
            return [(float) substr_replace($m[1], '.', 2, 0), (float) substr_replace($m[2], '.', 3, 0)];
        }

        // Jika string misal "-68951212,107.6351196"
        if (preg_match('/^(-6\d{7}),\s*(10\d+\.\d+)/', $raw, $m)) {
            return [(float) substr_replace($m[1], '.', 2, 0), (float) $m[2]];
        }

        // Jika string misal "-6931036956406279,107.63531270905601"
        if (preg_match('/^(-6\d{6})\d+,\s*(10\d+\.\d+)/', $raw, $m)) {
            return [round((float) substr_replace($m[1], '.', 2, 0), 7), round((float) $m[2], 7)];
        }

        // Jika string diawali dot multipel misal "-693.239.969.1746582,107.62426971945949"
        if (str_starts_with($raw, '-693.')) {
            $parts = explode(',', $raw);
            return [-6.9323997, round((float) ($parts[1] ?? 107.6242697), 7)];
        }

        // Format koma desimal misal "-7,104734.107,458259"
        if (preg_match('/^(-?\d+),(\d+)\.(10\d+),(\d+)/', $raw, $m)) {
            return [(float) ($m[1] . '.' . $m[2]), (float) ($m[3] . '.' . $m[4])];
        }

        // Format DMS rapat tanpa derajat misal "-646050\"S 1083212.2\"E"
        if (preg_match('/-?([67])(\d{2})(\d{2,3}(?:\.\d+)?)"?[Ss]\s*(10[6-9])(\d{2})(\d{2,3}(?:\.\d+)?)"?[Ee]/i', $raw, $m)) {
            $secLat = (float) $m[3];
            if ($secLat >= 100) $secLat = $secLat / 10;
            $lat = -1 * ((float)$m[1] + (float)$m[2]/60 + $secLat/3600);
            $secLng = (float) $m[6];
            if ($secLng >= 100) $secLng = $secLng / 10;
            $lng = (float)$m[4] + (float)$m[5]/60 + $secLng/3600;
            return [round($lat, 7), round($lng, 7)];
        }

        // Standard DMS misal 6°55'15.4"S 107°36'35.5"E
        if (preg_match('/(\d+)°(\d+)\'([\d.]+)"([NSns])\s*(\d+)°(\d+)\'([\d.]+)"([EWew])/', $raw, $m)) {
            $lat = (float) $m[1] + ((float) $m[2] / 60) + ((float) $m[3] / 3600);
            if (strtoupper($m[4]) === 'S') $lat = -$lat;
            $lng = (float) $m[5] + ((float) $m[6] / 60) + ((float) $m[7] / 3600);
            if (strtoupper($m[8]) === 'W') $lng = -$lng;
            return [round($lat, 7), round($lng, 7)];
        }

        // Decimal standard misal "-6.9461565,107.6217156,17" atau "-7.08867 107.46502"
        if (preg_match('/(-?\d+\.\d+)[\s,]+(10\d+\.\d+)/', $raw, $m)) {
            return [round((float) $m[1], 7), round((float) $m[2], 7)];
        }

        return $this->defaultCoordinates[$kabupatenKota] ?? [-6.917464, 107.619123];
    }

    protected function parseExcelDateClean(mixed $raw): ?string
    {
        if (empty($raw) || $raw === '-') {
            return null;
        }

        if (is_numeric($raw)) {
            $num = (float) $raw;
            // Jika tahun tunggal seperti 2025, 2030
            if ($num >= 1990 && $num <= 2050) {
                return ((int) $num) . '-01-01';
            }
            if ($num > 10000) {
                try {
                    return ExcelDate::excelToDateTimeObject($num)->format('Y-m-d');
                } catch (\Throwable $e) {}
            }
        }

        $str = trim((string) $raw);
        $str = str_ireplace('Meri', 'Mei', $str);

        $idMonths = [
            'januari' => '01', 'jan' => '01',
            'februari' => '02', 'feb' => '02',
            'maret' => '03', 'mar' => '03',
            'april' => '04', 'apr' => '04',
            'mei' => '05', 'may' => '05',
            'juni' => '06', 'jun' => '06',
            'juli' => '07', 'jul' => '07',
            'agustus' => '08', 'ags' => '08', 'agu' => '08',
            'september' => '09', 'sep' => '09',
            'oktober' => '10', 'okt' => '10', 'oct' => '10',
            'november' => '11', 'nov' => '11',
            'desember' => '12', 'des' => '12', 'dec' => '12'
        ];

        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/', $str, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = $idMonths[strtolower($m[2])] ?? null;
            $year = $m[3];
            if ($month) {
                return "{$year}-{$month}-{$day}";
            }
        }

        if (preg_match('/^(\d{4})$/', $str, $m)) {
            return "{$m[1]}-01-01";
        }

        try {
            return Carbon::parse($str)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function detectKabupatenKota(string $text): string
    {
        $t = strtolower($text);

        if (str_contains($t, 'bandung barat')) {
            return 'Kabupaten Bandung Barat';
        }
        if (str_contains($t, 'kabupaten bandung') || str_contains($t, 'kab bandung') || str_contains($t, 'kab. bandung') || str_contains($t, 'baleendah') || str_contains($t, 'pasirjambu') || str_contains($t, 'majalaya') || str_contains($t, 'mekarmaju')) {
            return 'Kabupaten Bandung';
        }
        if (str_contains($t, 'kabupaten bogor') || str_contains($t, 'kab bogor') || str_contains($t, 'kab. bogor') || str_contains($t, 'ciawi') || str_contains($t, 'telukpinang')) {
            return 'Kabupaten Bogor';
        }
        if (str_contains($t, 'harjamukti') || str_contains($t, 'kalijaga') || str_contains($t, 'kota cirebon')) {
            return 'Kota Cirebon';
        }
        if (str_contains($t, 'kabupaten cirebon') || str_contains($t, 'kab cirebon')) {
            return 'Kabupaten Cirebon';
        }
        if (str_contains($t, 'kota bogor') || str_contains($t, 'bogor tengah') || str_contains($t, 'paledang') || str_contains($t, 'bogor')) {
            return 'Kota Bogor';
        }
        if (str_contains($t, 'cianjur') || str_contains($t, 'cikadu')) {
            return 'Kabupaten Cianjur';
        }
        if (str_contains($t, 'karawang') || str_contains($t, 'tempuran')) {
            return 'Kabupaten Karawang';
        }
        if (str_contains($t, 'sumedang') || str_contains($t, 'sindangsari') || str_contains($t, 'sukasari')) {
            return 'Kabupaten Sumedang';
        }
        if (str_contains($t, 'sukabumi')) {
            return str_contains($t, 'kota sukabumi') ? 'Kota Sukabumi' : 'Kabupaten Sukabumi';
        }
        if (str_contains($t, 'garut')) {
            return 'Kabupaten Garut';
        }
        if (str_contains($t, 'subang')) {
            return 'Kabupaten Subang';
        }
        if (str_contains($t, 'purwakarta')) {
            return 'Kabupaten Purwakarta';
        }
        if (str_contains($t, 'indramayu')) {
            return 'Kabupaten Indramayu';
        }
        if (str_contains($t, 'tasikmalaya')) {
            return str_contains($t, 'kota tasikmalaya') ? 'Kota Tasikmalaya' : 'Kabupaten Tasikmalaya';
        }
        if (str_contains($t, 'ciamis')) {
            return 'Kabupaten Ciamis';
        }
        if (str_contains($t, 'kuningan')) {
            return 'Kabupaten Kuningan';
        }
        if (str_contains($t, 'majalengka')) {
            return 'Kabupaten Majalengka';
        }
        if (str_contains($t, 'pangandaran')) {
            return 'Kabupaten Pangandaran';
        }
        if (str_contains($t, 'cimahi')) {
            return 'Kota Cimahi';
        }
        if (str_contains($t, 'depok')) {
            return 'Kota Depok';
        }
        if (str_contains($t, 'bekasi')) {
            return str_contains($t, 'kota bekasi') ? 'Kota Bekasi' : 'Kabupaten Bekasi';
        }
        if (str_contains($t, 'banjar')) {
            return 'Kota Banjar';
        }

        return 'Kota Bandung';
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
        if (str_contains($textLower, 'pertanian') || str_contains($textLower, 'kebun') || str_contains($textLower, 'agro') || str_contains($textLower, 'tani')) {
            return 'Agribisnis / Pertanian';
        }

        return str_contains(strtolower($bentuk), 'bangunan') ? 'Komersial / Bangunan' : 'Lahan Komersial';
    }

    protected function detectMitraType(string $mitra): string
    {
        $mitraLower = strtolower($mitra);

        if (str_starts_with($mitraLower, 'pt ') || str_contains($mitraLower, ' pt') || str_contains($mitraLower, 'perseroda') || str_contains($mitraLower, 'persero') || str_contains($mitraLower, 'tbk')) {
            return 'Perusahaan (PT)';
        }
        if (str_starts_with($mitraLower, 'cv ') || str_contains($mitraLower, ' cv')) {
            return 'Perusahaan (CV)';
        }
        if (str_contains($mitraLower, 'koperasi') || str_starts_with($mitraLower, 'kkbd') || str_starts_with($mitraLower, 'kkmp') || str_starts_with($mitraLower, 'kdmp')) {
            return 'Koperasi';
        }
        if (str_contains($mitraLower, 'yayasan')) {
            return 'Yayasan';
        }
        if (str_contains($mitraLower, 'pemerintah desa') || str_contains($mitraLower, 'desa ')) {
            return 'Pemerintah Desa';
        }
        if (str_contains($mitraLower, 'kelompok tani') || str_contains($mitraLower, 'perkumpulan')) {
            return 'Kelompok Masyarakat / Tani';
        }
        if (str_contains($mitraLower, 'perorangan') || str_starts_with($mitraLower, 'sdr') || str_starts_with($mitraLower, 'ibu') || str_starts_with($mitraLower, 'bapak')) {
            return 'Perorangan';
        }

        return 'Swasta / Mitra';
    }

    protected function generateDefaultFacilities(string $bentuk): array
    {
        $common = ['Akses Jalan Aspal / Beton', 'Keamanan Aset Pemprov', 'Kepastian Hukum Bermaterai'];

        if (str_contains(strtolower($bentuk), 'bangunan')) {
            return array_merge($common, ['Listrik PLN', 'Air Bersih / PDAM', 'Toilet / Sanitasi', 'Area Parkir']);
        }

        return array_merge($common, ['Bebas Sengketa', 'Patok Batas Jelas']);
    }
}
