<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_booking',
        'asset_id',
        'asset_unit_id',
        'selected_unit_ids',
        'user_id',
        'tipe_pemohon',
        'nama_pemohon',
        'nik_atau_nib',
        'nama_instansi',
        'jabatan_pemohon',
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
        'beauty_contest_scheduled_at',
        'beauty_contest_location',
        'beauty_contest_notes',
        'beauty_contest_status',
        'is_beauty_contest_winner',
        'beauty_contest_berita_acara_path',
    ];

    protected $casts = [
        'durasi_sewa_tahun' => 'integer',
        'selected_unit_ids' => 'array',
        'tanggal_booking' => 'datetime',
        'tanggal_dihubungi' => 'datetime',
        'beauty_contest_scheduled_at' => 'datetime',
        'is_beauty_contest_winner' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if (empty($booking->kode_booking)) {
                $prefix = 'BK-'.now()->format('Ym').'-';
                $latest = static::where('kode_booking', 'like', $prefix.'%')
                    ->orderBy('kode_booking', 'desc')
                    ->first();

                $lastNumber = 0;
                if ($latest && preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $latest->kode_booking, $matches)) {
                    $lastNumber = (int) $matches[1];
                }

                $nextNumber = $lastNumber + 1;
                $booking->kode_booking = $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saved(function ($booking) {
            if ($booking->wasChanged('status')) {
                $booking->asset?->syncBookingStatus();
                $booking->syncUnitStatuses();
            }
        });

        static::deleted(function ($booking) {
            $booking->asset?->syncBookingStatus();
            $booking->syncUnitStatuses();
        });
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetUnit(): BelongsTo
    {
        return $this->belongsTo(AssetUnit::class, 'asset_unit_id');
    }

    public function getSelectedUnitsAttribute()
    {
        if (! empty($this->selected_unit_ids) && is_array($this->selected_unit_ids)) {
            return AssetUnit::whereIn('id', $this->selected_unit_ids)->get();
        }

        if ($this->asset_unit_id) {
            $unit = $this->assetUnit;

            return $unit ? collect([$unit]) : collect();
        }

        return collect();
    }

    public function getUnitNamesTextAttribute(): string
    {
        $units = $this->selected_units;
        if ($units->isNotEmpty()) {
            return $units->pluck('nomor_unit')->map(fn ($num) => 'Blok '.$num)->join(', ');
        }

        return 'Seluruh Objek';
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

    public function payments(): HasMany
    {
        return $this->hasMany(BookingPayment::class)->orderBy('termin_ke');
    }

    public function getWhatsappUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->nomor_whatsapp);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $assetName = $this->asset?->nama_objek ?? 'Aset BMD';
        $message = rawurlencode(
            "Halo Bapak/Ibu {$this->nama_pemohon},\n\n".
            "Kami dari UPTD Pengamanan dan Pemanfaatan Aset Pemprov Jawa Barat menindaklanjuti permohonan booking pemanfaatan aset:\n".
            "*{$assetName}* (Kode Booking: {$this->kode_booking}).\n\n".
            'Apakah berkas persyaratan sewa sudah dapat dikoordinasikan untuk proses verifikasi lebih lanjut? Terima kasih.'
        );

        return "https://wa.me/{$phone}?text={$message}";
    }

    public function getBeautyContestWhatsappUrlAttribute(): string
    {
        $phone = preg_replace('/[^0-9]/', '', $this->nomor_whatsapp);
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $assetName = $this->asset?->nama_objek ?? 'Aset BMD';
        $schedule = $this->formatted_beauty_contest_schedule ?? 'Akan diinformasikan lebih lanjut';
        $location = $this->beauty_contest_location ?? 'Kantor UPTD Pengamanan & Pemanfaatan Aset';
        $notes = $this->beauty_contest_notes ? "\nCatatan: {$this->beauty_contest_notes}" : '';

        $message = rawurlencode(
            "Yth. Bapak/Ibu {$this->nama_pemohon},\n\n".
            "Sehubungan dengan permohonan sewa BMD *{$assetName}* (Kode: {$this->kode_booking}), kami menginformasikan bahwa berkas persyaratan Anda telah LENGKAP.\n\n".
            "Karena terdapat lebih dari satu peminat yang memenuhi syarat, UPTD Pengamanan & Pemanfaatan Aset BPKAD Jabar mengundang Anda untuk menghadiri sesi Seleksi Pemaparan Rencana Usaha (*Beauty Contest*):\n\n".
            "📅 Waktu: {$schedule}\n".
            "📍 Tempat/Tautan: {$location}{$notes}\n\n".
            'Mohon konfirmasi kehadiran Anda dengan membalas pesan ini. Terima kasih.'
        );

        return "https://wa.me/{$phone}?text={$message}";
    }

    public function getFormattedBeautyContestScheduleAttribute(): ?string
    {
        return $this->beauty_contest_scheduled_at?->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i').' WIB';
    }

    public function isBeautyContestScheduled(): bool
    {
        return $this->beauty_contest_scheduled_at !== null;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Tindak Lanjut',
            'contacted' => 'Sudah Dihubungi via WA',
            'document_review' => 'Verifikasi Berkas',
            'beauty_contest' => 'Tahap Beauty Contest',
            'field_survey' => 'Peninjauan Lapangan',
            'approved' => 'Disetujui',
            'contract_signed' => 'PKS Terbit (Selesai)',
            'rejected' => 'Ditolak / Batal',
            'expired' => 'Gugur / Kedaluwarsa',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'contacted' => 'info',
            'document_review' => 'primary',
            'beauty_contest' => 'purple',
            'field_survey' => 'indigo',
            'approved' => 'success',
            'contract_signed' => 'success',
            'rejected' => 'danger',
            'expired' => 'danger',
            'cancelled' => 'danger',
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

    public function getTipePemohonLabelAttribute(): string
    {
        return match ($this->tipe_pemohon) {
            'badan_usaha' => 'Badan Usaha / Badan Hukum',
            default => 'Perorangan / Pribadi',
        };
    }

    public function isBadanUsaha(): bool
    {
        return $this->tipe_pemohon === 'badan_usaha';
    }

    /**
     * Cek apakah booking ini dimiliki oleh user tertentu (atau admin).
     */
    public function isOwnedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->user_id !== null) {
            return (int) $this->user_id === (int) $user->id;
        }

        return ! empty($this->email) && strtolower($this->email) === strtolower($user->email);
    }

    /**
     * Dapatkan daftar kode persyaratan yang wajib dipenuhi bagi booking ini.
     */
    public function getRequiredRequirementCodes(): array
    {
        $codes = [
            'REQ_SURAT_PERMOHONAN',
            'REQ_SURAT_PERNYATAAN',
            'REQ_KTP',
            'REQ_NPWP',
        ];

        if ($this->isBadanUsaha()) {
            $codes[] = 'REQ_SURAT_BADAN_HUKUM';
            $codes[] = 'REQ_NIB';
            $codes[] = 'REQ_AKTA_PENDIRIAN';
        }

        if ($this->durasi_sewa_tahun > 5) {
            $codes[] = 'REQ_PROPOSAL';
        }

        return $codes;
    }

    /**
     * Dapatkan daftar kode persyaratan yang relevan bagi pemohon ini.
     */
    public function getApplicableRequirementCodes(): array
    {
        $codes = [
            'REQ_SURAT_PERMOHONAN',
            'REQ_SURAT_PERNYATAAN',
            'REQ_KTP',
            'REQ_NPWP',
            'REQ_NIB',
            'REQ_PROPOSAL',
        ];

        if ($this->isBadanUsaha()) {
            $codes[] = 'REQ_SURAT_BADAN_HUKUM';
            $codes[] = 'REQ_AKTA_PENDIRIAN';
        }

        return $codes;
    }

    /**
     * Hitung statistik kelengkapan berkas wajib pemohon.
     * Mengembalikan array dengan persentase kelengkapan berkas sebagai patokan user.
     */
    public function getCompletenessStats(): array
    {
        $requiredCodes = $this->getRequiredRequirementCodes();
        $totalWajib = count($requiredCodes);

        if ($totalWajib === 0) {
            return [
                'total_wajib' => 0,
                'total_terunggah' => 0,
                'persentase' => 100,
                'is_complete' => true,
                'total_terverifikasi' => 0,
            ];
        }

        // Ambil dokumen booking yang sudah diunggah dan termasuk dalam syarat wajib
        $docs = $this->documents()->with('requirement')->get();
        $uploadedCodes = $docs->filter(function ($doc) use ($requiredCodes) {
            return ! empty($doc->file_path)
                && $doc->requirement
                && in_array($doc->requirement->kode, $requiredCodes);
        })->pluck('requirement.kode')->unique();

        $totalTerunggah = $uploadedCodes->count();
        $persentase = (int) round(($totalTerunggah / $totalWajib) * 100);
        $persentase = min(100, max(0, $persentase));

        $totalTerverifikasi = $docs->filter(fn ($doc) => $doc->status_verifikasi === 'verified')->count();

        return [
            'total_wajib' => $totalWajib,
            'total_terunggah' => $totalTerunggah,
            'persentase' => $persentase,
            'is_complete' => ($totalTerunggah >= $totalWajib),
            'total_terverifikasi' => $totalTerverifikasi,
        ];
    }

    /**
     * Accessor persentase kelengkapan berkas.
     */
    public function getCompletenessPercentageAttribute(): int
    {
        return $this->getCompletenessStats()['persentase'];
    }

    public function getProposalBisnisUrlAttribute(): ?string
    {
        if (! $this->proposal_bisnis_path) {
            return null;
        }

        return route('booking.proposal.view', $this);
    }

    /**
     * Tampilan tersamar (masking) NIK/NIB untuk pelindungan data pribadi (UU PDP).
     */
    public function getMaskedNikAtauNibAttribute(): string
    {
        if (empty($this->nik_atau_nib)) {
            return '-';
        }

        $clean = trim((string) $this->nik_atau_nib);
        $len = strlen($clean);

        if ($len >= 12) {
            return substr($clean, 0, 4).' •••• •••• '.substr($clean, -4);
        }

        if ($len >= 6) {
            return substr($clean, 0, 2).' •••• '.substr($clean, -2);
        }

        return '••••';
    }

    /**
     * Hitung batas waktu booking 5 hari kerja presisi (Senin s.d. Jumat pkl 23:59:59).
     */
    public static function calculateBookingDeadline(?\DateTimeInterface $from = null): Carbon
    {
        return Asset::calculateBookingDeadline($from);
    }

    /**
     * Dapatkan tanggal deadline permohonan booking ini.
     */
    public function getDeadlineAtAttribute(): ?Carbon
    {
        if ($this->asset?->isMultiUnit()) {
            $units = $this->selected_units;
            if ($units->isNotEmpty()) {
                $unitDeadlines = $units->pluck('booking_deadline_at')->filter();
                if ($unitDeadlines->isNotEmpty()) {
                    return $unitDeadlines->min();
                }
            }
            if ($this->assetUnit?->booking_deadline_at) {
                return $this->assetUnit->booking_deadline_at;
            }
        }

        return $this->asset?->booking_deadline_at;
    }

    /**
     * Format tanggal deadline dalam bahasa Indonesia.
     */
    public function getFormattedDeadlineAttribute(): ?string
    {
        return $this->deadline_at?->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H:i').' WIB';
    }

    /**
     * Sisa waktu batas akhir permohonan (humanized).
     */
    public function getRemainingTimeAttribute(): ?string
    {
        $deadline = $this->deadline_at;
        if (! $deadline || now()->gt($deadline)) {
            return null;
        }

        $totalSeconds = (int) now()->diffInSeconds($deadline);
        $days = (int) floor($totalSeconds / 86400);
        $hours = (int) floor(($totalSeconds % 86400) / 3600);
        $minutes = (int) floor(($totalSeconds % 3600) / 60);

        if ($days > 0) {
            return "{$days} hari {$hours} jam {$minutes} menit";
        }
        if ($hours > 0) {
            return "{$hours} jam {$minutes} menit";
        }

        return "{$minutes} menit";
    }

    /**
     * Cek apakah masa tenggang 5 hari kerja untuk melengkapi berkas sudah kedaluwarsa.
     */
    public function isExpired(): bool
    {
        if ($this->status === 'expired') {
            return true;
        }

        if (in_array($this->status, ['rejected', 'cancelled'], true)) {
            return false;
        }

        // Jika seluruh berkas wajib sudah lengkap diunggah, tidak dianggap expired
        if ($this->getCompletenessStats()['is_complete']) {
            return false;
        }

        $deadline = $this->deadline_at;
        if (! $deadline) {
            return false;
        }

        return now()->gt($deadline);
    }

    /**
     * Dapatkan langkah progres linimasa aktif (1 sampai 6).
     */
    public function getStatusStepAttribute(): int
    {
        if ($this->status === 'contract_signed') {
            return 6;
        }
        if ($this->status === 'approved') {
            return 5;
        }
        if ($this->status === 'field_survey') {
            return 4;
        }
        if (in_array($this->status, ['document_review', 'beauty_contest'], true)) {
            return 3;
        }
        if ($this->status === 'contacted') {
            return 2;
        }

        return 1;
    }

    /**
     * URL Berita Acara Seleksi Beauty Contest.
     */
    public function getBeritaAcaraSeleksiUrlAttribute(): ?string
    {
        if (! $this->beauty_contest_berita_acara_path) {
            return null;
        }

        return route('booking.beauty-contest.berita-acara', $this);
    }

    /**
     * Cek apakah ada dokumen persyaratan yang berstatus 'revision' (diminta perbaikan oleh admin).
     */
    public function hasRevisionDocuments(): bool
    {
        return $this->documents()->where('status_verifikasi', 'revision')->exists();
    }

    /**
     * Dapatkan permohonan lain yang bersaing pada objek/unit yang sama.
     */
    public function getCompetingBeautyContestBookings()
    {
        return static::where('id', '!=', $this->id)
            ->where('asset_id', $this->asset_id)
            ->where('status', 'beauty_contest')
            ->get();
    }

    /**
     * Jadwalkan Beauty Contest untuk permohonan ini dan seluruh pemohon pesaingnya pada aset yang sama.
     */
    public function scheduleBeautyContest(\DateTimeInterface $scheduledAt, string $location, ?string $notes = null): int
    {
        $payload = [
            'beauty_contest_scheduled_at' => $scheduledAt,
            'beauty_contest_location' => $location,
            'beauty_contest_notes' => $notes,
            'beauty_contest_status' => 'scheduled',
        ];

        $this->update($payload);

        $competing = $this->getCompetingBeautyContestBookings();
        foreach ($competing as $c) {
            $c->update($payload);
        }

        return 1 + $competing->count();
    }

    /**
     * Tetapkan pemenang Beauty Contest dan selesaikan seleksi:
     * - Pemenang beralih ke 'document_review'.
     * - Peserta lain dialihkan ke 'rejected' dengan alasan objektif.
     */
    public function awardBeautyContestWinner(?string $beritaAcaraPath = null, ?string $catatanAdmin = null): void
    {
        $this->update([
            'status' => 'document_review',
            'is_beauty_contest_winner' => true,
            'beauty_contest_status' => 'completed',
            'beauty_contest_berita_acara_path' => $beritaAcaraPath ?? $this->beauty_contest_berita_acara_path,
            'catatan_admin' => $catatanAdmin ? "Ditetapkan sebagai pemenang Beauty Contest. {$catatanAdmin}" : 'Ditetapkan sebagai pemenang seleksi Beauty Contest.',
        ]);

        $competing = $this->getCompetingBeautyContestBookings();
        foreach ($competing as $c) {
            $c->update([
                'status' => 'rejected',
                'is_beauty_contest_winner' => false,
                'beauty_contest_status' => 'completed',
                'beauty_contest_berita_acara_path' => $beritaAcaraPath ?? $c->beauty_contest_berita_acara_path,
                'alasan_penolakan' => 'Tidak terpilih dalam seleksi Beauty Contest (skor evaluasi berada di bawah mitra terpilih).',
                'catatan_admin' => 'Terima kasih atas partisipasinya dalam Beauty Contest. Objek ditetapkan kepada mitra dengan nilai tertinggi sesuai Berita Acara Seleksi.',
            ]);
        }
    }

    /**
     * Sinkronkan status unit aset yang dipilih.
     */
    public function syncUnitStatuses(): void
    {
        foreach ($this->selected_units as $unit) {
            $unit->syncBookingStatus();
        }
    }

    /**
     * Evaluasi batas waktu untuk aset reguler:
     * - Batalkan booking yang belum lengkap jika deadline terlewati.
     * - Kembalikan aset ke 'tersedia' jika tidak ada peminat lengkap yang bertahan.
     * - Arahkan ke 'beauty_contest' jika terdapat >= 2 peminat lengkap.
     */
    public static function evaluateDeadlineForAsset(?Asset $asset): void
    {
        if (! $asset) {
            return;
        }

        $deadline = $asset->booking_deadline_at;
        if (! $deadline || now()->lte($deadline)) {
            return;
        }

        $activeBookings = $asset->bookings()
            ->whereNotIn('status', ['rejected', 'expired', 'cancelled', 'contract_signed'])
            ->get();

        if ($activeBookings->isEmpty()) {
            $asset->syncBookingStatus();

            return;
        }

        $completeBookings = collect();

        foreach ($activeBookings as $b) {
            $stats = $b->getCompletenessStats();
            if ($stats['is_complete']) {
                $completeBookings->push($b);
            } else {
                $b->update([
                    'status' => 'expired',
                    'alasan_penolakan' => 'Batas waktu 5 hari kerja terlampaui tanpa kelengkapan berkas persyaratan.',
                    'catatan_admin' => 'Gugur otomatis: Berkas persyaratan tidak dilengkapi hingga batas waktu 5 hari kerja berakhir.',
                ]);
            }
        }

        if ($completeBookings->isEmpty()) {
            $asset->update([
                'status_ketersediaan' => 'tersedia',
                'booking_opened_at' => null,
                'booking_deadline_at' => null,
            ]);
        } elseif ($completeBookings->count() === 1) {
            $winner = $completeBookings->first();
            if ($winner->status === 'pending') {
                $winner->update(['status' => 'document_review']);
            }
        } else {
            foreach ($completeBookings as $contestant) {
                if (in_array($contestant->status, ['pending', 'document_review'], true)) {
                    $contestant->update(['status' => 'beauty_contest']);
                }
            }
        }
    }

    /**
     * Evaluasi batas waktu untuk unit aset (ruko/kios):
     */
    public static function evaluateDeadlineForUnit(?AssetUnit $unit): void
    {
        if (! $unit) {
            return;
        }

        $deadline = $unit->booking_deadline_at;
        if (! $deadline || now()->lte($deadline)) {
            return;
        }

        $unitBookings = static::where(function ($q) use ($unit) {
            $q->where('asset_unit_id', $unit->id)
                ->orWhereJsonContains('selected_unit_ids', $unit->id)
                ->orWhereJsonContains('selected_unit_ids', (string) $unit->id);
        })
            ->whereNotIn('status', ['rejected', 'expired', 'cancelled', 'contract_signed'])
            ->get();

        if ($unitBookings->isEmpty()) {
            $unit->syncBookingStatus();

            return;
        }

        $completeBookings = collect();

        foreach ($unitBookings as $b) {
            $stats = $b->getCompletenessStats();
            if ($stats['is_complete']) {
                $completeBookings->push($b);
            } else {
                $b->update([
                    'status' => 'expired',
                    'alasan_penolakan' => 'Batas waktu 5 hari kerja terlampaui tanpa kelengkapan berkas persyaratan.',
                    'catatan_admin' => 'Gugur otomatis: Berkas persyaratan tidak dilengkapi hingga batas waktu 5 hari kerja berakhir.',
                ]);
            }
        }

        if ($completeBookings->isEmpty()) {
            $unit->update([
                'status_ketersediaan' => 'tersedia',
                'booking_opened_at' => null,
                'booking_deadline_at' => null,
            ]);
        } elseif ($completeBookings->count() === 1) {
            $winner = $completeBookings->first();
            if ($winner->status === 'pending') {
                $winner->update(['status' => 'document_review']);
            }
        } else {
            foreach ($completeBookings as $contestant) {
                if (in_array($contestant->status, ['pending', 'document_review'], true)) {
                    $contestant->update(['status' => 'beauty_contest']);
                }
            }
        }
    }

    /**
     * Evaluasi seluruh aset dan unit yang memiliki batas waktu kedaluwarsa.
     */
    public static function evaluateAllDeadlines(): array
    {
        $expiredAssetsCount = 0;

        $assets = Asset::whereNotNull('booking_deadline_at')
            ->where('booking_deadline_at', '<', now())
            ->get();

        foreach ($assets as $asset) {
            $beforeStatus = $asset->status_ketersediaan;
            static::evaluateDeadlineForAsset($asset);
            $asset->refresh();
            if ($beforeStatus === 'proses_booking' && $asset->status_ketersediaan === 'tersedia') {
                $expiredAssetsCount++;
            }
        }

        $units = AssetUnit::whereNotNull('booking_deadline_at')
            ->where('booking_deadline_at', '<', now())
            ->get();

        foreach ($units as $unit) {
            $beforeStatus = $unit->status_ketersediaan;
            static::evaluateDeadlineForUnit($unit);
            $unit->refresh();
            if ($beforeStatus === 'proses_booking' && $unit->status_ketersediaan === 'tersedia') {
                $expiredAssetsCount++;
            }
        }

        return [
            'assets_reverted' => $expiredAssetsCount,
            'bookings_expired' => static::where('status', 'expired')->count(),
            'beauty_contest_active' => static::where('status', 'beauty_contest')->count(),
        ];
    }
}
