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

    @php
        $isBeautyContestLoser = ($booking->status === 'rejected' && ($booking->beauty_contest_status === 'completed' || str_contains($booking->alasan_penolakan ?? '', 'Beauty Contest') || str_contains($booking->catatan_admin ?? '', 'Beauty Contest')));
    @endphp

    {{-- 1. RUANG KHUSUS: PEMENANG BEAUTY CONTEST / MITRA TERPILIH (DITERIMA) --}}
    @if($booking->is_beauty_contest_winner)
        <div class="mb-8 bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-800 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-emerald-950/25 border-2 border-emerald-400/50 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-amber-400/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex items-start gap-4 sm:gap-5">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-amber-400 text-amber-950 flex items-center justify-center shrink-0 shadow-lg shadow-amber-400/30 mt-0.5">
                            <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap mb-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-black bg-amber-400 text-amber-950 uppercase tracking-wider shadow-xs flex items-center gap-1.5">
                                    <span>🏆</span>
                                    <span>Pemenang Beauty Contest</span>
                                </span>
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white backdrop-blur-xs border border-white/25">
                                    Permohonan Diterima &amp; Ditetapkan
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                Selamat! Anda Ditetapkan Sebagai Pemenang Seleksi Sewa BMD
                            </h2>
                            <p class="text-xs sm:text-sm text-emerald-100 mt-1.5 max-w-3xl leading-relaxed">
                                Berdasarkan hasil penilaian dan evaluasi sesi pemaparan (Beauty Contest), permohonan sewa untuk objek <strong class="text-white underline decoration-amber-400 decoration-2 underline-offset-2">{{ $booking->asset?->nama_objek }}</strong> resmi ditetapkan kepada <strong class="text-white">{{ $booking->nama_pemohon }}</strong>.
                            </p>
                        </div>
                    </div>

                    @if($booking->beauty_contest_berita_acara_path)
                        <div class="shrink-0">
                            <a href="{{ $booking->berita_acara_seleksi_url ?? '#' }}" target="_blank" class="px-5 py-3 rounded-2xl bg-white hover:bg-emerald-50 text-emerald-950 text-xs font-black shadow-md transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Unduh Berita Acara Seleksi (.pdf)</span>
                            </a>
                        </div>
                    @endif
                </div>

                <!-- RUANG KHUSUS: CATATAN RESMI PENETAPAN EVALUASI UPTD -->
                <div class="mt-6 bg-white/95 backdrop-blur-md rounded-2xl p-5 sm:p-6 text-gray-900 shadow-md border border-emerald-300/60">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-emerald-800 mb-2">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Catatan Resmi Penetapan Hasil Evaluasi UPTD:</span>
                    </div>
                    <p class="text-sm sm:text-base font-extrabold text-gray-900 leading-relaxed pl-4 border-l-4 border-amber-400">
                        "{{ $booking->catatan_admin ?: 'Ditetapkan sebagai pemenang seleksi Beauty Contest. Selamat, permohonan Anda berhak melanjutkan ke proses sewa.' }}"
                    </p>
                </div>

                <!-- Panduan Tahapan Selanjutnya (Roadmap) -->
                <div class="mt-5 pt-5 border-t border-emerald-500/50 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                    <div class="bg-emerald-800/40 rounded-xl p-3.5 border border-emerald-400/30">
                        <span class="font-extrabold text-amber-300 block mb-1">1. BA Negosiasi &amp; Kepgub</span>
                        <p class="text-[11px] text-emerald-100 leading-relaxed">Tim UPTD menyiapkan Berita Acara Negosiasi dan fasilitasi Keputusan Gubernur Jawa Barat.</p>
                    </div>
                    <div class="bg-emerald-800/40 rounded-xl p-3.5 border border-emerald-400/30">
                        <span class="font-extrabold text-amber-300 block mb-1">2. Perjanjian Kerja Sama (PKS)</span>
                        <p class="text-[11px] text-emerald-100 leading-relaxed">Penyusunan dan penandatanganan naskah resmi PKS pemanfaatan BMD.</p>
                    </div>
                    <div class="bg-emerald-800/40 rounded-xl p-3.5 border border-emerald-400/30">
                        <span class="font-extrabold text-amber-300 block mb-1">3. Pembayaran via STS</span>
                        <p class="text-[11px] text-emerald-100 leading-relaxed">Penyetoran uang sewa ke Kasda (RKUD) Pemprov Jabar sebelum serah terima fisik aset.</p>
                    </div>
                </div>
            </div>
        </div>

    {{-- 2. RUANG KHUSUS: PESERTA BEAUTY CONTEST TIDAK TERPILIH (KALAH BEAUTY CONTEST) --}}
    @elseif($isBeautyContestLoser)
        <div class="mb-8 bg-gradient-to-br from-slate-900 via-slate-800 to-rose-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-950/30 border-2 border-rose-500/40 relative overflow-hidden">
            <div class="absolute -right-12 -top-12 w-64 h-64 bg-rose-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-start justify-between gap-6">
                    <div class="flex items-start gap-4 sm:gap-5">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-rose-600/90 text-white flex items-center justify-center shrink-0 shadow-lg shadow-rose-600/30 mt-0.5">
                            <svg class="w-8 h-8 sm:w-9 sm:h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2.5 flex-wrap mb-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-black bg-rose-500 text-white uppercase tracking-wider shadow-xs">
                                    Hasil Seleksi Beauty Contest
                                </span>
                                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-white/20 text-white backdrop-blur-xs border border-white/25">
                                    Belum Terpilih Sebagai Mitra
                                </span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">
                                Pemberitahuan Hasil Seleksi Terbuka (Beauty Contest)
                            </h2>
                            <p class="text-xs sm:text-sm text-slate-300 mt-1.5 max-w-3xl leading-relaxed">
                                Terima kasih atas partisipasi dan pemaparan rencana usaha yang telah Anda sampaikan untuk objek <strong class="text-white underline decoration-rose-400 decoration-2 underline-offset-2">{{ $booking->asset?->nama_objek }}</strong>. Berdasarkan hasil evaluasi komprehensif oleh Tim Penilai UPTD, objek sewa ini telah ditetapkan kepada mitra dengan perolehan nilai evaluasi tertinggi.
                            </p>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <a href="{{ route('catalog.index') }}" class="px-5 py-3 rounded-2xl bg-white hover:bg-rose-50 text-slate-950 text-xs font-black shadow-md transition flex items-center justify-center gap-2">
                            <span>Jelajahi Aset Lain di Katalog &rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- RUANG KHUSUS: KETERANGAN & CATATAN RESMI HASIL EVALUASI TIM PENILAI UPTD -->
                <div class="mt-6 bg-white/10 backdrop-blur-md rounded-2xl p-5 sm:p-6 border border-rose-400/40 text-white shadow-inner">
                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-rose-300 mb-3">
                        <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Keterangan Resmi Hasil Evaluasi Tim Penilai UPTD:</span>
                    </div>
                    
                    <div class="space-y-3 pl-4 border-l-4 border-rose-400">
                        @if($booking->alasan_penolakan)
                            <div>
                                <span class="text-[11px] font-bold text-rose-300 uppercase tracking-wide block">Alasan Penetapan:</span>
                                <p class="text-sm sm:text-base font-extrabold text-white mt-0.5 leading-relaxed">
                                    {{ $booking->alasan_penolakan }}
                                </p>
                            </div>
                        @endif
                        
                        @if($booking->catatan_admin)
                            <div>
                                <span class="text-[11px] font-bold text-rose-300 uppercase tracking-wide block">Catatan Tim Penilai UPTD:</span>
                                <p class="text-xs sm:text-sm text-slate-200 mt-0.5 leading-relaxed italic">
                                    "{{ $booking->catatan_admin }}"
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-700/80 text-xs text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <span>Pemerintah Provinsi Jawa Barat mengapresiasi partisipasi Anda. Kesempatan sewa terbuka untuk objek BMD potensial lainnya.</span>
                    <span class="text-rose-300 font-bold">Status: Permohonan Tidak Terpilih</span>
                </div>
            </div>
        </div>

    {{-- 3. RUANG KHUSUS: PERMOHONAN DITOLAK REGULER (BUKAN BEAUTY CONTEST) --}}
    @elseif($booking->status === 'rejected')
        <div class="mb-8 bg-gradient-to-br from-rose-500/10 via-rose-50/80 to-white rounded-3xl p-6 sm:p-7 border-2 border-rose-300 shadow-md shadow-rose-500/5 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/25 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-rose-600 text-white uppercase tracking-wider">
                                Permohonan Ditolak
                            </span>
                            <span class="text-xs text-rose-900 font-bold">
                                Pemberitahuan Resmi UPTD
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                            Permohonan Sewa Belum Dapat Disetujui
                        </h3>
                        <p class="text-xs text-gray-600 mt-1 max-w-2xl leading-relaxed">
                            Mohon maaf, permohonan sewa Anda untuk objek <strong class="text-gray-900">{{ $booking->asset?->nama_objek }}</strong> belum dapat disetujui oleh tim verifikator UPTD.
                        </p>

                        <!-- RUANG KHUSUS CATATAN PENOLAKAN -->
                        <div class="mt-4 p-4 rounded-2xl bg-rose-100/70 border border-rose-200 text-xs text-rose-950">
                            <strong class="font-extrabold text-rose-900 block mb-1">Alasan Penolakan:</strong>
                            <p class="leading-relaxed font-semibold">{{ $booking->alasan_penolakan ?? 'Dokumen atau permohonan tidak memenuhi kualifikasi pemanfaatan BMD.' }}</p>
                            @if($booking->catatan_admin)
                                <div class="mt-2 pt-2 border-t border-rose-200/80 text-[11.5px] text-rose-800">
                                    <strong>Catatan Petugas:</strong> "{{ $booking->catatan_admin }}"
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('catalog.index') }}" class="px-5 py-3 rounded-2xl bg-gray-900 hover:bg-black text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center gap-2">
                        <span>Jelajahi Aset Lain di Katalog &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

    {{-- 4. TAHAP BEAUTY CONTEST SEDANG BERJALAN (BELUM FINAL) --}}
    @elseif($booking->status === 'beauty_contest')
        <div class="mb-8 bg-gradient-to-br from-purple-500/10 via-purple-50/80 to-white rounded-3xl p-6 sm:p-7 border-2 border-purple-300 shadow-md shadow-purple-500/5 relative overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-purple-600/25 mt-0.5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                </div>
                <div class="flex-grow">
                    <div class="flex items-center gap-2.5 flex-wrap mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-purple-600 text-white uppercase tracking-wider">
                            Tahap Beauty Contest
                        </span>
                        <span class="text-xs text-purple-900 font-bold">
                            Seleksi Terbuka Pemanfaatan BMD
                        </span>
                    </div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                        Permohonan Anda Masuk ke Tahap Seleksi Beauty Contest
                    </h3>
                    <p class="text-xs text-gray-600 mt-1 max-w-2xl leading-relaxed">
                        Terdapat lebih dari satu peminat yang berhasil melengkapi seluruh dokumen persyaratan sebelum batas waktu berakhir. Pihak UPTD Pengamanan &amp; Pemanfaatan Aset menyelenggarakan sesi pemaparan rencana usaha untuk menentukan mitra pemanfaatan terbaik.
                    </p>

                    @if($booking->isBeautyContestScheduled())
                        <div class="mt-4 p-4 rounded-2xl bg-white border border-purple-200 shadow-xs">
                            <div class="flex items-center gap-2 font-black text-purple-900 text-xs mb-2.5">
                                <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                                <span>JADWAL RESMI SESI PEMAPARAN (BEAUTY CONTEST):</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <span class="text-gray-400 font-bold uppercase text-[10px] block">Waktu Pelaksanaan:</span>
                                    <strong class="text-gray-900 text-sm">{{ $booking->formatted_beauty_contest_schedule }}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-400 font-bold uppercase text-[10px] block">Lokasi / Media:</span>
                                    @if(str_contains($booking->beauty_contest_location, 'http'))
                                        <a href="{{ $booking->beauty_contest_location }}" target="_blank" class="inline-flex items-center gap-1.5 text-purple-700 font-bold hover:underline">
                                            <span>Buka Tautan Video Conference</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                        </a>
                                    @else
                                        <strong class="text-gray-900">{{ $booking->beauty_contest_location }}</strong>
                                    @endif
                                </div>
                                @if($booking->beauty_contest_notes)
                                    <div class="sm:col-span-2 pt-2 border-t border-gray-100 text-[11.5px] text-gray-600">
                                        <strong class="text-gray-800">Petunjuk Materi:</strong> {{ $booking->beauty_contest_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="mt-3.5 p-3 rounded-xl bg-purple-50/80 border border-purple-200 text-xs text-purple-900 flex items-center gap-2 font-medium">
                            <svg class="w-4 h-4 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Jadwal pemaparan sedang difinalisasi oleh tim UPTD dan akan diumumkan di sini serta melalui nomor WhatsApp Anda.</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    {{-- 5. MASA TENGGANG KEDALUWARSA KARENA TIDAK LENGKAP 5 HARI KERJA (EXPIRED) --}}
    @elseif($booking->status === 'expired' || ($booking->deadline_at && now()->gt($booking->deadline_at) && !$booking->getCompletenessStats()['is_complete']))
        <div class="mb-8 bg-gradient-to-br from-rose-500/10 via-rose-50/80 to-white rounded-3xl p-6 sm:p-7 border-2 border-rose-300 shadow-md shadow-rose-500/5 relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/25 mt-0.5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-rose-600 text-white uppercase tracking-wider">
                                Masa Pendaftaran Telah Kedaluwarsa
                            </span>
                            <span class="text-xs text-rose-900 font-bold">
                                Batas Waktu 5 Hari Kerja Terlewati
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                            Masa Tenggang Pengunggahan Berkas Telah Berakhir
                        </h3>
                        <p class="text-xs text-gray-600 mt-1 max-w-2xl leading-relaxed">
                            Batas waktu melengkapi berkas persyaratan sewa untuk permohonan ini telah berakhir pada <strong class="text-gray-900">{{ $booking->formatted_deadline }}</strong>. Sesuai ketentuan, permohonan yang belum dilengkapi seluruh berkasnya dinyatakan gugur secara otomatis.
                        </p>
                    </div>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('catalog.index') }}" class="px-5 py-3 rounded-2xl bg-gray-900 hover:bg-black text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center gap-2">
                        <span>Jelajahi Aset Lain di Katalog &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

    {{-- 6. MASA TENGGANG MASIH BERJALAN & BERKAS BELUM LENGKAP --}}
    @elseif($booking->deadline_at && now()->lte($booking->deadline_at) && !$booking->getCompletenessStats()['is_complete'])
        <div class="mb-8 bg-gradient-to-br from-amber-500/10 via-amber-50/80 to-white rounded-3xl p-6 sm:p-7 border-2 border-amber-300 shadow-md shadow-amber-500/5 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-40 h-40 bg-amber-400/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/25 mt-0.5">
                        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5 flex-wrap mb-1">
                            <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-amber-500 text-white uppercase tracking-wider">
                                Masa Pendaftaran Terbuka (5 Hari Kerja)
                            </span>
                            <span class="text-xs text-amber-900 font-bold">
                                Batas Waktu Melengkapi Berkas
                            </span>
                        </div>
                        <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                            Lengkapi Seluruh Persyaratan Sebelum Batas Waktu
                        </h3>
                        <p class="text-xs text-gray-600 mt-1 max-w-2xl leading-relaxed">
                            Permohonan Anda tercatat untuk objek <strong class="text-gray-900">{{ $booking->asset?->nama_objek }}</strong>. Seluruh berkas wajib dilengkapi dan dikirimkan secara serentak dalam masa tenggang 5 hari kerja (hingga hari ke-5 pukul 23:59 WIB).
                        </p>
                    </div>
                </div>

                <!-- Live Countdown Box -->
                <div class="bg-white rounded-2xl p-4 border border-amber-200 shadow-xs shrink-0 md:min-w-[260px] text-center md:text-right">
                    <span class="text-[10.5px] font-extrabold text-gray-400 uppercase tracking-wider block mb-1">
                        Sisa Waktu Anda
                    </span>
                    <div class="text-lg sm:text-xl font-black font-mono text-amber-600" id="live-countdown-timer" data-deadline="{{ $booking->deadline_at?->toIso8601String() }}">
                        Sisa {{ $booking->remaining_time }}
                    </div>
                    <span class="text-[11px] text-gray-500 font-medium block mt-1">
                        Batas: <strong class="text-gray-800">{{ $booking->formatted_deadline }}</strong>
                    </span>
                </div>
            </div>

            <!-- Notice / Warning Box: Ketentuan Batas Waktu & Seleksi Peminat -->
            <div class="mt-6 pt-5 border-t border-amber-200/90 relative z-10">
                <div class="bg-gradient-to-r from-amber-100/90 via-amber-50/95 to-amber-100/70 border border-amber-300 rounded-2xl p-4 sm:p-5 text-xs text-amber-950 shadow-xs">
                    <div class="flex items-center gap-2 font-black text-amber-900 uppercase tracking-wider text-[11px] mb-3">
                        <svg class="w-4 h-4 text-amber-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Ketentuan Penting Batas Waktu &amp; Seleksi Peminat (Permendagri No. 7/2024):</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 leading-relaxed">
                        <div class="bg-white/90 rounded-xl p-3.5 border border-amber-200/80 shadow-xs flex flex-col justify-start">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-5 h-5 rounded-full bg-amber-500 text-white font-black text-[10px] flex items-center justify-center shrink-0">1</span>
                                <span class="font-extrabold text-amber-950 text-xs">Batas Waktu Mutlak</span>
                            </div>
                            <p class="text-[11px] text-gray-700">
                                Batas waktu pengunggahan seluruh berkas persyaratan terikat penuh dengan masa aktif tombol sewa aset (maksimal hari kerja ke-5 pukul 23:59 WIB).
                            </p>
                        </div>
                        <div class="bg-white/90 rounded-xl p-3.5 border border-rose-200/80 shadow-xs flex flex-col justify-start">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-5 h-5 rounded-full bg-rose-600 text-white font-black text-[10px] flex items-center justify-center shrink-0">2</span>
                                <span class="font-extrabold text-rose-950 text-xs">Gugur Otomatis &amp; Terbuka Lagi</span>
                            </div>
                            <p class="text-[11px] text-gray-700">
                                Jika batas waktu habis dan berkas belum lengkap, permohonan <strong>otomatis dibatalkan</strong>, booking hilang, dan aset kembali berstatus <strong>"Tersedia"</strong> di beranda katalog untuk dapat diminati kembali oleh Anda maupun pihak lain.
                            </p>
                        </div>
                        <div class="bg-white/90 rounded-xl p-3.5 border border-emerald-200/80 shadow-xs flex flex-col justify-start">
                            <div class="flex items-center gap-1.5 mb-1.5">
                                <span class="w-5 h-5 rounded-full bg-emerald-600 text-white font-black text-[10px] flex items-center justify-center shrink-0">3</span>
                                <span class="font-extrabold text-emerald-950 text-xs">Penentuan Peminat Terpilih</span>
                            </div>
                            <p class="text-[11px] text-gray-700">
                                Apabila hanya <strong>1 peminat</strong> yang lengkap, langsung lanjut ke tahap verifikasi. Jika terdapat <strong>lebih dari 1 peminat yang berhasil melengkapi berkas</strong>, pihak UPTD akan mengadakan seleksi terbuka (<strong>Beauty Contest</strong>).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    {{-- 7. BERKAS LENGKAP 100% (MENUNGGU VERIFIKASI PETUGAS / AKHIR BATAS WAKTU) --}}
    @elseif($booking->getCompletenessStats()['is_complete'])
        <div class="mb-8 bg-gradient-to-br from-emerald-500/10 via-emerald-50/80 to-white rounded-3xl p-6 sm:p-7 border-2 border-emerald-300 shadow-md shadow-emerald-500/5 relative overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/25 mt-0.5">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5 flex-wrap mb-1">
                        <span class="px-2.5 py-0.5 rounded-full text-[10.5px] font-extrabold bg-emerald-600 text-white uppercase tracking-wider">
                            Berkas Persyaratan Lengkap (100%)
                        </span>
                        <span class="text-xs text-emerald-800 font-bold">
                            Tahap Verifikasi Petugas
                        </span>
                    </div>
                    <h3 class="text-base sm:text-lg font-extrabold text-gray-900">
                        Seluruh Berkas Telah Berhasil Dikirimkan
                    </h3>
                    <p class="text-xs text-gray-600 mt-1 max-w-2xl leading-relaxed">
                        Terima kasih telah melengkapi seluruh persyaratan tepat waktu. Tim Verifikator UPTD Pengamanan dan Pemanfaatan Aset BPKAD Jawa Barat sedang menelaah keabsahan dokumen Anda.
                    </p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- 6-Step Stepper Progress Bar (Clean White Behance Card) -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-200/90 mb-10 overflow-x-auto">
        <div class="flex items-center justify-between mb-8 pb-3 border-b border-gray-100">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">
                Linimasa Progres Permohonan (Permendagri No. 7 Tahun 2024)
            </h2>
            @if($booking->is_beauty_contest_winner)
                <span class="text-xs font-extrabold text-emerald-800 bg-emerald-100 px-3.5 py-1 rounded-full border border-emerald-300 flex items-center gap-1.5">
                    <span>🏆</span>
                    <span>Tahap {{ $booking->status_step }} dari 6 (Mitra Terpilih)</span>
                </span>
            @elseif($booking->status === 'rejected')
                <span class="text-xs font-extrabold text-rose-800 bg-rose-100 px-3.5 py-1 rounded-full border border-rose-300">
                    Permohonan Selesai (Ditolak)
                </span>
            @else
                <span class="text-xs font-extrabold text-emerald-800 bg-emerald-50 px-3.5 py-1 rounded-full border border-emerald-200">
                    Tahap {{ $booking->status_step }} dari 6
                </span>
            @endif
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
            <div class="mt-8 p-4 sm:p-5 rounded-2xl {{ $booking->is_beauty_contest_winner ? 'bg-emerald-50/80 border-emerald-200 text-emerald-950' : ($booking->status === 'rejected' ? 'bg-rose-50/80 border-rose-200 text-rose-950' : 'bg-gray-50 border-gray-200 text-gray-700') }} border text-xs">
                <div class="flex items-center gap-2 font-black {{ $booking->is_beauty_contest_winner ? 'text-emerald-900' : ($booking->status === 'rejected' ? 'text-rose-900' : 'text-gray-900') }} mb-1.5">
                    @if($booking->is_beauty_contest_winner)
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span>Catatan Penetapan Pemenang Seleksi UPTD:</span>
                    @elseif($booking->status === 'rejected')
                        <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Catatan Hasil Evaluasi Seleksi UPTD:</span>
                    @else
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                        <span>Catatan Terakhir Petugas UPTD Pengamanan &amp; Pemanfaatan Aset:</span>
                    @endif
                </div>
                <p class="whitespace-pre-line leading-relaxed {{ $booking->is_beauty_contest_winner ? 'text-emerald-800' : ($booking->status === 'rejected' ? 'text-rose-800' : 'text-gray-600') }} pl-6 font-semibold">{{ $booking->catatan_admin }}</p>
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
                        $isExpired = $booking->isExpired();
                        $hasRevision = $booking->hasRevisionDocuments();
                        $isBatchMode = (!$isAllComplete && !$isExpired && !$hasRevision);
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
                            'REQ_LAPORAN_PENILAIAN' => '1',
                            'REQ_BA_NEGOSIASI' => '2',
                            'REQ_FASILITASI_KEPGUB' => '3',
                            'REQ_KEPUTUSAN_GUBERNUR' => '4',
                            'REQ_PEMBERITAHUAN_PEMBAYARAN' => '5',
                            'REQ_BUKTI_BAYAR' => '6',
                            'REQ_PERJANJIAN_SEWA' => '7',
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

                    <!-- Jaminan Keamanan & Pelindungan Data Pribadi (UU PDP) -->
                    <div class="p-3.5 sm:p-4 rounded-2xl bg-gradient-to-r from-emerald-50 via-teal-50 to-slate-50 border border-emerald-200/80 flex items-start sm:items-center gap-3 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs mt-0.5 sm:mt-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <div class="text-xs text-gray-700 leading-relaxed">
                            <span class="font-extrabold text-emerald-950 block sm:inline">Pelindungan Data Pribadi (UU PDP No. 27/2022):</span>
                            <span class="text-gray-600">Seluruh dokumen identitas (KTP, NPWP, NIB) disimpan di media penyimpanan privat dengan watermark pengamanan dinamis, dan hanya dapat diakses oleh Anda serta Tim Verifikator Resmi UPTD BPKAD Jabar.</span>
                        </div>
                    </div>
                </div>

                <!-- Document List Items -->
                @if($isBatchMode)
                    <form action="{{ route('booking.upload-all', $booking->kode_booking) }}" method="POST" enctype="multipart/form-data" id="batch-upload-form" class="space-y-4">
                        @csrf
                @else
                    <div class="space-y-4">
                @endif
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
                        @elseif($req->kode === 'REQ_LAPORAN_PENILAIAN')
                            <div class="pt-6 pb-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs font-black uppercase tracking-wider text-gray-800 border-t border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                                    <span>3. Berkas Administrasi &amp; Penetapan Sewa (7 Dokumen)</span>
                                </div>
                                <span class="text-[10px] font-extrabold text-emerald-800 normal-case bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">Diinput Admin UPTD &amp; Khusus Bukti Bayar oleh Mitra</span>
                            </div>
                        @endif

                        <div id="requirement-{{ $req->kode }}" class="p-4 sm:p-5 rounded-2xl border {{ $isVerified ? 'bg-emerald-50/40 border-emerald-200' : ($isRevision ? 'bg-rose-50/40 border-rose-200' : ($doc ? 'bg-amber-50/30 border-amber-200' : 'bg-gray-50/70 border-gray-200')) }} transition">
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
                                            <span>
                                                @if($req->kode === 'REQ_BUKTI_BAYAR')
                                                    {{ $req->nama_persyaratan }}
                                                    <span class="text-xs font-semibold text-gray-500">
                                                        - {{ $booking->skema_pembayaran === 'bertahap' ? 'Bukti Setor PAD (Termin Ke-1 - Tahun Pertama)' : 'Bukti Setor PAD (Pelunasan 100% Sewa)' }}
                                                    </span>
                                                @else
                                                    {{ $req->nama_persyaratan }}
                                                @endif
                                            </span>

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
                                            @elseif($req->kode === 'REQ_LAPORAN_PENILAIAN')
                                                <span class="text-[10px] text-gray-600 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">1. Diinput Admin UPTD (Arsip Internal)</span>
                                            @elseif($req->kode === 'REQ_BA_NEGOSIASI')
                                                <span class="text-[10px] text-blue-700 font-extrabold bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">2. Diinput Admin UPTD (Dapat Diunduh)</span>
                                            @elseif($req->kode === 'REQ_FASILITASI_KEPGUB')
                                                <span class="text-[10px] text-gray-600 font-bold bg-gray-100 px-2 py-0.5 rounded-full border border-gray-200">3. Diinput Admin UPTD (Arsip Internal)</span>
                                            @elseif($req->kode === 'REQ_KEPUTUSAN_GUBERNUR')
                                                <span class="text-[10px] text-blue-700 font-extrabold bg-blue-50 px-2 py-0.5 rounded-full border border-blue-200">4. Diinput Admin UPTD (Dapat Diunduh)</span>
                                            @elseif($req->kode === 'REQ_PEMBERITAHUAN_PEMBAYARAN')
                                                <span class="text-[10px] text-amber-800 font-extrabold bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">5. Diinput Admin UPTD (Dapat Diunduh)</span>
                                            @elseif($req->kode === 'REQ_BUKTI_BAYAR')
                                                <span class="text-[10px] text-emerald-800 font-extrabold bg-emerald-100 px-2.5 py-0.5 rounded-full border border-emerald-300">6. Khusus Diinput Mitra Penyewa</span>
                                            @elseif($req->kode === 'REQ_PERJANJIAN_SEWA')
                                                <span class="text-[10px] text-purple-700 font-extrabold bg-purple-50 px-2 py-0.5 rounded-full border border-purple-200">7. Diinput Admin UPTD (Dapat Diunduh)</span>
                                            @elseif($req->is_required)
                                                <span class="text-[10px] text-gray-500 font-bold bg-gray-100 px-2 py-0.5 rounded-full">Diterbitkan UPTD</span>
                                            @endif
                                        </h4>
                                        <p class="text-[11px] text-gray-500 mt-0.5 leading-normal font-medium">
                                            @if($req->kode === 'REQ_BUKTI_BAYAR')
                                                @if($booking->skema_pembayaran === 'bertahap')
                                                    Uang sewa tahun pertama wajib disetorkan ke Kasda (RKUD) Pemprov Jabar sebelum penandatanganan naskah PKS &amp; serah terima fisik aset. Bukti setor termin tahun ke-2 dan seterusnya diunggah pada tabel jadwal termin di bawah.
                                                @else
                                                    Surat Tanda Setoran (STS) lunas ke Rekening Kas Umum Daerah (RKUD) Pemprov Jawa Barat sebelum penandatanganan naskah PKS.
                                                @endif
                                            @else
                                                {{ $req->deskripsi }}
                                            @endif
                                        </p>

                                        @if($doc)
                                            <div class="mt-2.5 text-[11px] text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                                                @if($req->isViewableByApplicant())
                                                    @if($doc->file_name)
                                                        <span>Berkas: <strong class="text-gray-800">{{ $doc->file_name }}</strong></span>
                                                    @endif
                                                    @if($doc->tanggal_dokumen)
                                                        <span>Diunggah: {{ $doc->tanggal_dokumen->format('d/m/Y') }}</span>
                                                    @endif
                                                    @if($doc->file_path)
                                                        <a href="{{ route('booking.documents.view', $doc) }}" target="_blank" class="text-emerald-700 font-extrabold hover:underline flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            <span>Lihat Berkas Scan</span>
                                                        </a>
                                                    @endif
                                                @else
                                                    <span class="text-gray-500 italic flex items-center gap-1.5 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-200">
                                                        <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                        <span>Dokumen Arsip Internal Kedinasan (Selesai Diproses UPTD)</span>
                                                    </span>
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
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                            Menunggu Terbit UPTD
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

                            <!-- Form Upload Berkas -->
                            @if($isExpired)
                                <div class="mt-3 pt-2.5 border-t border-rose-100 text-[11px] text-rose-600 font-semibold flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Masa tenggang 5 hari kerja telah berakhir. Pengunggahan berkas ditutup.</span>
                                </div>
                            @elseif($isBatchMode && $isMandatory && !$isVerified)
                                <div class="mt-3.5 pt-3 border-t border-gray-200/80">
                                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                                        <div class="flex-grow">
                                            <input type="file" 
                                                   name="documents[{{ $req->kode }}]" 
                                                   id="input-file-{{ $req->kode }}" 
                                                   data-req-code="{{ $req->kode }}" 
                                                   data-req-title="{{ $req->nama_persyaratan }}" 
                                                   accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" 
                                                   class="batch-file-input sr-only">
                                            
                                            <label for="input-file-{{ $req->kode }}" id="label-for-{{ $req->kode }}" class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-dashed border-gray-300 hover:border-emerald-500 hover:bg-emerald-50/40 text-xs font-bold text-gray-700 transition">
                                                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                <span id="label-text-{{ $req->kode }}">Pilih Berkas (PDF / Scan, Maks. 20MB)</span>
                                            </label>
                                        </div>

                                        <div id="file-badge-{{ $req->kode }}" class="hidden items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 font-extrabold shrink-0">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span id="file-name-{{ $req->kode }}" class="truncate max-w-[180px]"></span>
                                            <span id="file-size-{{ $req->kode }}" class="text-[10px] text-emerald-600 font-mono"></span>
                                            <button type="button" onclick="clearFileSelection('{{ $req->kode }}')" class="ml-1 text-gray-400 hover:text-rose-600 transition" title="Hapus pilihan berkas ini">&times;</button>
                                        </div>
                                    </div>
                                </div>
                            @elseif($hasRevision && $isRevision)
                                <form action="{{ route('booking.upload-dokumen', $booking->kode_booking) }}" method="POST" enctype="multipart/form-data" class="mt-3.5 pt-3 border-t border-rose-200 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                    @csrf
                                    <input type="hidden" name="booking_requirement_id" value="{{ $req->id }}">
                                    <div class="flex-grow">
                                        <input type="file" name="dokumen_file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-rose-600 file:text-white hover:file:bg-rose-700 file:cursor-pointer file:transition cursor-pointer bg-white p-1 rounded-xl border border-gray-200 focus:outline-none">
                                    </div>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs shadow-xs transition shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <span>Unggah Berkas Perbaikan</span>
                                    </button>
                                </form>
                            @elseif($req->kode === 'REQ_BUKTI_BAYAR' && $canUpload && !$isVerified)
                                <form action="{{ route('booking.upload-dokumen', $booking->kode_booking) }}" method="POST" enctype="multipart/form-data" class="mt-3.5 pt-3 border-t border-gray-200/80 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                                    @csrf
                                    <input type="hidden" name="booking_requirement_id" value="{{ $req->id }}">
                                    <div class="flex-grow">
                                        <input type="file" name="dokumen_file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer file:transition cursor-pointer bg-white p-1 rounded-xl border border-gray-200 focus:outline-none">
                                    </div>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs transition shrink-0 flex items-center justify-center gap-1.5 cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <span>{{ $doc ? 'Unggah Ulang Bukti Bayar (STS)' : 'Unggah Bukti Bayar Kasda (STS)' }}</span>
                                    </button>
                                </form>
                            @elseif($isNotApplicable)
                                <div class="mt-3 pt-2.5 border-t border-gray-200/60 text-[11px] text-gray-400 italic">
                                    Dokumen ini khusus Badan Usaha/Hukum dan tidak diwajibkan untuk pemohon perorangan.
                                </div>
                            @endif

                        </div>
                    @endforeach

                    @if($isBatchMode)
                        <!-- Sticky Bottom Bar: Single Submit Button -->
                        <div class="mt-8 p-5 sm:p-6 rounded-3xl bg-white border border-gray-200/90 shadow-sm relative overflow-hidden">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5 relative z-10">
                                <div>
                                    <div class="flex items-center gap-2 mb-1.5">
                                        <span id="batch-status-dot" class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                                        <span id="batch-status-badge" class="text-xs font-black uppercase tracking-wider text-amber-500">
                                            Menunggu Kelengkapan Berkas
                                        </span>
                                    </div>
                                    <p id="batch-helper-text" class="text-xs text-gray-500 mt-1 max-w-xl leading-relaxed">
                                        Tombol kirim akan otomatis aktif setelah semua berkas dilengkapi.
                                    </p>
                                    <div id="batch-missing-container" class="mt-2 text-xs text-amber-600 font-medium">
                                        <!-- Diisi secara dinamis oleh JavaScript -->
                                    </div>
                                </div>

                                <div class="shrink-0">
                                    <button type="submit" 
                                            id="btn-batch-submit" 
                                            disabled 
                                            class="w-full sm:w-auto px-7 py-3.5 rounded-2xl font-black text-xs sm:text-sm tracking-wide transition flex items-center justify-center gap-2.5 cursor-not-allowed opacity-50 bg-gray-400 text-white border border-gray-300 shadow-none">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                        <span id="btn-batch-submit-text">Kirim</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    @else
                    </div>
                    @endif
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
                        <span class="text-gray-400 font-bold uppercase tracking-wider text-[10px] block mb-1">NIK / NIB (Dilindungi UU PDP):</span>
                        <span class="font-bold text-gray-800 font-mono tracking-wider">{{ $booking->masked_nik_atau_nib }}</span>
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

            <!-- Rencana Mekanisme Pembayaran Bertahap (HANYA Berlaku Khusus Skema Bertahap) -->
            @if($booking->skema_pembayaran === 'bertahap')
                <div class="bg-white rounded-3xl p-6 sm:p-8 shadow-xs border border-blue-200/90 relative overflow-hidden">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-6 border-b border-gray-100 gap-3">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center shrink-0 shadow-2xs">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-0.5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider">
                                        Khusus Skema Bertahap
                                    </span>
                                    <span class="text-xs text-gray-400 font-bold">&bull;</span>
                                    <span class="text-xs font-bold text-gray-600">{{ $booking->durasi_sewa_tahun }} Termin Tahunan</span>
                                </div>
                                <h3 class="text-base font-extrabold text-gray-900 leading-snug">
                                    Ketentuan &amp; Mekanisme Pembayaran Bertahap
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5 font-medium">
                                    Sesuai Permendagri No. 19/2016 jo. No. 7/2024 dan Perda Jabar No. 3/2026, pembayaran sewa bertahap dituangkan secara mengikat dalam naskah Perjanjian Kerja Sama (PKS).
                                </p>
                            </div>
                        </div>

                        <div class="shrink-0 self-start sm:self-auto">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-800 border border-blue-200 text-xs font-extrabold">
                                <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                                <span>Termin 1 Wajib Sebelum PKS</span>
                            </span>
                        </div>
                    </div>

                    <!-- Ringkasan Alur Prinsip Bertahap -->
                    <div class="p-4 rounded-2xl bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-blue-50/20 border border-blue-200/80 mb-6">
                        <div class="flex items-start gap-3 text-xs leading-relaxed text-blue-950">
                            <svg class="w-5 h-5 text-blue-700 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <div>
                                <strong class="font-extrabold text-blue-950 block mb-0.5">Aturan Pokok Pembayaran Bertahap Sewa BMD:</strong>
                                <span>
                                    Uang sewa <strong>Termin Pertama (Tahun ke-1) wajib disetorkan lunas ke Kas Daerah</strong> sebelum penandatanganan PKS dan Berita Acara Serah Terima (BAST). Pembayaran termin tahun ke-2 dan seterusnya dibayarkan paling lambat <strong>30 (tiga puluh) hari kalender</strong> sebelum masa sewa tahun berjalan dimulai, sesuai tanggal jatuh tempo yang ditetapkan resmi pada naskah PKS.
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline Jadwal Termin -->
                    <div class="space-y-3.5 mb-6">
                        <h4 class="text-xs font-black uppercase tracking-wider text-gray-700 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                            <span>Simulasi Jadwal Pembayaran Termin (Durasi {{ $booking->durasi_sewa_tahun }} Tahun)</span>
                        </h4>

                        <div class="grid grid-cols-1 gap-3.5">
                            @for($t = 1; $t <= $booking->durasi_sewa_tahun; $t++)
                                @php
                                    $terminPayment = $booking->payments->firstWhere('termin_ke', $t);
                                    $hasUploaded = false;
                                    $filePath = null;
                                    $fileName = null;
                                    $stsNumber = null;
                                    $paymentDate = null;
                                    $statusKey = 'menunggu_pembayaran';
                                    $catatanTermin = null;

                                    if ($t === 1) {
                                        $reqBukti = $allReqs->firstWhere('kode', 'REQ_BUKTI_BAYAR');
                                        $docBukti1 = $reqBukti ? $uploadedDocs->get($reqBukti->id) : null;
                                        if ($terminPayment && $terminPayment->file_path) {
                                            $hasUploaded = true;
                                            $filePath = $terminPayment->bukti_url;
                                            $fileName = $terminPayment->file_name;
                                            $stsNumber = $terminPayment->nomor_sts;
                                            $paymentDate = $terminPayment->tanggal_bayar;
                                            $statusKey = $terminPayment->status_pembayaran;
                                            $catatanTermin = $terminPayment->catatan;
                                        } elseif ($docBukti1 && $docBukti1->file_path) {
                                            $hasUploaded = true;
                                            $filePath = route('booking.documents.view', $docBukti1);
                                            $fileName = $docBukti1->file_name;
                                            $stsNumber = $docBukti1->nomor_dokumen;
                                            $paymentDate = $docBukti1->tanggal_dokumen;
                                            $statusKey = ($docBukti1->status_verifikasi === 'verified' ? 'lunas' : ($docBukti1->status_verifikasi === 'revision' ? 'ditolak' : 'pending_verifikasi'));
                                            $catatanTermin = $docBukti1->catatan;
                                        }
                                    } else {
                                        if ($terminPayment && $terminPayment->file_path) {
                                            $hasUploaded = true;
                                            $filePath = $terminPayment->bukti_url;
                                            $fileName = $terminPayment->file_name;
                                            $stsNumber = $terminPayment->nomor_sts;
                                            $paymentDate = $terminPayment->tanggal_bayar;
                                            $statusKey = $terminPayment->status_pembayaran;
                                            $catatanTermin = $terminPayment->catatan;
                                        }
                                    }
                                @endphp

                                <div class="p-4 sm:p-5 rounded-2xl border {{ $statusKey === 'lunas' ? 'bg-emerald-50/40 border-emerald-200' : ($statusKey === 'pending_verifikasi' ? 'bg-amber-50/40 border-amber-200' : ($statusKey === 'ditolak' ? 'bg-rose-50/40 border-rose-200' : ($t === 1 ? 'bg-emerald-50/20 border-emerald-200/80' : 'bg-gray-50/70 border-gray-200'))) }} transition">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-0.5 w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black shrink-0 {{ $statusKey === 'lunas' ? 'bg-emerald-600 text-white shadow-2xs' : ($statusKey === 'pending_verifikasi' ? 'bg-amber-500 text-white shadow-2xs' : ($statusKey === 'ditolak' ? 'bg-rose-600 text-white shadow-2xs' : ($t === 1 ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-blue-600 text-white shadow-2xs'))) }}">
                                                @if($statusKey === 'lunas')
                                                    ✓
                                                @else
                                                    T{{ $t }}
                                                @endif
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h5 class="text-xs sm:text-sm font-extrabold text-gray-900">
                                                        Termin ke-{{ $t }} (Sewa Tahun ke-{{ $t }})
                                                    </h5>
                                                    @if($t === 1)
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                            Syarat Mutlak Akad &amp; Kunci
                                                        </span>
                                                    @else
                                                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-200">
                                                            Jatuh Tempo Tahunan
                                                        </span>
                                                    @endif

                                                    <!-- Status Setoran Badge -->
                                                    @if($statusKey === 'lunas')
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                                            ✓ Lunas (Disetor Kasda)
                                                        </span>
                                                    @elseif($statusKey === 'pending_verifikasi')
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">
                                                            Menunggu Verifikasi Kasda
                                                        </span>
                                                    @elseif($statusKey === 'ditolak')
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300">
                                                            Perlu Perbaikan / Ditolak
                                                        </span>
                                                    @else
                                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">
                                                            Belum Ada Setoran
                                                        </span>
                                                    @endif
                                                </div>

                                                <p class="text-[11px] text-gray-500 mt-1 leading-normal font-medium">
                                                    @if($t === 1)
                                                        Wajib disetor lunas setelah persetujuan Sekda terbit, sebelum tanda tangan naskah PKS dan serah terima kunci/fisik aset.
                                                    @else
                                                        Jatuh tempo paling lambat H-30 hari sebelum masa sewa Tahun ke-{{ $t - 1 }} berakhir (sekitar bulan ke-{{ (($t - 1) * 12) - 1 }} masa sewa).
                                                    @endif
                                                </p>

                                                @if($hasUploaded)
                                                    <div class="mt-2.5 p-2.5 rounded-xl bg-white/80 border border-gray-200/90 text-[11px] text-gray-600 flex flex-wrap items-center gap-x-4 gap-y-1">
                                                        @if($fileName)
                                                            <span>Berkas: <strong class="text-gray-800">{{ $fileName }}</strong></span>
                                                        @endif
                                                        @if($paymentDate)
                                                            <span>Tgl Setor: <strong>{{ \Carbon\Carbon::parse($paymentDate)->format('d/m/Y') }}</strong></span>
                                                        @endif
                                                        @if($stsNumber)
                                                            <span>No. STS: <strong>{{ $stsNumber }}</strong></span>
                                                        @endif
                                                        @if($filePath)
                                                            <a href="{{ $filePath }}" target="_blank" class="text-blue-700 font-extrabold hover:underline flex items-center gap-1">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                <span>Lihat Bukti Setor Bank BJB</span>
                                                            </a>
                                                        @endif
                                                    </div>

                                                    @if($catatanTermin)
                                                        <div class="mt-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-900">
                                                            <strong class="font-bold">Catatan Verifikator Kasda:</strong> {{ $catatanTermin }}
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        <div class="text-left sm:text-right shrink-0">
                                            <span class="text-[10px] text-gray-400 font-bold block uppercase tracking-wider">Jatuh Tempo:</span>
                                            <span class="text-xs font-extrabold {{ $t === 1 ? 'text-emerald-700' : 'text-blue-800' }}">
                                                {{ $t === 1 ? 'Sebelum Penandatanganan PKS' : 'H-30 Hari Tahun ke-' . $t }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Bagian Tombol & Formulir Unggah Bukti Setor -->
                                    <div class="mt-3.5 pt-3 border-t border-gray-200/70">
                                        @if($t === 1)
                                            <div class="flex items-center justify-between text-xs text-gray-500 font-medium">
                                                <div class="flex items-center gap-1.5 text-emerald-800 text-[11px]">
                                                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    <span>Terhubung dengan <strong>Berkas Persyaratan No. 9</strong> (Bukti Setor PAD Termin ke-1).</span>
                                                </div>
                                                <a href="#requirement-REQ_BUKTI_BAYAR" class="text-emerald-700 hover:text-emerald-900 font-extrabold text-[11px] hover:underline shrink-0">
                                                    Lihat Slot Berkas No. 9 &uarr;
                                                </a>
                                            </div>
                                        @else
                                            <!-- Form Upload Bukti Setor Termin Lanjutan (Termin 2 dst.) -->
                                            @if($statusKey !== 'lunas')
                                                <details class="group">
                                                    <summary class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl {{ $hasUploaded ? 'bg-amber-600 hover:bg-amber-700' : 'bg-blue-600 hover:bg-blue-700' }} active:scale-[0.98] text-white font-extrabold text-xs transition cursor-pointer select-none shadow-2xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                        <span>{{ $hasUploaded ? 'Unggah Ulang Bukti Setor Termin ke-' . $t : 'Unggah Bukti Setor Termin ke-' . $t }}</span>
                                                    </summary>

                                                    <form action="{{ route('booking.upload-termin', $booking->kode_booking) }}" method="POST" enctype="multipart/form-data" class="mt-3 p-4 rounded-2xl bg-white border border-blue-200/90 shadow-2xs space-y-3">
                                                        @csrf
                                                        <input type="hidden" name="termin_ke" value="{{ $t }}">
                                                        
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                                            <div class="sm:col-span-2">
                                                                <label class="block font-bold text-gray-700 mb-1">
                                                                    File Slip Setoran / Bukti Transfer Bank BJB <span class="text-rose-500">*</span>
                                                                </label>
                                                                <input type="file" name="bukti_file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full text-xs text-gray-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-extrabold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer file:transition cursor-pointer bg-gray-50 p-1 rounded-xl border border-gray-200 focus:outline-none">
                                                                <span class="text-[10px] text-gray-400 mt-0.5 block">Format yang diterima: PDF, JPG, PNG (Maksimal 20 MB)</span>
                                                            </div>
                                                            <div>
                                                                <label class="block font-bold text-gray-700 mb-1">
                                                                    Nomor STS / NTPD / No. Referensi BJB <span class="text-gray-400 font-normal">(Opsional)</span>
                                                                </label>
                                                                <input type="text" name="nomor_sts" value="{{ old('nomor_sts', $stsNumber) }}" placeholder="Contoh: STS-2026/BPKAD/045" class="w-full text-xs px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                            </div>
                                                            <div>
                                                                <label class="block font-bold text-gray-700 mb-1">
                                                                    Tanggal Penyetoran ke Kasda <span class="text-gray-400 font-normal">(Opsional)</span>
                                                                </label>
                                                                <input type="date" name="tanggal_bayar" value="{{ old('tanggal_bayar', now()->format('Y-m-d')) }}" class="w-full text-xs px-3 py-2 rounded-xl bg-gray-50 border border-gray-200 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                                                            </div>
                                                        </div>

                                                        <div class="pt-2 flex justify-end">
                                                            <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-xs transition cursor-pointer flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                                                <span>Kirim Bukti Setor Termin ke-{{ $t }}</span>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </details>
                                            @else
                                                <div class="flex items-center gap-1.5 text-emerald-800 text-xs font-bold">
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    <span>Setoran Kasda Termin ke-{{ $t }} telah diverifikasi sah dan lunas.</span>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Rekening Resmi Kas Daerah & Tata Cara -->
                    <div class="p-4 sm:p-5 rounded-2xl bg-gray-50 border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block mb-1">Bank Penampung Resmi:</span>
                                <strong class="text-gray-900 block font-bold text-sm">PT Bank Pembangunan Daerah Jawa Barat &amp; Banten (Bank BJB)</strong>
                                <span class="text-[11px] text-gray-500 block mt-0.5">Rekening Kas Umum Daerah (RKUD) Pemerintah Provinsi Jawa Barat</span>
                            </div>
                            <div>
                                <span class="text-[10px] font-extrabold uppercase tracking-wider text-gray-400 block mb-1">Instrumen Penyetoran:</span>
                                <strong class="text-gray-900 block font-bold text-sm">Surat Tanda Setoran (STS) / Kode Billing BPKAD Jabar</strong>
                                <span class="text-[11px] text-gray-500 block mt-0.5">Diterbitkan oleh tim UPTD BPKAD Jabar pada tahapan verifikasi</span>
                            </div>
                            <div class="md:col-span-2 pt-3 border-t border-gray-200/80 text-[11px] text-gray-600 leading-relaxed font-medium">
                                <strong class="text-gray-900 font-bold">Catatan Kepatuhan PKS:</strong> Apabila penyewa terlambat menyetorkan termin tahunan melewati tanggal jatuh tempo yang tertuang dalam PKS, berlaku denda keterlambatan sebesar <strong>1‰ (satu permil) per hari kalender</strong> serta peringatan tertulis (SP 1, SP 2, SP 3). Jika kewajiban tidak diselesaikan, Pemprov Jabar berhak memutus PKS secara sepihak dan menarik kembali penguasaan aset.
                            </div>
                        </div>
                    </div>

                </div>
            @endif

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
                        @if($booking->unit_names_text)
                            <div class="mb-2">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>Unit Ruko: {{ $booking->unit_names_text }}</span>
                                </span>
                            </div>
                        @endif
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Live Countdown Timer
    const timerEl = document.getElementById('live-countdown-timer');
    if (timerEl && timerEl.dataset.deadline) {
        const deadline = new Date(timerEl.dataset.deadline).getTime();
        
        function updateTimer() {
            const now = new Date().getTime();
            const diff = deadline - now;
            
            if (diff <= 0) {
                timerEl.textContent = 'Waktu Habis';
                timerEl.classList.remove('text-amber-600');
                timerEl.classList.add('text-rose-600');
                return;
            }
            
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);
            
            let str = '';
            if (days > 0) str += days + 'h ';
            str += hours + 'j ' + minutes + 'm ' + seconds + 'd';
            
            timerEl.textContent = 'Sisa ' + str;
        }
        
        updateTimer();
        setInterval(updateTimer, 1000);
    }

    // 2. Batch Upload Validation Logic
    const batchForm = document.getElementById('batch-upload-form');
    if (batchForm) {
        const requiredCodes = @json($requiredCodes ?? []);
        const fileInputs = document.querySelectorAll('.batch-file-input');
        const submitBtn = document.getElementById('btn-batch-submit');
        const submitBtnText = document.getElementById('btn-batch-submit-text');
        const statusBadge = document.getElementById('batch-status-badge');
        const statusDot = document.getElementById('batch-status-dot');
        const missingContainer = document.getElementById('batch-missing-container');

        const reqTitleMap = {};
        fileInputs.forEach(input => {
            reqTitleMap[input.dataset.reqCode] = input.dataset.reqTitle || input.dataset.reqCode;
        });

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        window.clearFileSelection = function(code) {
            const input = document.getElementById('input-file-' + code);
            if (input) {
                input.value = '';
                handleFileChange(input);
            }
        };

        function handleFileChange(input) {
            const code = input.dataset.reqCode;
            const badge = document.getElementById('file-badge-' + code);
            const fileNameEl = document.getElementById('file-name-' + code);
            const fileSizeEl = document.getElementById('file-size-' + code);
            const labelText = document.getElementById('label-text-' + code);

            if (input.files && input.files[0]) {
                const file = input.files[0];

                if (file.size > 20 * 1024 * 1024) {
                    alert('Ukuran berkas ' + file.name + ' melebihi batas 20 MB. Silakan pilih berkas yang lebih kecil.');
                    input.value = '';
                    if (badge) {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                    if (labelText) {
                        labelText.textContent = 'Pilih Berkas (PDF / Scan, Maks. 20MB)';
                    }
                    checkCompleteness();
                    return;
                }

                if (fileNameEl) fileNameEl.textContent = file.name;
                if (fileSizeEl) fileSizeEl.textContent = '(' + formatBytes(file.size) + ')';
                if (badge) {
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                }
                if (labelText) labelText.textContent = 'Ganti Berkas';
            } else {
                if (badge) {
                    badge.classList.add('hidden');
                    badge.classList.remove('flex');
                }
                if (labelText) labelText.textContent = 'Pilih Berkas (PDF / Scan, Maks. 20MB)';
            }

            checkCompleteness();
        }

        function checkCompleteness() {
            const selectedCodes = new Set();
            fileInputs.forEach(input => {
                if (input.files && input.files.length > 0) {
                    selectedCodes.add(input.dataset.reqCode);
                }
            });

            const missing = requiredCodes.filter(c => !selectedCodes.has(c));

            if (missing.length === 0) {
                submitBtn.disabled = false;
                submitBtn.className = 'w-full sm:w-auto px-7 py-3.5 rounded-2xl font-black text-xs sm:text-sm tracking-wide transition flex items-center justify-center gap-2.5 cursor-pointer opacity-100 bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 active:scale-[0.98] border border-emerald-500';
                submitBtnText.textContent = 'Kirim';
                
                statusDot.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';
                statusBadge.textContent = '✓ Seluruh Berkas Wajib Siap Dikirim (' + requiredCodes.length + '/' + requiredCodes.length + ')';
                statusBadge.className = 'text-xs font-black uppercase tracking-wider text-emerald-500';
                missingContainer.innerHTML = '<span class="text-emerald-500 font-medium">Semua slot dokumen telah terisi. Klik tombol kirim untuk memproses pengajuan.</span>';
            } else {
                submitBtn.disabled = true;
                submitBtn.className = 'w-full sm:w-auto px-7 py-3.5 rounded-2xl font-black text-xs sm:text-sm tracking-wide transition flex items-center justify-center gap-2.5 cursor-not-allowed opacity-50 bg-gray-400 text-white border border-gray-300 shadow-none';
                submitBtnText.textContent = 'Kirim';
                
                statusDot.className = 'w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse';
                statusBadge.textContent = 'Belum Lengkap (' + selectedCodes.size + '/' + requiredCodes.length + ' Berkas Dipilih)';
                statusBadge.className = 'text-xs font-black uppercase tracking-wider text-amber-500';
                
                const missingNames = missing.map(c => reqTitleMap[c] || c).join(', ');
                missingContainer.innerHTML = '<span class="text-gray-500">Belum dipilih: <strong class="text-gray-700">' + missingNames + '</strong></span>';
            }
        }

        fileInputs.forEach(input => {
            input.addEventListener('change', function () {
                handleFileChange(this);
            });
        });

        batchForm.addEventListener('submit', function (e) {
            submitBtn.disabled = true;
            submitBtnText.textContent = 'Sedang Mengunggah & Memproses Berkas...';
            submitBtn.classList.add('opacity-75');
        });

        checkCompleteness();
    }
});
</script>
@endpush
@endsection
