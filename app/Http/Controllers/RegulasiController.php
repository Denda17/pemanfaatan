<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class RegulasiController extends Controller
{
    /**
     * Daftar dokumen regulasi dan dasar hukum resmi pemanfaatan BMD.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function getRegulations(): array
    {
        return [
            [
                'slug' => 'perda-jabar-3-2026',
                'nomor' => 'Nomor 3 Tahun 2026',
                'tahun' => 2026,
                'jenis' => 'Peraturan Daerah (Perda)',
                'tingkat' => 'daerah',
                'penerbit' => 'Pemerintah Daerah Provinsi Jawa Barat',
                'judul' => 'Pengelolaan Barang Milik Daerah Provinsi Jawa Barat',
                'deskripsi' => 'Regulasi payung tingkat daerah yang mengatur perencanaan kebutuhan, pengadaan, penggunaan, pemanfaatan, pengamanan, pemeliharaan, penilaian, pemindahtanganan, serta pengawasan dan pengendalian BMD di lingkungan Pemprov Jawa Barat.',
                'poin_kunci' => [
                    'Bentuk pemanfaatan: Sewa, Kerjasama Pemanfaatan (KSP), Bangun Guna Serah (BGS), Bangun Serah Guna (BSG), dan Pinjam Pakai',
                    'Wewenang Gubernur & Sekda selaku Pengelola Barang dalam persetujuan pemanfaatan BMD',
                    'Kewajiban penyetoran seluruh hasil penerimaan sewa langsung ke Kas Daerah (Kasda) Pemprov Jabar',
                ],
                'filename' => 'Perda Provinsi Jawa Barat Nomor 3 Tahun 2026.pdf',
                'download_name' => 'Perda-Jabar-No-3-Tahun-2026-Pengelolaan-BMD.pdf',
                'badge_color' => 'emerald',
            ],
            [
                'slug' => 'pergub-jabar-20-2021',
                'nomor' => 'Nomor 20 Tahun 2021',
                'tahun' => 2021,
                'jenis' => 'Peraturan Gubernur (Pergub)',
                'tingkat' => 'daerah',
                'penerbit' => 'Gubernur Jawa Barat',
                'judul' => 'Peraturan Pelaksanaan Pemanfaatan Barang Milik Daerah',
                'deskripsi' => 'Pedoman teknis operasional dan tata cara pelaksanaan pemanfaatan BMD bagi perangkat daerah dan mitra penyewa di Jawa Barat, mencakup mekanisme pengajuan permohonan, peninjauan lapangan, serta penandatanganan Perjanjian Kerjasama (PKS).',
                'poin_kunci' => [
                    'Prosedur teknis penerimaan & verifikasi berkas permohonan sewa oleh UPTD Pengamanan & Pemanfaatan Aset',
                    'Format baku naskah Perjanjian Kerjasama (PKS) sewa dan hak/kewajiban mitra penyewa',
                    'Mekanisme evaluasi periodik dan pengamanan fisik serta yuridis terhadap objek yang dikerjasamakan',
                ],
                'filename' => '2021pg00320020.pdf',
                'download_name' => 'Pergub-Jabar-No-20-Tahun-2021-Pelaksanaan-Pemanfaatan-BMD.pdf',
                'badge_color' => 'teal',
            ],
            [
                'slug' => 'permendagri-7-2024',
                'nomor' => 'Nomor 7 Tahun 2024',
                'tahun' => 2024,
                'jenis' => 'Peraturan Menteri Dalam Negeri',
                'tingkat' => 'kemendagri',
                'penerbit' => 'Kementerian Dalam Negeri Republik Indonesia',
                'judul' => 'Perubahan atas Permendagri No. 19/2016 tentang Pedoman Pengelolaan Barang Milik Daerah',
                'deskripsi' => 'Regulasi nasional terbaru yang mengatur formulasi perhitungan tarif pokok sewa BMD, pembobotan faktor penyesuaian sewa, serta insentif afirmatif berupa keringanan tarif bagi Pelaku Usaha Mikro, Kecil, Koperasi, dan kegiatan sosial.',
                'poin_kunci' => [
                    'Formula resmi tarif sewa: Tarif Pokok Sewa × Faktor Penyesuaian Sewa',
                    'Faktor insentif afirmatif: 25% - 50% untuk Koperasi dan Usaha Mikro/Kecil',
                    'Ketentuan jangka waktu sewa hingga 5 tahun dan perpanjangan berkala',
                ],
                'filename' => 'Permendagri No 7 Tahun 2024_OCR.pdf',
                'download_name' => 'Permendagri-No-7-Tahun-2024-Tarif-Sewa-BMD.pdf',
                'badge_color' => 'blue',
            ],
            [
                'slug' => 'permendagri-19-2016',
                'nomor' => 'Nomor 19 Tahun 2016',
                'tahun' => 2016,
                'jenis' => 'Peraturan Menteri Dalam Negeri',
                'tingkat' => 'kemendagri',
                'penerbit' => 'Kementerian Dalam Negeri Republik Indonesia',
                'judul' => 'Pedoman Pengelolaan Barang Milik Daerah',
                'deskripsi' => 'Pedoman komprehensif tata kelola siklus BMD di seluruh Indonesia, termasuk asas legalitas batas waktu pengajuan perpanjangan sewa paling lambat 6 (enam) bulan sebelum masa kontrak berakhir.',
                'poin_kunci' => [
                    'Pasal 100: Pengajuan perpanjangan sewa wajib diajukan paling lambat 6 bulan sebelum berakhir',
                    'Standar penetapan objek yang dapat dan tidak dapat dipindahtangankan/disewakan',
                    'Prinsip efisiensi, efektivitas, kepatutan, dan kepastian nilai ekonomi daerah',
                ],
                'filename' => 'Permendagri Nomor 19 Tahun 2016.pdf',
                'download_name' => 'Permendagri-No-19-Tahun-2016-Pedoman-Pengelolaan-BMD.pdf',
                'badge_color' => 'indigo',
            ],
            [
                'slug' => 'pp-28-2020',
                'nomor' => 'Nomor 28 Tahun 2020',
                'tahun' => 2020,
                'jenis' => 'Peraturan Pemerintah (PP)',
                'tingkat' => 'pusat',
                'penerbit' => 'Presiden Republik Indonesia',
                'judul' => 'Perubahan atas PP No. 27/2014 tentang Pengelolaan Barang Milik Negara/Daerah',
                'deskripsi' => 'Peraturan Pemerintah yang memperluas fleksibilitas skema pemanfaatan aset pemerintah untuk mendukung investasi, kemudahan berusaha, serta optimalisasi Pendapatan Asli Daerah (PAD).',
                'poin_kunci' => [
                    'Penyempurnaan tata cara sewa, KSP, dan pemanfaatan infrastruktur strategis daerah',
                    'Penyederhanaan birokrasi perizinan pemanfaatan aset untuk pertumbuhan ekonomi lokal',
                    'Kewajiban pengamanan aset sebelum, selama, dan setelah masa kerjasama berakhir',
                ],
                'filename' => 'PP Nomor 28 Tahun 2020.pdf',
                'download_name' => 'PP-No-28-Tahun-2020-Pengelolaan-BMN-BMD.pdf',
                'badge_color' => 'purple',
            ],
            [
                'slug' => 'pp-27-2014',
                'nomor' => 'Nomor 27 Tahun 2014',
                'tahun' => 2014,
                'jenis' => 'Peraturan Pemerintah (PP)',
                'tingkat' => 'pusat',
                'penerbit' => 'Presiden Republik Indonesia',
                'judul' => 'Pengelolaan Barang Milik Negara / Daerah',
                'deskripsi' => 'Landasan hukum utama nasional yang menegaskan kedudukan hukum, prinsip akuntabilitas, pembagian kewenangan pengelola/pengguna barang, serta batasan hukum pemanfaatan kekayaan daerah.',
                'poin_kunci' => [
                    'Asas fungsional, kepastian hukum, transparansi, efisiensi, dan akuntabilitas publik',
                    'Definisi yuridis pemanfaatan sebagai pendayagunaan BMD tanpa mengubah status kepemilikan',
                    'Larangan menjaminkan atau menggadaikan BMD kepada pihak ketiga',
                ],
                'filename' => 'PP Nomor 27 Tahun 2014.pdf',
                'download_name' => 'PP-No-27-Tahun-2014-Pengelolaan-BMN-BMD.pdf',
                'badge_color' => 'slate',
            ],
        ];
    }

    /**
     * Halaman indeks daftar dasar hukum & regulasi.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->get('q', ''));
        $kategori = trim((string) $request->get('kategori', 'semua'));

        $regulations = collect($this->getRegulations())->map(function ($item) {
            $filePath = base_path('docs/dasar-hukum/' . $item['filename']);
            $fileSize = file_exists($filePath) ? filesize($filePath) : 0;
            $item['size_formatted'] = $fileSize > 0 ? round($fileSize / (1024 * 1024), 2) . ' MB' : '-';
            $item['file_exists'] = file_exists($filePath);
            $item['url_preview'] = route('regulasi.preview', $item['slug']);
            $item['url_download'] = route('regulasi.download', $item['slug']);
            return $item;
        });

        if (!empty($kategori) && $kategori !== 'semua') {
            $regulations = $regulations->where('tingkat', $kategori);
        }

        if (!empty($search)) {
            $searchLower = Str::lower($search);
            $regulations = $regulations->filter(function ($item) use ($searchLower) {
                return Str::contains(Str::lower($item['nomor']), $searchLower)
                    || Str::contains(Str::lower($item['jenis']), $searchLower)
                    || Str::contains(Str::lower($item['judul']), $searchLower)
                    || Str::contains(Str::lower($item['deskripsi']), $searchLower)
                    || Str::contains(Str::lower(implode(' ', $item['poin_kunci'])), $searchLower);
            });
        }

        $counts = [
            'semua' => count($this->getRegulations()),
            'daerah' => collect($this->getRegulations())->where('tingkat', 'daerah')->count(),
            'kemendagri' => collect($this->getRegulations())->where('tingkat', 'kemendagri')->count(),
            'pusat' => collect($this->getRegulations())->where('tingkat', 'pusat')->count(),
        ];

        return view('pages.regulasi', compact('regulations', 'search', 'kategori', 'counts'));
    }

    /**
     * Tampilkan berkas PDF langsung di peramban (in-browser preview).
     */
    public function preview(string $slug): BinaryFileResponse
    {
        $item = collect($this->getRegulations())->firstWhere('slug', $slug);
        abort_unless($item, 404, 'Regulasi tidak ditemukan.');

        $filePath = base_path('docs/dasar-hukum/' . $item['filename']);
        abort_unless(file_exists($filePath), 404, 'File dokumen regulasi belum tersedia di server.');

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $item['download_name'] . '"',
        ]);
    }

    /**
     * Unduh berkas PDF dokumen regulasi.
     */
    public function download(string $slug): BinaryFileResponse
    {
        $item = collect($this->getRegulations())->firstWhere('slug', $slug);
        abort_unless($item, 404, 'Regulasi tidak ditemukan.');

        $filePath = base_path('docs/dasar-hukum/' . $item['filename']);
        abort_unless(file_exists($filePath), 404, 'File dokumen regulasi belum tersedia di server.');

        return response()->download($filePath, $item['download_name'], [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
