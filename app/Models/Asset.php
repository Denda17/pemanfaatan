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
        'nomor_urut_excel',
        'nama_objek',
        'slug',
        'bentuk_objek',
        'status_pengelola',
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
        'foto_utama',
        'foto_galeri',
        'site_plan_url',
        'fasilitas',
        'is_featured',
    ];

    protected $casts = [
        'luas_tanah' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'estimasi_tarif_tahunan' => 'decimal:2',
        'foto_galeri' => 'array',
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
        if (!empty($this->foto_utama)) {
            if (str_starts_with($this->foto_utama, 'http://') || str_starts_with($this->foto_utama, 'https://')) {
                return $this->foto_utama;
            }
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_utama);
        }

        $media = $this->getFirstMedia('thumbnail');
        if ($media) {
            return $media->getUrl();
        }

        return 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?auto=format&fit=crop&w=800&q=80';
    }

    /**
     * Galeri multi-foto aset (foto lapangan dari berbagai sudut)
     *
     * @return array<string>
     */
    public function getGalleryUrlsAttribute(): array
    {
        $urls = [];

        // 1. Foto utama sebagai foto pertama di galeri
        $urls[] = $this->thumbnail_url;

        // 2. Foto galeri tambahan jika diunggah admin
        if (!empty($this->foto_galeri) && is_array($this->foto_galeri)) {
            foreach ($this->foto_galeri as $item) {
                if (str_starts_with($item, 'http://') || str_starts_with($item, 'https://')) {
                    $urls[] = $item;
                } else {
                    $urls[] = \Illuminate\Support\Facades\Storage::disk('public')->url($item);
                }
            }
        }

        // 3. Spatie Media Gallery
        foreach ($this->getMedia('gallery') as $media) {
            $urls[] = $media->getUrl();
        }

        // 4. Default fallback jika hanya ada foto utama
        if (count($urls) === 1) {
            // Berikan 2 foto referensi sudut pandang arsitektural landscape & kawasan
            $urls[] = 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=800&q=80';
            $urls[] = 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=800&q=80';
        }

        return array_values(array_unique($urls));
    }

    public function getSitePlanImageUrlAttribute(): ?string
    {
        if (empty($this->site_plan_url)) {
            return null;
        }

        if (str_starts_with($this->site_plan_url, 'http://') || str_starts_with($this->site_plan_url, 'https://')) {
            return $this->site_plan_url;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->site_plan_url);
    }

    public function hasSitePlan(): bool
    {
        return !empty($this->site_plan_url);
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
