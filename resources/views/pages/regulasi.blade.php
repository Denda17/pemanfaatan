@extends('layouts.app')

@section('title', 'Dasar Hukum & Regulasi Pemanfaatan BMD Jawa Barat - BPKAD')

@section('content')
@php
    $groupMeta = [
        'pengelolaan_bmd' => [
            'title' => 'Dasar Hukum Pengelolaan BMD',
            'short' => 'Pengelolaan BMD',
            'desc' => 'Payung hukum Pusat, Kemendagri, hingga Perda & Pergub Jawa Barat terkait tata cara sewa, tarif pokok, dan kerjasama aset daerah.',
            'empty' => 'Dokumen Dasar Hukum Pengelolaan BMD sedang dalam pemutakhiran oleh Tim UPTD.',
        ],
        'kdkmp' => [
            'title' => 'Dasar Hukum KDKMP',
            'short' => 'Regulasi KDKMP',
            'desc' => 'Landasan regulasi, kebijakan afirmasi, dan ketentuan kerjasama Koperasi Desa/Kelurahan Merah Putih (KDKMP) pada pemanfaatan BMD.',
            'empty' => 'Dokumen Dasar Hukum KDKMP sedang disiapkan dan akan segera dipublikasikan oleh Tim UPTD.',
        ],
        'sop' => [
            'title' => 'Standar Operasional Prosedur (SOP)',
            'short' => 'SOP & Juknis',
            'desc' => 'Panduan teknis alur pengajuan sewa, verifikasi berkas, survei lapangan, seleksi beauty contest, hingga serah terima aset.',
            'empty' => 'Dokumen SOP & Petunjuk Teknis sedang dalam tahap pemutakhiran oleh Tim UPTD.',
        ],
        'semua' => [
            'title' => 'Seluruh Dokumen Regulasi & SOP',
            'short' => 'Semua Kategori',
            'desc' => 'Menampilkan gabungan seluruh dokumen Dasar Hukum Pengelolaan BMD, KDKMP, dan SOP UPTD.',
            'empty' => 'Belum ada dokumen regulasi yang dipublikasikan.',
        ],
    ];
    $currentMeta = $groupMeta[$kelompok] ?? $groupMeta['pengelolaan_bmd'];
@endphp

