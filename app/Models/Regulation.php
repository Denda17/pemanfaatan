<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Regulation extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'nomor',
        'tahun',
        'jenis',
        'tingkat',
        'penerbit',
        'judul',
        'deskripsi',
        'poin_kunci',
        'file_path',
        'disk',
        'download_name',
        'file_size',
        'badge_color',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'poin_kunci' => 'array',
        'tahun' => 'integer',
        'urutan' => 'integer',
        'file_size' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Regulation $regulation) {
            if (empty($regulation->slug)) {
                $regulation->slug = Str::slug($regulation->nomor . '-' . $regulation->judul);
            }

            if (empty($regulation->download_name) && !empty($regulation->file_path)) {
                $regulation->download_name = Str::slug($regulation->nomor . '-' . $regulation->judul) . '.pdf';
            }

            // Sync file size if file exists on disk
            $realPath = $regulation->resolveFilePath();
            if ($realPath && file_exists($realPath)) {
                $regulation->file_size = filesize($realPath);
            }
        });
    }

    /**
     * Scope regulasi yang aktif tayang di portal publik.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pengurutan dokumen regulasi.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('urutan', 'asc')->orderBy('tahun', 'desc');
    }

    /**
     * Temukan lokasi fisik berkas di server.
     */
    public function resolveFilePath(): ?string
    {
        if (empty($this->file_path)) {
            return null;
        }

        // 1. Cek disk yang terkonfigurasi (default: public)
        if (Storage::disk($this->disk ?? 'public')->exists($this->file_path)) {
            return Storage::disk($this->disk ?? 'public')->path($this->file_path);
        }

        // 2. Cek direktori regulasi-docs di disk public
        if (Storage::disk('public')->exists('regulasi-docs/' . basename($this->file_path))) {
            return Storage::disk('public')->path('regulasi-docs/' . basename($this->file_path));
        }

        // 3. Fallback direktori docs/dasar-hukum
        $docPath = base_path('docs/dasar-hukum/' . basename($this->file_path));
        if (file_exists($docPath)) {
            return $docPath;
        }

        // 4. Fallback path relatif dari base
        $directPath = base_path($this->file_path);
        if (file_exists($directPath)) {
            return $directPath;
        }

        return null;
    }

    /**
     * Format ukuran berkas ramah baca (MB/KB).
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;

        if (!$bytes) {
            $realPath = $this->resolveFilePath();
            if ($realPath && file_exists($realPath)) {
                $bytes = filesize($realPath);
            }
        }

        if (!$bytes) {
            return '-';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        return round($bytes / 1024, 0) . ' KB';
    }

    /**
     * Akses URL inline preview.
     */
    public function getUrlPreviewAttribute(): string
    {
        return route('regulasi.preview', $this->slug);
    }

    /**
     * Akses URL download.
     */
    public function getUrlDownloadAttribute(): string
    {
        return route('regulasi.download', $this->slug);
    }
}
