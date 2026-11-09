<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Asset extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'kode_aset',
        'nama_objek',
        'slug',
        'bentuk_objek',
        'luas_tanah',
        'luas_bangunan',
        'alamat',
        'kabupaten_kota',
        'latitude',
        'longitude',
        'status_ketersediaan',
        'estimasi_tarif_tahunan',
        'peruntukan',
        'deskripsi',
        'fasilitas',
        'is_featured',
    ];

    protected $casts = [
        'luas_tanah' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'estimasi_tarif_tahunan' => 'decimal:2',
        'fasilitas' => 'array',
        'is_featured' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($asset) {
            if (empty($asset->slug)) {
                $asset->slug = Str::slug($asset->nama_objek) . '-' . Str::random(5);
            }
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('thumbnail')
            ->singleFile();

        $this->addMediaCollection('gallery');
    }

    public function getThumbnailUrlAttribute(): string
    {
        $media = $this->getFirstMedia('thumbnail');
        if ($media) {
            return $media->getUrl();
        }

        return 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?auto=format&fit=crop&w=800&q=80';
    }

    public function pemanfaatan(): HasMany
    {
        return $this->hasMany(AssetPemanfaatan::class);
    }

    public function pemanfaatanAktif(): HasOne
    {
        return $this->hasOne(AssetPemanfaatan::class)->latestOfMany();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->whereNotIn('status', ['rejected'])
            ->latestOfMany();
    }
}
