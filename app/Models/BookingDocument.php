<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class BookingDocument extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'booking_id',
        'booking_requirement_id',
        'nomor_dokumen',
        'tanggal_dokumen',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'status_verifikasi',
        'catatan',
        'admin_id',
    ];

    protected $casts = [
        'tanggal_dokumen' => 'date',
        'file_size' => 'integer',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(BookingRequirement::class, 'booking_requirement_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
