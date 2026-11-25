<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'kode_pembayaran',
        'kode_bayar_file_path',
        'kode_bayar_file_name',
        'kode_diminta_at',
        'kode_diterbitkan_at',
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
        'kode_diminta_at' => 'datetime',
        'kode_diterbitkan_at' => 'datetime',
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
        if (! $this->file_path) {
            return null;
        }

        if (str_starts_with($this->file_path, 'http://') || str_starts_with($this->file_path, 'https://')) {
            return $this->file_path;
        }

        return route('booking.payments.view', $this);
    }

    public function getKodeBayarFileUrlAttribute(): ?string
    {
        if (! $this->kode_bayar_file_path) {
            return null;
        }

        if (str_starts_with($this->kode_bayar_file_path, 'http://') || str_starts_with($this->kode_bayar_file_path, 'https://')) {
            return $this->kode_bayar_file_path;
        }

        return route('booking.payments.billing-code', $this);
    }

    public function hasIssuedPaymentCode(): bool
    {
        return ! empty($this->kode_pembayaran) || ! empty($this->kode_bayar_file_path);
    }

    /**
     * Selisih hari dari hari ini ke tanggal jatuh tempo (negatif berarti terlambat).
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (! $this->tanggal_jatuh_tempo) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->tanggal_jatuh_tempo->copy()->startOfDay(), false);
    }

    /**
     * Status urgensi jatuh tempo: lunas | overdue | warning (<=30 hari) | normal.
     */
    public function getDueUrgencyAttribute(): string
    {
        if ($this->status_pembayaran === 'lunas') {
            return 'lunas';
        }

        $days = $this->days_until_due;
        if ($days === null) {
            return 'normal';
        }

        if ($days < 0) {
            return 'overdue';
        }

        if ($days <= 30) {
            return 'warning';
        }

        return 'normal';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'Lunas (Disetor Kasda)',
            'pending_verifikasi' => 'Menunggu Verifikasi Kasda',
            'ditolak' => 'Perlu Perbaikan / Ditolak',
            'kode_diminta' => 'Menunggu Kode Bayar UPTD',
            'kode_terbit' => 'Kode Bayar Terbit (Siap Dibayar)',
            default => 'Belum Dibayar',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'success',
            'pending_verifikasi', 'kode_diminta' => 'warning',
            'kode_terbit' => 'info',
            'ditolak' => 'danger',
            default => 'gray',
        };
    }
}
