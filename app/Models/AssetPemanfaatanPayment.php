<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class AssetPemanfaatanPayment extends Model
{
    use HasFactory;

    protected $table = 'asset_pemanfaatan_payments';

    protected $fillable = [
        'asset_pemanfaatan_id',
        'termin_ke',
        'tahun_ke',
        'nominal_kewajiban',
        'nominal_disetor',
        'tanggal_jatuh_tempo',
        'tanggal_bayar',
        'nomor_sts',
        'status_pembayaran',
        'bukti_bayar_path',
        'catatan',
        'verified_by',
    ];

    protected $casts = [
        'termin_ke' => 'integer',
        'nominal_kewajiban' => 'decimal:2',
        'nominal_disetor' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
    ];

    public function pemanfaatan(): BelongsTo
    {
        return $this->belongsTo(AssetPemanfaatan::class, 'asset_pemanfaatan_id');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getBuktiBayarUrlAttribute(): ?string
    {
        if (!$this->bukti_bayar_path) {
            return null;
        }

        if (str_starts_with($this->bukti_bayar_path, 'http://') || str_starts_with($this->bukti_bayar_path, 'https://')) {
            return $this->bukti_bayar_path;
        }

        return Storage::disk('public')->url($this->bukti_bayar_path);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status_pembayaran) {
            'lunas' => 'Lunas (Disetor Kasda)',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'jatuh_tempo' => 'Lewat Jatuh Tempo',
            'batal' => 'Dibatalkan',
            default => ucfirst(str_replace('_', ' ', $this->status_pembayaran)),
        };
    }
}
