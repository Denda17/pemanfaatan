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
                    <a href="{{ route('booking.tracker') }}" class="hover:text-emerald-700 transition">Lacak Pengajuan</a>
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
                            Berkas diinput dan diverifikasi oleh Admin UPTD berdasarkan dokumen fisik yang diserahkan pemohon.
                        </p>
                    </div>

                    <div class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200 shrink-0 self-start sm:self-auto">
                        {{ $booking->documents->where('status_verifikasi', 'verified')->count() }} dari 8 Persyaratan Lengkap
                    </div>
                </div>

                <!-- Document List Items -->
                @php
                    $allReqs = \App\Models\BookingRequirement::orderBy('urutan')->get();
                    $uploadedDocs = $booking->documents->keyBy('booking_requirement_id');
                @endphp

                <div class="space-y-3.5">
                    @foreach($allReqs as $req)
                        @php
                            $doc = $uploadedDocs->get($req->id);
                        @endphp
                        <div class="p-4 rounded-2xl border {{ $doc ? ($doc->status_verifikasi === 'verified' ? 'bg-emerald-50/40 border-emerald-200' : 'bg-gray-50 border-gray-200') : 'bg-white border-dashed border-gray-200' }} transition">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-3">
                                    <div class="mt-0.5 w-7 h-7 rounded-xl flex items-center justify-center text-xs font-extrabold shrink-0 {{ $doc && $doc->status_verifikasi === 'verified' ? 'bg-emerald-600 text-white shadow-xs' : ($doc ? 'bg-amber-500 text-white' : 'bg-gray-200 text-gray-500') }}">
                                        @if($doc && $doc->status_verifikasi === 'verified')
                                            ✓
                                        @else
                                            {{ $req->urutan }}
                                        @endif
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-extrabold text-gray-900 leading-snug">
                                            {{ $req->nama_persyaratan }}
                                            @if($req->is_required)
                                                <span class="text-[10px] text-rose-500 font-bold ml-1">*Wajib</span>
                                            @endif
                                        </h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5 leading-normal font-medium">{{ $req->deskripsi }}</p>

                                        @if($doc)
                                            <div class="mt-2 text-[11px] text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                                                @if($doc->nomor_dokumen)
                                                    <span>No. Dok: <strong class="text-gray-800">{{ $doc->nomor_dokumen }}</strong></span>
                                                @endif
                                                @if($doc->tanggal_dokumen)
                                                    <span>Tanggal: {{ $doc->tanggal_dokumen->format('d/m/Y') }}</span>
                                                @endif
                                                @if($doc->file_path)
                                                    <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-emerald-700 font-extrabold hover:underline flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                        Lihat Dokumen
                                                    </a>
                                                @endif
                                            </div>
                                            @if($doc->catatan)
                                                <div class="mt-2 text-[11px] text-gray-600 italic bg-white p-2.5 rounded-xl border border-gray-200">
                                                    Catatan UPTD: "{{ $doc->catatan }}"
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="shrink-0">
                                    @if($doc)
                                        @if($doc->status_verifikasi === 'verified')
                                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                Terverifikasi
                                            </span>
                                        @elseif($doc->status_verifikasi === 'revision')
                                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                                Perlu Revisi
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                                Sedang Ditelaah
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                            Menunggu Input Admin
                                        </span>
                                    @endif
                                </div>
                            </div>
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
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">Alamat Email:</span>
                        <span class="font-bold text-gray-800">{{ $booking->email ?: '-' }}</span>
                    </div>
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
