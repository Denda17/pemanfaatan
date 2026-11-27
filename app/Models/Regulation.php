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

    public const KATEGORI_PENGELOLAAN_BMD = 'pengelolaan_bmd';

    public const KATEGORI_KDKMP = 'kdkmp';

    public const KATEGORI_SOP = 'sop';

    /**
     * @return array<string, string>
     */
    public static function kategoriOptions(): array
    {
        return [
            self::KATEGORI_PENGELOLAAN_BMD => 'Dasar Hukum Pengelolaan BMD',
            self::KATEGORI_KDKMP => 'Dasar Hukum KDKMP',
            self::KATEGORI_SOP => 'Standar Operasional Prosedur (SOP)',
        ];
    }

    protected $fillable = [
        'slug',
        'kategori_regulasi',
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
            if (empty($regulation->kategori_regulasi)) {
                $isSop = ($regulation->tingkat === 'sop') || str_contains((string) $regulation->jenis, 'SOP');
                $regulation->kategori_regulasi = $isSop ? self::KATEGORI_SOP : self::KATEGORI_PENGELOLAAN_BMD;
            } elseif (
                ! $regulation->isDirty('kategori_regulasi')
                && $regulation->kategori_regulasi === self::KATEGORI_PENGELOLAAN_BMD
                && ($regulation->tingkat === 'sop' || str_contains((string) $regulation->jenis, 'SOP'))
            ) {
                $regulation->kategori_regulasi = self::KATEGORI_SOP;
            }

            if (empty($regulation->slug)) {
                $regulation->slug = Str::slug($regulation->nomor.'-'.$regulation->judul);
            }

            if (empty($regulation->download_name) && ! empty($regulation->file_path)) {
                $regulation->download_name = Str::slug($regulation->nomor.'-'.$regulation->judul).'.pdf';
            }

            // Sync file size if file exists on disk
            $realPath = $regulation->resolveFilePath();
            if ($realPath && file_exists($realPath)) {
                $regulation->file_size = filesize($realPath);
            }
        });
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::kategoriOptions()[$this->kategori_regulasi ?? self::KATEGORI_PENGELOLAAN_BMD] ?? 'Dasar Hukum Pengelolaan BMD';
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
        if (Storage::disk('public')->exists('regulasi-docs/'.basename($this->file_path))) {
            return Storage::disk('public')->path('regulasi-docs/'.basename($this->file_path));
        }

        // 3. Fallback direktori docs/dasar-hukum
        $docPath = base_path('docs/dasar-hukum/'.basename($this->file_path));
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

        if (! $bytes) {
            $realPath = $this->resolveFilePath();
            if ($realPath && file_exists($realPath)) {
                $bytes = filesize($realPath);
            }
        }

        if (! $bytes) {
            return '-';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2).' MB';
        }

        return round($bytes / 1024, 0).' KB';
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
