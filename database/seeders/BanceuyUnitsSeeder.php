<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\AssetUnit;
use Illuminate\Database\Seeder;

class BanceuyUnitsSeeder extends Seeder
{
    public function run(): void
    {
        // Temukan atau buat Aset Induk Kawasan Banceuy
        $asset = Asset::where('nama_objek', 'like', '%Banceuy%')->first();

        if (! $asset) {
            $asset = Asset::create([
                'kode_aset' => 'BMD-JBR-0009',
                'nomor_urut_excel' => 9,
                'nama_objek' => 'Kawasan Pertokoan Banceuy Permai',
                'slug' => 'kawasan-pertokoan-banceuy-permai-0009',
                'bentuk_objek' => 'Tanah dan bangunan',
                'tipe_aset' => 'kompleks_multi_unit',
                'status_pengelola' => 'PENGELOLA',
                'luas_tanah' => 1145,
                'luas_bangunan' => 4326,
                'alamat' => 'Jl. Banceuy No. 8 & Jl. ABC, Kelurahan Braga, Kecamatan Sumur Bandung',
                'kabupaten_kota' => 'Kota Bandung',
                'latitude' => -6.9209444,
                'longitude' => 107.6098611,
                'status_ketersediaan' => 'tersedia',
                'estimasi_tarif_tahunan' => 62500000,
                'peruntukan' => 'Pertokoan, Perkantoran, Niaga & Kuliner',
                'deskripsi' => 'Komplek ruko komersial strategis Banceuy Permai berada di jantung pusat perdagangan Kota Bandung, menghubungkan Jl. Banceuy, Jl. ABC, dan Jl. Belakang Factory. Terdiri dari 62 unit ruko 2-3 lantai dengan beragam klasifikasi zona (Hook Luar, Premium, Basic, Khusus, dan Amat Khusus) yang sangat cocok untuk berbagai jenis usaha ritel, elektronik, perbankan, kuliner, dan perkantoran.',
                'foto_utama' => 'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80',
                'foto_galeri' => [
                    'https://images.unsplash.com/photo-1577495508048-b635879837f1?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                ],
                'site_plan_url' => 'siteplans/banceuy-site-plan.jpg',
                'site_plan_type' => 'interactive_svg',
                'is_featured' => true,
            ]);
        } else {
            $asset->update([
                'nama_objek' => 'Kawasan Pertokoan Banceuy Permai',
                'tipe_aset' => 'kompleks_multi_unit',
                'site_plan_url' => 'siteplans/banceuy-site-plan.jpg',
                'site_plan_type' => 'interactive_svg',
                'status_ketersediaan' => 'tersedia',
            ]);
        }

        // Hapus unit lama jika ada sebelum mengisi ulang 62 unit
        AssetUnit::where('asset_id', $asset->id)->delete();

        // Foto Galeri Khas per Kategori Zona
        $galleriesByZone = [
            'hook_luar' => [
                'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1000&q=80',
            ],
            'hook_dalam' => [
                'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1000&q=80',
            ],
            'super_premium' => [
                'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?auto=format&fit=crop&w=1000&q=80',
            ],
            'premium' => [
                'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
            ],
            'basic' => [
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1000&q=80',
            ],
            'khusus' => [
                'https://images.unsplash.com/photo-1524758631624-e2822e304c36?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=80',
            ],
            'amat_khusus' => [
                'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1000&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1000&q=80',
            ],
        ];

        // Definisi 62 Ruko Banceuy sesuai Site Plan
        $unitsData = [
            // 1. Hook Luar (Merah) - Rp 90.000.000 / thn (10 Unit)
            ['nomor' => 'A11-56', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Posisi sudut strategis menghadap langsung ke Jl. ABC & Jl. Banceuy, visibilitas jalan raya tertinggi.'],
            ['nomor' => 'A12-58', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Menghadap Jl. ABC, fasad terbuka dengan eksposur lalu lintas utama.'],
            ['nomor' => 'A13-60', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Ruko depan Jl. ABC, akses kendaraan cepat dan area parkir depan.'],
            ['nomor' => 'A25-62', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Ruko baris depan Jl. ABC, cocok untuk perbankan / apotek.'],
            ['nomor' => 'A24-64', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Ruko depan Jl. ABC dekat persimpangan Banceuy.'],
            ['nomor' => 'A23-66', 'blok' => 'Blok A', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 54, 'luas_b' => 108, 'deskripsi' => 'Sudut timur Blok A berhadapan dengan koridor utama komplek.'],
            ['nomor' => 'B1-68', 'blok' => 'Blok B', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 56, 'luas_b' => 112, 'deskripsi' => 'Sudut barat Blok B menghadap Jl. ABC, lalu lintas sangat padat.'],
            ['nomor' => 'B7-80', 'blok' => 'Blok B', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 56, 'luas_b' => 112, 'deskripsi' => 'Sudut timur Blok B menghadap Jl. ABC.'],
            ['nomor' => 'C1-82', 'blok' => 'Blok C', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 52, 'luas_b' => 104, 'deskripsi' => 'Sudut barat Blok C depan Jl. ABC.'],
            ['nomor' => 'C5-90', 'blok' => 'Blok C', 'zona' => 'hook_luar', 'label' => 'Hook Luar', 'tarif' => 90000000, 'luas_t' => 52, 'luas_b' => 104, 'deskripsi' => 'Sudut timur Blok C depan Jl. ABC dekat akses Cikapundung.'],

            // 2. Hook Dalam (Pink) - Rp 76.000.000 / thn (5 Unit)
            ['nomor' => 'B14-14', 'blok' => 'Blok B', 'zona' => 'hook_dalam', 'label' => 'Hook Dalam', 'tarif' => 76000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Sudut koridor dalam Blok B barat, akses sirkulasi internal lebar.'],
            ['nomor' => 'B8-22', 'blok' => 'Blok B', 'zona' => 'hook_dalam', 'label' => 'Hook Dalam', 'tarif' => 76000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Sudut koridor dalam Blok B timur berhadapan Blok C.'],
            ['nomor' => 'E15-23', 'blok' => 'Blok E', 'zona' => 'hook_dalam', 'label' => 'Hook Dalam', 'tarif' => 76000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Sudut koridor dalam Blok E barat depan area parkiran.'],
            ['nomor' => 'E20-28', 'blok' => 'Blok E', 'zona' => 'hook_dalam', 'label' => 'Hook Dalam', 'tarif' => 76000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Sudut koridor Blok E berdekatan dengan gedung arsip & parkir.'],
            ['nomor' => 'C18', 'blok' => 'Blok C', 'zona' => 'hook_dalam', 'label' => 'Hook Dalam', 'tarif' => 76000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Sudut Blok C bagian dalam menghadap koridor plaza.'],

            // 3. Premium (Ungu) - Rp 83.000.000 / thn (14 Unit)
            ['nomor' => 'A9-38', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium menghadap langsung Jl. Banceuy.'],
            ['nomor' => 'A8-36', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium menghadap langsung Jl. Banceuy.'],
            ['nomor' => 'A7-34', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy, lalu lintas ramai pedestrian.'],
            ['nomor' => 'A6-32', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy.'],
            ['nomor' => 'A5-30', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy.'],
            ['nomor' => 'A4-28', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy.'],
            ['nomor' => 'A3-26', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy.'],
            ['nomor' => 'A2-24', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Deretan premium Jl. Banceuy.'],
            ['nomor' => 'A1-22', 'blok' => 'Blok A', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Ujung selatan Blok A menghadap Jl. Banceuy & situs penjara Banceuy.'],
            ['nomor' => 'D7-20', 'blok' => 'Blok D', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Blok D menghadap Jl. Banceuy, berdampingan ATM Panin Bank.'],
            ['nomor' => 'D6-18', 'blok' => 'Blok D', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 50, 'luas_b' => 100, 'deskripsi' => 'Blok D menghadap Jl. Banceuy.'],
            ['nomor' => 'C2-84', 'blok' => 'Blok C', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Blok C deretan depan menghadap Jl. ABC.'],
            ['nomor' => 'C3-86', 'blok' => 'Blok C', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Blok C tengah menghadap Jl. ABC.'],
            ['nomor' => 'C4-88', 'blok' => 'Blok C', 'zona' => 'premium', 'label' => 'Premium', 'tarif' => 83000000, 'luas_t' => 48, 'luas_b' => 96, 'deskripsi' => 'Blok C deretan depan Jl. ABC.'],

            // 4. Basic (Biru) - Rp 62.500.000 / thn (21 Unit)
            ['nomor' => 'B2-70', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko koridor tengah Blok B.'],
            ['nomor' => 'B3-72', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko koridor tengah Blok B.'],
            ['nomor' => 'B4-74', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko koridor tengah Blok B.'],
            ['nomor' => 'B5-76', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko koridor tengah Blok B.'],
            ['nomor' => 'B6-78', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko koridor tengah Blok B.'],
            ['nomor' => 'B13-17', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko sisi selatan Blok B.'],
            ['nomor' => 'B12-18', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko sisi selatan Blok B.'],
            ['nomor' => 'B11-19', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko sisi selatan Blok B.'],
            ['nomor' => 'B10-20', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko sisi selatan Blok B.'],
            ['nomor' => 'B9-21', 'blok' => 'Blok B', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko sisi selatan Blok B.'],
            ['nomor' => 'D-6', 'blok' => 'Blok D', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 46, 'luas_b' => 92, 'deskripsi' => 'Ruko Blok D koridor dalam.'],
            ['nomor' => 'E16-24', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi barat.'],
            ['nomor' => 'E17-25', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi barat.'],
            ['nomor' => 'E18-26', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi barat.'],
            ['nomor' => 'E19-27', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi barat.'],
            ['nomor' => 'E8-39', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E deretan dalam.'],
            ['nomor' => 'E9-38', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E deretan dalam.'],
            ['nomor' => 'E10-37', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E deretan dalam.'],
            ['nomor' => 'E13-47', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi timur.'],
            ['nomor' => 'E12-46', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi timur.'],
            ['nomor' => 'E12a-45', 'blok' => 'Blok E', 'zona' => 'basic', 'label' => 'Basic', 'tarif' => 62500000, 'luas_t' => 45, 'luas_b' => 90, 'deskripsi' => 'Ruko Blok E sisi timur dekat gedung parkiran.'],

            // 5. Khusus (Hijau) - Rp 42.500.000 / thn (8 Unit)
            ['nomor' => 'A21-14', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam, sangat cocok untuk gudang/ekspedisi/distribusi.'],
            ['nomor' => 'A20-13', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A19-12', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A18-11', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A17-10', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A16-9', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A15-8', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam.'],
            ['nomor' => 'A14-7', 'blok' => 'Blok A', 'zona' => 'khusus', 'label' => 'Khusus', 'tarif' => 42500000, 'luas_t' => 42, 'luas_b' => 84, 'deskripsi' => 'Ruko Blok A deretan dalam dekat situs penjara Banceuy.'],

            // 6. Amat Khusus (Kuning) - Rp 40.000.000 / thn (4 Unit)
            ['nomor' => 'C17', 'blok' => 'Blok C', 'zona' => 'amat_khusus', 'label' => 'Amat Khusus', 'tarif' => 40000000, 'luas_t' => 40, 'luas_b' => 80, 'deskripsi' => 'Ruko Blok C deretan selatan, tarif sangat terjangkau untuk UMKM.'],
            ['nomor' => 'C16', 'blok' => 'Blok C', 'zona' => 'amat_khusus', 'label' => 'Amat Khusus', 'tarif' => 40000000, 'luas_t' => 40, 'luas_b' => 80, 'deskripsi' => 'Ruko Blok C deretan selatan.'],
            ['nomor' => 'C15', 'blok' => 'Blok C', 'zona' => 'amat_khusus', 'label' => 'Amat Khusus', 'tarif' => 40000000, 'luas_t' => 40, 'luas_b' => 80, 'deskripsi' => 'Ruko Blok C deretan selatan.'],
            ['nomor' => 'C14', 'blok' => 'Blok C', 'zona' => 'amat_khusus', 'label' => 'Amat Khusus', 'tarif' => 40000000, 'luas_t' => 40, 'luas_b' => 80, 'deskripsi' => 'Ruko Blok C deretan selatan berhadapan area parkiran.'],
        ];

        // Seed setiap unit ruko dengan status realistis: mayoritas tersedia, beberapa tersewa / proses booking
        $occupiedUnits = ['B1-68', 'B7-80', 'A1-22', 'A2-24', 'D6-18', 'B2-70', 'B3-72', 'E8-39', 'A21-14', 'C14'];
        $processBookingUnits = ['A11-56', 'C18']; // Unit yang sedang dalam 5 hari countdown

        foreach ($unitsData as $idx => $u) {
            $status = 'tersedia';
            $openedAt = null;
            $deadlineAt = null;

            if (in_array($u['nomor'], $occupiedUnits)) {
                $status = 'tersewa';
            } elseif (in_array($u['nomor'], $processBookingUnits)) {
                $status = 'proses_booking';
                $openedAt = now()->subDays(1);
                $deadlineAt = now()->addWeekdays(4)->endOfDay();
            }

            $zoneGallery = $galleriesByZone[$u['zona']] ?? $galleriesByZone['basic'];
            // Variasikan foto utama dengan index agar terlihat beragam
            $mainPhoto = $zoneGallery[$idx % count($zoneGallery)];

            AssetUnit::create([
                'asset_id' => $asset->id,
                'nomor_unit' => $u['nomor'],
                'blok' => $u['blok'],
                'kategori_zona' => $u['zona'],
                'kategori_label' => $u['label'],
                'luas_tanah' => $u['luas_t'],
                'luas_bangunan' => $u['luas_b'],
                'tarif_tahunan' => $u['tarif'],
                'status_ketersediaan' => $status,
                'booking_opened_at' => $openedAt,
                'booking_deadline_at' => $deadlineAt,
                'foto_utama' => $mainPhoto,
                'foto_galeri' => $zoneGallery,
                'deskripsi' => $u['deskripsi'],
                'svg_key' => 'ruko-'.strtolower(str_replace([' ', '-', '/'], '_', $u['nomor'])),
            ]);
        }
    }
}
