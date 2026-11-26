<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookingRequirement extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'nama_persyaratan',
        'deskripsi',
        'kategori',
        'diunggah_oleh',
        'is_required',
        'urutan',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'urutan' => 'integer',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function isAdminUploadable(): bool
    {
        return $this->diunggah_oleh === 'admin';
    }

    public function isMitraUploadable(): bool
    {
        return in_array($this->diunggah_oleh, ['mitra', 'pemohon'], true);
    }

    /**
     * Kode dokumen terbitan admin yang sah untuk dilihat dan diunduh oleh peminat:
     * 1. REQ_BA_NEGOSIASI (Berita Acara Negosiasi)
     * 2. REQ_KEPUTUSAN_GUBERNUR (Keputusan Gubernur)
     * 3. REQ_PERJANJIAN_SEWA (Perjanjian Sewa / PKS)
     * 4. REQ_ADDENDUM_PKS (Addendum Perjanjian Sewa - Opsional)
     * 5. REQ_PEMBERITAHUAN_PEMBAYARAN (Surat Pemberitahuan Pembayaran / Tagihan)
     */
    public const APPLICANT_ACCESSIBLE_ADMIN_CODES = [
        'REQ_BA_NEGOSIASI',
        'REQ_KEPUTUSAN_GUBERNUR',
        'REQ_PERJANJIAN_SEWA',
        'REQ_ADDENDUM_PKS',
        'REQ_PEMBERITAHUAN_PEMBAYARAN',
    ];

    /**
     * Cek apakah dokumen persyaratan ini dapat dilihat dan diunduh oleh peminat.
     */
    public function isViewableByApplicant(): bool
    {
        if ($this->isMitraUploadable()) {
            return true;
        }

        return in_array($this->kode, self::APPLICANT_ACCESSIBLE_ADMIN_CODES, true);
    }

    public function scopeAdminProcess(Builder $query): Builder
    {
        return $query->where('kategori', 'administrasi');
    }

    public function scopeApplicantSubmission(Builder $query): Builder
    {
        return $query->where('kategori', 'pemohon');
    }
}
