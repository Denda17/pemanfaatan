<?php

namespace Database\Seeders;

use App\Models\Regulation;
use Illuminate\Database\Seeder;

class RegulationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regulations = [
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
                'file_path' => 'regulasi-docs/Perda Provinsi Jawa Barat Nomor 3 Tahun 2026.pdf',
                'disk' => 'public',
                'download_name' => 'Perda-Jabar-No-3-Tahun-2026-Pengelolaan-BMD.pdf',
                'badge_color' => 'emerald',
                'urutan' => 1,
                'is_active' => true,
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
                'file_path' => 'regulasi-docs/2021pg00320020.pdf',
                'disk' => 'public',
                'download_name' => 'Pergub-Jabar-No-20-Tahun-2021-Pelaksanaan-Pemanfaatan-BMD.pdf',
                'badge_color' => 'teal',
                'urutan' => 2,
                'is_active' => true,
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
                'file_path' => 'regulasi-docs/Permendagri No 7 Tahun 2024_OCR.pdf',
                'disk' => 'public',
                'download_name' => 'Permendagri-No-7-Tahun-2024-Tarif-Sewa-BMD.pdf',
                'badge_color' => 'blue',
                'urutan' => 3,
                'is_active' => true,
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
                'file_path' => 'regulasi-docs/Permendagri Nomor 19 Tahun 2016.pdf',
                'disk' => 'public',
                'download_name' => 'Permendagri-No-19-Tahun-2016-Pedoman-Pengelolaan-BMD.pdf',
                'badge_color' => 'indigo',
                'urutan' => 4,
                'is_active' => true,
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
                'file_path' => 'regulasi-docs/PP Nomor 28 Tahun 2020.pdf',
                'disk' => 'public',
                'download_name' => 'PP-No-28-Tahun-2020-Pengelolaan-BMN-BMD.pdf',
                'badge_color' => 'purple',
                'urutan' => 5,
                'is_active' => true,
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
                'file_path' => 'regulasi-docs/PP Nomor 27 Tahun 2014.pdf',
                'disk' => 'public',
                'download_name' => 'PP-No-27-Tahun-2014-Pengelolaan-BMN-BMD.pdf',
                'badge_color' => 'slate',
                'urutan' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($regulations as $data) {
            Regulation::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}
