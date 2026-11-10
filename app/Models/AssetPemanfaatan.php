<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetPemanfaatan extends Model
{
    use HasFactory;

    protected $table = 'asset_pemanfaatan';

    protected $fillable = [
        'asset_id',
        'nomor_urut_excel',
        'jenis_pemanfaatan',
        'status_pengelola',
        'nama_mitra',
        'jenis_mitra',
        'kontak_mitra',
        'besaran_sewa_tahunan',
        'jangka_waktu_tahun',
        'jangka_waktu_teks',
        'total_sewa',
        'mekanisme_pembayaran',
        'nomor_persetujuan',
        'nomor_kepgub',
        'nomor_perjanjian',
        'tanggal_awal',
        'tanggal_akhir',
        'status_perpanjangan',
        'kategori_status',
        'keterangan',
    ];

    protected $casts = [
        'besaran_sewa_tahunan' => 'decimal:2',
        'total_sewa' => 'decimal:2',
        'jangka_waktu_tahun' => 'integer',
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetPemanfaatanPayment::class, 'asset_pemanfaatan_id')->orderBy('termin_ke');
    }

    public function getTotalDisetorAttribute(): float
    {
        return (float) $this->payments()->where('status_pembayaran', 'lunas')->sum('nominal_disetor');
    }

    public function getSisaKewajibanAttribute(): float
    {
        $totalSewa = (float) ($this->total_sewa ?? 0);
        return max(0, $totalSewa - $this->total_disetor);
    }

    public function getPersentasePelunasanAttribute(): float
    {
        $totalSewa = (float) ($this->total_sewa ?? 0);
        if ($totalSewa <= 0) {
            return 100.0;
        }

        return min(100.0, round(($this->total_disetor / $totalSewa) * 100, 1));
    }

    /**
     * Format nomor telepon mitra ke standar internasional 62...
     */
    public function getFormattedPhoneAttribute(): ?string
    {
        if (empty($this->kontak_mitra)) {
            return null;
        }

        $phone = preg_replace('/[^0-9]/', '', (string) $this->kontak_mitra);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62' . $phone;
        }

        return $phone;
    }

    /**
     * Generate template pesan peringatan jatuh tempo resmi Permendagri 19/2016 untuk WhatsApp
     */
    public function getWhatsappReminderUrlAttribute(): ?string
    {
        $phone = $this->formatted_phone;
        $tglAkhir = $this->tanggal_akhir ? $this->tanggal_akhir->translatedFormat('d F Y') : '-';
        $namaAset = $this->asset?->nama_objek ?? 'Objek BMD Jabar';
        $pks = $this->nomor_perjanjian ?? '-';

        $text = "Yth. Pimpinan / Pengelola {$this->nama_mitra},\n\n"
            . "Kami dari *UPTD Pengamanan dan Pemanfaatan Aset BPKAD Provinsi Jawa Barat* menyampaikan pemberitahuan resmi bahwa Perjanjian Sewa Barang Milik Daerah (BMD) atas:\n"
            . "• *Objek Aset:* {$namaAset}\n"
            . "• *No. Perjanjian / PKS:* {$pks}\n"
            . "• *Batas Akhir Sewa:* {$tglAkhir}\n\n"
            . "Berdasarkan *Pasal 100 Permendagri No. 19 Tahun 2016*, permohonan perpanjangan sewa BMD wajib diajukan paling lambat *6 (enam) bulan* sebelum masa sewa berakhir.\n\n"
            . "Mohon kesediaan konfirmasi perpanjangan sewa atau persiapan serah terima kembali objek ke Pemprov Jawa Barat. Apabila ada pertanyaan, silakan membalas pesan ini.\n\n"
            . "Hormat kami,\n"
            . "*UPTD Pengamanan & Pemanfaatan Aset Jabar*";

        if ($phone) {
            return "https://api.whatsapp.com/send?phone={$phone}&text=" . rawurlencode($text);
        }

        return "https://api.whatsapp.com/send?text=" . rawurlencode($text);
    }
}
