@extends('layouts.app')

@section('title', 'Status Pengajuan: ' . $booking->kode_booking . ' - SIP-BMD Jabar')

@section('content')
<!-- Header Status (Behance Light Aesthetic) -->
<div class="relative bg-mesh-light text-gray-900 py-12 lg:py-14 border-b border-gray-200/80 overflow-hidden">
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-2.5 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                    <span class="text-gray-300">/</span>
                    <a href="{{ route('booking.my') }}" class="hover:text-emerald-700 transition">Pengajuan Saya</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-emerald-700 font-extrabold font-mono">{{ $booking->kode_booking }}</span>
                </nav>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-gray-900">Status Permohonan Sewa BMD</h1>
                    <span class="font-mono font-extrabold text-xs bg-emerald-100 text-emerald-800 px-3 py-1 rounded-full border border-emerald-300">
                        {{ $booking->kode_booking }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1.5 font-medium">
                    Diajukan pada: {{ $booking->tanggal_booking ? $booking->tanggal_booking->format('d F Y, H:i') : $booking->created_at->format('d F Y, H:i') }} WIB
                </p>
            </div>

            <!-- Current Stage Badge -->
            <div class="flex items-center gap-3">
                @php
                    $badgeColors = [
                        'pending' => 'bg-amber-50 text-amber-800 border-amber-300',
                        'contacted' => 'bg-blue-50 text-blue-800 border-blue-300',
                        'document_review' => 'bg-indigo-50 text-indigo-800 border-indigo-300',
                        'field_survey' => 'bg-purple-50 text-purple-800 border-purple-300',
                        'approved' => 'bg-emerald-50 text-emerald-800 border-emerald-300',
                        'contract_signed' => 'bg-teal-50 text-teal-800 border-teal-300',
                        'rejected' => 'bg-rose-50 text-rose-800 border-rose-300',
                    ];
                    $badgeStyle = $badgeColors[$booking->status] ?? 'bg-gray-100 text-gray-700 border-gray-300';
                @endphp
                <div class="px-4 py-2.5 rounded-full text-xs font-extrabold border bg-white {{ $badgeStyle }} flex items-center gap-2 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <span>Tahap: {{ $booking->status_label }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-3 shadow-xs">
            <div class="w-6 h-6 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center gap-3 shadow-xs">
            <div class="w-6 h-6 rounded-lg bg-rose-600 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold shadow-xs">
            <p class="font-bold mb-1">Terdapat kesalahan:</p>
            <ul class="list-disc list-inside space-y-0.5 text-[11.5px]">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- 6-Step Stepper Progress Bar (Clean White Behance Card) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-200/90 mb-10 overflow-x-auto">
        <div class="flex items-center justify-between mb-8 pb-3 border-b border-gray-100">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                Linimasa Progres Permohonan (Permendagri No. 7 Tahun 2024)
            </h2>
            <span class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3.5 py-1 rounded-full border border-emerald-200">
                Tahap {{ $booking->status_step ?? '1' }} dari 6
            </span>
        </div>

        <div class="min-w-[700px] px-2 py-4">
            <div class="grid grid-cols-6 gap-2 relative">
                @foreach($steps as $idx => $st)
                    <div class="flex flex-col items-center text-center relative group">
                        
                        <!-- Connecting Line between dots -->
                        @if($idx < count($steps) - 1)
                            <div class="absolute top-5 left-1/2 w-full h-1 {{ $st['completed'] ? 'bg-emerald-600' : 'bg-gray-200' }} -z-0"></div>
                        @endif

                        <!-- Step Circle Icon -->
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-extrabold text-xs relative z-10 transition duration-300 {{ $st['completed'] ? 'bg-emerald-600 text-white shadow-md shadow-emerald-700/25 ring-4 ring-emerald-50' : ($st['active'] ? 'bg-amber-500 text-white shadow-md shadow-amber-500/25 ring-4 ring-amber-50' : 'bg-gray-100 text-gray-400 border border-gray-200') }}">
                            @if($st['completed'])
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $st['step'] }}
                            @endif
                        </div>

                        <!-- Step Title & Description -->
                        <div class="mt-3.5">
                            <span class="block text-xs font-extrabold {{ $st['active'] ? 'text-amber-800' : ($st['completed'] ? 'text-emerald-800' : 'text-gray-400') }}">
                                {{ $st['title'] }}
                            </span>
                            <span class="block text-[10px] text-gray-400 font-medium leading-tight mt-1 max-w-[125px] mx-auto">
                                {{ $st['desc'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($booking->catatan_admin)
            <div class="mt-8 p-4 sm:p-5 rounded-2xl bg-gray-50 border border-gray-200 text-xs text-gray-700">
                <div class="flex items-center gap-2 font-bold text-gray-900 mb-1.5">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    <span>Catatan Terakhir Petugas UPTD Pengamanan & Pemanfaatan Aset:</span>
                </div>
                <p class="whitespace-pre-line leading-relaxed text-gray-600 pl-6 font-medium">{{ $booking->catatan_admin }}</p>
            </div>
        @endif
    </div>

    <!-- Main 2-Column Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10">
        
        <!-- Left Column: Verification Documents & Details (8 cols) -->
        <div class="lg:col-span-8 space-y-8">
            
            <!-- Berkas Persyaratan Status Section -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-200/90">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-gray-100 gap-3">
                    <div>
                        <h2 class="text-base font-extrabold text-gray-900 flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span>Daftar Berkas Persyaratan Sewa (Permendagri 7/2024)</span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-1 font-medium">
                            Lengkapi berkas permohonan Anda di bawah ini sesuai tahapan dan format resmi yang telah ditentukan.
                        </p>
                    </div>

                    @php
                        $completeness = $booking->getCompletenessStats();
                        $percentage = $completeness['persentase'];
                        $totalWajib = $completeness['total_wajib'];
                        $totalTerunggah = $completeness['total_terunggah'];
                        $isAllComplete = $completeness['is_complete'];
                        $allReqs = \App\Models\BookingRequirement::orderBy('urutan')->get();
                        $uploadedDocs = $booking->documents->keyBy('booking_requirement_id');
                        $requiredCodes = $booking->getRequiredRequirementCodes();
                        $isApplicantUploadable = fn($kode) => in_array($kode, [
                            'REQ_SURAT_PERMOHONAN', 
                            'REQ_SURAT_PERNYATAAN', 
                            'REQ_SURAT_BADAN_HUKUM', 
                            'REQ_KTP', 
                            'REQ_NPWP', 
                            'REQ_NIB', 
                            'REQ_AKTA_PENDIRIAN', 
                            'REQ_PROPOSAL', 
                            'REQ_BUKTI_BAYAR'
                        ]);
                        $displayNumberMap = [
                            'REQ_SURAT_PERMOHONAN' => '1',
                            'REQ_SURAT_PERNYATAAN' => '2',
                            'REQ_SURAT_BADAN_HUKUM' => '3',
                            'REQ_KTP' => '4.a',
                            'REQ_NPWP' => '4.b',
                            'REQ_NIB' => '4.c',
                            'REQ_AKTA_PENDIRIAN' => '4.d',
                            'REQ_PROPOSAL' => '4.e',
                            'REQ_SURVEY_LAPANGAN' => '5',
                            'REQ_PENILAIAN_TARIF' => '6',
                            'REQ_PERSETUJUAN_SEKDA' => '7',
                            'REQ_DRAFT_PKS' => '8',
                            'REQ_BUKTI_BAYAR' => '9',
                        ];
                    @endphp

                    <div class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 shrink-0 self-start sm:self-auto flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $isAllComplete ? 'bg-emerald-500' : 'bg-amber-500 animate-pulse' }}"></span>
                        <span>{{ $completeness['total_terverifikasi'] }} Berkas Terverifikasi Sah</span>
                    </div>
                </div>

                <!-- Card Progres & Persentase Kelengkapan Dokumen (Patokan User) -->
                <div class="mb-8 p-5 sm:p-6 rounded-3xl bg-gradient-to-br from-emerald-50/90 via-teal-50/40 to-white border border-emerald-200/90 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2.5 h-2.5 rounded-full {{ $isAllComplete ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                <span class="text-[11px] uppercase font-extrabold tracking-wider text-emerald-800">Patokan Kelengkapan Dokumen</span>
                            </div>
                            <h3 class="text-lg font-black text-gray-900 tracking-tight flex items-center gap-2">
                                <span>{{ $isAllComplete ? 'Dokumen Persyaratan Wajib Lengkap (100%)' : 'Dokumen Persyaratan Belum Lengkap' }}</span>
                            </h3>
                            <p class="text-xs text-gray-600 mt-1 max-w-xl leading-relaxed">
                                @if($isAllComplete)
                                    Seluruh <strong>{{ $totalWajib }} dokumen persyaratan wajib</strong> telah berhasil Anda unggah. Tim verifikator UPTD BPKAD Jabar akan segera memeriksa dan menelaah keabsahan berkas Anda.
                                @else
                                    Anda telah mengunggah <strong>{{ $totalTerunggah }}</strong> dari <strong>{{ $totalWajib }} dokumen wajib</strong> ({{ $percentage }}%). Silakan lengkapi sisa {{ $totalWajib - $totalTerunggah }} dokumen di bawah ini agar permohonan dapat segera diproses ke tahap verifikasi.
                                @endif
                            </p>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 self-start sm:self-auto">
                            <div class="text-right">
                                <div class="text-3xl sm:text-4xl font-black {{ $isAllComplete ? 'text-emerald-700' : ($percentage >= 50 ? 'text-teal-700' : 'text-amber-700') }} font-mono tracking-tight">
                                    {{ $percentage }}%
                                </div>
                                <div class="text-[10px] font-extrabold text-gray-400 uppercase tracking-wider">
                                    {{ $totalTerunggah }}/{{ $totalWajib }} Berkas Wajib
                                </div>
                            </div>
                            <div class="w-12 h-12 rounded-2xl {{ $isAllComplete ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }} flex items-center justify-center shadow-md {{ $isAllComplete ? 'shadow-emerald-600/20' : 'shadow-amber-500/20' }}">
                                @if($isAllComplete)
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Progress Bar Track -->
                    <div class="w-full bg-gray-200/80 rounded-full h-3 overflow-hidden p-0.5">
                        <div class="h-full rounded-full transition-all duration-700 ease-out {{ $isAllComplete ? 'bg-gradient-to-r from-emerald-500 to-teal-500' : ($percentage >= 50 ? 'bg-gradient-to-r from-amber-500 to-emerald-500' : 'bg-gradient-to-r from-amber-500 to-orange-500') }}" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>

                <!-- Document List Items -->
                <div class="space-y-4">
                    @foreach($allReqs as $req)
                        @php
                            $doc = $uploadedDocs->get($req->id);
                            $canUpload = $isApplicantUploadable($req->kode);
                            $isMandatory = in_array($req->kode, $requiredCodes);
                            $isProposal = ($req->kode === 'REQ_PROPOSAL');
                            $proposalRequired = ($isProposal && $booking->durasi_sewa_tahun > 5);
                            $isNotApplicable = (!$booking->isBadanUsaha() && in_array($req->kode, ['REQ_AKTA_PENDIRIAN']));
                            $isVerified = ($doc && $doc->status_verifikasi === 'verified');
                            $isRevision = ($doc && $doc->status_verifikasi === 'revision');
                            $isPending = ($doc && $doc->status_verifikasi === 'pending');
                            $itemNumber = $displayNumberMap[$req->kode] ?? (string)$req->urutan;
                        @endphp

                        @if($req->kode === 'REQ_SURAT_PERMOHONAN')
                            <div class="pt-2 pb-1 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-emerald-800">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                <span>1. Berkas Template Persyaratan Resmi (Unduh & Bubuhkan Meterai)</span>
                            </div>
                        @elseif($req->kode === 'REQ_KTP')
                            <div class="pt-6 pb-1 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-blue-800 border-t border-gray-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span>
                                <span>2. Dokumen Pendukung Lainnya (Unggah Terpisah)</span>
                            </div>
                        @elseif($req->kode === 'REQ_SURVEY_LAPANGAN')
                            <div class="pt-6 pb-1 flex items-center gap-2 text-xs font-black uppercase tracking-wider text-gray-700 border-t border-gray-100">
                                <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                                <span>3. Dokumen Proses Telaah & Administrasi Lanjutan oleh Tim UPTD BPKAD Jabar</span>
                            </div>
                        @endif

                        <div class="p-4 sm:p-5 rounded-2xl border {{ $isVerified ? 'bg-emerald-50/40 border-emerald-200' : ($isRevision ? 'bg-rose-50/40 border-rose-200' : ($doc ? 'bg-amber-50/30 border-amber-200' : 'bg-gray-50/70 border-gray-200')) }} transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 min-w-[28px] h-7 px-1.5 rounded-xl flex items-center justify-center text-xs font-extrabold shrink-0 {{ $isVerified ? 'bg-emerald-600 text-white shadow-xs' : ($isRevision ? 'bg-rose-600 text-white shadow-xs' : ($doc ? 'bg-amber-500 text-white shadow-xs' : 'bg-gray-200 text-gray-500')) }}">
                                        @if($isVerified)
                                            ✓
                                        @else
                                            {{ $itemNumber }}
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-xs sm:text-sm font-extrabold text-gray-900 leading-snug flex items-center gap-1.5 flex-wrap">
                                            <span>{{ $req->nama_persyaratan }}</span>

                                            @if($req->kode === 'REQ_SURAT_PERMOHONAN' || $req->kode === 'REQ_SURAT_PERNYATAAN' || $req->kode === 'REQ_KTP' || $req->kode === 'REQ_NPWP')
                                                <span class="text-[10px] text-rose-500 font-bold">*Wajib</span>
                                            @elseif($req->kode === 'REQ_SURAT_BADAN_HUKUM')
                                                @if($booking->isBadanUsaha())
                                                    <span class="text-[10px] text-rose-600 font-extrabold bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">*Wajib (Badan Usaha)</span>
                                                @else
                                                    <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">Opsional / Khusus Badan Usaha</span>
                                                @endif
                                            @elseif($req->kode === 'REQ_NIB')
                                                @if($booking->isBadanUsaha())
                                                    <span class="text-[10px] text-rose-600 font-extrabold bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">*Wajib (Badan Usaha)</span>
                                                @else
                                                    <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">Opsional (Izin Usaha Mikro)</span>
                                                @endif
                                            @elseif($req->kode === 'REQ_AKTA_PENDIRIAN')
                                                @if($booking->isBadanUsaha())
                                                    <span class="text-[10px] text-rose-600 font-extrabold bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">*Wajib (Badan Usaha)</span>
                                                @else
                                                    <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">Tidak Berlaku (Perorangan)</span>
                                                @endif
                                            @elseif($req->kode === 'REQ_PROPOSAL')
                                                @if($proposalRequired)
                                                    <span class="text-[10px] text-rose-600 font-extrabold bg-rose-100 px-2 py-0.5 rounded-full border border-rose-200">*Wajib (Sewa > 5 Tahun)</span>
                                                @else
                                                    <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">Tidak Diwajibkan (&le; 5 Tahun)</span>
                                                @endif
                                            @elseif($req->is_required)
                                                <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full">Diterbitkan UPTD</span>
                                            @endif
                                        </h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5 leading-normal font-medium">{{ $req->deskripsi }}</p>

                                        @if($doc)
                                            <div class="mt-2.5 text-[11px] text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                                                @if($doc->file_name)
                                                    <span>Berkas: <strong class="text-gray-800">{{ $doc->file_name }}</strong></span>
                                                @endif
                                                @if($doc->tanggal_dokumen)
                                                    <span>Diunggah: {{ $doc->tanggal_dokumen->format('d/m/Y') }}</span>
                                                @endif
                                                @if($doc->file_path)
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-emerald-700 font-extrabold hover:underline flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        <span>Lihat Berkas Scan</span>
                                                    </a>
                                                @endif
                                            </div>

                                            @if($isRevision && $doc->catatan)
                                                <div class="mt-2.5 p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900 flex items-start gap-2">
                                                    <svg class="w-4 h-4 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                    <div>
                                                        <strong class="font-extrabold">Catatan Perbaikan dari UPTD:</strong>
                                                        <p class="mt-0.5 leading-relaxed">{{ $doc->catatan }}</p>
                                                    </div>
                                                </div>
                                            @elseif($doc->catatan)
                                                <div class="mt-2 text-[11px] text-gray-600 italic bg-white p-2.5 rounded-xl border border-gray-200">
                                                    Catatan UPTD: "{{ $doc->catatan }}"
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="shrink-0">
                                    @if($isVerified)
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                            ✓ Terverifikasi
                                        </span>
                                    @elseif($isRevision)
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                            Perlu Revisi
                                        </span>
                                    @elseif($isPending)
                                        <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                            Sedang Ditelaah UPTD
                                        </span>
                                    @elseif($isNotApplicable)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                            Tidak Diperlukan
                                        </span>
                                    @elseif($canUpload)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                            Belum Diunggah
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                            Diterbitkan Tim UPTD
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- 1. Khusus Surat Permohonan: Sediakan Tombol Download Template Resmi (Auto-Fill & Raw) -->
                            @if($req->kode === 'REQ_SURAT_PERMOHONAN')
                                <div class="mt-4 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50/70 to-emerald-50/40 border border-emerald-200/80">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-600 shrink-0"></span>
                                            <div>
                                                <h5 class="text-xs font-extrabold text-emerald-950">Draf Resmi Surat Permohonan Sewa</h5>
                                                <span class="text-[10px] text-emerald-700 font-semibold">Sesuai Format Permendagri No. 7 Tahun 2024</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('booking.surat-permohonan', $booking->kode_booking) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-xs transition" title="Unduh surat yang sudah terisi otomatis dengan data Anda & aset">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Unduh Surat Terisi Otomatis (.docx)</span>
                                            </a>
                                            <a href="{{ route('booking.surat-permohonan', ['kode' => $booking->kode_booking, 'mode' => 'raw']) }}" class="inline-flex items-center gap-1 px-3 py-2 rounded-xl bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 font-bold text-xs transition" title="Unduh template mentah kosong jika ingin mengisi manual">
                                                <span>Template Mentah (.docx)</span>
                                            </a>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-emerald-950/80 leading-relaxed font-medium">
                                        <strong>Petunjuk Alur:</strong> 1) Unduh draf surat di atas (data pemohon, peruntukan, dan objek aset sudah otomatis terisi). 2) Cetak dokumen, bubuhkan tanda tangan basah di atas <strong>meterai Rp 10.000</strong>. 3) Scan atau foto dokumen tersebut dalam format PDF/JPG untuk diunggah di bawah ini.
                                    </p>
                                </div>
                            @endif

                            <!-- 2. Khusus Surat Pernyataan: Sediakan Tombol Download Template Resmi -->
                            @if($req->kode === 'REQ_SURAT_PERNYATAAN')
                                <div class="mt-4 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-teal-50 via-emerald-50/70 to-teal-50/40 border border-teal-200/80">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-teal-600 shrink-0"></span>
                                            <div>
                                                <h5 class="text-xs font-extrabold text-teal-950">Template Resmi Surat Pernyataan Sewa BMD</h5>
                                                <span class="text-[10px] text-teal-700 font-semibold">Format Standar Kepatuhan Sewa BMD Pemprov Jawa Barat</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('booking.surat-pernyataan', $booking->kode_booking) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-xs transition" title="Unduh template resmi Surat Pernyataan (.docx)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Unduh Template Surat Pernyataan (.docx)</span>
                                            </a>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-teal-950/80 leading-relaxed font-medium">
                                        <strong>Petunjuk Alur:</strong> 1) Unduh format template dokumen resmi di atas. 2) Lengkapi data pernyataan, cetak, bubuhkan tanda tangan di atas <strong>meterai Rp 10.000</strong>. 3) Scan atau foto dokumen tersebut untuk diunggah pada formulir di bawah ini.
                                    </p>
                                </div>
                            @endif

                            <!-- 3. Khusus Surat Keterangan Badan Hukum/Usaha: Sediakan Tombol Download Template Resmi -->
                            @if($req->kode === 'REQ_SURAT_BADAN_HUKUM')
                                <div class="mt-4 p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-blue-50 via-indigo-50/60 to-blue-50/40 border border-blue-200/80">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2.5">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-blue-600 shrink-0"></span>
                                            <div>
                                                <h5 class="text-xs font-extrabold text-blue-950">Template Resmi Surat Keterangan Badan Hukum / Usaha</h5>
                                                <span class="text-[10px] text-blue-700 font-semibold">{{ $booking->isBadanUsaha() ? 'Wajib bagi Pemohon Badan Usaha' : 'Khusus Pemohon Badan Usaha (Opsional / Tidak Wajib bagi Perorangan)' }}</span>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('booking.surat-badan-hukum', $booking->kode_booking) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-xs transition" title="Unduh template resmi Surat Keterangan Badan Hukum/Usaha (.docx)">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                <span>Unduh Template Badan Hukum/Usaha (.docx)</span>
                                            </a>
                                        </div>
                                    </div>
                                    <p class="text-[11px] text-blue-950/80 leading-relaxed font-medium">
                                        <strong>Petunjuk Alur:</strong> 1) Unduh format template dokumen. 2) Diisi lengkap oleh direktur/pimpinan instansi dan dibubuhi stempel resmi perusahaan. 3) Scan dan unggah hasil dokumen di bawah.
                                    </p>
                                </div>
                            @endif

                            <!-- Form Upload Berkas (Jika Termasuk Berkas Pemohon dan Belum Terverifikasi) -->
                            @if($canUpload && !$isNotApplicable)
                                @if(!$isVerified)
                                    <form action="{{ route('booking.upload-dokumen', $booking->kode_booking) }}" method="POST" enctype="multipart/form-data" class="mt-3.5 pt-3 border-t border-gray-200/80 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                        @csrf
                                        <input type="hidden" name="booking_requirement_id" value="{{ $req->id }}">
                                        
                                        <div class="flex-grow">
                                            <input type="file" name="dokumen_file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer file:transition cursor-pointer bg-white p-1 rounded-xl border border-gray-200 focus:outline-none">
                                        </div>

                                        <button type="submit" class="px-4 py-2 rounded-xl {{ $isRevision ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }} active:scale-[0.98] text-white font-extrabold text-xs shadow-xs transition shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                            <span>{{ $doc ? 'Unggah Ulang Berkas' : 'Unggah Berkas Scan' }}</span>
                                        </button>
                                    </form>
                                @endif
                            @elseif($isNotApplicable)
                                <div class="mt-3 pt-2.5 border-t border-gray-200/60 text-[11px] text-gray-400 italic">
                                    Dokumen ini khusus Badan Usaha/Hukum dan tidak diwajibkan untuk pemohon perorangan.
                                </div>
                            @endif

                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Detail Permohonan -->
            <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-gray-200/90">
                <h3 class="text-sm font-extrabold text-gray-900 mb-5 pb-3 border-b border-gray-100">
                    Informasi Data Pemohon
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Kategori Pemohon:</span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $booking->isBadanUsaha() ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                            {{ $booking->tipe_pemohon_label }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Nama Pemohon:</span>
                        <span class="font-extrabold text-gray-900 text-sm">{{ $booking->nama_pemohon }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Instansi / Badan Usaha:</span>
                        <span class="font-bold text-gray-800 text-sm">{{ $booking->nama_instansi ?: 'Perorangan' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Nomor WhatsApp:</span>
                        <span class="font-extrabold text-emerald-700 text-sm">{{ $booking->nomor_whatsapp }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">NIK / NIB:</span>
                        <span class="font-bold text-gray-800">{{ $booking->nik_atau_nib ?: '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Rencana Durasi Sewa:</span>
                        <span class="font-extrabold text-gray-900">{{ $booking->durasi_sewa_tahun }} Tahun</span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Skema Pembayaran:</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-extrabold {{ $booking->skema_pembayaran === 'bertahap' ? 'bg-blue-50 text-blue-800 border border-blue-200' : 'bg-emerald-50 text-emerald-800 border border-emerald-200' }}">
                            {{ $booking->skema_pembayaran_label }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Alamat Email:</span>
                        <span class="font-bold text-gray-800">{{ $booking->email ?: '-' }}</span>
                    </div>
                    @if($booking->proposal_bisnis_path)
                    <div class="sm:col-span-2 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <span class="block text-xs font-extrabold text-amber-950">Dokumen Proposal Bisnis / Feasibility Study</span>
                                <span class="block text-[11px] text-amber-800">Dilampirkan pemohon untuk pemanfaatan sewa jangka panjang ({{ $booking->durasi_sewa_tahun }} Tahun)</span>
                            </div>
                        </div>
                        <a href="{{ $booking->proposal_bisnis_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 active:scale-[0.98] text-white font-extrabold text-xs transition shadow-xs shrink-0 self-start sm:self-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Berkas</span>
                        </a>
                    </div>
                    @endif
                    <div class="sm:col-span-2">
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Rencana Konsep Usaha:</span>
                        <div class="p-3.5 bg-gray-50 rounded-2xl text-gray-700 border border-gray-200 leading-relaxed font-medium">
                            {{ $booking->rencana_peruntukan }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Asset Summary & Helpdesk (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Asset Card Summary -->
            @if($booking->asset)
                <div class="behance-card rounded-3xl overflow-hidden shadow-xs flex flex-col justify-between">
                    <div class="relative aspect-[16/10] bg-gray-100">
                        <img src="{{ $booking->asset->thumbnail_url }}" alt="{{ $booking->asset->nama_objek }}" class="w-full h-full object-cover">
                    </div>
                    <div class="p-5 sm:p-6">
                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700 block mb-1">
                            Objek Yang Diajukan
                        </span>
                        <h3 class="font-extrabold text-sm text-gray-900 leading-snug mb-2">
                            <a href="{{ route('catalog.show', $booking->asset->slug) }}" class="hover:text-emerald-700 transition">
                                {{ $booking->asset->nama_objek }}
                            </a>
                        </h3>
                        <p class="text-xs text-gray-500 mb-3 flex items-center gap-1 font-medium">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $booking->asset->kabupaten_kota }}</span>
                        </p>

                        <div class="grid grid-cols-2 gap-2 text-xs py-2.5 border-y border-gray-100 text-gray-600 mb-4 bg-gray-50 rounded-2xl px-2.5">
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-bold">Luas Tanah:</span>
                                <span class="font-extrabold text-gray-900">{{ $booking->asset->luas_tanah ? number_format($booking->asset->luas_tanah, 0, ',', '.') . ' m²' : '-' }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] uppercase font-bold">Bentuk:</span>
                                <span class="font-extrabold text-gray-900">{{ $booking->asset->bentuk_objek }}</span>
                            </div>
                        </div>

                        <a href="{{ route('catalog.show', $booking->asset->slug) }}" class="block text-center py-2.5 px-4 rounded-full text-xs font-extrabold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 transition border border-emerald-200/80">
                            Lihat Halaman Objek Aset &rarr;
                        </a>
                    </div>
                </div>
            @endif

            <!-- Helpdesk WhatsApp Card (Eye-Catching Gradient) -->
            <div class="bg-gradient-to-br from-emerald-600 via-teal-600 to-emerald-700 text-white rounded-3xl p-6 sm:p-7 shadow-lg shadow-emerald-700/20 border border-emerald-500/30">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md text-white flex items-center justify-center mb-4 border border-white/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </div>
                <h3 class="text-sm font-extrabold text-white mb-1">Butuh Koordinasi Cepat?</h3>
                <p class="text-xs text-emerald-100 leading-relaxed mb-5 font-normal">
                    Jika Anda memiliki pertanyaan tentang kelengkapan berkas fisik atau jadwal survei lokasi, hubungi langsung Tim Layanan UPTD.
                </p>

                @php
                    $waMsg = urlencode("Halo Petugas UPTD Pengamanan dan Pemanfaatan Aset Jabar, saya ingin mengonfirmasi progres permohonan booking kode: " . $booking->kode_booking . " atas nama " . $booking->nama_pemohon . ". Terima kasih.");
                    $waLink = "https://wa.me/6281234567890?text=" . $waMsg;
                @endphp

                <a href="{{ $waLink }}" target="_blank" class="w-full py-3 px-4 rounded-full bg-white hover:bg-emerald-50 text-emerald-950 font-extrabold text-xs shadow-md transition flex items-center justify-center gap-2 active:scale-95">
                    <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                    <span>Konfirmasi via WhatsApp UPTD</span>
                </a>
            </div>

            <!-- Print summary button -->
            <button onclick="window.print()" class="w-full py-3 px-4 rounded-full bg-white text-gray-700 hover:bg-gray-50 border border-gray-200 font-extrabold text-xs transition flex items-center justify-center gap-2 shadow-2xs">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Tanda Bukti Pengajuan</span>
            </button>

        </div>

    </div>
</div>
@endsection