<!-- Page Header -->
<div class="relative bg-mesh-light text-gray-900 py-10 lg:py-14 border-b border-gray-200/80 overflow-hidden">
    <div class="absolute top-0 right-10 w-96 h-96 bg-emerald-400/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-0 left-10 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
            <div class="max-w-3xl">
                <nav class="flex items-center gap-2 text-xs text-gray-500 mb-3 font-semibold">
                    <a href="{{ route('home') }}" class="hover:text-emerald-700 transition">Beranda</a>
                    <span class="text-gray-300">/</span>
                    <span class="text-emerald-700 font-extrabold">Dasar Hukum & Regulasi</span>
                </nav>

                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-extrabold mb-3 border border-emerald-200/80 shadow-2xs">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    Landasan Yuridis & Kepastian Investasi BMD
                </div>

                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-gray-900 leading-tight">
                    Dasar Hukum & Regulasi Pemanfaatan BMD
                </h1>

                <p class="text-sm sm:text-base text-gray-600 mt-3 font-normal leading-relaxed">
                    Pilih kategori di bawah untuk menelusuri regulasi <strong>Pengelolaan BMD</strong>, <strong>Dasar Hukum KDKMP</strong>, maupun <strong>Standar Operasional Prosedur (SOP)</strong> pelayanan resmi di lingkungan Pemprov Jawa Barat.
                </p>
            </div>

            <!-- Total Dokumen Chip -->
            <div class="bg-white p-4 rounded-3xl border border-gray-200/90 shadow-xs self-start md:self-auto shrink-0 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Pusat Dokumen</span>
                    <span class="block text-lg font-black text-gray-900 leading-none mt-0.5">{{ $groupCounts['semua'] }} Berkas Resmi</span>
                    <span class="text-[11px] text-emerald-700 font-extrabold">3 Kategori Terpisah &bull; PDF</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- 3 INTERACTIVE CATEGORY PILLAR CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        <!-- Pilar 1: Dasar Hukum Pengelolaan BMD -->
        <a
            href="{{ route('regulasi.index', ['kelompok' => 'pengelolaan_bmd']) }}"
            class="group relative text-left p-5 sm:p-6 rounded-3xl border transition-all duration-200 flex flex-col justify-between {{ $kelompok === 'pengelolaan_bmd' ? 'bg-emerald-50/60 border-emerald-500 ring-2 ring-emerald-500/20 shadow-md shadow-emerald-900/5' : 'bg-white border-gray-200/90 hover:border-emerald-300 hover:shadow-sm' }}"
        >
            <div>
                <div class="flex items-start justify-between gap-3 mb-3.5">
                    <div class="w-11 h-11 rounded-2xl {{ $kelompok === 'pengelolaan_bmd' ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/30' : 'bg-emerald-50 text-emerald-700 border border-emerald-100' }} flex items-center justify-center shrink-0 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $kelompok === 'pengelolaan_bmd' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-700 group-hover:bg-emerald-100 group-hover:text-emerald-800' }} transition">
                        {{ $groupCounts['pengelolaan_bmd'] }} Dokumen
                    </span>
                </div>
                <h2 class="text-base font-extrabold text-gray-900 group-hover:text-emerald-800 transition">
                    1. Dasar Hukum Pengelolaan BMD
                </h2>
                <p class="text-xs text-gray-600 leading-relaxed mt-1.5">
                    PP, Permendagri, serta Perda & Pergub Jawa Barat mengenai tata kelola, perhitungan tarif pokok sewa, dan kerjasama aset daerah.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t {{ $kelompok === 'pengelolaan_bmd' ? 'border-emerald-200/80 text-emerald-800' : 'border-gray-100 text-gray-500 group-hover:text-emerald-700' }} flex items-center justify-between text-xs font-extrabold">
                <span>{{ $kelompok === 'pengelolaan_bmd' ? 'Sedang Ditampilkan' : 'Buka Kategori Ini' }}</span>
                <span>&rarr;</span>
            </div>
        </a>

        <!-- Pilar 2: Dasar Hukum KDKMP -->
        <a
            href="{{ route('regulasi.index', ['kelompok' => 'kdkmp']) }}"
            class="group relative text-left p-5 sm:p-6 rounded-3xl border transition-all duration-200 flex flex-col justify-between {{ $kelompok === 'kdkmp' ? 'bg-blue-50/60 border-blue-500 ring-2 ring-blue-500/20 shadow-md shadow-blue-900/5' : 'bg-white border-gray-200/90 hover:border-blue-300 hover:shadow-sm' }}"
        >
            <div>
                <div class="flex items-start justify-between gap-3 mb-3.5">
                    <div class="w-11 h-11 rounded-2xl {{ $kelompok === 'kdkmp' ? 'bg-blue-600 text-white shadow-sm shadow-blue-600/30' : 'bg-blue-50 text-blue-700 border border-blue-100' }} flex items-center justify-center shrink-0 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $kelompok === 'kdkmp' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 group-hover:bg-blue-100 group-hover:text-blue-800' }} transition">
                        {{ $groupCounts['kdkmp'] }} Dokumen
                    </span>
                </div>
                <h2 class="text-base font-extrabold text-gray-900 group-hover:text-blue-800 transition">
                    2. Dasar Hukum KDKMP
                </h2>
                <p class="text-xs text-gray-600 leading-relaxed mt-1.5">
                    Regulasi khusus, kebijakan afirmasi, dan dasar hukum kemitraan Koperasi Desa/Kelurahan Merah Putih (KDKMP) dalam pemanfaatan BMD.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t {{ $kelompok === 'kdkmp' ? 'border-blue-200/80 text-blue-800' : 'border-gray-100 text-gray-500 group-hover:text-blue-700' }} flex items-center justify-between text-xs font-extrabold">
                <span>{{ $kelompok === 'kdkmp' ? 'Sedang Ditampilkan' : 'Buka Kategori Ini' }}</span>
                <span>&rarr;</span>
            </div>
        </a>

        <!-- Pilar 3: Standar Operasional Prosedur (SOP) -->
        <a
            href="{{ route('regulasi.index', ['kelompok' => 'sop']) }}"
            class="group relative text-left p-5 sm:p-6 rounded-3xl border transition-all duration-200 flex flex-col justify-between {{ $kelompok === 'sop' ? 'bg-amber-50/60 border-amber-500 ring-2 ring-amber-500/20 shadow-md shadow-amber-900/5' : 'bg-white border-gray-200/90 hover:border-amber-300 hover:shadow-sm' }}"
        >
            <div>
                <div class="flex items-start justify-between gap-3 mb-3.5">
                    <div class="w-11 h-11 rounded-2xl {{ $kelompok === 'sop' ? 'bg-amber-500 text-white shadow-sm shadow-amber-500/30' : 'bg-amber-50 text-amber-700 border border-amber-100' }} flex items-center justify-center shrink-0 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-extrabold {{ $kelompok === 'sop' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-700 group-hover:bg-amber-100 group-hover:text-amber-800' }} transition">
                        {{ $groupCounts['sop'] }} Dokumen
                    </span>
                </div>
                <h2 class="text-base font-extrabold text-gray-900 group-hover:text-amber-800 transition">
                    3. SOP &amp; Petunjuk Teknis
                </h2>
                <p class="text-xs text-gray-600 leading-relaxed mt-1.5">
                    Standar Operasional Prosedur pelayanan pengajuan sewa, verifikasi berkas, survei lapangan, seleksi beauty contest, dan serah terima aset.
                </p>
            </div>
            <div class="mt-4 pt-3 border-t {{ $kelompok === 'sop' ? 'border-amber-200/80 text-amber-800' : 'border-gray-100 text-gray-500 group-hover:text-amber-700' }} flex items-center justify-between text-xs font-extrabold">
                <span>{{ $kelompok === 'sop' ? 'Sedang Ditampilkan' : 'Buka Kategori Ini' }}</span>
                <span>&rarr;</span>
            </div>
        </a>
    </div>

    <!-- Active Category Banner + Search & Contextual Sub-Filter -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-gray-200/90 shadow-xs mb-8 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3.5 border-b border-gray-100">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="text-sm sm:text-base font-extrabold text-gray-900">{{ $currentMeta['title'] }}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-gray-100 text-gray-700">
                        {{ $regulations->count() }} ditampilkan
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5">{{ $currentMeta['desc'] }}</p>
            </div>

            @if($kelompok !== 'semua')
                <a href="{{ route('regulasi.index', ['kelompok' => 'semua', 'q' => $search ?: null]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-full text-xs font-bold bg-gray-100 text-gray-700 hover:bg-gray-200 transition shrink-0 self-start sm:self-auto">
                    <span>Gabungkan Semua Kategori ({{ $groupCounts['semua'] }})</span>
                </a>
            @endif
        </div>

        <form action="{{ route('regulasi.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <input type="hidden" name="kelompok" id="filter-kelompok-input" value="{{ $kelompok }}">
            <input type="hidden" name="kategori" id="filter-kategori-input" value="{{ $kategori }}">

            <!-- Search Input Bar -->
            <div class="relative flex-grow max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nomor peraturan, judul, atau kata kunci di {{ strtolower($currentMeta['short']) }}..." class="w-full pl-10 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @if($search)
                    <a href="{{ route('regulasi.index', ['kelompok' => $kelompok]) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" title="Hapus pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <!-- Hierarchy Sub-Filter Pills (Disembunyikan saat berada di tab SOP agar bersih) -->
            @if($kelompok !== 'sop')
                <div class="flex items-center gap-1.5 flex-wrap shrink-0">
                    <button type="button" onclick="setKategori('semua')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition cursor-pointer {{ $kategori === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Semua Tingkat ({{ $counts['semua'] }})
                    </button>
                    <button type="button" onclick="setKategori('daerah')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition cursor-pointer {{ $kategori === 'daerah' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Prov. Jabar ({{ $counts['daerah'] }})
                    </button>
                    <button type="button" onclick="setKategori('kemendagri')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition cursor-pointer {{ $kategori === 'kemendagri' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Kementerian ({{ $counts['kemendagri'] }})
                    </button>
                    <button type="button" onclick="setKategori('pusat')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition cursor-pointer {{ $kategori === 'pusat' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                        Pusat ({{ $counts['pusat'] }})
                    </button>
                </div>
            @endif
        </form>
    </div>

    <!-- Regulation Cards Grid -->
    @if($regulations->isEmpty())
        <div class="bg-white rounded-3xl p-10 sm:p-12 text-center border border-gray-200/90 shadow-xs max-w-xl mx-auto my-6">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            @if($search)
                <h3 class="text-base font-extrabold text-gray-900 mb-1">Dokumen Tidak Ditemukan</h3>
                <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                    Tidak ada dokumen pada <strong>{{ $currentMeta['title'] }}</strong> yang cocok dengan kata kunci "{{ $search }}".
                </p>
                <a href="{{ route('regulasi.index', ['kelompok' => $kelompok]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition shadow-sm">
                    <span>Reset Pencarian</span>
                </a>
            @else
                <h3 class="text-base font-extrabold text-gray-900 mb-1">Belum Ada Dokumen pada Kategori Ini</h3>
                <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                    {{ $currentMeta['empty'] }}
                </p>
                @if($kelompok !== 'pengelolaan_bmd')
                    <a href="{{ route('regulasi.index', ['kelompok' => 'pengelolaan_bmd']) }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition shadow-sm">
                        <span>Lihat Dasar Hukum Pengelolaan BMD</span>
                    </a>
                @endif
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($regulations as $reg)
                @php
                    $isKdkmp = $reg->kategori_regulasi === 'kdkmp';
                    $isSop = $reg->kategori_regulasi === 'sop' || $reg->tingkat === 'sop';
                    $badgeTheme = $isSop
                        ? 'bg-amber-50 text-amber-800 border-amber-200'
                        : ($isKdkmp ? 'bg-blue-50 text-blue-800 border-blue-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200');
                    $btnTheme = $isSop
                        ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20'
                        : ($isKdkmp ? 'bg-blue-600 hover:bg-blue-700 shadow-blue-600/20' : 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20');
                @endphp
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/90 shadow-md shadow-gray-200/40 hover:shadow-lg hover:border-emerald-200/90 transition flex flex-col justify-between group">

                    <div>
                        <!-- Top Badges Row -->
                        <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wide border shadow-2xs {{ $badgeTheme }}">
                                    {{ $reg->jenis }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    Tahun {{ $reg->tahun }}
                                </span>
                                @if($isKdkmp)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-blue-100/80 text-blue-900 border border-blue-200">
                                        KDKMP
                                    </span>
                                @elseif($isSop)
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-50 text-amber-800 border border-amber-200">
                                        Internal SOP
                                    </span>
                                @endif
                            </div>

                            <span class="inline-flex items-center gap-1.5 text-[11px] font-mono font-bold text-gray-500 bg-gray-50 px-2.5 py-0.5 rounded-full border border-gray-200">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M11.363 2c4.155 0 2.637 6 2.637 6s6-1.65 6 2.457v11.543h-16v-20h7.363zm.826-2h-10.189v24h20v-14.363c0-6.233-4.562-7.447-7.447-7.447-1.127 0-1.844.75-2.364 1.81z"/></svg>
                                <span>PDF &bull; {{ $reg->formatted_size }}</span>
                            </span>
                        </div>

                        <!-- Regulation Number & Title -->
                        <h3 class="text-base sm:text-lg font-black text-gray-900 group-hover:text-emerald-700 transition leading-snug mb-1">
                            {{ $reg->nomor }}
                        </h3>
                        <h4 class="text-xs sm:text-sm font-extrabold text-gray-700 mb-2 leading-relaxed">
                            {{ $reg->judul }}
                        </h4>
                        <p class="text-[11px] text-gray-400 font-semibold mb-3 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ $reg->penerbit }}</span>
                        </p>

                        <!-- Summary Description -->
                        @if($reg->deskripsi)
                            <p class="text-xs text-gray-600 leading-relaxed mb-4">
                                {{ $reg->deskripsi }}
                            </p>
                        @endif

                        <!-- Key Takeaway Bullets -->
                        @php
                            $poinKunci = is_array($reg->poin_kunci) ? $reg->poin_kunci : (json_decode($reg->poin_kunci, true) ?: []);
                        @endphp
                        @if(!empty($poinKunci))
                            <div class="bg-gray-50/90 rounded-2xl p-4 border border-gray-100 mb-6">
                                <span class="text-[10px] uppercase font-bold text-gray-400 tracking-wider block mb-2">
                                    {{ $isSop ? 'Tahapan & Ketentuan Layanan:' : 'Poin Pokok untuk Peminat Sewa / Calon Mitra:' }}
                                </span>
                                <ul class="space-y-1.5">
                                    @foreach($poinKunci as $pk)
                                        <li class="flex items-start gap-2 text-[11px] text-gray-700 font-medium leading-relaxed">
                                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span>{{ $pk }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <!-- Action Buttons: Preview & Download -->
                    <div class="pt-3 border-t border-gray-100 flex items-center gap-2.5">
                        <a href="{{ $reg->url_preview }}" target="_blank" class="flex-1 py-2.5 px-4 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-800 font-extrabold text-xs transition flex items-center justify-center gap-1.5 border border-gray-200/80 shadow-2xs">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span>Lihat Dokumen</span>
                        </a>

                        <a href="{{ $reg->url_download }}" class="flex-1 py-2.5 px-4 rounded-xl {{ $btnTheme }} active:scale-[0.98] text-white font-extrabold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh PDF</span>
                        </a>
                    </div>

                </div>
            @endforeach
        </div>
    @endif

    <!-- Helpdesk Banner for Legal Consultation -->
    <div class="mt-12 bg-gradient-to-r from-emerald-800 to-teal-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 max-w-xl">
            <span class="inline-block px-3 py-1 rounded-full bg-emerald-700/80 text-[10px] font-extrabold uppercase tracking-wider mb-2 border border-emerald-500/40">
                Pusat Bantuan & Konsultasi Regulasi
            </span>
            <h3 class="text-xl sm:text-2xl font-black tracking-tight leading-snug">
                Butuh Penjelasan Klausul PKS, KDKMP, SOP, atau Simulasi Retribusi Sewa?
            </h3>
            <p class="text-xs sm:text-sm text-emerald-100 mt-2 font-normal leading-relaxed">
                Tim UPTD Pengamanan dan Pemanfaatan Aset BPKAD Jabar siap memberikan pendampingan hukum dan simulasi resmi sesuai ketentuan peraturan perundang-undangan.
            </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 shrink-0">
            <a href="https://wa.me/6281234567890?text=Halo%20Admin%20UPTD%20Pemanfaatan%20Aset%20Jabar,%20saya%20ingin%20konsultasi%20regulasi%20dan%20perhitungan%20sewa%20BMD" target="_blank" rel="noopener noreferrer" class="px-6 py-3.5 rounded-full bg-white text-emerald-900 hover:bg-emerald-50 font-extrabold text-xs sm:text-sm shadow-md transition flex items-center gap-2 active:scale-95">
                <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                <span>Konsultasi Regulasi via WA</span>
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function setKategori(kat) {
        const input = document.getElementById('filter-kategori-input');
        if (input) {
            input.value = kat;
            input.form.submit();
        }
    }
</script>
@endpush
