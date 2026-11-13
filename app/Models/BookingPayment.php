<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookingPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'termin_ke',
        'tahun_ke',
        'nominal_kewajiban',
        'nominal_disetor',
        'nomor_sts',
        'tanggal_bayar',
        'tanggal_jatuh_tempo',
        'file_path',
        'file_name',
        'file_size',
        'status_pembayaran',
        'catatan',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'termin_ke' => 'integer',
        'nominal_kewajiban' => 'decimal:2',
        'nominal_disetor' => 'decimal:2',
        'tanggal_bayar' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'verified_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getBuktiUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return Storage::disk('public')->url($this->file_path);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'Lunas (Disetor Kasda)',
            'pending_verifikasi' => 'Menunggu Verifikasi Kasda',
            'ditolak' => 'Perlu Perbaikan / Ditolak',
            default => 'Belum Dibayar',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'success',
            'pending_verifikasi' => 'warning',
            'ditolak' => 'danger',
            default => 'gray',
        };
    }
}
