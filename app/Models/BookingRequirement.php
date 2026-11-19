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

    public function scopeAdminProcess(Builder $query): Builder
    {
        return $query->where('kategori', 'administrasi');
    }

    public function scopeApplicantSubmission(Builder $query): Builder
    {
        return $query->where('kategori', 'pemohon');
    }
}
