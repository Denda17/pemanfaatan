<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AssetUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'nomor_unit',
        'blok',
        'kategori_zona',
        'kategori_label',
        'luas_tanah',
        'luas_bangunan',
        'tarif_tahunan',
        'status_ketersediaan',
        'booking_opened_at',
        'booking_deadline_at',
        'foto_utama',
        'foto_galeri',
        'deskripsi',
        'svg_key',
    ];

    protected $casts = [
        'foto_galeri' => 'array',
        'tarif_tahunan' => 'decimal:2',
        'luas_tanah' => 'decimal:2',
        'luas_bangunan' => 'decimal:2',
        'booking_opened_at' => 'datetime',
        'booking_deadline_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_tarif',
        'thumbnail_url',
        'gallery_urls',
        'color_hex',
        'badge_color_class',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'asset_unit_id');
    }

    public function getFormattedTarifAttribute(): string
    {
        if (! $this->tarif_tahunan || $this->tarif_tahunan <= 0) {
            return 'Sesuai Penilaian Resmi';
        }

        return 'Rp '.number_format($this->tarif_tahunan, 0, ',', '.').' / tahun';
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (! empty($this->foto_utama)) {
            if (str_starts_with($this->foto_utama, 'http://') || str_starts_with($this->foto_utama, 'https://')) {
                return $this->foto_utama;
            }

            return Storage::disk('public')->url($this->foto_utama);
        }

        // Fallback ke foto utama kawasan
        return $this->asset?->thumbnail_url ?? 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?auto=format&fit=crop&w=800&q=80';
    }

    public function getGalleryUrlsAttribute(): array
    {
        $urls = [];

        if (! empty($this->foto_utama)) {
            $urls[] = $this->thumbnail_url;
        }

        if (is_array($this->foto_galeri) && count($this->foto_galeri) > 0) {
            foreach ($this->foto_galeri as $item) {
                if (empty($item)) {
                    continue;
                }

                if (str_starts_with($item, 'http://') || str_starts_with($item, 'https://')) {
                    $urls[] = $item;
                } else {
                    $urls[] = Storage::disk('public')->url($item);
                }
            }
        }

        $urls = array_values(array_unique($urls));

        if (count($urls) > 0) {
            return $urls;
        }

        // Fallback ke galeri aset induk
        return $this->asset?->gallery_urls ?? [$this->thumbnail_url];
    }

    public function getColorHexAttribute(): string
    {
        return match ($this->kategori_zona) {
            'hook_luar', 'hook' => '#ef4444',
            'hook_dalam' => '#ec4899',
            'super_premium' => '#94a3b8',
            'premium' => '#8b5cf6',
            'basic' => '#0ea5e9',
            'khusus' => '#22c55e',
            'amat_khusus' => '#eab308',
            default => '#10b981',
        };
    }

    public function getBadgeColorClassAttribute(): string
    {
        return match ($this->kategori_zona) {
            'hook_luar', 'hook' => 'bg-rose-50 text-rose-800 border-rose-300 ring-rose-500/20',
            'hook_dalam' => 'bg-pink-50 text-pink-800 border-pink-300 ring-pink-500/20',
            'super_premium' => 'bg-slate-100 text-slate-800 border-slate-300 ring-slate-500/20',
            'premium' => 'bg-purple-50 text-purple-800 border-purple-300 ring-purple-500/20',
            'basic' => 'bg-sky-50 text-sky-800 border-sky-300 ring-sky-500/20',
            'khusus' => 'bg-emerald-50 text-emerald-800 border-emerald-300 ring-emerald-500/20',
            'amat_khusus' => 'bg-amber-50 text-amber-800 border-amber-300 ring-amber-500/20',
            default => 'bg-gray-100 text-gray-800 border-gray-300 ring-gray-500/20',
        };
    }

    public function isAvailable(): bool
    {
        return $this->status_ketersediaan === 'tersedia';
    }

    public function isBookingOpen(): bool
    {
        if ($this->status_ketersediaan === 'tersewa') {
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

    public function isBookingClosed(): bool
    {
        return ! $this->isBookingOpen();
    }

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

    public function getBookingDeadlineIsoAttribute(): ?string
    {
        return $this->booking_deadline_at?->toIso8601String();
    }

    public function getFormattedBookingDeadlineAttribute(): ?string
    {
        if (! $this->booking_deadline_at) {
            return null;
        }

        return $this->booking_deadline_at->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i').' WIB';
    }

    public function startBookingCountdown(): void
    {
        $openedAt = now();
        $deadlineAt = now()->addWeekdays(5)->endOfDay();

        $this->update([
            'status_ketersediaan' => 'proses_booking',
            'booking_opened_at' => $this->booking_opened_at ?? $openedAt,
            'booking_deadline_at' => $deadlineAt,
        ]);
    }
}
