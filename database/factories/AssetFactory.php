<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        $name = 'Aset BMD '.fake()->company();

        return [
            'kode_aset' => 'AST-'.strtoupper(Str::random(8)),
            'nama_objek' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999),
            'bentuk_objek' => fake()->randomElement(['Tanah', 'Tanah dan bangunan', 'Bangunan', 'Ruko', 'Gedung']),
            'status_pengelola' => 'BPKAD Jabar',
            'luas_tanah' => fake()->randomFloat(2, 100, 5000),
            'luas_bangunan' => fake()->randomFloat(2, 50, 2500),
            'alamat' => fake()->address(),
            'kabupaten_kota' => fake()->randomElement([
                'Kota Bandung', 'Kota Bogor', 'Kota Bekasi', 'Kota Depok', 'Kota Cirebon',
                'Kabupaten Bandung', 'Kabupaten Bandung Barat', 'Kabupaten Garut', 'Kabupaten Subang',
            ]),
            'status_ketersediaan' => 'tersedia',
            'estimasi_tarif_tahunan' => fake()->numberBetween(10000000, 150000000),
            'peruntukan' => 'Komersial / Ruang Usaha / Perdagangan & Jasa',
            'deskripsi' => fake()->paragraph(),
            'is_featured' => fake()->boolean(30),
        ];
    }

    public function tersedia(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_ketersediaan' => 'tersedia',
        ]);
    }

    public function prosesBooking(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => now(),
            'booking_deadline_at' => Asset::calculateBookingDeadline(now()),
        ]);
    }

    public function tersewa(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_ketersediaan' => 'tersewa',
        ]);
    }
}
