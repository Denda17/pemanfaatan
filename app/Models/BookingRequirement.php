<?php

namespace App\Models;

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
}
