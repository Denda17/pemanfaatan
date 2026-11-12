<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_booking',
        'asset_id',
        'user_id',
        'nama_pemohon',
        'nik_atau_nib',
        'nama_instansi',
        'nomor_whatsapp',
        'email',
        'rencana_peruntukan',
        'durasi_sewa_tahun',
        'skema_pembayaran',
        'proposal_bisnis_path',
        'status',
        'catatan_admin',
        'alasan_penolakan',
        'tanggal_booking',
        'tanggal_dihubungi',
        'petugas_uptd_id',
    ];

    protected $casts = [
        'durasi_sewa_tahun' => 'integer',
        'tanggal_booking' => 'datetime',
        'tanggal_dihubungi' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if (empty($booking->kode_booking)) {
                $count = static::whereYear('created_at', now()->year)
                    ->whereMonth('created_at', now()->month)
                    ->count() + 1;
                $booking->kode_booking = 'BK-' . now()->format('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function petugasUptd(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_uptd_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }

    public function getWhatsappUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->nomor_whatsapp);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $assetName = $this->asset?->nama_objek ?? 'Aset BMD';
        $message = rawurlencode(
            "Halo Bapak/Ibu {$this->nama_pemohon},\n\n" .
            "Kami dari UPTD Pengamanan dan Pemanfaatan Aset Pemprov Jawa Barat menindaklanjuti permohonan booking pemanfaatan aset:\n" .
            "*{$assetName}* (Kode Booking: {$this->kode_booking}).\n\n" .
            "Apakah berkas persyaratan sewa sudah dapat dikoordinasikan untuk proses verifikasi lebih lanjut? Terima kasih."
        );

        return "https://wa.me/{$phone}?text={$message}";
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Tindak Lanjut',
            'contacted' => 'Sudah Dihubungi via WA',
            'document_review' => 'Verifikasi Berkas',
            'field_survey' => 'Peninjauan Lapangan',
            'approved' => 'Disetujui',
            'contract_signed' => 'PKS Terbit (Selesai)',
            'rejected' => 'Ditolak / Batal',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'contacted' => 'info',
            'document_review' => 'primary',
            'field_survey' => 'indigo',
            'approved' => 'success',
            'contract_signed' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getSkemaPembayaranLabelAttribute(): string
    {
        return match ($this->skema_pembayaran) {
            'bertahap' => 'Pembayaran Bertahap (Termin Tahunan)',
            'sekaligus' => 'Dibayar Full Sekaligus',
            default => ucfirst($this->skema_pembayaran ?? 'Sekaligus'),
        };
    }

    public function getProposalBisnisUrlAttribute(): ?string
    {
        if (!$this->proposal_bisnis_path) {
            return null;
        }

        return asset('storage/' . $this->proposal_bisnis_path);
    }
}
