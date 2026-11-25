<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetUnit;
use App\Models\Booking;
use App\Models\BookingDocument;
use App\Models\BookingPayment;
use App\Models\BookingRequirement;
use App\Services\DocumentWatermarkService;
use App\Services\SuratPermohonanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function store(Request $request, string $slug)
    {
        $asset = Asset::where('slug', $slug)->firstOrFail();
        $user = Auth::user();

        if ($asset->status_ketersediaan === 'tersewa') {
            return back()->with('error', 'Maaf, aset ini sedang dalam masa sewa aktif.');
        }

        // Handle unit validation jika aset bertipe multi-unit
        $unitIds = [];
        $validUnits = collect();
        if ($asset->isMultiUnit()) {
            $rawUnits = $request->input('selected_units', []);
            if (! is_array($rawUnits) && ! empty($rawUnits)) {
                $rawUnits = explode(',', (string) $rawUnits);
            }
            if (empty($rawUnits) && $request->filled('asset_unit_id')) {
                $rawUnits = [(int) $request->input('asset_unit_id')];
            }

            $unitIds = array_values(array_filter(array_map('intval', (array) $rawUnits)));

            if (empty($unitIds)) {
                return back()->withInput()->with('error', 'Silakan pilih minimal 1 unit ruko pada denah atau daftar unit sebelum mengirimkan permohonan.');
            }

            $validUnits = AssetUnit::where('asset_id', $asset->id)
                ->whereIn('id', $unitIds)
                ->get();

            if ($validUnits->isEmpty()) {
                return back()->withInput()->with('error', 'Unit ruko yang dipilih tidak valid.');
            }

            foreach ($validUnits as $vu) {
                if ($vu->status_ketersediaan === 'tersewa') {
                    return back()->withInput()->with('error', "Unit {$vu->nomor_unit} telah berstatus tersewa aktif.");
                }
                if ($vu->isBookingClosed()) {
                    return back()->withInput()->with('error', "Masa pendaftaran minat untuk Unit {$vu->nomor_unit} telah berakhir.");
                }
            }
        } elseif ($asset->isBookingClosed()) {
            // Cek apakah jendela pengajuan minat (5 hari kerja sejak peminat pertama) telah ditutup untuk aset reguler
            return back()->with('error', 'Mohon maaf, batas waktu pendaftaran minat (5 hari kerja) untuk aset ini telah ditutup.');
        }

        // Cek anti-spam: 1 akun pemohon hanya boleh memiliki 1 pengajuan aktif per aset (atau per unit jika multi-unit)
        $existingBookingQuery = Booking::where('asset_id', $asset->id)
            ->where(function ($q) use ($user, $request) {
                if ($user) {
                    $q->where('user_id', $user->id)
                        ->orWhere('email', $user->email);
                }
                if ($request->filled('email')) {
                    $q->orWhere('email', $request->input('email'));
                }
            })
            ->whereNotIn('status', ['rejected']);

        if ($asset->isMultiUnit() && ! empty($unitIds)) {
            $existingBookingQuery->where(function ($q) use ($unitIds) {
                $q->whereIn('asset_unit_id', $unitIds);
                foreach ($unitIds as $uid) {
                    $q->orWhereJsonContains('selected_unit_ids', (int) $uid);
                }
            });
        }

        $existingBooking = $existingBookingQuery->first();
        if ($existingBooking) {
            return back()->with('error', "Anda telah mengajukan minat untuk objek/unit ini dengan Kode Booking {$existingBooking->kode_booking}. Setiap pemohon hanya dapat mengajukan 1 kali (dibatasi 1 permohonan aktif per unit).");
        }

        $validated = $request->validate([
            'tipe_pemohon' => 'required|in:perorangan,badan_usaha',
            'nama_pemohon' => 'required|string|max:255',
            'nomor_whatsapp' => 'required|string|min:9|max:20',
            'nik_atau_nib' => 'nullable|string|max:100',
            'nama_instansi' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('tipe_pemohon') === 'badan_usaha'),
            ],
            'jabatan_pemohon' => 'nullable|string|max:100',
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
            'tipe_pemohon.required' => 'Pilih kategori pemohon sewa (Perorangan atau Badan Usaha).',
            'tipe_pemohon.in' => 'Kategori pemohon tidak valid.',
            'nama_pemohon.required' => 'Nama lengkap pemohon / penanggung jawab wajib diisi.',
            'nama_instansi.required' => 'Nama Badan Usaha / Perusahaan wajib diisi untuk kategori Badan Usaha.',
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
        $isBadanUsaha = $validated['tipe_pemohon'] === 'badan_usaha';

        $proposalPath = null;
        if ($request->hasFile('proposal_bisnis')) {
            $proposalFile = $request->file('proposal_bisnis');
            $proposalPath = $proposalFile->store('booking-documents', 'local');
        }

        $primaryUnitId = ! empty($unitIds) ? $unitIds[0] : null;

        $booking = Booking::create([
            'asset_id' => $asset->id,
            'asset_unit_id' => $primaryUnitId,
            'selected_unit_ids' => ! empty($unitIds) ? $unitIds : null,
            'user_id' => $user?->id,
            'tipe_pemohon' => $validated['tipe_pemohon'],
            'nama_pemohon' => $validated['nama_pemohon'],
            'nik_atau_nib' => $validated['nik_atau_nib'] ?? ($user?->nik ?? $user?->nib),
            'nama_instansi' => $isBadanUsaha ? $validated['nama_instansi'] : 'Perorangan',
            'jabatan_pemohon' => $isBadanUsaha ? ($validated['jabatan_pemohon'] ?? 'Pimpinan / Penanggung Jawab') : 'Pemohon Perseorangan',
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
                    'nomor_dokumen' => 'PROP/'.$booking->kode_booking,
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

        // Jalankan countdown 5 hari kerja
        if ($asset->isMultiUnit()) {
            foreach ($validUnits as $vu) {
                if ($vu->status_ketersediaan === 'tersedia' || ! $vu->booking_deadline_at) {
                    $vu->startBookingCountdown();
                }
            }
        } else {
            // Jika ini adalah peminat pertama pada aset reguler, tetapkan deadline penutupan (5 hari kerja s.d. 23:59 WIB)
            if ($asset->status_ketersediaan === 'tersedia' || ! $asset->booking_deadline_at) {
                $openedAt = now();
                $deadlineAt = Asset::calculateBookingDeadline($openedAt);
                $asset->update([
                    'status_ketersediaan' => 'proses_booking',
                    'booking_opened_at' => $asset->booking_opened_at ?? $openedAt,
                    'booking_deadline_at' => $deadlineAt,
                ]);
            }
        }

        return redirect()->route('booking.status', $booking->kode_booking)
            ->with('success', "Permohonan booking berhasil dikirim! Kode Booking Anda adalah {$booking->kode_booking}. Petugas UPTD Pengamanan dan Pemanfaatan Aset akan segera menghubungi nomor WhatsApp Anda.");
    }

    public function myBookings(Request $request)
    {
        $user = Auth::user();

        $bookings = Booking::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhere(function ($q) use ($user) {
                    $q->whereNull('user_id')->where('email', $user->email);
                });
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
            ->with(['asset', 'documents.requirement', 'petugasUptd', 'payments.verifier'])
            ->firstOrFail();

        $user = Auth::user();

        // Otorisasi: hanya pemilik permohonan atau admin yang dapat melihat status
        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk melihat permohonan ini.');

        // Evaluasi otomatis jika batas waktu telah berakhir
        if ($booking->asset?->isMultiUnit()) {
            foreach ($booking->selected_units as $unit) {
                Booking::evaluateDeadlineForUnit($unit);
            }
        } else {
            Booking::evaluateDeadlineForAsset($booking->asset);
        }

        $booking->refresh();

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
                'completed' => in_array($booking->status, ['contacted', 'document_review', 'beauty_contest', 'field_survey', 'approved', 'contract_signed']),
                'active' => ($booking->status === 'contacted'),
            ],
            [
                'step' => 3,
                'title' => ($booking->status === 'beauty_contest' ? 'Seleksi Beauty Contest' : ($booking->is_beauty_contest_winner ? 'Pemenang Seleksi & Verifikasi' : 'Input & Verifikasi Berkas')),
                'desc' => ($booking->status === 'beauty_contest' ? 'UPTD menyelenggarakan seleksi pemaparan rencana usaha bagi beberapa peminat lengkap' : ($booking->is_beauty_contest_winner ? 'Mitra terpilih melanjutkan ke penetapan berita acara negosiasi dan fasilitasi Kepgub' : 'Admin UPTD menginput & memverifikasi dokumen persyaratan (KTP/NIB, Proposal, dll)')),
                'completed' => in_array($booking->status, ['document_review', 'beauty_contest', 'field_survey', 'approved', 'contract_signed']),
                'active' => in_array($booking->status, ['document_review', 'beauty_contest']),
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

    public function downloadSuratPermohonan(Request $request, string $kode)
    {
        $booking = Booking::with(['user', 'asset'])->where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        // Otorisasi: hanya pemilik booking atau admin
        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunduh dokumen permohonan ini.');

        // Mode template mentah (dengan perihal disesuaikan jenis aset)
        if ($request->query('mode') === 'raw') {
            $generatedRaw = SuratPermohonanService::generateRaw($booking);

            return response()->download($generatedRaw, 'DRAFT_Surat_Permohonan_Sewa_BMD_2026.docx');
        }

        // Mode auto-fill (otomatis terisi)
        $generatedFile = SuratPermohonanService::generate($booking);
        $fileName = 'Surat_Permohonan_Sewa_'.preg_replace('/[^A-Za-z0-9_-]/', '_', $booking->kode_booking).'.docx';

        return response()->download($generatedFile, $fileName);
    }

    public function downloadSuratPernyataan(Request $request, string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunduh template permohonan ini.');

        $templatePath = base_path('docs/data/Template Surat Pernyataan.docx');
        if (! file_exists($templatePath)) {
            abort(404, 'File template surat pernyataan tidak ditemukan di server.');
        }

        return response()->download($templatePath, 'Template_Surat_Pernyataan_Sewa_BMD.docx');
    }

    public function downloadSuratBadanHukum(Request $request, string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunduh template permohonan ini.');

        $templatePath = base_path('docs/data/Template Surat Keterangan Badan Hukum_Usaha.docx');
        if (! file_exists($templatePath)) {
            abort(404, 'File template surat keterangan badan hukum tidak ditemukan di server.');
        }

        return response()->download($templatePath, 'Template_Surat_Keterangan_Badan_Hukum_Usaha.docx');
    }

    public function uploadAllDocuments(Request $request, string $kode)
    {
        $booking = Booking::with(['user', 'asset', 'documents.requirement'])->where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        // Otorisasi
        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunggah dokumen pada permohonan ini.');

        // Proteksi status selesai / ditolak
        if (in_array($booking->status, ['rejected', 'cancelled'], true)) {
            return back()->with('error', 'Permohonan ini telah ditutup atau tidak aktif.');
        }

        // Proteksi masa tenggang 5 hari kerja
        if ($booking->isExpired()) {
            return back()->with('error', 'Masa tenggang 5 hari kerja untuk melengkapi dokumen persyaratan telah berakhir. Permohonan ini telah kedaluwarsa.');
        }

        $requiredCodes = $booking->getRequiredRequirementCodes();
        $requirements = BookingRequirement::whereIn('kode', $requiredCodes)->get()->keyBy('kode');
        $existingVerifiedDocs = $booking->documents->where('status_verifikasi', 'verified')->keyBy('requirement.kode');

        $rules = [];
        $messages = [];

        foreach ($requiredCodes as $code) {
            // Jika dokumen sudah verified (misal saat perbaikan bertahap), lewati
            if ($existingVerifiedDocs->has($code)) {
                continue;
            }

            $req = $requirements->get($code);
            $nama = $req?->nama_persyaratan ?? $code;

            $rules["documents.{$code}"] = 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480';
            $messages["documents.{$code}.required"] = "Dokumen \"{$nama}\" wajib dipilih dan diunggah.";
            $messages["documents.{$code}.file"] = "Berkas \"{$nama}\" harus berupa file valid.";
            $messages["documents.{$code}.mimes"] = "Format berkas \"{$nama}\" harus berupa PDF, JPG, PNG, atau DOC/DOCX.";
            $messages["documents.{$code}.max"] = "Ukuran berkas \"{$nama}\" tidak boleh melebihi 20 MB.";
        }

        $request->validate($rules, $messages);

        $watermarkService = app(DocumentWatermarkService::class);
        $uploadedCount = 0;

        foreach ($rules as $fieldKey => $rule) {
            $code = str_replace('documents.', '', $fieldKey);
            $req = $requirements->get($code);
            if (! $req) {
                continue;
            }

            $file = $request->file("documents.{$code}");
            if (! $file) {
                continue;
            }

            // Watermark pelindungan data pribadi (UU PDP)
            if (in_array($code, ['REQ_KTP', 'REQ_NPWP', 'REQ_NIB'], true)) {
                $watermarkService->applyWatermarkIfImage($file, $booking->kode_booking);
            }

            $path = $file->store('booking-documents', 'local');

            BookingDocument::updateOrCreate(
                [
                    'booking_id' => $booking->id,
                    'booking_requirement_id' => $req->id,
                ],
                [
                    'nomor_dokumen' => $req->kode.'/'.$booking->kode_booking,
                    'tanggal_dokumen' => now(),
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                    'status_verifikasi' => 'pending',
                    'catatan' => null,
                ]
            );

            if ($code === 'REQ_PROPOSAL') {
                $booking->update(['proposal_bisnis_path' => $path]);
            }

            $uploadedCount++;
        }

        // Perbarui status booking ke document_review (Verifikasi Berkas) jika sebelumnya pending
        if ($booking->status === 'pending') {
            $booking->update([
                'status' => 'document_review',
            ]);
        }

        return redirect()->route('booking.status', $booking->kode_booking)
            ->with('success', "Seluruh ({$uploadedCount}) berkas persyaratan berhasil dikirim secara lengkap satu paket! Tim Verifikator UPTD BPKAD Jawa Barat akan segera menelaah berkas Anda.");
    }

    public function uploadDocument(Request $request, string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        // Otorisasi
        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunggah dokumen pada permohonan ini.');

        // Proteksi status selesai / ditolak
        if (in_array($booking->status, ['rejected', 'cancelled'], true)) {
            return back()->with('error', 'Permohonan ini telah ditutup atau tidak aktif.');
        }

        // Proteksi masa tenggang 5 hari kerja
        if ($booking->isExpired()) {
            return back()->with('error', 'Masa tenggang 5 hari kerja untuk melengkapi dokumen persyaratan telah berakhir. Permohonan ini telah kedaluwarsa.');
        }

        $validated = $request->validate([
            'booking_requirement_id' => 'required|exists:booking_requirements,id',
            'dokumen_file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:20480',
        ], [
            'booking_requirement_id.required' => 'Jenis persyaratan dokumen wajib ditentukan.',
            'booking_requirement_id.exists' => 'Jenis persyaratan tidak valid.',
            'dokumen_file.required' => 'File dokumen wajib dipilih.',
            'dokumen_file.mimes' => 'Format file yang diperbolehkan adalah PDF, JPG, PNG, atau DOC/DOCX.',
            'dokumen_file.max' => 'Ukuran file maksimal adalah 20 MB.',
        ]);

        $requirement = BookingRequirement::findOrFail($validated['booking_requirement_id']);

        // Khusus berkas proses administrasi yang diterbitkan oleh admin tidak dapat diunggah pemohon
        if ($requirement->diunggah_oleh === 'admin') {
            abort(403, 'Dokumen "'.$requirement->nama_persyaratan.'" diterbitkan oleh Tim Admin UPTD BPKAD Jawa Barat dan tidak dapat diunggah oleh pemohon.');
        }

        $file = $request->file('dokumen_file');

        // Lindungi dokumen identitas/pribadi (KTP, NPWP, NIB) dengan watermark pelindungan data pribadi (UU PDP)
        if (in_array($requirement->kode, ['REQ_KTP', 'REQ_NPWP', 'REQ_NIB'], true)) {
            app(DocumentWatermarkService::class)->applyWatermarkIfImage($file, $booking->kode_booking);
        }

        // Simpan ke private disk ('local' -> storage/app/private) agar tidak dapat diakses langsung via URL publik
        $path = $file->store('booking-documents', 'local');

        BookingDocument::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'booking_requirement_id' => $requirement->id,
            ],
            [
                'nomor_dokumen' => $requirement->kode.'/'.$booking->kode_booking,
                'tanggal_dokumen' => now(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file->getClientMimeType(),
                'status_verifikasi' => 'pending',
                'catatan' => null, // reset catatan revisi saat pemohon mengunggah ulang
            ]
        );

        if ($requirement->kode === 'REQ_PROPOSAL') {
            $booking->update(['proposal_bisnis_path' => $path]);
        }

        if ($requirement->kode === 'REQ_BUKTI_BAYAR' && $booking->skema_pembayaran === 'bertahap') {
            BookingPayment::updateOrCreate(
                [
                    'booking_id' => $booking->id,
                    'termin_ke' => 1,
                ],
                [
                    'tahun_ke' => 'Tahun ke-1',
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'tanggal_bayar' => now(),
                    'status_pembayaran' => 'pending_verifikasi',
                    'catatan' => null,
                ]
            );
        }

        return back()->with('success', 'Dokumen "'.$requirement->nama_persyaratan.'" berhasil diunggah dan sedang menunggu verifikasi Tim UPTD BPKAD Jabar.');
    }

    public function requestTerminPaymentCode(Request $request, string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengajukan kode pembayaran pada permohonan ini.');

        if ($booking->skema_pembayaran !== 'bertahap') {
            return back()->with('error', 'Permintaan kode pembayaran termin hanya berlaku untuk skema pembayaran bertahap.');
        }

        $validated = $request->validate([
            'termin_ke' => 'required|integer|min:2|max:'.max(2, (int) $booking->durasi_sewa_tahun),
        ], [
            'termin_ke.required' => 'Nomor termin sewa wajib ditentukan.',
            'termin_ke.min' => 'Permintaan kode pembayaran hanya untuk termin ke-2 dan seterusnya.',
        ]);

        $terminKe = (int) $validated['termin_ke'];
        $existing = BookingPayment::where('booking_id', $booking->id)
            ->where('termin_ke', $terminKe)
            ->first();

        if ($existing && $existing->status_pembayaran === 'lunas') {
            return back()->with('success', "Setoran Termin ke-{$terminKe} sudah diverifikasi lunas.");
        }

        if ($existing && $existing->hasIssuedPaymentCode()) {
            return back()->with('success', "Kode pembayaran Termin ke-{$terminKe} sudah diterbitkan oleh Admin UPTD.");
        }

        BookingPayment::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'termin_ke' => $terminKe,
            ],
            [
                'tahun_ke' => 'Tahun ke-'.$terminKe,
                'status_pembayaran' => 'kode_diminta',
                'kode_diminta_at' => now(),
            ]
        );

        return back()->with('success', "Permintaan kode pembayaran Termin ke-{$terminKe} berhasil dikirim ke Admin UPTD BPKAD Jabar.");
    }

    public function uploadTerminPayment(Request $request, string $kode)
    {
        $booking = Booking::where('kode_booking', $kode)->firstOrFail();
        $user = Auth::user();

        abort_unless($booking->isOwnedBy($user), 403, 'Anda tidak memiliki hak akses untuk mengunggah setoran termin pada permohonan ini.');

        $validated = $request->validate([
            'termin_ke' => 'required|integer|min:1|max:'.max(1, (int) $booking->durasi_sewa_tahun),
            'nomor_sts' => 'nullable|string|max:100',
            'tanggal_bayar' => 'nullable|date',
            'nominal_disetor' => 'nullable|numeric|min:0',
            'bukti_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ], [
            'termin_ke.required' => 'Nomor termin sewa wajib ditentukan.',
            'bukti_file.required' => 'File bukti setor Bank BJB wajib dipilih.',
            'bukti_file.mimes' => 'Format file yang diperbolehkan adalah PDF, JPG, atau PNG.',
            'bukti_file.max' => 'Ukuran file maksimal adalah 20 MB.',
        ]);

        $terminKe = (int) $validated['termin_ke'];
        $existingPayment = BookingPayment::where('booking_id', $booking->id)
            ->where('termin_ke', $terminKe)
            ->first();

        if ($terminKe >= 2 && (! $existingPayment || (! $existingPayment->hasIssuedPaymentCode() && ! in_array($existingPayment->status_pembayaran, ['kode_terbit', 'pending_verifikasi', 'ditolak'], true)))) {
            return back()->with('error', "Silakan klik \"Minta Kode Pembayaran\" untuk Termin ke-{$terminKe} dan tunggu Admin UPTD mengunggah kode bayar terlebih dahulu.");
        }

        $file = $request->file('bukti_file');
        // Simpan bukti bayar ke private disk
        $path = $file->store('booking-termin-payments', 'local');

        BookingPayment::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'termin_ke' => $terminKe,
            ],
            [
                'tahun_ke' => 'Tahun ke-'.$terminKe,
                'nominal_disetor' => $validated['nominal_disetor'] ?? null,
                'nomor_sts' => $validated['nomor_sts'] ?? null,
                'tanggal_bayar' => $validated['tanggal_bayar'] ?? now(),
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'status_pembayaran' => 'pending_verifikasi',
                'catatan' => null,
            ]
        );

        // Jika termin_ke === 1, sinkronkan juga dengan REQ_BUKTI_BAYAR
        if ((int) $validated['termin_ke'] === 1) {
            $reqBukti = BookingRequirement::where('kode', 'REQ_BUKTI_BAYAR')->first();
            if ($reqBukti) {
                BookingDocument::updateOrCreate(
                    [
                        'booking_id' => $booking->id,
                        'booking_requirement_id' => $reqBukti->id,
                    ],
                    [
                        'nomor_dokumen' => $validated['nomor_sts'] ?? ($reqBukti->kode.'/'.$booking->kode_booking),
                        'tanggal_dokumen' => $validated['tanggal_bayar'] ?? now(),
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getClientMimeType(),
                        'status_verifikasi' => 'pending',
                        'catatan' => null,
                    ]
                );
            }
        }

        return back()->with('success', 'Bukti setor Kasda Termin ke-'.$validated['termin_ke'].' berhasil diunggah! Petugas UPTD BPKAD Jabar akan segera memverifikasi transaksi Anda.');
    }
}
