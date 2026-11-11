@extends('layouts.app')

@section('title', 'Dasar Hukum & Regulasi Pemanfaatan BMD Jawa Barat - BPKAD')

@section('content')
<!-- Page Header (Behance Style: Ultra-Clean, Light & Modern) -->
<div class="relative bg-mesh-light text-gray-900 py-12 lg:py-16 border-b border-gray-200/80 overflow-hidden">
    <!-- Ambient Blur Glows -->
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
                    Dasar Hukum, Regulasi & SOP Pemanfaatan BMD
                </h1>

                <p class="text-sm sm:text-base text-gray-600 mt-3 font-normal leading-relaxed">
                    Kumpulan peraturan perundang-undangan resmi dari tingkat Pemerintah Pusat, Kementerian Dalam Negeri, Pemerintah Provinsi Jawa Barat, hingga Standar Operasional Prosedur (SOP) internal yang menjadi landasan hukum pelaksanaan sewa dan kerjasama pemanfaatan aset daerah.
                </p>
            </div>

            <!-- Quick Download Summary Chip -->
            <div class="bg-white p-4 rounded-3xl border border-gray-200/90 shadow-xs self-start md:self-auto shrink-0 flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Total Dokumen</span>
                    <span class="block text-lg font-black text-gray-900 leading-none mt-0.5">{{ $counts['semua'] }} Regulasi & SOP</span>
                    <span class="text-[11px] text-emerald-700 font-extrabold">Format PDF Asli / OCR</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Highlight Bento Cards (Dynamic 3 or 4 cols) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ ($counts['sop'] ?? 0) > 0 ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-5 mb-10">
        <div class="bg-white p-5 rounded-3xl border border-gray-200/90 shadow-2xs hover:border-emerald-200 transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center font-bold text-xs">
                    JABAR
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">Perda & Pergub Jabar</h3>
                    <span class="text-[11px] text-gray-500 font-medium">{{ $counts['daerah'] }} Dokumen Kebijakan Daerah</span>
                </div>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Mengatur tata laksana teknis pengajuan, wewenang Gubernur & Sekda selaku Pengelola Barang, serta penetapan perjanjian sewa (PKS) di Jawa Barat.
            </p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-200/90 shadow-2xs hover:border-blue-200 transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs">
                    MENDAGRI
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">Permendagri 7/2024 & 19/2016</h3>
                    <span class="text-[11px] text-gray-500 font-medium">{{ $counts['kemendagri'] }} Pedoman Nasional BMD</span>
                </div>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Formula matematis perhitungan tarif pokok sewa, faktor penyesuaian kegiatan usaha, serta insentif afirmatif potongan tarif untuk UMKM & Koperasi.
            </p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-gray-200/90 shadow-2xs hover:border-purple-200 transition">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs">
                    PUSAT
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-gray-900">PP No. 28/2020 & 27/2014</h3>
                    <span class="text-[11px] text-gray-500 font-medium">{{ $counts['pusat'] }} Peraturan Pemerintah</span>
                </div>
            </div>
            <p class="text-xs text-gray-600 leading-relaxed">
                Payung hukum nasional pengelolaan barang milik negara/daerah untuk menjamin fleksibilitas kerjasama investasi dan peningkatan PAD.
            </p>
        </div>

        @if(($counts['sop'] ?? 0) > 0)
            <div class="bg-white p-5 rounded-3xl border border-gray-200/90 shadow-2xs hover:border-amber-200 transition">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-2xl bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-xs">
                        SOP
                    </div>
                    <div>
                        <h3 class="font-extrabold text-sm text-gray-900">SOP & Juknis UPTD</h3>
                        <span class="text-[11px] text-gray-500 font-medium">{{ $counts['sop'] }} Panduan Operasional</span>
                    </div>
                </div>
                <p class="text-xs text-gray-600 leading-relaxed">
                    Petunjuk teknis operasional, alur survei lapangan, standar waktu pelayanan, serta tata cara serah terima aset BMD di lingkungan dinas.
                </p>
            </div>
        @endif
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-gray-200/90 shadow-xs mb-8">
        <form action="{{ route('regulasi.index') }}" method="GET" class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Search Input Bar -->
            <div class="relative flex-grow max-w-xl">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="q" value="{{ $search }}" placeholder="Cari nomor peraturan, topik, atau kata kunci (contoh: Permendagri, Tarif, SOP, UMKM)..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-2xl text-xs sm:text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition">
                @if($search)
                    <a href="{{ route('regulasi.index', ['kategori' => $kategori]) }}" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600" title="Hapus pencarian">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>

            <!-- Category Pills Filter -->
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0 shrink-0">
                <input type="hidden" name="kategori" id="filter-kategori-input" value="{{ $kategori }}">

                <button type="button" onclick="setKategori('semua')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition {{ $kategori === 'semua' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Semua ({{ $counts['semua'] }})
                </button>
                <button type="button" onclick="setKategori('daerah')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition {{ $kategori === 'daerah' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Prov. Jabar ({{ $counts['daerah'] }})
                </button>
                <button type="button" onclick="setKategori('kemendagri')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition {{ $kategori === 'kemendagri' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    Kemendagri ({{ $counts['kemendagri'] }})
                </button>
                <button type="button" onclick="setKategori('pusat')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition {{ $kategori === 'pusat' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    PP Pusat ({{ $counts['pusat'] }})
                </button>
                <button type="button" onclick="setKategori('sop')" class="px-3.5 py-2 rounded-full text-xs font-extrabold transition {{ $kategori === 'sop' ? 'bg-emerald-600 text-white shadow-xs' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                    SOP & Juknis ({{ $counts['sop'] }})
                </button>
            </div>
        </form>
    </div>

    <!-- Regulation Cards Grid (Ultra-Clean Behance Cards) -->
    @if($regulations->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border border-gray-200/90 shadow-xs max-w-xl mx-auto my-8">
            <div class="w-16 h-16 rounded-3xl bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-extrabold text-gray-900 mb-1">Dokumen Regulasi / SOP Tidak Ditemukan</h3>
            <p class="text-xs text-gray-500 mb-5 leading-relaxed">
                Tidak ada dokumen yang cocok dengan kata kunci "{{ $search }}". Silakan coba kata kunci lain atau reset filter.
            </p>
            <a href="{{ route('regulasi.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs transition shadow-sm">
                <span>Reset Pencarian</span>
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($regulations as $reg)
                <div class="bg-white rounded-3xl p-6 sm:p-7 border border-gray-200/90 shadow-md shadow-gray-200/40 hover:shadow-lg hover:border-emerald-200/90 transition flex flex-col justify-between group">
                    
                    <div>
                        <!-- Top Badges Row -->
                        <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wide bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-2xs">
                                    {{ $reg->jenis }}
                                </span>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    Tahun {{ $reg->tahun }}
                                </span>
                                @if($reg->tingkat === 'sop')
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
                                    Poin Pokok untuk Peminat Sewa / Calon Mitra:
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

                        <a href="{{ $reg->url_download }}" class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition flex items-center justify-center gap-1.5">
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
                Butuh Penjelasan Klausul PKS, SOP, atau Simulasi Retribusi Sewa?
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
