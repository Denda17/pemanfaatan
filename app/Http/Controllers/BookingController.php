<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $asset = Asset::where('slug', $slug)->firstOrFail();

        if ($asset->status_ketersediaan === 'tersewa') {
            return back()->with('error', 'Maaf, aset ini sedang dalam masa sewa aktif.');
        }

        $validated = $request->validate([
            'nama_pemohon' => 'required|string|max:255',
            'nomor_whatsapp' => 'required|string|min:9|max:20',
            'nik_atau_nib' => 'nullable|string|max:100',
            'nama_instansi' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'rencana_peruntukan' => 'required|string|max:1000',
            'durasi_sewa_tahun' => 'required|integer|min:1|max:30',
        ], [
            'nama_pemohon.required' => 'Nama lengkap pemohon wajib diisi.',
            'nomor_whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi agar petugas UPTD dapat menghubungi Anda.',
            'rencana_peruntukan.required' => 'Rencana peruntukan / jenis usaha wajib dijelaskan.',
            'durasi_sewa_tahun.required' => 'Rencana durasi sewa wajib dipilih.',
        ]);

        // Cek jika pemohon login atau cari user berdasarkan email/telepon
        $userId = Auth::id();
        if (!$userId && !empty($validated['email'])) {
            $existingUser = User::where('email', $validated['email'])->first();
            $userId = $existingUser?->id;
        }

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $userId,
            'nama_pemohon' => $validated['nama_pemohon'],
            'nik_atau_nib' => $validated['nik_atau_nib'] ?? null,
            'nama_instansi' => $validated['nama_instansi'] ?? null,
            'nomor_whatsapp' => $validated['nomor_whatsapp'],
            'email' => $validated['email'] ?? null,
            'rencana_peruntukan' => $validated['rencana_peruntukan'],
            'durasi_sewa_tahun' => (int) $validated['durasi_sewa_tahun'],
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        // Update status aset di marketplace menjadi dalam proses booking
        if ($asset->status_ketersediaan === 'tersedia') {
            $asset->update(['status_ketersediaan' => 'proses_booking']);
        }

        return redirect()->route('booking.status', $booking->kode_booking)
            ->with('success', "Permohonan booking berhasil dikirim! Kode Booking Anda adalah {$booking->kode_booking}. Petugas UPTD Pengamanan dan Pemanfaatan Aset akan segera menghubungi nomor WhatsApp Anda.");
    }

    public function tracker(Request $request)
    {
        $search = trim($request->get('q', ''));
        $bookings = collect();

        if (!empty($search)) {
            $bookings = Booking::where('kode_booking', 'ilike', "%{$search}%")
                ->orWhere('nomor_whatsapp', 'like', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
                ->with(['asset'])
                ->latest()
                ->get();
        }

        return view('pages.booking-tracker', compact('bookings', 'search'));
    }

    public function showStatus(string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)
            ->with(['asset', 'documents.requirement', 'petugasUptd'])
            ->firstOrFail();

        // 6 Tahapan Proses Permohonan Pemanfaatan BMD
        $steps = [
            [
                'step' => 1,
                'title' => 'Pengajuan Booking',
                'desc' => 'Peminat mengisi formulir & nomor WhatsApp aktif via aplikasi',
                'completed' => true,
                'active' => ($booking->status === 'pending'),
            ],
            [
                'step' => 2,
                'title' => 'Follow-up UPTD via WA',
                'desc' => 'Petugas UPTD menghubungi pemohon via WhatsApp untuk koordinasi awal',
                'completed' => in_array($booking->status, ['contacted', 'document_review', 'field_survey', 'approved', 'contract_signed']),
                'active' => ($booking->status === 'contacted'),
            ],
            [
                'step' => 3,
                'title' => 'Input & Verifikasi Berkas',
                'desc' => 'Admin UPTD menginput & memverifikasi dokumen persyaratan (KTP/NIB, Proposal, dll)',
                'completed' => in_array($booking->status, ['document_review', 'field_survey', 'approved', 'contract_signed']),
                'active' => ($booking->status === 'document_review'),
            ],
            [
                'step' => 4,
                'title' => 'Survey Lapangan & Penilaian',
                'desc' => 'Peninjauan fisik lokasi aset dan penetapan besaran tarif sewa sesuai regulasi',
                'completed' => in_array($booking->status, ['field_survey', 'approved', 'contract_signed']),
                'active' => ($booking->status === 'field_survey'),
            ],
            [
                'step' => 5,
                'title' => 'Persetujuan Pengelola Barang',
                'desc' => 'Penerbitan Surat Persetujuan Sekda / Keputusan Gubernur Jawa Barat',
                'completed' => in_array($booking->status, ['approved', 'contract_signed']),
                'active' => ($booking->status === 'approved'),
            ],
            [
                'step' => 6,
                'title' => 'Penandatanganan PKS & Sewa Aktif',
                'desc' => 'Penyetoran PAD ke Kasda dan penandatanganan Perjanjian Kerjasama Sewa',
                'completed' => ($booking->status === 'contract_signed'),
                'active' => ($booking->status === 'contract_signed'),
            ],
        ];

        return view('pages.booking-status', compact('booking', 'steps'));
    }
}
