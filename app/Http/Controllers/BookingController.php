<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingRequirement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
            'durasi_sewa_tahun' => 'required|integer|min:1|max:10',
            'skema_pembayaran' => 'required|in:sekaligus,bertahap',
            'proposal_bisnis' => [
                'nullable',
                Rule::requiredIf(fn () => (int) $request->input('durasi_sewa_tahun') > 5),
                'file',
                'mimes:pdf,doc,docx',
                'max:20480',
            ],
        ], [
            'nama_pemohon.required' => 'Nama lengkap pemohon wajib diisi.',
            'nomor_whatsapp.required' => 'Nomor WhatsApp aktif wajib diisi agar petugas UPTD dapat menghubungi Anda.',
            'rencana_peruntukan.required' => 'Rencana peruntukan / jenis usaha wajib dijelaskan.',
            'durasi_sewa_tahun.required' => 'Rencana durasi sewa wajib dipilih.',
            'durasi_sewa_tahun.max' => 'Rencana durasi sewa maksimal 10 tahun.',
            'skema_pembayaran.required' => 'Skema pembayaran sewa wajib dipilih.',
            'skema_pembayaran.in' => 'Pilihan skema pembayaran tidak valid.',
            'proposal_bisnis.required' => 'Untuk permohonan sewa dengan durasi lebih dari 5 tahun, Anda wajib melampirkan Dokumen Proposal Bisnis / Feasibility Study.',
            'proposal_bisnis.mimes' => 'Format file proposal bisnis harus berupa dokumen PDF atau DOC/DOCX.',
            'proposal_bisnis.max' => 'Ukuran file proposal bisnis maksimal 20 MB.',
        ]);

        $user = Auth::user();

        $proposalPath = null;
        if ($request->hasFile('proposal_bisnis')) {
            $proposalFile = $request->file('proposal_bisnis');
            $proposalPath = $proposalFile->store('booking-documents', 'public');
        }

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'user_id' => $user?->id,
            'nama_pemohon' => $validated['nama_pemohon'],
            'nik_atau_nib' => $validated['nik_atau_nib'] ?? ($user?->nik ?? $user?->nib),
            'nama_instansi' => $validated['nama_instansi'] ?? $user?->instansi,
            'nomor_whatsapp' => $validated['nomor_whatsapp'],
            'email' => $validated['email'] ?? $user?->email,
            'rencana_peruntukan' => $validated['rencana_peruntukan'],
            'durasi_sewa_tahun' => (int) $validated['durasi_sewa_tahun'],
            'skema_pembayaran' => $validated['skema_pembayaran'],
            'proposal_bisnis_path' => $proposalPath,
            'status' => 'pending',
            'tanggal_booking' => now(),
        ]);

        if ($proposalPath) {
            $proposalReq = BookingRequirement::where('kode', 'REQ_PROPOSAL')->first();
            if ($proposalReq) {
                BookingDocument::create([
                    'booking_id' => $booking->id,
                    'booking_requirement_id' => $proposalReq->id,
                    'nomor_dokumen' => 'PROP/' . $booking->kode_booking,
                    'tanggal_dokumen' => now(),
                    'file_path' => $proposalPath,
                    'file_name' => $request->file('proposal_bisnis')->getClientOriginalName(),
                    'file_size' => $request->file('proposal_bisnis')->getSize(),
                    'file_type' => $request->file('proposal_bisnis')->getClientMimeType(),
                    'status_verifikasi' => 'pending',
                    'catatan' => 'Diunggah mandiri oleh pemohon saat pengajuan sewa > 5 tahun',
                ]);
            }
        }

        // Update status aset di marketplace menjadi dalam proses booking
        if ($asset->status_ketersediaan === 'tersedia') {
            $asset->update(['status_ketersediaan' => 'proses_booking']);
        }

        return redirect()->route('booking.status', $booking->kode_booking)
            ->with('success', "Permohonan booking berhasil dikirim! Kode Booking Anda adalah {$booking->kode_booking}. Petugas UPTD Pengamanan dan Pemanfaatan Aset akan segera menghubungi nomor WhatsApp Anda.");
    }

    public function myBookings(Request $request)
    {
        $user = Auth::user();

        $bookings = Booking::where(function ($query) use ($user) {
                $query->where('user_id', $user->id)
                    ->orWhere('email', $user->email)
                    ->orWhere('nomor_whatsapp', $user->phone);
            })
            ->with(['asset'])
            ->latest('tanggal_booking')
            ->get();

        return view('pages.my-bookings', compact('bookings', 'user'));
    }

    public function tracker(Request $request)
    {
        if (Auth::check()) {
            return redirect()->route('booking.my');
        }

        return redirect()->route('login')
            ->with('info', 'Silakan masuk ke akun Anda untuk melihat daftar dan status permohonan sewa aset.');
    }

    public function showStatus(string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)
            ->with(['asset', 'documents.requirement', 'petugasUptd'])
            ->firstOrFail();

        $user = Auth::user();

        // Otorisasi: hanya pemilik permohonan atau admin yang dapat melihat status
        if ($user && !$user->isAdmin()) {
            $isOwner = ($booking->user_id === $user->id) 
                || ($booking->email && strtolower($booking->email) === strtolower($user->email))
                || ($booking->nomor_whatsapp && $booking->nomor_whatsapp === $user->phone);

            abort_unless($isOwner, 403, 'Anda tidak memiliki hak akses untuk melihat permohonan ini.');
        }

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
