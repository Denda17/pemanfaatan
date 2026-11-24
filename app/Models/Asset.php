<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
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
        'booking_opened_at',
        'booking_deadline_at',
        'estimasi_tarif_tahunan',
        'peruntukan',
        'deskripsi',
        'foto_utama',
        'foto_galeri',
        'site_plan_url',
        'site_plan_type',
        'tipe_aset',
        'video_path',
        'video_url',
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
        'booking_opened_at' => 'datetime',
        'booking_deadline_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($asset) {
            if (empty($asset->slug)) {
                $asset->slug = Str::slug($asset->nama_objek).'-'.Str::random(5);
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
        if (! empty($this->foto_utama)) {
            if (str_starts_with($this->foto_utama, 'http://') || str_starts_with($this->foto_utama, 'https://')) {
                return $this->foto_utama;
            }

            return Storage::disk('public')->url($this->foto_utama);
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
        if (! empty($this->foto_galeri) && is_array($this->foto_galeri)) {
            foreach ($this->foto_galeri as $item) {
                if (str_starts_with($item, 'http://') || str_starts_with($item, 'https://')) {
                    $urls[] = $item;
                } else {
                    $urls[] = Storage::disk('public')->url($item);
                }
            }
        }

        // 3. Spatie Media Gallery
        foreach ($this->getMedia('gallery') as $media) {
            $urls[] = $media->getUrl();
        }

        // 4. Default fallback jika hanya ada foto utama
        if (count($urls) === 1) {
            $urls = array_merge($urls, [
                'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1628744448840-55bdb2497bd4?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1582407947304-fd86f028f716?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1500076656116-558758c991c1?auto=format&fit=crop&w=1200&q=80',
            ]);
        }

        return array_slice(array_values(array_unique($urls)), 0, 10);
    }

    public function hasVideo(): bool
    {
        return ! empty($this->video_path) || ! empty($this->video_url);
    }

    public function isVideoDirectFile(): bool
    {
        if (! empty($this->video_path)) {
            return true;
        }

        if (empty($this->video_url)) {
            return false;
        }

        $url = strtolower((string) $this->video_url);

        return str_ends_with($url, '.mp4') || str_ends_with($url, '.webm') || str_ends_with($url, '.ogg') || str_ends_with($url, '.mov');
    }

    public function getVideoPlayUrlAttribute(): ?string
    {
        if (! empty($this->video_path)) {
            return Storage::disk('public')->url($this->video_path);
        }

        if (empty($this->video_url)) {
            return null;
        }

        return $this->video_url;
    }

    public function getVideoEmbedUrlAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return $this->video_play_url;
        }

        $url = trim((string) $this->video_url);

        // YouTube full URL atau short URL
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $matches)) {
            return 'https://www.youtube.com/embed/'.$matches[1].'?rel=0&autoplay=0';
        }

        // Vimeo URL
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/i', $url, $matches)) {
            return 'https://player.vimeo.com/video/'.($matches[3] ?? $matches[1]);
        }

        return $this->video_play_url;
    }

    public function getSitePlanImageUrlAttribute(): ?string
    {
        if (empty($this->site_plan_url)) {
            return null;
        }

        if (str_starts_with($this->site_plan_url, 'http://') || str_starts_with($this->site_plan_url, 'https://')) {
            return $this->site_plan_url;
        }

        return Storage::disk('public')->url($this->site_plan_url);
    }

    public function hasSitePlan(): bool
    {
        return ! empty($this->site_plan_url);
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

    public function units(): HasMany
    {
        return $this->hasMany(AssetUnit::class)->orderBy('nomor_unit');
    }

    public function availableUnits(): HasMany
    {
        return $this->hasMany(AssetUnit::class)
            ->where(function ($q) {
                $q->where('status_ketersediaan', 'tersedia')
                    ->orWhere(function ($q2) {
                        $q2->where('status_ketersediaan', 'proses_booking')
                            ->whereNotNull('booking_deadline_at')
                            ->where('booking_deadline_at', '>=', now());
                    });
            })
            ->orderBy('nomor_unit');
    }

    public function isMultiUnit(): bool
    {
        if ($this->tipe_aset === 'kompleks_multi_unit') {
            return true;
        }

        return $this->units()->exists();
    }

    public function getTotalUnitsCountAttribute(): int
    {
        return $this->units()->count();
    }

    public function getAvailableUnitsCountAttribute(): int
    {
        return $this->availableUnits()->count();
    }

    public function getRentPriceRangeAttribute(): string
    {
        if (! $this->isMultiUnit()) {
            if (! $this->estimasi_tarif_tahunan || $this->estimasi_tarif_tahunan <= 0) {
                return 'Sesuai Penilaian Resmi';
            }

            return 'Rp '.number_format($this->estimasi_tarif_tahunan, 0, ',', '.').' / thn';
        }

        $min = $this->units()->min('tarif_tahunan');
        $max = $this->units()->max('tarif_tahunan');

        if (! $min || ! $max || $min <= 0) {
            return 'Rp 40.000.000 – Rp 90.000.000 / thn';
        }

        if ($min == $max) {
            return 'Rp '.number_format($min, 0, ',', '.').' / thn';
        }

        return 'Rp '.number_format($min, 0, ',', '.').' – Rp '.number_format($max, 0, ',', '.').' / thn';
    }

    public function activeBooking(): HasOne
    {
        return $this->hasOne(Booking::class)
            ->whereNotIn('status', ['rejected'])
            ->latestOfMany();
    }

    /**
     * Cek apakah aset masih membuka pengajuan minat (tersedia atau dalam jendela 5 hari kerja).
     */
    public function isBookingOpen(): bool
    {
        if ($this->status_ketersediaan === 'tersewa' || $this->status_ketersediaan === 'pemeliharaan') {
            return false;
        }

        if ($this->status_ketersediaan === 'tersedia') {
            return true;
        }

        if ($this->status_ketersediaan === 'proses_booking') {
            if (! $this->booking_deadline_at) {
                return false;
            }

            return now()->lte($this->booking_deadline_at);
        }

        return false;
    }

    /**
     * Cek apakah jendela pengajuan minat untuk aset ini telah ditutup.
     */
    public function isBookingClosed(): bool
    {
        if ($this->status_ketersediaan === 'tersedia') {
            return false;
        }

        if ($this->status_ketersediaan === 'proses_booking') {
            if ($this->booking_deadline_at) {
                return now()->gt($this->booking_deadline_at);
            }

            return true;
        }

        return true;
    }

    /**
     * Cek apakah aset sedang dalam proses administratif internal tanpa deadline publik.
     */
    public function isAdministrativeProcess(): bool
    {
        return $this->status_ketersediaan === 'proses_booking' && ! $this->booking_deadline_at;
    }

    /**
     * Teks sisa waktu batas akhir booking (humanized).
     */
    public function getRemainingBookingTimeAttribute(): ?string
    {
        if (! $this->booking_deadline_at || now()->gt($this->booking_deadline_at)) {
            return null;
        }

        $totalSeconds = (int) now()->diffInSeconds($this->booking_deadline_at);
        $days = (int) floor($totalSeconds / 86400);
        $hours = (int) floor(($totalSeconds % 86400) / 3600);
        $minutes = (int) floor(($totalSeconds % 3600) / 60);

        if ($days > 0) {
            return "{$days} hari {$hours} jam lagi";
        }

        if ($hours > 0) {
            return "{$hours} jam {$minutes} menit lagi";
        }

        return "{$minutes} menit lagi";
    }

    public function getRemainingSecondsAttribute(): int
    {
        if (! $this->booking_deadline_at || now()->gt($this->booking_deadline_at)) {
            return 0;
        }

        return (int) now()->diffInSeconds($this->booking_deadline_at);
    }

    public function getBookingDeadlineIsoAttribute(): ?string
    {
        return $this->booking_deadline_at?->toIso8601String();
    }

    /**
     * Scope aset yang siap diajukan sewa oleh calon mitra / publik:
     * 1. Status 'tersedia'
     * 2. Atau status 'proses_booking' yang jendela minatnya masih terbuka (dalam batas 5 hari kerja)
     */
    public function scopeSiapSewa($query)
    {
        return $query->where(function ($q) {
            $q->where('status_ketersediaan', 'tersedia')
                ->orWhere(function ($sub) {
                    $sub->where('status_ketersediaan', 'proses_booking')
                        ->whereNotNull('booking_deadline_at')
                        ->where('booking_deadline_at', '>=', now());
                });
        });
    }

    /**
     * Format tanggal dan jam batas akhir pengajuan booking resmi.
     */
    public function getFormattedBookingDeadlineAttribute(): ?string
    {
        if (! $this->booking_deadline_at) {
            return null;
        }

        return $this->booking_deadline_at->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i').' WIB';
    }

    /**
     * Sinkronisasi status ketersediaan dan deadline aset berdasarkan status booking yang ada.
     * Jika semua pemohon berstatus 'rejected', status aset otomatis kembali ke 'tersedia' dan deadline dibersihkan.
     */
    public function syncBookingStatus(): void
    {
        $hasContract = $this->bookings()->where('status', 'contract_signed')->exists();
        if ($hasContract) {
            $this->update(['status_ketersediaan' => 'tersewa']);

            return;
        }

        $activeBookings = $this->bookings()->whereNotIn('status', ['rejected', 'expired', 'cancelled'])->get();
        if ($activeBookings->isEmpty()) {
            // Semua pengajuan ditolak/gugur atau tidak ada peminat aktif -> reset ke tersedia & bersihkan deadline
            $this->update([
                'status_ketersediaan' => 'tersedia',
                'booking_opened_at' => null,
                'booking_deadline_at' => null,
            ]);

            return;
        }

        // Masih ada peminat aktif
        $updates = ['status_ketersediaan' => 'proses_booking'];
        if (! $this->booking_deadline_at) {
            $earliest = $activeBookings->sortBy('created_at')->first();
            $opened = $earliest?->created_at ?? now();
            $updates['booking_opened_at'] = $opened;
            $updates['booking_deadline_at'] = self::calculateBookingDeadline($opened);
        }

        $this->update($updates);
    }

    /**
     * Hitung deadline booking 5 hari kerja presisi:
     * - Hari klik dihitung sebagai hari kerja ke-1 (Senin-Jumat).
     * - Batas waktu adalah hari kerja ke-5 pukul 23:59:59 WIB.
     * - Akhir pekan (Sabtu & Minggu) tidak dihitung.
     */
    public static function calculateBookingDeadline(?\DateTimeInterface $from = null): Carbon
    {
        $start = $from ? Carbon::parse($from) : now();

        if ($start->isWeekend()) {
            $start = $start->nextWeekday();
        }

        return $start->addWeekdays(4)->endOfDay();
    }
}
